<?php
function is_sec_session_started() {
  if ( php_sapi_name() !== 'cli' ) {
    if ( version_compare(phpversion(), '5.4.0', '>=') ) {
      // http://php.net/manual/en/function.session-status.php#116634
      // return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
      return session_status() === 2 ? TRUE : FALSE;
    } else {
      return session_id() === '' ? FALSE : TRUE;
    }
  }
  return FALSE;
}

function sec_session_start() {
  $lifetime = 12 * 60 * 60;
  $path = '/';
  $session_name = 'ChangeMe';
  $domain = '.your.domain.com';
  // Are we https?
  $secure = 1;
  // Prevent JavaScript access to session ID
  $httponly = TRUE;
  //Force sessions to only use cookies
  if (ini_set('session.use_only_cookies', 1) === FALSE) {
    header("Location:  ../error.php?err=Could not initiate a safe session (ini_set)");
    exit;
  }
  //Set the sesion name
  session_name($session_name);
  //Set session parameters
  session_set_cookie_params(
    $lifetime,
    $path,
    $domain,
    $secure,
    $httponly
  );
  session_start();
  session_regenerate_id();
}
?>
