<?php

  namespace rjdeliveryomaha\courierinvoice;

  class Scheduling
  {
    private static $weekdays = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];
    private static $schedule = [
      [
        'schedule_index' => 1,
        'code' => 'a1',
        'literal' => 'Every Day'
      ],
      [
        'schedule_index' => 2,
        'code' => 'a2',
        'literal' => 'Every Weekday'
      ],
      [
        'schedule_index' => 3,
        'code' => 'a3',
        'literal' => 'Every Monday'
      ],
      [
        'schedule_index' => 4,
        'code' => 'a4',
        'literal' => 'Every Tuesday'
      ],
      [
        'schedule_index' => 5,
        'code' => 'a5',
        'literal' => 'Every Wednesday'
      ],
      [
        'schedule_index' => 6,
        'code' => 'a6',
        'literal' => 'Every Thursday'
      ],
      [
        'schedule_index' => 7,
        'code' => 'a7',
        'literal' => 'Every Friday'
      ],
      [
        'schedule_index' => 8,
        'code' => 'a8',
        'literal' => 'Every Saturday'
      ],
      [
        'schedule_index' => 9,
        'code' => 'a9',
        'literal' => 'Every Sunday'
      ],
      [
        'schedule_index' => 10,
        'code' => 'b1',
        'literal' => 'Every Other Day'
      ],
      [
        'schedule_index' => 11,
        'code' => 'b2',
        'literal' => 'Every Other Weekday'
      ],
      [
        'schedule_index' => 12,
        'code' => 'b3',
        'literal' => 'Every Other Monday'
      ],
      [
        'schedule_index' => 13,
        'code' => 'b4',
        'literal' => 'Every Other Tuesday'
      ],
      [
        'schedule_index' => 14,
        'code' => 'b5',
        'literal' => 'Every Other Wednesday'
      ],
      [
        'schedule_index' => 15,
        'code' => 'b6',
        'literal' => 'Every Other Thursday'
      ],
      [
        'schedule_index' => 16,
        'code' => 'b7',
        'literal' => 'Every Other Friday'
      ],
      [
        'schedule_index' => 17,
        'code' => 'b8',
        'literal' => 'Every Other Saturday'
      ],
      [
        'schedule_index' => 18,
        'code' => 'b9',
        'literal' => 'Every Other Sunday'
      ],
      [
        'schedule_index' => 19,
        'code' => 'c1',
        'literal' => 'Every First Day'
      ],
      [
        'schedule_index' => 20,
        'code' => 'c2',
        'literal' => 'Every First Weekday'
      ],
      [
        'schedule_index' => 21,
        'code' => 'c3',
        'literal' => 'Every First Monday'
      ],
      [
        'schedule_index' => 22,
        'code' => 'c4',
        'literal' => 'Every First Tuesday'
      ],
      [
        'schedule_index' => 23,
        'code' => 'c5',
        'literal' => 'Every First Wednesday'
      ],
      [
        'schedule_index' => 24,
        'code' => 'c6',
        'literal' => 'Every First Thursday'
      ],
      [
        'schedule_index' => 25,
        'code' => 'c7',
        'literal' => 'Every First Friday'
      ],
      [
        'schedule_index' => 26,
        'code' => 'c8',
        'literal' => 'Every First Saturday'
      ],
      [
        'schedule_index' => 27,
        'code' => 'c9',
        'literal' => 'Every First Sunday'
      ],
      [
        'schedule_index' => 28,
        'code' => 'd1',
        'literal' => 'Every Second Day'
      ],
      [
        'schedule_index' => 29,
        'code' => 'd2',
        'literal' => 'Every Second Weekday'
      ],
      [
        'schedule_index' => 30,
        'code' => 'd3',
        'literal' => 'Every Second Monday'
      ],
      [
        'schedule_index' => 31,
        'code' => 'd4',
        'literal' => 'Every Second Tuesday'
      ],
      [
        'schedule_index' => 32,
        'code' => 'd5',
        'literal' => 'Every Second Wednesday'
      ],
      [
        'schedule_index' => 33,
        'code' => 'd6',
        'literal' => 'Every Second Thursday'
      ],
      [
        'schedule_index' => 34,
        'code' => 'd7',
        'literal' => 'Every Second Friday'
      ],
      [
        'schedule_index' => 35,
        'code' => 'd8',
        'literal' => 'Every Second Saturday'
      ],
      [
        'schedule_index' => 36,
        'code' => 'd9',
        'literal' => 'Every Second Sunday'
      ],
      [
        'schedule_index' => 37,
        'code' => 'e1',
        'literal' => 'Every Third Day'
      ],
      [
        'schedule_index' => 38,
        'code' => 'e2',
        'literal' => 'Every Third Weekday'
      ],
      [
        'schedule_index' => 39,
        'code' => 'e3',
        'literal' => 'Every Third Monday'
      ],
      [
        'schedule_index' => 40,
        'code' => 'e4',
        'literal' => 'Every Third Tuesday'
      ],
      [
        'schedule_index' => 41,
        'code' => 'e5',
        'literal' => 'Every Third Wednesday'
      ],
      [
        'schedule_index' => 42,
        'code' => 'e6',
        'literal' => 'Every Third Thursday'
      ],
      [
        'schedule_index' => 43,
        'code' => 'e7',
        'literal' => 'Every Third Friday'
      ],
      [
        'schedule_index' => 44,
        'code' => 'e8',
        'literal' => 'Every Third Saturday'
      ],
      [
        'schedule_index' => 45,
        'code' => 'e9',
        'literal' => 'Every Third Sunday'
      ],
      [
        'schedule_index' => 46,
        'code' => 'f1',
        'literal' => 'Every Fourth Day'
      ],
      [
        'schedule_index' => 47,
        'code' => 'f2',
        'literal' => 'Every Fourth Weekday'
      ],
      [
        'schedule_index' => 48,
        'code' => 'f3',
        'literal' => 'Every Fourth Monday'
      ],
      [
        'schedule_index' => 49,
        'code' => 'f4',
        'literal' => 'Every Fourth Tuesday'
      ],
      [
        'schedule_index' => 50,
        'code' => 'f5',
        'literal' => 'Every Fourth Wednesday'
      ],
      [
        'schedule_index' => 51,
        'code' => 'f6',
        'literal' => 'Every Fourth Thursday'
      ],
      [
        'schedule_index' => 52,
        'code' => 'f7',
        'literal' => 'Every Fourth Friday'
      ],
      [
        'schedule_index' => 53,
        'code' => 'f8',
        'literal' => 'Every Fourth Saturday'
      ],
      [
        'schedule_index' => 54,
        'code' => 'f9',
        'literal' => 'Every Fourth Sunday'
      ],
      [
        'schedule_index' => 55,
        'code' => 'g1',
        'literal' => 'Every Last Day'
      ],
      [
        'schedule_index' => 56,
        'code' => 'g2',
        'literal' => 'Every Last Weekday'
      ],
      [
        'schedule_index' => 57,
        'code' => 'g3',
        'literal' => 'Every Last Monday'
      ],
      [
        'schedule_index' => 58,
        'code' => 'g4',
        'literal' => 'Every Last Tuesday'
      ],
      [
        'schedule_index' => 59,
        'code' => 'g5',
        'literal' => 'Every Last Wednesday'
      ],
      [
        'schedule_index' => 60,
        'code' => 'g6',
        'literal' => 'Every Last Thursday'
      ],
      [
        'schedule_index' => 61,
        'code' => 'g7',
        'literal' => 'Every Last Friday'
      ],
      [
        'schedule_index' => 62,
        'code' => 'g8',
        'literal' => 'Every Last Saturday'
      ],
      [
        'schedule_index' => 63,
        'code' => 'g9',
        'literal' => 'Every Last Sunday'
      ],
      [
        'schedule_index' => 64,
        'code' => 'h5',
        'literal' => 'Every 5th'
      ],
      [
        'schedule_index' => 65,
        'code' => 'h6',
        'literal' => 'Every 6th'
      ],
      [
        'schedule_index' => 66,
        'code' => 'h7',
        'literal' => 'Every 7th'
      ],
      [
        'schedule_index' => 67,
        'code' => 'h8',
        'literal' => 'Every 8th'
      ],
      [
        'schedule_index' => 68,
        'code' => 'h9',
        'literal' => 'Every 9th'
      ],
      [
        'schedule_index' => 69,
        'code' => 'h10',
        'literal' => 'Every 10th'
      ],
      [
        'schedule_index' => 70,
        'code' => 'h11',
        'literal' => 'Every 11th'
      ],
      [
        'schedule_index' => 71,
        'code' => 'h12',
        'literal' => 'Every 12th'
      ],
      [
        'schedule_index' => 72,
        'code' => 'h13',
        'literal' => 'Every 13th'
      ],
      [
        'schedule_index' => 73,
        'code' => 'h14',
        'literal' => 'Every 14th'
      ],
      [
        'schedule_index' => 74,
        'code' => 'h15',
        'literal' => 'Every 15th'
      ],
      [
        'schedule_index' => 75,
        'code' => 'h16',
        'literal' => 'Every 16th'
      ],
      [
        'schedule_index' => 76,
        'code' => 'h17',
        'literal' => 'Every 17th'
      ],
      [
        'schedule_index' => 77,
        'code' => 'h18',
        'literal' => 'Every 18th'
      ],
      [
        'schedule_index' => 78,
        'code' => 'h19',
        'literal' => 'Every 19th'
      ],
      [
        'schedule_index' => 79,
        'code' => 'h20',
        'literal' => 'Every 20th'
      ],
      [
        'schedule_index' => 80,
        'code' => 'h21',
        'literal' => 'Every 21st'
      ],
      [
        'schedule_index' => 81,
        'code' => 'h22',
        'literal' => 'Every 22nd'
      ],
      [
        'schedule_index' => 82,
        'code' => 'h23',
        'literal' => 'Every 23rd'
      ],
      [
        'schedule_index' => 83,
        'code' => 'h24',
        'literal' => 'Every 24th'
      ],
      [
        'schedule_index' => 84,
        'code' => 'h25',
        'literal' => 'Every 25th'
      ],
      [
        'schedule_index' => 85,
        'code' => 'h26',
        'literal' => 'Every 26th'
      ],
      [
        'schedule_index' => 86,
        'code' => 'h27',
        'literal' => 'Every 27th'
      ],
      [
        'schedule_index' => 87,
        'code' => 'h28',
        'literal' => 'Every 28th'
      ]
    ];

    public static function codeFromIndex(int $index)
    {
      return self::$schedule[$index]['code'] ?? false;
    }

    public static function literalFromIndex(int $index)
    {
      return self::$schedule[$index]['literal'] ?? false;
    }

    public static function indexFromCode(string $code)
    {
      for ($i = 0; $i < count(self::$schedule); $i++) {
        if (self::$schedule[$i]['code'] === $code) return self::$schedule[$i]['schedule_index'];
      }
      return false;
    }

    public static function literalFromCode(string $code)
    {
      for ($i = 0; $i < count(self::$schedule); $i++) {
        if (self::$schedule[$i]['code'] === $code) return self::$schedule[$i]['literal'];
      }
      return false;
    }

    public static function indexFromLiteral(string $literal)
    {
      for ($i = 0; $i < count(self::$schedule); $i++) {
        if (self::$schedule[$i]['literal'] === $literal) return self::$schedule[$i]['schedule_index'];
      }
      return false;
    }

    public static function codeFromLiteral(string $literal)
    {
      for ($i = 0; $i < count(self::$schedule); $i++) {
        if (self::$schedule[$i]['literal'] === $literal) return self::$schedule[$i]['code'];
      }
      return false;
    }

    public static function testCode(string $code, \dateTime $startDate, \dateTime $testDate)
    {
      $literal = self::literalFromCode($code);
      return self::isScheduleToday($literal, $startDate, $testDate);
    }

    public static function testIndex(int $index, \dateTime $startDate, \dateTime $testDate)
    {
      $literal = self::literalFromIndex($index);
      return self::isScheduleToday($literal, $startDate, $testDate);
    }

    public static function testLiteral(string $literal, \dateTime $startDate, \dateTime $testDate)
    {
      return self::isScheduleToday($literal, $startDate, $testDate);
    }

    private static function isFirstWeekday(\dateTime $dateObject)
    {
      switch($dateObject->format('j')) {
        case 1: return $dateObject->format('N') <= 5;
        case 2:
        case 3: return $dateObject->format('N') == 1;
        default: return false;
      }
    }

    private static function isScheduleToday($literal, $startDate, $testDate)
    {
      $test = explode(' ', $literal);
      if (count($test) < 2 || count($test) > 3) return false;
      if (count($test) == 2) {
        switch ($test[1]) {
          case 'Day':
            return true;
          case 'Weekday':
            return $testDate->format('N') <= 5;
          default:
            return (preg_match('/(^\d{1,2}\D{2}$)/', $test[1]) === 1) ?
              (int)substr($test[1], 0, -2) === $testDate->format('j') : $test[1] == $testDate->format('l');
        }
      }
      if ($test[2] != 'Day' && $test[2] != 'Weekday' && $test[2] != $testDate->format('l')) return false;
      // set the time to be equal on both date objects so that all whole days are counted
      $testDate->setTime(0,0);
      $startDate->setTime(0,0);
      switch ($test[1]) {
        case 'Other':
          switch ($test[2]) {
            case 'Day':
              $diff = $startDate->diff($testDate);
              return $diff->days % 2 === 0;
            case 'Weekday':
              $days = 0;
              do {
                $startDate->modify('+ 1 day');
                if ($startDate->format('N') < 6) $days++;
              } while ($startDate < $testDate);
              return $days % 2 == 0;
            default:
              /* Mon - Sun */
              if ($startDate->format('l') != $test[2]) {
                $arr = array_flip(self::$weekdays);
                $x = $arr[$test[2]];
                $y = $arr[$startDate->format('l')];
                $res = $x - $y;
                $mod = ($res < 0) ? '' : '+';
                $startDate->modify("$mod $res day");
              }
              $diff = $startDate->diff($testDate);
              return $diff->days % 14 == 0;
          }
          break;
        case 'First':
          if ($test[2] == 'Day') {
            return $testDate->format('j') == '1';
          } elseif ($test[2] === 'Weekday') {
            return self::isFirstWeekday($testDate);
          } else {
            return $testDate->format('Y-m-d') ==
              date('Y-m-d', strtotime("first {$test[2]} of {$testDate->format('F Y')}"));
          }
          break;
        case 'Second':
          if ($test[2] == 'Day') {
            return $testDate->format('j') == '2';
          } elseif ($test[2] === 'Weekday') {
            return self::isFirstWeekday($testDate->modify('- 1 day'));
          } else {
            return $testDate->format('Y-m-d') ==
              date('Y-m-d', strtotime("second {$test[2]} of {$testDate->format('F Y')}"));
          }
          break;
        case 'Third':
          if ($test[2] == 'Day') {
            return $testDate->format('j') == '3';
          } elseif ($test[2] == 'Weekday') {
            return self::isFirstWeekday($testDate->modify('- 2 day'));
          } else {
            return $testDate->format('Y-m-d') ==
              date('Y-m-d', strtotime("third {$test[2]} of {$testDate->format('F Y')}"));
          }
          break;
        case 'Fourth':
          if ($test[2] == 'Day') {
            return $testDate->format('j') == '4';
          } elseif ($test[2] === 'Weekday') {
            return self::isFirstWeekday($testDate->modify('- 3 day'));
          } else {
            return $testDate->format('Y-m-d') ==
              date('Y-m-d', strtotime("fourth {$test[2]} of {$testDate->format('F Y')}"));
          }
          break;
        case 'Last':
          if ($test[2] === 'Weekday') {
            switch ($testDate->format('t') - $testDate->format('j')) {
              case 0: return $testDate->format('N') <= 5;
              case 1:
              case 2: return $testDate->format('N') == 5;
              default: return false;
            }
          } else {
            return $testDate->format('Y-m-d') ==
              date('Y-m-d', strtotime("last {$test[2]} of {$testDate->format('F Y')}"));
          }
          break;
        default: return false;
      }
    }
  }
