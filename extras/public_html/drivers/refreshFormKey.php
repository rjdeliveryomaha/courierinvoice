<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    $val = SecureSessionHandler::newKey($config);
  } catch(Exception $e) {
    $val = $e->getMessage();
  }
  echo $val;
  return FALSE;
