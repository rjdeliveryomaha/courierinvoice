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
    // string: login name for Courier Invoice user as an alternative to using ClientID 0 (zero)
    'userLogin' => 'CustomLogin',
    // By default all charges are shown for Courier Invoice user client 0
    // By default charges Canceled (0) and Dead Run (8) are excluded on ticket __entry__ forms for drivers and dispatchers
    // By default Credit (9) is also excluded from ticket __entry__ forms for clients
    // By default all charges are included on ticket __query__ forms
    // The following five settings remove charges based on the user type
    // 2 dimentional indexed array of charges to exclude for drivers
    // index 0 driver can dispatch to self
    // index 1 driver can dispatch to all
    'driverChargesExclude' => [ [ 9 ], [ 9 ] ],
    // indexed array of charges to exclude for dispatchers
    'dispatchChargesExclude' => [ 9 ],
    // 2 dimentional indexed array of charges to exclude for clients
    // index 0 = admin clients
    // index 1 = dayly clients
    'clientChargesExclude' => [ [], [] ],
    // indexed array of charges to exclude for organizations
    'orgChargesExclude' => [],
    // indexed array of charges to exclude for Courier Invoice user client 0
    'client0ChargesExclude' => [],
    // associative array extend layout and menue with custom pages
    // The top level keys are who to create the custom pages for; all, client, org, driver, dispatcher, client0.
    // The structure of subsequent indexed arrays:
    // [ 'Menue Entry', 'functionName', '../path/to/script.js', 'scriptAttribute1', 'scriptAttribute2', ... ... ]
    // Index 0, if not null or an empty string, will be added to the menue.
    // Index 1, if set and not null or an empty string and index 0 is not null or an empty string, will be looked for as a function in includes/user_functions.php to populate the page.
    // This function should return html content.
    // Index 2, if set and not null or an empty string, will be added as the src of a script element.
    // If an entry has a non-null, not empty string at index 0 and a null or empty string at index 1 it will be moved to the end of the list.
    // This is done to preserve the indexing of entries to pages.
    // If both index 0 and index 1 are null or empty string index 2 will be added as the src of  a script element.
    // Any indices past 2 wll be interpreted as attributes to be applied to the script ex: defer or async.
    // They will be applied in the order that they appear.
    'extend' => [
      'all' => [
        // this will be moved to the end of the next not empty extend property.
        ['<a href="mailto:support@yourdomain.com">Contact Support</a>'],
        // this will be the first entry after the standard list of links.
        ['Help', 'createHelpContent'],
        // this add the element <script src="../js/maps.js"></script> to the page
        [ null, null, '../js/maps.js'],
        // this adds the element <script src="https://address_of_defered_script" async defer></script> to the page
        [null, null, 'https://address_of_defered_script', 'async', 'defer'],
        // this adds an element to the menue and a script to the page
        ['Notifications: <button type="button" class="fab__push">Off</button>', '', '../js/pushMessaging.js'],
        // these files are used by default.
        [null, null, '../app_js/jQuery.ajaxRetry.min.js'],
        [null, null, '../app_js/ajaxTemplate.js'],
        [null, null, '../app_js/app.js']
      ],
      'client' => [],
      'org' => [],
      'driver' => [
        // these are default configurations for collecting signatures
        [null, null, 'https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js'],
        [null, null, '../../app_js/sigPad.js']
      ],
      'dispatcher' => [],
      // This setting is not yet implemented
      'client0' => []
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
      'TwoHour' => 0.0,
      'ThreeHour' => 0.0,
      'FourHour' => 0.0,
      'DeadRun' => 0.0,
      'DedicatedRunRate' => 0.0,
      'Geocoders' => '{}',
      'BaseTicketFee' => 0.0,
      'MaximumFee' => 0.0,
      'RangeIncrement' => 0.0,
      'PriceIncrement' => 0.0,
      'MaxRange' => 0.0,
      'RangeCenter' => [ 'lat' => 41.2125742, 'lng' => -95.9765968 ]
    ];
  }
