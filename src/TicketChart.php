<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  /***
  * throws Exception
  *
  ***/
  class TicketChart extends CommonFunctions {
    protected $dataSet;
    protected $clientID;
    protected $compare;
    protected $compareMembers;
    protected $ListBy;
    protected $organizationFlag = FALSE;
    protected $ticketChartRowLimit = 5;
    protected $orgClients = [];
    private $memberList = [];
    private $totals = [];
    private $monthKeys = [];
    // Define the order for the keys in $dataSet
    private $properOrder = [ 'monthTotal', 'contract', 'onCall', 'credit', 'withIce', 'withoutIce', 'canceled', 'deadRun', 'oneHour', 'twoHour', 'threeHour', 'fourHour', 'routine', 'roundTrip', 'dedicated' ];
    private $graph_height = 12.5;
    private $bar_width = 0.35;  //width of bar in em
    private $bar_gap = 0.35;  //gap between adjacent bars
    private $interval_gap = 1;  //gap between groups
    private $interval_border = 0.125; //border-width of bar container
    // $ratio will be used to make sure that bars never go beyond graph height
    private $ratio;
    private $testMax = [];
    private $max;
    private $heights;
    private $margins;
    private $counts;
    private $split;
    private $labels = [];
    private $tableLabelGroups = [];
    private $tableHead;
    private $tableHeadPrefix;
    private $nonZeroIgnore = [ 'billTo', 'startDate', 'endDate' ];
    protected $nonZero = [];
    private $orderedData;
    private $nonAssocData;
    private $groupLabels = [];
    private $groups;
    private $total_bars;
    private $interval_width;
    private $graph_width;
    private $bars;
    private $tableOutput;
    private $graphOutput;
    private $colorCode = 0;
    protected $chartIndex = 1;
    protected $firstChart;
    protected $secondChart;
    protected $currentChart;
    private $nestedTableColspan;

    public function __construct($options, $data) {
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
      foreach ($this->dataSet as $key => $value) {
        $this->monthKeys[] = $key;
      }
      $multiMemberTest = FALSE;
      for ($i = 0; $i < count($this->monthKeys); $i++) {
        if (count($this->dataSet[$this->monthKeys[$i]]) > 1) {
          $multiMemberTest = TRUE;
          break;
        }
      }
      if ($multiMemberTest === TRUE) {
        if ($this->compareMembers === FALSE) {
          foreach ($this->orgClients as $key => $value) {
            $this->clientID = $key;
            self::sortData();
            // Number of bars in each group
            $this->total_bars = count($this->labels);
            // Number of months with data for the given client
            $this->groups = 0;
            foreach ($this->dataSet as $key => $value) {
              if (array_key_exists($this->clientID, $value)) $this->groups++;
            }
            // Calculate the width of each interval
            $this->interval_width = ($this->bar_width * $this->total_bars) + ($this->bar_gap * $this->total_bars + 1) + (2 * $this->interval_border);
            // Calculate the width of the graph
            $this->graph_width = ($this->interval_width * $this->groups) + ($this->interval_gap * ($this->groups + 1)) + 1;
            self::displayTable();
            self::displayBarGraph();
          }
        } elseif ($this->compareMembers === TRUE) {
          self::sortDataForMemberCompare();
          self::displayCompareTable();
          self::resortData();
          // Number of groups
          $this->groups = count($this->orderedData);
          // Number of bars in each group
          $this->total_bars = count($this->labels);
          // Calculate the width of each interval
          $this->interval_width = 5;
          // Calculate the width of the graph
          $this->graph_width = ($this->interval_width * $this->groups) + ($this->interval_gap * ($this->groups + 1)) + 1;
          // var_dump($this->interval_width, $this->groups, $this->interval_gap, $this->graph_width);
          self::displayCompareGraph();
        } else {
          $this->error = 'Invalid Member Compare Value Line ' . __line__;
          self::exitWithError();
        }
      } else {
        foreach ($this->dataSet[$this->monthKeys[0]] as $key => $value) {
          $this->clientID = $key;
        }
        self::sortData();
        // Number of groups
        $this->groups = count($this->orderedData);
        // Number of bars in each group
        $this->total_bars = count($this->labels);
        // Calculate the width of each interval
        $this->interval_width = ($this->bar_width * $this->total_bars) + ($this->bar_gap * $this->total_bars + 1) + (2 * $this->interval_border);
        // Calculate the width of the graph
        $this->graph_width = ($this->interval_width * $this->groups) + ($this->interval_gap * ($this->groups + 1));
        self::displayTable();
        self::displayBarGraph();
      }
    }

    private function arrayValueToChartLabel($arrayValue) {
      switch ($arrayValue) {
        case 'monthTotal': return 'Total';
        case 'contract': return 'Contract';
        case 'credit': return 'Credit';
        case 'onCall': return 'On Call';
        case 'withIce': return 'With Ice';
        case 'withoutIce': return 'W/O Ice';
        case 'canceled': return 'Canceled';
        case 'deadRun': return 'Dead Run';
        case 'oneHour': return '1 Hour';
        case 'twoHour': return '2 Hour';
        case 'threeHour': return '3 Hour';
        case 'fourHour': return '4 Hour';
        case 'routine': return 'Routine';
        case 'roundTrip': return 'Round Trip';
        case 'dedicatedRun': return 'Dedicated Run';
        default: return 'Label Error';
      }
    }

    private function displayGroupTotals($pointer) {
      foreach ($this->totals as $key => $value) {
        if ($pointer === $key) {
          $returnData = array_sum($value);
          $returnData .= (array_sum($value) == 0) ? '' : '<br>(' . implode(', ', $value) . ')';
        }
      }
      return $returnData;
    }

    private function sortData() {
      if (count($this->dataSet) < 1) {
        $this->error = 'Chart Data Empty';
        echo $this->error;
        return FALSE;
      } else {
        // Reset properties to empty arrays to prevent data overlap when working with multiple organization members
        $this->totals = $this->testMax = $this->nonZero = $this->labels = $this->orderedData = $this->groupLabels = $this->nonAssocData = $this->heights = $this->margins = $this->counts = [];
        $this->colorCode = 0;
        // Process the data set for the given member
        foreach ($this->dataSet as $test) {
          if (array_key_exists($this->clientID, $test)) {
            $this->testMax[] = $test[$this->clientID]['monthTotal'];
            foreach ($test[$this->clientID] as $key => $value) {
              if (((int)$value !== 0 || $key === 'withIce' || $key === 'withoutIce' || $key === 'contract' || $key === 'onCall') && !in_array($key, $this->nonZero) && !in_array($key, $this->nonZeroIgnore)) {
                $this->nonZero[] = $key;
              }
            }
          }
        }
        foreach ($this->dataSet as $key => $value) {
          if (array_key_exists($this->clientID, $value)) {
            $temp = array_merge(array_flip($this->properOrder), $value[$this->clientID]);
            foreach ($temp as $k => $v) {
              if (in_array($k, $this->nonZero)) {
                if (!in_array($k, $this->labels)) $this->labels[] = $k;
                $this->orderedData[$key][$k] = $v;
                $this->totals[$k][] = $v;
              } elseif ($k === 'startDate') {
                $t = ($temp['monthTotal'] == 1) ? 'Ticket' : 'Tickets';
                $this->groupLabels[] = "{$key}<br>
                <form action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                  <input type=\"hidden\" name=\"formKey\" value=\"{$this->formKey}\" />
                  <input type=\"hidden\" name=\"method\" value=\"GET\" />
                  <input type=\"hidden\" name=\"endPoint\" value=\"tickets\" />
                  <input type=\"hidden\" name=\"startDate\" value=\"{$v}\" />
                  <input type=\"hidden\" name=\"endDate\" value=\"{$temp['endDate']}\" />
                  <input type=\"hidden\" name=\"clientID\" value=\"{$temp['billTo']}\" />
                  <input type=\"hidden\" name=\"allTime\" value=\"N\" />
                  <input type=\"hidden\" name=\"compare\" value=\"0\" />
                  <input type=\"hidden\" name=\"charge\" value=\"10\" />
                  <input type=\"hidden\" name=\"type\" value=\"2\" />
                  <input type=\"hidden\" name=\"display\" value=\"tickets\" />
                  <button type=\"submit\" class=\"submitTicketQuery\">{$temp['monthTotal']} {$t}</button>
                </form>";
              }
            }
          }
        }
        $this->tableLabelGroups = array_chunk($this->labels, 5);
        $this->max = max($this->testMax);
        $this->ratio = $this->graph_height/$this->max;
        foreach ($this->orderedData as $key => $value) {
          $this->nonAssocData[] = array_values($value);
          foreach ($value as $k => $v) {
            $this->heights["{$k}_height"][] = $v * $this->ratio;
            $this->margins["{$k}_margin"][] = $this->graph_height - ($v * $this->ratio);
            $this->counts["{$k}_counts"][] = self::number_format_drop_zero_decimals($v, 2);
          }
        }
        $this->tableHead = $this->monthKeys[0] . ' and ' . $this->monthKeys[count($this->monthKeys) - 1];
        // Set $this->nonZero equal to array keys of the first month in $this->orderedData that this member has tickets in
        $monthKeyIndex = 0;
        for ($i = 0; $i < count($this->monthKeys); $i++) {
          if (array_key_exists($this->monthKeys[$i], $this->orderedData)) {
            $monthKeyIndex = $i;
            break;
          }
        }
        $this->nonZero = array_keys($this->orderedData[$this->monthKeys[$monthKeyIndex]]);
      }
    }

    private function sortDataForMemberCompare() {
      if (count($this->dataSet) < 1) {
        $this->error = 'Chart Data Empty Line';
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
          foreach ($value as $k => $v) {
            if (!in_array($k, $this->memberList)) $this->memberList[] = $k;
            foreach ($v as $k1 => $v1) {
              if ((int)$v1 !== 0 && !in_array($k1, $this->nonZero) && !in_array($k1, $this->nonZeroIgnore)) {
                $this->nonZero[] = $k1;
              }
            }
          }
        }
        // Remove withIce and withoutIce if both are not in this->nonZero
        if ((in_array('withIce', $this->nonZero) && !in_array('withoutIce', $this->nonZero)) || (!in_array('withIce', $this->nonZero) && in_array('withoutIce', $this->nonZero))) {
          $temp1 = array_flip($this->nonZero);
          if (array_key_exists('withIce', $temp1)) unset($tmep1['withIce']);
          if (array_key_exists('withoutIce', $temp1)) unset($temp1['withoutIce']);
          $this->nonZero = array_keys($temp1);
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
        if (count($this->labels) > $this->organizationFlag) {
          $this->split = round((count($this->labels) / 2), 0, PHP_ROUND_HALF_UP);
          $this->tableLabelGroups[] = array_slice($this->labels, 0, $this->split);
          $this->tableLabelGroups[] = array_slice($this->labels, $this->split);
        } else {
          $this->tableLabelGroups[] = $this->labels;
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
      $this->max = max($this->testMax);
      $this->ratio = $this->graph_height/$this->max;
      foreach ($temp as $key => $value) {
        foreach ($value as $k => $v) {
          $this->totals[$k] = $v;
          foreach ($v as $k1 => $v1) {
            $this->heights["{$k1}_height"][] = $v1 * $this->ratio;
            $this->margins["{$k1}_margin"][] = $this->graph_height - ($v1 * $this->ratio);
            $this->counts["{$k1}_counts"][] = self::number_format_drop_zero_decimals($v1, 2);
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
        $this->tableOutput .= "
  <table class=\"member centerDiv\">
    <thead>
      <tr>
        <th class=\"displayHeader\" colspan=\"{$this->headerSpan}\">{$this->tableHead}</th>
      </tr>
    </thead>
    <tbody>";
        for ($j = 0; $j < count($this->monthKeys); $j++) {
          $this->tableOutput .= '
      <tr>
        <th>Month</th>
        <th>Member</th>';
        for ($k = 0; $k < count($this->tableLabelGroups[$i]); $k++) {
          $this->tableOutput .= "
        <th>{$this->arrayValueToChartLabel($this->tableLabelGroups[$i][$k])}</th>";
          if ($k === count($this->tableLabelGroups[$i]) - 1) {
            $this->tableOutput .= '</tr>';
          }
        }
          $this->tableOutput .= "
      <tr>
        <td class=\"center\">{$this->monthKeys[$j]}</td>
        <td colspan=\"{$this->nestedTableColspan}\">";
          for ($x = 0; $x < count($this->memberList); $x++) {
            $this->tableOutput .= "
          <table class=\"wide\">
            <tr class=\"highlight\">
              <td class=\"bar{$x}Label center\">{$this->clientListBy($this->memberList[$x])}</td>";
              for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
                $this->tableOutput .= "
              <td class=\"center highlight2\">{$this->orderedData[$this->monthKeys[$j]][$this->memberList[$x]][$this->tableLabelGroups[$i][$y]]}<br>{$this->displayPercentage($this->orderedData[$this->monthKeys[$j]][$this->memberList[$x]][$this->tableLabelGroups[$i][$y]], $this->totals[$this->tableLabelGroups[$i][$y]])}&#37;</td>
                ";
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
            $this->tableOutput .= "
        <td class=\"center highlight2 red\">{$this->totals[$this->monthKeys[$j]][$this->tableLabelGroups[$i][$y]]}<br>{$this->displayPercentage($this->totals[$this->monthKeys[$j]][$this->tableLabelGroups[$i][$y]], $this->totals[$this->tableLabelGroups[$i][$y]])}&#37;</td>
                ";
              }
          $this->tableOutput .= '
      </tr>
      <tr class="highlight" style="border-bottom: 0.1em solid black;">
        <td></td>
        <th>Query Total:</th>';
          for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
            $this->tableOutput .= "
        <td class=\"center highlight2\">{$this->number_format_drop_zero_decimals($this->totals[$this->tableLabelGroups[$i][$y]], 2)}</td>
                ";
              }
          $this->tableOutput .= '
      </tr>';
        }
        $this->tableOutput .= '
    </tbody>
  </table>';
      }
      echo $this->tableOutput;
    }

    private function displayTable() {
      if ($this->error != NULL) return FALSE;
      $this->tableHeadPrefix = ($this->compare === TRUE) ? 'Comparing Tickets Between ' : 'Tickets for the period between ';
      if ($this->organizationFlag === TRUE) {
        $this->tableHead .= "<br><span class=\"medium\">{$this->clientListBy($this->clientID)}</span>";
      }
      $count = 'count';
      $this->tableOutput = "
      <div class=\"break ticketTable\">
      <table class=\"center\">
        <thead>
          <th class=\"displayHeader\" colspan=\"{$count($this->tableLabelGroups[0])}\">{$this->tableHeadPrefix}{$this->tableHead}</th>
        </thead>
        <tbody>";
      for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
        for ($j = 0; $j < count($this->tableLabelGroups[$i]); $j++) {
          if ($j === 0) $this->tableOutput .= '
          <tr>';
          $code = $j + $this->colorCode;
          $this->tableOutput .= "
            <th class=\"bar{$code}Label\">{$this->arrayValueToChartLabel($this->tableLabelGroups[$i][$j])}</th>";
          if ($j === count($this->tableLabelGroups[$i]) - 1) $this->tableOutput .= '
          </tr>';
        }
        for ($x = 0; $x < count($this->tableLabelGroups[$i]); $x++) {
          if ($x === 0) $this->tableOutput .= '
          <tr>';
          $this->tableOutput .= "
            <td class=\"center\">{$this->displayGroupTotals($this->tableLabelGroups[$i][$x])}</td>";
          if ($x === count($this->tableLabelGroups[$i]) - 1) $this->tableOutput .= '
          </tr>';
        }
        $this->colorCode += 5;
      }
      $this->tableOutput .= '
        </tbody>
      </table>
      </div>';
      echo $this->tableOutput;
    }

    private function displayBarGraph() {
      if ($this->error != NULL) return FALSE;
      $this->graphOutput = "
      <div class=\"ticketGraphContainer\">
        <div class=\"centerDiv\" style=\"border:solid 0.1em #e1e1e1; background-color:#f4f4f4; height:{$this->graph_height}em; width:{$this->graph_width}em; margin-top:1.25em; overflow: hidden;\">
          <div style=\"height:{$this->graph_height}em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      // Generate bar graph output for each month
      for ($i = 0; $i < count($this->nonAssocData); $i++) {
        $this->graphOutput .= "
          <div style=\"height:{$this->graph_height}em; width:{$this->interval_width}em; margin:0; padding:0;\" class=\"barContainer\">
          <div style=\"height:{$this->graph_height}em;width:{$this->bar_gap}em;\" class=\"gap\"></div>";
        for ($j = 0; $j < count($this->nonAssocData[$i]); $j++) {
          $height = $this->heights[$this->nonZero[$j] . '_height'][$i];
          $margin = $this->margins[$this->nonZero[$j] . '_margin'][$i];
          $this->graphOutput .= "
            <div title=\"{$this->arrayValueToChartLabel($this->nonZero[$j])}&#10;{$this->totals[$this->nonZero[$j]][$i]}\" style=\"height:{$height}em; width:{$this->bar_width}em; margin-top:{$margin}em;\" class=\"bar{$j}\"></div>
            <div style=\"height:{$this->graph_height}em;width:{$this->bar_gap}em;\" class=\"gap\"></div>
          ";
        }
        $this->graphOutput .= "
          </div>
          <div style=\"height:{$this->graph_height}em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      }
      $this->graphOutput .= "
          <div style=\"clear:both;\"></div>
        </div>
        <div class=\"centerDiv\" style=\"height:2.75em; background-color:#8c8c8c; width:{$this->graph_width}em; color:#fff; border:solid 1px #666;\">
        <div style=\"height:2.75em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      foreach ($this->groupLabels as $label) {
        $this->graphOutput .= "
          <div style=\"width:{$this->interval_width}em;padding-left:{$this->interval_border}em; padding-right:{$this->interval_border}em;\" class=\"chartLabels\">{$label}</div>
          <div style=\"height:2.75em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      }
      $this->graphOutput .= '
        </div>
      </div>
      ';
      echo $this->graphOutput;
    }

    private function displayCompareGraph() {
      if ($this->error != NULL) return FALSE;
      $this->currentChart = self::chartIndexToProperty();
      if ($this->currentChart === NULL) {
        echo $this->graphOutput;
        return FALSE;
      }
      // dynamically calculate the width of the graph
      $this->groups = count($this->currentChart);  // Number of groups
      $this->graph_width = ($this->interval_width * $this->groups) + ($this->interval_gap * ($this->groups + 1));
      if ($this->compareMembers === TRUE) {
        $this->graphOutput .= "
        <p class=\"center displayHeader\">{$this->tableHead}</p>
        <p style=\"display:flex; justify-content:space-around;\">";
          for ($i = 0; $i < count($this->memberList); $i++) {
            $this->graphOutput .= "
          <span class=\"bar{$i}Label\">{$this->clientListBy($this->memberList[$i])}</span>";
          }
        $this->graphOutput .= '
        </p>';
      }
      $this->graphOutput .= "
        <div class=\"ticketGraphContainer\">
        <div class=\"centerDiv\" style=\"border:solid 0.1em #e1e1e1; background-color:#f4f4f4; height:{$this->graph_height}em; width:{$this->graph_width}em; margin-top:1.25em; /* padding-top:0.75em; */ overflow: hidden;\">
          <div style=\"height:{$this->graph_height}em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      // Sort out the bars here
      for ($i = 0; $i < count($this->currentChart); $i++) {
        $this->graphOutput .= "
          <div style=\"height:{$this->graph_height}em; width:{$this->interval_width}em; margin:0; padding:0;\" class=\"barContainer\">
          <div style=\"height:{$this->graph_height}em;width:{$this->bar_gap}em;\" class=\"gap\"></div>";
        for ($j = 0; $j < count($this->monthKeys); $j++) {
          $height = $this->heights[$this->currentChart[$i] . '_height'][$j];
          $margin = $this->margins[$this->currentChart[$i] . '_margin'][$j];
          $this->graphOutput .= "
            <div title=\"{$this->totals[$this->monthKeys[$j]][$this->currentChart[$i]]}\" style=\"height:{$height}em; width:{$this->bar_width}em; margin-top:{$margin}em;\" class=\"bar{$j}\"></div>
            <div style=\"height:{$this->graph_height}em;width:{$this->bar_gap}em;\" class=\"gap\"></div>
          ";
        }
        $this->graphOutput .= "
          </div>
          <div style=\"height:{$this->graph_height}em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      }
      $this->graphOutput .= "
          <div style=\"clear:both;\"></div>
        </div>
        <div class=\"centerDiv\" style=\"height:2.75em; background-color:#8c8c8c; width:{$this->graph_width}em; color:#fff; border:solid 1px #666;\">
        <div style=\"height:2.75em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      foreach ($this->currentChart as $label) {
        $this->graphOutput .= "
          <div style=\"width:{$this->interval_width}em;\" class=\"chartLabels\">{$this->arrayValueToChartLabel($label)}</div>
          <div style=\"height:2.75em;width:{$this->interval_gap}em;\" class=\"space\"></div>";
      }
      $this->graphOutput .= '
        </div>
      ';
      $this->chartIndex++;
      self::displayCompareGraph();
    }
  }
