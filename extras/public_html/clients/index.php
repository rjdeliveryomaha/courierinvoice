<?php
  header('Cache-Control: max-age=2592000'); //30days (60sec * 60min * 24hours * 30days);
  // set headers to NOT cache a page
  // header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
  // header("Pragma: no-cache"); //HTTP 1.0
  // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\CommonFunctions;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    SecureSessionHandler::start_session($config);
  } catch (Exception $e) {
    echo "<p class=\"center\">{$e->getMessage()}</p>";
    exit(header('refresh:5;url=logout'));
  }
  if (isset($_SESSION['error'])) exit(header('Location: logout'));

  try {
    $functions = new CommonFunctions($config, array());
  } catch(Exception $e) {
    echo "<p class=\"center\">{$e->getMessage()}</p>";
    exit(header('refresh:5;url=/'));
  }
  try {
    $timezone = new dateTimeZone($_SESSION['config']['TimeZone']);
  } catch (Exception $e) {
    exit(header('refresh:5;url=/'));
  }
  try {
    $date = new dateTime('', $timezone);
  } catch (Exception $e) {
    exit(header('refresh:5;url=/'));
  }
  if ($_SESSION['ulevel'] > 0) {
    $loginName = $_SESSION['ClientID'];
  } else {
    $loginName = $_SESSION['Login'];
  }
  $mobileMarker = (isset($_SESSION['mobile']) && $_SESSION['mobile'] === TRUE) ? 1 : 0;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <title>Clients</title>
  <?php echo $functions->injectCSS(); ?>
</head>
<body>
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
            <td class="pullRight bold"><label for="upw">Password:</label></td>
            <td class="pullLeft">
              <input type="password" name="upw" id="upw" autocomplete="current-password" />
              <input type="hidden" name="uid" id="uid" value="<?php echo $loginName; ?>" />
            </td>
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
  <div class="app app__layout">
    <header>
      <span class="header__icon">
        <svg class="menu__icon no--select" width="24px" height="24px" viewBox="0 0 48 48" fill="#fff">
          <path d="M6 36h36v-4H6v4zm0-10h36v-4H6v4zm0-14v4h36v-4H6z"></path>
        </svg>
        <span class="alert"></span><span id="newUpdate" class="medium hide">New</span>
      </span>
      <span class="header__title no--select"><span class="pageTitle medium"></span><span class="medium" style="position:fixed;right:2%"><?php echo $date->format("D d M Y"); ?></span></span>
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
</body>
</html>
