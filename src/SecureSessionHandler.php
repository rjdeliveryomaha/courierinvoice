<?php

namespace rjdeliveryomaha\courierinvoice;

class SecureSessionHandler extends \SessionHandler
{

  public static function start_session($config)
  {
    try {
      static::create_session($config);
    } catch(Exception $e) {
      throw $e;
    }
    // Make sure the session hasn't expired, and destroy it if it has
    if (static::validateSession()) {
      // Check to see if the session is new or a hijacking attempt
      if(!static::preventHijacking())	{
        $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        static::regenerate_session();
        // Give a 5% chance of the session id changing on any request
      } elseif (rand(1, 100) <= 5) {
        static::regenerate_session();
      }
    } else {
      $_SESSION['error'] = '1';
      throw new \Exception('Session Error');
    }
    if (!isset($_SESSION['formKey'])) $_SESSION['formKey'] = mt_rand();
  }

  public static function outputKey()
  {
    return $_SESSION['formKey'];
  }

  public static function destroySession()
  {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params['path'], $params['domain'],
      $params['secure'], $params['httponly']
    );
    $formKey = $_SESSION['formKey'] ?? false;
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION = [ 'formKey' => $formKey ];
  }

  public static function newKey($config)
  {
    try {
      static::create_session($config);
    } catch(Exception $e) {
      throw $e;
    }
    $_SESSION['formKey'] = mt_rand();
    return $_SESSION['formKey'];
  }

  public static function regenerate_session()
  {
    // If this session is obsolete it means there already is a new id
    if (isset($_SESSION['OBSOLETE']) && $_SESSION['OBSOLETE'] === true) return;
    // Set current session to expire in 10 seconds
    $_SESSION['OBSOLETE'] = true;
    $_SESSION['EXPIRES'] = time() + 10;
    // Create new session without destroying the old one
    session_regenerate_id(false);
    // Grab current session ID and close both sessions to allow other scripts to use them
    $new_session = session_id();
    session_write_close();
    // Set session ID to the new one, and start it back up again
    session_id($new_session);
    session_start();
    // Now unset the obsolete and expiration values for the session we want to keep
    unset($_SESSION['OBSOLETE'], $_SESSION['EXPIRES']);
  }

  protected static function create_session($config)
  {
    if (session_status() === PHP_SESSION_ACTIVE) return false;
    if (ini_set('session.use_only_cookies', 1) === false) throw new \Exception('Session Error: use only cookies failed');
    if (ini_set('session.use_strict_mode', 1) === false) throw new \Exception('Session Error: use strict mode failed');
    $secure = $config['secure'] ?? 0;
    if (ini_set('session.cookie_secure', $secure) === false) throw new \Exception('Session Error: cookie secure failed');
    if (ini_set('session.use_trans_sid', 0) === false) throw new \Exception('Session Error: use trans id failed');
    $domain = $config['domain'] ?? false;
    if (!$domain || $domain === '') $domain = $_SERVER['SERVER_NAME'];
    $session_name = $config['session_name'] ?? false;
    if (!$session_name || $session_name === '' || is_numeric($session_name)) throw new \Exception('Session Error: Invalid session name');
    $lifetime = $config['lifetime'] ?? 8 * 60 * 60;
    $path = $config['path'] ?? '/';
    session_name($session_name);
    //Set session parameters
    session_set_cookie_params(
      $lifetime,
      $path,
      $domain,
      $secure,
      true
    );
    session_start();
  }

  protected static function preventHijacking()
  {
    if(!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent'])) return false;

    if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR']) return false;

    if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']) return false;

    return true;
  }

  protected static function validateSession()
  {
    $alternateHijackingTest = $config['alternateHijackingTest'] ?? false;

    if (filter_var($alternateHijackingTest, FILTER_VALIDATE_BOOLEAN) === true) {
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['formKey']) || ((int)$_POST['formKey'] !== $_SESSION['formKey'])) return false;
      }

      if (isset($_SESSION['formKey'])) $_SESSION['formKey']++;
    } else {
      if (isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES'])) return false;

      if (isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time()) return false;
    }
    return true;
  }
}
