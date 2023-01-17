<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;
  use rjdeliveryomaha\courierinvoice\Query;
  use rjdeliveryomaha\courierinvoice\Ticket;
  use rjdeliveryomaha\courierinvoice\Invoice;
  use rjdeliveryomaha\courierinvoice\Client;
  use rjdeliveryomaha\courierinvoice\TicketChart;
  use rjdeliveryomaha\courierinvoice\InvoiceChart;

  class CommonFunctions
  {
    protected $username;
    protected $publicKey;
    protected $privateKey;
    // preserve the options to be passed to new objects
    protected $options;
    // session validation key
    protected $formKey;
    // session dependant variables
    protected $config;
    protected $organizationFlag = false;
    protected $CanDispatch;
    protected $weightMarker;
    protected $rangeMarker;
    protected $countryClass;
    protected $countryInput;
    protected $requireCountry = 'required';
    protected $shippingCountry;
    protected $headerLogo;
    protected $headerLogo2;
    protected $myInfo;
    protected $maxRange;
    protected $timezone;
    protected $ulevel;
    protected $RepeatClient;
    protected $ListBy;
    protected $members;
    protected $pwWarning;
    // other variables
    protected $noSession = false;
    protected $dateObject;
    // string: login name for courier invoice user
    protected $userLogin;
    // maximum number of months to display on a chart
    protected $allTimeChartLimit = 6;
    // Client names that should be changed for example to abbreviate
    protected $clientNameExceptions = [];
    // Addresses that should be ignored for example due to change of address
    protected $clientAddressExceptions = [];
    protected $dryIceStep;
    protected $sanitized;
    protected $enableLogging = false;
    protected $targetFile;
    protected $fileWriteTry;
    protected $loggingError = false;
    protected $error = '';
    protected $postKeys;
    protected $paperFormat;
    protected $paperOrientation;
    private $ints = [ 'dryIce', 'DryIce', 'diWeight', 'fromMe', 'toMe', 'charge', 'Charge', 'type', 'Type', 'contract',
      'Contract', 'DispatchedTo', 'emailConfirm', 'EmailConfirm', 'pSigReq', 'dSigReq', 'd2SigReq', 'repeatClient',
      'RepeatClient', 'ticketNumber', 'TicketNumber', 'sigType', 'PriceOverride', 'RunNumber', 'holder', 'receiver',
      'TransferState', 'ClientID', 'Organization', 'ListBy', 'same', 'Closed', 'Deleted', 'receivedReady',
      'ReceivedReady', 'VATable', 'VATtype', 'VATableIce', 'VATtypeIce', 'InvoiceTerms', 'DiscountWindow', 'TermLength'
    ];
    // Properties that should always be floats
    private $floats = [ 'diPrice', 'TicketBase', 'RunPrice', 'TicketPrice', 'Multiplier', 'timestamp', 'lat',' lng',
      'maxRange', 'pLat', 'pLng', 'dLat', 'dLng', 'd2Lat', 'd2Lng', 'latitude', 'longitude', 'BalanceForwarded',
      'InvoiceSubTotal', 'AmountDue', 'InvoiceTotal', 'AmountPaid', 'Balance', 'Late30Value', 'Late60Value',
      'Late90Value', 'Over90Value', 'VATrate', 'VATrateIce', 'DiscountRate'
    ];
    // Properties that should always be boolean
    private $bools = [ 'newTicket', 'compare', 'compareMembers', 'ticketEditor', 'updateTicket',
      'consolidateContractTicketsOnInvoice', 'showCanceledTicketsOnInvoice', 'organizationFlag', 'noSession',
      'processTransfer', 'mapAvailable'
    ];
    // Properties that are passed at the end of a string value
    private $afterSemicolon = [ 'billTo', 'dispatchedTo', 'PendingReceiver' ];
    // Properties that are json encoded strings
    private $jsonStrings = [ 'Transfers' ];
    // No need to filter passwords they will be hashed
    private $noFilter = [ 'currentPw', 'newPw1', 'newPw2' ];
    // These properties should not be accessible when setting values from the $data argument
    private $protectedProperties = [ 'username', 'publicKey', 'privateKey', 'config', 'weightMarker', 'rangeMarker',
      'countryClass', 'countryInput', 'requireCountry', 'shippingCountry', 'headerLogo', 'headerLogo2', 'myInfo',
      'clientNameExceptions', 'clientAddressExceptions', 'showCanceledTicketsOnInvoiceExceptions', 'ignoreValues',
      'showCanceledTicketsOnInvoice', 'consolidateContractTicketsOnInvoice', 'ints', 'floats', 'bools', 'afterSemicolon',
      'jsonStrings', 'noFilter', 'sanitized', 'enableLogging', 'targetFile', 'fileWriteTry', 'loggingError', 'error',
      'protectedProperties', 'RangeCenter', 'lat', 'lng', 'maxRange', 'timezone', 'emailConfig', 'allTimeChartLimit',
      'invoicePage1Max', 'invoicePageMax', 'dryIceStep'
    ];
    protected $nullable = [ 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'Department', 'Contact', 'Telephone', 'pTime',
      'dTime', 'd2Time', 'Notes', 'LastName', 'EmailAddress', 'LastSeen', 'Attention', 'RequestedBy', 'pDepartment',
      'pContact', 'pTelephone', 'dDepartment', 'dContact', 'dTelephone', 'pSigPrint', 'pSig', 'dSigPrint', 'dSig',
      'd2SigPrint', 'd2Sig', 'ReadyDate', 'DispatchTimeStamp', 'Transfers', 'DatePaid', 'Late30Invoice', 'Late30Value',
      'Late60Invoice', 'Late60Value', 'Late90Invoice', 'Late90Value', 'Over90Invoice', 'Over90Value', 'CheckNumber',
      'pLat', 'pLng', 'dLat', 'dLng', 'd2Lat', 'd2Lng', 'latitude', 'longitude'
    ];
    private $noGetProps = [ 'error', 'loggingError', 'fileWriteTry', 'sanitized' ];
    private $customMenuItems;
    private $customPages;
    private $customScripts;

    public function __construct($options, $data=[])
    {
      if (!is_array($options) || empty($options)) {
        throw new \Exception('Invalid Configuration');
      }
      $this->options = $options;
      do {
        $this->{key($options)} = current($options);
        next($options);
      } while (key($options) !== null);

      if ($this->enableLogging !== false && ($this->targetFile === '' || $this->targetFile === null)) {
        $this->error .= "Logging Enabled With No Target File.\n";
        $this->loggingError = true;
      }
      if (!preg_match('/([A-Za-z]{1}[\d]+)/', $this->username)) {
        $this->error .= "User Name has unexpected format.\n";
      }
      if (strlen($this->publicKey) !== 32) {
        $this->error .= "Public Key has unexpected length.\n";
      }
      if (strlen($this->privateKey) !== 32) {
        $this->error .= "Private Key has unexpected length.\n";
      }
      if ($this->error !== '') {
        if ($this->enableLogging !== false && $this->loggingError === false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ($this->options['testMode'] === true && $this->options['testURL'] === null || $this->options['testURL'] === '') {
        throw new \Exception('Invalid URL');
      }
      if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST))
        throw new \Exception('Post body unavailable');
      $this->postKeys = array_keys($_POST);
      if (!isset($data['noSession'])) {
        if (
          empty($_SESSION) ||
          !isset($_SESSION['ulevel']) ||
          !isset($_SESSION['config']) ||
          empty($_SESSION['config'])
        ) {
          $this->error = 'Session Error';
          if ($this->enableLogging !== false) self::writeLoop();
          throw new \Exception($this->error);
        }
        $this->ulevel = $_SESSION['ulevel'];
        // Populate the config array from the Session
        $this->config = $_SESSION['config'];
        // RangeCenter is stored as a string it needs to be an array
        if (strpos($_SESSION['config']['RangeCenter'], ',') !== false) $this->config['RangeCenter'] = [
          'lat' => (float)self::before(',', $this->config['RangeCenter']),
          'lng' => (float)self::after(',', $this->config['RangeCenter'])
        ];

        if (isset($_SESSION['pwWarning'])) $this->pwWarning = $_SESSION['pwWarning'];
        if (is_numeric($this->ulevel)) $this->ClientID = $_SESSION['ClientID'];
        if (self::after_last('\\', get_class($this)) !== 'Client' && isset($_SESSION['members'])) {
          $this->organizationFlag = $this->ulevel === 0;
          foreach ($_SESSION['members'] as $key => $value) {
            $temp = self::createClient($value);
            if ($temp === false) {
              throw new \Exception($this->error);
            }
            $this->members[$key] = $temp;
          }
        }
        if (isset($_SESSION['driver_index']) || isset($_SESSION['dispatch_index'])) {
          $this->CanDispatch = (isset($_SESSION['CanDispatch'])) ? (int)$_SESSION['CanDispatch'] : null;
          $this->shippingCountry = $this->config['ShippingCountry'];
        }
        $this->logo = $this->config['LogoFileName'];
        $this->logoFile = glob("../images/logo/{$this->logo}");
        if (!$this->logoFile) {
          $this->headerLogo = "<div><h1>{$this->config['ClientName']}</h1></div>";
          $this->headerLogo2 = "<h5>{$this->config['ClientName']}</h5>";
        } else {
          $this->headerLogo = "<img class=\"invoiceLogo\" src=\"../images/logo/{$this->logo}\" alt=\"{$this->config['ClientName']}\" />";
          $this->headerLogo2 = "<img class=\"ticketLogo\" src=\"../images/logo/{$this->logo}\" alt=\"{$this->config['ClientName']}\" />";
        }
        if (self::test_bool($this->config['WeightsMeasures']) === false) {
          $this->rangeMarker = 'mi';
          $this->weightMarker = '&#35;';
        } else {
          $this->rangeMarker = 'km';
          $this->weightMarker = 'kg;';
        }
        if (self::test_bool($this->config['InternationalAddressing']) === false) {
          $this->countryClass = 'hide';
          $this->countryInput = 'disabled';
          $this->requireCountry = '';
        }
        $this->myInfo = [
          'Name' => $this->config['ClientName'],
          'EmailAddress' => $this->config['EmailAddress'],
          'Telephone' => $this->config['Telephone']
        ];
      }
      $this->sanitized = self::recursive_santizer($data);
      do {
        if (
          !in_array(key($this->sanitized), $this->protectedProperties) &&
          !in_array(ucfirst(key($this->sanitized)), $this->protectedProperties)
        ) {
          if (property_exists($this, ucfirst(key($this->sanitized)))) {
            $temp = ucfirst(key($this->sanitized));
            $this->{$temp} = current($this->sanitized);
          }
          $this->{key($this->sanitized)} = current($this->sanitized);
        }
        next($this->sanitized);
      } while (key($this->sanitized) !== null);
    }

    public function getProperty($property)
    {
      if (property_exists($this, $property) && !in_array($property, $this->noGetProps)) {
        return (is_array($this->{$property})) ? json_encode($this->{$property}) : $this->{$property};
      }
      return false;
    }

    public function updateProperty($property, $value)
    {
      if (property_exists($this, $property) && !in_array($property, $this->noGetProps)) {
        if (in_array($property, $this->ints)) {
          if (in_array($property, $this->nullable)) {
            return $this->{$property} = ($value === '' || $value === null) ? null : self::test_int($value);
          } else {
            return $this->{$property} = self::test_int($value);
          }
        } elseif (in_array($property, $this->floats)) {
          if (in_array($property, $this->nullable)) {
            return $this->{$property} = ($value === '' || $value === null) ? null : self::test_float($value);
          } else {
            return $this->{$property} = self::test_float($value);
          }
        } elseif (in_array($property, $this->bools)) {
          if (in_array($property, $this->nullable)) {
            return $this->{$property} = ($value === '' || $value === null) ? null : self::test_bool($value);
          } else {
            return $this->{$property} = self::test_bool($value);
          }
        } else {
          if (is_array($value)) {
            $value = self::recursive_santizer($value);
          } else {
            if (in_array($property, $this->nullable)) {
              $value = ($value === '' || $value === null) ? null : self::test_input($value);
            } else {
              $value = self::test_input($value);
            }
          }
          return $this->{$property} = $value;
        }
      }
      return false;
    }

    public function addToProperty($property, $value)
    {
      if (
        property_exists($this, $property) &&
        (in_array($property, $this->ints) || in_array($property, $this->floats)) &&
        is_numeric($value)
      ) {
        return $this->{$property} += $value;
      }
      return false;
    }

    public function substractFromProperty($property, $value)
    {
      if (
        property_exists($this, $property) &&
        (in_array($property, $this->ints) || in_array($property, $this->floats)) &&
        is_numeric($value)
      ) {
        return $this->{$property} -= $value;
      }
      return false;
    }

    public function compareProperties($obj1, $obj2, $property, $strict=false)
    {
      if (!(is_object($obj1) && is_object($obj2))) {
        return false;
      }
      if (!property_exists($obj1, $property) || !property_exists($obj2, $property)) {
        return false;
      }
      return ($strict === true) ?
        ($obj1->getProperty($property) === $obj2->getProperty($property)) :
        ($obj1->getProperty($property) == $obj2->getProperty($property));
    }

    public function debug()
    {
      return self::safe_print_r($this);
    }

    public function getError()
    {
      return $this->error;
    }

    protected function writeLoop()
    {
      $this->error .= PHP_EOL . get_class($this) . PHP_EOL;
      $i = 0;
      do {
        $test = self::writeFile();
        $i++;
      } while ($test !== strlen($this->error) && $i < $this->fileWriteTry);
    }

    protected function writeFile()
    {
      /*** http://php.net/manual/en/function.fwrite.php#81269 ***/
      $fp = fopen( $this->targetFile, 'ab' );
      /*** write the new file content ***/
      $bytes_to_write = strlen($this->error);
      $bytes_written = 0;
      while ($bytes_written < $bytes_to_write) {
        if ($bytes_written == 0) {
          $rv = fwrite($fp, $this->error);
        } else {
          $rv = fwrite($fp, substr($this->error, $bytes_written));
        }
        if ($rv === false || $rv == 0) {
          return($bytes_written == 0 ? false : $bytes_written);
        }
        $bytes_written += $rv;
      }
      return $bytes_written;
    }

    protected function setTimezone()
    {
      if (!date_default_timezone_set($this->config['TimeZone'])) {
        $this->error = 'Timezone Error Line ' . __line__;
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ($this->timezone === null || !is_object($this->timezone)) {
        try {
          $this->timezone = new \dateTimeZone($this->config['TimeZone']);
        } catch (\Exception $e) {
          $this->error = 'Timezone Error Line ' . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== false) self::writeLoop();
          throw new \Exception($this->error);
        }
      }
    }

    protected function createDateObject($dateString = 'NOW') {
      try {
        self::setTimezone();
      } catch (\Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        throw $e;
      }
      try {
        $this->dateObject = new \dateTime($dateString, $this->timezone);
      } catch (\Exception $e) {
        $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        throw $e;
      }
    }

    protected function recursive_santizer($array, $filter='input')
    {
      // possible filters: email, float, int
      $returnData = array();
      if (!$array) return $returnData;
      foreach ($array as $key => $value) {
        if (is_object($value)) {
          $returnData[$key] = $value;
        } elseif (is_array($value)) {
          $returnData[$key] = self::recursive_santizer($value);
        } elseif (in_array($key, $this->ints, true) || substr($key, -5) === 'index') {
          if (in_array($key, $this->nullable)) {
            $returnData[$key] = ($value === '' || $value === null) ? null : self::test_int($value);
          } else {
            $returnData[$key] = self::test_int($value);
          }
        } elseif (in_array($key, $this->bools, true)) {
          if (in_array($key, $this->nullable)) {
            $returnData[$key] = ($value === '' || $value === null) ? null : self::test_bool($value);
          } else {
            $returnData[$key] = self::test_bool($value);
          }
        } elseif (in_array($key, $this->floats, true)) {
          if (in_array($key, $this->nullable)) {
            $returnData[$key] = ($value === '' || $value === null) ? null : self::test_float($value);
          } else {
            $returnData[$key] = self::test_float($value);
          }
        } elseif (in_array($key, $this->afterSemicolon, true)) {
          // Capture client name and driver name from this group
          if (strpos($value, ';') !== false) {
            switch ($key) {
              case 'billTo':
                if (strpos($value, ';') !== false) {
                  $returnData['ClientName'] = self::decode(self::test_input(self::before_last(';', $value)));
                  $returnData['BillTo'] = self::test_int(self::after_last(';', $value));
                } else {
                  $returnData['BillTo'] = self::test_int($value);
                }
                break;
              case 'dispatchedTo':
                if (strpos($value, ';') !== false) {
                  $returnData['DriverName'] = self::decode(self::test_input(self::before_last(';', $value)));
                  $returnData['driverID'] = $returnData['DispatchedTo'] = self::test_int(self::after_last(';', $value));
                } else {
                  $returnData['driverID'] = $returnData['DispatchedTo'] = self::test_int($value);
                }
                break;
              case 'PendingReceiver':
                if (strpos($value, ';') !== false) {
                  $returnData['receiverName'] = self::decode(self::test_input(self::before_last(';', $value)));
                  $returnData[$key] = self::test_int(self::after_last(';', $value));
                } else {
                  $returnData[$key] = self::test_int($value);
                }
                break;
            }
            $returnData[$key] = self::test_int(self::after_last(';', $value));
          } else {
            $returnData[$key] = self::test_int($value);
          }
        } elseif (!in_array($key, $this->noFilter)) {
          $returnData[$key] = ($value === null) ? null : self::decode(self::test_input($value));
        } else {
          $returnData[$key] = $value;
        }
      }
      return $returnData;
    }
    // http://php.net/manual/en/function.array-search.php#91365
    // Changed or to || for boolean testing
    // Added test for values being an object
    protected function recursive_array_search($needle,$haystack)
    {
      if (!is_array($haystack) && !is_object($haystack)) {
        $this->error = 'Invalid Search Target Line ' . __line__;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      foreach($haystack as $key=>$value) {
        $current_key=$key;
        if (
          $needle===$value ||
          ((is_array($value) || is_object($value)) && self::recursive_array_search($needle,$value) !== false)
        ) {
          return $current_key;
        }
      }
      return false;
    }

    protected function user_array_sort($array, $on, $order=SORT_ASC)
    {
      // http://php.net/manual/en/function.sort.php#99419
      $new_array = array();
      $sortable_array = array();
      if (count($array) > 0) {
        foreach ($array as $k => $v) {
          if (is_array($v)) {
            foreach ($v as $k2 => $v2) {
              if ($k2 == $on) {
                $sortable_array[$k] = $v2;
              }
            }
          } else {
            $sortable_array[$k] = $v;
          }
        }
        switch ($order) {
          case SORT_ASC:
            asort($sortable_array);
            break;
          case SORT_DESC:
            arsort($sortable_array);
            break;
        }
        foreach ($sortable_array as $k => $v) {
          $new_array[$k] = $array[$k];
        }
      }
      return $new_array;
    }
    // Search function for returning part of a string
    protected function after($needle, $inthat)
    {
      if (!is_bool(strpos($inthat, $needle))) {
        return substr($inthat, strpos($inthat,$needle)+strlen($needle));
      }
    }

    protected function after_last($needle, $inthat)
    {
      if (!is_bool(strrpos($inthat, $needle))) {
        return substr($inthat, strrpos($inthat, $needle)+strlen($needle));
      }
    }

    protected function before($needle, $inthat)
    {
      return substr($inthat, 0, strpos($inthat, $needle));
    }

    protected function before_last($needle, $inthat)
    {
      return substr($inthat, 0, strrpos($inthat, $needle));
    }

    protected function between($needle, $that, $inthat)
    {
      return self::before ($that, self::after($needle, $inthat));
    }

    protected function between_last($needle, $that, $inthat)
    {
      return self::after_last($needle, self::before_last($that, $inthat));
    }

    protected function esc_url($url)
    {
      if ('' == $url) {
        return $url;
      }
      $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
      $strip = array('%0d', '%0a', '%0D', '%0A');
      $url = (string) $url;
      $count = 1;
      while ($count) {
        $ulr = str_replace($strip, '', $url, $count);
      }
      $url = str_replace(';//', '://', $url);
      $url = htmlentities($url);
      $url = str_replace('&amp;', '&#038;', $url);
      $url = str_replace("'", '&039;', $url);
      if ($url[0] !== '/') {
        return '';
      } else {
        return $url;
      }
    }

    protected function decode($data)
    {
      if (preg_match('/(&#\d+;)/', $data)) {
        $data = preg_replace_callback("/(&#[0-9]+;)/", function($m)
        {
          return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
        }, $data);
      }
      if (preg_match('/(&\w+;)/', $data)) {
        $data = html_entity_decode($data);
      }
      return $data;
    }

    protected function encodeURIComponent($str)
    {
      $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
      return strtr(rawurlencode($str), $revert);
    }

    protected function test_input($val)
    {
      return filter_var(htmlentities(stripcslashes(trim($val))), FILTER_SANITIZE_STRING);
    }

    protected function test_int($val)
    {
      if ($val === '' || $val === null) return null;
      return (int)round(self::test_float($val), 0, PHP_ROUND_HALF_EVEN);
    }

    protected function test_float($val)
    {
      return (float)filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    protected function test_email($val)
    {
      return filter_var($val, FILTER_SANITIZE_EMAIL);
    }

    protected function test_bool($val)
    {
      return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    protected function test_time($val)
    {
      return preg_match('/(0?\d|1[0-2]):(0\d|[0-5]\d) (AM|PM)/i', $val) ||
        preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/', $val);
    }

    protected function test_invoice_number($val)
    {
      return (preg_match('/(^[\d]{2}EX[\d]+-t[\d]+$)/', $val) || preg_match('/(^[\d]{2}EX[\d]+-[\d]+$)/', $val));
    }

    protected function safe_print_r()
    {
      echo '<pre>', $this->safe_print(func_get_args()), '</pre>';
    }
    // http://php.net/manual/en/function.print-r.php#117746
    // why var_export: https://stackoverflow.com/a/139553
    protected function safe_print() {
      $argc = func_num_args();
      $argv = func_get_args();
      $val = '';
      if ($argc > 0) {
        ob_start();
        call_user_func_array('var_dump', $argv);
        $result = ob_get_contents();
        ob_end_clean();
        $val = strip_tags(preg_replace(['/\]\=/','/>(\r|\n)/','/>\s\s+/'],['] =', '>', '> '],$result));
      }
      return $val;
    }
    //Turn an array into a nice printed list
    protected function arrayToList($array)
    {
      $elements = count($array);
      if ($elements === 0) {
        return false;
      } elseif ($elements === 1) {
        return $array[0];
      } elseif ($elements === 2) {
        return "{$array[0]} and {$array[1]}";
      } else {
        $end = array_pop($array);
        return implode(', ', $array) . ", and {$end}";
      }
    }
    /***
    * http://php.net/manual/de/function.money-format.php#112890
    * Same as php number_format(), but if ends in .0, .00, .000, etc... ,
    * drops the decimals altogether
    * Returns string type, rounded number - same as php number_format()):
    * Examples:
    *  number_format_drop_zero_decimals(54.378, 2) ==> '54.38'
    *  number_format_drop_zero_decimals(54.00, 2) ==> '54'
    ***/
    protected function number_format_drop_zero_decimals($n, $n_decimals)
    {
      /*
      ** round($n, $n_decimals) may equal a whole number in cases when rounding up and preceding precision is 9s
      ** ex: round(16.995, 2) equals 17.00. Test for rounded up integer with ceil($n).
      */
      return ((floor($n) == round($n, $n_decimals)) || (ceil($n) == round($n, $n_decimals))) ?
        number_format($n) : number_format($n, $n_decimals);
    }
    /* Put negative numbers in parentheses */
    protected function negParenth($data)
    {
      return ((float)$data < 0 ? '<span class="paren">(</span>' . ($data * -1) . '<span class="paren">)</span>' : $data);
    }
    //Prevent division by zero when trying to show percentages
    protected function displayPercentage($numerator, $denominator)
    {
      return ((float)$denominator == 0) ?
        '0' : self::number_format_drop_zero_decimals((float)$numerator/(float)$denominator * 100, 2);
    }

    protected function clientListBy($val)
    {
      return ($this->ListBy === 0) ?
        $this->members[$val]->getProperty('ShippingAddress1') : $this->members[$val]->getProperty('Department');
    }

    protected function createClient($data)
    {
      try {
        $obj = new Client($this->options, $data);
      } catch(\Exception $e) {
        $this->error = $e->getMessage();
        return false;
      }
      return $obj;
    }

    protected function createTicket($data)
    {
      try {
        $obj = new Ticket($this->options, $data);
      } catch(\Exception $e) {
        $this->error = $e->getMessage();
        return false;
      }
      return $obj;
    }

    protected function createTicketChart($data)
    {
      try {
        $obj = new TicketChart($this->options, $data);
      } catch(\Exception $e) {
        $this->error = $e->getMessage();
        return false;
      }
      return $obj;
    }

    protected function createInvoice($data)
    {
      try {
        $obj = new Invoice($this->options, $data);
      } catch(\Exception $e) {
        $this->error = $e->getMessage();
        return false;
      }
      return $obj;
    }

    protected function createInvoiceChart($data)
    {
      try {
        $obj = new InvoiceChart($this->options, $data);
      } catch(\Exception $e) {
        $this->error = $e->getMessage();
        return false;
      }
      return $obj;
    }

    protected function createQuery($data)
    {
      try {
        $obj = new Query($this->options, $data);
      } catch (\Exception $e) {
        $this->error = $e->getMessage();
        return false;
      }
      return $obj;
    }

    protected function callQuery($query)
    {
      try {
        $result = $query->buildURI()->call();
      } catch (\Exception $e) {
        $this->error = $e->getMessage();
        return false;
      }
      $returnData = json_decode($result, true);
      return (strtoupper($query->getProperty('method')) === 'GET') ? $returnData['records'] : $returnData;
    }

    protected function createLimitedMonthInput($params)
    {
      $clientIDs = $params['clientIDs'] ?? [];
      $inputID = $params['inputID'] ?? '';
      $disabled = $params['disabled'] ?? false;
      $type = $params['type'] ?? 'month';
      $table = $params['table'] ?? 'invoices';
      $required = $params['required'] ?? false;
      $form = $params['form'] ?? '';
      $sql = $min = $max = $returnData = '';
      $dates = $data = $repeatFilter = $nonRepeatFilter = $repeatClients = $nonRepeatClients = [];
      $disableInput = ($disabled === false) ? '' : 'disabled';
      $requireInput = ($required === false) ? '' : 'required';
      $inputName = (strpos($inputID,'invoice') === false) ? $inputID : substr($inputID, 7);
      $queryData = [];
      $queryData['endPoint'] = $table;
      $queryData['method'] = 'GET';
      // Make sure that $clientIDs is an array
      if (!is_array($clientIDs)) {
        $temp = $clientIDs;
        $clientIDs = [ $temp ];
      }
      // Return an error if no clients are listed
      if (!isset($clientIDs[0])) {
        $returnData = 'No Clients Passed';
        return $returnData;
      }
      for ($i = 0; $i < count($clientIDs); $i++) {
        if (strpos($clientIDs[$i], 't') === false) {
          $repeatClients[] = $clientIDs[$i];
        } else {
          $nonRepeatClients[] = self::test_int($clientIDs[$i]);
        }
      }
      // Define variables based on the input type
      if ($type === 'month') {
        $format = 'Y-m';
        $when = 'DateIssued';
        $queryData['queryParams']['include'] = ['DateIssued'];
        if (!empty($repeatClients)) {
          $repeatFilter = [
            ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1],
            ['Resource'=>'ClientID', 'Filter'=>'in', 'Value'=>implode(',', $repeatClients)]
          ];
        }
        if (!empty($nonRepeatClients)) {
          $nonRepeatFilter = [
            ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0],
            ['Resource'=>'ClientID', 'Filter'=>'in', 'Value'=>implode(',', $nonRepeatClients)]
          ];
        }
        $placeholder = 'JAN 2000';
      } elseif ($type === 'date') {
        $format = 'Y-m-d';
        $when = 'ReceivedDate';
        $queryData['queryParams']['include'] = ['ReceivedDate'];
        if (!empty($repeatClients)) {
          $repeatFilter = [
            ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1],
            ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $repeatClients)]
          ];
        }
        if (!empty($nonRepeatClients)) {
          $nonRepeatFilter = [
            ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0],
            ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $nonRepeatClients)]
          ];
        }
        $placeholder = '';
      }
      if (!empty($repeatFilter) && !empty($nonRepeatFilter)) {
        $queryData['queryParams']['filter'] = [ $repeatFilter, $nonRepeatFilter ];
      } elseif (empty($repeatFilter) && !empty($nonRepeatFilter)) {
        $queryData['queryParams']['filter'] = $nonRepeatFilter;
      } elseif (!empty($repeatFilter) && empty($nonRepeatFilter)) {
        $queryData['queryParams']['filter'] = $repeatFilter;
      }
      if (!$query = self::createQuery($queryData)) {
        return $this->error;
      }
      $data = self::callQuery($query);
      if ($data === false) {
        return $this->error;
      }
      // Process the data array to find the min and max values for the month input
      if (!empty($data)) {
        $testData = [];
        for ($i = 0; $i < count($data); $i++) {
          $testData[] = date($format, strtotime($data[$i][$when]));
        }
        $max = max($testData);
        $min = min($testData);
        $lcfirst = 'lcfirst';
        $ucfirst = 'ucfirst';
        //Define the input
        $returnData = "
        <input type=\"{$type}\" min=\"{$min}\" max=\"{$max}\" name=\"{$lcfirst($inputName)}\" class=\"{$inputID}{$ucfirst($type)}\" placeholder=\"{$placeholder}\" {$disableInput} {$requireInput} form=\"{$form}\" />";
      } else {
        $returnData = 'No Data On File';
      }
      return $returnData;
    }

    protected function createInvoiceNumberSelect($search)
    {
      $queryData = [];
      $queryData['method'] = 'GET';
      $queryData['endPoint'] = 'invoices';
      $queryData['queryParams']['include'] = ['InvoiceNumber', 'Closed'];
      $queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID','Filter'=>'eq','Value'=>$search] ];
      $queryData['queryParams']['filter'][] = (self::test_bool($this->RepeatClient) === false) ?
        ['Resource'=>'InvoiceNumber', 'Filter'=>'cs', 'Value'=>'t'] :
        ['Resource'=>'InvoiceNumber', 'Filter'=>'ncs', 'Value'=>'t'];
      $queryData['queryParams']['order'] = ['InvoiceNumber,desc'];
      if (!$query = self::createQuery($queryData)) {
        return $this->error;
      }
      $invoiceList = self::callQuery($query);
      if ($invoiceList === false) {
        return $this->error;
      }
      if (empty($invoiceList)) {
        return 'No Invoice On File';
      }
      $returnData = '<select name="invoiceNumber" id="invoiceNumber" form="singleInvoiceQuery" disabled>';
      foreach ($invoiceList as $invoice) {
        $flag = (self::test_bool($invoice['Closed']) === false) ? '*' : '';
        $returnData .= "<option value=\"{$invoice['InvoiceNumber']}\">{$invoice['InvoiceNumber']}{$flag}</option>";
      }
      $returnData .= '</select>';
      return $returnData;
    }

    protected function getCredit()
    {
      $repeatClient = (int)$_SESSION['members'][$_SESSION['ClientID']]['RepeatClient'];
      $data = [];
      $data['method'] = 'GET';
      $data['endPoint'] = 'invoices';
      $data['queryParams']['include'] = ['Balance', 'DatePaid'];
      $data['queryParams']['filter'] = [
        ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$_SESSION['ClientID']],
        ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$repeatClient],
        ['Resource'=>'Closed', 'Filter'=>'eq', 'Value'=>1]
      ];
      if (!$query = self::createQuery($data)) {
        return $this->error;
      }
      $temp = self::callQuery($query);
      if ($temp === false) {
        return $this->error;
      }

      $credit = ['Balance'=>'0', 'DatePaid'=>'0'];

      foreach ($temp as $test) {
        if ($test['DatePaid'] > $credit['DatePaid']) {
          $credit = $test;
        }
      }
      $credit['Balance'] *= -1;
      $data['endPoint'] = 'tickets';
      $data['queryParams']['include'] = ['TicketPrice'];
      $data['queryParams']['filter'] = [
        ['Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>$_SESSION['ClientID']],
        ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$repeatClient],
        ['Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>'-'],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>9] ];
      if (!$query = self::createQuery($data)) {
        return $this->error;
      }
      $temp2 = self::callQuery($query);
      if ($temp2 === false) {
        return $this->error;
      }
      foreach ($temp2 as $test) {
        $credit['Balance'] += ($test['TicketPrice'] * -1);
      }
      return self::negParenth(self::number_format_drop_zero_decimals($credit['Balance'], 2));
    }

    protected function listOrgMembers($identifier)
    {
      $returnData = '';
      $x = 0;
      foreach ($_SESSION['members'] as $key => $value) {
        $display = ($this->ListBy === 1) ? $value['Department'] : $value['ShippingAddress1'];
        $returnData .= "<span class=\"floatLeft highlight\"><input type=\"checkbox\" name=\"clientID[]\" value=\"{$key}\" class=\"orgMember\" id=\"orgMember{$x}{$identifier}\" data-value=\"{$key}\" /><label for=\"orgMember{$x}{$identifier}\">{$display}; {$key}</label>  </span>";
        $x++;
      }
      return $returnData;
    }

    private function customize()
    {
      $spinner = '
      <div class="loader">
        <div class="face">
          <div class="circle"></div>
        </div>
        <div class="face">
          <div class="circle"></div>
        </div>
      </div>';
      if (is_numeric($this->ulevel)) {
        $type = ($this->ulevel > 0) ? 'client' : 'org';
        $type .= ($this->ClientID === 0) ? '0' : '';
      } else {
        $type = ($this->ulevel === 'driver') ? 'driver' : 'dispatcher';
      }
      $withPage = $pages = $noPage = [];
      if (
        isset($this->options['extend']['all']) &&
        is_array($this->options['extend']['all']) &&
        !empty($this->options['extend']['all'])
      ) {
        for ($i = 0; $i < count($this->options['extend']['all']); $i++) {
          if (isset($this->options['extend']['all'][$i][0]) && $this->options['extend']['all'][$i][0] !== '') {
            if (!isset($this->options['extend']['all'][$i][1]) || $this->options['extend']['all'][$i][1] === '') {
              $noPage[] = $this->options['extend']['all'][$i][0];
            } else {
              $withPage[] = $this->options['extend']['all'][$i][0];
              $pages[] = $this->options['extend']['all'][$i][1];
            }
          }
          if (isset($this->options['extend']['all'][$i][2]) && $this->options['extend']['all'][$i][2] !== '') {
            $this->customScripts .= "
            <script src=\"{$this->options['extend']['all'][$i][2]}\"";
          }
          if (isset($this->options['extend']['all'][$i][3])) {
            for ($j = 3; $j < count($this->options['extend']['all'][$i]); $j++) {
              if (isset($this->options['extend']['all'][$i][$j]) && $this->options['extend']['all'][$i][$j] !== '') {
                $this->customScripts .= " {$this->options['extend']['all'][$i][$j]}";
              }
            }
          }
          if (isset($this->options['extend']['all'][$i][2]) && $this->options['extend']['all'][$i][2] !== '')
            $this->customScripts .= '></script>';
        }
      }
      // if the current user type has been extended add the menu items without pages to the end of that array.
      // Otherwise add them to the customMenuItems property.
      $search_index = false;
      $moreWithPage = $morePages = $moreNoPage = [];
      switch($type) {
        case 'org':
        case 'dispatcher':
        case 'client0':
        case 'org0':
          if (
            isset($this->options['extend'][$type]) &&
            is_array($this->options['extend'][$type]) &&
            !empty($this->options['extend'][$type])
          ) {
            for ($i = 0; $i < count($this->options['extend'][$type]); $i++) {
              if (
                isset($this->options['extend'][$type][$i][0]) &&
                $this->options['extend'][$type][$i][0] !== ''
              ) {
                if (
                  !isset($this->options['extend'][$type][$i][1]) ||
                  $this->options['extend'][$type][$i][1] === ''
                ) {
                  $moreNoPage[] = $this->options['extend'][$type][$i][0];
                } else {
                  if (
                    substr($type,0,3) === 'org' &&
                    $_SESSION['org_id']['RequestTickets'] < 2 &&
                    $this->options['extend'][$type][$i][1] == 'ticketForm'
                  ) continue;
                  $moreWithPage[] = $this->options['extend'][$type][$i][0];
                  $morePages[] = $this->options['extend'][$type][$i][1];
                }
              }
              if (
                isset($this->options['extend'][$type][$i][2]) &&
                $this->options['extend'][$type][$i][2] !== ''
              ) {
                $this->customScripts .= "
            <script src=\"{$this->options['extend'][$type][$i][2]}\"";
              }
              if (isset($this->options['extend'][$type][$i][3])) {
                for ($j = 3; $j < count($this->options['extend'][$type][$i]); $j++) {
                  if (isset($this->options['extend'][$type][$i][$j]) && $this->options['extend'][$type][$i][$j] !== '') {
                    $this->customScripts .= " {$this->options['extend'][$type][$i][$j]}";
                  }
                }
              }
              if (
                isset($this->options['extend'][$type][$i][2]) &&
                $this->options['extend'][$type][$i][2] !== ''
              ) {
                  $this->customScripts .= '></script>';
                }
            }
          }
          break;
        case 'client':
          $search_index = $this->ulevel;
        case 'driver':
          if (!$search_index) $search_index = $this->CanDispatch + 1;
          if (
            isset($this->options['extend'][$type][0]) &&
            is_array($this->options['extend'][$type][0]) &&
            !empty($this->options['extend'][$type][0])
          ) {
            for ($i = 0; $i < count($this->options['extend'][$type][0]); $i++) {
              if (
                isset($this->options['extend'][$type][0][$i][0]) &&
                $this->options['extend'][$type][0][$i][0] !== ''
              ) {
                if (
                  !isset($this->options['extend'][$type][0][$i][1]) ||
                  $this->options['extend'][$type][0][$i][1] === ''
                ) {
                  $moreNoPage[] = $this->options['extend'][$type][0][$i][0];
                } else {
                  $moreWithPage[] = $this->options['extend'][$type][0][$i][0];
                  $morePages[] = $this->options['extend'][$type][0][$i][1];
                }
              }
              if (
                isset($this->options['extend'][$type][0][$i][2]) &&
                $this->options['extend'][$type][0][$i][2] !== ''
              ) {
                $this->customScripts .= "
                <script src=\"{$this->options['extend'][$type][0][$i][2]}\"";
              }
              if (isset($this->options['extend'][$type][0][$i][3])) {
                for ($j = 3; $j < count($this->options['extend'][$type][0][$i]); $j++) {
                  if (isset($this->options['extend'][$type][0][$i][$j]) && $this->options['extend'][$type][0][$i][$j] !== '') {
                    $this->customScripts .= " {$this->options['extend'][$type][0][$i][$j]}";
                  }
                }
              }
              if (
                isset($this->options['extend'][$type][0][$i][2]) &&
                $this->options['extend'][$type][0][$i][2] !== ''
              ) {
                  $this->customScripts .= '></script>';
                }
            }
          }
          if (
            isset($this->options['extend'][$type][$search_index]) &&
            is_array($this->options['extend'][$type][$search_index]) &&
            !empty($this->options['extend'][$type][$search_index])
          ) {
            for ($i = 0; $i < count($this->options['extend'][$type][$search_index]); $i++) {
              if (
                isset($this->options['extend'][$type][$search_index][$i][0]) &&
                $this->options['extend'][$type][$search_index][$i][0] !== ''
              ) {
                if (
                  !isset($this->options['extend'][$type][$search_index][$i][1]) ||
                  $this->options['extend'][$type][$search_index][$i][1] === ''
                ) {
                  $moreNoPage[] = $this->options['extend'][$type][$search_index][$i][0];
                } else {
                  $moreWithPage[] = $this->options['extend'][$type][$search_index][$i][0];
                  $morePages[] = $this->options['extend'][$type][$search_index][$i][1];
                }
              }
              if (
                isset($this->options['extend'][$type][$search_index][$i][2]) &&
                $this->options['extend'][$type][$search_index][$i][2] !== ''
              ) {
                $this->customScripts .= "
                <script src=\"{$this->options['extend'][$type][$search_index][$i][2]}\"";
              }
              if (isset($this->options['extend'][$type][$search_index][$i][3])) {
                for ($j = 3; $j < count($this->options['extend'][$type][$search_index][$i]); $j++) {
                  if (
                    isset($this->options['extend'][$type][$search_index][$i][$j]) &&
                    $this->options['extend'][$type][$search_index][$i][$j] !== ''
                  ) {
                    $this->customScripts .= " {$this->options['extend'][$type][$search_index][$i][$j]}";
                  }
                }
              }
              if (
                isset($this->options['extend'][$type][$search_index][$i][2]) &&
                $this->options['extend'][$type][$search_index][$i][2] !== ''
              ) {
                $this->customScripts .= '></script>';
              }
            }
          }
          break;
      }
      $totalWithPage = array_merge($moreWithPage, $withPage);
      $totalPages = array_merge($morePages, $pages);
      $totalNoPage = array_merge($moreNoPage, $noPage);
      for ($i = 0; $i < count($totalWithPage); $i++) {
        $id = strtolower(preg_replace('/\s+/', '_', strip_tags($totalWithPage[$i])));
        $alert = '';
        if ($id === 'change_password' || $id === 'change_admin_password') {
          switch($this->ulevel) {
            case 0:
              $alert = ($this->pwWarning === 4) ? '<span class="PWalert">!</span>' : '<span class="PWalert"></span>';
              break;
            case 1:
              if ($id === 'change_password') {
                $alert = ($this->pwWarning === 1 || $this->pwWarning === 3) ?
                  '<span class="PWalert">!</span>' : '<span class="PWalert"></span>';
              } elseif ($id === 'change_admin_password') {
                $alert = ($this->pwWarning === 2 || $this->pwWarning === 3) ?
                  '<span class="PWalert">!</span>' : '<span class="PWalert"></span>';
              }
              break;
          }
        } elseif ($id === 'on_call' || $id === 'transfers' || $id === 'dispatch') {
          $alert = ($id === 'on_call') ? '<span class="ticketCount"></span>' : "<span class=\"{$id}Count\"></span>";
        }
        $this->customMenuItems .= ($i === 0) ?
          "<li class=\"menu__list__active\"><a data-id=\"{$id}\" class=\"nav\">{$totalWithPage[$i]}{$alert}</a></li>
        " : "<li><a data-id=\"{$id}\" class=\"nav\">{$totalWithPage[$i]}{$alert}</a></li>
        ";
        $this->customPages .= "<div id=\"{$id}\" data-function=\"{$totalPages[$i]}\" class=\"page\">{$spinner}</div>
        ";
      }
      for ($i = 0; $i < count($totalNoPage); $i++) {
        $this->customMenuItems .= "<li>{$totalNoPage[$i]}</li>
        ";
      }
    }

    public function injectCSS()
    {
      $returnData = '';
      if (is_numeric($this->ulevel)) {
        if ($this->ulevel > 0) {
          if (
            !isset($this->options['extend']['css']['client']) ||
            !is_array($this->options['extend']['css']['client'])
          ) {
              return false;
            }
          for ($i = 0; $i < count($this->options['extend']['css']['client']); $i++) {
            $returnData .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->options['extend']['css']['client'][$i]}\">
            ";
          }
        } else {
          if (
            !isset($this->options['extend']['css']['org']) ||
            !is_array($this->options['extend']['css']['org'])
          ) {
            return false;
          }
          for ($i = 0; $i < count($this->options['extend']['css']['org']); $i++) {
            $returnData .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->options['extend']['css']['org'][$i]}\">
            ";
          }
        }
      } elseif ($this->ulevel === 'driver') {
        if (
          !isset($this->options['extend']['css']['driver']) ||
          !is_array($this->options['extend']['css']['driver'])
        ) {
          return false;
        }
        for ($i = 0; $i < count($this->options['extend']['css']['driver']); $i++) {
          $returnData .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->options['extend']['css']['driver'][$i]}\">
          ";
        }
      } elseif ($this->ulevel === 'dispatch') {
        if (
          !isset($this->options['extend']['css']['dispatch']) ||
          !is_array($this->options['extend']['css']['dispatch'])
        ) {
          return false;
        }
        for ($i = 0; $i < count($this->options['extend']['css']['dispatch']); $i++) {
          $returnData .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$this->options['extend']['css']['dispatch'][$i]}\">
          ";
        }
      }
      return $returnData;
    }

    public function createNavMenu()
    {
      self::customize();
      if (is_numeric($_SESSION['ulevel'])) {
        $userType = 'client';
        $displayClientName = $_SESSION['ClientName'];
        if ($this->ulevel === 0) {
          $userType = 'org';
          $displayClientName .= '<br>Organizational';
        } elseif ($this->ulevel > 0) {
          $displayClientName .= "<br>{$_SESSION['Department']}";
          $displayClientName .= ($this->ulevel === 1) ? ' Admin' : '';
        }
      } elseif ($this->ulevel === 'driver') {
        $userType = 'driver';
        $displayClientName = "{$_SESSION['FirstName']} {$_SESSION['LastName']}";
        if ($_SESSION['CanDispatch'] > 0) {
          $displayClientName .= '<br>Driver / Dispatch';
        } else {
          $displayClientName .= '<br>Driver';
        }
      } elseif ($this->ulevel === 'dispatch') {
        $userType = 'dispatch';
        $displayClientName = "{$_SESSION['FirstName']} {$_SESSION['LastName']}";
        $displayClientName .= '<br>Dispatch';
      }
      $mobileMarker = (isset($_SESSION['mobile']) && $_SESSION['mobile'] === true) ? 1 : 0;
      return "
        <div class=\"menu__header\">
          <p id=\"userType\" class=\"$userType\">{$displayClientName}</p>
          <div id=\"logoutRefresh\">
            <button type=\"button\" class=\"refresh\">Refresh</button>
            <form id=\"logoutLink\" action=\"logout\" method=\"post\">
              <input type=\"hidden\" name=\"formKey\" id=\"formKey\" value=\"" . SecureSessionHandler::outputKey() . "\" />
              <input type=\"hidden\" name=\"mobile\" value=\"{$mobileMarker}\" />
              <button type=\"submit\" form=\"logoutLink\">Log Out</button>
            </form>
          </div>
        </div>
        <ul class=\"menu__list\">
          {$this->customMenuItems}
        </ul>";
    }

    public function createAppLayout()
    {
      return "
        <div class=\"swipe-wrap\">
          {$this->customPages}
        </div>
      <script>
        var myInfo = [\"{$_SESSION['config']['ClientName']}\",\"{$_SESSION['config']['EmailAddress']}\",\"{$_SESSION['config']['Telephone']}\"]
      </script>
      {$this->customScripts}";
    }

    protected function countryFromAbbr($abbr)
    {
      // Credits will have a value of '-' for pCountry and dCountry
      if ($abbr === '-') return $abbr;
      //"XZ" sounds unlikely as a country abbreviation so it will stand for "Not On File"
      if ($abbr == null) return '';
      if (strlen($abbr) === 2) {
        switch ($abbr) {
          case 'AL': return 'Albania';
          case 'DZ': return 'Algeria';
          case 'AD': return 'Andorra';
          case 'AO': return 'Angola';
          case 'AR': return 'Argentina';
          case 'AM': return 'Armenia';
          case 'AW': return 'Aruba';
          case 'AU': return 'Australia';
          case 'AT': return 'Austria';
          case 'AZ': return 'Azerbaijan';
          case 'PT': return 'Azores';
          case 'BS': return 'Bahamas';
          case 'BH': return 'Bahrain';
          case 'BD': return 'Bangladesh';
          case 'BB': return 'Barbados';
          case 'BY': return 'Belarus';
          case 'BE': return 'Belgium';
          case 'BZ': return 'Belize';
          case 'BJ': return 'Benin';
          case 'BM': return 'Bermuda';
          case 'BT': return 'Bhutan';
          case 'BO': return 'Bolivia';
          case 'BA': return 'Bosna-Herzegovina';
          case 'BW': return 'Botswana';
          case 'BR': return 'Brazil';
          case 'BN': return 'Brunei Darussalam';
          case 'BG': return 'Bulgaria';
          case 'BF': return 'Burkina FASO';
          case 'BI': return 'Burundi';
          case 'KH': return 'Cambodia';
          case 'CM': return 'Cameroon';
          case 'CA': return 'Canada';
          case 'CV': return 'Cape Verde';
          case 'KY': return 'Cayman Islands';
          case 'CF': return 'Cntl African Republic';
          case 'TS': return 'Chad';
          case 'CL': return 'Chile';
          case 'CN': return 'China';
          case 'CO': return 'Columbia';
          case 'ZR': return 'Democratic Republic of Congo';
          case 'CG': return 'Republic of the Congo (Brazaville)';
          case 'CR': return 'Costa Rica';
          case 'CI': return 'Cote d\'Ivoire (Ivory Coast)';
          case 'HR': return 'Croatia';
          case 'CY': return 'Cyprus';
          case 'CZ': return 'Czech Republic';
          case 'DK': return 'Denmark';
          case 'DJ': return 'Djibouti';
          case 'DO': return 'Dominican Republic';
          case 'EC': return 'Ecuador';
          case 'EG': return 'Egypt';
          case 'SV': return 'El Salvador';
          case 'GQ': return 'Equatorial Guinea';
          case 'ER': return 'Eritrea';
          case 'EE': return 'Estonia';
          case 'ET': return 'Ethiopia';
          case 'DK': return 'Faroe Islands';
          case 'FJ': return 'Fiji';
          case 'FI': return 'Finland';
          case 'FR': return 'France';
          case 'GF': return 'French Guiana';
          case 'PF': return 'French Polynesia (Tahitti)';
          case 'GA': return 'Gabon';
          case 'GE': return 'Georgia, Republic of';
          case 'DE': return 'Germany';
          case 'GH': return 'Ghana';
          case 'GB': return 'Great Britain &amp; Northern Ireland';
          case 'GR': return 'Greece';
          case 'GD': return 'Grenada';
          case 'GP': return 'Guadeloupe';
          case 'GT': return 'Guatemala';
          case 'GN': return 'Guinea';
          case 'GW': return 'Guinea-Bissau';
          case 'GY': return 'Guyana';
          case 'HT': return 'Haiti';
          case 'HN': return 'Honduras';
          case 'HK': return 'Hong Kong';
          case 'HU': return 'Hungary';
          case 'IS': return 'Iceland';
          case 'IN': return 'India';
          case 'ID': return 'Indonesia';
          case 'IR': return 'Iran';
          case 'IQ': return 'Iraq';
          case 'IE': return 'Ireland (Eire)';
          case 'IL': return 'Israel';
          case 'IT': return 'Italy';
          case 'JM': return 'Jamaica';
          case 'JP': return 'Japan';
          case 'JO': return 'Jordan';
          case 'KG': return 'Kazakhstan';
          case 'KE': return 'Kenya';
          case 'KR': return 'South Korea, Republic of';
          case 'KW': return 'Kuwait';
          case 'KG': return 'Kyrgyzstan';
          case 'LA': return 'Laos';
          case 'LV': return 'Latvia';
          case 'LS': return 'Lesotho';
          case 'LR': return 'Liberia';
          case 'LI': return 'Liechtenstein';
          case 'LT': return 'Lithuania';
          case 'LU': return 'Luxembourg';
          case 'MO': return 'Macao';
          case 'MK': return 'Macedonia, Republic of';
          case 'MG': return 'Madagascar';
          case 'PT': return 'Madeira Islands';
          case 'MW': return 'Malawi';
          case 'MY': return 'Malaysia';
          case 'MV': return 'Maldives';
          case 'ML': return 'Mali';
          case 'MT': return 'Malta';
          case 'MQ': return 'Martinique';
          case 'MR': return 'Mauritania';
          case 'MU': return 'Mauritius';
          case 'MX': return 'Mexico';
          case 'MD': return 'Moldova';
          case 'MN': return 'Mongolia';
          case 'MA': return 'Morocco';
          case 'MZ': return 'Mozambique';
          case 'NA': return 'Namibia';
          case 'NR': return 'Nauru';
          case 'NP': return 'Nepal';
          case 'NL': return 'Netherlands (Holland)';
          case 'AN': return 'Netherlands Antilles';
          case 'NC': return 'New Caledonia';
          case 'NZ': return 'New Zealand';
          case 'NI': return 'Nicaragua';
          case 'NE': return 'Niger';
          case 'NG': return 'Nigeria';
          case 'NO': return 'Norway';
          case 'OM': return 'Oman';
          case 'PK': return 'Pakistan';
          case 'PA': return 'Panama';
          case 'PG': return 'Papua New Guinea';
          case 'PY': return 'Paraguay';
          case 'PE': return 'Peru';
          case 'PH': return 'Philippines';
          case 'PL': return 'Poland';
          case 'PT': return 'Portugal';
          case 'QA': return 'Qatar';
          case 'RO': return 'Romania';
          case 'RU': return 'Russia (Russia Federation)';
          case 'RW': return 'Rwanda';
          case 'KN'; return 'St. Christopher (St. Kitts) &amp; Nevis';
          case 'LC': return 'St. Lucia';
          case 'VC': return 'St. Vincent &amp; the Grenadines';
          case 'SA': return 'Saudi Arabia';
          case 'SN': return 'Senegal';
          case 'YU': return 'Serbia Montenegro (Yugoslavia)';
          case 'SC': return 'Seychelles';
          case 'SL': return 'Sierra Leone';
          case 'SG': return 'Singapore';
          case 'SK': return 'Slovak Republic (Slovakia)';
          case 'SI': return 'Slovenia';
          case 'SB': return 'Solomon Islands';
          case 'SO': return 'Somalia';
          case 'ZA': return 'South Africa';
          case 'ES': return 'Spain';
          case 'LK': return 'Sri Lanka';
          case 'SD': return 'Sudan';
          case 'SZ': return 'Swaziland';
          case 'SE': return 'Sweden';
          case 'CH': return 'Switzerland';
          case 'SY': return 'Syrian Arab Republic';
          case 'TW': return 'Taiwan';
          case 'TJ': return 'Tajikistan';
          case 'TZ': return 'Tanzania';
          case 'TH': return 'Thailand';
          case 'TG': return 'Togo';
          case 'TT': return 'Trinidad &amp; Tobago';
          case 'TN': return 'Tunisia';
          case 'TR': return 'Turkey';
          case 'TM': return 'Turkmenistan';
          case 'UG': return 'Uganda';
          case 'AE': return 'United Arab Emirates';
          case 'UA': return 'Ukraine';
          case 'US': return 'United States of America';
          case 'UY': return 'Uruguay';
          case 'VU': return 'Vanuatu';
          case 'VE': return 'Venezuela';
          case 'VN': return 'Vietnam';
          case 'WS': return 'Western Samoa';
          case 'YE': return 'Yemen';
          default: return 'Not On File';
        }
      } else {
        switch ($abbr) {
          case 'Albania': return 'AL';
          case 'Algeria': return 'DZ';
          case 'Andorra': return 'AD';
          case 'Angola': return 'AO';
          case 'Argentina': return 'AR';
          case 'Armenia': return 'AM';
          case 'Aruba': return 'AW';
          case 'Australia': return 'AU';
          case 'Austria': return 'AT';
          case 'Azerbaijan': return 'AZ';
          case 'Azores': return 'PT';
          case 'Bahamas': return 'BS';
          case 'Bahrain': return 'BH';
          case 'Bangladesh': return 'BD';
          case 'Barbados': return 'BB';
          case 'Belarus': return 'BY';
          case 'Belgium': return 'BE';
          case 'Belize': return 'BZ';
          case 'Benin': return 'BJ';
          case 'Bermuda': return 'BM';
          case 'Bhutan': return 'BT';
          case 'Bolivia': return 'BO';
          case 'Bosna-Herzegovina': return 'BA';
          case 'Botswana': return 'BW';
          case 'Brazil': return 'BR';
          case 'Brunei Darussalam': return 'BN';
          case 'Bulgaria': return 'BG';
          case 'Burkina FASO': return 'BF';
          case 'Burundi': return 'BI';
          case 'Cambodia': return 'KH';
          case 'Cameroon': return 'CM';
          case 'Canada': return 'CA';
          case 'Cape Verde': return 'CV';
          case 'Cayman Islands': return 'KY';
          case 'Cntl African Republic': return 'CF';
          case 'Chad': return 'TS';
          case 'Chile': return 'CL';
          case 'China': return 'CN';
          case 'Columbia': return 'CO';
          case 'Democratic Republic of Congo': return 'ZR';
          case 'Republic of the Congo (Brazaville)': return 'CG';
          case 'Costa Rica': return 'CR';
          case 'Cote d\'Ivoire (Ivory Coast)': return 'CI';
          case 'Croatia': return 'HR';
          case 'Cyprus': return 'CY';
          case 'Czech Republic': return 'CZ';
          case 'Denmark': return 'DK';
          case 'Djibouti': return 'DJ';
          case 'Dominican Republic': return 'DO';
          case 'Ecuador': return 'EC';
          case 'Egypt': return 'EG';
          case 'El Salvador': return 'SV';
          case 'Equatorial Guinea': return 'GQ';
          case 'Eritrea': return 'ER';
          case 'Estonia': return 'EE';
          case 'Ethiopia': return 'ET';
          case 'Faroe Islands': return 'DK';
          case 'Fiji': return 'FJ';
          case 'Finland': return 'FI';
          case 'France': return 'FR';
          case 'French Guiana': return 'GF';
          case 'French Polynesia (Tahitti)': return 'PF';
          case 'Gabon': return 'GA';
          case 'Georgia, Republic of': return 'GE';
          case 'Germany': return 'DE';
          case 'Ghana': return 'GH';
          case 'Great Britain &amp; Northern Ireland': return 'GB';
          case 'Greece': return 'GR';
          case 'Grenada': return 'GD';
          case 'Guadeloupe': return 'GP';
          case 'Guatemala': return 'GT';
          case 'Guinea': return 'GN';
          case 'Guinea-Bissau': return 'GW';
          case 'Guyana': return 'GY';
          case 'Haiti': return 'HT';
          case 'Honduras': return 'HN';
          case 'Hong Kong': return 'HK';
          case 'Hungary': return 'HU';
          case 'Iceland': return 'IS';
          case 'India': return 'IN';
          case 'Indonesia': return 'ID';
          case 'Iran': return 'IR';
          case 'Iraq': return 'IQ';
          case 'Ireland (Eire)': return 'IE';
          case 'Israel': return 'IL';
          case 'Italy': return 'IT';
          case 'Jamaica': return 'JM';
          case 'Japan': return 'JP';
          case 'Jordan': return 'JO';
          case 'Kazakhstan': return 'KG';
          case 'Kenya': return 'KE';
          case 'South Korea, Republic of': return 'KR';
          case 'Kuwait': return 'KW';
          case 'Kyrgyzstan': return 'KG';
          case 'Laos': return 'LA';
          case 'Latvia': return 'LV';
          case 'Lesotho': return 'LS';
          case 'Liberia': return 'LR';
          case 'Liechtenstein': return 'LI';
          case 'Lithuania': return 'LT';
          case 'Luxembourg': return 'LU';
          case 'Macao': return 'MO';
          case 'Macedonia, Republic of': return 'MK';
          case 'Madagascar': return 'MG';
          case 'Madeira Islands': return 'PT';
          case 'Malawi': return 'MW';
          case 'Malaysia': return 'MY';
          case 'Maldives': return 'MV';
          case 'Mali': return 'ML';
          case 'Malta': return 'MT';
          case 'Martinique': return 'MQ';
          case 'Mauritania': return 'MR';
          case 'Mauritius': return 'MU';
          case 'Mexico': return 'MX';
          case 'Moldova': return 'MD';
          case 'Mongolia': return 'MN';
          case 'Morocco': return 'MA';
          case 'Mozambique': return 'MZ';
          case 'Namibia': return 'NA';
          case 'Nauru': return 'NR';
          case 'Nepal': return 'NP';
          case 'Netherlands (Holland)': return 'NL';
          case 'Netherlands Antilles': return 'AN';
          case 'New Caledonia': return 'NC';
          case 'New Zealand': return 'NZ';
          case 'Nicaragua': return 'NI';
          case 'Niger': return 'NE';
          case 'Nigeria': return 'NG';
          case 'Norway': return 'NO';
          case 'Oman': return 'OM';
          case 'Pakistan': return 'PK';
          case 'Panama': return 'PA';
          case 'Papua New Guinea': return 'PG';
          case 'Paraguay': return 'PY';
          case 'Peru': return 'PE';
          case 'Philippines': return 'PH';
          case 'Poland': return 'PL';
          case 'Portugal': return 'PT';
          case 'Qatar': return 'QA';
          case 'Romania': return 'RO';
          case 'Russia (Russia Federation)': return 'RU';
          case 'Rwanda': return 'RW';
          case 'St. Christopher (St. Kitts) &amp; Nevis'; return 'KN';
          case 'St. Lucia': return 'LC';
          case 'St. Vincent &amp; the Grenadines': return 'VC';
          case 'Saudi Arabia': return 'SA';
          case 'Senegal': return 'SN';
          case 'Serbia Montenegro (Yugoslavia)': return 'YU';
          case 'Seychelles': return 'SC';
          case 'Sierra Leone': return 'SL';
          case 'Singapore': return 'SG';
          case 'Slovak Republic (Slovakia)': return 'SK';
          case 'Slovenia': return 'SI';
          case 'Solomon Islands': return 'SB';
          case 'Somalia': return 'SO';
          case 'South Africa': return 'ZA';
          case 'Spain': return 'ES';
          case 'Sri Lanka': return 'LK';
          case 'Sudan': return 'SD';
          case 'Swaziland': return 'SZ';
          case 'Sweden': return 'SE';
          case 'Switzerland': return 'CH';
          case 'Syrian Arab Republic': return 'SY';
          case 'Taiwan': return 'TW';
          case 'Tajikistan': return 'TJ';
          case 'Tanzania': return 'TZ';
          case 'Thailand': return 'TH';
          case 'Togo': return 'TG';
          case 'Trinidad &amp; Tobago': return 'TT';
          case 'Tunisia': return 'TN';
          case 'Turkey': return 'TR';
          case 'Turkmenistan': return 'TM';
          case 'Uganda': return 'UG';
          case 'United Arab Emirates': return 'AE';
          case 'Ukraine': return 'UA';
          case 'United States of America': return 'US';
          case 'Uruguay': return 'UY';
          case 'Vanuatu': return 'VU';
          case 'Venezuela': return 'VE';
          case 'Vietnam': return 'VN';
          case 'Western Samoa': return 'WS';
          case 'Yemen': return 'YE';
          default: return 'XZ';
        }
      }
    }
  }
