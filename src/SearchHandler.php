<?php
  namespace RJDeliveryOmaha\CourierInvoice;
  
  use RJDeliveryOmaha\CourierInvoice\CommonFunctions;
  use RJDeliveryOmaha\CourierInvoice\Ticket;
  use RJDeliveryOmaha\CourierInvoice\Invoice;
  /***
  * throws Exception
  *
  ***/
  
  class SearchHandler extends CommonFunctions {
    protected $endPoint;
    protected $display;
    protected $compare;
    protected $compareMembers = FALSE;
    protected $clientID;
    protected $billTo;
    protected $startDate;
    protected $endDate;
    protected $invoiceNumber;
    protected $dateIssued;
    protected $ticketNumber;
    protected $charge;
    protected $type;
    protected $allTime;
    protected $repeatClient = 1;
    private $query;
    private $queryData = [];
    private $result;
    private $today;
    private $yesterday;
    
    private $tickets;
    private $ticketHolder;
    private $invoices;
    private $months;
    private $dataSet;
    
    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw $e;
      }
      try {
        self::setTimezone();
      } catch (Exception $e) {
        $this->error .= "\n" . __function__ . ' Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw $e;
      }
      try {
        $this->today = new \dateTime("NOW", $this->timezone);
      } catch (Exception $e) {
        $this->error .= "\nDate Error Line " . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw $e;
      }
      if ($this->clientID === NULL && $this->billTo !== NULL) {
        $this->clientID = $this->billTo;
      } elseif ($this->clientID !== NULL && $this->billTo === NULL) {
        $this->billTo = $this->clientID;
      }
    }
    
    public function ticketLookup() {
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = 'tickets';
      $this->queryData['noSession'] = TRUE;
      $this->queryData['formKey'] = $this->formKey;
      $this->queryData['queryParams']['resources'] = [ 'Charge', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'd2SigReq' ];
      $this->queryData['queryParams']['filter'] = [ [ 'Resource'=>'TicketNumber', 'Filter'=>'eq', 'Value'=>$this->ticketNumber ] ];
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        echo $this->error;
        return FALSE;
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === FALSE) {
        echo $this->error;
        return FALSE;
      }
      if (empty($this->result)) {
        echo 'No Results Match Query';
        return FALSE;
      }
      $rtFlag = $this->result[0]['Charge'] === 6 || ($this->result[0]['Charge'] === 7 && $this->result[0]['d2SigReq'] === 1);
      
      $cancelledFlag = $this->result[0]['Charge'] === 0;
      
      $data = [];
      foreach ($this->result[0] as $key => $value) {
        if ($key !== 'Charge') {
          if ($value === '' || $value === NULL) {
            $data[$key] = 'Pending';
          } else {
            $data[$key] = date('d M Y \a\t h:i A', strtotime($value));
          }
        }
      }
      if (!$rtFlag) {
        $data['d2TimeStamp'] = 'Not Scheduled';
      }
      if ($cancelledFlag) {
        $data['queryError'] = 'Delivery Cancelled';
      }
      return json_encode($data);
    }
    
    public function handleSearch() {
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = $this->endPoint;
      if (is_array($this->clientID)) {
        $billToValue = implode(',', $this->clientID);
        $billToFilter = 'in';
      } else {
        $billToValue = $this->clientID;
        $billToFilter = 'eq';
      }
      $billToResource = 'BillTo';
      $dateResource = 'ReceivedDate';
      $this->queryData['queryParams']['filter'] = [];
      switch ($this->endPoint) {
        case 'tickets':
          $this->queryData['queryParams']['resources'] = [ 'ticket_index', 'TicketNumber', 'RunNumber', 'BillTo', 'RequestedBy', 'ReceivedDate', 'pClient', 'pDepartment', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'dClient', 'dDepartment', 'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dryIce', 'diWeight', 'diPrice', 'TicketBase', 'Charge', 'Contract', 'Multiplier', 'RunPrice', 'TicketPrice', 'EmailConfirm', 'EmailAddress', 'Notes', 'DispatchTimeStamp', 'DispatchedTo', 'DispatchedBy', 'Transfers', 'TransferState', 'PendingReceiver', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'pTime', 'dTime', 'd2Time', 'pSigReq', 'dSigReq', 'd2SigReq', 'pSigPrint', 'dSigPrint', 'd2SigPrint', 'pSig', 'dSig', 'd2Sig', 'pSigType', 'dSigType', 'd2SigType', 'RepeatClient', 'InvoiceNumber' ];
          if ($this->charge < 10) {
            $this->queryData['queryParams']['filter'][] = [ 'Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>$this->charge ];
          }
          if ($this->type < 2) {
            $this->queryData['queryParams']['filter'][] = [ 'Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->type ];
          }
          $this->queryData['queryParams']['filter'][] = [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->repeatClient ];
        break;
        case 'invoices':
          $billToResource = 'ClientID';
          $dateResource = 'DateIssued';
          $this->queryData['queryParams']['resources'] = [ 'invoice_index', 'InvoiceNumber', 'ClientID', 'InvoiceTotal', 'InvoiceSubTotal', 'BalanceForwarded', 'AmountDue', 'StartDate', 'EndDate', 'DateIssued', 'DatePaid', 'AmountPaid', 'Balance', 'Late30Invoice', 'Late30Value', 'Late60Invoice', 'Late60Value', 'Late90Invoice', 'Late90Value', 'Over90Invoice', 'Over90Value', 'CheckNumber', 'Closed' ];
          $this->queryData['queryParams']['filter'][] = [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->repeatClient ];
        break;
        default: $this->error = 'Invalid End Point'; return FALSE;
      }
      
      $this->queryData['queryParams']['filter'][] = [ 'Resource'=>$billToResource, 'Filter'=>$billToFilter, 'Value'=>$billToValue ];
      
      if ($this->ticketNumber !== NULL) {
        $this->queryData['queryParams']['filter'][] = [ 'Resource'=>'TicketNumber', 'Filter'=>'eq', 'Value'=>$this->ticketNumber ];
      } elseif ($this->invoiceNumber !== NULL) {
        $this->queryData['queryParams']['filter'][] = [ 'Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>$this->invoiceNumber ];
      } elseif ($this->dateIssued !== NULL) {
        $this->queryData['queryParams']['filter'][] = [ 'Resource'=>'DateIssued', 'Filter'=>'sw', 'Value'=>$this->dateIssued ];
      } elseif ($this->startDate !== NULL) {
        $this->endDate = ($this->endDate === NULL) ? $this->startDate : $this->endDate;
        if ($this->startDate > $this->endDate) {
          $temp = $this->startDate;
          $this->startDate = $this->endDate;
          $this->endDate = $temp;
        }
        $tempStart = $tempEnd = '';
        try {
          $tempStart = new \dateTime($this->startDate, $this->timezone);
        } catch (Exception $e) {
          $this->error .= "\n" . __function__ . ' Line ' . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== FALSE) self::writeLoop();
          echo $this->error;
          return FALSE;
        }
        try {
          $tempEnd = new \dateTime($this->endDate, $this->timezone);
        } catch (Exception $e) {
          $this->error .= "\n" . __function__ . ' Line ' . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== FALSE) self::writeLoop();
          echo $this->error;
          return FALSE;
        }
        if ($this->compare === FALSE) {
          // Make sure the query only pulls as many months as $this->allTimeChartLimit
          $diff = $tempStart->diff($tempEnd);
          if ($diff->m > $this->allTimeChartLimit) {
            $marker = ($this->allTimeChartLimit === 1) ? 'month' : 'months';
            $this->error = "Query Range to large. Please limit query range to {$this->allTimeChartLimit} {$marker}.";
            if ($this->enableLogging !== FALSE) self::writeLoop();
            echo $this->error;
            return FALSE;
          }
          $this->queryData['queryParams']['filter'][] = [ 'Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempStart->format('Y-m-d')} 00:00:00,{$tempEnd->format('Y-m-t')} 23:59:59" ];
        } else {
          $this->queryData['queryParams']['filter'] = [ [ [ 'Resource'=>$billToResource, 'Filter'=>$billToFilter, 'Value'=>$billToValue ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->repeatClient ], [ 'Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempStart->format('Y-m-d')} 00:00:00,{$tempStart->format('Y-m-t')} 23:59:59" ] ], [ [ 'Resource'=>$billToResource, 'Filter'=>$billToFilter, 'Value'=>$billToValue ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->repeatClient ], [ 'Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempEnd->format('Y-m-d')} 00:00:00,{$tempEnd->format('Y-m-t')} 23:59:59" ] ] ];
        }
      } elseif ($this->allTime === "0") {
        $temp = clone $this->today;
        $this->yesterday = $temp->modify('-1 day')->format('Y-m-d') . ' 23:59:59';
        $this->queryData['queryParams']['filter'] = array(array('Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>$this->clientID),array('Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->repeatClient),array('Resource'=>'ReceivedDate', 'Filter'=>'gt', 'Value'=>$this->yesterday));
      } elseif ($this->allTime === "Y") {
        switch ($this->display) {
          case "tickets":
            $filterStart = array(array('Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>$this->clientID),array('Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>$this->repeatClient),array('Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>$this->charge), array('Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->type));
            // Remove the type and charge filter if they are set to their respective 'all' values
            foreach($filterStart as $temp) {
              if ($temp['Resource'] === "Charge") {
                if ($temp['Value'] !== 10) {
                  $this->queryData['queryParams']['filter'][] = $temp;
                }
              } elseif ($temp['Resource'] === "Contract") {
                if ($temp['Value'] !== 2) {
                  $this->queryData['queryParams']['filter'][] = $temp;
                }
              } else {
                $this->queryData['queryParams']['filter'][] = $temp;
              }
            }
          break;
          case "chart":
            $this->startDate = clone $this->today;
            $this->endDate = $this->today->format('Y-m-t') . ' 23:59:59';
            $this->queryData['queryParams']['filter'] = array(array('Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>$this->clientID),array('Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>'1'),array('Resource'=>'ReceivedDate', 'Filter'=>'bt', 'Value'=>$this->startDate->modify('- ' . $this->allTimeChartLimit . ' months')->format('Y-m-d') . " 00:00:00," . $this->endDate));
          break;
          default: 
            $this->error = 'Invalid Display Option Line ' . __line__;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
        }
      }
      $this->queryData['formKey'] = $this->formKey;
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        echo $this->error;
        return FALSE;
      }
      try {
        $this->result = self::callQuery($this->query);
      } catch(Exception $e) {
        throw $e;
      }
      if ($this->result === FALSE) {
        echo $this->error;
        return FALSE;
      }
      if (empty($this->result)) {
        echo 'No Results Match Query';
        return FALSE;
      }
      switch ($this->display) {
        case 'tickets':
          $temp = self::createTicket([ "formKey" => $this->formKey ]);
          if ($temp === FALSE) {
            echo $this->error;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
          for ($i = 0; $i < count($this->result); $i++) {
            foreach ($this->result[$i] as $key => $value) {
              $temp->updateProperty($key, $value);
            }
            echo $temp->regenTicket();
          }
        break;
        case 'invoice':
          $data['formKey'] = $this->formKey;
          $data['invoiceQueryResult'] = $this->result;
          $temp = self::createInvoice($data);
          if ($temp === FALSE) {
            echo $this->error;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
          if (!$displayInvoice = $temp->regenInvoice()) {
            $this->error = $temp->getError();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            echo $this->error;
            return FALSE;
          }
          echo $displayInvoice;
        break;
        case 'chart':
          $chartData = [ 'formKey'=>$this->formKey, 'organizationFlag'=>$this->organizationFlag, 'clientID'=>$this->clientID, 'compare'=>$this->compare, 'compareMembers'=>$this->compareMembers, ];
          if ($this->endPoint === 'tickets') {
            if (!self::groupTickets() === FALSE) {
              if ($this->enableLogging !== FALSE) self::writeLoop();
              echo $this->error;
              return FALSE;
            }
            $chartData['dataSet'] = $this->months;
            $chart = self::createTicketChart($chartData);
          } elseif ($this->endPoint === 'invoices') {
            if (self::fetchInvoiceTickets() === FALSE) {
              if ($this->enableLogging !== FALSE) self::writeLoop();
              echo $this->error;
              return FALSE;
            }
            if (!self::groupInvoiceTickets() === FALSE) {
              if ($this->enableLogging !== FALSE) self::writeLoop();
              echo $this->error;
              return FALSE;
            }
            $chartData['dataSet'] = $this->months;
            $chart = self::createInvoiceChart($chartData);
          }
          if ($chart === FALSE) {
            echo $this->error;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
          if (!$displayChart = $chart->displayChart()) {
            $this->error = $chart->getError();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            echo $this->error;
            return FALSE;
          }
          echo $displayChart;
        break;
        default: $this->error = 'Invalid Display Option'; return FALSE;
      }
    }
    
    private function fetchInvoiceTickets() {
      $this->queryData = [];
      $this->queryData['formKey'] = $this->formKey;
      $this->queryData['endPoint'] = 'tickets';
      $this->queryData['method'] = 'GET';
      $this->queryData['queryParams']['resources'] = [ 'ticket_index', 'TicketNumber', 'RunNumber', 'BillTo', 'RequestedBy', 'ReceivedDate', 'pClient', 'pDepartment', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'dClient', 'dDepartment', 'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dryIce', 'diWeight', 'diPrice', 'TicketBase', 'Charge', 'Contract', 'Multiplier', 'RunPrice', 'TicketPrice', 'EmailConfirm', 'EmailAddress', 'Notes', 'DispatchTimeStamp', 'DispatchedTo', 'DispatchedBy', 'Transfers', 'TransferState', 'PendingReceiver', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'pTime', 'dTime', 'd2Time', 'pSigReq', 'dSigReq', 'd2SigReq', 'pSigPrint', 'dSigPrint', 'd2SigPrint', 'pSig', 'dSig', 'd2Sig', 'pSigType', 'dSigType', 'd2SigType', 'RepeatClient', 'InvoiceNumber' ];
      for ($i = 0; $i < count($this->result); $i++) {
        $this->queryData['queryParams']['filter'][] = [ [ 'Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>$this->result[$i]['InvoiceNumber'] ] ];
      }
      
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        echo $this->error;
        return FALSE;
      }
      $this->tickets = self::callQuery($this->query);
      if ($this->result === FALSE) {
        echo $this->error;
        return FALSE;
      }
    }
    
    private function sortMonths() {
      // When querying the month list gets out of order
      // Ensure chronological order using array_merge and array_flip
      $tempMonthList = array();
      $newMonthOrder = array();
      foreach ($this->months as $key => $value) {
        $tempMonthList[] = date("Y-m", strtotime($key));
      }
      sort($tempMonthList);
      for ($i = 0; $i < count($tempMonthList); $i++) {
        $newMonthOrder[] = date("M Y", strtotime($tempMonthList[$i]));
      }
      $monthHolder = array_merge(array_flip($newMonthOrder), $this->months);
      $this->months = $monthHolder;
    }
    
    private function groupTickets() {
      foreach ($this->result as $ticket) {
        try {
          $receivedDate = new \dateTime($ticket['ReceivedDate'], $this->timezone);
        } catch (Exception $e) {
          $this->error = 'Processing Error Line ' . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== FALSE) self::writeLoop();
          echo $this->error;
          return FALSE;
        }
        
        $monthLabel = $receivedDate->format('M Y');
      
        // group tickets by month
        if (isset($this->months[$monthLabel][$ticket['BillTo']])) {
          $this->months[$monthLabel][$ticket['BillTo']]['monthTotal']++;
          $this->months[$monthLabel][$ticket['BillTo']]['endDate'] = $receivedDate->format('Y-m-d');
        } else {
          $this->months[$monthLabel][$ticket['BillTo']] = array(
                                      "billTo" => $ticket['BillTo'],
                                      "monthTotal" => 1,
                                      "contract" => 0,
                                      "credit" => 0,
                                      "cancelled" => 0,
                                      "onCall" => 0,
                                      "routine" => 0,
                                      "fourHour" => 0,
                                      "threeHour" => 0,
                                      "twoHour" => 0,
                                      "oneHour" => 0,
                                      "roundTrip" => 0,
                                      "deadRun" => 0,
                                      "dedicatedRun" => 0,
                                      "withIce" => 0,
                                      "withoutIce" => 0,
                                      "startDate" => $receivedDate->format('Y-m-d'),
                                      "endDate" => $receivedDate->format('Y-m-d')
                                    );
        }
        // count totals for ticket types overall and by month
        switch ($ticket['Charge']) {
          case 0:
            $this->months[$monthLabel][$ticket['BillTo']]['cancelled']++;
          break;
          case 1:
            $this->months[$monthLabel][$ticket['BillTo']]['oneHour']++;
          break;
          case 2:
            $this->months[$monthLabel][$ticket['BillTo']]['twoHour']++;
          break;
          case 3:
            $this->months[$monthLabel][$ticket['BillTo']]['threeHour']++;
          break;
          case 4:
            $this->months[$monthLabel][$ticket['BillTo']]['fourHour']++;
          break;
          case 5:
            $this->months[$monthLabel][$ticket['BillTo']]['routine']++;
          break;
          case 6:
            $this->months[$monthLabel][$ticket['BillTo']]['roundTrip']++;
          break;
          case 7:
            $this->months[$monthLabel][$ticket['BillTo']]['dedicatedRun']++;
          break;
          case 8:
            $this->months[$monthLabel][$ticket['BillTo']]['deadRun']++;
          break;
          case 9:
            $this->months[$monthLabel][$ticket['BillTo']]['credit']++;
          break;
        }
        switch ($ticket['Contract']) {
          case 0:
            $this->months[$monthLabel][$ticket['BillTo']]['onCall']++;
          break;
          case 1:
            $this->months[$monthLabel][$ticket['BillTo']]['contract']++;
          break;
        }
        switch ($ticket['dryIce']) {
          case 1:
            $this->months[$monthLabel][$ticket['BillTo']]['withIce']++;
          break;
          case 0:
            $this->months[$monthLabel][$ticket['BillTo']]['withoutIce']++;
          break;
        }
      }
      if(count($this->months) > 1) {
        self::sortMonths();
      }
    }
    
    private function groupInvoiceTickets() {
      if (!is_array($this->tickets) || empty($this->tickets)) {
        $this->error = 'No Tickets To Sort';
        return FALSE;
      }
      // Split $this->months up by clientID if this is an organization call
      if ($this->organizationFlag === TRUE) {
        foreach ($this->result as $invoice) {
          $invoiceGroup = $invoice['ClientID'];
          $invoiceLabel = date('M Y', strtotime($invoice['DateIssued']));
          if (isset($this->months[$invoiceLabel][$invoiceGroup])) {
            $this->months[$invoiceLabel][$invoiceGroup]['invoices'][] = $invoice['InvoiceNumber'];
            $this->months[$invoiceLabel][$invoiceGroup]['monthTotal'] += $invoice['InvoiceSubTotal'];
            $this->months[$invoiceLabel][$invoiceGroup]['monthTotal'] -= $invoice['BalanceForwarded'];
          } else {
            $this->months[$invoiceLabel][$invoiceGroup] = array (
                                        "invoices" => array(0 => $invoice['InvoiceNumber']),
                                        "monthTotal" => $invoice['InvoiceSubTotal'] - $invoice['BalanceForwarded'],
                                        "contract" => 0,
                                        "credit" => 0,
                                        "cancelled" => 0,
                                        "onCall" => 0,
                                        "routine" => 0,
                                        "fourHour" => 0,
                                        "threeHour" => 0,
                                        "twoHour" => 0,
                                        "oneHour" => 0,
                                        "roundTrip" => 0,
                                        "deadRun" => 0,
                                        "dedicatedRun" => 0,
                                        "dryIce" => 0,
                                        "iceDelivery" => 0,
                                        );
          }
        }
      } else {
        // Otherwise only split up the data by month
        foreach ($this->result as $invoice) {
          $invoiceLabel = date('M Y', strtotime($invoice['DateIssued']));
          if (isset($this->months[$invoiceLabel])) {
            $this->months[$invoiceLabel]['invoices'][] = $invoice['InvoiceNumber'];
            $this->months[$invoiceLabel]['monthTotal'] += $invoice['InvoiceSubTotal'];
            $this->months[$invoiceLabel]['monthTotal'] -= $invoice['BalanceForwarded'];
          } else {
            $this->months[$invoiceLabel] = array (
                                        "invoices" => array(0 => $invoice['InvoiceNumber']),
                                        "billTo" => $invoice['ClientID'],
                                        "monthTotal" => $invoice['InvoiceSubTotal'] - $invoice['BalanceForwarded'],
                                        "contract" => 0,
                                        "credit" => 0,
                                        "cancelled" => 0,
                                        "onCall" => 0,
                                        "routine" => 0,
                                        "fourHour" => 0,
                                        "threeHour" => 0,
                                        "twoHour" => 0,
                                        "oneHour" => 0,
                                        "roundTrip" => 0,
                                        "deadRun" => 0,
                                        "dedicatedRun" => 0,
                                        "dryIce" => 0,
                                        "iceDelivery" => 0,
                                        );
          }
        }
      }
      foreach ($this->tickets as $ticket) {
        if (!$targetKey = self::recursive_array_search($ticket['InvoiceNumber'], $this->months)) {
          echo $this->error;
          return FALSE;
        }
        if ($this->organizationFlag === FALSE) {
          switch ($ticket['Charge']) {
            case 0:
              $this->months[$targetKey]["cancelled"] += $ticket['TicketPrice'];
            break;
            case 1:
              $this->months[$targetKey]["oneHour"] += $ticket['TicketPrice'];
            break;
            case 2:
              $this->months[$targetKey]["twoHour"] += $ticket['TicketPrice'];
            break;
            case 3:
              $this->months[$targetKey]["threeHour"] += $ticket['TicketPrice'];
            break;
            case 4:
              $this->months[$targetKey]["fourHour"] += $ticket['TicketPrice'];
            break;
            case 5:
              $this->months[$targetKey]["routine"] += $ticket['TicketPrice'];
            break;
            case 6:
              $this->months[$targetKey]["roundTrip"] += $ticket['TicketPrice'];
            break;
            case 7:
              $this->months[$targetKey]["dedicatedRun"] += $ticket['TicketPrice'];
            break;
            case 8:
              $this->months[$targetKey]["deadRun"] += $ticket['TicketPrice'];
            break;
            case 9:
              $this->months[$targetKey]["credit"] += $ticket['TicketPrice'];
            break;
          }
          switch ($ticket['Contract']) {
            case 1:
              if ($ticket['Charge'] !== 0) $this->months[$targetKey]["contract"] += $ticket['TicketPrice'];
            break;
            case 0:
              if ($ticket['Charge'] !== 9 && $ticket['Charge'] !== 0) $this->months[$targetKey]["onCall"] += $ticket['TicketPrice'];
            break;
          }
          if ($ticket['dryIce']=== 1) {
            if ($ticket['Charge'] !== 0) {
              $this->months[$targetKey]['dryIce'] += $ticket['diPrice'];
              $this->months[$targetKey]['iceDelivery'] += $ticket['RunPrice'];
            }
          }
        } elseif ($this->organizationFlag === TRUE) {
          switch ($ticket['Charge']) {
            case 0:
              $this->months[$targetKey][$ticket['BillTo']]["cancelled"] += $ticket['TicketPrice'];
            break;
            case 1:
              $this->months[$targetKey][$ticket['BillTo']]["oneHour"] += $ticket['TicketPrice'];
            break;
            case 2:
              $this->months[$targetKey][$ticket['BillTo']]["twoHour"] += $ticket['TicketPrice'];
            break;
            case 3:
              $this->months[$targetKey][$ticket['BillTo']]["threeHour"] += $ticket['TicketPrice'];
            break;
            case 4:
              $this->months[$targetKey][$ticket['BillTo']]["fourHour"] += $ticket['TicketPrice'];
            break;
            case 5:
              $this->months[$targetKey][$ticket['BillTo']]["routine"] += $ticket['TicketPrice'];
            break;
            case 6:
              $this->months[$targetKey][$ticket['BillTo']]["roundTrip"] += $ticket['TicketPrice'];
            break;
            case 7:
              $this->months[$targetKey][$ticket['BillTo']]["dedicatedRun"] += $ticket['TicketPrice'];
            break;
            case 8:
              $this->months[$targetKey][$ticket['BillTo']]["deadRun"] += $ticket['TicketPrice'];
            break;
            case 9:
              $this->months[$targetKey][$ticket['BillTo']]["credit"] += $ticket['TicketPrice'];
            break;
          }
          switch ($ticket['Contract']) {
            case 1:
              if ($ticket['Charge'] !== 0) $this->months[$targetKey][$ticket['BillTo']]["contract"] += $ticket['TicketPrice'];
            break;
            case 0:
              if ($ticket['Charge'] !== 9 && $ticket['Charge'] !== 0) $this->months[$targetKey][$ticket['BillTo']]["onCall"] += $ticket['TicketPrice'];
            break;
          }
          if ($ticket['dryIce'] === 1) {
            if ($ticket['Charge'] !== 0) {
              $this->months[$targetKey][$ticket['BillTo']]['dryIce'] += $ticket['diPrice'];
              $this->months[$targetKey][$ticket['BillTo']]['iceDelivery'] += $ticket['RunPrice'];
            }
          }
        }
      }
      self::sortMonths();
    }
  }