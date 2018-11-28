<?php
  header("Cache-Control: max-age=2592000"); //30days (60sec * 60min * 24hours * 30days);
  // set headers to NOT cache a page
  // header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
  // header("Pragma: no-cache"); //HTTP 1.0
  // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
  // Include functions
  require_once '../../includes/user_functions.php';

  if (!is_sec_session_started()) sec_session_start();

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="manifest" href="/manifest.json">
  <title>R.J. Delivery</title>
  <link rel="stylesheet" type="text/css" href="../style/main.SIP.css">
  <link rel="stylesheet" type="text/css" href="../style/route.SIP.css">
  <link rel="stylesheet" type="text/css" href="../style/app_style.css">
  <style>
  </style>
</head>
<body>
<?php
  try {
    $functions = new CommonFunctions($config, array());
  } catch(Exception $e) {
    echo "<p class=\"center\">{$e->getMessage()}</p>";
    header('refresh:5;url=../mobileLogin');
    exit;
  }
  echo "input type=\"hidden\" name=\"formKey\" class=\"formKey\" id=\"formKey\" value=\"{$functions->outputKey()}\" />";
  try {
    $timezone = new dateTimeZone($_SESSION['config']['TimeZone']);
  } catch (Exception $e) {
    echo "<p class=\"center\">Session Value Missing: {$e->getMessage()}</p>";
    header("refresh:5;url=../mobileLogin");
    exit;
  }
  try {
    $date = new dateTime("", $timezone);
  } catch (Exception $e) {
    echo "<p class=\"center\">Session Value Missing: {$e->getMessage()}</p>";
    header("refresh:5;url=../mobileLogin");
    exit;
  }
  $initialTitle = ($_SESSION['ulevel'] === "driver") ? '<button type="button" id="refreshRoute">Route</button>' : '<button type="button" id="refreshDispatch">Dispatch</button><span class="medium dispatchCount"></span>';
  $mobileMarker = (isset($_SESSION['mobile']) && $_SESSION['mobile'] === TRUE) ? 1 : 0;
?>
  <div id="confirmLogin" class="hide">
    <form id="login" action="../login.php" method="post">
      <input type="hidden" name="mobile" id="mobile" value="<?php echo $mobileMarker; ?>" />
      <input type="hidden" name="function" id="function" value="" />
      <table>
        <thead>
          <tr>
            <th colspan="2" class="center">Please confirm credentials.</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <td colspan="2" id="confirmMessage"></td>
          </tr>
        </tfoot>
        <tbody>
          <tr>
            <td class="pullRight bold"><label for="uid">Login ID:</label></td>
            <td class="pullLeft"><input type="text" name="uid" id="uid" /></td>
          </tr>
          <tr>
            <td class="pullRight bold"><label for="upw">Password:</label></td>
            <td class="pullLeft"><input type="password" name="upw" id="upw" /></td>
          </tr>
          <tr>
            <td><button type="submit" id="confirm" form="login">Login</button></td>
            <td><button type="button" id="cancel">Cancel</button></td>
          </tr>
        </tbody>
      </table>
    </form>
  </div>
  <script>let coords1="",address1="",coords2="",address2="",center="";</script>
  <div id="appContainer" class="app app__layout">
    <header>
      <span class="header__icon">
        <svg class="menu__icon no--select" width="24px" height="24px" viewBox="0 0 48 48" fill="#fff">
          <path d="M6 36h36v-4H6v4zm0-10h36v-4H6v4zm0-14v4h36v-4H6z"></path>
        </svg>
        <span class="alert"></span><span id="newUpdate" class="medium hide">New</span>
      </span>
      <span class="header__title no--select"><span class="pageTitle medium"><?php echo $initialTitle; ?></span><span style="position:fixed;right:2%" class="medium"><?php echo $date->format("D d M Y"); ?></span></span>
    </header>
    <div class="menu">
      <?php echo $functions->createNavMenu(); ?>
    </div>
    <div class="menu__overlay"></div>
    <!-- Toast msg's  -->
    <div class="toast__container"></div>
    <div id="slider" class="swipe">
      <?php echo $functions->createAppLayout(); ?>
    </div>
  </div>
  <script>
    var myInfo = ["<?php echo $_SESSION['config']['ClientName']; ?>","<?php echo $_SESSION['config']['EmailAddress']; ?>","<?php echo $_SESSION['config']['Telephone']; ?>"]
  </script>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/jquery-3.3.1.min.js"><\/script>')</script>
  <script src="../app_js/jQuery.ajaxRetry.min.js"></script>
  <script src="../app_js/ajaxTemplate.min.js"></script>
  <script src="../app_js/pushMessaging.js"></script>
  <script src="../app_js/app.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
</body>
</html>
