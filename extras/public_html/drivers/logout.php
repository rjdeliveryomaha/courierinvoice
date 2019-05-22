<?php
  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    SecureSessionHandler::start_session($config);
  } catch (Exception $e) {
    echo $e->getMessage();
    exit;
  }
  //Set a flag to indicate where to redrict after the session is destroyed
  $mobile =  (isset($_POST['mobile']) && $_POST['mobile'] === 1);
  // Destroy session
  SecureSessionHandler::destroySession();

  if ($mobile) {
    exit(header('Location: ../mobileLogin'));
  }
  else {
    exit(header('Location: /'));
  }
