# CourierInvoice

A set of classes for the Courier Invoice API

# Instalation

```
composer require "rjdeliveryomaha/courierinvoice"
```
 Or add "rjdeliveryomaha/courierinvoice":"~1.1" to composer.json
# Classes
  - [CommonFunctions](https://github.com/rjdeliveryomaha/courierinvoice#commonfunctions)
  - [Query](https://github.com/rjdeliveryomaha/courierinvoice#query)
  - [LoginHandler](https://github.com/rjdeliveryomaha/courierinvoice#loginhandler)
  - [Ticket](https://github.com/rjdeliveryomaha/courierinvoice#ticket)
  - [Route](https://github.com/rjdeliveryomaha/courierinvoice#route)
  - [Invoice](https://github.com/rjdeliveryomaha/courierinvoice#invoice)
  - [SearchHandler](https://github.com/rjdeliveryomaha/courierinvoice#searchHandler)
  - [TicketChart](https://github.com/rjdeliveryomaha/courierinvoice#ticketChart)
  - [InvoiceChart](https://github.com/rjdeliveryomaha/courierinvoice#invoiceChart)
  - [Client](https://github.com/rjdeliveryomaha/courierinvoice#client)
  - [InvoiceCron](https://github.com/rjdeliveryomaha/courierinvoice#invoiceCron)

[extras](https://github.com/rjdeliveryomaha/courierinvoice#extras)

---

# CommonFunctions

Utility class extended by all other classes

Throws exception on error

## Usage:

```php
try {
  $functions = new CommonFunctions($config, $data);
} catch(Exception $e) {
  return $e->getMessage();
}
```

$config and $data should both be array. There is a sample config in the extras directory.

This class expects that a session exists unless the property 'noSession' is set in $data.

## Public Methods:

```php
$functions->getProperty($property);
```

Returns a value if the property exists. False is it does not.

```php
$functions->updateProperty($property, $value);
```

Sets property to new value. Returns true. False is property does not exist.

```php
$functions->addToProperty($property, $value);
```

Adds value to property. Returns false if property does not exists or is not numeric.

```php
$functions->substractFromProperty($property, $value);
```

Subtract value from property. Returns false if property does not exists or is not numeric.

```php
$functions->compareProperties($obj1, $obj2, $property, $strict=FALSE);
```

Returns false if property does not exist in both objects. $strict compares type as well as value.

```php
$functions->debug();
```

Pretty print properties and values.

```php
$functions->getError();
```

Return the last error.

```php
$functions->outputKey();
```

Generates unique session value for validating POST data. Returns the value.

---

# Query
Throws exception on error.

## Usage:
```php
try {
  $query = new Query($config, $data);
} catch(Exception $e) {
  return $e->getMessage();
}
```
$config and $data should both be array. There is a sample config in the extras directory.

## Properties settable in $data:
   - primaryKey
     * Integer

     * Should not be set unless using PUT or DELETE.

     * An Exception will be thrown if the PUT or DELETE method are used with this property being not set.

   - endPoint
     * String

     * Valid end points:

       config, tickets, invoices, clients, o_clients, contract_locations, contract_runs, schedule_override, drivers, dispatchers

   - method
     * String

     * Valid values:

       POST, PUT, GET, DELETE

   - queryParams
     * Associative array

     * keys:
       - include / exclude:

         Indexed array of resources to retrieve / ignore from the end point. Include will be favored if both are provided. If omitted all resources are returned. Ex:

         Return only ticket number, billed client, and ticket price.
         ```php
         $data['queryParams']['include'] = [ 'TicketNumber', 'BillTo', 'TicketPrice' ];
         ```
         Return all resources __except__ pick up country.
         ```php
         $data['queryParams']['exclude'] = [ 'pCountry' ];
         ```
       - filter:
          * simple "and" query:

            Indexed array of associative arrays. Ex:

            Select tickets with charge equal to 5 and billed to clients other than client 1.
            ```php
            $data['queryParams']['filter'] = [];

            $data['queryParams']['filter'][] = ['Resource'=>'Charge', 'Filter'=>'eq', 'Value'=>5];

            $data['queryParams']['filter'][] = ['Resource'=>'BillTo', 'Filter'=>'neq', 'Value'=>1];
            ```
          * complex "and" & "or" query:

            Indexed array of simple "and" queries. Ex:

            Select tickets with charge between 1 and 5 and billed to client 1 or tickets billed to client 2.
            ```php
            $filter1 = [];

            $filter1[] = ['Resource'=>'Charge', 'Filter'=>'bt', 'Value'=>'1,5'];

            $filter1[] = ['Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>1];

            $filter2 = [ ['Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=>2] ];

            $data['queryParams']['filter'] = [$filter1, $filter2];
            ```
          * available filters:

            + cs: contain string (string contains value)
            + sw: start with (string starts with value)
            + ew: end with (string end with value)
            + eq: equal (string or number matches exactly)
            + lt: lower than (number is lower than value)
            + le: lower or equal (number is lower than or equal to value)
            + ge: greater or equal (number is higher than or equal to value)
            + gt: greater than (number is higher than value)
            + bt: between (number is between two comma separated values)
            + in: in (number is in comma separated list of values)
            + is: is null (field contains "NULL" value)
            + You can negate all filters by prepending a 'n' character, so that 'eq' becomes 'neq'.

       - order

         Indexed array of ordering parameters. With the "order" parameter you can sort. By default the sort is in ascending order, but by specifying "desc" this can be reversed. Ex:
         ```php
         $data['queryParams']['order'] = ['DispatchTimeStamp,desc'];
         ```
         or
         ```php
         $data['queryParams']['order'] = ['BillTo', 'DispatchTimeStamp,desc'];
         ```
       - page

          * string

          * The "page" parameter holds the requested page. The default page size is 20, but can be adjusted (e.g. to 50). Pages that are not ordered cannot be paginated. EX:

          ```php
          $data['queryParams']['page'] = '1';
          ```
          or
          ```php
          $data['queryParams']['page'] = '1,50';
          ```
## Public Methods

```php
$query->buildURI();
```
Processes the queryParams property into a query string then returns itself so that it can be chained with the call method.

```php
$query->call();
```
Uses the cURL library to execute the query string. Returns the result of the query or throws Exception on error. Ex:

```php
try {
  $query->buildURI()->call();
} catch (Exception $e) {
  return $e->getMessage();
}
```

---

# LoginHandler

Processes login credentials.

Populates ` $_SESSION ` with user data.

Returns string; either '/clients' or '/drivers'.

Throws exception on error.

## Usage

```php
try {
  $handler = new LoginHandler($config, $data);
} catch(Exception $e) {
  return $e->getMessage();
}
```
## Properties settable in $data:

- clientID

  Users login name.
  * Repeat clients: integer
  * Non-repeat clients: string; ` t1 `
  * Organizations: string; ` orgLogin `
  * Drivers: string; ` driver1 `
  * Dispatchers: string; ` dispatch1 `
- upw

  Users password.

## Public Methods

```php
try {
  $type = $handler->login();
} catch(Exception $e) {
  echo $e->getMessage();
  return false;
}
echo $type;
return false;
```

Sets the following session values:

```php
$_SESSION['config']
```

This associative array contains resources from the [config](https://rjdeliveryomaha.com/courierinvoice/apidoc#config) endpoint as well as all Client 0 resources except ` Password `, ` AdminPassword `, and ` Deleted `.


```php
$_SESSION['mobile']
```

If ` true ` this Boolean value indicates that the user logged in from a portal located at domain.com/mobileLogin that passed the LoginHandler class a dataset that included the key "mobile" and will redirect there instead of domain.com upon log out.

```php
$_SESSION['ulevel']
```

Describes the type of user logged in:

  - 0: Organization

  - 1: Admin Client

  - 2: Daily Client

  - driver

  - dispatch

All resources from the end point corresponding to the client, organization, driver, or dispatcher except ` Deleted ` and passwords.

Dispatchers have ` $_SESSION['CanDispatch'] ` set to a value of 2.

Clients, Dispatchers, and Drivers with dispatch privileges have ` $_SESSION['config']['ContractDiscount'] ` and ` $_SESSION['config']['GeneralDiscount'] ` set. These are associative arrays with keys being Client IDs (preceded with the letter 't' for non-repeat clients) and the values being the discount values for each (or current) client.

Clients and organizations will have ` $_SESSION['pwWarning'] ` set.

Integer

  - 0: password did not match the default password for the login type.

  - 1: daily client password is default

  - 2: admin client password is default

  - 3: both daily and admin clients are default

  - 4: organization password is default

These values are used to notify the user that the password should be changed.

Organizations have ` $_SESSION['members'] ` set. This is an associative array with the keys being client IDs (preceded by the letter 't' for non-repeat clients) and the values being an associative array containing the data stored at the clients end point for each client associated with the organization.

---

# Ticket

Processes and displays tickets individually or batched

## Usage:

```php
try {
  $ticket = new Ticket($config, $data);
} catch(Exception $e) {
  return $e->getMessage();
}
```

## Properties settable in $data:

A list of standard properties can be found in the [API Documentation](https://www.rjdeliveryomaha.com/courierinvoice/apidoc#tickets)

The ` step ` property is used by ` $ticket->updateStep() `.

The ` action ` property is used by ` $ticket->cancelTicket() `.

The special property ` multiTicket ` can contain an array of ticket data sets for batch creation or updating. Ex: Update two tickets; one is being delivered the other returned to the same address.

```php
$data['multiTicket'] = [
  [
    'ticket_index': 1,
    'step': 'delivered',
    'notes': 'This ticket was delivered'
  ],
  [
    'ticket_index': 2,
    'step': 'returned'
  ]
]
```

## Public Methods:

```php
echo $ticket->regenTicket();
```

Display single ticket for client review or for dispatch.

```php
echo $ticket->displaySingleTicket();
```

Display single ticket for drivers.

```php
echo $ticket->displayMultiTicket();
```

Display groups of tickets with common pick up or drop off location and time.

```php
echo $ticket->ticketsToDispatch();
```

Check database for tickets that have not been dispatched.

```php
echo $ticket->ticketForm();
```

Has 3 states based on $data passed to Ticket.

- initial: Generates an empty ticket entry form.

  This form is followed by ` <div class="mapContainer" id="map"></div> ` for use with a javascript api.

  This div is only generated on this step.

- edit: Generates a ticket entry form populated with provided ` $data `.

- confirmation: Generates a read-only form for validation.

  TicketPrice is solved here.

- process: Submit ticket to the server.

  TicketNumber is set here.

```php
echo $ticket->runPriceForm();
```

Generates a simplified ticket form that only accepts 2 addresses, charge, and dry ice weight.

The data from this form should be passed to ` $ticket->calculateRunPrice() `.

This form is followed by ` <div class="mapContainer" id="map2"></div> ` for use with a javascript api.

```php
echo $ticket->calculateRunPrice();
```

Returns a json encoded array with the following properties:

- billTo: ClientID.
- address1: Pick up address.
- address2: Delivery address.
- result1: Coordinates of address1.
- result2: Coordinates of address2.
- center: Coordinates of mid-point between result1 and result2.
- pRangeTest: The distance between address1 and RangeCenter.
- dRangeTest: The distance between address2 and RangeCenter.
- rangeDisplay: The distance between address1 and address2.
- runPrice: The calculated price without dry ice.
- ticketPrice: The calculated price with dry ice.
- diWeight: The weight of dry ice provided for the calculation.
- diPrice: The calculated price of dry ice.

```php
echo $ticket->fetchTodaysTickets();
```

Checks for tickets for a given client on the current date.

Returns the results of ` $ticket->regenTicket() ` for each ticket found, false if none exist.

```php
$ticket->processRouteTicket();
```

__Note__: This does not display a map when calculating TicketPrice. Consult [geocoder providers](https://github.com/geocoder-php/Geocoder#providers) for limitations.

Process tickets generated by the Route class:

- Sets TicketNumber

- Solves TicketPrice

- Submits tickets

- Updates LastCompleted date

Returns true on success, false, and optionally logs an error, on failure.

```php
$ticket->processReturnTicket();
```

Processes and submits a change of charge from 5 to 6 using the values stored in TicketBase.

```php
echo $ticket->stepTicket();
```

Sets the time stamp and submits that, notes and other values for the given ` step ` for a single ticket or multiTicket array.

Sends confirmation email if indicated.

Returns a string.

Valid values:

- 'pickedUp'

  Checks for ` $ticket->sigImage ` and submits pSigPrint, pSig, and pSigType.

- 'delivered'

  Checks for ` $ticket->sigImage ` and submits dSigPrint, dSig, and dSigType.

  Solves for TicketPrice is Charge is 7 and d2SigReq is 0.

- 'returned'

  Checks for ` $ticket->sigImage ` and submits d2SigPrint, d2Sig, and d2SigType.

  Solves for TicketPrice if Charge is 7 and d2SigReq is 1.

- 'dispatched'

  Sets DispatchTimeStamp and DispatchMicroTime.

  Updates TicketPrice for charge 7.

```php
echo $ticket->cancelTicket();
```

Processes the given ` action ` for a single ticket or multiTicket array.


Valid values:

- 'delete'

- 'cancel'

- 'deadRun'

- 'declined'

- 'transfer'

---

# Route

Creates tickets for a driver as defined on the Manage Runs page.

Updates LastSeen resource.

Checks for and displays on call tickets.

Checks for and displays transferred tickets.

## Usage:

```php
try {
  $route = new Route($config, $data);
} catch(Exception $e) {
  echo $e->getMessage();
  return FALSE;
}
```

## Properties settable in $data:

All properties of this class are set by the session.

## Public Methods:

```php
$route->routeTickets();
```

Checks drivers LastSeen resource. If it is not the current date a call is made to check for tickets defined on the Manage Runs page and creates them. The LastSeen resource is then updated.

A call is made to fetch incomplete routine and round trip contract tickets. If this result is empty a third call is made to check if any contract tickets were created for the driver on the current day.

Returns HTML content to populate the route page.

```php
$route->onCallTickets();
```

Checks for incomplete on call tickets that have been dispatched to the current driver in the past 7 days.

Returns HTML content to populate the onCall page.

```php
$route->transferredTickets();
```

Checks for tickets that have been transferred to or by the current driver.

Returns HTML content to populate the transfers page.

---

# Invoice

Displays invoices for review.

By default canceled thickets are not displayed. This behavior can be adjusted on a per client basis in the config file located at [extras/includes](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/includes).

By default contract tickets are consolidated by run number. This behavior can be adjusted on a per client basis in the config file located at [extras/includes](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/includes).

## Usage:

```php
try {
  $invoice = new Invoice($config, $data);
} catch(Exception $e) {
  echo $e->getMessage();
  return FALSE;
}
```

## Properties settable in $data:

A list of standard properties can be found in the [API Documentation](https://www.rjdeliveryomaha.com/courierinvoice/apidoc#invoices)

The ` invoiceQueryResult ` property is an indexed array of invoice datasets used for receiving data from the [SearchHandler](https://github.com/rjdeliveryomaha/courierinvoice#searchHandler) class.

## Public Methods:

```php
$invoice->regenInvoice();
```

Uses the data provided in ` invoiceQueryResult ` to displays invoice.

If multiple datasets are provided a form will be returned to select which invoice to recreate by invoice number.

```php
$invoice->invoiceQueryForm();
```

Creates a query form for admin and organization clients. The parameters provided by this form are processed by the [SearchHandler](https://github.com/rjdeliveryomaha/courierinvoice#searchHandler) class.


---

# SearchHandler

Accepts input from ticket and invoice query forms.

Returns html result of datasets processed by those respective classes.

## Usage:

```php
try {
  $handler = new SearchHandler($config, $data);
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
```

## Properties settable in $data

- endPoint

  + tickets

  + invoices

- display

  + tickets: displays the result of ` $ticket->regenTicket() ` for each ticket matching query parameters.

  + invoice: displays the result of ` $invoice->regenInvoice() ` for the matching invoice.

  + chart: displays a bar graph generated by either the TicketChart or InvoiceChart class.

- compare

  When a chart query is made that spans multiple months the default behavior is to display each month in the query. Setting this value to 1 will compare the first and last month of the query parameters.

- compareMembers

  When a chart query is made that includes multiple members of an organization the default behavior is to display charts for each member. Setting this value to 1 will compare members in a query set.

- clientID

  Client associated with an invoice.

- billTo

  Client associated with a ticket.

- repeatClient

  + 0: non-repeat client

  + 1: repeat client

- startDate

  Lower date boundary for a query. Format YYYY-mm-dd.

- endDate

  Upper boundary for a query. Format YYYY-mm-dd.

- invoiceNumber

  Specific invoice to query for a client.

  Expected formats:

  + ##EX####-## regex: ` /(^[\d]{2}EX[\d]+-[\d]+$)/ `

  + ##EX####-t## regex: ` /(^[\d]{2}EX[\d]+-t[\d]+$)/ `

- dateIssued

  Moth to query for invoices for a given client. Format YYYY-mm.

- ticketNumber

  Specific ticket to query for a given client.

- charge

  Specific ticket charge to query for a given client.

  Values from 0 - 9.

- type

  Type of ticket to query for a given client.

  + 2: All

  + 1: Contract

  + 0: On Call

- allTime

  Default 0. If set to 1:

  + If display is "tickets" all tickets from account creation to current date will be displayed.

  + If display is "chart" data will be displayed for the period between the current date and the pervious ` allTimeChartLimit ` months.

---

# TicketChart

Receives a dataset of tickets grouped by month from the [SearchHandler](https://github.com/rjdeliveryomaha/courierinvoice#searchHandler) class.

Displays a simple bar chart depicting the number of each type of ticket over a given period.

[example](extras/example_images/ticketChartExample.png?raw=true "Example of ticket chart.")

## Usage:

```php
try {
  $obj = new TicketChart($this->options, $data);
} catch(Exception $e) {
  echo $e->getMessage();
  return FALSE;
}
```

## Properties settable in $data

- dataSet

  Associative array of data sorted by month.

  ` $monthLabel ` is the ` ReceivedDate ` of a ticket in the format 'M Y'.

  ```php
  $this->months[$monthLabel][$ticket['BillTo']] = [
                                                   'billTo'=>$ticket['BillTo'],
                                                   'monthTotal'=>1,
                                                   'contract'=>0,
                                                   'credit'=>0,
                                                   'canceled'=>0,
                                                   'onCall'=>0,
                                                   'routine'=>0,
                                                   'fourHour'=>0,
                                                   'threeHour'=>0,
                                                   'twoHour'=>0,
                                                   'oneHour'=>0,
                                                   'roundTrip'=>0,
                                                   'deadRun'=>0,
                                                   'dedicatedRun'=>0,
                                                   'withIce'=>0,
                                                   'withoutIce'=>0,
                                                   'startDate'=>$receivedDate->format('Y-m-d'),
                                                   'endDate'=>$receivedDate->format('Y-m-d')
                                                 ];
  ```

- organizationFlag

  Boolean

  Indicates if the query if for an organization (` true `) or an individual client (` false `)

- clientID

  Array or Integer

  If ` organizationFlag ` is ` true ` this is an indexed array of members to be queries.

  If ` organizationFlag ` is ` false ` this is the ID of the current client.

- compare

  Boolean

  Indicates if dataset is two months compared or a series of months.

- compareMembers

  Boolean

  Indicates if members are being compared of displayed separately.

---

# InvoiceChart

---

# Client

---

# InvoiceCron

Automatically creates invoices with a [cron](https://en.wikipedia.org/wiki/Cron) job run on the day following the end of a monthly billing cycle.

This class does not account for leap years and assumes that invoices will not be generated after the 28th of any month.

Updates tickets with new invoice number.

Can be configured to ignore clients and non-repeat clients.

Checks for past due invoices.

## Usage:

```php
  require_once '../includes/api_config.php';
  require_once '../vendor/autoload.php';

  use rjdeliveryomaha\courierinvoice\InvoiceCron;

  try {
    $cron = new InvoiceCron($config);
  } catch(Exception $e) {
    // log $e->getMessage();
    exit;
  }

  try {
    $cron->createInvoices();
  } catch(Exception $e) {
    // log $e->getMessage();
    exit;
  }
  exit;
```

---

# extras

This is an extendable drop-in implementation of this set of classes using jQuery v3.3.1.

[jQuery.ajaxRetry](https://github.com/dcherman/jQuery.ajaxRetry) is used to implement a simple backoff.

A templet for ajax calls with this backoff built in is also included. This will retry a failed call 20 times waiting ` n * 250 ` milliseconds between calls where n is the retry count. The function returns the jQuery ajax object.

```javascript
  ajax_template(callMethod, url, returnType, postData=false)
```

Usage:

```javascript
  let postData =  { "formKey": $("#formKey").val(), "ticket_index": $(".ticket_index").val() }
  let attempt = ajax_template("post", "../ajax/doSomething.php", "html", postData)
  .done((result)=>{
    // do something with the returned html
  })
  .fail((jqXHR, status, error)=>{
    // display error
  });
```

[Signature Pad v2.3.2](https://github.com/szimek/signature_pad) is preconfigured with [extras/public_html/app_js/sigPad.js](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/public_html/app_js/sigPad.js) to collect signatures in conjunction with the functions ` $ticket->displaySingleTicket() ` and ` $ticket->displayMultiTicket() `.

The function ` toast(msg, options) ` is exported to the global scope. It creates and deletes a toast-like div to show messages to the user.

Usage:

```javascript
  let options = {},
      msg = "Test Message";
  options.title = "sample div title"; // title attribute of toast div. default ""
  options.time = 3000; // milliseconds to show toast div. div will be removed 1 second after it is hidden. default 4000
  options.eleClass = "ticketOncallReceived"; // custom class for the toast div. The function will display only the newest of a custom class, removing previous messages. All default class divs will be displayed for the configured time. default "toast__msg"
  options.datatime = 1512574797926; // unix time stamp to parse for display with message. default new Date().getTime();
  toast(msg, options);
```

### Features

Uses ticket information from database to populate datalist elements to assist form completion.

Single page design navigated by either swipe or menu. Offers unique features based upon user type.

At the end of the transition between pages ` scroll(0,0) ` is called and a window event named 'pageChange' is triggered.

Setting the order of menu items as well as adding custom menu items (with or without matching pages), and javascript files is handled in the config file located at [extras/includes](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/includes).

### Drivers

* Route

  + Uses Route class to create or fetch contract tickets for a given driver.

  + Groups tickets with matching location and time.

  + Displays single and grouped tickets with ability to:

    - collect signatures

    - update step

    - update notes

    - cancel

    - mark as dead run

    - transfer

  + Can be independently refreshed.

* On Call

  + Uses Route class to fetch on call tickets for a given driver.

  + Displays single tickets with ability to:

    - collect signatures

    - update step

    - update notes

    - cancel

    - mark as dead run

    - transfer

  + Can be independently refreshed.

* Transfers

  + Uses Route class to fetch tickets either transferred by or transferred to a given driver.

  + Groups contract tickets with matching location and time.

  + Displays single and grouped tickets with ability to:

    - accept transfer

    - decline transfer

    - cancel transfer

  + Can be independently refreshed.

* Ticket Entry and Dispatch page for drivers with dispatch privileges. Described below.

* Active Tickets page for drivers with dispatch privileges. Described below.

* Change Password

  + Provides a simple form to update the password

### Dispatchers

* Dispatch

  + Uses Ticket class to check for tickets that have not been dispatched.

  + Displays single tickets with ability to dispatch.

* Price Calculator

  + Compact ticket form

  + Accepts only pick up address, delivery address, charge, and dry ice information

  + Uses Ticket class to compute the price of a ticket

  + ` <div class="mapContainer" id="map2"></div> ` available to display a map

* Active Tickets

  + Query contract or on call tickets for a given driver

  + Displays single tickets with ability to edit

* Ticket Entry

  + Form to submit tickets to the API

  + ` <div class="mapContainer" id="map"></div> ` available to display a map

* Change Password

  + Provides a simple form to update the password

### Clients

* Admin User

  + Delivery request form

    - Form to submit tickets to the API

  + Ticket Query

    - Can search tickets by ticket number or by date

    - Displays either individual tickets or a bar graph depicting ticket volume

    - Bar graph can cover a range of months (maximum can be configured) or compare two months

    - Ticket charges available to be queried can be configured separately for admin and daily users

  + Invoice Query

    - Can search invoices by invoice number or by date

    - Displays either individual invoices or bar graph depicting expenses

    - Bar graph can cover a range of months (maximum can be configured) or compare two months

  + Password management

    - Update password for either admin or daily users

    - Warns to change from default password

  + Contact information management

  + Price Calculator

* Daily User

  + Delivery request form

  + Ticket Query

  + Price Calculator

### Organizations

* Price Calculator

* Ticket Query

  - Can search ticket by ticket number or by date

  - Can search by individual member or by groups of members

  - Displays either individual tickets or bar graph depicting ticket volume

  - Bar graph can cover a range of months (maximum can be configured) or compare two months

* Invoice Query

  - Can search invoices by invoice number or by date

  - Can search by individual member or by groups of members

  - Displays either individual invoices or bar graph depicting expenses

  - Bar graph can cover a range of months (maximum can be configured) or compare two months

* Password management
