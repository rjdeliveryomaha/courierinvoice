(function(user_scripts, undefined) {
  pause = duration => { return new Promise(resolve => setTimeout(resolve, duration)) };

  user_scripts.fetch_template = async({ url, postData = {}, method = "POST", retry = 0 }) => {
    if (!url) throw new Error("URL not defined");
    let fetchOptions = {
        method: method.toUpperCase()
      };
    if (Object.keys(postData).length > 0) {
      fetchOptions.headers = { "Content-Type": "application/json" }
      fetchOptions.body = JSON.stringify(postData);
    }
    try {
      return await fetch(url, fetchOptions);
    } catch(err) {
      retry++;
      if (retry === 20) throw err;
      await pause(250 * retry);
      return await rjdci.fetch_template({ url, postData, method, retry });
    }
  }
}(window.user_scripts = window.user_scripts || {}));
/*!
* domready (c) Dustin Diaz 2014 - License MIT
* https://github.com/ded/domready
*/
!function(e,t){typeof module!="undefined"?module.exports=t():typeof define=="function"&&typeof define.amd=="object"?define(t):this[e]=t()}("domready",function(){var e=[],t,n=typeof document=="object"&&document,r=n&&n.documentElement.doScroll,i="DOMContentLoaded",s=n&&(r?/^loaded|^c/:/^loaded|^i|^c/).test(n.readyState);return!s&&n&&n.addEventListener(i,t=function(){n.removeEventListener(i,t),s=1;while(t=e.shift())t()}),function(t){s?setTimeout(t,0):e.push(t)}})

domready(() => {
  let deferredPrompt;
  const btnAdd = document.getElementById("btnAdd");
  window.addEventListener('beforeinstallprompt', eve => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    eve.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = eve;
    // show the button
    btnAdd.style.display = "block";
    return false;
  });

  document.getElementById("btnAdd").addEventListener("click", e => {
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
  if (document.querySelector("#showLogin")) {
    document.querySelector("#showLogin").addEventListener("click", eve => {
      e.preventDefault();
      let form = document.querySelector("#loginForm");
      if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
        window.location = "/mobileLogin";
      } else {
        form.style.display = (form.style.display === "none") ? "inline" : "none";
      }
    })
  }
  // Login function
  document.querySelector("#login").addEventListener("click", async eve => {
    eve.preventDefault();
    if (document.querySelector("#clientID").value === "" || document.querySelector("#upw").value === "") return false;
    eve.target.disabled = true;
    document.querySelector("#message").innerHTML = '<span class="ellipsis">.</span>';
    let postData = {},
      ele =  document.querySelector("#message .ellipsis"),
      forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.slice(0, -2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
    Array.from(document.querySelectorAll("#loginForm input")).forEach(input => {
      postData[input.getAttribute("name")] = input.value;
    });
    await user_scripts.fetch_template({ url: "./login.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(async data => {
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if (data === "/clients" || data === "/drivers") {
        window.location = data;
        throw new Error("Login Successful");
      } else {
        let bruteCheck = { clientID: postData.clientID, brute: 1 };
        return await user_scripts.fetch_template({ url: "./login.php", postData: bruteCheck });
      }
    })
    .then(bruteCall => {
      if (typeof bruteCall === "undefined") throw new Error("Result Undefined");
      if (bruteCall.ok) {
        return bruteCall.text();
      } else {
        throw new Error(bruteCall.status + " " + bruteCall.statusText);
      }
    })
    .then(bruteResult => {
      let message = bruteResult || document.querySelector("#message").innerHTML;
      throw new Error(message);
    })
    .catch(error => {
      clearInterval(dots);
      document.querySelector("#message").innerHTML = error.message;
      eve.target.disabled = false;
    });
  });
});
