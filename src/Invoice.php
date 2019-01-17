<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

  class Invoice extends CommonFunctions {
    protected $invoice_index;
    protected $InvoiceNumber;
    protected $ClientID;
    protected $RepeatClient;
    protected $InvoiceTotal;
    protected $InvoiceSubTotal;
    protected $BalanceForwarded;
    protected $AmountDue;
    protected $StartDate;
    protected $EndDate;
    protected $DateIssued;
    protected $DatePaid;
    protected $AmountPaid;
    protected $Balance;
    protected $CheckNumber;
    protected $Closed;
    protected $consolidateContractTicketsOnInvoice = TRUE;
    protected $showCanceledTicketsOnInvoice = FALSE;
    protected $Tickets;
    protected $ConsolidatedTickets = [];
    protected $RegenThisInvoice;
    protected $Late30Invoice;
    protected $Late30Value;
    protected $Late60Invoice;
    protected $Late60Value;
    protected $Late90Invoice;
    protected $Late90Value;
    protected $Over90Invoice;
    protected $Over90Value;
    protected $ticketList;
    protected $invoiceQueryResult;
    protected $invoicePage1Max;
    protected $invoicePageMax;
    private $repeat;
    private $pastDueData = [];
    private $paymentDisplay;
    private $closedMarker;
    private $invoiceDisplay;
    private $counter = 2;

    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
    }

    private function fetchInvoiceTickets() {
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['queryParams']['include'] = [ 'ticket_index', 'TicketNumber', 'RunNumber', 'BillTo', 'RequestedBy', 'ReceivedDate', 'pClient', 'pDepartment', 'pAddress1', 'pAddress2', 'pCountry', 'pContact', 'pTelephone', 'dClient', 'dDepartment', 'dAddress1', 'dAddress2', 'dCountry', 'dContact', 'dTelephone', 'dryIce', 'diWeight', 'diPrice', 'TicketBase', 'Charge', 'Contract', 'Multiplier', 'RunPrice', 'TicketPrice', 'EmailConfirm', 'EmailAddress', 'Notes', 'DispatchTimeStamp', 'DispatchedTo', 'DispatchedBy', 'Transfers', 'TransferState', 'PendingReceiver', 'pTimeStamp', 'dTimeStamp', 'd2TimeStamp', 'pTime', 'dTime', 'd2Time', 'pSigReq', 'dSigReq', 'd2SigReq', 'pSigPrint', 'dSigPrint', 'd2SigPrint', 'pSig', 'dSig', 'd2Sig', 'pSigType', 'dSigType', 'd2SigType', 'RepeatClient', 'InvoiceNumber' ];
      $ticketQueryData['queryParams']['filter'] = [ ['Resource'=>'InvoiceNumber', 'Filter'=>'eq', 'Value'=>$this->InvoiceNumber] ];
      $ticketQueryData['queryParams']['order'] = ['Contract,desc', 'ReceivedDate'];
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        $this->Tickets = FALSE;
        return FALSE;
      }
      $ticketQueryResult = self::callQuery($ticketQuery);
      if ($ticketQueryResult === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        $this->Tickets = FALSE;
        return FALSE;
      }
      $this->Tickets = [];
      for ($i = 0; $i < count($ticketQueryResult); $i++) {
        if (!$this->Tickets[] = self::createTicket($ticketQueryResult[$i])) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          $this->Tickets = FALSE;
          return FALSE;
        }
      }
    }

    private function multiInvoiceForm() {
      $returnData = '
        <p data-error="error" class="center">Multiple invoices available for ' . date('F Y', strtotime($this->invoiceQueryResult[0]['DateIssued'])) . '.</p>
        <form class="center" id="multiInvoiceForm" method="post" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '">
          <input type="hidden" name="clientID" value="' . $this->invoiceQueryResult[0]['ClientID'] . '" form="multiInvoiceForm" />
          <input type="hidden" name="endPoint" value="invoices" form="multiInvoiceForm" />
          <input type="hidden" name="display" value="invoice" />
          <select name="invoiceNumber" form="multiInvoiceForm">';
      foreach ($this->invoiceQueryResult as $invoice) {
        $returnData .= '<option value="' . $invoice['InvoiceNumber'] . '">' . $invoice['InvoiceNumber'] . ' ' . date('d M Y', strtotime($invoice['DateIssued'])) . '</option>';
      }
      $returnData .= '
          </select>
          <button type="submit" id="mulitInvoiceButton" form="multiInvoiceForm">Submit</button>
        </form>';
      return $returnData;
    }

    private function consolidateTickets() {
      //Merge contract tickets
      foreach ($this->Tickets as $ticket) {
        if ($ticket->getProperty('Contract') === 1) {
          if ($ticket->getProperty('Charge') !== 0) {
            if (empty($this->ConsolidatedTickets)) {
              $this->ConsolidatedTickets[] = clone $ticket;
            } else {
              $add = TRUE;
              for ($i = 0; $i < count($this->ConsolidatedTickets); $i++) {
                if (self::compareProperties($ticket, $this->ConsolidatedTickets[$i], 'RunNumber') === TRUE) {
                  $add = FALSE;
                  break;
                }
              }
              if ($add === TRUE) {
                $this->ConsolidatedTickets[] = clone $ticket;
              } else {
                foreach ($this->ConsolidatedTickets as $test) {
                  if ($ticket->getProperty('RunNumber') === $test->getProperty('RunNumber')) {
                    if (!$test->addToProperty('Multiplier', $ticket->getProperty('Multiplier'))) {
                      $temp = $this->error . "\n";
                      $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                      if ($this->enableLogging !== FALSE) self::writeLoop();
                      $this->ConsolidatedTickets = FALSE;
                      return FALSE;
                    }
                    if (!$test->addToProperty('TicketPrice', $ticket->getProperty('TicketPrice'))) {
                      $temp = $this->error . "\n";
                      $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                      if ($this->enableLogging !== FALSE) self::writeLoop();
                      $this->ConsolidatedTickets = FALSE;
                      return FALSE;
                    }
                    if (!$test->updateProperty('ReceivedDate', $ticket->getProperty('ReceivedDate'))) {
                      $temp = $this->error . "\n";
                      $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                      if ($this->enableLogging !== FALSE) self::writeLoop();
                      $this->ConsolidatedTickets = FALSE;
                      return FALSE;
                    }
                  }
                }
              }
            }
          }
        } else {
          $this->ConsolidatedTickets[] = clone $ticket;
        }
      }
    }

    private function invoiceBodyTickets() {
      // Check for ticket consolidation request
      if ($this->consolidateContractTicketsOnInvoice === TRUE) {
        $ticketSet = $this->ConsolidatedTickets;
      } else {
        $ticketSet = $this->Tickets;
      }
      $body = '
            <table class="wide">';
      $page1 = $morePages = $filteredTicketSet = array();
      if ($this->showCanceledTicketsOnInvoice === FALSE) {
        foreach ($ticketSet as $filtered) {
          if ($filtered->getProperty('Charge') !== 0) {
            $filteredTicketSet[] = $filtered;
          }
        }
      } else {
        $filteredTicketSet = $ticketSet;
      }
      if (count($filteredTicketSet) === $this->invoicePage1Max) $this->invoicePage1Max -= 1;
      $page1 = array_slice($filteredTicketSet,0,$this->invoicePage1Max);
      $morePages = array_slice($filteredTicketSet,$this->invoicePage1Max);
      $singlePage = empty($morePages);
      for ($i=0; $i<count($page1); $i++) {
        if ($i === 0) {
          $body .= '
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Ticket #</th>
                <th scope="col">Charge</th>
                <th scope="col" colspan="2">Description</th>
                <th scope="col">Price</th>
                <th scope="col">#</th>
                <th scope="col">Line Price</th>
              </tr>';
          $body .= $page1[$i]->invoiceBody();
        } else {
          $body .= '
              <tr>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col" colspan="2"></th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
              </tr>';
          $body .= $page1[$i]->invoiceBody();
        }
      }
      if ($singlePage) {
        $body .= '
              <tr>
                <td colspan="5" style="border:none;"></td>
                <th scope="row" colspan="2" style="border:1px solid black; white-space:nowrap;">Subtotal:</th>
                <td style="border:1px solid black;"><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</td>
              </tr>';
      } else {
        for ($i=0; $i<count($morePages); $i++) {
          if ($i === 0) {
              $body .= '
            </tbody>
          </table>
        </td>
      </tr>
    </table>
    <p class="medium center">Continued on page ' . $this->counter . '</p>
    <p class="pageBreak"></p>
    <table class="invoiceBody wide">
      <tbody>
        <tr>
          <td style="border:none;">
            <table class="wide">
              <tr>
                <th scope="col">Invoice</th>
                <th scope="col">Issued</th>
                <th scope="col">Subtotal</th>
                <th scope="col">Client</th>
                <th scope="col">Page</th>
              </tr>
              <tr>
                <td>' . $this->InvoiceNumber . '</td>
                <td>' . date('d M Y', strtotime($this->DateIssued)) . '</td>
                <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>'. self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</td>
                <td>' . $this->members[$this->ClientID]->getProperty('ClientName') . '</td>
                <td>' . $this->counter . '</td>
              </tr>
              <tr class="smallTableSpace">
                <td colspan="5"></td>
              </tr>
            </table>
            <table class="wide">
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Ticket #</th>
                <th scope="col">Charge</th>
                <th scope="col" colspan="2">Description</th>
                <th scope="col">Price</th>
                <th scope="col">#</th>
                <th scope="col">Line Price</th>
              </tr>';
            $body .= $morePages[$i]->invoiceBody();
          } else {
            if ($i % $this->invoicePageMax === 0) {
              $this->counter++;
              $body .= '
            </tbody>
          </table>
        </td>
      </tr>
    </table>
    <p class="medium center">Continued on page ' . $this->counter . '</p>
    <p class="pageBreak"></p>
    <table class="invoiceBody wide">
      <tbody>
        <tr>
          <td style="border:none;">
            <table class="wide">
              <tr>
                <th scope="col">Invoice</th>
                <th scope="col">Issued</th>
                <th scope="col">Subtotal</th>
                <th scope="col">Client</th>
                <th scope="col">Page</th>
              </tr>
              <tr>
                <td>' . $this->InvoiceNumber . '</td>
                <td>' . date('d M Y', strtotime($this->DateIssued)) . '</td>
                <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</td>
                <td>' . $this->members[$this->ClientID]->getProperty('ClientName') . '</td>
                <td>' . $this->counter . '</td>
              </tr>
              <tr class="smallTableSpace">
                <td colspan="5"></td>
              </tr>
            </table>
            <table class="wide">
              <tr>
                <th scope="col">Date</th>
                <th scope="col">Ticket #</th>
                <th scope="col">Charge</th>
                <th scope="col" colspan="2">Description</th>
                <th scope="col">Price</th>
                <th scope="col">#</th>
                <th scope="col">Line Price</th>
              </tr>';
              $body .= $morePages[$i]->invoiceBody();
            } else {
              $body .= '
              <tr>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col" colspan="2"></th>
                <th scope="col"></th>
                <th scope="col"></th>
                <th scope="col"></th>
              </tr>';
              $body .= $morePages[$i]->invoiceBody();
            }
            if ($i === count($morePages) - 1) {
              $body .= '
              <tr>
                <td colspan="5" style="border:none;"></td>
                <th scope="row" colspan="2" style="border:1px solid black; white-space:nowrap;">Subtotal:</th>
                <td style="border:1px solid black;"><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</td>
              </tr>';
            }
          }
        }
      }
      $body .= '
            </table>';
      return $body;
    }

    public function regenInvoice() {
      if (count($this->invoiceQueryResult) > 1) {
        return self::multiInvoiceForm();
      } else {
        foreach ($this->invoiceQueryResult[0] as $key => $value) {
          if ($value !== NULL) self::updateProperty($key, $value);
        }
      }

      $this->ClientID = ($this->RepeatClient === 1) ? $this->ClientID : "t{$this->ClientID}";

      $this->showCanceledTicketsOnInvoice = in_array($this->ClientID, $this->options['showCanceledTicketsOnInvoiceExceptions'], true);

      $this->consolidateContractTicketsOnInvoice = !in_array($this->ClientID, $this->options['consolidateContractTicketsOnInvoiceExceptions'], true);

      self::fetchInvoiceTickets();

      if ($this->Tickets === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      // Check for ticket consolidation request
      if ($this->consolidateContractTicketsOnInvoice === TRUE) {
        self::consolidateTickets();
        if ($this->ConsolidatedTickets === FALSE) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return FALSE;
        }
      }

      if ($this->Closed === 1) {
        // Format the payment method display
        $paymentDisplay = (is_numeric($this->CheckNumber)) ? 'Check #: ' : '';
        $closedMarker = '
          <td id="paid" colspan="4" rowspan="6">
            <p class="paid">PAID</p>
            <p>' . date('d M Y', strtotime($this->DatePaid)) . '</p>
            <p class="center">' . $paymentDisplay . $this->CheckNumber . '</p>
          </td>';
      } else {
        $closedMarker = '
          <td colspan="4" rowspan="6"></td>';
      }

      $this->invoiceDisplay = '
  <div id="invoice">
    <table class="wide">
      <tbody>
        <tr>
          <td class="pullLeft" colspan="4" style="vertical-align: middle;">
          <div>' . $this->headerLogo . '</div>
          </td>
          <td class="pullRight" colspan="4" id="invoiceLabel2">invoice</td>
        </tr>
        <tr>
          <td colspan="8" class="smallTableSpace"><hr></td>
        </tr>
        <tr>
          <td class="big pullLeft" colspan="2">' . $this->config['ClientName'] . '</td>' . $closedMarker . '
          <td colspan="2" rowspan="6" class="pullLeft alignTop">
            <div id="floatRight">
              <span>Invoice #:  ' . $this->InvoiceNumber . '</span>
              <br>
              <span>Start Date:  ' . date('d M Y', strtotime($this->StartDate)) . '</span>
              <br>
              <span>End Date:  ' . date('d M Y', strtotime($this->EndDate)) . '</span>
              <br>
              <span>Date Issued:  ' . date('d M Y', strtotime($this->DateIssued)) . '</span>
              <br>
              <span>Client ID:  ' . $this->members[$this->ClientID]->getProperty('ClientID') . '</span>
              <br>
              <span>' . $this->members[$this->ClientID]->getProperty('ClientName') . '</span>
              <br>
              <span>' . $this->members[$this->ClientID]->getProperty('Department'). '</span>
              <br>
              <span>Subtotal: <span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</span>
            </div>
          </td>
        </tr>
        <tr>
          <td class="pullLeft" colspan="2">' . $this->config['BillingAddress1'] . '</td>
        </tr>
        <tr>
          <td class="pullLeft" colspan="2">' . $this->config['BillingAddress2'] . '</td>
        </tr>
        <tr class="' .  $this->countryClass . '">
          <td class="pullLeft" colspan="2">' . self::countryFromAbbr($this->config['BillingCountry']) . '</td>
        </tr>
        <tr>
          <td class="pullLeft" colspan="2">' .  $this->config['Telephone'] . '</td>
        </tr>
        <tr>
          <td class="pullLeft" colspan="2">' .  $this->config['EmailAddress'] . '</td>
        </tr>
        <tr>
          <td class="pullLeft" style="padding-top:1em;" colspan="4">
            <span style="color:#8c8c89">Bill<br>To: </span>' . $this->members[$this->ClientID]->getProperty('BillingName') . '
              <br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Attention:  ' . $this->members[$this->ClientID]->getProperty('Attention') . '
              <br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->members[$this->ClientID]->getProperty('BillingAddress1') . '
              <br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->members[$this->ClientID]->getProperty('BillingAddress2') . '<span class="' . $this->countryClass . '">
              <br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . self::countryFromAbbr($this->members[$this->ClientID]->getProperty('BillingCountry')) . '
            </span>
          </td>
        </tr>
      </tbody>
    </table>
    <table class="invoiceBody wide">
      <tbody>
        <tr class="smallTableSpace">
          <td style="border:none;"></td>
        </tr>
        <tr>
          <td style="border:none;">';
        $this->invoiceDisplay .= self::invoiceBodyTickets();
		    $this->invoiceDisplay .= '
          </td>
        </tr>
        <tr>
          <td class="smallTableSpace" style="border:none;"></td>
        </tr>
      </tbody>
    </table>
    <table class="invoiceBody wide">
      <tfoot>
        <tr>
          <td colspan="7" style="padding:0px;border:none;">
            <p class="center medium">Payment is due upon receipt.</p>
            <p class="center medium">Make all checks payable to ' . $this->config['ClientName'] . '.</p>
            <p class="center bold medium">Thank You for your business!</p>
          </td>
        </tr>
      </tfoot>
    <tbody>
      <tr>
        <th scope="col">Current</th>
        <th scope="col">30 Days<br>Past Due</th>
        <th scope="col">60 Days<br>Past Due</th>
        <th scope="col">90 Days<br>Past Due</th>
        <th scope="col">120+ Days<br>Past Due</th>
        <th scope="col">Credit</th>
        <th scope="col">Amount<br>Due</th>
      </tr>
      <tr>
        <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</td>
        <td><span class="currencySymbol">' .  $this->config['CurrencySymbol'] . '</span>' .  self::number_format_drop_zero_decimals($this->Late30Value, 2) . '</td>
        <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::number_format_drop_zero_decimals($this->Late60Value, 2) . '</td>
        <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::number_format_drop_zero_decimals($this->Late90Value, 2) . '</td>
        <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::number_format_drop_zero_decimals($this->Over90Value, 2) . '</td>
        <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->BalanceForwarded, 2)) . '</td>
        <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::number_format_drop_zero_decimals($this->InvoiceTotal, 2) . '</td>
      </tr>
      <tr>
        <th scope="row" style="border:none;">' . $this->InvoiceNumber . '</th>
        <td>' . $this->Late30Invoice . '</td>
        <td>' . $this->Late60Invoice . '</td>
        <td>' . $this->Late90Invoice . '</td>
        <td colspan="3">' . $this->Over90Invoice . '</td>
      </tr>
    </tbody>
  </table>
</div>';
      return $this->invoiceDisplay;
    }

    public function invoiceQueryForm() {
      if ($this->ulevel === 1) {
        return '
            <table id="queryForms" class="noPrint centerDiv">
              <tr>
                <td>
                  <form id="singleInvoiceQuery" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                    <input type="hidden" name="clientID" value="' . $_SESSION['ClientID'] . '" />
                    <input type="hidden" name="repeatClient" value="' . $this->RepeatClient . '" />
                    <input type="hidden" name="method" value="GET" />
                    <input type="hidden" name="endPoint" value="invoices" />
                    <input type="hidden" name="display" value="invoice" />
                    <fieldset form="invoiceQuery" name="dateRange">
                      <legend>Single Invoice Query</legend>
                      <table>
                        <tr>
                          <td><label for="dateIssued">Date Issued:  </label></td>
                          <td class="pullLeft">' . self::createLimitedMonthInput($_SESSION['ClientID'], 'dateIssued', FALSE, 'month', 'invoices', TRUE) . '</td>
                        </tr>
                        <tr>
                          <td><label for="invoiceNumber">Invoice Number:  </label></td>
                          <td class="pullLeft">
                            <input type="checkbox" id="useInvoice" value="1" />
                            ' . self::createInvoiceNumberSelect($_SESSION['ClientID']) . '
                          </td>
                        </tr>
                        <tr>
                          <td><span class="medium">*Open Invoice</span></td>
                          <td class="pullRight"><button type="submit" id="singleInvoice">Query</button></td>
                        </tr>
                      </table>
                    </fieldset>
                  </form>
                </td>
                <td>
                  <form id="multiInvoiceQuery" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                    <input type="hidden" name="clientID" value="' . $_SESSION['ClientID'] . '" />
                    <input type="hidden" name="repeatClient" value="' . $this->RepeatClient . '" />
                    <input type="hidden" name="method" value="GET" />
                    <input type="hidden" name="endPoint" value="invoices" />
                    <input type="hidden" name="display" value="chart" />
                    <fieldset>
                      <legend>Multi Invoice Query</legend>
                      <table>
                        <tr>
                          <td><label for="startDate">Start Date:</label></td>
                          <td>' . self::createLimitedMonthInput($_SESSION['ClientID'], 'startDate', FALSE, 'month', 'invoices', TRUE) . '</td>
                        </tr>
                        <tr>
                          <td><label for="endDate">End Date:</label></td>
                          <td>' . self::createLimitedMonthInput($_SESSION['ClientID'], 'endDate', FALSE, 'month', 'invoices', TRUE). '</td>
                        </tr>
                        <tr>
                          <td colspan="2" title="Range limited to 6 months.">
                            <label for="compareInvoices">Compare:  </label>
                            <select id="compareInvoices" name="compare">
                              <option value="0">Date Range</option>
                              <option value="1">Two Months</option>
                            </select>
                            <button type="submit" id="rangeInvoice">Query</button>
                          </td>
                        </tr>
                      </table>
                    </fieldset>
                    <input type="hidden" name="compareMembers" value="0" />
                  </form>
                  <span id="message2" class="error"></span>
                </td>
              </tr>
            </table>
            <div id="invoiceQueryResults"></div>';
      }
      if ($this->ulevel === 0) {
        return '<table id="queryForms" class="noPrint centerDiv">
              <thead>
                <tr>
                  <td id="message" colspan="2"></td>
                </tr>
              </thead>
              <tfoot>
                <tr>
                  <td colspan="2">' . self::listOrgMembers('invoice') .'</td>
                </tr>
              </tfoot>
              <tbody>
                <tr>
                  <td>
                    <form id="singleInvoiceQuery" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                      <fieldset form="invoiceQuery" name="dateRange">
                        <input type="hidden" name="method" value="GET" />
                        <input type="hidden" name="endPoint" value="invoices" />
                        <input type="hidden" name="display" value="invoice" />
                        <input type="hidden" name="single" value="0" />
                        <legend><label for="single">Single Invoice Query </label><input title="Regenerate an invoice as issued" type="checkbox" name="single" id="single" value="1" checked /></legend>
                        <table>
                          <tr>
                            <td><label for="dateIssued">Date Issued:</label></td>
                            <td class="pullLeft">' . self::createLimitedMonthInput(array_keys($_SESSION['members']), 'dateIssued', FALSE, 'month', 'invoices', TRUE) . '</td>
                          </tr>
                          <tr>
                            <td colspan="2">&nbsp;</td>
                          </tr>
                          <tr>
                            <td><button type="submit" id="submitSingle" disabled>Query</button></td>
                          </tr>
                        </table>
                      </fieldset>
                    </form>
                  </td>
                  <td>
                    <form id="multiInvoiceQuery" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                      <fieldset>
                        <input type="hidden" name="method" value="GET" />
                        <input type="hidden" name="endPoint" value="invoices" />
                        <input type="hidden" name="display" value="chart" />
                        <input type="hidden" name="multi" value="0" />
                        <legend><label for="multi">Multi Invoice Query </label><input title="Generate charts comparing data points&#10;For any 2 months or 6 month range." type="checkbox" name="multi" id="multi" value="1" /></legend>
                        <table>
                          <tr>
                            <td class="pullLeft"><label for="invoiceStartDateMonth">Start Date:  </label></td>
                            <td>' . self::createLimitedMonthInput(array_keys($_SESSION['members']), 'invoiceStartDate'). '</td>
                            <td colspan="2" class="center bold">Compare</td>
                          </tr>
                          <tr>
                            <td class="pullLeft"><label for="invoiceEndDateMonth">End Date:  </label></td>
                            <td class="pullLeft">' . self::createLimitedMonthInput(array_keys($_SESSION['members']), 'invoiceEndDate') . '</td>
                            <td class="center">
                              <label for="compareInvoices">Months:</label>
                              <input type="hidden" name="compare" value="0" />
                              <input type="checkbox" name="compare" id="compareInvoices" value="1" />
                            </td>
                            <td class="center">
                              <label for="compareMembers">Members:</label>
                              <input type="hidden" name="compareMembers" value="0" />
                              <input type="checkbox" name="compareMembers" id="compareMembers" value="1" disabled />
                            </td>
                          </tr>
                          <tr>
                            <td colspan="4">
                              <button type="submit" id="range" disabled>Query</button>
                            </td>
                          </tr>
                        </table>
                      </fieldset>
                    </form>
                    <span id="message2" class="error"></span>
                  </td>
                </tr>
              </tbody>
            </table>
            <div id="invoiceQueryResults"></div>';
      }
    }
  }
