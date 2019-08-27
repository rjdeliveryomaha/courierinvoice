<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  use rjdeliveryomaha\courierinvoice\Query;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  class LoginHandler extends CommonFunctions
  {
    protected $clientID;
    protected $upw;
    private $repeatFlag;
    private $query;
    private $queryData;
    private $result;
    private $clientResult;
    private $configResult;
    private $courierResult;
    private $discountResult;
    private $client;
    private $loginType;
    // key names not to include in the session
    private $exclude = [ 'Password', 'AdminPassword', 'Deleted', 'config' ];
    // key names of percentages
    private $percentages = [ 'GeneralDiscount', 'ContractDiscount', 'StandardVAT', 'ReducedVAT' ];
    // key names that will be sent through countryFromAbbr()
    private $countryParams = [ 'ShippingCountry', 'BillingCountry' ];

    public function __construct($options, $data=[])
    {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
      $_SESSION['mobile'] = (array_key_exists('mobile', $data)) ? self::test_bool($data['mobile']) : false;
    }

    public function login()
    {
      // Save the formKey
      $tempKey = (array_key_exists('formKey', $_SESSION)) ? $_SESSION['formKey'] : null;
      $tempFlag = (array_key_exists('mobile', $_SESSION)) ? $_SESSION['mobile'] : false;
      // Clear any session created by prior login
      $_SESSION = array();
      // Restore the formKey
      $_SESSION['formKey'] = $tempKey;
      $_SESSION['mobile'] = $tempFlag;
      if ($this->clientID === null || $this->upw === null) {
        throw new \Exception('Invalid Credentials');
      }
      if (
        preg_match('/^[1-9][0-9]{0,10}$/', $this->clientID) === 1 ||
        preg_match('/^[t][1-9][0-9]{0,10}$/', $this->clientID) === 1
      ) {
        $this->repeatFlag = (strpos($this->clientID, 't') === false) ? 1 : 0;
        $this->queryData['noSession'] = true;
        $this->queryData['method'] = 'GET';
        $this->queryData['endPoint'] = 'clients';
        $this->queryData['queryParams']['filter'] = [
          ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>self::test_int($this->clientID)],
          ['Resource'=>'RepeatClient','Filter'=>'eq','Value'=>$this->repeatFlag],
          ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
        ];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === false) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === false) {
          throw new \Exception($this->error);
        }
        if (empty($this->result[0])) {
          throw new \Exception('<span class="error">Invalid Credentials</span>');
        }
        return self::validateClient();
      } elseif (strtolower(substr($this->clientID, 0, 6)) === 'driver') {
        // Driver Login Using "driver" + ID Number
        $temp = self::test_int($this->clientID);
        $this->clientID = $temp;
        $this->queryData['noSession'] = true;
        $this->queryData['formKey'] = $this->formKey;
        $this->queryData['method'] = 'GET';
        $this->queryData['endPoint'] = 'drivers';
        $this->queryData['queryParams']['filter'] = [
          ['Resource'=>'DriverID', 'Filter'=>'eq', 'Value'=>$this->clientID],
          ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
        ];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === false) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === false) {
          throw new \Exception($this->error);
        }
        if (empty($this->result[0])) {
          throw new \Exception('Invalid Credentials');
        }
        return self::validateDriver();
      } elseif (preg_match('/(\bdispatch\d+\b)/i', $this->clientID) === 1) {
        // Dispatch Login using "dispatch" + ID Number
        $temp = self::test_int($this->clientID);
        $this->clientID = $temp;
        $this->queryData['noSession'] = true;
        $this->queryData['method'] = 'GET';
        $this->queryData['endPoint'] = 'dispatchers';
        $this->queryData['queryParams']['filter'] = [
          ['Resource'=>'DispatchID', 'Filter'=>'eq', 'Value'=>$this->clientID],
          ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
        ];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === false) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === false) {
          throw new \Exception($this->error);
        }
        if (empty($this->result[0])) {
          throw new \Exception('Invalid Credentials');
        }
        return self::validateDispatch();
      } elseif (preg_match('/^([\w]+)/', $this->clientID) === 1) {
        if ($this->clientID === $this->userLogin) {
          // login user 0
          $this->queryData['noSession'] = true;
          $this->queryData['method'] = 'GET';
          $this->queryData['endPoint'] = 'clients';
          $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>0] ];
        } else {
          // Single word login for organizations
          $temp = self::test_input($this->clientID);
          $this->clientID = $temp;
          $this->queryData['noSession'] = true;
          $this->queryData['method'] = 'GET';
          $this->queryData['endPoint'] = 'o_clients';
          $this->queryData['queryParams']['filter'] = [
            ['Resource'=>'Login', 'Filter'=>'eq', 'Value'=>$this->clientID],
            ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0]
          ];
          if ($this->clientID !== $this->username) $this->queryData['queryParams']['join'] = [ 'clients' ];
        }
        $this->query = self::createQuery($this->queryData);
        if ($this->query === false) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === false) {
          throw new \Exception($this->error);
        }
        if (empty($this->result[0])) {
          throw new \Exception('Invalid Credentials');
        }
        return ($this->clientID === $this->userLogin) ? self::validateClient() : self::validateOrg();
      } else {
        throw new \Exception('Invalid Credentials');
      }
    }

    private function validateDriver()
    {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'driver';
      } else {
        throw new \Exception('Invalid Credentials');
      }
      SecureSessionHandler::regenerate_session();
      try {
        self::fetchConfig();
      } catch (Exception $e) {
        throw $e;
      }
      // Add the driver info to the session array
      foreach ($this->result[0] as $key => $value) {
        if (!in_array($key, $this->exclude)) {
          $_SESSION[$key] = $value;
        }
      }
      unset($_SESSION['error']);
      $_SESSION['driverName'] = $this->result[0]['FirstName'] . " " . $this->result[0]['LastName'];
      $_SESSION['ulevel'] = 'driver';
      echo '/drivers';
      return false;
    }

    private function validateDispatch()
    {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'dispatch';
      } else {
        throw new \Exception('Invalid Credentials');
      }
      SecureSessionHandler::regenerate_session();
      try {
        self::fetchConfig();
      } catch (Exception $e) {
        throw $e;
      }
      // Add the driver info to the session array
      foreach ($this->result[0] as $key => $value) {
        if (!in_array($key, $this->exclude)) {
          $_SESSION[$key] = $value;
        }
      }
      unset($_SESSION['error']);
      $_SESSION['ulevel'] = 'dispatch';
      $_SESSION['CanDispatch'] = 2;
      echo '/drivers';
      return false;
    }

    private function validateClient()
    {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'client';
        $hash = $this->result[0]['Password'];
      } elseif (password_verify($this->upw, $this->result[0]['AdminPassword'])) {
        $this->loginType = 'clientAdmin';
        $hash = $this->result[0]['AdminPassword'];
      } else {
        throw new \Exception('Invalid Credentials');
      }
      SecureSessionHandler::regenerate_session();
      try {
        self::fetchConfig();
      } catch (Exception $e) {
        throw $e;
      }
      $clientMarker = ($this->repeatFlag === 1) ? '' : 't';
      foreach ($this->result[0] as $key => $value) {
        if (!in_array($key, $this->exclude)) {
          if (in_array($key, $this->percentages)) {
            $_SESSION['config'][$key]["{$clientMarker}{$this->result[0]['ClientID']}"] =
              (substr($key, -3) === 'VAT') ?  $value : (100 - $value) / 100;
          } elseif ($key === 'StandardVAT' || $key === 'ReducedVAT') {
            $_SESSION[$key] = round(1 + ($value / 100), 2, PHP_ROUND_HALF_UP);
          } else {
            $_SESSION[$key] = (in_array($key, $this->countryParams)) ? self::countryFromAbbr($value) : $value;
          }
        }
      }
      unset($_SESSION['error']);
      $_SESSION['pwWarning'] = 0;
      if (password_verify('!Delivery1', $this->result[0]['Password'])) {
        $_SESSION['pwWarning'] += 1;
      }
      if (password_verify('!Delivery2', $this->result[0]['AdminPassword'])) {
        $_SESSION['pwWarning'] += 2;
      }
      $_SESSION['ulevel'] = ($this->loginType === 'client') ? 2 : 1;
      echo '/clients';
      return false;
    }

    private function validateOrg()
    {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'org';
      } else {
        throw new \Exception('Invalid Credentials');
      }
      SecureSessionHandler::regenerate_session();
      try {
        self::fetchConfig();
      } catch (Exception $e) {
        throw $e;
      }
      $_SESSION['pwWarning'] = 0;
      if (password_verify('3Delivery!', $this->result[0]['Password'])) {
        $_SESSION['pwWarning'] += 4;
      }
      unset($_SESSION['error']);
      $_SESSION['Login'] = $this->result[0]['Login'];
      $_SESSION['ClientName'] = $this->result[0]['Name'];
      $_SESSION['ClientID'] = $this->result[0]['id'];
      $_SESSION['ListBy'] = $this->result[0]['ListBy'];
      $_SESSION['ulevel'] = 0;
      self::fetchOrgClients();
      echo '/clients';
      return false;
    }

    private function fetchConfig()
    {
      $this->queryData = [];
      $this->queryData['noSession'] = true;
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = 'clients';
      $this->queryData['queryParams']['filter'] =
        ($this->loginType === 'driver' || $this->loginType === 'dispatch') ?
        [ ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0] ] :
        [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>0] ];
      $this->queryData['queryParams']['join'] = [ 'config' ];
      $this->query = self::createQuery($this->queryData);
      if ($this->query === false) {
        throw new \Exception($this->error);
      }
      $this->configResult = self::callQuery($this->query);
      if ($this->configResult === false) {
        throw new \Exception($this->error);
      }
      if (empty($this->configResult[0])) {
        throw new \Exception('Unable To Fetch Configuration');
      }
      $_SESSION['config']['GeneralDiscount'] =
      $_SESSION['config']['ContractDiscount'] =
      $_SESSION['config']['DeliveryStandardVAT'] =
      $_SESSION['config']['DeliveryReducedVAT'] = [];
      for ($i = 0; $i < count($this->configResult); $i++) {
        if ($this->configResult[$i]['ClientID'] === 0) {
          $_SESSION['config'] = $this->configResult[$i]['config'][0];
          foreach($this->configResult[$i] as $key => $value) {
            if (!in_array($key, $this->exclude) && !in_array($key, $this->percentages)) {
              $_SESSION['config'][$key] = (in_array($key, $this->countryParams)) ?
                self::countryFromAbbr($value) : $value;
            }
          }
        }
        $clientMarker = ($this->configResult[$i]['RepeatClient'] === 1) ?
        $this->configResult[$i]['ClientID'] : "t{$this->configResult[$i]['ClientID']}";

        $_SESSION['config']['GeneralDiscount'][$clientMarker] =
          (100 - $this->configResult[$i]['GeneralDiscount']) / 100;

        $_SESSION['config']['ContractDiscount'][$clientMarker] =
          (100 - $this->configResult[$i]['ContractDiscount']) / 100;

        $_SESSION['config']['StandardVAT'][$clientMarker] = $this->configResult[$i]['StandardVAT'];

        $_SESSION['config']['ReducedVAT'][$clientMarker] = $this->configResult[$i]['ReducedVAT'];
      }
    }

    private function fetchOrgClients()
    {
      if (!isset($this->result[0]['clients'])) {
        $this->queryData = [];
        $this->queryData['noSession'] = true;
        $this->queryData['method'] = 'GET';
        $this->queryData['endPoint'] = 'clients';
        $this->query = self::createQuery($this->queryData);
        if ($this->query === false) {
          throw new \Exception($this->error);
        }
        $this->clientResult = self::callQuery($this->query);
        if ($this->clientResult === false) {
          throw new \Exception($this->error);
        }
        if (empty($this->clientResult)) {
          throw new \Exception('Unable To Fetch Organization Members');
        }
        $this->result[0]['clients'] = $this->clientResult;
      }
      foreach ($this->result[0]['clients'] as $member) {
        if ($member['Deleted'] === 1) continue;
        $marker = ($member['RepeatClient'] === 0) ? 't' : '';
        $_SESSION['members'][$marker . $member['ClientID']] = [];
        foreach ($member as $key => $value) {
          if (!in_array($key, $this->exclude)) {
            $_SESSION['members'][$marker . $member['ClientID']][$key] = (in_array($key, $this->countryParams)) ?
            self::countryFromAbbr($value) : $value;
          }
        }
      }
    }
  }
