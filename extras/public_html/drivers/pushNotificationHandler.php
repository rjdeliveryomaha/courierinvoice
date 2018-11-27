<?php
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    return FALSE;
  }
  // Include functions
  require_once "../../includes/user_functions.php";
  require_once "../../includes/formKey.class.php";
  if (!is_sec_session_started()) {
    sec_session_start();
  }
  $formKey = new formKey();
  if (!isset($_POST['formKey']) || !$formKey->validate()) {
    echo 'Session Error';
    return FALSE;
  }
  echo managePushSubscription();
  return FALSE;