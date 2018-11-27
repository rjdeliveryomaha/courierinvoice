const applicationServerKey = "BKbaTbqZgyaBzmMVMzwMXNY3WkRgo49H63naexnqhN7T8Ow8UbTM1430bvAW8CIUdZ1rBIhXgYb6J97qyXCk28I=";
function resetPushUI() {
  const pushButton = document.querySelector('.fab__push');
  console.log("test for button...");
  if (!pushButton) {
    console.warn('no button');
    return;
  }
  if ($("a[data-id='ticketQuery']").length > 0 || $("a[data-id='orgTickets']").length > 0) return $(".fab__push").parents("li").hide();
  console.log("button found.");
  console.log("test for notification permission...");
  if (Notification.permission === 'denied') {
    try {
      toast("<p>Notifications Disabled.</p><p>User Declined.</p>");
    } catch(error) {
      console.log('Unable to get permission to notify.', error);
    }
    changePushStatus("false");
    return;
  }
  console.log("notification permission good.");
  console.log("test for push manager...");
  if (!('PushManager' in window)) {
    try {
      toast("<p>Notifications Disabled.</p><p>No Push Support.</p>");
    } catch(error) {
      console.log('Unable to get permission to notify.', error);
    }
    changePushStatus("incompatible");
    return;
  }
  console.log("push manager found.");
  console.log("test for show notification...");
  if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
    try {
      toast("<p>Notifications Disabled.</p><p>No Show Support.</p>");
    } catch(error) {
      console.log('Unable to get permission to notify.', error);
    }
    changePushStatus("incompatible");
    return;
  }
  console.log("show notification found.");
  console.log("test for local storage...");
  if (!lsTest()) {
    try {
      toast("<p>Notifications Disabled.</p><p>No Local Storage Support.</p>");
    } catch(error) {
      console.log('Unable to get permission to notify.', error);
    }
    changePushStatus("incompatible");
    return;
  }
  console.log("local storage found.");
  console.log("test isUserSubscribed...");
  if (isUserSubscribed()) {
    console.log("wait for serviceWorker...");
    navigator.serviceWorker.ready
    .then(registration => {
      console.log("getSubscription");
      registration.pushManager.getSubscription()
      .then(subscription => {
        console.log("test subscription");
        if (!subscription) {
          console.log("no subscription, wait for serviceWorker again");
          navigator.serviceWorker.ready
          .then(registration => {
            console.log("subscribe user");
            return registration.pushManager.subscribe({
              userVisibleOnly: true, //Always show notification when received
              applicationServerKey: urlBase64ToUint8Array(applicationServerKey) //Public key for push application
            });
          })
          .then(subscription => {
            console.log("save subscription");
            let oldToken = fetchToken();
            let newToken = subscription.endpoint.substr(subscription.endpoint.lastIndexOf("/") + 1);
            if (newToken !== oldToken) {
              saveSubscriptionID(subscription);
              deleteTokenFromServer(oldToken);
              toast("<p>Subscription Updated</p>");
            } else {
              toast("<p>Notifications Enabled.</p>");
            }
            return changePushStatus("true");
          });
        } else {
          console.log("subscription exists, but is it current?");
          let oldToken = fetchToken();
          let newToken = subscription.endpoint.substr(subscription.endpoint.lastIndexOf("/") + 1);
          if (newToken !== oldToken) {
            saveSubscriptionID(subscription);
            deleteTokenFromServer(oldToken);
            toast("<p>Subscription Updated</p>");
            console.log("token updated");
          } else {
            toast("<p>Notifications Enabled.</p>");
            console.log("token stable");
          }
          return changePushStatus("true");
        }
      })
      .catch(error => {
        console.log("An error occurred while retrieving token.", error);
        setTokenSentToServer("0");
        changePushStatus("false");
        try {
          toast("<p>Error: " + error.message + "</p>");
          console.log(error.message);
        } catch(error) {
          console.log(error.message);
        }
      });
    });
  } else {
    console.log("user not subscribed");
    let oldToken = fetchToken();
    if (oldToken) deleteTokenFromServer(oldToken);
    localStorage.clear();
    changePushStatus("false");
    toast("<p>Notifications Disabled.</p>");
  }
}
function isUserSubscribed() { return localStorage.getItem("subscribed") === "1"; }
function subscribeUser(subscribed) { return localStorage.setItem("subscribed", subscribed); }
function isTokenSentToServer() { return localStorage.getItem("sentToServer") === "1"; }
function setTokenSentToServer(sent) { return localStorage.setItem("sentToServer", sent); }
function storeToken(token) { return localStorage.setItem("token", token); }
function fetchToken() { return localStorage.getItem("token"); }
function lsTest(){
  let testVal = "test";
  try {
    localStorage.setItem(testVal, testVal);
    localStorage.removeItem(testVal);
    return true;
  } catch(e) {
    return false;
  }
}
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}
function changePushStatus(status) {
  const pushButton = document.querySelector('.fab__push');
  switch(status) {
    case "true":
      pushButton.dataset.checked = status;
      pushButton.disabled = false;
      pushButton.classList.remove('red');
      pushButton.classList.add('active', 'green');
      pushButton.innerHTML="On";
    break;
    case "false":
      pushButton.dataset.checked = status;
      pushButton.disabled = false;
      pushButton.classList.remove('active', 'green', 'red');
      pushButton.innerHTML="Off";
    break;
    case 'incompatible':
      pushButton.dataset.checked = "false";
      pushButton.disabled = true;
      pushButton.classList.remove('active', 'green');
      pushButton.classList.add('red');
      pushButton.innerHTML="N/A";
    break;
  }
}
function deleteTokenFromServer(token) {
  return ajax_template("POST", "./pushNotificationHandler.php", "text", { action: "remove", subscription: token, formKey: $("#formKey").val() })
  .done(result => {
    console.log(result);
    if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
  })
  .fail((jqXHR, status, error) => {
    try {
      toast('<p>' + error + '</p>');
    } catch(e) {
      alert(error);
    }
  });
}
function saveSubscriptionID(subscription) {
  const key = subscription.getKey('p256dh');
  const token = subscription.getKey('auth');
  const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
  // detect which page is visible for displaying errors
  let currentPageID,
      currentPageIndex = $(".menu .menu__list__active").find("a").attr("data-value");
  $(".page").each(function() {
    if ($(this).attr("data-index") === currentPageIndex) currentPageID = "#" + $(this).attr("id");
  });
  let subscription_info = {
    "endpoint": subscription.endpoint,
    "publicKey": key ? btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))) : null,
    "authToken": token ? btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey("auth")))) : null,
    contentEncoding,
  };
  let attempt = ajax_template("POST", "./pushNotificationHandler.php", "text", { action: "add", subscription: JSON.stringify(subscription_info), formKey: $("#formKey").val() })
  .done(result => {
    console.log(result);
    storeToken(subscription_info.endpoint.substr(subscription_info.endpoint.lastIndexOf("/") + 1));
    setTokenSentToServer("1");
    subscribeUser("1");
    if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
  })
  .fail((jqXHR, status, error) => {
    $(currentPageID).prepend('<p class="error">' + error + '</p>');
  });
}
function deleteSubscriptionID(subscription) {
  // detect which page is visible for displaying errors
  let currentPageID;
  let currentPageIndex = $(".menu .menu__list__active").find("a").attr("data-value");
  $(".page").each(function() {
    if ($(this).attr("data-index") === currentPageIndex) currentPageID = "#" + $(this).attr("id");
  });
  let attempt = ajax_template("POST", "./pushNotificationHandler.php", "text", { action: "remove", subscription: subscription.endpoint, formKey: $("#formKey").val() })
  .done(result => {
    console.log(result);
    setTokenSentToServer("0");
    if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
  })
  .fail((jqXHR, status, error) => {
    $(currentPageID).prepend('<p class="error">' + error + '</p>');
  });
}
function requestPermission() {
  try {
    toast("Notifications Pending.");
  } catch(error) {
    console.log('Requesting permission...');
  }
  Notification.requestPermission()
  .then(() => {
    console.log('Notification permission granted.');
    subscribeUser("1");
    resetUI();
  })
  .catch((error) => {
    subscribeUser("0");
    try {
      toast("<p>Notifications Disabled.</p><p>User Declined.</p>");
    } catch(error) {
      console.log('Unable to get permission to notify.', error);
    }
  });
}
function deleteToken() {
  console.log("deleteToken");
  navigator.serviceWorker.ready
  .then(serviceWorkerRegistration => serviceWorkerRegistration.pushManager.getSubscription())
  .then(subscription => {
    if (!subscription) {
      setTokenSentToServer("0");
      subscribeUser("0");
    } else {
      deleteTokenFromServer(subscription.endpoint)
      .then(() => {
        console.log('Token deleted.');
        setTokenSentToServer("0");
        subscribeUser("0");
      })
      .catch(error => {
        console.log('Unable to delete token. ', error);
      });
    }
  })
  .catch(error => {
    try {
      toast("<p>Error retrieving Instance token</p><p>" + error.message + "</p>");
      console.log('Error retrieving Instance ID token. ', error);
    } catch(error) {
      console.log('Error retrieving Instance ID token. ', error);
    }
  });
}
function subscribePush() {
  console.log("wait for serviceWorker");
  navigator.serviceWorker.ready
  .then(registration => {
    console.log('processing...');
    if (!registration.pushManager) {
      return toast("<p>Your browser doesn't support push notification.</p>");
    }
    if (Notification.permission === "denied") {
      return toast("<p>Notifications Disabled By User</p>");
    }
    return registration.pushManager.subscribe({
      userVisibleOnly: true, //Always show notification when received
      applicationServerKey: urlBase64ToUint8Array(applicationServerKey) //Public key for push application
    })
    .then(subscription => {
      if (!subscription) return false;
      toast("<p>Subscribed successfully.</p>");
      subscribeUser("1");
      saveSubscriptionID(subscription);
      changePushStatus("true");
    })
    .catch(error => {
      console.error(error);
      toast("<p>Subscription Error:</p><p>" + error + "</p>");
      changePushStatus("false");
      subscribeUser("0");
      console.error("Push notification subscription error: ", error);
    });
  })
  .catch(error => {
    console.error(error);
    toast("<p>Subscription Error:</p><p>" + error + "</p>");
    changePushStatus("false");
    subscribeUser("0");
    console.error("Push notification subscription error: ", error);
  });
}
// Unsubscribe the user from push notifications
function unsubscribePush() {
  navigator.serviceWorker.ready
  .then(registration => {
    //Get `push subscription`
    registration.pushManager.getSubscription()
    .then(subscription => {
      //If no `push subscription`, then return
      if (subscription === null) {
        return;
      }
      //Unsubscribe `push notification`
      subscription.unsubscribe()
      .then(() => {
        toast('Unsubscribed<br>successfully.');
        deleteSubscriptionID(subscription);
        changePushStatus("false");
      })
      .catch(error => {
        console.error(error);
      });
    })
    .catch(error => {
      console.error("Failed to unsubscribe push notification." + error);
    });
  });
}
navigator.serviceWorker.addEventListener("pushsubscriptionchange", event => {
  navigator.serviceWorker.ready
  .then(registration => {
    return registration.pushManager.subscribe({
      userVisibleOnly: true, //Always show notification when received
      applicationServerKey: urlBase64ToUint8Array(applicationServerKey) //Public key for push application
    });
  })
  .catch(error => {
    console.log(error);
    toast("<p>Failed to subscribe</p>");
  })
  .then(subscription => {
    let oldToken = fetchToken();
    let newToken = subscription.endpoint.substr(subscription.endpoint.lastIndexOf("/") + 1);
    deleteToken(subscription.endpoint.substr(0, subscription.endpoint.lastIndexOf("/") + 1) + oldToken);
    saveSubscriptionID(subscription);
    toast("<p>Subscription Updated</p>");
  })
  .catch(error => {
    console.log(error);
  });
});
navigator.serviceWorker.addEventListener("message", event => {
  const payload = event.data;
  let options = {},
      toastMessage = '';
  if (typeof(payload.update) !== "undefined") options.eleClass = payload.update;
  if (typeof(payload.timestamp) !== "undefined")  options.datatime = payload.timestamp;
  options.title = payload.title;
  toastMessage = "<p>" + payload.title + "</p><p>" + payload.body + "</p>";
  try {
    toast(toastMessage, options);
  } catch(error) {
    console.log(toastMessage);
  }
});
$(document).ready( () => {
  const pushButton = document.querySelector('.fab__push');
  if (pushButton) {
    pushButton.addEventListener('click', function () {
      return (pushButton.dataset.checked === "true") ? unsubscribePush() : subscribePush();
    });
  }
  $(document).on("click", ".toast__container>div", function() {
    let filterArray = $(this).attr("class").split(/(?=[A-Z])/);
    // title should only be either 0 or 2 words
    let titleFilter = ($(this).attr("title") === "") ? [] : $(this).attr("title").split(/\s+/);
    if (filterArray[0] === "ticket") {
      switch (filterArray[2]) {
        case "Dispatched":
          $("a.nav").each(function() {
            if ($(this).attr("data-id") === "onCall") {
              $(this).trigger("click");
              refreshOnCall();
            }
          });
          if (titleFilter.length > 0) {
            $("#dispatch").find(".tNumDisplay").each(function() {
              if ($(this).text() === titleFilter[1]) {
                $(this).parents(".tickets").remove();
                let oldCount = Number($(".dispatchCount").text()) - 1;
                countDispatch(oldCount);
              }
            });
          }
        break;
        case "Received":
          $("a.nav").each(function() {
            if ($(this).attr("data-id") === "dispatch") {
              $(this).trigger("click");
              refreshDispatch();
            }
          });
        break;
        case "Transferred":
          $("a.nav").each(function() {
            if ($(this).attr("data-id") === "transfers") {
              $(this).trigger("click");
              refreshTransfers();
            }
            (filterArray[1] === "Contract") ? refreshRoute() : refreshOnCall();
          });
        break;
        case "Updated":
          let targetLink = (filterArray[1] === "Contract") ? "route" : "onCall";
          $("a.nav").each(function() {
            if ($(this).attr("data-id") === targetLink) {
              $(this).trigger("click");
              refreshTransfers();
            }
          });
          (filterArray[1] === "Contract") ? refreshRoute() : refreshOnCall();
        break;
      }
    }
    $(this).remove();
  });
});