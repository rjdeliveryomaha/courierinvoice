<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  /***
  * throws Exception
  *
  ***/

  class Route extends CommonFunctions {
    protected $newTickets;
    protected $activeTicketSet = [];
    protected $onCallTicketSet = [];
    protected $contractTicketSet = [];
    protected $transferredTicketSet = [];
    protected $ticketSet = [];
    protected $singleLocation = [];
    protected $multiLocation = [];
    protected $cancelations;
    protected $processTransfer = FALSE;
    protected $overrideTickets;
    protected $rescheduledRuns;
    protected $driverID;
    protected $driverName;
    protected $today;
    protected $backstop;
    protected $dateObject;
    protected $testDate;
    protected $testDateObject;
    protected $secondTestDate;
    protected $add;
    protected $runList = [];
    protected $locations = [];
    protected $locationTest;
    protected $LastSeen;
    // List of drivers on file for transfer data list for drivers without dispatch authorization
    protected $transferList;
    private $tTest;

    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
      $this->driverID = $_SESSION['DriverID'];
      $this->driverName = "{$_SESSION['FirstName']} {$_SESSION['LastName']}";
      $this->LastSeen = $_SESSION['LastSeen'];
      try {
        self::setTimezone();
      } catch (Exception $e) {
        $this->error = $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw $e;
      }
      try {
        $this->dateObject = new \dateTime('NOW', $this->timezone);
      } catch (Exception $e) {
        $this->error = __function__ . ' Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw $e;
      }
      $this->today = $this->dateObject->format('Y-m-d');
      $temp = clone $this->dateObject;
      $temp->modify('- 7 days');
      $this->backstop = $temp->format('Y-m-d');
    }

    private function setLastSeen() {
      $lastSeenUpdateData['endPoint'] = (array_key_exists('driver_index', $_SESSION)) ? 'drivers' : 'dispatchers';
      $lastSeenUpdateData['method'] = 'PUT';
      $lastSeenUpdateData['formKey'] = $this->formKey;
      $lastSeenUpdateData['primaryKey'] = (array_key_exists('driver_index', $_SESSION)) ? $_SESSION['driver_index'] : $_SESSION['dispatch_index'];
      $lastSeenUpdateData['payload'] = ['LastSeen'=>$this->today];
      if (!$lastSeenUpdate = self::createQuery($lastSeenUpdateData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      $lastSeenUpdateResult = self::callQuery($lastSeenUpdate);
      if ($lastSeenUpdateResult === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      return $this->LastSeen = $_SESSION['LastSeen'] = $this->today;
    }

    public function onCallTickets() {
      $this->ticketSet = $ticketQueryData = [];
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['formKey'] = $this->formKey;
      $ticketQueryData['queryParams']['filter'] = [];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>$this->backstop . ' 00:00:00,' . $this->today . ' 23:59:59'], ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,5'], ['Resource'=>'dTimeStamp', 'Filter'=>'is'] ];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>$this->backstop . ' 00:00:00,' . $this->today . ' 23:59:59'], ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6], ['Resource'=>'d2TimeStamp', 'Filter'=>'is'] ];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"], ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7], ['Resource'=>'dTimeStamp', 'Filter'=>'is'], ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>0] ];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->backstop} 00:00:00,{$this->today} 23:59:59"], ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7], ['Resource'=>'d2TimeStamp', 'Filter'=>'is'], ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>1] ];
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      $this->onCallTicketSet = self::callQuery($ticketQuery);
      if ($this->onCallTicketSet === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
      }
      if (count($this->onCallTicketSet) === 0) {
        return '<p class="center">No On Call Tickets On File</p>';
      }
      $returnData = '';
      for ($i = 0; $i < count($this->onCallTicketSet); $i++) {
        $this->ticketSet[$i]['formKey'] = $this->formKey;
        $ticket = self::createTicket($this->ticketSet[$i]);
        if ($ticket === FALSE) {
          $temp = $this->error;
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          $returnData .= "<p class=\"center\"><span class=\"error\">Error</span>: {$this->error}</p>";
        } else {
          $returnData .= $ticket->displaySingleTicket();
        }
      }
      return $returnData;
    }

    public function routeTickets() {
      $output = '';
      if (!self::buildRoute()) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return self::getError();
      }
      // Check for active contract tickets
      // Pull Round Trip ticket
      $ticketQueryData['endPoint'] = 'tickets';
      $ticketQueryData['method'] = 'GET';
      $ticketQueryData['formKey'] = $this->formKey;
      $ticketQueryData['queryParams']['filter'] = [];
      // Pull RoundTrip tickets
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->today} 00:00:00,{$this->today} 23:59:59"], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6], ['Resource'=>'d2TimeStamp', 'Filter'=>'is'], ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0] ];
      // Pull Routine tickets
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->today} 00:00:00,{$this->today} 23:59:59"], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>5], ['Resource'=>'dTimeStamp', 'Filter'=>'is'], ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0] ];
      if (!$ticketQuery = self::createQuery($ticketQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      $this->activeTicketSet = self::callQuery($ticketQuery);
      if ($this->activeTicketSet === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
      }
      // Check for completed contract tickets
      if (empty($this->activeTicketSet)) {
        //  Check for completed contract tickets for today
        // Only queryParams['filter'] needs to be changed here
        $ticketQueryData['queryParams']['filter'] = [ ['Resource'=>'Contract', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'neq', 'Value'=>0], ['Resource'=>'DispatchTimeStamp', 'Filter'=>'bt', 'Value'=>"{$this->today} 00:00:00,{$this->today} 23:59:59"], ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>0] ];
        if (!$ticketQuery = self::createQuery($ticketQueryData)) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return $this->error;
        }
        $tempTicketSet = self::callQuery($ticketQuery);
        if ($tempTicketSet === FALSE) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
        }
        $state = (count($tempTicketSet) > 0) ? 'Complete' : 'Empty';
        return "<p class=\"center\">{$this->dateObject->format('d M Y')} Route {$state}.</p>";
      } else {
        self::prepTickets();
        if (!empty($this->singleLocation)) {
          for ($i = 0; $i < count($this->singleLocation); $i++) {
            $this->singleLocation[$i]['formKey'] = $this->formKey;
            $ticket = self::createTicket($this->singleLocation[$i]);
            if ($ticket === FALSE) {
              $temp = $this->error . "\n";
              $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              return "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
            }
            $output .= $ticket->displaySingleTicket();
          }
        }
        if (!empty($this->multiLocation)) {
          foreach ($this->multiLocation as $group) {
            $temp = array();
            for ($i = 0; $i < count($group); $i++) {
              $group[$i]['formKey'] = $this->formKey;
              $ticket = self::createTicket($group[$i]);
              if ($ticket === FALSE) {
                $temp = $this->error . "\n";
                $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
                if ($this->enableLogging !== FALSE) self::writeLoop();
                return "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
              }
              $temp[] = $ticket;
            }
            $ticketPrimeData = [ 'multiTicket'=>$temp ];
            $ticketPrimeData['formKey'] = $this->formKey;
            $ticketPrime = self::createTicket($ticketPrimeData);
            if ($ticketPrime === FALSE) {
              $temp = $this->error . "\n";
              $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              return "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
            }
            $output .= $ticketPrime->displayMultiTicket();
          }
        }
      }
      return $output;
    }

    public function transferredTickets() {
      $this->contractTicketSet = $this->singleLocation = [];
      $returnData = '';
      // Drivers without dispatch authorization need a single datalist for transferring tickets.
      if ($this->ulevel === "driver" && $this->CanDispatch === 0) {
        if ($this->transferList === NULL) {
          self::fetchDriversTransfer();
          if ($this->transferList === FALSE) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
        }
        if ($this->transferList !== 'empty' && $this->transferList !== NULL) {
          $returnData = '<datalist id="receivers">';
          foreach (json_decode($this->transferList, TRUE) as $driver) {
            $driverName = ($driver['LastName'] == NULL) ? htmlentities($driver['FirstName']) . '; ' . $driver['DriverID'] : htmlentities($driver['FirstName']) . ' ' . htmlentities($driver['LastName']) . '; ' . $driver['DriverID'];
            $returnData .= ($driver['DriverID'] !== $_SESSION['DriverID']) ? '<option value="' . $driverName . '">' . $driverName . '</option>' : '';
          }
          $returnData .= '</datalist>';
        }
      }
      $this->processTransfer = TRUE;
      $transfersQueryData['endPoint'] = 'tickets';
      $transfersQueryData['method'] = 'GET';
      $transfersQueryData['formKey'] = $this->formKey;
      $transfersQueryData['queryParams']['filter'] = [];
      $transfersQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,5'], ['Resource'=>'dTimeStamp', 'Filter'=>'is'] ];
      $transfersQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6], ['Resource'=>'d2TimeStamp', 'Filter'=>'is'] ];
      $transfersQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,5'], ['Resource'=>'dTimeStamp', 'Filter'=>'is'] ];
      $transfersQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>6], ['Resource'=>'d2TimeStamp', 'Filter'=>'is'] ];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7], ['Resource'=>'dTimeStamp', 'Filter'=>'is'], ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>0] ];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7], ['Resource'=>'d2TimeStamp', 'Filter'=>'is'], ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>1] ];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7], ['Resource'=>'dTimeStamp', 'Filter'=>'is'], ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>0] ];
      $ticketQueryData['queryParams']['filter'][] = [ ['Resource'=>'TransferState', 'Filter'=>'eq', 'Value'=>1], ['Resource'=>'PendingReceiver', 'Filter'=>'eq', 'Value'=>$this->driverID], ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>7], ['Resource'=>'d2TimeStamp', 'Filter'=>'is'], ['Resource'=>'d2SigReq', 'Filter'=>'eq', 'Value'=>1] ];
      if (!$transfersQuery = self::createQuery($transfersQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      $this->activeTicketSet = self::callQuery($transfersQuery);
      if ($this->activeTicketSet === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->error;
      }
      if (empty($this->activeTicketSet)) {
        $returnData .= '<p class="center">No Pending Transfers On File.</p>';
        return $returnData;
      }
      foreach ($this->activeTicketSet as $test) {
        $test["processTransfer"] = $this->processTransfer;
        if ($test["Contract"] === 1) {
          $this->contractTicketSet[] = $test;
        } else {
          $this->singleLocation[] = $test;
        }
      }
      if (empty($this->contractTicketSet) && empty($this->singleLocation)) {
        $returnData .= '<p class="center">No Pending Transfers On File.</p>';
        return $returnData;
      }
      if (!empty($this->contractTicketSet)) {
        $this->activeTicketSet = $this->contractTicketSet;
        self::prepTickets();
      }
      if (!empty($this->singleLocation)) {
        for ($i = 0; $i < count($this->singleLocation); $i++) {
          $this->singleLocation[$i]['formKey'] = $this->formKey;
          $this->singleLocation[$i]['processTransfer'] = TRUE;
          $ticket = self::createTicket($this->singleLocation[$i]);
          if ($ticket === FALSE) {
            $temp = $this->error;
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            $returnData .= "<p class=\"center\"><span class=\"error\">Error</span>: {$this->error}</p>";
          } else {
            $returnData .= $ticket->displaySingleTicket();
          }
        }
      }
      if (!empty($this->multiLocation)) {
        foreach ($this->multiLocation as $group) {
          $temp = array();
          for ($i = 0; $i < count($group); $i++) {
            $group[$i]['formKey'] = $this->formKey;
            $ticket = self::createTicket($group[$i]);
            if ($ticket === FALSE) {
              $temp = $this->error . "\n";
              $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
              if ($this->enableLogging !== FALSE) self::writeLoop();
              $returnData .= "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
            }
            $temp[] = $ticket;
          }
          $ticketPrimeData = [ 'multiTicket'=>$temp ];
          $ticketPrimeData['formKey'] = $this->formKey;
          $ticketPrimeData['processTransfer'] = TRUE;
          $ticketPrime = self::createTicket($ticketPrimeData);
          if ($ticketPrime === FALSE) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            $returnData .= "<p class=\"center\"><span class=\"error\>Error</span>: {$this->error}</p>";
          }
          $returnData .= $ticketPrime->displayMultiTicket();
        }
      }
      return $returnData;
    }

    private function buildRoute() {
      // Check if driver has been seen today
      if ($this->LastSeen === NULL || $this->LastSeen !== $this->today) {
        // Pull list of runs dispatched to this driver
        self::fetchRunList();
        if ($this->runList === FALSE) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return FALSE;
        }
        // Process the reschedule codes
        if (!empty($this->runList)) {
          self::processScheduleCodes();
          // check for cancelations
          self::fetchCancelations();
          if ($this->cancelations === FALSE) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
          // Filter runs by schedule, check them against the schedule override, and add them to the daily ticket set
          self::filterRuns();
        }
        // Check for rescheduled runs dispatched to this driver
        self::fetchRescheduledRuns();
        if ($this->rescheduledRuns === FALSE) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return FALSE;
        }
        if (!empty($this->newTickets)) {
          self::fetchContractLocations();
          if ($this->locations === FALSE) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
          self::addLocationsToTickets();
          if (!self::submitRouteTickets()) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            return FALSE;
          }
        }
        // Set that driver has been seen today
        if (!self::setLastSeen()) {
          $temp = $this->error . "\n";
          $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
          if ($this->enableLogging !== FALSE) self::writeLoop();
          return FALSE;
        }
      }
      return TRUE;
    }

    private function fetchDriversTransfer() {
      // Pull the data to make the datalists
      $driverQueryData['method'] = 'GET';
      $driverQueryData['endPoint'] = 'drivers';
      $driverQueryData['formKey'] = $this->formKey;
      $driverQueryData['queryParams']['include'] = ['DriverID', 'FirstName', 'LastName'];
      $driverQueryData['queryParams']['filter'] = [ ['Resource'=>'Deleted', 'Filter'=>'neq', 'Value'=>1] ];
      if (!$driverQuery = self::createQuery($driverQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->transferList = FALSE;
      }
      $tempDriver = self::callQuery($driverQuery);
      if ($tempDriver === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->transferList = FALSE;
      }
      // Only proceed if a record is returned
      if (empty($tempDriver)) {
        return $this->transferList = 'empty';
      }
      return $this->transferList = json_encode($tempDriver);
    }

    private function fetchRunList() {
      $runListQueryData['endPoint'] = 'contract_runs';
      $runListQueryData['method'] = 'GET';
      $runListQueryData['formKey'] = $this->formKey;
      $runListQueryData['queryParams']['include'] = ['crun_index', 'RunNumber', 'BillTo', 'PickUp', 'DropOff', 'RoundTrip', 'pTime', 'dTime', 'd2Time', 'Schedule', 'StartDate', 'LastCompleted', 'Notes', 'DryIce', 'diWeight', 'PriceOverride', 'TicketPrice'];
      $runListQueryData['queryParams']['filter'] = [ ['Resource'=>'DispatchedTo', 'Filter'=>'eq', 'Value'=>$this->driverID] ];
      if (!$runListQuery = self::createQuery($runListQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return $this->runList = FALSE;
      }
      $this->runList = self::callQuery($runListQuery);
    }

    private function fetchRescheduledRuns() {
      // Pull the list of reschedule events for this driver
      $rescheduledQueryData['endPoint'] = 'schedule_override';
      $rescheduledQueryData['method'] = 'GET';
      $rescheduledQueryData['formKey'] = $this->formKey;
      $rescheduledQueryData['queryParams']['include'] = ['ID','StartDate','EndDate','RunNumber','pTime','dTime','d2Time'];
      $rescheduledQueryData['queryParams']['filter'] = [ ['Resource'=>'Cancel', 'Filter'=>'eq', 'Value'=>5], ['Resource'=>'DriverID', 'Filter'=>'eq', 'Value'=>$this->driverID] ];
      if (!$rescheduledQuery = self::createQuery($rescheduledQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        $this->runList = FALSE;
        return FALSE;
      }
      $this->rescheduledRuns = self::callQuery($rescheduledQuery);
      if ($this->rescheduledRuns === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        $this->runList = FALSE;
        return FALSE;
      }
      if (empty($this->rescheduledRuns)) return FALSE;
      // Use month and day to determine if today has an event and process the ticket
      foreach ($this->rescheduledRuns as $event) {
        if (date('n-j-Y', strtotime($this->today)) >= date('n-j-Y', strtotime($event['StartDate'])) && date('n-j-Y', strtotime($this->today)) <= date('n-j-Y', strtotime($event['EndDate']))) {
          // Pull the ticket info
          $runQueryData['endPoint'] = 'contract_runs';
          $runQueryData['method'] = 'GET';
          $runQueryData['formKey'] = $this->formKey;
          $runQueryData['queryParams']['include'] = ['crun_index', 'RunNumber', 'BillTo', 'PickUp', 'DropOff', 'Notes', 'DryIce', 'diWeight', 'PriceOverride', 'TicketPrice'];
          $runQueryData['queryParams']['filter'] = [ ['Resource'=>'RunNumber', 'Filter'=>'eq', 'Value'=>$event['RunNumber']] ];
          if (!$runQuery = self::createQuery($runQueryData)) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            $this->runList = FALSE;
            return FALSE;
          }
          $temp1 = self::callQuery($runQuery);
          if ($temp1 === FALSE) {
            $temp = $this->error . "\n";
            $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
            if ($this->enableLogging !== FALSE) self::writeLoop();
            $this->runList = FALSE;
            return FALSE;
          }
          $temp = $temp1[0];
          // Add the rescheduled times
          $temp['pTime'] = $event['pTime'];
          $temp['dTime'] = $event['dTime'];
          $temp['d2Time'] = $event['d2Time'];
          if ($event['d2Time'] == NULL) {
            $temp['RoundTrip'] = 0;
          } else {
            $temp['RoundTrip'] = 1;
          }
          // Set a flag in the Rescheduled field indicating that this ticket is rescheduled
          $temp['Rescheduled'] = $event['ID'];
          $this->newTickets[] = $temp;
        }
      }
    }

    private function fetchContractLocations() {
      $contractLocationQueryData['endPoint'] = 'contract_locations';
      $contractLocationQueryData['method'] = 'GET';
      $contractLocationQueryData['formKey'] = $this->formKey;
      $contractLocationQueryData['queryParams']['include'] = ['ID','ClientName','Department', 'Contact', 'Telephone', 'Address1', 'Address2', 'Country'];
      if (!$contractLocationQuery = self::createQuery($contractLocationQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        $this->locations = FALSE;
        return FALSE;
      }
      $this->locations = self::callQuery($contractLocationQuery);
      if ($this->locations === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      if (empty($this->locations)) {
        $this->error = '<p class="center"><span class="error">Route Error Line ' . __line__ . '</span>: No Contract Locations On File.</p>';
        $this->locations = FALSE;
      }
      return FALSE;
    }

    private function fetchCancelations() {
      $cancelationQueryData['endPoint'] = 'schedule_override';
      $cancelationQueryData['method'] = 'GET';
      $cancelationQueryData['formKey'] = $this->formKey;
      $cancelationQueryData['queryParams']['include'] = ['Cancel', 'RunNumber', 'StartDate', 'EndDate'];
      $cancelationQueryData['queryParams']['filter'] = [ ['Resource'=>'Cancel', 'Filter'=>'le', 'Value'=>4] ];
      if (!$cancelationQuery = self::createQuery($cancelationQueryData)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        $this->cancelations = FALSE;
        return FALSE;
      }
      $this->cancelations = self::callQuery($cancelationQuery);
      if ($this->cancelations === FALSE) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      return FALSE;
    }

    private function addLocationsToTickets() {
      for ($i = 0; $i < count($this->newTickets); $i++) {
        foreach ($this->locations as $location) {
          // Replace PickUp and DropOff codes with names and addresses
          if ($this->newTickets[$i]['PickUp'] === $location['ID']) {
            $this->newTickets[$i]['pClient'] = self::decode($location['ClientName']);
            $this->newTickets[$i]['pDepartment'] = self::decode($location['Department']);
            $this->newTickets[$i]['pContact'] = self::decode($location['Contact']);
            $this->newTickets[$i]['pTelephone'] = self::decode($location['Telephone']);
            $this->newTickets[$i]['pAddress1'] = self::decode($location['Address1']);
            $this->newTickets[$i]['pAddress2'] = self::decode($location['Address2']);
            $this->newTickets[$i]['pCountry'] = $location['Country'];
          }
          if ($this->newTickets[$i]['DropOff'] === $location['ID']) {
            $this->newTickets[$i]['dClient'] = self::decode($location['ClientName']);
            $this->newTickets[$i]['dDepartment'] = self::decode($location['Department']);
            $this->newTickets[$i]['dContact'] = self::decode($location['Contact']);
            $this->newTickets[$i]['dTelephone'] = self::decode($location['Telephone']);
            $this->newTickets[$i]['dAddress1'] = self::decode($location['Address1']);
            $this->newTickets[$i]['dAddress2'] = self::decode($location['Address2']);
            $this->newTickets[$i]['dCountry'] = $location['Country'];
          }
        }
      }
    }

    private function processScheduleCodes() {
      for ($i = 0; $i < count($this->runList); $i++) {
        // Process the schedule codes
        if (strpos($this->runList[$i]['Schedule'], ',')) {
          $this->runList[$i]['Schedule'] = explode(',', $this->runList[$i]['Schedule']);
          for ($x = 0; $x < count($this->runList[$i]['Schedule']); $x++) {
            $this->runList[$i]['Schedule'][$x] = self::scheduleFrequency($this->runList[$i]['Schedule'][$x]);
          }
        } else {
          $this->runList[$i]['Schedule'] = array(self::scheduleFrequency($this->runList[$i]['Schedule']));
        }
      }
    }

    private function scheduleFrequency($code) {
      $x = $y = $schedule = '';
      $test = explode(' ', $code);
      if (count($test) === 1) {
        switch(substr($code, 0, 1)) {
          case 'a':
            $x = 'Every';
          break;
          case 'b':
            $x = 'Every Other';
          break;
          case 'c':
            $x = 'Every First';
          break;
          case 'd':
            $x = 'Every Second';
          break;
          case 'e':
            $x = 'Every Third';
          break;
          case 'f':
            $x = 'Every Fourth';
          break;
          case 'g':
            $x = 'Every Last';
          break;
        }
        switch (substr($code, 1, 1)) {
          case '1':
            $y = 'Day';
          break;
          case '2':
            $y = 'Weekday';
          break;
          case '3':
            $y = 'Monday';
          break;
          case '4':
            $y = 'Tuesday';
          break;
          case '5':
            $y = 'Wednesday';
          break;
          case '6':
            $y = 'Thursday';
          break;
          case '7':
            $y = 'Friday';
          break;
          case '8':
            $y = 'Saturday';
          break;
          case '9':
            $y = 'Sunday';
          break;
        }
        $schedule = "{$x} {$y}";
      } else {
        if(count($test) === 3) {
          // If the literal schedule is 3 words long the first must be Every and can be eliminated
          array_shift($test);
        }
        switch($test[0]) {
          case 'Every':
            $x = 'a';
          break;
          case 'Other':
            $x = 'b';
          break;
          case 'First':
            $x = 'c';
          break;
          case '"econd':
            $x = 'd';
          break;
          case 'Third':
            $x = 'e';
          break;
          case 'Fourth':
            $x = 'f';
          break;
          case 'Last':
            $x = 'g';
          break;
        }
        switch ($test[1]) {
          case 'Day':
            $y = '1';
          break;
          case 'Weekday':
            $y = '2';
          break;
          case 'Monday':
            $y = '3';
          break;
          case 'Tuesday':
            $y = '4';
          break;
          case 'Wednesday':
            $y = '5';
          break;
          case 'Thursday':
            $y = '6';
          break;
          case 'Friday':
            $y = '7';
          break;
          case 'Saturday':
            $y = '8';
          break;
          case 'Sunday':
            $y = '9';
          break;
        }
        $schedule = "{$x} {$y}";
      }
      return $schedule;
    }

    private function filterRuns() {
      for($i = 0; $i < count($this->runList); $i++) {
        for ($x = 0; $x < count($this->runList[$i]['Schedule']); $x++) {
          // If the run has never been completed set LastCompleted to one day prior
          if ($this->runList[$i]['LastCompleted'] === $this->tTest) {
            $this->testDate = clone $this->dateObject;
            $this->testDate->modify('- 1 day')->format('Y-m-d');
          } else {
            $this->testDate = $this->runList[$i]['LastCompleted'];
          }
          if (self::compareSchedule($this->runList[$i]['RunNumber'], $this->runList[$i]['Schedule'][$x]) === TRUE) {
            // Set a flag indicating that the ticket should be added to the new ticket set
            $this->add = TRUE;
            // After the first ticket is added set the flag to FALSE if the new ticket set contains a ticket with the same run number
            if (!empty($this->newTickets)) {
              foreach ($this->newTickets as $test) {
                if ($this->runList[$i]['RunNumber'] === $test['RunNumber']) {
                  $this->add = FALSE;
                }
              }
            }
            if ($this->add === TRUE) {
              $this->newTickets[] = $this->runList[$i];
            }
          }
        }
      }
    }

    private function compareSchedule($runNumber, $scheduleFrequency) {
      // Stop here if the run has been canceled
      if (self::canceledRun($runNumber)) {
        return FALSE;
      }
      if ($this->testDate === $this->today) {
        return FALSE;
      }
      try {
        $this->testDateObject = new \dateTime($this->testDate, $this->timezone);
      } catch (Exception $e) {
        $this->error = 'Date Error Line ' . __line__ . ': ' . $e->getMessage();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      if ($this->error != NULL) return FALSE;
      $test = explode(' ', $scheduleFrequency);
      if ($test[0] !== 'Every') {
        $this->error = 'Something is very wrong. This error should never occur. Line ' . __line__;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      if (count($test) === 2) {
        switch ($test[1]) {
          case 'Day':
            return TRUE;
          case 'Weekday':
            return $this->dateObject->format('N') <= 5;
          default:
            return $test[1] === $this->dateObject->format('l');
        }
      } elseif (count($test) === 3) {
        if ($test[2] === 'Day' || $test[2] === 'Weekday' || $test[2] === $this->dateObject->format('l')) {
          return self::testFrequency($test[1], $test[2]);
        } else {
          return FALSE;
        }
      }
    }

    private function canceledRun($runNumber) {
      // Use month and day to determine if today has an event
      $match = FALSE;
      for ($i = 0; $i < count($this->cancelations); $i++) {
        if (date('n-j-Y', strtotime($this->today)) >= date('n-j-Y', strtotime($this->cancelations[$i]['StartDate'])) && date('n-j-Y', strtotime($this->today)) <= date('n-j-Y', strtotime($this->cancelations[$i]['EndDate']))) {
          if ((int)$this->cancelations[$i]['Cancel'] <= 2 || $this->cancelations[$i]['RunNumber'] === $runNumber) {
            $match = TRUE;
            break;
          }
        }
      }
      return $match;
    }

    private function testFrequency($test, $dayName) {
      switch ($test) {
        case 'Other':
          switch ($dayName) {
            case 'Day':
              return $this->testDateObject->format('j') % 2 === $this->dateObject->format('j') % 2;
            break;
            case 'Weekday':
              $diff = $this->dateObject->diff($this->testDateObject);
              //If it's been more than two days and today is not Monday
              if ($diff->d > 2 && $this->dateObject->format('N') > 1) {
                return $diff->d % 2 == 0;
              } else {
                return $this->testDateObject->modify('+ 2 weekdays')->format('Y-m-d') === $this->dateObject->format('Y-m-d');
              }
            break;
            default:
              /*Sun - Sat*/
              return $this->testDateObject->modify('+ 1 fortnight')->format('Y-m-d') === $this->dateObject->format('Y-m-d'); break;
          }
        break;
        case 'First':
          if ($dayName === 'Weekday') {
            return self::isFirstWeekday($this->dateObject);
          } else {
            return $this->dateObject->format('Y-m-d') === date('Y-m-d', strtotime("first {$dayName} of {$this->dateObject->format('F Y')}"));
          }
        break;
        case 'Second':
          if ($dayName === 'Weekday') {
            return self::isFirstWeekday($this->dateObject->modify('- 1 day'));
          } else {
            return $this->dateObject->format('Y-m-d') === date('Y-m-d', strtotime("second {$dayName} of
            {$this->dateObject->format('F Y')}"));
          }
        break;
        case 'Third':
          if ($dayName === 'Weekday') {
            return self::isFirstWeekday($this->dateObject->modify("- 2 day"));
          } else {
            return $this->dateObject->format('Y-m-d') === date('Y-m-d', strtotime("third {$dayName} of {$this->dateObject->format('F Y')}"));
          }
        break;
        case 'Fourth':
          if ($dayName === 'Weekday') {
            return self::isFirstWeekday($this->dateObject->modify('- 3 day'));
          } else {
            return $this->dateObject->format('Y-m-d') === date('Y-m-d', strtotime("fourth {$dayName} of {$this->dateObject->format('F Y')}"));
          }
        break;
        case 'Last':
          if ($dayName === 'Weekday') {
            switch ($this->dateObject->format('t') - $this->dateObject->format('j')) {
              case 0: return $this->dateObject->format('N') <= 5;
              case 1:
              case 2: return $this->dateObject->format('N') == 5;
              default: return FALSE;
            }
          } else {
            return $this->dateObject->format('Y-m-d') === date('Y-m-d', strtotime("last {$dayName} of {$this->dateObject->format('F Y')}"));
          }
        break;
        default: return FALSE;
      }
    }
    /** http://stackoverflow.com/questions/33446530/testing-for-the-first-weekday-of-the-month-in-php **/
    private function isFirstWeekday($dateObject) {
      switch($dateObject->format('j')) {
        case 1: return $dateObject->format('N') <= 5;
        case 2:
        case 3: return $dateObject->format('N') == 1;
        default: return FALSE;
      }
    }

    private function submitRouteTickets() {
      $data['multiTicket'] = [];
      $data['formKey'] = $this->formKey;
      foreach ($this->newTickets as $newTicket) {
        $micro_date = microtime();
        $date_array = explode(' ',$micro_date);
        $newTicket['TicketNumber'] = $newTicket['RunNumber'] . $this->dateObject->format('m') . '00';
        $newTicket['Contract'] = 1;
        $newTicket['DispatchTimeStamp'] = $newTicket['ReceivedDate'] = $this->dateObject->format('Y-m-d H:i:s');
        $newTicket['DispatchMicroTime'] = substr($date_array[0], 1, 7);
        $newTicket['DispatchedTo'] = $this->driverID;
        $newTicket['DispatchedBy'] = '1.1';
        $newTicket['Charge'] = ($newTicket['RoundTrip'] === 1) ? 6 : 5;
        $newTicket['TicketBase'] = $newTicket['TicketPrice'];

        $data['multiTicket'][] = $newTicket;
      }
      if (!$ticketPrime = self::createTicket($data)) {
        $temp = $this->error . "\n";
        $this->error = __function__ . ' Line ' . __line__ . ': ' . $temp;
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      if ($ticketPrime->processRouteTicket() === FALSE) {
        $this->error .= "\n" . $ticketPrime->getError();
        if ($this->enableLogging !== FALSE) self::writeLoop();
        return FALSE;
      }
      return TRUE;
    }

    private function sortTickets($ticket) {
      if (array_key_exists($ticket['locationTest'], $this->multiLocation) && self::recursive_array_search($ticket['TicketNumber'], $this->multiLocation) === FALSE) {
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

    private function prepTickets() {
      // Set new keys 1) using client name, department, address1, and schedule time for grouping tickets, 2) indicating what step the ticket is on to ease processing.
      foreach ($this->activeTicketSet as $ticket) {
        if ($ticket['pTimeStamp'] === $this->tTest) {
          $ticket['locationTest'] = "{$ticket['pClient']} {$ticket['pDepartment']} {$ticket['pAddress1']} {$ticket['pTime']}";
          $ticket['step'] = 'pickedUp';
          self::sortTickets($ticket);
        } elseif ($ticket['pTimeStamp'] !== $this->tTest && $ticket['dTimeStamp'] === $this->tTest) {
          $ticket['locationTest'] = "{$ticket['dClient']} {$ticket['dDepartment']} {$ticket['dAddress1']} {$ticket['dTime']}";
          $ticket['step'] = 'delivered';
          self::sortTickets($ticket);
        } elseif ($ticket['pTimeStamp'] !== $this->tTest && $ticket['dTimeStamp'] !== $this->tTest) {
          // Non round trip tickets with dTimeStamp !== $tTest will not have been returned from the database. No need to test the charge code.
          $ticket['locationTest'] = "{$ticket['pClient']} {$ticket['pDepartment']} {$ticket['pAddress1']} {$ticket['d2Time']}";
          $ticket['step'] = 'returned';
          self::sortTickets($ticket);
        }
      }
      foreach ($this->ticketSet as $ticket) {
        if (!array_key_exists($ticket['locationTest'], $this->multiLocation)) {
          $this->singleLocation[] = $ticket;
        }
      }
    }
  }
