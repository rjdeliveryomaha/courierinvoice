<?php
function getLoginConnection() {
  // Create connection
  $conn = new mysqli('localhost', 'user_name', 'password', 'database');
  //check connection
  if ($conn->connect_error) {
    return "{$conn->connect_errno}: {$conn->connect_error}";
	}
  //set character set
  $conn->set_charset('utf8mb4');
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
  return $conn;
}
//Check for brute force attempt
function checkbrute($data) {
  // Create connection
  $conn = getLoginConnection();
  if (!is_object($conn)) {
    return $conn;
  }
  $timezone = new dateTimeZone('America/Chicago');
  $now = new dateTime('NOW', $timezone);
  $uname = $data['clientID'];
  if (isset($data['brute'])) {
    if ($stmt = $conn->prepare("INSERT INTO attempt user = ?  time = ?")) {
      $stmt->bind_param($uname, $now->format('Y-m-d H:i:s'));
      $stmt->execute();
    }
    return false;
  }
  //Count login attempts for past 2 hours
  $valid_attempts = $now->modify('-2 hour')->format('Y-m-d H:i:s');
  // Delete attempts older than two hours
  $sql = "DELETE FROM attempt WHERE time < '$valid_attempts'";
  $result = $conn->query($sql);
  if ($conn->error != NULL) {
    return true;
  }
  if ($stmt = $conn->prepare("SELECT id FROM attempt WHERE user = ? AND time > '$valid_attempts'")) {
    $stmt->bind_param('s', $uname);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 5;
  }
}
