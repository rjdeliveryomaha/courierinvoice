<?php
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return false;

  require_once '../../includes/api_config.php';
  require_once '../../includes/user_functions.php';
  require_once '../../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\SearchHandler;
  use rjdeliveryomaha\courierinvoice\SecureSessionHandler;
  use Dompdf\Dompdf;

  $content = false;
  try {
    SecureSessionHandler::start_session($config);
  } catch(Exception $e) {
    $content = "<span data-value=\"error\">{$e->getMessage()}</span>";
  }

  if (!$content) {
    $content = $_POST['content'];
  }

  $title = $_POST['invoiceNumber'] ?? $_POST['title'] ?? 'download';
  $style = 'client';
  if (isset($_POST['type'])) {
    switch ($_POST['type']) {
      case 'invoice': $style = 'pdfInvoice'; break;
      case 'chart': $style = 'pdfReports'; break;
      default: $style = 'client'; break;
    }
  }
  $format = $_POST['paperFormat'] ?? $config['paperFormat'] ?? 'letter';
  $orientation = $_POST['paperOrientation'] ?? $config['paperOrientation'] ?? 'portrait';
  $val = "
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">
    <meta name=\"viewport\" content=\"width=device-width\">
    <title>{$title}</title>
    <link rel=\"stylesheet\" href=\"../style/{$style}.css\" type=\"text/css\">
    <link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"../apple-touch-icon.png\">
    <link rel=\"icon\" type=\"image/png\" href=\"../favicon-32x32.png\" sizes=\"32x32\">
    <link rel=\"icon\" type=\"image/png\" href=\"../favicon-16x16.png\" sizes=\"16x16\">
  </head>
  <body>{$content}";

  $dompdf = new Dompdf();
  $dompdf->set_paper($format, $orientation);
  $dompdf->set_option('isHtml5ParserEnabled', true);
  $dompdf->loadHtml($val);
  $dompdf->render();
  return $dompdf->stream("{$title}.pdf", [ 'Attachment' => false ]);
