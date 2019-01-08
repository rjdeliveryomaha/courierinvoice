<?php
  header("Cache-Control: max-age=2592000"); //30days (60sec * 60min * 24hours * 30days);
  // set headers to NOT cache a page
  // header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
  // header("Pragma: no-cache"); //HTTP 1.0
  // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
  
  require_once '../includes/APIToolsConfig.php';
  require_once '../vendor/autoload.php';
  
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;
  
  try {
    SecureSessionHandler::start_session($config);
  } catch (Exception $e) {
    echo $e->getMessage();
    exit;
  }
?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width">
  <link rel="stylesheet" type="text/css" href="/style/mobileLogin.css">
  <link rel="stylesheet" type="text/css" href="/style/app_style.css">
  <meta name="theme-color" content="#ffffff">
  <title>Mobile Login</title>
</head>
<body>
  <main>
    <div class="logoContainer">
      <a href="/">
        <img src="../images/logo/logo2.png" alt="Company Logo">
      </a>
    </div>
    <form class="wide" name="login" action="/" method="post">
      <table class="centerDiv" id="driverLoginForm">
        <tfoot>
          <tr>
           <td class="message"></td>
          </tr>
        </tfoot>
        <tbody>
          <tr>
            <td><label for="clientID">Login ID</label></td>
          </tr>
          <tr>
            <td><input type="text" class="clientID" id="clientID" autofocus="autofocus" /></td>
          </tr>
          <tr>
            <td><label for="upw">Password</label></td>
          </tr>
          <tr>
            <td><input type="password" class="upw" id="upw" /></td>
          </tr>
          <tr>
            <td>
              <input type="hidden" class="mobile" name="mobile" value="1" />
              <button type="submit" id="submit" class="login">Login</button>
            </td>
          </tr>
          <tr>
            <td>
              <button type="button" style="display:none;" id="btnAdd">Add To Home Screen</button>
            </td>
          </tr>
        </tbody>
      </table>
    </form>
  </main>
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/jquery-3.3.1.min.js"><\/script>')</script>
  <script src="./js/user_scripts.min.js"></script>
</body>
</html>
