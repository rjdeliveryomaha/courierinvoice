<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\Ticket;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }

  try {
    $ticket = new Ticket($config, $_POST);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }
  try {
    $val = $ticket->stepTicket();
  } catch(Exception $e) {
    $val = "<span data-value=\"error\">{$e->getMessage()}</span>";
  }
  echo $val;
  return FALSE;
