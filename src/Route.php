<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  use rjdeliveryomaha\courierinvoice\Scheduling;

  class Route extends CommonFunctions {
    protected $newTickets;
    protected $activeTicketSet = [];
    protected $onCallTicketSet = [];
    protected $contractTicketSet = [];
    protected $transferredTicketSet = [];
    protected $ticketSet = [];
    protected $singleLocation = [];
    protected $multiLocation = [];
    protected $cancelations = [];
    protected $processTransfer = false;
    protected $rescheduledRuns;
    protected $rescheduledRunsList = [];
    protected $todaysRoutes = [];
    protected $cancelRoute = false;
    protected $driverID;
    protected $driverName;
    protected $yesterday;
    protected $today;
    protected $backstop;
    protected $testDate;
    protected $testDateObject;
    protected $startDateObject;
    protected $add;
    protected $todaysRouteTickets = [];
    protected $runList = [];
    protected $locations = [];
    protected $locationTest;
    protected $LastSeen;
    // List of drivers on file for transfer data list for drivers without dispatch authorization
    protected $transferList;
    private $timestamp;
    private $tTest;

    public function __construct($options, $data=[])
    {
      try {
        parent::__construct($options, $data);
      } catch (\Exception $e) {
        throw $e;
      }
      $this->driverID = $_SESSION['DriverID'] ?? $_SESSION['DispatchID'];
      $this->driverName = "{$_SESSION['FirstName']} {$_SESSION['LastName']}";
      $this->LastSeen = $_SESSION['LastSeen'];
      try {
        self::createDateObject();
      } catch (\Exception $e) {
        throw $e;
      }
      $this->timestamp = $this->dateObject->getTimestamp();
      $this->today = $this->dateObject->format('Y-m-d');
      $temp = clone $this->dateObject;
      $this->yesterday = $temp->modify('- 1 day')->format('Y-m-d');
      $this->backstop = $temp->modify('- 6 day')->format('Y-m-d');
    }

    public function logout()
    {
      $logoutData['endPoint'] = (array_key_exists('driver_index', $_SESSION)) ? 'drivers' : 'dispatchers';
      $logoutData['method'] = 'PUT';
      $logoutData['primaryKey'] = $_SESSION['driver_index'] ?? $_SESSION['dispatch_index'] ?? '';

      $logoutData['payload'] = [ 'LoggedIn' => 0 ];
      if (!$logoutUpdate = self::createQuery($logoutData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return 'error';
      }
      $logoutUpdateResult = self::callQuery($logoutUpdate);
      if ($logoutUpdateResult === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return 'error';
      }
      return true;
    }

    public function routeTickets()
    {
      $output = '';
      if (!self::buildRoute()) {
        if ($this->cancelRoute === false) {
          if ($this->enableLogging !== false) self::writeLoop();
        }
        return '<p class="center result">' . self::getError() . '</p>';
      }
      // Check for active contract tickets
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      // Pull RoundTrip tickets with null d2TimeStamp
      $roundTripFilter = [
        ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6],
        ['Resource'=>'d2TimeStamp', 'Filter'=>'is'],
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0]
      ];
      // Pull Routine tickets with null dTimeStamp
      $routineFilter = [
        ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>5],
        ['Resource'=>'dTimeStamp', 'Filter'=>'is'],
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0]
      ];

      $ticketQueryData['queryParams']['filter'] = [ $roundTripFilter, $routineFilter ];
      $ticketQueryData['queryParams']['order'] = [ 'pTimeStamp' ];

      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return '<p class="center result">' . self::getError() . '</p>';
      }
      $this->activeTicketSet = self::callQuery($ticketQuery);
      if ($this->activeTicketSet === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return '<p class="center result">' . self::getError() . '</p>';
      }
      // Check for completed contract tickets
      if (empty($this->activeTicketSet)) {
        // If no routes are assigned no tickets will be available
        if (empty($this->config['routes'])) {
          return '<p class="center result">No routes assigned.</p>';
        }
        // Check for completed contract tickets for today
        // Only queryParams['filter'] needs to be changed here
        $ticketQueryData['queryParams']['filter'] = [
          ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
          ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>1],
          ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
          ['Resource'=>'Charge', 'Filter'=>'neq', 'Value'=>0],
          ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->today} 00:00:00,{$this->today} 23:59:59"],
          ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0]
        ];
        if (!$ticketQuery = self::createQuery($ticketQueryData)) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          return '<p class="center result">' . self::getError() . '</p>';
        }
        $tempTicketSet = self::callQuery($ticketQuery);
        if ($tempTicketSet === false) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          return '<p class="center result">' . self::getError() . '</p>';
        }
        $state = (count($tempTicketSet) > 0) ? 'Complete' : 'Pending';
        return "<p class=\"center result\">{$this->dateObject->format('d M Y')} Route {$state}.</p>";
      } else {
        self::prepTickets();
        if (!empty($this->singleLocation)) {
          for ($i = 0; $i < count($this->singleLocation); $i++) {
            $ticket = self::createTicket($this->singleLocation[$i]);
            if ($ticket === false) {
              $temp = $this->error . "\n";
              $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
              if ($this->enableLogging !== false) self::writeLoop();
              return '<p class="center result">' . self::getError() . '</p>';
            }
            $output .= $ticket->displaySingleTicket();
          }
        }
        if (!empty($this->multiLocation)) {
          foreach ($this->multiLocation as $group) {
            $temp = array();
            for ($i = 0; $i < count($group); $i++) {
              $ticket = self::createTicket($group[$i]);
              if ($ticket === false) {
                $temp = $this->error . "\n";
                $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                if ($this->enableLogging !== false) self::writeLoop();
                return '<p class="center result">' . self::getError() . '</p>';
              }
              $temp[] = $ticket;
            }
            $ticketPrimeData = [ 'multiTicket'=>$temp ];
            $ticketPrime = self::createTicket($ticketPrimeData);
            if ($ticketPrime === false) {
              $temp = $this->error . "\n";
              $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
              if ($this->enableLogging !== false) self::writeLoop();
              return '<p class="center result">' . self::getError() . '</p>';
            }
            $output .= $ticketPrime->displayMultiTicket();
          }
        }
      }
      // give javascript the list of routes
      $jse = 'json_encode';
      $output .= "<span id=\"overnightFlag\" class=\"hide\">{$jse($_SESSION['config']['routes'])}</span>";
      return $output;
    }

    private function buildRoute()
    {
      $this->testDateObject = clone $this->dateObject;
      $this->startDateObject = clone $this->dateObject;
      for ($i = 0; $i < count($this->config['routes']); $i++) {
        if ($this->config['routes'][$i]['LastDispatched'] === null) {
          // Skip this route if it's start date is in the future
          if ($this->config['routes'][$i]['StartDate'] > $this->today) continue;
          // If the route is scheduled to start today and the start time has passed add it
          if (
            ($this->config['routes'][$i]['StartDate'] === $this->today ||
            $this->config['routes'][$i]['StartDate'] === $this->tTest)
          ) {
            if ($this->config['routes'][$i]['StartTime'] < $this->dateObject->format('H:i:s')) {
              $this->todaysRoutes[] = $this->config['routes'][$i];
            }
            continue;
          }
          // If no other condition is met set LastDispatched to one day prior
          $this->testDate = $this->yesterday;
        } else {
          $this->testDate = substr($this->config['routes'][$i]['LastDispatched'], 0, 10);
        }
        $startDate = $this->config['routes'][$i]['StartDate'] ?? $this->testDate ?? $this->today;
        $this->startDateObject->setDate(...explode('-', $startDate));
        $schedule = [];
        foreach ($this->config['routes'][$i]['route_schedule'] as $route_schedule) {
          if (self::test_bool($route_schedule['Deleted']) === false) $schedule[] = $route_schedule['schedule_index'];
        }
        $this->testDateObject->setTimestamp(strtotime($this->testDate . ' ' . $this->config['routes'][$i]['StartTime']));
        for($j = 0; $j < count($schedule); $j++) {
          if ($this->dateObject->format('Y-m-d') !== $this->today) {
            $this->dateObject->setTimestamp($this->timestamp);
          }
          if (
            self::compareSchedule($schedule[$j], $this->config['routes'][$i]['route_index'], true) === true &&
            (!isset($this->config['routes'][$i]['dispatched']) ||
            (isset($this->config['routes'][$i]['dispatched']) && $this->config['routes'][$i]['dispatched'] !== true))
          ) {
            $this->todaysRoutes[] = $this->config['routes'][$i];
            break;
          }
        }
      }
      if (empty($this->todaysRoutes)) return true;
      // Pull list of runs dispatched to today's routes
      self::fetchRouteTickets();
      if ($this->todaysRouteTickets === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      self::fetchRunList();
      if ($this->runList === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      if ($this->cancelRoute === true) {
        $this->error = '<p class="center">All Routes Canceled</p>';
        return false;
      }
      // Filter runs by schedule, check them against the schedule override, and add them to the daily ticket set
      if (!self::filterRuns()) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      if ($this->dateObject->format('Y-m-d') !== $this->today) {
        $this->dateObject->setTimestamp($this->timestamp);
      }
      if (!empty($this->newTickets)) {
        if (!self::submitRouteTickets()) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          return false;
        }
      }
      $route_indecies = $setStartDate = [];
      foreach ($this->todaysRoutes as $route) {
        if (self::test_bool($route['Overnight']) === true) $_SESSION['config']['overnight'] = 1;
        if (!isset($route['dispatched']) || $route['dispatched'] !== true) {
          $route_indecies[] = $route['route_index'];
        }
        if ($route['StartDate'] === $this->tTest) {
          $setStartDate[] = $route['route_index'];
        }
      }
      $this->dateObject->setTimestamp($this->timestamp);
      if (!empty($setStartDate)) {
        $startDateData['method'] = 'PUT';
        $startDateData['endPoint'] = 'routes';
        $startDateData['primaryKey'] = implode(',', $setStartDate);
        $startDateData['payload'] = [];
        for ($i = 0; $i < count($setStartDate); $i++) {
          $newObj = new \stdClass();
          $newObj->StartDate = $this->dateObject->format('Y-m-d');
          $startDateData['payload'][] = $newObj;
        }
        if (!$startDateUpdate = self::createQuery($startDateData)) {
          // Log but don't break here on fail
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
        }
        $updateResult = self::callQuery($startDateUpdate);
        if ($updateResult === false) {
          // Log but don't break here on fail
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
        }
      }
      if (empty($route_indecies)) return true;
      $routeUpdateData['method'] = 'PUT';
      $routeUpdateData['endPoint'] = 'routes';
      $routeUpdateData['primaryKey'] = implode(',', $route_indecies);
      $routeUpdateData['payload'] = [];
      for ($i = 0; $i < count($route_indecies); $i++) {
        $newObj = new \stdClass();
        $newObj->LastDispatched = $this->dateObject->format('Y-m-d H:i:s');
        $routeUpdateData['payload'][] = $newObj;
      }
      if (!$routeUpdate = self::createQuery($routeUpdateData)) {
        // Log but don't break here on fail
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
      }
      $updateResult = self::callQuery($routeUpdate);
      if ($updateResult === false) {
        // Log but don't break here on fail
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
      }
      for ($i = 0; $i < count($this->todaysRoutes); $i++) {
        for ($j = 0; $j < count($_SESSION['config']['routes']); $j++) {
          if ($_SESSION['config']['routes'][$j]['route_index'] === $this->todaysRoutes[$i]['route_index']) {
            $_SESSION['config']['routes'][$j]['LastDispatched'] = $this->dateObject->format('Y-m-d H:i:s');
          }
        }
      }
      return true;
    }

    private function fetchRouteTickets() {
      $route_indecies = [];
      foreach ($this->todaysRoutes as $route) {
        if (!isset($route['dispatched']) || $route['dispatched'] !== true) {
          $route_indecies[] = $route['route_index'];
        }
      }
      if (empty($route_indecies)) return $this->todaysRouteTickets = [];
      $routeTicketsQueryData['endPoint'] = 'route_tickets';
      $routeTicketsQueryData['method'] = 'GET';
      $routeTicketsQueryData['queryParams']['filter'] = [
        [
          'Resource'=>'route_index',
          'Filter'=>'in',
          'Value'=>implode(',', array_column($this->todaysRoutes, 'route_index'))
        ]
      ];
      if (!$routeTicketsQuery = self::createQuery($routeTicketsQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->todaysRouteTickets = false;
      }
      return $this->todaysRouteTickets = self::callQuery($routeTicketsQuery);
    }

    private function fetchRunList()
    {
      $goodVals = [ 'Client', 'Department', 'Contact', 'Telephone', 'Address1', 'Address2', 'Country' ];
      $runVals = [
        'DryIce', 'diWeight', 'Notes', 'PriceOverride', 'TicketPrice', 'RoundTrip', 'pSigReq', 'dSigReq', 'd2SigReq',
        'VATable', 'VATrate', 'VATtype', 'VATableIce', 'VATrateIce', 'VATtypeIce'
      ];
      // Fetch cancelations and reschedules
      $rescheduledQueryData['endPoint'] = 'schedule_override';
      $rescheduledQueryData['method'] = 'GET';
      $rescheduleFilter = [
        ['Resource'=>'Cancel', 'Filter'=>'eq', 'Value'=>0],
        ['
          Resource'=>'route_index',
          'Filter'=>'in',
          'Value'=>implode(',', array_column($this->todaysRoutes, 'route_index'))
        ],
        ['Resource'=>'StartDate', 'Filter'=>'le', 'Value'=>$this->today],
        ['Resource'=>'EndDate', 'Filter'=>'ge', 'Value'=>$this->today],
        ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
      ];
      $cancelFilter = [
        ['Resource'=>'Cancel', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'StartDate', 'Filter'=>'le', 'Value'=>$this->today],
        ['Resource'=>'EndDate', 'Filter'=>'ge', 'Value'=>$this->today],
        ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
      ];
      $rescheduledQueryData['queryParams']['filter'] = [ $rescheduleFilter, $cancelFilter ];
      $rescheduledQueryData['queryParams']['order'] = [ 'Cancel,desc', 'crun_index' ];
      if (!$rescheduledQuery = self::createQuery($rescheduledQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->runList = false;
      }
      $this->rescheduledRuns = self::callQuery($rescheduledQuery);
      if ($this->rescheduledRuns === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->runList = false;
      }
      for ($i = 0; $i < count($this->rescheduledRuns); $i++) {
        if ($this->cancelRoute === true) break;
        switch ((int)$this->rescheduledRuns[$i]['Cancel']) {
          case 1:
            if ($this->rescheduledRuns[$i]['crun_index'] === 1) $this->cancelRoute = true;
            $this->cancelations[] = $this->rescheduledRuns[$i]['crun_index'];
            break;
          case 0:
            if (
              $this->cancelRoute === true ||
              in_array($this->rescheduledRuns[$i]['crun_index'], $this->cancelations, true)
            ) break;
            $this->rescheduledRunsList[] = $this->rescheduledRuns[$i]['crun_index'];
            break;
        }
      }
      if ($this->cancelRoute === true) return false;
      $runListQueryData['endPoint'] = 'contract_runs';
      $runListQueryData['method'] = 'GET';
      $filtered = array_diff(array_column($this->todaysRouteTickets, 'crun_index'), $this->cancelations);
      if (!empty($filtered)) {
        $runListQueryData['queryParams']['filter'][] = [
          ['Resource'=>'crun_index', 'Filter'=>'in', 'Value'=>implode(',', $filtered)],
          ['Resource'=>'LastCompleted', 'Filter'=>'ne', 'Value'=>$this->today],
          ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
        ];
      }
      $filtered = array_diff($this->rescheduledRunsList, $this->cancelations);
      if (!empty($filtered)) {
        $runListQueryData['queryParams']['filter'][] = [
          ['Resource'=>'crun_index', 'Filter'=>'in', 'Value'=>implode(',', $filtered)],
          ['Resource'=>'LastCompleted', 'Filter'=>'ne', 'Value'=>$this->today],
          ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
        ];
      }
      if (empty($runListQueryData['queryParams']['filter'])) return $this->runList = [];
      $runListQueryData['queryParams']['join'] = [ 'contract_locations', 'c_run_schedule' ];
      if (!$runListQuery = self::createQuery($runListQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->runList = false;
      }
      $runList = self::callQuery($runListQuery);
      if ($runList === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->runList = false;
      }
      for ($i = 0; $i < count($runList); $i++) {
        for ($j = 0; $j < count($this->rescheduledRuns); $j++) {
          if ($runList[$i]['crun_index'] === $this->rescheduledRuns[$j]['crun_index']) {
            $runList[$i]['pTime'] = $this->rescheduledRuns[$j]['pTime'];
            $runList[$i]['dTime'] = $this->rescheduledRuns[$j]['dTime'];
            $runList[$i]['d2Time'] = $this->rescheduledRuns[$j]['d2Time'];
            $runList[$i]['Rescheduled'] = true;
          }
        }
        for ($j = 0; $j < count($this->todaysRouteTickets); $j++) {
          if ($runList[$i]['crun_index'] === $this->todaysRouteTickets[$j]['crun_index']) {
            $runList[$i]['route_index'] = $this->todaysRouteTickets[$j]['route_index'];
          }
        }
        foreach ($runList[$i]['pickup_id'] as $key => $value) {
          if (in_array($key, $goodVals)) $runList[$i]["p{$key}"] = self::decode($value);
        }
        unset($runList[$i]['pickup_id']);
        foreach ($runList[$i]['dropoff_id'] as $key => $value) {
          if (in_array($key, $goodVals)) $runList[$i]["d{$key}"] = self::decode($value);
        }
        unset($runList[$i]['dropoff_id']);
      }
      return $this->runList = $runList;
    }

    private function filterRuns()
    {
      $this->startDateObject = clone $this->dateObject;
      for ($i = 0; $i < count($this->runList); $i++) {
        $this->runList[$i]['ReadyDate'] = "{$this->dateObject->format('Y-m-d')} {$this->runList[$i]['pTime']}";
        if (isset($this->runList[$i]['Rescheduled'])) {
          $this->newTickets[] = $this->runList[$i];
          continue;
        }
        if ($this->runList[$i]['LastCompleted'] === null && $this->runList[$i]['StartDate'] === $this->today) {
          $this->newTickets[] = $this->runList[$i];
          continue;
        }
        if ($this->runList[$i]['StartDate'] > $this->today) continue;
        $this->startDateObject->setDate(...explode('-', $this->runList[$i]['StartDate']));
        $this->testDateObject->setTimestamp(strtotime($this->runList[$i]['LastCompleted'] . ' ' . $this->runList[$i]['pTime']));
        $schedule = [];
        foreach ($this->runList[$i]['c_run_schedule'] as $run_schedule) {
          if (self::test_bool($run_schedule['Deleted']) === false) $schedule[] = $run_schedule['schedule_index'];
        }
        for ($x = 0; $x < count($schedule); $x++) {
          if ($this->dateObject->format('Y-m-d') !== $this->today) {
            $this->dateObject->setTimestamp($this->timestamp);
          }
          if (self::compareSchedule($schedule[$x], $this->runList[$i]['route_index']) === true) {
            // Set a flag indicating that the ticket should be added to the new ticket set
            $this->add = true;
            // After the first ticket is added
            // set the flag to false if the new ticket set contains a ticket with the same run number
            if (!empty($this->newTickets)) {
              foreach ($this->newTickets as $test) {
                if ($this->runList[$i]['RunNumber'] === $test['RunNumber']) {
                  $this->add = false;
                }
              }
            }
            if ($this->add === true) {
              $this->newTickets[] = $this->runList[$i];
            }
          }
        }
      }
      return true;
    }

    private function compareSchedule($scheduleFrequency, $route_index = null, $routeTest = false)
    {
      $route = null;
      $routeLocalIndex = null;
      for ($i = 0; $i < count($this->config['routes']); $i++) {
        if ($this->config['routes'][$i]['route_index'] === $route_index) {
          $route = $this->config['routes'][$i];
          $routeLocalIndex = $i;
        }
      }
      if ($route === null) return false;
      $overnight = self::test_bool($route['Overnight']);
      $routeStart = $route['StartTime'];
      if ($routeTest === true) {
        if (
          ($overnight === true &&
          (($this->testDateObject->format('Y-m-d') === $this->dateObject->format('Y-m-d') &&
          $this->testDateObject->format('H:i:s') < $this->dateObject->format('H:i:s')) ||
          ($this->testDateObject->format('Y-m-d') === $this->yesterday &&
          $this->testDateObject->format('H:i:s') > $this->dateObject->format('H:i:s')))) ||
          ($overnight === false &&
          ($this->testDateObject->format('Y-m-d') >= $this->today ||
          ($this->testDateObject->format('Y-m-d') < $this->today &&
          $this->testDateObject->format('H:i:s') > $this->dateObject->format('H:i:s'))))
        ) {
          $this->config['routes'][$routeLocalIndex]['dispatched'] = true;
        }
        // if this is an overnight route and it's past midnight
        // it's schedule should be tested against yesterday
        if (
          $overnight === true &&
          ('00:00:00' < $this->dateObject->format('H:i:s') && $this->dateObject->format('H:i:s') < '12:00:00')
        ) {
          $this->dateObject->modify('- 1 day');
        }
      } else {
        // if this is an overnight route and a ticket is scheduled for pick up in the morning
        // it's schedule should be tested against tomorrow
        if (
          $overnight === true &&
          $this->testDateObject->format('H:i:s') < $routeStart &&
          '12:00:00' < $this->dateObject->format('H:i:s') && $this->dateObject->format('H:i:s') > '11:59:59'
        ) {
          $this->dateObject->modify('+ 1 day');
        }
        if ($this->testDateObject->format('Y-m-d') >= $this->dateObject->format('Y-m-d')) return false;
      }
      return Scheduling::testIndex($scheduleFrequency, $this->startDateObject, $this->dateObject);
    }

    private function submitRouteTickets()
    {
      $this->dateObject->setTimestamp($this->timestamp);
      $data['multiTicket'] = [];
      foreach ($this->newTickets as $newTicket) {
        $micro_date = microtime();
        $date_array = explode(' ',$micro_date);
        $newTicket['TicketNumber'] = $newTicket['RunNumber'] . $this->dateObject->format('m') . '00';
        $newTicket['Contract'] = 1;
        $newTicket['DispatchTimeStamp'] =
          $newTicket['ReceivedDate'] =
          $this->dateObject->format('Y-m-d H:i:s');
        $newTicket['DispatchMicroTime'] = substr($date_array[0], 1, 7);
        $newTicket['DispatchedTo'] = $this->driverID;
        $newTicket['DispatchedBy'] = '1.1';
        $newTicket['Charge'] = (self::test_bool($newTicket['RoundTrip']) === true) ? 6 : 5;
        $newTicket['TicketBase'] = $newTicket['TicketPrice'];
        $newTicket['ReceivedReady'] = 0;
        $data['multiTicket'][] = $newTicket;
      }
      if (!$ticketPrime = self::createTicket($data)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      if ($ticketPrime->processRouteTicket() === false) {
        $this->error .= "\n" . $ticketPrime->getError();
        if ($this->enableLogging !== false) self::writeLoop();
        return false;
      }
      return true;
    }

    private function sortTickets($ticket)
    {
      if (
        array_key_exists($ticket['locationTest'], $this->multiLocation) &&
        self::recursive_array_search($ticket['TicketNumber'], $this->multiLocation) === false
      ) {
        $this->multiLocation[$ticket['locationTest']][] = $ticket;
      } else {
        if (empty($this->ticketSet)) {
          $this->ticketSet[] = $ticket;
        } else {
          $match = 0;
          for ($i = 0; $i < count($this->ticketSet); $i++) {
            if ($this->ticketSet[$i]['locationTest'] === $ticket['locationTest']) {
              $this->multiLocation[$ticket['locationTest']][] = $this->ticketSet[$i];
              $this->multiLocation[$ticket['locationTest']][] = $ticket;
              $match++;
            }
          }
          if ($match === 0) $this->ticketSet[] = $ticket;
        }
      }
    }

    private function prepTickets()
    {
      // Set new keys 1) using client name, department, address1, and schedule time for grouping tickets,
      // 2) indicating what step the ticket is on to ease processing.
      foreach ($this->activeTicketSet as $ticket) {
        if (!isset($ticket['ReadyDate'])) {
          $ticket['ReadyDate'] = "{$this->dateObject->format('Y-m-d')} {$ticket['pTime']}";
        }
        $readyObj = new \dateTime($ticket['ReadyDate']);
        if ($ticket['pTimeStamp'] === $this->tTest) {
          $ticket['locationTest'] =
            "{$ticket['pClient']}{$ticket['pDepartment']}{$ticket['pAddress1']}{$readyObj->format('Y-m-dH:ia')}";
          $ticket['step'] = 'pickedUp';
        } elseif ($ticket['pTimeStamp'] !== $this->tTest && $ticket['dTimeStamp'] === $this->tTest) {
          $dTimeArray = explode(':', $ticket['dTime']);
          $readyObj->setTime($dTimeArray[0], $dTimeArray[1], $dTimeArray[2]);
          if ($ticket['pTime'] > $ticket['dTime']) {
            $readyObj->modify('+ 1 day');
          }
          $ticket['locationTest'] =
            "{$ticket['dClient']}{$ticket['dDepartment']}{$ticket['dAddress1']}{$readyObj->format('Y-m-dH:ia')}";
          $ticket['step'] = 'delivered';
        } elseif ($ticket['pTimeStamp'] !== $this->tTest && $ticket['dTimeStamp'] !== $this->tTest) {
          // Non round trip tickets with dTimeStamp !== $tTest will not have been returned from the database.
          // No need to test the charge code.
          $d2TimeArray = explode(':', $ticket['d2Time']);
          $readyObj->setTime($d2TimeArray[0], $d2TimeArray[1], $d2TimeArray[2]);
          if ($ticket['dTime'] > $ticket['d2Time']) {
            $readyObj->modify('+ 1 day');
          }
          $ticket['locationTest'] =
            "{$ticket['pClient']}{$ticket['pDepartment']}{$ticket['pAddress1']}{$readyObj->format('Y-m-dH:ia')}";
          $ticket['step'] = 'returned';
        }
        self::sortTickets($ticket);
      }
      foreach ($this->ticketSet as $ticket) {
        if (!array_key_exists($ticket['locationTest'], $this->multiLocation)) {
          $this->singleLocation[] = $ticket;
        }
      }
    }

    public function onCallTickets()
    {
      $this->ticketSet = $ticketQueryData = [];
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['queryParams']['filter'] = [];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"],
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,5'],
        ['Resource'=>'dTimeStamp', 'Filter'=>'is']
      ];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"],
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6],
        ['Resource'=>'d2TimeStamp', 'Filter'=>'is']
      ];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"],
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7],
        ['Resource'=>'dTimeStamp', 'Filter'=>'is'],
        ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>0]
      ];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'NotForDispatch', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"],
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7],
        ['Resource'=>'d2TimeStamp', 'Filter'=>'is'],
        ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>1]
      ];
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->error;
      }
      $this->onCallTicketSet = self::callQuery($ticketQuery);
      if ($this->onCallTicketSet === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return "<p class=\"center result\"><span class=\"error\>Error</span>: {$this->error}</p>";
      }
      if (count($this->onCallTicketSet) === 0) {
        return '<p class="center result">No On Call Tickets On File</p>';
      }
      $returnData = '';
      for ($i = 0; $i < count($this->onCallTicketSet); $i++) {
        $ticket = self::createTicket($this->onCallTicketSet[$i]);
        if ($ticket === false) {
          $temp = $this->error;
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== false) self::writeLoop();
          $returnData .= "<p class=\"center result\"><span class=\"error\">Error</span>: {$this->error}</p>";
        } else {
          $returnData .= $ticket->displaySingleTicket();
        }
      }
      return $returnData;
    }

    public function transferredTickets()
    {
      $this->contractTicketSet = $this->singleLocation = [];
      $returnData = '';
      // Drivers without dispatch authorization need a single datalist for transferring tickets.
      if ($this->ulevel === 'driver' && $this->CanDispatch === 0) {
        if ($this->transferList === null) {
          self::fetchDriversTransfer();
          if ($this->transferList === false) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== false) self::writeLoop();
            return false;
          }
        }
        if ($this->transferList !== 'empty' && $this->transferList !== null) {
          $returnData = '<datalist id="receivers">';
          foreach (json_decode($this->transferList, true) as $driver) {
            $driverName = ($driver['LastName'] == null) ? htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] : htmlentities($driver['FirstName']) . ' ' . htmlentities($driver['LastName']) . '; ' . $driver['DriverID'];
            $returnData .= ($driver['DriverID'] !== $_SESSION['DriverID']) ?
            "<option value=\"{$driverName}\">{$driverName}</option>" : '';
          }
          $returnData .= '</datalist>';
        }
      }
      $this->processTransfer = true;
      $transfersQueryData['endPoint'] = 'tickets';
      $transfersQueryData['method'] = 'GET';
      $transfersQueryData['queryParams']['filter'] = [];
      $transfersQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,5'],
        ['Resource'=>'dTimeStamp', 'Filter'=>'is']
      ];
      $transfersQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6],
        ['Resource'=>'d2TimeStamp', 'Filter'=>'is']
      ];
      $transfersQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,5'],
        ['Resource'=>'dTimeStamp', 'Filter'=>'is']
      ];
      $transfersQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6],
        ['Resource'=>'d2TimeStamp', 'Filter'=>'is']
      ];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7],
        ['Resource'=>'dTimeStamp', 'Filter'=>'is'],
        ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>0]
      ];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7],
        ['Resource'=>'d2TimeStamp', 'Filter'=>'is'],
        ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>1]
      ];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7],
        ['Resource'=>'dTimeStamp', 'Filter'=>'is'],
        ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>0]
      ];
      $ticketQueryData['queryParams']['filter'][] = [
        ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1],
        ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID],
        ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7],
        ['Resource'=>'d2TimeStamp', 'Filter'=>'is'],
        ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>1]
      ];
      if (!$transfersQuery = self::createQuery($transfersQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return '<p class="center result">' . self::getError() . '</p>';
      }
      $this->activeTicketSet = self::callQuery($transfersQuery);
      if ($this->activeTicketSet === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return '<p class="center result">' . self::getError() . '</p>';
      }
      if (empty($this->activeTicketSet)) {
        return '<p class="center result">No Pending Transfers On File.</p>';
      }
      foreach ($this->activeTicketSet as $test) {
        $test['processTransfer'] = $this->processTransfer;
        if (self::test_bool($test['Contract']) === true) {
          $this->contractTicketSet[] = $test;
        } else {
          $this->singleLocation[] = $test;
        }
      }
      if (!empty($this->contractTicketSet)) {
        $this->activeTicketSet = $this->contractTicketSet;
        self::prepTickets();
      }
      if (!empty($this->singleLocation)) {
        for ($i = 0; $i < count($this->singleLocation); $i++) {
          $this->singleLocation[$i]['processTransfer'] = true;
          $ticket = self::createTicket($this->singleLocation[$i]);
          if ($ticket === false) {
            $temp = $this->error;
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== false) self::writeLoop();
            $returnData .= '<p class="center result">' . self::getError() . '</p>';
          } else {
            $returnData .= $ticket->displaySingleTicket();
          }
        }
      }
      if (!empty($this->multiLocation)) {
        foreach ($this->multiLocation as $group) {
          $temp = array();
          for ($i = 0; $i < count($group); $i++) {
            $ticket = self::createTicket($group[$i]);
            if ($ticket === false) {
              $temp = $this->error . "\n";
              $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
              if ($this->enableLogging !== false) self::writeLoop();
              $returnData .= '<p class="center result">' . self::getError() . '</p>';
            }
            $temp[] = $ticket;
          }
          $ticketPrimeData = [ 'multiTicket'=>$temp ];
          $ticketPrimeData['processTransfer'] = true;
          $ticketPrime = self::createTicket($ticketPrimeData);
          if ($ticketPrime === false) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== false) self::writeLoop();
            $returnData .= '<p class="center result">' . self::getError() . '</p>';
          }
          $returnData .= $ticketPrime->displayMultiTicket();
        }
      }
      return $returnData;
    }

    private function fetchDriversTransfer()
    {
      // Pull the data to make the datalists
      $driverQueryData['method'] = 'GET';
      $driverQueryData['endPoint'] = 'drivers';
      $driverQueryData['queryParams']['include'] = ['DriverID', 'FirstName', 'LastName'];
      $driverQueryData['queryParams']['filter'] = [ ['Resource'=>'Deleted', 'Filter'=>'neq', 'Value'=>1] ];
      if (!$driverQuery = self::createQuery($driverQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->transferList = false;
      }
      $tempDriver = self::callQuery($driverQuery);
      if ($tempDriver === false) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== false) self::writeLoop();
        return $this->transferList = false;
      }
      // Only proceed if a record is returned
      if (empty($tempDriver)) {
        return $this->transferList = 'empty';
      }
      return $this->transferList = json_encode($tempDriver);
    }
  }
