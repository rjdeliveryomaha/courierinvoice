<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  use rjdeliveryomaha\courierinvoice\Query;

  class LoginHandler extends CommonFunctions {
    protected $clientID;
    protected $upw;
    private $repeatFlag;
    private $query;
    private $queryData;
    private $response;
    private $configResult;
    private $courierResult;
    private $discountResult;
    private $client;
    private $loginType;
    // Define an array of key names not to include in the session
    private $exclude = ['Password', 'AdminPassword', 'Deleted'];
    // Define key names that will be sent through countryFromAbbr()
    private $countryParams = ['ShippingCountry', 'BillingCountry'];

    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
      $_SESSION['mobile'] = (array_key_exists('mobile', $data)) ? self::test_bool($data['mobile']) : FALSE;
      if (!isset($_SESSION['formKey']) && isset($data['formKey'])) $_SESSION['formKey'] = $data['formKey'];
    }

    public function login() {
      // Save the formKey
      $tempKey = (array_key_exists('formKey', $_SESSION)) ? $_SESSION['formKey'] : NULL;
      $tempFlag = (array_key_exists('mobile', $_SESSION)) ? $_SESSION['mobile'] : FALSE;
      // Clear any session created by prior login
      $_SESSION = array();
      // Restore the formKey
      $_SESSION['formKey'] = $tempKey;
      $_SESSION['mobile'] = $tempFlag;
      if ($this->clientID === NULL || $this->upw === NULL) {
        throw new \Exception('Invalid Credentials');
      }
      if (preg_match('/^[1-9][0-9]{0,10}$/', $this->clientID) === 1 || preg_match('/^[t][1-9][0-9]{0,10}$/', $this->clientID) === 1) {
        $this->repeatFlag = (strpos($this->clientID, 't') === FALSE) ? 1 : 0;
        $this->queryData['noSession'] = TRUE;
        $this->queryData['formKey'] = $this->formKey;
        $this->queryData['method'] = 'GET';
        $this->queryData['endPoint'] = 'clients';
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>self::test_int($this->clientID)], ['Resource'=>'RepeatClient','Filter'=>'eq','Value'=>$this->repeatFlag], ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0] ];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === FALSE) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === FALSE) {
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
        $this->queryData['noSession'] = TRUE;
        $this->queryData['formKey'] = $this->formKey;
        $this->queryData['method'] = 'GET';
        $this->queryData['endPoint'] = 'drivers';
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'DriverID', 'Filter'=>'eq', 'Value'=>$this->clientID], ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0] ];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === FALSE) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === FALSE) {
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
        $this->queryData['noSession'] = TRUE;
        $this->queryData['formKey'] = $this->formKey;
        $this->queryData['method'] = 'GET';
        $this->queryData['endPoint'] = 'dispatchers';
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'DispatchID', 'Filter'=>'eq', 'Value'=>$this->clientID], ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0] ];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === FALSE) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === FALSE) {
          throw new \Exception($this->error);
        }
        if (empty($this->result[0])) {
          throw new \Exception('Invalid Credentials');
        }
        return self::validateDispatch();
      } elseif (preg_match('/^([\w]+)/', $this->clientID) === 1) {
        if ($this->clientID === $this->userLogin) {
          // login user 0
          $this->queryData['noSession'] = TRUE;
          $this->queryData['formKey'] = $this->formKey;
          $this->queryData['method'] = 'GET';
          $this->queryData['endPoint'] = 'clients';
          $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>0] ];
        } else {
          // Single word login for organizations
          $temp = self::test_input($this->clientID);
          $this->clientID = $temp;
          $this->queryData['noSession'] = TRUE;
          $this->queryData['formKey'] = $this->formKey;
          $this->queryData['method'] = 'GET';
          $this->queryData['endPoint'] = 'o_clients';
          $this->queryData['queryParams']['filter'] = [ ['Resource'=>'Login', 'Filter'=>'eq', 'Value'=>$this->clientID], ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0] ];
        }
        $this->query = self::createQuery($this->queryData);
        if ($this->query === FALSE) {
          throw new \Exception($this->error);
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === FALSE) {
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

    private function validateDriver() {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'driver';
      } else {
        throw new \Exception('Invalid Credentials');
      }
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
      $_SESSION['driverName'] = $_SESSION['config']['driverName'] = $this->result[0]['FirstName'] . " " . $this->result[0]['LastName'];
      $_SESSION['ClientName'] = $_SESSION['driverName'];
      $_SESSION['ClientID'] = $this->result[0]['DriverID'];
      $user_browser = $_SERVER['HTTP_USER_AGENT'];
      $_SESSION['login_string'] = hash('sha512', $this->result[0]['Password'] . $user_browser);
      $_SESSION['ulevel'] = 'driver';
      $_SESSION['CanDispatch'] = $this->result[0]['CanDispatch'];
      echo '/drivers';
      return FALSE;
    }

    private function validateDispatch() {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'dispatch';
      } else {
        throw new \Exception('Invalid Credentials');
      }
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
      $_SESSION['driverName'] = $_SESSION['config']['driverName'] = $this->result[0]['FirstName'] . " " . $this->result[0]['LastName'];
      $_SESSION['ClientName'] = $_SESSION['driverName'];
      $_SESSION['ClientID'] = $this->result[0]['DispatchID'];
      $user_browser = $_SERVER['HTTP_USER_AGENT'];
      $_SESSION['login_string'] = hash('sha512', $this->result[0]['Password'] . $user_browser);
      $_SESSION['ulevel'] = 'dispatch';
      $_SESSION['CanDispatch'] = 2;
      echo '/drivers';
      return FALSE;
    }

    private function validateClient() {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'client';
        $hash = $this->result[0]['Password'];
      } elseif (password_verify($this->upw, $this->result[0]['AdminPassword'])) {
        $this->loginType = 'clientAdmin';
        $hash = $this->result[0]['AdminPassword'];
      } else {
        throw new \Exception('Invalid Credentials');
      }
      try {
        self::fetchConfig();
      } catch (Exception $e) {
        throw $e;
      }
      foreach ($this->result[0] as $key => $value) {
        if (!in_array($key, $this->exclude)) {
          if ($key === 'GeneralDiscount' || $key === 'ContractDiscount') {
            $_SESSION['config'][$key][$this->result[0]['ClientID']] = floatval((100 - $value) / 100);
          } else {
            $_SESSION[$key] = (in_array($key, $this->countryParams)) ? self::countryFromAbbr($value) : $value;
          }
        }
      }
      $_SESSION['pwWarning'] = 0;
      if (password_verify('!Delivery1', $this->result[0]['Password'])) {
        $_SESSION['pwWarning'] += 1;
      }
      if (password_verify('!Delivery2', $this->result[0]['AdminPassword'])) {
        $_SESSION['pwWarning'] += 2;
      }
      $user_browser = $_SERVER['HTTP_USER_AGENT'];
      $_SESSION['login_string'] = hash('sha512', $hash . $user_browser);
      $_SESSION['ulevel'] = ($this->loginType === 'client') ? 2 : 1;
      echo '/clients';
      return FALSE;
    }

    private function validateOrg() {
      if (password_verify($this->upw, $this->result[0]['Password'])) {
        $this->loginType = 'org';
      } else {
        throw new \Exception('Invalid Credentials');
      }
      try {
        self::fetchConfig();
      } catch (Exception $e) {
        throw $e;
      }
      $_SESSION['pwWarning'] = 0;
      if (password_verify('3Delivery!', $this->result[0]['Password'])) {
        $_SESSION['pwWarning'] += 4;
      }
      $user_browser = $_SERVER['HTTP_USER_AGENT'];
      $_SESSION['login_string'] = hash('sha512', $this->result[0]['Password'] . $user_browser);
      $_SESSION['ClientName'] = $this->result[0]['Name'];
      $_SESSION['ClientID'] = $this->result[0]['id'];
      $_SESSION['ListBy'] = $this->result[0]['ListBy'];
      $_SESSION['ulevel'] = 0;
      self::fetchOrgClients();
      echo "/clients";
      return FALSE;
    }

    private function fetchConfig() {
      $this->queryData = [];
      $this->queryData['noSession'] = TRUE;
      $this->queryData['formKey'] = $this->formKey;
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = 'config';
      $this->queryData['queryParams'] = [];
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        throw new \Exception($this->error);
      }
      $this->configResult = self::callQuery($this->query);
      if ($this->configResult === FALSE) {
        throw new \Exception($this->error);
      }
      if (empty($this->configResult[0])) {
        throw new \Exception('Unable To Fetch Configuration');
      }
      $_SESSION['config'] = $this->configResult[0];
      // pause for one quater (1/4 (0.25)) of a second between API calls
      time_nanosleep(0, 250000000);
      $this->queryData['endPoint'] = 'clients';
      $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>0] ];
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        throw new \Exception($this->error);
      }
      $this->courierResult = self::callQuery($this->query);
      if ($this->courierResult === FALSE) {
        throw new \Exception($this->error);
      }
      if (empty($this->courierResult[0])) {
        throw new \Exception('Unable To Fetch Configuration');
      }
      foreach ($this->courierResult[0] as $key => $value) {
        if ($key === 'GeneralDiscount' || $key === 'ContractDiscount') {
          $_SESSION['config'][$key] = array();
        } else {
          $_SESSION['config'][$key] = (in_array($key, $this->countryParams)) ? self::countryFromAbbr($value) : $value;
        }
      }
      if ($this->loginType === 'driver' || $this->loginType === 'dispatch') {
        // pause for one quater (1/4 (0.25)) of a second between API calls
        time_nanosleep(0, 250000000);
        $this->queryData['queryParams']['include'] = [ 'ClientID', 'ContractDiscount', 'GeneralDiscount' ];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0] ];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === FALSE) {
          throw new \Exception($this->error);
        }
        $this->discountResult = self::callQuery($this->query);
        if ($this->discountResult === FALSE) {
          throw new \Exception($this->error);
        }
        if (empty($this->discountResult)) {
          throw new \Exception('Unable To Fetch Configuration');
        }
        foreach ($this->discountResult as $temp) {
          $_SESSION['config']['GeneralDiscount'][$temp['ClientID']] = floatval((100 - $temp['GeneralDiscount']) / 100);
          $_SESSION['config']['ContractDiscount'][$temp['ClientID']] = floatval((100 - $temp['ContractDiscount']) / 100);
        }
      }
    }

    private function fetchOrgClients() {
      $this->queryData = [];
      $this->queryData['noSession'] = TRUE;
      $this->queryData['formKey'] = $this->formKey;
      $this->queryData['method'] = 'GET';
      $this->queryData['endPoint'] = 'clients';
      $this->queryData['queryParams']['filter'] = [ [ 'Resource'=>'Deleted', 'Filter'=>'eq', 'Value'=>0 ], [ 'Resource'=>'Organization', 'Filter'=>'eq', 'Value'=>$_SESSION['ClientID'] ] ];
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        throw new \Exception($this->error);
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === FALSE) {
        throw new \Exception($this->error);
      }
      if (empty($this->result)) {
        throw new \Exception('Unable To Fetch Organization Members');
      }
      foreach ($this->result as $member) {
        $marker = ($member['RepeatClient'] === 0) ? 't' : '';
        $_SESSION['members'][$marker . $member['ClientID']] = [];
        foreach ($member as $key => $value) {
          if (!in_array($key, $this->exclude)) {
            $_SESSION['members'][$marker . $member['ClientID']][$key] = (in_array($key, $this->countryParams)) ? self::countryFromAbbr($value) : $value;
          }
        }
      }
    }
  }
