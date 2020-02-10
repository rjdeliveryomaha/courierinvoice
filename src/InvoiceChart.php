<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

  class InvoiceChart extends CommonFunctions
  {
    protected $dataSet;
    private $tempData;
    protected $clientID;
    protected $compare;
    protected $compareMembers;
    protected $organizationFlag = false;
    protected $orgClients = [];
    protected $ListBy;
    private $listByKey;
    protected $memberList = [];
    private $newData;
    protected $singleMember;
    private $memberInput;
    private $totals = [];
    private $monthKeys = [];
    // Define the order for the keys in $dataSet
    private $properOrder = [ 'monthTotal', 'contract', 'onCall', 'dryIce', 'iceDelivery', 'oneHour', 'twoHour',
      'threeHour', 'fourHour', 'routine', 'roundTrip', 'dedicated', 'deadRun'
    ];
    // $ratio will be used to make sure that bars never go beyond graph height
    private $ratio;
    private $testMax = [];
    private $monthMaxs= [];
    private $maxVal;
    private $heights;
    private $margins;
    private $counts;
    private $split;
    private $labels = [];
    private $tableLabelGroups = [];
    private $tableHead;
    private $tableHeadPrefix;
    private $tableHeadAddendum;
    private $headerSpan;
    private $nestedTableColspan;
    private $nonZeroIgnore = [ 'invoices', 'canceled', 'credit', 'billTo' ];
    private $nonZero = [];
    private $orderedData;
    private $groupLabels = [];
    private $groups;
    protected $chartIndex = 1;
    protected $firstChart;
    protected $secondChart;
    protected $currentChart;
    private $total_bars;
    private $interval_width = 5;
    private $graph_width;
    private $bars;
    private $tableOutput;
    private $graphOutput;
    private $colorCode = 0;
    private $today;
    private $addPDFbutton = true;

    public function __construct($options, $data=[])
    {
      try {
        parent::__construct($options, $data);
      } catch (\Exception $e) {
        throw $e;
      }
    }

    public function displayChart()
    {
      if ($this->organizationFlag === true && (is_array($this->clientID) && count($this->clientID) > 1)) {
        for ($i = 0; $i < count($this->clientID); $i++) {
          $this->orgClients[$this->clientID[$i]] = $this->members[$this->clientID[$i]];
        }
      }
      $returnData = '';
      if (empty($this->dataSet)) {
        $this->error = '<p class="center result">No invoices on file</p>';
        return false;
      }
      if ($this->organizationFlag === true) {
        foreach ($this->members as $key => $value) {
          if (in_array((string)$key, $this->clientID, true)) {
            $this->memberList[] = $key;
          }
        }
        // If clients are not being compared give each client a new invoiceChart and populate it with their data
        if ($this->compareMembers === false) {
          $this->tempData = $this->dataSet;
          for ($i = 0; $i < count($this->memberList); $i++) {
            $this->singleMember = $this->memberList[$i];
            $this->totals = [];
            $this->labels = [];
            $this->dataSet = [];
            $this->heights = [];
            $this->counts = [];
            $this->margins = [];
            $this->testMax = [];
            $this->nonZero = [];
            $this->monthKeys = [];
            $this->orderedData = [];
            $this->groupLabels = [];
            $this->orderDataSet = [];
            $this->tableLabelGroups = [];
            foreach ($this->tempData as $key => $value) {
              foreach ($value as $k => $v) {
                if ($k === $this->memberList[$i]) {
                  $this->dataSet[$key] = $v;
                  $this->dataSet[$key]['billTo'] = $k;
                }
              }
            }
            if (self::sortData() === false) return false;
            $this->total_bars = count($this->monthKeys);
            $returnData .= self::displayTable();
            $returnData .= self::displayBarGraph();
            $this->addPDFbutton = false;
          }
        } else {
          if (self::sortDataForMemberCompare() === false) return false;
          $returnData .= self::displayCompareTable();
          self::resortData();
          $returnData .= self::displayCompareGraph();
        }
      }
      if ($this->organizationFlag === false) {
        if (self::sortData() === false) return false;
        $this->total_bars = count($this->monthKeys);
        $returnData .= self::displayTable();
        $returnData .= self::displayBarGraph();
      }
      return $returnData;
    }

    private function arrayValueToChartLabel($arrayValue)
    {
      switch ($arrayValue) {
        case 'monthTotal': return 'Total';
        case 'contract': return 'Contract';
        case 'onCall': return 'On Call';
        case 'dryIce': return 'Dry Ice';
        case 'iceDelivery': return 'Ice Delivery';
        case 'deadRun': return 'Dead Run';
        case 'oneHour': return 'Stat';
        case 'twoHour': return 'ASAP';
        case 'threeHour': return '3 Hour';
        case 'fourHour': return '4 Hour';
        case 'routine': return 'Routine';
        case 'roundTrip': return 'Round Trip';
        case 'dedicatedRun': return 'Dedicated Run';
        default: return 'Label Error';
      }
    }

    private function orderDataSet()
    {
      $reorder = $ordered = [];
      foreach ($this->dataSet as $key => $value) {
        $reorder[] = date('Y-m', strtotime($key));
      }
      ksort($reorder);
      foreach ($reorder as $key => $value) {
        $ordered[] = date('M Y', strtotime($value));
      }
      $this->dataSet = array_merge(array_flip($ordered), $this->dataSet);
    }

    private function sortData()
    {
      if (count($this->dataSet) < 1) {
        $this->error = '<p class="center result">Chart Data Empty Line ' . __line__ . '</p>';
        return false;
      } else {
        if ($this->compare === true) {
          self::orderDataSet();
        }
        foreach ($this->dataSet as $test) {
          $this->testMax[] = $test['monthTotal'];
          foreach ($test as $key => $value) {
            if ((int)$value !== 0 && !in_array($key, $this->nonZero) && !in_array($key, $this->nonZeroIgnore)) {
              $this->nonZero[] = $key;
            }
          }
        }
        if ($this->options['displayDryIce'] === false) {
          $temp = array_flip($this->nonZero);
          unset($temp['dryIce'], $temp['iceDelivery']);
          $this->nonZero = array_flip($temp);
        }
        foreach ($this->dataSet as $key => $value) {
          $this->monthKeys[] = $key;
          $temp = array_merge(array_flip($this->properOrder), $value);
          foreach ($temp as $k => $v) {
            if (in_array($k, $this->nonZero)) {
              if (!in_array($k, $this->labels)) $this->labels[] = $k;
              $this->orderedData[$key][$k] = $v;
              $this->totals[$k][] = $v;
            }
          }
        }
        for ($i = 0; $i < count($this->monthKeys); $i++) {
          if ($this->singleMember !== null) {
            $repeatMarker = (strpos($this->singleMember, 't') === false) ? '1' : '0';
            $this->memberInput = "
                    <input type=\"hidden\" name=\"clientID\" value=\"{$this->singleMember}\" />
                    <input type=\"hidden\" name=\"repeatClient\" value=\"$repeatMarker\" />
                    ";
          } else {
            $repeatMarker = (strpos($this->clientID[0], 't') === false) ? '1' : '0';
            $this->memberInput = "
                    <input type=\"hidden\" name=\"clientID\" value=\"{$this->clientID[0]}\" />
                    <input type=\"hidden\" name=\"repeatClient\" value=\"$repeatMarker\" />
                    ";
          }
          $this->groupLabels[] = '
                  <form action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
                    <input type="hidden" name="endPoint" value="invoices" />
                    <input type="hidden" name="display" value="invoice" />
                    <input type="hidden" name="single" value="1" />
                    <input type="hidden" name="dateIssued" value="' .
                      date('Y-m', strtotime($this->monthKeys[$i])) . '" />'
                    . $this->memberInput . '
                    <button type="submit" class="bar' . $i . 'Label invoiceQuery">' . $this->monthKeys[$i] . '</button>
                  </form>';
        }

        $this->maxVal = max($this->testMax);
        $this->ratio = $this->options['chart_height'] / $this->maxVal;
        foreach ($this->orderedData as $key => $value) {
          foreach ($value as $k => $v) {
            $this->heights[$k . '_height'][] = $v * $this->ratio;
            $this->margins[$k . '_margin'][] = $this->options['chart_height'] - ($v * $this->ratio);
            $this->counts[$k . '_counts'][] = self::number_format_drop_zero_decimals($v, 2);
          }
        }

        $conjunction = ($this->compare === true || count($this->monthKeys) === 2) ? ' &amp; ' : ' - ';
        if (count($this->monthKeys) === 1) {
          $this->tableHead = $this->monthKeys[0];
        } else {
          $this->tableHead = $this->monthKeys[0] . $conjunction . $this->monthKeys[count($this->monthKeys) - 1];
        }
        // Set $this->nonZero equal to array keys of the first month in $this->orderedData
        $this->nonZero = array_keys($this->orderedData[$this->monthKeys[0]]);
        // Split $this->nonZero into groups based on the total number of values
        if (count($this->labels) > 6) {
          $temp = array_shift($this->labels);
          $this->tableLabelGroups = array_chunk($this->labels, 5);
          for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
            array_unshift($this->tableLabelGroups[$i], $temp);
          }
          array_unshift($this->labels, $temp);
        } else {
          $this->tableLabelGroups[] = $this->labels;
        }
      }
    }

    private function sortDataForMemberCompare()
    {
      if (count($this->dataSet) < 1) {
        $this->error = '<p class="center result">Chart Data Empty Line ' . __line__ . '</p>';
        return false;
      } else {
        foreach ($this->orgClients as $key => $value) {
          foreach ($this->dataSet as $k => $v) {
            if (!array_key_exists($key, $v)) {
              for ($i = 0; $i < count($this->properOrder); $i++) {
                $this->dataSet[$k][$key][$this->properOrder[$i]] = 0;
              }
            }
          }
        }
        foreach ($this->dataSet as $key => $value) {
          $this->monthKeys[] = $key;
          foreach ($value as $k => $v) {
            foreach ($v as $k1 => $v1) {
              if ((int)$v1 !== 0 && !in_array($k1, $this->nonZero) && !in_array($k1, $this->nonZeroIgnore)) {
                $this->nonZero[] = $k1;
              }
            }
          }
        }
        foreach ($this->dataSet as $key => $value) {
          foreach ($value as $k => $v) {
            $temp = array_merge(array_flip($this->properOrder), $v);
            foreach ($temp as $k1 => $v1) {
              if (in_array($k1, $this->nonZero)) {
                if (!in_array($k1, $this->labels)) $this->labels[] = $k1;
                $this->orderedData[$key][$k]['counts'][$k1] = $v1;
              }
            }
          }
        }
        foreach ($this->nonZero as $key => $value) {
          $temp = 0;
          $temp2 = 0;
          for ($i = 0; $i < count($this->monthKeys); $i++) {
            for ($j = 0; $j < count($this->memberList); $j++) {
              if (!isset($this->totals[$this->monthKeys[$i]][$value])) {
                $this->totals[$this->monthKeys[$i]][$value] = 0;
              }
              $this->totals[$this->monthKeys[$i]][$value] +=
                $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]]['counts'][$value];
              $temp += $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]]['counts'][$value];
            }
          }
          $this->totals[$value] = $temp;
        }
        if (count($this->labels) > 7) {
          $temp = array_shift($this->labels);
          $this->split = round((count($this->labels) / 2), 0, PHP_ROUND_HALF_UP);
          $this->tableLabelGroups = array_chunk($this->labels, $this->split);
          for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
            array_unshift($this->tableLabelGroups[$i], $temp);
          }
        } else {
          $this->tableLabelGroups[] = $this->labels;
        }

        $conjunction = ($this->compare === true || count($this->monthKeys) === 2) ? ' &amp; ' : ' - ';
        if (count($this->monthKeys) === 1) {
          $this->tableHead = $this->monthKeys[0];
        } else {
          $this->tableHead = $this->monthKeys[0] . $conjunction . $this->monthKeys[count($this->monthKeys) - 1];
        }
      }
    }

    private function resortData()
    {
      $temp = [];
      for ($i = 0; $i < count($this->properOrder); $i++) {
        if (in_array($this->properOrder[$i], $this->nonZero)) {
          $temp[] = $this->properOrder[$i];
        }
      }
      $this->nonZero = $temp;
      $temp = [];
      for ($i = 0; $i < count($this->monthKeys); $i++) {
        $temp[$this->monthKeys[$i]]['max'] = 0;
        for ($j = 0; $j < count($this->memberList); $j++) {
          if (
              $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]]['counts']['monthTotal'] >
              $temp[$this->monthKeys[$i]]['max']
            ) {
                $temp[$this->monthKeys[$i]]['max'] =
                  $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]]['counts']['monthTotal'];
              }
          foreach ($this->nonZero as $key => $value) {
            if (
              !isset($temp[$this->monthKeys[$i]][$this->memberList[$j]][$value])
            ) {
              $temp[$this->monthKeys[$i]][$this->memberList[$j]][$value] = 0;
            }

            $temp[$this->monthKeys[$i]][$this->memberList[$j]][$value] +=
              $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]]['counts'][$value];
          }
        }
      }
      foreach ($temp as $key => $value) {
        $ratio = 0;
        foreach ($value as $k => $v) {
          if ($k === 'max') {
            $ratio = $this->options['chart_height'] / $v;
          } else {
            $this->totals[$k] = $v;
            foreach ($v as $k1 => $v1) {
              $this->orderedData[$key][$k]['heights'][$k1] = $v1 * $ratio;
            }
          }
        }
      }
    }

    private function displayCompareTable()
    {
      $format = $_POST['paperFormat'] ?? $config['paperFormat'] ?? 'letter';
      $membergroup = [];
      switch ($format) {
        case 'a4':
          $membergroup = array_chunk($this->memberList, 13);
          break;
        case 'legal':
          $membergroup = array_chunk($this->memberList, 14);
          break;
        default: $membergroup = array_chunk($this->memberList, 14);
      }
      $this->tableOutput = (class_exists('Dompdf\Dompdf') && $this->options['enableChartPDF'] === true) ? "
        <form id=\"invoiceChartPDFform\" target=\"_blank\" method=\"post\" action=\"pdf\">
          <input type=\"hidden\" name=\"title\" value=\"invoiceChart\" form=\"invoiceChartPDFform\" />
          <input type=\"hidden\" name=\"type\" value=\"chart\" form=\"invoiceChartPDFform\" />
          <input type=\"hidden\" name=\"paperFormat\" value=\"{$format}\" form=\"invoiceChartPDFform\" />
          <input type=\"hidden\" name=\"paperOrientation\" value=\"landscape\" form=\"invoiceChartPDFform\" />
          <input type=\"hidden\" name=\"formKey\" form=\"invoiceChartPDFform\" />
          <input type=\"hidden\" name=\"content\" form=\"invoiceChartPDFform\" />
          <button type=\"button\" id=\"invoiceChartPDF\" form=\"invoiceChartPDFform\">PDF</button>
        </form>" : '';
      foreach ($this->monthKeys as $_ => $month) {
        foreach ($membergroup as $__ => $memberList) {
          for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
            $page_break =
              ((count($memberList) < 6 && $i === count($this->tableLabelGroups) - 1) || count($memberList) > 5) ?
              'style="page-break-after: always;"' : '';

            $this->tableOutput .= "
        <table class=\"invoiceTable member centerDiv\" {$page_break}>
          <tr>
            <th>Member</th>";
            for ($j = 0; $j < count($this->tableLabelGroups[$i]); $j++) {
              $this->tableOutput .= "
            <th>{$this->arrayValueToChartLabel($this->tableLabelGroups[$i][$j])}</th>";
            }
            $this->tableOutput .= '
          </tr>';
            for ($x = 0; $x < count($memberList); $x++) {
              $pdfHighlight = ($x === 0 || $x % 2 === 0) ? 'style="background-color: #ccc;"' : '';
              $this->tableOutput .= "
          <tr class=\"highlight\" {$pdfHighlight}>
            <td class=\"bar{$x}Label center\">{$this->clientListBy($memberList[$x])}</td>";
              for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
                $this->tableOutput .= "
            <td class=\"center highlight2\">
              <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>{$this->number_format_drop_zero_decimals($this->orderedData[$month][$memberList[$x]]['counts'][$this->tableLabelGroups[$i][$y]], 2)}
              <br>
              {$this->displayPercentage($this->orderedData[$month][$memberList[$x]]['counts'][$this->tableLabelGroups[$i][$y]], $this->totals[$this->tableLabelGroups[$i][$y]])}&#37;
            </td>";
              }
              $this->tableOutput .= "
          </tr>";
            }
            $this->tableOutput .= "
          <tr>
            <th>{$month}:</th>";
            for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
              $total = $this->totals[$month][$this->tableLabelGroups[$i][$y]];
              $percentage = self::displayPercentage(
                $this->totals[$month][$this->tableLabelGroups[$i][$y]],
                $this->totals[$this->tableLabelGroups[$i][$y]]
              );
              $this->tableOutput .= "
            <td class=\"center highlight2 error\">
              <p><span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>$total</p>
              <p>{$percentage}&#37;</p>
            </td>";
            }
            $this->tableOutput .= "
          </tr>
          <tr>
            <th>{$this->tableHead}</th>";
            for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
              $total = self::number_format_drop_zero_decimals($this->totals[$this->tableLabelGroups[$i][$y]], 2);
              $this->tableOutput .= "
            <td class=\"center highlight2\">
              <span class=\"currencySymbol\">{$this->config['CurrencySymbol']}</span>$total
            </td>";
            }
            $this->tableOutput .= '
          </tr>
        </table>';
          }
        }
      }
      return $this->tableOutput;
    }

    private function displayTable()
    {
      $this->tableOutput =
        (class_exists('Dompdf\Dompdf') && $this->addPDFbutton === true && $this->options['enableChartPDF'] === true) ? '
        <form id="invoiceChartPDFform" target="_blank" method="post" action="pdf">
          <input type="hidden" name="title" value="invoiceChart" form="invoiceChartPDFform" />
          <input type="hidden" name="type" value="chart" form="invoiceChartPDFform" />
          <input type="hidden" name="paperOrientation" value="landscape" form="invoiceChartPDFform" />
          <input type="hidden" name="formKey" form="invoiceChartPDFform" />
          <input type="hidden" name="content" form="invoiceChartPDFform" />
          <button type="button" id="invoiceChartPDF" form="invoiceChartPDFform">PDF</button>
        </form>' : '';
      $this->tableHeadPrefix = ($this->compare === true) ? 'Comparing Expenses For ' : 'Expenses for ';
      if ($this->singleMember !== null) {
        $this->tableHeadAddendum = "<br>
              <span class=\"medium\">{$this->clientListBy($this->singleMember)}</span>";
      }
      $this->headerSpan = count($this->tableLabelGroups[0]) + 1;
      $this->tableOutput .= "
        <table class=\"invoiceTable\">
          <thead>
            <th class=\"displayHeader\" colspan=\"{$this->headerSpan}\">
              {$this->tableHeadPrefix}{$this->tableHead}{$this->tableHeadAddendum}
            </th>
          </thead>
          <tbody>";
      for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
        for ($j = 0; $j < count($this->tableLabelGroups[$i]); $j++) {
          if ($j === 0) $this->tableOutput .= '
            <tr>
              <th>Month</th>';
          $this->tableOutput .= '
              <th>
                ' . self::arrayValueToChartLabel($this->tableLabelGroups[$i][$j]) . '
              </th>';
          if ($j === count($this->tableLabelGroups[$i]) - 1) $this->tableOutput .= '
            </tr>';
        }
        for ($x = 0; $x < count($this->groupLabels); $x++) {
          for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
            if ($y === 0) {
              $this->tableOutput .= '
            <tr class="bar' . $x . 'Label">
              <td>' . $this->groupLabels[$x] . '
              </td>';
            }
            $this->tableOutput .= '
              <td class="center"><span class="currencySymbol">
                ' . $this->config['CurrencySymbol'] . '</span>' .
                  self::negParenth(
                    self::number_format_drop_zero_decimals($this->totals[$this->tableLabelGroups[$i][$y]][$x], 2)
                  ) . '
              </td>';

            if ($y === count($this->tableLabelGroups[$i]) - 1) $this->tableOutput .= '
            </tr>';
          }
        }
      }
      $this->tableOutput .= '
          </tbody>
        </table>';
      return $this->tableOutput;
    }

    private function displayBarGraph()
    {
      $this->graphOutput = '';
      $firstBarMargin = (100 - (7 * (count($this->monthKeys)  + (count($this->monthKeys) - 1)))) / 2;
      for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
        $this->graphOutput .= "
        <table class=\"bargraph\">
          <tbody>
            <tr class=\"graphBody\" style=\"height: {$this->options['chart_height']}rem;\">";
        for ($j = 0; $j < count($this->tableLabelGroups[$i]); $j++) {
          $this->graphOutput .= '
              <td class="barContainer">';
          for ($k = 0; $k < count($this->monthKeys); $k++) {
            $margin = ($k === 0) ? "margin-left: {$firstBarMargin}%;" : '';
            $titleValue = ($this->compareMembers === true) ?
              $this->totals[$this->monthKeys[$k]][$this->tableLabelGroups[$i][$j]] :
              $this->totals[$this->tableLabelGroups[$i][$j]][$k];
            $heightKey = "{$this->tableLabelGroups[$i][$j]}_height";
            $this->graphOutput .= "<div title=\"{$this->arrayValueToChartLabel($this->tableLabelGroups[$i][$j])}
            &#10;{$this->config['CurrencySymbol']}{$this->number_format_drop_zero_decimals($titleValue, 2)}\"
            style=\"height: {$this->heights[$heightKey][$k]}rem;{$margin}\" class=\"bar{$k}\"></div>";
            if ($k !== count($this->monthKeys) - 1) {
              $this->graphOutput .= '<div class="gap"></div>';
            }
          }
          $this->graphOutput .= '
            </td>';
          if ($j !== count($this->tableLabelGroups[$i]) - 1) {
            $this->graphOutput .= '
              <td class="space"></td>';
          }
        }
        $this->graphOutput .= '
            </tr>
            <tr class="graphFoot">';
        for ($j = 0; $j < count($this->tableLabelGroups[$i]); $j++) {
          $this->graphOutput .= "
              <td class=\"chartLabels\">{$this->arrayValueToChartLabel($this->tableLabelGroups[$i][$j])}</td>";
          if ($j !== count($this->tableLabelGroups[$i]) - 1) {
            $this->graphOutput .= '
              <td class="space"></td>';
          }
        }
        $this->graphOutput .= '
            </tr>
          </tbody>
        </table>';
      }
      return $this->graphOutput;
    }

    private function displayCompareGraph()
    {
      $this->graphOutput = '';
      $memberList = array_chunk($this->memberList, 6);
      $heights = $counts = [];
      foreach ($this->heights as $key => $value) {
        $heights[$key] = array_chunk($value, 6);
      }
      foreach ($this->counts as $key => $value) {
        $counts[$key] = array_chunk($value, 6);
      }
      foreach ($this->monthKeys as $_ => $month) {
        for ($j = 0; $j < count($memberList); $j++) {
          for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
            $this->graphOutput .= "
            <div class=\"graphKey\">
              <h2 class=\"monthKey\">{$month}</h2>";
            for ($k = 0; $k < count($memberList[$j]); $k++) {
              $firstBarMargin = (100 - (7 * (count($memberList[$j])  + (count($memberList[$j]) - 1)))) / 2;
              $this->graphOutput .= "
              <p class=\"bar{$k}Label\">{$this->clientListBy($memberList[$j][$k])}</p>";
            }
            $this->graphOutput .= "
            </div>
            <table class=\"bargraph\">
              <tr class=\"graphBody\">";
            for ($k = 0; $k < count($this->tableLabelGroups[$i]); $k++) {
              $this->graphOutput .= "
                <td class=\"barContainer\" style=\"height:{$this->options['chart_height']}rem;\">";
              for ($l = 0; $l < count($memberList[$j]); $l++) {
                $margin = ($l === 0) ? " margin-left: {$firstBarMargin}%;" : '';
                $this->graphOutput .= "<div class=\"bar{$l}\" title=\"{$this->config['CurrencySymbol']}{$this->orderedData[$month][$memberList[$j][$l]]['counts'][$this->tableLabelGroups[$i][$k]]}\" style=\"height: {$this->orderedData[$month][$memberList[$j][$l]]['heights'][$this->tableLabelGroups[$i][$k]]}rem;{$margin}\"></div>";
                if ($l !== count($memberList[$j]) - 1) {
                  $this->graphOutput .= '<div class="gap"></div>';
                }
              }
              $this->graphOutput .= '
                </td>';
              if ($k !== count($this->tableLabelGroups[$i]) - 1) {
                $this->graphOutput .= '
                <td class="space"></td>';
              }
            }
            $this->graphOutput .= '
              </tr>
              <tr class="graphFoot">';
            for ($k = 0; $k < count($this->tableLabelGroups[$i]); $k++) {
              $this->graphOutput .= "
                <td class=\"chartLabels\">
                  {$this->arrayValueToChartLabel($this->tableLabelGroups[$i][$k])}
                </td>";
                if ($k !== count($this->tableLabelGroups[$i]) - 1) {
                  $this->graphOutput .= '
                <td class="space"></td>';
                }
            }
            $this->graphOutput .= '
              </tr>
            </table>';
          }
        }
      }
      return $this->graphOutput;
    }
  }
