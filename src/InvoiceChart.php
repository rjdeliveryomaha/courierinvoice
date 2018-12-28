<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

  class InvoiceChart extends CommonFunctions {
    protected $dataSet;
    private $tempData;
    protected $clientID;
    protected $compare;
    protected $compareMembers;
    protected $organizationFlag = FALSE;
    protected $orgClients = [];
    protected $ListBy;
    protected $invoiceChartRowLimit = 9;
    private $listByKey;
    protected $memberList = [];
    private $newData;
    protected $singleMember;
    private $memberInput;
    private $totals = [];
    private $monthKeys = [];
    // Define the order for the keys in $dataSet
    private $properOrder = ['monthTotal', 'contract', 'onCall', 'dryIce', 'iceDelivery', 'oneHour', 'twoHour', 'threeHour', 'fourHour', 'routine', 'roundTrip', 'dedicated', 'deadRun'];
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
    private $nonZeroIgnore = ['invoices', 'canceled', 'credit', 'billTo'];
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

    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
      if ($this->organizationFlag === TRUE && (is_array($this->clientID) && count($this->clientID) > 1)) {
        for ($i = 0; $i < count($this->clientID); $i++) {
          $this->orgClients[$this->clientID[$i]] = $this->members[$this->clientID[$i]];
        }
      }
    }

    public function displayChart() {
      if (empty($this->dataSet)) {
        $this->error = '<p class="center">No invoices on file</p>';
        echo $this->error;
        return FALSE;
      }
      if ($this->organizationFlag === TRUE) {
        foreach ($this->members as $key => $value) {
          if (in_array($key, $this->clientID)) {
            $this->memberList[] = $key;
          }
        }
        // If clients are not being compared give each client a new invoiceChart and populate it with their data
        if ($this->compareMembers === FALSE) {
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
            self::sortData();
            $this->total_bars = count($this->monthKeys);
            self::displayTable();
            self::displayBarGraph();
          }
        } else {
          $this->invoiceChartRowLimit = 5;
          self::sortDataForMemberCompare();
          self::displayCompareTable();
          self::resortData();
          self::displayBarGraph();
        }
      }
      if ($this->organizationFlag === FALSE) {
        self::sortData();
        $this->total_bars = count($this->monthKeys);
        self::displayTable();
        self::displayBarGraph();
      }
    }

    private function arrayValueToChartLabel($arrayValue) {
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

    private function orderDataSet() {
      $reorder = $ordered = [];
      foreach ($this->dataSet as $key => $value) {
        $reorder[] = date('Y-m', strtotime($key));
      }
      krsort($reorder);
      foreach ($reorder as $key => $value) {
        $ordered[] = date('M Y', strtotime($value));
      }
      $this->dataSet = array_merge(array_flip($ordered), $this->dataSet);
    }

    private function sortData() {
      if (count($this->dataSet) < 1) {
        $this->error = 'Chart Data Empty Line ' . __line__;
        echo $this->error;
        return FALSE;
      } else {
        if ($this->compare === TRUE) {
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
          if ($this->singleMember !== NULL) {
            $this->memberInput = '<input type="hidden" name="clientID[]" value="' . $this->singleMember . '" />';
          } else {
            $this->memberInput = '<input type="hidden" name="clientID" value="' . $this->clientID . '" />';
          }
          $this->groupLabels[] = '
            <form action="' . self::esc_url($_SERVER['REQUEST_URI']) . '" method="post">
              <input type="hidden" name="formKey" value="' . $this->formKey . '" />
              <input type="hidden" name="endPoint" value="invoices" />
              <input type="hidden" name="display" value="invoice" />
              <input type="hidden" name="dateIssued" value="' . date('Y-m', strtotime($this->monthKeys[$i])) . '" />'
              . $this->memberInput .
              '
              <button type="submit" class="bar' . $i . 'Label invoiceQuery">' . $this->monthKeys[$i] . '</button>
            </form>
          ';
        }
        if (count($this->labels) > $this->invoiceChartRowLimit) {
          $this->split = round((count($this->labels) / 2), 0, PHP_ROUND_HALF_UP);
          $this->tableLabelGroups[] = array_slice($this->labels, 0, $this->split);
          $this->tableLabelGroups[] = array_slice($this->labels, $this->split);
        } else {
          $this->tableLabelGroups[] = $this->labels;
        }
        $this->maxVal = max($this->testMax);
        $this->ratio = $this->options['chart_height']/$this->maxVal;
        foreach ($this->orderedData as $key => $value) {
          foreach ($value as $k => $v) {
            $this->heights[$k . '_height'][] = $v * $this->ratio;
            $this->margins[$k . '_margin'][] = $this->options['chart_height'] - ($v * $this->ratio);
            $this->counts[$k . '_counts'][] = self::number_format_drop_zero_decimals($v, 2);
          }
        }

        $conjunction = ($this->compare === TRUE || count($this->monthKeys) === 2) ? ' And ' : ' Through ';
        if (count($this->monthKeys) === 1) {
          $this->tableHead = $this->monthKeys[0];
        } else {
          $this->tableHead = $this->monthKeys[0] . $conjunction . $this->monthKeys[count($this->monthKeys) - 1];
        }
        // Set $this->nonZero equal to array keys of the first month in $this->orderedData
        $this->nonZero = array_keys($this->orderedData[$this->monthKeys[0]]);
        // Split $this-nonZero into groups based on the total number of values
        if (count($this->tableLabelGroups) > 1) {
          $this->split = round((count($this->nonZero) / 2), 0, PHP_ROUND_HALF_UP);
          $this->firstChart = array_slice($this->nonZero, 0 , $this->split);
          $this->secondChart = array_slice($this->nonZero, $this->split);
          array_unshift($this->secondChart, $this->nonZero[0]);
        } else {
          $this->firstChart = $this->nonZero;
        }
      }
    }

    private function sortDataForMemberCompare() {
      if (count($this->dataSet) < 1) {
        $this->error = 'Chart Data Empty Line ' . __line__;
        echo $this->error;
        return FALSE;
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
                $this->orderedData[$key][$k][$k1] = $v1;
              }
            }
          }
        }
        foreach ($this->nonZero as $key => $value) {
          $temp = 0;
          $temp2 = 0;
          for ($i = 0; $i < count($this->monthKeys); $i++) {
            for ($j = 0; $j < count($this->memberList); $j++) {
              if (!isset($this->totals[$this->monthKeys[$i]][$value])) $this->totals[$this->monthKeys[$i]][$value] = 0;
              $this->totals[$this->monthKeys[$i]][$value] += $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]][$value];
              $temp += $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]][$value];
            }
          }
          $this->totals[$value] = $temp;
        }
        if (count($this->labels) > $this->invoiceChartRowLimit) {
          $this->split = round((count($this->labels) / 2), 0, PHP_ROUND_HALF_UP);
          $this->tableLabelGroups[] = array_slice($this->labels, 0, $this->split);
          $this->tableLabelGroups[] = array_slice($this->labels, $this->split);
        } else {
          $this->tableLabelGroups[] = $this->labels;
        }
        // Split $this->nonZero into groups based on the total number of values
        if (count($this->nonZero) > 9) {
          $this->split = round((count($this->nonZero) / 2), 0, PHP_ROUND_HALF_UP);
          $this->firstChart = array_slice($this->nonZero, 0 , $this->split);
          $this->secondChart = array_slice($this->nonZero, $this->split);
          array_unshift($this->secondChart, $this->nonZero[0]);
        } else {
          $this->firstChart = $this->nonZero;
        }
        $conjunction = ($this->compare === TRUE || count($this->monthKeys) === 2) ? ' And ' : ' Through ';
        if (count($this->monthKeys) === 1) {
          $this->tableHead = $this->monthKeys[0];
        } else {
          $this->tableHead = $this->monthKeys[0] . $conjunction . $this->monthKeys[count($this->monthKeys) - 1];
        }
      }
    }

    private function resortData() {
      $temp = array();
      for ($i = 0; $i < count($this->properOrder); $i++) {
        if (in_array($this->properOrder[$i], $this->nonZero)) {
          $temp[] = $this->properOrder[$i];
        }
      }
      $this->nonZero = $temp;
      $temp = array();
      for ($i = 0; $i < count($this->monthKeys); $i++) {
        for ($j = 0; $j < count($this->memberList); $j++) {
          foreach ($this->nonZero as $key => $value) {
            if (!isset($temp[$this->monthKeys[0]][$this->memberList[$j]][$value])) $temp[$this->monthKeys[0]][$this->memberList[$j]][$value] = 0;
            $temp[$this->monthKeys[0]][$this->memberList[$j]][$value] += $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]][$value];
          }
        }
      }
      foreach ($temp as $target) {
        for ($i = 0; $i < count($this->memberList); $i++) {
          foreach ($target[$this->memberList[$i]] as $key => $value) {
            if ($key === 'monthTotal') $this->testMax[] = $value;
          }
        }
      }
      $this->maxVal = max($this->testMax);
      $this->ratio = $this->options['chart_height']/$this->maxVal;
      foreach ($temp as $key => $value) {
        foreach ($value as $k => $v) {
          $this->totals[$k] = $v;
          foreach ($v as $k1 => $v1) {
            $this->heights[$k1 . '_height'][] = $v1 * $this->ratio;
            $this->margins[$k1 . '_margin'][] = $this->options['chart_height'] - ($v1 * $this->ratio);
            $this->counts[$k1 . '_counts'][] = self::number_format_drop_zero_decimals($v1, 2);
          }
        }
      }
      // Split $this-nonZero into groups based on the total number of values
      if (count($this->nonZero) > 9) {
        $this->split = round((count($this->nonZero) / 2), 0, PHP_ROUND_HALF_UP);
        $this->firstChart = array_slice($this->nonZero, 0 , $this->split);
        $this->secondChart = array_slice($this->nonZero, $this->split);
        array_unshift($this->secondChart, $this->nonZero[0]);
      } else {
        $this->firstChart = $this->nonZero;
      }
      $this->monthKeys = $this->memberList;
    }

    private function displayCompareTable() {
      for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
        $this->headerSpan = count($this->tableLabelGroups[$i]) + 2;
        $this->nestedTableColspan = $this->headerSpan - 1;
        $this->tableOutput .= '
  <table class="member centerDiv">
    <thead>
      <tr>
        <th class="displayHeader" colspan="' . $this->headerSpan . '">' . $this->tableHead . '</th>
      </tr>
    </thead>
    <tbody>';
        for ($j = 0; $j < count($this->monthKeys); $j++) {
          $this->tableOutput .= '
      <tr>
        <th>Month</th>
        <th>Member</th>';
        for ($k = 0; $k < count($this->tableLabelGroups[$i]); $k++) {
          $this->tableOutput .= '
        <th>' . self::arrayValueToChartLabel($this->tableLabelGroups[$i][$k]) . '</th>';
          if ($k === count($this->tableLabelGroups[$i]) - 1) {
            $this->tableOutput .= '</tr>';
          }
        }
        $this->tableOutput .= '
      <tr>
        <td class="center">' . $this->monthKeys[$j] . '</td>
        <td colspan="' . $this->nestedTableColspan . '">';
          for ($x = 0; $x < count($this->memberList); $x++) {
            $this->tableOutput .= '
          <table class="wide">
            <tr class="highlight">
              <td class="bar' . $x . 'Label center">' . self::clientListBy($this->memberList[$x]) . '</td>';
              for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
                $this->tableOutput .= '
              <td class="center highlight2"><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . $this->orderedData[$this->monthKeys[$j]][$this->memberList[$x]][$this->tableLabelGroups[$i][$y]] . '<br>' . self::displayPercentage($this->orderedData[$this->monthKeys[$j]][$this->memberList[$x]][$this->tableLabelGroups[$i][$y]], $this->totals[$this->tableLabelGroups[$i][$y]]) . '&#37;</td>';
              }
              $this->tableOutput .= '
            </tr>
          </table>';
          }
          $this->tableOutput .= '
        </td>
      </tr>
      <tr class="highlight">
        <td></td>
        <th>Month Total:</th>';
          for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
            $this->tableOutput .= '
        <td class="center highlight2 error"><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . $this->totals[$this->monthKeys[$j]][$this->tableLabelGroups[$i][$y]] . '<br>' . self::displayPercentage($this->totals[$this->monthKeys[$j]][$this->tableLabelGroups[$i][$y]], $this->totals[$this->tableLabelGroups[$i][$y]]) . '&#37;</td>';
              }
          $this->tableOutput .= '
      </tr>
      <tr class="highlight" style="border-bottom: 0.1em solid black;">
        <td></td>
        <th>Query Total:</th>';
          for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
            $this->tableOutput .= '
        <td class="center highlight2"><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::number_format_drop_zero_decimals($this->totals[$this->tableLabelGroups[$i][$y]], 2) . '</td>';
              }
          $this->tableOutput .= '
      </tr>';
        }
        $this->tableOutput .= '
    </tbody>
  </table>';
      }
      echo $this->tableOutput;
      return FALSE;
    }

    private function displayTable() {
      if ($this->error !== '') return FALSE;
      $this->tableHeadPrefix = ($this->compare === TRUE) ? 'Comparing Expenses For ' : 'Expenses for the period between ';
      if ($this->singleMember !== NULL) {
        $this->tableHeadAddendum = '<br>';
        $this->tableHeadAddendum .= '<span class="medium">' . self::clientListBy($this->singleMember) . '</span>';
      }
      $this->headerSpan = count($this->tableLabelGroups[0]) + 1;
      $this->tableOutput = '
        <div class="break invoiceTable">
          <table>
            <thead>
              <th class="displayHeader" colspan="' . $this->headerSpan . '">' . $this->tableHeadPrefix . $this->tableHead . $this->tableHeadAddendum . '</th>
            </thead>
            <tbody>';
      for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
        for ($j = 0; $j < count($this->tableLabelGroups[$i]); $j++) {
          if ($j === 0) $this->tableOutput .= '
              <tr>
                <td></td>
                ';
          $this->tableOutput .= '<th>' . self::arrayValueToChartLabel($this->tableLabelGroups[$i][$j]) . '</th>';
          if ($j === count($this->tableLabelGroups[$i]) - 1) $this->tableOutput .= '</tr>';
        }
        for ($x = 0; $x < count($this->groupLabels); $x++) {
          for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
            if ($y === 0) {
              $this->tableOutput .= '
              <tr class="bar' . $x . 'Label">
                <td>' . $this->groupLabels[$x] . '</td>
                ';
            }
            $this->tableOutput .= '<td class="center"><span class="currencySymbol">' . $this->config['CurrencySymbol'] . '</span>' . self::negParenth(self::number_format_drop_zero_decimals($this->totals[$this->tableLabelGroups[$i][$y]][$x], 2)) . '</td>';

            if ($y === count($this->tableLabelGroups[$i]) - 1) $this->tableOutput .= '</tr>';
          }
        }
      }
      $this->tableOutput .= '</tbody></table></div>';
      echo $this->tableOutput;
      return FALSE;
    }

    private function displayBarGraph() {
      if ($this->error !== '') return FALSE;
      $this->currentChart = self::chartIndexToProperty();
      if ($this->currentChart === NULL) {
        echo $this->graphOutput;
        return FALSE;
      }
      // dynamically calculate the width of the graph
      $this->groups = count($this->currentChart);
      $this->graph_width = ($this->interval_width * $this->groups) + ($this->options['interval_gap'] * ($this->groups + 1));
      $this->graphOutput .= '';
      if ($this->compareMembers === TRUE) {
        $this->graphOutput .= '
        <p class="center displayHeader">' . $this->tableHead . '</p>
        <p style="display:flex; justify-content:space-around;">';
          for ($i = 0; $i < count($this->memberList); $i++) {
            $this->graphOutput .= '<span class="bar' . $i . 'Label">' . self::clientListBy($this->memberList[$i]) . '  </span>';
          }
        $this->graphOutput .= '
        </p>';
      }
      $this->graphOutput .= '
        <div class="invoiceGraphContainer">
        <div class="centerDiv" style="border:solid 0.1em #e1e1e1; background-color:#f4f4f4; height:' . $this->options['chart_height'] . 'em; width:' . $this->graph_width . 'em; margin-top:1.25em; /* padding-top:0.75em; */ overflow: hidden;">
          <div style="height:' . $this->options['chart_height'] . 'em;width:' . $this->options['interval_gap'] . 'em;" class="space"></div>';
      // Sort out the bars here
      for ($i = 0; $i < count($this->currentChart); $i++) {
        $this->graphOutput .= '
          <div style="height:' . $this->options['chart_height'] . 'em; width:' . $this->interval_width . 'em; margin:0; padding:0;' . $this->options['interval_border'] . 'em;" class="barContainer">
          <div style="height:' . $this->options['chart_height'] . 'em;width:' . $this->options['bar_gap'] . 'em;" class="gap"></div>';
        for ($j = 0; $j < count($this->monthKeys); $j++) {
          $titleValue = ($this->compareMembers === TRUE) ? $this->totals[$this->monthKeys[$j]][$this->currentChart[$i]] : $this->totals[$this->currentChart[$i]][$j];
          $this->graphOutput .= '
            <div title="' . self::arrayValueToChartLabel($this->currentChart[$i]) . '&#10;' . $this->config['CurrencySymbol'] . self::number_format_drop_zero_decimals($titleValue, 2) . '" style="height:' . $this->heights[$this->currentChart[$i] . '_height'][$j] . 'em; width:' . $this->options['bar_width'] . 'em; margin-top:' . $this->margins[$this->currentChart[$i] . '_margin'][$j] . 'em;" class="bar' . $j . '"></div>
            <div style="height:' . $this->options['chart_height'] . 'em;width:' . $this->options['bar_gap'] . 'em;" class="gap"></div>
          ';
        }
        $this->graphOutput .= '
          </div>
          <div style="height:' . $this->options['chart_height'] . 'em;width:' . $this->options['interval_gap'] . 'em;" class="space"></div>';
      }
      $this->graphOutput .= '
          <div style="clear:both;"></div>
        </div>
        <div class="centerDiv" style="height:2.75em; background-color:#8c8c8c; width:' . $this->graph_width . 'em; color:#fff; border:solid 1px #666;">
        <div style="height:2.75em;width:' . $this->options['interval_gap'] . 'em;" class="space"></div>';
      foreach ($this->currentChart as $label) {
        $this->graphOutput .= '
          <div style="width:' . $this->interval_width . 'em;" class="chartLabels">' . self::arrayValueToChartLabel($label) . '</div>
          <div style="height:2.75em;width:' . $this->options['interval_gap'] . 'em;" class="space"></div>';
      }
      $this->graphOutput .= '
        </div>
      </div>
      ';
      $this->chartIndex++;
      self::displayBarGraph();
    }
  }
