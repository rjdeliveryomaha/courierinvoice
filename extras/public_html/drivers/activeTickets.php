<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;
  // Include functions
  require_once '../../includes/user_functions.php';

  if (!is_sec_session_started()) sec_session_start();

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\Ticket;

  try {
    $ticket = new Ticket($config, $_POST);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }
  $ticket->updateProperty('ticketEditor', TRUE);
  try {
    $val = $ticket->ticketsToDispatch();
  } catch(Exception $e) {
    $val = "<span data-value=\"error\">{$e->getMessage()}</span>";
  }
  echo $val;
  return FALSE;