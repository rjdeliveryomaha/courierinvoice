<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  use rjdeliveryomaha\courierinvoice\Ticket;
  use rjdeliveryomaha\courierinvoice\Invoice;

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
    private $repeatClients = [];
    private $nonRepeat = [];
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
        $this->today = new \dateTime('NOW', $this->timezone);
      } catch (Exception $e) {
        $this->error .= "\nDate Error Line " . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw $e;
      }
      if ($this->clientID === NULL && $this->billTo !== NULL) {
        $this->clientID = $this->billTo;
      }
    }

    public function ticketLookup() {
      $returnData = [];
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = 'tickets';
      $this->queryData['noSession'] = TRUE;
      $this->queryData['queryParams']['include'] = [ 'Charge', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'd2SigReq' ];
      $this->queryData['queryParams']['filter'] = [ [ 'Resource'=>'TicketNumber', 'Filter'=>'eq', 'Value'=>$this->ticketNumber ] ];
      $this->query = self::createQuery($this->queryData);

      if ($this->query === FALSE) {
        $returnData['queryError'] =  $this->error;
        return json_encode($returnData);
      }

      $this->result = self::callQuery($this->query);

      if ($this->result === FALSE) {
        $returnData['queryError'] =  $this->error;
        return json_encode($returnData);
      }

      if (empty($this->result)) {
        $returnData['queryError'] = 'No Results Match Query';
        return json_encode($returnData);
      }

      if ($this->result[0]['Charge'] === 0) {
        $returnData['queryError'] = 'Delivery Canceled';
        return json_encode($returnData);
      }

      foreach ($this->result[0] as $key => $value) {
        if ($key !== 'Charge') {
          if ($value === '' || $value === NULL) {
            $returnData[$key] = 'Pending';
          } else {
            $returnData[$key] = date('d M Y \a\t h:i A', strtotime($value));
          }
        }
      }

      if ($this->result[0]['Charge'] !== 6 || ($this->result[0]['Charge'] === 7 && $this->result[0]['d2SigReq'] !== 1)) {
        $returnData['d2TimeStamp'] = 'Not Scheduled';
      }

      return json_encode($returnData);
    }

    public function handleSearch() {
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = $this->endPoint;
      if (!is_array($this->clientID)) {
        $marker = ($this->RepeatClient === 1) ? $this->clientID : "t{$this->clientID}";
        $this->clientID = [ $marker ];
      }
      for ($i = 0; $i < count($this->clientID); $i++) {
        if (strpos($this->clientID[$i], 't') === FALSE) {
          $this->repeatClients[] = $this->clientID[$i];
        } else {
          $this->nonRepeat[] = self::test_int($this->clientID[$i]);
        }
      }
      $billToResource = 'BillTo';
      $dateResource = 'ReceivedDate';
      $repeatFilter = $nonRepeatFilter = [];
      $this->queryData['queryParams']['exclude'] = [ 'Tenant' ];
      switch ($this->endPoint) {
        case 'tickets':
          if (!empty($this->repeatClients)) {
            if ($this->charge < 10) {
              $repeatFilter[] = [ 'Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>$this->charge ];
            }
            if ($this->type < 2) {
              $repeatFilter[] = [ 'Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->type ];
            }
            $repeatFilter[] = [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1 ];
          }
          if (!empty($this->nonRepeat)) {
            if ($this->charge < 10) {
              $nonRepeatFilter[] = [ 'Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>$this->charge ];
            }
            if ($this->type < 2) {
              $nonRepeatFilter[] = [ 'Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->type ];
            }
            $nonRepeatFilter[] = [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0 ];
          }
        break;
        case 'invoices':
          $billToResource = 'ClientID';
          $dateResource = 'DateIssued';
          if (!empty($this->repeatClients)) {
            $repeatFilter[] = [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1 ];
          }
          if (!empty($this->nonRepeat)) {
            $nonRepeatFilter[] = [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0 ];
          }
          if ($this->display === 'invoice') $this->queryData['queryParams']['join'] = [ 'tickets' ];
        break;
        default:
          $this->error = 'Invalid End Point ' . __line__;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return "<p class=\"result center\">{$this->error}</p>";
      }
      if (!empty($this->repeatClients)) {
        $repeatFilter[] = [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients) ];
      }
      if (!empty($this->nonRepeat)) {
        $nonRepeatFilter[] = [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat) ];
      }

      if ($this->ticketNumber !== NULL) {
        $repeatFilter = $nonRepeatFilter = [];
        if ($this->ulevel === 0) {
          $members = array_keys($this->members);
          $this->repeatClients = $this->nonRepeat = [];
          for ($i = 0; $i < count($members); $i++) {
            if (strpos($members[$i], 't') === FALSE) {
              $this->repeatClients[] = $members[$i];
            } else {
              $this->nonRepeat[] = self::test_int($members[$i]);
            }
          }
          if (!empty($this->repeatClients)) {
            $repeatFilter[] = [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients) ];
          }
          if (!empty($this->nonRepeat)) {
            $nonRepeatFilter[] = [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat) ];
          }
        } else {
          if (!empty($this->repeatClients)) {
            $repeatFilter[] = [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients) ];
          }
          if (!empty($this->nonRepeat)) {
            $nonRepeatFilter[] = [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat) ];
          }
        }
        if (!empty($this->repeatClients)) {
          $repeatFilter[] = [ 'Resource'=>'TicketNumber', 'Filter'=>'eq', 'Value'=>$this->ticketNumber ];
        }
        if (!empty($this->nonRepeat)) {
          $nonRepeatFilter[] = [ 'Resource'=>'TicketNumber', 'Filter'=>'eq', 'Value'=>$this->ticketNumber ];
        }
      } elseif ($this->invoiceNumber !== NULL) {
        if (!empty($this->repeatClients)) {
          $repeatFilter[] = [ 'Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>$this->invoiceNumber ];
        }
        if (!empty($this->nonRepeat)) {
          $nonRepeatFilter[] = [ 'Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>$this->invoiceNumber ];
        }
      } elseif ($this->dateIssued !== NULL) {
        if (!empty($this->repeatClients)) {
          $repeatFilter[] = [ 'Resource'=>'DateIssued', 'Filter'=>'sw', 'Value'=>$this->dateIssued ];
        }
        if (!empty($this->nonRepeat)) {
          $nonRepeatFilter[] = [ 'Resource'=>'DateIssued', 'Filter'=>'sw', 'Value'=>$this->dateIssued ];
        }
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
          return "<p class=\"result center\">{$this->error}</p>";
        }
        try {
          $tempEnd = new \dateTime($this->endDate, $this->timezone);
        } catch (Exception $e) {
          $this->error .= "\n" . __function__ . ' Line ' . __line__ . ': ' . $e->getMessage();
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return "<p class=\"result center\">{$this->error}</p>";
        }
        if ($this->compare === FALSE) {
          $endDateMarker = ($this->display === 'chart') ? 'Y-m-t' : 'Y-m-d';
          // Make sure the query only pulls as many months as $this->allTimeChartLimit
          $diff = $tempStart->diff($tempEnd);
          if ($diff->m > $this->allTimeChartLimit) {
            $marker = ($this->allTimeChartLimit === 1) ? 'month' : 'months';
            $this->error = "Query Range to large. Please limit query range to {$this->allTimeChartLimit} {$marker}.";
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return "<p class=\"result center\">{$this->error}</p>";
          }
          if (!empty($this->repeatClients)) {
            $repeatFilter[] = ['Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempStart->format('Y-m-d')} 00:00:00,{$tempEnd->format($endDateMarker)} 23:59:59"];
          }
          if (!empty($this->nonRepeat)) {
            $nonRepeatFilter[] = ['Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempStart->format('Y-m-d')} 00:00:00,{$tempEnd->format($endDateMarker)} 23:59:59"];
          }
        } else {
          if (!empty($this->repeatClients)) {
            $repeatFilter = [ [ [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients) ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1 ], [ 'Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempStart->format('Y-m-d')} 00:00:00,{$tempStart->format('Y-m-t')} 23:59:59" ] ], [ [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients) ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1 ], [ 'Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempEnd->format('Y-m-d')} 00:00:00,{$tempEnd->format('Y-m-t')} 23:59:59" ] ] ];
          }
          if (!empty($this->nonRepeat)) {
            $nonRepeatFilter = [ [ [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat) ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0 ], [ 'Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempStart->format('Y-m-d')} 00:00:00,{$tempStart->format('Y-m-t')} 23:59:59" ] ], [ [ 'Resource'=>$billToResource, 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat) ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0 ], [ 'Resource'=>$dateResource, 'Filter'=>'bt', 'Value'=>"{$tempEnd->format('Y-m-d')} 00:00:00,{$tempEnd->format('Y-m-t')} 23:59:59" ] ] ];
          }
        }
      } elseif ($this->allTime === '0') {
        $temp = clone $this->today;
        $this->yesterday = $temp->modify('-1 day')->format('Y-m-d') . ' 23:59:59';
        if (!empty($this->repeatClients)) {
          $repeatFilter[] = [ ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients)], ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'ReceivedDate', 'Filter'=>'gt', 'Value'=>$this->yesterday] ];
        }
        if (!empty($this->nonRepeat)) {
          $nonRepeatFilter[] = [ ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat)], ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'ReceivedDate', 'Filter'=>'gt', 'Value'=>$this->yesterday] ];
        }
      } elseif ($this->allTime === '1') {
        switch ($this->display) {
          case 'tickets':
            if (!empty($this->repeatClients)) {
              $filterStart = [ ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients)], ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>$this->charge], ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->type] ];
              $repeatFilter = [];
              // Remove the type and charge filter if they are set to their respective 'all' values
              foreach($filterStart as $temp) {
                if ($temp['Resource'] === 'Charge') {
                  if ($temp['Value'] !== 10) {
                    $repeatFilter[] = $temp;
                  }
                } elseif ($temp['Resource'] === 'Contract') {
                  if ($temp['Value'] !== 2) {
                    $repeatFilter[] = $temp;
                  }
                } else {
                  $repeatFilter[] = $temp;
                }
              }
            }
            if (!empty($this->nonRepeat)) {
              $filterStart = [ ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat)], ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>$this->charge], ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>$this->type] ];
              $nonRepeatFilter = [];
              // Remove the type and charge filter if they are set to their respective 'all' values
              foreach($filterStart as $temp) {
                if ($temp['Resource'] === 'Charge') {
                  if ($temp['Value'] !== 10) {
                    $nonRepeatFilter[] = $temp;
                  }
                } elseif ($temp['Resource'] === 'Contract') {
                  if ($temp['Value'] !== 2) {
                    $nonRepeatFilter[] = $temp;
                  }
                } else {
                  $nonRepeatFilter[] = $temp;
                }
              }
            }
          break;
          case 'chart':
            $this->startDate = clone $this->today;
            $this->startDate->modify('- ' . ($this->allTimeChartLimit - 1). ' months');
            $this->endDate = $this->today->format('Y-m-t') . ' 23:59:59';
            if (!empty($this->repeatClients)) {
              $repeatFilter = [ ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $this->repeatClients)], ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'ReceivedDate', 'Filter'=>'bt', 'Value'=>"{$this->startDate->format('Y-m-d')} 00:00:00,{$this->endDate}"] ];
            }
            if (!empty($this->nonRepeat)) {
              $nonRepeatFilter = [ ['Resource'=>'BillTo', 'Filter'=>'in', 'Value'=>implode(',', $this->nonRepeat)], ['Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'ReceivedDate', 'Filter'=>'bt', 'Value'=>"{$this->startDate->format('Y-m-d')} 00:00:00,{$this->endDate}"] ];
            }
          break;
          default:
            $this->error = 'Invalid Display Option Line ' . __line__;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return "<p class=\"result center\">{$this->error}</p>";
        }
      }
      if (!empty($repeatFilter) && !empty($nonRepeatFilter)) {
        $this->queryData['queryParams']['filter'] = [ $repeatFilter, $nonRepeatFilter ];
      } elseif (empty($repeatFilter) && !empty($nonRepeatFilter)) {
        $this->queryData['queryParams']['filter'] = $nonRepeatFilter;
      } elseif (!empty($repeatFilter) && empty($nonRepeatFilter)) {
        $this->queryData['queryParams']['filter'] = $repeatFilter;
      } else {
        $this->error = 'Empty Query Filter Line ' . __line__;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return "<p class=\"result center\">{$this->error}</p>";
      }
      // return self::safe_print_r($this->queryData);
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return "<p class=\"result center\">{$this->error}</p>";
      }
      try {
        $this->result = self::callQuery($this->query);
      } catch(Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return "<p class=\"result center\">{$this->error}</p>";
      }
      if ($this->result === FALSE) {
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return "<p class=\"result center\">{$this->error}</p>";
      }
      if (empty($this->result)) {
        return '<p class="result center">No Results Match Query</p>';
      }
      switch ($this->display) {
        case 'tickets':
          $returnData = '';
          $temp = self::createTicket([]);
          if ($temp === FALSE) {
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return "<p class=\"result center\">{$this->error}</p>";
          }
          for ($i = 0; $i < count($this->result); $i++) {
            foreach ($this->result[$i] as $key => $value) {
              $temp->updateProperty($key, $value);
            }
            $returnData .= $temp->regenTicket();
          }
          return $returnData;
        break;
        case 'invoice':
          $data['invoiceQueryResult'] = $this->result;
          $temp = self::createInvoice($data);
          if ($temp === FALSE) {
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return "<p class=\"result center\">{$this->error}</p>";
          }
          if (!$displayInvoice = $temp->regenInvoice()) {
            $this->error = $temp->getError();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return "<p class=\"result center\">{$this->error}</p>";
          }
          return $displayInvoice;
        break;
        case 'chart':
          $chartData = [ 'organizationFlag'=>$this->organizationFlag, 'clientID'=>$this->clientID, 'compare'=>$this->compare, 'compareMembers'=>$this->compareMembers, ];
          if ($this->endPoint === 'tickets') {
            $groupTicketsError = self::groupTickets();
            if ($groupTicketsError !== FALSE) {
              $this->error = $groupTicketsError;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              return "<p class=\"result center\">{$this->error}</p>";
            }
            $chartData['dataSet'] = $this->months;
            $chart = self::createTicketChart($chartData);
          } elseif ($this->endPoint === 'invoices') {
            $fetchTicketError = self::fetchInvoiceTickets();
            if ($fetchTicketError !== FALSE) {
              $this->error = $fetchTicketError;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              return "<p class=\"result center\">{$this->error}</p>";
            }
            $groupTicketsError = self::groupInvoiceTickets();
            if ($groupTicketsError !== FALSE) {
              $this->error = $groupTicketsError;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              return "<p class=\"result center\">{$this->error}</p>";
            }
            $chartData['dataSet'] = $this->months;
            $chart = self::createInvoiceChart($chartData);
          }
          if ($chart === FALSE) {
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return "<p class=\"result center\">{$this->error}</p>";
          }
          if (!$displayChart = $chart->displayChart()) {
            $this->error = $chart->getError();
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return "<p class=\"result center\">{$this->error}</p>";
          }
          return $displayChart;
        break;
        default: return '<p class="result center">Invalid Display Option</p>';
      }
    }

    private function fetchInvoiceTickets() {
      $this->queryData = [];
      $this->queryData['endPoint'] = 'tickets';
      $this->queryData['method'] = 'GET';
      $this->queryData['queryParams']['include'] = [ 'ticket_index', 'TicketNumber', 'RunNumber', 'BillTo', 'RequestedBy', 'ReceivedDate', 'pClient', 'pDepartment', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'dClient', 'dDepartment', 'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dryIce', 'diWeight', 'diPrice', 'TicketBase', 'Charge', 'Contract', 'Multiplier', 'RunPrice', 'TicketPrice', 'EmailConfirm', 'EmailAddress', 'Notes', 'DispatchTimeStamp', 'DispatchedTo', 'DispatchedBy', 'Transfers', 'TransferState', 'PendingReceiver', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'pTime', 'dTime', 'd2Time', 'pSigReq', 'dSigReq', 'd2SigReq', 'pSigPrint', 'dSigPrint', 'd2SigPrint', 'pSig', 'dSig', 'd2Sig', 'pSigType', 'dSigType', 'd2SigType', 'RepeatClient', 'InvoiceNumber' ];
      for ($i = 0; $i < count($this->result); $i++) {
        $this->queryData['queryParams']['filter'][] = [ [ 'Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>$this->result[$i]['InvoiceNumber'] ] ];
      }

      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        return "<p class=\"result\">{$this->error}</p>";
      }
      $this->tickets = self::callQuery($this->query);
      if ($this->result === FALSE) {
        return "<p class=\"result\">{$this->error}</p>";
      }
      return FALSE;
    }

    private function sortMonths() {
      // When querying the month list gets out of order
      // Ensure chronological order using array_merge and array_flip
      $tempMonthList = $newMonthOrder = [];
      foreach ($this->months as $key => $value) {
        $tempMonthList[] = date('Y-m', strtotime($key));
      }
      sort($tempMonthList);
      for ($i = 0; $i < count($tempMonthList); $i++) {
        $newMonthOrder[] = date('M Y', strtotime($tempMonthList[$i]));
      }
      $monthHolder = array_merge(array_flip($newMonthOrder), $this->months);
      $this->months = $monthHolder;
    }

    private function groupTickets() {
      foreach ($this->result as $ticket) {
        try {
          $receivedDate = new \dateTime($ticket['ReceivedDate'], $this->timezone);
        } catch (Exception $e) {
          return '<p class="result center">Processing Error Line ' . __line__ . ': ' . $e->getMessage() . '</p>';
        }

        $monthLabel = $receivedDate->format('M Y');

        // group tickets by month
        if (isset($this->months[$monthLabel][$ticket['BillTo']])) {
          $this->months[$monthLabel][$ticket['BillTo']]['monthTotal']++;
          $this->months[$monthLabel][$ticket['BillTo']]['endDate'] = $receivedDate->format('Y-m-d');
        } else {
          $this->months[$monthLabel][$ticket['BillTo']] = [ 'billTo'=>$ticket['BillTo'], 'monthTotal'=>1, 'contract'=>0, 'credit'=>0, 'canceled'=>0, 'onCall'=>0, 'routine'=>0, 'fourHour'=>0, 'threeHour'=>0, 'twoHour'=>0, 'oneHour'=>0, 'roundTrip'=>0, 'deadRun'=>0, 'dedicatedRun'=>0, 'withIce'=>0, 'withoutIce'=>0, 'startDate'=>$receivedDate->format('Y-m-d'), 'endDate'=>$receivedDate->format('Y-m-d') ];
        }
        // count totals for ticket types overall and by month
        switch ($ticket['Charge']) {
          case 0:
            $this->months[$monthLabel][$ticket['BillTo']]['canceled']++;
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
      return FALSE;
    }

    private function groupInvoiceTickets() {
      if (!is_array($this->tickets) || empty($this->tickets)) {
        return '<p class="result center">No Tickets To Sort</p>';
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
            $this->months[$invoiceLabel][$invoiceGroup] = [ 'invoices' => [0=>$invoice['InvoiceNumber']], 'monthTotal'=>$invoice['InvoiceSubTotal'] - $invoice['BalanceForwarded'], 'contract'=>0, 'credit'=>0, 'canceled'=>0, 'onCall'=>0, 'routine'=>0, 'fourHour'=>0, 'threeHour'=>0, 'twoHour'=>0, 'oneHour'=>0, 'roundTrip'=>0, 'deadRun'=>0, 'dedicatedRun'=>0, 'dryIce'=>0, 'iceDelivery'=>0, ];
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
            $this->months[$invoiceLabel] = [ 'invoices'=>[0 => $invoice['InvoiceNumber']], 'billTo'=>$invoice['ClientID'], 'monthTotal'=>$invoice['InvoiceSubTotal'] - $invoice['BalanceForwarded'], 'contract'=>0, 'credit'=>0, 'canceled'=>0, 'onCall'=>0, 'routine'=>0, 'fourHour'=>0, 'threeHour'=>0, 'twoHour'=>0, 'oneHour'=>0, 'roundTrip'=>0, 'deadRun'=>0, 'dedicatedRun'=>0, 'dryIce'=>0, 'iceDelivery'=>0, ];
          }
        }
      }
      foreach ($this->tickets as $ticket) {
        if (!$targetKey = self::recursive_array_search($ticket['InvoiceNumber'], $this->months)) {
          return "<p class=\"result center\">Invoice {$ticket['InvoiceNumber']} Not Found</p>";
        }
        if ($this->organizationFlag === FALSE) {
          switch ($ticket['Charge']) {
            case 0:
              $this->months[$targetKey]['canceled'] += $ticket['TicketPrice'];
            break;
            case 1:
              $this->months[$targetKey]['oneHour'] += $ticket['TicketPrice'];
            break;
            case 2:
              $this->months[$targetKey]['twoHour'] += $ticket['TicketPrice'];
            break;
            case 3:
              $this->months[$targetKey]['threeHour'] += $ticket['TicketPrice'];
            break;
            case 4:
              $this->months[$targetKey]['fourHour'] += $ticket['TicketPrice'];
            break;
            case 5:
              $this->months[$targetKey]['routine'] += $ticket['TicketPrice'];
            break;
            case 6:
              $this->months[$targetKey]['roundTrip'] += $ticket['TicketPrice'];
            break;
            case 7:
              $this->months[$targetKey]['dedicatedRun'] += $ticket['TicketPrice'];
            break;
            case 8:
              $this->months[$targetKey]['deadRun'] += $ticket['TicketPrice'];
            break;
            case 9:
              $this->months[$targetKey]['credit'] += $ticket['TicketPrice'];
            break;
          }
          switch ($ticket['Contract']) {
            case 1:
              if ($ticket['Charge'] !== 0) $this->months[$targetKey]['contract'] += $ticket['TicketPrice'];
            break;
            case 0:
              if ($ticket['Charge'] !== 9 && $ticket['Charge'] !== 0) $this->months[$targetKey]['onCall'] += $ticket['TicketPrice'];
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
              $this->months[$targetKey][$ticket['BillTo']]['canceled'] += $ticket['TicketPrice'];
            break;
            case 1:
              $this->months[$targetKey][$ticket['BillTo']]['oneHour'] += $ticket['TicketPrice'];
            break;
            case 2:
              $this->months[$targetKey][$ticket['BillTo']]['twoHour'] += $ticket['TicketPrice'];
            break;
            case 3:
              $this->months[$targetKey][$ticket['BillTo']]['threeHour'] += $ticket['TicketPrice'];
            break;
            case 4:
              $this->months[$targetKey][$ticket['BillTo']]['fourHour'] += $ticket['TicketPrice'];
            break;
            case 5:
              $this->months[$targetKey][$ticket['BillTo']]['routine'] += $ticket['TicketPrice'];
            break;
            case 6:
              $this->months[$targetKey][$ticket['BillTo']]['roundTrip'] += $ticket['TicketPrice'];
            break;
            case 7:
              $this->months[$targetKey][$ticket['BillTo']]['dedicatedRun'] += $ticket['TicketPrice'];
            break;
            case 8:
              $this->months[$targetKey][$ticket['BillTo']]['deadRun'] += $ticket['TicketPrice'];
            break;
            case 9:
              $this->months[$targetKey][$ticket['BillTo']]['credit'] += $ticket['TicketPrice'];
            break;
          }
          switch ($ticket['Contract']) {
            case 1:
              if ($ticket['Charge'] !== 0) $this->months[$targetKey][$ticket['BillTo']]['contract'] += $ticket['TicketPrice'];
            break;
            case 0:
              if ($ticket['Charge'] !== 9 && $ticket['Charge'] !== 0) $this->months[$targetKey][$ticket['BillTo']]['onCall'] += $ticket['TicketPrice'];
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
      return FALSE;
    }
  }
