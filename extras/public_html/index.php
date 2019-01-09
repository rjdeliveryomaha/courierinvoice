<?php
  header("Cache-Control: max-age=2592000"); //30days (60sec * 60min * 24hours * 30days);
  // set headers to NOT cache a page
  // header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
  // header("Pragma: no-cache"); //HTTP 1.0
  // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

  require_once '../includes/user_functions.php';
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
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
	
  <title>HOME | Company Name</title>
  
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
  include_once './loginForm.php';
?>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script>window.jQuery || document.write('<script src="./app_js/jquery-3.3.1.min.js"><\/script>')</script>
<script src="./js/user_scripts.js"></script>
</body>
</html>
