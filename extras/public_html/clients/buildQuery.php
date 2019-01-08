<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\SearchHandler;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;

  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    echo "<span data-value=\"error\">{$e->getMessage()}</span>";
    return FALSE;
  }
  try {
    $handler = new SearchHandler($config, $_POST);
  } catch (Exception $e) {
    echo $e->getMessage();
    return false;
  }
  try {
    $val = $handler->handleSearch();
  } catch(Exception $e) {
    $val = $e->getMessage();
  }
  echo $val;
  return false;
  
