<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;
  // Include functions
  require_once "../../includes/user_functions.php";
  
  if (!is_sec_session_started()) sec_session_start();
  
  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';
  use rjdeliveryomaha\courierinvoice\Client;
  
  $client = new Client($config, $_POST);
  if ($client === false) {
    return $client->getError();
  }
  return $client->changePassword();
  