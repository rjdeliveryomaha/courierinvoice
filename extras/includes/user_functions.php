<?php
function is_sec_session_started() {
  if ( php_sapi_name() !== 'cli' ) {
    if ( version_compare(phpversion(), '5.4.0', '>=') ) {
      // http://php.net/manual/en/function.session-status.php#116634
      // return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
      return session_status() === 2 ? TRUE : FALSE;
    } else {
      return session_id() === '' ? FALSE : TRUE;
    }
  }
  return FALSE;
}

function sec_session_start() {
  $lifetime = 12 * 60 * 60;
  $path = '/';
  $session_name = 'ChangeMe';
  $domain = '.your.domain.com';
  // Are we https?
  $secure = 1;
  // Prevent JavaScript access to session ID
  $httponly = TRUE;
  //Force sessions to only use cookies
  if (ini_set('session.use_only_cookies', 1) === FALSE) {
    header('Location:  ../error.php?err=Could not initiate a safe session (ini_set)');
    exit;
  }
  //Set the sesion name
  session_name($session_name);
  //Set session parameters
  session_set_cookie_params(
    $lifetime,
    $path,
    $domain,
    $secure,
    $httponly
  );
  session_start();
  session_regenerate_id();
}

function managePushSubscription() {
  $validActions = ['add','remove'];
  if (!isset($_POST['action']) || !in_array($_POST['action'], $validActions)) {
    return 'Invalid Action';
  }
  // variables set by $_POST
  $subscription = test_input($_POST['subscription']);
  // variables set by $_SESSION
  $ulevel;
  $userID;
  // push notification user levels:
  //    1: Client
  //    2: Driver
  //    3: Driver with dispatch access
  //    4: Dispatcher
  if (is_numeric($_SESSION['ulevel'])) {
    $ulevel = 1;
    $userID = $_SESSION['ClientID'];
  } elseif ($_SESSION['ulevel'] === 'driver') {
    if ($_SESSION['CanDispatch'] === 2) {
      $ulevel = 3;
    } else {
      $ulevel = 2;
    }
    $userID = $_SESSION['DriverID'];
  } elseif ($_SESSION['ulevel'] === 'dispatch') {
    $ulevel = 4;
    $userID = $_SESSION['DispatchID'];
  } else {
    return 'Invalid session value when setting user level';
  }
  // variables set by query
  $sql;
  $dataOut;
  $dataIn;
  // Create connection
  $conn = new mysqli('localhost', 'user_name', 'password', 'database');
  //check connection
  if ($conn->connect_error) {
    return "Connection failed: {$conn->connect_error}";
	}
  //set character set
  $conn->set_charset('utf8mb4');
  // Both add and remove start from this query
  $sql = "SELECT endpoints FROM pushClients WHERE userID = '$userID' AND ulevel = '$ulevel'";
  $result = $conn->query($sql);
  if ($conn->error != NULL) {
    return "Server Error {$conn->errno} Line " . __line__ . ": {$conn->error}";
  }
  switch($_POST['action']) {
    case 'remove':
      if ($result->num_rows === 0) {
        return 'no subscription on file';
      }
      $dataOut = [];
      while ($row = $result->fetch_assoc()) {
        // subscriptionID should always be a json encoded string
        $dataOut = json_decode($row['endpoints']);
      }
      if (count($dataOut) === 1) {
        $sql = "DELETE FROM pushClients WHERE userID = '$userID' AND ulevel = '$ulevel'";
        if (!$conn->query($sql)) {
          return "Server Error {$conn->errno} Line " . __line__ . ": {$conn->error}";
        } else {
          return 'subscription removed';
        }
      } else {
        foreach ($dataOut as $test) {
          if (after_last('/', json_decode(html_entity_decode($test))->endpoint) !== $subscription) {
            $dataIn[] = $test;
          }
        }
        $temp = json_encode($dataIn);
        // update user level here as well to keep it current with any changes made
        $sql = "UPDATE pushClients SET endpoints = '$temp', ulevel = '$ulevel' WHERE userID = '$userID'";
        $result = $conn->query($sql);
        if ($conn->error != NULL) {
          return "Server Error {$conn->errno} Line " . __line__ . ": {$conn->error}";
        }
        return 'subscription removed';
      }
    break;
    case 'add':
      if ($result->num_rows === 0) {
        // firebase creates it's own subscription IDs so don't deal with subscription objects
        $dataIn[] = stripslashes($subscription);
        $temp = json_encode($dataIn);
        $sql = "INSERT INTO pushClients (endpoints, ulevel, userID) VALUES ('$temp', '$ulevel', '$userID')";
        if (!$conn->query($sql)) {
          return "Server Error {$conn->errno} Line " . __line__ . ": {$conn->error}";
        }
        return 'subscription added';
    } else {
      while ($row = $result->fetch_assoc()) {
        $dataIn = json_decode($row['endpoints']);
        $testObj = json_decode(html_entity_decode(stripslashes($subscription)));
        for($i = 0; $i < count($dataIn); $i++) {
          $tempIn = json_decode(html_entity_decode($dataIn[$i]));
          if ($tempIn->endpoint === $testObj->endpoint) return 'subscription exists';
        }
        $dataIn[] = stripslashes($subscription);
        $temp = json_encode($dataIn);
        // update user level here as well to keep it current with any changes made
        $sql = "UPDATE pushClients SET endpoints = '$temp', ulevel = '$ulevel' WHERE userID = '$userID'";
        $result = $conn->query($sql);
        if ($conn->error != NULL) {
          return "Server Error {$conn->errno} Line " . __line__ . ": {$conn->error}";
        }
        return 'subscription added';
      }
    }
    break;
  }
}
