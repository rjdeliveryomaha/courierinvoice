<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\Query;
  use rjdeliveryomaha\courierinvoice\Ticket;
  use rjdeliveryomaha\courierinvoice\Invoice;
  use rjdeliveryomaha\courierinvoice\Client;
  use rjdeliveryomaha\courierinvoice\TicketChart;
  use rjdeliveryomaha\courierinvoice\InvoiceChart;

  class CommonFunctions {
    protected $username;
    protected $publicKey;
    protected $privateKey;
    // preserve the options to be passed to new objects
    protected $options;
    // session validation key
    protected $formKey;
    // session dependant variables
    protected $config;
    protected $organizationFlag = FALSE;
    protected $CanDispatch;
    protected $weightMarker;
    protected $rangeMarker;
    protected $countryClass;
    protected $countryInput;
    protected $requireCountry;
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
    protected $noSession = FALSE;
    // string: login name for courier invoice user
    protected $userLogin;
    // maximum number of months to display on a chart
    protected $allTimeChartLimit = 6;
    // Client names that should be changed for example to abbreviate
    protected $clientNameExceptions = [];
    // Addresses that should be ignored for example due to change of address
    protected $clientAddressExceptions = [];
    protected $sanitized;
    protected $enableLogging = FALSE;
    protected $targetFile;
    protected $fileWriteTry;
    protected $loggingError = FALSE;
    protected $error = '';
    private $ints = ['dryIce', 'DryIce', 'diWeight', 'fromMe', 'toMe', 'charge', 'Charge', 'type', 'Type', 'contract', 'Contract', 'DispatchedTo', 'emailConfirm', 'EmailConfirm', 'pSigReq', 'dSigReq', 'd2SigReq', 'repeatClient', 'RepeatClient', 'ticketNumber', 'TicketNumber', 'sigType', 'PriceOverride', 'RunNumber', 'holder', 'receiver', 'TransferState', 'ClientID', 'Organization', 'ListBy', 'same'];
    // Properties that should always be floats
    private $floats = ['diPrice', 'TicketBase', 'RunPrice', 'TicketPrice', 'Multiplier', 'timestamp', 'lat',' lng', 'maxRange'];
    // Properties that should always be boolean
    private $bools = ['newTicket', 'compare', 'compareMembers', 'ticketEditor', 'updateTicket', 'consolidateContractTicketsOnInvoice', 'showCanceledTicketsOnInvoice', 'organizationFlag', 'noSession', 'processTransfer'];
    // Properties that are passed at the end of a string value
    private $afterSemicolon = ['billTo', 'dispatchedTo', 'PendingReceiver'];
    // Properties that are json encoded strings
    private $jsonStrings = ['Transfers'];
    // No need to filter passwords they will be hashed
    private $noFilter = ['currentPw', 'newPw1', 'newPw2'];
    // These properties should not be accessible when setting values from the $data argument
    private $protectedProperties = [ 'username', 'publicKey', 'privateKey', 'config', 'weightMarker', 'rangeMarker', 'countryClass', 'countryInput', 'requireCountry', 'shippingCountry', 'headerLogo', 'headerLogo2', 'myInfo', 'clientNameExceptions', 'clientAddressExceptions', 'showCanceledTicketsOnInvoiceExceptions', 'ignoreValues', 'showCanceledTicketsOnInvoice', 'consolidateContractTicketsOnInvoice', 'ints', 'floats', 'bools', 'afterSemicolon', 'jsonStrings', 'noFilter', 'sanitized', 'enableLogging', 'targetFile', 'fileWriteTry', 'loggingError', 'error', 'protectedProperties', 'RangeCenter', 'lat', 'lng', 'maxRange', 'timezone', 'emailConfig', 'allTimeChartLimit', 'invoicePage1Max', 'invoicePageMax'];
    private $noGetProps = [ 'error', 'loggingError', 'fileWriteTry', 'sanitized' ];
    private $customMenuItems;
    private $customPages;
    private $customScripts;

    public function __construct($options, $data=[]) {
      if (!is_array($options) || empty($options)) {
        throw new \Exception('Invalid Configuration');
      }
      $this->options = $options;
      do {
        $this->{key($options)} = current($options);
        next($options);
      } while (key($options) !== NULL);

      if ($this->enableLogging !== FALSE && ($this->targetFile === '' || $this->targetFile === NULL)) {
        $this->error .= "Logging Enabled With No Target File.\n";
        $this->loggingError = TRUE;
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
        if ($this->enableLogging !== FALSE && $this->loggingError === FALSE) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ($this->options['testMode'] === true && $this->options['testURL'] === null || $this->options['testURL'] === '') {
        throw new Exception('Invalid URL');
      }
      if (!isset($data['noSession'])) {
        if (empty($_SESSION) || !isset($_SESSION['ulevel']) || !isset($_SESSION['config']) || empty($_SESSION['config'])) {
          $this->error = 'Session Error';
          if ($this->enableLogging !== FALSE) self::writeLoop();
          throw new \Exception($this->error);
        }
        $this->ulevel = $_SESSION['ulevel'];
        // Populate the config array from the Session
        $this->config = $_SESSION['config'];
        // RangeCenter is stored as a string it needs to be an array
        if (strpos($_SESSION['config']['RangeCenter'], ',') !== FALSE) $this->config['RangeCenter'] = [ 'lat' => (float)self::before(',', $_SESSION['config']['RangeCenter']), 'lng' => (float)self::after(',', $_SESSION['config']['RangeCenter']) ];

        if (isset($_SESSION['pwWarning'])) $this->pwWarning = $_SESSION['pwWarning'];
        if (($this->ulevel === 1 || $this->ulevel === 2) && self::after_last('\\', get_class($this)) !== 'Client') {
          $this->RepeatClient = $_SESSION['RepeatClient'];
          $this->ClientID = $_SESSION['ClientID'];
          $clientData = [
            'client_index'=>$_SESSION['client_index'],
            'RepeatClient'=>$_SESSION['RepeatClient'],
            'ClientID'=>$_SESSION['ClientID'],
            'ClientName'=>self::decode($_SESSION['ClientName']),
            'Department'=>self::decode($_SESSION['Department']),
            'ShippingAddress1'=>self::decode($_SESSION['ShippingAddress1']),
            'ShippingAddress2'=>self::decode($_SESSION['ShippingAddress2']),
            'ShippingCountry'=>$_SESSION['ShippingCountry'],
            'BillingName'=>self::decode($_SESSION['BillingName']),
            'BillingAddress1'=>self::decode($_SESSION['BillingAddress1']),
            'BillingAddress2'=>self::decode($_SESSION['BillingAddress2']),
            'BillingCountry'=>$_SESSION['BillingCountry'],
            'Telephone'=>$_SESSION['Telephone'],
            'EmailAddress'=>self::decode($_SESSION['EmailAddress']),
            'Organization'=>$_SESSION['Organization'],
            'formKey'=>$_SESSION['formKey']
          ];
          $this->members[$this->ClientID] = self::createClient($clientData);
          if ($this->members[$this->ClientID] === FALSE) {
            throw new \Exception($this->error);
          }
          $this->shippingCountry = $_SESSION['ShippingCountry'];
        }
        if ($this->ulevel === 0 && self::after_last('\\', get_class($this)) !== 'Client') {
          $this->organizationFlag = TRUE;
          $this->ClientID = $_SESSION['ClientID'];
          $this->ListBy = $_SESSION['ListBy'];
          foreach ($_SESSION['members'] as $key => $value) {
            $value['formKey'] = $_SESSION['formKey'];
            $temp = self::createClient($value);
            if ($temp === FALSE) {
              throw new \Exception($this->error);
            }
            $this->members[$key] = $temp;
            $this->shippingCountry = $_SESSION['members'][$key]['ShippingCountry'];
          }
        }
        if (isset($_SESSION['driver_index']) || isset($_SESSION['dispatch_index'])) {
          $this->CanDispatch = (isset($_SESSION['CanDispatch'])) ? (int)$_SESSION['CanDispatch'] : NULL;
          $this->shippingCountry = $this->config['ShippingCountry'];
        }
        $this->logo = $this->config['LogoFileName'];
        $this->logoFile = glob("../images/logo/{$this->logo}");
        if (!$this->logoFile) {
          $this->headerLogo = "<div><h1>{$this->config['ClientName']}</h1></div>";
          $this->headerLogo2 = "<h5>{$this->config['ClientName']}</h5>";
        } else {
          $this->headerLogo = "<img src=\"../images/logo/{$this->logo}\" alt=\"{$this->config['ClientName']}\" height=\"75\" width=\"300\" />";
          $this->headerLogo2 = "<img src=\"../images/logo/{$this->logo}\" alt=\"{$this->config['ClientName']}\" height=\"30\" width=\"120\" />";
        }
        $this->weightMarker = ($this->config['WeightsMeasures'] === 0) ? '&#35;' : 'kg';
        $this->rangeMarker = ($this->config['WeightsMeasures'] === 0) ? 'mi' : 'km';
        $this->countryClass = ($this->config['InternationalAddressing'] === 0) ? 'hide' : '';
        $this->countryInput = ($this->config['InternationalAddressing'] === 0) ? 'disabled' : '';
        $this->requireCountry = ($this->config['InternationalAddressing'] === 1) ? 'required' : '';
        $this->myInfo = array('Name' => $this->config['ClientName'], 'EmailAddress' => $this->config['EmailAddress'], 'Telephone' => $this->config['Telephone']);
      }
      $this->sanitized = self::recursive_santizer($data);
      do {
        if (!in_array(key($this->sanitized), $this->protectedProperties) && !in_array(ucfirst(key($this->sanitized)), $this->protectedProperties)) {
          if (property_exists($this, ucfirst(key($this->sanitized)))) {
            $temp = ucfirst(key($this->sanitized));
            $this->{$temp} = current($this->sanitized);
          }
          $this->{key($this->sanitized)} = current($this->sanitized);
        }
        next($this->sanitized);
      } while (key($this->sanitized) !== NULL);

      if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($data['noSession'])) {
        if (!$this->validate()) {
          $this->error = 'Session Validation Error';
          if ($this->enableLogging !== FALSE) self::writeLoop();
          throw new \Exception($this->error);
        }
      }
    }

    public function getProperty($property) {
      if (property_exists($this, $property) && !in_array($property, $this->noGetProps)) {
        return $this->{$property};
      }
      return FALSE;
    }

    public function updateProperty($property, $value) {
      if (property_exists($this, $property) && !in_array($property, $this->noGetProps)) {
        if (in_array($property, $this->ints)) {
          return $this->{$property} = self::test_int($value);
        } elseif (in_array($property, $this->floats)) {
          return $this->{$property} = self::test_float($value);
        } elseif (in_array($property, $this->bools)) {
          return $this->{$property} = self::test_bool($value);
        } else {
          $value = (is_array($value)) ? self::recursive_santizer($value) : self::test_input($value);
          return $this->{$property} = $value;
        }
      }
      return FALSE;
    }

    public function addToProperty($property, $value) {
      if (property_exists($this, $property) && (in_array($property, $this->ints) || in_array($property, $this->floats))) {
        return $this->{$property} += $value;
      }
      return FALSE;
    }

    public function substractFromProperty($property, $value) {
      if (property_exists($this, $property) && (in_array($property, $this->ints) || in_array($property, $this->floats))) {
        return $this->{$property} -= $value;
      }
      return FALSE;
    }

    public function compareProperties($obj1, $obj2, $property, $strict=FALSE) {
      if (!(is_object($obj1) && is_object($obj2))) {
        return FALSE;
      }
      if (!property_exists($obj1, $property) || !property_exists($obj2, $property)) {
        return FALSE;
      }
      return ($strict === TRUE) ? ($obj1->getProperty($property) === $obj2->getProperty($property)) : ($obj1->getProperty($property) == $obj2->getProperty($property));
    }

    public function debug() {
      return self::safe_print_r($this);
    }

    public function getError() {
      return $this->error;
    }

    protected function writeLoop() {
      $this->error .= PHP_EOL;
      $i = 0;
      do {
        $test = self::writeFile();
        $i++;
      } while ($test !== strlen($this->error) && $i < $this->fileWriteTry);
    }

    protected function writeFile() {
      /*** http://php.net/manual/en/function.fwrite.php#81269 ***/
      /*** open the file for writing and truncate it to zero length ***/
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
        if ($rv === FALSE || $rv == 0) {
          return($bytes_written == 0 ? FALSE : $bytes_written);
        }
        $bytes_written += $rv;
      }
      return $bytes_written;
    }

    protected function setTimezone() {
      if (!date_default_timezone_set($this->config['TimeZone'])) {
        $this->error = 'Timezone Error Line ' . __line__;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ($this->timezone === NULL || !is_object($this->timezone)) {
        try {
          $this->timezone = new \dateTimeZone($this->config['TimeZone']);
        } catch (Exception $e) {
          $this->error = 'Timezone Error Line ' . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== FALSE) self::writeLoop();
          throw new \Exception($this->error);
        }
      }
    }
    // session validation key
    // Private function to generate the key
    private function generateKey() {
      // Get the user IP-address for use in key
      $ip = $_SERVER['REMOTE_ADDR'];
      // Use mt_rand and uniqid to generate another part of the key
      $uniqid = uniqid(mt_rand(), TRUE);
      // Return a hash of the string for added opacity
      return md5($ip . $uniqid);
    }

    // Public function to output the key in a hidden form element
    public function outputKey() {
      // Generate the key and store it in the class
      $this->formKey = self::generateKey();
      // Store the key as a session variable
      $_SESSION['formKey'] = $this->formKey;
      //Output the form key
      return $this->formKey;
    }

    //Public function to validate the key as POST data
    private function validate() {
      // Compare the POST value to the previous key
      return (isset($_SESSION['formKey']) && $this->formKey === $_SESSION['formKey']);
    }

    protected function recursive_santizer($array, $filter='input') {
      // possible filters: email, float, int
      $returnData = array();

      foreach ($array as $key => $value) {
        if (is_object($value)) {
          $returnData[$key] = $value;
        } elseif (is_array($value)) {
          $returnData[$key] = self::recursive_santizer($value);
        } elseif (in_array($key, $this->ints, true) || substr($key, -5) === 'index') {
          $returnData[$key] = self::test_int($value);
        } elseif (in_array($key, $this->bools, true)) {
          $returnData[$key] = self::test_bool($value);
        } elseif (in_array($key, $this->floats, true)) {
          $returnData[$key] = self::test_float($value);
        } elseif (in_array($key, $this->afterSemicolon, true)) {
          // Capture client name and driver name from this group
          if (strpos($value, ';') !== FALSE) {
            switch ($key) {
              case 'billTo':
                if (strpos($value, ';') !== FALSE) {
                  $returnData['ClientName'] = self::decode(self::test_input(self::before_last(';', $value)));
                  $returnData['BillTo'] = self::test_int(self::after_last(';', $value));
                } else {
                  $returnData['BillTo'] = self::test_int($value);
                }
              break;
              case 'dispatchedTo':
                if (strpos($value, ';') !== FALSE) {
                  $returnData['DriverName'] = self::decode(self::test_input(self::before_last(';', $value)));
                  $returnData['driverID'] = $returnData['DispatchedTo'] = self::test_int(self::after_last(';', $value));
                } else {
                  $returnData['driverID'] = $returnData['DispatchedTo'] = self::test_int($value);
                }
              break;
              case 'PendingReceiver':
                if (strpos($value, ';') !== FALSE) {
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
          $returnData[$key] = ($value === NULL) ? NULL : self::decode(self::test_input($value));
        } else {
          $returnData[$key] = $value;
        }
      }
      return $returnData;
    }
    // http://php.net/manual/en/function.array-search.php#91365
    // Changed or to || for boolean testing
    // Added test for values being an object
    protected function recursive_array_search($needle,$haystack) {
      if (!is_array($haystack) && !is_object($haystack)) {
        $this->error = 'Invalid Search Target Line ' . __line__;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value || ((is_array($value) || is_object($value)) && self::recursive_array_search($needle,$value) !== FALSE)) {
          return $current_key;
        }
      }
      return FALSE;
    }

    protected function user_array_sort($array, $on, $order=SORT_ASC) {
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
    protected function after($needle, $inthat) {
      if (!is_bool(strpos($inthat, $needle)))
      return substr($inthat, strpos($inthat,$needle)+strlen($needle));
    }

    protected function after_last($needle, $inthat) {
      if (!is_bool(strrpos($inthat, $needle)))
      return substr($inthat, strrpos($inthat, $needle)+strlen($needle));
    }

    protected function before($needle, $inthat) {
      return substr($inthat, 0, strpos($inthat, $needle));
    }

    protected function before_last($needle, $inthat) {
      return substr($inthat, 0, strrpos($inthat, $needle));
    }

    protected function between($needle, $that, $inthat) {
      return self::before ($that, self::after($needle, $inthat));
    }

    protected function between_last($needle, $that, $inthat) {
      return self::after_last($needle, self::before_last($that, $inthat));
    }

    protected function esc_url($url) {
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

    protected function decode($data) {
      if (preg_match('/(&#\d+;)/', $data)) {
        $data = preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES'); }, $data);
      }
      if (preg_match('/(&\w+;)/', $data)) {
        $data = html_entity_decode($data);
      }
      return $data;
    }

    protected function encodeURIComponent($str) {
      $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
      return strtr(rawurlencode($str), $revert);
    }

    protected function test_input($val) {
      return filter_var(htmlentities(stripcslashes(trim($val))), FILTER_SANITIZE_STRING);
    }

    protected function test_int($val) {
      if ($val === '' || $val === NULL) return NULL;
      return (int)round(self::test_float($val), 0, PHP_ROUND_HALF_EVEN);
    }

    protected function test_float($val) {
      return (float)filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    protected function test_email($val) {
      return filter_var($val, FILTER_SANITIZE_EMAIL);
    }

    protected function test_bool($val) {
      return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    protected function test_time($val) {
      return preg_match('/(0?\d|1[0-2]):(0\d|[0-5]\d) (AM|PM)/i', $val) || preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]/', $val);
    }

    protected function test_phone($val) {
      return preg_match('/(\d{3})-(\d{3})-(\d{4})x(\d+)/i', $val) || preg_match('/(\d{3})-(\d{3})-(\d{4})/', $val);
    }

    protected function test_invoice_number($val) {
      return (preg_match('/(^[\d]{2}EX[\d]+-t[\d]+$)/', $val) || preg_match('/(^[\d]{2}EX[\d]+-[\d]+$)/', $val));
    }

    protected function safe_print_r($data) {
      echo "<pre>\n{$this->safe_print($data)}\n</pre>";
    }
    // http://php.net/manual/en/function.print-r.php#117746
    // why var_export: https://stackoverflow.com/a/139553
    protected function safe_print($data, $nesting = 15, $indent = '') {
      $returnData = '';
      if (!is_object($data) && !is_array($data) && !is_resource($data)) {
        switch (gettype($data)) {
          case 'string': $returnData .= ucfirst(gettype($data)) . ' (' . strlen($data) . '): ' . var_export($data, TRUE) . "\n"; break;
          case "integer":
          case "double": $returnData .= ucfirst(gettype($data)) . ' (' . var_export($data, TRUE) . ")\n"; break;
          case "NULL": $returnData .= ucfirst(gettype($data)) . "\n"; break;
          // boolean is covered by default
          default: $returnData .= ucfirst(gettype($data)) . ': ' . var_export($data, TRUE) . "\n"; break;
        }
      } elseif ($nesting < 0) {
        $returnData .= "** MORE **\n";
      } else {
        $returnData .= ucfirst(gettype($data)) . " (\n";
        $objFullName = (is_object($data)) ? get_class($data) : '';
        $objType = (strpos($objFullName, '\\') !== FALSE) ? self::after_last('\\', $objFullName) : $objFullName;
        $objType .= ($objType === '') ? '' : ':';
        foreach ($data as $k => $v) {
          $returnData .= $indent . "\t[$objType$k] => ";
          $returnData .= self::safe_print($v, $nesting - 1, "$indent\t");
        }
        $returnData .= "$indent)\n";
      }
      return $returnData;
    }
    //Turn an array into a nice printed list
    protected function arrayToList($array) {
      $elements = count($array);
      if ($elements === 0) {
        return FALSE;
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
    protected function number_format_drop_zero_decimals($n, $n_decimals) {
      /*
      round($n, $n_decimals) may equal a whole number in cases when rounding up and preceding precision is 9s, ex: round(16.995, 2) equals 17.00. Test for rounded up integer with ceil($n).
      */
      return ((floor($n) == round($n, $n_decimals)) || (ceil($n) == round($n, $n_decimals))) ? number_format($n) : number_format($n, $n_decimals);
    }
    /* Put negative numbers in parentheses */
    protected function negParenth($data) {
      return ((float)$data < 0 ? '<span class="paren">(</span>' . ($data * -1) . '<span class="paren">)</span>' : $data);
    }
    //Prevent division by zero when trying to show percentages
    protected function displayPercentage($numerator, $denominator) {
      if ((float)$denominator == 0) {
        return '0';
      }
      return self::number_format_drop_zero_decimals((float)$numerator/(float)$denominator * 100, 2);
    }

    protected function clientListBy($val) {
      return ($this->ListBy === 0) ? $this->members[$val]->getProperty('ShippingAddress1') : $this->members[$val]->getProperty('Department');
    }

    protected function chartIndexToProperty() {
      switch ($this->chartIndex) {
        case 1: return $this->firstChart;
        case 2: return $this->secondChart;
        default: return NULL;
      }
    }

    protected function createClient($data) {
      try {
        $obj = new Client($this->options, $data);
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        return FALSE;
      }
      return $obj;
    }

    protected function createTicket($data) {
      try {
        $obj = new Ticket($this->options, $data);
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        return FALSE;
      }
      return $obj;
    }

    protected function createTicketChart($data) {
      try {
        $obj = new TicketChart($this->options, $data);
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        return FALSE;
      }
      return $obj;
    }

    protected function createInvoice($data) {
      try {
        $obj = new Invoice($this->options, $data);
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        return FALSE;
      }
      return $obj;
    }

    protected function createInvoiceChart($data) {
      try {
        $obj = new InvoiceChart($this->options, $data);
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        return FALSE;
      }
      return $obj;
    }

    protected function createQuery($data) {
      try {
        $obj = new Query($this->options, $data);
      } catch (Exception $e) {
        $this->error = $e->getMessage();
        return FALSE;
      }
      return $obj;
    }

    protected function callQuery($query) {
      try {
        $result = $query->buildURI()->call();
      } catch (Exception $e) {
        $this->error = $e->getMessage();
        return FALSE;
      }
      $returnData = json_decode($result, true);
      return (strtoupper($query->getProperty('method')) === 'GET') ? $returnData['records'] : $returnData;
    }

    protected function createLimitedMonthInput($clientIDs, $inputID, $disabled=FALSE, $type='month', $table='invoices', $required=FALSE) {
      $sql = $min = $max = $returnData = '';
      $dates = $data = array();
      $disableInput = ($disabled === FALSE) ? '' : 'disabled';
      $requireInput = ($required === FALSE) ? '' : 'required';
      $id = ($type === "month") ? $inputID . 'Month' : $inputID;
      $inputName = (strpos($inputID,'invoice') === FALSE) ? $inputID : substr($inputID, 7);
      $queryData = [];
      $queryData['endPoint'] = $table;
      $queryData['formKey'] = $this->formKey;
      $queryData['method'] = 'GET';
      // Define variables based on the input type
      if ($type === 'month') {
        $format = 'Y-m';
        $when = 'DateIssued';
        $queryData['queryParams']['include'] = ['DateIssued'];
        $queryData['queryParams']['filter'] = ($this->RepeatClient === 0) ? [ ['Resource'=>'InvoiceNumber', 'Filter'=>'cs', 'Value'=>'t'] ] : [ ['Resource'=>'InvoiceNumber', 'Filter'=>'ncs', 'Value'=>'t'] ];
        $who = 'ClientID';
        $placeholder = 'JAN 2000';
      } elseif ($type === 'date') {
        $format = 'Y-m-d';
        $when = 'ReceivedDate';
        $queryData['queryParams']['include'] = ['ReceivedDate'];
        $queryData['queryParams']['filter'] = [ ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->RepeatClient] ];
        $who = 'BillTo';
        $placeholder = '';
      }
      // Make sure that $clientIDs is an array
      if ($clientIDs !== NULL && !is_array($clientIDs)) {
        $temp = $clientIDs;
        $clientIDs = [];
        $clientIDs[] = $temp;
      }
      // Return an error if no clients are listed
      if (count($clientIDs) === 0){
        $returnData = "No Clients In Organization";
        return $returnData;
      }
      $queryData['queryParams']['filter'][] = ['Resource'=>$who, 'Filter'=>'in', 'Value'=> implode(',', $clientIDs)];
      if (!$query = self::createQuery($queryData)) {
        return $this->error;
      }
      $data = self::callQuery($query);
      if ($data === FALSE) {
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
        //Define the input
        $returnData = '<input type="' . $type . '" min="' . $min . '" max="' . $max . '" name="' . lcfirst($inputName) . '" class="' . $inputID . ucfirst($type) . '" placeholder="' . $placeholder . '" ' . $disableInput . ' ' . $requireInput . ' />';
      } else {
        $returnData = 'No Data On File';
      }
      return $returnData;
    }

    protected function createInvoiceNumberSelect($search) {
      $queryData = [];
      $queryData['formKey'] = $this->formKey;
      $queryData['method'] = 'GET';
      $queryData['endPoint'] = 'invoices';
      $queryData['queryParams']['include'] = ['InvoiceNumber', 'Closed'];
      $queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID','Filter'=>'eq','Value'=>$search] ];
      $queryData['queryParams']['filter'][] = ($this->RepeatClient === 0) ? ['Resource'=>'InvoiceNumber', 'Filter'=>'cs', 'Value'=>'t'] : ['Resource'=>'InvoiceNumber', 'Filter'=>'ncs', 'Value'=>'t'];
      $queryData['queryParams']['order'] = ['InvoiceNumber,desc'];
      if (!$query = self::createQuery($queryData)) {
        return $this->error;
      }
      $invoiceList = self::callQuery($query);
      if ($invoiceList === FALSE) {
        return $this->error;
      }
      if (empty($invoiceList)) {
        return 'No Invoice On File';
      }
      $returnData = '<select name="invoiceNumber" id="invoiceNumber" disabled>';
      foreach ($invoiceList as $invoice) {
        $flag = ($invoice['Closed'] === 0) ? '*' : '';
        $returnData .= "<option value=\"{$invoice['InvoiceNumber']}\">{$invoice['InvoiceNumber']}{$flag}</option>";
      }
      $returnData .= '</select>';
      return $returnData;
    }

    protected function getCredit() {
      $data = [];
      $data['formKey'] = $this->formKey;
      $data['method'] = 'GET';
      $data['endPoint'] = 'invoices';
      $data['queryParams']['include'] = ['Balance', 'DatePaid'];
      $data['queryParams']['filter'] = [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$_SESSION['ClientID']], ['Resource'=>'Closed', 'Filter'=>'eq', 'Value'=>1] ];
      if (!$query = self::createQuery($data)) {
        return $this->error;
      }
      $temp = self::callQuery($query);
      if ($temp === FALSE) {
        return $this->error;
      }

      $credit = ['Balance'=>'0', 'DatePaid'=>'0'];

      foreach ($temp as $test) {
        if ($test['DatePaid'] > $credit['DatePaid']) {
          $credit = $test;
        }
      }
      $data['endPoint'] = 'tickets';
      $data['queryParams']['include'] = ['TicketPrice'];
      $data['queryParams']['filter'] = [ ['Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>$_SESSION['ClientID']], ['Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>'-'], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>9] ];
      if (!$query = self::createQuery($data)) {
        return $this->error;
      }
      $temp2 = self::callQuery($query);
      if ($temp2 === FALSE) {
        return $this->error;
      }
      foreach ($temp2 as $test) {
        $credit['Balance'] += ($test['TicketPrice'] * -1);
      }
      return self::negParenth(self::number_format_drop_zero_decimals($credit['Balance'], 2));
    }

    protected function listOrgMembers($identifier) {
      $returnData = '';
      $x = 0;
      foreach ($_SESSION['members'] as $key => $value) {
        $display = ($this->ListBy === 1) ? $value['Department'] : $value['ShippingAddress1'];
        $returnData .= "<span class=\"floatLeft highlight\"><input type=\"checkbox\" name=\"clientID[]\" value=\"{$key}\" class=\"orgMember\" id=\"orgMember{$x}{$identifier}\" data-value=\"{$key}\" /><label for=\"orgMember{$x}{$identifier}\">{$display}; {$key}</label>  </span>";
        $x++;
      }
      return $returnData;
    }

    private function customize() {
      $spinner = '
            <div class="showbox">
              <!-- New spinner from http://codepen.io/collection/HtAne/ -->
              <div class="loader">
                <svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                </svg>
              </div>
            </div>';
      if (is_numeric($_SESSION['ulevel'])) {
        $type = ($_SESSION['ulevel'] > 0) ? 'client' : 'org';
        $type .= ($this->ClientID === 0) ? '0' : '';
      } else {
        $type = ($_SESSION['ulevel'] === 'driver') ? 'driver' : 'dispatcher';
      }
      $withPage = $pages = $noPage = [];
      if (isset($this->options['extend']['all']) && is_array($this->options['extend']['all']) && !empty($this->options['extend']['all'])) {
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
          if (isset($this->options['extend']['all'][$i][2]) && $this->options['extend']['all'][$i][2] !== '') $this->customScripts .= '></script>';
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
          if (isset($this->options['extend'][$type]) && is_array($this->options['extend'][$type]) && !empty($this->options['extend'][$type])) {
            for ($i = 0; $i < count($this->options['extend'][$type]); $i++) {
              if (isset($this->options['extend'][$type][$i][0]) && $this->options['extend'][$type][$i][0] !== '') {
                if (!isset($this->options['extend'][$type][$i][1]) || $this->options['extend'][$type][$i][1] === '') {
                  $moreNoPage[] = $this->options['extend'][$type][$i][0];
                } else {
                  $moreWithPage[] = $this->options['extend'][$type][$i][0];
                  $morePages[] = $this->options['extend'][$type][$i][1];
                }
              }
              if (isset($this->options['extend'][$type][$i][2]) && $this->options['extend'][$type][$i][2] !== '') {
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
              if (isset($this->options['extend'][$type][$i][2]) && $this->options['extend'][$type][$i][2] !== '') $this->customScripts .= '></script>';
            }
          }
        break;
        case 'client':
          $search_index = $this->ulevel;
        case 'driver':
          if (!$search_index) $search_index = $this->CanDispatch + 1;
          if (isset($this->options['extend'][$type][0]) && is_array($this->options['extend'][$type][0]) && !empty($this->options['extend'][$type][0])) {
            for ($i = 0; $i < count($this->options['extend'][$type][0]); $i++) {
              if (isset($this->options['extend'][$type][0][$i][0]) && $this->options['extend'][$type][0][$i][0] !== '') {
                if (!isset($this->options['extend'][$type][0][$i][1]) || $this->options['extend'][$type][0][$i][1] === '') {
                  $moreNoPage[] = $this->options['extend'][$type][0][$i][0];
                } else {
                  $moreWithPage[] = $this->options['extend'][$type][0][$i][0];
                  $morePages[] = $this->options['extend'][$type][0][$i][1];
                }
              }
              if (isset($this->options['extend'][$type][0][$i][2]) && $this->options['extend'][$type][0][$i][2] !== '') {
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
              if (isset($this->options['extend'][$type][0][$i][2]) && $this->options['extend'][$type][0][$i][2] !== '') $this->customScripts .= '></script>';
            }
          }
          if (isset($this->options['extend'][$type][$search_index]) && is_array($this->options['extend'][$type][$search_index]) && !empty($this->options['extend'][$type][$search_index])) {
            for ($i = 0; $i < count($this->options['extend'][$type][$search_index]); $i++) {
              if (isset($this->options['extend'][$type][$search_index][$i][0]) && $this->options['extend'][$type][$search_index][$i][0] !== '') {
                if (!isset($this->options['extend'][$type][$search_index][$i][1]) || $this->options['extend'][$type][$search_index][$i][1] === '') {
                  $moreNoPage[] = $this->options['extend'][$type][$search_index][$i][0];
                } else {
                  $moreWithPage[] = $this->options['extend'][$type][$search_index][$i][0];
                  $morePages[] = $this->options['extend'][$type][$search_index][$i][1];
                }
              }
              if (isset($this->options['extend'][$type][$search_index][$i][2]) && $this->options['extend'][$type][$search_index][$i][2] !== '') {
                $this->customScripts .= "
                <script src=\"{$this->options['extend'][$type][$search_index][$i][2]}\"";
              }
              if (isset($this->options['extend'][$type][$search_index][$i][3])) {
                for ($j = 3; $j < count($this->options['extend'][$type][$search_index][$i]); $j++) {
                  if (isset($this->options['extend'][$type][$search_index][$i][$j]) && $this->options['extend'][$type][$search_index][$i][$j] !== '') {
                    $this->customScripts .= " {$this->options['extend'][$type][$search_index][$i][$j]}";
                  }
                }
              }
              if (isset($this->options['extend'][$type][$search_index][$i][2]) && $this->options['extend'][$type][$search_index][$i][2] !== '') $this->customScripts .= '></script>';
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
              $alert = ($this->pwWarning === 4) ? '<span class="alert">!</span>' : '<span class="alert"></span>';
            break;
            case 1:
              if ($id === 'change_password') {
                $alert = ($this->pwWarning === 1 || $this->pwWarning === 3) ? '<span class="alert">!</span>' : '<span class="alert"></span>';
              } elseif ($id === 'change_admin_password') {
                $alert = ($this->pwWarning === 2 || $this->pwWarning === 3) ? '<span class="alert">!</span>' : '<span class="alert"></span>';
              }
            break;
          }
        } elseif ($id === 'on_call' || $id === 'transfers' || $id === 'dispatch') {
          $alert = ($id === 'on_call') ? '<span class="ticketCount"></span>' : "<span class=\"{$id}Count\"></span>";
        }
        $this->customMenuItems .= "<li><a data-id=\"{$id}\" class=\"nav\">{$totalWithPage[$i]}{$alert}</a></li>
        ";
        $this->customPages .= "<div id=\"{$id}\" data-function=\"{$totalPages[$i]}\" class=\"page\">{$spinner}</div>
        ";
      }
      for ($i = 0; $i < count($totalNoPage); $i++) {
        $this->customMenuItems .= "<li>{$totalNoPage[$i]}</li>
        ";
      }
    }

    public function createNavMenu() {
      self::customize();
      if (is_numeric($_SESSION['ulevel'])) {
        $displayClientName = $_SESSION['ClientName'];
        switch ($_SESSION['ulevel']) {
          case 2:
            $displayClientName .= "<br>{$_SESSION['Department']}";
          case 1:
            $displayClientName .= "<br>{$_SESSION['Department']} Admin";
          break;
          case 0:
            $displayClientName .= '<br>Organizational';
          break;
        }
      } elseif ($_SESSION['ulevel'] === 'driver') {
        $displayClientName = "{$_SESSION['FirstName']} {$_SESSION['LastName']}";
        if ($_SESSION['CanDispatch'] > 0) {
          $displayClientName .= '<br>Driver / Dispatch';
        } else {
          $displayClientName .= '<br>Driver';
        }
      } elseif ($_SESSION['ulevel'] === 'dispatch') {
        $displayClientName = "{$_SESSION['FirstName']} {$_SESSION['LastName']}";
        $displayClientName .= '<br>Dispatch';
      }
      $mobileMarker = (isset($_SESSION['mobile']) && $_SESSION['mobile'] === TRUE) ? 1 : 0;
      return "
        <div class=\"menu__header\">
          <p id=\"menuDriverName\">{$displayClientName}</p>
          <div id=\"logoutRefresh\">
            <button type=\"button\" class=\"refresh\">Refresh</button>
            <form id=\"logoutLink\" action=\"logout\" method=\"post\">
              <input type=\"hidden\" name=\"mobile\" value=\"{$mobileMarker}\" />
              <button type=\"submit\" form=\"logoutLink\">Log Out</button>
            </form>
          </div>
        </div>
        <ul class=\"menu__list\">
          {$this->customMenuItems}
        </ul>";
    }

    public function createAppLayout() {
      return "
        <div class=\"swipe-wrap\">
          {$this->customPages}
        </div>
      <script>
        var myInfo = [\"{$_SESSION['config']['ClientName']}\",\"{$_SESSION['config']['EmailAddress']}\",\"{$_SESSION['config']['Telephone']}\"]
      </script>
      <script src=\"https://code.jquery.com/jquery-3.3.1.min.js\"></script>
      <script>window.jQuery || document.write('<script src=\"../app_js/jquery-3.3.1.min.js\"><\/script>')</script>
      {$this->customScripts}";
    }

    protected function countryFromAbbr($abbr) {
      // Credits will have a value of '-' for pCountry and dCountry
      if ($abbr === '-') return $abbr;
      //"XZ" sounds unlikely as a country abbreviation so it will stand for "Not On File"
      if ($abbr == NULL) return '';
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
