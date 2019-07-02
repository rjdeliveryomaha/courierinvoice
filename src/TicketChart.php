<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

  class TicketChart extends CommonFunctions {
    protected $dataSet;
    protected $clientID;
    protected $compare;
    protected $compareMembers;
    protected $ListBy;
    protected $organizationFlag = false;
    protected $orgClients = [];
    private $memberList = [];
    private $totals = [];
    private $monthKeys = [];
    // Define the order for the keys in $dataSet
    private $properOrder = [ 'monthTotal', 'contract', 'onCall', 'credit', 'withIce', 'withoutIce', 'canceled',
      'deadRun', 'oneHour', 'twoHour', 'threeHour', 'fourHour', 'routine', 'roundTrip', 'dedicated'
    ];
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
    private $alwaysInclude = [ 'contract', 'onCall' ];
    protected $nonZero = [];
    private $orderedData;
    private $nonAssocData;
    private $groupLabels = [];
    private $bars;
    private $tableOutput;
    private $graphOutput;
    private $colorCode = 0;
    private $addPDFbutton = true;

    public function __construct($options, $data)
    {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
      if ($this->organizationFlag === true && (is_array($this->clientID) && count($this->clientID) > 1)) {
        for ($i = 0; $i < count($this->clientID); $i++) {
          $this->orgClients[$this->clientID[$i]] = $this->members[$this->clientID[$i]];
        }
      }
    }

    public function displayChart()
    {
      $returnData = '';
      foreach ($this->dataSet as $key => $value) {
        $this->monthKeys[] = $key;
      }
      $multiMemberTest = false;
      for ($i = 0; $i < count($this->monthKeys); $i++) {
        if (count($this->dataSet[$this->monthKeys[$i]]) > 1) {
          $multiMemberTest = true;
          break;
        }
      }
      if ($multiMemberTest === true) {
        if ($this->compareMembers === false) {
          foreach ($this->orgClients as $key => $value) {
            $this->clientID = $key;
            $this->error = self::sortData();
            if ($this->error !== false) {
              return false;
            }
            $returnData .= self::displayTable();
            $returnData .= self::displayBarGraph();
            $this->addPDFbutton = false;
          }
        } elseif ($this->compareMembers === true) {
          $this->error = self::sortDataForMemberCompare();
          if ($this->error !== false) {
            return false;
          }
          $returnData .= self::displayCompareTable();
          self::resortData();
          $returnData .= self::displayCompareGraph();
        } else {
          $this->error = 'Invalid Member Compare Value Line ' . __line__;
          return false;
        }
      } else {
        foreach ($this->dataSet[$this->monthKeys[0]] as $key => $value) {
          $this->clientID = $key;
        }
        $this->error = self::sortData();
        if ($this->error !== false) {
          return false;
        }
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

    private function displayGroupTotals($pointer)
    {
      foreach ($this->totals as $key => $value) {
        if ($pointer === $key) {
          $returnData = array_sum($value);
          $returnData .= (array_sum($value) == 0) ? '' : '<br>(' . implode(', ', $value) . ')';
        }
      }
      return $returnData;
    }

    private function sortData()
    {
      if (count($this->dataSet) < 1) {
        return '<p class="center result">Ticket Chart Data Empty Line ' . __line__ . '</p>';
      } else {
        // Reset properties to empty arrays to prevent data overlap when working with multiple organization members
        $this->totals = $this->testMax = $this->nonZero = $this->labels = $this->orderedData = $this->groupLabels =
        $this->nonAssocData = $this->heights = $this->margins = $this->counts = [];
        $this->colorCode = 0;
        // Process the data set for the given member
        foreach ($this->dataSet as $test) {
          if (array_key_exists($this->clientID, $test)) {
            $this->testMax[] = $test[$this->clientID]['monthTotal'];
            foreach ($test[$this->clientID] as $key => $value) {
              if (
                ((int)$value !== 0 || in_array($key, $this->alwaysInclude)) &&
                !in_array($key, $this->nonZero) &&
                !in_array($key, $this->nonZeroIgnore)
            ) {
                $this->nonZero[] = $key;
              }
            }
          }
        }
        if ($this->options['displayDryIce'] === false) {
          $temp = array_flip($this->nonZero);
          unset($temp['withIce'], $temp['withoutIce']);
          $this->nonZero = array_flip($temp);
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
                  <button type=\"submit\" class=\"submitTicketQuery\">{$temp['monthTotal']}<br>{$t}</button>
                </form>";
              }
            }
          }
        }
        $this->tableLabelGroups = array_chunk($this->labels, 5);
        $this->max = (empty($this->testMax)) ? 1 : max($this->testMax);
        $this->ratio = $this->options['chart_height'] / $this->max;
        foreach ($this->orderedData as $key => $value) {
          $this->nonAssocData[] = array_values($value);
          foreach ($value as $k => $v) {
            $this->heights["{$k}_height"][] = $v * $this->ratio;
            $this->counts["{$k}_counts"][] = self::number_format_drop_zero_decimals($v, 2);
          }
        }
        $this->tableHead = $this->monthKeys[0] . ' &amp; ' . $this->monthKeys[count($this->monthKeys) - 1];
        // Set $this->nonZero equal to array keys of the first month in $this->orderedData that this member has tickets in
        $monthKeyIndex = 0;
        for ($i = 0; $i < count($this->monthKeys); $i++) {
          if (array_key_exists($this->monthKeys[$i], $this->orderedData)) {
            $monthKeyIndex = $i;
            break;
          }
        }
        $this->nonZero = (isset($this->orderedData[$this->monthKeys[$monthKeyIndex]])) ? array_keys($this->orderedData[$this->monthKeys[$monthKeyIndex]]) : [];
      }
      return false;
    }

    private function sortDataForMemberCompare()
    {
      if (count($this->dataSet) < 1) {
        return '<p class="center result">Ticket Chart Data Empty Line' . __line__ . '</p>';
      }
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
      if (
        (in_array('withIce', $this->nonZero) && !in_array('withoutIce', $this->nonZero)) ||
        (!in_array('withIce', $this->nonZero) && in_array('withoutIce', $this->nonZero))
      ) {
        $temp1 = array_flip($this->nonZero);
        unset($tmep1['withIce'], $temp1['withoutIce']);
        $this->nonZero = array_keys($temp1);
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
            if (
              !isset($this->totals[$this->monthKeys[$i]][$value])
            ) {
              $this->totals[$this->monthKeys[$i]][$value] = 0;
            }

            $this->totals[$this->monthKeys[$i]][$value] += $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]]['counts'][$value];
            $temp += $this->orderedData[$this->monthKeys[$i]][$this->memberList[$j]]['counts'][$value];
          }
        }
        $this->totals[$value] = $temp;
      }
      if (count($this->labels) > 7) {
        $temp = array_shift($this->labels);
        $this->split = round((count($this->labels) / 2), 0, PHP_ROUND_HALF_UP);
        $this->tableLabelGroups[] = array_slice($this->labels, 0, $this->split);
        $this->tableLabelGroups[] = array_slice($this->labels, $this->split);
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
      return false;
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
        <form id=\"ticketPDFform\" target=\"_blank\" method=\"post\" action=\"pdf\">
          <input type=\"hidden\" name=\"title\" value=\"invoiceChart\" form=\"ticketPDFform\" />
          <input type=\"hidden\" name=\"type\" value=\"chart\" form=\"ticketPDFform\" />
          <input type=\"hidden\" name=\"paperFormat\" value=\"{$format}\" form=\"ticketPDFform\" />
          <input type=\"hidden\" name=\"paperOrientation\" value=\"landscape\" form=\"ticketPDFform\" />
          <input type=\"hidden\" name=\"formKey\" form=\"ticketPDFform\" />
          <input type=\"hidden\" name=\"content\" form=\"ticketPDFform\" />
          <button type=\"button\" id=\"ticketPDF\" form=\"ticketPDFform\">PDF</button>
        </form>" : '';
      foreach ($this->monthKeys as $_ => $month) {
        foreach ($membergroup as $__ => $memberList) {
          for ($i = 0; $i < count($this->tableLabelGroups); $i++) {
            $page_break = ((count($memberList) < 6 && $i === count($this->tableLabelGroups) - 1) || count($memberList) > 5) ? 'style="page-break-after: always;"' : '';
            $this->tableOutput .= "
        <table class=\"ticketTable member centerDiv\" {$page_break}>
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
              {$this->orderedData[$month][$memberList[$x]]['counts'][$this->tableLabelGroups[$i][$y]]}
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
              $this->tableOutput .= "
            <td class=\"center highlight2 error\">
              {$this->totals[$month][$this->tableLabelGroups[$i][$y]]}
              <br>
              {$this->displayPercentage($this->totals[$month][$this->tableLabelGroups[$i][$y]], $this->totals[$this->tableLabelGroups[$i][$y]])}&#37;
            </td>";
            }
            $this->tableOutput .= "
          </tr>
          <tr>
            <th>{$this->tableHead}</th>";
            for ($y = 0; $y < count($this->tableLabelGroups[$i]); $y++) {
              $this->tableOutput .= "
            <td class=\"center highlight2\">
              {$this->number_format_drop_zero_decimals($this->totals[$this->tableLabelGroups[$i][$y]], 2)}
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
      $this->tableOutput = (class_exists('Dompdf\Dompdf') && $this->addPDFbutton === true && $this->options['enableChartPDF'] === true) ? '
        <form id="ticketPDFform" target="_blank" method="post" action="pdf">
          <input type="hidden" name="title" value="ticketChart" form="ticketPDFform" />
          <input type="hidden" name="type" value="chart" form="ticketPDFform" />
          <input type="hidden" name="paperOrientation" value="landscape" form="ticketPDFform" />
          <input type="hidden" name="formKey" form="ticketPDFform" />
          <input type="hidden" name="content" form="ticketPDFform" />
          <button type="button" id="ticketPDF" form="ticketPDFform">PDF</button>
        </form>' : '';
      $this->tableHeadPrefix = ($this->compare === true) ? 'Comparing Tickets Between ' : 'Tickets Between ';
      if ($this->organizationFlag === true) {
        $this->tableHead .= "<br><span class=\"medium\">{$this->clientListBy($this->clientID)}</span>";
      }
      $count = 'count';
      if (!isset($this->tableLabelGroups[0])) {
        $this->tableOutput .= "
      <div class=\"ticketTable\">
        <p class=\"center displayHeader\">{$this->tableHeadPrefix}{$this->tableHead}</p>
        <p class=\"center\">No Data On File</p>
      </div>";
      return $this->tableOutput;
      }
      $this->tableOutput .= "
      <table class=\"ticketTable\">
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
      </table>';
      return $this->tableOutput;
    }

    private function displayBarGraph()
    {
      if (!isset($this->tableLabelGroups[0])) return '<div class="bargraph"></div>';
      $this->graphOutput = "
        <table class=\"bargraph\">
          <tbody>
            <tr class=\"graphBody\" style=\"height:{$this->options['chart_height']}rem;\">";
      $firstBarMargin = (100 - (7 * (count($this->nonAssocData[0])  + (count($this->nonAssocData[0]) - 1)))) / 2;
      for ($i = 0; $i < count($this->nonAssocData); $i++) {
        $this->graphOutput .= '
            <td class="barContainer">';
        for ($j = 0; $j < count($this->nonAssocData[$i]); $j++) {
          $height = $this->heights[$this->nonZero[$j] . '_height'][$i];
          $margin = ($j === 0) ? " margin-left: {$firstBarMargin}%;" : '';
          $this->graphOutput .= "<div title=\"{$this->arrayValueToChartLabel($this->nonZero[$j])}&#10;{$this->totals[$this->nonZero[$j]][$i]}\" style=\"height:{$height}rem;{$margin}\" class=\"bar{$j}\"></div>";
          if ($j !== count($this->nonAssocData[$i]) - 1) {
            $this->graphOutput .= '<div class="gap"></div>';
          }
        }
        $this->graphOutput .= '
            </td>';
        if ($i !== count($this->nonAssocData) - 1) {
          $this->graphOutput .= '
            <td class="space"></td>';
        }
      }
      $this->graphOutput .= '
          </tr>
        </tbody>
        <tfoot>
          <tr class="graphFoot">';
      for ($i = 0; $i < count($this->groupLabels); $i++) {
        $this->graphOutput .= "
            <th class=\"chartLabels\">{$this->groupLabels[$i]}</th>
        ";
        if ($i !== count($this->groupLabels) - 1) {
          $this->graphOutput .= '
            <th class="space"></th>';
        }
      }
      $this->graphOutput .= '
          </tr>
        </tfoot>
      </table>';
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
                $this->graphOutput .= "<div class=\"bar{$l}\" title=\"{$this->orderedData[$month][$memberList[$j][$l]]['counts'][$this->tableLabelGroups[$i][$k]]}\" style=\"height: {$this->orderedData[$month][$memberList[$j][$l]]['heights'][$this->tableLabelGroups[$i][$k]]}rem;{$margin}\"></div>";
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
