let deferredPrompt;
const btnAdd = document.getElementById("btnAdd");
window.addEventListener('beforeinstallprompt', (e) => {
  // Prevent Chrome 67 and earlier from automatically showing the prompt
  e.preventDefault();
  // Stash the event so it can be triggered later.
  deferredPrompt = e;
  // show the button
  btnAdd.style.display = "block";
  return false;
});

document.getElementById("btnAdd").addEventListener("click", (e) => {
  e.preventDefault();
  deferredPrompt.prompt();
  // Wait for the user to respond to the prompt
  deferredPrompt.userChoice
    .then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        btnAdd.style.display = "none";
        console.log('User accepted the A2HS prompt');
      } else {
        console.log('User dismissed the A2HS prompt');
        btnAdd.style.display = "block";
      }
      deferredPrompt = null;
    });
});

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

$(document).ready(function() {
  // Login function
  $(".login").click(function(e){
    e.preventDefault();
    $(this).prop("disabled", true);
    $(".message").html("");
    if ($(this).parents("form").find(".clientID").val() != '' && $(this).parents("form").find(".upw").val() != ''){
      $(this).parents("form").find(".message").append('<span class="ellipsis">.</span>');
      let clientID = $(this).parents("form").find(".clientID").val(),
          upw = $(this).parents("form").find(".upw").val(),
          mobile = $(this).parents("form").find(".mobile").val(),
          $ele =  $(this).parents("form").find(".ellipsis"),
          forward = true,
          dots = window.setInterval(function() {
            if (forward === true) {
              $ele.append("..");
              forward = $ele.text().length < 21 && $ele.text().length != 1;
            }
            if (forward === false) {
              $ele.text($ele.text().substr(0,$ele.text().length - 2));
              forward = $ele.text().length === 1;
            }
          }, 500),
          loginAttempt = ajax_template("POST", "./login.php", "html", { clientID:clientID, upw:upw, mobile:mobile, noSession:'1', formKey:$("#formKey").val() })
      .done(result => {
        if (result.search("clients") === -1 && result.search("drivers") === -1) {
          $(this).parents("form").find(".message").append(result);
          let bruteCheck  = ajax_template("POST", "./login.php", "html", { clientID:clientID, brute:1 })
          .done(result2 => {
            clearInterval(dots);
            $(".ellipsis").remove();
            $(this).parents("form").find(".message").append(result2);
            $(this).prop("disabled", false);
            $(this).removeClass("red");
          })
          .fail((jqXHR, status, error) => {
            clearInterval(dots);
            $(".ellipsis").remove();
            $(this).parents("form").find(".message").text(error);
            $(this).prop("disabled", false);
            $(this).removeClass("red");
          });
        } else {
          clearInterval(dots);
          window.location = result;
        }
      })
      .fail((jqXHR, status, error) => {
        clearTimeout(dots);
        $(".ellipsis").remove();
        $(this).parents("form").find(".message").text(error);
        $(this).prop("disabled", false);
        $(this).removeClass("red");
      });
      return false;
    }
  });
});
