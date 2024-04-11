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
    protected $newTicket = false;
    private $forDisatch = false;
    protected $ticketEditor = false;
    protected $updateTicket = false;
    protected $processTransfer = false;
    protected $crun_index;
    protected $ticket_index;
    protected $TicketNumber;
    protected $RunNumber;
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
    protected $diWeight;
    protected $diPrice;
    protected $TicketBase;
    protected $OldBase;
    protected $Charge;
    protected $Contract;
    protected $Multiplier;
    protected $RunPrice;
    protected $VATable;
    protected $VATrate;
    protected $VATtype;
    protected $VATableIce;
    protected $VATrateIce;
    protected $VATtypeIce;
    protected $TicketPrice;
    protected $Notes;
    protected $EmailConfirm;
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
    protected $DispatchedTo;
    protected $driverID;
    protected $FirstName;
    protected $LastName;
    protected $DriverName;
    protected $ReceivedDate;
    protected $DispatchTimeStamp;
    protected $DispatchMicroTime;
    protected $ReadyDate;
    protected $ReceivedReady = 1;
    protected $DispatchedBy;
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
    protected $pLat;
    protected $pLng;
    protected $dLat;
    protected $dLng;
    protected $d2Lat;
    protected $d2Lng;
    protected $pTime;
    protected $dTime;
    protected $d2Time;
    protected $InvoiceNumber = '-';
    protected $toMe;
    protected $fromMe;
    protected $PriceOverride;
    protected $step;
    protected $noReturn;
    protected $transferredBy;
    private $stepMarker;
    private $driverDatalist;
    private $dispatchForm;
    protected $ticketEditorSearchDate;
    // Ticket values that should not be included on datalists
    protected $ignoreValues = [];
    // bool flag indicating if a map will be displayed for price calculation
    protected $mapAvailable = true;
    // Other needed properties
    private $activeTicketSet = [];
    private $today;
    public $index = 0;
    public $edit;
    private $memberInput;
    private $ticketNumberList;
    private $selectID;
    private $formName;
    private $userType;
    protected $renderPDF;
    // variables for creating and calling queries
    private $query;
    private $queryData;
    private $result;
    // Default dateTime value in the database
    private $tTest;
    // Define the type of form to create charge options for
    private $formType;
    private $newTicketDatabaseKeys = [ 'Contract', 'RunNumber', 'TicketNumber', 'TicketBase', 'BillTo', 'RepeatClient',
      'RequestedBy', 'pClient', 'dClient', 'pDepartment', 'dDepartment', 'pAddress1', 'dAddress1', 'pAddress2',
      'dAddress2', 'pCountry', 'dCountry', 'pContact', 'dContact', 'pTelephone', 'dTelephone', 'dryIce', 'diWeight',
      'diPrice', 'Charge', 'RunPrice', 'VATable', 'VATrate', 'VATtype', 'VATableIce', 'VATrateIce', 'VATtypeIce',
      'TicketPrice', 'EmailConfirm', 'EmailAddress', 'Telephone', 'pTime', 'dTime', 'd2Time', 'pSigReq', 'dSigReq',
      'd2SigReq', 'DispatchedTo', 'ReceivedDate', 'ReceivedReady', 'ReadyDate', 'DispatchTimeStamp',
      'DispatchMicroTime', 'DispatchedBy', 'Notes'
    ];
    private $updateTicketDatabaseKeys = [ 'BillTo', 'Charge', 'EmailAddress', 'EmailConfirm', 'Telephone',
      'RequestedBy', 'pClient', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'dClient',
      'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dryIce', 'diWeight', 'diPrice',
      'ReceivedReady', 'ReadyDate', 'DispatchedTo', 'Transfers', 'TicketBase', 'RunPrice', 'VATable', 'VATrate',
      'VATtype', 'VATableIce', 'VATrateIce', 'VATtypeIce', 'TicketPrice', 'Notes', 'pSigReq', 'dSigReq', 'd2SigReq',
      'pLat', 'pLng', 'dLat', 'dLng', 'd2Lat', 'd2Lng'
    ];
    private $postableKeys = [ 'repeatClient', 'fromMe', 'pClient', 'pDepartment', 'pAddress1', 'pAddress2', 'pCountry',
      'pContact', 'pTelephone', 'pSigReq', 'toMe', 'dClient', 'dDepartment', 'dAddress1', 'dAddress2', 'dCountry',
      'dContact', 'dTelephone', 'dSigReq', 'dryIce', 'diWeight', 'Notes', 'Charge', 'ReceivedReady', 'ReadyDate',
      'DispatchedTo', 'd2SigReq', 'EmailAddress', 'EmailConfirm', 'Telephone', 'RequestedBy', 'locationList',
      'clientList', 'tClientList', 'driverList', 'DispatchTimeStamp', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp',
      'BillTo', 'ticket_index'
    ];
    private $javascriptKeys = [ 'ClientName', 'Department', 'ShippingAddress1', 'ShippingAddress2', 'ShippingCountry' ];
    // Results form geocoder
    private $result1obj;
    private $result2obj;
    private $result1;
    private $result2;
    private $loc1;
    private $loc2;
    private $center;
    private $geocoder;
    private $guzzleConfig = [ 'timeout' => 2.0, 'verify' => false ];
    private $guzzle;
    private $adapter;
    private $dumper;
    private $chain;
    private $dLatR;
    private $dLngR;
    private $pi80 = M_PI / 180;
    private $MER = 6372.797; // Mean Earth Radius in km
    private $angle;
    private $greatCircleDistance;
    private $processingRoute = false;
    // list of providers supported by php/Geocoder
    private $providers = [ 'AlgoliaPlaces', 'ArcGISOnline', 'BingMaps', 'FreeGeoIp', 'GeoIP', 'GeoIP2', 'GeoIPs',
      'GeoPlugin', 'Geonames', 'GoogleMaps', 'Here', 'HostIp', 'IpInfo', 'IpInfoDb', 'Ipstack', 'LocationIQ', 'MapQuest',
      'MapBox', 'Mapzen', 'MaxMind', 'MaxMindBinary', 'Nominatim', 'OpenCage', 'PickPoint', 'TomTom', 'Yandex'
    ];
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
    private $geoRetry;
    // Variables for the function stepTicket
    protected $sigImage;
    protected $sigType;
    protected $printName;
    protected $latitude;
    protected $longitude;

    public function __construct($options, $data=[])
    {
      try {
        parent::__construct($options, $data);
      } catch (\Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        throw $e;
      }
      if ($this->noSession === false) {
        $this->driverID = (isset($_SESSION['driver_index'])) ? $_SESSION['DriverID'] : null;
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
        } catch (\Exception $e) {
          $this->error .= "\nLine " . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== false) self::writeLoop();
          throw $e;
        }
        try {
          $this->today = new \dateTime('NOW', $this->timezone);
        } catch (\Exception $e) {
          $this->error .= "\nDate Error Line " . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== false) self::writeLoop();
          throw $e;
        }
        $temp = json_decode($this->config['Geocoders']);
        $this->geoRetry = (count((array)$temp) < 5) ?
          5 : count((array)$temp);
      }
      // forms will send ticketNumber, contract, charge while the API and this class expect TicketNumber, Contract, Charge
      if ($this->ticketNumber !== null) {
        $this->TicketNumber = $this->ticketNumber;
      }
      if ($this->contract !== null) {
        $this->Contract = $this->contract;
      }
      if ($this->charge !== null) {
        $this->Charge = $this->charge;
      }
    }

    protected function clearTicket()
    {
      $keysToClear = [];
      for ($i = 0; $i < count($this->newTicketDatabaseKeys); $i++) {
        if ($this->newTicketDatabaseKeys !== 'RepeatClient') $keysToClear[] = $this->newTicketDatabaseKeys[$i];
      }
      $keysToClear[] = 'ticket_index';
      foreach ($this as $key => $value) {
        if (in_array($key, $keysToClear)) { $this->{$key} = null; }
      }
    }

    private function rangeTest()
    {
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
        default:
          return false;
      }
      $this->rangeLoc1['lat'] *= $this->pi80;
      $this->rangeLoc1['lng'] *= $this->pi80;
      $this->rangeLoc2['lat'] *= $this->pi80;
      $this->rangeLoc2['lng'] *= $this->pi80;

      $this->dLatR = $this->rangeLoc2['lat'] - $this->rangeLoc1['lat'];

      $this->dLngR = $this->rangeLoc2['lng'] - $this->rangeLoc1['lng'];

      $this->angle = sin($this->dLatR / 2) * sin($this->dLatR / 2) +
        cos($this->rangeLoc1['lat']) * cos($this->rangeLoc2['lat']) * sin($this->dLngR / 2) * sin($this->dLngR / 2);

      $this->greatCircleDistance = 2 * atan2(sqrt($this->angle), sqrt(1 - $this->angle));
      // Distance in km
      switch ($this->rangeFlag) {
        case 0:
          return $this->rangeVal = round($this->MER * $this->greatCircleDistance, 2);
        case 1:
          return $this->pRangeTest = round($this->MER * $this->greatCircleDistance, 2);
        case 2:
          return $this->dRangeTest = round($this->MER * $this->greatCircleDistance, 2);
      }
    }

    private function setVATrates()
    {
      if (self::test_bool($this->config['ApplyVAT']) === false) {
        return $this->VATable =
        $this->VATtype =
        $this->VATtypeIce =
        $this->VATableIce =
        $this->VATrate =
        $this->VATrateIce = 0;
      }
      $this->VATable = 1;
      $this->VATableIce = 1;
      $clientMarker = (self::test_bool($this->RepeatClient) === true) ? $this->BillTo : "t{$this->BillTo}";
      $this->VATtype = $this->members[$clientMarker]->getProperty('VATtype');
      $this->VATtypeIce = $this->members[$clientMarker]->getProperty('VATtypeIce');

      switch ($this->VATtype) {
        case 1:
          $this->VATrate = $this->members[0]->getProperty('StandardVAT');
          break;
        case 2:
          $this->VATrate = $this->members[0]->getProperty('ReducedVAT');
          break;
        case 3:
          $this->VATrate = $this->members[$clientMarker]->getProperty('StandardVAT');
          break;
        case 4:
          $this->VATrate = $this->members[$clientMarker]->getProperty('ReducedVAT');
          break;
        case 5:
          // Zero-Rated
          // no break
        case 6:
          // Exempt
          $this->VATrate = 0;
          break;
        default:
          $this->VATtype = 
          $this->VATable = 
          $this->VATrate = 0;
          break;
      }

      switch ($this->VATtypeIce) {
        case 1:
          $this->VATrateIce = $this->members[0]->getProperty('StandardVAT');
          break;
        case 2:
          $this->VATrateIce = $this->members[0]->getProperty('ReducedVAT');
          break;
        case 3:
          $this->VATrateIce = $this->members[$clientMarker]->getProperty('StandardVAT');
          break;
        case 4:
          $this->VATrateIce = $this->members[$clientMarker]->getProperty('ReducedVAT');
          break;
        case 5:
          // Zero-Rated
          // no break
        case 6:
          // Exempt
          $this->VATrateIce = 0;
          break;
        default:
          $this->VATtypeIce = 
          $this->VATableIce = 
          $this->VATrateIce = 0;
          break;
      }
    }

    private function solveTicketPrice()
    {
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
      if ($this->ticket_index !== null) {
        $data['method'] = 'GET';
        $data['endPoint'] = 'tickets';
        $data['queryParams'] = [];
        $data['queryParams']['filter'] = [ ['Resource'=>'ticket_index', 'Filter'=>'eq', 'Value'=>$this->ticket_index] ];
        if (!$ticketQuery = self::createQuery($data)) {
          $temp = $this->error;
          $this->error = ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          return false;
        }
        $ticketQueryResult = self::callQuery($ticketQuery);
        if ($ticketQueryResult === false) {
          $temp = $this->error;
          $this->error = ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          return false;
        }
        $testTicket = $ticketQueryResult[0];
        if (self::test_bool($testTicket['Contract']) === true && $testTicket['RunNumber'] !== 0) {
          $contractRunQueryData['endPoint'] = 'contract_runs';
          $contractRunQueryData['method'] = 'GET';
          $contractRunQueryData['queryParams'] = [];
          $contractRunQueryData['queryParams']['filter'] = [
            ['Resource'=>'RunNumber', 'Filter'=>'eq', 'Value'=>$testTicket['RunNumber']]
          ];
          if (!$contractRunQuery = self::createQuery($contractRunQueryData)) {
            $temp = $this->error;
            $this->error = ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== false) self::writeLoop();
            return false;
          }
          $contractRunQueryResult = self::callQuery($contractRunQuery);
          if ($contractRunQueryResult === false) {
            $temp = $this->error;
            $this->error = ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== false) self::writeLoop();
            return false;
          }
          if (!empty($contractRunQueryResult[0])) {
            foreach($contractRunQueryResult[0] as $key => $value) {
              $testTicket[$key] = $value;
            }
          }
        }
        $this->PriceOverride = (isset($testTicket['PriceOverride'])) ? $testTicket['PriceOverride'] : 0;
        // If the neither address has changed set flag to prevent recalculating TicketBase
        $testP = $this->pAddress1 . $this->pAddress2 . $this->pCountry;
        $originP = $testTicket['pAddress1'] . $testTicket['pAddress2'] . $testTicket['pCountry'];
        $testD = $this->dAddress1 . $this->dAddress2 . $this->dCountry;
        $originD = $testTicket['dAddress1'] . $testTicket['dAddress2'] . $testTicket['dCountry'];
        if ($testP === $originP && $testD === $originD) {
          $this->PriceOverride = 1;
          $this->TicketBase = $testTicket['TicketBase'];
        }
        $this->Contract = $testTicket['Contract'];
      }

      if (self::test_bool($this->PriceOverride) === false && $this->Charge !== 7) self::getTicketBase();
      
      switch ($this->Charge) {
        case 1:
          $this->RunPrice = $this->TicketBase * $this->config['OneHour'];
          break;
        case 2:
          $this->RunPrice = $this->TicketBase * $this->config['TwoHour'];
          break;
        case 3:
          $this->RunPrice = $this->TicketBase * $this->config['ThreeHour'];
          break;
        case 4:
          $this->RunPrice = $this->TicketBase * $this->config['FourHour'];
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
          $this->RunPrice = $this->TicketBase * $this->config['DeadRun'];
          break;
        case 9:
          // credit will currently not be a case here
          break;
        default:
          $this->RunPrice = $this->TicketBase;
          break;
      }
      $this->diPrice = (self::test_bool($this->dryIce) === true) ? $this->diWeight * $this->config['diPrice'] : 0;
      if ($this->Charge === 7) return true;

      self::setVATrates();

      $deliveryVAT = ($this->config['ApplyVAT'] == true && $this->VATable == true) ? 1 + ($this->VATrate / 100) : 1;
      $iceVAT = ($this->config['ApplyVAT'] == true && $this->VATableIce == true) ? 1 + ($this->VATrateIce / 100) : 1;

      $this->TicketPrice =
        self::number_format_drop_zero_decimals(($this->RunPrice * $deliveryVAT) + ($this->diPrice * $iceVAT), 2);
      return true;
    }

    private function solveDedicatedRunPrice()
    {
      self::queryTicket();
      // Define the start and end times based on return signature request
      if (self::test_bool($this->d2SigReq) === true) {
        if ($this->d2TimeStamp !== $this->tTest) {
          try {
            $start = new \dateTime($this->pTimeStamp, $this->timezone);
          } catch (\Exception $e) {
            $this->error = ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== false) self::writeLoop();
            return false;
          }
          try {
            $end = new \dateTime($this->d2TimeStamp, $this->timezone);
          } catch (\Exception $e) {
            $this->error = ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== false) self::writeLoop();
            return false;
          }
        } else {
          return;
        }
      } else {
        if ($this->dTimeStamp !== $this->tTest) {
          try {
            $start = new \dateTime($this->pTimeStamp, $this->timezone);
          } catch (\Exception $e) {
            $this->error = ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== false) self::writeLoop();
            return false;
          }
          try {
            $end = new \dateTime($this->dTimeStamp, $this->timezone);
          } catch (\Exception $e) {
            $this->error = ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
            if ($this->enableLogging !== false) self::writeLoop();
            return false;
          }
        } else {
          return;
        }
      }
      $interval = date_diff($start, $end);
      $seconds = $interval->days*86400 + $interval->h*3600 + $interval->i*60 + $interval->s;
      $rate = $this->TicketBase / 3600;
      $payload['RunPrice'] = self::number_format_drop_zero_decimals(($seconds * $rate), 2);
      self::setVATrates();
      $deliveryVAT = ($this->config['ApplyVAT'] === true && $this->VATable === true) ? 1 + ($this->VATrate / 100) : 1;
      $iceVAT = ($this->config['ApplyVAT'] === true && $this->VATableIce === true) ? 1 + ($this->VATrateIce / 100) : 1;
      $payload['TicketPrice'] =
        self::number_format_drop_zero_decimals(($payload['RunPrice'] * $deliveryVAT) + ($this->diPrice * $iceVAT), 2);
      $updateTicketPriceData['endPoint'] = 'tickets';
      $updateTicketPriceData['method'] = 'PUT';
      $updateTicketPriceData['primaryKey'] = $this->ticket_index;
      $updateTicketPriceData['payload'] = $payload;
      $updateTicketPriceData['queryParams'] = [];
      if (!$updateTicketPrice = self::createQuery($updateTicketPriceData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return;
      }
      $updateTicketPriceResult = self::callQuery($updateTicketPrice);
      if ($updateTicketPriceResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return;
      }
      return true;
    }

    private function getTicketBase()
    {
      // clear results as this might not be the first try
      $this->result1 = $this->result2 = null;
      if (strlen($this->pCountry) === 2) {
        $this->pCountry = self::countryFromAbbr($this->pCountry);
      }
      if (strlen($this->dCountry) === 2) {
        $this->dCountry = self::countryFromAbbr($this->dCountry);
      }
      $addy1 = "{$this->pAddress1} {$this->pAddress2}, {$this->pCountry}";
      $addy2 = "{$this->dAddress1} {$this->dAddress2}, {$this->dCountry}";
      $addy1GeocoderAdmin1 = trim(self::after(',', self::before_last(' ', $this->pAddress2)));
      $addy2GeocoderAdmin1 = trim(self::after(',', self::before_last(' ', $this->dAddress2)));
      // Load the Geocoder
      $this->geocoder = new \Geocoder\ProviderAggregator();
      $this->guzzle = new GuzzleClient($this->guzzleConfig);
      $this->adapter  = new GuzzleAdapter($this->guzzle);
      $this->dumper = new \Geocoder\Dumper\GeoJson();
      $geoProviders = json_decode($this->config['Geocoders']);
      if (json_last_error() !== JSON_ERROR_NONE) {
        if ($this->enableLogging === true) {
          $this->error = 'getTicketBase failure line ' . __line__ . '. ' . json_last_error_msg();
          $this->writeLoop();
        }
        return $this->TicketBase = 0;
      }
      // Don't test for providers that require a map if none is available
      $exclude = ($this->mapAvailable === true) ? [] : ['GoogleMaps'];
      $chainProviders = [];
      foreach ($geoProviders as $key => $value) {
        $providerIndex = $newProvider = null;
        for ($i = 0; $i < count($this->providers); $i++) {
          if (
            strtolower($this->providers[$i]) === strtolower(preg_replace('/\s+/', '', $key)) &&
            !in_array($this->providers[$i], $exclude)
          ) {
            $providerIndex = $i;
          }
        }
        if ($providerIndex !== null) {
          $testClass = "\Geocoder\Provider\\{$this->providers[$providerIndex]}\\{$this->providers[$providerIndex]}";
          if (class_exists($testClass)) {
            if (count($value) > 1) {
              try {
                $newProvider = new $testClass($this->adapter, $value[1], $value[0]);
              } catch(\Exception $e) {
                if ($this->enableLogging !== false) {
                  $this->error = "Geocoder Error {$e->getMessage()}";
                  self::writeLoop();
                }
                $newProvider = null;
              }
              if ($newProvider !== null) $chainProviders[] = $newProvider;
            } else {
              try {
                $newProvider = new $testClass($this->adapter, $value[0]);
              } catch(\Exception $e) {
                if ($this->enableLogging !== false) {
                  $this->error = "Geocoder Error {$e->getMessage()}";
                  self::writeLoop();
                }
                $newProvider = null;
              }
            }
            if ($newProvider !== null) $chainProviders[] = $newProvider;
          }
        }
      }
      if (empty($chainProviders)) {
        if ($this->enableLogging !== false) {
          $this->error = 'No geocoder providers available';
          self::writeLoop();
        }
        return $this->TicketBase = 0;
      }
      $this->chain = new \Geocoder\Provider\Chain\Chain($chainProviders);

      $this->geocoder->registerProvider($this->chain);
      // Use GeoCode to get the coordinates of the two addresses
      // get the result objects
      try {
        $this->geocoder->geocodeQuery(GeocodeQuery::create($addy1));
      } catch(\Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        if ($this->ticketBaseRetries < $this->geoRetry) {
          self::shiftGeocoders();
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        }
        return $this->TicketBase = 0;
      }
      $temp = $this->geocoder->geocodeQuery(GeocodeQuery::create($addy1))->all();
      for ($i = 0; $i < count($temp); $i++) {
        $test = json_decode($this->dumper->dump($temp[$i]));
        if (
          $test->properties->adminLevels->{1}->name === $addy1GeocoderAdmin1 ||
          $test->properties->adminLevels->{1}->code === $addy1GeocoderAdmin1
        ) {
          $this->result1 = $test;
          break;
        }
      }
      if (!$this->result1) {
        if ($this->ticketBaseRetries < $this->geoRetry) {
          self::shiftGeocoders();
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        } else {
          $this->error = 'No address1 match from geocoder';
          if ($this->enableLogging !== false) self::writeLoop();
          return $this->TicketBase = 0;
        }
      }
      try {
        $this->geocoder->geocodeQuery(GeocodeQuery::create($addy2));
      } catch(\Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        if ($this->ticketBaseRetries < $this->geoRetry) {
          self::shiftGeocoders();
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        }
        return $this->TicketBase = 0;
      }
      $temp = $this->geocoder->geocodeQuery(GeocodeQuery::create($addy2))->all();
      for ($i = 0; $i < count($temp); $i++) {
        $test = json_decode($this->dumper->dump($temp[$i]));
        if (
          $test->properties->adminLevels->{1}->name === $addy2GeocoderAdmin1 ||
          $test->properties->adminLevels->{1}->code === $addy2GeocoderAdmin1
        ) {
          $this->result2 = $test;
          break;
        }
      }
      if (!$this->result2) {
        if ($this->ticketBaseRetries < $this->geoRetry) {
          self::shiftGeocoders();
          $this->ticketBaseRetries++;
          return self::getTicketBase();
        } else {
          $this->error = 'No address2 match from geocoder';
          if ($this->enableLogging !== false) self::writeLoop();
          return $this->TicketBase = 0;
        }
      }
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
      $this->TicketBase = round($this->config['BaseTicketFee'] *
        pow($this->config['PriceIncrement'], $this->billingCode), 2, PHP_ROUND_HALF_DOWN);
      // Solve for ticketPrice
      $discountType = ($this->Contract == 0) ? 'GeneralDiscount' : 'ContractDiscount';
      $clientMarker = ($this->RepeatClient == 0) ? "t{$this->BillTo}" : $this->BillTo;
      $discountRaw = ($this->members) ? $this->members[$clientMarker]->getProperty($discountType) : 0;
      $discount = ($discountRaw == 0) ? 1 : (100 - $discountRaw) / 100;
      $this->TicketBase = round(($this->TicketBase * $discount), 2, PHP_ROUND_HALF_DOWN);
      if ($this->TicketBase > $this->maxFee) $this->TicketBase = $this->maxFee;
      return true;
    }

    private function shiftGeocoders()
    {
      $geoProviders = json_decode($this->config['Geocoders'], true);
      $keys = array_keys($geoProviders);
      if (count($geoProviders) > 1) {
        $firstKey = array_shift($keys);
        $firstVal = array_shift($geoProviders);
        $geoProviders[$firstKey] = $firstVal;
        $this->config['Geocoders'] = json_encode($geoProviders);
      }
    }

    private function buildLocationList()
    {
      $filter = [];
      if ($this->userType == 'client') {
        $org_id_json = $this->members[$this->ClientID]->getProperty('org_id');
        $org_id = json_decode($org_id_json,true);
        $requestTickets = (is_array($org_id) && array_key_exists('RequestTickets',$org_id)) ?
          $org_id['RequestTickets'] : false;
      }
      if ($this->userType == 'org' ||
        ($this->userType == 'client' && $requestTickets !== false && ($requestTickets == 1 || $requestTickets >= 3))
      ) {
        $repeat = $nonRepeat = $repeatFilter = $nonRepeatFilter = [];
        foreach ($this->members as $member) {
          $array = ($member->getProperty('RepeatClient') == false) ? 'nonRepeat' : 'repeat';
          $$array[] = $member->getProperty('ClientID');
        }
        if (!empty($repeat)) $repeatFilter = [
          [ 'Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $repeat) ],
          [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1 ]
        ];
        if (!empty($nonRepeat)) $nonRepeatFilter = [
          [ 'Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $nonRepeat) ],
          [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0 ]
        ];
        if (!empty($repeatFilter) && !empty($nonRepeatFilter)) {
          $filter = [ $repeatFilter, $nonRepeatFilter ];
        } elseif (empty($repeatFilter) && !empty($nonRepeatFilter)) {
          $filter = $nonRepeatFilter;
        } elseif (!empty($repeatFilter) && empty($nonRepeatFilter)) {
          $filter = $repeatFilter;
        }
      } elseif (
        $this->userType == 'client' &&
        ($requestTickets === false || ($requestTickets < 3 && $requestTickets != 1))
      ) {
        $filter = [
          [ 'Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>self::test_int($this->ClientID) ],
          [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->RepeatClient ]
        ];
      }
      $tempClients = $uniqueTest = [];
      $locationQueryData['method'] = 'GET';
      $locationQueryData['endPoint'] = 'tickets';
      $locationQueryData['queryParams']['include'] = [
        'pClient', 'dClient', 'pAddress1', 'pAddress2', 'pCountry', 'dAddress1', 'dAddress2',
        'dCountry', 'pDepartment', 'dDepartment', 'pContact', 'dContact'
      ];
      $locationQueryData['queryParams']['filter'] = $filter;

      if (!$locationQuery = self::createQuery($locationQueryData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      //Pull the data to make the datalists
      $locationData = self::callQuery($locationQuery);
      if ($locationData === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      //Only proceed if a record is returned
      if (count($locationData) === 0) {
        return $this->locationList = 'empty';
      } else {
        // Filter the locationList first to be unique then using $this->ignoreValues and $this->clientAddressExceptions
        foreach ($locationData as $location) {
          if (!in_array(strtolower($location['pClient']), $this->ignoreValues)) {
            $test = self::decode(
              "{$location['pClient']} {$location['pDepartment']} {$location['pAddress1']} {$location['pAddress2']}"
              ) . ' ' . $location['pCountry'];
            $exceptionTest = self::decode("{$location['pClient']}, {$location['pAddress1']}");
            if (!in_array($test, $uniqueTest) && !in_array($exceptionTest, $this->clientAddressExceptions)) {
              $uniqueTest[] = $test;
              $tempClients[] = [
                'client'=>self::decode($location['pClient']),
                'department'=>self::decode($location['pDepartment']),
                'contact'=>self::decode($location['pContact']),
                'address1'=>self::decode($location['pAddress1']),
                'address2'=>self::decode($location['pAddress2']),
                'country'=>$location['pCountry']
              ];
            }
          }
          if (!in_array(strtolower($location['dClient']), $this->ignoreValues)) {
            $test = self::decode(
              "{$location['dClient']} {$location['dDepartment']} {$location['dAddress1']} {$location['dAddress2']}"
              ) . ' ' . $location['dCountry'];
            $exceptionTest = self::decode("{$location['dClient']}, {$location['dAddress1']}");
            if (!in_array($test, $uniqueTest) && !in_array($exceptionTest, $this->clientAddressExceptions)) {
              $uniqueTest[] = $test;
              $tempClients[] = [
                'client'=>self::decode($location['dClient']),
                'department'=>self::decode($location['dDepartment']),
                'contact'=>self::decode($location['dContact']),
                'address1'=>self::decode($location['dAddress1']),
                'address2'=>self::decode($location['dAddress2']),
                'country'=>$location['dCountry']
              ];
            }
          }
        }
        // Sort $tempClients and reset the keys to the new order before encoding
        return $this->locationList = (count($tempClients) === 0) ?
          'empty' : self::encodeURIComponent(json_encode(self::user_array_sort($tempClients, 'client')));
      }
    }

    private function fetchDrivers()
    {
      $tempDriver = [];
      // Pull the data to make the datalists
      $driverQueryData['method'] = 'GET';
      $driverQueryData['endPoint'] = 'drivers';
      $driverQueryData['queryParams'] = [];
      $driverQueryData['queryParams']['include'] = ['DriverID', 'FirstName', 'LastName'];
      $driverQueryData['queryParams']['filter'] = [ ['Resource'=>'Deleted', 'Filter'=>'neq', 'Value'=>1] ];
      if (!$driverQuery = self::createQuery($driverQueryData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      $tempDriver = self::callQuery($driverQuery);
      if ($tempDriver === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        $this->driverList = 'empty';
        return false;
      }
      // Only proceed if a record is returned
      if (empty($tempDriver)) {
        return $this->driverList = 'empty';
      }
      return $this->driverList = self::encodeURIComponent(json_encode($tempDriver));
    }

    private function fetchClients()
    {
      $tempClients = $repeatList = $nrList = [];
      $clientQueryData['method'] = 'GET';
      $clientQueryData['endPoint'] = 'clients';
      $clientQueryData['queryParams'] = [];
      $clientQueryData['queryParams']['include'] = ['ClientID', 'ClientName', 'Department', 'RepeatClient'];
      $clientQueryData['queryParams']['filter'] = [ ['Resource'=>'Deleted', 'Filter'=>'neq', 'Value'=>1] ];
      if (!$clientQuery = self::createQuery($clientQueryData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      $tempClients = self::callQuery($clientQuery);
      if ($tempClients === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      // Only proceed if a record is returned
      if (empty($tempClients)) {
        return $this->clientList = $this->tClientList = 'empty';
      }
      for ($i = 0; $i < count($tempClients); $i++) {
        if (self::test_bool($tempClients[$i]['RepeatClient']) === true) {
          $repeatList[] = $tempClients[$i];
        } else {
          $nrList[] = $tempClients[$i];
        }
      }
      $this->clientList = (empty($repeatList)) ? 'empty' : self::encodeURIComponent(json_encode($repeatList));
      $this->tClientList = (empty($nrList)) ? 'empty' : self::encodeURIComponent(json_encode($nrList));
      return true;
    }

    private function buildDatalists()
    {
      $returnData = '';
      if ($this->userType !== 'client' && $this->userType !== 'org') {
        if ($this->userType === 'dispatch' || $this->CanDispatch === 2) {
          if ($this->driverList == null) {
            self::fetchDrivers();
          }
          if ($this->driverList !== 'empty' && $this->driverList !== null) {
            $returnData .= '<datalist id="drivers">';
            foreach (json_decode(urldecode($this->driverList), true) as $driver) {
              // Set DispatchedTo for display if the ticket_index is not null
              if ($this->ticket_index !== null) {
                if ($this->DispatchedTo == $driver['DriverID']) {
                  $this->DispatchedTo = ($driver['LastName'] === null || $driver['LastName'] === '') ?
                  htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] :
                  htmlentities("{$driver['FirstName']} {$driver['LastName']}") . '; ' . $driver['DriverID'];
                }
              }
              $driverName = ($driver['LastName'] === null || $driver['LastName'] === '') ?
              htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] :
              htmlentities("{$driver['FirstName']} {$driver['LastName']}") . '; ' . $driver['DriverID'];

              $returnData .= "<option value=\"$driverName\">$driverName</option>";
            }
            $returnData .= '</datalist>';
            if ($this->userType === 'driver') {
              $returnData .= '<datalist id="receivers">';
              foreach (json_decode(urldecode($this->driverList), true) as $driver) {
                $driverName = ($driver['LastName'] === null || $driver['LastName'] === '') ?
                htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] :
                htmlentities("{$driver['FirstName']} {$driver['LastName']}") . '; ' . $driver['DriverID'];

                $returnData .= ($driver['DriverID'] !== $_SESSION['DriverID']) ?
                "<option value=\"$driverName\">$driverName</option>" : '';
              }
              $returnData .= '</datalist>';
            }
          }
        }
        if ($this->userType === 'driver' && $this->CanDispatch === 0) {
          if ($this->driverList !== 'empty' && $this->driverList == null) {
            self::fetchDrivers();
          }
          if ($this->driverList !== 'empty' && $this->driverList != null) {
            $returnData .= '<datalist id="receivers">';
            foreach (json_decode(urldecode($this->driverList), true) as $driver) {
              $driverName = ($driver['LastName'] == null) ?
              htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] :
              htmlentities("{$driver['FirstName']} {$driver['LastName']}") . '; ' . $driver['DriverID'];

              $returnData .= ($driver['DriverID'] !== $_SESSION['DriverID']) ?
              "<option value=\"$driverName\">$driverName</option>" : '';
            }
            $returnData .= '</datalist>';
          }
        }
        if ($this->clientList === null) {
          if (!self::fetchClients()) {
            return $this->error;
          }
        }
        if ($this->clientList !== 'empty' && $this->clientList !== null) {
          $returnData .= '<datalist id="clients">';
          foreach (json_decode(urldecode($this->clientList), true) as $client) {
            // Set BillTo for display if the ticket_index is not null
            if ($this->ticket_index !== null) {
              if ($this->BillTo == $client['ClientID']) {
                $this->BillTo = ($client['Department'] === null || $client['Department'] === '') ?
                $client['ClientName'] . '; ' . $client['ClientID'] :
                $client['ClientName'] . ', ' . $client['Department'] . '; ' . $client['ClientID'];
              }
            }
            $clientVal = ($client['Department'] === null || $client['Department'] === '') ?
            $client['ClientName'] . '; ' . $client['ClientID'] :
            $client['ClientName'] . ', ' . $client['Department'] . '; ' . $client['ClientID'];

            $returnData .= '<option value="' . $clientVal . '">' . html_entity_decode($clientVal) . '</option>';
          }
          $returnData .= '</datalist>';
        }
        if ($this->tClientList !== 'empty' && $this->tClientList !== null) {
          $returnData .= '<datalist id="t_clients">
            <option value="new">new</option>';
          foreach (json_decode(urldecode($this->tClientList), true) as $tclient) {
            $tclientVal = ($tclient['Department'] === null || $tclient['Department'] === '') ?
            $tclient['ClientName'] . '; t' . $tclient['ClientID'] :
            $tclient['ClientName'] . ', ' . $tclient['Department'] . '; t' . $tclient['ClientID'];

            $returnData .= "<option value=\"$tclientVal\">" . html_entity_decode($tclientVal) . '</option>';
          }
          $returnData .= '</datalist>';
        }
      } else {
        if ($this->userType == 'client') {
          $org_id_json = $this->members[$this->ClientID]->getProperty('org_id');
          $org_id = json_decode($org_id_json,true);
          $requestTickets = (is_array($org_id) && array_key_exists('RequestTickets',$org_id)) ?
            $org_id['RequestTickets'] : false;
        }
        if ($this->userType == 'org' ||
          ($this->userType == 'client' && $requestTickets !== false && ($requestTickets == 1 || $requestTickets >= 3))
        ) {
          $returnData .= '<datalist id="members">';
          foreach ($_SESSION['members'] as $key => $value) {
            if ($value['Organization'] != $org_id['id']) continue;
            $name = $value['ClientName'];
            if ($value['Department'] != null) $name .= " {$value['Department']}";
            $name .= "; $key";
            $returnData .= "<option value=\"$name\">" . html_entity_decode($name) . '</option>';
          }
          $returnData .= '</datalist>';
        }
      }
      if (self::test_bool($this->config['InternationalAddressing']) === true) {
        $returnData .= '<datalist id="countries">';
        $lines = file( __dir__ . '/countryList.php', FILE_IGNORE_NEW_LINES);
        // countryList.php was originally supposed to echo these values so start at line two
        for ($i=2; $i<count($lines) - 1; $i++) {
          $returnData .= $lines[$i];
        }
        $returnData .= '</datalist>';
      }
      if ($this->locationList === 'empty' || $this->locationList === null) {
        return $returnData;
      }
      $clients = $departments = $contacts = $addy1s = $addy2s = [];
      $locations = array_values(json_decode(urldecode($this->locationList), true));
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
      $html_entity_decode = 'html_entity_decode';
      foreach ($clients as $client) {
        $returnData .= "
          <option vlaue=\"$client\">{$html_entity_decode($client)}</option>";
      }

      $returnData .= '
        </datalist>
        <datalist id="departments">';

      foreach ($departments as $department) {
        $returnData .= "
          <option vlaue=\"$department\">{$html_entity_decode($department)}</option>";
      }

      $returnData .= '
        </datalist>
        <datalist id="addy1">';

      for($i = 0; $i < count($addy1s); $i++) {
        $returnData .= "
          <option vlaue=\"{$addy1s[$i]}\" data-value=\"$i\">{$html_entity_decode($addy1s[$i])}</option>";
      }

      $returnData .= '
        </datalist>
        <datalist id="addy2">';

      for ($i = 0; $i < count($addy2s); $i++) {
        $returnData .= "
          <option value=\"{$addy2s[$i]}\" data-value=\"$i\">{$html_entity_decode($addy2s[$i])}</option>";
      }

      $returnData .= '</datalist>
        <datalist id="contacts">';

      foreach ($contacts as $contact) {
        $returnData .= "
          <option value=\"$contact\">{$html_entity_decode($contact)}</option>";
      }

      $returnData .= '
        </datalist>';

      return $returnData;
    }

    private function buildSelectElement()
    {
      if ($this->locationList === 'empty') {
        return false;
      }
      $locations = json_decode(urldecode($this->locationList), true);
      $returnData = '';
      $returnData = "
        <select name=\"{$this->selectID}\" class=\"clientSelect\" form=\"request\" disabled>";
      for ($i = 0; $i < count($locations); $i++) {
        $val = $locations[$i][strtolower(substr($this->selectID, 1))];
        $display = html_entity_decode($locations[$i][strtolower(substr($this->selectID, 1))]);
        $returnData .= "
          <option data-value=\"$i\" value=\"$val\">$display</option>";
      }
      $returnData .= '
        </select>';
      return $returnData;
    }

    private function testTicketNumber()
    {
      $ticketNumberQueryData['method'] = 'GET';
      $ticketNumberQueryData['endPoint'] = 'tickets';
      $ticketNumberQueryData['queryParams']['include'] = ['TicketNumber'];
      if (!$ticketNumberQuery = self::createQuery($ticketNumberQueryData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      $this->ticketNumberList = self::callQuery($ticketNumberQuery);
      if ($this->ticketNumberList === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      while (self::recursive_array_search($this->TicketNumber, $this->ticketNumberList) !== false) {
        $this->TicketNumber++;
      }
      return true;
    }

    private function queryTicket()
    {
      $ticketQueryResult = [];
      // When querying multiple tickets $this->ticket_index will be a comma separated list of indexes.
      // Test for a comma and adjust the filter accordingly.
      $queryFilter = (strpos($this->ticket_index, ',') === false) ? 'eq' : 'in';
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['queryParams']['filter'] = [
        ['Resource'=>'ticket_index', 'Filter'=>$queryFilter, 'Value'=>$this->ticket_index]
      ];
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      $ticketQueryResult = self::callQuery($ticketQuery);
      if ($ticketQueryResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      if (count($ticketQueryResult) === 1) {
        $this->sanitized = self::recursive_santizer($ticketQueryResult[0]);
        foreach ($this->sanitized as $key => $value) {
          foreach ($this as $k => $v) {
            if (strtolower($key) === strtolower($k) && $k !== 'sanitized') {
              if (strtolower($key) === 'transfers') {
                $this->$k = ($value === null || $value === '') ? null : $value;
              } elseif (strtolower($key) === 'transferstate' || strtolower($key) === 'pendingreceiver') {
                $this->$k = ($this->processTransfer === true) ? $v : $value;
                $tempkey = lcfirst($k) . 'Old';
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
      return true;
    }

    public function sendEmail()
    {
      switch ($this->action) {
        case 'step':
          if ($this->step === 'pickedUp') {
            // send email on 1, 3, 5, 7
            return $this->EmailConfirm % 2 !== 0;
          } elseif ($this->step === 'delivered') {
            // send email on 2, 3, 6, 7
            switch ($this->EmailConfirm) {
              case 2:
              // no break;
              case 3:
              // no break;
              case 6:
              // no break;
              case 7: return true;
              default: return false;
            }
          } elseif ($this->step === 'returned') {
            // send email on 4, 5, 6, 7
            return $this->EmailConfirm > 3;
          } else {
            return false;
          }
          break;
        case 'delete':
        // no break;
        case 'cancel':
        // no break;
        case 'deadRun':
        // no break;
        case 'declined': return true;
        default: return false;
      }
    }

    private function processEmail()
    {
      if (
        !$this->options['emailConfig'] ||
        (is_array($this->options['emailConfig']) && empty($this->options['emailConfig'])) ||
        !$this->EmailAddress
      ) {
        return false;
      }
      $recipients = array_map('trim', explode(',', $this->EmailAddress));
      $mailBody = "Ticket number {$this->TicketNumber} picking up from {$this->pClient} at {$this->pAddress1} delivering to {$this->dClient} at {$this->dAddress1} ";
      if ($this->Charge == 6 || ($this->Charge == 7 && $this->d2SigReq)) $mailBody .= 'and returning ';
      switch ($this->action) {
        case 'step':
          $mailBody .= "has been {$this->stepMarker}.";
          break;
        case 'delete':
          // no break
        case 'cancel':
          $mailBody .= 'has been canceled.';
          break;
        case 'deadRun':
          $mailBody .= 'could not be picked up.';
          break;
        case 'declined':
          $mailBody .= 'has been declined and will be made a round trip.';
          break;
      }
      $mailBody .= "<br><br>
        This message is automatically generated.
        Please do not respond.<br><br>
        If you believe that you've received this message in error or have questions or comments please contact
        {$this->myInfo['Name']} by phone at <a href=\"tel:{$this->myInfo['Telephone']}\">{$this->myInfo['Telephone']}</a>
        or by email at <a href=\"mailto:{$this->myInfo['EmailAddress']}\">{$this->myInfo['EmailAddress']}</a>";
      $mail = new PHPMailer(true);
      try {
        //Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $this->options['emailConfig']['smtpHost'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = $this->options['emailConfig']['emailAddress']; // SMTP username
        $mail->Password = $this->options['emailConfig']['password']; // SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $this->options['emailConfig']['port']; // TCP port to connect to
        // Recipients
        $mail->setFrom($this->options['emailConfig']['emailAddress'], $this->options['emailConfig']['fromName']);
        foreach ($recipients as $r) {
          $mail->addAddress($r);     // Add a recipient
        }
        $mail->addBCC($this->options['emailConfig']['BCCAddress']);
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Update';
        $mail->Body  = $mailBody;
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
        $mail->send();
        //echo 'Message has been sent';
      } catch (\Exception $e) {
        if ($this->enableLogging !== false) {
          $this->error = 'Email Not Sent: ' . $mail->ErrorInfo;
          self::writeLoop();
        }
      }
    }

    //Ticket Charge values
    protected function ticketCharge($charge)
    {
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
          return false;
      }
    }

    public function invoiceBody()
    {
      if ($this->pDepartment == null) {
        $pClientDisplay = $this->pClient;
      } else {
        $pClientDisplay = $this->pClient . ' | ' . $this->pDepartment;
      }

      if ($this->dDepartment == null) {
        $dClientDisplay = $this->dClient;
      } else {
        $dClientDisplay = $this->dClient . ' | ' . $this->dDepartment;
      }
      $cSym = $this->config['CurrencySymbol'];
      // Define the dry ice display
      if (self::test_bool($this->dryIce) === true && $this->options['displayDryIce'] === true) {
        $weight = self::number_format_drop_zero_decimals($this->diWeight, 3) . $this->weightMarker;
        $val = self::number_format_drop_zero_decimals($this->diPrice, 2);
        $answerIce = "Weight: $weight | Price: <span class=\"currencySymbol\">$cSym</span>$val";
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
      $runPrice = self::negParenth(self::number_format_drop_zero_decimals($this->RunPrice, 2));
      $ticketPrice = self::negParenth(self::number_format_drop_zero_decimals($this->TicketPrice, 2));
      $forcedVATstyle = ($this->renderPDF === true) ? 'style="font-size:0.65rem;"' : '';

      if ($this->config['ApplyVAT'] === true) {
        $vat = 'Error';
        switch ($this->VATtype) {
          case 0:
            $vat = 'NA';
            break;
          case 1:
            $vat = 'Standard';
            break;
          case 2:
            $vat = 'Reduced';
            break;
          case 3:
            $vat = 'Standard';
            break;
          case 4:
            $vat = 'Reduced';
            break;
          case 5:
            $vat = 'Zero-Rated';
            break;
          case 6:
            $vat = 'Exempt';
            break;
        }
        if (self::test_bool($this->dryIce) === true) {
          $temp = $vat;
          $vat = "D: $temp I: ";
          switch ($this->VATtypeIce) {
            case 0:
              $vat .= 'NA';
              break;
            case 1:
              $vat .= 'Standard';
              break;
            case 2:
              $vat .= 'Reduced';
              break;
            case 3:
              $vat .= 'Standard';
              break;
            case 4:
              $vat .= 'Reduced';
              break;
            case 5:
              $vat .= 'Zero-Rated';
              break;
            case 6:
              $vat .= 'Exempt';
              break;
          }
        }
        $VATnotice = "<p $forcedVATstyle class=\"vatNotice\">$vat</p>";
      } else {
        $VATnotice = '';
      }
      return "
              <tr>
                <td>$date</td>
                <td>{$this->TicketNumber}</td>
                <td>{$this->ticketCharge($this->Charge)}</td>
                <td>P.U.:<br><hr>D.O.:<br><hr>$labelIce</td>
                <td>$pClientDisplay<br>{$this->pAddress1}<br><hr>$dClientDisplay<br>{$this->dAddress1}<br><hr>$answerIce</td>
                <td><span class=\"currencySymbol\">$cSym</span>$runPrice</td>
                <td>{$this->Multiplier}</td>
                <td><span class=\"currencySymbol\">$cSym</span>$ticketPrice $VATnotice</td>
              </tr>";
    }

    public function regenTicket()
    {
      $hideTableHead = '';
      if ($this->forDisatch === true) {
        $dispatchValue = '';
        if ($this->driverID !== 0) {
          $dispatchValue = "{$this->DriverName}; {$this->driverID}";
        }
        $this->dispatchForm = "
          <form id=\"dispatchForm{$this->ticket_index}\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
            <input type=\"hidden\" name=\"step\" class=\"step\" value=\"dispatched\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->EmailConfirm}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->EmailAddress}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"dispatchedBy\" class=\"dispatchedBy\" value=\"{$this->DispatchedBy}\" form=\"dispatchForm{$this->ticket_index}\" />
            <input type=\"hidden\" name=\"readyDate\" class=\"readyDate\" value=\"{$this->ReadyDate}\" form=\"dispatchForm{$this->ticket_index}\" />
            <button type=\"submit\" class=\"stepTicket\" form=\"dispatchForm{$this->ticket_index}\">Dispatch</button>
            <label for=\"dispatch{$this->ticket_index}\" class=\"hide\">Dispatch To: </label>
            <input list=\"drivers\" id=\"dispatch{$this->ticket_index}\" name=\"dispatchedTo\" class=\"dispatchedTo\" value=\"$dispatchValue\" form=\"dispatchForm{$this->ticket_index}\" />
            <p class=\"message2\"></p>
          </form>
        ";
        $this->driverDatalist = '<datalist id="drivers">';
        foreach (json_decode(urldecode($this->driverList), true) as $driver) {
          $driverName = ($driver['LastName'] == null) ?
          $driver['FirstName'] . '; ' . $driver['DriverID'] :
          $driver['FirstName'] . ' ' . $driver['LastName'] . '; ' . $driver['DriverID'];

          $this->driverDatalist .= "<option value=\"$driverName\">$driverName</option>";
        }
        $this->driverDatalist .= '</datalist>';
        $hideTableHead = 'hide';
      }
      $ticketType = (self::test_bool($this->Contract) === true) ? 'Contract' : 'On Call';
      if ($this->ticketEditor === true) $hideTableHead = 'hide';
      try {
        $rDate = new \dateTime($this->ReceivedDate, $this->timezone);
        $rDateDisplay = $rDate->format('d M Y \a\t g:i A');
	    } catch (\Exception $e) {
        $rDateDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
	    }
      if ($this->ReadyDate === null) $this->ReadyDate = $this->ReceivedDate;
      try {
        $ready = new \dateTime($this->ReadyDate, $this->timezone);
        $readyDisplay = $ready->format('d M Y \a\t g:i A');
	    } catch (\Exception $e) {
        $readyDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
	    }
      if ($this->DispatchTimeStamp !== $this->tTest) {
        try {
          $pDate = new \dateTime($this->DispatchTimeStamp, $this->timezone);
          $dispatchTimeDisplay = $pDate->format('d M Y \a\t g:i A');
        } catch (\Exception $e) {
          $dispatchTimeDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
        }
      } else {
        $dispatchTimeDisplay = 'Not Available<span class="hide">Error: None</span>';
      }
      if ($this->pTimeStamp !== $this->tTest) {
        try {
          $pDate = new \dateTime($this->pTimeStamp, $this->timezone);
          $pTimeStampDispay = $pDate->format('d M Y \a\t g:i A');
        } catch (\Exception $e) {
          $pTimeStampDispay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
        }
      } else {
        $pTimeStampDispay = 'Not Available<span class="hide">Error: None</span>';
      }
      if ($this->dTimeStamp !== $this->tTest) {
        try {
          $dDate = new \dateTime($this->dTimeStamp, $this->timezone);
          $dTimeStampDisplay = $dDate->format('d M Y \a\t g:i A');
        } catch (\Exception $e) {
          $dTimeStampDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
        }
      } else {
        $dTimeStampDisplay = 'Not Available<span class="hide">Error: None</span>';
      }
      if (($this->Charge === 6 || $this->Charge === 7) && $this->d2TimeStamp !== $this->tTest) {
        try {
          $d2Date = new \dateTime($this->d2TimeStamp, $this->timezone);
          $d2TimeStampDisplay = $d2Date->format('d M Y \a\t g:i A');
        } catch (\Exception $e) {
          $d2TimeStampDisplay = 'Not Available<span class="hide">Error: ' . $e->getMessage() . '</span>';
        }
      } elseif ($this->Charge !== 6) {
        if ($this->Charge === 7) {
          $d2TimeStampDisplay = (self::test_bool($this->d2SigReq) === false) ? 'Not Scheduled' : 'Not Available';
        } else {
          $d2TimeStampDisplay = 'Not Scheduled';
        }
      } else {
        $d2TimeStampDisplay = 'Not Available<span class="hide">Error: None</span>';
      }

      $requestedByDisplay = ($this->RequestedBy === null || $this->RequestedBy === '') ?
        'Not On File' : $this->RequestedBy;

      if ($this->InvoiceNumber !== '-') {
        if ($this->ulevel < 2) {
          if ($this->ulevel === 1) {
            $url = 'invoices';
          } elseif ($this->ulevel === 0) {
            $url = 'orgInvoices';
          } else {
            $url = 'error';
          }
          $repeatMarker = (self::test_bool($this->RepeatClient) === true) ? '' : 't';
          $repeatVal = (self::test_bool($this->RepeatClient) === true) ? '1' : '0';
          if ($this->organizationFlag === true) {
            $this->memberInput = "<input type=\"hidden\" name=\"clientID[]\" value=\"$repeatMarker{$this->BillTo}\" />";
          } else {
            $this->memberInput = "<input type=\"hidden\" name=\"clientID\" value=\"{$this->ClientID}\" />";
          }
          $billed = "
          <form class=\"noPrint\" action=\"$url\" method=\"post\">
            <input type=\"hidden\" name=\"endPoint\" value=\"invoices\" />
            <input type=\"hidden\" name=\"display\" value=\"invoice\" />
            <input type=\"hidden\" name=\"invoiceNumber\" value=\"{$this->InvoiceNumber}\" />
            <input type=\"hidden\" name=\"method\" value=\"GET\" />
            <input type=\"hidden\" name=\"repeatClient\" value=\"$repeatVal\" />
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
      $iceAndNotes = '';
      $readonlyNotes = ($this->forDisatch === true) ? "form=\"dispatchForm{$this->ticket_index}\"" : 'readonly';
      if ($this->options['displayDryIce'] === true) {
        $cSym = "<span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>";
        $diPrice = (is_numeric($this->ulevel)) ?
          $cSym . self::number_format_drop_zero_decimals($this->diPrice, 2) : '';
        $diWeight = self::number_format_drop_zero_decimals($this->diWeight, 3);
        $iceAndNotes .= "<table style=\"width: 25%;\" class=\"tFieldLeft\">
              <thead>
                <tr class=\"pullLeft\">
                  <th>Dry Ice</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td class=\"center\">
                    $diWeight{$this->weightMarker} $diPrice
                  </td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                </tr>
              </tbody>
            </table>
            <table style=\"width: 75%;\">
              <thead>
                <tr>
                  <th class=\"pullLeft\">Notes:</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><textarea class=\"notes\" $readonlyNotes rows=\"3\">{$this->Notes}</textarea></td>
                </tr>
              </tbody>
            </table>";
      } else {
        $iceAndNotes .= "<table class=\"tFieldRight\">
              <thead>
                <tr>
                  <th class=\"pullLeft\">Notes:</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><textarea class=\"notes\" $readonlyNotes rows=\"3\">{$this->Notes}</textarea></td>
                </tr>
              </tbody>
            </table>";
      }
      $iceAndNotes .= '
          </tr>
        </table>';
      $priceDisplay = '';
      if (self::test_bool($this->Contract) === true) {
        $priceDisplay .= "
              <tr class=\"$hideTableHead\">
                <td><span class=\"bold\">Repeats:</span> {$this->Multiplier}</td>
                <td>
                  <span class=\"bold\">Ticket Base:</span>
                  <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->TicketBase}
                </td>
              </tr>";
      }
      $runPrice = "
		      <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->RunPrice, 2)}";
      $ticketPrice = self::negParenth(self::number_format_drop_zero_decimals($this->TicketPrice, 2));
      // Reset the run price display if this is an incomplete dedicated run
      if (
        $this->Charge === 7 &&
        ((self::test_bool($this->d2SigReq) === true && $this->d2TimeStamp === $this->tTest) ||
        (self::test_bool($this->d2SigReq) === false && $this->dTimeStamp === $this->tTest))
      ) {
        $runPrice = $ticketPrice = 'Pending';
      }
      $currencySymbol = ($ticketPrice === 'Pending') ? '' :
        "<span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>";
      $priceDisplay = "
            <tr class=\"$hideTableHead\">
              <td><span class=\"bold\">Run Price:</span> $runPrice</td>
              <td>
                <span class=\"bold\">Ticket Price:</span>
                $currencySymbol $ticketPrice
              </td>
            </tr>";
      $emailConfirm = '';
      switch($this->EmailConfirm) {
        case 0:
          $emailConfirm = 'None';
          break;
        case 1:
          $emailConfirm = 'On Pick Up';
          break;
        case 2:
          $emailConfirm = 'On Delivery';
          break;
        case 3:
          $emailConfirm = 'On Pick Up &amp; Delivery';
          break;
        case 4:
          $emailConfirm = 'On Return';
          break;
        case 5:
          $emailConfirm = 'On Pick Up &amp; Return';
          break;
        case 6:
          $emailConfirm = 'On Delivery &amp; Return';
          break;
        case 7:
          $emailConfirm = 'At Each Step';
          break;
      }
      $pName = $this->pClient;
      $pName .= ($this->pDepartment == null) ? '<br>&nbsp;' : "<br>{$this->pDepartment}";
      $dName = $this->dClient;
      $dName .= ($this->dDepartment == null) ? '<br>&nbsp;' : "<br>{$this->dDepartment}";
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
        if ($this->$token !== null) {
          $fileType = "{$token}Type";
          $base64_decode = 'base64_decode';
          $showSig = "<img src=\"data:{$this->$fileType};base64,{$base64_decode($this->$token)}\" height=\"100\" width=\"375\" />";
        } else {
          $showSig = 'Image Not On File';
        }
        $tempProperty = $token.'Print';
        $tokenSet[$token . 'Display'] = "
          <tr class=\"sigPrint\">
            <td colspan=\"2\" class=\"pullLeft\">$label Signed For By: {$this->$tempProperty}</td>
          </tr>
          <tr class=\"sigImage\">
            <td colspan=\"2\" class=\"center\">
              $showSig
            </td>
          </tr>";
      }
      extract($tokenSet,EXTR_IF_EXISTS);
      $pLoc = ($this->pLat === null || $this->pLng === null) ?
      'Not On File' :
      "<span class=\"coordinates\">{$this->pLat}, {$this->pLng}</span>";

      $dLoc = ($this->dLat === null || $this->dLng === null) ?
      'Not On File' :
      "<span class=\"coordinates\">{$this->dLat}, {$this->dLng}</span>";

      $d2Loc = ($this->d2Lat === null || $this->d2Lng === null) ?
      'Not On File':
      "<span class=\"coordinates\">{$this->d2Lat}, {$this->d2Lng}</span>";

      $hideVAT = ($this->config['ApplyVAT'] === true) ? '' : 'hide';
      if ($this->config['ApplyVAT'] === false) {
        $this->VATable = 0;
        $this->VATableIce = 0;
        $this->VATrate = 0;
        $this->VATrateIce = 0;
        $this->VATtype = 0;
        $this->VATtypeIce = 0;
      }
      if (self::test_bool($this->VATable) === false) {
        $this->VATtype = 0;
        $this->VATrate = 0;
      }
      $vatDisplay = '<span class="bold">VAT:</span>D:';
      $VATamount = 0;
      if (0 < $this->VATtype && $this->VATtype < 5) {
        $vatDisplay .= ($this->VATtype === 1 || $this->VATtype === 3) ? ' Standard' : ' Reduced';
        $VATamount += ($this->RunPrice * (1 + ($this->VATrate / 100))) - $this->RunPrice;
      }
      if ($this->VATtype === 5) {
        $vatDisplay .= ' Zero-Rated';
        $this->VATrate = 0;
      }
      if ($this->VATtype === 6) {
        $vatDisplay .= ' Exempt';
        $this->VATrate = 0;
      }
      if ($this->config['ApplyVAT'] === false || self::test_bool($this->VATable) === false) {
        $vatDisplay = ' Not VAT-able';
        $this->VATrate = 0;
      }
      if (self::test_bool($this->dryIce) === true) {
        $begin = self::before(' ', $vatDisplay);
        $end = self::after(' ', $vatDisplay);
        $vatDisplay = "$begin D: $end ";
        if (0 < $this->VATtypeIce && $this->VATtypeIce < 5) {
          $vatDisplay .= ($this->VATtypeIce === 1 || $this->VATtypeIce === 3) ? 'I: Standard' : 'I: Reduced';
          $VATamount += ($this->diPrice * (1 + ($this->VATrateIce / 100))) - $this->diPrice;
        }
        if ($this->VATtypeIce === 5) {
          $vatDisplay .= 'I: Zero-Rated';
          $this->VATrateIce = 0;
        }
        if ($this->VATtypeIce === 6) {
          $vatDisplay .= 'I: Exempt';
          $this->VATrateIce = 0;
        }
        if ($this->VATableIce === 0) {
          $vatDisplay .= 'I: Not VAT-able';
          $this->VATrateIce = 0;
        }
      }
      $sortable = ($this->userType === 'client') ? '' : 'sortable';
      $returnData =
        $this->driverDatalist .
        "<div class=\"tickets $sortable\">
          <p class=\"center $hideTableHead\">
            <span class=\"imageSpan floatLeft\">{$this->headerLogo2}</span>
            <span class=\"ticketHeadAddress medium\">{$this->config['ShippingAddress1']}<br>
            {$this->config['ShippingAddress2']}<br>
            <span class=\"{$this->countryClass}\">{$this->config['ShippingCountry']}</span></span>
            <span class=\"floatRight\">{$this->config['Telephone']}</span>
          </p>
          <table class=\"regenBilling\">
            <tr>
              <td>
                <span class=\"bold\">Ticket Number:</span> <span class=\"tNumDisplay\">{$this->TicketNumber}</span>
              </td>
              <td>$ticketType</td>
            </tr>
            <tr>
              <td><span class=\"bold\">Requested By:</span> $requestedByDisplay</td>
              <td><span class=\"bold\">Charge:</span> {$this->ticketCharge($this->Charge)}</td>
            </tr>
            $priceDisplay
            <tr class=\"$hideVAT\">
              <td>$vatDisplay</td>
              <td>
                <span class=\"bold\">Ticket VAT:</span>
                <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($VATamount, 2)}
              </td>
            </tr>
            <tr class=\"$hideTableHead\">
              <td><span class=\"bold\">Confirmation:</span> $emailConfirm</td>
              <td><span class=\"bold\">Email Address:</span> {$this->EmailAddress}</td>
            </tr>
            <tr>
              <td><span class=\"bold\">Received:</span> $rDateDisplay</td>
              <td><span class=\"bold\">Ready:</span> $readyDisplay</td>
            </tr>
            <tr class=\"$hideTableHead\">
              <td><span class=\"bold\">Dispatch:</span> $dispatchTimeDisplay</td>
              <td><span class=\"bold\">Pick Up:</span> $pTimeStampDispay</td>
            </tr>
            <tr class=\"$hideTableHead\">
              <td><span class=\"bold\">Drop Off:</span> $dTimeStampDisplay</td>
              <td><span class=\"bold\">Return:</span> $d2TimeStampDisplay</td>
            </tr>
            <tr>
              <td>{$this->dispatchForm}</td>
              <td class=\"$hideTableHead\"><span class=\"bold\">Invoice:</span> $billed</td>
            </tr>
          </table>
          <hr>
          <table class=\"wide\">
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
                    <td class=\"pullLeft\">
                      {$this->decode($this->pAddress1)}<br>
                      {$this->decode($this->pAddress2)}<br>
                      <span class=\"{$this->countryClass}\">{$this->countryFromAbbr($this->pCountry)}</span>
                    </td>
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
                    <td class=\"pullLeft\">
                      {$this->decode($this->dAddress1)}<br>
                      {$this->decode($this->dAddress2)}<br>
                      <span class=\"{$this->countryClass}\">{$this->countryFromAbbr($this->dCountry)}</span>
                    </td>
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
          </table>
          <hr>
          $iceAndNotes
          <hr>
          <table class=\"wide $hideTableHead\">
            <tr>
              <td colspan=\"2\">
                <table class=\"wide sigTable\">
                  $pSigDisplay $dSigDisplay $d2SigDisplay
                </table>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\">
                <fieldset>
                  <legend>Coordinates</legend>
                  <table class=\"wide\">
                    <tr>
                      <td class=\"center\"><span class=\"bold line\">Pick Up</span> $pLoc</td>
                      <td class=\"center\"><span class=\"bold line\">Delivery</span> $dLoc</td>
                      <td class=\"center\"><span class=\"bold line\">Return</span> $d2Loc</td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            </tr>
          </table>";
      if ($this->ticketEditor === true) $returnData .= "
        <button type=\"button\" class=\"ticketEditor\" data-contract=\"{$this->Contract}\" data-index=\"{$this->ticket_index}\">Edit Ticket</button>";
      $returnData .= '
      </div>';
      return $returnData;
    }

    public function displaySingleTicket()
    {
      if ($this->ReadyDate === null) {
        $this->ReadyDate = $this->DispatchTimeStamp;
      }
      $readyObj = new \dateTime($this->ReadyDate);
      $singleTicket = '';
      if (self::test_bool($this->Contract) === false) {
        // Test for fault in query. There should be no null dispatch times here
        if ($this->DispatchTimeStamp === $this->tTest) {
          return false;
        }
        // Set the completion deadline based on the dispatch time stamp and charge
        switch ($this->Charge) {
          case 1:
          case 2:
          case 3:
          case 4:
            $this->pTime = date('d M \b\y g:i a', strtotime($this->ReadyDate) + 60*60*$this->Charge);
          break;
          default:
            $this->pTime = date('d M \b\y g:i a', strtotime($this->ReadyDate) + 60*60*5);
          break;
        }
      }
      /***
      * Set the confirmation form and the display time for the stop based on
      * charge and timestamps.
      ***/
      $addressInputs = "
      <input type=\"hidden\" name=\"pClient\" class=\"pClient\" form=\"ticketForm{$this->ticket_index}\" value=\"{$this->pClient}\" />
      <input type=\"hidden\" name=\"pAddress1\" class=\"pAddress1\" form=\"ticketForm{$this->ticket_index}\" value=\"{$this->pAddress1}\" />
      <input type=\"hidden\" name=\"dClient\" class=\"dClient\" form=\"ticketForm{$this->ticket_index}\" value=\"{$this->dClient}\" />
      <input type=\"hidden\" name=\"dAddress1\" class=\"dAddress1\" form=\"ticketForm{$this->ticket_index}\" value=\"{$this->dAddress1}\" />
      ";
      $sigClass = $sigActive = $sigPlaceholder = $sigName = $buttonName = '';
      $timingMultiplier = ($this->Charge < 6) ? $this->Charge : 5;
      $timingSource = 'now';
      if ($this->pTimeStamp === $this->tTest) {
        $label1 = (self::test_bool($this->Contract) === true) ?
          '' :
          '<p>Ready: ' . date('d M \a\t g:i a', strtotime($this->ReadyDate)) . '</p>';
        $this->step = $this->step ?? 'pickedUp';
        $sigName = 'pSig';
        if (self::test_bool($this->pSigReq) === true) {
          $sigClass = 'pulse';
          $sigActive = 'required';
          $sigPlaceholder = 'REQUIRED';
        }
        $buttonClass = '';
        $buttonName = 'Pick Up';
        $button2Class = 'deadRun';
        $button2Name = 'Dead Run';
        $noReturn = '';
        if (self::test_bool($this->Contract) === false) {
          $label1 .= 'Deadline: ';
          $label2 = '';
          $this->dTime = '';
          $timingSource = date('Y-m-d H:i', strtotime($this->ReadyDate) + 60*60*$timingMultiplier);
        } else {
          $label1 .= 'Pick Up: ';
          $label2 = 'Deliver: ';
          $this->pTime = $readyObj->format('d M g:i a');
          $timingSource = $readyObj->format('Y-m-d H:i');
          $dTimeArray = explode(':', $this->dTime);
          $readyObj->setTime($dTimeArray[0], $dTimeArray[1], $dTimeArray[2]);
          if ($this->pTime > $this->dTime) {
            $readyObj->modify('+ 1 day');
          }
          $this->dTime = $readyObj->format('d M g:i a');
        }
      } else {
        if ($this->dTimeStamp === $this->tTest) {
          $this->step = $this->step ?? 'delivered';
          $sigName = 'dSig';
          if (self::test_bool($this->dSigReq) === true) {
            $sigClass =  'pulse';
            $sigActive = 'required';
            $sigPlaceholder = 'REQUIRED';
          }
          $buttonClass = 'hide';
          $buttonName = 'Deliver';
          $noReturn = ($this->Charge === 6) ? "<label for=\"noReturn{$this->ticket_index}\">No Return</label><input type=\"checkbox\" name=\"noReturn\" id=\"noReturn{$this->ticket_index}\" class=\"noReturn\" value=\"1\" form=\"ticketForm{$this->ticket_index}\" />" : '';
          $button2Class = 'declined';
          $button2Name = 'Declined';
          if (self::test_bool($this->Contract) === false) {
            $label1 = 'Deadline: ';
            $label2 = '';
            $this->dTime = '';
            $timingSource = date('Y-m-d H:i', strtotime($this->ReadyDate) + 60*60*$timingMultiplier);
          } else {
            $label1 = 'Deliver: ';
            $label2 = 'Return: ';
            $dTimeArray = explode(':', $this->dTime);
            $readyObj->setTime($dTimeArray[0], $dTimeArray[1], $dTimeArray[2]);
            $this->pTime = $readyObj->format('d M g:i a');
            $timingSource = $readyObj->format('Y-m-d H:i');
            if ($this->Charge === 6) {
              $d2TimeArray = explode(':', $this->d2Time);
              $readyObj->setTime($d2TimeArray[0], $d2TimeArray[1], $d2TimeArray[2]);
              if ($this->dTime > $this->d2Time) {
                $readyObj->modify('+ 1 day');
              }
              $this->dTime = $readyObj->format('d M g:i a');
            } else {
              $this->dTime = '-';
            }
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
        } elseif ($this->dTimeStamp !== $this->tTest) {
          $this->step = $this->step ?? 'returned';
          $sigName = 'd2Sig';
          if (self::test_bool($this->d2SigReq) === true) {
            $sigClass = 'pulse';
            $sigActive = 'required';
            $sigPlaceholder = 'REQUIRED';
          }
          $buttonClass = $button2Class = 'hide';
          $buttonName = 'Return';
          $button2Name = '';
          $noReturn = '';
          $label1 = 'Return: ';
          $label2 = '-';
          $d2TimeArray = explode(':', $this->d2Time);
          $readyObj->setTime($d2TimeArray[0], $d2TimeArray[1], $d2TimeArray[2]);
          if ($this->dTime > $this->d2Time) {
            $readyObj->modify('+ 1 day');
          }
          $this->pTime = $readyObj->format('d M g:i a');
          $timingSource = $readyObj->format('Y-m-d H:i');
          $this->dTime = '-';
        }
      }
      $timingObj = new \dateTime($timingSource);
      $timing = $timingObj->format('U');
      $transfersFormValue = ($this->Transfers) ? htmlspecialchars($this->Transfers) : '';
      $confirm = "
            <table class=\"wide confirm\">
              <tbody>
                <tr>
                  <td colspan=\"2\">
                    <form id=\"ticketForm{$this->ticket_index}\" class=\"routeStop\">
                      <input type=\"hidden\" name=\"sigImage\" class=\"sigImage\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"latitude\" class=\"latitude\" form=\"ticketForm{$this->ticket_index}\" value=\"\" />
                      <input type=\"hidden\" name=\"longitude\" class=\"longitude\" form=\"ticketForm{$this->ticket_index}\" value=\"\" />
                      <input type=\"hidden\" name=\"action\" class=\"action\" value=\"step\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"step\" class=\"step\" value=\"{$this->step}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"ticketNumber\" class=\"ticketNumber\" value=\"{$this->TicketNumber}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"dispatchedTo\" class=\"dispatchedTo\" value=\"{$this->DispatchedTo}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"charge\" class=\"charge\" value=\"{$this->Charge}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->EmailConfirm}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->EmailAddress}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"transfers\" class=\"transfers\" value=\"$transfersFormValue\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"ticketBase\" class=\"ticketBase\" value=\"{$this->TicketBase}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"diPrice\" class=\"diPrice\" value=\"{$this->diPrice}\" form=\"ticketForm{$this->ticket_index}\" />
                      <label for=\"{$sigName}Print{$this->ticket_index}\">Signer</label><br><input type=\"text\" name=\"{$sigName}Print\" id=\"{$sigName}Print{$this->ticket_index}\" class=\"{$sigName}Print printName\" placeholder=\"$sigPlaceholder\" $sigActive form=\"ticketForm{$this->ticket_index}\" />
                      $addressInputs
                    </form>
                  </td>
                  <td colspan=\"2\" class=\"center\" style=\"vertical-align:bottom;\">
                    <button type=\"button\" class=\"getSig $sigClass\"><img src=\"../images/sign.png\" height=\"24\" width=\"24\" alt=\"Open Signature Box\" /></button>
                  </td>
                </tr>
                <tr>
                  <td>
                    <button type=\"button\" class=\"dTicket\" form=\"ticketForm{$this->ticket_index}\">$buttonName</button> $noReturn
                  </td>";
      if ($this->processTransfer === true) {
        $confirm = "
            <table class=\"wide confirm\">
              <tbody>
                <tr>
                  <td colspan=\"2\">
                    <form id=\"ticketForm{$this->ticket_index}\" class=\"routeStop\">
                      <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"ticketNumber\" class=\"ticketNumber\" value=\"{$this->TicketNumber}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"dispatchedTo\" class=\"dispatchedTo\" value=\"{$this->DispatchedTo}\" form=\"ticketForm{$this->ticket_index}\" />
                      <input type=\"hidden\" name=\"transfers\" class=\"transfers\" value=\"$transfersFormValue\" form=\"ticketForm{$this->ticket_index}\" />
                    </form>
                  </td>
                </tr>
                <tr>";
        $confirm .= ($this->PendingReceiver !== $this->driverID) ? '
                  <td>Pending</td>
                  <td><button type="button" class="cancelTransfer">Cancel Transfer</button></td>' :
                  '<td><button type="button" class="acceptTransfer">Accept Transfer</button></td>
                  <td><button type="button" class="declineTransfer">Decline Transfer</button>';
      }
      // Make the client name look good for display
      $this->pClient .= ($this->pDepartment != null) ? "<br>{$this->pDepartment}" : '';

      $this->dClient .= ($this->dDepartment != null) ? "<br>{$this->dDepartment}" : '';

      if ($this->diWeight == 0) {
        $iceWeight = '-';
      } else {
        $iceWeight = self::number_format_drop_zero_decimals($this->diWeight, 3);
      }
      // Set the contact info
      $pContactDisplay = ($this->pContact == null) ? '' : "<tr><td>Contact:</td><td>{$this->pContact}</td></tr>";
      $pTelDisplay = ($this->pTelephone == null) ? '' : "<tr><td>Tel:</td><td><a href=\"tel:{$this->pTelephone}\" style=\"color:blue;\">{$this->pTelephone}</a></td></tr>";
      $dContactDisplay = ($this->dContact == null) ? '' : "<tr><td>Contact:</td><td>{$this->dContact}</td></tr>";
      $dTelDisplay = ($this->dTelephone == null) ? '' : "<tr><td>Tel:</td><td><a href=\"tel:{$this->dTelephone}\" style=\"color:blue;\">{$this->dTelephone}</a></td></tr>";
      $pAddressEncoded = urlencode("{$this->pAddress1}, {$this->pAddress2}, {$this->countryFromAbbr($this->pCountry)}");
      $dAddressEndoded = urlencode("{$this->dAddress1}, {$this->dAddress2}, {$this->countryFromAbbr($this->dCountry)}");
      $displayDryIce = ($this->options['displayDryIce'] === true) ? "
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
              <td class=\"center\" style=\"white-space:nowrap;\">$iceWeight{$this->weightMarker}</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
            </tr>
          </tbody>
        </table>
        <table class=\"tFieldRight\" style=\"width:75%;\">
          <thead>
            <tr>
              <th class=\"pullLeft\">Notes: <button type=\"button\" class=\"updateNotes\" form=\"ticketForm{$this->ticket_index}\">Update</button></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class=\"center\">
                <textarea class=\"wide notes\" rows=\"4\" name=\"notes\" form=\"ticketForm{$this->ticket_index}\">{$this->Notes}</textarea>
              </td>
            </tr>
          </tbody>
        </table>": "
        <table class=\"tFieldRight\">
          <thead>
            <tr>
              <th class=\"pullLeft\">Notes: <button type=\"button\" class=\"updateNotes\" form=\"ticketForm{$this->ticket_index}\">Update</button></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class=\"center\">
                <textarea class=\"wide notes\" rows=\"4\" name=\"notes\" form=\"ticketForm{$this->ticket_index}\">{$this->Notes}</textarea>
              </td>
            </tr>
          </tbody>
        </table>";
      $singleTicket .= "<div class=\"tickets sortable\">
        <h3>{$this->TicketNumber}</h3>
        <span class=\"hide rNum\">{$this->RunNumber}</span>
        <span class=\"hide pendingReceiver\">{$this->PendingReceiver}</span>
        <h3 class=\"ticketCharge floatRight\">{$this->ticketCharge($this->Charge)}</h3>
        <hr>
        <table class=\"wide\">
          <thead>
            <tr>
              <td colspan=\"2\">$label1{$this->pTime}<span class=\"timing hide\">$timing</span></td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{$this->pClient}</td>
              <td>
                <a class=\"plain addressLink\" target=\"_blank\" href=\"https://www.google.com/maps/dir//$pAddressEncoded\">
                  {$this->pAddress1}<br>{$this->pAddress2}
                </a>
              </td>
            </tr>
            <tr class=\"{$this->countryClass}\">
              <td></td>
              <td>{$this->countryFromAbbr($this->pCountry)}</td>
            </tr>
            $pContactDisplay $pTelDisplay
          </tbody>
        </table>
        <hr>
        <table class=\"wide\">
          <thead>
            <tr>
              <td colspan=\"2\">$label2{$this->dTime}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{$this->dClient}</td>
              <td>
                <a class=\"plain addressLink\" target=\"_blank\" href=\"https://www.google.com/maps/dir//$dAddressEndoded\">
                  {$this->dAddress1}<br>{$this->dAddress2}
                </a>
              </td>
            </tr>
            <tr class=\"{$this->countryClass}\">
              <td></td>
              <td>{$this->countryFromAbbr($this->dCountry)}</td>
            </tr>
            $dContactDisplay $dTelDisplay
          </tbody>
        </table>
        <hr>
        $displayDryIce
        <hr>
        <p class=\"message2 center\"></p>
        $confirm";
      if ($this->processTransfer === false) {
        $singleTicket .= "
              <td><button type=\"button\" class=\"cancelRun $buttonClass\" form=\"ticketForm{$this->ticket_index}\">Cancel</button></td>
              <td><button type=\"button\" class=\"$button2Class\" form=\"ticketForm{$this->ticket_index}\">$button2Name</button></td>
              <td><button type=\"button\" class=\"transferTicket\" form=\"ticketForm{$this->ticket_index}\">Transfer</button></td>";
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

    public function displayMultiTicket()
    {
      $multiTicket = '';
      $this->processTransfer = $this->multiTicket[0]->processTransfer;
      if ($this->processTransfer === true) {
        $this->PendingReceiver = $this->multiTicket[0]->PendingReceiver;
      }
      $readyObj = new \dateTime($this->multiTicket[0]->ReadyDate);
      switch ($this->multiTicket[0]->step) {
        case 'delivered':
          $topClient = self::decode($this->multiTicket[0]->dClient);
          if ($this->multiTicket[0]->dDepartment != null) {
            $topClient .= '<br>' . self::decode($this->multiTicket[0]->dDepartment);
          }
          $topAddressEncoded = urlencode(
            "{$this->multiTicket[0]->dAddress1}, {$this->multiTicket[0]->dAddress2}, {$this->multiTicket[0]->dCountry}"
          );
          $topAddress = "
            <a class=\"plain addressLink\" target=\"_blank\" href=\"https://www.google.com/maps/dir//$topAddressEncoded\">
              {$this->decode($this->multiTicket[0]->dAddress1)}<br>{$this->decode($this->multiTicket[0]->dAddress2)}
            </a>";
          $dTimeArray = explode(':', $this->multiTicket[0]->dTime);
          $readyObj->setTime($dTimeArray[0], $dTimeArray[1], $dTimeArray[2]);
          if ($this->multiTicket[0]->pTime > $this->multiTicket[0]->dTime) {
            $readyObj->modify('+ 1 day');
          }
          $pTime = $readyObj->format('d M g:i a');
          $timing = $readyObj->format('U');
        break;
        default:
          $topClient = self::decode($this->multiTicket[0]->pClient);
          if ($this->multiTicket[0]->pDepartment != null) {
            $topClient .= '<br>' . self::decode($this->multiTicket[0]->pDepartment);
          }
          $topAddressEncoded = urlencode(
            "{$this->multiTicket[0]->pAddress1}, {$this->multiTicket[0]->pAddress2}, {$this->multiTicket[0]->pCountry}"
          );
          $topAddress = "
            <a class=\"plain addressLink\" target=\"_blank\" href=\"https://www.google.com/maps/dir//$topAddressEncoded\">
              {$this->decode($this->multiTicket[0]->pAddress1)}<br>{$this->decode($this->multiTicket[0]->pAddress2)}
            </a>";
          switch ($this->multiTicket[0]->step) {
            case 'pickedUp':
              $pTime = $readyObj->format('d M g:i a');
              $timing = $readyObj->format('U');
            break;
            case 'returned':
              $d2TimeArray = explode(':', $this->multiTicket[0]->d2Time);
              $readyObj->setTime($d2TimeArray[0], $d2TimeArray[1], $d2TimeArray[2]);
              if ($this->multiTicket[0]->dTime > $this->multiTicket[0]->d2Time) {
                $readyObj->modify('+ 1 day');
              }
              $pTime = $readyObj->format('d M g:i a');
              $timing = $readyObj->format('U');
            break;
          }
        break;
      }
      $multiTicket .= "<div class=\"tickets sortable centerDiv\">
          <table class=\"wide\">
            <thead>
              <tr>
                <td colspan=\"2\" class=\"center\"><h3>$pTime</h3><span class=\"timing hide\">$timing</span></td>
              </tr>
              <tr>
                <td colspan=\"2\"><hr></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>$topClient</td>
                <td>$topAddress</td>
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
            if ($this->multiTicket[$i]->pDepartment != null) {
              $client .= '<br>' . self::decode($this->multiTicket[$i]->pDepartment);
            }
            $addressEndoded = urlencode(
              "{$this->multiTicket[$i]->pAddress1}, {$this->multiTicket[$i]->pAddress2}, {$this->multiTicket[$i]->pCountry}"
            );
            $address = "
              <a class=\"plain addressLink\" target=\"_blank\" href=\"https://www.google.com/maps/dir//$addressEndoded\">
                {$this->decode($this->multiTicket[$i]->pAddress1)}<br>{$this->decode($this->multiTicket[$i]->pAddress2)}
              </a>";
            $contact = ($this->multiTicket[$i]->dContact == null) ?
            '' :
            "<tr>
              <td>Contact:</td>
              <td>{$this->decode($this->multiTicket[$i]->dContact)}</td>
            </tr>";

            $tel = ($this->multiTicket[$i]->dTelephone == null) ?
            '' :
            "<tr>
              <td>Tel:</td>
              <td>
                <a href=\"tel:{$this->multiTicket[$i]->dTelephone}\" style=\"color:blue;\">
                  {$this->multiTicket[$i]->dTelephone}
                </a>
              </td>
            </tr>";
            break;
          default:
            $client = self::decode($this->multiTicket[$i]->dClient);
            if ($this->multiTicket[$i]->dDepartment != null) {
              $client .= '<br>' . self::decode($this->multiTicket[$i]->dDepartment);
            }
            $addressEndoded = urlencode(
              "{$this->multiTicket[$i]->dAddress1}, {$this->multiTicket[$i]->dAddress2}, {$this->multiTicket[$i]->dCountry}"
            );
            $address = "
              <a class=\"plain addressLink\" target=\"_blank\" href=\"https://www.google.com/maps/dir//$addressEndoded\">
                {$this->decode($this->multiTicket[$i]->dAddress1)}<br>{$this->decode($this->multiTicket[$i]->dAddress2)}
              </a>";

            $contact = ($this->multiTicket[$i]->pContact == null) ?
            '' :
            "<tr>
              <td>Contact:</td>
              <td>{$this->decode($this->multiTicket[$i]->pContact)}</td>
            </tr>";

            $tel = ($this->multiTicket[$i]->pTelephone == null) ?
            '' :
            "<tr>
              <td>Tel:</td>
              <td>
                <a href=\"tel:{$this->multiTicket[$i]->pTelephone}\" style=\"color:blue;\">
                  {$this->multiTicket[$i]->pTelephone}
                </a>
              </td>
            </tr>";
            break;
        }
        switch ($this->multiTicket[$i]->step) {
          case 'pickedUp':
            $label = 'Pick Up For';
            $buttonName = 'Cancel';
            $buttonClass = 'cancelRun';
            $button2Class = 'deadRun';
            $button2Name = 'Dead Run';
            $noReturn = '';
            break;
          case 'delivered':
            $label = 'Deliver From';
            $buttonName = 'Not Used';
            $buttonClass = 'hide';
            $button2Class = ($this->processTransfer) ? 'hide' : 'declined';
            $button2Name = 'Declined';
            $noReturn = ($this->Charge === 6) ?
              "<label for=\"noReturn{$this->ticket_index}\">No Return</label><input type=\"checkbox\" name=\"noReturn\" id=\"noReturn{$this->ticket_index}\" class=\"noReturn\" value=\"1\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />" : '';
            break;
          case 'returned':
            $label = 'Return From';
            $buttonName = 'Not Used';
            $buttonClass = $button2Class = 'hide';
            $button2Name = '';
            $noReturn = '';
            break;
        }
        $transfersFormValue = ($this->multiTicket[$i]->Transfers) ? htmlspecialchars($this->multiTicket[$i]->Transfers) : '';
        $displayDryIce = ($this->options['displayDryIce'] === true) ? "
              <td colspan=\"2\">
                <table class=\"tFieldLeft\" style=\"width:25%;\">
                  <tr>
                    <th class=\"pullLeft\">Dry Ice:</th>
                  </tr>
                  <tr>
                    <td class=\"center\">$iceWeight{$this->weightMarker}</td>
                  </tr>
                </table>
                <table class=\"tFieldRight\" style=\"width:75%;\">
                  <tr>
                    <th class=\"pullLeft\">Notes: <button type=\"button\" class=\"updateNotes\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\">Update</button></th>
                  </tr>
                  <tr>
                    <td>
                      <textarea class=\"wide notes\" rows=\"4\" name=\"notes\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\">{$this->decode($this->multiTicket[$i]->Notes)}</textarea>
                    </td>
                  </tr>
                </table>
              </td>": "
              <td colspan=\"2\">
                <table class=\"tFieldRight\">
                  <tr>
                    <th class=\"pullLeft\">Notes: <button type=\"button\" class=\"updateNotes\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\">Update</button></th>
                  </tr>
                  <tr>
                    <td>
                      <textarea class=\"wide notes\" rows=\"4\" name=\"notes\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\">
                        {$this->decode($this->multiTicket[$i]->Notes)}
                      </textarea>
                    </td>
                  </tr>
                </table>
              </td>";
        $multiTicket .= "<table class=\"tickets center\">
          <tfoot>
            <tr>
              <td><button type=\"button\" class=\"$buttonClass\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\">$buttonName</button>$noReturn</td>
              <td><button type=\"button\" class=\"$button2Class\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\">$button2Name</button></td>
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
                  <input type=\"hidden\" name=\"ticket_index\" class=\"ticket_index\" value=\"{$this->multiTicket[$i]->ticket_index}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"ticketNumber\" class=\"ticketNumber\" value=\"{$this->multiTicket[$i]->TicketNumber}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"dispatchedTo\" class=\"dispatchedTo\" value=\"{$this->multiTicket[$i]->DispatchedTo}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"runNumber\" class=\"runNumber\" value=\"{$this->multiTicket[$i]->RunNumber}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"charge\" class=\"charge\" value=\"{$this->multiTicket[$i]->Charge}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"emailConfirm\" class=\"emailConfirm\" value=\"{$this->multiTicket[$i]->EmailConfirm}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"emailAddress\" class=\"emailAddress\" value=\"{$this->multiTicket[$i]->EmailAddress}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"transfers\" class=\"transfers\" value=\"$transfersFormValue\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"pendingReceiver\" class=\"pendingReceiver\" value=\"{$this->multiTicket[$i]->PendingReceiver}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"step\" class=\"step\" value=\"{$this->multiTicket[$i]->step}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"ticketBase\" class=\"ticketBase\" value=\"{$this->multiTicket[$i]->TicketBase}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"pClient\" class=\"pClient\" value=\"{$this->multiTicket[$i]->pClient}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"pAddress1\" class=\"pAddress1\" value=\"{$this->multiTicket[$i]->pAddress1}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"dClient\" class=\"dClient\" value=\"{$this->multiTicket[$i]->dClient}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"dAddress1\" class=\"dAddress1\" value=\"{$this->multiTicket[$i]->dAddress1}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                  <input type=\"hidden\" name=\"diPrice\" class=\"diPrice\" value=\"{$this->multiTicket[$i]->diPrice}\" form=\"ticketForm{$this->multiTicket[$i]->ticket_index}\" />
                </form>
                <h3 class=\"floatLeft\">{$this->multiTicket[$i]->TicketNumber}</h3>
                $label
                <h3 class=\"ticketCharge floatRight\">{$this->ticketCharge($this->Charge)}</h3>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>$client</td>
              <td>$address</td>
            </tr>
              $contact $tel
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
            <tr>
            $displayDryIce
            </tr>
          </tbody>
        </table>";
      }
      $multiTicket .= '<p class="message2 center"></p>';
      $count = 'count';
      if ($this->processTransfer === false) {
        $multiTicket .= "
          <p class=\"center\">
            <input type=\"hidden\" name=\"sigImage\" id=\"sigImage{$this->multiTicket[0]->ticket_index}\" class=\"sigImage\" />
            <input type=\"hidden\" name=\"latitude\" class=\"latitude\" form=\"ticketForm{$this->ticket_index}\" value=\"\" />
            <input type=\"hidden\" name=\"longitude\" class=\"longitude\" form=\"ticketForm{$this->ticket_index}\" value=\"\" />
            <label for=\"pSigPrint{$this->ticket_index}\">Signer</label><br><input type=\"text\" name=\"pSigPrint\" id=\"pSigPrint{$this->multiTicket[0]->ticket_index}\" class=\"pSigPrint printName\" form=\"ticketForm{$this->multiTicket[0]->ticket_index}\" /><button type=\"button\" style=\"vertical-align:middle;\" class=\"getSig\"><img src=\"../images/sign.png\" height=\"24\" width=\"24\" alt=\"Open Signature Box\" /></button>
          </p>
          <div class=\"signature-pad sigField\"></div>
          <button type=\"button\" class=\"confirmAll\">Confirm {$count($this->multiTicket)}</button> <button type=\"button\" class=\"transferGroup\">Transfer {$count($this->multiTicket)}</button></div>";
      } else {
        if ($this->PendingReceiver === $this->driverID) {
          $multiTicket .= '<button type="button" class="acceptTransferGroup">Accept Transfer Group</button>
                <button type="button" class="declineTransferGroup">Decline Transfer Group</button>
                ';
        } else {
          $multiTicket .= 'Pending <button type="button" class="cancelTransferGroup">Cancel Transfer Group</button>';
        }
      }
      return $multiTicket;
    }

    private function javascriptVars()
    {
      if ($this->userType != 'client') return;
      $returnData = '<form id="javascriptVars">';
      foreach ($this->javascriptKeys as $_ => $value) {
        $temp = $this->members[$this->ClientID]->getProperty($value);
        if (array_key_exists($temp, $this->clientNameExceptions)) $temp = $this->clientNameExceptions[$temp];
        $returnData .= "
            <input type=\"hidden\" name=\"$value\" value=\"{$this->decode($temp)}\" form=\"javascriptVars\" />";
      }
      $returnData .= '</form>';
      return $returnData;
    }

    protected function hiddenInputs()
    {
      $returnData = '';
      $htmlentities = 'htmlentities';
      $lcfirst = 'lcfirst';
      foreach ($this as $key => $value) {
        // Don't include values ending with 'List' in the form to add a new ticket
        if (substr($this->formName, 0, 12) === 'submitTicket' && substr($key, -4) === 'List') {
          break;
        }
        if (in_array($key, $this->postableKeys)) {
          $returnData .= "
            <input type=\"hidden\" name=\"{$lcfirst($key)}\" value=\"{$htmlentities($value)}\" form=\"{$this->formName}\" />";
        }
      }
      return $returnData;
    }

    public function ticketsToDispatch()
    {
      $response = (object) [
        'state' => 'valid',
        'message' => null,
        'tickets' => null
      ];
      $returnData = '';
      $ticketQueryResult = [];
      $this->forDisatch = true;
      // Pull tickets that have not been dispatched
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['queryParams'] = [];
      if ($this->ticket_index === null) {
        $ticketQueryData['queryParams']['filter'] = [
          ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
          ['Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>'-'],
          ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->Contract]
        ];
      } else {
        $ticketQueryData['queryParams']['filter'] = [
          ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
          ['Resource'=>'ticket_index', 'Filter'=>'eq', 'Value'=>$this->ticket_index]
        ];
      }
      if ($this->ticketEditor === false) {
        $this->driverID = 0;
        $ticketQueryData['queryParams']['filter'][] = ['Resource'=>'DispatchTimeStamp', 'Filter'=>'is'];
        $ticketQueryData['queryParams']['filter'][] = ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,8'];
      } else {
        if ($this->ticket_index === null) {
          if (preg_match('/\d{4}-\d{2}-\d{2}/', $this->ticketEditorSearchDate) !== 1) {
            $this->error = '<p class="center result">Invalid search date. Please use YYYY-mm-dd</p>';
            if ($this->enableLogging !== false) self::writeLoop();
            return $this->error;
          }
          $ticketQueryData['queryParams']['filter'][] = ['Resource'=>'ReceivedDate', 'Filter'=>'sw', 'Value'=>$this->ticketEditorSearchDate];
        }
      }
      if ($this->ticket_index === null) {
        $ticketQueryData['queryParams']['filter'][] = ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->DispatchedTo];
      }

      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        $response->state = 'error';
        $response->message = self::getError();
        return json_encode($response);
      }
      $ticketQueryResult = self::callQuery($ticketQuery);
      if ($ticketQueryResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        $response->state = 'error';
        $response->message = self::getError();
        return json_encode($response);
      }

      if (empty($ticketQueryResult)) {
        $response->state = 'info';
        $response->message = 'No Tickets Available.';
        return json_encode($response);
      }
      $response->tickets = $ticketQueryResult;
      // return json_encode($response);
      if ($this->driverList === null) {
        self::fetchDrivers();
      }
      if ($this->ticketEditor === false) {
        for ($i = 0; $i < count($ticketQueryResult); $i++) {
          foreach ($ticketQueryResult[$i] as $key => $value) {
            if (property_exists($this, $key)) {
              $this->$key = $value;
            }
          }
          $returnData .= self::regenTicket();
        }
        self::clearTicket();
        return $returnData;
      } else {
        $this->forDisatch = false;
        $this->ticketEditor = true;
        // Sort the tickets based on charge code and timestamps
        foreach ($ticketQueryResult as $ticket) {
          if ($ticket['pTimeStamp'] === $this->tTest && $ticket['Charge'] !== 9) {
            $this->activeTicketSet[] = $ticket;
          } elseif (
            $ticket['pTimeStamp'] !== $this->tTest &&
            $ticket['dTimeStamp'] === $this->tTest &&
            $ticket['Charge'] !== 9
          ) {
            $this->activeTicketSet[] = $ticket;
          } elseif (
            $ticket['pTimeStamp'] !== $this->tTest &&
            $ticket['dTimeStamp'] !== $this->tTest &&
            $ticket['d2TimeStamp'] === $this->tTest &&
            ($ticket['Charge'] === 6 || ($ticket['Charge'] === 7 && self::test_bool($ticket['d2SigReq']) === true))
          ) {
            $this->activeTicketSet[] = $ticket;
          }
        }
        if (empty($this->activeTicketSet)) {
          return '<p class="center result">No Tickets Available.</p>';
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

    public function ticketQueryForm()
    {
      $this->formType = 'Query';
      if ($this->userType === 'client' && $this->ulevel === 2) {
        $returnData = "
            <div id=\"ticketQueryOptions\">
              <form id=\"deliveryQuery\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                <input type=\"hidden\" name=\"billTo\" value=\"{$this->ClientID}\" />
                <input type=\"hidden\" name=\"repeatClient\" value=\"{$this->RepeatClient}\" />
                <input type=\"hidden\" name=\"endPoint\" class=\"endPoint\" value=\"tickets\" />
                <input type=\"hidden\" name=\"method\" class=\"method\" value=\"GET\" />
                <fieldset form=\"deliveryQuery\" name=\"dateRange\">
                  <legend>Search Parameters</legend>
                  <div>
                    <p>
                      <label for=\"allTime\">All Time:</label>
                      <input type=\"hidden\" name=\"allTime\" value=\"0\" />
                      <input type=\"checkbox\" name=\"allTime\" id=\"allTime\" class=\"allTime2\" value=\"1\" />
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
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'startDate', 'disabled' => true ])}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'startDate', 'type' => 'date', 'table' => 'tickets' ])}</span>
                    </p>
                    <p>
                      <label for=\"endDate\">End Date:</label>
                      <input type=\"hidden\" name=\"endDate\" class=\"endDateMarker\" disabled />
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'endDate', 'disabled' => true ])}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'endDate', 'type' => 'date', 'table' => 'tickets' ])}</span>
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
                <input type=\"hidden\" name=\"billTo\" value=\"{$this->ClientID}\" />
                <input type=\"hidden\" name=\"repeatClient\" value=\"{$this->RepeatClient}\" />
                <input type=\"hidden\" name=\"endPoint\" class=\"endPoint\" value=\"tickets\" />
                <input type=\"hidden\" name=\"method\" class=\"method\" value=\"GET\" />
                <fieldset form=\"deliveryQuery\" name=\"dateRange\">
                  <legend>Search Parameters</legend>
                  <div>
                    <p>
                      <label for=\"allTime\">All Time:</label>
                      <input type=\"hidden\" name=\"allTime\" value=\"0\" />
                      <input type=\"checkbox\" name=\"allTime\" id=\"allTime\" class=\"allTime\" value=\"1\" />
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
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'startDate', 'disabled' => true ])}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'startDate', 'type' => 'date', 'table' => 'tickets' ])}</span>
                    </p>
                    <p>
                      <label for=\"endDate\">End Date:</label>
                      <input type=\"hidden\" name=\"endDate\" class=\"endDateMarker\" disabled />
                      <span style=\"display:none;\" class=\"chartDate\" title=\"Query Range Limited To 6 Month Periods\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'endDate', 'disabled' => true ])}</span>
                      <span class=\"ticketDate\">{$this->createLimitedMonthInput([ 'clientIDs' => $this->ClientID, 'inputID' => 'endDate', 'type' => 'date', 'table' => 'tickets' ])}</span>
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
                      <span class=\"compare\">
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
            <div id=\"ticketQueryOptions\">
              <form id=\"deliveryQuery\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                <input type=\"hidden\" name=\"endPoint\" value=\"tickets\" />
                <input type=\"hidden\" name=\"method\" value=\"GET\" />
                <fieldset form=\"deliveryQuery\">
                  <legend>Search Parameters</legend>
                  <div id=\"orgInputContainer\">
                    <div>
                      <p>
                        <label for=\"allTime\">All Time:</label>
                        <input type=\"hidden\" name=\"allTime\" value=\"0\" />
                        <input type=\"checkbox\" name=\"allTime\" id=\"allTime\" value=\"1\" />
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
                          {$this->createLimitedMonthInput([ 'clientIDs' => $array_keys($_SESSION['members']), 'inputID' => 'startDate', 'disabled' => true ])}
                        </span>
                        <span class=\"ticketDate\">
                          {$this->createLimitedMonthInput([ 'clientIDs' => $array_keys($_SESSION['members']), 'inputID' => 'startDate', 'type' => 'date', 'table' => 'tickets' ])}
                        </span>
                      </p>
                      <p>
                        <label for=\"endDate\">End Date:</label>
                        <input type=\"hidden\" name=\"endDate\" id=\"endDateMarker\" />
                        <span class=\"chartDate\" style=\"display:none;\" title=\"Query Range Limited To 6 Month Periods\">
                          {$this->createLimitedMonthInput([ 'clientIDs' => $array_keys($_SESSION['members']), 'inputID' => 'endDate', 'disabled' => true ])}
                        </span>
                        <span class=\"ticketDate\">
                          {$this->createLimitedMonthInput([ 'clientIDs' => $array_keys($_SESSION['members']), 'inputID' => 'endDate', 'type' => 'date', 'table' => 'tickets' ])}
                        </span>
                      </p>
                    </div>
                    <div>
                      <p>
                        <label for=\"charge\">Charge:</label>
                        <input type=\"hidden\" name=\"charge\" id=\"chargeMarker\" value=\"10\" />
                        <select name=\"charge\" id=\"chargeHistory\">
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
                        <input type=\"hidden\" name=\"display\" id=\"displayMarker\" value=\"tickets\" disabled />
                        <select name=\"display\" id=\"display\">
                          <option value=\"tickets\">Tickets</option>
                          <option value=\"chart\">Chart</option>
                        </select>
                      </p>
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
                  </div>
                </fieldset>
                <p class=\"centerDiv\">{$this->listOrgMembers('ticket')}</p>
                <button type=\"submit\" class=\"submitOrgTickets\" title=\"Select a member or&#10enter a ticket number to continue\">Query</button>
              </form>
            </div>
            <div id=\"ticketQueryResults\"></div>";
      }
      return $returnData;
    }

    private function createChargeSelectOptions()
    {
      $returnData = '';
      $testCharge = $this->Charge ?? $this->options['initialCharge'] ?? 'NAN';
      if ($this->userType === 'client') {
        if ($this->ClientID === 0) {
          $excludes = (isset($this->options["client0Charges{$this->formType}Exclude"])) ?
          $this->options["client0Charges{$this->formType}Exclude"] : [];
        } else {
          $excludes = (isset($this->options["clientCharges{$this->formType}Exclude"][$this->ulevel - 1])) ?
          $this->options["clientCharges{$this->formType}Exclude"][$this->ulevel - 1] : [];
        }
      } elseif ($this->userType === 'driver') {
        $excludes = (isset($this->options["driverCharges{$this->formType}Exclude"][$this->CanDispatch - 1])) ?
        $this->options["driverCharges{$this->formType}Exclude"][$this->CanDispatch - 1] : [];
      } else {
        $excludes = (isset($this->options["{$this->userType}Charges{$this->formType}Exclude"])) ?
        $this->options["{$this->userType}Charges{$this->formType}Exclude"] : [];
      }
      for ($i=0; $i < 10; $i++) {
        if (!in_array($i, $excludes, true)) {
          $selected = ($testCharge === $i) ? 'selected' : '';
          $returnData .= "
          <option value=\"$i\" $selected>{$this->ticketCharge($i)}</option>";
        }
      }
      if ($this->formType === 'Query' && (is_numeric($this->ulevel) && ($this->ulevel < 2 || $this->ClientID === 0))) {
        $temp = $returnData;
        $returnData = "
          <option value=\"10\">All</option>$temp";
      }
      return $returnData;
    }

    public function initTicketEditor()
    {
      return "
            <form id=\"ticketEditor\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
              <input type=\"hidden\" name=\"ticketEditor\" value=\"1\" form=\"ticketEditor\" />
              <p>
                <span class=\"item\">
                  <label for=\"dispatchedTo\">Driver:</label>
                  <input list=\"drivers\" name=\"dispatchedTo\" class=\"dispatchedTo\" form=\"ticketEditor\" />
                </span>
                <span class=\"item\">
                  <label for=\"contract\">Type:</label>
                  <select name=\"contract\" class=\"contract\" form=\"ticketEditor\">
                    <option value=\"0\">On Call</option>
                    <option value=\"1\">Contract</option>
                  </select>
                </span>
                <span class=\"item\">
                  <label for=\"ticketEditorDate\">Date</label>
                  <input type=\"date\" name=\"ticketEditorSearchDate\" class=\"ticketEditorSearchDate\" value=\"{$this->today->format('Y-m-d')}\" form=\"ticketEditor\" />
                </span>
                <span class=\"item\">
                  <button type=\"submit\" id=\"ticketEditorSubmit\" form=\"ticketEditor\">Submit</button>
                  <button type=\"button\" id=\"clearTicketEditorResults\" form=\"ticketEditor\">Clear Results</button>
                </span>
              </p>
            </form>
            <hr>
            <span class=\"message\"></span>
            <span id=\"ticketEditorResultContainer\"><p class=\"center\">Select Driver &amp; Ticket Type</p></span>";
    }

    public function ticketForm()
    {
      if ($this->userType == 'org' && $_SESSION['org_id']['RequestTickets'] < 2) {
        return "<div id=\"deliveryRequest{$this->ticket_index}\" class=\"removableByEditor\">
        <p class=\"center\">Feature Not Available.</p>
        </div>";
      }
      $this->Contract = $this->Contract ?? 0;
      $this->RunNumber = $this->RunNumber ?? 0;
      $returnData = '';
      $this->action = self::esc_url($_SERVER['REQUEST_URI']);
      $this->formType = 'Entry';
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($this->newTicket !== false || ($this->ticket_index !== null && $this->updateTicket !== false)) {
          try {
            $returnData = self::processTicket();
          } catch (\Exception $e) {
            throw $e;
          }
          return $returnData;
        }
        if ($this->edit !== null && self::test_bool($this->edit) === false) {
          return self::confirmRequest();
        }
      }
      if ($this->locationList === null) {
        if (!self::buildLocationList()) {
          return $this->error;
        }
      }
      $returnData .= self::javascriptVars();
      try {
        $returnData .= self::buildDatalists();
      } catch (\Exception $e) {
        throw $e;
      }
      if ($this->ticket_index !== null) {
        if (!self::queryTicket()) {
          return $this->error;
        }
        $this->action = '/drivers/ticketEditor';
      }
      if ($this->BillTo !== null) {
        if ($this->RepeatClient === 1 && $this->clientList !== 'empty' && $this->clientList !== null) {
          foreach (json_decode(urldecode($this->clientList), true) as $client) {
            if ($client['ClientID'] == $this->BillTo) {
              $this->BillTo = ($client['Department'] == null) ?
              "{$client['ClientName']}; {$client['ClientID']}" :
              "{$client['ClientName']}, {$client['Department']}; {$client['ClientID']}";
            }
          }
        } else {
          if ($this->tClientList !== 'empty' && $this->tClientList !== null) {
            foreach (json_decode(urldecode($this->tClientList), true) as $client) {
              if ($client['ClientID'] == $this->BillTo) {
                $this->BillTo = ($client['Department'] == null) ?
                "{$client['ClientName']}; {$client['ClientID']}" :
                "{$client['ClientName']}, {$client['Department']}; {$client['ClientID']}";
              }
            }
          }
        }
      }
      if ($this->driverList !== null) {
        foreach (json_decode(urldecode($this->driverList), true) as $driver) {
          if ($driver['DriverID'] == $this->DispatchedTo) {
            $this->DriverName = ($driver['LastName'] === null) ?
            $driver['FirstName'] :
            $driver['FirstName'] . ' ' . $driver['LastName'];
          }
        }
      }
      // Check boxes and display notices based on values
      $toMeChecked = (self::test_bool($this->toMe) === true) ? 'checked' : '';
      $fromMeCheked = (self::test_bool($this->fromMe) === true) ? 'checked' : '';
      $pSigChecked = (self::test_bool($this->pSigReq) === true) ? 'checked' : '';
      $dSigChecked = (self::test_bool($this->dSigReq) === true) ? 'checked' : '';
      $d2SigChecked = (self::test_bool($this->d2SigReq) === true) ? 'checked' : '';
      $sigNoteClass = (
        self::test_bool($this->pSigReq) === true ||
        self::test_bool($this->dSigReq) === true ||
        self::test_bool($this->d2SigReq) === true
      ) ? '' : 'hide';
      $emailNoteClass = (self::test_bool($this->EmailConfirm) === false) ? 'hide' : '';
      $dryIceChecked = $diWeightMarkerDisabled = '';
      $diWeightDisabled = 'disabled';
      if (self::test_bool($this->dryIce) === true) {
        $dryIceChecked = 'checked';
        $diWeightMarkerDisabled = 'disabled';
        $diWeightDisabled = '';
      }
      $rtDisplay = ($this->Charge === 6 || $this->Charge === 7) ? 'inline-block' : 'none';
      if ($this->ReceivedReady === 1) {
        $readyChecked = 'checked';
        $readyNote = 'inline-block';
        $readyDateDisplay = 'none';
        $readyDate = '';
      } else {
        $readyChecked = '';
        $readyNote = 'none';
        $readyDateDisplay = 'inline-block';
        $readyDate = preg_replace('/\s/', 'T', $this->ReadyDate);
      }

      $emailConfirm0 = $emailConfirm1 = $emailConfirm2 = $emailConfirm3 =
      $emailConfirm4 = $emailConfirm5 = $emailConfirm6 = $emailConfirm7 = '';

      switch ($this->EmailConfirm) {
        case 0:
          $emailConfirm0 = 'selected';
          break;
        case 1:
          $emailConfirm1 = 'selected';
          break;
        case 2:
          $emailConfirm2 = 'selected';
          break;
        case 3:
          $emailConfirm3 = 'selected';
          break;
        case 4:
          $emailConfirm4 = 'selected';
          break;
        case 5:
          $emailConfirm5 = 'selected';
          break;
        case 6:
          $emailConfirm6 = 'selected';
          break;
        case 7:
          $emailConfirm7 = 'selected';
          break;
      }
      $emailNoteDisplay = (self::test_bool($this->EmailConfirm) === false) ? 'hide' : '';
      
      $hideBillToLabel = '';

      if ($this->userType === 'client' || $this->userType === 'org') {
        $repeatOption = $readonlyDispatch = $hideFromDriver = $hideDispatch = $requiredDispatch =
        $billToRequired = $dispatchedBy = $transferredBy = $cancelTicketEditor =
        $nonRepeatChecked = '';
        $billingRowClass = 'hide';
        $dispatchInputType = 'type="hidden"';
        $billToType = 'type="hidden"';
        $hideBillToLabel = 'hide';
        
        if ($this->userType == 'client') {
          $this->RepeatClient = $this->members[$this->ClientID]->getProperty('RepeatClient');
          $org_id_json = $this->members[$this->ClientID]->getProperty('org_id');
          $org_id = json_decode($org_id_json,true);
          $requestTickets = (is_array($org_id) && array_key_exists('RequestTickets',$org_id)) ?
            $org_id['RequestTickets'] : false;
          if ($requestTickets !== false && ($requestTickets == 1 || $requestTickets >= 3)) {
            $billToType = 'list="members"';
            $billToRequired = 'required';
            $hideBillToLabel = '';
          }
        } else {
          $hideFromDriver = 'class="hide"';
          $this->RepeatClient = 1;
          $billToType = 'list="members"';
          $billToRequired = 'required';
          $hideBillToLabel = '';
        }
        $billToValue = ($this->userType == 'client') ? $_SESSION['ClientName'] : false;
        if ($billToValue) {
          $billToValue .= ($_SESSION['Department'] !== null) ? ", {$_SESSION['Department']}" : '';
          $billToValue .= '; ' . $_SESSION['ClientID'];
        }

        $dispatchInputValue = '0';
      } else {
        $billingRowClass = '';
        $dispatchInputType = ($this->userType === 'dispatch' || $this->CanDispatch === 2) ?
        'list="drivers"' :
        (($this->CanDispatch === 1) ? 'type="text"' : 'type="hidden"');

        $readonlyDispatch = ($this->CanDispatch === 1) ? 'readonly' : '';
        $requiredDispatch = ($this->CanDispatch === 2) ? 'required' : '';
        if ($this->ticket_index != null) {
          $dispatchInputValue = $this->DriverName . '; ' . $this->DispatchedTo;
        } else {
          $dispatchInputValue = ($this->CanDispatch === 1) ?
          $_SESSION['driverName'] . '; ' . $_SESSION['DriverID'] :
          (($this->DispatchedTo == null) ? '' : $this->DriverName . '; ' . $this->DispatchedTo);
        }
        $dispatchedBy = ($this->ticketEditor === false) ? "
          <input type=\"hidden\" name=\"dispatchedBy\" class=\"dispatchedBy\" value=\"{$this->DispatchedBy}\" />" : '';
        $hideDispatch = ($this->CanDispatch >= 1) ? '' : 'class="hide"';
        $billToType = (self::test_bool($this->RepeatClient) === false) ? 'list="t_clients"' : 'list="clients"';
        $nonRepeatChecked = (self::test_bool($this->RepeatClient) === false) ? 'checked' : '';
        $billToValue = $this->BillTo;
        $billToRequired = 'required';
        $repeatOption = "
          <input type=\"checkbox\" name=\"repeatClient\" class=\"repeat\" id=\"repeatClient{$this->ticket_index}\" value=\"0\" form=\"request{$this->ticket_index}\" />";
        $hideFromDriver = 'class="hide"';
        $transfersValue = ($this->Transfers === null) ? '' : htmlentities(json_encode($this->Transfers));
        $transferredBy = ($this->ticketEditor === true) ? "<input type=\"hidden\" name=\"transferredBy\" class=\"transferredBy\" value=\"{$this->transferredBy}\" form=\"request{$this->ticket_index}\" />
        <input type=\"hidden\" name=\"holder\" class=\"holder\" value=\"{$this->DispatchedTo}\" form=\"request{$this->ticket_index}\" />
        <input type=\"hidden\" name=\"transfers\" class=\"transfers\" value=\"$transfersValue\" form=\"request{$this->ticket_index}\" />" : '';
        $cancelTicketEditor = ($this->ticketEditor === true) ? '<button type="button" class="cancelTicketEditor floatRight">Cancel</button>' : '';
      }
      // Display the ticket form
      $indexInput = ($this->ticket_index == null) ? '' : "<input type=\"hidden\" name=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"request{$this->ticket_index}\" />
      ";
      $ticketEditor = ($this->ticketEditor === true) ? "
        <input type=\"hidden\" name=\"ticketEditor\" value=\"1\" form=\"request{$this->ticket_index}\" />" : '';
      $ticketNumberInput = ($this->TicketNumber !== null) ? "
        <input type=\"hidden\" name=\"ticketNumber\" class=\"ticketNumber\" value=\"{$this->TicketNumber}\" form=\"request{$this->ticket_index}\" />
        " : '';
      $d2TimeStampDisabled = ($this->Charge === 6 || $this->Charge === 7) ? '' : 'disabled';
      if ($this->ticketEditor === true) {
        $dispatchTimeStamp = preg_replace('/\s/', 'T', $this->DispatchTimeStamp);
        $pTimeStamp = preg_replace('/\s/', 'T', $this->pTimeStamp);
        $dTimeStamp = preg_replace('/\s/', 'T', $this->dTimeStamp);
        $d2TimeStamp = preg_replace('/\s/', 'T', $this->d2TimeStamp);
        $timing = "
        <tr>
          <td colspan=\"2\">
            <fieldset form=\"request{$this->ticket_index}\" id=\"timing{$this->ticket_index}\">
              <legend>Timing</legend>
              <table class=\"centerDiv\">
                <tr>
                  <td>
                    <label for=\"dispatchTimeStamp{$this->ticket_index}\">Dispatch:</label>
                    <input type=\"datetime-local\" name=\"dispatchTimeStamp\" id=\"dispatchTimeStamp{$this->ticket_index}\" class=\"dispatchTimeStamp\" value=\"$dispatchTimeStamp\" step=\"1\" form=\"request{$this->ticket_index}\" />
                  </td>
                  <td>
                    <label for=\"pTimeStamp{$this->ticket_index}\">Pickup:</label>
                    <input type=\"datetime-local\" name=\"pTimeStamp\" id=\"pTimeStamp{$this->ticket_index}\" class=\"pTimeStamp\" value=\"$pTimeStamp\" step=\"1\" form=\"request{$this->ticket_index}\" />
                  </td>
                </tr>
                <tr>
                  <td>
                    <label for=\"dTimeStamp{$this->ticket_index}\">Delivery:</label>
                    <input type=\"datetime-local\" name=\"dTimeStamp\" id=\"dTimeStamp{$this->ticket_index}\" class=\"dTimeStamp\" value=\"$dTimeStamp\" step=\"1\" form=\"request{$this->ticket_index}\" /></td>
                  </td>
                  <td>
                    <label for=\"d2TimeStamp{$this->ticket_index}\">Return:</label>
                    <input type=\"datetime-local\" name=\"d2TimeStamp\" id=\"d2TimeStamp{$this->ticket_index}\" class=\"d2TimeStamp\" value=\"$d2TimeStamp\" step=\"1\" form=\"request{$this->ticket_index}\" $d2TimeStampDisabled /></td>
                  </td>
                </tr>
              </table>
            </fieldset>
          </td>
        </tr>
      ";
      } else {
        $timing = '';
      }
      $ticketEditorValues = ($this->ticket_index === null) ? '' : "<input type=\"hidden\" name=\"ticketBase\" value=\"{$this->TicketBase}\" form=\"request{$this->ticket_index}\" />
          <input type=\"hidden\" name=\"runPrice\" value=\"{$this->RunPrice}\" form=\"request{$this->ticket_index}\" />
          <input type=\"hidden\" name=\"receivedDate\" value=\"{$this->ReceivedDate}\" form=\"request{$this->ticket_index}\" />
          ";
      $displayDryIce = ($this->options['displayDryIce'] === true) ? "
              <td>
                <fieldset form=\"request{$this->ticket_index}\" id=\"diField{$this->ticket_index}\">
                  <legend>
                    <label for=\"dryIce{$this->ticket_index}\">Dry Ice:</label>
                    <input type=\"hidden\" name=\"dryIce\" id=\"dryIceMarker{$this->ticket_index}\" value=\"0\" form=\"request{$this->ticket_index}\" />
                    <input type=\"checkbox\" name=\"dryIce\" id=\"dryIce{$this->ticket_index}\" class=\"dryIce\" value=\"1\" $dryIceChecked form=\"request{$this->ticket_index}\" />
                  </legend>
                  <table class=\"centerDiv wide\">
                    <tr>
                      <td colspan=\"2\">&nbsp;</td>
                    </tr>
                    <tr>
                      <td class=\"ticketSpace\"></td>
                      <td title=\"Increments of 5 please\">
                        <label for=\"diWeight{$this->ticket_index}\">Weight:</label>
                        <input type=\"hidden\" name=\"diWeight\" id=\"diWeightMarker{$this->ticket_index}\" class=\"diWeightMarker\" value=\"0\" $diWeightMarkerDisabled form=\"request{$this->ticket_index}\" />
                        <input type=\"number\" name=\"diWeight\" id=\"diWeight{$this->ticket_index}\" class=\"diWeight\" form=\"request{$this->ticket_index}\" min=\"0\" step=\"{$this->dryIceStep}\" value=\"{$this->number_format_drop_zero_decimals($this->diWeight, 3)}\" $diWeightDisabled />{$this->weightMarker}
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
              </td>" : "
              <td colspan=\"2\">
                <fieldset form=\"request{$this->ticket_index}\">
                  <legend><label for=\"notes{$this->ticket_index}\">Notes:</label></legend>
                  <textarea rows=\"4\" name=\"notes\" id=\"notes{$this->ticket_index}\" class=\"notes\" form=\"request{$this->ticket_index}\">{$this->Notes}</textarea>
	              </fieldset>
              </td>";
      $returnData .= "
      <div id=\"deliveryRequest{$this->ticket_index}\" class=\"removableByEditor\">
        <form id=\"request{$this->ticket_index}\" action=\"{$this->action}\" method=\"post\">
          $indexInput $dispatchedBy $transferredBy $ticketNumberInput $ticketEditor
          <input type=\"hidden\" name=\"runNumber\" value=\"{$this->RunNumber}\" form=\"request{$this->ticket_index}\" />
          <input type=\"hidden\" name=\"contract\" value=\"{$this->Contract}\" form=\"request{$this->ticket_index}\" />
          $ticketEditorValues
          <table class=\"ticketContainer\">
            <tr>
              <td colspan=\"2\">
                <fieldset form=\"request{$this->ticket_index}\" id=\"information{$this->ticket_index}\">
                  <legend>General Information</legend>
                  <table class=\"centerDiv\">
                    <tr class=\"$billingRowClass\">
                      <td>
                        <label for=\"repeatClient{$this->ticket_index}\">Non-Repeat:</label>
                        <input type=\"hidden\" name=\"repeatClient\" value=\"{$this->RepeatClient}\" form=\"request{$this->ticket_index}\" $nonRepeatChecked />
                        $repeatOption
                      </td>
                      <td><td>
                    </tr>
                    <tr>
                      <td>
                        <label class=\"$hideBillToLabel\" for=\"billTo{$this->ticket_index}\">Bill To:</label>
                        <input $billToType name=\"billTo\" id=\"billTo{$this->ticket_index}\" class=\"billTo\" value=\"$billToValue\" title=\"$billToValue\" form=\"request{$this->ticket_index}\" $billToRequired />
                      </td>
                      <td class=\"$billingRowClass\">
                        <label for=\"dispatchedTo{$this->ticket_index}\" $hideDispatch>Dispatch To: </label>
                        <input $dispatchInputType name=\"dispatchedTo\" id=\"dispatchedTo{$this->ticket_index}\" class=\"dispatchedTo\" form=\"request{$this->ticket_index}\" value=\"$dispatchInputValue\" $readonlyDispatch $requiredDispatch />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"charge{$this->ticket_index}\">Delivery Time:</label>
                        <select name=\"charge\" id=\"charge{$this->ticket_index}\" class=\"charge\" form=\"request{$this->ticket_index}\">
                          {$this->createChargeSelectOptions()}
                        </select>
                      </td>
                      <td>
                        <label class=\"rtMarker\" for=\"d2SigReq{$this->ticket_index}\" style=\"display:$rtDisplay;\">Return Signature:</label>
                        <input type=\"hidden\" name=\"d2SigReq\" id=\"d2SigReqMarker{$this->ticket_index}\" class=\"d2SigReqMarker\" value=\"0\" form=\"request{$this->ticket_index}\" />
                        <input type=\"checkbox\" class=\"rtMarker\" name=\"d2SigReq\" id=\"d2SigReq{$this->ticket_index}\" class=\"d2SigReq\" style=\"display:$rtDisplay;\" value=\"1\" $d2SigChecked form=\"request{$this->ticket_index}\" />
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"emailAddress{$this->ticket_index}\">Email Address:</label>
                        <input type=\"email\" name=\"emailAddress\" id=\"emailAddress{$this->ticket_index}\" class=\"emailAddress\" form=\"request{$this->ticket_index}\" value=\"{$this->EmailAddress}\" multiple />
                      </td>
                      <td>
                        <label for=\"emailConfirm{$this->ticket_index}\">Email <span class=\"mobileHide\">Confirmation</span>:</label>
                        <select form=\"request{$this->ticket_index}\" name=\"emailConfirm\" id=\"emailConfirm{$this->ticket_index}\" class=\"emailConfirm\">
                          <option value=\"0\" $emailConfirm0>None</option>
                          <option value=\"1\" $emailConfirm1>Picked Up</option>
                          <option value=\"2\" $emailConfirm2>Delivered</option>
                          <option value=\"3\" $emailConfirm3>Picked Up & Delivered</option>
                          <option class=\"rtMarker\" value=\"4\" $emailConfirm4 style=\"display:$rtDisplay;\">Returned</option>
                          <option class=\"rtMarker\" value=\"5\" $emailConfirm5 style=\"display:$rtDisplay;\">Picked Up &amp; Returned</option>
                          <option class=\"rtMarker\" value=\"6\" $emailConfirm6 style=\"display:$rtDisplay;\">Delivered &amp; Returned</option>
                          <option class=\"rtMarker\" value=\"7\" $emailConfirm7 style=\"display:$rtDisplay;\">All</option>
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
                    <tr>
                      <td>
                        <label for=\"receivedReady{$this->ticket_index}\">Ready: <input type=\"checkbox\" name=\"receivedReady\" id=\"receivedReady{$this->ticket_index}\" class=\"receivedReady\" value=\"1\" form=\"request{$this->ticket_index}\" $readyChecked /></label>
                        <p class=\"readyNote\" style=\"display:$readyNote\">Now</p>
                        <input type=\"datetime-local\" name=\"readyDate\" class=\"readyDate\" style=\"display:$readyDateDisplay\" value=\"$readyDate\" form=\"request{$this->ticket_index}\" />
                      </td>
                      <td></td>
                    </tr>
                  </table>
                </fieldset>
              </td>
            </tr>
            $timing
            <tr class=\"deliveryInfo\">
              <td>
                <fieldset form=\"request{$this->ticket_index}\" id=\"pickupField{$this->ticket_index}\">
                  <legend>Pick Up</legend>
                  <table class=\"centerDiv\">
                    <thead $hideFromDriver>
                      <tr>
                        <td><label for=\"fromMe{$this->ticket_index}\">From Me:</label>
                          <input type=\"hidden\" name=\"fromMe\" value=\"0\" form=\"request{$this->ticket_index}\" />
                          <input type=\"checkbox\" id=\"fromMe{$this->ticket_index}\" class=\"me\" name=\"fromMe\" value=\"1\" $fromMeCheked form=\"request{$this->ticket_index}\" />
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
                          <input type=\"checkbox\" name=\"pSigReq\" id=\"pSigReq{$this->ticket_index}\" value=\"1\" $pSigChecked form=\"request{$this->ticket_index}\" />
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
                    <thead $hideFromDriver>
                      <tr>
                        <td><label for=\"toMe{$this->ticket_index}\">To Me:</label>
                          <input type=\"hidden\" name=\"toMe\" value=\"0\" form=\"request{$this->ticket_index}\" />
                          <input type=\"checkbox\" id=\"toMe{$this->ticket_index}\" class=\"me\" name=\"toMe\" value=\"1\" $toMeChecked form=\"request{$this->ticket_index}\" />
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
                          <input type=\"checkbox\" name=\"dSigReq\" id=\"dSigReq{$this->ticket_index}\" value=\"1\" $dSigChecked form=\"request{$this->ticket_index}\" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
	              </fieldset>
              </td>
            </tr>
            <tr class=\"iceAndNotes\">
            $displayDryIce
            </tr>
            <tr>
              <td colspan=\"2\">
                <input type=\"hidden\" name=\"locationList\" value=\"{$this->locationList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"driverList\" value=\"{$this->driverList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"clientList\" value=\"{$this->clientList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"tClientList\" value=\"{$this->tClientList}\" form=\"request{$this->ticket_index}\" />
                <input type=\"hidden\" name=\"edit\" value=\"0\" form=\"request{$this->ticket_index}\" />
	              <button class=\"submitForm floatLeft\" type=\"submit\" form=\"request{$this->ticket_index}\">Submit</button> $cancelTicketEditor</td>
            </tr>
          </table>
          <p class=\"ticketError\"></p>";
    if ($this->ticketEditor === false) {
      $returnData .= "
          <p class=\"sigNote $sigNoteClass\">Unless a specific request to the contrary is made all deliveries will be completed to the best of our ability even if a signature request is declined.</p>
          <p class=\"emailNote $emailNoteClass\">Please add noreply@rjdeliveryomaha.com to your contacts. This will prevent notifications from being marked as spam.</p>";
    }
    $returnData .= '
          <p class="dedicatedNote">To indicate a round trip request return signature.</p>
	      </form>
      </div>';
    if ($this->edit === null && $this->ticketEditor === false) $returnData .= "
      <div class=\"subContainer\">
        <div id=\"terms\">
          <p id=\"switchTerms\" class=\"error center\">*TERMS</p>
          <div id=\"deliveryTerms\" class=\"hide\">{$this->options['deliveryTerms']}</div>
        </div>
        <div class=\"mapContainer\" id=\"map\"></div>
      </div>";
      return $returnData;
    }

    public function runPriceForm()
    {
      $returnData = '';
      $this->formType = 'Entry';
      if ($this->organizationFlag === true) {
        self::buildLocationList();
        $returnData .= self::buildDatalists();
      }
      $displayDryIce = ($this->options['displayDryIce'] === true) ? '' : 'style="display:none;';
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
                          <input type=\"hidden\" name=\"pCountry\" id=\"pCountryMarkerCalc\" value=\"{$this->shippingCountry}\" form=\"priceCalc\" />
                          <input list=\"countries\" name=\"pCountry\" class=\"pCountry\" id=\"pCountryCalc\" value=\"{$this->countryFromAbbr($this->pCountry)}\" {$this->countryInput} form=\"priceCalc\" {$this->requireCountry} />
                        </td>
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
                          <input type=\"hidden\" name=\"dCountry\" id=\"dCountryMarkerCalc\" value=\"{$this->shippingCountry}\" form=\"priceCalc\" />
                          <input list=\"countries\" name=\"dCountry\" class=\"dCountry\" id=\"dCountryCalc\" value=\"{$this->countryFromAbbr($this->dCountry)}\" {$this->countryInput} form=\"priceCalc\" {$this->requireCountry} />
                        </td>
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
                <span $displayDryIce class=\"floatRight\">
                  <label for=\"CalcDryIce\">Dry Ice:</label>
                  <input name=\"dryIce\" id=\"CalcDryIce\" type=\"checkbox\" class=\"dryIce\" value=\"1\" form=\"priceCalc\" />
                  <input type=\"number\" class=\"diWeight diRow\" name=\"diWeight\" id=\"CalcWeight\" value=\"0\" min=\"0\" step=\"{$this->dryIceStep}\" title=\"Increments of 5\" form=\"priceCalc\" disabled />{$this->weightMarker}
                </span>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\">
                <button type=\"submit\" class=\"submitPriceQuery floatLeft\" form=\"priceCalc\">Enter Run</button>
                <button type=\"reset\" class=\"clear floatRight\" form=\"priceCalc\">Clear Form</button>
              </td>
            </tr>
            <tr>
              <td colspan=\"2\" class=\"ticketError center\"></td>
            </tr>
          </tfoot>
        </table>
      <div id=\"priceResult\">
        <p class=\"hide\">Range: <span id=\"rangeResult\"></span></p>
        <p $displayDryIce>Dry Ice: <span id=\"diWeightResult\"></span><span style=\"display:none;\" class=\"weightMarker\">{$this->weightMarker}</span></p>
        <p>Run Price:<span style=\"display:none;\" class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span><span id=\"runPriceResult\"></span></p>
        <p $displayDryIce>Dry Ice Price: <span style=\"display:none;\" class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span><span style=\"min-width:3em;\" id=\"diPriceResult\">&nbsp;</span></p>
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
      if ($this->organizationFlag === true) {
        $returnData .= self::buildDatalists();
      }
      return $returnData;
    }

    public function calculateRunPrice()
    {
      $this->Contract = 0;
      if ($this->ulevel === 1 || $this->ulevel === 2) {
        $this->BillTo = $this->ClientID;
      } else {
        $this->BillTo = null;
      }
      if (!self::solveTicketPrice()) {
        return $this->error;
      }
      $this->pCountry = self::countryFromAbbr($this->pCountry);
      $this->dCountry = self::countryFromAbbr($this->dCountry);
      if ($this->generalDiscount !== null && $this->generalDiscount !== '') {
        $this->RunPrice *= ($this->generalDiscount / 100);
        $this->TicketPrice = $this->RunPrice + $this->diPrice;
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

    public function fetchTodaysTickets()
    {
      $returnData = '';
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = 'tickets';
      $this->queryData['queryParams']['filter'] = [
        ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>(int)$_SESSION['ClientID']],
        ['Resource'=>'ReceivedDate', 'Filter'=>'sw', 'Value'=>$this->today->format('Y-m-d')]
      ];
      if (!$this->query = self::createQuery($this->queryData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
      }
      if (empty($this->result)) {
        return false;
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

    private function confirmRequest()
    {
      self::solveTicketPrice();
      $editForm = "
      <form id=\"editForm{$this->ticket_index}\" method=\"post\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\">";
      // Set the form name to editForm
      $this->formName = 'editForm' . $this->ticket_index;
      $editForm .= self::hiddenInputs();
      $editForm .= "
          <input type=\"hidden\" name=\"edit\" form=\"editForm{$this->ticket_index}\" value=\"1\" />
        </form>";
      $editButton = "
          <button type=\"submit\" class=\"editForm\" form=\"editForm{$this->ticket_index}\">Edit</button>";

      // Generate a ticket number if one wasn't provided
      if ($this->TicketNumber == null) {
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

      $sigReqTemp = [];
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

      if ($this->ReceivedReady == 1 || strlen($this->ReadyDate) == 0) {
        $ready = 'Now';
        $this->ReceivedReady = 1;
      } else {
        try {
          $temp = new \dateTime($this->ReadyDate);
          $ready = $temp->format('d M Y \a\t g:i a');
        } catch(\Exception $e) {
          $ready ="Now*<span class=\"hide\">{$e->getMessage()}</span>";
          $this->ReceivedReady = 1;
        }
      }
      // Generate the hidden form
      // Add the values that we just solved for
      $newTicketInput = ($this->ticket_index === null) ?
      "<input type=\"hidden\" name=\"newTicket\" value=\"1\" form=\"submitTicket{$this->ticket_index}\" />" :
      "<input type=\"hidden\" name=\"ticket_index\" value=\"{$this->ticket_index}\" form=\"submitTicket{$this->ticket_index}\" />";
      $submitForm = "
            <form id=\"submitTicket{$this->ticket_index}\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
              $newTicketInput
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
              <input type=\"hidden\" name=\"dispatchedTo\" value=\"{$this->DispatchedTo}\" form=\"submitTicket{$this->ticket_index}\" />";
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
      $div_marker = ($this->ticketEditor === true) ? 'class="editorConfirmation"' : 'id="deliveryConfirmation"';
      $output = "
          <div $div_marker>";
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
      // Generate the confirmation display
      $jsVar = '
      <input type="hidden" name="coords1" class="coords1" value="' . htmlentities(json_encode($this->loc1)) . '" form="coordinates" />
      <input type="hidden" name="address1" class="address1" value="' . htmlentities($this->pAddress1 . ' ' . $this->pAddress2, ENT_QUOTES) . '" form="coordinates" />
      <input type="hidden" name="coords2" class="coords2" value="' . htmlentities(json_encode($this->loc2)) . '" form="coordinates" />
      <input type="hidden" name="address2" class="address2" value="' . htmlentities($this->dAddress1 . ' ' . $this->dAddress2, ENT_QUOTES) . '" form="coordinates" />
      <input type="hidden" name="center" class="center" value="' . htmlentities(json_encode($this->center)) . '" form="coordinates" />';
      $displayDryIce = ($this->options['displayDryIce'] === true) ? '' : 'hide';
      $client = false;
      foreach ($this->members as $member) {
        if (
          $member->getProperty('ClientID') == $this->BillTo &&
          (int)$member->getProperty('RepeatClient') == $this->RepeatClient
        ) $client = $member;
      }
      if (!$client) throw new \Exception('Invalid Client');
      $billToDisplay = $client->getProperty('ClientName');
      $dep = $client->getProperty('Department');
      if ($dep) $billToDisplay .= ", $dep";
      $hideVAT = ($this->config['ApplyVAT'] === true) ? '' : 'hide';
      $vatDisplay = '<span class="bold">VAT:</span>D:';
      $VATamount = 0;
      if (0 < $this->VATtype && $this->VATtype < 5) {
        $vatDisplay .= ($this->VATtype === 1 || $this->VATtype === 3) ? ' Standard' : ' Reduced';
        $VATamount += ($this->RunPrice * (1 + ($this->VATrate / 100))) - $this->RunPrice;
      }
      if ($this->VATtype === 5) {
        $vatDisplay .= ' Zero-Rated';
      }
      if ($this->VATtype === 6) {
        $vatDisplay .= ' Exempt';
      }
      if ($this->config['ApplyVAT'] === false || self::test_bool($this->VATable) === false) {
        $vatDisplay = ' Not VAT-able';
      }
      if (self::test_bool($this->dryIce) === true) {
        $begin = self::before(' ', $vatDisplay);
        $end = self::after(' ', $vatDisplay);
        $vatDisplay = "$begin D: $end ";
        if (0 < $this->VATtypeIce && $this->VATtypeIce < 5) {
          $vatDisplay .= ($this->VATtypeIce === 1 || $this->VATtypeIce === 3) ? 'I: Standard' : 'I: Reduced';
          $VATamount += ($this->diPrice * (1 + ($this->VATrateIce / 100))) - $this->diPrice;
        }
        if ($this->VATtypeIce === 5) {
          $vatDisplay .= 'I: Zero-Rated';
        }
        if ($this->VATtypeIce === 6) {
          $vatDisplay .= 'I: Exempt';
        }
        if ($this->VATableIce === 0) {
          $vatDisplay .= 'I: Not VAT-able';
        }
      }
      $output .= "
        <table class=\"ticketContainer\">
          <thead>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Bill To: </span> $billToDisplay</td>
            </tr>
            </tr>
            <tr class=\"$displayDryIce\">
              <td colspan=\"2\"><span class=\"bold\">Dry Ice: </span>{$this->number_format_drop_zero_decimals($this->diWeight, 3)}{$this->weightMarker} $iceChargeDisplay</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Requested By: </span>{$this->RequestedBy}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Email Address: </span>{$this->EmailAddress}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Email Confirmation: </span>$emailAnswer</td>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Signature Request: </span>$sigReq</td>
            </tr>
            <tr>
              <td colspan=\"2\">$ticketPriceDisplay $totalPriceDisplay</td>
            </tr>
            <tr class=\"$hideVAT\">
              <td>$vatDisplay</td>
              <td>
                <span class=\"bold\">Ticket VAT:</span>
                <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($VATamount, 2)}
              </td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Ready: </span> $ready</td>
            </tr>
            <tr>
              <td colspan=\"2\"><span class=\"bold\">Notes: </span>{$this->Notes}</td>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <td>$submitTicketButton</td>
              <td>$editButton</td>
            </tr>
            <tr>
              <td colspan=\"2\" class=\"ticketError\"></td>
            </tr>
          </tfoot>
          <tbody>
            <tr>
              <td colspan=\"2\">$chargeAnswer</td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
            <tr class=\"confirmAddress\">
              <th>Pick Up</th>
              <th>Deliver</tr>
            </tr>
            <tr class=\"confirmAddress\">
              <td>{$this->pClient}</td>
              <td>{$this->dClient}</td>
            </tr>
            <tr class=\"confirmAddress\">
              <td>{$this->pDepartment}</td>
              <td>{$this->dDepartment}</td>
            </tr>
            <tr class=\"confirmAddress\">
              <td>{$this->pAddress1}</td>
              <td>{$this->dAddress1}</td>
            </tr class=\"confirmAddress\">
            <tr class=\"confirmAddress\">
              <td>{$this->pAddress2}</td>
              <td>{$this->dAddress2}</td>
            </tr class=\"confirmAddress\">
            <tr class=\"{$this->countryClass}\">
              <td>{$this->pCountry}</td>
              <td>{$this->dCountry}</td>
            </tr>
            <tr class=\"confirmAddress\">
              <td><span class=\"bold\">Contact</span>: {$this->pContact}</td>
              <td><span class=\"bold\">Contact</span>: {$this->dContact}</td>
            </tr>
            <tr class=\"confirmAddress\">
              <td><span class=\"bold\">Telephone</span>: {$this->pTelephone}</td>
              <td><span class=\"bold\">Telephone</span>: {$this->dTelephone}</td>
            </tr>
            <tr>
              <td colspan=\"2\"><hr></td>
            </tr>
            <tr>
              <td colspan=\"2\">{$this->pRangeError}  {$this->dRangeError}</td>
            </tr>
          </tbody>
        </table>
        $jsVar
      </div>";
      return $output;
    }

    private function processTicket()
    {
      foreach ($this as $key => $value) {
        if (substr($key,1) === 'Country') {
          if (strlen($value) > 2) {
            $this->$key = self::countryFromAbbr($value);
          } else {
            $this->$key = $value;
          }
        }
      }
      if ($this->updateTicket === true) {
        // Do /not/ display the ticket if new transfer is processed
        $regen = true;
        $payload = [];
        foreach ($this as $key => $value) {
          if (in_array($key, $this->updateTicketDatabaseKeys) && in_array(lcfirst($key), $this->postKeys)) {
            if ($key === 'Transfers' && $value != false) {
              $tempArray = json_decode(html_entity_decode($value));
              $target = [];
              for ($i=0;$i<count($tempArray); $i++) {
                $newObj = new \stdClass();
                foreach ($tempArray[$i] as $k => $v) {
                  if ($v == null) {
                    $newVal = time();
                  } else {
                    $newVal = $v;
                  }
                  $newObj->$k = $newVal;
                }
                $target[] = $newObj;
              }
              $payload[$key] = $target;
            } else {
              if (in_array($key, $this->nullable) && !$value) {
                $payload[$key] = null;
              } else {
                $payload[$key] = self::decode($value);
              }
            }
          }
        }
        $ticketUpdateData['endPoint'] = 'tickets';
        $ticketUpdateData['method'] = 'PUT';
        $ticketUpdateData['queryParams'] = [];
        $ticketUpdateData['payload'] = $payload;
        $ticketUpdateData['primaryKey'] = $this->ticket_index;
        if (!$ticketUpdate = self::createQuery($ticketUpdateData)) {
          $temp = $this->error;
          $this->error = ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          throw new \Exception($this->error);
        }
        $ticketUpdateResult = self::callQuery($ticketUpdate);
        if ($ticketUpdateResult === false) {
          $temp = $this->error;
          $this->error = ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          throw new \Exception($this->error);
        }
        if ($regen === true) {
          $this->ticketEditor = true;
          return self::regenTicket();
        } else {
          return 'remove';
        }
      }
      if ($this->ticket_index === null) {
        if (!self::testTicketNumber()) {
          throw new \Exception($this->error);
        }
      }
      try {
        $this->now = new \dateTime('NOW', $this->timezone);
      } catch(\Exception $e) {
        $this->error = ' Received Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      $this->ReceivedDate = ($this->ReceivedDate === null || $this->ReceivedDate === '') ?
      $this->now->format('Y-m-d H:i:s') : $this->ReceivedDate;

      $this->ReadyDate = ($this->ReadyDate === null || $this->ReadyDate === '') ?
      $this->now->format('Y-m-d H:i:s') : $this->ReadyDate;

      if ($this->DispatchedTo != 0) {
        $this->DispatchTimeStamp = ($this->DispatchTimeStamp === null || $this->DispatchTimeStamp === '') ?
        $this->ReceivedDate : $this->DispatchTimeStamp;

        $micro_date = microtime();
        $date_array = explode(" ",$micro_date);
        $this->DispatchMicroTime = ($this->DispatchMicroTime === null || $this->DispatchMicroTime === '') ?
        substr($date_array[0], 1, 7) : $this->DispatchMicroTime;

      } else {
        $this->DispatchTimeStamp = $this->DispatchMicroTime = null;
      }
      // Create a new query object to post the new ticket
      $postTicketData = [];
      foreach ($this as $key => $value) {
        if (in_array($key, $this->newTicketDatabaseKeys) && $value !== null) {
          $postTicketData['payload'][$key] = self::decode($value);
        }
      }
      $postTicketData['endPoint'] = 'tickets';
      $postTicketData['method'] = 'POST';
      $postTicketData['queryParams'] = [];
      if (!$postTicket = self::createQuery($postTicketData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      $postTicketResult = self::callQuery($postTicket);
      if ($postTicketResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      return "
        <div id=\"deliveryRequestComplete\">
          <h1>request submitted</h1>
          <p class=\"center\">Your request has been received.<br>The ticket number for this delivery is {$this->TicketNumber}.</p>
        </div>";
    }

    public function processRouteTicket()
    {
      // multiTicket data has come from the database and doesn't require sanitizing
      $tempTickets = $crun_index_list = [];
      // test each tickets number for uniqueness and solve for price
      $this->mapAvailable = false;
      $this->processingRoute = true;
      for ($i = 0; $i < count($this->multiTicket); $i++) {
        foreach ($this->multiTicket[$i] as $key => $value) {
          if (property_exists($this, $key)) {
            $this->$key = $value;
          }
        }
        if (!self::testTicketNumber()) {
          // failure logged in function
          return false;
        }
        if (!self::solveTicketPrice()) {
          // failure logged in function
          return false;
        }
        $newObj = new \stdClass();
        foreach ($this as $key => $value) {
          if ($key === 'crun_index') $crun_index_list[] = $value;
          if (($key === 'pCountry' || $key === 'dCountry') && strlen($value) != 2) {
            $temp = self::countryFromAbbr($value);
            $value = $temp;
          }
          if (in_array($key, $this->newTicketDatabaseKeys) && $value !== null) {
            $newObj->$key = self::decode($value);
          }
        }
        $tempTickets[] = $newObj;
      }
      $this->multiTicket = $tempTickets;
      // Create a new query object to post the new tickets
      $postTicketData['endPoint'] = 'tickets';
      $postTicketData['method'] = 'POST';
      $postTicketData['queryParams'] = [];
      $postTicketData['payload'] = $this->multiTicket;
      if (!$postTicket = self::createQuery($postTicketData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        echo $this->error;
        return false;
      }
      $postTest = self::callQuery($postTicket);
      if ($postTest === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        echo $this->error;
        return false;
      }
      // set $this->now for last completed value.
      try {
        $this->now = new \dateTime('now', $this->timezone);
      } catch(\Exception $e) {
        $this->error = ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        echo $this->error;
        return false;
      }
      $updateLastCompletedDateData['endPoint'] = 'contract_runs';
      $updateLastCompletedDateData['method'] = 'PUT';
      $updateLastCompletedDateData['queryParams'] = [];
      $updateLastCompletedDateData['primaryKey'] = implode(',', $crun_index_list);
      $updateLastCompletedDateData['payload'] = [];
      for ($i = 0; $i < count($crun_index_list); $i++) {
        $newObj = new \stdClass();
        $newObj->LastCompleted = $this->now->format('Y-m-d');
        $updateLastCompletedDateData['payload'][] = $newObj;
      }
      if (!$updateLastCompletedDate = self::createQuery($updateLastCompletedDateData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        echo $this->error;
        return false;
      }
      $updateResult = self::callQuery($updateLastCompletedDate);
      if ($updateResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        echo $this->error;
        return false;
      }
      return true;
    }

    public function processReturnTicket()
    {
      $this->mapAvailable = false;
      self::solveTicketPrice();
      try {
        self::processTicket();
      } catch (\Exception $e) {
        throw $e;
      }
    }

    public function updateTicketProperty()
    {
      if ($this->multiTicket !== null) {
        if (!is_array($this->multiTicket)) $this->multiTicket = json_decode($this->multiTicket, true);
        $tempIndex = [];
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            if ($key === 'ticket_index') $tempIndex[] = (int)$value;
          }
        }
        $this->ticket_index = implode(',', $tempIndex);
      }
      $ticketUpdateData['endPoint'] = 'tickets';
      $ticketUpdateData['method']= 'PUT';
      $ticketUpdateData['queryParams'] = [];
      $ticketUpdateData['primaryKey'] = $this->ticket_index;
      $ticketUpdateData['payload'] = [];
      if ($this->multiTicket === null) {
        foreach($this as $key => $value) {
          if (
            in_array($key, $this->updateTicketDatabaseKeys) &&
            in_array(lcfirst($key), $this->postKeys)
          ) $ticketUpdateData['payload'][$key] = $value;
        }
      } else {
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          $tempObj = new \stdClass();
          foreach ($this->multiTicket[$i] as $key => $value) {
            if (in_array($key, $this->updateTicketDatabaseKeys)) $tempObj->$key = $value;
          }
          $ticketUpdateData['payload'][] = $tempObj;
        }
      }
      if (!$ticketUpdate = self::createQuery($ticketUpdateData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
        return false;
      }
      $updateResult = self::callQuery($ticketUpdate);
      if ($updateResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
        return false;
      }
      $marker = ($this->multiTicket === null) ? $this->TicketNumber : 'group';
      return "<p class=\"center result\">Ticket $marker updated.</p>";
    }

    public function stepTicket()
    {
      if ($this->multiTicket !== null) {
        if (!is_array($this->multiTicket))
			    $this->multiTicket = json_decode($this->multiTicket, true);
		    if (json_last_error() !== JSON_ERROR_NONE) {
          $this->error = __function__ . ' Error: ' . json_last_error_msg() . ' Line: ' . __line__;
          if ($this->enableLogging !== false) self::writeLoop();
          return $this->error;
        }
        $tempIndex = [];
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            if ($key === 'ticket_index') $tempIndex[] = (int)$value;
          }
        }
        $this->ticket_index = implode(',', $tempIndex);
      }
      try {
        $this->now = new \dateTime('now', $this->timezone);
      } catch(\Exception $e) {
        $this->error = ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
        return false;
      }
      $ticketUpdateData['endPoint'] = 'tickets';
      $ticketUpdateData['method']= 'PUT';
      $ticketUpdateData['queryParams'] = [];
      $ticketUpdateData['primaryKey'] = $this->ticket_index;
      if ($this->multiTicket === null) {
        switch($this->step){
          case 'pickedUp':
            $ticketUpdateData['payload']['Notes'] = $this->Notes;
            $ticketUpdateData['payload']['pTimeStamp'] = $this->now->format('Y-m-d H:i:s');
            if ($this->printName !== null && $this->printName !== '') {
              $ticketUpdateData['payload']['pSigPrint'] =  $this->printName;
              $ticketUpdateData['payload']['pSigReq'] = 1;
            }
            if ($this->sigImage !== null && $this->sigImage !== '') {
              $dataPieces = explode(',', $this->sigImage);
              $ticketUpdateData['payload']['pSigType'] = self::between('/',';',$dataPieces[0]);
              $ticketUpdateData['payload']['pSig'] = base64_encode($dataPieces[1]);
              $ticketUpdateData['payload']['pSigReq'] = 1;
            }
            if ($this->latitude !== null && $this->longitude !== null) {
              $ticketUpdateData['payload']['pLat'] = $this->latitude;
              $ticketUpdateData['payload']['pLng'] = $this->longitude;
            }
            $this->stepMarker = 'Picked Up';
            break;
          case 'delivered':
            $ticketUpdateData['payload']['Notes'] = $this->Notes;
            $ticketUpdateData['payload']['dTimeStamp'] = $this->now->format('Y-m-d H:i:s');
            if ($this->Charge === 6 && $this->noReturn) {
              $ticketUpdateData['payload']['Charge'] = 5;
              $ticketUpdateData['payload']['RunPrice'] = $this->TicketBase;
              $ticketUpdateData['payload']['TicketPrice'] = $this->TicketBase + $this->diPrice;
            }
            if ($this->printName !== null && $this->printName !== '') {
              $ticketUpdateData['payload']['dSigPrint'] = $this->printName;
              $ticketUpdateData['payload']['dSigReq'] = 1;
            }
            if ($this->sigImage != null && $this->sigImage !== '') {
              $dataPieces = explode(',', $this->sigImage);
              $ticketUpdateData['payload']['dSigType'] = self::between('/',';',$dataPieces[0]);
              $ticketUpdateData['payload']['dSig'] = base64_encode($dataPieces[1]);
              $ticketUpdateData['payload']['dSigReq'] = 1;
            }
            if ($this->latitude !== null && $this->longitude !== null) {
              $ticketUpdateData['payload']['dLat'] = $this->latitude;
              $ticketUpdateData['payload']['dLng'] = $this->longitude;
            }
            $this->stepMarker = 'Delivered';
            break;
          case 'returned':
            $ticketUpdateData['payload']['d2TimeStamp'] = $this->now->format('Y-m-d H:i:s');
            if ($this->printName !== null && $this->printName !== '') {
              $ticketUpdateData['payload']['d2SigPrint'] = $this->printName;
              $ticketUpdateData['payload']['d2SigReq'] = 1;
            }
            if ($this->sigImage != null && $this->sigImage !== "") {
              $dataPieces = explode(',', $this->sigImage);
              $ticketUpdateData['payload']['d2SigType'] = between('/',';',$dataPieces[0]);
              $ticketUpdateData['payload']['d2Sig'] = base64_encode($dataPieces[1]);
              $ticketUpdateData['payload']['d2SigReq'] = 1;
            }
            if ($this->latitude !== null && $this->longitude !== null) {
              $ticketUpdateData['payload']['d2Lat'] = $this->latitude;
              $ticketUpdateData['payload']['d2Lng'] = $this->longitude;
            }
            $this->stepMarker = 'Returned';
            break;
          case 'dispatched':
            $this->DispatchTimeStamp = $this->now->format('Y-m-d H:i:s');
            $micro_date = microtime();
            $date_array = explode(" ",$micro_date);
            $this->DispatchMicroTime = substr($date_array[0], 1, 7);
            $ticketUpdateData['payload'] = [
              'DispatchTimeStamp'=>$this->DispatchTimeStamp,
              'DispatchMicroTime'=>$this->DispatchMicroTime,
              'DispatchedTo'=>$this->DispatchedTo,
              'DispatchedBy'=>$this->DispatchedBy
            ];
            $this->stepMarker = 'Dispatched';
            break;
          default:
            $this->error = ' Error: Unknown Action Line ' . __line__;
            if ($this->enableLogging !== false) self::writeLoop();
            return $this->error;
        }
      } else {
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          $tempObj = new \stdClass();
          switch ($this->multiTicket[$i]['step']) {
            case 'pickedUp':
              $tempObj->Notes = $this->multiTicket[$i]['notes'];
              $tempObj->pTimeStamp = $this->now->format('Y-m-d H:i:s');
              if ($this->printName !== null && $this->printName !== '') {
                $tempObj->pSigPrint = $this->printName;
                $tempObj->pSigReq = 1;
              }
              if ($this->sigImage !== null && $this->sigImage !== '') {
                $dataPieces = explode(',', $this->sigImage);
                $tempObj->pSigType = self::between('/',';',$dataPieces[0]);
                $tempObj->pSig = base64_encode($dataPieces[1]);
                $tempObj->pSigReq = 1;
              }
              if ($this->latitude !== null && $this->longitude !== null) {
                $tempObj->pLat = $this->latitude;
                $tempObj->pLng = $this->longitude;
              }
              if ($this->printName !== null && $this->printName !== '') $tempObj->pSigReq = 1;
              $ticketUpdateData['payload'][] = $tempObj;
              break;
            case 'delivered':
              $tempObj->Notes = $this->multiTicket[$i]['notes'];
              $tempObj->dTimeStamp = $this->now->format('Y-m-d H:i:s');
              if ($this->multiTicket[$i]['Charge'] == 6 && $this->multiTicket[$i]['noReturn']) {
                $tempObj->Charge = 5;
                $tempObj->RunPrice = $this->multiTicket[$i]['TicketBase'];
                $tempObj->TicketPrice = $this->multiTicket[$i]['TicketBase'] + $this->multiTicket[$i]['diPrice'];
              }
              if ($this->printName !== null && $this->printName !== '') {
                $tempObj->dSigPrint = $this->printName;
                $tempObj->dSigReq = 1;
              }
              if ($this->sigImage !== null && $this->sigImage !== '') {
                $dataPieces = explode(',', $this->sigImage);
                $tempObj->dSigType = self::between('/',';',$dataPieces[0]);
                $tempObj->dSig = base64_encode($dataPieces[1]);
                $tempObj->dSigReq = 1;
              }
              if ($this->latitude !== null && $this->longitude !== null) {
                $tempObj->dLat = $this->latitude;
                $tempObj->dLng = $this->longitude;
              }
              if ($this->printName !== null && $this->printName !== '') $tempObj->dSigReq = 1;
              $ticketUpdateData['payload'][] = $tempObj;
              break;
            case 'returned':
              $tempObj->Notes = $this->multiTicket[$i]['notes'];
              $tempObj->d2TimeStamp = $this->now->format('Y-m-d H:i:s');
              if ($this->printName !== null && $this->printName !== '') {
                $tempObj->d2SigPrint = $this->printName;
                $tempObj->d2SigReq = 1;
              }
              if ($this->sigImage !== null && $this->sigImage !== '') {
                $dataPieces = explode(',', $this->sigImage);
                $tempObj->d2SigType = self::between('/',';',$dataPieces[0]);
                $tempObj->d2Sig = base64_encode($dataPieces[1]);
                $tempObj->d2SigReq = 1;
              }
              if ($this->latitude !== null && $this->longitude !== null) {
                $tempObj->d2Lat = $this->latitude;
                $tempObj->d2Lng = $this->longitude;
              }
              if ($this->printName !== null && $this->printName !== '') $tempObj->d2SigReq = 1;
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
              $this->error = ' Error: Unknown Action Line ' . __line__;
              if ($this->enableLogging !== false) self::writeLoop();
              return $this->error;
          }
        }
      }
      if (!$ticketUpdate = self::createQuery($ticketUpdateData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
        return false;
      }
      $updateResult = self::callQuery($ticketUpdate);
      if ($updateResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
        return false;
      }
      if ($this->multiTicket === null) {
        $marker = "{$this->TicketNumber} {$this->stepMarker}";
        $success = true;
        if ($this->Charge === 7) {
          $success = self::solveDedicatedRunPrice();
        }
        if (self::sendEmail() === true) {
          self::processEmail();
        }
        if ($success === false) $marker = "Error: {$this->error}";
      } else {
        $marker = 'group updated';
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            foreach($this as $k => $v) {
              if (strtolower($k) === strtolower($key)) {
                $this->$k = $value;
              }
            }
            if ($this->sendEmail() === true) $this->processEmail();
          }
        }
      }
      return "<p class=\"center result\">Ticket $marker.</p>";
    }

    public function cancelTicket()
    {
      if ($this->multiTicket !== null) {
        if (!is_array($this->multiTicket)) $this->multiTicket = json_decode($this->multiTicket, true);
        $tempIndex = [];
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            if ($key === 'ticket_index') $tempIndex[] = (int)$value;
            if ($key === 'action') $this->action = $value;
          }
        }
        $this->ticket_index = implode(',', $tempIndex);
      }
      $this->processTransfer = ($this->TransferState !== null);
      $ticketUpdateData['endPoint'] = 'tickets';
      $ticketUpdateData['method'] = 'PUT';
      $ticketUpdateData['queryParams'] = [];
      $ticketUpdateData['primaryKey'] = $this->ticket_index;
      switch ($this->action) {
        case 'delete':
          $ticketUpdateData['method'] = 'DELETE';
          $answer = 'deleted';
          break;
        case 'cancel':
          if ($this->multiTicket === null) {
            $ticketUpdateData['payload'] = [
              'Charge' => 0,
              'TicketPrice' => 0,
              'pTimeStamp' => $this->today->format('Y-m-d H:i:s'),
              'Notes' => $this->Notes
            ];
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
          $answer = 'canceled';
          break;
        case 'deadRun':
          if ($this->multiTicket === null) {
            $newPrice = self::number_format_drop_zero_decimals($this->TicketBase * $this->config['DeadRun'], 2);
            $ticketUpdateData['payload'] = [
              'Charge' => 8,
              'pTimeStamp' => $this->today->format('Y-m-d H:i:s'),
              'RunPrice' => $newPrice,
              'TicketPrice' => $newPrice,
              'Notes' => $this->Notes
            ];
            if ($this->latitude !== null) $ticketUpdateData['payload']['pLat'] = $this->latitude;
            if ($this->longitude !== null) $ticketUpdateData['payload']['pLng'] = $this->longitude;
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
              if ($this->latitude !== null) $tempObj->pLat = $this->latitude;
              if ($this->longitude !== null) $tempObj->pLng = $this->longitude;
              $ticketUpdateData['payload'][] = $tempObj;
            }
          }
          $answer = 'marked as Dead Run';
          break;
        case 'declined':
          if ($this->multiTicket === null) {
            switch ($this->Charge) {
              case 1:
              case 2:
              case 3:
              case 4:
              case 5:
              case 6:
              case 7:
                $newPrice = self::number_format_drop_zero_decimals(($this->TicketBase * 2), 2);
                $ticketUpdateData['payload'] = [
                  'dryIce' => 0,
                  'diPrice' => 0,
                  'Charge' => 6,
                  'dTimeStamp' => $this->today->format('Y-m-d H:i:s'),
                  'RunPrice' => $newPrice,
                  'TicketPrice' => $newPrice,
                  'Notes' => "Delivery declined.\n" . $this->Notes
                ];
                if ($this->latitude !== null) $ticketUpdateData['payload']['dLat'] = $this->latitude;
                if ($this->longitude !== null) $ticketUpdateData['payload']['dLng'] = $this->longitude;
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
              if ($this->latitude !== null) $tempObj->dLat = $this->latitude;
              if ($this->longitude !== null) $tempObj->dLng = $this->longitude;
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
              if ($this->multiTicket === null) {
                $ticketUpdateData['payload'] = [
                  'TransferState' => (int)$this->TransferState,
                  'PendingReceiver' => (int)self::after_last(';', $this->pendingReceiver),
                  'Notes' => $this->Notes
                ];
                $this->receiverName = self::test_input(self::before_last(';', $this->pendingReceiver));
              } else {
                $ticketUpdateData['payload'] = [];
                $this->receiverName = self::test_input(self::before_last(';', $this->multiTicket[0]['pendingReceiver']));
                if (!$this->receiverName) {
                  $this->error = '<span class="error">Error</span>: Transfer Receiver Not Defined.';
                  if ($this->enableLogging !== false) self::writeLoop();
                  return $this->error;
                }
                for ($i = 0; $i < count($this->multiTicket); $i++) {
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
              if ($this->multiTicket === null) {
                $ticketUpdateData['payload'] = [ 'TransferState'=>0, 'PendingReceiver'=>0, 'Notes'=>$this->Notes ];
              } else {
                $ticketUpdateData['payload'] = [];
                for ($i = 0; $i < count($this->multiTicket); $i++) {
                  $tempObj = new \stdClass();
                  $tempObj->TransferState = 0;
                  $tempObj->PendingReceiver = 0;
                  $tempObj->Notes = $this->multiTicket[$i]['notes'];
                  $ticketUpdateData['payload'][] = $tempObj;
                }
              }
              $answer = 'transfer canceled';
              break;
            case 3:
              if ($this->multiTicket === null) {
                $ticketUpdateData['payload'] = [
                  'TransferState' => 0,
                  'PendingReceiver' => 0,
                  'Notes' => $this->Notes,
                  'DispatchedTo' => $this->DispatchedTo
                ];
              } else {
                $ticketUpdateData['payload'] = [];
                for ($i = 0; $i < count($this->multiTicket); $i++) {
                  $multiTicketIndex = self::recursive_array_search($this->multiTicket[$i]['ticket_index'], $this->sanitized);
                  $tempObj = new \stdClass();
                  $tempObj->TransferState = 0;
                  $tempObj->PendingReceiver = 0;
                  $tempObj->Notes = $this->multiTicket[$i]['notes'];
                  $ticketUpdateData['payload'][] = $tempObj;
                }
              }
              $answer = 'transfer declined';
              break;
            case 4:
              if ($this->multiTicket === null) {
                $tempTransfer = new \stdClass();
                $tempTransfer->holder = (int)$this->DispatchedTo;
                $tempTransfer->receiver = (int)$this->driverID;
                $tempTransfer->transferredBy = "2.{$this->DispatchedTo}";
                $tempTransfer->timestamp = time();
                if ($this->Transfers) {
                  $testTransfers = json_decode(html_entity_decode($this->Transfers));
                  if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->Transfers = [ $tempTransfer ];
                  } else {
                    $testTransfers[] = $tempTransfer;
                    $this->Transfers = $testTransfers;
                  }
                } else {
                  $this->Transfers = [ $tempTransfer ];
                }
                $ticketUpdateData['payload'] = [
                  'TransferState' => 0,
                  'PendingReceiver' => 0,
                  'Notes' => $this->Notes,
                  'DispatchedTo' => $this->driverID,
                  'Transfers' => $this->Transfers
                ];
              } else {
                $ticketUpdateData['payload'] = [];
                for ($i = 0; $i < count($this->multiTicket); $i++) {
                  $tempObj = new \stdClass();
                  $tempTransfer = new \stdClass();
                  $tempTransfer->holder = (int)$this->multiTicket[$i]['dispatchedTo'];
                  $tempTransfer->receiver = (int)$this->driverID;
                  $tempTransfer->transferredBy = "2.{$this->multiTicket[$i]['dispatchedTo']}";
                  $tempTransfer->timestamp = time();
                  if (!$this->multiTicket[$i]['transfers']) {
                    $tempObj->Transfers = [ $tempTransfer ];
                  } else {
                    $testTransfers = json_decode(html_entity_decode($this->multiTicket[$i]['transfers']));
                    if (json_last_error() !== JSON_ERROR_NONE) {
                      $tempObj->Transfers = [ $tempTransfer ];
                    } else {
                      $testTransfers[] = $tempTransfer;
                      $tempObj->Transfers = $testTransfers;
                    }
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
              $this->error = ' Invalid Transfer State Line ' . __line__ . ': ' . $this->TransferState;
              if ($this->enableLogging !== false) self::writeLoop();
              return $this->error;
          }
          break;
        default:
          $this->error = ' Line ' . __line__ . ": Action {$this->action} not recognized.";
          if ($this->enableLogging !== false) self::writeLoop();
          return $this->error;
      }
      if (!$ticketUpdate = self::createQuery($ticketUpdateData)) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        echo $this->error;
        return false;
      }
      $updateResult = self::callQuery($ticketUpdate);
      if ($updateResult === false) {
        $temp = $this->error;
        $this->error = ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        echo $this->error;
        return false;
      }
      if (!$this->multiTicket) {
        if (self::sendEmail() === true) self::processEmail();
      } else {
        for ($i = 0; $i < count($this->multiTicket); $i++) {
          foreach ($this->multiTicket[$i] as $key => $value) {
            foreach($this as $k => $v) {
              if (strtolower($k) === strtolower($key)) {
                $this->$k = $value;
              }
            }
            if ($this->sendEmail() === true) $this->processEmail();
          }
        }
      }
      $marker = ($this->multiTicket === null) ? $this->TicketNumber : 'group';
      echo "<p class=\"result\">Ticket $marker $answer.</p>";
      return false;
    }
  }
