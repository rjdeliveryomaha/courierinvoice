<?php
  namespace rjdeliveryomaha\courierinvoice;

  use rjdeliveryomaha\courierinvoice\CommonFunctions;

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
      $this->baseURI = ($this->options['testMode'] === true) ? $this->options['testURL'] : 'https://rjdeliveryomaha.com';
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
      if ($this->enableLogging !== false) self::writeLoop();
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

        default: return false;
      }
    }

    private function orderParams() {
      $paramList = [];
      $params = $this->queryParams;
      if (isset($this->queryParams['exclude'])) {
        $paramList[] = 'exclude';
      }
      if (isset($this->queryParams['include'])) {
        $paramList[] = 'include';
      }
      if (isset($this->queryParams['filter'])) {
        $paramList[] = 'filter';
      }
      if (isset($this->queryParams['order'])) {
        $paramList[] = 'order';
      }
      if (isset($this->queryParams['page'])) {
        $paramList[] = 'page';
      }
      $this->queryParams = array_merge(array_flip($paramList), $params);
    }

    public function buildURI() {
      if (!in_array($this->endPoint, $this->validTables)) {
        $this->error = "Invalid End Point\n";
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ($this->primaryKey === NULL && (strtoupper($this->method) === 'PUT' || strtoupper($this->method) === 'DELETE')) {
        $this->error = "Primary Key Not Set For End Point {$this->endPoint}\n";
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if ((strtoupper($this->method) === 'POST' || strtoupper($this->method) === 'PUT') && ($this->payload === NULL || (is_array($this->payload) && empty($this->payload)))) {
        $this->error = "No Payload Provided\n";
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if (!is_array($this->queryParams) || empty($this->queryParams)) {
        $this->queryURI = "{$this->baseURI}/v2/records/{$this->endPoint}";
      } else {
        // make sure that the 'include' or 'exclude' key preceeds the 'filter' key
        self::orderParams();
        foreach ($this->queryParams as $key => $value) {
          if ($key === 'exclude' && (!isset($this->queryParams['include']) || empty($this->queryParams['include']))) {
            $this->query['exclude'] = implode(',', $this->queryParams['exclude']);
          }
          if ($key === 'include') {
            $this->query['include'] = implode(',', $this->queryParams['include']);
          } elseif ($key === 'filter') {
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
          }
        }
        $encodedQuery = http_build_query($this->query,NULL,'&',PHP_QUERY_RFC3986);
        // http://php.net/manual/en/function.http-build-query.php#111819
        $encodedQuery = preg_replace('/%5B[0-9]+%5D/simU', '', $encodedQuery);
        if (isset($this->queryParams['order'])) {
          for ($i = 0; $i < count($this->queryParams['order']); $i++) {
            $encodedQuery .= "&order={$this->queryParams['order'][$i]}";
          }
          if (isset($this->queryParams['page'])) {
            $paramTest = false;
            if (strpos(',', $this->queryParams['page']) !== false) {
              $testVal = explode(',', $this->queryParams['page']);
              $paramTest = count($testVal) === 2 && is_numeric($testVal[0]) && is_numeric($testVal[1]);
            }
            if (is_numeric($this->queryParams['page']) || $paramTest === true) $encodedQuery .= "&page={$this->queryParams['page']}";
          }
        }
        $this->queryURI = "{$this->baseURI}/v2/records/{$this->endPoint}?$encodedQuery";
      }
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
      $this->token = hash_hmac('sha256', $this->after($this->baseURI, $this->queryURI) . $this->timeVal, $this->privateKey);
      $this->ch = curl_init();
      curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
      curl_setopt($this->ch, CURLOPT_URL, $this->queryURI);
      curl_setopt($this->ch, CURLOPT_FAILONERROR, true);
      if (strpos($this->baseURI, 'testing') !== false) {
        //CURLOPT_SSL_VERIFYPEER set to false for testing only
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        // CURLOPT_SSL_VERIFYHOST disabled for testing only
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        /**
        ** How to create and store cacert.pem:
        ** http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
        **/
        // curl_setopt($this->ch, CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . "cacert.pem");
      } else {
        //CURLOPT_SSL_VERIFYPEER set to false for testing only
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
        // CURLOPT_SSL_VERIFYHOST disabled for testing only
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
        /**
        ** How to create and store cacert.pem:
        ** http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
        **/
        curl_setopt($this->ch, CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . "cacert.pem");
      }
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
      curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
      $this->result = curl_exec($this->ch);
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
