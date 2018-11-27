<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return FALSE;
  // Include functions
  require_once "../../includes/user_functions.php";

  if (!is_sec_session_started()) sec_session_start();

  require_once '../../includes/APIToolsConfig.php';
  require_once '../../vendor/autoload.php';
  use rjdeliveryomaha\courierinvoice\SearchHandler;
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
