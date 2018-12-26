<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

  class InvoiceCron extends CommonFunctions {
    protected $invoiceCronLogSuccess;
    protected $invoiceCronLogFailure;
    // array of (int)ClientID that should not be processed on this schedule
    protected $invoiceCronIgnoreClients;
    protected $invoiceCronIgnoreNonRepeat;
    protected $invoiceCronTimezone;
    // invoice variables
    private $startDate;
    private $endDate;
    private $dateIssued;
    private $tickets;
    private $clientList;
    private $newInvoices;
    private $DateIssued;
    private $Over90InvoiceList;
    private $today;
    private $result;
    // update variables
    private $ticketUpdateKeys;
    private $ticketUpdateValues;

    public function __construct($options) {
      try {
        parent::__construct($options, ['noSession'=>true]);
      } catch (Exception $e) {
        throw $e;
      }
      // Extend timeout for large queries
      set_time_limit(3600);
      $this->clients = $this->t_clients = $this->clientList = $this->query = $this->data = $this->ticketUpdateKeys = $this->ticketUpdateValues = [];
      // set timezone
      try {
        self::setTimezone();
      } catch (Exception $e) {
        $this->error = date('Y-m-d H:i:s') . "\nTimezone Error: {$e->getMessage()}\n\n";
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      // set today's date
      try {
        $this->today = new \dateTime('NOW', $this->timezone);
      } catch(Exception $e) {
        $this->error = date('Y-m-d H:i:s') . "\nDate Error: {$e->getMessage()}\n\n";
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      // set the invoice start date
      $tempDate = clone $this->today;
      $tempDate->modify('- 1 month');
      $this->startDate = $tempDate->format('Y-m-d');
      // set the invoice end date
      $tempDate = clone $this->today;
      $tempDate->modify('- 1 day');
      $this->endDate = $tempDate->format('Y-m-d');
      // set DateIssued for all invoices
      $this->DateIssued = $this->today->format('Y-m-d');
    }

    public function createInvoices() {
      // fetch all tickets that have not been billed this cycle
      $this->fetchTickets();
      // fetch most recent invoice numbers and forwarded balances
      $this->fetchLastInvoice();
      // process new invoices
      $this->processInvoices();
      // submit invoices to the API
      $this->submitInvoices();
      // submit ticket updates to API
      $this->submitTickets();
      // log success
      if ($this->invoiceCronLogSuccess === true) {
        $this->error = $this->today->format("d M Y H:i:s.u") . "\n" . count($this->newInvoices) . " Invoices Created\n\n";
        self::writeLoop();
      }
      exit;
    }

    private function fetchTickets() {
      $ticketQueryData['noSession'] = true;
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['queryParams']['include'] = array('ticket_index', 'TicketPrice', 'Charge', 'RepeatClient', 'BillTo');
      // Create filter for repeat clients
      $repeatFilter = [ [ 'Resource'=>'ReceivedDate', 'Filter'=>'bt', 'Value'=>"{$this->startDate} 00:00:00, {$this->endDate} 11:59:59" ], [ 'Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>'-' ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1 ] ];
      if (!empty($this->ignoreClients)) $repeatFilter[] = [ 'Resource'=>'ClientID', 'Filter'=>'nin', 'Value'=>implode(',', $this->ignoreClients) ];
      // Create filter for non-repeat clients
      $nonrepeatFilter = [ [ 'Resource'=>'ReceivedDate', 'Filter'=>'bt', 'Value'=>"{$this->startDate} 00:00:00, {$this->endDate} 11:59:59" ], [ 'Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>'-' ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0 ] ];
      if (!empty($this->ignoreNonRepeat)) $repeatFilter[] = [ 'Resource'=>'ClientID', 'Filter'=>'nin', 'Value'=>implode(',', $this->ignoreNonRepeat) ];
      $ticketQueryData['queryParams']['filter'] = [ $repeatFilter, $nonrepeatFilter ];
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      $this->result = self::callQuery($ticketQuery);
      if ($this->result === false) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      if (empty($this->result)) {
        $this->error = "{$this->today->format('d M Y H:i:s.u')}\nNo Tickets To Process\n\n" . print_r($this->data, true) . "\n" . print_r($this->result, true) . "\n----\n";
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      for ($i = 0; $i < count($this->result); $i++) {
        $key = ($this->result[$i]['RepeatClient'] === 1) ? $this->result[$i]['BillTo'] : "t{$this->result[$i]['BillTo']}";
        if (!array_key_exists($key, $this->clientList)) $this->clientList[$key] = [ 'tickets'=>[], 'lastInvoice'=>[], 'openInvoices'=>[] ];
        $this->clientList[$key]['tickets'][] = $this->result[$i];
      }
    }

    private function fetchLastInvoice() {
      // Grabbing all invoices in a single call then sorting them seems more efficient than trying to compose multiple queries to filter by Closed state, ClientID, and most recent DateIssued
      $invoiceQueryData['noSession'] = true;
      $invoiceQueryData['endPoint'] = 'invoices';
      $invoiceQueryData['method'] = 'GET';
      $invoiceQueryData['queryParams']['include'] = [ 'InvoiceNumber', 'RepeatClient', 'BalanceForwarded', 'InvoiceSubTotal', 'DateIssued', 'Closed', 'Deleted' ];
      // Split repeat and non-repeat clientIDs into separate arrays
      $repeats = $nonrepeats = $repeatFilter = $nonrepeatFilter = [];
      foreach($this->clientList as $key => $value) {
        if (strpos($key,'t') === false) {
          $repeats[] = $key;
        } else {
          $nonrepeats[] = substr($key, 1);
        }
      }
      if (!empty($repeats)) {
        $repeatFilter = [ [ 'Resource'=>'ClientID', 'Filter'=>'in', 'Value'=>implode(',', $repeats) ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>1 ] ];
      }
      if (!empty($nonrepeats)) {
        $nonrepeatFilter = [ [ 'Resource'=>'ClientID', 'Filter'=>'in', 'Value'=>implode(',', $nonrepeats) ], [ 'Resource'=>'RepeatClient', 'Filter'=>'eq', 'Value'=>0] ];
      }
      if (!empty($repeatFilter) && !empty($nonrepeatFilter)) {
        $this->data['filter'] = [ $repeatFilter, $nonrepeatFilter ];
      } else {
        $this->data['filter'] = (empty($nonrepeatFilter)) ? $repeatFilter : $nonrepeatFilter;
      }
      if (!$invoiceQuery = self::createQuery($invoiceQueryData)) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      $this->result = self::callQuery($invoiceQuery);
      if ($this->result === false) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      for ($i = 0; $i < count($this->result); $i++) {
        $tempID = substr($this->result[$i]['InvoiceNumber'], strpos($this->result[$i]['InvoiceNumber'],'-') + 1);
        if ($this->result[$i]['Closed'] === 0 && $this->result[$i]['Deleted'] === 0) {
            $this->clientList[$tempID]['openInvoices'][] = $this->result[$i];
        }
        if (empty($this->clientList[$tempID]['lastInvoice'])) {
          $this->clientList[$tempID]['lastInvoice'] = $this->result[$i];
        } else {
          $this->clientList[$tempID]['lastInvoice'] = ($this->clientList[$tempID]['lastInvoice']['DateIssued'] > $this->result[$i]['DateIssued']) ? $this->clientList[$tempID]['lastInvoice'] : $this->result[$i];
        }
      }
    }

    private function processInvoices() {
      foreach ($this->clientList as $key => $value) {
        if (empty($value['tickets'])) continue;
        // create invoice object for submission
        $tempInvoice = new \stdClass();
        $tempInvoice->ClientID = self::test_int($key);
        $tempInvoice->RepeatClient = (substr($key,0,1) === 't') ? 0 : 1;
        $tempInvoice->StartDate = $this->startDate;
        $tempInvoice->EndDate = $this->endDate;
        $tempInvoice->DateIssued = $this->DateIssued;
        // create new InvoiceNumber
        $invoicePointer = mt_rand(1000, 1100);
        if (!empty($value['lastInvoice'])) {
          $invoicePointer = (int)self::between('X', '-', $value['lastInvoice']['InvoiceNumber']) + 1;
        }
        $tempInvoice->InvoiceNumber = "{$this->today->format('y')}EX{$invoicePointer}-{$key}";
        // solve the invoice subtotal and prep tickets to be update with new invoice number
        $tempInvoice->InvoiceTotal = $tempInvoice->InvoiceSubTotal = $this->getTotal($value['tickets'], $tempInvoice->InvoiceNumber);
        // solve the amount due for the current invoice
        $tempInvoice->AmountDue = (!empty($value['lastInvoice'])) ? $tempInvoice->InvoiceSubTotal - $value['lastInvoice']['BalanceForwarded'] : $tempInvoice->InvoiceSubTotal;
        // solve the total due for all open invoices
        if (array_key_exists('openInvoices', $value) && !empty($value['openInvoices'])) {
          // Past due invoices need to be sortted by age and added to InvoiceTotal
          for ($i = 0; $i < count($value['openInvoices']); $i++) {
            try {
              $tempDate = new \dateTime($value['openInvoices'][$i]['DateIssued'], $this->timezone);
            } catch(Exception $e) {
              $this->content = "{$today->format("d M Y H:i:s.u")}\nDate Error Line " . __line__ . ": {$e->getMessage()}\n\n";
              $this->writeLoop();
              exit;
            }
            $plusOneMonth = clone $tempDate;
            $plusOneMonth->modify('+ 1 month');
            $plusTwoMonth = clone $tempDate;
            $plusTwoMonth->modify('+ 2 month');
            $plusThreeMonth = clone $tempDate;
            $plusThreeMonth->modify('+ 3 month');
            $diff = $tempDate->diff($this->today);
            if ($diff->days > $tempDate->format('t') && $diff->days < ($tempDate->format('t') + $plusOneMonth->format('t'))) {
              $tempInvoice->InvoiceTotal += $value['openInvoices'][$i]['InvoiceSubTotal'];
              if (property_exists($tempInvoice, 'Late30Invoice')) {
                $tempInvoice->Late30Invoice .= (strpos($tempInvoice->Late30Invoice, '+') === FALSE) ? '+' : '';
              } else {
                $tempInvoice->Late30Invoice = $value['openInvoices'][$i]['InvoiceNumber'];
              }
              if (property_exists($tempInvoice, 'Late30Value')) {
                $tempInvoice->Late30Value +=  $value['openInvoices'][$i]['InvoiceSubTotal'];
              } else {
                $tempInvoice->Late30Value = $value['openInvoices'][$i]['InvoiceSubTotal'];
              }
            } elseif ($diff->days >= ($tempDate->format('t') + $plusOneMonth->format('t')) && $diff->days < ($tempDate->format('t') + $plusOneMonth->format('t') + $plusTwoMonth->format('t'))) {
              $tempInvoice->InvoiceTotal += $value['openInvoices'][$i]['InvoiceSubTotal'];
              if (property_exists($tempInvoice, 'Late60Invoice')) {
                $tempInvoice->Late60Invoice .= (strpos($tempInvoice->Late60Invoice, '+') === FALSE) ? '+' : '';
              } else {
                $tempInvoice->Late60Invoice = $value['openInvoices'][$i]['InvoiceNumber'];
              }
              if (property_exists($tempInvoice, 'Late60Value')) {
                $tempInvoice->Late60Value +=  $value['openInvoices'][$i]['InvoiceSubTotal'];
              } else {
                $tempInvoice->Late60Value = $value['openInvoices'][$i]['InvoiceSubTotal'];
              }
            } elseif ($diff->days >= ($tempDate->format('t') + $plusOneMonth->format('t') + $plusTwoMonth->format('t')) && $diff->days < ($tempDate->format('t') + $plusOneMonth->format('t') + $plusTwoMonth->format('t') + $plusThreeMonth->format('t'))) {
              $tempInvoice->InvoiceTotal += $value['openInvoices'][$i]['InvoiceSubTotal'];
              if (property_exists($tempInvoice, 'Late90Invoice')) {
                $tempInvoice->Late90Invoice .= (strpos($tempInvoice->Late90Invoice, '+') === FALSE) ? '+' : '';
              } else {
                $tempInvoice->Late90Invoice = $value['openInvoices'][$i]['InvoiceNumber'];
              }
              if (property_exists($tempInvoice, 'Late90Value')) {
                $tempInvoice->Late90Value +=  $value['openInvoices'][$i]['InvoiceSubTotal'];
              } else {
                $tempInvoice->Late90Value = $value['openInvoices'][$i]['InvoiceSubTotal'];
              }
            } elseif ($diff->days >= ($tempDate->format('t') + $plusOneMonth->format('t') + $plusTwoMonth->format('t') + $plusThreeMonth->format('t'))) {
              $tempInvoice->InvoiceTotal += $value['openInvoices'][$i]['InvoiceSubTotal'];
              $this->Over90InvoiceList[] = $value['openInvoices'][$i]['InvoiceNumber'];
              if (property_exists($tempInvoice, 'Over90Value')) {
                $tempInvoice->Over90Value +=  $value['openInvoices'][$i]['InvoiceSubTotal'];
              } else {
                $tempInvoice->Over90Value = $value['openInvoices'][$i]['InvoiceSubTotal'];
              }
            }
            // Fix the over90Invioce to display a maximum of 4 invoice numbers or 3 invoice numbers and how many are not displayed, but only if there is at least one
            if (!empty($this->Over90InvoiceList)) {
              if (count($this->Over90InvoiceList) > 4) {
                $j = count($this->Over90InvoiceList) - 3;
                $appendment = ", + $j more";
                $tempInvoice->Over90Invoice = implode(', ', array_slice($this->Over90InvoiceList, 0, 3));
                $tempInvoice->Over90Invoice .= $appendment;
              } else {
                $tempInvoice->Over90Invoice = implode(', ',$this->Over90InvoiceList);
              }
            }
          }
          // Clear this list for the next iteration
          $this->Over90InvoiceList = [];
        }
        // add invoice object to array for submission
        $this->newInvoices[] = $tempInvoice;
      }
    }

    private function getTotal($ticketArray, $invoiceNumber) {
      if (!is_array($ticketArray)) return 0;
      $total = 0;
      for ($i = 0; $i < count($ticketArray); $i++) {
        $total += ($ticketArray[$i]['Charge'] !== 0) ? $ticketArray[$i]['TicketPrice'] : 0;
        $this->ticketUpdateKeys[] = $ticketArray[$i]['ticket_index'];
        $temp = new \stdClass();
        $temp->InvoiceNumber = $invoiceNumber;
        $this->ticketUpdateValues[] = $temp;
      }
      return self::number_format_drop_zero_decimals($total, 2);
    }

    private function submitInvoices() {
      $invoiceQueryData['noSession'] = true;
      $invoiceQueryData['endPoint'] = 'invoices';
      $invoiceQueryData['method'] = 'POST';
      $invoiceQueryData['payload'] = $this->newInvoices;
      if (!$invoiceQuery = self::createQuery($invoiceQueryData)) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      $this->result = self::callQuery($invoiceQuery);
      if ($this->result === false) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
    }

    private function submitTickets() {
      $ticketQueryData['noSession'] = true;
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'PUT';
      $ticketQueryData['primaryKey'] = implode(',', $this->ticketUpdateKeys);
      $ticketQueryData['payload'] = $this->ticketUpdateValues;
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
      $this->result = self::callQuery($ticketQuery);
      if ($this->result === false) {
        $this->error = __function__ . ' Line ' . __line__;
        if ($this->invoiceCronLogFailure) self::writeLoop();
        exit;
      }
    }
  }
