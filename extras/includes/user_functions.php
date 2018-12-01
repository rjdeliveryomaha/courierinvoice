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
