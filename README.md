# CourierInvoice
A set of classes for the Courier Invoice API
# Instalation
```
composer --dev require "rjdeliveryomaha/courierinvoice"
```
 Or add "rjdeliveryomaha/courierinvoice":"dev-master" to composer.json
# Classes
  - CommonFunctions
  - Query
  - LoginHandler
  - Ticket
  - Route
  - Invoice
  - SearchHandler
  - TicketChart
  - InvoiceChart
  - Client

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

            $filter1[] = ['Resource'=>'BillTo', 'Filter'=>'eq', 'Value'=> 1];

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

# LoginHandler
Processes login credentials.

Populates ``` $_SESSION ``` with user data.

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
  * Non-repeat clients: string ``` t1 ```
  * Organizations: string ``` orgLogin ```
  * Drivers: string; ``` driver1 ```
  * Dispatchers: string; ``` dispatch1 ```
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
