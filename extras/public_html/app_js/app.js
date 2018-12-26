/*!
 * Swipe 2.2.11
 *
 * Brad Birdsall
 * Copyright 2013, MIT License
 *
*/
!function(a,b){"function"==typeof define&&define.amd?define([],function(){return a.Swipe=b(),a.Swipe}):"object"==typeof module&&module.exports?module.exports=b():a.Swipe=b()}(this,function(){function Swipe(c,d){"use strict";function e(){I.addEventListener?(N.removeEventListener("touchstart",R,!1),N.removeEventListener("mousedown",R,!1),N.removeEventListener("webkitTransitionEnd",R,!1),N.removeEventListener("msTransitionEnd",R,!1),N.removeEventListener("oTransitionEnd",R,!1),N.removeEventListener("otransitionend",R,!1),N.removeEventListener("transitionend",R,!1),a.removeEventListener("resize",R,!1)):a.onresize=null}function f(){I.addEventListener?(I.touch&&N.addEventListener("touchstart",R,!1),d.draggable&&N.addEventListener("mousedown",R,!1),I.transitions&&(N.addEventListener("webkitTransitionEnd",R,!1),N.addEventListener("msTransitionEnd",R,!1),N.addEventListener("oTransitionEnd",R,!1),N.addEventListener("otransitionend",R,!1),N.addEventListener("transitionend",R,!1)),a.addEventListener("resize",R,!1)):a.onresize=Q}function g(a){var b=a.cloneNode(!0);N.appendChild(b),b.setAttribute("data-cloned",!0),b.removeAttribute("id")}function h(a){if(null!=a)for(var b in a)d[b]=a[b];J=N.children,M=J.length;for(var h=0;h<J.length;h++)J[h].getAttribute("data-cloned")&&M--;J.length<2&&(d.continuous=!1),I.transitions&&d.continuous&&J.length<3&&(g(J[0]),g(J[1]),J=N.children),K=new Array(J.length),L=c.getBoundingClientRect().width||c.offsetWidth,N.style.width=J.length*L*2+"px";for(var i=J.length;i--;){var j=J[i];j.style.width=L+"px",j.setAttribute("data-index",i),I.transitions&&(j.style.left=i*-L+"px",p(i,O>i?-L:O<i?L:0,0))}d.continuous&&I.transitions&&(p(m(O-1),-L,0),p(m(O+1),L,0)),I.transitions||(N.style.left=O*-L+"px"),c.style.visibility="visible",e(),f()}function i(){E||(d.continuous?o(O-1):O&&o(O-1))}function j(){E||(d.continuous?o(O+1):O<J.length-1&&o(O+1))}function k(a,b,c){d.callback&&d.callback(a,b,c)}function l(a,b){d.transitionEnd&&d.transitionEnd(a,b)}function m(a){return(J.length+a%J.length)%J.length}function n(){var a=O;return a>=M&&(a-=M),a}function o(a,b){if(a="number"!=typeof a?parseInt(a,10):a,O!==a){if(I.transitions){var c=Math.abs(O-a)/(O-a);if(d.continuous){var e=c;c=-K[m(a)]/L,c!==e&&(a=-c*J.length+a)}for(var f=Math.abs(O-a)-1;f--;)p(m((a>O?a:O)-f-1),L*c,0);a=m(a),p(O,L*c,b||P),p(a,0,b||P),d.continuous&&p(m(a-c),-L*c,0)}else a=m(a),r(O*-L,a*-L,b||P);O=a,G(function(){k(n(),J[O],c)})}}function p(a,b,c){q(a,b,c),K[a]=b}function q(a,b,c){var d=J[a],e=d&&d.style;e&&(e.webkitTransitionDuration=e.MozTransitionDuration=e.msTransitionDuration=e.OTransitionDuration=e.transitionDuration=c+"ms",e.webkitTransform="translate("+b+"px,0)translateZ(0)",e.msTransform=e.MozTransform=e.OTransform="translateX("+b+"px)")}function r(a,b,c){if(!c)return void(N.style.left=b+"px");var e=+new Date,f=setInterval(function(){var g=+new Date-e;if(g>c)return N.style.left=b+"px",(D||d.autoRestart)&&u(),l(n(),J[O]),void clearInterval(f);N.style.left=(b-a)*(Math.floor(g/c*100)/100)+a+"px"},4)}function s(){(D=d.auto||0)&&(A=setTimeout(j,D))}function t(){D=0,clearTimeout(A)}function u(){t(),s()}function v(){t(),E=!0}function w(){E=!1,u()}function x(a){return/^mouse/.test(a.type)}function y(){t(),c.style.visibility="",N.style.width="",N.style.left="";for(var a=J.length;a--;){I.transitions&&q(a,0,0);var b=J[a];if(b.getAttribute("data-cloned")){b.parentElement.removeChild(b)}b.style.width="",b.style.left="",b.style.webkitTransitionDuration=b.style.MozTransitionDuration=b.style.msTransitionDuration=b.style.OTransitionDuration=b.style.transitionDuration="",b.style.webkitTransform=b.style.msTransform=b.style.MozTransform=b.style.OTransform=""}e(),Q.cancel()}d=d||{};var z,A,B={},C={},D=d.auto||0,E=!1,F=function(){},G=function(a){setTimeout(a||F,0)},H=function(a,b){function c(){e&&clearTimeout(e)}function d(){var d=this,f=arguments;c(),e=setTimeout(function(){e=null,a.apply(d,f)},b)}b=b||100;var e=null;return d.cancel=c,d},I={addEventListener:!!a.addEventListener,touch:"ontouchstart"in a||a.DocumentTouch&&b instanceof DocumentTouch,transitions:function(a){var b=["transitionProperty","WebkitTransition","MozTransition","OTransition","msTransition"];for(var c in b)if(void 0!==a.style[b[c]])return!0;return!1}(b.createElement("swipe"))};if(c){var J,K,L,M,N=c.children[0],O=parseInt(d.startSlide,10)||0,P=d.speed||300;d.continuous=void 0===d.continuous||d.continuous,d.autoRestart=void 0!==d.autoRestart&&d.autoRestart;var Q=H(h),R={handleEvent:function(a){if(!E){switch(a.type){case"mousedown":case"touchstart":this.start(a);break;case"mousemove":case"touchmove":this.move(a);break;case"mouseup":case"mouseleave":case"touchend":this.end(a);break;case"webkitTransitionEnd":case"msTransitionEnd":case"oTransitionEnd":case"otransitionend":case"transitionend":this.transitionEnd(a);break;case"resize":Q()}d.stopPropagation&&a.stopPropagation()}},start:function(a){var b;x(a)?(b=a,a.preventDefault()):b=a.touches[0],B={x:b.pageX,y:b.pageY,time:+new Date},z=void 0,C={},x(a)?(N.addEventListener("mousemove",this,!1),N.addEventListener("mouseup",this,!1),N.addEventListener("mouseleave",this,!1)):(N.addEventListener("touchmove",this,!1),N.addEventListener("touchend",this,!1))},move:function(a){var b;if(x(a))b=a;else{if(a.touches.length>1||a.scale&&1!==a.scale)return;d.disableScroll&&a.preventDefault(),b=a.touches[0]}C={x:b.pageX-B.x,y:b.pageY-B.y},void 0===z&&(z=!!(z||Math.abs(C.x)<Math.abs(C.y))),z||(a.preventDefault(),t(),d.continuous?(q(m(O-1),C.x+K[m(O-1)],0),q(O,C.x+K[O],0),q(m(O+1),C.x+K[m(O+1)],0)):(C.x=C.x/(!O&&C.x>0||O===J.length-1&&C.x<0?Math.abs(C.x)/L+1:1),q(O-1,C.x+K[O-1],0),q(O,C.x+K[O],0),q(O+1,C.x+K[O+1],0)))},end:function(a){var b=+new Date-B.time,c=Number(b)<250&&Math.abs(C.x)>20||Math.abs(C.x)>L/2,e=!O&&C.x>0||O===J.length-1&&C.x<0;d.continuous&&(e=!1);var f=Math.abs(C.x)/C.x;z||(c&&!e?(f<0?(d.continuous?(p(m(O-1),-L,0),p(m(O+2),L,0)):p(O-1,-L,0),p(O,K[O]-L,P),p(m(O+1),K[m(O+1)]-L,P),O=m(O+1)):(d.continuous?(p(m(O+1),L,0),p(m(O-2),-L,0)):p(O+1,L,0),p(O,K[O]+L,P),p(m(O-1),K[m(O-1)]+L,P),O=m(O-1)),k(n(),J[O],f)):d.continuous?(p(m(O-1),-L,P),p(O,0,P),p(m(O+1),L,P)):(p(O-1,-L,P),p(O,0,P),p(O+1,L,P))),x(a)?(N.removeEventListener("mousemove",R,!1),N.removeEventListener("mouseup",R,!1),N.removeEventListener("mouseleave",R,!1)):(N.removeEventListener("touchmove",R,!1),N.removeEventListener("touchend",R,!1))},transitionEnd:function(a){parseInt(a.target.getAttribute("data-index"),10)===O&&((D||d.autoRestart)&&u(),l(n(),J[O]))}};return h(),s(),{setup:h,slide:function(a,b){t(),o(a,b)},prev:function(){t(),i()},next:function(){t(),j()},restart:u,stop:t,getPos:n,disable:v,enable:w,getNumSlides:function(){return M},kill:y}}}var a="object"==typeof self&&self.self===self&&self||"object"==typeof global&&global.global===global&&global||this,b=a.document;return(a.jQuery||a.Zepto)&&function(a){a.fn.Swipe=function(b){return this.each(function(){a(this).data("Swipe",new Swipe(a(this)[0],b))})}}(a.jQuery||a.Zepto),Swipe});
// End Swipe
(function () {
  let width = screen.width,
    height = screen.height;
  setInterval(() => {
    if (screen.width !== width || screen.height !== height) {
      width = screen.width;
      height = screen.height;
      $(window).trigger("resolutionchange");
    }
  }, 250);
}());

function showLogin() {
  $("#confirmLogin").removeClass("hide");
  centerLoginForm();
  $(window).trigger("loggedout");
}

function reloadPage() {
  location.reload();
}

function disableApp() {
  let header = document.querySelector("header"),
      menuHeader = document.querySelector(".menu__header");
  if (header === undefined || menuHeader === undefined || header.classList.contains("loggedout")) {
    return false;
  }
  header.classList.add("loggedout");
  menuHeader.classList.add("loggedoutHeader");
  // use jQuery to disable all button on the page
  $("#appContainer").find("button").each(function() {
    $(this).prop("disabled", true);
  });
}

$(window).bind("resolutionchange", function() {
  setTimeout(() => { fixDeadRunButton(); centerLoginForm(); }, 500);
});

$(window).bind("loggedout", function() {
  setTimeout(disableApp, 500);
});

$(window).bind("loggedin", function() {
  setTimeout(enableApp, 500);
});

function debug(val) {
  if (typeof(val) === "undefined") {
    console.log(typeof(val));
  } else {
    console.log(typeof(val), val.length, val);
  }
}
// menu control
(function () {
  "use strict";

  let menuIconElement = document.querySelector(".header__icon");
  let menuElement = document.querySelector(".menu");
  let menuOverlayElement = document.querySelector(".menu__overlay");
  let menuLink = document.querySelectorAll("a.nav");
  let subscriptionButton = document.querySelector(".fab__push");
  //Menu click event
  menuIconElement.addEventListener("click", showMenu, false);
  menuOverlayElement.addEventListener("click", hideMenu, false);
  for (let i = 0; i < menuLink.length; i++) {
    menuLink[i].addEventListener("click", hideMenu, false);
  }
  if (subscriptionButton !== null) subscriptionButton.addEventListener("click", hideMenu, false);
  // menuElement.addEventListener("transitionend", onTransitionEnd, false);

  //To show menu
  function showMenu() {
    let canvasTest = document.getElementById("signature-pad");
    if (canvasTest !== null) return false;
    let newUpdate = document.getElementById("newUpdate");
    if (newUpdate !== null) {
      if (!newUpdate.classList.contains("hide")) newUpdate.classList.add("hide");
    }
    menuElement.style.transform = "translateX(0)";
    menuElement.classList.add("menu--show");
    menuOverlayElement.classList.add("menu__overlay--show");
  }

  //To hide menu
  function hideMenu() {
    menuElement.style.transform = "translateX(-110%)";
    menuElement.classList.remove("menu--show");
    menuOverlayElement.classList.remove("menu__overlay--show");
    // menuElement.addEventListener("transitionend", onTransitionEnd, false);
  }

  /*Swipe from edge to open menu*/

  /* let touchStartPoint, touchMovePoint;

  //`TouchStart` event to find where user start the touch
  document.body.addEventListener("touchstart", function(event) {
    touchStartPoint = event.changedTouches[0].pageX;
    touchMovePoint = touchStartPoint;
  }, false);

  //`TouchMove` event to determine user touch movement
  document.body.addEventListener("touchmove", function(event) {
    touchMovePoint = event.touches[0].pageX;
    if (touchStartPoint < 10 && touchMovePoint > 30) {
      menuElement.style.transform = "translateX(0)";
    }
  }, false);

  function onTransitionEnd() {
    if (touchStartPoint < 10) {
      menuElement.style.transform = "translateX(0)";
      menuOverlayElement.classList.add("menu__overlay--show");
      menuElement.removeEventListener("transitionend", onTransitionEnd, false);
    }
  } */
})();

(function (exports) {
  "use strict";
  // Use arrays to make date display pretty
  let months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
  let days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  //To show notification
  function toast(msg, options) {
    if (!msg) return;

    let toastContainer = document.querySelector(".toast__container");

    options = options || {};

    options.title = options.title || "";

    options.time = options.time || 4000;

    options.eleClass = options.eleClass || "toast__msg";

    options.datatime = options.datatime || new Date().getTime();

    let toastMsg = document.createElement("div");

    toastMsg.className = options.eleClass;
    toastMsg.title = options.title;
    let d = new Date(Number(options.datatime))
    let minutes = d.getMinutes();
    minutes = (minutes < 10) ? "0" + minutes.toString() : minutes.toString();
    toastMsg.innerHTML = msg + "<p>" + days[d.getDay()] + " " + months[d.getMonth()] + " " + d.getDate() + "</p><p>" + d.getHours() + ":" + minutes + "</p>";
    toastMsg.setAttribute("data-time", options.datatime);

    if (options.eleClass !== "toast__msg") {
      for (let i = 0; i < toastContainer.children.length; i++) {
        if (toastContainer.children[i].classList.contains(options.eleClass)) {
          let elem = toastContainer.children[i];
          elem.parentNode.removeChild(elem);
        }
      }
      toastContainer.appendChild(toastMsg);
    } else {
      toastContainer.appendChild(toastMsg);
    }

    //Show toast for 3secs and hide it
    setTimeout(() => {
      if (toastMsg.classList.contains("toast__msg")) toastMsg.classList.add("toast__msg--hide");
    }, options.time);

    // add onclick event to remove toastMsg
    // this will be handled in app.js with jQuery
    // toastMsg.onclick = function() { this.parentNode.removeChild(this); };

    //Remove the element after hiding
    // Wait one second longer than the passed value and loop over all of the children.
    setTimeout(() => {
      for (let i = 0; i < toastContainer.children.length; i++) {
        let elem = toastContainer.children[i];
        if (elem.classList.contains("toast__msg")) elem.parentNode.removeChild(elem);
      }
    }, options.time + 1000);
  }

  exports.toast = toast; //Make this method available in global
})(typeof window === "undefined" ? module.exports : window);
// Navigation
window.mySwipe = new Swipe(document.getElementById("slider"), {
  startSlide: 0,
  speed: 300,
  // auto: 300,
  draggable: false,
  continuous: true,
  autoRestart: false,
  disablescroll: false,
  stopPropagation: false,
  callback: function(index, elem, dir) {
    // runs at slide change
    // direction: 1 for left or backward, -1 for right or forward
  },
  transitionEnd: function(index, elem) {
    // runs at the end of a slide transition
    $(".menu__list li").each(function() {
      $(this).removeClass("menu__list__active");
    });
    $(".page").each(function() {
      if ($(this).prop("id") === elem.id) {
        let titleText;
        let buttonTitles = [ "Route", "On Call", "Dispatch", "Transfers" ];
        // if the link text has a span in it or links to div#route.page
        // use the text to make button to refresh the corresponding div.page
        let eleTest = $('a.nav[data-id="' + $(this).prop("id") + '"').html().split("<");
        if (buttonTitles.indexOf(eleTest[0]) !== - 1) {
          titleText = '<button type="button" id="refresh' + eleTest[0].replace(/\s/g, '') + '">' + eleTest[0] + '</button>';
          titleText += (eleTest[0] === "Route") ? "" : "<" + eleTest[1] + "<" + eleTest[2];
        } else {
          titleText = $('a.nav[data-id="' + $(this).prop("id") + '"').html();
        }
        $(".pageTitle").html(titleText);
        $('a.nav[data-id="' + $(this).prop("id") + '"').parents("li").addClass("menu__list__active");
      }
    });
    scroll(0,0);
    $(window).trigger('pageChange');
  }
});

function assignLinkValues() {
  let eles = document.getElementsByClassName("nav");
  for (let i = 0; i < eles.length; i++) {
    eles[i].setAttribute("data-value", i);
  }
}
// End menu control
// offline
(function () {
  "use strict";

  const header = document.querySelector("header");
  const menuHeader = document.querySelector(".menu__header");

  //After DOM Loaded
  document.addEventListener("DOMContentLoaded", function(event) {
    //On initial load to check connectivity
    if (!navigator.onLine) {
      updateNetworkStatus();
    }

    window.addEventListener("online", updateNetworkStatus, false);
    window.addEventListener("offline", updateNetworkStatus, false);
  });

  //To update network status
  function updateNetworkStatus() {
    if (navigator.onLine) {
      header.classList.remove("app__offline");
      menuHeader.style.background = "#1E88E5";
    }
    else {
      toast("You are now offline..");
      header.classList.add("app__offline");
      menuHeader.style.background = "#9E9E9E";
    }
  }
})();
// END offline
// isTarget is called by datalist validation
function isTarget(ele) {
  let targets = [ "billTo", "dispatchedTo", "dispatchedByUser", "shippingCountry", "billingCountry", "pCountry", "dCountry" ];
  for (let i = 0; i < targets.length; i++) {
    if (ele.hasClass(targets[i])) return true;
  }
  return false;
}
// count organization memebers for invoice page
function disableButtonsInvoices() {
  let howMany = 0;
  $("#orgInvoices .orgMember").each(function() {
    if ($(this).is(":checked")) howMany++;
  });
  $("#compareMembers").prop("disabled", howMany < 2);
  if ($("#compareMembers").is(":disabled")) $("#compareMembers").prop("checked", false);

  if (howMany === 0) {
    $("#range, #submitSingle").prop("disabled", true).prop("title", "Select a member to continue");
    $("#orgInvoices").find(".noticeRow").show();
  } else {
    $("#orgInvoices").find(".noticeRow").hide();
    $("#range, #submitSingle").prop("title", "");
    if ($("#single").is(":checked")) {
      $("#submitSingle").prop("disabled", false);
    } else if ($("#multi").is(":checked")) {
      $("#range").prop("disabled", false);
    }
  }
}
// count organization members for ticket page
function disableButtonsTickets() {
  let howMany = 0;
  $("#orgTickets .orgMember").each(function() {
    if ($(this).is(":checked")) howMany++;
  });
  $("#compareMembersTickets").prop("disabled", !(howMany > 1 && $("#display").val() === "chart"));
  if ($("#compareMembersTickets").is(":disabled")) $("#compareMembersTickets").prop("checked", false);
  (howMany === 0 && $("#ticketNumber").val() === "") ? $("#orgTickets").find(".noticeRow").show() : $("#orgTickets").find(".noticeRow").hide()
}

if (!String.prototype.convert12to24) {
  String.prototype.convert12to24 = function convert12to24() {
    let hours = Number(this.match(/^(\d+)/)[1]),
        minutes = Number(this.match(/:(\d+)/)[1]),
        AMPM = this.match(/\s(.*)$/)[1],
        sHours,
        sMinutes;
    if(AMPM.toUpperCase() == "PM" && hours<12) {
      hours = hours+12;
    }
    if(AMPM.toUpperCase() == "AM" && hours==12) {
      hours = hours-12;
    }
    sHours = (hours<10) ? `0${hours.toString()}` : hours.toString();
    sMinutes = (minutes<10) ? `0${minutes.toString()}` : minutes.toString();
    return sHours + ":" + sMinutes;
  }
}

if (!String.prototype.convert24to12) {
  String.prototype.convert24to12 = function convert24to12() {
    let hours = Number(this.match(/^(\d+)/)[1]),
        minutes = Number(this.match(/:(\d+)/)[1]),
        AMPM = "am",
        sHours,
        sMinutes;
    if (hours > 12) {
      hours = hours - 12;
      AMPM = "pm";
    }
    if (hours === 0) {
      hours = 12;
    }
    sHours = hours.toString();
    sMinutes = (minutes<10) ? `0${minutes.toString()}` : minutes.toString();
    return sHours + ":" + sMinutes + " " + AMPM;
  }
}

function disable_scroll() {
  $("body").bind("touchmove", function(e){e.preventDefault()});
}

function enable_scroll() {
  $("body").unbind("touchmove");
}
// https://stackoverflow.com/a/11986374
// Finds y value of given object
// offset the position by the height of the header element
function findPos(obj) {
  let curtop = 0;
  if (obj.offsetParent) {
    do {
      curtop += obj.offsetTop;
    } while (obj = obj.offsetParent);
    return curtop - document.getElementsByTagName("header")[0].offsetHeight;
  }
}

function fixDeadRunButton() {
  let h1 = $(".cancelRun:first").height();
  $(".deadRun").each(function() {
    $(this).text("Dead Run");
    let h2 = $(this).height();
    if (h2 > h1) {
      $(this).text("D. Run");
    }
  });
}

function centerLoginForm() {
  let pageWidth = $(document).width(),
      eleWidth = $("#confirmLogin").width(),
      diff = (pageWidth - eleWidth) / 2;
  $("#confirmLogin").css("left", diff + "px");
}

function sortRoute() {
  let $container = $("#route"),
      $items = $container.children(".sortable").get();
  $items.sort((a,b) => {
    return ($(a).find(".timing").text().convert12to24() > $(b).find(".timing").text().convert12to24()) ? 1 : -1;
  });
  $.each($items, (i, x) => { $container.append(x); });
}

function refreshRoute() {
  $("#route").html('<div class="showbox"><!-- New spinner from http://codepen.io/collection/HtAne/ --><div class="loader"><svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/></svg></div></div>');
  scrollTo(0,0);
  let attempt = ajax_template("POST", "./refreshRoute.php", "html", { formKey: $("#formKey").val() })
  .done((result) => {
    if (result.indexOf("Session Error") !== -1) {
      $("#confirmLogin").find("#function").val("refreshRoute");
      showLogin();
      return false;
    }
    setTimeout(() => {
      $("#route").html(result);
      fixDeadRunButton();
      sortRoute();
    }, 2000);
  })
  .fail((jqXHR, status, error) => {
    $("#route").html('<p class="center error">' + error + "</p>");
  });
}

function countOnCallTickets(oldCount) {
  let newCount = $("#on_call .tickets").length;
  if (newCount > oldCount) {
    $(".alert").addClass("onCallAlert").text("!");
    $("#newUpdate").removeClass("hide");
  }
  if (newCount === 0) $(".alert").removeClass("onCallAlert");
  let classList = $(".alert").attr("class").split(/\s+/);
  if (classList.length === 1) $(".alert").text("");
  $(".ticketCount").text(newCount);
}

function sortOnCall() {
  let $container = $("#on_call"),
      $items = $container.children(".sortable").get();
  $items.sort((a,b) => {
    return ($(a).find(".timing").text().convert12to24() > $(b).find(".timing").text().convert12to24()) ? 1 : -1;
  });
  $.each($items, (i, x) => { $container.append(x); });
}

function refreshOnCall(ticketCount = $(".ticketCount:first").text()) {
  $("#on_call").html('<div class="showbox"><!-- New spinner from http://codepen.io/collection/HtAne/ --><div class="loader"><svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/></svg></div></div>');
  scrollTo(0,0);
  let attempt = ajax_template("POST", "./refreshOnCall.php", "html", { formKey: $("#formKey").val() })
  .done((result) => {
    if (result.indexOf("Session Error") !== -1) {
      $("#confirmLogin").find("#function").val("refreshOnCall");
      showLogin();
      return false;
    }
    setTimeout(() => {
      $("#on_call").html(result);
      fixDeadRunButton();
      sortOnCall();
      countOnCallTickets(ticketCount);
    }, 2000);
  })
  .fail((jqXHR, status, error) => {
    $("#on_call").html('<p class="center error">' + error + "</p>");
  });
}

function countInitOnCall() {
  let newCount = $("#on_call .tickets").length;
  $(".ticketCount").text(newCount);
  if (newCount > 0) $(".alert").addClass("onCallAlert").text("!");
}

function countDispatch(oldCount) {
  let newCount = $("#dispatch .tickets").length;
  if (newCount > oldCount) $(".alert").addClass("dispatchAlert").text("!");
  if (newCount === 0) $(".alert").removeClass("dispatchAlert");
  let classList = $(".alert").attr("class").split(/\s+/);
  if (classList.length === 1) $(".alert").text("");
  $(".dispatchCount").text(newCount);
}

function refreshDispatch() {
  let oldCount = Number($(".dispatchCount").text()) - 1;
  $("#dispatch").html('<div class="showbox"><!-- New spinner from http://codepen.io/collection/HtAne/ --><div class="loader"><svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/></svg></div></div>');
  scrollTo(0,0);
  let attempt = ajax_template("POST", "./refreshDispatch.php", "html", { formKey: $("#formKey").val() })
  .done((result) => {
    if (result.indexOf("Session Error") !== -1) {
      $("#confirmLogin").find("#function").val("refreshDispatch");
      showLogin();
      return false;
    }
    setTimeout(() => {
      $("#dispatch").html(result);
      countDispatch(oldCount);
    }, 2000);
  })
  .fail((jqXHR, status, error) => {
    $("#dispatch").html('<p class="center error">' + error + "</p>");
  });
}

function countInitDispatch() {
  let newCount = $("#dispatch .tickets").length;
  $(".dispatchCount").text(newCount);
  if (newCount > 0) $(".alert").addClass("dispatchAlert").text("!");
}

function countTransferTickets(oldCount) {
  let newCount = $("#transfers .sortable").length;
  if (newCount > oldCount) {
    $(".alert").addClass("transfersAlert").text("!");
    $("#newUpdate").removeClass("hide");
  }
  if (newCount === 0) $(".alert").removeClass("transfersAlert");
  let classList = $(".alert").attr("class").split(/\s+/);
  if (classList.length === 1) $(".alert").text("");
  $(".transfersCount").text(newCount);
}

function refreshTransfers(transferCount = $(".transfersCount:first").text()) {
  $("#transfers").html('<div class="showbox"><!-- New spinner from http://codepen.io/collection/HtAne/ --><div class="loader"><svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/></svg></div></div>');
  scrollTo(0,0);
  let refreshTransfersAttempt = ajax_template("POST", "./refreshTransfers.php", "html", { formKey: $("#formKey").val() })
  .done((result) => {
    if (result.indexOf("Session Error") !== -1) {
      $("#confirmLogin").find("#function").val("refreshTransfers");
      showLogin();
      return false;
    }
    setTimeout(() => {
      $("#transfers").html(result);
      countTransferTickets(transferCount);
    }, 2000);
  })
  .fail((jqXHR, status, error) => {
    $("#transfers").html('<p class="center error">' + error + "</p>");
  });
}

function countInitTransfers() {
  let newCount = $("#transfers .sortable").length;
  $(".transfersCount").text(newCount);
  if (newCount > 0) $(".alert").addClass("transfersAlert").text("!");
}

function populatePage() {
  let funcs = [],
      results;
  $(".page").each(function() {
    if ($(this).attr("data-function") !== undefined && $(this).attr("data-function") !== "") {
      funcs.push($(this).attr("data-function"));
    }
  });
  let initAttemp = ajax_template("POST", "initApp.php", "html", { functions: funcs, formKey: $("#formKey").val() })
  .done(result => {
    let obj,
        breakFunction = false;
    try {
      obj = JSON.parse(result);
    } catch(e) {
      $(".page:first").html('<p class="center">' + e + "</p>");
      console.log(result);
      breakFunction = true;
    }
    if (breakFunction === true) return false;
    for (let i = 0; i < obj.length; i++) {
      $(".page").each(function() {
        if (Number($(this).attr("data-index")) === i) $(this).html(obj[i]);
      });
    }
    scrollTo(0,0);
    if ($("#route").length > 0) sortRoute();
    if ($("#on_call").length > 0) {
      sortOnCall();
      countInitOnCall();
    }
    if ($(".deadRun").length > 0) fixDeadRunButton();
    if ($("#dispatch").length > 0) countInitDispatch();
    if ($("#transfers").length > 0) countInitTransfers();
  })
  .fail((jqXHR, status, error) => {
    $(".page:first").html('<p class="center"><span class="error">Error ' + status + "</span>:" + error + "</p>");
  });
}

function enableApp() {
  $("header").removeClass("loggedout");
  $(".menu__header").removeClass("loggedoutHeader");
  $("#appContainer").find("button").each(function() {
    $(this).prop("disabled", false);
  });
}
$(document).ready(function() {
  let noData = false;
  // assign data-values to navigation links
  assignLinkValues();
  // only run this function if the login confirmation form is present indicating a client or driver is loged in
  if ($("#confirmLogin").length > 0) populatePage();

  $("a.nav").click(function() {
    mySwipe.slide($(this).attr("data-value"), 300);
  });
  // don't let the menu open if signature pad is open
  $(document).on("click", ".header__icon", function() {
    if ($("#sig").is(":visible")) return false;
  });

  $(document).on("click", ".refresh", function(){
    reloadPage();
  });

  $(document).on("keydown", "input[type='number']", function() {
    if ($(this).val() === "0") $(this).val("");
  });

  $(document).on("click", "button", function() {
    let $temp = $(this).addClass("red");
    setTimeout(() => { $temp.removeClass("red"); }, 3000);
  });

  $(document).on("click", "#refreshRoute, #refreshOnCall, #refreshDispatch, #refreshTransfers", function(e) {
    e.preventDefault();
    window[$(this).prop("id")]();
  });

  $("#confirm").click(function( e ) {
    e.preventDefault();
    let breakFunction = false;
    $("#login").find("input").each(function() {
      if ($(this).prop("id") !== "function" && $(this).val() === "") {
        breakFunction = true;
        $tempError = $(this).addClass("elementError");
        setTimeout(() => { $tempError.removeClass("elementError"); }, 3000);
      }
    });
    if (breakFunction === true) return false;
    $("#confirmMessage").html("<span class=\"ellipsis\">.</span>");
    let $ele = $("#confirmMessage").find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      }
      if (forward === false) {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let postData = {};
    postData.clientID = $("#uid").val();
    postData.upw = $("#upw").val();
    postData.mobile = $("#mobile").val();
    postData.noSession = 1;
    let fetchNewFormKey = ajax_template("POST", "./refreshFormKey.php", "text", { "noSession":1 })
    .done((result) => {
      if (result.indexOf("error") !== -1) {
        $("#confirmMessage").html(result);
        return false;
      }
      postData.formKey = result;
      let loginAttempt = ajax_template("POST", "../login.php", "html", postData)
      .done((result1) => {
        clearInterval(dots);
        if (result1.indexOf("error") === - 1 && result1 !== "Invalid Credentials") {
          $("#confirmMessage").html("User Confirmed");
          $(".formKey, #formKey").each(function() {
            $(this).val(result);
          });
          setTimeout(() => {
            $("#confirmMessage").html("");
            $("#confirmLogin").addClass("hide");
            if ($("#confirmLogin").find("#function").val() !== "") {
              let func = $("#confirmLogin").find("#function").val();
              $("#confirmLogin").find("#function").val("");
              window[func]();
            }
            $(window).trigger("loggedin");
          }, 1000);
        } else {
          $("#confirmMessage").html(result1);
          setTimeout(() => { $("#confirmMessage").html(""); }, 4000);
          return false;
        }
      })
      .fail((jqXHR, status, error) => {
        clearInterval(dots);
        $("#confirmMessage").html("<span class=\"error\">Error</span>: " + error);
        setTimeout(() => { $("#confirmMessage").html(""); }, 4000);
        return false;
      });
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $("#confirmMessage").html(error);
        return false;
    });
  });

  $("#cancel").click(function() {
    window.location = "./logout";
  });
  // active tickets
  $(document).on("client", "#ticketEditorSubmit", function(e) {
    e.preventDefault();
    $("#ticketEditor .container").html("<span class=\"ellipsis\">.</span>");
    let $ele = $("#ticketEditor .ellipsis"),
        forward = true,
        dots = setInterval(() => {
          if (forward === true) {
            $ele.append("..");
            forward = $ele.text().length < 21 && $ele.text().length != 1;
          } else {
            $ele.text($ele.text().substr(0,$ele.text().length - 2));
            forward = $ele.text().length === 1;
          }
        }, 500),
        dispatchedTo = $(this).parents("form").find(".driverID").val(),
        contract = $(this).parents("form").find(".contract").val(),
        formKey = $("#formKey").val();
    if (dispatchedTo === "" || dispatchedTo === null || dispatchedTo === "undefined") return false;
    let attempt = ajax_template("POST", "./activeTickets.php", "html", { dispatchedTo: dispatchedTo, contract: contract, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $("#ticketEditor").find(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $("#ticketEditor .container").html(result);
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $("#ticketEditor").find(".ellipsis").remove();
      $("#ticketEditor .message").find("#message").html("<p class=\"center ajaxError\"><span class=\"error\">Error</span>: " + error + "</p>");
      setTimeout(() => { $("#ticketEditor").find(".ajaxError").remove(); }, 4000);
    });
  });

  $(document).on("click", ".cancelTicketEditor", function() {
    $(this).parents(".tickets").find("table:first, button.ticketEditor").show();
    $(this).parents(".removableByEditor").remove();
  });

  $(document).on("click", ".ticketEditor", function() {
    let attempt = ajax_template("POST", "./enterTicket.php", "html", { ticket_index: $(this).attr("data-index"), ticketEditor: 1, formKey: $("#formKey").val() })
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $(this).parent(".tickets").find("table:first, button.ticketEditor").hide();
      $(this).parent(".tickets").append(result);
    })
    .fail((jqXHR, status, error) => {
      $(this).parent(".tickets").html('<p class="center">' + error + "</p>");
    });
  });

  $(document).on("click", "#ticketEditor .submitForm", function(e) {
    e.preventDefault();
    $(this).prop("disable", true);
    let breakFunction = false,
        checkboxes = ["repeatClient", "fromMe", "toMe", "dryIce", "pSigReq", "dSigReq", "d2SigReq"],
        requiredElements = ["pClient", "pAddress1", "pAddress2", "dClient", "dAddress1", "dAddress2", "dispatchedTo"],
        formdata = {};
    $(this).parents("form").find("input[name], select, textarea").each(function() {
      if (checkboxes.indexOf($(this).attr("name")) === -1 && $(this).prop("disabled") === false) {
        if ((($(this).prop("required") === true && $(this).attr("name") !== "requestedBy") || requiredElements.indexOf($(this).attr("name")) !== -1) && $(this).val() === "") {
          let $temp = $(this).addClass("elementError");
          setTimeout(() => { $temp.removeClass("elementError") }, 3000);
          breakFunction = true;
        } else {
          $(this).removeClass("elementError");
        }
        formdata[$(this).attr("name")] = $(this).val();
      } else if ($(this).prop("type") === "checkbox") {
        if ($(this).attr("name") === "repeatClient") {
          formdata[$(this).attr("name")] = 1 - $(this).is(":checked");
        } else {
          formdata[$(this).attr("name")] = 0 + $(this).is(":checked");
        }
      }
      formdata.formKey = $("#formKey").val();
    });
    if (formdata.dryIce === 1) {
      if (formdata.diWeight % 5 !== 0) {
        let $tempMessage = $(this).parents("form").find(".ticketError").text("Dry Ice in increments of 5 only.");
        let $tempError = $(this).parents("form").find(".diWeight").addClass("elementError");
        breakFunction = true;
        setTimeout(() => { $tempMessage.text(""); $tempError.removeClass("elementError"); }, 3000)
      } else if (formdata.diWeight === "0") {
        let $tempMessage = $(this).parents("form").find(".ticketError").text("Dry Ice must be non-zero.");
        let $tempError = $(this).parents("form").find(".diWeight").addClass("elementError");
        breakFunction = true;
        setTimeout(() => { $tempMessage.text(""); $tempError.removeClass("elementError"); }, 3000)
      }
    }
    // Replace html entity &quot; with double quote for JSON parsing
    if (formdata.transfers !== null && formdata.transfers !== "") formdata.transfers = $.parseJSON(formdata.transfers.replace(/&quot;/g,'"'));
    if (formdata.dispatchedTo.substr(formdata.dispatchedTo.lastIndexOf(" ") + 1) !== formdata.holder) {
      if (typeof(formdata.transfers) === "object") {
        formdata.transfers.push({ "holder":Number(formdata.holder), "receiver": Number(formdata.dispatchedTo.substr(formdata.dispatchedTo.lastIndexOf(" ") + 1)), "transferredBy": formdata.transferredBy, "timestamp": null });
      } else {
        formdata.transfers = [ { "holder":Number(formdata.holder), "receiver": Number(formdata.dispatchedTo.substr(formdata.dispatchedTo.lastIndexOf(" ") + 1)), "transferredBy": formdata.transferredBy, "timestamp": null } ];
      }
    }
    if (breakFunction === true) {
      $(this).prop("disabled", false);
      return false;
    }
    let attempt = ajax_template("POST", "./enterTicket.php", "html", formdata)
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if (result.indexOf("data-error") !== -1) {
        $(this).parents(".tickets").find(".ticketError").html(result);
      } else {
        $(this).parents(".tickets").html(result);
      }
    })
    .fail((jqXHR, status, error) => {
      $(this).parents(".tickets").find(".ticketError").html('<span class="center">' + error + "</span>");
    });
  });

  $(document).on("click", "#ticketEditor .editForm, #ticketEditor .confirmed", function(e) {
    e.preventDefault();
    let button = $(this);
    let workspace = button.parents(".tickets");
    button.prop("disabled", true);
    setTimeout(() => { button.prop("disabled", false); }, 5000);
    let targetForm = "#" + $(this).attr("form");
    let tempError;
    let formdata = {};
    $(this).parents(".tickets").find(targetForm + " input").each(function() {
      formdata[$(this).attr("name")] = ($(this).attr("type") === "checkbox") ? (($(this).is(":checked")) ? 1 : 0) : $(this).val();
    });
    formdata.formKey = $("#formKey").val();
    if (button.hasClass("editForm")) {
      let ticket_index = button.attr("form").match(/\d+/)[0];
      if (ticket_index === "" || ticket_index === null) {
        tempError = '<p class="center">Invalid Ticket Index</p>';
        workspace.find(".ticketError").html(tempError);
        setTimeout(() => { workspace.find(".ticketError").html(""); }, 3000);
      }
      let attempt = ajax_template("POST", "./enterTicket.php", "html", { ticket_index: ticket_index, ticketEditor: 1, formKey: $("#formKey").val() })
      .done((result) => {
        if (result.indexOf("Session Error") !== -1) return showLogin();
        workspace.html(result);
      })
      .fail((jqXHR, status, error) => {
        tempError = '<p class="center">Error: ' + error + "</p>";
        workspace.find(".ticketError").html(tempError);
        setTimeout(() => { workspace.find(".ticketError").html(""); }, 3000);
      });
    } else if (button.hasClass("confirmed")) {
      formdata.updateTicket = 1;
      let attempt = ajax_template("POST", "./enterTicket.php", "html", formdata)
      .done((result) => {
        if (result.indexOf("Session Error") !== -1) return showLogin();
        if (result === "remove") {
          workspace.prepend('<p class="center">Update Successful</p>').scrollTop($("header").outerHeight() + 5);
          setTimeout(() => { workspace.remove(); }, 3000);
        } else {
          workspace.before(result);
          workspace.prev(".tickets").prepend('<p class="center removable">Update Successful</p>').scrollTop($("header").outerHeight() + 5);
          workspace.remove();
          setTimeout(() => { $(".removable").remove(); }, 3000);
        }
      })
      .fail((jqXHR, status, error) => {
        workspace.prepend('<span class="center">' + error + "</span>");
      });
    } else {
      tempError = '<p class="center">Error: Invalid action</p>';
      workspace.find(".ticketError").html(tempError);
      setTimeout(() => { workspace.find(".ticketError").html(""); }, 3000);
      return false;
    }
  });

  $(document).on("click", "#clearTicketEditorResults", function() {
    $("#ticketEditor .container").html('<p class="center">Select Driver &amp; Ticket Type</p>');
  });
  // change password
  $(document).on("click", ".PWsubmit", function(e) {
    e.preventDefault();
    $(this).prop("disabled", true);
    $(this).closest(".PWform").find(".message").html("");
    let postData = {};
    $(this).closest(".PWform").find("input").each(function() {
      postData[$(this).attr("name")] = $(this).val();
    });
    postData.formKey = $("#formKey").val();
    let attempt = ajax_template("POST", "./changePW.php", "html", postData)
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $(this).closest(".PWform").find(".message").html(result);
      setTimeout(() => {
        $(this).closest(".PWform").find(".message").html("").end().find(".currentPw, .newPw1, .newPw2").val("").end().find(".currentPw").focus();
        $(this).prop("disabled", false);
        if (postData.flag !== "driver" && postData.flag !== "dispatch") {
          let $ele,
              pwError = false;
          switch(postData.flag) {
            case "daily":
              $ele = $("a[data-id='change_password']");
              pwError = (postData.newPw1 === "!Delivery1");
            break;
            case "admin":
              $ele = $("a[data-id='change_admin_password']");
              pwError = (postData.newPw1 === "!Delivery2");
            break;
            case "org":
              $ele = $("a[data-id='change_password']");
              pwError = (postData.newPw1 === "3Delivery!");
            break;
          }
          if (result.search("Password Updated") !== -1) {
            if (pwError === true) {
              button.closest(".page").find(".defaultWarning").removeClass("hide");
              $ele.find(".alert").text("!");
              $(".pageTitle .alert").text("!");
            } else {
              button.closest(".page").find(".defaultWarning").addClass("hide");
              $ele.find(".alert").text("");
              $(".pageTitle .alert").text("");
            }
          }
        }
      }, 4000);
    })
    .fail((jqXHR, status, error) => {
      button.prop("disabled", false);
      button.closest("form").find(".message").text(error);
    });
  });
  // datalist validation
  // Ensure input with "list" attribute only takes values from associated datalist
  $(document).on("change", "input[list]", function(){
    if (isTarget($(this))) {
      let goodVals = [];
      $("body").find("datalist#" + $(this).attr("list") + " option").each(function() {
        goodVals.push($(this).val());
      });
      if ($(this).val() !== "" && goodVals.indexOf($(this).val()) === -1) {
        let oldHolder = $(this).attr("placeholder") || "";
        let $temp = $(this).val("").addClass("elementError").attr("placeholder", "Selection Not On File").trigger("change").trigger("blur");
        setTimeout(() => { $temp.removeClass("elementError").attr("placeholder", oldHolder); }, 3000);
      }
    }
  }).change();
  // dispatch tickets
  $(document).on("click", "#dispatch .dTicket", function(e){
    e.preventDefault();
    let button = $(this);
    button.prop("disabled", true);
    let formID = button.attr("form");
    let workspace = button.parents(".tickets");
    let postData = {};
    postData.ticket_index = $(".ticket_index[form='" + formID + "']").val();
    postData.step = $(".step[form='" + formID + "']").val();
    postData.dispatchedTo = $(".dispatchedTo[form='" + formID + "']").val();
    postData.notes = $(".notes[form='" + formID + "']").val();
    postData.formKey = $("#formKey").val();
    if (postData.dispatchedTo === "") {
      workspace.prepend('<p class="center warning">Please Select Driver</p>');
      setTimeout(() => { workspace.find(".warning").remove(); button.prop("disabled", false); }, 3000 );
      return false;
    }
    let attempt = ajax_template("POST", "./updateStep.php", "html", postData)
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      workspace.prepend(result);
      if (result.indexOf("error") === -1) {
        setTimeout(refreshDispatch, 3000);
      }
    })
    .fail((jqXHR, status, error) => {
      workspace.prev(".spacer").text(errorThrown);
    });
  });
  // invoice query page
  $(document).on("touchstart", ".invoiceGraphContainer, .invoiceTable", function() {
    mySwipe.disable();
  });

  $(document).on("touchend", ".invoiceGraphContainer, .invoiceTable", function() {
    mySwipe.enable();
  });

  if ($("#invoices input[type='month']").length === 0) {
    noData = true;
    $("#useInvoice").prop("disabled", true);
  }

  if ($("#orgInvoices .orgMember").length > 0) {
    $("#orgInvoices .orgMember:first").parents("tfoot").append('<tr class="noticeRow"><td colspan="2">Select member to query</td></tr>')
  }

  $(document).on("click", "#single, #multi", function() {
    return ($(this).is(":checked"));
  });

  $(document).on("change", "#single, #multi", function() {
    disableButtonsInvoices();
    let recheck = [];
    $("#orgInvoices .orgMember").each(function() {
      if ($(this).is(":checked")) recheck.push($(this));
    });
    $("#orgInvoices .orgMember").prop("checked", false).trigger("change");
    let target = ($(this).prop("id") === "single") ? "multi" : "single";
    if ($(this).is(":checked")) {
      $("#" + target).prop("checked", false);
    }
    if (recheck.length > 0) recheck[0].prop("checked", true).trigger("change");
  }).change();

  $(document).on("change", "#orgInvoices .orgMember", function() {
    disableButtonsInvoices();
    let testVal = $(this).attr("data-value");
    if ($(this).is(":checked")) {
      if ($("#single").is(":checked")) {
        $("#orgInvoices .orgMember").each(function() { $(this).prop("checked", $(this).attr("data-value") === testVal); } );
        $("#singleInvoiceQuery").find($(".removable")).remove();
        $("#submitSingle").before('<input type="hidden" class="removable" name="clientID[]" value="' + testVal + '" />');
      } else if ($("#multi").is(":checked")) {
        $("#range").before('<input type="hidden" class="removable" name="clientID[]" value="' + testVal + '" />');
      }
    } else {
      $("#queryForms").find("input.removable[value='" + testVal + "']").remove();
    }
  }).change();

  $(document).on("change", "#useInvoice", function(){
    $(this).parents("#singleInvoiceQuery").find("#invoiceNumber").prop("disabled", !$(this).is(":checked")).end().find(".dateIssuedMonth").prop("disabled", $(this).is(":checked")).prop("required", !$(this).is(":checked")).val("");
  }).change();

  $(document).on("change", "#compareInvoices", function(){
    ($(this).is(":checked") || $(this).val() === "1") ? $(this).parent("td").attr("title", "") : $(this).parent("td").attr("title", "Range limited to 6 months");
  }).change();

  $(document).on("click", "#singleInvoice, #rangeInvoice, #submitSingle, #range", function( e ) {
    e.preventDefault();
    let breakFunction = false;
    let thisButton = $(this);
    thisButton.prop("disabled", true);
    let workspace = ($(this).attr("id") === "singleInvoice" || $(this).attr("id") === "rangeInvoice") ? $("#invoices") : $("#orgInvoices");
    if (workspace.attr("id") === "invoices" && noData === true) return false;
    let postData = {};
    $(this).parents("form").find("input, select").each(function() {
      if (($(this).prop("required") === true || $(this).attr("name") === "startDate" || $(this).attr("name") === "endDate") && $(this).val() === "") {
        let $temp = $(this).addClass("elementError");
        setTimeout(() => { $temp.removeClass("elementError"); }, 3000 );
        // returning false wasn't stopping the function from executing the ajax call so a flag was implemented.
        //return false;
        breakFunction = true;
      } else {
        $(this).removeClass("elementError");
      }
      if ($(this).attr("type") === "checkbox") {
        if ($(this).is(":checked")) {
          postData[$(this).attr("name")] = $(this).val();
        }
      } else {
        if ($(this).prop("disabled") === false) {
          if ($(this).attr("name").slice(-2) === "[]") {
            if (typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "undefined") {
              postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))] = [ $(this).val() ];
            } else if (typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "object" || typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "array") {
                postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))].push($(this).val());
            }
          } else {
            postData[$(this).attr("name")] = $(this).val();
          }
        }
      }
    });
    if (breakFunction === true) {
      thisButton.prop("disabled", false);
      return false;
    }
    $("#invoiceQueryResults").html("");
    postData.formKey = $("#formKey").val();
    $("#invoiceQueryResults").html('<p id="working" class="center"><span id="ellipsis">.</span></p>');
    let forward = true,
        dots = setInterval(() => {
      let ele = document.getElementById("ellipsis");
      if (forward === true) {
        ele.innerHTML += "..";
        forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
      } else {
        ele.innerHTML = ele.innerHTML.substr(0, (ele.innerHTML.length - 2));
        forward = ele.innerHTML.length === 1;
      }
    }, 250);
    let attempt = ajax_template("POST", "./buildQuery.php", "html", postData)
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      thisButton.prop("disabled", false);
      // reset the form
      switch(thisButton.prop("id")) {
        case "singleInvoice":
          $("#dateIssuedMonth").val("");
          if ($("#useInvoice").is(":checked")) $("#useInvoice").prop("checked", false).trigger("change");
        break;
        case "rangeInvoice":
          $("#startDateMonth, #endDateMonth").val("");
          $("#compareInvoices").val("0").trigger("change");
        break;
        case "submitSingle":
          $("#dateIssuedMonth").val("");
        break;
        case "range":
          $("#invoiceStartDateMonth, #invoiceEndDateMonth").val("");
          $("#compareInvoices, #compareMembers").each(function() { if ($(this).is(":checked")) $(this).prop("checked", false).trigger("change"); } );
        break;
      }
      $("#working").fadeOut( 1000, function() { $(this).remove(); });
      clearInterval(dots);
      $("#invoiceQueryResults").html(result);
      $("#dateIssued, #startDate, #endDate").val("");
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      thisButton.prop("disabled", false);
      $("#invoiceQueryResults").html('<p class="center">' + error + "</p>");
    });
  });

  $(document).on("click", "button.invoiceQuery", function( e ) {
    e.preventDefault();
    let postData = {};
    $(this).parents("form").find("input").each(function() {
      if ($(this).attr("name").slice(-2) === "[]") {
        if (typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "undefined") {
          postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))] = [ $(this).val() ];
        } else if (typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "object" || typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "array") {
            postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))].push($(this).val());
        }
      } else {
        postData[$(this).attr("name")] = $(this).val();
      }
    });
    postData.formKey = $("#formKey").val();
    $("#invoiceQueryResults").html("");
    $("#invoiceQueryResults").html('<p id="working" class="center"><span id="ellipsis">.</span></p>');
    let forward = true,
        dots = setInterval(() => {
      let ele = document.getElementById("ellipsis");
      if (forward === true) {
        ele.innerHTML += "..";
        forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
      } else {
        ele.innerHTML = ele.innerHTML.substr(0, (ele.innerHTML.length - 2));
        forward = ele.innerHTML.length === 1;
      }
    }, 250);
    let attempt = ajax_template("POST", "./buildQuery.php", "html", postData)
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $("#working").fadeOut( 1000, function() { $(this).remove(); });
      clearInterval(dots);
      $("#invoiceQueryResults").html(result);
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $("#invoiceQueryResults").html('<p class="center">' + error + "</p>");
    });
  });

  $(document).on("click", "#mulitInvoiceButton", function( e ) {
    e.preventDefault();
    let postData = {};
    $(this).parents("form").find("input, select").each(function() {
      postData[$(this).prop("name")] = $(this).val();
    });
    $("#invoiceQueryResults").html("");
    $("#invoiceQueryResults").html('<p id="working" class="center"><span id="ellipsis">.</span></p>');
    let forward = true;
    let dots = setInterval(() => {
      let ele = document.getElementById("ellipsis");
      if (forward === true) {
        ele.innerHTML += "..";
        forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
      } else {
        ele.innerHTML = ele.innerHTML.substr(0, (ele.innerHTML.length - 2));
        forward = ele.innerHTML.length === 1;
      }
    }, 250);
    let attempt = ajax_template("POST", "./buildQuery.php", "html", postData)
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $("#working").fadeOut( 1000, function() { $(this).remove(); });
      clearInterval(dots);
      $("#invoiceQueryResults").html(result);
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $("#invoiceQueryResults").html('<p class="center">' + error + "</p>");
    });
  });
  // ticket query page
  if ($("#options").find(".ticketDate:first").text() === "No Data On File") {
    $("#options").find("input[type='submit']").prop("disabled", true);
  }

  if ($("#orgTickets .orgMember").length > 0) {
    $("#ticketQueryResults").before('<p class="center noticeRow">Select member or ticket number to query</p>')
  }

  $(document).on("touchstart", ".ticketGraphContainer", function() {
    mySwipe.disable();
  });

  $(document).on("touchend", ".ticketGraphContainer", function() {
    mySwipe.enable();
  });

  $(document).on("change", "#display", function() {
    switch ($(this).val()) {
      case "tickets":
        if ($("#queryForms").find("#charge").length > 0) $("#queryForms").find("#charge").prop("disabled", false);
        $("#deliveryQuery").find(".ticketDate").show().end().find(".chartDate, .compare").hide().end().find("#compareBox").prop("disabled", true).end().find("#ticketNumber").prop("readonly", false).end().find("#allTime, #chargeHistory, #type").prop("disabled", false).end().find(".startDateDate, .endDateDate").prop("required", true).prop("disabled", false).end().find(".startDateMonth, .endDateMonth").prop("required", false).prop("disabled", true).end().find("#compareBox").prop("checked", false).prop("disabled", true);
      break;
      case "chart":
      if ($("#queryForms").find("#charge").length > 0) $("#queryForms").find("#charge").prop("disabled", true);
        $("#deliveryQuery").find(".chartDate, .compare").show().end().find("#compareBox").prop("disabled", false).end().find(".ticketDate").hide().end().find("#ticketNumber").prop("readonly", true).val("").end().find("#allTime").prop("checked", false).prop("disabled", true).end().find("#chargeHistory").val("10").prop("disabled", true).end().find("#type").val("2").prop("disabled", true).end().find(".startDateDate, .endDateDate").prop("required", false).prop("disabled", true).end().find(".startDateMonth, .endDateMonth").prop("required", true).prop("disabled", false);
        $("#deliveryQuery").find("#compareBox").prop("disabled", false);
      break;
    }
    $("#orgTickets .orgMember").each(function() { $(this).prop("checked", false); } );
  });

  $(document).on("change", "#orgTickets .orgMember", function() {
    disableButtonsTickets();
    if ($(this).is(":checked")) {
      $("#orgTickets #ticketNumber").val("").prop("readonly", true).trigger("change");
    } else {
      $("#orgTickets #ticketNumber").prop("readonly", ($("#display").val() === "chart"));
    }
  }).change();

  $(document).on("change", "#compareBox", function(){
    if($(this).is(":checked")) {
      if ($("#allTime").is(":checked")) $("#allTime").prop("checked", false).trigger("change");
      $(this).parents("fieldset").find(".chartDate").attr("title", "");
    } else {
      $(this).parents("fieldset").find(".chartDate").attr("title", "Query Range Limited To 6 Month Periods");
    }
  }).change();

  $(document).on("change", ".allTime3", function() {
    if($(this).is(":checked")){
      $(this).parents("fieldset").find("#startDateMonth, #endDateMonth").prop("disabled", true).end().find(".startDateMarker, .endDateMarker").prop("disabled", false).end().find("#compareBox").prop("checked", false);
    } else{
      $(this).parents("fieldset").find("#startDateMonth, #endDateMonth").prop("disabled", false).end().find(".startDateMarker, .endDateMarker").prop("disabled", true);
    }
  }).change();

  $(document).on("change", ".allTime2", function(){
    if($(this).is(":checked")){
      $(this).parents("fieldset").find(".startDateDate, .endDateDate").prop("disabled", true).end().find(".startDateMarker, .endDateMarker").prop("disabled", false);
    } else{
      $(this).parents("fieldset").find(".startDateDate, .endDateDate").prop("disabled", false).end().find(".startDateMarker, .endDateMarker").prop("disabled", true);
    }
  }).change();

  $(document).on("change", "#allTime", function(){
    if($(this).is(":checked")){
      $(this).parents("form").find(".startDateDate, .endDateDate").prop("disabled", true).end().find("#ticketNumber").val("").prop("readonly", true);
      $(this).parents("form").find(".startDateMarker, .endDateMarker, .ticketNumberMarker").prop("disabled", false);
    } else{
      $(this).parents("form").find(".startDateDate, .endDateDate").prop("disabled", false).end().find("#ticketNumber").prop("readonly", false);
      $(this).parents("form").find(".startDateMarker, .endDateMarker, .ticketNumberMarker").prop("disabled", true);
    }
  }).change();

  $(document).on("change", "#deliveryQuery #ticketNumber", function(){
    if($(this).val() !== "") {
      if ($("#orgTickets .noticeRow").length > 0) {
        $("#orgTickets .noticeRow").hide();
      }
      $(this).parents("form").find("#startDate, #endDate, #chargeHistory, #type, #allTime, #display, #compareBox, #compareMembersTickets").prop("disabled", true).prop("checked", false).prop("required", false).end().find(".startDateMarker, .endDateMarker, .chargeMarker, .typeMarker, #displayMarker").prop("disabled", false);
      if ($(".submitOrgTickets").length > 0) {
        $(".submitOrgTickets").prop("disabled", false);
      }
    } else {
      if ($("#orgTickets .noticeRow").length > 0) {
        let memberTest = false;
        $("#orgTickets .orgMember").each(function() {
          if ($(this).is(":checked")) {
            memberTest = true;
          }
        });
        if (memberTest === false) $("#orgTickets .noticeRow").show();
      }
      if ($(this).prop("readonly") === false) {
        $("#deliveryQuery #startDate, #deliveryQuery #endDate").prop("required", true);
        $(this).parents("form").find("#startDate, #endDate, #allTime, #display, #compareBox, #compareMembersTickets").prop("disabled", false).end().find(".startDateMarker, .endDateMarker, .chargeMarker, .typeMarker, #displayMarker").prop("disabled", true);
        $(this).parents("form").find("#chargeHistory, #type").prop("disabled", ($("#display").val() === "chart"));
      }
    }
  }).change();

  $(document).on("change", "#deliveryQuery #startDate, #deliveryQuery #endDate", function() {
    let testVal = ($("#deliveryQuery #endDate").val() === "") && ($("#deliveryQuery #startDate").val() === "");
    $(this).parents("fieldset").find("#ticketNumber").prop("readonly", !testVal);
    if (!testVal) $(this).parents("fieldset").find("#ticketNumber").val("");
  }).change();

  $(document).on("click", ".sigPrint", function(){
    $(this).next("tr.sigImage").toggle(900);
  });

  $(document).on("click", ".submitTicketQuery", function( e ) {
    e.preventDefault();
    $(this).prop("disabled", true);
    $(this).parents("#deliveryQuery").find("#startDate, #endDate").each(function() {
      if ($(this).is(":visible")) {
        $(this).prop("required", true);
      }
    });
    let breakFunction = false;
    let workspace = $(this).closest(".page");
    let postData = {};
    $(this).closest("form").find("input, select").each(function() {
      if ($(this).prop("disabled") === false) {
        if (($(this).prop("required") === true || $(this).prop("id") === "startDate" || $(this).prop("id") === "endDate") && $(this).val() === "") {
          let $temp = $(this).addClass("elementError");
          setTimeout(() => { $temp.removeClass("elementError"); }, 3000 );
          breakFunction = true;
        } else {
          $(this).removeClass("elementError");
        }
        if ($(this).attr("type") !== "checkbox") {
          postData[$(this).attr("name")] = $(this).val();
        } else {
          if ($(this).is(":checked")) {
            postData[$(this).attr("name")] = $(this).val();
          }
        }
      }
    });
    postData.formKey = $("#formKey").val();
    if (breakFunction === true) return false;
    $("#ticketQueryResults").html("");
    $("#ticketQueryResults").html('<p id="working" class="center"><span id="ellipsis">.</span></p>');
    let forward = true;
    let dots = setInterval(() => {
      let ele = document.getElementById("ellipsis");
      if (forward === true) {
        ele.innerHTML += "..";
        forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
      } else {
        ele.innerHTML = ele.innerHTML.substr(0, (ele.innerHTML.length - 2));
        forward = ele.innerHTML.length === 1;
      }
    }, 250);
    let attempt = ajax_template("POST", "./buildQuery.php", "html", postData)
    .done((result) => {
      $(this).prop("disabled", false);
      $("#working").fadeOut( 1000, function() { $(this).remove(); });
      clearInterval(dots);
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $("#ticketQueryResults").html(result);
    })
    .fail((jqXHR, status, error) => {
      $(this).prop("disabled", false);
      clearInterval(dots);
      $("#ticketQueryResults").html('<p class="center">' + error + "</p>");
    });
  });

  $(document).on("click", ".resetTicketQuery", function() {
    $("#display").val("tickets").trigger("change");
    $("#deliveryQuery").find(".elementError").each(function() {
      $(this).removeClass("elementError");
    });
  });

  $(document).on("click", ".clearTicketResults", function() {
    $("#ticketQueryResults").html("");
  });

  $(document).on("click", ".submitOrgTickets", function( e ) {
    e.preventDefault();
    let workspace = $(this).closest(".page");
    $(this).prop("disabled", true);
    $("#deliveryQuery").find("#startDate, #endDate").each(function() {
      if ($(this).parent("span").is(":visible")) {
        $(this).prop("required", true);
      }
    });
    let breakFunction = false;
    let postData = {};
    $(this).closest("form").find("input, select").each(function() {
      if ($(this).prop("disabled") === false) {
        if (($(this).prop("required") === true) && $(this).val() === "") {
          let $temp = $(this).addClass("elementError");
          setTimeout(() => { $temp.removeClass("elementError"); }, 3000 );
          breakFunction = true;
        } else {
          $(this).removeClass("elementError");
        }
        if ($(this).attr("name").slice(-2) === "[]") {
          if (($(this).attr("type") === "checkbox" && $(this).is(":checked")) || $(this).attr("type") !== "checkbox") {
            if (typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "undefined") {
              postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))] = [ $(this).val() ];
            } else if (typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "object" || typeof(postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))]) === "array") {
                postData[$(this).attr("name").slice(0, ($(this).attr("name").length - 2))].push($(this).val());
            }
          }
        } else {
          if ($(this).attr("type") === "checkbox") {
            if ($(this).is(":checked")) postData[$(this).attr("name")] = $(this).val();
          } else {
            postData[$(this).attr("name")] = $(this).val();
          }
        }
      }
    });
    if (postData.ticketNumber === "" && postData.clientID === "undefined") {
      $("#ticketQueryResults").html('<p class="queryError center">Please enter a ticket number or select a member to query</p>');
      setTimeout(() => { $(".queryError").remove(); }, 2000);
      return false;
    }
    if (postData.ticketNumber !== "") {
      let clients = [];
      $("#orgTickets .orgMember").each(function() {
        clients.push($(this).val());
      });
      postData.clientID = clients;
    }
    postData.formKey = $("#formKey").val();
    $("#startDate, #endDate, #startDateMonth, #endDateMonth, #ticketNumber").val("");
    $("#compareBox, #compareMembersTickets").prop("checked", false).trigger("change");
    $("#orgTickets #display").val("tickets").trigger("change");
    disableButtonsTickets();
    if (breakFunction === true) return false;
    $("#ticketQueryResults").html("");
    $("#ticketQueryResults").html('<p id="working" class="center"><span id="ellipsis">.</span></p>');
    let forward = true;
    let dots = setInterval(() => {
      let ele = document.getElementById("ellipsis");
      if (forward === true) {
        ele.innerHTML += "..";
        forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
      } else {
        ele.innerHTML = ele.innerHTML.substr(0, (ele.innerHTML.length - 2));
        forward = ele.innerHTML.length === 1;
      }
    }, 250);
    let attempt = ajax_template("POST", "./buildQuery.php", "html", postData)
    .done((result) => {
      $(this).prop("disabled", false);
      $("#working").fadeOut( 1000, function() { $(this).remove(); });
      clearInterval(dots);
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $("#ticketQueryResults").html(result);
    })
    .fail((jqXHR, status, error) => {
      $(this).prop("disabled", false);
      clearInterval(dots);
      $("#ticketQueryResults").html('<p class="center">' + error + "</p>");
    });
  });

  $(document).on("click", "#ticketQueryResults button.invoiceQuery", function( e ) {
    mySwipe.slide($("a.nav:contains('Invoice')").attr("data-value"), 300);
  });
  // on call tickets page
  $(document).on("click", "#on_call .transferTicket", function() {
    //Clear all 'message2' containers
    $(this).parents(".tickets").find(".message2").html("");
    //Request transfer confirmation
    $(this).closest(".tickets").find(".message2").html("Confirm Transfer:<br><input list=\"receivers\" class=\"pendingReceiver\" name=\"pendingReceiver\" id=\"pendingReceiver" + $(this).parents(".tickets").find(".tNum").text() + "\" /><br><button type=\"button\" class=\"confirmTransfer\">Confirm</button>  <button type=\"button\" class=\"cancelThis\">Go Back</button>");
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find(".transferTicket, .cancelRun, .deadRun, .dTicket, .declined, input[type='text'], .pGetSig, .dGetSig, .d2GetSig").prop("disabled", true);
  });

  $(document).on("click", "#on_call .confirmTransfer", function() {
    let button = $(this);
    button.parents(".message2").find("button").prop("disabled", true);
    let pendingReceiver = $(this).closest(".message2").find(".pendingReceiver").val();
    if (pendingReceiver === null || pendingReceiver === "") {
      let $temp = $(this).closest(".message2").find(".pendingReceiver").addClass("elementError");
      setTimeout(() => { $temp.removeClass("elementError"); button.parents(".message2").find("button").prop("disabled", false); }, 3000 );
      return false;
    }
    // Get the ticket number to be removed from the data base
    let tNum = $(this).parents(".tickets").find(".tNum").text();
    // Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    //Set a flag to mark the ticket for deletion
    let action = "transfer";
    // Get the form key
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, TransferState: 1, PendingReceiver: pendingReceiver, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(() => { refreshOnCall(Number($(".ticketCount:first").text()) - 1); refreshTransfers(); }, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#on_call .declined", function(){
    //Clear all 'message2' containers
    $(this).parents(".tickets").find(".message2").html("");
    //Request cancellation confirmation
    $(this).closest(".tickets").find(".message2").html('Confirm Decline:<br><button type="button" class="confirmDecline">Confirm</button>  <button type="button" class="cancelThis">Go Back</button>');
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find(".transferTicket, .cancelRun, .deadRun, .dTicket, .declined, input[type='text'], .pGetSig, .dGetSig, .d2GetSig").prop("disabled", true);
  })

  $(document).on("click", "#on_call .cancelRun", function(){
    //Clear all 'message2' containers
    $(this).parents(".tickets").find(".message2").html("");
    //Request cancellation confirmation
    $(this).closest(".tickets").find(".message2").html('Confirm Cancel:<br><button type="button" class="confirmCancel">Confirm</button>  <button type="button" class="cancelThis">Go Back</button>');
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find(".transferTicket, .cancelRun, .deadRun, .dTicket, .declined, input[type='text'], .pGetSig, .dGetSig, .d2GetSig").prop("disabled", true);
  });

  $(document).on("click", "#on_call .deadRun", function(){
    //Clear all 'message2' containers
    $(this).parents(".tickets").find(".message2").html("");
    //Request dead run confirmation
    $(this).closest(".tickets").find(".message2").html('Confirm Dead Run:<br><button type="button" class="confirmDeadRun">Confirm</button>  <button type="button" class="cancelThis">Go Back</button>');
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find(".transferTicket, .cancelRun, .deadRun, .dTicket, .declined, input[type='text'], .pGetSig, .dGetSig, .d2GetSig").prop("disabled", true);
  });

  $(document).on("click", "#on_call .cancelThis", function(){
    $(this).parents(".tickets").find("button, .dTicket, input[type='text'], textarea").prop("disabled", false);
    $(this).parent("p").html("");
  });

  $(document).on("click", "#on_call .confirmCancel", function(){
    //Get the ticket number to be removed from the data base
    let tNum = $(this).parents(".tickets").find(".ticket_index").val();
    //Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    //Set a flag to mark the ticket for deletion
    let action = "cancel";
    //let formKey = x.find("span.formKey").text();
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(() => { refreshOnCall(Number($(".ticketCount:first").text()) - 1) }, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#on_call .confirmDeadRun", function(){
    // Get the ticket number to be marked as dead run
    let tNum = $(this).parents(".tickets").find(".ticket_index").val();
    // Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    // Set a flag to mark the ticket for charge change
    let action = "deadRun";
    // let formKey = x.find("span.formKey").text();
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(() => { refreshOnCall(Number($(".ticketCount:first").text()) - 1) }, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#on_call .confirmDecline", function(){
    //Get the ticket number to be removed from the data base
    let tNum = $(this).parents(".tickets").find(".ticket_index").val();
    //Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    //Set a flag to mark the ticket for deletion
    let action = "declined";
    //let formKey = x.find("span.formKey").text();
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(() => { refreshOnCall(Number($(".ticketCount:first").text()) - 1) }, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#on_call .dTicket", function( e ) {
    e.preventDefault();
    //Clear all 'message2' containers
    $(this).parents("#on_call").find(".message2").html("");
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find("button").prop("disabled", true);
    //Request step confirmation
    $(this).closest(".tickets").find(".message2").html("Confirm " + $(this).text() + ':<br><button type="button" class="stepTicket" form="' + $(this).attr("form") + '">Confirm</button>  <button type="button" class="cancelThis">Go Back</button>');
  });

  $(document).on("click", "#on_call .stepTicket", function( e ) {
    e.preventDefault();
    let x = $(this);
    x.parents(".message2").find("button").prop("disabled", true);
    let postData = {};
    $(this).parents(".tickets").find("input[form='" + $(this).attr("form") + "'], textarea[form='" + $(this).attr("form") + "']").each(function() {
      postData[$(this).prop("name")] = $(this).val();
    });
    // let dedicatedRoundTrip = $(this).parents(".tickets").find(".timing").parent("td").text();
    if ($(this).parents(".tickets").find(".printName").is(":required") && $(this).parents(".tickets").find(".printName").val() === "") {
      $temp = $(this).parents(".tickets").find(".printName").addClass("elementError");
      setTimeout(() => { $temp.removeClass("elementError"); }, 3000);
      x.parents(".message2").find("button").prop("disabled", false);
      return false;
    }
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    postData.formKey = $("#formKey").val();
    if (postData.hasOwnProperty("pSigPrint")) {
      postData.printName = postData.pSigPrint;
      delete postData.pSigPrint;
    }
    if (postData.hasOwnProperty("dSigPrint")) {
      postData.printName = postData.dSigPrint;
      delete postData.dSigPrint;
    }
    if (postData.hasOwnProperty("d2SigPrint")) {
      postData.printName = postData.d2SigPrint;
      delete postData.d2SigPrint;
    }
    let attempt = ajax_template("POST", "./updateStep.php", "text", postData)
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        let currentTicketCount = $(".ticketCount:first").text();
        currentTicketCount -= (postData.step === "returned" || (postData.step === "delivered" && charge < 6) || (charge === "7" && x.parents(".tickets").find(".timing").parent("td").text().indexOf("Return") !== -1)) ? 1 : 0;
        setTimeout(() => { refreshOnCall(currentTicketCount) }, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html('<span class="center">' + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });
  // change password page
  $(document).on("change", ".newPw1, .newPw2, .currentPw", function() {
    let test0 = $(this).closest(".PWform").find(".currentPw").val();
    let test1 = "";
    let test2 = "";
    let target = "";
    // Make sure that test1 is always newPw1
    if ($(this).hasClass("newPw1")) {
      test1 = $(this).closest(".PWform").find(".newPw1").val();
      target = ".newPw2";
      test2 = $(this).closest(".PWform").find(target).val();
    } else {
      test2 = $(this).closest(".PWform").find(".newPw2").val();
      target = ".newPw1";
      test1 = $(this).closest(".PWform").find(target).val();
    }
    if (test1 !== "") {
      if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^\&*\)\(\{\}\[\]\-_.=+\?\:;,])(?=.{8,}).*$/.test(test1)) {
        if ($(this).closest(".PWform").find(".error1").length === 0) {
          $(this).closest(".PWform").find(".message").append('<p class="error1"> <span class="error">Error:</span> Password does not meet criteria.</p>');
          $(this).closest(".PWform").find(".PWsubmit").prop("disabled", true);
          return false;
        }
      } else {
        $(this).closest(".PWform").find(".error1").remove();
        if ($(this).closest(".PWform").find(".message p").length === 0) {
          $(this).closest(".PWform").find(".PWsubmit").prop("disabled", false);
        }
      }
      if (test0 !== "" && test1 === test0) {
        if ($(this).closest(".PWform").find(".error3").length === 0) {
          $(this).closest(".PWform").find(".message").append('<p class="error3"> <span class="error">Error:</span> Password should be changed.</p>');
          $(this).closest(".PWform").find(".PWsubmit").prop("disabled", true);
        }
      } else {
        $(this).closest(".PWform").find(".error3").remove();
        if ($(this).closest(".PWform").find(".message p").length === 0) {
          $(this).closest(".PWform").find(".PWsubmit").prop("disabled", false);
        }
      }
    } else {
      $(this).closest(".PWform").find(".error1").remove();
      if ($(this).closest(".PWform").find(".message p").length === 0) {
        $(this).closest(".PWform").find(".PWsubmit").prop("disabled", false);
      }
    }
    if (test2 !== "") {
      if (test1 !== test2) {
        if ($(this).closest(".PWform").find(".error2").length === 0) {
          $(this).closest(".PWform").find(".message").append('<p class="error2"> <span class="error">Error:</span> Password missmatch.</p>');
          $(this).closest(".PWform").find(".PWsubmit").prop("disabled", true);
          return false;
        }
      } else {
        $(this).closest(".PWform").find(".error2").remove();
        if ($(this).closest(".PWform").find(".message p").length === 0) {
          $(this).closest(".PWform").find(".PWsubmit").prop("disabled", false);
        }
      }
    } else {
      $(this).closest(".PWform").find(".error2").remove();
      if ($(this).closest(".PWform").find(".message p").length === 0) {
        $(this).closest(".PWform").find(".PWsubmit").prop("disabled", false);
      }
    }
  }).change();

  $(document).on("click", ".clearPWform", function() {
    $(this).closest(".PWform").find(".message").html("").end().find(".PWsubmit").prop("disabled", false);
  });

  $(document).on("change", ".showText", function() {
    if ($(this).is(":checked")) {
      $(this).parents("form").find("input[type='password']").each(function() {
        $(this).attr("type", "text");
      });
    } else {
      $(this).parents("form").find("input[type='text']").each(function() {
        $(this).attr("type", "password");
      });
    }
  }).change();
  // route page
  $(document).on("click", "#route .transferTicket", function() {
    //Clear all 'message2' containers
    $(this).parents("#route").find(".message2").html("");
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find("button").prop("disabled", true);
    //Request cancellation confirmation
    $(this).closest(".tickets").find(".message2").html("Confirm Transfer:<br><input list=\"receivers\" class=\"pendingReceiver\" name=\"pendingReceiver\" id=\"pendingReceiver" + $(this).parents(".tickets").find(".tNum").text() + "\" /><br><button type=\"button\" class=\"confirmTransfer\">Confirm</button>  <button type=\"button\" class=\"cancelThis\">Go Back</button>");
  });

  $(document).on("click", "#route .confirmTransfer", function() {
    $(this).parents(".message2").find("button").prop("disabled", true);
    let pendingReceiver = $(this).closest(".message2").find(".pendingReceiver").val();
    if (pendingReceiver === null || pendingReceiver === "") {
      let $temp = $(this).closest(".message2").find(".pendingReceiver").addClass("elementError");
      setTimeout(() => { $temp.removeClass("elementError"); $temp.closest(".message2").find("button").prop("disabled", false); }, 3000 );
      return false;
    }
    // Get the ticket number to be removed from the data base
    let tNum = $(this).parents(".tickets").find(".tNum").text();
    // Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    //Set a flag to mark the ticket for deletion
    let action = "transfer";
    // Get the form key
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, TransferState: 1, PendingReceiver: pendingReceiver, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(() => { refreshRoute(); refreshTransfers() }, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
        if (result.indexOf("Session Error") !== - 1) return showLogin();
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#route .declined", function(){
    //Clear all 'message2' containers
    $(this).parents("#route").find(".message2").html("");
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find("button").prop("disabled", true);
    //Request cancellation confirmation
    $(this).closest(".tickets").find(".message2").html("Confirm Decline:<br><button type=\"button\" class=\"confirmDecline\">Confirm</button>  <button type=\"button\" class=\"cancelThis\">Go Back</button>");
  });

  $(document).on("click", "#route .confirmDecline", function(){
    $(this).parents(".message2").find("button").prop("disabled", true);
    // Get the ticket number to be removed from the data base
    let tNum = $(this).parents(".tickets").find(".tNum").text();
    // Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    // Set a flag to mark the ticket for deletion
    let action = "declined";
    // Get the form key
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      }
      if (forward === false) {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(refreshRoute, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
        if (result.indexOf("Session Error") !== - 1) return showLogin();
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#route .cancelRun", function(){
    //Clear all 'message2' containers
    $(this).parents("#route").find(".message2").html("");
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find("button").prop("disabled", true);
    //Request cancellation confirmation
    $(this).closest(".tickets").find(".message2").html("Confirm Cancellation:<br><button type=\"button\" class=\"confirmCancel\">Confirm</button>  <button type=\"button\" class=\"cancelThis\">Go Back</button>");
  });

  $(document).on("click", "#route .deadRun", function(){
    //Clear all 'message2' containers
    $(this).parents("#route").find(".message2").html("");
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find("button").prop("disabled", true);
    //Request dead run confirmation
    $(this).closest(".tickets").find(".message2").html("Confirm Dead Run:<br><button type=\"button\" class=\"confirmDeadRun\">Confirm</button>  <button type=\"button\" class=\"cancelThis\">Go Back</button>");
  });

  $(document).on("click", "#route .cancelThis", function(){
    $(this).parents(".tickets").find("button, .notes").prop("disabled", false);
    $(this).parent(".message2").html("");
  });

  $(document).on("click", "#route .confirmCancel", function(){
    // Get the ticket number to be removed from the data base
    let tNum = $(this).parents(".tickets").find(".tNum").text();
    // Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    //Set a flag to mark the ticket for deletion
    let action = "cancel";
    // Get the form key
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(refreshRoute, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
        if (result.indexOf("Session Error") !== - 1) return showLogin();
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#route .confirmDeadRun", function(){
    $(this).parents(".message2").find("button").prop("disabled", true);
    // Get the ticket number to be marked as dead run
    let tNum = $(this).parents(".tickets").find(".tNum").html();
    // Get the notes for the ticket
    let notes = $(this).parents(".tickets").find(".notes").val();
    // Set a flag to mark the ticket for charge change
    let action = "deadRun";
    // Get the form key
    let formKey = $("#formKey").val();
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "html", { ticket_index: tNum, action: action, notes: notes, formKey: formKey })
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(refreshRoute, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
        if (result.indexOf("Session Error") !== - 1) return showLogin();
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html('<span class="center">' + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", "#route .dTicket", function( e ) {
    e.preventDefault();
    //Clear all 'message2' containers
    $(this).parents("#route").find(".message2").html("");
    //Disable other buttons in the ticket form
    $(this).closest(".tickets").find("button").prop("disabled", true);
    //Request step confirmation
    $(this).closest(".tickets").find(".message2").html("Confirm " + $(this).text() + ':<br><button type="button" class="stepTicket" form="' + $(this).attr("form") + '">Confirm</button>  <button type="button" class="cancelThis">Go Back</button>');

  });

  $(document).on("click", "#route .stepTicket", function( e ) {
    e.preventDefault();
    let x = $(this);
    x.parents(".message2").find("button").prop("disabled", true);
    let postData = {};
    $(this).parents(".tickets").find("input[form='" + $(this).attr("form") + "'], textarea[form='" + $(this).attr("form") + "']").each(function() {
      postData[$(this).prop("name")] = $(this).val();
    });
    if ($(".printName[form='" + $(this).attr("form") + "']").prop("required") === true && $(".printName[form='" + $(this).attr("form") + "']").val() === "") {
      $temp = x.parents(".sortable").find(".printName").addClass("elementError");
      setTimeout(() => { $temp.removeClass("elementError"); }, 3000);
      x.parents(".message2").find("button").prop("disabled", false);
      return false;
    }
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    postData.formKey = $("#formKey").val();
    if (postData.hasOwnProperty("pSigPrint")) {
      postData.printName = postData.pSigPrint;
      delete postData.pSigPrint;
    }
    if (postData.hasOwnProperty("dSigPrint")) {
      postData.printName = postData.dSigPrint;
      delete postData.dSigPrint;
    }
    if (postData.hasOwnProperty("d2SigPrint")) {
      postData.printName = postData.d2SigPrint;
      delete postData.d2SigPrint;
    }
    let attempt = ajax_template("POST", "./updateStep.php", "text", postData)
    .done((result) => {
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();

      if(result.indexOf("error") === - 1) {
        $parentElement.html(result);
        setTimeout(refreshRoute, 3000);
      } else {
        $parentElement.html('<span class="center">' + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html('<span class="center">' + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".tickets").find("button").prop("disabled", false); }, 5000);
    });
  });

  $(document).on("click", ".transferGroup", function() {
    $(this).parents(".sortable").find("p.message2").html("Confirm Transfer: <input list=\"receivers\" class=\"pendingReceiver\" name=\"pendingReceiver\" id=\"pendingReceiver\" /> <button type=\"button\" class=\"confirmTransferGroup\">Confirm</button>  <button type=\"button\" class=\"cancelThis\">Go Back</button>");
  });

  $(document).on("click", "#route .confirmTransferGroup", function() {
    let multiTicket = [];
    let pendingReceiver = $(this).closest(".message2").find(".pendingReceiver").val();
    $(this).parents(".sortable").find(".tickets").each(function( i ) {
      multiTicket[ i ] = { ticket_index: $(this).find(".tNum").text(), notes: $(this).find(".notes").val(), pendingReceiver: pendingReceiver, action: "transfer", transferState: 1 };
    });
    let postData = { multiTicket: multiTicket, TransferState: 1, formKey: $("#formKey").val() };
    let attempt = ajax_template("POST", "./deleteContractTicket.php", "text", postData)
    .done((result) => {
      // console.log(result);
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if(result.indexOf("error") === -1) {
        $(this).parents(".sortable").find("p.message2").text(result);
        setTimeout(refreshTransfers, 2990);
        setTimeout(refreshRoute, 3000);
      }
      else {
        $(this).after('<p class="center">Processing Error ' + result + "</p>");
        if (result.indexOf("Session Error") !== - 1) return showLogin();
      }
    })
    .fail((jqXHR, status, error) => {
      $(this).parents(".sortable").find(".message2").text("Error: " + error);
    });
  });

  $(document).on("click", ".confirmAll", function( e ) {
    e.preventDefault();
    //Clear all 'message2' containers
    $("#route").find(".message2").html("");
    //Disable other buttons in the ticket form
    $(this).closest(".sortable").find("button").prop("disabled", true);
    //Request step confirmation
    $(this).closest(".sortable").find(".message2:last").html("Confirm Group Update: <br><button type=\"button\" class=\"stepAll\">Confirm</button>  <button type=\"button\" class=\"cancelThis\">Go Back</button>");
  });

  $(document).on("click", "#route .stepAll", function(){
    $(this).parents(".message2").find("button").prop("disabled", true);
    let multiTicket = [];
    $(this).parents(".sortable").find(".routeStop").each(function( i ) {
      let data = {};
      $(this).find("input").each(function() {
        if ($(this).prop("name") !== "formKey") data[$(this).prop("name")] = $(this).val();
      });
      data.notes = $(this).closest("table").find(".notes").val();
      multiTicket[ i ] = data;
    });
    if ($(this).closest(".sortable").find(".printName").prop("required") === true && $(this).closest(".sortable").find(".printName").val() === "") {
      $temp = $(this).closest(".sortable").find(".printName").addClass("elementError");
      setTimeout(() => { $temp.removeClass("elementError"); }, 3000);
      $(this).closest(".message2").find("button").prop("disabled", false);
      return false;
    }
    let postData = { multiTicket: multiTicket, formKey: $("#formKey").val(), printName: $(this).closest(".sortable").find(".printName").val(), sigImage: $(this).closest(".sortable").find(".sigImage").val() };
    let $parentElement = $(this).parents(".message2");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./updateStep.php", "html", postData)
    .done((result) => {
      console.log(result);
      clearInterval(dots);
      $(".ellipsis").remove();
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if (result.indexOf("error") === -1) {
        $parentElement.html(result);
        setTimeout(refreshRoute, 3000);
      } else {
        $parentElement.html("<span>" + result + "</span>");
        setTimeout(() => { $parentElement.html(""); $parentElement.closest(".sortable").find("button").prop("disabled", false); }, 3000);
      }
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html('<span class="center">' + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest(".sortable").find("button").prop("disabled", false); }, 5000);
    });
  });
  // price calculator page
  $(document).on("change", "#price_calculator .dryIce", function(){
    if($(this).is(":checked")){
      $(this).parents("form").find(".diWeight").val("0").prop("disabled", false).focus();
    }
    else{
      $(this).parents("form").find(".diWeight").val("0").prop("disabled", true);
    }
  }).change();

  $(document).on("change", "#price_calculator #discountMarker", function(){
    if($(this).is(":checked")){
      $(this).parents("form").find("#discount").val("0").prop("disabled", false).show();
    }
    else{
      $(this).parents("form").find("#discount").val("0").prop("disabled", true).hide();
    }
  }).change();

  $(document).on("change", "#price_calculator .address1", function(){
    let element = $(this);
    let listIndex = "i";
    // Check the datalist to see if this value is on it and set the listIndex equal to the corisponding data-value
    $("#addy1 option").each(function(){
      if ($(this).val() === element.val()) {
        listIndex = $(this).attr("data-value");
      }
    });
    // If the listIndex has changed continue on
    if (listIndex !== "i") {
      $("#addy2 option").each(function(){
        if ($(this).attr("data-value") === listIndex) {
          element.closest("table").find(".address2").val($(this).val());
        }
      });
    }
  }).change();

  $(document).on("click", "#price_calculator .submitPriceQuery", function(e){
    e.preventDefault();
    $(this).prop("disabled", true);
    let breakFunction = false;
    let pickUpError = false;
    let dropOffError = false;
    $("#pickUp input, #dropOff input").each(function() {
      if ($(this).prop("disabled") === false && $(this).prop("required") === true && $(this).val() === "") {
        breakFunction = true;
        $(this).addClass("elementError");
        if ($(this).attr("id").substr(0, 1) === "p" && pickUpError === false) {
          pickUpError = true;
          $("#CalcError").append("<p>Pick Up Address Required</p>");
        } else if ($(this).attr("id").substr(0, 1) === "d" && dropOffError === false) {
          dropOffError = true;
          $("#CalcError").append("<p>Delivery Address Required</p>");
        }
      }
    });
    let dryIce = "";
    let diWeight = "";
    let pAddress1 = $("#pAddress1Calc").val();
    let pAddress2 = $("#pAddress2Calc").val();
    let pCountry = ($("#pCountryCalc").val() === "") ? $("#pCountryMarkerCalc").val() : $("#pCountryCalc").val();
    let dAddress1 = $("#dAddress1Calc").val();
    let dAddress2 = $("#dAddress2Calc").val();
    let dCountry = ($("#dCountryCalc").val() === "") ? $("#dCountryMarkerCalc").val() : $("#dCountryCalc").val();
    let charge = $("#CalcCharge").val();
    if ($("#CalcDryIce").is(":checked")) {
      dryIce = "1";
      diWeight = $("#CalcWeight").val();
      if (diWeight % 5 !== 0) {
        breakFunction = true;
        $("#CalcError").append("<p>Dry Ice in increments of 5 only.</p>");
        $("#CalcWeight").addClass("elementError");
        setTimeout(() => { $("#CalcError").text(""); $("#CalcWeight").removeClass("elementError"); }, 4000);
      } else if (diWeight === "0") {
        breakFunction = true;
        $("#CalcError").append("<p>Dry Ice must be non-zero.</p>");
        $("#CalcWeight").addClass("elementError");
        setTimeout(() => { $("#CalcError").text(""); $("#CalcWeight").removeClass("elementError"); }, 4000);
      }
    }
    else {
      dryIce = "0";
      diWeight = "0";
    }
    if (breakFunction === true) {
      setTimeout(() => { $("#CalcError").html(""); $("#pickUp .elementError, #dropOff .elementError").removeClass("elementError"); }, 4000);
      return false;
    }
    let attempt = ajax_template("POST", "../priceCalc.php", "text", { pAddress1: pAddress1, pAddress2: pAddress2, pCountry: pCountry, dAddress1: dAddress1, dAddress2: dAddress2, dCountry: dCountry, charge: charge, dryIce: dryIce, diWeight: diWeight, formKey: $("#formKey").val() })
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $(this).prop("disabled", false);
      $("#pNotice, #dNotice, #CalcError").html("");
      let obj;
      if (result.substr(0,1) === "<") {
        $("#CalcError").html(result);
        setTimeout(() => { $("#CalcError").html(""); }, 4000);
        return false;
      }
      try {
        obj = jQuery.parseJSON(result);
      } catch( err ) {
        $("#CalcError").text( err );
        setTimeout(() => { $("#CalcError").text(""); }, 4000);
        return false;
      }
      $("#priceResult .currencySymbol, #priceResult .weightMarker").show();
      $("#rangeResult").text(obj.rangeDisplay);
      $("#diWeightResult").html(obj.diWeight);
      $("#runPriceResult").text(obj.runPrice);
      $("#diPriceResult").text(obj.diPrice);
      $("#ticketPriceResult").text(obj.ticketPrice);
      if (obj.pRangeTest > 15 && obj.pRangeTest < 20) {
        $("#pNotice").text("Pick Up address is outside of our standard range. Please call to confirm availability.");
      } else if (obj.pRangeTest > 20) {
        $("#pNotice").text("Pick Up address is outside of our extended range.");
        $("#runPriceResult, #ticketPriceResult, #diPriceResult, #diWeightResult").text("");
        $("#priceResult .currencySymbol, #priceResult .weightMarker").hide();
      }
      if (obj.dRangeTest > 15 && obj.dRangeTest < 20) {
        $("#dNotice").text("Delivery address is outside of our standard range. Please call to confirm availability.");
      } else if (obj.dRangeTest > 20) {
        $("#dNotice").text("Delivery address is outside of our extended range.");
        $("#runPriceResult, #ticketPriceResult, #diPriceResult, #diWeightResult").text("");
        $("#priceResult .currencySymbol, #priceResult .weightMarker").hide();
      }
      initPriceMap("map2", obj.result1, obj.address1, obj.result2, obj.address2, obj.center);
    })
    .fail((jqXHR, status, error) => {
      $("#CalcError").text(errorThrown);
      setTimeout(() => { $("#CalcError").text(""); }, 4000);
    });
  });

  $(document).on("click", "#price_calculator .clear", function(){
    $(this).parents("form").find(".dryIce").prop("checked", false).trigger("change").end().find("#CalcCharge").val("0");
    $("#pNotice, #dNotice, #rangeResult, #diWeightResult, #diPriceResult, #runPriceResult, #ticketPriceResult").text("");
    $("#priceResult .currencySymbol, #priceResult .weightMarker").hide();
    $("#price_calculator .elementError").removeClass("elementError");
    initPriceMap();
  });
  // ticket entry page
  $(document).on("click", "p.switch", function() {
    $(this).next("p").toggle(900);
  });

  $(document).on("change", ".contract", function(){
    if($(this).is(":checked")) {
      $(this).closest("table").find(".multiplier").prop("disabled", false);
      $(this).closest("table").find(".multiplierMarker").prop("disabled", true);
      $(this).closest("table").find(".repeat").prop("disabled", true).prop("checked", false);
    }
    else {
      $(this).closest("table").find(".multiplierMarker").prop("disabled", false);
      $(this).closest("table").find(".multiplier").prop("disabled", true);
      $(this).closest("table").find(".repeat").prop("disabled", false);
    }
  }).change();

  $(document).on("change", ".repeat", function(){
    if($(this).is(":checked")) {
      $(this).closest("table").find(".billTo").attr("list", "t_clients").val("");
      $(this).closest("table").find(".contract").prop("disabled", true).prop("checked", false);
    }
    else {
      $(this).closest("table").find(".billTo").attr("list", "clients").val("");
      $(this).parents("form").find(".nrEntry").hide();
      $(this).parents("form").find(".processNewT_client").prop("disabled", true).val("0").end().find("input[name^='t_']").prop("disabled", true).val("").end();
      $(this).closest("table").find(".contract").prop("disabled", false);
    }
  }).change();
  //Display or hide new t_client form based on billTo field
  $(document).on("change", ".billTo", function(){
    if($(this).val() === "new") {
      $(this).parents("form").find(".nrEntry").show();
      $(this).parents("form").find(".processNewT_client").prop("disabled", false).val("1").end().find("input[name^='t_']").prop("disabled", false).end();
    }
    else {
      $(this).parents("form").find(".nrEntry").hide();
      $(this).parents("form").find(".processNewT_client").prop("disabled", true).val("0").end().find("input[name^='t_']").prop("disabled", true).val("").end();
    }
  }).change();

  $(document).on("change", ".dryIce", function(){
    if($(this).is(":checked")){
      $(this).parents("fieldset").find(".diWeight").val("0").prop("disabled", false).focus();
      $(this).parents("fieldset").find(".diWeightMarker").val("0").prop("disabled", true);
    }
    else{
      $(this).parents("fieldset").find(".diWeightMarker").val("0").prop("disabled", false);
      $(this).parents("fieldset").find(".diWeight").val("0").prop("disabled", true);
    }
  }).change();

  $(document).on("change", ".charge", function(){
    if ($(this).val() === "6") {
      $(this).parents("form").find(".rtMarker").show().end().find(".dedicatedNote").hide().end().find("#pSigReq, #dSigReq").removeClass("stayChecked");
    }
    else if ($(this).val() === "7") {
      $(this).parents("form").find(".rtMarker, .dedicatedNote").show().end().find("#pSigReq, #dSigReq").prop("checked", true).addClass("stayChecked");
    }
    else {
      $(this).parents("form").find(".rtMarker, .dedicatedNote").hide().prop("checked", false).end().find("#pSigReq, #dSigReq").prop("checked", false).removeClass("stayChecked");
    }
  }).change();

  $(document).on("click", ".stayChecked", function(e) {
    e.preventDefault();
  });

  $(document).on("change", ".emailConfirm", function() {
    if ($(this).val() !== "0") {
      $(this).parents("form").find(".emailAddress").prop("required", true);
      $(this).parents("form").find(".emailNote").removeClass("hide");
    }
    else {
      $(this).parents("form").find(".emailAddress").prop("required", false);
      $(this).parents("form").find(".emailNote").addClass("hide");
    }
  }).change();

  $(document).on("blur", "input[name='pAddress1'], input[name='dAddress1']", function(){
    let element = $(this);
    let listIndex = "i";
    // Check the datalist to see if this value is on it and set the listIndex equal to the cosponsoring data-value
    $("#addy1 option").each(function(){
      if ($(this).val() === element.val()) {
        listIndex = $(this).attr("data-value");
        element.attr("data-value", listIndex)
      }
    });
    // If the listIndex has changed continue on
    if (listIndex !== "i") {
      $("#addy2 option").each(function(){
        if ($(this).attr("data-value") === listIndex) {
          element.closest("fieldset").find("input[name='" + element.attr("name").substr(0,1) + "Address2']").val($(this).val())
        }
      });
    }
  });

  $(document).on("change", "#ticket_entry #pSigReq, #ticket_entry #dSigReq, #ticket_entry #d2SigReq", function(){
    let y = $("#pSigReq").is(":checked");
    let x = $("#dSigReq").is(":checked");
    let z = $("#d2SigReq").is(":checked");
    if ($(this).is(":checked")) {
      $(this).parents("form").find(".sigNote").show();
    }
    else {
      if (!x && !y && !z) {
        $(this).parents("form").find(".sigNote").hide();
      }
    }
  }).change();

  $(document).on("click", "#ticket_entry .submitForm", function(e) {
    e.preventDefault();
    let button = $(this);
    button.prop("disabled", true);
    let breakFunction = false;
    let checkboxes = ["repeatClient", "fromMe", "toMe", "dryIce", "pSigReq", "dSigReq", "d2SigReq"];
    let requiredElements = ["pClient", "pAddress1", "pAddress2", "pCountry", "dClient", "dAddress1", "dAddress2", "dCountry"]
    let formdata = {};
    $(this).parents("#request").find("input[name], select, textarea").each(function() {
      if (checkboxes.indexOf($(this).attr("name")) === -1 && $(this).prop("disabled") === false) {
        if (($(this).prop("required") === true || (requiredElements.indexOf($(this).attr("name")) !== -1 && $(this).is(":visible"))) && $(this).val() === "") {
          let $temp = $(this).addClass("elementError");
          setTimeout(() => { $temp.removeClass("elementError"); button.prop("disabled", false); }, 3000);
          breakFunction = true;
        } else {
          $(this).removeClass("elementError");
        }
        formdata[$(this).attr("name")] = $(this).val();
      } else if ($(this).prop("type") === "checkbox") {
        formdata[$(this).attr("name")] = ($(this).attr("name") === "repeatClient") ? 1 - $(this).is(":checked") : 0 + $(this).is(":checked");
      }
      formdata.formKey = $("#formKey").val();
    });
    if (formdata.dryIce === 1) {
      if (formdata.diWeight % 5 !== 0) {
        let $tempMessage = $(this).parents("form").find(".ticketError").text("Dry Ice in increments of 5 only.");
        let $tempError = $(this).parents("form").find(".diWeight").addClass("elementError");
        breakFunction = true;
        setTimeout(() => { $tempMessage.text(""); $tempError.removeClass("elementError"); }, 3000)
      } else if (formdata.diWeight === "0") {
        let $tempMessage = $(this).parents("form").find(".ticketError").text("Dry Ice must be non-zero.");
        let $tempError = $(this).parents("form").find(".diWeight").addClass("elementError");
        breakFunction = true;
        setTimeout(() => { $tempMessage.text(""); $tempError.removeClass("elementError"); }, 3000)
      }
    }
    if (breakFunction === true) return false;
    let $parentElement = $(this).parents("#request").find(".ticketError");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      } else {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./enterTicket.php", "html", formdata)
    .done((result) => {
      clearInterval(dots);
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if (result.indexOf("data-error") !== -1) {
        $parentElement.html(result);
        setTimeout(() => { $parentElement.html(""); $parentElement.closest("table").find("button").prop("disabled", false); }, 3000);
      } else {
        $("#deliveryRequest").remove();
        $("#ticket_entry").prepend(result);
        initMap("map", coords1, address1, coords2, address2, center);
      }
      scrollTo(0,0);
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>Error: " + error + "</span>");
      setTimeout(() => { $parentElement.html(""); }, 3000);
    });
  });

  $(document).on("click", "#ticket_entry .editForm, #ticket_entry .confirmed", function(e) {
    e.preventDefault();
    let button = $(this);
    button.closest("tr").find("button").prop("disabled", true);
    let elementToReturn = $(".subContainer");
    let targetForm = ($(this).hasClass("editForm")) ? "#editForm" : "#submitTicket";
    let formdata = {};
    $(this).parents("#ticket_entry").find(targetForm + " input").each(function() {
      formdata[$(this).attr("name")] = $(this).val();
    });
    formdata.formKey = $("#formKey").val();
    let $parentElement = $(this).parents("#deliveryConfirmation").find(".ticketError");
    $parentElement.html("<span class=\"ellipsis\">.</span>");
    let $ele = $parentElement.find(".ellipsis");
    let forward = true;
    let dots = setInterval(() => {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      }
      if (forward === false) {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let attempt = ajax_template("POST", "./enterTicket.php", "html", formdata)
    .done((result) => {
      clearInterval(dots);
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if (result.indexOf("data-error") !== -1) {
        $parentElement.html(result);
        setTimeout(() => { $parentElement.html(""); $parentElement.closest("table").find("button").prop("disabled", false); }, 3000);
      } else {
        $("#ticket_entry").html("");
        $("#ticket_entry").prepend(result).append(elementToReturn);
        if (targetForm === "#editForm") {
          initMap("map");
        } else {
          let attempt2 = ajax_template("POST", "./refreshTicketForm.php", "html", { formKey: $("#formKey").val(), edit: 1 })
          .done((result2) => {
            scrollTo(0,0);
            if (result.indexOf("Session Error") !== -1) return showLogin();
            setTimeout(() => { $("#ticket_entry").html(result2).append(elementToReturn); initMap("map"); }, 5000);
            if ($("#deliveryQuery").length === 1) {
              let d = new Date();
              let month = d.getMonth() + 1;
              let monthDisplay = (month <= 9) ? "0" + month.toString() : month;
              let day = d.getDate();
              let dayDisplay = (day <= 9) ? "0" + day.toString() : day;
              let testDate = d.getFullYear() + "-" + monthDisplay + "-" + dayDisplay;
              $("#deliveryQuery").find(".startDateDate, .endDateDate").attr("max", testDate);
            }
          })
          .fail((jqXHR, status, error) => {
            $("#ticket_entry").prepend('<span class="center">' + error + "</span>");
          });
        }
      }
      scrollTo(0,0);
    })
    .fail((jqXHR, status, error) => {
      clearInterval(dots);
      $parentElement.html("<span>" + error + "</span>");
      setTimeout(() => { $parentElement.html(""); $parentElement.closest("table").find("button").prop("disabled", false); }, 3000);
    });
  });
  // Supplemental for client side page
  $(document).on("click", "#toMe, #fromMe", function() {
    let neighbor = ($(this).parents("fieldset").prop("id") === "deliveryField") ? "pickupField" : "deliveryField";
    let testVal = "";
    $("#" + neighbor).find(".clientList").each(function() {
      if (testVal += $(this).val());
    });
    let homeAddress = ClientName + Department + ShippingAddress1 + ShippingAddress2;
    return !(testVal === homeAddress);
  });

  $(document).on("change", "#toMe, #fromMe", function() {
    let neighbor = ($(this).parents("fieldset").prop("id") === "deliveryField") ? "pickupField" : "deliveryField";
    if ($(this).is(":checked")) {
      $(this).parents("thead").find(".onFile").prop("checked", false);
      $(this).parents("fieldset").find(".clientList").each( function(){$(this).prop("disabled", false).prop("required", true).show();} ).end().find(".clientSelect").each( function(){$(this).prop("disabled", true).hide();} );
      $(this).parents("fieldset").find(".clientList").each(function() {
        $(this).prop("required", false).prop("readonly", true);
      });
      $("#" + neighbor + " .me").prop("disabled", true);
      $(this).parents("fieldset").find("[id$='Client']").val(ClientName).end().find("[id$='Department']").val(Department).end().find("[id$='Address1']").val(ShippingAddress1).end().find("[id$='Address2']").val(ShippingAddress2);
    }
    else {
      $(this).parents("fieldset").find(".clientList").prop("readonly", false);
      $("#" + neighbor + " .me").prop("disabled", false);
    }
  }).change();

  $(document).on("click", ".onFile", function() {
    if ($(".clientSelect:first").children().length === 0) {
      $(this).prop("title", "No Locations On File");
      return false;
    }
  });

  $(document).on("change", ".clientSelect", function() {
    let listIndex = $(this).children("option").filter(":selected").attr("data-value");
    $(this).parents("fieldset").find(".clientSelect").each(function(){
      $(this).children().each(function(){
        if ($(this).attr("data-value") === listIndex) {
          $(this).prop("selected", true);
        }
      })
    });
  }).change();

  $(document).on("change", ".onFile", function(){
    if ($(this).is(":checked")) {
      $(this).parents("thead").find(".me").prop("checked", false);
      $(this).parents("fieldset").find(".clientList").each( function(){ $(this).prop("disabled", true).prop("required", false).prop("readonly", false).val("").hide();} ).end().find(".clientSelect").each( function(){$(this).prop("disabled", false).show();} );
      if ($(this).attr("id") === "onFileP") {
        $(this).parents("form").find("#toMe").prop("disabled", false);
      }
      else {
        $(this).parents("form").find("#fromMe").prop("disabled", false);
      }
    }
    else {
      $(this).parents("fieldset").find(".clientList").each( function(){ $(this).prop("disabled", false).prop("required", true).show(); if ($(this).prop("name").substr(1) === "Department") { $(this).prop("required", false); } } ).end().find(".clientSelect").each( function(){$(this).prop("disabled", true).hide();} );
    }
  }).change();
  // ticket transfers page
  $(document).on("click", ".cancelTransfer, .declineTransfer, .acceptTransfer", function() {
    let workspace = $(this).parents(".sortable");
    let formKey = $("#formKey").val();
    let ticket_index = Number($(this).parents(".tickets").find(".tNum").text());
    let dispatchedTo = workspace.find(".dispatchedTo").text();
    let transferState;
    if ($(this).hasClass("cancelTransfer")) {
      transferState = 2;
    } else if ($(this).hasClass("declineTransfer")) {
      transferState = 3;
    } else if ($(this).hasClass("acceptTransfer")) {
      transferState = 4;
    }
    let updateTransferAttempt = ajax_template("POST", "./deleteContractTicket.php", "html", { formKey: formKey, ticket_index: ticket_index, TransferState: transferState, pendingReceiver: 0, DispatchedTo: dispatchedTo, action: "transfer" })
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      workspace.find(".message2").html(result);
      if (result.indexOf("error") === -1) {
        if (workspace.find(".rNum").text() === "0") {
          refreshOnCall();
        } else {
          refreshRoute();
        }
        setTimeout(refreshTransfers, 3000);
      } else {
        setTimeout(() => { workspace.find(".message2").html(""); }, 3000);
      }
    })
    .fail((jqXHR, status, error) => {
       workspace.find(".message2").text(error);
    });
  });

  $(document).on("click", ".acceptTransferGroup, .declineTransferGroup, .cancelTransferGroup", function() {
    let workspace = $(this).parents(".sortable");
    let formKey = $("#formKey").val();
    let dispatchedTo = workspace.find(".dispatchedTo").text();
    let multiTicket = [];
    let transferState;
    if ($(this).hasClass("cancelTransferGroup")) {
      transferState = 2;
    } else if ($(this).hasClass("declineTransferGroup")) {
      transferState = 3;
    } else if ($(this).hasClass("acceptTransferGroup")) {
      transferState = 4;
    }
    $(this).parents(".sortable").find(".tickets").each(function( i ) {
      multiTicket[ i ] = { ticket_index: Number($(this).find(".tNum").text()), transferState: transferState, pendingReceiver: Number($(this).find(".pendingReceiver").text()), notes: $(this).find(".notes").val(), DispatchedTo: dispatchedTo, action: "transfer" }
    });
    let updateTransferGroupAttempt = ajax_template("POST", "./deleteContractTicket.php", "html", { formKey: formKey, TransferState: transferState, multiTicket: multiTicket })
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      if (result.indexOf("error") === -1) {
        workspace.find("p.message2").text(result);
        setTimeout(() => { refreshRoute(); refreshTransfers(); }, 3000);
      } else {
        workspace.find("p.message2").html('<p class="center">Processing Error ' + result + ".</p>");
      }
    })
    .fail((jqXHR, status, error) => {
      workspace.find("p.message2").text(errorThrown);
    });
  });
  // user info update page
  $(document).on("change", "#enableInfoUpdate", function(){
    if ($(this).is(":checked")) {
      $(this).parent().next("td").find(".submitInfoUpdate").prop("disabled", false);
    } else {
      $(this).parent().next("td").find(".submitInfoUpdate").prop("disabled", true);
    }
  });

  $(document).on("change", "#same", function() {
    if ($(this).is(":checked")) {
      $("#billingName, #billingAddress1, #billingAddress2, #billingCountry").val("").prop("disabled", true).prop("required", false).next(".error").hide();
    } else {
      $("#billingName, #billingAddress1, #billingAddress2, #billingCountry").prop("disabled", false).prop("required", true).next(".error").show();
    }
  });

  $(document).on("click", "#clientUpdate .submitInfoUpdate", function( e ) {
    e.preventDefault();
    let button = $(this);
    button.prop("disabled", true);
    let postData = {};
    $(this).parents("form").find("input").each(function() {
      if ($(this).prop("disabled") === false) {
        if ($(this).attr("type") === "checkbox") {
          if ($(this).is(":checked")) {
            postData[$(this).attr("name")] = $(this).val();
          }
        } else {
          postData[$(this).attr("name")] = $(this).val();
        }
      }
    });
    postData.formKey = $("#formKey").val();
    let updateClientInfoAttempt = ajax_template("POST", "./updateClientInfo.php", "text", postData)
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return showLogin();
      $("#clientUpdateResult").html(result);
      setTimeout(() => { $("#clientUpdateResult").html(""); button.prop("disabled", false); }, 4000);
    })
    .fail((jqXHR, status, error) => {
      $("#clientUpdateResult").html("<span class=\"error\">Error</span>: " + error);
      setTimeout(() => { $("#clientUpdateResult").html(""); button.prop("disabled", false); }, 4000);
    });
  });
});
