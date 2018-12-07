<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
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
    private $userType;

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
        $this->queryData['queryParams']['include'] = ['AdminPassword', 'Password', 'client_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$client], ['Resource'=>'RepeatClient', 'Filter'=>'eq','Value'=>$this->RepeatClient] ];
      } elseif ($this->flag === 'daily') {
        $this->resourceName = 'Password';
        $this->testAgainst = 'AdminPassword';
        $this->primaryKey = 'client_index';
        $this->queryData['endPoint'] = 'clients';
        $this->queryData['queryParams']['include'] = ['AdminPassword', 'Password', 'client_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$client], ['Resource'=>'RepeatClient', 'Filter'=>'eq','Value'=>$this->RepeatClient] ];
      } elseif ($this->flag === 'org') {
        $this->resourceName = 'Password';
        $this->secondTest = FALSE;
        $this->primaryKey = 'o_client_index';
        $this->queryData['endPoint'] = 'o_clients';
        $this->queryData['queryParams']['include'] = ['Password', 'o_client_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ID', 'Filter'=>'eq', 'Value'=>$client] ];
      } elseif ($this->flag === 'driver') {
        $this->resourceName = 'Password';
        $this->secondTest = FALSE;
        $this->primaryKey = 'driver_index';
        $this->queryData['endPoint'] = 'drivers';
        $this->queryData['queryParams']['include'] = ['Password', 'driver_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'DriverID', 'Filter'=>'eq', 'Value'=>$client] ];
      } elseif ($this->flag === 'dispatch') {
        $this->resourceName = 'Password';
        $this->secondTest = FALSE;
        $this->primaryKey = 'dispatch_index';
        $this->queryData['endPoint'] = 'dispatchers';
        $this->queryData['queryParams']['include'] = ['Password', 'dispatch_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'DispatchID', 'Filter'=>'eq', 'Value'=>$client] ];
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
        echo '<p class="center"><span class="error">Invalid entry</span>: The password should be changed.</p>';
        return FALSE;
      } elseif ($this->newPw1 !== $this->newPw2) {
        // Make sure the same value was entered twice
        echo '<p class="center"><span class="error">Invalid entry</span>: The values entered don\'t match.</p>';
        return FALSE;
      }
      // Test old password
      if (!password_verify($this->currentPw, $hash)) {
        echo '<p class="center"><span class="error">Invalid Entry</span>: The password entered doesn\'t match the password on file.</p>';
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
        $this->queryData['payload'] = [$this->resourceName=>$this->newPass];
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

    private function passwordForm() {
      if ($this->userType === 'admin') {
        $showPWwarning = ($this->pwWarning === 2 || $this->pwWarning === 3) ? '' : 'hide';
        $formID = 'apwUpdate';
        $flag = 'admin';
        $type = 'client';
        $id = $_SESSION['ClientID'];
      } elseif ($this->userType === 'daily') {
        $showPWwarning = ($this->pwWarning === 1 || $this->pwWarning === 3) ? '' : 'hide';
        $formID = 'pwUpdate';
        $flag = 'daily';
        $type = 'client';
        $id = $_SESSION['ClientID'];
      } elseif ($this->userType === 'org') {
        $showPWwarning = ($this->pwWarning === 4) ? '' : 'hide';
        $formID = 'opwUpdate';
        $flag = 'org';
        $type = 'client';
        $id = $_SESSION['ClientID'];
      } elseif ($this->userType === 'driver') {
        $showPWwarning = 'hide';
        $formID = 'driverPwUpdate';
        $flag = 'driver';
        $type = 'driver';
        $id = $_SESSION['ClientID'];
      } elseif ($this->userType === 'dispatch') {
        $showPWwarning = 'hide';
        $formID = 'dispatchPwUpdate';
        $flag = 'dispatch';
        $type = 'dispatch';
        $id = $_SESSION['ClientID'];
      }
      return "
            <div class=\"PWcontainer\">
              <div class=\"PWform\">
                <form id=\"{$formID}\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                  <input type=\"hidden\" name=\"{$type}\" class=\"{$type}\" value=\"{$id}\" form=\"{$formID}\" />
                  <input type=\"hidden\" name=\"flag\" class=\"flag\" value=\"{$flag}\" form=\"{$formID}\" />
                  <table>
                    <tr>
                      <td><label for=\"currentPw\">Current Password</label>:</td>
                      <td><input type=\"password\" name=\"currentPw\" class=\"currentPw\"  form=\"{$formID}\" /></td>
                    </tr>
                    <tr>
                      <td><label for=\"newPw1\">New Password</label>:</td>
                      <td><input type=\"password\" name=\"newPw1\" class=\"newPw1\" form=\"{$formID}\" />
                    </tr>
                    <tr>
                      <td><label for=\"newPw2\">Confirm Password</label>:</td>
                      <td><input type=\"password\" name=\"newPw2\" class=\"newPw2\" form=\"{$formID}\" /></td>
                    </tr>
                    <tr>
                      <td>
                        <button type=\"submit\" class=\"PWsubmit\" form=\"{$formID}\">Submit</button>
                        <button type=\"reset\" class=\"clearPWform\" form=\"{$formID}\">Clear</button>
                      </td>
                      <td><label for=\"showText\">Show Text:</label> <input type=\"checkbox\" class=\"showText\" name=\"showText\"  form=\"{$formID}\" /></td>
                    </tr>
                    <tr>
                      <td style=\"height:2em;\" class=\"message\" colspan=\"2\"></td>
                    </tr>
                  </table>
                </form>
              </div>
              <div class=\"criteria\">
                <span>Password criteria:</span>
                <br>
                <ul>
                  <li class=\"defaultWarning {$showPWwarning}\">Password should be changed from default.</li>
                  <li>Passwords must be at least 8 characters long.</li>
                  <li>Passwords must contain:</li>
                    <ul>
                      <li>At least one upper case letter. <span style=\"background:black;color:#90EE90;\"> A..Z </span></li>
                      <li>At least one lower case letter. <span style=\"background:black;color:#90EE90;\">a..z</span></li>
                      <li>At least one number. <span style=\"background:black;color:#90EE90;\">0..9</span></li>
                      <li>At least one special character. <span style=\"background:black;color:#90EE90;\"> ! @ # $ % ^ & * ( ) { } [ ] - _ . : ; , = + </span></li>
                    </ul>
                  <li>The \"New Password\" and \"Confirm Password\" fields must match.</li>
                  <li>daily user password must be different from admin password.</li>
                </ul>
              </div>
            </div>";
    }

    public function adminPasswordForm() {
      $this->userType = 'admin';
      return $this->passwordForm();
    }

    public function dailyPasswordForm() {
      $this->userType = 'daily';
      return $this->passwordForm();
    }

    public function orgPasswordForm() {
      $this->userType = 'org';
      return $this->passwordForm();
    }

    public function driverPasswordForm() {
      $this->userType = 'driver';
      return $this->passwordForm();
    }

    public function dispatchPasswordForm() {
      $this->userType = 'dispatch';
      return $this->passwordForm();
    }

    public function updateInfoForm() {
      $requireCountry = $requireCountry2 = $_SESSION['config']['InternationalAddressing'] === 1 ? 'required' : '';
      if ($_SESSION['ClientName'] === $_SESSION['BillingName'] && $_SESSION['ShippingAddress1'] === $_SESSION['BillingAddress1'] && $_SESSION['ShippingAddress2'] === $_SESSION['BillingAddress2']) {
        $sameChecked = 'checked';
        $sameDisabled = $requireCountry2 = 'disabled';
        $hideClass = 'hide';
        $billingName = $billingAddress1 = $billingAddress2 = $billingCountry = '';
      } else {
        $sameChecked = $sameDisabled = $hideClass = '';
        $billingName = $_SESSION['BillingName'];
        $billingAddress1 = $_SESSION['BillingAddress1'];
        $billingAddress2 = $_SESSION['BillingAddress2'];
        $billingCountry = $_SESSION['BillingCountry'];
      }
      $hideCountry = $_SESSION['config']['InternationalAddressing'] === 1 ? '' : 'hide';
      return "
            <div id=\"clientUpdateForm\">
              <p id=\"clientUpdateResult\" class=\"center\"></p>
              <form id=\"clientUpdate\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                <input type=\"hidden\" name=\"formKey\" value=\"\" />
                <fieldset form=\"clientUpdate\" name=\"shippingInfo\">
                  <legend>Shipping Information</legend>
                  <table class=\"centerDiv\">
                    <thead>
                      <tr>
                        <td colspan=\"4\">&nbsp;
                        </td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <label for=\"clientID\">Client ID</label>:  {$_SESSION['ClientID']}
                          <input type=\"hidden\" name=\"clientID\" value=\"{$_SESSION['ClientID']}\" form=\"clientUpdate\" />
                        </td>
                        <td></td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"clientName\">Name</label>:  {$_SESSION['ClientName']}
                          <input type=\"hidden\" name=\"ClientName\" value=\"{$_SESSION['ClientName']}\" form=\"clientUpdate\" />
                        </td>
                        <td>
                          <label for=\"department\">Department</label>:  {$_SESSION['Department']}
                          <input type=\"hidden\" name=\"Department\" value=\"{$_SESSION['Department']}\" form=\"clientUpdate\" />
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"shippingAddress1\">Address 1</label>:
                          <input type=\"text\" name=\"ShippingAddress1\" required placeholder=\"1234 Main St.\" value=\"{$_SESSION['ShippingAddress1']}\" form=\"clientUpdate\" /><span class=\"error\">*</span>
                        </td>
                        <td>
                          <label for=\"shippingAddress2\">Address 2</label>:
                          <input type=\"text\" name=\"ShippingAddress2\" required placeholder=\"City, State ZIP\" value=\"{$_SESSION['ShippingAddress2']}\" form=\"clientUpdate\" /><span class=\"error\">*</span>
                        </td>
                      </tr>
                      <tr class=\"{$hideCountry}\">
                        <td></td>
                        <td>
                          <label for=\"shippingCountry\">Shipping Country:</label>
                          <input list=\"countries\" name=\"ShippingCountry\" class=\"shippingCountry\" value=\"{$_SESSION['ShippingCountry']}\" {$requireCountry} form=\"clientUpdate\" /><span class=\"error\">*</span>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"telephone\">Telephone</label>:
                          <input type=\"tel\" name=\"Telephone\" value=\"{$_SESSION['Telephone']}\" placeholder=\"555-321-1234x567\" form=\"clientUpdate\" />
                        </td>
                        <td>
                          <label for=\"emailAddress\">Email Address</label>:
                          <input type=\"email\" name=\"EmailAddress\" value=\"{$_SESSION['EmailAddress']}\" form=\"clientUpdate\" />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </fieldset>
                <fieldset form=\"clientUpdate\" name=\"billingInfo\">
                  <legend>Billing Information</legend>
                  <table class=\"centerDiv\">
                    <tr>
                      <td>
                        <label for=\"same\">Same As Shipping</label>:
                        <input type=\"hidden\" name=\"same\" value=\"0\" form=\"clientUpdate\" />
  	                    <input type=\"checkbox\" name=\"same\" id=\"same\" value=\"1\" {$sameChecked} form=\"clientUpdate\" />
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"billingName\">Name</label>:
                        <input type=\"text\" name=\"BillingName\" value=\"{$billingName}\" form=\"clientUpdate\" {$sameDisabled} /><span class=\"error {$hideClass}\">*</span>
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"billingAddress1\">Address 1</label>:
                        <input type=\"text\" name=\"BillingAddress1\" placeholder=\"1234 Main St.\" value=\"{$billingAddress1}\" form=\"clientUpdate\" {$sameDisabled} /><span class=\"error {$hideClass}\">*</span></td>
                      <td>
                        <label for=\"billingAddress2\">Address 2</label>:
                        <input type=\"text\" name=\"BillingAddress2\" placeholder=\"City, State ZIP\" value=\"{$billingAddress2}\" form=\"clientUpdate\" {$sameDisabled} /><span class=\"error {$hideClass}\">*</span>
                      </td>
                    </tr>
                      <tr class=\"{$hideCountry}\">
                        <td></td>
                        <td>
                          <lable for=\"billingCountry\">Billing Country:</label>
                          <input list=\"countries\" name=\"BillingCountry\" class=\"billingCountry\" value=\"{$billingCountry}\" {$requireCountry2} form=\"clientUpdate\" /><span class=\"error . {$hideClass}\">*</span>
                        </td>
                      </tr>
                    <tr>
                      <td>
                        <label for=\"attention\">Attention</label>:
                        <input type=\"text\" name=\"Attention\" value=\"{$_SESSION['Attention']}\" form=\"clientUpdate\" />
                      </td>
                      <td>
                        <label for=\"credit\">Credit</label>:
                        <span class=\"currencySymbol\">{$_SESSION['config']['CurrencySymbol']}</span>{$this->getCredit()}
                      </td>
                    </tr>
                  </table>
                </fieldset>
                <table>
                  <tr>
                    <td colspan=\"2\">
                      <label for=\"update\">UPDATE</label>:
                      <input type=\"hidden\" name=\"update\" value=\"0\" form=\"clientUpdate\" />
                      <input type=\"checkbox\" name=\"update\" id=\"enableInfoUpdate\" value=\"1\" form=\"clientUpdate\" />
                    </td>
                    <td class=\"pullLeft\"><button type=\"submit\" class=\"submitInfoUpdate\" form=\"clientUpdate\" disabled>Submit</button></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                  <tr>
                    <td><span class=\"error\">* Required</span></td>
                    <td colspan=\"5\"></td>
                  </tr>
                  <tr>
                    <td colspan=\"6\">If greater changes are required than are permitted here please contact us.</td>
                  </tr>
                  <tr>
                    <td colspan=\"6\">By phone at {$_SESSION['config']['Telephone']}</td>
                  </tr>
                  <tr>
                    <td colspan=\"6\">Or via email at <a style=\"color:green\" href=\"mailto:{$_SESSION['config']['EmailAddress']}?subject=Update Contact Information\">{$_SESSION['config']['EmailAddress']}</a></td>
                  </tr>
                </table>
	            </form>
            </div>";
    }
  }
