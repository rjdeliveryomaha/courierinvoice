<?php
  $config = [
    'username' => '',
    'publicKey' => '',
    'privateKey' => '',
    // bool: logging is disabled by default
    'enableLogging' => false,
    // string: if logging is enabled a target file /must/ be defined
    'targetFile' => './API.log',
    // Canceled tickets are not displayed on invoices by default
    // numeric array of integers: client IDs to exempt from this rule
    'showCancelledTicketsOnInvoiceExceptions' => [],
    // Contract tickets are consolidated by run number on invoices by default
    // numeric array of integers: client IDs to exempt from this rule
    'consolidateContractTicketsOnInvoiceExceptions' => [],
    // associative array: client names that should be changed, for example, to abbreviate
    // ex: [ 'some long client name' => 'SLCN']
    'clientNameExceptions' => [],
    // numeric array: addresses that should be ignored, for example, due to change of address
    'clientAddressExceptions' => [],
    // numeric array: Values that should not be included on ticket entry datalists. Values should be lower case.
    'ignoreValues' => [ 'none', 'test', 'billing correction', '-', 'multiple', 'fuel compensation', 'other charge' ],
    // associative array: Setting to use with PHPMailer.
    'emailConfig' => [ 'fromAddress' => '', 'password'=> '', 'smtpHost' => '', 'port' => '587', 'secureType' => 'tls', 'fromName' => '', 'BCCAddress' => '' ],
    // number: maximum number of months to display on a chart. Default is 6
    'allTimeChartLimit' => 6,
    // number: to be described
    'ticketChartRowLimit' => 5,
    // string: login name for courier invoice user as an alternative to using ClientID 0 (zero)
    'userLogin' => 'RJDelivery'
  ];
  if (!isset($_SESSION['config'])) {
    $config['config'] = [
      'CurrencySymbol' => '$',
      'WeightsMeasures' => 0,
      'InternationalAddressing' => 0,
      'TimeZone' => 'America/Chicago',
      'diPrice' => 0,
      'OneHour' => 0.0,
      'TwoHour' => 0,
      'ThreeHour' => 0,
      'FourHour' => 0,
      'DeadRun' => 0.0,
      'DedicatedRunRate' => 0,
      'Geocoders' => '{}',
      'BaseTicketFee' => 0.0,
      'MaximumFee' => 0.0,
      'RangeIncrement' => 0,
      'PriceIncrement' => 0.0,
      'MaxRange' => 0,
      'RangeCenter' => [ 'lat' => 41.2125742, 'lng' => -95.9765968 ]
    ];
  }
