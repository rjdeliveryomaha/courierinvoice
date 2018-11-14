<?php
  namespace RJDeliveryOmaha\CourierInvoice;
  
  use RJDeliveryOmaha\CourierInvoice\CommonFunctions;
  /***
  * throws Exception
  *
  ***/
  class Client extends CommonFunctions {
    protected $client_index;
    protected $RepeatClient;
    protected $ClientID;
    protected $ClientName;
    protected $Department;
    protected $ShippingAddress1;
    protected $ShippingAddress2;
    protected $ShippingCountry;
    protected $BillingName;
    protected $BillingAddress1;
    protected $BillingAddress2;
    protected $BillingCountry;
    protected $Telephone;
    protected $EmailAddress;
    protected $Attention;
    protected $ContractDiscount;
    protected $GeneralDiscount;
    protected $Organization;
    protected $same;
    protected $currentPw;
    protected $newPw1;
    protected $newPw2;
    protected $flag;
    protected $repeat;
    // Only the org flag doesn't need a second password test so set the test flag to true
    private $secondTest = TRUE;
    private $resourceName;
    private $primaryKey;
    private $testAgainst;
    private $query;
    private $queryData;
    private $result;
    private $newPass;
    private $returnable = [ 'client_index', 'RepeatClient', 'ClientID', 'ClientName', 'Department', 'ShippingAddress1', 'ShippingAddress2', 'ShippingCountry', 'BillingName', 'BillingAddress1', 'BillingAddress2', 'BillingCountry', 'Telephone', 'EmailAddress', 'Attention', 'ContractDiscount', 'GeneralDiscount', 'Organization', 'repeat' ];
    private $updateValues = [ 'ShippingAddress1', 'ShippingAddress2', 'ShippingCountry', 'BillingName', 'BillingAddress1', 'BillingAddress2', 'BillingCountry', 'Telephone', 'EmailAddress', 'Attention' ];
    private $nullable = [ 'Telephone', 'EmailAddress', 'Attention' ];
    private $clientInfo;
    
    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
      if ($this->ulevel === 1 || $this->ulevel === 2) {
        $this->repeat = (strpos($_SESSION['ClientID'], 't') === FALSE);
        $this->clientInfo = [
          'client_index'=>$_SESSION['client_index'],
          'RepeatClient'=>$_SESSION['RepeatClient'],
          'ClientID'=>$_SESSION['ClientID'],
          'ClientName'=>$_SESSION['ClientName'],
          'Department'=>$_SESSION['Department'],
          'ShippingAddress1'=>$_SESSION['ShippingAddress1'],
          'ShippingAddress2'=>$_SESSION['ShippingAddress2'],
          'ShippingCountry'=>$_SESSION['ShippingCountry'],
          'BillingName'=>$_SESSION['BillingName'],
          'BillingAddress1'=>$_SESSION['BillingAddress1'],
          'BillingAddress2'=>$_SESSION['BillingAddress2'],
          'BillingCountry'=>$_SESSION['BillingCountry'],
          'Telephone'=>$_SESSION['Telephone'],
          'EmailAddress'=>$_SESSION['EmailAddress'],
          'Organization'=>$_SESSION['Organization'],
          'formKey'=>$_SESSION['formKey']
        ];
      }
    }
    
    public function getAllClientInfo() {
      $returnData = [];
      foreach ($this as $key => $value) {
        if (in_array($key, $this->returnable)) $returnData[$key] = $value;
      }
      return $returnData;
    }
    
    public function changePassword() {
      $client = self::test_int($_SESSION['ClientID']);
      $this->queryData['method'] = 'GET';
      $this->queryData['formKey'] = $this->formKey;
      if ($this->flag === 'admin') {
        $this->resourceName = 'AdminPassword';
        $this->testAgainst = 'Password';
        $this->primaryKey = 'client_index';
        $this->queryData['endPoint'] = 'clients';
        $this->queryData['queryParams']['resources'] = array('AdminPassword', 'Password', 'client_index');
        $this->queryData['queryParams']['filter'] = array(array('Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$client), array('Resource'=>'RepeatClient', 'Filter'=>'eq','Value'=>$this->RepeatClient));
      } elseif ($this->flag === 'daily') {
        $this->resourceName = 'Password';
        $this->testAgainst = 'AdminPassword';
        $this->primaryKey = 'client_index';
        $this->queryData['endPoint'] = 'clients';
        $this->queryData['queryParams']['resources'] = array('AdminPassword', 'Password', 'client_index');
        $this->queryData['queryParams']['filter'] = array(array('Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$client), array('Resource'=>'RepeatClient', 'Filter'=>'eq','Value'=>$this->RepeatClient));
      } elseif ($this->flag === 'org') {
        $this->resourceName = 'Password';
        $this->secondTest = FALSE;
        $this->primaryKey = 'o_client_index';
        $this->queryData['endPoint'] = 'o_clients';
        $this->queryData['queryParams']['resources'] = array('Password', 'o_client_index');
        $this->queryData['queryParams']['filter'] = array(array('Resource'=>'ID', 'Filter'=>'eq', 'Value'=>$client));
      } else {
        return '<span class="error">Invalid Flag</span>';
      }
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        echo $this->error;
        return FALSE;
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === FALSE) {
        echo $this->error;
        return FALSE;
      }
      // Now that we have the data to test againt start building the update query
      // queryData['endPoint'] and queryData['formKey'] do not change
      $this->queryData['method'] = 'PUT';
      $this->queryData['primaryKey'] = $this->result[0][$this->primaryKey];
      $this->queryData['queryParams'] = [];
      $hash = $this->result[0][$this->resourceName];
      if (!(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^\&*\)\(\{\}\[\]\-_.=+\?\:;,])(?=.{8,}).*$/', $this->newPw1))) {
        // Make sure the new password meets criteria
        echo '<p class="center"><span class="error">Password does not meet criteria.</span></p>';
        return FALSE;
      } elseif($this->currentPw === $this->newPw1) {
        // Make sure that the password is actually being changed
        echo '<p class="center"><span class="error">Invalid entry:</span> The password should be changed.</p>';
        return FALSE;
      } elseif ($this->newPw1 !== $this->newPw2) {
        // Make sure the same value was entered twice
        echo '<p class="center"><span class="error">Invalid entry:</span> The values entered don\'t match.</p>';
        return FALSE;
      }
      // Test old password
      if (!password_verify($this->currentPw, $hash)) {
        echo '<p class="center"><span class="error">Invalid Entry:</span> The password entered doesn\'t match the password on file.</p>';
        return FALSE;
      } else {
        if ($this->secondTest) {
          $hash2 = $this->result[0][$this->testAgainst];
          //Compare new admin password to current daily password
          if (password_verify($this->newPw1, $hash2)) {
            echo '<p><span class="error">Invalid Entry.</span> Admin password and daily user password must not match.</p>';
            return FALSE;
          }
        }
        $options = [
         'cost' => 12,
        ];
        $this->newPass = password_hash($this->newPw1, PASSWORD_DEFAULT, $options);
        $this->queryData['payload'] = array($this->resourceName=>$this->newPass);
        $this->query = self::createQuery($this->queryData);
        if ($this->query === FALSE) {
          echo $this->error . ' No chages were made to the account.';
          return FALSE;
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === FALSE) {
          echo $this->error;
          return FALSE;
        }
        switch ($this->flag) {
          case 'daily':
            if ($this->newPw1 === '!Delivery1') {
              if ($_SESSION['pwWarning'] === 0 || $_SESSION['pwWarning'] === 2) {
                $_SESSION['pwWarning'] += 1;
              }
            } else {
              if ($_SESSION['pwWarning'] === 1 || $_SESSION['pwWarning'] === 3) {
                $_SESSION['pwWarning'] -= 1;
              }
            }
          break;
          case 'admin':
            if ($this->newPw1 === '!Delivery2') {
              if ($_SESSION['pwWarning'] === 0 || $_SESSION['pwWarning'] === 1) {
                $_SESSION['pwWarning'] += 2;
              }
            } else {
              if ($_SESSION['pwWarning'] === 2 || $_SESSION['pwWarning'] === 3) {
                $_SESSION['pwWarning'] -= 2;
              }
            }
          break;
          case 'org':
            if ($this->newPw1 === '3Delivery!') {
              if ($_SESSION['pwWarning'] === 0) {
                $_SESSION['pwWarning'] += 4;
              }
            } else {
              if ($_SESSION['pwWarning'] === 4) {
                $_SESSION['pwWarning'] -= 4;
              }
            }
          break;
        }
        echo '<p class="center big">Password Updated.</p>';
        return TRUE;
      }
    }
    
    public function updateInfo() {
      if ($this->same === 1) {
        $this->BillingName = $this->ClientName;
        $this->BillingAddress1 = $this->ShippingAddress1;
        $this->BillingAddress2 = $this->ShippingAddress2;
        $this->BillingCountry = $this->ShippingCountry;
      }
      if ($this->Telephone !== '' && $this->Telephone !== NULL) {
        if (!self::test_phone($this->Telephone)) {
          $this->error = 'Invalid Telephone Number: Please use the format 555-321-1234x567';
          echo $this->error;
          return FALSE;
        }
      }
      // clear the query data of values used i fetchClientIndex
      $this->queryData = [];
      $this->queryData['formKey'] = $this->formKey;
      $this->queryData['method'] = 'PUT';
      $this->queryData['primaryKey'] = ($this->repeat === TRUE) ? $this->clientInfo['client_index'] : $this->clientInfo['t_client_index'];
      $this->queryData['endPoint'] = ($this->repeat === TRUE) ? 'clients' : 't_clients';
      for ($i = 0; $i < count($this->updateValues); $i++) {
        $this->queryData['payload'][$this->updateValues[$i]] = (strpos($this->updateValues[$i], 'Country') !== FALSE) ? self::countryFromAbbr($this->{$this->updateValues[$i]}) : $this->{$this->updateValues[$i]};
      }
      // Build the update query
      $this->query = self::createQuery($this->queryData);
      if ($this->query === FALSE) {
        echo $this->error;
        return FALSE;
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === FALSE) {
        echo $this->error;
        return FALSE;
      }
      // update the session
      for ($i = 0; $i < count($this->updateValues); $i++) {
        $_SESSION[$this->updateValues[$i]] = $this->{$this->updateValues[$i]};
      }
      echo 'Update Successful';
      return FALSE;
    }
  }