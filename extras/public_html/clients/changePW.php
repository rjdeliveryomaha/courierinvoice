<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\Client;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }

  try {
    $client = new Client($config, $_POST);
  } catch(Exception $e) {
    return $e->getMessage();
  }
  return $client->changePassword();
