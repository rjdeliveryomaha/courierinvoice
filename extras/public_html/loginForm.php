<?php
  require_once '../includes/APIToolsConfig.php';
  require_once '../vendor/autoload.php';
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;
?>
<form id="loginForm" class="fright" style="display:none;">
  <input type="hidden" name="formKey" id="formKey" value="<?php echo SecureSessionHandler::outputKey(); ?>" />
  <input type="hidden" name="noSession" id="noSession" value="1" />
  <input type="hidden" name="mobile" id="mobile" value="0" />
  <table>
    <tfoot>
      <!-- <tr>
        <td colspan="3">Service Temporarily Unavailable</td>
      </tr> -->
      <tr>
        <td colspan="3">*Located in the upper right hand area of your latest invoice.</td>
      </tr>
      <tr>
        <td id="message" colspan="3"></td>
      </tr>
    </tfoot>
    <tbody>
      <tr>
        <td><input type="text" name="clientID" id="clientID" autocomplete="username" placeholder="ID Number*" /></td>
        <td><input type="password" name="upw" id="upw" autocomplete="current-password" placeholder="password" /></td>
        <td><button type="submit" id="login">Login</button></td>
      </tr>
    </tbody>
  </table>
</form>
