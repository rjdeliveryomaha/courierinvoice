<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

  class Invoice extends CommonFunctions
  {
    protected $invoice_index;
    protected $InvoiceNumber;
    protected $InvoiceTerms;
    protected $DiscountRate;
    protected $DiscountWindow;
    protected $TermLength;
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
    protected $Deleted;
    protected $consolidateContractTicketsOnInvoice = true;
    protected $showCanceledTicketsOnInvoice = false;
    protected $tickets;
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
    protected $renderPDF;
    private $repeat;
    private $pastDueData = [];
    private $paymentDisplay;
    private $closedMarker;
    private $invoiceDisplay;
    private $counter = 2;
    private $totalDeliveryVAT;
    private $totalIceVAT;
    private $totalVAT;
    private $updateValues = [ 'InvoiceNumber', 'ClientID', 'RepeatClient', 'InvoiceTotal', 'InvoiceSubTotal',
      'BalanceForwarded', 'AmountDue', 'StartDate', 'EndDate', 'DateIssued', 'DatePaid', 'AmountPaid', 'Balance',
      'Late30Invoice', 'Late30Value', 'Late60Invoice', 'Late60Value', 'Late90Invoice', 'Late90Value', 'Over90Invoice',
      'Over90Value', 'CheckNumber', 'Closed', 'Deleted'
    ];

    public function __construct($options, $data=[])
    {
      try {
        parent::__construct($options, $data);
      } catch (\Exception $e) {
        throw $e;
      }
    }

    private function displayTerms()
    {
      if ($this->InvoiceTerms <= 1 || $this->InvoiceTerms > 4) return 'Due Upon Receipt';
      if ($this->InvoiceTerms == 2 || $this->InvoiceTerms == 3) {
        $eom = ($this->InvoiceTerms == 2) ? '' : 'EOM ';
        return ($this->TermLength < 1) ? 'Due Upon Receipt' : "Net $eom{$this->TermLength}";
      }
      if ($this->InvoiceTerms == 4) {
        if ($this->DiscountRate <= 0 || $this->DiscountWindow <= 0 || $this->TermLength < 1) return 'Due Upon Receipt';
        return "{$this->DiscountRate}/{$this->DiscountWindow} Net {$this->TermLength}";
      }
    }

    private function multiInvoiceForm()
    {
      $returnData = '
        <p data-error="error" class="center">Multiple invoices available for ' . date('F Y', strtotime($this->invoiceQueryResult[0]['DateIssued'])) . '.</p>
        <form class="center" id="multiInvoiceForm" method="post" action="/">
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

    private function consolidateTickets()
    {
      //Merge contract tickets
      foreach ($this->tickets as $ticket) {
        if (self::test_bool($ticket->getProperty('Contract')) === true) {
          if ($ticket->getProperty('Charge') !== 0) {
            if (empty($this->ConsolidatedTickets)) {
              $this->ConsolidatedTickets[] = clone $ticket;
            } else {
              $add = true;
              for ($i = 0; $i < count($this->ConsolidatedTickets); $i++) {
                if (self::compareProperties($ticket, $this->ConsolidatedTickets[$i], 'RunNumber') === true) {
                  $add = false;
                  break;
                }
              }
              if ($add === true) {
                $this->ConsolidatedTickets[] = clone $ticket;
              } else {
                foreach ($this->ConsolidatedTickets as $test) {
                  if ($ticket->getProperty('RunNumber') === $test->getProperty('RunNumber')) {
                    if (!$test->addToProperty('Multiplier', $ticket->getProperty('Multiplier'))) {
                      $temp = $this->error . "\n";
                      $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                      if ($this->enableLogging !== false) self::writeLoop();
                      $this->ConsolidatedTickets = false;
                      return false;
                    }
                    if (!$test->addToProperty('TicketPrice', $ticket->getProperty('TicketPrice'))) {
                      $temp = $this->error . "\n";
                      $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                      if ($this->enableLogging !== false) self::writeLoop();
                      $this->ConsolidatedTickets = false;
                      return false;
                    }
                    if (!$test->updateProperty('ReceivedDate', $ticket->getProperty('ReceivedDate'))) {
                      $temp = $this->error . "\n";
                      $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                      if ($this->enableLogging !== false) self::writeLoop();
                      $this->ConsolidatedTickets = false;
                      return false;
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

    private function invoiceBodyTickets()
    {
      if ($this->config['ApplyVAT'] === true) {
        foreach ($this->tickets as $ticket) {
          $ticket->updateProperty('renderPDF', $this->renderPDF);
          if (0 < $ticket->getProperty('Charge') && $ticket->getProperty('Charge') < 9) {
            $temp = $ticket->getProperty('RunPrice') * (1 + ($ticket->getProperty('VATrate') / 100));
            $this->totalDeliveryVAT = $temp - $ticket->getProperty('RunPrice');
            $this->totalVAT += $this->totalDeliveryVAT;
            $temp = $ticket->getProperty('diPrice') * (1 + ($ticket->getProperty('VATrateIce') / 100));
            $this->totalIceVAT = $temp - ($ticket->getProperty('diWeight') * $_SESSION['config']['diPrice']);
            $this->totalVAT += $this->totalIceVAT;
          }
        }
      }
      // Check for ticket consolidation request
      if ($this->consolidateContractTicketsOnInvoice === true) {
        $ticketSet = $this->ConsolidatedTickets;
      } else {
        $ticketSet = $this->tickets;
      }
      $body = '
            <table class="wide invoiceBody">';
      $page1 = $morePages = $filteredTicketSet = [];
      $pageCount = 0;
      if ($this->showCanceledTicketsOnInvoice === false) {
        foreach ($ticketSet as $filtered) {
          if ($filtered->getProperty('Charge') !== 0) {
            $filteredTicketSet[] = $filtered;
          }
        }
      } else {
        $filteredTicketSet = $ticketSet;
      }
      $displayName = $this->members[$this->ClientID]->getProperty('ClientName');
      $displayName .= ($this->members[$this->ClientID]->getProperty('Department') !== null) ?
        "; {$this->members[$this->ClientID]->getProperty('Department')}" : '';

      switch (strtolower($this->options['paperFormat'])) {
        case 'a4':
          if (strtolower($this->options['paperOrientation']) === 'landscape') {
            $this->invoicePage1Max = (count($filteredTicketSet) === 5) ? 4 : 5;
            $this->invoicePageMax = 7;
          } else {
            $this->invoicePage1Max = (count($filteredTicketSet) === 9) ? 8 : 9;
            $this->invoicePageMax = 11;
          }
          break;
        case 'legal':
          if (strtolower($this->options['paperOrientation']) === 'landscape') {
            $this->invoicePage1Max = (count($filteredTicketSet) === 5) ? 4 : 5;
            $this->invoicePageMax = 7;
          } else {
            $this->invoicePage1Max = (count($filteredTicketSet) === 12) ? 11 : 12;
            $this->invoicePageMax = 14;
          }
          break;
        case 'letter':
        default:
          if (strtolower($this->options['paperOrientation']) === 'landscape') {
            $this->invoicePage1Max = (count($filteredTicketSet) === 5) ? 4 : 5;
            $this->invoicePageMax = 7;
          } else {
            $this->invoicePage1Max = (count($filteredTicketSet) === 8) ? 7 : 8;
            $this->invoicePageMax = 10;
          }
          break;
      }
      $page1 = array_slice($filteredTicketSet,0,$this->invoicePage1Max);
      $morePages = array_slice($filteredTicketSet,$this->invoicePage1Max);
      $singlePage = empty($morePages);
      if ($singlePage === false) {
        $pages = array_chunk($morePages, $this->invoicePageMax);
        $index = count($pages) - 1;
        if (count($pages[$index]) === $this->invoicePageMax) {
          $pages[] = [ array_pop($pages[$index]) ];
        }
        $pageCount = count($pages) + 1;
      }
      for ($i = 0; $i < count($page1); $i++) {
        if ($i === 0) {
          $body .= '
              <tr>
                <th>Date</th>
                <th>Ticket #</th>
                <th>Charge</th>
                <th colspan="2">Description</th>
                <th>Price</th>
                <th>#</th>
                <th>Line Price</th>
              </tr>' .
              $page1[$i]->invoiceBody();
        } else {
          $body .= '
              <tr>
                <th></th>
                <th></th>
                <th></th>
                <th colspan="2"></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>' .
              $page1[$i]->invoiceBody();
        }
      }
      if ($singlePage === true) {
        if ($this->config['ApplyVAT'] === true) {
          $body .= '
              <tr>
                <td colspan="5" style="border: none;"></td>
                <th colspan="2" style="border: 0.1rem solid black;">VAT:</th>
                <td style="border: 0.1rem solid black;">
                  <span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::number_format_drop_zero_decimals($this->totalVAT, 2) . '
                </td>
              </tr>';
        }
        $body .= '
              <tr>
                <td colspan="5" style="border:none;"></td>
                <th colspan="2" style="border:0.1rem solid black; white-space:nowrap;">Subtotal:</th>
                <td style="border:0.1rem solid black;">
                  <span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '
                </td>
              </tr>';
      } else {
        $body .= '
            </table>
            <p class="medium center">1 / ' . $pageCount . '</p>';
        foreach ($pages as $page) {
          for ($i = 0; $i < count($page); $i++) {
            if ($i === 0) {
              $body .= '
            <p class="pageBreak"></p>
            <table class="wide invoiceBody">
              <tr>
                <th>Invoice</th>
                <th>Issued</th>
                <th>Subtotal</th>
                <th>Terms</th>
                <th>Client</th>
                <th>Page</th>
              </tr>
              <tr>
                <td>' . $this->InvoiceNumber . '</td>
                <td>' . date('d M Y', strtotime($this->DateIssued)) . '</td>
                <td>
                  <span class="currencySymbol">' . $_SESSION['config']['CurrencySymbol'] . '</span>'. self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '
                </td>
                <td>' . self::displayTerms() . '</td>
                <td>' . $displayName . '</td>
                <td>' . $this->counter . ' / ' . $pageCount . '</td>
              </tr>
            </table>
            <p class="smallTableSpace"></p>
            <table class="wide invoiceBody">
              <tr>
                <th>Date</th>
                <th>Ticket #</th>
                <th>Charge</th>
                <th colspan="2">Description</th>
                <th>Price</th>
                <th>#</th>
                <th>Line Price</th>
              </tr>';
            }
            $body .= $page[$i]->invoiceBody();
            $body .= '
              <tr>
                <th colspan="8"></th>
              </tr>';
            if ($i === count($page) - 1) {
              if ($this->config['ApplyVAT'] === true) {
                $body .= '
              <tr>
                <td colspan="5" style="border: none;"></td>
                <th colspan="2" style="border: 0.1rem solid black;">VAT:</th>
                <td style="border: 0.1rem solid black;">
                  <span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::number_format_drop_zero_decimals($this->totalVAT, 2) . '
                </td>
              </tr>';
              }
                $body .= '
              <tr>
                <td colspan="5" style="border:none;"></td>
                <th colspan="2" style="border:1px solid black; white-space:nowrap;">Subtotal:</th>
                <td style="border:1px solid black;">
                  <span class="currencySymbol">' . $_SESSION['config']['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '
                </td>
              </tr>';
            }
          }
          $body .= '
            </table>';
          $this->counter++;
        }
      }
      return $body;
    }

    public function regenInvoice()
    {
      if (count($this->invoiceQueryResult) > 1) {
        return self::multiInvoiceForm();
      } else {
        foreach ($this->invoiceQueryResult[0] as $key => $value) {
          self::updateProperty($key, $value);
        }
      }

      $this->ClientID = (self::test_bool($this->RepeatClient) === true) ? $this->ClientID : "t{$this->ClientID}";

      $this->showCanceledTicketsOnInvoice =
        in_array($this->ClientID, $this->options['showCanceledTicketsOnInvoiceExceptions'], true);

      $this->consolidateContractTicketsOnInvoice =
        !in_array($this->ClientID, $this->options['consolidateContractTicketsOnInvoiceExceptions'], true);

      $temp = [];
      for ($i = 0; $i < count($this->tickets); $i++) {
        if (!$temp[] = self::createTicket($this->tickets[$i])) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          $this->Tickets = false;
          return false;
        }
      }
      $this->tickets = $temp;
      // Check for ticket consolidation request
      if ($this->consolidateContractTicketsOnInvoice === true) {
        self::consolidateTickets();
        if ($this->ConsolidatedTickets === false) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          return false;
        }
      }

      if (self::test_bool($this->Closed) === true) {
        // Format the payment method display
        $paymentDisplay = (is_numeric($this->CheckNumber)) ? 'Check #: ' : '';
        $closedMarker = '
          <div id="paid">
            PAID<br>
            ' . date('d M Y', strtotime($this->DatePaid)) . '<br>
            ' . $paymentDisplay . $this->CheckNumber . '
          </div>';
      } else {
        $closedMarker = '';
      }

      $invoiceAddress1 = $this->config['BillingAddress1'] ?? $this->config['ShippingAddress1'];

      $invoiceAddress2 = $this->config['BillingAddress2'] ?? $this->config['ShippingAddress2'];

      $invoiceCountry = $this->config['BillingCountry'] ?? $this->config['ShippingCountry'];

      $billingName =  $this->members[$this->ClientID]->getProperty('BillingName') ??
        $this->members[$this->ClientID]->getProperty('ClientName');

      $billingAddress1 = $this->members[$this->ClientID]->getProperty('BillingAddress1') ??
        $this->members[$this->ClientID]->getProperty('ShippingAddress1');

      $billingAddress2 =  $this->members[$this->ClientID]->getProperty('BillingAddress2') ??
        $this->members[$this->ClientID]->getProperty('ShippingAddress2');

      $billingCountry = $this->members[$this->ClientID]->getProperty('BillingCountry') ??
        $this->members[$this->ClientID]->getProperty('ShippingCountry');

      $flagedForDeletion = (self::test_bool($this->Deleted) === true) ?
        '<p class="center small" style="z-index: 1;">Flagged For Deletion.</p>' : '';

      $pdfForm = (class_exists('Dompdf\Dompdf')) ? "
        <form id=\"invoicePDFform\" target=\"_blank\" method=\"post\" action=\"pdf\">
          <input type=\"hidden\" name=\"invoiceNumber\" value=\"{$this->InvoiceNumber}\" form=\"invoicePDFform\" />
          <input type=\"hidden\" name=\"type\" value=\"invoice\" form=\"invoicePDFform\" />
          <input type=\"hidden\" name=\"formKey\" form=\"invoicePDFform\" />
          <input type=\"hidden\" name=\"content\" form=\"invoicePDFform\" />
          <button type=\"button\" id=\"invoicePDF\" form=\"invoicePDFform\">PDF</button>
        </form>" : '';

      if ($this->renderPDF === true) {
        $this->invoiceDisplay = "
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
    <meta name=\"viewport\" content=\"width=device-width\">
    <title>{$this->InvoiceNumber}</title>
    <link rel=\"stylesheet\" href=\"../style/pdfInvoice.css\" type=\"text/css\">
    <link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"../apple-touch-icon.png\">
    <link rel=\"icon\" type=\"image/png\" href=\"../favicon-32x32.png\" sizes=\"32x32\">
    <link rel=\"icon\" type=\"image/png\" href=\"../favicon-16x16.png\" sizes=\"16x16\">
  </head>
  <body>";
      } else {
        $this->invoiceDisplay = '';
      }

      $this->invoiceDisplay .= '
  <div id="invoice">
    ' . $this->headerLogo .
    $pdfForm . '
    <div id="invoiceLabel2">invoice</div>
    <hr>
    <div class="invoiceHead">
      <div>
        <p class="big">' . $this->config['ClientName'] . '</p>
        <p>' . $invoiceAddress1 . '</p>
        <p>' . $invoiceAddress2 . '</p>
        </tr>
        <p class="' .  $this->countryClass . '">' . self::countryFromAbbr($invoiceCountry) . '</p>
        <p>' .  $this->config['Telephone'] . '</p>
        <p>' .  $this->config['EmailAddress'] . '</p>
        <p id="billToLabel">Bill<br>To: </p>
        <div id="invoiceAddress">
          <p>' . $billingName . '</p>
          <p>Attention: ' . $this->members[$this->ClientID]->getProperty('Attention') . '</p>
          <p>' . $billingAddress1 . '</p>
          <p>' .$billingAddress2 . '</p>
          <p class="' . $this->countryClass . '">' . self::countryFromAbbr($billingCountry) . '</p>
        </div>
      </div>
      ' . $closedMarker . '
      <div>
        <p>Invoice #: ' . $this->InvoiceNumber . '</p>
        <p>Start Date: ' . date('d M Y', strtotime($this->StartDate)) . '</p>
        <p>End Date: ' . date('d M Y', strtotime($this->EndDate)) . '</p>
        <p>Date Issued: ' . date('d M Y', strtotime($this->DateIssued)) . '</p>
        <p>Client ID: ' . $this->members[$this->ClientID]->getProperty('ClientID') . '</p>
        <p>' . $this->members[$this->ClientID]->getProperty('ClientName') . '</p>
        <p>' . $this->members[$this->ClientID]->getProperty('Department'). '</p>
        <p>Subtotal: <span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</p>
        <p>Terms: ' . self::displayTerms() . '</p>
      </div>
    </div>'
    . $flagedForDeletion .
    '<div class="smallTableSpace"></div>';
      $this->invoiceDisplay .= self::invoiceBodyTickets();
		  $this->invoiceDisplay .= '
    <table class="invoiceBody wide" style="margin-top: 1rem;">
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
          <td><span class="currencySymbol">' .
            $this->config['CurrencySymbol'] . '</span>' .
            self::negParenth(self::number_format_drop_zero_decimals($this->InvoiceSubTotal, 2)) . '</td>
          <td><span class="currencySymbol">' .
            $this->config['CurrencySymbol'] . '</span>' .
            self::number_format_drop_zero_decimals($this->Late30Value, 2) . '</td>
          <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] .
            '</span>' . self::number_format_drop_zero_decimals($this->Late60Value, 2) . '</td>
          <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] .
            '</span>' . self::number_format_drop_zero_decimals($this->Late90Value, 2) . '</td>
          <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] .
            '</span>' . self::number_format_drop_zero_decimals($this->Over90Value, 2) . '</td>
          <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] .
            '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->BalanceForwarded, 2)) . '</td>
          <td><span class="currencySymbol">' . $this->config['CurrencySymbol'] .
            '</span>' . self::number_format_drop_zero_decimals($this->InvoiceTotal, 2) . '</td>
        </tr>
        <tr>
          <th style="border:none;">' . $this->InvoiceNumber . '</th>
          <td>' . $this->Late30Invoice . '</td>
          <td>' . $this->Late60Invoice . '</td>
          <td>' . $this->Late90Invoice . '</td>
          <td colspan="3">' . $this->Over90Invoice . '</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="7" style="padding:0px;border:none;">
            <p class="center medium">Make all checks payable to ' . $this->config['ClientName'] . '.</p>
            <p class="center bold medium">Thank You for your business!</p>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>';
      return $this->invoiceDisplay;
    }

    public function invoiceQueryForm()
    {
      if ($this->ulevel === 1) {
        return '
            <table id="invoiceQueryOptions" class="noPrint centerDiv">
              <tr>
                <td>
                  <form id="singleInvoiceQuery" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                    <input type="hidden" name="clientID" value="' . $_SESSION['ClientID'] . '" form="singleInvoiceQuery" />
                    <input type="hidden" name="repeatClient" value="' . (int)$this->RepeatClient . '" form="singleInvoiceQuery" />
                    <input type="hidden" name="method" value="GET" form="singleInvoiceQuery" />
                    <input type="hidden" name="endPoint" value="invoices" form="singleInvoiceQuery" />
                    <input type="hidden" name="display" value="invoice" form="singleInvoiceQuery" />
                    <fieldset form="singleInvoiceQuery" name="dateRange">
                      <legend>Single Invoice Query</legend>
                      <table>
                        <tr>
                          <td><label for="dateIssued">Date Issued:</label></td>
                          <td class="pullLeft">' .
                            self::createLimitedMonthInput([
                              'clientIDs' => $_SESSION['ClientID'],
                              'inputID' => 'dateIssued',
                              'type' => 'month',
                              'required' => true,
                              'form' => 'singleInvoiceQuery'
                            ]) .
                          '</td>
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
                          <td class="pullRight"><button type="submit" id="singleInvoice" form="singleInvoiceQuery">Query</button></td>
                        </tr>
                      </table>
                    </fieldset>
                  </form>
                </td>
                <td>
                  <form id="multiInvoiceQuery" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                    <input type="hidden" name="clientID" value="' . $_SESSION['ClientID'] . '" form="multiInvoiceQuery" />
                    <input type="hidden" name="repeatClient" value="' . (int)$this->RepeatClient . '" form="multiInvoiceQuery" />
                    <input type="hidden" name="method" value="GET" form="multiInvoiceQuery" />
                    <input type="hidden" name="endPoint" value="invoices" form="multiInvoiceQuery" />
                    <input type="hidden" name="display" value="chart" form="multiInvoiceQuery" />
                    <fieldset form="multiInvoiceQuery">
                      <legend>Multi Invoice Query</legend>
                      <table>
                        <tr>
                          <td><label for="startDate">Start Date:</label></td>
                          <td>' .
                            self::createLimitedMonthInput([
                              'clientIDs' => $_SESSION['ClientID'],
                              'inputID' => 'startDate',
                              'type' => 'month',
                              'required' => true,
                              'form' => 'multiInvoiceQuery'
                            ]) .
                          '</td>
                        </tr>
                        <tr>
                          <td><label for="endDate">End Date:</label></td>
                          <td>' .
                            self::createLimitedMonthInput([
                              'clientIDs' => $_SESSION['ClientID'],
                              'inputID' => 'endDate',
                              'type' => 'month',
                              'required' => true,
                              'form' => 'multiInvoiceQuery'
                            ]).
                          '</td>
                        </tr>
                        <tr>
                          <td colspan="2" title="Range limited to 6 months.">
                            <label for="compareInvoices">Compare:  </label>
                            <select id="compareInvoices" name="compare" form="multiInvoiceQuery">
                              <option value="0">Date Range</option>
                              <option value="1">Two Months</option>
                            </select>
                            <button type="submit" id="rangeInvoice" form="multiInvoiceQuery">Query</button>
                          </td>
                        </tr>
                      </table>
                    </fieldset>
                    <input type="hidden" name="compareMembers" value="0" form="multiInvoiceQuery" />
                  </form>
                  <span id="message2" class="error"></span>
                </td>
              </tr>
            </table>
            <div id="invoiceQueryResults"></div>';
      }
      if ($this->ulevel === 0) {
        return '<table id="invoiceQueryOptions" class="noPrint centerDiv">
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
                      <fieldset form="singleInvoiceQuery" name="dateRange">
                        <input type="hidden" name="method" value="GET" form="singleInvoiceQuery" />
                        <input type="hidden" name="endPoint" value="invoices" form="singleInvoiceQuery" />
                        <input type="hidden" name="display" value="invoice" form="singleInvoiceQuery" />
                        <input type="hidden" name="single" value="0" form="singleInvoiceQuery" />
                        <legend><label for="single">Single Invoice Query </label><input title="Regenerate an invoice as issued" type="checkbox" name="single" id="single" value="1" form="singleInvoiceQuery" checked /></legend>
                        <table>
                          <tr>
                            <td><label for="dateIssued">Date Issued:</label></td>
                            <td class="pullLeft">' .
                              self::createLimitedMonthInput([
                                'clientIDs' => array_keys($_SESSION['members']),
                                'inputID' => 'dateIssued',
                                'type' => 'month',
                                'required' => true,
                                'form' => 'singleInvoiceQuery'
                              ]) .
                            '</td>
                          </tr>
                          <tr>
                            <td colspan="2">&nbsp;</td>
                          </tr>
                          <tr>
                            <td><button type="submit" id="singleInvoice" form="singleInvoiceQuery">Query</button></td>
                          </tr>
                        </table>
                      </fieldset>
                    </form>
                  </td>
                  <td>
                    <form id="multiInvoiceQuery" action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                      <fieldset form="multiInvoiceQuery">
                        <input type="hidden" name="method" value="GET" form="multiInvoiceQuery" />
                        <input type="hidden" name="endPoint" value="invoices" form="multiInvoiceQuery" />
                        <input type="hidden" name="display" value="chart" form="multiInvoiceQuery" />
                        <input type="hidden" name="multi" value="0" form="multiInvoiceQuery" />
                        <legend><label for="multi">Multi Invoice Query </label><input title="Generate charts comparing data points&#10;For any 2 months or 6 month range." type="checkbox" name="multi" id="multi" value="1" form="multiInvoiceQuery" /></legend>
                        <table>
                          <tr>
                            <td class="pullLeft"><label for="invoiceStartDateMonth">Start Date:</label></td>
                            <td>' .
                              self::createLimitedMonthInput([
                                'clientIDs' => array_keys($_SESSION['members']),
                                'inputID' => 'invoiceStartDate',
                                'form' => 'multiInvoiceQuery',
                                'required' => true
                              ]) .
                            '</td>
                            <td colspan="2" class="center bold">Compare</td>
                          </tr>
                          <tr>
                            <td class="pullLeft"><label for="invoiceEndDateMonth">End Date:</label></td>
                            <td class="pullLeft">' .
                              self::createLimitedMonthInput([
                                'clientIDs' => array_keys($_SESSION['members']),
                                'inputID' => 'invoiceEndDate',
                                'form' => 'multiInvoiceQuery',
                                'required' => true
                              ]) .
                            '</td>
                            <td class="center">
                              <label for="compareInvoices">Months:</label>
                              <input type="hidden" name="compare" value="0" form="multiInvoiceQuery" />
                              <input type="checkbox" name="compare" id="compareInvoices" value="1" form="multiInvoiceQuery" />
                            </td>
                            <td class="center">
                              <label for="compareMembers">Members:</label>
                              <input type="hidden" name="compareMembers" value="0" form="multiInvoiceQuery" />
                              <input type="checkbox" name="compareMembers" id="compareMembers" value="1" form="multiInvoiceQuery" disabled />
                            </td>
                          </tr>
                          <tr>
                            <td colspan="4">
                              <button type="submit" id="rangeInvoice" form="multiInvoiceQuery" disabled>Query</button>
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

    public function updateInvoice()
    {
      $updateData = [];
      $updateData['method'] = 'PUT';
      $updateData['endPoint'] = 'invoices';
      $updateData['primaryKey'] = $this->invoice_index;
      foreach($this as $key => $value) {
        if (in_array($key, $this->updateValues) && in_array(lcfirst($key), $this->postKeys)) {
          if (in_array($key, $this->ints)) {
            $updateData['payload'][$key] =
              (in_array($key, $this->nullable) && $value === null) ? null : self::test_int($value);
          } elseif (in_array($key, $this->floats)) {
            $updateData['payload'][$key] =
              (in_array($key, $this->nullable) && $value === null) ? null : self::test_float($value);
          } else {
            $updateData['payload'][$key] =
              (in_array($key, $this->nullable) && $value === null) ? null : self::test_input($value);
          }
        }
      }
      // Build the update query
      $query = self::createQuery($updateData);
      if ($query === false) {
        echo $this->error;
        return false;
      }
      $result = self::callQuery($query);
      if ($result === false) {
        echo $this->error;
        return false;
      }
      echo 'Update Successful';
      return false;
    }
  }
