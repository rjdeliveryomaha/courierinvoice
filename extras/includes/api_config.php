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
    // By default all charges are included on ticket forms
    // The following settings remove charges based on the user type and form type
    // 2 dimentional indexed array of charges to exclude for drivers ticket enrty and update form
    // index 0 driver can dispatch to self
    // index 1 driver can dispatch to all
    'driverChargesEntryExclude' => [ [ 0, 8, 9 ], [ 0, 8, 9 ] ],
    // 2 dimentional indexed array of charges to exclude for drivers ticket query form
    // index 0 driver can dispatch to self
    // index 1 driver can dispatch to all
    'driverChargesQueryExclude' => [ [ 0, 8, 9 ], [ 0, 8, 9 ] ],
    // indexed array of charges to exclude for dispatchers ticket entry and update forms
    'dispatchChargesEntryExclude' => [ 0, 8, 9 ],
    // indexed array of charges to exclude for dispatchers ticket query forms
    'dispatchChargesQueryExclude' => [ 9 ],
    // 2 dimentional indexed array of charges to exclude for clients entry (request) form
    // index 0 = admin clients
    // index 1 = dayly clients
    'clientChargesEntryExclude' => [ [ 0, 8, 9 ], [ 0, 8, 9 ] ],
    // 2 dimentional indexed array of charges to exclude for clients query form
    // index 0 = admin clients
    // index 1 = dayly clients
    'clientChargesQueryExclude' => [ [], [ 0, 8, 9 ] ],
    // indexed array of charges to exclude for organizations query from
    'orgChargesQueryExclude' => [],
    // indexed array of charges to exclude for Courier Invoice user client 0 entry and update forms
    'client0ChargesEntryExclude' => [],
    // indexed array of charges to exclude for Courier Invoice user client 0 query form
    'client0ChargesQueryExclude' => [],
    // By default Charge is null when the ticket entry form is initialized
    // number: Charge to be selected when ticket entry form is initialized
    'initialCharge' => 5,
    // associative array extend layout and menue with custom pages
    // The top level keys are who to create the custom pages for; all, client, org, driver, dispatcher, client0.
    // all, org, and dispatcher should contain a single indexed array
    // Index 0 will be added, as is, to the menue. It will be converted to lowercase, spaces replaced with underscore and used as the id attribute of the page.
    // Index 1, if set and not null or an empty string, will be looked for first as a method in the Ticket, Route, Invoice, and Client classes then as a function in includes/user_functions.php to populate the page.
    // This function should return html content.
    // Index 2, if set and not null or an empty string, will be added as the src of a script element.
    // If an entry has a non-null, not empty string at index 0 and a null or empty string at index 1 it will be moved to the end of the list.
    // This is done to preserve the indexing of entries to pages.
    // If both index 0 and index 1 are null or empty string index 2 will be added as the src of a script element.
    // Any indices past 2 will be interpreted as properties to be applied to the script ex: defer or async.
    // The client and driver keys should a multi-dimentional indexed array with elements as described above
    // 'client' => [ [ ['all clients'] ], [ ['admin clients'] ], [ ['dayly clients'] ] ]
    // 'drivers' => [ [ ['all drivers'] ], [ ['driver cannot dispatch'] ], [ ['driver can dispatch self'] ], [ ['driver can dispatch any'] ] ]
    'extend' => [
      'all' => [
//        ['Help', 'createHelpContent', '../js/help.js'], // This item would be moved to the end of the menue
//        [ null, null, '../js/maps.js'], // This item would be added as the first script on the page
        [null, null, '../app_js/jQuery.ajaxRetry.min.js'],
        [null, null, '../app_js/ajaxTemplate.js'],
        [null, null, '../app_js/app.js']
      ],
      'client' => [
        [
          // All clients
//          [null, null, 'https://remote.script.com/do_something', 'async', 'defer'],
          ['Ticket Entry', 'ticketForm'],
          ['Ticket Query', 'ticketQueryForm']
        ],
        [
          // admin clients
          ['Invoice Query', 'invoiceQueryForm'],
          ['Price Calculator', 'runPriceForm'],
          ['Change Password', 'daylyPasswordForm'],
          ['Change Admin Password', 'adminPasswordForm'],
          ['Contact Info', 'updateInfoForm']
        ],
        [
          // dayly clients
          ['Price Calculator', 'runPriceForm']
        ]
      ],
      'org' => [
        [ 'Price Calculator', 'runPriceForm' ],
        [ 'Invoice Query', 'invoiceQueryForm' ],
        [ 'Change Password', 'orgPasswordForm']
      ],
      'driver' => [
        [
          // all drivers
          ['Route', 'activeTickets'],
          ['On Call', 'onCallTickets'],
          ['Transfers', 'transferredTickets'],
          [null, null, 'https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js'],
          [null, null, '../app_js/sigPad.js']
        ],
        [
          // can dispatch = 0
          ['Change Password', 'driverPasswordForm']
        ],
        [
          // can dispatch = 1
          ['Ticket Entry', 'ticketForm'],
          ['Change Password', 'driverPasswordForm']
        ],
        [
          // can dispatch = 2
          ['Dispatch', 'ticketsToDispatch'],
          ['Active Tickets', 'initTicketEditor'],
          ['Ticket Entry', 'ticketForm'],
          ['Change Password', 'driverPasswordForm']
        ]
      ],
      'dispatcher' => [
        ['Dispatch', 'ticketsToDispatch'],
        ['Active Tickets', 'initTicketEditor'],
        ['Ticket Entry', 'ticketForm'],
        ['Price Calculator', 'runPriceForm'],
        ['Change Password', 'dispatchPasswordForm']
      ],
      'client0' => [
        ['Price Calculator', 'runPriceForm'],
        ['Dispatch', 'ticketsToDispatch'],
        ['Change Password', 'daylyPasswordForm'],
        ['Change Admin Password', 'adminPasswordForm'],
        ['Contact Info', 'updateInfoForm']
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
