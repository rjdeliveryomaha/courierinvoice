<?php
function is_sec_session_started() {
  if ( php_sapi_name() !== 'cli' ) {
    if ( version_compare(phpversion(), '5.4.0', '>=') ) {
      // http://php.net/manual/en/function.session-status.php#116634
      // return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
      return session_status() === 2 ? true : false;
    } else {
      return session_id() === '' ? false : true;
    }
  }
  return FALSE;
}

function sec_session_start() {
  $session_name = 'a_named_session';
  $domain = '.domain.com';
  $lifetime = 12 * 60 * 60;
  $path = '/';
  $https = false;
  //Prevent JavaScript access to session ID
  $httponly = true;
  //Set the sesion name
  session_name($session_name);
  //Set session parameters
  session_set_cookie_params(
    $lifetime,
    $path,
    $domain,
    $https,
    $httponly
  );
  session_start();
  session_regenerate_id();
}
