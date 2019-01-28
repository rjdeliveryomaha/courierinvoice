<?php
  require_once '../includes/APIToolsConfig.php';
  require_once '../vendor/autoload.php';
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;
?>
<form id="loginForm" class="fright" style="display:none;">
  <input type="hidden" name="formKey" id="formKey" value="<?php echo SecureSessionHandler::outputKey(); ?>" />
  <input type="hidden" class="mobile" value="0" />
  <table>
    <tfoot>
      <!-- <tr>
        <td colspan="3">Service Temporarily Unavailable</td>
      </tr> -->
      <tr>
        <td colspan="3">*Located in the upper right hand area of your latest invoice.</td>
      </tr>
      <tr>
        <td class="message" colspan="3"></td>
      </tr>
    </tfoot>
    <tbody>
      <tr>
        <td><input type="text" class="clientID" autocomplete="off" placeholder="ID Number*" /></td>
        <td><input type="password" class="upw" placeholder="password" /></td>
        <td><button type="submit" class="login">Login</button></td>
      </tr>
    </tbody>
  </table>
</form>
