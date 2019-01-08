<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }
  echo SecureSessionHandler::outputKey();
  return FALSE;
