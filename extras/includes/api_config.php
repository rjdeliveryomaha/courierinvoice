<?php
  $config = [
    'username' => '',
    'publicKey' => '',
    'privateKey' => '',
    // bool: logging is disabled by default
    'enableLogging' => false,
    // string: if logging is enabled a target file must be defined
    'targetFile' => './API.log',
    // Canceled tickets are not displayed on invoices by default
    // indexed array of integers: client IDs to exempt from this rule
    'showCancelledTicketsOnInvoiceExceptions' => [],
    // Contract tickets are consolidated by run number on invoices by default
    // indexed array of integers: client IDs to exempt from this rule
    'consolidateContractTicketsOnInvoiceExceptions' => [],
    // associative array: client names that should be changed, for example, to abbreviate
    // ex: [ 'some long client name' => 'SLCN']
    'clientNameExceptions' => [],
    // indexed array: addresses that should be ignored, for example, due to change of address
    'clientAddressExceptions' => [],
    // indexed array: Values that should not be included on ticket entry datalists. Values should be lower case.
    'ignoreValues' => [ 'none', 'test', 'billing correction', '-', 'multiple', 'fuel compensation', 'other charge' ],
    // associative array: Setting to use with PHPMailer.
    'emailConfig' => [ 'fromAddress' => '', 'password'=> '', 'smtpHost' => '', 'port' => '587', 'secureType' => 'tls', 'fromName' => '', 'BCCAddress' => '' ],
    // integer: maximum number of months to display on a chart. Default is 6
    'allTimeChartLimit' => 6,
    // integer: to be described
    'ticketChartRowLimit' => 5,
    // string: login name for courier invoice user as an alternative to using ClientID 0 (zero)
    'userLogin' => 'CustomLogin',
    // associative array extend layout and menue with custom pages
    // The top level keys are who to create the custom pages for; all, client, org, driver, dispatcher.
    // Top level keys will be evaluated by name in the order above.
    // In the subsequent indexed arrays the value 0 will be added to the menue.
    // Value 1, if set and not null or an empty string, will be looked for as a function in includes/user_functions.php to populate the page.
    // This function should return html content.
    // value 2, if set and not null or an empty string, will be added as the src of a script element.
    // If an entry has a non-null, not empty string vlaue 0 and a null or empty string value 1 it will be moved to the end of the list.
    // This is done to preserve the indexing of entries to pages.
    // If both value 0 and value 1 are null or empty string value 2 will be added as the src of  a script element.
    // Any values past 2 wll be interpreted as properties to be applied to the script ex: defer or async.
    // The will be applied in the order that they appear.
    'extend' => [
      'all' => [
        // this will be moved to the end of the next not empty extend property.
        ['<a href="mailto:support@yourdomain.com">Contact Support</a>'],
        // this will be the first entry after the standard list of links.
        ['Help', 'createHelpContent'],
        [ null, null, '../js/maps.js']
      ],
      'client' => [
        ['Notifications: <button type="button" class="fab__push">Off</button>', '', '../js/pushMessaging.js']
      ],
      'org' => [
        [null, null, 'https://address_of_defered_script', 'async', 'defer']
      ],
      'driver' => [
        ['Notifications: <button type="button" class="fab__push">Off</button>', '', '../js/pushMessaging.js']
      ],
      'dispatcher' => [
        ['Notifications: <button type="button" class="fab__push">Off</button>', '', '../js/pushMessaging.js']
      ]
    ]
  ];
  // config for price calculation without session
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
