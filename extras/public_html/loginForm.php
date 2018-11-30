<?php
  require_once '../includes/APIToolsConfig.php';
  require_once '../vendor/autoload.php';
  use rjdeliveryomaha\courierinvoice\CommonFunctions;
?>
<form id="loginForm" class="fright" style="display:none;">
  <input type="hidden" class="mobile" value="0" />
<?php
  try {
    $functions = new CommonFunctions($config, ['noSession'=>true]);
    $key = $functions->outputKey();
    $formKey = "<input type=\"hidden\" name=\"formKey\" class=\"formKey\" value=\"$key\" />";
    $disabled = '';
  } catch(Exception $e) {
    $key = $e->getMessage();
    $formKey = $key;
    $disabled = 'disabled';
  }
?>
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
        <td colspan="3"><?php echo $formKey; ?></td>
      </tr>
      <tr>
        <td><input type="text" class="clientID" autocomplete="off" placeholder="ID Number*" /></td>
        <td><input type="password" class="upw" placeholder="password" /></td>
        <td><button type="submit" class="login">Login</button></td>
      </tr>
    </tbody>
  </table>
</form>
