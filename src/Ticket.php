<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  use Geocoder\Query\GeocodeQuery;
  use GuzzleHttp\Client as GuzzleClient;
  use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  class Ticket extends CommonFunctions {
    protected $startDate;
    protected $endDate;
    protected $allTime;
    protected $charge;
    protected $type;
    protected $ticketNumber;
    protected $contract;
    protected $invoiceNumber;
    protected $generalDiscount;
    protected $newTicket = FALSE;
    private $forDisatch = FALSE;
    protected $ticketEditor = FALSE;
    protected $updateTicket = FALSE;
    protected $processTransfer = FALSE;
    protected $crun_index;
    protected $ticket_index;
    protected $TicketNumber;
    protected $RunNumber = 0;
    protected $BillTo;
    protected $ClientName;
    protected $Department;
    protected $RequestedBy;
    protected $pClient;
    protected $pDepartment;
    protected $pContact;
    protected $pTelephone;
    protected $pAddress1;
    protected $pAddress2;
    protected $pCountry;
    protected $dClient;
    protected $dDepartment;
    protected $dContact;
    protected $dTelephone;
    protected $dAddress1;
    protected $dAddress2;
    protected $dCountry;
    protected $dryIce;
    protected $diWeight = 0;
    protected $diPrice;
    protected $TicketBase;
    protected $OldBase;
    protected $Charge;
    protected $Contract = 0;
    protected $Multiplier;
    protected $RunPrice;
    protected $TicketPrice;
    protected $Notes;
    protected $EmailConfirm = 0;
    protected $EmailAddress;
    protected $Telephone;
    protected $pSigReq;
    protected $pSigPrint;
    protected $pSig;
    protected $pSigType;
    protected $pSigFile;
    protected $dSigReq;
    protected $dSigPrint;
    protected $dSig;
    protected $dSigType;
    protected $dSigFile;
    protected $d2SigReq;
    protected $d2SigPrint;
    protected $d2Sig;
    protected $d2SigType;
    protected $d2SigFile;
    protected $RepeatClient = 1;
    protected $DispatchedTo = 0;
    protected $driverID;
    protected $FirstName;
    protected $LastName;
    protected $DriverName;
    protected $ReceivedDate;
    protected $DispatchTimeStamp;
    protected $DispatchMicroTime;
    protected $DispatchedBy = '0.0';
    protected $Transfers;
    /* TransferState will be bool to and from the API
     * Here it is used to indicate how to process a transfer
     * 2: Cancel Transfer
     * 3: Decline Transfer
     * 4: Accept Transfer
    */
    protected $TransferState;
    private $transferStateOld;
    private $pendingReceiverOld;
    protected $PendingReceiver;
    protected $receiverName;
    protected $pTimeStamp;
    protected $dTimeStamp;
    protected $d2TimeStamp;
    protected $pTime;
    protected $dTime;
    protected $d2Time;
    protected $InvoiceNumber = '-';
    protected $toMe;
    protected $fromMe;
    protected $PriceOverride;
    protected $step;
    protected $transferredBy;
    private $stepMarker;
    private $driverDatalist;
    private $dispatchForm;
    // Ticket values that should not be included on datalists
    protected $ignoreValues = [];
    // Other needed properties
    private $activeTicketSet = [];
    private $today;
    private $backstop;
    private $dateObject;
    public $index = 0;
    public $edit;
    private $memberInput;
    private $ticketNumberList;
    private $selectID;
    private $formName;
    private $userType;
    // variables for creating and calling queries
    private $query;
    private $queryData;
    private $result;
    // Default dateTime value in the database
    private $tTest;
    // Define the type of form to create charge options for
    private $formType;
    private $newTicketDatabaseKeys = ['Contract', 'RunNumber', 'TicketNumber', 'TicketBase', 'BillTo', 'RequestedBy', 'pClient', 'dClient', 'pDepartment', 'dDepartment', 'pAddress1', 'dAddress1', 'pAddress2', 'dAddress2', 'pCountry', 'dCountry', 'pContact', 'dContact', 'pTelephone', 'dTelephone', 'dryIce', 'diWeight', 'diPrice', 'Charge', 'RunPrice', 'TicketPrice', 'EmailConfirm', 'EmailAddress', 'Telephone', 'pTime', 'dTime', 'd2Time', 'pSigReq', 'dSigReq', 'd2SigReq', 'DispatchedTo', 'Transfers', 'ReceivedDate', 'DispatchTimeStamp', 'DispatchMicroTime', 'DispatchedBy', 'Notes'];
    private $updateTicketDatabaseKeys = ['BillTo', 'Charge', 'EmailAddress', 'EmailConfirm', 'Telephone', 'RequestedBy', 'pClient', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'dClient', 'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dryIce', 'diWeight', 'diPrice', 'DispatchedTo', 'Transfers', 'TicketBase', 'RunPrice', 'TicketPrice', 'Notes', 'pSigReq', 'dSigReq', 'd2SigReq'];
    private $postableKeys = ['repeatClient', 'fromMe', 'pClient', 'pDepartment', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'pSigReq', 'toMe', 'dClient', 'dDepartment', 'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dSigReq', 'dryIce', 'diWeight', 'Notes', 'Charge', 'DispatchedTo', 'd2SigReq', 'EmailAddress', 'EmailConfirm', 'Telephone', 'RequestedBy', 'locationList', 'clientList', 'tClientList', 'driverList'];
    private $javascriptKeys = ['ClientName', 'Department', 'ShippingAddress1', 'ShippingAddress2', 'ShippingCountry'];
    // Results form geocoder
    private $result1obj;
    private $result2obj;
    private $result1;
    private $result2;
    private $loc1;
    private $loc2;
    private $center;
    private $geocoder;
    private $guzzleConfig = [ 'timeout' => 2.0, 'verify' => FALSE ];
    private $guzzle;
    private $adapter;
    private $dumper;
    private $chain;
    private $dLat;
    private $dLng;
    private $pi80 = M_PI / 180;
    private $MER = 6372.797; // Mean Earth Radius in km
    private $angle;
    private $greatCircleDistance;
    private $processingRoute = FALSE;
    // bool flag indicating if a map will be displayed for price calculation
    private $mapAvailable = TRUE;
    // list of providers supported by php/Geocoder
    private $providers = [ 'AlgoliaPlaces', 'ArcGISOnline', 'BingMaps', 'FreeGeoIp', 'GeoIP', 'GeoIP2', 'GeoIPs', 'GeoPlugin', 'Geonames', 'GoogleMaps', 'Here', 'HostIp', 'IpInfo', 'IpInfoDb', 'Ipstack', 'LocationIQ', 'MapQuest', 'MapBox', 'Mapzen', 'MaxMind', 'MaxMindBinary', 'Nominatim', 'OpenCage', 'PickPoint', 'TomTom', 'Yandex' ];
    private $ticketBaseRetries = 0;
    /**
    *  int flag indicating what range to solve for
    *    0: range. distance between location 1 and location 2
    *    1: loc1Range. distance between home and location 1
    *    2: loc2Range. distance between home and location 2
    */
    private $rangeFlag = 0;
    private $rangeVal;
    private $loc1Range;
    private $loc2Range;
    // variables used in solving for ranges
    private $rangeLoc1;
    private $rangeLoc2;
    private $billingCode;
    private $maxFee;
    protected $maxRange;
    private $now;
    // Store lists of locations, drivers, and clients to save api calls
    protected $locationList;
    protected $driverList;
    protected $clientList;
    protected $tClientList;
    // Used for displaying route tickets with multiple locations for pick up or delivery
    public $multiTicket;
    // Used for canceling ticket
    public $action;
    // error catching
    private $imageError;
    private $pRangeTest;
    private $dRangeTest;
    private $pRangeError;
    private $dRangeError;
    // Variables for the function stepTicket
    public $sigImage;
    public $sigType;
    public $printName;

    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw $e;
      }
      if ($this->noSession === FALSE) {
        $this->driverID = (isset($_SESSION['driver_index'])) ? $_SESSION['DriverID'] : NULL;
        if (!is_numeric($this->ulevel)) {
          if ($this->ulevel === 'dispatch') {
            $this->userType = 'dispatch';
            $this->DispatchedBy = "1.{$_SESSION['DispatchID']}";
            $this->transferredBy = $this->DispatchedBy;
          } else {
            $this->userType = 'driver';
            $this->DispatchedBy = "2.{$_SESSION['DriverID']}";
            $this->transferredBy = $this->DispatchedBy;
          }
        } else {
          $this->userType  = ($this->ulevel > 0) ? 'client' : 'org';
          $this->DispatchedBy = '1.1';
          $this->transferredBy = 'error';
        }
        try {
          self::setTimezone();
        } catch (Exception $e) {
          $this->error .= "\n" . __function__ . ' Line ' . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== FALSE) self::writeLoop();
          throw $e;
        }
        try {
          $this->today = new \dateTime('NOW', $this->timezone);
        } catch (Exception $e) {
          $this->error .= "\nDate Error Line " . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== FALSE) self::writeLoop();
          throw $e;
        }
      }
      // forms will send ticketNumber, contract, charge while the API and this class expect TicketNumber, Contract, Charge
      if ($this->ticketNumber !== NULL) {
        $this->TicketNumber = $this->ticketNumber;
      }
      if ($this->contract !== NULL) {
        $this->Contract = $this->contract;
      }
      if ($this->charge !== NULL) {
        $this->Charge = $this->charge;
      }
    }

    private function rangeTest() {
      switch ($this->rangeFlag) {
        case 0:
          $this->rangeLoc1 = $this->loc1;
          $this->rangeLoc2 = $this->loc2;
        break;
        case 1:
          $this->rangeLoc1 = $this->config['RangeCenter'];
          $this->rangeLoc2 = $this->loc1;
        break;
        case 2:
          $this->rangeLoc1 = $this->config['RangeCenter'];
          $this->rangeLoc2 = $this->loc2;
        break;
        default: return FALSE;
      }
      $this->rangeLoc1['lat'] *= $this->pi80;
      $this->rangeLoc1['lng'] *= $this->pi80;
      $this->rangeLoc2['lat'] *= $this->pi80;
      $this->rangeLoc2['lng'] *= $this->pi80;

      $this->dLat = $this->rangeLoc2['lat'] - $this->rangeLoc1['lat'];

      $this->dLng = $this->rangeLoc2['lng'] - $this->rangeLoc1['lng'];

      $this->angle = sin($this->dLat / 2) * sin($this->dLat / 2) + cos($this->rangeLoc1['lat']) * cos($this->rangeLoc2['lat']) * sin($this->dLng / 2) * sin($this->dLng / 2);

      $this->greatCircleDistance = 2 * atan2(sqrt($this->angle), sqrt(1 - $this->angle));
      // Distance in km
      switch ($this->rangeFlag) {
        case 0: return $this->rangeVal = round($this->MER * $this->greatCircleDistance, 2);
        case 1: return $this->pRangeTest = round($this->MER * $this->greatCircleDistance, 2);
        case 2: return $this->dRangeTest = round($this->MER * $this->greatCircleDistance, 2);
      }
    }

    private function solveTicketPrice() {
      if ($this->fromMe === 1) {
        $this->pClient = $_SESSION['ClientName'];
        $this->pDepartment = $_SESSION['Department'];
        $this->pAddress1 = $_SESSION['ShippingAddress1'];
        $this->pAddress2 = $_SESSION['ShippingAddress2'];
        $this->pCountry = $_SESSION['ShippingCountry'];
      }
      if ($this->toMe === 1) {
        $this->dClient = $_SESSION['ClientName'];
        $this->dDepartment = $_SESSION['Department'];
        $this->dAddress1 = $_SESSION['ShippingAddress1'];
        $this->dAddress2 = $_SESSION['ShippingAddress2'];
        $this->dCountry = $_SESSION['ShippingCountry'];
      }
      if ($this->config['MaximumFee'] <= 0) {
        $this->maxFee = PHP_INT_MAX;
      } else {
        $this->maxFee = $this->config['MaximumFee'];
      }
      if ((float)$this->config['MaxRange'] === 0.0) {
        $this->maxRange = PHP_INT_MAX;
      } else {
        $this->maxRange = $this->config['MaxRange'];
      }
      // if ticket_index is not null then this is an update
      // query the database to determine if a price change is needed
      if ($this->ticket_index !== NULL) {
        $data['method'] = 'GET';
        $data['endPoint'] = 'tickets';
        $data['formKey'] = $this->formKey;
        $data['queryParams'] = [];
        $data['queryParams']['filter'] = [ ['Resource'=>'ticket_index', 'Filter'=>'eq', 'Value'=>$this->ticket_index] ];
        if (!$ticketQuery = self::createQuery($data)) {
          $temp = $this->error;
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return FALSE;
        }
        $testTicket = self::callQuery($ticketQuery);
        if ($testTicket === FALSE) {
          $temp = $this->error;
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return FALSE;
        }
        if ($testTicket[0]['Contract'] === 1 && $testTicket[0]['RunNumber'] !== 0) {
          $contractRunQueryData['endPoint'] = 'contract_runs';
          $contractRunQueryData['method'] = 'GET';
          $contractRunQueryData['formKey'] = $this->formKey;
          $contractRunQueryData['queryParams'] = [];
          $contractRunQueryData['queryParams']['filter'] = [ ['Resource'=>'RunNumber', 'Filter'=>'eq', 'Value'=>$testTicket[0]['RunNumber']] ];
          if (!$contractRunQuery = self::createQuery($contractRunQueryData)) {
            $temp = $this->error;
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
          $contractRunQueryResult = self::callQuery($contractRunQuery);
          if ($contractRunQueryResult === FALSE) {
            $temp = $this->error;
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
          if (!empty($contractRunQueryResult[0])) {
            foreach($contractRunQueryResult[0] as $key => $value) {
              $testTicket[0][$key] = $value;
            }
          }
        }
        $originalTicket = self::recursive_santizer($testTicket[0]);
        $this->PriceOverride = (isset($originalTicket['PriceOverride'])) ? $originalTicket['PriceOverride'] : 0;
        // If the neither address has changed set flag to prevent recalculating the price
        if ($this->pAddress1 . $this->pAddress2 . $this->pCountry === $originalTicket['pAddress1'] . $originalTicket['pAddress2'] . $originalTicket['pCountry'] && $this->dAddress1 . $this->dAddress2 . $this->dCountry === $originalTicket['dAddress1'] . $originalTicket['dAddress2'] . $originalTicket['dCountry']) {
          $this->PriceOverride = 1;
          $this->TicketBase = $originalTicket['TicketBase'];
        }
        $this->Contract = $originalTicket['Contract'];
      }

      if ($this->PriceOverride !== 1) {
        self::getTicketBase();
      }
      switch ($this->Charge) {
        case 1:
          $this->RunPrice = round(($this->TicketBase * $this->config['OneHour']), 2, PHP_ROUND_HALF_UP);
        break;
        case 2:
          $this->RunPrice = round(($this->TicketBase * $this->config['TwoHour']), 2, PHP_ROUND_HALF_UP);
        break;
        case 3:
          $this->RunPrice = round(($this->TicketBase * $this->config['ThreeHour']), 2, PHP_ROUND_HALF_UP);
        break;
        case 4:
          $this->RunPrice = round(($this->TicketBase * $this->config['FourHour']), 2, PHP_ROUND_HALF_UP);
        break;
        case 5:
          $this->RunPrice = $this->TicketBase;
        break;
       case 6:
          $this->RunPrice = $this->TicketBase * 2;
        break;
        case 7:
          $this->TicketBase = $this->config['DedicatedRunRate'];
          $this->RunPrice = 0;
        break;
        case 8:
          $this->RunPrice = round(($this->TicketBase * $this->config['DeadRun']), 2, PHP_ROUND_HALF_UP);
        break;
        case 9:
          // credit will currently not be a case here
        break;
        default:
          $this->RunPrice = $this->TicketBase;
        break;
      }
      if ($this->dryIce === 1) {
        $this->diPrice = $this->config['diPrice'] * $this->diWeight;
        $this->TicketPrice = $this->RunPrice + $this->diPrice;
      } else {
        $this->diPrice = 0;
        $this->TicketPrice = $this->RunPrice;
      }
      return TRUE;
    }

    private function getTicketBase() {
      if (strlen($this->pCountry) === 2) {
        $this->pCountry = self::countryFromAbbr($this->pCountry);
      }
      if (strlen($this->dCountry) === 2) {
        $this->dCountry = self::countryFromAbbr($this->dCountry);
      }
      $addy1 = "{$this->pAddress1} {$this->pAddress2}, {$this->pCountry}";
      $addy2 = "{$this->dAddress1} {$this->dAddress2}, {$this->dCountry}";
      // Load the Geocoder
      $this->geocoder = new \Geocoder\ProviderAggregator();
      $this->guzzle = new GuzzleClient($this->guzzleConfig);
      $this->adapter  = new GuzzleAdapter($this->guzzle);
      $this->dumper = new \Geocoder\Dumper\GeoJson();
      $geoProviders = json_decode($this->config['Geocoders']);
      if (json_last_error() !== JSON_ERROR_NONE) {
        if ($this->enableLogging === TRUE) {
          $this->error = 'getTicketBase failure line ' . __line__ . '. ' . json_last_error_msg();
          $this->writeLoop();
        }
        return $this->TicketBase = 0;
      }
      // Don't test for providers that require a map if none is available
      $exclude = ($this->mapAvailable === TRUE) ? [] : ['GoogleMaps'];
      $chainProviders = [];
      foreach ($geoProviders as $key => $value) {
        $providerIndex = $newProvider = NULL;
        for ($i = 0; $i < count($this->providers); $i++) {
          if (strtolower($this->providers[$i]) === strtolower(preg_replace('/\s+/', '', $key)) && !in_array($this->providers[$i], $exclude)) {
            $providerIndex = $i;
          }
        }
        if ($providerIndex !== NULL) {
          $testClass = "\Geocoder\Provider\\{$this->providers[$providerIndex]}\\{$this->providers[$providerIndex]}";
          if (class_exists($testClass)) {
            if (count($value) > 1) {
              try {
                $newProvider = new $testClass($this->adapter, $value[1], $value[0]);
              } catch(Exception $e) {
                if ($this->enableLogging !== FALSE) {
                  $this->error = "Geocoder Error {$e->getMessage()}";
                  self::writeLoop();
                }
                $newProvider = NULL;
              }
              if ($newProvider !== NULL) $chainProviders[] = $newProvider;
            } else {
              try {
                $newProvider = new $testClass($this->adapter, $value[0]);
              } catch(Exception $e) {
                if ($this->enableLogging !== FALSE) {
                  $this->error = "Geocoder Error {$e->getMessage()}";
                  self::writeLoop();
                }
                $newProvider = NULL;
              }
            }
            if ($newProvider !== NULL) $chainProviders[] = $newProvider;
          }
        }
      }
      if (empty($chainProviders)) {
        if ($this->enableLogging !== FALSE) {
          $this->error = 'No geocoder providers available';
          self::writeLoop();
        }
        return $this->TicketBase = 0;
      }
      $this->chain = new \Geocoder\Provider\Chain\Chain($chainProviders);

      $this->geocoder->registerProvider($this->chain);
      // Use GeoCode to get the coordinates of the two addresses
      // get the geocoded objects
      try {
        $this->geocoder->geocodeQuery(GeocodeQuery::create($addy1));
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        if ($this->ticketBaseRetries < 5) {
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        }
        return $this->TicketBase = 0;
      }
      if (!$this->result1obj = $this->geocoder->geocodeQuery(GeocodeQuery::create($addy1))->first()) {
        if ($this->ticketBaseRetries < 5) {
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        } else {
          $this->error = 'No address1 result from geocoder';
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return $this->TicketBase = 0;
        }
      }
      try {
        $this->geocoder->geocodeQuery(GeocodeQuery::create($addy2));
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        if ($this->ticketBaseRetries < 5) {
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        }
        return $this->TicketBase = 0;
      }
      if (!$this->result2obj = $this->geocoder->geocodeQuery(GeocodeQuery::create($addy2))->first()) {
        if ($this->ticketBaseRetries < 5) {
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        } else {
          $this->error = 'No address2 result from geocoder';
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return $this->TicketBase = 0;
        }
      }
      // dump the objects as json strings and encode them as array
      $this->result1 = json_decode($this->dumper->dump($this->result1obj));
      $this->result2 = json_decode($this->dumper->dump($this->result2obj));

      $this->loc1['lat'] = $this->result1->geometry->coordinates[1];
      $this->loc1['lng'] = $this->result1->geometry->coordinates[0];
      $this->loc2['lat'] = $this->result2->geometry->coordinates[1];
      $this->loc2['lng'] = $this->result2->geometry->coordinates[0];
      $this->center['lat'] = ($this->loc1['lat'] + $this->loc2['lat']) / 2;
      $this->center['lng'] = ($this->loc1['lng'] + $this->loc2['lng']) / 2;
      // Find the distance between the two addresses
      // Test that pick up and delivery are within the defined distance of the home location
      for ($i = 0; $i < 3; $i++) {
        $this->rangeFlag = $i;
        self::rangeTest();
      }
      // Distance in miles
      if ($this->config['WeightsMeasures'] == 0) {
        $this->rangeVal = round($this->rangeVal * 0.621371192, 2);
        $this->pRangeTest = round($this->pRangeTest * 0.621371192, 2);
        $this->dRangeTest = round($this->dRangeTest * 0.621371192, 2);
      }
      $this->billingCode = round($this->rangeVal / $this->config['RangeIncrement'], 0, PHP_ROUND_HALF_UP);
      $this->TicketBase = round($this->config['BaseTicketFee'] * pow($this->config['PriceIncrement'], $this->billingCode), 2, PHP_ROUND_HALF_DOWN);
      // Solve for ticketPrice
      if ($this->Contract == 0 && $this->BillTo !== NULL) {
        $this->TicketBase = round(($this->TicketBase * $this->config['GeneralDiscount'][$this->BillTo]), 2, PHP_ROUND_HALF_DOWN);
      } elseif ($this->Contract == 1 && $this->BillTo !== NULL) {
        $this->TicketBase = round(($this->TicketBase * $this->config['ContractDiscount'][$this->BillTo]), 2, PHP_ROUND_HALF_DOWN);
      }
      if ($this->TicketBase > $this->maxFee) {
        $this->TicketBase = $this->maxFee;
      }
      return TRUE;
    }

    private function solveDedicatedRunPrice() {
      // Define the start and end times based on return signature request
      if ($this->d2SigReq === 1) {
        if ($this->d2TimeStamp !== $this->tTest) {
          try {
            $start = new \dateTime($this->pTimeStamp, $this->timezone);
          } catch (Exception $e) {
            $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            echo $this->error;
            return FALSE;
          }
          try {
            $end = new \dateTime($this->d2TimeStamp, $this->timezone);
          } catch (Exception $e) {
            $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            echo $this->error;
            return FALSE;
          }
        } else {
          return FALSE;
        }
      } else {
        if ($this->dTimeStamp !== $this->tTest) {
          try {
            $start = new \dateTime($this->pTimeStamp, $this->timezone);
          } catch (Exception $e) {
            $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            echo $this->error;
            return FALSE;
          }
          try {
            $end = new \dateTime($this->dTimeStamp, $this->timezone);
          } catch (Exception $e) {
            $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            echo $this->error;
            return FALSE;
          }
        } else {
          return FALSE;
        }
      }
      $interval = date_diff($start, $end);
      $seconds = $interval->days*86400 + $interval->h*3600 + $interval->i*60 + $interval->s;
      $rate = $this->TicketBase / 3600;
      $payload['RunPrice'] = self::number_format_drop_zero_decimals(($seconds * $rate), 2);
      $payload['TicketPrice'] = self::number_format_drop_zero_decimals(($payload['RunPrice'] + $this->diPrice), 2);
      $updateTicketPriceData['endPoint'] = 'tickets';
      $updateTicketPriceData['method'] = 'PUT';
      $updateTicketPriceData['formKey'] = $this->formKey;
      $updateTicketPriceData['primaryKey'] = $this->ticket_index;
      $updateTicketPriceData['payload'] = $payload;
      $updateTicketPriceData['queryParams'] = [];
      if (!$updateTicketPrice = self::createQuery($updateTicketPriceData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      $updateTicketPriceResult = self::callQuery($updateTicketPrice);
      if ($updateTicketPriceResult === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      return TRUE;
    }

    private function buildLocationList() {
      if ($this->organizationFlag === TRUE) $this->ClientID = implode(',', array_keys($this->members));
      $tempClients = $uniqueTest = array();
      $locationQueryData['queryParams']['include'] = ['pClient', 'dClient', 'pAddress1', 'pAddress2', 'pCountry', 'dAddress1', 'dAddress2', 'dCountry', 'pDepartment', 'dDepartment', 'pContact', 'dContact'];
      $locationQueryData['queryParams']['filter'] = ($this->ulevel === 'dispatch' || $this->ulevel === 'driver') ? [] : [ ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>$this->ClientID] ];
      $locationQueryData['method'] = 'GET';
      $locationQueryData['endPoint'] = 'tickets';
      $locationQueryData['formKey'] = $this->formKey;
      if (!$locationQuery = self::createQuery($locationQueryData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      //Pull the data to make the datalists
      $locationData = self::callQuery($locationQuery);
      if ($locationData === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      //Only proceed if a record is returned
      if (count($locationData) === 0) {
        return $this->locationList = 'empty';
      } else {
        // Filter the locationList first to be unique then using $this->ignoreValues and $this->clientAddressExceptions
        foreach ($locationData as $location) {
          if (!in_array(strtolower($location['pClient']), $this->ignoreValues)) {
            $test = self::decode($location['pClient']) . ' ' . self::decode($location['pDepartment']) . ' ' . self::decode($location['pAddress1']) . ' ' . self::decode($location['pAddress2']) . ' ' . $location['pCountry'];
            $exceptionTest = self::decode($location['pClient']) . ', ' . self::decode($location['pAddress1']);
            if (!in_array($test, $uniqueTest) && !in_array($exceptionTest, $this->clientAddressExceptions)) {
              $uniqueTest[] = $test;
              $tempClients[] = ['client'=>self::decode($location['pClient']), 'department'=>self::decode($location['pDepartment']), 'contact'=>self::decode($location['pContact']), 'address1'=>self::decode($location['pAddress1']), 'address2'=>self::decode($location['pAddress2']), 'country'=>$location['pCountry']];
            }
          }
          if (!in_array(strtolower($location['dClient']), $this->ignoreValues)) {
            $test = self::decode($location['dClient']) . ' ' . self::decode($location['dDepartment']) . ' ' . self::decode($location['dAddress1']) . ' ' . self::decode($location['dAddress2']) . ' ' . $location['dCountry'];
            $exceptionTest = self::decode($location['dClient']) . ' ' . self::decode($location['dAddress1']);
            if (!in_array($test, $uniqueTest) && !in_array($exceptionTest, $this->clientAddressExceptions)) {
              $uniqueTest[] = $test;
              $tempClients[] = ['client'=>self::decode($location['dClient']), 'department'=>self::decode($location['dDepartment']), 'contact'=>self::decode($location['dContact']), 'address1'=>self::decode($location['dAddress1']), 'address2'=>self::decode($location['dAddress2']), 'country'=>$location['dCountry']];
            }
          }
        }
        // Sort $tempClients and reset the keys to the new order before encoding
        return $this->locationList = (count($tempClients) === 0) ? 'empty' : self::encodeURIComponent(json_encode(self::user_array_sort($tempClients, 'client')));
      }
    }

    private function fetchDrivers() {
      $tempDriver = [];
      // Pull the data to make the datalists
      $driverQueryData['queryParams'] = [];
      $driverQueryData['queryParams']['include'] = ['DriverID', 'FirstName', 'LastName'];
      $driverQueryData['queryParams']['filter'] = [ ['Resource'=>'Deleted', 'Filter'=>'neq', 'Value'=>1] ];
      $driverQueryData['method'] = 'GET';
      $driverQueryData['endPoint'] = 'drivers';
      $driverQueryData['formKey'] = $this->formKey;
      if (!$driverQuery = self::createQuery($driverQueryData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      $tempDriver = self::callQuery($driverQuery);
      if ($tempDriver === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        $this->driverList = 'empty';
        return FALSE;
      }
      // Only proceed if a record is returned
      if (empty($tempDriver)) {
        return $this->driverList = 'empty';
      }
      return $this->driverList = self::encodeURIComponent(json_encode($tempDriver));
    }

    private function fetchClients() {
      $tempClients = $repeatList = $nrList = [];
      $clientQueryData['queryParams'] = [];
      $clientQueryData['queryParams']['include'] = ['ClientID', 'ClientName', 'Department', 'RepeatClient'];
      $clientQueryData['queryParams']['filter'] = [ ['Resource'=>'Deleted', 'Filter'=>'neq', 'Value'=>1] ];
      $clientQueryData['method'] = 'GET';
      $clientQueryData['endPoint'] = 'clients';
      $clientQueryData['formKey'] = $this->formKey;
      if (!$clientQuery = self::createQuery($clientQueryData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      $tempClients = self::callQuery($clientQuery);
      if ($tempClients === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      // Only proceed if a record is returned
      if (empty($tempClients)) {
        return $this->clientList = $this->tClientList = 'empty';
      }
      for ($i = 0; $i < count($tempClients); $i++) {
        if ($tempClients[$i]['RepeatClient'] === 1) {
          $repeatList[] = $tempClients[$i];
        } else {
          $nrList[] = $tempClients[$i];
        }
      }
      $this->clientList = (empty($repeatList)) ? 'empty' : self::encodeURIComponent(json_encode($repeatList));
      $this->tClientList = (empty($nrList)) ? 'empty' : self::encodeURIComponent(json_encode($nrList));
      return TRUE;
    }

    private function buildDatalists() {
      $returnData = '';
      if ($this->userType !== 'client') {
        if ($this->userType === 'dispatch' || $this->CanDispatch === 2) {
          if ($this->driverList == NULL) {
            self::fetchDrivers();
          }
          if ($this->driverList !== 'empty' && $this->driverList !== NULL) {
            $returnData .= '<datalist id="drivers">';
            foreach (json_decode(urldecode($this->driverList), TRUE) as $driver) {
              // Set DispatchedTo for display if the ticket_index is not NULL
              if ($this->ticket_index !== NULL) {
                if ($this->DispatchedTo == $driver['DriverID']) {
                  $this->DispatchedTo = ($driver['LastName'] === NULL || $driver['LastName'] === '') ? htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] : htmlentities($driver['FirstName']) . ' ' . htmlentities($driver['LastName']) . '; ' . $driver['DriverID'];
                }
              }
              $driverName = ($driver['LastName'] === NULL || $driver['LastName'] === '') ? htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] : htmlentities($driver['FirstName']) . ' ' . htmlentities($driver['LastName']) . '; ' . $driver['DriverID'];
              $returnData .= "<option value=\"{$driverName}\">{$driverName}</option>";
            }
            $returnData .= '</datalist>';
            if ($this->userType === 'driver') {
              $returnData .= '<datalist id="receivers">';
              foreach (json_decode(urldecode($this->driverList), TRUE) as $driver) {
                $driverName = ($driver['LastName'] === NULL || $driver['LastName'] === '') ? htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] : htmlentities($driver['FirstName']) . ' ' . htmlentities($driver['LastName']) . '; ' . $driver['DriverID'];
                $returnData .= ($driver['DriverID'] !== $_SESSION['DriverID']) ? "<option value=\"{$driverName}\">{$driverName}</option>" : '';
              }
              $returnData .= '</datalist>';
            }
          }
        }
        if ($this->userType === 'driver' && $this->CanDispatch === 0) {
          if ($this->driverList !== 'empty' && $this->driverList == NULL) {
            self::fetchDrivers();
          }
          if ($this->driverList !== 'empty' && $this->driverList != NULL) {
            $returnData .= '<datalist id="receivers">';
            foreach (json_decode(urldecode($this->driverList), TRUE) as $driver) {
              $driverName = ($driver['LastName'] == NULL) ? htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] : htmlentities($driver['FirstName']) . ' ' . htmlentities($driver['LastName']) . '; ' . $driver['DriverID'];
              $returnData .= ($driver['DriverID'] !== $_SESSION['DriverID']) ? "<option value=\"{$driverName}\">{$driverName}</option>" : '';
            }
            $returnData .= '</datalist>';
          }
        }
        if ($this->clientList === NULL) {
          if (!self::fetchClients()) {
            return $this->error;
          }
        }
        if ($this->clientList !== 'empty' && $this->clientList !== NULL) {
          $returnData .= '<datalist id="clients">';
          foreach (json_decode(urldecode($this->clientList), TRUE) as $client) {
            // Set BillTo for display if the ticket_index is not NULL
            if ($this->ticket_index !== NULL) {
              if ($this->BillTo == $client['ClientID']) {
                $this->BillTo = ($client['Department'] === NULL || $client['Department'] === '') ? $client['ClientName'] . '; ' . $client['ClientID'] : $client['ClientName'] . ', ' . $client['Department'] . '; ' . $client['ClientID'];
              }
            }
            $clientVal = ($client['Department'] === NULL || $client['Department'] === '') ? $client['ClientName'] . '; ' . $client['ClientID'] : $client['ClientName'] . ', ' . $client['Department'] . '; ' . $client['ClientID'];
            $returnData .= '<option value="' . $clientVal . '">' . html_entity_decode($clientVal) . '</option>';
          }
          $returnData .= '</datalist>';
        }
        if ($this->tClientList !== 'empty' && $this->tClientList !== NULL) {
          $returnData .= '<datalist id="t_clients">
            <option value="new">new</option>';
          foreach (json_decode(urldecode($this->tClientList), TRUE) as $tclient) {
            $tclientVal = ($tclient['Department'] === NULL || $tclient['Department'] === '') ? $tclient['ClientName'] . '; t' . $tclient['ClientID'] : $tclient['ClientName'] . ', ' . $tclient['Department'] . '; t' . $client['ClientID'];
            $returnData .= "<option value=\"{$tclientVal}\">" . html_entity_decode($tclientVal) . '</option>';
          }
          $returnData .= '</datalist>';
        }
      }
      if ($this->config['InternationalAddressing'] !== 0) {
        $returnData .= '<datalist id="countries">';
        $lines = file( __dir__ . '/countryList.php', FILE_IGNORE_NEW_LINES);
        // countryList.php was originally supposed to echo these values so start at line two
        for ($i=2; $i<count($lines) - 1; $i++) {
          $returnData .= $lines[$i];
        }
        $returnData .= '</datalist>';
      }
      if ($this->locationList === 'empty' || $this->locationList === NULL) {
        return $returnData;
      }
      $clients = $departments = $contacts = $addy1s = $addy2s = [];
      $locations = array_values(json_decode(urldecode($this->locationList), TRUE));
      foreach ($locations as $location) {
        foreach ($location as $key => $value) {
          switch ($key) {
            case 'client':
              if (!in_array($value, $clients)) {
                $clients[] = $value;
              }
            break;
            case 'department':
              if (!in_array($value, $departments)) {
                $departments[] = $value;
              }
            break;
            case 'address1':
              if (!in_array($value, $addy1s)) {
                $addy1s[] = $value;
                $addy2s[] = $location['address2'];
              }
            break;
            case 'contact':
              if (!in_array($value, $contacts)) {
                $contacts[] = $value;
              }
            break;
          }
        }
      }
      $returnData .= '<datalist id="clientName">';

      foreach ($clients as $client) {
        $returnData .= '<option value="' . htmlentities($client) . '">' . html_entity_decode($client) . '</option>';
      }

      $returnData .= '</datalist>
        <datalist id="departments">';

      foreach ($departments as $department) {
        $returnData .= '<option value="' . htmlentities($department) . '">' . html_entity_decode($department) . '</option>';
      }

      $returnData .= '</datalist>
        <datalist id="addy1">';

      for($i = 0; $i < count($addy1s); $i++) {
        // Use htmlentities to ensure that addresses with double quotes are displayed properly
        $returnData .= '<option value="' . htmlentities($addy1s[$i]) . '" data-value="' . $i . '">' . html_entity_decode($addy1s[$i]) . '</option>';
      }

      $returnData .= '</datalist>
        <datalist id="addy2">';

      for ($i = 0; $i < count($addy2s); $i++) {
        $returnData .= "<option value=\"{$addy2s[$i]}\" data-value=\"{$i}\">" . html_entity_decode($addy2s[$i]) . '</option>';
      }

      $returnData .= '</datalist>
        <datalist id="contacts">';

      foreach ($contacts as $contact) {
        $returnData .= "<option value=\"{$contact}\">" . html_entity_decode($contact) . '</option>';
      }

      $returnData .= '</datalist>';

      return $returnData;
    }

    private function buildSelectElement() {
      $locations = json_decode(urldecode($this->locationList), TRUE);
      $returnData = '';
      if ($this->locationList === 'empty') {
        return FALSE;
      }
      $returnData = "<select name=\"{$this->selectID}\" class=\"clientSelect\" form=\"request\" disabled>";
      for ($i=0; $i<count($locations); $i++) {
        $returnData .= '<option data-value="' . $i . '" value="' . htmlentities($locations[$i][strtolower(substr($this->selectID, 1))]) . '">' . html_entity_decode($locations[$i][strtolower(substr($this->selectID, 1))]) . '</option>';
      }
      $returnData .= '</select>';
      return $returnData;
    }

    private function testTicketNumber() {
      $ticketNumberQueryData['method'] = 'GET';
      $ticketNumberQueryData['endPoint'] = 'tickets';
      $ticketNumberQueryData['formKey'] = $this->formKey;
      $ticketNumberQueryData['queryParams']['include'] = ['TicketNumber'];
      if (!$ticketNumberQuery = self::createQuery($ticketNumberQueryData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      $this->ticketNumberList = self::callQuery($ticketNumberQuery);
      if ($this->ticketNumberList === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      while (self::recursive_array_search($this->TicketNumber, $this->ticketNumberList) !== FALSE) {
        $this->TicketNumber++;
      }
      return TRUE;
    }

    public function sendEmail() {
      if ($this->step === 'pickedUp') {
        // send email on 1, 3, 5, 7
        return $this->EmailConfirm % 2 !== 0;
      } elseif ($this->step === 'delivered') {
        // send email on 2, 3, 6, 7
        switch ($this->EmailConfirm) {
            case 2:
            case 3:
            case 6:
            case 7: return TRUE;
            default: return FALSE;
          }
      } elseif ($this->step === 'returned') {
        // send email on 4, 5, 6, 7
        return $this->EmailConfirm > 3;
      } else {
        return FALSE;
      }
    }

    private function queryTicket() {
      $ticketQueryResult = array();
      // When querying multiple tickets $this->ticket_index will be a comma separated list of indexes. Test for a comma and adjust the filter accordingly.
      $queryFilter = (strpos($this->ticket_index, ',') === FALSE) ? 'eq' : 'in';
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['formKey'] = $this->formKey;
      $ticketQueryData['queryParams']['filter'] = [ ['Resource'=>'ticket_index', 'Filter'=>$queryFilter, 'Value'=>$this->ticket_index] ];
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      $ticketQueryResult = self::callQuery($ticketQuery);
      if ($ticketQueryResult === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      if (count($ticketQueryResult) === 1) {
        $this->sanitized = self::recursive_santizer($ticketQueryResult[0]);
        foreach ($this->sanitized as $key => $value) {
          foreach ($this as $k => $v) {
            if (strtolower($key) === strtolower($k) && $k !== 'sanitized') {
              if (strtolower($key) === 'transfers') {
                $this->$k = ($value === NULL || $value === '') ? NULL : json_decode(html_entity_decode($value));
              } elseif (strtolower($key) === 'transferstate' || strtolower($key) === 'pendingreceiver') {
                $this->$k = ($this->processTransfer === TRUE) ? $v : $value;
                $tempkey = strtolower(substr($k, 0, 1)) . substr($k, 1) . 'Old';
                $this->$tempkey = $value;
              } else {
                $this->$k = $value;
              }
            }
          }
        }
      } else {
        $this->sanitized = self::recursive_santizer($ticketQueryResult);
      }
      return TRUE;
    }

    private function processEmail() {
      if (!isset($this->emailConfig)) {
        return FALSE;
      }
      $mail = new PHPMailer(TRUE);
      try {
        //Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $this->emailConfig['smtpHost'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth = TRUE; // Enable SMTP authentication
        $mail->Username = $this->emailConfig['fromAddress']; // SMTP username
        $mail->Password = $this->emailConfig['password']; // SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $this->emailConfig['port']; // TCP port to connect to
        //Recipients
        $mail->setFrom($this->emailConfig['emailAddress'], $this->emailConfig['fromName']);
        $mail->addAddress($this->EmailAddress);     // Add a recipient
        $mail->addBCC($this->emailConfig['BCCAddress']);
        /* $mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('info@example.com', 'Information');
        $mail->addCC('cc@example.com');
        $mail->addBCC('bcc@example.com');
        //Attachments
        $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        //Content */
        $mail->isHTML(TRUE);                                  // Set email format to HTML
        $mail->Subject = 'Update';
        $mail->Body    = "Delivery {$this->TicketNumber} has been {$this->stepMarker}.<br><br>This message is automatically generated. Please do not respond.<br><br>If you believe that you've received this message in error or have questions or comments please contact {$this->myInfo['Name']} by phone at <a href=\"tel:{$this->myInfo['Telephone']}\">{$this->myInfo['Telephone']}</a> or by email at <a href=\"mailto:{$this->myInfo['EmailAddress']}\">{$this->myInfo['EmailAddress']}</a>";
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        $mail->send();
        //echo 'Message has been sent';
      } catch (Exception $e) {
        if ($this->enableLogging !== FALSE) {
          $this->error = 'Email Not Sent: ' . $mail->ErrorInfo;
          self::writeLoop();
        }
      }
    }

    //Ticket Charge values
    protected function ticketCharge($charge) {
      switch ($charge) {
        case 0:
          return 'Canceled';
        case 1:
          return '1 Hour';
        case 2:
          return '2 Hour';
        case 3:
          return '3 Hour';
        case 4:
          return '4 Hour';
        case 5:
          return 'Routine';
        case 6:
          return 'Round Trip';
        case 7:
          return 'Dedicated Run';
        case 8:
          return 'Dead Run';
        case 9:
          return 'Credit';
        default:
          return FALSE;
      }
    }

    public function invoiceBody() {
      if ($this->pDepartment == NULL) {
        $pClientDisplay = $this->pClient;
      } else {
        $pClientDisplay = $this->pClient . ' | ' . $this->pDepartment;
      }

      if ($this->dDepartment == NULL) {
        $dClientDisplay = $this->dClient;
      } else {
        $dClientDisplay = $this->dClient . ' | ' . $this->dDepartment;
      }
      // Define the dry ice display
      if ($this->dryIce === 1) {
        $answerIce = "Weight: {$this->number_format_drop_zero_decimals($this->diWeight, 3)}{$this->weightMarker}  |   Price: <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->diPrice, 2)}";
        $labelIce = 'Dry Ice:';
      } else {
        $answerIce = '&nbsp;';
        $labelIce= '&nbsp;';
      }
      // Define the date display
      if ($this->Multiplier > 1) {
        $date = date('M Y', strtotime($this->ReceivedDate));
      } else {
        $date = date('m/d/y', strtotime($this->ReceivedDate));
      }
      return "
              <tr>
                <td>{$date}</td>
                <td>{$this->TicketNumber}</td>
                <td>{$this->ticketCharge($this->Charge)}</td>
                <td>P.U.:<br><hr>D.O.:<br><hr>{$labelIce}</td>
                <td>{$pClientDisplay}<br>{$this->pAddress1}<br><hr>{$dClientDisplay}<br>{$this->dAddress1}<br><hr>{$answerIce}</td>
                <td><span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->negParenth($this->number_format_drop_zero_decimals($this->RunPrice, 2))}</td>
                <td>{$this->Multiplier}</td>
                <td><span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->negParenth($this->number_format_drop_zero_decimals($this->TicketPrice, 2))}</td>
              </tr>";
    }

    public function regenTicket() {
      //Prepare the received, pick up, drop of, and return time stamps for display
      $hideTableHead = '';
      if ($this->forDisatch === TRUE) {
        $dispatchValue = '';
        if ($this->driverID !== 0) {
          $dispatchValue = "{$this->DriverName}; {$this->driverID}";
        }
        $this->dispatchForm = "
          <form id=\"dispatchForm{$this->ticket_index}\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
            <input type=\"hidden\" name=\"formKey\" class=\"formKey\" value=\"{$this->formKey}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"step\" class=\"step\" value=\"dispatched\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->EmailConfirm}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->EmailAddress}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"dispatchedBy\" class=\"dispatchedBy\" value=\"{$this->DispatchedBy}\" form=\"dispatchForm{$this->ticket_index}\" />
            <button type=\"submit\" class=\"dTicket\" form=\"dispatchForm{$this->ticket_index}\">Dispatch</button>
            <label for=\"dispatch{$this->ticket_index}\" class=\"hide\">Dispatch To: </label>
            <input list=\"drivers\" id=\"dispatch{$this->ticket_index}\" name=\"dispatchedTo\" class=\"dispatchedTo\" value=\"{$dispatchValue}\" form=\"dispatchForm{$this->ticket_index}\" />
          </form>
        ";
        $this->driverDatalist = '<datalist id="drivers">';
        foreach (json_decode(urldecode($this->driverList), TRUE) as $driver) {
          $driverName = ($driver['LastName'] == NULL) ? $driver['FirstName'] . '; ' . $driver['DriverID'] : $driver['FirstName'] . ' ' . $driver['LastName'] . '; ' . $driver['DriverID'];
          $this->driverDatalist .= "<option value=\"{$driverName}\">{$driverName}</option>";
        }
        $this->driverDatalist .= '</datalist>';
        $hideTableHead = 'class="hide"';
      }
      if ($this->ticketEditor === TRUE) $hideTableHead = 'class="hide"';
      try {
        $rDate = new \dateTime($this->ReceivedDate, $this->timezone);
        $rDateDisplay = $rDate->format('d M Y \a\t g:i A');
	    } catch (Exception $e) {
        $rDateDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
	    }
      if ($this->pTimeStamp !== $this->tTest) {
        try {
          $pDate = new \dateTime($this->pTimeStamp, $this->timezone);
          $pTimeStampDispay = $pDate->format('d M Y \a\t g:i A');
        } catch (Exception $e) {
          $pTimeStampDispay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
        }
      } else {
        $pTimeStampDispay = 'Not Available<span class="hide">Error: None</span>';
      }
      if ($this->dTimeStamp !== $this->tTest) {
        try {
          $dDate = new \dateTime($this->dTimeStamp, $this->timezone);
          $dTimeStampDisplay = $dDate->format('d M Y \a\t g:i A');
        } catch (Exception $e) {
          $dTimeStampDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
        }
      } else {
        $dTimeStampDisplay = 'Not Available<span class="hide">Error: None</span>';
      }
      if (($this->Charge === 6 || $this->Charge === 7) && $this->d2TimeStamp !== $this->tTest) {
        try {
          $d2Date = new \dateTime($this->d2TimeStamp, $this->timezone);
          $d2TimeStampDisplay = $d2Date->format('d M Y \a\t g:i A');
        } catch (Exception $e) {
          $d2TimeStampDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
        }
      } elseif ($this->Charge !== 6) {
        if ($this->Charge === 7) {
          $d2TimeStampDisplay = ($this->d2SigReq === 0) ? 'Not Scheduled' : 'Not Available';
        } else {
          $d2TimeStampDisplay = 'Not Scheduled';
        }
      } else {
        $d2TimeStampDisplay = 'Not Available<span class="hide">Error: None</span>';
      }
      //Check to see if a name is listed in the requestedBy field
      $requestedByDisplay = ($this->RequestedBy === NULL || $this->RequestedBy === '') ? 'Not On File' : $this->RequestedBy;
      //Check to see if the ticket has been billed
      if ($this->InvoiceNumber !== '-') {
        if ($this->ulevel < 2) {
          if ($this->ulevel === 1) {
            $url = 'invoices';
          } elseif ($this->ulevel === 0) {
            $url = 'orgInvoices';
          } else {
            $url = 'error';
          }
          if ($this->organizationFlag === TRUE) {
            $this->memberInput = "<input type=\"hidden\" name=\"clientID[]\" value=\"{$this->BillTo}\" />";
          }
          $billed = "
          <form class=\"noPrint\" action=\"{$url}\" method=\"post\">
            <input type=\"hidden\" name=\"formKey\" value=\"{$this->formKey}\" />
            <input type=\"hidden\" name=\"clientID\" value=\"{$this->ClientID}\" />
            <input type=\"hidden\" name=\"endPoint\" value=\"invoices\" />
            <input type=\"hidden\" name=\"display\" value=\"invoice\" />
            <input type=\"hidden\" name=\"invoiceNumber\" value=\"{$this->InvoiceNumber}\" />
            {$this->memberInput}
            <button type=\"submit\" class=\"invoiceQuery\">{$this->InvoiceNumber}</button>
          </form>
          <span class=\"printOnly\">{$this->InvoiceNumber}</span>";
        } else {
          $billed = $this->InvoiceNumber;
        }
      } else {
        $billed = 'Not Billed';
      }
      $answerIce = "
        <table class=\"wide\">
          <thead>
            <tr>
              <th class=\"pullLeft\">Dry Ice:</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class=\"center\">{$this->number_format_drop_zero_decimals($this->diWeight, 3)}{$this->weightMarker}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
            </tr>
          </tbody>
        </table>
      ";
      $answerIce2 = "
	        <td><span class=\"bold\">Dry Ice Price:</span> <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->diPrice, 2)}</td>";
      if (($this->Notes !== NULL && $this->Notes !== '') || $this->forDisatch === TRUE) {
        $readonlyNotes = ($this->forDisatch === TRUE) ? "form=\"dispatchForm{$this->ticket_index}\"" : 'readonly';
        $answerNotes = "
          <table class=\"wide\">
            <thead>
              <tr>
                <th class=\"pullLeft\">Notes:</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><textarea class=\"notes\" {$readonlyNotes} rows=\"3\">{$this->Notes}</textarea></td>
              </tr>
            </tbody>
  	      </table>
          ";
      } else {
        $answerNotes = '
          <table class="wide">
            <thead>
              <tr>
                <th class="pullLeft">Notes</th>
               </tr>
            </thead>
            <tbody>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </tbody>
  	      </table>
          ';
      }
      $answerContract1 = $answerContract2 = '';
      if ($this->Contract == 1) {
        $answerContract1 = '
	        <span class="bold">Repeats:</span>  ';
        $answerContract2 = (float)$this->Multiplier;
		  }
      $runPrice1 = $runPrice2 = '';
      if ($this->dryIce == 1 || $this->Contract == 1) {
        $runPrice1 = '
	        <span class="bold">Run Price:</span>
          ';
        $runPrice2 = "
		      <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->RunPrice, 2)}";
          // Reset the run price display if this is an incomplete dedicated run
          if ($this->Charge === 7 && (($this->d2SigReq === 1 && $this->d2TimeStamp === $this->tTest) || ($this->d2SigReq === 0 && $this->dTimeStamp === $this->tTest))) {
            $runPrice2 = 'Pending';
          }
      }
      // Set the ticket price display
      $ticketPriceDisplay = "<span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->negParenth($this->TicketPrice)}";
      // Reset the ticket price display if this is an incomplete dedicated run
      if ($this->Charge === 7 && (($this->d2SigReq === 1 && $this->d2TimeStamp === $this->tTest) || ($this->d2SigReq === 0 && $this->dTimeStamp === $this->tTest))) {
        $ticketPriceDisplay = 'Pending';
      }
      $pName = $this->pClient;
      $pName .= ($this->pDepartment == NULL) ? '<br>&nbsp;' : "<br>{$this->pDepartment}";
      $dName = $this->dClient;
      $dName .= ($this->dDepartment == NULL) ? '<br>&nbsp;' : "<br>{$this->dDepartment}";
      $pSigDisplay = $dSigDisplay = $d2SigDisplay = '';
      $sigTokens = ['pSig', 'dSig', 'd2Sig'];
      $tokenSet = [];
      foreach ($sigTokens as $token) {
        switch ($token) {
          case 'pSig':
            $label = 'Pick Up';
          break;
          case 'dSig':
            $label = 'Delivery';
          break;
          case 'd2Sig':
            $label = 'Return';
          break;
        }
        if ($this->$token !== NULL) {
          $fileType = "{$token}Type";
          $showSig = '<img src="data:' . $this->$fileType . ';base64,' . base64_decode($this->$token) . '" height="100" width="375" />';
        } else {
          $showSig = 'Image Not On File';
        }
        $tempProperty = $token.'Print';
        $signer = $this->$tempProperty;
        $tokenSet[$token . 'Display'] = "
          <tr class=\"sigPrint\">
            <td colspan=\"2\" class=\"pullLeft\">{$label} Signed For By: {$this->$tempProperty}</td>
          </tr>
          <tr class=\"sigImage\">
            <td colspan=\"2\" class=\"center\">
              {$showSig}
            </td>
          </tr>";
      }
      extract($tokenSet,EXTR_IF_EXISTS);
      $returnData =
        $this->driverDatalist .
        "<div class=\"tickets sortable\">
        <table>
          <tr {$hideTableHead}>
            <td colspan=\"2\" class=\"center\"><span class=\"imageSpan floatLeft\">{$this->headerLogo2}</span><span class=\"ticketHeadAddress medium\">{$this->config['ShippingAddress1']}<br>{$this->config['ShippingAddress2']}<br><span class=\"{$this->countryClass}\">{$this->config['ShippingCountry']}</span></span><span class=\"floatRight\">{$this->config['Telephone']}</span></td>
          </tr>
          <tr>
            <td>
              <table class=\"regenBilling\">
                <tr>
                  <td><span class=\"bold\">Ticket Number:</span> <span class=\"tNumDisplay\">{$this->TicketNumber}</span></td>
                </tr>
                <tr {$hideTableHead}>
                  <td><span class=\"bold\">Pick Up:</span> {$pTimeStampDispay}</td>
                </tr>
                <tr {$hideTableHead}>
                  <td><span class=\"bold\">Return:</span> {$d2TimeStampDisplay}</td>
                </tr>
                <tr>
                  <td><span class=\"bold\">Requested By:</span> {$requestedByDisplay}</td>
                </tr>
                <tr>
                  <td><span class=\"bold\">Charge:</span> {$this->ticketCharge($this->Charge)}</td>
                </tr>
                <tr>
                  <td>{$this->dispatchForm}</td>
                </tr>
                <tr {$hideTableHead}>
                  <td>{$runPrice1} {$runPrice2}</td>
                </tr>
                <tr>
                  <td></td>
                </tr>
              </table>
            </td>
            <td>
              <table class=\"regenBilling\">
                <tr>
                  <td><span class=\"bold\">Received:</span> {$rDateDisplay}</td>
                </tr>
                <tr {$hideTableHead}>
                  <td><span class=\"bold\">Drop Off:</span> {$dTimeStampDisplay}</td>
                </tr>
                <tr {$hideTableHead}>
                  <td><span class=\"bold\">Invoice:</span> {$billed}</td>
                </tr>
                <tr {$hideTableHead}>
                  <td>{$answerContract1} {$answerContract2}</td>
                </tr>
                <tr {$hideTableHead}>
                  <td><span class=\"bold\">Email Address:</span> {$this->EmailAddress}</td>
                </tr>
                <tr {$hideTableHead}>
                  {$answerIce2}
                </tr>
                <tr {$hideTableHead}>
                  <td><span class=\"bold\">Ticket Price:</span> <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->negParenth($this->number_format_drop_zero_decimals($this->TicketPrice, 2))}</td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td colspan=\"2\"><hr></td>
          </tr>
          <tr>
            <td>
              <table class=\"wide\">
                <tr>
                  <th colspan=\"2\" class=\"pullLeft\">Pick Up</th>
                </tr>
              <tr>
                <td class=\"ticketSpace\"></td>
                <td class=\"pullLeft\">{$this->decode($pName)}</td>
              </tr>
              <tr>
                <td class=\"ticketSpace\"></td>
                <td class=\"pullLeft\">{$this->decode($this->pAddress1)}<br>{$this->decode($this->pAddress2)}<br><span class=\"{$this->countryClass}\">{$this->countryFromAbbr($this->pCountry)}</span></td>
              </tr>
              <tr>
                <td class=\"ticketSpace pullRight\">Attn:</td>
                <td class=\"pullLeft\">{$this->pContact}</td>
              </tr>
              <tr>
                <td class=\"ticketSpace\"></td>
                <td class=\"pullLeft\">{$this->pTelephone}</td>
              </tr>
            </table>
          </td>
          <td>
            <table class=\"wide\">
              <tr>
                <th colspan=\"2\" class=\"pullLeft\">Delivery</th>
              </tr>
              <tr>
                <td class=\"ticketSpace\"></td>
                <td class=\"pullLeft\">{$this->decode($dName)}</td>
              </tr>
              <tr>
                <td class=\"ticketSpace\"></td>
                <td class=\"pullLeft\">{$this->decode($this->dAddress1)}<br>{$this->decode($this->dAddress2)}<br><span class=\"{$this->countryClass}\">{$this->countryFromAbbr($this->dCountry)}</span></td>
              </tr>
              <tr>
                <td class=\"ticketSpace pullRight\">Attn:</td>
                <td class=\"pullLeft\">{$this->dContact}</td>
              </tr>
              <tr>
                <td class=\"ticketSpace\"></td>
                <td class=\"pullLeft\">{$this->dTelephone}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td colspan=\"2\"><hr></td>
        </tr>
        <tr>
          <td>
            {$answerIce}
          </td>
          <td>
            {$answerNotes}
          </td>
        </tr>
        <tr>
          <td colspan=\"2\"><hr></td>
        </tr>
        <tr {$hideTableHead}>
          <td colspan=\"2\">
            <table class=\"wide sigTable\">
              {$pSigDisplay} {$dSigDisplay} {$d2SigDisplay}
            </table>
          </td>
        </tr>
      </table>";
      if ($this->ticketEditor === TRUE) $returnData .= "<button type=\"button\" class=\"ticketEditor\" data-key=\"{$this->formKey}\" data-contract=\"{$this->Contract}\" data-index=\"{$this->ticket_index}\">Edit Ticket</button>";
    $returnData .= '
    </div>';
    return $returnData;
    }

    public function displaySingleTicket() {
      $singleTicket = '';
      if ($this->Contract === 0) {
        // Test for fault in query. There should be no NULL dispatch times here
        if ($this->DispatchTimeStamp === $this->tTest) {
          return FALSE;
        }
        // Set the completion deadline based on the dispatch time stamp and charge
        switch ($this->Charge) {
          case 1:
            $this->pTime = date('g:i a', strtotime($this->DispatchTimeStamp) + 60*60);
          break;
          case 2:
            $this->pTime = date('g:i a', strtotime($this->DispatchTimeStamp) + 60*60*2);
          break;
          case 3:
            $this->pTime = date('g:i a', strtotime($this->DispatchTimeStamp) + 60*60*3);
          break;
          case 4:
            $this->pTime = date('g:i a', strtotime($this->DispatchTimeStamp) + 60*60*4);
          break;
          default:
            $this->pTime = date('g:i a', strtotime($this->DispatchTimeStamp) + 60*60*5);
          break;
        }
      }
      //Define whether the signature capture should be active for the given step of the run.
      if ($this->pSigReq === 0) {
        $pSigClass = $pSigActive = $pSigPlaceholder = '';
      } else {
        $pSigClass = 'pulse';
        $pSigActive = 'required';
        $pSigPlaceholder = 'REQUIRED';
      }
      if ($this->dSigReq === 0) {
        $dSigClass = $dSigActive = $dSigPlaceholder = '';
      } else {
        $dSigClass =  'pulse';
        $dSigActive = 'required';
        $dSigPlaceholder = 'REQUIRED';
      }
      if ($this->d2SigReq === 0) {
        $d2SigClass = $d2SigActive = $d2SigPlaceholder = '';
      } else {
        $d2SigClass = 'pulse';
        $d2SigActive = 'required';
        $d2SigPlaceholder = 'REQUIRED';
      }
      /***
      * Set the confirmation form and the display time for the stop based on
      * charge and timestamps.
      ***/
      if ($this->pTimeStamp === $this->tTest) {
        $buttonClass = '';
        $button2Class = 'deadRun';
        $button2Name = 'Dead Run';
        $label1 = ($this->Contract === 0) ? 'Deadline: ' : 'Pick Up: ';
        $label2 = ($this->Contract === 0) ? '' : 'Deliver: ';
        $temp = strtotime($this->pTime);
        $this->pTime = date('g:i a', $temp);
        $temp2 = ($this->Contract === 0) ? '' : strtotime($this->dTime);
        $this->dTime = ($this->Contract === 0) ? '' : date('g:i a', $temp2);
        $confirm = "
            <table class=\"wide confirm\">
              <tbody>
                <tr>
                  <td colspan=\"2\">
                    <form id=\"ticketForm{$this->ticket_index}\" class=\"routeStop\">
                      <input type=\"hidden\" name=\"formKey\" value=\"{$this->formKey}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"sigImage\" class=\"sigImage\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"step\" class=\"step\" value=\"pickedUp\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"charge\" class=\"charge\" value=\"{$this->Charge}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->EmailConfirm}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->EmailAddress}\" form=\"ticketForm{$this->ticket_index}\" />
                      <label for=\"pSigPrint{$this->ticket_index}\">Signer</label><br><input type=\"text\" name=\"pSigPrint\" id=\"pSigPrint{$this->ticket_index}\" class=\"pSigPrint printName\" placeholder=\"{$pSigPlaceholder}\" {$pSigActive} form=\"ticketForm{$this->ticket_index}\" />
                    </form>
                  </td>
                  <td colspan=\"2\" class=\"center\" style=\"vertical-align:bottom;\">
                    <button type=\"button\" class=\"pGetSig {$pSigClass}\"><img src=\"../images/sign.png\" height=\"24\" width=\"24\" alt=\"Open Signature Box\" /></button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type=\"button\" class=\"dTicket\" form=\"ticketForm{$this->ticket_index}\">Pick Up</button>
                  </td>";
      } else {
        if ($this->dTimeStamp === $this->tTest) {
          $buttonClass = 'hide';
          $button2Class = 'declined';
          $button2Name = 'Declined';
          $label1 = ($this->Contract === 0) ? 'Deadline: ' : 'Deliver: ';
          $label2 = ($this->Contract === 0) ? '' : 'Return: ';
          $temp = ($this->Contract === 0) ? '' : strtotime($this->dTime);
          $this->pTime = ($this->Contract === 0) ? $this->pTime : date('g:i a', $temp);
          if ($this->Contract === 1) {
            if ($this->Charge == 6) {
              $temp2 = strtotime($this->d2Time);
              $this->dTime = date('g:i a', $temp2);
            } else {
              $this->dTime = '-';
            }
          } else {
            $this->dTime = '';
          }
          // Swap the pick up and delivery locations
          $tempClient = $this->pClient;
          $tempDepartment = $this->pDepartment;
          $tempContact = $this->pContact;
          $tempTelephone = $this->pTelephone;
          $tempAddy1 = $this->pAddress1;
          $tempAddy2 = $this->pAddress2;
          $tempCountry = $this->pCountry;
          $this->pClient = $this->dClient;
          $this->pDepartment = $this->dDepartment;
          $this->pContact = $this->dContact;
          $this->pTelephone = $this->dTelephone;
          $this->pAddress1 = $this->dAddress1;
          $this->pAddress2 = $this->dAddress2;
          $this->pCountry = $this->dCountry;
          $this->dClient = $tempClient;
          $this->dDepartment = $tempDepartment;
          $this->dContact = $tempContact;
          $this->dTelephone = $tempTelephone;
          $this->dAddress1 = $tempAddy1;
          $this->dAddress2 = $tempAddy2;
          $this->dCountry = $tempCountry;
          $confirm = "
            <table class=\"wide confirm\">
              <tbody>
                <tr>
                  <td colspan=\"2\">
                    <form id=\"ticketForm{$this->ticket_index}\" class=\"routeStop\">
                      <input type=\"hidden\" name=\"formKey\" value=\"{$this->formKey}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"sigImage\" class=\"sigImage\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"step\" class=\"step\" value=\"delivered\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"charge\" class=\"charge\" value=\"{$this->Charge}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->EmailConfirm}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->EmailAddress}\" form=\"ticketForm{$this->ticket_index}\" />
                      <label for=\"dSigPrint{$this->ticket_index}\">Signer</label><br><input type=\"text\" name=\"dSigPrint\" id=\"dSigPrint{$this->ticket_index}\" class=\"dSigPrint printName\" placeholder=\"{$dSigPlaceholder}\" {$dSigActive} form=\"ticketForm{$this->ticket_index}\" />
                    </form>
                  </td>
                  <td colspan=\"2\" class=\"center\" style=\"vertical-align:bottom;\">
                    <button type=\"button\" class=\"dGetSig {$dSigClass}\"><img src=\"../images/sign.png\" height=\"24\" width=\"24\" alt=\"Open Signature Box\" /></button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type=\"button\" class=\"dTicket\" form=\"ticketForm{$this->ticket_index}\">Deliver</button>
                  </td>";
        } elseif ($this->dTimeStamp !== $this->tTest) {
          $buttonClass = $button2Class = 'hide';
          $button2Name = '';
          $label1 = 'Return: ';
          $label2 = '-';
          $temp = strtotime($this->d2Time);
          $this->pTime = date('g:i a', $temp);
          $this->dTime = '-';
          $confirm = "
            <table class=\"wide confirm\">
              <tbody>
                <tr>
                  <td colspan=\"2\">
                    <form id=\"ticketForm{$this->ticket_index}\" class=\"routeStop\">
                      <input type=\"hidden\" name=\"formKey\" value=\"{$this->formKey}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"sigImage\" class=\"sigImage\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"step\" class=\"step\" value=\"returned\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"charge\" class=\"charge\" value=\"{$this->Charge}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->EmailConfirm}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->EmailAddress}\" form=\"ticketForm{$this->ticket_index}\" />
                      <label for=\"d2SigPrint{$this->ticket_index}\">Signer</label><br><input type=\"text\" name=\"d2SigPrint\" id=\"d2SigPrint{$this->ticket_index}\" class=\"d2SigPrint printName\" placeholder=\"{$d2SigPlaceholder}\" {$d2SigActive} form=\"ticketForm{$this->ticket_index}\" />
                    </form>
                  </td>
                  <td colspan=\"2\" class=\"center\" style=\"vertical-align:bottom;\">
                    <button type=\"button\" class=\"d2GetSig {$d2SigClass}\"><img src=\"../images/sign.png\" height=\"24\" width=\"24\" alt=\"Open Signature Box\" /></button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type=\"button\" class=\"dTicket\" form=\"ticketForm{$this->ticket_index}\">Return</button>
                  </td>";
        }
      }
      if ($this->processTransfer === TRUE) {
        $confirm = '
            <table class="wide confirm">
              <tbody>
                <tr>';
        $confirm .= ($this->PendingReceiver !== $this->driverID) ? '
                  <td>Pending</td>
                  <td><button type="button" class="cancelTransfer">Cancel Transfer</button></td>' :
                  '<td><button type="button" class="acceptTransfer">Accept Transfer</button></td>
                  <td><button type="button" class="declineTransfer">Decline Transfer</button>';
      }
      // Make the client name look good for display
      if ($this->pDepartment != NULL) {
        $this->pClient .= "<br>{$this->pDepartment}";
      }
      if ($this->dDepartment != NULL) {
        $this->dClient .= "<br>{$this->dDepartment}";
      }
      if ($this->diWeight === 0) {
        $iceWeight = '-';
      } else {
        $iceWeight = self::number_format_drop_zero_decimals($this->diWeight, 3);
      }
      // Set the contact info
      $pContactDisplay = ($this->pContact == NULL) ? '' : "<tr><td>Contact:</td><td>{$this->pContact}</td></tr>";
      $pTelDisplay = ($this->pTelephone == NULL) ? '' : "<tr><td>Tel:</td><td><a href=\"tel:{$this->pTelephone}\" style=\"color:blue;\">{$this->pTelephone}</a></td></tr>";
      $dContactDisplay = ($this->dContact == NULL) ? '' : "<tr><td>Contact:</td><td>{$this->dContact}</td></tr>";
      $dTelDisplay = ($this->dTelephone == NULL) ? '' : "<tr><td>Tel:</td><td><a href=\"tel:{$this->dTelephone}\" style=\"color:blue;\">{$this->dTelephone}</a></td></tr>";
      $pAddressEncoded = urlencode($this->pAddress1 . ', ' . $this->pAddress2 . ', ' . $this->countryFromAbbr($this->pCountry));
      $dAddressEndoded = urlencode($this->dAddress1 . ', ' . $this->dAddress2 . ', ' . $this->countryFromAbbr($this->dCountry));
      $singleTicket .= "<div class=\"tickets sortable\">
        <h3>{$this->TicketNumber}</h3>
        <span  class=\"hide tNum\">{$this->ticket_index}</span>
        <span class=\"hide rNum\">{$this->RunNumber}</span>
        <span class=\"hide formKey\">{$this->formKey}</span>
        <span class=\"hide pendingReceiver\">{$this->PendingReceiver}</span>
        <h3 class=\"error floatRight\">{$this->ticketCharge($this->Charge)}</h3>
        <hr>
        <table class=\"wide\">
          <thead>
            <tr>
              <td colspan=\"2\">{$label1}<span class=\"timing\">{$this->pTime}</span></td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{$this->pClient}</td>
              <td><a class=\"plain\" target=\"_blank\" href=\"https://www.google.com/maps/dir//{$pAddressEncoded}\">{$this->pAddress1}<br>{$this->pAddress2}</a></td>
            </tr>
            <tr class=\"{$this->countryClass}\">
              <td></td>
              <td>{$this->countryFromAbbr($this->pCountry)}</td>
            </tr>
            {$pContactDisplay} {$pTelDisplay}
          </tbody>
        </table>
        <hr>
        <table class=\"wide\">
          <thead>
            <tr>
              <td colspan=\"2\">{$label2}{$this->dTime}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{$this->dClient}</td>
              <td><a class=\"plain\" target=\"_blank\" href=\"https://www.google.com/maps/dir//{$dAddressEndoded}\">{$this->dAddress1}<br>{$this->dAddress2}</a></td>
            </tr>
            <tr class=\"{$this->countryClass}\">
              <td></td>
              <td>{$this->countryFromAbbr($this->dCountry)}</td>
            </tr>
            {$dContactDisplay} {$dTelDisplay}
          </tbody>
        </table>
        <hr>
        <table class=\"tFieldLeft\" style=\"width:25%;\">
          <thead>
            <tr>
              <th class=\"pullLeft\">Dry Ice:</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td class=\"center\" style=\"white-space:nowrap;\">{$iceWeight}{$this->weightMarker}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
            </tr>
          </tbody>
        </table>
        <table class=\"tFieldRight\" style=\"width:75%;\">
          <thead>
            <tr>
              <th class=\"pullLeft\">Notes:</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class=\"center\"><textarea class=\"wide notes\" rows=\"4\" name=\"notes\" form=\"ticketForm{$this->ticket_index}\">{$this->Notes}</textarea></td>
            </tr>
          </tbody>
        </table>
        <hr>
        <p class=\"message2 center\"></p>
        {$confirm}";
      if ($this->processTransfer === FALSE) {
        $singleTicket .= "
              <td><button type=\"button\" class=\"cancelRun {$buttonClass}\">Cancel</button></td>
              <td><button type=\"button\" class=\"{$button2Class}\">{$button2Name}</button></td>
              <td><button type=\"button\" class=\"transferTicket\">Transfer</button></td>";
      } else {
        $singleTicket .= '
              <td></td>
              <td></td>
              <td></td>';
          }
      $singleTicket .= '
            </tr>
          </tbody>
        </table>
        <div class="signature-pad sigField"></div>
      </div>';
      return $singleTicket;
    }

    public function displayMultiTicket() {
      $multiTicket = '';
      $this->processTransfer = $this->multiTicket[0]->processTransfer;
      if ($this->processTransfer === TRUE) {
        $this->PendingReceiver = $this->multiTicket[0]->PendingReceiver;
      }
      switch ($this->multiTicket[0]->step) {
        case 'delivered':
          $topClient = self::decode($this->multiTicket[0]->dClient);
          if ($this->multiTicket[0]->dDepartment != NULL) {
            $topClient .= '<br>' . self::decode($this->multiTicket[0]->dDepartment);
          }
          $topAddressEncoded = urlencode($this->multiTicket[0]->dAddress1 . ', ' . $this->multiTicket[0]->dAddress2 . ', ' . self::countryFromAbbr($this->multiTicket[0]->dCountry));
          $topAddress = "<a class=\"plain\" target=\"_blank\" href=\"https://www.google.com/maps/dir//{$topAddressEncoded}\">{$this->decode($this->multiTicket[0]->dAddress1)}<br>{$this->decode($this->multiTicket[0]->dAddress2)}</a>";
          $temp = strtotime($this->multiTicket[0]->dTime);
          $pTime = date('g:i a', $temp);
        break;
        default:
          $topClient = self::decode($this->multiTicket[0]->pClient);
          if ($this->multiTicket[0]->pDepartment != NULL) {
            $topClient .= '<br>' . self::decode($this->multiTicket[0]->pDepartment);
          }
          $topAddressEncoded = urlencode($this->multiTicket[0]->pAddress1 . ', ' . $this->multiTicket[0]->pAddress2 . ', ' . self::countryFromAbbr($this->multiTicket[0]->pCountry));
          $topAddress = "<a class=\"plain\" target=\"_blank\" href=\"https://www.google.com/maps/dir//{$topAddressEncoded}\">{$this->decode($this->multiTicket[0]->pAddress1)}<br>{$this->decode($this->multiTicket[0]->pAddress2)}</a>";
          switch ($this->multiTicket[0]->step) {
            case 'pickedUp':
              $temp = strtotime($this->multiTicket[0]->pTime);
              $pTime = date('g:i a', $temp);
            break;
            case 'returned':
              $temp = strtotime($this->multiTicket[0]->d2Time);
              $pTime = date('g:i a', $temp);
            break;
          }
        break;
      }
      $multiTicket .= "<div class=\"tickets sortable centerDiv\">
          <table class=\"wide\">
            <thead>
              <tr>
                <td colspan=\"2\" class=\"center\"><h3 class=\"timing\">{$pTime}</h3></td>
              </tr>
              <tr>
                <td colspan=\"2\"><hr></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>{$topClient}</td>
                <td>{$topAddress}</td>
              </tr>
            </tbody>
          </table>
          <hr>";
      for ($i = 0; $i < count($this->multiTicket); $i++) {
        // Set the ticket Charge property to the current multiTicket Charge property for the ticketCharge function
        $this->Charge = $this->multiTicket[$i]->Charge;
        if ($this->multiTicket[$i]->diWeight == 0) {
          $iceWeight = '-';
        } else {
          $iceWeight = self::number_format_drop_zero_decimals($this->multiTicket[$i]->diWeight, 3);
        }
        switch ($this->multiTicket[$i]->step) {
          case 'delivered':
            $client = self::decode($this->multiTicket[$i]->pClient);
            if ($this->multiTicket[$i]->pDepartment != NULL) {
              $client .= '<br>' . self::decode($this->multiTicket[$i]->pDepartment);
            }
            $addressEndoded = urlencode($this->multiTicket[$i]->pAddress1 . ', ' . $this->multiTicket[$i]->pAddress2 . ', ' . self::countryFromAbbr($this->multiTicket[$i]->pCountry));
            $address = "<a class=\"plain\" target=\"_blank\" href=\"https://www.google.com/maps/dir//{$addressEndoded}\">{$this->decode($this->multiTicket[$i]->pAddress1)}<br>{$this->decode($this->multiTicket[$i]->pAddress2)}</a>";
            $contact = ($this->multiTicket[$i]->dContact == NULL) ? '' : "<tr><td>Contact:</td><td>{$this->decode($this->multiTicket[$i]->dContact)}</td></tr>";
            $tel = ($this->multiTicket[$i]->dTelephone == NULL) ? '' : "<tr><td>Tel:</td><td><a href=\"tel:{$this->multiTicket[$i]->dTelephone}\" style=\"color:blue;\">{$this->multiTicket[$i]->dTelephone}</a></td></tr>";
          break;
          default:
            $client = self::decode($this->multiTicket[$i]->dClient);
            if ($this->multiTicket[$i]->dDepartment != NULL) {
              $client .= '<br>' . self::decode($this->multiTicket[$i]->dDepartment);
            }
            $addressEndoded = urlencode($this->multiTicket[$i]->dAddress1 . ', ' . $this->multiTicket[$i]->dAddress2 . ', ' . self::countryFromAbbr($this->multiTicket[$i]->dCountry));
            $address = "<a class=\"plain\" target=\"_blank\" href=\"https://www.google.com/maps/dir//{$addressEndoded}\">{$this->decode($this->multiTicket[$i]->dAddress1)}<br>{$this->decode($this->multiTicket[$i]->dAddress2)}</a>";

            $contact = ($this->multiTicket[$i]->pContact == NULL) ? '' : "<tr><td>Contact:</td><td>{$this->decode($this->multiTicket[$i]->pContact)}</td></tr>";

            $tel = ($this->multiTicket[$i]->pTelephone == NULL) ? '' : "<tr><td>Tel:</td><td><a href=\"tel:{$this->multiTicket[$i]->pTelephone}\" style=\"color:blue;\">{$this->multiTicket[$i]->pTelephone}</a></td></tr>";
          break;
        }
        switch ($this->multiTicket[$i]->step) {
          case 'pickedUp':
            $label = 'Pick Up For';
            $buttonName = 'Cancel';
            $buttonClass = 'cancelRun';
            $button2Class = 'deadRun';
            $button2Name = 'Dead Run';
          break;
          case 'delivered':
            $label = 'Deliver From';
            $buttonName = 'Not Used';
            $buttonClass = 'hide';
            $button2Class = 'declined';
            $button2Name = 'Declined';
          break;
          case 'returned':
            $label = 'Return From';
            $buttonName = 'Not Used';
            $buttonClass = $button2Class = 'hide';
            $button2Name = '';
          break;
        }
        $multiTicket .= "<table class=\"tickets center\">
          <tfoot>
            <tr>
              <td><button type=\"button\" class=\"{$buttonClass}\">{$buttonName}</button></td>
              <td><button type=\"button\" class=\"{$button2Class}\">{$button2Name}</button></td>
            </tr>
            <tr>
              <td colspan=\"2\" class=\"message2 center\" style=\"padding-top:0.5em\"></td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
          </tfoot>
          <thead>
            <tr>
              <td colspan=\"2\" class=\"center\">
                <form id=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" class=\"routeStop\">
                  <input type=\"hidden\" name=\"formKey\" class=\"formKey\" value=\"{$this->formKey}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->multiTicket[$i]->ticket_index}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"runNumber\" class=\"runNumber\" value=\"{$this->multiTicket[$i]->RunNumber}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"charge\" class=\"charge\" value=\"{$this->multiTicket[$i]->Charge}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->multiTicket[$i]->EmailConfirm}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->multiTicket[$i]->EmailAddress}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"pendingReceiver\" class=\"pendingReceiver\" value=\"{$this->multiTicket[$i]->PendingReceiver}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"step\" class=\"step\" value=\"{$this->multiTicket[$i]->step}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                </form>
                <h3 class=\"floatLeft\">{$this->multiTicket[$i]->TicketNumber}</h3>{$label}<h3 class=\"floatRight error\">{$this->ticketCharge($this->Charge)}</h3>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{$client}</td>
              <td>{$address}</td>
            </tr>
              {$contact} {$tel}
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
            <tr>
              <td colspan=\"2\">
                <table class=\"tFieldLeft\" style=\"width:25%;\">
                  <tr>
                    <th class=\"pullLeft\">Dry Ice:</th>
                  </tr>
                  <tr>
                    <td class=\"center\">{$iceWeight}{$this->weightMarker}</td>
                  </tr>
                </table>
                <table class=\"tFieldRight\" style=\"width:75%;\">
                  <tr>
                    <th class=\"pullLeft\">Notes:</th>
                  </tr>
                  <tr>
                    <td><textarea class=\"wide notes\" rows=\"4\" name=\"notes\">{$this->decode($this->multiTicket[$i]->Notes)}</textarea></td>
                  </tr>
                </table>
              </td>
            </tr>
          </tbody>
        </table>";
      }
      $multiTicket .= '<p class="message2 center"></p>';
      $count = 'count';
      if ($this->processTransfer === FALSE) {
        $multiTicket .= "
          <p class=\"center\">
            <input type=\"hidden\" name=\"sigImage\" id=\"sigImage{$this->multiTicket[0]->ticket_index}\" class=\"sigImage\" />
            <label for=\"pSigPrint{$this->ticket_index}\">Signer</label><br><input type=\"text\" name=\"pSigPrint\" id=\"pSigPrint{$this->multiTicket[0]->ticket_index}\" class=pSigPrint printName\" form=form=\"ticketForm{$this->multiTicket[0]->ticket_index}\" /><button type=\"button\" style=\"vertical-align:middle;\" class=\"pGetSig\"><img src=\"../images/sign.png\" height=\"24\" width=\"24\" alt=\"Open Signature Box\" /></button>
          </p>
          <div class=\"signature-pad sigField\"></div>
          <button type=\"button\" class=\"confirmAll\">Confirm {$count($this->multiTicket)}</button> <button type=\"button\" class=\"transferGroup\">Transfer {$count($this->multiTicket)}</button></div>";
      } else {
        if ($this->PendingReceiver === $this->driverID) {
          $multiTicket .= '<button type="button" class="acceptTransferGroup floatLeft">Accept Transfer Group</button>
                <button type="button" class="declineTransferGroup floatRight">Decline Transfer Group</button>
                ';
        } else {
          $multiTicket .= '<button type="button" class="cancelTransferGroup">Cancel Transfer Group</button>';
        }
      }
      return $multiTicket;
    }

    private function javascriptVars() {
      $returnData = '';
      $keyList = ['ShippingAddress1', 'ShippingAddress2', 'ClientName', 'Department'];
      foreach($_SESSION as $key => $value) {
        if (in_array($key, $this->javascriptKeys)) {
          if (array_key_exists($value, $this->clientNameExceptions)) {
            $value = $this->clientNameExceptions[$value];
          }
          $returnData .= "
            <script>var {$key} = \"{$this->decode($value)}\"</script>";
        }
      }
      return $returnData;
    }

    protected function hiddenInputs() {
      $returnData = '';
      $htmlentities = 'htmlentities';
      foreach ($this as $key => $value) {
        // Don't include values ending with 'List' in the form to add a new ticket
        if (substr($this->formName, 0, 12) === 'submitTicket' && substr($key, -4) === 'List') {
          break;
        }
        if (in_array($key, $this->postableKeys)) {
          $returnData .= "
            <input type=\"hidden\" name=\"{$key}\" value=\"{$htmlentities($value)}\" form=\"{$this->formName}\" />";
        }
      }
      return $returnData;
    }

    public function ticketsToDispatch() {
      $returnData = '';
      $ticketQueryResult = [];
      $this->forDisatch = TRUE;
      // Pull tickets that have not been dispatched
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['formKey'] = $this->formKey;
      $ticketQueryData['queryParams'] = [];
      if ($this->ticket_index === NULL) {
        $ticketQueryData['queryParams']['filter'] = [ ['Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>'-'], ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->Contract] ];
      } else {
        $ticketQueryData['queryParams']['filter'] = [ ['Resource'=>'ticket_index', 'Filter'=>'eq', 'Value'=>$this->ticket_index] ];
      }
      if ($this->ticketEditor === FALSE) {
        $this->driverID = 0;
        $ticketQueryData['queryParams']['filter'][] = ['Resource'=>'DispatchTimeStamp', 'Filter'=>'is'];
      } else {
        if ($this->ticket_index === NULL) {
          $this->dateObject = clone $this->today;
          $this->backstop = $this->dateObject->modify('- 7 days')->format('Y-m-d');
          $ticketQueryData['queryParams']['filter'][] = ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today->format('Y-m-d')} 23:59:59"];
        }
      }
      if ($this->ticket_index === NULL) $ticketQueryData['queryParams']['filter'][] = ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->DispatchedTo];

      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      $ticketQueryResult = self::callQuery($ticketQuery);
      if ($ticketQueryResult === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }

      if (empty($ticketQueryResult)) {
        return '<p class="center">No Tickets Available.</p>';
      }
      if ($this->driverList === NULL) {
        self::fetchDrivers();
      }
      if ($this->ticketEditor === FALSE) {
        for ($i = 0; $i < count($ticketQueryResult); $i++) {
          foreach ($ticketQueryResult[$i] as $key => $value) {
            if (property_exists($this, $key)) {
              $this->$key = $value;
            }
          }
          $returnData .= self::regenTicket();
        }
        return $returnData;
      } else {
        $this->forDisatch = FALSE;
        $this->ticketEditor = TRUE;
        // Sort the tickets based on charge code and timestamps
        foreach ($ticketQueryResult as $ticket) {
          if ($ticket['pTimeStamp'] === $this->tTest && $ticket['Charge'] !== 9) {
            $this->activeTicketSet[] = $ticket;
          } elseif ($ticket['pTimeStamp'] !== $this->tTest && $ticket['dTimeStamp'] === $this->tTest && $ticket['Charge'] !== 9) {
            $this->activeTicketSet[] = $ticket;
          } elseif ($ticket['pTimeStamp'] !== $this->tTest && $ticket['dTimeStamp'] !== $this->tTest && $ticket['d2TimeStamp'] === $this->tTest && ($ticket['Charge'] === 6 || ($ticket['Charge'] === 7 && $ticket['d2SigReq'] === 1))) {
            $this->activeTicketSet[] = $ticket;
          }
        }
        if (empty($this->activeTicketSet)) {
          return '<p class="center">No Tickets Available.</p>';
        }
        for ($i = 0; $i < count($this->activeTicketSet); $i++) {
          foreach ($this->activeTicketSet[$i] as $key => $value) {
            foreach ($this as $k => $v) {
              if (strtolower($k) === strtolower($key)) {
                $this->{$k} = $value;
              }
            }
          }
          $returnData .= self::regenTicket();
        }
        return $returnData;
      }
    }

    public function ticketQueryForm() {
      $this->formType = 'Query';
      if ($this->userType === 'client' && $this->ulevel === 2) {
        $returnData = "
            <div id=\"ticketQueryOptions\">
              <form id=\"deliveryQuery\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                <input type=\"hidden\" name=\"billTo\" value=\"{$_SESSION['ClientID']}\" />
                <input type=\"hidden\" name=\"endPoint\" class=\"endPoint\" value=\"tickets\" />
                <input type=\"hidden\" name=\"method\" class=\"method\" value=\"GET\" />
                <fieldset form=\"deliveryQuery\" name=\"dateRange\">
                  <legend>Search Parameters</legend>
                  <div>
                    <p>
                      <label for=\"allTime\">All Time:</label>
                      <input type=\"hidden\" name=\"allTime\" value=\"N\" />
                      <input type=\"checkbox\" name=\"allTime\" id=\"allTime\" class=\"allTime2\" value=\"Y\" />
                    </p>
                    <p>
                      <label for=\"ticketNumber\" class=\"switchable\">Ticket<span class=\"mobileHide\"> Number</span>:</label>
                      <input type=\"number\" min=\"0\" name=\"ticketNumber\" id=\"ticketNumber\" class=\"switchable\" />
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"startDate\">Start Date:</label>
                      <input type=\"hidden\" name=\"startDate\" class=\"startDateMarker\" disabled />
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'startDate', TRUE)}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'startDate', FALSE, 'date', 'tickets')}</span>
                    </p>
                    <p>
                      <label for=\"endDate\">End Date:</label>
                      <input type=\"hidden\" name=\"endDate\" class=\"endDateMarker\" disabled />
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'endDate', TRUE)}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'endDate', FALSE, 'date', 'tickets')}</span>
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"chargeHistory\" class=\"switchable\">Charge:</label>
                      <select name=\"charge\" id=\"chargeHistory\" class=\"switchable\">
                      {$this->createChargeSelectOptions()}
                      </select>
                    </p>
                    <p>
                      <input type=\"hidden\" name=\"type\" id=\"typeMarker\" value=\"2\" />
                      <label for=\"type\" class=\"switchable\">Type:</label>
                      <select name=\"type\" id=\"type\" class=\"switchable\">
                        <option value=\"2\">All</option>
                        <option value=\"1\">Contract</option>
                        <option value=\"0\">On Call</option>
                      </select>
                    </p>
                  </div>
                  <div>
                    <input type=\"hidden\" name=\"display\" value=\"tickets\" />
                    <input type=\"hidden\" name=\"compare\" id=\"compare\" value=\"0\" />
                  </div>
                </fieldset>
                <button type=\"submit\" class=\"submitTicketQuery\">Query</button>
                <button type=\"reset\" class=\"resetTicketQuery\" form=\"deliveryQuery\">Reset</button>
                <button type=\"button\" class=\"clearTicketResults\">Clear Results</button>
                <span class=\"floatRight\"></span>
              </form>
            </div>
            <div id=\"ticketQueryResults\">
              {$this->fetchTodaysTickets()}
            </div>";
      } elseif ($this->userType === 'client' && $this->ulevel === 1) {
        $returnData = "
            <div id=\"ticketQueryOptions\">
              <form id=\"deliveryQuery\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                <input type=\"hidden\" name=\"billTo\" value=\"{$_SESSION['ClientID']}\" />
                <input type=\"hidden\" name=\"endPoint\" class=\"endPoint\" value=\"tickets\" />
                <input type=\"hidden\" name=\"method\" class=\"method\" value=\"GET\" />
                <fieldset form=\"deliveryQuery\" name=\"dateRange\">
                  <legend>Search Parameters</legend>
                  <div>
                    <p>
                      <label for=\"allTime\">All Time:</label>
                      <input type=\"hidden\" name=\"allTime\" value=\"N\" />
                      <input type=\"checkbox\" name=\"allTime\" id=\"allTime\" class=\"allTime\" value=\"Y\" />
                    </p>
                    <p>
                      <label for=\"ticketNumber\" class=\"switchable\">Ticket<span class=\"mobileHide\"> Number</span>:</label>
                      <input type=\"number\" min=\"0\" name=\"ticketNumber\" id=\"ticketNumber\" class=\"switchable\" />
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"startDate\">Start Date:</label>
                      <input type=\"hidden\" name=\"startDate\" class=\"startDateMarker\" disabled />
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'startDate', TRUE)}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'startDate', FALSE, 'date', 'tickets')}</span>
                    </p>
                    <p>
                      <label for=\"endDate\">End Date:</label>
                      <input type=\"hidden\" name=\"endDate\" class=\"endDateMarker\" disabled />
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'endDate', TRUE)}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput($_SESSION['ClientID'], 'endDate', FALSE, 'date', 'tickets')}</span>
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"chargeHistory\" class=\"switchable\">Charge:</label>
                      <input type=\"hidden\" name=\"charge\" id=\"chargeMarker\" value=\"10\" />
                      <select name=\"charge\" id=\"chargeHistory\" class=\"switchable\">
                      {$this->createChargeSelectOptions()}
                      </select>
                    </p>
                    <p>
                      <input type=\"hidden\" name=\"type\" id=\"typeMarker\" value=\"2\" />
                      <label for=\"type\" class=\"switchable\">Type:</label>
                      <select name=\"type\" id=\"type\" class=\"switchable\">
                        <option value=\"2\">All</option>
                        <option value=\"1\">Contract</option>
                        <option value=\"0\">On Call</option>
                      </select>
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"display\">Display:</label>
                      <input type=\"hidden\" name=\"display\" value=\"tickets\" />
                      <select name=\"display\" id=\"display\">
                         <option value=\"tickets\">Tickets</option>
                        <option value=\"chart\">Chart</option>
                      </select>
                    </p>
                    <p>
                      <span class=\"compare\" style=\"display:none;\">
                        <label for=\"compare\">Compare Months: </label>
                        <input type=\"hidden\" name=\"compare\" value=\"0\" />
                        <input type=\"checkbox\" name=\"compare\" id=\"compareBox\" value=\"1\" disabled />
                      </span>
                    </p>
                  </div>
                </fieldset>
                <button type=\"submit\" class=\"submitTicketQuery\">Query</button>
                <button type=\"reset\" class=\"resetTicketQuery\" form=\"deliveryQuery\">Reset</button>
                <button type=\"button\" class=\"clearTicketResults\">Clear Results</button>
              </form>
            </div>
            <div id=\"ticketQueryResults\">
              {$this->fetchTodaysTickets()}
            </div>";
      } elseif ($this->userType === 'org') {
        $array_keys = 'array_keys';
        $returnData = "
            <div id=\"options\">
              <form id=\"queryForms\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                <input type=\"hidden\" name=\"endPoint\" value=\"tickets\" />
                <input type=\"hidden\" name=\"method\" value=\"GET\" />
                <fieldset form=\"queryForms\" id=\"deliveryQuery\">
                  <legend>Search Parameters</legend>
                  <div>
                    <p>
                      <label for=\"allTime\">All Time:</label>
                      <input type=\"hidden\" name=\"allTime\" value=\"N\" />
                      <input type=\"checkbox\" name=\"allTime\" id=\"allTime\" value=\"Y\" />
                    </p>
                    <p>
                      <label for=\"ticketNumber\" class=\"switchable\">Ticket<span class=\"mobileHide\"> Number</span>:</label>
                      <input type=\"hidden\" class=\"ticketNumberMarker\" name=\"ticketNumber\" />
                      <input type=\"number\" min=\"1\" name=\"ticketNumber\" id=\"ticketNumber\" />
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"startDate\">Start Date:</label>
                      <input type=\"hidden\" name=\"startDate\" id=\"startDateMarker\" disabled />
                      <span class=\"chartDate\" style=\"display:none;\" title=\"Query Range Limited To 6 Month Periods\">
                        {$this->createLimitedMonthInput($array_keys($_SESSION['members']), 'startDate', TRUE)}
                      </span>
                      <span class=\"ticketDate\">
                        {$this->createLimitedMonthInput($array_keys($_SESSION['members']), 'startDate', FALSE, 'date', 'tickets')}
                      </span>
                    </p>
                    <p>
                      <label for=\"endDate\">End Date:</label>
                      <input type=\"hidden\" name=\"endDate\" id=\"endDateMarker\" />
                      <span class=\"chartDate\" style=\"display:none;\" title=\"Query Range Limited To 6 Month Periods\">
                        {$this->createLimitedMonthInput($array_keys($_SESSION['members']), 'endDate', TRUE)}
                      </span>
                      <span class=\"ticketDate\">
                        {$this->createLimitedMonthInput($array_keys($_SESSION['members']), 'endDate', FALSE, 'date', 'tickets')}
                      </span>
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"charge\">Charge:</label>
                      <input type=\"hidden\" name=\"charge\" id=\"chargeMarker\" value=\"10\" />
                      <select name=\"charge\" id=\"charge\">
                        {$this->createChargeSelectOptions()}
                      </select>
                    </p>
                    <p>
                      <label for=\"type\">Type:</label>
                      <input type=\"hidden\" name=\"type\" id=\"typeMarker\" value=\"2\" />
                      <select name=\"type\" id=\"type\">
                        <option value=\"2\">All</option>
                        <option value=\"1\">Contract</option>
                        <option value=\"0\">On Call</option>
                      </select>
                    </p>
                  </div>
                  <div>
                    <p>
                      <label for=\"display\">Display:  </label>
                      <input type=\"hidden\" name=\"display\" id=\"displayMarker\" value=\"tickets\" />
                      <select name=\"display\" id=\"display\">
                        <option value=\"tickets\">Tickets</option>
                        <option value=\"chart\">Chart</option>
                      </select>
                    </p>
                  </div>
                  <div style=\"clear:both\">
                    <p>
                      <span>Compare:</span>
                      <input type=\"hidden\" name=\"compare\" value=\"0\" />
                      <input type=\"checkbox\" name=\"compare\" id=\"compareBox\" value=\"1\" disabled />
                      <label for=\"compareBox\">Months</label>
                      <input type=\"hidden\" name=\"compareMembers\" value=\"0\" />
                      <input type=\"checkbox\" name=\"compareMembers\" id=\"compareMembersTickets\" value=\"1\" disabled />
                      <label for=\"compareMembersTickets\">Members</label>
                    </p>
                  </div>
                </fieldset>
                <p class=\"centerDiv\">{$this->listOrgMembers('ticket')}</p>
                <button type=\"submit\" class=\"submitOrgTickets\" title=\"Select a member or&#10enter a ticket number to continue\">Query</button>
              </form>
              <div id=\"ticketQueryResults\"></div>
            </div>";
      }
      return $returnData;
    }

    private function createChargeSelectOptions() {
      $returnData = '';
      if ($this->userType === 'client') {
        if ($this->ClientID === 0) {
          $excludes = (isset($this->options["client0Charges{$this->formType}Exclude"])) ? $this->options["client0Charges{$this->formType}Exclude"] : [];
        } else {
          $excludes = (isset($this->options["clientCharges{$this->formType}Exclude"][$this->ulevel - 1])) ? $this->options["clientCharges{$this->formType}Exclude"][$this->ulevel - 1] : [];
        }
      } elseif ($this->userType === 'driver') {
        $excludes = (isset($this->options["driverCharges{$this->formType}Exclude"][$this->CanDispatch - 1])) ? $this->options['driverChargesExclude'][$this->CanDispatch - 1] : [];
      } else {
        $excludes = (isset($this->options["{$this->userType}Charges{$this->formType}Exclude"])) ? $this->options["{$this->userType}Charges{$this->formType}Exclude"] : [];
      }
      for ($i=0; $i < 10; $i++) {
        if (!in_array($i, $excludes, true)) {
          $selected = ($this->Charge === $i) ? 'selected' : '';
          $returnData .= "
          <option value=\"{$i}\" {$selected}>{$this->ticketCharge($i)}</option>";
        }
      }
      if ($this->formType === 'Query' && (is_numeric($this->ulevel) && ($this->ulevel < 2 || $this->ClientID === 0))) {
        $temp = $returnData;
        $returnData = "
          <option value=\"10\">All</option>" . $temp;
      }
      return $returnData;
    }

    public function ticketForm() {
      $returnData = '';
      $this->action = self::esc_url($_SERVER['REQUEST_URI']);
      $this->formType = 'Entry';
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($this->newTicket !== FALSE || ($this->ticket_index !== NULL && $this->updateTicket !== FALSE)) return self::processTicket();
        if ($this->edit === '0') return self::confirmRequest();
      }
      if ($this->locationList === NULL) {
        if (!self::buildLocationList()) {
          return $this->error;
        }
      }
      $returnData .= self::javascriptVars();
      try {
        $returnData .= self::buildDatalists();
      } catch (Exception $e) {
        throw $e;
      }
      if ($this->ticket_index !== NULL) {
        if (!self::queryTicket()) {
          return $this->error;
        }
        $this->action = '/drivers/ticketEditor';
        if ($this->RepeatClient === 1) {
          foreach (json_decode(urldecode($this->clientList), TRUE) as $client) {
            if ($client['ClientID'] === (int)$this->BillTo) {
              $this->BillTo = ($client['Department'] == NULL) ? "{$client['ClientName']}; {$client['ClientID']}" : "{$client['ClientName']}, {$client['Department']}; {$client['ClientID']}";
            }
          }
        } else {
          foreach (json_decode(urldecode($this->tClientList), TRUE) as $client) {
            if ($client['ClientID'] === (int)$this->BillTo) {
              $this->BillTo = ($client['Department'] == NULL) ? "{$client['ClientName']}; {$client['ClientID']}" : "{$client['ClientName']}, {$client['Department']}; {$client['ClientID']}";
            }
          }
        }
        foreach (json_decode(urldecode($this->driverList), TRUE) as $driver) {
          if ($driver['DriverID'] == $this->DispatchedTo) {
            $this->DriverName = ($driver['LastName'] === NULL) ? $driver['FirstName'] : $driver['FirstName'] . ' ' . $driver['LastName'];
          }
        }
      }
      // Check boxes and display notices based on values
      $toMeChecked = ($this->toMe === 1) ? 'checked' : '';
      $fromMeCheked = ($this->fromMe === 1) ? 'checked' : '';
      $pSigChecked = ($this->pSigReq === 1) ? 'checked' : '';
      $dSigChecked = ($this->dSigReq === 1) ? 'checked' : '';
      $d2SigChecked = ($this->d2SigReq === 1) ? 'checked' : '';
      $sigNoteClass = ($this->pSigReq === 1 || $this->dSigReq === '1' || $this->d2SigReq === '1') ? '' : 'hide';
      $emailNoteClass = ($this->EmailConfirm === 0) ? 'hide' : '';
      $dryIceChecked = $diWeightMarkerDisabled = '';
      $diWeightDisabled = 'disabled';
      if ($this->dryIce === 1) {
        $dryIceChecked = 'checked';
        $diWeightMarkerDisabled = 'disabled';
        $diWeightDisabled = '';
      }
      $emailConfirm0 = $emailConfirm1 = $emailConfirm2 = $emailConfirm3 = $emailConfirm4 = $emailConfirm5 = $emailConfirm6 = $emailConfirm7 = '';
      switch ($this->EmailConfirm) {
        case 0: $emailConfirm0 = 'selected'; break;
        case 1: $emailConfirm1 = 'selected'; break;
        case 2: $emailConfirm2 = 'selected'; break;
        case 3: $emailConfirm3 = 'selected'; break;
        case 4: $emailConfirm4 = 'selected'; break;
        case 5: $emailConfirm5 = 'selected'; break;
        case 6: $emailConfirm6 = 'selected'; break;
        case 7: $emailConfirm7 = 'selected'; break;
      }
      $emailNoteDisplay = ($this->EmailConfirm === 0) ? 'hide' : '';

      if ($this->userType === 'client') {
        $billingRowClass = 'hide';
        $dispatchInputType = 'type="hidden"';
        $billToType = 'type="hidden"';
        $billToValue = $_SESSION['ClientName'] . ', ' . $_SESSION['Department'] . '; ' . $_SESSION['ClientID'];
        $repeatOption = $readonlyDispatch = $hideFromDriver = $hideDispatch = $requiredDispatch = $billToRequired = $dispatchedBy = '';
        $dispatchInputValue = '0';
        $transferredBy = $cancelTicketEditor = $nonRepeatChecked = '';
      } else {
        $billingRowClass = '';
        $dispatchInputType = ($this->userType === 'dispatch' || $this->CanDispatch === 2) ? 'list="drivers"' : (($this->CanDispatch === 1) ? 'type="text"' : 'type="hidden"');
        $readonlyDispatch = ($this->CanDispatch === 1) ? 'readonly' : '';
        $requiredDispatch = ($this->CanDispatch === 2) ? 'required' : '';
        if ($this->ticket_index != NULL) {
          $dispatchInputValue = $this->DriverName . '; ' . $this->DispatchedTo;
        } else {
          $dispatchInputValue = ($this->CanDispatch === 1) ? $this->config['driverName'] . '; ' . $_SESSION['ClientID'] : '';
        }
        $dispatchedBy = ($this->ticketEditor === FALSE) ? "
          <input type=\"hidden\" name=\"dispatchedBy\" class=\"dispatchedBy\" value=\"{$this->DispatchedBy}\" />" : '';
        $hideDispatch = ($this->CanDispatch >= 1) ? '' : 'class="hide"';
        $billToType = ($this->RepeatClient == 0) ? 'list="t_clients"' : 'list="clients"';
        $nonRepeatChecked = ($this->RepeatClient == 0) ? 'checked' : '';
        $billToValue = $this->BillTo;
        $billToRequired = 'required';
        $repeatOption = "<input type=\"checkbox\" name=\"repeatClient\" class=\"repeat\" id=\"repeatClient{$this->ticket_index}\" value=\"0\" form=\"request{$this->ticket_index}\" />";
        $hideFromDriver = 'class="hide"';
        $transfersValue = ($this->Transfers === NULL) ? '' : htmlentities(json_encode($this->Transfers));
        $transferredBy = ($this->ticketEditor === TRUE) ? "<input type=\"hidden\" name=\"transferredBy\" class=\"transferredBy\" value=\"{$this->transferredBy}\" form=\"request{$this->ticket_index}\" />
        <input type=\"hidden\" name=\"holder\" class=\"holder\" value=\"{$this->DispatchedTo}\" form=\"request{$this->ticket_index}\" />
        <input type=\"hidden\" name=\"transfers\" class=\"transfers\" value=\"{$transfersValue}\" form=\"request{$this->ticket_index}\" />" : '';
        $cancelTicketEditor = ($this->ticketEditor === TRUE) ? '<button type="button" class="cancelTicketEditor floatRight">Cancel</button>' : '';
      }
      // Display the ticket form
      $indexInput = ($this->ticket_index == NULL) ? '' : "<input type=\"hidden\" name=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"request{$this->ticket_index}\" />
      ";
      $ticketNumberInput = ($this->TicketNumber !== NULL) ? "
        <input type=\"hidden\" name=\"ticketNumber\" class=\"ticketNumber\" value=\"{$this->TicketNumber}\" form=\"request{$this->ticket_index}\" />
        " : '';
      $timing = ($this->ticketEditor === TRUE) ? "
        <tr>
          <td colspan=\"2\">
            <fieldset form=\"request{$this->ticket_index}\" id=\"timing{$this->ticket_index}\">
              <legend>Timing</legend>
              <table class=\"centerDiv\">
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </table>
            </fieldset>
          </td>
        </tr>
      " : '';
      $returnData .= "
      <div id=\"deliveryRequest{$this->ticket_index}\" class=\"removableByEditor\">
        <form id=\"request{$this->ticket_index}\" action=\"{$this->action}\" method=\"post\">
          <input type=\"hidden\" name=\"formKey\" value=\"{$this->formKey}\" form=\"request{$this->ticket_index}\" />
          {$indexInput} {$dispatchedBy} {$transferredBy} {$ticketNumberInput}
          <input type=\"hidden\" name=\"runNumber\" value=\"'{$this->RunNumber}\" form=\"request{$this->ticket_index}\" />
          <input type=\"hidden\" name=\"contract\" value=\"{$this->Contract}\" form=\"request{$this->ticket_index}\" />
          <table class=\"ticketContainer\">
            <tr>
              <td colspan=\"2\">
                <fieldset form=\"request{$this->ticket_index}\" id=\"information{$this->ticket_index}\">
                  <legend>General Information</legend>
                  <table class=\"centerDiv\">
                    <tr class=\"{$billingRowClass}\">
                      <td>
                        <label for=\"repeatClient{$this->ticket_index}\">Non-Repeat:</label>
                        <input type=\"hidden\" name=\"repeatClient\" value=\"1\" form=\"request{$this->ticket_index}\" {$nonRepeatChecked} . ' />
                        {$repeatOption}
                      </td>
                    </tr>
                    <tr class=\"{$billingRowClass}\">
                      <td><label for=\"billTo{$this->ticket_index}\">Bill To: </label><input {$billToType} name=\"billTo\" id=\"billTo{$this->ticket_index}\" class=\"billTo\" value=\"{$billToValue}\" title=\"{$billToValue}\" form=\"request{$this->ticket_index}\" {$billToRequired} /></td>
                      <td><label for=\"dispatchedTo{$this->ticket_index}\" {$hideDispatch}>Dispatch To: </label><input {$dispatchInputType} name=\"dispatchedTo\" id=\"dispatchedTo{$this->ticket_index}\" class=\"dispatchedTo\" form=\"request{$this->ticket_index}\" value=\"{$dispatchInputValue}\" {$readonlyDispatch} {$requiredDispatch} /></td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"charge{$this->ticket_index}\">Delivery Time:</label>
                        <select name=\"charge\" id=\"charge{$this->ticket_index}\" class=\"charge\" form=\"request{$this->ticket_index}\">
                          {$this->createChargeSelectOptions()}
                        </select>
                      </td>
                      <td>
                        <label class=\"rtMarker\" for=\"d2SigReq{$this->ticket_index}\">Request Signature:</label>
                        <input type=\"hidden\" name=\"d2SigReq\" id=\"d2SigReqMarker{$this->ticket_index}\" class=\"d2SigReqMarker\" value=\"0\" form=\"request{$this->ticket_index}\" />
                        <input type=\"checkbox\" class=\"rtMarker\" name=\"d2SigReq\" id=\"d2SigReq{$this->ticket_index}\" class=\"d2SigReq\" value=\"1\" {$d2SigChecked} form=\"request{$this->ticket_index}\" />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"emailAddress{$this->ticket_index}\">Email Address:</label>
                        <input type=\"email\" name=\"emailAddress\" id=\"emailAddress{$this->ticket_index}\" class=\"emailAddress\" form=\"request{$this->ticket_index}\" value=\"{$this->EmailAddress}\" />
                      </td>
                      <td>
                        <label for=\"emailConfirm{$this->ticket_index}\">Email <span class=\"mobileHide\">Confirmation</span>:</label>
                        <select form=\"request{$this->ticket_index}\" name=\"emailConfirm\" id=\"emailConfirm{$this->ticket_index}\" class=\"emailConfirm\">
                          <option value=\"0\" {$emailConfirm0}>None</option>
                          <option value=\"1\" {$emailConfirm1}>Picked Up</option>
                          <option value=\"2\" {$emailConfirm2}>Delivered</option>
                          <option value=\"3\" {$emailConfirm3}>Picked Up & Delivered</option>
                          <option class=\"rtMarker\" value=\"4\" {$emailConfirm4}>Returned</option>
                          <option class=\"rtMarker\" value=\"5\" {$emailConfirm5}>Picked Up &amp; Returned</option>
                          <option class=\"rtMarker\" value=\"6\" {$emailConfirm6}>Delivered &amp; Returned</option>
                          <option class=\"rtMarker\" value=\"7\" {$emailConfirm7}>All</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"telephone{$this->ticket_index}\">Phone <span class=\"mobileHide\">Number</span>:</label>
                        <input type=\"tel\" name=\"telephone\" id=\"telephone{$this->ticket_index}\" class=\"telephone\" form=\"request{$this->ticket_index}\" placeholder=\"555-123-4567x890\" value=\"{$this->Telephone}\" />
                      </td>
                      <td>
                        <label for=\"requestedBy{$this->ticket_index}\">Requested By:</label>
                        <input type=\"text\" name=\"requestedBy\" id=\"requestedBy{$this->ticket_index}\" class=\"requestedBy\" value=\"{$this->RequestedBy}\" form=\"request{$this->ticket_index}\" required />
                      </td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            </tr>
            {$timing}
            <tr>
              <td>
                <fieldset form=\"request{$this->ticket_index}\" id=\"pickupField{$this->ticket_index}\">
                  <legend>Pick Up</legend>
                  <table class=\"centerDiv\">
                    <thead {$hideFromDriver}>
                      <tr>
                        <td><label for=\"fromMe{$this->ticket_index}\">From Me:</label>
                          <input type=\"hidden\" name=\"fromMe\" value=\"0\" form=\"request{$this->ticket_index}\" />
                          <input type=\"checkbox\" id=\"fromMe{$this->ticket_index}\" class=\"me\" name=\"fromMe\" value=\"1\" {$fromMeCheked} form=\"request{$this->ticket_index}\" />
                        </td>
                        <td>
                          <label for=\"onFileP{$this->ticket_index}\">On File:  </label>
                          <input type=\"checkbox\" id=\"onFileP{$this->ticket_index}\" class=\"onFile\" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan=\"2\"><hr></td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><label for=\"pClient{$this->ticket_index}\">Client<span class=\"mobileHide\"> Name</span>:</label></td>
                        <td>
                          <input list=\"clientName\" name=\"pClient\" id=\"pClient{$this->ticket_index}\" class=\"clientList\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->pClient)}\" />";
                if ($this->userType === 'client') {
                  $this->selectID = 'pClient'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr>
                        <td><label for=\"pDepartment{$this->ticket_index}\">Department:</label></td>
                        <td>
                          <input list=\"departments\" name=\"pDepartment\" id=\"pDepartment{$this->ticket_index}\" class=\"clientList\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->pDepartment)}\" />";
                if ($this->userType === 'client') {
                  $this->selectID = 'pDepartment'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr>
                        <td><label for=\"pAddress1{$this->ticket_index}\">Address 1:</label></td>
                        <td>
                          <input list=\"addy1\" name=\"pAddress1\" id=\"pAddress1{$this->ticket_index}\" class=\"clientList\" placeholder=\"1234 Main St.\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->pAddress1)}\"  />";
                if ($this->userType === 'client') {
                  $this->selectID = 'pAddress1'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr>
                        <td><label for=\"pAddress2{$this->ticket_index}\">Address 2:</label></td>
                        <td>
                          <input list=\"addy2\" name=\"pAddress2\" id=\"pAddress2{$this->ticket_index}\" class=\"clientList\" placeholder=\"City, State ZIP\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->pAddress2)}\"  />";
                if ($this->userType === 'client') {
                  $this->selectID = 'pAddress2'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr class=\"{$this->countryClass}\">
                        <td><label for=\"pCountry{$this->ticket_index}\">Country:</label></td>
                        <td>
                          <input type=\"hidden\" name=\"pCountry\" id=\"pCountryMarker{$this->ticket_index}\" value=\"{$this->config['ShippingCountry']}\" form=\"request{$this->ticket_index}\" />
                          <input list=\"countries\" name=\"pCountry\" class=\"pCountry\" id=\"pCountry{$this->ticket_index}\" value=\"{$this->countryFromAbbr($this->pCountry)}\" {$this->countryInput} form=\"request{$this->ticket_index}\" />
                        </td>
                      </tr>
                      <tr>
                        <td><label for=\"pContact{$this->ticket_index}\">Contact:</label></td>
                        <td><input list=\"contacts\" name=\"pContact\" id=\"pContact{$this->ticket_index}\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->pContact)}\" form=\"request{$this->ticket_index}\" /></td>
                      </tr>
                      <tr>
                        <td><label for=\"pTelephone{$this->ticket_index}\">Telephone:</label></td>
                        <td><input type=\"text\" name=\"pTelephone\" id=\"pTelephone{$this->ticket_index}\" value=\"{$this->pTelephone}\" form=\"request{$this->ticket_index}\" /></td>
                      </tr>
                      <tr>
                        <td colspan=\"2\">
                          <label for=\"pSigReq{$this->ticket_index}\">Request Signature:  </label>
                          <input type=\"hidden\" name=\"pSigReq\" id=\"pSigReqMarker{$this->ticket_index}\" value=\"0\" form=\"request{$this->ticket_index}\" />
                          <input type=\"checkbox\" name=\"pSigReq\" id=\"pSigReq{$this->ticket_index}\" value=\"1\" {$pSigChecked} form=\"request{$this->ticket_index}\" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
	              </fieldset>
	            </td>
              <td>
                <fieldset form=\"request{$this->ticket_index}\" id=\"deliveryField{$this->ticket_index}\">
                  <legend>Deliver</legend>
                  <table class=\"centerDiv\">
                    <thead {$hideFromDriver}>
                      <tr>
                        <td><label for=\"toMe{$this->ticket_index}\">To Me:</label>
                          <input type=\"hidden\" name=\"toMe\" value=\"0\" form=\"request{$this->ticket_index}\" />
                          <input type=\"checkbox\" id=\"toMe{$this->ticket_index}\" class=\"me\" name=\"toMe\" value=\"1\" {$toMeChecked} form=\"request{$this->ticket_index}\" />
                        </td>
                        <td>
                          <label for=\"onFileD{$this->ticket_index}\">On File:</label>
                          <input type=\"checkbox\" id=\"onFileD{$this->ticket_index}\" class=\"onFile\" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan=\"2\"><hr></td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><label for=\"dClient{$this->ticket_index}\">Client<span class=\"mobileHide\"> Name</span>:</label></td>
                        <td>
                          <input list=\"clientName\" name=\"dClient\" id=\"dClient{$this->ticket_index}\" class=\"clientList\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->dClient)}\" />";
                if ($this->userType === 'client') {
                  $this->selectID = 'dClient'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr>
                        <td><label for=\"dDepartment{$this->ticket_index}\">Department:</label></td>
                        <td>
                          <input list=\"departments\" name=\"dDepartment\" id=\"dDepartment{$this->ticket_index}\" class=\"clientList\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->dDepartment)}\" />";
                if ($this->userType === 'client') {
                  $this->selectID = 'dDepartment'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr>
                        <td><label for=\"dAddress1{$this->ticket_index}\">Address 1:</label></td>
                        <td>
                          <input list=\"addy1\" name=\"dAddress1\" id=\"dAddress1{$this->ticket_index}\" class=\"clientList\" placeholder=\"1234 Main St.\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->dAddress1)}\" />";
                if ($this->userType === 'client') {
                  $this->selectID = 'dAddress1'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr>
                        <td><label for=\"dAddress2{$this->ticket_index}\">Address 2:</label></td>
                        <td>
                          <input list=\"addy2\" name=\"dAddress2\" id=\"dAddress2{$this->ticket_index}\" class=\"clientList\" placeholder=\"City, State ZIP\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->dAddress2)}\" />";
                if ($this->userType === 'client') {
                  $this->selectID = 'dAddress2'; $returnData .= self::buildSelectElement();
                }
                  $returnData .= "</td>
                      </tr>
                      <tr class=\"{$this->countryClass}\">
                        <td><label for=\"dCountry{$this->ticket_index}\">Country:</label></td>
                        <td>
                          <input type=\"hidden\" name=\"dCountry\" id=\"dCountryMarker{$this->ticket_index}\" value=\"{$this->config['ShippingCountry']}\" form=\"request{$this->ticket_index}\" />
                          <input list=\"countries\" name=\"dCountry\" class=\"dCountry\" id=\"dCountry{$this->ticket_index}\" value=\"{$this->countryFromAbbr($this->dCountry)}\" {$this->countryInput} form=\"request{$this->ticket_index}\" />
                        </td>
                      </tr>
                      <tr>
                        <td><label for=\"dContact{$this->ticket_index}\">Contact:</label></td>
                        <td><input list=\"contacts\" name=\"dContact\" id=\"dContact{$this->ticket_index}\" form=\"request{$this->ticket_index}\" value=\"{$this->decode($this->dContact)}\" /></td>
                      </tr>
                      <tr>
                        <td><label for=\"dTelephone{$this->ticket_index}\">Telephone:</label></td>
                        <td colspan=\"2\"><input type=\"text\" name=\"dTelephone\" id=\"dTelephone{$this->ticket_index}\" value=\"{$this->dTelephone}\" form=\"request{$this->ticket_index}\" /></td>
                      </tr>
                      <tr>
                        <td colspan=\"2\">
                          <label for=\"dSigReq{$this->ticket_index}\">Request Signature:  </label>
                          <input type=\"hidden\" name=\"dSigReq\" id=\"dSigReqMarker{$this->ticket_index}\" value=\"0\" form=\"request{$this->ticket_index}\" />
                          <input type=\"checkbox\" name=\"dSigReq\" id=\"dSigReq{$this->ticket_index}\" value=\"1\" {$dSigChecked} form=\"request{$this->ticket_index}\" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
	              </fieldset>
              </td>
            </tr>
            <tr>
              <td>
                <fieldset form=\"request{$this->ticket_index}\" id=\"diField{$this->ticket_index}\">
                  <legend>
                    <label for=\"dryIce{$this->ticket_index}\">Dry Ice:  </label>
                    <input type=\"hidden\" name=\"dryIce\" id=\"dryIceMarker{$this->ticket_index}\" value=\"0\" form=\"request{$this->ticket_index}\" />
                    <input type=\"checkbox\" name=\"dryIce\" id=\"dryIce{$this->ticket_index}\" class=\"dryIce\" value=\"1\" {$dryIceChecked} form=\"request{$this->ticket_index}\" />
                  </legend>
                  <table class=\"centerDiv wide\">
                    <tr>
                      <td colspan=\"2\">&nbsp;</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td title=\"Increments of 5 please\">
                        <label for=\"diWeight{$this->ticket_index}\">Weight:</label>
                        <input type=\"hidden\" name=\"diWeight\" id=\"diWeightMarker{$this->ticket_index}\" class=\"diWeightMarker\" value=\"0\" {$diWeightMarkerDisabled} form=\"request{$this->ticket_index}\" />
                        <input type=\"number\" name=\"diWeight\" id=\"diWeight{$this->ticket_index}\" class=\"diWeight\" form=\"request{$this->ticket_index}\" min=\"0\" step=\"5\" value=\"{$this->diWeight}\" {$diWeightDisabled} />{$this->weightMarker}
                      </td>
                    </tr>
                    <tr>
                      <td colspan=\"2\">&nbsp;</td>
                    </tr>
                  </table>
	              </fieldset>
              </td>
              <td>
                <fieldset form=\"request{$this->ticket_index}\">
                  <legend><label for=\"notes{$this->ticket_index}\">Notes:</label></legend>
                  <textarea rows=\"4\" name=\"notes\" id=\"notes{$this->ticket_index}\" class=\"notes\" form=\"request{$this->ticket_index}\">{$this->Notes}</textarea>
	              </fieldset>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\">
                <input type=\"hidden\" name=\"locationList\" value=\"{$this->locationList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"driverList\" value=\"{$this->driverList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"clientList\" value=\"{$this->clientList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"tClientList\" value=\"{$this->tClientList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"edit\" value=\"0\" />
	              <button class=\"submitForm floatLeft\" type=\"submit\" form=\"request{$this->ticket_index}\">Submit</button> {$cancelTicketEditor}</td>
            </tr>
          </table>
          <p class=\"ticketError\"></p>";
  if ($this->ticketEditor === FALSE) {
    $returnData .= "
          <p class=\"sigNote {$sigNoteClass}\">Unless a specific request to the contrary is made all deliveries will be completed to the best of our ability even if a signature request is declined.</p>
          <p class=\"emailNote {$emailNoteClass}\">Please add noreply@rjdeliveryomaha.com to your contacts. This will prevent notifications from being marked as spam.</p>";
  }
  $returnData .= '
	      </form>
      </div>';
  if ($this->edit === NULL && $this->ticketEditor === FALSE) $returnData .= "
    <div class=\"subContainer\">
      <div id=\"terms\">
        <p class=\"error switch center\">*TERMS</p>
        <p style=\"display: none;\">Responsibility for remittance is implicit when requesting a delivery via this web service. If you do not wish to be held responsible for the payment for services rendered please contact the responsible party and have them request the delivery either on-line or by phone at {$this->config['Telephone']}.<br><br>
        Routine or Round Trip deliveries that are canceled within one hour of being requested will not be billed. Canceled stat deliveries, Routine or Round Trip deliveries canceled more than one hour after being requested, and scheduled deliveries canceled less than two hours prior to pick up will be billed at 77.5%.<br><br>
        Unless a greater value has been placed on a shipment prior to the time that a request for delivery service is made through this web service, it is agreed that in consideration of the rate being charged, the liability of the company for damages is limited to $100.00. If the shipper declares to the company\'s office that the value of the shipment exceeds $100.00, the company can furnish a rate which will provide insurance against damage to, or loss or delay of, the shipment at the higher value so declared by the shipper subject to certain limitations. In any event, we won\'t be liable for any damages whether direct, incidental, special or consequential, in excess of the declared value; including but not limited to loss, of income or profits, whether or not we had knowledge that such damages might be incurred. We will not be liable for your acts or omissions including but not limited to incorrect declaration of cargo, improper or insufficient packing, securing, marking or addressing of your shipment, or for the acts or omissions of the recipient. We will not be liable for loss, damage or delay caused by events we cannot control, including but not limited to acts of God, perils of the air, weather conditions, acts of public enemies, war, strikes, civil commotion or acts or omissions of public authorities including customs and health officials with actual or apparent authority. All complaints regarding damage to, loss or delay of any shipment and any special or consequential damages must be submitted in writing to the company's office, within 15 days of delivery of the shipment.</p>
      </div>
      <div class=\"mapContainer\" id=\"map\"></div>
    </div>";
    return $returnData;
    }

    public function runPriceForm() {
      $returnData = '';
      if ($this->organizationFlag === TRUE) {
        self::buildLocationList();
        $returnData .= self::buildDatalists();
      }
      $returnData .= "
    <div id=\"priceContainer\">
      <form id=\"priceCalc\">
        <table>
          <tbody>
            <tr>
              <td>
                <fieldset id=\"pickUp\" class=\"po\">
                  <legend>Pick Up</legend>
                  <table>
                    <tbody>
                      <tr>
                        <td>
                          <label for=\"pAddress1Calc\">Address 1</label>:
                          <input list=\"addy1\" name=\"pAddress1\" id=\"pAddress1Calc\" class=\"address1\" placeholder=\"4535 Leavenworth St.\" form=\"priceCalc\" required />
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"pAddress2Calc\">Address 2</label>:
                          <input list=\"addy2\" name=\"pAddress2\" id=\"pAddress2Calc\" class=\"address2\" placeholder=\"Omaha, NE 68106\" form=\"priceCalc\" required />
                        </td>
                      </tr>
                      <tr class=\"{$this->countryClass}\">
                        <td>
                          <label for=\"pCountryCalc\">Country</label>:
                          <input type=\"hidden\" name=\"pCountry\" id=\"pCountryMarkerCalc\" value=\"{$this->countryFromAbbr($this->shippingCountry)}\" form=\"request\" />
                          <input list=\"countries\" name=\"pCountry\" class=\"pCountry\" id=\"pCountryCalc\" value=\"{$this->countryFromAbbr($this->pCountry)}\" {$this->countryInput} form=\"request{$this->requireCountry}\" /></td>
                      </tr>
                    </tbody>
                  </table>
                </fieldset>
              </td>
              <td>
                <fieldset id=\"dropOff\" class=\"do\">
                  <legend>Drop Off</legend>
                  <table>
                    <tbody>
                      <tr>
                        <td>
                          <label for=\"dAddress1Calc\">Address 1</label>:
                          <input list=\"addy1\" name=\"dAddress1\" id=\"dAddress1Calc\" class=\"address1\" placeholder=\"4535 Leavenworth St.\" form=\"priceCalc\" required />
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"dAddress2Calc\">Address 2</label>:
                          <input list=\"addy2\" name=\"dAddress2\" id=\"dAddress2Calc\" class=\"address2\" placeholder=\"Omaha NE, 68106\" form=\"priceCalc\" required />
                        </td>
                      </tr>
                      <tr class=\"{$this->countryClass}\">
                        <td>
                          <label for=\"dCountryCalc\">Country</label>:
                          <input type=\"hidden\" name=\"dCountry\" id=\"dCountryMarkerCalc\" value=\"{$this->countryFromAbbr($this->shippingCountry)}\" form=\"request\" />
                          <input list=\"countries\" name=\"dCountry\" class=\"dCountry\" id=\"dCountryCalc\" value=\"{$this->countryFromAbbr($this->dCountry)}\" {$this->countryInput} form=\"request\" {$this->requireCountry} /></td>
                      </tr>
                    </tbody>
                  </table>
                </fieldset>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan=\"2\">
                <span class=\"floatLeft\">
                  <label for=\"CalcCharge\">Charge:</label>
                  <select name=\"charge\" id=\"CalcCharge\" form=\"priceCalc\">
                    {$this->createChargeSelectOptions()}
                  </select>
                </span>
                <span class=\"floatRight\">
                  <label for=\"CalcDryIce\">Dry Ice:</label>
                  <input name=\"dryIce\" id=\"CalcDryIce\" type=\"checkbox\" class=\"dryIce\" value=\"1\" form=\"priceCalc\" />
                  <input type=\"number\" class=\"diWeight diRow\" name=\"diWeight\" id=\"CalcWeight\" value=\"0\" min=\"0\" step=\"5\" title=\"Increments of 5\" form=\"priceCalc\" disabled />{$this->weightMarker}
                </span>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\">
                <button type=\"submit\" class=\"submitPriceQuery floatLeft\" form=\"priceCalc\">Enter Run</button>
                <button type=\"reset\" class=\"clear floatRight\" form=\"priceCalc\">Clear Form</button>
              </td>
            </tr>
          </tfoot>
        </table>
      <div id=\"priceResult\">
        <p class=\"hide\">Range: <span id=\"rangeResult\"></span></p>
        <p>Dry Ice: <span id=\"diWeightResult\"></span><span style=\"display:none;\" class=\"weightMarker\">{$this->weightMarker}</span></p>
        <p>Run Price:<span style=\"display:none;\" class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span><span id=\"runPriceResult\"></span></p>
        <p>Dry Ice Price: <span style=\"display:none;\" class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span><span style=\"min-width:3em;\" id=\"diPriceResult\">&nbsp;</span></p>
        <p>Total: <span style=\"display:none;\" class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span><span id=\"ticketPriceResult\"></span></p>
      </div>
      </form>
      <div id=\"priceContainerFoot\">
        <div>
          <p id=\"pNotice\"></p>
          <p id=\"dNotice\"></p>
          <p id=\"CalcError\"></p>
        </div>
        <p id=\"mapInfo\"><span class=\"standard medium\" title=\"Blue: Standard Delivery Range\">Standard Delivery Range</span>&nbsp;&nbsp;<span class=\"error medium\" title=\"Red: Extended Delivery Range\">Extended Delivery Range</span></p>
        <div class=\"mapContainer\" id=\"map2\"></div>
      </div>
    </div>";
      if ($this->organizationFlag === TRUE) {
        $returnData .= self::buildDatalists();
      }
      return $returnData;
    }

    public function calculateRunPrice() {
      $this->Contract = 0;
      if ($this->ulevel === 1 || $this->ulevel === 2) {
        $this->BillTo = $this->ClientID;
      } else {
        $this->BillTo = NULL;
      }
      if (!self::solveTicketPrice()) {
        return $this->error;
      }
      $this->pCountry = self::countryFromAbbr($this->pCountry);
      $this->dCountry = self::countryFromAbbr($this->dCountry);
      if ($this->generalDiscount !== NULL && $this->generalDiscount !== '') {
        $this->RunPrice *= $this->generalDiscount;
        $this->TicketPrice - $this->RunPrice + $this->diPrice;
      }
      $returnData = [
        'billTo' => $this->BillTo,
        'address1' => "{$this->pAddress1} {$this->pAddress2}, {$this->pCountry}",
        'address2' => "{$this->dAddress1} {$this->dAddress2}, {$this->dCountry}",
        'result1' => $this->loc1,
        'result2' => $this->loc2,
        'center' => $this->center,
        'pRangeTest' => $this->pRangeTest,
        'dRangeTest' => $this->dRangeTest,
        'rangeDisplay' => $this->rangeVal,
        'runPrice' => self::number_format_drop_zero_decimals($this->RunPrice, 2),
        'ticketPrice' => self::number_format_drop_zero_decimals($this->TicketPrice, 2),
        'diWeight' => self::number_format_drop_zero_decimals($this->diWeight, 3),
        'diPrice' => self::number_format_drop_zero_decimals($this->diPrice, 2)
      ];
      return json_encode($returnData);
    }

    public function fetchTodaysTickets() {
      $returnData = '';
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = 'tickets';
      $this->queryData['formKey'] = $this->formKey;
      $this->queryData['queryParams']['include'] = [ 'ticket_index', 'TicketNumber', 'RunNumber', 'BillTo', 'RequestedBy', 'ReceivedDate', 'pClient', 'pDepartment', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'dClient', 'dDepartment', 'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dryIce', 'diWeight', 'diPrice', 'TicketBase', 'Charge', 'Contract', 'Multiplier', 'RunPrice', 'TicketPrice', 'EmailConfirm', 'EmailAddress', 'Notes', 'DispatchTimeStamp', 'DispatchedTo', 'DispatchedBy', 'Transfers', 'TransferState', 'PendingReceiver', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'pTime', 'dTime', 'd2Time', 'pSigReq', 'dSigReq', 'd2SigReq', 'pSigPrint', 'dSigPrint', 'd2SigPrint', 'pSig', 'dSig', 'd2Sig', 'pSigType', 'dSigType', 'd2SigType', 'RepeatClient', 'InvoiceNumber' ];
      $this->queryData['queryParams']['filter'] = [  [ 'Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>(int)$_SESSION['ClientID'] ], [ 'Resource'=>'ReceivedDate', 'Filter'=>'sw', 'Value'=>$this->today->format('Y-m-d') ]  ];
      if (!$this->query = self::createQuery($this->queryData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      if (empty($this->result)) {
        return FALSE;
      }
      $returnData .= '<p class="center">Today\'s Tickets.</p>';
      for ($i = 0; $i < count($this->result); $i++) {
        foreach ($this->result[$i] as $key => $value) {
          $this->{$key} = $value;
        }
        $returnData .= self::regenTicket();
      }
      return $returnData;
    }

    private function confirmRequest() {
      self::solveTicketPrice();
      $editForm = "<form id=\"editForm{$this->ticket_index}\" method=\"post\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\">
          <input type=\"hidden\" name=\"formKey\" value=\"{$this->formKey}\" form=\"editForm{$this->ticket_index}\" />";
      // Set the form name to editForm
      $this->formName = 'editForm' . $this->ticket_index;
      $editForm .= self::hiddenInputs();
      $editForm .= "
          <input type=\"hidden\" name=\"edit\" form=\"editForm{$this->ticket_index}\" value=\"1\" />
        </form>";
      $editButton = "
          <button type=\"submit\" class=\"editForm\" form=\"editForm{$this->ticket_index}\">Edit</button>";

      // Generate a ticket number if one wasn't provided
      if ($this->TicketNumber == NULL) {
        $tempTicketNumber = ($this->BillTo === 0) ? date('m') . '00' : $this->BillTo . date('m') . '00';
        // This ticket number will be tested for uniqueness in the processing function
        $this->TicketNumber = (int)$tempTicketNumber;
      }
      $output = '';
      switch ($this->Charge) {
        case 0:
          $chargeAnswer = '<p class="center rollUp" title="Canceled">Canceled</p>';
        break;
        case 1:
          $chargeAnswer = '<p class="center rollUp" title="1 hour">STAT</p>';
        break;
        case 2:
          $chargeAnswer = '<p class="center rollUp" title="2 hours">ASAP</p>';
        break;
        case 3:
          $chargeAnswer = '<p class="center rollUp" title="3 hours">3 Hours</p>';
        break;
        case 4:
          $chargeAnswer = '<p class="center rollUp" title="4 hours">4 Hours</p>';
        break;
        case 5:
          $chargeAnswer = '<p class="center rollUp" title="3 - 4 hours">Routine</p>';
        break;
        case 6:
         $chargeAnswer = '<p class="center rollUp" title="4 - 6 hours">Round Trip</p>';
        break;
        case 7:
          $chargeAnswer = '<p class="rollUp">Dedicated Run</p>';
        break;
      }
      //Define the display for email confirmation
      switch ($this->EmailConfirm) {
        case 0:
          $emailAnswer = 'None';
        break;
        case 1:
          $emailAnswer = 'Picked Up';
        break;
        case 2:
          $emailAnswer = 'Delivered';
        break;
        case 3:
          $emailAnswer = 'Picked Up &amp; Delivered';
        break;
        case 4:
          $emailAnswer = 'Returned';
        break;
        case 5:
          $emailAnswer = 'Picked Up &amp; Returned';
        break;
        case 6:
          $emailAnswer = 'Delivered &amp; Returned';
        break;
        case 7:
          $emailAnswer = 'At Each Step';
        break;
      }
      // Define the display for signature request
      $sigReqTemp = array();
      if ($this->pSigReq === 1) {
        $sigReqTemp[] = 'On Pick Up';
      }
      if ($this->dSigReq === 1) {
        $sigReqTemp[] = 'On Delivery';
      }
      if ($this->d2SigReq === 1) {
        $sigReqTemp[] = 'On Return';
      }
      if ($this->pSigReq === 0 && $this->dSigReq === 0 && $this->d2SigReq === 0) {
        $sigReqTemp[] = 'None';
      }
      $sigReq = self::arrayToList($sigReqTemp);
      // Generate the hidden form
      // Add the values that we just solved for
      $newTicketInput = ($this->ticket_index === NULL) ? "<input type=\"hidden\" name=\"newTicket\" value=\"1\" form=\"submitTicket{$this->ticket_index}\" />" : "<input type=\"hidden\" name=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"submitTicket{$this->ticket_index}\" />";
      $transfersFormValue = ($this->Transfers == NULL) ? '' : htmlentities(json_encode($this->Transfers), ENT_QUOTES);
      $submitForm = "
            <form id=\"submitTicket{$this->ticket_index}\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
              <input type=\"hidden\" name=\"formKey\" id=\"formKey\" value=\"{$this->formKey}\" />
              {$newTicketInput}
              <input type=\"hidden\" name=\"edit\" value=\"0\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"ticketNumber\" value=\"{$this->TicketNumber}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"billTo\" value=\"{$this->BillTo}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"diPrice\" value=\"{$this->diPrice}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"ticketBase\" value=\"{$this->TicketBase}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"contract\" value=\"{$this->Contract}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"runNumber\" value=\"{$this->RunNumber}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"multiplier\" value=\"1\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"runPrice\" value=\"{$this->RunPrice}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"ticketPrice\" value=\"{$this->TicketPrice}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"dispatchedTo\" value=\"{$this->DispatchedTo}\" form=\"submitTicket{$this->ticket_index}\" />
              <input type=\"hidden\" name=\"transfers\" value=\"{$transfersFormValue}\" form=\"submitTicket{$this->ticket_index}\" />";
      // Set the form name to  submitTicket
      $this->formName = "submitTicket{$this->ticket_index}";
      // Add values in the $postableKeys array
      $submitForm .= self::hiddenInputs();
      $submitForm .= '</form>';
      $submitTicketButton = "<button type=\"submit\" class=\"confirmed\" id=\"submit{$this->ticket_index}\" form =\"submitTicket{$this->ticket_index}\">Confirm</button>";
      // pRangeTest and dRangeTest are set in solveTicketPrice()
      if ($this->pRangeTest > $this->maxRange) {
        // if ($this->userType === "client") $submitTicketButton = '';
        $this->pRangeError = '<p>Pick Up address is outside of our delivery range. Please contact us via phone or email to confirm driver availability.</p>';
      }
      if ($this->dRangeTest > $this->maxRange) {
        // if ($this->userType === "client") $submitTicketButton = '';
        $this->dRangeError = '<p>Delivery address is outside of our delivery range. Please contact us via phone or email to confirm driver availability.</p>';
      }
      // Generate the output
      $output = '
          <div id="deliveryConfirmation">';
      $output .= ($this->edit === 0) ? '<h1>Delivery Confirmation<span class="error">*</span></h1>' : '';
      $output .= $editForm . $submitForm;
      // Display the delivery and dry ice price for clients only
      if ($this->userType === 'driver') {
        $ticketPriceDisplay = '';
        $iceChargeDisplay = '';
        $totalPriceDisplay = '';
      } else {
        if ($this->Charge !== 7) {
          $ticketPriceDisplay = "<span class=\"bold\">Delivery: </span><span class=\"currencySymbol\">{$_SESSION['config']['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->RunPrice, 2)}";
          $iceChargeDisplay = "<span class=\"bold\">Charge: </span><span class=\"currencySymbol\">{$_SESSION['config']['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->diPrice, 2)}";
          $totalPriceDisplay = "<span class=\"bold\">Total: </span><span class=\"currencySymbol\">{$_SESSION['config']['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->TicketPrice, 2)}";
        } else {
          $ticketPriceDisplay = $totalPriceDisplay = '<span class=\"bold\">Total: </span>Pending ';
          $iceChargeDisplay = "<span class=\"bold\">Charge: </span><span class=\"currencySymbol\">{$_SESSION['config']['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->diPrice, 2)}";
        }
      }
      $json_decode = 'json_decode';
      $htmlentities = 'htmlentities';
      // Generate the confirmation display
      $jsVar = 'coords1=' . json_encode($this->loc1) . ',address1="' . htmlentities($this->pAddress1 . ' ' . $this->pAddress2, ENT_QUOTES) . '", coords2=' . json_encode($this->loc2) . ',address2="' . htmlentities($this->dAddress1 . ' ' . $this->dAddress2, ENT_QUOTES) . '",center="' . json_encode($this->center) . ';';
      $output .= "
        <table class=\"ticketContainer\">
          <thead>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Dry Ice:</span>{$this->diWeight}{$this->weightMarker} {$iceChargeDisplay}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Requested By:</span>{$this->RequestedBy}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Email Address:</span>{$this->EmailAddress}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Email Confirmation: </span>{$emailAnswer}</td>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Signature Request: </span>{$sigReq}</td>
            </tr>
            <tr>
              <td colspan=\"2\">{$ticketPriceDisplay} {$totalPriceDisplay}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Notes: </span>{$this->Notes}</td>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td>{$submitTicketButton}</td>
              <td>{$editButton}</td>
            </tr>
            <tr>
              <td colspan=\"2\" class=\"ticketError\"></td>
            </tr>
          </tfoot>
          <tbody>
            <tr class=\"confirmAddress\">
              <td colspan=\"2\">{$chargeAnswer}</td>
            </tr>
            <tr class=\"confirmAddress\">
              <td colspan=\"2\"><hr></td>
            </tr>
            <tr class=\"confirmAddress\">
              <td>
                <table class=\"wide\">
                  <thead>
                    <tr>
                      <th colspan=\"2\">Pick Up</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->pClient}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->pDepartment}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->pAddress1}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->pAddress2}</td>
                    </tr>
                    <tr class=\"{$this->countryClass}\">
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->pCountry}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td><span class=\"bold\">Contact</span>: {$this->pContact}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td><span class=\"bold\">Telephone</span>: {$this->pTelephone}</td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>
                <table class=\"wide\">
                  <thead>
                    <tr>
                      <th colspan=\"2\">Deliver</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->dClient}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->dDepartment}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->dAddress1}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->dAddress2}</td>
                    </tr>
                    <tr class=\"{$this->countryClass}\">
                      <td class=\"ticketSpace\"></td>
                      <td>{$this->dCountry}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td><span class=\"bold\">Contact</span>: {$this->dContact}</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td><span class=\"bold\">Telephone</span>: {$this->dTelephone}</td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
            <tr class=\"confirmAddress\">
              <td colspan=\"2\"><hr></td>
            </tr>
            <tr>
              <td colspan=\"2\">{$this->pRangeError}  {$this->dRangeError}</td>
            </tr>
          </tbody>
        </table>
        <script>{$jsVar}</script>
      </div>";
      return $output;
    }

    private function processTicket() {
      foreach ($this as $key => $value) {
        if (substr($key,1) === 'Country') {
          if (strlen($value) > 2) {
            $this->$key = self::countryFromAbbr($value);
          } else {
            $this->$key = $value;
          }
        }
      }
      if ($this->updateTicket === TRUE) {
        // Do /not/ display the ticket if new transfer is processed
        $regen = TRUE;
        $payload = array();
        foreach ($this as $key => $value) {
          if (in_array($key, $this->updateTicketDatabaseKeys)) {
            if ($key === 'Transfers') {
              $tempArray = json_decode(html_entity_decode($value));
              $target = array();
              for ($i=0;$i<count($tempArray); $i++) {
                $newObj = new \stdClass();
                foreach ($tempArray[$i] as $k => $v) {
                  if ($v == NULL) {
                    $newVal = time();
                    $regen = FALSE;
                  } else {
                    $newVal = $v;
                  }
                  $newObj->$k = $newVal;
                }
                $target[] = $newObj;
              }
              $payload[$key] = $target;
            } else {
              $payload[$key] = self::decode($value);
            }
          }
        }
        $ticketUpdateData['endPoint'] = 'tickets';
        $ticketUpdateData['method'] = 'PUT';
        $ticketUpdateData['formKey'] = $this->formKey;
        $ticketUpdateData['queryParams'] = [];
        $ticketUpdateData['payload'] = $payload;
        $ticketUpdateData['primaryKey'] = $this->ticket_index;
        if (!$ticketUpdate = self::createQuery($ticketUpdateData)) {
          $temp = $this->error;
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          echo $this->error;
          return FALSE;
        }
        $ticketUpdateResult = self::callQuery($ticketUpdate);
        if ($ticketUpdateResult === FALSE) {
          $temp = $this->error;
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          echo $this->error;
          return FALSE;
        }
        if ($regen === TRUE) {
          $this->ticketEditor = TRUE;
          self::regenTicket();
        } else {
          echo 'remove';
        }
        return FALSE;
      }
      if ($this->ticket_index === NULL) {
        if (!self::testTicketNumber()) {
          echo $this->error;
          return FALSE;
        }
      }
      try {
        $this->now = new \dateTime('NOW', $this->timezone);
      } catch(Exception $e) {
        $this->error = __function__ . ' Received Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      $this->ReceivedDate = ($this->ReceivedDate === NULL || $this->ReceivedDate === '') ? $this->now->format('Y-m-d H:i:s') : $this->ReceivedDate;
      if ($this->DispatchedTo != 0) {
        $this->DispatchTimeStamp = ($this->DispatchTimeStamp === NULL || $this->DispatchTimeStamp === '') ? $this->ReceivedDate : $this->DispatchTimeStamp;
        $micro_date = microtime();
        $date_array = explode(" ",$micro_date);
        $this->DispatchMicroTime = ($this->DispatchMicroTime === NULL || $this->DispatchMicroTime === '') ? substr($date_array[0], 1, 7) : $this->DispatchMicroTime;
      }
      // Create a new query object to post the new ticket
      $postTicketData = [];
      foreach ($this as $key => $value) {
        if (in_array($key, $this->newTicketDatabaseKeys) && $value !== NULL) {
          $postTicketData['payload'][$key] = self::decode($value);
        }
      }
      $postTicketData['endPoint'] = 'tickets';
      $postTicketData['method'] = 'POST';
      $postTicketData['formKey'] = $this->formKey;
      $postTicketData['queryParams'] = [];
      if (!$postTicket = self::createQuery($postTicketData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      $postTicketResult = self::callQuery($postTicket);
      if ($postTicketResult === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      echo "
        <div id=\"deliveryRequestComplete\">
          <h1>request submitted</h1>
          <p class=\"center\">Your request has been received.<br>The ticket number for this delivery is {$this->TicketNumber}.</p>
        </div>";
      return FALSE;
    }

    public function processRouteTicket() {
      // multiTicket data has come from the database and doesn't require sanitizing
      $tempTickets = $crun_index_list = array();
      // test each tickets number for uniqueness and solve for price
      $this->mapAvailable = FALSE;
      $this->processingRoute = TRUE;
      for ($i = 0; $i < count($this->multiTicket); $i++) {
        foreach ($this->multiTicket[$i] as $key => $value) {
          if (property_exists($this, $key)) {
            $this->$key = $value;
          }
        }
        if (!self::testTicketNumber()) {
          // failure logged in function
          return FALSE;
        }
        if (!self::solveTicketPrice()) {
          // failure logged in function
          return FALSE;
        }
        $newObj = new \stdClass();
        foreach ($this as $key => $value) {
          if ($key === 'crun_index') $crun_index_list[] = $value;
          if (($key === 'pCountry' || $key === 'dCountry') && strlen($value) != 2) {
            $temp = self::countryFromAbbr($value);
            $value = $temp;
          }
          if (in_array($key, $this->newTicketDatabaseKeys) && $value !== NULL) {
            $newObj->$key = self::decode($value);
          }
        }
        $tempTickets[] = $newObj;
      }
      $this->multiTicket = $tempTickets;
      // Create a new query object to post the new tickets
      $postTicketData['endPoint'] = 'tickets';
      $postTicketData['method'] = 'POST';
      $postTicketData['formKey'] = $this->formKey;
      $postTicketData['queryParams'] = [];
      $postTicketData['payload'] = $this->multiTicket;
      if (!$postTicket = self::createQuery($postTicketData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      $postTest = self::callQuery($postTicket);
      if ($postTest === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      // set $this->now for last completed value. Doing this any earlier will result in $this->now being overwritten as null
      try {
        $this->now = new \dateTime('now', $this->timezone);
      } catch(Exception $e) {
        $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      $updateLastCompletedDateData['endPoint'] = 'contract_runs';
      $updateLastCompletedDateData['method'] = 'PUT';
      $updateLastCompletedDateData['formKey'] = $this->formKey;
      $updateLastCompletedDateData['queryParams'] = [];
      $updateLastCompletedDateData['primaryKey'] = implode(',', $crun_index_list);
      $updateLastCompletedDateData['payload'] = array();
      for ($i = 0; $i < count($crun_index_list); $i++) {
        $newObj = new \stdClass();
        $newObj->LastCompleted = $this->now->format('Y-m-d');
        $updateLastCompletedDateData['payload'][] = $newObj;
      }
      if (!$updateLastCompletedDate = self::createQuery($updateLastCompletedDateData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      $updateResult = self::callQuery($updateLastCompletedDate);
      if ($updateResult === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      return TRUE;
    }

    public function processReturnTicket() {
      $this->mapAvailable = FALSE;
      self::solveTicketPrice();
      self::processTicket();
    }

    public function stepTicket() {
      if ($this->multiTicket !== NULL) {
        $tempIndex = array();
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            if ($key === 'ticket_index') $tempIndex[] = (int)$value;
          }
        }
        $this->ticket_index = implode(',', $tempIndex);
      }
      try {
        $this->now = new \dateTime('now', $this->timezone);
      } catch(Exception $e) {
        $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
        return FALSE;
      }
      $ticketUpdateData['endPoint'] = 'tickets';
      $ticketUpdateData['method']= 'PUT';
      $ticketUpdateData['formKey'] = $this->formKey;
      $ticketUpdateData['queryParams'] = [];
      $ticketUpdateData['primaryKey'] = $this->ticket_index;
      if ($this->multiTicket === NULL) {
        switch($this->step){
          case 'pickedUp':
            $ticketUpdateData['payload']['Notes'] = $this->Notes;
            $ticketUpdateData['payload']['pTimeStamp'] = $this->now->format('Y-m-d H:i:s');
            if ($this->printName !== NULL && $this->printName !== '') {
              $ticketUpdateData['payload']['pSigPrint'] =  $this->printName;
              $ticketUpdateData['payload']['pSigReq'] = 1;
            }
            if ($this->sigImage !== NULL && $this->sigImage !== '') {
              $dataPieces = explode(',', $this->sigImage);
              $ticketUpdateData['payload']['pSigType'] = self::between('/',';',$dataPieces[0]);
              $ticketUpdateData['payload']['pSig'] = base64_encode($dataPieces[1]);
              $ticketUpdateData['payload']['pSigReq'] = 1;
            }
            $this->stepMarker = 'Picked Up';
          break;
          case 'delivered':
            $ticketUpdateData['payload']['Notes'] = $this->Notes;
            $ticketUpdateData['payload']['dTimeStamp'] = $this->now->format('Y-m-d H:i:s');
            if ($this->printName !== NULL && $this->printName !== '') {
              $ticketUpdateData['payload']['dSigPrint'] = $this->printName;
              $ticketUpdateData['payload']['dSigReq'] = 1;
            }
            if ($this->sigImage != NULL && $this->sigImage !== '') {
              $dataPieces = explode(',', $this->sigImage);
              $ticketUpdateData['payload']['dSigType'] = self::between('/',';',$dataPieces[0]);
              $ticketUpdateData['payload']['dSig'] = base64_encode($dataPieces[1]);
              $ticketUpdateData['payload']['dSigReq'] = 1;
            }
            $this->stepMarker = 'Delivered';
          break;
          case 'returned':
            $ticketUpdateData['payload']['d2TimeStamp'] = $this->now->format('Y-m-d H:i:s');
            if ($this->printName !== NULL && $this->printName !== '') {
              $ticketUpdateData['payload']['d2SigPrint'] = $this->printName;
              $ticketUpdateData['payload']['d2SigReq'] = 1;
            }
            if ($this->sigImage != NULL && $this->sigImage !== "") {
              $dataPieces = explode(',', $this->sigImage);
              $ticketUpdateData['payload']['d2SigType'] = between('/',';',$dataPieces[0]);
              $ticketUpdateData['payload']['d2Sig'] = base64_encode($dataPieces[1]);
              $ticketUpdateData['payload']['d2SigReq'] = 1;
            }
            $this->stepMarker = 'Returned';
          break;
          case 'dispatched':
            $this->DispatchTimeStamp = $this->now->format('Y-m-d H:i:s');
            $micro_date = microtime();
            $date_array = explode(" ",$micro_date);
            $this->DispatchMicroTime = substr($date_array[0], 1, 7);
            $ticketUpdateData['payload'] = [ 'DispatchTimeStamp'=>$this->DispatchTimeStamp, 'DispatchMicroTime'=>$this->DispatchMicroTime, 'DispatchedTo'=>$this->DispatchedTo, 'DispatchedBy'=>$this->DispatchedBy ];
            $this->stepMarker = "Dispatched";
          break;
          default:
            $this->error = __function__ . ' Error: Unknown Action Line ' . __line__;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return $this->error;
          break;
        }
      } else {
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          $tempObj = new \stdClass();
          switch ($this->multiTicket[$i]['step']) {
            case 'pickedUp':
              $tempObj->Notes = $this->multiTicket[$i]['notes'];
              $tempObj->pTimeStamp = $this->now->format('Y-m-d H:i:s');
              if ($this->printName !== NULL && $this->printName !== '') {
                $tempObj->pSigPrint = $this->printName;
                $tempObj->pSigReq = 1;
              }
              if ($this->sigImage !== NULL && $this->sigImage !== '') {
                $dataPieces = explode(',', $this->sigImage);
                $tempObj->pSigType = self::between('/',';',$dataPieces[0]);
                $tempObj->pSig = base64_encode($dataPieces[1]);
                $tempObj->pSigReq = 1;
              }
              if ($this->printName !== NULL && $this->printName !== '') $tempObj->pSigReq = 1;
              $ticketUpdateData['payload'][] = $tempObj;
            break;
            case 'delivered':
              $tempObj->Notes = $this->multiTicket[$i]['notes'];
              $tempObj->dTimeStamp = $this->now->format('Y-m-d H:i:s');
              if ($this->printName !== NULL && $this->printName !== '') {
                $tempObj->dSigPrint = $this->printName;
                $tempObj->dSigReq = 1;
              }
              if ($this->sigImage !== NULL && $this->sigImage !== '') {
                $dataPieces = explode(',', $this->sigImage);
                $tempObj->dSigType = self::between('/',';',$dataPieces[0]);
                $tempObj->dSig = base64_encode($dataPieces[1]);
                $tempObj->dSigReq = 1;
              }
              if ($this->printName !== NULL && $this->printName !== '') $tempObj->dSigReq = 1;
              $ticketUpdateData['payload'][] = $tempObj;
            break;
            case 'returned':
              $tempObj->Notes = $this->multiTicket[$i]['notes'];
              $tempObj->d2TimeStamp = $this->now->format('Y-m-d H:i:s');
              if ($this->printName !== NULL && $this->printName !== '') {
                $tempObj->d2SigPrint = $this->printName;
                $tempObj->d2SigReq = 1;
              }
              if ($this->sigImage !== NULL && $this->sigImage !== '') {
                $dataPieces = explode(',', $this->sigImage);
                $tempObj->d2SigType = self::between('/',';',$dataPieces[0]);
                $tempObj->d2Sig = base64_encode($dataPieces[1]);
                $tempObj->d2SigReq = 1;
              }
              if ($this->printName !== NULL && $this->printName !== '') $tempObj->d2SigReq = 1;
              $ticketUpdateData['payload'][] = $tempObj;
            break;
            case 'dispatched':
              $this->DispatchTimeStamp = $this->now->format('Y-m-d H:i:s');
              $micro_date = microtime();
              $date_array = explode(' ',$micro_date);
              $this->DispatchMicroTime = substr($date_array[0], 1, 7);
              $tempObj->DispatchMicroTime = $this->DispatchTimeStamp;
              $tempObj->DispatchMicroTime = $this->DispatchMicroTime;
              $tempObj->DispatchedTo = $this->DispatchedTo;
              $tempObj->DispatchedBy = $this->DispatchedBy;
              $ticketUpdateData['payload'][] = $tempObj;
            break;
            default:
              $this->error = __function__ . ' Error: Unknown Action Line ' . __line__;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              return $this->error;
            break;
          }
        }
      }
      if (!$ticketUpdate = self::createQuery($ticketUpdateData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
        return FALSE;
      }
      $updateResult = self::callQuery($ticketUpdate);
      if ($updateResult === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
        return FALSE;
      }
      if ($this->multiTicket === NULL) {
        $marker = "{$this->TicketNumber} {$this->stepMarker}";
        if ($this->Charge === 7) {
          self::solveDedicatedRunPrice();
        }
        if (self::sendEmail() === TRUE) {
          self::processEmail();
        }
      } else {
        $marker = 'group updated';
        $lower = [ 'ticket_index', 'step' ];
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            if (!in_array($key, $lower)) {
              if ($this->updateProperty(ucfirst($key), $value) === FALSE) {
                $ucfirst = 'ucfirst';
                $this->error = __function__ . ' Error Line ' . __line__ . ": unable to set {$ucfirst($key)} => $value";
                if ($this->enableLogging !== FALSE) self::writeLoop();
                return $this->error;
              }
            } else {
              if (!$this->updateProperty($key, $value)) {
                $this->error = __function__ . ' Error Line ' . __line__ . ": unable to set $key => $value";
                if ($this->enableLogging !== FALSE) self::writeLoop();
                return $this->error;
              }
            }
            if ($this->Charge === 7) $this->solveDedicatedRunPrice();
            if ($this->sendEmail() === TRUE) $this->processEmail();
          }
        }
      }
      return "<p class=\"center\">Ticket {$marker}.</p>";
    }

    public function cancelTicket() {
      if ($this->multiTicket !== NULL) {
        $tempIndex = [];
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            if ($key === 'ticket_index') $tempIndex[] = (int)$value;
            if ($key === 'action') $this->action = $value;
          }
        }
        $this->ticket_index = implode(',', $tempIndex);
      }
      $this->processTransfer = ($this->TransferState !== NULL);
      $ticketUpdateData['endPoint'] = 'tickets';
      $ticketUpdateData['method'] = 'PUT';
      $ticketUpdateData['formKey'] = $this->formKey;
      $ticketUpdateData['queryParams'] = [];
      $ticketUpdateData['primaryKey'] = $this->ticket_index;
      switch ($this->action) {
        case 'delete':
          $ticketUpdateData['method'] = 'DELETE';
          $answer = 'deleted';
        break;
        case 'cancel':
          if ($this->multiTicket === NULL) {
            $ticketUpdateData['payload'] = [ 'Charge'=>'0', 'TicketPrice'=>'0', 'pTimeStamp'=>$this->today->format('Y-m-d H:i:s'), 'Notes'=>$this->Notes ];
          } else {
            $ticketUpdateData['payload'] = [];
            for ($i = 0; $i < count($this->multiTicket); $i++) {
              $tempObj = new \stdClass();
              $tempObj->Charge = 0;
              $tempObj->TicketPrice = 0;
              $tempObj->pTimeStamp = $this->today->format('Y-m-d H:i:s');
              $tempObj->Notes = $this->multiTicket[$i]['Notes'];
              $ticketUpdateData['payload'][] = $tempObj;
            }
          }
          $answer = 'cancelled';
        break;
        case 'deadRun':
          if ($this->multiTicket === NULL) {
            $newPrice = self::number_format_drop_zero_decimals($this->TicketBase * $this->config['DeadRun'], 2);
            $ticketUpdateData['payload'] = array('Charge'=>'8', 'pTimeStamp'=>$this->today->format('Y-m-d H:i:s'), 'RunPrice'=>$newPrice, 'TicketPrice'=>$newPrice, 'Notes'=>$this->Notes);
          } else {
            $ticketUpdateData['payload'] = [];
            for ($i = 0; $i < count($this->multiTicket); $i++) {
              $tempObj = new \stdClass();
              $multiTicketIndex = self::recursive_array_search($this->multiTicket[$i]['ticket_index'], $this->sanitized);
              $newPrice = self::number_format_drop_zero_decimals($this->sanitized[$multiTicketIndex]['TicketBase'] * $this->config['DeadRun'], 2);
              $tempObj->Charge = 8;
              $tempObj->TicketPrice = $newPrice;
              $tempObj->RunPrice = $newPrice;
              $tempObj->pTimeStamp = $this->today->format('Y-m-d H:i:s');
              $tempObj->Notes = $this->multiTicket[$i]['Notes'];
              $ticketUpdateData['payload'][] = $tempObj;
            }
          }
          $answer = 'marked as Dead Run';
        break;
        case 'declined':
          if ($this->multiTicket === NULL) {
            switch ($this->Charge) {
              case 1:
              case 2:
              case 3:
              case 4:
              case 5:
                $newPrice = self::number_format_drop_zero_decimals(($this->TicketBase * 2), 2);
                $ticketUpdateData['payload'] = [ 'dryIce'=>0, 'diPrice'=>0, 'Charge'=>6, 'dTimeStamp'=>$this->today->format('Y-m-d H:i:s'), 'RunPrice'=>$newPrice, 'TicketPrice'=>$newPrice, 'Notes'=>"Delivery declined.\n" . $this->Notes ];
              break;
            }
          } else {
            $ticketUpdateData['payload'] = [];
            for ($i = 0; $i < count($this->multiTicket); $i++) {
              $tempObj = new \stdClass();
              $multiTicketIndex = self::recursive_array_search($this->multiTicket[$i]['ticket_index'], $this->sanitized);
              switch ($this->sanitized[$multiTicketIndex]['Charge']) {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                  $newPrice = self::number_format_drop_zero_decimals(($this->sanitized[$multiTicketIndex]['TicketBase'] * 2), 2);
                  $tempObj->dryIce = 0;
                  $tempObj->diPrice = 0;
                  $tempObj->Charge = 6;
                  $tempObj->dTimeStamp = $this->today->format('Y-m-d H:i:s');
                  $tempObj->RunPrice = $newPrice;
                  $tempObj->TicketPrice = $newPrice;
                  $tempObj->Notes = $this->sanitized[$multiTicketIndex]['Notes'];
              }
              $ticketUpdateData['payload'][] = $tempObj;
            }
          }
          $answer = 'marked for Return';
        break;
        case 'transfer':
          switch ($this->TransferState) {
            /* TransferState will be bool to and from the API
             * Here it is used to indicate how to process a transfer
             * 1: Initiate Transfer
             * 2: Cancel Transfer
             * 3: Decline Transfer
             * 4: Accept Transfer
            */
            case 1:
              if ($this->multiTicket === NULL) {
                $ticketUpdateData['payload'] = [ 'TransferState'=>(int)$this->TransferState, 'PendingReceiver'=>(int)$this->PendingReceiver, 'Notes'=>$this->Notes ];
              } else {
                $ticketUpdateData['payload'] = [];
                for ($i = 0; $i < count($this->multiTicket); $i++) {
                  $this->receiverName = self::test_input(self::before_last(';', $this->multiTicket[$i]['pendingReceiver']));
                  $tempObj = new \stdClass();
                  $tempObj->TransferState = (int)$this->multiTicket[$i]['transferState'];
                  $tempObj->PendingReceiver = self::test_int(self::after_last(';', $this->multiTicket[$i]['pendingReceiver']));
                  $tempObj->Notes = $this->multiTicket[$i]['notes'];
                  $ticketUpdateData['payload'][] = $tempObj;
                }
              }
              $answer = "transferred to {$this->receiverName}";
            break;
            case 2:
              if ($this->multiTicket === NULL) {
                $ticketUpdateData['payload'] = [ 'TransferState'=>0, 'PendingReceiver'=>(int)$this->PendingReceiver, 'Notes'=>$this->Notes, 'DispatchedTo'=>$this->DispatchedTo ];
              } else {
                $ticketUpdateData['payload'] = [];
                for ($i = 0; $i < count($this->multiTicket); $i++) {
                  $multiTicketIndex = self::recursive_array_search($this->multiTicket[$i]['ticket_index'], $this->sanitized);
                  $tempObj = new \stdClass();
                  $tempObj->TransferState = 0;
                  $tempObj->PendingReceiver =self::test_int(self::after_last(';', $this->multiTicket[$i]['pendingReceiver']));
                  $tempObj->Notes = $this->multiTicket[$i]['notes'];
                  $ticketUpdateData['payload'][] = $tempObj;
                }
              }
              $answer = 'transfer cancelled';
            break;
            case 3:
              if ($this->multiTicket === NULL) {
                $ticketUpdateData['payload'] = [ 'TransferState'=>0, 'PendingReceiver'=>(int)$this->driverID, 'Notes'=>$this->Notes, 'DispatchedTo'=>$this->DispatchedTo ];
              } else {
                $ticketUpdateData['payload'] = [];
                for ($i = 0; $i < count($this->multiTicket); $i++) {
                  $multiTicketIndex = self::recursive_array_search($this->multiTicket[$i]['ticket_index'], $this->sanitized);
                  $tempObj = new \stdClass();
                  $tempObj->TransferState = 0;
                  $tempObj->PendingReceiver = (int)$this->driverID;
                  $tempObj->Notes = $this->multiTicket[$i]['notes'];
                  $tempObj->DispatchedTo = $this->sanitized[$multiTicketIndex]['DispatchedTo'];
                  $ticketUpdateData['payload'][] = $tempObj;
                }
              }
              $answer = 'transfer declined';
            break;
            case 4:
              if ($this->multiTicket === NULL) {
                $tempTransfer = new \stdClass();
                $tempTransfer->holder = (int)$this->DispatchedTo;
                $tempTransfer->receiver = (int)$this->driverID;
                $tempTransfer->transferredBy = "2.{$this->DispatchedTo}";
                $tempTransfer->timestamp = time();
                if ($this->Transfers === NULL || $this->Transfers === '') {
                  $this->Transfers = [ $tempTransfer ];
                } else {
                  $tempArray = json_decode($this->Transfers);
                  $tempArray[] = $tempTransfer;
                  $this->Transfers = $tempArray;
                }
                $ticketUpdateData['payload'] = [ 'TransferState'=>0, 'PendingReceiver'=>0, 'Notes'=>$this->Notes, 'DispatchedTo'=>$this->driverID, "Transfers"=>$this->Transfers ];
              } else {
                $ticketUpdateData['payload'] = [];
                for ($i = 0; $i < count($this->multiTicket); $i++) {
                  $multiTicketIndex = self::recursive_array_search($this->multiTicket[$i]['ticket_index'], $this->sanitized);
                  $tempObj = new \stdClass();
                  $tempTransfer = new \stdClass();
                  $tempTransfer->holder = (int)$this->sanitized[$multiTicketIndex]['DispatchedTo'];
                  $tempTransfer->receiver = (int)$this->driverID;
                  $tempTransfer->transferredBy = "2.{$this->sanitized[$multiTicketIndex]['DispatchedTo']}";
                  $tempTransfer->timestamp = time();
                  if ($this->sanitized[$multiTicketIndex]['Transfers'] === NULL || $this->sanitized[$multiTicketIndex]['Transfers'] === '') {
                    $tempObj->Transfers = [ $tempTransfer ];
                  } else {
                    $tempArray = json_decode($this->sanitized[$multiTicketIndex]['Transfers']);
                    $tempArray[] = $tempTransfer;
                    $tempObj->Transfers = $tempArray;
                  }
                  $tempObj->TransferState = 0;
                  $tempObj->PendingReceiver = 0;
                  $tempObj->Notes = $this->multiTicket[$i]['notes'];
                  $tempObj->DispatchedTo = $this->driverID;
                  $ticketUpdateData['payload'][] = $tempObj;
                }
              }
              $answer = 'transfer accepted';
            break;
            default:
              $this->error = __function__ . ' Invalid Transfer State Line ' . __line__ . ': ' . $this->TransferState;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              return $this->error;
            break;
          }
        break;
        default:
          $this->error = __function__ . ' Line ' . __line__ . ": Action {$this->action} not recognised.";
          if ($this->enableLogging !== FALSE) self::writeLoop();
          echo $this->error;
          return FALSE;
        break;
      }
      if (!$ticketUpdate = self::createQuery($ticketUpdateData)) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      $updateResult = self::callQuery($ticketUpdate);
      if ($updateResult === FALSE) {
        $temp = $this->error;
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        echo $this->error;
        return FALSE;
      }
      $marker = ($this->multiTicket === NULL) ? $this->TicketNumber : 'group';
      echo "Ticket {$marker} {$answer}.";
      return FALSE;
    }
  }
