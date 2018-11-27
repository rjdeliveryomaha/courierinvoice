function ajax_template(callMethod, url, returnType, postData=false) {
  if (postData === false) {
    return $.ajax({
      method: callMethod.toUpperCase(),
      url: url,
      dataType: returnType,
      shouldRetry: function( jqXHR, retryCount, requestMethod ) {
        if (retryCount < 20) {
          return $.Deferred(function(jqXHR) {
            setTimeout(function() {
                jqXHR.resolve(true);
            }, (250 * retryCount));
          }).promise();
        }
      }
    });
  } else {
    return $.ajax({
      method: callMethod.toUpperCase(),
      url: url,
      data: postData,
      dataType: returnType,
      shouldRetry: function( jqXHR, retryCount, requestMethod ) {
        if (retryCount < 20) {
          return $.Deferred(function(jqXHR) {
            setTimeout(function() {
                jqXHR.resolve(true);
            }, (250 * retryCount));
          }).promise();
        }
      }
    });
  }
}