<?php
  include_once '../includes/APIToolsConfig.php';
  include_once '../vendor/autoload.php';
  include_once '../includes/sip_secSession.php';
  
  use rjdeliveryomaha\courierinvoice\LoginHandler;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;
  
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;
  if (isset($_POST['brute'])) {
    $uname = $_POST['clientID'];
    // Create connection
    $conn = getLoginConnection();
    if (!is_object($conn)) {
      echo "<span data-value=\"break\" class=\"error\">{$conn}</span>";
      return false;
    }
    $sql = 'INSERT INTO attempt (user) VALUES (?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $uname);
    if (!$stmt->execute()) {
      echo "<span data-value=\"break\" class=\"error\">Server Error {$stmt->errno}</span>: {$stmt->error}";
      return false;
    } else {
      return false;
    }
  }
  
  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    echo $e->getMessage();
    return false;
  }
  
  if (isset($_SESSION['brute']) && in_array($_POST['clientID'], json_decode($_SESSION['brute'], TRUE), TRUE)) {
    echo '<span data-value="break" class="error">Account locked.<br>Too many failed login attempts.</span>';
    return false;
  }
  $bruteCheck = checkbrute($_POST);
  if ($bruteCheck === TRUE) {
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
    echo '<span data-value="break" class="error">Account locked.<br>Too many failed login attempts.</span>';
    return false;
  } elseif (!is_bool($bruteCheck)) {
    echo $bruteCheck;
    return false;
  }
  try {
    $handler = new LoginHandler($config, $_POST);
  } catch(Exception $e) {
    echo $e->getMessage();
    return false;
  }
  try {
    $url = $handler->login();
  } catch(Exception $e) {
    echo $e->getMessage();
    return false;
  }
  echo $url;
  return false;
