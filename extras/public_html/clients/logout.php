<?php
  require_once "../../includes/user_functions.php";
  sec_session_start();
  //Set a flag to indicate where to redrict after the session is destroyed
  $mobile =  (isset($_POST['mobile']) && $_POST['mobile'] === '1');
  // Destroy session
  session_destroy();
  if ($mobile) {
    header("Location: ../mobileLogin");
  }
  else {
    header("Location: /");
  }
  exit;