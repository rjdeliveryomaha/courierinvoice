<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;
  if (!isset($_POST['functions'])) return FALSE;
  
  require_once '../../includes/APIToolsConfig.php';
  require_once '../../includes/user_functions.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\Ticket;
  use rjdeliveryomaha\courierinvoice\Route;
  use rjdeliveryomaha\courierinvoice\Invoice;
  use rjdeliveryomaha\courierinvoice\Client;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    echo $e->getMessage();
    return FALSE;
  }
  $returnData = [];
  $functions = [];
  if (is_array($_POST['functions'])) {
    for ($i = 0; $i < count($_POST['functions']); $i++) {
      $functions[] = test_input($_POST['functions'][$i]);
    }
  } else {
    $functions[] = test_input($_POST['functions']);
  }

  try {
    $ticket = new Ticket($config, $_POST);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }

  try {
    $route = new Route($config, $_POST);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }

  try {
    $invoice = new Invoice($config, $_POST);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }

  try {
    $client = new Client($config, $_POST);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }

  for ($i = 0; $i < count($functions); $i++) {
    if (method_exists($route, $functions[$i])) {
      try {
        $returnData[] = $route->{$functions[$i]}();
      }catch(Exception $e) {
        $returnData[] = $e->getMessage();
      }
    } elseif (method_exists($ticket, $functions[$i])) {
      try {
        $returnData[] = $ticket->{$functions[$i]}();
      }catch(Exception $e) {
        $returnData[] = $e->getMessage();
      }
    } elseif (method_exists($invoice, $functions[$i])) {
      try {
        $returnData[] = $invoice->{$functions[$i]}();
      }catch(Exception $e) {
        $returnData[] = $e->getMessage();
      }
    } elseif (method_exists($client, $functions[$i])) {
      try {
        $returnData[] = $client->{$functions[$i]}();
      }catch(Exception $e) {
        $returnData[] = $e->getMessage();
      }
    } elseif (function_exists($functions[$i])) {
      $returnData[] = $functions[$i]();
    } else {
      $returnData[] = FALSE;
    }
  }

  echo json_encode($returnData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
  return FALSE;
