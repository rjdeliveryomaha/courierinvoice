<?php
  $config = [
    'username' => '',

    'publicKey' => '',

    'privateKey' => '',

    'enableLogging' => false,

    'targetFile' => './API.log',

    'testMode' => false,

    'testURL' => '',

    'invoicePage1Max' => 6,

    'invoicePageMax' => 9,

    'showCancelledTicketsOnInvoiceExceptions' => [],

    'consolidateContractTicketsOnInvoiceExceptions' => [],

    'clientNameExceptions' => [],

    'clientAddressExceptions' => [],

    'ignoreValues' => [ 'none', 'test', 'billing correction', '-', 'multiple', 'fuel compensation', 'other charge' ],

    'emailConfig' => [ 'fromAddress' => '', 'password'=> '', 'smtpHost' => '', 'port' => '587', 'secureType' => 'tls', 'fromName' => '', 'BCCAddress' => '' ],

    'allTimeChartLimit' => 6,

    'userLogin' => 'CustomLogin',

    'driverChargesEntryExclude' => [ [ 0, 8, 9 ], [ 0, 8, 9 ] ],

    'driverChargesQueryExclude' => [ [ 0, 8, 9 ], [ 0, 8, 9 ] ],

    'dispatchChargesEntryExclude' => [ 0, 8, 9 ],

    'dispatchChargesQueryExclude' => [ 9 ],

    'clientChargesEntryExclude' => [ [ 0, 8, 9 ], [ 0, 8, 9 ] ],

    'clientChargesQueryExclude' => [ [], [ 0, 8, 9 ] ],

    'orgChargesQueryExclude' => [],

    'client0ChargesEntryExclude' => [],

    'client0ChargesQueryExclude' => [],

    'initialCharge' => 5,

    'extend' => [
      'all' => [
        [null, null, '../app_js/jQuery.ajaxRetry.min.js'],
        [null, null, '../app_js/ajaxTemplate.js'],
        [null, null, '../app_js/app.js']
      ],
      'client' => [
        [
          // All clients
          ['Ticket Entry', 'ticketForm'],
          ['Ticket Query', 'ticketQueryForm']
        ],
        [
          // admin clients
          ['Invoice Query', 'invoiceQueryForm'],
          ['Price Calculator', 'runPriceForm'],
          ['Change Password', 'dailyPasswordForm'],
          ['Change Admin Password', 'adminPasswordForm'],
          ['Contact Info', 'updateInfoForm']
        ],
        [
          // daily clients
          ['Price Calculator', 'runPriceForm']
        ]
      ],
      'org' => [
        [ 'Price Calculator', 'runPriceForm' ],
        [ 'Ticket Query', 'ticketQueryForm' ],
        [ 'Invoice Query', 'invoiceQueryForm' ],
        [ 'Change Password', 'orgPasswordForm']
      ],
      'driver' => [
        [
          // all drivers
          ['Route', 'routeTickets'],
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
        ['Change Password', 'dailyPasswordForm'],
        ['Change Admin Password', 'adminPasswordForm'],
        ['Contact Info', 'updateInfoForm']
      ],
      'org0' => [
        [ 'Price Calculator', 'runPriceForm' ],
        [ 'Ticket Query', 'ticketQueryForm' ],
        [ 'Invoice Query', 'invoiceQueryForm' ],
        [ 'Change Password', 'orgPasswordForm']
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
