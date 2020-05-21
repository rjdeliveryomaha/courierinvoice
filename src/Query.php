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
    protected $result;
    protected $responseHeaders;

    public function __construct($options, $data=[])
    {
      try {
        parent::__construct($options, $data);
      } catch (\Exception $e) {
        throw $e;
      }
      // username, publicKey, and privateKey will be set in CommonFunctions
      $this->baseURI = (self::test_bool($this->options['testMode']) === true) ?
        $this->options['testURL'] : 'https://rjdeliveryomaha.com';

      $this->validTables = [ 'clients', 'config', 'contract_locations', 'contract_runs', 'c_run_schedule',
        'dispatchers', 'drivers', 'invoices', 'o_clients', 'routes', 'route_schedule', 'route_tickets',
        'schedule_override', 'tickets', 'webhooks' ];
    }

    private function responseError()
    {
      switch (curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE)) {
        case 200:
          $this->error = null;
          break;
        case 301:
        case 302:
          if (
            !array_key_exists('location', $this->responseHeaders) ||
            count($this->responseHeaders['location']) == 0
          ) {
            $this->error = 'Code 301. Please contact support.';
          } else {
            $locations = [];
            $this->error = 'Resourse moved to ';
            for ($i = 0; $i < count($this->responseHeaders['location']); $i++) {
              if (strpos($this->responseHeaders['location'][$i], '?') === false) {
                $locations[] = $this->responseHeaders['location'][$i];
              } else {
                $locations[] =
                substr($this->responseHeaders['location'][$i], 0, strpos($this->responseHeaders['location'][$i], '?'));
              }
            }
            $this->error .= implode(' or ', $locations);
          }
          break;
        case 400:
          $this->error = 'Invalid Request URI.';
          break;
        case 401:
          $this->error = 'Invalid API credentials.';
          break;
        case 403:
          $this->error = 'API credentials not defined.';
          break;
        case 404:
          $this->error = 'Server Failed to locate record.';
          break;
        case 405:
          $this->error = "Method {$this->method} no allowed. Allow: {$this->responseHeaders['Allow'][0]}";
          break;
        case 422:
          $temp = json_decode($this->result, true);
          $this->error = "{$temp['message']}:";
          foreach ($temp['details'] as $key => $value) {
            $this->error .= " $key: $value";
          }
          break;
        case 500:
          $this->error = 'Internal Server Error.';
          break;
        case 503:
          $this->error = 'Service temporarily unavailable.';
          break;
        default:
          $this->error = curl_error($this->ch);
          break;
      }
      if ($this->enableLogging !== false) self::writeLoop();
    }

    private function orderParams()
    {
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
      if (isset($this->queryParams['join'])) {
        $paramList[] = 'join';
      }
      $this->queryParams = array_merge(array_flip($paramList), $params);
    }

    public function buildURI()
    {
      if (!in_array($this->endPoint, $this->validTables)) {
        $this->error = "Invalid End Point\n";
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if (
        $this->primaryKey === null && (strtoupper($this->method) === 'PUT' ||
        strtoupper($this->method) === 'DELETE')
      ) {
        $this->error = "Primary Key Not Set For End Point {$this->endPoint}\n";
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if (
        (strtoupper($this->method) === 'POST' || strtoupper($this->method) === 'PUT') &&
        ($this->payload === null || (is_array($this->payload) && empty($this->payload)))
      ) {
        $this->error = "No Payload Provided\n";
        if ($this->enableLogging !== false) self::writeLoop();
        throw new \Exception($this->error);
      }
      if (!is_array($this->queryParams) || empty($this->queryParams)) {
        $this->queryURI = "{$this->baseURI}/v2/records/{$this->endPoint}";
      } else {
        // make sure that the 'include' or 'exclude' key precedes the 'filter' key
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
                  $this->query["{$key}{$filter_index}"][] = (count($value[$i]) > 1) ?
                    implode(',', array_values($value[$i][$j])) : $value[$i][$j];
                }
              }
              self::writeLoop();
            }
          }
        }
        if (is_array($this->query)) {
          $encodedQuery = http_build_query($this->query,null,'&',PHP_QUERY_RFC3986);
          // http://php.net/manual/en/function.http-build-query.php#111819
          $encodedQuery = preg_replace('/%5B[0-9]+%5D/simU', '', $encodedQuery);
        } else {
          $encodedQuery = '';
        }
        if (isset($this->queryParams['order'])) {
          for ($i = 0; $i < count($this->queryParams['order']); $i++) {
            if (strlen($encodedQuery) > 0) $encodedQuery .= '&';
            $orderParam = preg_replace('/\s+/', '', $this->queryParams['order'][$i]);
            $encodedQuery .= "order={$orderParam}";
          }
          if (isset($this->queryParams['page'])) {
            $paramTest = false;
            if (strpos(',', $this->queryParams['page']) !== false) {
              $testVal = explode(',', $this->queryParams['page']);
              $paramTest = count($testVal) === 2 && is_numeric($testVal[0]) && is_numeric($testVal[1]);
            } else {
              $paramTest = is_numeric($this->queryParams['page']);
            }
            if ($paramTest === true) $encodedQuery .= "&page={$this->queryParams['page']}";
          }
        }
        if (isset($this->queryParams['join'])) {
          for ($i = 0; $i < count($this->queryParams['join']); $i++) {
            if (strlen($encodedQuery) > 0) $encodedQuery .= '&';
            $joinParam = preg_replace('/\s+/', '', $this->queryParams['join'][$i]);
            $encodedQuery .= "join={$joinParam}";
          }
        }
        $this->queryURI = "{$this->baseURI}/v2/records/{$this->endPoint}?{$encodedQuery}";
      }
      return $this;
    }

    private function readResponseHeaders($curl, $header)
    {
      $len = strlen($header);
      $header = explode(':', $header, 2);
      if (count($header) < 2) return $len;
      $this->responseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);
      return $len;
    }

    public function call()
    {
      if ($this->primaryKey !== null) {
        $this->queryURI .= '/' . $this->primaryKey;
      }
      $this->headers = [];
      $this->responseHeaders = [];
      $this->timeVal = time();
      $this->token =
        hash_hmac('sha256', $this->after($this->baseURI, $this->queryURI) . $this->timeVal, $this->privateKey);
      $this->ch = curl_init();
      curl_setopt($this->ch, CURLOPT_URL, $this->queryURI);
      curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, strtoupper($this->method));
      curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($this->ch, CURLOPT_CAINFO, __DIR__ . DIRECTORY_SEPARATOR . 'cacert.pem');
      curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array(&$this, 'readResponseHeaders'));
      // CURLOPT_SSL_VERIFYPEER set to false for testing only
      $ssl_verifypeer = true;
      // CURLOPT_SSL_VERIFYHOST disabled for testing only
      $ssl_verifyhost = 2;
      if ($this->options['testMode'] === true) {
        $ssl_verifypeer = substr($this->options['testURL'], 0, 5) === 'https';
        $ssl_verifyhost = (substr($this->options['testURL'], 0, 5) === 'https') ? 2 : 0;
      }
      curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
      curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);
      $this->headers[] = 'Authorization: Basic ' . base64_encode("{$this->username}:{$this->publicKey}");
      $this->headers[] = "auth: {$this->token}";
      $this->headers[] = "time: {$this->timeVal}";
      if ($this->payload !== null) {
        $this->jsonData = json_encode($this->payload);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->jsonData);
        $this->headers[] = 'Content-Type: application/json';
        $this->headers[] = 'Content-Length: ' . strlen($this->jsonData);
      }
      curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
      $this->result = curl_exec($this->ch);
      self::responseError();
      curl_close($this->ch);
      if ($this->error !== null) {
        throw new \Exception($this->error);
      }
      return $this->result;
    }
  }
