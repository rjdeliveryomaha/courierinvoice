<?php
  include_once '../includes/api_config.php';
  include_once '../vendor/autoload.php';
  include_once '../includes/user_functions.php';

  use rjdeliveryomaha\courierinvoice\LoginHandler;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  if ($_SERVER['REQUEST_METHOD'] !== "POST") return false;
  if (isset($_POST['brute'])) {
    $uname = $_POST['clientID'];
    // Create connection
    $conn = getLoginConnection();
    if (!is_object($conn)) {
      return true;
    }
    $sql = "INSERT INTO attempt (user) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $uname);
    if (!$stmt->execute()) {
      echo '<span data-value="break" class="error">Server Error ' . $stmt->errno . 'Line ' . __line__ . ':</span>' . $stmt->error;
      return FALSE;
    } else {
      return FALSE;
    }
  }

  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    echo $e->getMessage();
    return FALSE;
  }

  if (isset($_SESSION['brute']) && in_array($_POST['clientID'], json_decode($_SESSION['brute'], TRUE), TRUE)) {
    echo '<span data-value="break" class="error">Account locked.<br>Too many failed login attempts.</span><span class="hide">Line ' . __line__ . '</span>';
    return FALSE;
  }
  if (checkbrute($_POST) === TRUE) {
    if (isset($_SESSION['brute'])) {
      $bruteList = json_decode($_SESSION['brute'], TRUE);
      if (!in_array($uname, $bruteList, TRUE)) {
        $bruteList[] = $uname;
      }
      $brute = json_encode($bruteList);
      $_SESSION['brute'] = $brute;
    }
    else {
      $_SESSION['brute'] = json_encode(array($uname));
    }
    echo '<span data-value="break" class="error">Account locked.<br>Too many failed login attempts.</span><span class="hide">Line ' . __line__ . '</span>';
    return FALSE;
  }
  try {
    $handler = new LoginHandler($config, $_POST);
  } catch(Exception $e) {
    echo $e->getMessage();
    return FALSE;
  }
  try {
    $url = $handler->login();
  } catch(Exception $e) {
    echo $e->getMessage();
    return FALSE;
  }
  echo $url;
  return FALSE;
