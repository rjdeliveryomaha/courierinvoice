<?php
  namespace RJDeliveryOmaha\CourierInvoice;
  
  use RJDeliveryOmaha\CourierInvoice\CommonFunctions;
  
  /***
  * throws Exception
  *
  * @param $endPoint string: must be in $validTables
  *
  * @param $method string: 'POST', 'PUT', 'GET', 'DELETE'
  *
  * @param $queryParams: assoc array keys = resources, filter, order, page
  *
  *  $queryParams['resources'] = array('res1', 'res2', 'res3', ...);
  *
  *  If $queryParams['resources'] is omitted all resources for entries matching the filter will be returned
  *
  *  $queryParams['filter'] = array(array('Resource'=>'resName', 'Filter'=>'filter', 'Value'=>'val'))
  *  The above describes a simple query. The structure of an 'AND' statement simply adds elements to the primary arry. EX:
  *  $queryParams['filter'] = array(array('Resource'=>'resName', 'Filter'=>'filter', 'Value'=>'val'), array('Resource'=>'resName', 'Filter'=>'filter', 'Value'=>'val'))
  *  An 'OR' statement requires an array of simple or 'AND' statements. Ex:
  *  $queryParams['filter'] = array(array(array('Resource'=>'resName', 'Filter'=>'filter', 'Value'=>'val'), array('Resource'=>'resName', 'Filter'=>'filter', 'Value'=>'val')), array('Resource'=>'resName', 'Filter'=>'filter', 'Value'=>'val'))
  * The available filters:
  *  cs: contain string (string contains value)
  *  sw: start with (string starts with value)
  *  ew: end with (string end with value)
  *  eq: equal (string or number matches exactly)
  *  lt: lower than (number is lower than value)
  *  le: lower or equal (number is lower than or equal to value)
  *  ge: greater or equal (number is higher than or equal to value)
  *  gt: greater than (number is higher than value)
  *  bt: between (number is between two comma separated values)
  *  in: in (number is in comma separated list of values)
  *  is: is null (field contains "NULL" value)
  *  You can negate all filters by prepending a 'n' character, so that 'eq' becomes 'neq'.
  *
  *  If $queryParams['filter'] is omitted all entries will return the requested resources
  *
  *  With the "order" parameter you can sort. By default the sort is in ascending order, but by specifying "desc" this can be reversed
  *  $queryParams['order'] = array(array('resource'=>'TicketNumber','dir'=>'desc'));
  *
  *  You may sort on multiple fields by using multiple "order" parameters
  *  $queryParams['order'] = array(array('resource'=>'TicketNumber', 'dir'=>'desc'),array('resource'=>'Contract'));
  *
  *
  * The "page" parameter holds the requested page. The default page size is 20, but can be adjusted (e.g. to 50)
  * $queryParams['page'] = '1'; or $queryParams['page'] = '1,50';
  *
  * Pages that are not ordered cannot be paginated
  *
  *  When using this function to update a resource (PUT):
  *    $queryParams should be an empty array
  *    The function call should have a string appended:
  *    This string should contain a forward slash followed by the PRIMARY KEY value of the resource to be modified
  *    An example using the call function defined above:
  *      call('PUT', buildURI($endPoint, array()) . '/' . $varHoldingPK, $payload);
  *    See the table above for the PRIMARY KEY resources to be used for these calls
  ***/
  
  class Query extends CommonFunctions {
    protected $method;
    protected $endPoint;
    protected $primaryKey;
    protected $queryParams;
    protected $payload;
    private $baseURI;
    private $validTables;
    protected $query;
    protected $queryURI;
    private $timeVal;
    private $token;
    private $headers;
    private $jsonData;
    private $ch;
    private $result;
    protected $testVal;
    
    public function __construct($options, $data=[]) {
      try {
        parent::__construct($options, $data);
      } catch (Exception $e) {
        throw $e;
      }
      // username, publicKey, and privateKey will be set in CommonFunctions
      $this->baseURI = 'https://rjtesting.ddns.net/v2/records/';
      $this->validTables = [ 'config', 'tickets', 'invoices', 'clients', 'o_clients', 'contract_locations', 'contract_runs', 'schedule_override', 'drivers', 'dispatchers', 'categories' ];
    }

    private function responseError() {
      switch (self::test_int($this->result)) {
        case 400:
          $this->error = "<span class=\"error\">Server Error</span>: Invalid Request URI.\n";
        break;
        case 401:
          $this->error = "<span class=\"error\">Server Error</span>: Invalid API credentials.\n";
        break;
        case 403:
          $this->error = "<span class=\"error\">Server Error</span>: API credentials not defined.\n";
        break;
        case 404:
          $this->error = "<span class=\"error\">Server Error</span>: Failed to locate record.\n";
        break;
        case 422:
          $this->error = "<span class=\"error\">Server Error</span>: Failed Data Validation. {$this->after('422', $this->result)}\n";
        break;
        case 500:
          $this->error = "<span class=\"error\">Server Error</span>: Internal Error.\n";
        break;
        case 503:
          $this->error = "<span class=\"error\">Server Error</span>: Service temporarily unavailable.\n";
        default:
          $this->error = "<span class=\"error\">Server Error</span>: {$this->result}.\n";
        break;
      }
      if ($this->enableLogging !== FALSE) self::writeLoop();
    }

    private function testResponse() {
      // A simple test to make sure an appropriate response was received
      switch (strtoupper($this->method)) {
        // POST: Expects the id of the last created resource
        // PUT, DELETE: Expects the number of rows affected
        // Expects an array of ids or rows affected when creating, updating, or deleting with array
        // Receives the string 'null' on failure
        case 'DELETE':
        case 'PUT':
        case 'POST': return (is_numeric($this->result) || substr($this->result, 0, 1) === '[');
        // Expects a json encoded object or array of objects
        case 'GET': return (substr($this->result,0,1) === '{' || substr($this->result,0,1) === '[');
    
        default: return FALSE;
      }
    }
    
    private function orderParams($params) {
      $paramList = [];
      if (isset($params['resources'])) {
        $paramList[] = 'resources';
      }
      if (isset($params['filter'])) {
        $paramList[] = 'filter';
      }
      if (isset($params['order'])) {
        $paramList[] = 'order';
      }
      if (isset($params['page'])) {
        $paramList[] = 'page';
      }
      $temp = array_merge(array_flip($paramList), $params);
      return $temp;
    }
    
    public function buildURI() {
      if (!in_array($this->endPoint, $this->validTables)) {
        $this->error = "Invalid End Point\n";
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ($this->primaryKey === NULL && (strtoupper($this->method) === 'PUT' || strtoupper($this->method) === 'DELETE')) {
        $this->error = "Primary Key Not Set For End Point {$this->endPoint}\n";
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ((strtoupper($this->method) === 'POST' || strtoupper($this->method) === 'PUT') && ($this->payload === NULL || (is_array($this->payload) && empty($this->payload)))) {
        $this->error = "No Payload Provided\n";
        if ($this->enableLogging !== FALSE) self::writeLoop();
        throw new \Exception($this->error);
      }
      if (!is_array($this->queryParams) || empty($this->queryParams)) {
        $this->queryURI = $this->baseURI . $this->endPoint;
      } else {
        // make sure that the 'resources' key preceeds the 'filter' key
        $temp = self::orderParams($this->queryParams);
        $this->queryParams = $temp;
        foreach ($this->queryParams as $key => $value) {
          if ($key === 'resources') {
            $this->query['include'] = implode(",",$this->queryParams['resources']);
          } elseif ($key === 'filter' || $key === 'order') {
            if (!isset($value[0][0])) {
              for ($i = 0; $i < count($value); $i++) {
                $this->query[$key][] = implode(',', array_values($value[$i]));
              }
            } else {
              for ($i = 0; $i < count($value); $i++) {
                $filter_index = ($key === 'filter') ? $i + 1 : '';
                for ($j = 0; $j < count($value[$i]); $j++) {
                  $this->query["{$key}{$filter_index}"][] = (count($value[$i] > 1)) ? implode(',', array_values($value[$i][$j])) : $value[$i][$j];
                }
              }
            }
          } else {
            $this->query[$key] = $value;
          }
        }
        $temp2 = http_build_query($this->query,NULL,'&',PHP_QUERY_RFC3986);
        // http://php.net/manual/en/function.http-build-query.php#111819
        $temp2 = preg_replace('/%5B[0-9]+%5D/simU', '', $temp2);
        $this->queryURI = "{$this->baseURI}{$this->endPoint}?$temp2";
      }
      /* $this->error = $this->queryURI;
      $this->writeLoop();
      $this->error = ''; */
      return $this;
    }

    public function call() {
      if ($this->primaryKey !== NULL) {
        $this->queryURI .= '/' . $this->primaryKey;
      }
      // clear $this->headers
      $this->headers = [];
      // Use api key to generate security token
      $this->timeVal = time();
      // Generate the security key using the REQUEST_URI
      $stringSearchVal = (strpos($this->baseURI, 'testing') === FALSE) ? '.com' : '.net';
      $this->token = hash_hmac('sha256', substr($this->queryURI, strpos($this->queryURI, $stringSearchVal) + 4) . $this->timeVal, $this->privateKey);
      $this->ch = curl_init();
      curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
      curl_setopt($this->ch, CURLOPT_URL, $this->queryURI);
      curl_setopt($this->ch, CURLOPT_FAILONERROR, TRUE);
      //CURLOPT_SSL_VERIFYPEER set to FALSE for testing only
      curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
      // CURLOPT_SSL_VERIFYHOST disabled for testing only
      curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
      /**
      ** How to create and store cacert.pem:
      ** http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
      ** gd_bundle-g2-g1.crt can be downloaded from the GoDaddy Repository:
      ** https://certs.godaddy.com/repository/
      ** if it is not the sole bundle it should be appended to the bundle in use
      **/
      // curl_setopt($this->ch, CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . "cacert.pem");
      // Set the authorization headers");
      $this->headers[] = 'Authorization: Basic ' . base64_encode("{$this->username}:{$this->publicKey}");
      $this->headers[] = "auth: {$this->token}";
      $this->headers[] = "time: {$this->timeVal}";
      if ($this->payload !== NULL) {
        $this->jsonData = json_encode($this->payload);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->jsonData);
        $this->headers[] = 'Content-Type: application/json';
        $this->headers[] = 'Content-Length: ' . strlen($this->jsonData);
      }
      curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
      curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
      //self::safe_print_r(curl_getinfo($this->ch)); //return FALSE;
      $this->result = curl_exec($this->ch);
      //self::safe_print_r(curl_getinfo($this->ch)); //return FALSE;
      if (!$this->result) {
        // TODO Implement retry with backoff here
        $this->result = curl_error($this->ch);
        self::responseError();
        curl_close($this->ch);
        throw new \Exception($this->error);
      }
      return $this->result;
    }
  }