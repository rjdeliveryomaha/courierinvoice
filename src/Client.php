<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

  class Client extends CommonFunctions
  {
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
    protected $StandardVAT;
    protected $ReducedVAT;
    protected $Organization;
    protected $Same;
    protected $currentPw;
    protected $newPw1;
    protected $newPw2;
    protected $flag;
    protected $org_id;
    // Only the org flag doesn't need a second password test so set the test flag to true
    private $secondTest = true;
    private $resourceName;
    private $primaryKey;
    private $testAgainst;
    private $query;
    private $queryData;
    private $result;
    private $newPass;
    private $returnable = [
      'client_index', 'RepeatClient', 'ClientID', 'ClientName', 'Department', 'ShippingAddress1', 'ShippingAddress2',
      'ShippingCountry', 'BillingName', 'BillingAddress1', 'BillingAddress2', 'BillingCountry', 'Telephone',
      'EmailAddress', 'Attention', 'ContractDiscount', 'GeneralDiscount', 'Organization'
    ];
    private $updateValues = [ 'ShippingAddress1', 'ShippingAddress2', 'ShippingCountry', 'BillingName',
      'BillingAddress1', 'BillingAddress2', 'BillingCountry', 'Telephone', 'EmailAddress', 'Attention', 'Same'
    ];
    private $clientInfo;
    private $userType;

    public function __construct($options, $data=[])
    {
      try {
        parent::__construct($options, $data);
      } catch (\Exception $e) {
        throw $e;
      }
      foreach ($data as $key => $value) {
        if (property_exists($this, $key)) {
         $this->$key = $value;
        }
      }
    }

    public function getAllClientInfo()
    {
      $returnData = [];
      foreach ($this as $key => $value) {
        if (in_array($key, $this->returnable)) $returnData[$key] = $value;
      }
      return $returnData;
    }

    public function changePassword()
    {
      $client = self::test_int($_SESSION['ClientID']);
      $this->queryData['method'] = 'GET';
      if ($this->flag === 'admin') {
        $this->resourceName = 'AdminPassword';
        $this->testAgainst = 'Password';
        $this->primaryKey = 'client_index';
        $this->queryData['endPoint'] = 'clients';
        $this->queryData['queryParams']['include'] = ['AdminPassword', 'Password', 'client_index'];
        $this->queryData['queryParams']['filter'] = [
          ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$client],
          ['Resource'=>'RepeatClient', 'Filter'=>'eq','Value'=>$this->RepeatClient]
        ];
      } elseif ($this->flag === 'daily') {
        $this->resourceName = 'Password';
        $this->testAgainst = 'AdminPassword';
        $this->primaryKey = 'client_index';
        $this->queryData['endPoint'] = 'clients';
        $this->queryData['queryParams']['include'] = ['AdminPassword', 'Password', 'client_index'];
        $this->queryData['queryParams']['filter'] = [
          ['Resource'=>'ClientID', 'Filter'=>'eq', 'Value'=>$client],
          ['Resource'=>'RepeatClient', 'Filter'=>'eq','Value'=>$this->RepeatClient]
        ];
      } elseif ($this->flag === 'org') {
        $this->resourceName = 'Password';
        $this->secondTest = false;
        $this->primaryKey = 'o_client_index';
        $this->queryData['endPoint'] = 'o_clients';
        $this->queryData['queryParams']['include'] = ['Password', 'o_client_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'ID', 'Filter'=>'eq', 'Value'=>$client] ];
      } elseif ($this->flag === 'driver') {
        $this->resourceName = 'Password';
        $this->secondTest = false;
        $this->primaryKey = 'driver_index';
        $this->queryData['endPoint'] = 'drivers';
        $this->queryData['queryParams']['include'] = ['Password', 'driver_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'DriverID', 'Filter'=>'eq', 'Value'=>$client] ];
      } elseif ($this->flag === 'dispatch') {
        $this->resourceName = 'Password';
        $this->secondTest = false;
        $this->primaryKey = 'dispatch_index';
        $this->queryData['endPoint'] = 'dispatchers';
        $this->queryData['queryParams']['include'] = ['Password', 'dispatch_index'];
        $this->queryData['queryParams']['filter'] = [ ['Resource'=>'DispatchID', 'Filter'=>'eq', 'Value'=>$client] ];
      } else {
        return '<span class="result error">Invalid Flag</span>';
      }
      $this->query = self::createQuery($this->queryData);
      if ($this->query === false) {
        echo '<p class="result">', $this->error, '</p>';
        return false;
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === false) {
        echo '<p class="result">', $this->error, '</p>';
        return false;
      }
      // Now that we have the data to test against start building the update query
      // queryData['endPoint'] does not change
      $this->queryData['method'] = 'PUT';
      $this->queryData['primaryKey'] = $this->result[0][$this->primaryKey];
      $this->queryData['queryParams'] = [];
      $hash = $this->result[0][$this->resourceName];
      $filter = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^\&*\)\(\{\}\[\]\-_.=+\?\:;,])(?=.{8,}).*$/';
      if (!(preg_match($filter, $this->newPw1))) {
        // Make sure the new password meets criteria
        echo '<p class="result center"><span class="error">Password does not meet criteria.</span></p>';
        return false;
      } elseif($this->currentPw === $this->newPw1) {
        // Make sure that the password is actually being changed
        echo '<p class="result center"><span class="error">Invalid entry</span>: The password should be changed.</p>';
        return false;
      } elseif ($this->newPw1 !== $this->newPw2) {
        // Make sure the same value was entered twice
        echo '<p class="result center"><span class="error">Invalid entry</span>: The values entered don\'t match.</p>';
        return false;
      }
      // Test old password
      if (!password_verify($this->currentPw, $hash)) {
        echo '
        <p class="result center">
          <span class="error">Invalid Entry</span>: The password entered doesn\'t match the password on file.
        </p>';
        return false;
      } else {
        if ($this->secondTest) {
          $hash2 = $this->result[0][$this->testAgainst];
          // Compare new admin password to current daily password
          if (password_verify($this->newPw1, $hash2)) {
            echo '
            <p class="result center">
              <span class="error">Invalid Entry.</span> Admin password and daily user password must not match.
            </p>';
            return false;
          }
        }
        $options = [
         'cost' => 12,
        ];
        $this->newPass = password_hash($this->newPw1, PASSWORD_DEFAULT, $options);
        $this->queryData['payload'] = [$this->resourceName => $this->newPass];
        $this->query = self::createQuery($this->queryData);
        if ($this->query === false) {
          echo '<p class="result">', $this->error, ' No chages were made to the account.</p>';
          return false;
        }
        $this->result = self::callQuery($this->query);
        if ($this->result === false) {
          echo '<p class="result">', $this->error, '</p>';
          return false;
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
        echo '<p class="result center big">Password Updated.</p>';
        return true;
      }
    }

    public function updateInfo()
    {
      if (self::test_bool($this->Same) === true) {
        $this->BillingName = $this->BillingAddress1 = $this->BillingAddress2 = $this->BillingCountry = null;
      }
      // clear the query data of values used i fetchClientIndex
      $this->queryData = [];
      $this->queryData['method'] = 'PUT';
      $this->queryData['primaryKey'] = $this->client_index;
      $this->queryData['endPoint'] = 'clients';
      /* $this->error = self::safe_print([ $this->updateValues, $this->postKeys ]);
      self::writeLoop(); */
      for ($i = 0; $i < count($this->updateValues); $i++) {
        if (in_array($this->updateValues[$i], $this->postKeys)) {
          $this->queryData['payload'][$this->updateValues[$i]] =
          (strpos($this->updateValues[$i], 'Country') !== false) ?
          self::countryFromAbbr($this->{$this->updateValues[$i]}) :
          self::test_input($this->{$this->updateValues[$i]});
        }
      }
      // Build the update query
      $this->query = self::createQuery($this->queryData);
      if ($this->query === false) {
        echo '<p class="result">', $this->error, '</p>';
        return false;
      }
      $this->result = self::callQuery($this->query);
      if ($this->result === false) {
        echo '<p class="result">', $this->error, '</p>';
        return false;
      }
      // update the session
      for ($i = 0; $i < count($this->updateValues); $i++) {
        $_SESSION[$this->updateValues[$i]] = $this->{$this->updateValues[$i]};
      }
      echo '<p class="result">Update Successful</p>';
      return false;
    }

    private function passwordForm()
    {
      if ($this->userType === 'admin') {
        $showPWwarning = ($this->pwWarning === 2 || $this->pwWarning === 3) ? '' : 'hide';
        $formID = 'apwUpdate';
        $flag = 'admin';
        $type = 'client';
        $id = $_SESSION['ClientID'];
        $repeatClient = '<input type="hidden" name="repeatClient" value="' . (int)$this->RepeatClient . '" />';
      } elseif ($this->userType === 'daily') {
        $showPWwarning = ($this->pwWarning === 1 || $this->pwWarning === 3) ? '' : 'hide';
        $formID = 'pwUpdate';
        $flag = 'daily';
        $type = 'client';
        $id = $_SESSION['ClientID'];
        $repeatClient = '<input type="hidden" name="repeatClient" value="' . (int)$this->RepeatClient . '" />';
      } elseif ($this->userType === 'org') {
        $showPWwarning = ($this->pwWarning === 4) ? '' : 'hide';
        $formID = 'opwUpdate';
        $flag = 'org';
        $type = 'client';
        $id = $_SESSION['ClientID'];
        $repeatClient = '';
      } elseif ($this->userType === 'driver') {
        $showPWwarning = 'hide';
        $formID = 'driverPwUpdate';
        $flag = 'driver';
        $type = 'driver';
        $id = $_SESSION['DriverID'];
        $repeatClient = '';
      } elseif ($this->userType === 'dispatch') {
        $showPWwarning = 'hide';
        $formID = 'dispatchPwUpdate';
        $flag = 'dispatch';
        $type = 'dispatch';
        $id = $_SESSION['DispatchID'];
        $repeatClient = '';
      }
      return "
            <div class=\"PWcontainer\">
              <div class=\"PWform\">
                <form id=\"$formID\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
                  <input type=\"hidden\" name=\"$type\" class=\"$type\" value=\"$id\" form=\"$formID\" />
                  $repeatClient
                  <input type=\"hidden\" name=\"flag\" class=\"flag\" value=\"$flag\" form=\"$formID\" />
                  <table>
                    <tr>
                      <td><label for=\"currentPw\">Current Password</label>:</td>
                      <td><input type=\"password\" name=\"currentPw\" class=\"currentPw\" autocomplete=\"current-password\"  form=\"$formID\" /></td>
                    </tr>
                    <tr>
                      <td><label for=\"newPw1\">New Password</label>:</td>
                      <td><input type=\"password\" name=\"newPw1\" class=\"newPw1\" autocomplete=\"new-password\" form=\"$formID\" />
                    </tr>
                    <tr>
                      <td><label for=\"newPw2\">Confirm Password</label>:</td>
                      <td><input type=\"password\" name=\"newPw2\" class=\"newPw2\" autocomplete=\"new-password\" form=\"$formID\" /></td>
                    </tr>
                    <tr>
                      <td>
                        <button type=\"submit\" class=\"PWsubmit\" form=\"$formID\">Submit</button>
                        <button type=\"reset\" class=\"clearPWform\" form=\"$formID\">Clear</button>
                      </td>
                      <td>
                        <label for=\"showText\">Show Text:</label>
                        <input type=\"checkbox\" class=\"showText\" name=\"showText\"  form=\"$formID\" />
                      </td>
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
                  <li class=\"defaultWarning $showPWwarning\">Password should be changed from default.</li>
                  <li>Passwords must be at least 8 characters long.</li>
                  <li>Passwords must contain:</li>
                    <ul>
                      <li>At least one upper case letter. <span style=\"background:black;color:#90EE90;\"> A..Z </span></li>
                      <li>At least one lower case letter. <span style=\"background:black;color:#90EE90;\">a..z</span></li>
                      <li>At least one number. <span style=\"background:black;color:#90EE90;\">0..9</span></li>
                      <li>At least one special character.
                        <span style=\"background:black;color:#90EE90;\"> ! @ # $ % ^ & * ( ) { } [ ] - _ . : ; , = + </span>
                      </li>
                    </ul>
                  <li>The \"New Password\" and \"Confirm Password\" fields must match.</li>
                  <li>daily user password must be different from admin password.</li>
                </ul>
              </div>
            </div>";
    }

    public function adminPasswordForm()
    {
      $this->userType = 'admin';
      return $this->passwordForm();
    }

    public function dailyPasswordForm()
    {
      $this->userType = 'daily';
      return $this->passwordForm();
    }

    public function orgPasswordForm()
    {
      $this->userType = 'org';
      return $this->passwordForm();
    }

    public function driverPasswordForm()
    {
      $this->userType = 'driver';
      return $this->passwordForm();
    }

    public function dispatchPasswordForm()
    {
      $this->userType = 'dispatch';
      return $this->passwordForm();
    }

    public function updateInfoForm()
    {
      $requireCountry = (self::test_bool($_SESSION['config']['InternationalAddressing']) === true) ?
        'required' : '';
      if (self::test_bool($this->Same) === true) {
        $sameChecked = 'checked';
        $sameDisabled = 'disabled';
        $hideClass = 'hide';
      } else {
        $sameChecked = $hideClass = '';
        $sameDisabled = 'required';
      }
      $hideCountry = (self::test_bool($this->config['InternationalAddressing']) === true) ? '' : 'hide';
      $hideVAT = (self::test_bool($this->config['ApplyVAT']) === true) ? '' : 'hide';
      $clientMarker = (self::test_bool($this->RepeatClient) === true) ?
        $_SESSION['ClientID'] : "t{$_SESSION['ClientID']}";
      return "
            <div id=\"clientUpdateForm\">
              <form id=\"clientUpdate\" action=\"{$this->esc_url($_SERVER['REQUEST_URI'])}\" method=\"post\">
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
                          <input type=\"hidden\" name=\"ClientID\" value=\"{$_SESSION['ClientID']}\" form=\"clientUpdate\" />
                        </td>
                        <td></td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"clientName\">Name</label>:  {$this->ClientName}
                          <input type=\"hidden\" name=\"ClientName\" value=\"{$this->ClientName}\" form=\"clientUpdate\" />
                        </td>
                        <td>
                          <label for=\"department\">Department</label>:  {$this->Department}
                          <input type=\"hidden\" name=\"Department\" value=\"{$this->Department}\" form=\"clientUpdate\" />
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"shippingAddress1\">Address 1</label>:
                          <input type=\"text\" name=\"ShippingAddress1\" required placeholder=\"1234 Main St.\" value=\"{$this->ShippingAddress1}\" form=\"clientUpdate\" /><span class=\"error\">*</span>
                        </td>
                        <td>
                          <label for=\"shippingAddress2\">Address 2</label>:
                          <input type=\"text\" name=\"ShippingAddress2\" required placeholder=\"City, State ZIP\" value=\"{$this->ShippingAddress2}\" form=\"clientUpdate\" /><span class=\"error\">*</span>
                        </td>
                      </tr>
                      <tr class=\"$hideCountry\">
                        <td></td>
                        <td>
                          <label for=\"shippingCountry\">Shipping Country:</label>
                          <input list=\"countries\" name=\"ShippingCountry\" class=\"shippingCountry\" value=\"{$this->ShippingCountry}\" $requireCountry form=\"clientUpdate\" /><span class=\"error\">*</span>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <label for=\"telephone\">Telephone</label>:
                          <input type=\"tel\" name=\"Telephone\" value=\"{$this->Telephone}\" placeholder=\"555-321-1234x567\" form=\"clientUpdate\" />
                        </td>
                        <td>
                          <label for=\"emailAddress\">Email Address</label>:
                          <input type=\"email\" name=\"EmailAddress\" value=\"{$this->EmailAddress}\" form=\"clientUpdate\" />
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
                        <label for=\"Same\">Same As Shipping</label>:
                        <input type=\"hidden\" name=\"Same\" value=\"0\" form=\"clientUpdate\" />
  	                    <input type=\"checkbox\" name=\"Same\" id=\"Same\" value=\"1\" $sameChecked form=\"clientUpdate\" />
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"billingName\">Name</label>:
                        <input type=\"text\" name=\"BillingName\" value=\"{$this->BillingName}\" form=\"clientUpdate\" $sameDisabled /><span class=\"error $hideClass\">*</span>
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td>
                        <label for=\"billingAddress1\">Address 1</label>:
                        <input type=\"text\" name=\"BillingAddress1\" placeholder=\"1234 Main St.\" value=\"{$this->BillingAddress1}\" form=\"clientUpdate\" $sameDisabled /><span class=\"error $hideClass\">*</span></td>
                      <td>
                        <label for=\"billingAddress2\">Address 2</label>:
                        <input type=\"text\" name=\"BillingAddress2\" placeholder=\"City, State ZIP\" value=\"{$this->BillingAddress2}\" form=\"clientUpdate\" $sameDisabled /><span class=\"error $hideClass\">*</span>
                      </td>
                    </tr>
                      <tr class=\"$hideCountry\">
                        <td></td>
                        <td>
                          <lable for=\"billingCountry\">Billing Country:</label>
                          <input list=\"countries\" name=\"BillingCountry\" class=\"billingCountry\" value=\"{$this->BillingCountry}\" $requireCountry form=\"clientUpdate\" /><span class=\"error $hideClass\">*</span>
                        </td>
                      </tr>
                    <tr>
                      <td>
                        <label for=\"attention\">Attention</label>:
                        <input type=\"text\" name=\"Attention\" value=\"{$this->Attention}\" form=\"clientUpdate\" />
                      </td>
                      <td>
                        <label for=\"credit\">Credit</label>:
                        <span class=\"currencySymbol\">{$_SESSION['config']['CurrencySymbol']}</span>{$this->getCredit()}
                      </td>
                    </tr>
                    <tr class=\"$hideVAT\">
                      <td>
                        <label for=\"standardVAT\">Standard</label>:
                        <input type=\"number\" name=\"standardVAT\" id=\"standardVAT\" min=\"0\" max=\"99.99\" step=\"0.01\" value=\"{$this->StandardVAT}\" form=\"clientUpdate\" />&#37;
                      </td>
                      <td>
                        <label for=\"reducedVAT\">Reduced</label>:
                        <input type=\"number\" name=\"reducedVAT\" id=\"reducedVAT\" min=\"0\" max=\"99.99\" step=\"0.01\" value=\"{$this->ReducedVAT}\" form=\"clientUpdate\" />&#37;
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
                    <td class=\"pullLeft\"><button type=\"submit\" id=\"submitInfoUpdate\" form=\"clientUpdate\" disabled>Submit</button></td>
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
              <p id=\"clientUpdateResult\" class=\"center\"></p>
            </div>";
    }
  }
