(function () {
  let width = screen.width,
    height = screen.height;
  setInterval(() => {
    if (screen.width !== width || screen.height !== height) {
      width = screen.width;
      height = screen.height;
      if (rjdci.resolutionchange) document.dispatchEvent(rjdci.resolutionchange);
    }
  }, 250);
}());
((global) => {
/*!
 * Swipe 2.2.14
 *
 * Brad Birdsall
 * Copyright 2013, MIT License
 * https://github.com/lyfeyaj/swipe
*/
  !function(t,e){"function"==typeof define&&define.amd?define([],function(){return t.Swipe=e(),t.Swipe}):"object"==typeof module&&module.exports?module.exports=e():t.Swipe=e()}(this,function(){var e,W="object"==typeof self&&self.self===self&&self||"object"==typeof global&&global.global===global&&global||this,Y=W.document;function Swipe(r,a){"use strict";var s,t,u={},l={},c=(a=a||{}).auto||0,e=!1,n=function(){},v=function(t){setTimeout(t||n,0)},i=function(t){return!!t&&("boolean"!=typeof t.cancelable||t.cancelable)},d={addEventListener:!!W.addEventListener,passiveEvents:function(){var e=!1;try{var t=Object.defineProperty({},"passive",{get:function(){e=!0}});W.addEventListener("testEvent",null,t),W.removeEventListener("testEvent",null,t)}catch(t){e=!1}return e}(),touch:"ontouchstart"in W||W.DocumentTouch&&Y instanceof DocumentTouch,transitions:function(t){var e=["transitionProperty","WebkitTransition","MozTransition","OTransition","msTransition"];for(var n in e)if(void 0!==t.style[e[n]])return!0;return!1}(Y.createElement("swipe"))};if(r){var f,h,m,p,E=r.children[0],y=parseInt(a.startSlide,10)||0,b=a.speed||300;a.continuous=void 0===a.continuous||a.continuous;var o,g,T,x=(g="direction",(o=r).currentStyle?T=o.currentStyle[g]:W.getComputedStyle&&(T=W.getComputedStyle(o,null).getPropertyValue(g)),"rtl"===T?"right":"left");a.autoRestart=void 0!==a.autoRestart&&a.autoRestart;var L=function(n,i){i=i||100;var o=null;function s(){o&&clearTimeout(o)}function t(){var t=this,e=arguments;s(),o=setTimeout(function(){o=null,n.apply(t,e)},i)}return t.cancel=s,t}(M),w={handleEvent:function(t){if(!e){switch(t.type){case"mousedown":case"touchstart":this.start(t);break;case"mousemove":case"touchmove":this.move(t);break;case"mouseup":case"mouseleave":case"touchend":this.end(t);break;case"webkitTransitionEnd":case"msTransitionEnd":case"oTransitionEnd":case"otransitionend":case"transitionend":this.transitionEnd(t);break;case"resize":L()}a.stopPropagation&&t.stopPropagation()}},start:function(t){var e;Q(t)?(e=t).preventDefault():e=t.touches[0],u={x:e.pageX,y:e.pageY,time:+new Date},s=void 0,l={},Q(t)?(E.addEventListener("mousemove",this,!1),E.addEventListener("mouseup",this,!1),E.addEventListener("mouseleave",this,!1)):(E.addEventListener("touchmove",this,!!d.passiveEvents&&{passive:!1}),E.addEventListener("touchend",this,!1))},move:function(t){var e;if(Q(t))e=t;else{if(1<t.touches.length||t.scale&&1!==t.scale)return;a.disableScroll&&i(t)&&t.preventDefault(),e=t.touches[0]}l={x:e.pageX-u.x,y:e.pageY-u.y},void 0===s&&(s=!!(s||Math.abs(l.x)<Math.abs(l.y))),s||(i(t)&&t.preventDefault(),N(),a.continuous?(C(j(y-1),l.x+h[j(y-1)],0),C(y,l.x+h[y],0),C(j(y+1),l.x+h[j(y+1)],0)):(l.x=l.x/(!y&&0<l.x||y===f.length-1&&l.x<0?Math.abs(l.x)/m+1:1),C(y-1,l.x+h[y-1],0),C(y,l.x+h[y],0),C(y+1,l.x+h[y+1],0)))},end:function(t){var e=+new Date-u.time,n=Number(e)<250&&20<Math.abs(l.x)||Math.abs(l.x)>m/2,i=!y&&0<l.x||y===f.length-1&&l.x<0;a.continuous&&(i=!1);var o=Math.abs(l.x)/l.x;s||(n&&!i?(y=o<0?(a.continuous?(R(j(y-1),-m,0),R(j(y+2),m,0)):R(y-1,-m,0),R(y,h[y]-m,b),R(j(y+1),h[j(y+1)]-m,b),j(y+1)):(a.continuous?(R(j(y+1),m,0),R(j(y-2),-m,0)):R(y+1,m,0),R(y,h[y]+m,b),R(j(y-1),h[j(y-1)]+m,b),j(y-1)),z(O(),f[y],o)):a.continuous?(R(j(y-1),-m,b),R(y,0,b),R(j(y+1),m,b)):(R(y-1,-m,b),R(y,0,b),R(y+1,m,b))),Q(t)?(E.removeEventListener("mousemove",w,!1),E.removeEventListener("mouseup",w,!1),E.removeEventListener("mouseleave",w,!1)):(E.removeEventListener("touchmove",w,!!d.passiveEvents&&{passive:!1}),E.removeEventListener("touchend",w,!1))},transitionEnd:function(t){parseInt(t.target.getAttribute("data-index"),10)===y&&((c||a.autoRestart)&&X(),A(O(),f[y]))}};return M(),I(),{setup:M,slide:function(t,e){N(),P(t,e)},prev:function(){N(),function(){if(e)return;a.continuous?P(y-1):y&&P(y-1)}()},next:function(){N(),S()},restart:X,stop:N,getPos:O,disable:function(){N(),e=!0},enable:function(){e=!1,X()},getNumSlides:function(){return p},kill:function(){N(),r.style.visibility="",E.style.width="",E.style[x]="";var t=f.length;for(;t--;){d.transitions&&C(t,0,0);var e=f[t];if(e.getAttribute("data-cloned")){var n=e.parentElement;n.removeChild(e)}e.style.width="",e.style[x]="",e.style.webkitTransitionDuration=e.style.MozTransitionDuration=e.style.msTransitionDuration=e.style.OTransitionDuration=e.style.transitionDuration="",e.style.webkitTransform=e.style.msTransform=e.style.MozTransform=e.style.OTransform=""}D(),L.cancel()}}}function D(){d.addEventListener?(E.removeEventListener("touchstart",w,!!d.passiveEvents&&{passive:!0}),E.removeEventListener("mousedown",w,!1),E.removeEventListener("webkitTransitionEnd",w,!1),E.removeEventListener("msTransitionEnd",w,!1),E.removeEventListener("oTransitionEnd",w,!1),E.removeEventListener("otransitionend",w,!1),E.removeEventListener("transitionend",w,!1),W.removeEventListener("resize",w,!1)):W.onresize=null}function k(t){var e=t.cloneNode(!0);E.appendChild(e),e.setAttribute("data-cloned",!0),e.removeAttribute("id")}function M(t){if(null!=t)for(var e in t)a[e]=t[e];f=E.children,p=f.length;for(var n=0;n<f.length;n++)f[n].getAttribute("data-cloned")&&p--;if(f.length<2&&(a.continuous=!1),d.transitions&&a.continuous&&f.length<3&&(k(f[0]),k(f[1]),f=E.children),"right"===x)for(var i=0;i<f.length;i++)f[i].style.float="right";h=new Array(f.length),m=r.getBoundingClientRect().width||r.offsetWidth,E.style.width=f.length*m*2+"px";for(var o=f.length;o--;){var s=f[o];s.style.width=m+"px",s.setAttribute("data-index",o),d.transitions&&(s.style[x]=o*-m+"px",R(o,o<y?-m:y<o?m:0,0))}a.continuous&&d.transitions&&(R(j(y-1),-m,0),R(j(y+1),m,0)),d.transitions||(E.style[x]=y*-m+"px"),r.style.visibility="visible",D(),d.addEventListener?(d.touch&&E.addEventListener("touchstart",w,!!d.passiveEvents&&{passive:!0}),a.draggable&&E.addEventListener("mousedown",w,!1),d.transitions&&(E.addEventListener("webkitTransitionEnd",w,!1),E.addEventListener("msTransitionEnd",w,!1),E.addEventListener("oTransitionEnd",w,!1),E.addEventListener("otransitionend",w,!1),E.addEventListener("transitionend",w,!1)),W.addEventListener("resize",w,!1)):W.onresize=L}function S(){e||(a.continuous?P(y+1):y<f.length-1&&P(y+1))}function z(t,e,n){a.callback&&a.callback(t,e,n)}function A(t,e){a.transitionEnd&&a.transitionEnd(t,e)}function j(t){return(f.length+t%f.length)%f.length}function O(){var t=y;return p<=t&&(t-=p),t}function P(t,e){if(t="number"!=typeof t?parseInt(t,10):t,y!==t){if(d.transitions){var n=Math.abs(y-t)/(y-t);if(a.continuous){var i=n;(n=-h[j(t)]/m)!==i&&(t=-n*f.length+t)}for(var o=Math.abs(y-t)-1;o--;)R(j((y<t?t:y)-o-1),m*n,0);t=j(t),R(y,m*n,e||b),R(t,0,e||b),a.continuous&&R(j(t-n),-m*n,0)}else t=j(t),function(e,n,i){if(!i)return E.style[x]=n+"px";var o=+new Date,s=setInterval(function(){var t=+new Date-o;if(i<t)return E.style[x]=n+"px",(c||a.autoRestart)&&X(),A(O(),f[y]),void clearInterval(s);E.style[x]=(n-e)*(Math.floor(t/i*100)/100)+e+"px"},4)}(y*-m,t*-m,e||b);y=t,v(function(){z(O(),f[y],n)})}}function R(t,e,n){C(t,e,n),h[t]=e}function C(t,e,n){var i=f[t],o=i&&i.style;o&&(o.webkitTransitionDuration=o.MozTransitionDuration=o.msTransitionDuration=o.OTransitionDuration=o.transitionDuration=n+"ms",o.webkitTransform=o.msTransform=o.MozTransform=o.OTransform=o.transform="translateX("+e+"px)")}function I(){(c=a.auto||0)&&(t=setTimeout(S,c))}function N(){c=0,clearTimeout(t)}function X(){N(),I()}function Q(t){return/^mouse/.test(t.type)}}return(W.jQuery||W.Zepto)&&((e=W.jQuery||W.Zepto).fn.Swipe=function(t){return this.each(function(){e(this).data("Swipe",new Swipe(e(this)[0],t))})}),Swipe});

  /*!
  * domready (c) Dustin Diaz 2014 - License MIT
  * https://github.com/ded/domready
  */
  !function(e,t){typeof module!="undefined"?module.exports=t():typeof define=="function"&&typeof define.amd=="object"?define(t):this[e]=t()}("domready",function(){var e=[],t,n=typeof document=="object"&&document,r=n&&n.documentElement.doScroll,i="DOMContentLoaded",s=n&&(r?/^loaded|^c/:/^loaded|^i|^c/).test(n.readyState);return!s&&n&&n.addEventListener(i,t=function(){n.removeEventListener(i,t),s=1;while(t=e.shift())t()}),function(t){s?setTimeout(t,0):e.push(t)}})

  rjdciSwipe = new Swipe(document.querySelector("#slider"), {
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
      let buttonTitles = [ "Route", "On Call", "Dispatch", "Transfers", "Ticket Entry" ],
        parser = new DOMParser();
      document.querySelector(".menu__list__active").classList.remove("menu__list__active");
      Array.from(document.querySelectorAll(".page")).forEach(page => {
        (page.id === elem.id) ? page.classList.add("active") : page.classList.remove("active");
        if (page.id === elem.id) {
          let eleTest = document.querySelector("a.nav[data-id='" + page.id + "']").innerHTML.split("<");
          if (buttonTitles.indexOf(eleTest[0]) !== -1) {
            let button = document.createElement("button"),
              func = "refresh" + eleTest[0].replace(/\s/g, "");
            button.setAttribute("type", "button");
            button.addEventListener("click", window["rjdci"][func]);
            button.innerHTML = eleTest[0];
            document.querySelector(".pageTitle").innerHTML = "";
            document.querySelector(".pageTitle").appendChild(button);
            if (eleTest.length > 1) {
              let htmlString = "<" + eleTest[1] + "<" + eleTest[2],
                newDom = parser.parseFromString(htmlString, "text/html"),
                element = newDom.querySelector("span");
              if (element) document.querySelector(".pageTitle").appendChild(element);
            }
          } else {
            document.querySelector(".pageTitle").innerHTML = document.querySelector("a.nav[data-id='" + page.id + "']").innerHTML;
          }
          document.querySelector("a.nav[data-id='" + page.id + "']").parentNode.classList.add("menu__list__active");
        }
      });
      scroll(0,0);
      document.dispatchEvent(rjdci.pageChange);
    }
  });
// End Swipe
})(window);

(function(rjdci, undefined) {
// Start custom events
  rjdci.loggedout = new Event("rjdci_loggedout");
  rjdci.loggedin = new Event("rjdci_loggedin");
  rjdci.resolutionchange = new Event("rjdci_resolutionchange");
  rjdci.loaded = new Event("rjdci_loaded");
  rjdci.pageChange = new Event("rjdci_pageChange");
  rjdci.refreshedRoute = new CustomEvent("rjdci_refreshed", { bubbles: true, detail: { type: () => "route" } });
  rjdci.refreshedOnCall = new CustomEvent("rjdci_refreshed", { bubbles: true, detail: { type: () => "oncall" } });
  rjdci.refreshedTransfers = new CustomEvent("rjdci_refreshed", { bubbles: true, detail: { type: () => "transfers" } });
  rjdci.refreshedDispatch = new CustomEvent("rjdci_refreshed", { bubbles: true, detail: { type: () => "dispatch" } });
  rjdci.refreshedTicketEntry = new CustomEvent("rjdci_refreshed", { bubbles: true, detail: { type: () => "ticketEntry" } });
  rjdci.triggerEvent = (element, eventName) => {
    // safari, webkit, gecko
    if (document.createEvent) {
      let evt = document.createEvent('HTMLEvents');
      evt.initEvent(eventName, true, true);
      return element.dispatchEvent(evt);
    }
    // Internet Explorer
    if (element.fireEvent) {
      return element.fireEvent('on' + eventName);
    }
  }
// End custom events
// utilities
  const parser = new DOMParser();

  rjdci.updateMap = ({ coords1, address1, coords2, address2, center, mapDivID }) => { return false; };

  ucfirst = string => string.charAt(0).toUpperCase() + string.slice(1);

  lcfirst = string => string.charAt(0).toLowerCase() + string.slice(1);

  isArray = a => {
    return Object.prototype.toString.call(a) === "[object Array]";
  };
  // https://stackoverflow.com/a/2947012/3899333
  make = desc => {
    let name = desc[0],
      attributes = desc[1],
      el = document.createElement(name),
      start = 1;
    if (typeof attributes === "object" && attributes !== null && !isArray(attributes)) {
      for (let attr in attributes) {
        el.setAttribute(attr, attributes[attr]);
      }
      start = 2;
    }

    for (let i = start; i < desc.length; i++) {
      if (isArray(desc[i])) {
        el.appendChild(make(desc[i]));
      } else {
        el.appendChild(document.createTextNode(desc[i]));
      }
    }

    return el;
  };
  // loading animation
  // New spinner from http://codepen.io/collection/HtAne/
  let circleProperties = { class: "path", cx: "50", cy: "50", r: "20", fill: "none" };
  circleProperties["stroke-width"] = "2";
  circleProperties["stroke-miterlimit"] = "10";
  const spinner = make(
  [
    "div",
    {
      class: "showbox"
    },
    [
      "svg",
      {
        class: "circular",
        viewBox: "25 25 50 50"
      },
      [
        "circle",
        circleProperties
      ]
    ]
  ]);

  rjdci.logout = count => {
    let newCount = count || 0,
      typeTest = document.querySelector("#uid").value;
    if (!typeTest.match(/(driver|dispatch)\d+/)) return document.querySelector("#logoutLink").submit();
    rjdci.fetch_template({ url: './logout', postData: { logout: 1, formKey: document.querySelector("#formKey").value } })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if (data.indexOf('error') !== -1) {
        newCount++;
        return rjdci.logout(newCount);
      } else {
        document.querySelector("#logoutLink").submit();
      }
    })
    .catch(error => {
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      newCount++;
      return newCount > 5 ? document.querySelector("#logoutLink").submit() : rjdci.logout(newCount);
    });
  };

// Start Toast
  // Use arrays to make date display pretty
  let months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  //To show notification
  rjdci.toast = (msg, options) => {
    if (!msg) return;

    if (!isArray(msg)) msg = [ msg ];

    options = options || {};

    options.title = options.title || "";

    options.time = options.time || 4000;

    options.eleClass = options.eleClass || "toast__msg";

    options.datatime = options.datatime || new Date().getTime();

    let toastContainer = document.querySelector(".toast__container");
      toastMsg = document.createElement("div"),
      d = new Date(Number(options.datatime)),
      mins = d.getMinutes();

    toastMsg.className = options.eleClass;
    toastMsg.title = options.title;

    minutes = (mins < 10) ?  `0${mins.toString()}` : mins.toString();
    msg.push(...[`${days[d.getDay()]} ${months[d.getMonth()]} ${d.getDate()}`, `${d.getHours()}:${minutes}`]);
    for (let i = 0; i < msg.length; i++) {
      toastMsg.appendChild(make(["p", msg[i]]));
    }
    toastMsg.setAttribute("data-time", options.datatime);
    if (toastMsg.className === "deliveryLocation") {
      toastMsg.addEventListener("click", eve => {
        return (eve.target.tagName.toUpperCase() === "DIV") ? eve.target.parentNode.removeChild(eve.target) : rjdci.triggerEvent(rjdci.getClosest(eve.target, "div"), "click");
      });
    }

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

    // Show toast for 4 secs or configured time and hide it
    setTimeout(() => {
      if (toastMsg.classList.contains("toast__msg")) toastMsg.classList.add("toast__msg--hide");
    }, options.time);

    // Remove the element after hiding
    // Wait one second longer than the passed value and loop over all of the children.
    setTimeout(() => {
      for (let i = 0; i < toastContainer.children.length; i++) {
        let elem = toastContainer.children[i];
        if (elem.classList.contains("toast__msg")) elem.parentNode.removeChild(elem);
      }
    }, options.time + 1000);
  };
// End Toast
// https://gomakethings.com/climbing-up-and-down-the-dom-tree-with-vanilla-javascript/
  rjdci.getClosest = function ( elem, selector ) {
    // Element.matches() polyfill
    if (!Element.prototype.matches) {
      Element.prototype.matches =
        Element.prototype.matchesSelector ||
        Element.prototype.mozMatchesSelector ||
        Element.prototype.msMatchesSelector ||
        Element.prototype.oMatchesSelector ||
        Element.prototype.webkitMatchesSelector ||
        function(s) {
          var matches = (this.document || this.ownerDocument).querySelectorAll(s),
          i = matches.length;
          while (--i >= 0 && matches.item(i) !== this) {}
          return i > -1;
        };
    }
    // Get closest match
    for ( ; elem && elem !== document; elem = elem.parentNode ) {
      if ( elem.matches( selector ) ) return elem;
    }
    return null;
  };

  pause = duration => { return new Promise(resolve => setTimeout(resolve, duration)) };

  rjdci.fetch_template = async({ url, postData = {}, method = "POST", retry = 0 }) => {
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
      await pause(250 * retry)
      return await rjdci.fetch_template({ url: url, postData: postData, method: method, retry: retry });
    }
  }

  rjdci.showLogin = () => {
    let elem = document.getElementById("confirmLogin");
    if (!(elem.offsetWidth > 0 && elem.offsetHeight > 0)) elem.classList.remove("hide");
    rjdci.centerForm(elem);
    document.dispatchEvent(rjdci.loggedout);
  }

  rjdci.enableApp = () => {
    document.querySelector("header").classList.remove("loggedout");
    document.querySelector(".menu__header").classList.remove("loggedoutHeader");
    Array.from(document.querySelectorAll("button")).forEach(element => {
      element.disabled = false;
    });
  }

  rjdci.disableApp = () => {
    let header = document.querySelector("header"),
        menuHeader = document.querySelector(".menu__header");
    if (header === undefined || menuHeader === undefined || header.classList.contains("loggedout")) {
      return false;
    }
    header.classList.add("loggedout");
    menuHeader.classList.add("loggedoutHeader");
    Array.from(document.querySelectorAll("button")).forEach(element => {
      if(element.id !== "confirm" && element.id !== "cancel") element.disabled = true;
    });
  }
// isTarget is called by datalist validation
  rjdci.isTarget = ele => {
    let targets = [ "billTo", "dispatchedTo", "dispatchedByUser", "shippingCountry", "billingCountry", "pCountry", "dCountry" ];
    for (let i = 0; i < targets.length; i++) {
      if (ele.classList.contains(targets[i])) return true;
    }
    return false;
  }
// count organization members for ticket page
  rjdci.disableButtonsTickets = () => {
    let howMany = 0,
        boxes = document.querySelectorAll("#ticket_query .orgMember"),
        compareMembers = document.querySelector("#compareMembersTickets");
    for (let i = 0; i < boxes.length; i++) {
      if (boxes[i].checked === true) howMany++;
    }
    compareMembers.disabled = !(howMany > 1 && document.querySelector("#display").value === "chart");
    if (compareMembers.disabled === true) compareMembers.checked = false;
  }

  rjdci.disable_scroll = () => {
    window.ontouchmove  = e => e.preventDefault();
  }

  rjdci.enable_scroll = () => {
    window.ontouchmove  = e => true;
  }

  rjdci.fixDeadRunButton = () => {
    let element = document.querySelector(".cancelRun:not(.hide)");
    if (!element) return;
    let h1 = element.offsetHeight;
    Array.from(document.querySelectorAll(".deadRun")).forEach(element => {
      element.innerHTML = "Dead Run";
      if (element.offsetHeight > h1) element.innerHTML = "D. Run";
    });
  }

  rjdci.centerForm = form => {
    let obj = document.body.getBoundingClientRect(),
        pageWidth = obj.width,
        obj2 = form.getBoundingClientRect(),
        eleWidth = obj2.width,
        diff = (pageWidth - eleWidth) / 2;
    form.style.left = diff + "px";
  }

  isStopBeforeNoon = ticket => {
    timestamp = Number(ticket.querySelector(".timing").innerText) * 1000;
    let d = new Date(timestamp);
    return d.getHours() < 12;
  };

  isStopAfterNoon = ticket => {
    timestamp = Number(ticket.querySelector(".timing").innerText) * 1000;
    let d = new Date(timestamp);
    return d.getHours() >= 12;
  };

  sortRoute = () => {
    let items,
      container = document.querySelector("#route"),
      overnight = (document.getElementById("overnightFlag")) ?
        document.getElementById("overnightFlag").innerText : 0,
      morning = Array.from(container.querySelectorAll(".sortable")).filter(isStopBeforeNoon),
      evening = Array.from(container.querySelectorAll(".sortable")).filter(isStopAfterNoon);
    morning.sort((a,b) => {
      return (a.querySelector(".timing").textContent > b.querySelector(".timing").textContent) ? 1 : -1;
    });
    evening.sort((a,b) => {
      return (a.querySelector(".timing").textContent > b.querySelector(".timing").textContent) ? 1 : -1;
    });
    items  = (overnight === "1") ? [...evening, ...morning] : [...morning, ...evening];
    let docFrag = document.createDocumentFragment();
    items.forEach(element => { docFrag.appendChild(element); });
    container.appendChild(docFrag);
  }

  rjdci.refreshRoute = async () => {
    let localSpinner = spinner.cloneNode(true);
    document.querySelector("#route").innerHTML = "";
    document.querySelector("#route").appendChild(localSpinner);
    scrollTo(0,0);
    await rjdci.fetch_template({ url: "./refreshRoute.php", postData: { formKey: document.querySelector("#formKey").value } })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      if (data.indexOf("Session Error") !== -1) {
        document.querySelector("#function").value = "refreshRoute";
        return rjdci.showLogin();
      }
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if (data.indexOf("error") !== -1) throw new Error(data);
      let newDom = parser.parseFromString(data, "text/html"),
        docFrag = document.createDocumentFragment();
      Array.from(newDom.querySelectorAll(".sortable, .result, #overnightFlag")).forEach(element => {
        docFrag.appendChild(element);
      });
      setTimeout(() => {
        document.querySelector("#route").removeChild(localSpinner);
        document.querySelector("#route").appendChild(docFrag);
        rjdci.assignListeners();
        rjdci.fixDeadRunButton();
        sortRoute();
        document.dispatchEvent(rjdci.refreshedRoute);
      }, 2000);
    })
    .catch(error => {
      document.querySelector("#route").appendChild(make(
        [
          "p",
          {class: "center"},
          [
            "span",
            {class: "error"},
            "Error"
          ],
          `: ${error.message}`
        ]
      ));
    });
  }

  countOnCallTickets = oldCount => {
    let newCount = Array.from(document.querySelectorAll("#on_call .tickets")).length;
    if (newCount > oldCount) {
      document.querySelector(".alert").classList.add("onCallAlert");
      document.querySelector(".alert").innerHTML = "!";
      document.querySelector("#newUpdate").classList.remove("hide");
    }
    if (newCount === 0) document.querySelector(".alert").classList.remove("onCallAlert");
    let cList = document.querySelector(".alert").getAttribute("class").split(/\s+/);
    if (cList.length === 1) document.querySelector(".alert").innerHTML = "";
    Array.from(document.querySelectorAll(".ticketCount")).forEach(element => { element.innerHTML = newCount; });
  }

  sortOnCall = () => {
    let container = document.querySelector("#on_call"),
        items = Array.from(container.querySelectorAll(".sortable"));
    items.sort((a,b) => {
      return (a.querySelector(".timing").textContent > b.querySelector(".timing").textContent) ? 1 : -1;
    });
    let docFrag = document.createDocumentFragment();
    items.forEach(element => { docFrag.appendChild(element); });
    container.appendChild(docFrag);
  }

  rjdci.refreshOnCall = async () => {
    let ticketCount = document.querySelector(".ticketCount").innerHTML,
      localSpinner = spinner.cloneNode(true);
    document.querySelector("#on_call").innerHTML = "";
    document.querySelector("#on_call").appendChild(localSpinner);
    scrollTo(0,0);
    await rjdci.fetch_template({ url: "./refreshOnCall.php", postData: { formKey: document.querySelector("#formKey").value } })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      if (data.indexOf("Session Error") !== -1) {
        document.querySelector("#function").value = "refreshOnCall";
        return rjdci.showLogin();
      }
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if (data.indexOf("error") !== -1) throw new Error(data);
      let newDom = parser.parseFromString(data, "text/html"),
        docFrag = document.createDocumentFragment();
      Array.from(newDom.querySelectorAll(".sortable, .result")).forEach(element => {
        docFrag.appendChild(element);
      });
      setTimeout(() => {
        document.querySelector("#on_call").removeChild(localSpinner);
        document.querySelector("#on_call").appendChild(docFrag);
        rjdci.assignListeners();
        rjdci.fixDeadRunButton();
        sortOnCall();
        countOnCallTickets(ticketCount);
        document.dispatchEvent(rjdci.refreshedOnCall);
      }, 2000);
    })
    .catch(error => {
      console.error(error.message);
      document.querySelector("#on_call").appendChild(make(
        [
          "p",
          {class: "center"},
          [
            "span",
            {class: "error"},
            "Error"
          ],
          `: ${error.message}`
        ]
      ));
    });
  }

  countInitOnCall = () => {
    let newCount = Array.from(document.querySelectorAll("#on_call .tickets")).length;
    Array.from(document.querySelectorAll(".ticketCount")).forEach( element => { element.innerHTML = newCount; });
    if (newCount > 0) {
      document.querySelector(".alert").classList.add("onCallAlert");
      document.querySelector(".alert").innerHTML = "!";
    }
  }

  rjdci.refreshTicketEntry = async () => {
    let elem = document.querySelector("#deliveryRequest"),
      workspace = elem.parentNode;
      target = workspace.querySelector(".subContainer"),
      localSpinner = spinner.cloneNode(true);
    workspace.removeChild(target);
    workspace.removeChild(elem);
    workspace.appendChild(localSpinner);
    scrollTo(0,0);
    await rjdci.fetch_template({ url: "./refreshTicketForm.php", postData: { edit: 1, formKey: document.querySelector("#formKey").value } })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      if (data.indexOf("Session Error") !== -1) {
        document.querySelector("#function").value = "refreshTicketForm";
        return rjdci.showLogin();
      }
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if (data.indexOf("error") !== -1) throw new Error(data);
      let newDom = parser.parseFromString(data, "text/html"),
        docFrag = document.createDocumentFragment();
      Array.from(newDom.querySelectorAll("#deliveryRequest")).forEach(element => {
        docFrag.appendChild(element);
      });
      setTimeout(() => {
        workspace.removeChild(localSpinner);
        workspace.appendChild(docFrag);
        workspace.appendChild(target);
        rjdci.assignListeners();
        document.dispatchEvent(rjdci.refreshedTicketEntry);
      }, 2000);
    })
    .catch(error => {
      workspace.innerHTML = "";
      workspace.appendChild(make(
        [
          "p",
          {class: "center"},
          [
            "span",
            {class: "error"},
            "Error"
          ],
          `: ${error.message}`
        ]
      ));
      target.classList.remove("hide");
    });
  }

  rjdci.countDispatch = oldCount => {
    let newCount = (document.querySelector("#dispatch .tickets")) ? Array.from(document.querySelectorAll("#dispatch .tickets")).length : 0,
    target = document.querySelector(".alert");
    if (newCount > oldCount) {
      target.classList.add("dispatchAlert");
      target.innerHTML = "!";
    }
    if (newCount === 0) target.classList.remove("dispatchAlert");
    if (target.classList.length === 1) target.innerHTML = "";
    Array.from(document.querySelectorAll(".dispatchCount")).forEach(element => { element.innerHTML = newCount; } );
  }

  rjdci.refreshDispatch = async () => {
    let oldCount = Number(document.querySelector(".dispatchCount").innerHTML) - 1,
      localSpinner = spinner.cloneNode(true);
    document.querySelector("#dispatch").innerHTML = "";
    document.querySelector("#dispatch").appendChild(localSpinner);
    scrollTo(0,0);
    await rjdci.fetch_template({ url: "./refreshDispatch.php", postData: { formKey: document.querySelector("#formKey").value } })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      if (data.indexOf("Session Error") !== -1) {
        document.querySelector("#function").value = "refreshDispatch";
        return rjdci.showLogin();
      }
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if (data.indexOf("error") !== -1) throw new Error(data);
      let newDom = parser.parseFromString(data, "text/html"),
        docFrag = document.createDocumentFragment();
      Array.from(newDom.querySelectorAll(".sortable, .result")).forEach(element => {
        docFrag.appendChild(element);
      });
      setTimeout(() => {
        document.querySelector("#dispatch").removeChild(localSpinner);
        document.querySelector("#dispatch").appendChild(docFrag);
        rjdci.assignListeners();
        rjdci.countDispatch(oldCount);
        Array.from(document.querySelectorAll("#dispatch .dTicket")).forEach(element => {
          element.removeEventListener("click", rjdci.stepTicket);
          element.addEventListener("click", rjdci.stepTicket);
        });
        document.dispatchEvent(rjdci.refreshedDispatch);
      }, 2000);
    })
    .catch(error => {
      document.querySelector("#dispatch").innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
    });
  }

  countInitDispatch = () => {
    let newCount = Array.from(document.querySelectorAll("#dispatch .tickets")).length;
    Array.from(document.querySelectorAll(".dispatchCount")).forEach(element => { element.innerHTML = newCount; } );
    if (newCount > 0) {
      document.querySelector(".alert").classList.add("dispatchAlert");
      document.querySelector(".alert").innerHTML = "!";
    }
  }

  countTransferTickets = oldCount => {
    let newCount = Array.from(document.querySelectorAll("#transfers .sortable")).length,
    target = document.querySelector(".alert");
    if (newCount > oldCount) {
      target.classList.add("transfersAlert");
      target.innerHTML = "!";
      document.querySelector("#newUpdate").classList.remove("hide");
    }
    if (newCount === 0) target.classList.remove("transfersAlert");
    if (target.classList.length === 1) target.innerHTML = "";
    Array.from(document.querySelectorAll(".transfersCount")).forEach(element => { element.innerHTML = newCount; } );
  }

  rjdci.refreshTransfers = async () => {
    let transferCount = document.querySelector(".transfersCount").innerHTML,
      localSpinner = spinner.cloneNode(true);
    document.querySelector("#transfers").innerHTML = "";
    document.querySelector("#transfers").appendChild(localSpinner);
    scrollTo(0,0);
    await rjdci.fetch_template({ url: "./refreshTransfers.php", postData: { formKey: document.querySelector("#formKey").value } })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      if (data.indexOf("Session Error") !== -1) {
        document.querySelector("#function").value = "refreshTransfers";
        return rjdci.showLogin();
      }
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if (data.indexOf("error") !== -1) throw new Error(data);
      let newDom = parser.parseFromString(data, "text/html"),
        docFrag = document.createDocumentFragment();
      Array.from(newDom.querySelectorAll(".sortable, .result")).forEach(element => {
        docFrag.appendChild(element);
      });
      setTimeout(() => {
        document.querySelector("#transfers").removeChild(localSpinner);
        document.querySelector("#transfers").appendChild(docFrag);
        rjdci.assignListeners();
        countTransferTickets(transferCount);
        document.dispatchEvent(rjdci.refreshedTransfers);
      }, 2000);
    })
    .catch(error => {
      document.querySelector("#transfers").innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
    });
  }

  countInitTransfers = () => {
    let newCount = Array.from(document.querySelectorAll("#transfers .sortable")).length;
    Array.from(document.querySelectorAll(".transfersCount")).forEach(element => { element.innerHTML = newCount; } );
    if (newCount > 0) {
      document.querySelector(".alert").classList.add("transfersAlert");
      document.querySelector(".alert").innerHTML = "!";
    }
  }

  rjdci.assignLinkValues = () => {
    let eles = document.getElementsByClassName("nav"),
      buttonTitles = [ "Route", "On Call", "Dispatch", "Transfers", "Ticket Entry" ];
    for (let i = 0; i < eles.length; i++) {
      eles[i].setAttribute("data-value", i);
      if (i === 0) {
        let eleTest = eles[i].innerHTML.split("<");
        if (buttonTitles.indexOf(eleTest[0]) !== -1) {
          let button = document.createElement("button"),
            func = "refresh" + eleTest[0].replace(/\s/g, "");
          button.setAttribute("type", "button");
          button.addEventListener("click", window["rjdci"][func]);
          button.innerHTML = eleTest[0];
          document.querySelector(".pageTitle").innerHTML = "";
          document.querySelector(".pageTitle").appendChild(button);
          if (eleTest.length > 1) {
            let htmlString = "<" + eleTest[1] + "<" + eleTest[2],
              newDom = parser.parseFromString(htmlString, "text/html"),
              element = newDom.querySelector("span");
            if (element) document.querySelector(".pageTitle").appendChild(element);
          }
        } else {
          document.querySelector(".pageTitle").innerHTML = eles[i].innerHTML;
        }
      }
    }
  }

  rjdci.populatePage = async () => {
    let funcs = [];
    Array.from(document.querySelectorAll(".page")).forEach((element, index) => {
      if (element.getAttribute("data-function") !== undefined && element.getAttribute("data-function") !== "") {
        funcs.push(element.getAttribute("data-function"));
      }
    });
    await rjdci.fetch_template({ url: "./initApp.php", postData: { functions: funcs, formKey: document.querySelector("#formKey").value } })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      if (data.indexOf("Session Error") !== -1) {
        document.getElementById("function").value = "populatePage";
        return rjdci.showLogin();
      }
      document.getElementById("formKey").value = Number(document.getElementById("formKey").value) + 1;
      let obj,
        breakFunction = false;
      try {
        obj = JSON.parse(data);
      } catch(error) {
        error.message = error.message + "\n" + data;
        throw error;
      }
      for (let i = 0; i < obj.length; i++) {
        document.querySelectorAll(".page").forEach((elem, index) => {
          if (Number(elem.getAttribute("data-index")) === i) elem.innerHTML = obj[i];
        });
      }
      scrollTo(0,0);
      if (document.getElementById("route") !== null) sortRoute();
      if (document.getElementById("on_call") !== null) {
        sortOnCall();
        countInitOnCall();
      }
      if (document.getElementsByClassName("deadRun") !== null) rjdci.fixDeadRunButton();
      if (document.getElementById("dispatch") !== null) countInitDispatch();
      if (document.getElementById("transfers") !== null) countInitTransfers();
    })
    .catch(error => {
      Array.from(document.querySelectorAll(".page")).forEach(element => {
        element.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      });
    });
  }

  rjdci.deliveryLocation = ({ ticket_index = [], step = [] }) => {
    if (typeof navigator.permissions === "undefined" || typeof navigator.geolocation === "undefined") return rjdci.toast("Location Not Available");
    let success_count = 0,
      error_count = 0,
      max_attempt = 5,
      min_accuracy = 10,
      watch_id = null,
      result = false,
      toast_options = {};
    toast_options.title = "Updating Location";
    toast_options.eleClass = "deliveryLocation";
    rjdci.toast([ "Updating Location", "Do not", "disable screen" ], toast_options);
    navigator.permissions.query({name: "geolocation"}).then(PermissionStatus => {
      let options = { enableHighAccuracy: true, timeout: 25000, maximumAge: 0},
        success = pos => {
          console.log(pos);
          success_count++;
          if (success_count > 1) {
            result = (result && result.coords.accuracy < pos.coords.accuracy) ? result : pos;
          } else {
            result = pos;
          }
          if (success_count > max_attempt || (success_count > 2 && result.coords.accuracy < min_accuracy)) {
            navigator.geolocation.clearWatch(watch_id);
            return sendResult(result);
          }
        },
        error = err => {
          error_count++;
          rjdci.toast([ "Location Not Available", err.message, "Tap to dismiss" ], toast_options);
          if (success_count > max_attempt || error_count > max_attempt) {
            navigator.geolocation.clearWatch(watch_id);
            return sendResult(result);
          }
        },
        sendResult = async data => {
          if (!data) return rjdci.toast([ "Location Not Available", "Tap to dismiss" ], toast_options);
          if (ticket_index.length !== step.length || ticket_index.length === 0) return rjdci.toast([ "Location Data Error", "Tap to dismiss" ], toast_options);
          let postData = {},
            tempData = {};
          postData.formKey = document.querySelector("#formKey").value;
          if (ticket_index.length > 1) {
            postData.multiTicket = [];
            ticket_index.forEach((val, index) => {
              tempData.ticket_index = val;
              tempData[step[ index ]+"Lat"] = data.coords.latitude;
              tempData[step[ index ]+"Lng"] = data.coords.longitude;
              postData.multiTicket[ index ] = tempData;
              tempData = {};
            });
          } else {
            postData.ticket_index = ticket_index[0];
            postData[step[0]+"Lat"] = data.coords.latitude;
            postData[step[0]+"Lng"] = data.coords.longitude;
          }
          await rjdci.fetch_template({ url: "./updateTicket.php", postData: postData })
          .then(result => {
            if (typeof result === "undefined") throw new Error("Result Undefined");
            if (result.ok) {
              return result.text();
            } else {
              throw new Error(result.status + " " + result.statusText);
            }
          })
          .then(data => {
            if (data.indexOf("Session Error") !== -1) throw new Error("Session Error");
            document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
            if (data.indexOf("error") !== - 1) throw new Error(data);
            return rjdci.toast([ "Location Updated", "Tap to dismiss" ], toast_options);
          })
          .catch(error => {
            console.error(error.message);
            return rjdci.toast([ error.message, "Tap to dismiss" ], toast_options);
          });
        };
      if (PermissionStatus.state == "granted") {
        watch_id = navigator.geolocation.watchPosition(success, error, options);
      } else if (PermissionStatus.state == "prompt") {
        navigator.geolocation.getCurrentPosition(pos => {return});
      } else if (PermissionStatus.state == "denied") {
        return false;
      }
      PermissionStatus.onchange = () => {
        if (PermissionStatus.state === "granted") {
          watch_id = navigator.geolocation.watchPosition(success, error, options);
        } else if (PermissionStatus.state == "prompt") {
          return false;
        } else if (PermissionStatus.state == "denied") {
          return false;
        }
      }
    });
  }

  getCancelThis = () => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Go Back";
    element.classList.add("cancelThis");
    element.addEventListener("click", rjdci.cancelThis);
    return element;
  }

  rjdci.cancelThis = eve => {
    let parent = rjdci.getClosest(eve.target, ".sortable")
    Array.from(parent.querySelectorAll("button")).forEach(elem => { elem.disabled = false; });
    eve.target.parentNode.innerHTML = "";
  }

  getStepTicket = form => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Confirm";
    element.classList.add("stepTicket");
    element.setAttribute("form", form);
    element.addEventListener("click", rjdci.stepTicket);
    return element;
  }

  rjdci.stepTicket = async eve => {
    eve.preventDefault();
    let postData = {},
      breakFunction = false,
      step = "",
      workspace = rjdci.getClosest(eve.target, ".message2"),
      functionFlag = rjdci.getClosest(eve.target, ".page").getAttribute("id"),
      ele = document.createElement("span");
    if (workspace === null) workspace = rjdci.getClosest(eve.target, "form").querySelector(".message2");
    Array.from(document.querySelectorAll("input[form="+eve.target.getAttribute("form")+"], textarea[form="+eve.target.getAttribute("form")+"]")).forEach(element => {
      if (element.getAttribute("name") !== "latitude" && element.getAttribute("name") !== "longitude") {
        if (element.required === true && element.value === "") {
          breakFunction = true;
          element.classList.add("elementError");
          setTimeout(() => { element.classList.remove("elementError"); }, 3000);
        }
        postData[element.getAttribute("name")] = element.value;
      }
    });
    postData.formKey = document.querySelector("#formKey").value;
    if (breakFunction) {
      return;
    }
    ele.classList.add("ellipsis");
    ele.innerHTML = ".";
    workspace.innerHTML = "";
    workspace.appendChild(ele);
    let forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
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
    switch (document.querySelector('.step[form="'+eve.target.getAttribute("form")+'"]').value) {
      case "pickedUp": step = "p"; break;
      case "delivered": step = "d"; break;
      case "returned": step = "d2"; break;
    }
    if (step) rjdci.deliveryLocation({ ticket_index: [ postData.ticket_index ], step: [ step ] });
    await rjdci.fetch_template({ url: "./updateStep.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      clearInterval(dots);
      if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if(data.indexOf("error") === - 1) {
        workspace.innerHTML = data;
        setTimeout( () => {
          switch(functionFlag) {
            case "route": rjdci.refreshRoute(); break;
            case "on_call": rjdci.refreshOnCall(); break;
            case "dispatch": rjdci.refreshDispatch(); break;
          }
        }, 3000);
      } else {
        throw new Error(data);
      }
    })
    .catch(error => {
      console.error(error.message);
      clearInterval(dots);
      workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      setTimeout(() => {
        workspace.innerHTML = "";
        Array.from(rjdci.getClosest(workspace, ".sortable").querySelectorAll("button")).forEach(element => {
          element.disabled = false;
        });
      }, 5000);
    });
  }

  getStepAll = () => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Confirm";
    element.classList.add("stepAll");
    element.addEventListener("click", rjdci.stepAll);
    return element;
  }

  rjdci.stepAll = async eve => {
    eve.preventDefault();
    let postData = {},
      multiTicket = [],
      data = {},
      locationData = { step: [], ticket_index: [] },
      workspace = rjdci.getClosest(eve.target, ".message2"),
      ticketGroup = rjdci.getClosest(eve.target, ".sortable"),
      ele = document.createElement("span"),
      sigTest = ticketGroup.querySelector(".printName");
    if (sigTest.required === true && sigTest.value === "") {
      sigTest.classList.add("elementError");
      setTimeout(() => { sigTest.classList.remove("elementError"); }, 3000);
      return false;
    }
    ele.classList.add("ellipsis");
    ele.innerHTML = ".";
    workspace.innerHTML = "";
    workspace.appendChild(ele);
    let forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
    Array.from(ticketGroup.querySelectorAll(".routeStop")).forEach((element, index) => {
      Array.from(document.querySelectorAll("input[form='" + element.getAttribute("id") + "'], textarea[form='" + element.getAttribute("id") + "']")).forEach(input => {
        if (input.getAttribute("name") !== "latitude" && input.getAttribute("name") !== "longitude") data[input.getAttribute("name")] = input.value;
        if (input.getAttribute("name") === "step") {
          switch (input.value) {
            case "pickedUp": locationData.step.push("p"); break;
            case "delivered": locationData.step.push("d"); break;
            case "returned": locationData.step.push("d2"); break;
          }
        }
        if (input.getAttribute("name") === "ticket_index") locationData.ticket_index.push(input.value);
      });
      multiTicket[index] = data;
      data = {};
    });
    postData.multiTicket = multiTicket;
    postData.formKey = document.querySelector("#formKey").value;
    postData.printName = sigTest.value;
    postData.sigImage = ticketGroup.querySelector(".sigImage").value;
    rjdci.deliveryLocation(locationData);
    await rjdci.fetch_template({ url: "./updateStep.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      clearInterval(dots);
      if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if(data.indexOf("error") === - 1) {
        workspace.innerHTML = data;
        setTimeout(rjdci.refreshRoute, 3000);
      } else {
        throw new Error(data);
      }
    })
    .catch(error => {
      console.error(error.message);
      clearInterval(dots);
      workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      setTimeout(() => {
        workspace.innerHTML = "";
        Array.from(rjdci.getClosest(workspace, ".sortable").querySelectorAll("button")).forEach(element => {
          element.disabled = false;
        });
      }, 5000);
    });
  }

  getCancelTicket = (form, type) => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Confirm";
    element.classList.add("confirm" + type);
    element.setAttribute("form", form);
    element.addEventListener("click", rjdci.confirmCancel);
    return element;
  }

  rjdci.confirmCancel = async eve => {
    eve.preventDefault();
    let postData = {},
      cancelValues = [ 'ticket_index', 'notes', 'ticketBase' ],
      workspace = rjdci.getClosest(eve.target, ".message2"),
      step = "",
      ele = document.createElement("span");
    ele.classList.add("ellipsis");
    ele.innerHTML = ".";
    workspace.innerHTML = "";
    workspace.appendChild(ele);
    let forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
    Array.from(document.querySelectorAll("input[form="+eve.target.getAttribute("form")+"], textarea[form="+eve.target.getAttribute("form")+"]")).forEach(element => {
      if (cancelValues.indexOf(element.getAttribute("name")) !== -1) postData[element.getAttribute("name")] = element.value;
    });
    switch(eve.target.getAttribute("class")) {
      case "confirmDelete": postData.action = "delete"; break;
      case "confirmCancelRun": postData.action = "cancel"; break;
      case "confirmDeadRun": postData.action = "deadRun"; step = "p"; break;
      case "confirmDeclined": postData.action = "declined"; step = "d"; break;
    }
    postData.formKey = document.querySelector("#formKey").value;
    if (step) rjdci.deliveryLocation({ ticket_index: [ postData.ticket_index ], step: [ step ] });
    await rjdci.fetch_template({ url: "./deleteContractTicket.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      clearInterval(dots);
      if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if(data.indexOf("error") === - 1) {
        workspace.innerHTML = data;
        setTimeout(async() => {
          switch (rjdci.getClosest(workspace, ".page").getAttribute("id")) {
            case "route": return await rjdci.refreshRoute();
            case "on_call" : return await rjdci.refreshOnCall();
          }
        }, 3000);
      } else {
        throw new Error(data);
      }
    })
    .catch(error => {
      console.error(error.message);
      clearInterval(dots);
      workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      setTimeout(() => {
        workspace.innerHTML = "";
        Array.from(rjdci.getClosest(workspace, ".sortable").querySelectorAll("button")).forEach(element => {
          element.disabled = false;
        });
      }, 5000);
    });
  }

  getTransferTicket = form => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Confirm";
    element.classList.add("confirmTransfer");
    element.setAttribute("form", form);
    element.addEventListener("click", rjdci.transferTicket);
    return element;
  }

  rjdci.transferTicket = async eve => {
    eve.preventDefault();
    let postData = {},
      workspace = rjdci.getClosest(eve.target, ".message2"),
      ticket = rjdci.getClosest(eve.target, ".tickets"),
      functionFlag = rjdci.getClosest(eve.target, ".page").getAttribute("id"),
      ele = document.createElement("span");
    ele.classList.add("ellipsis");
    ele.innerHTML = ".";
    postData.pendingReceiver = workspace.querySelector(".pendingReceiver").value;
    if (!postData.pendingReceiver) {
      workspace.querySelector(".pendingReceiver").classList.add("elementError");
      setTimeout(() => {
        workspace.querySelector(".pendingReceiver").classList.remove("elementError");
        Array.from(workspace.querySelectorAll("button")).forEach(element => { element.disabled = false; } );
      }, 3000 );
      return false;
    }
    workspace.innerHTML = "";
    workspace.appendChild(ele);
    let forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
    postData.ticket_index = ticket.querySelector(".ticket_index").value;
    postData.transfers = ticket.querySelector(".transfers").value;
    postData.notes = ticket.querySelector(".notes").value;
    postData.action = "transfer";
    postData.formKey = document.querySelector("#formKey").value;
    postData.TransferState = 1;
    await rjdci.fetch_template({ url: "./deleteContractTicket.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      clearInterval(dots);
      if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if(data.indexOf("error") === - 1) {
        return workspace.innerHTML = data;
      } else {
        throw new Error(data);
      }
    })
    .then(async third => {
      return await rjdci.refreshTransfers();
    })
    .then(async fourth => {
      switch(functionFlag) {
        case "route": return await rjdci.refreshRoute();
        case "on_call": return await rjdci.refreshOnCall();
      }
    })
    .catch(error => {
      console.error(error.message);
      clearInterval(dots);
      workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      setTimeout(() => {
        workspace.innerHTML = "";
        Array.from(rjdci.getClosest(workspace, ".sortable").querySelectorAll("button")).forEach(element => {
          element.disabled = false;
        });
      }, 5000);
    });
  }

  getTransferGroup = form => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Confirm";
    element.classList.add("confirmTransferGroup");
    element.setAttribute("form", form);
    element.addEventListener("click", rjdci.transferGroup);
    return element;
  }

  rjdci.transferGroup = async eve => {
    let multiTicket = [],
      data = {},
      postData = {},
      workspace = rjdci.getClosest(eve.target, ".message2"),
      ticketGroup = rjdci.getClosest(eve.target, ".sortable"),
      ele = document.createElement("span");
    ele.classList.add("ellipsis");
    ele.innerHTML = ".";
    pendingReceiver = workspace.querySelector(".pendingReceiver").value;
    if (!pendingReceiver) {
      workspace.querySelector(".pendingReceiver").classList.add("elementError");
      setTimeout(() => {
        workspace.querySelector(".pendingReceiver").classList.remove("elementError");
        Array.from(workspace.querySelectorAll("button")).forEach(element => { element.disabled = false; } );
      }, 3000 );
      return false;
    }
    workspace.innerHTML = "";
    workspace.appendChild(ele);
    let forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
    Array.from(ticketGroup.querySelectorAll(".routeStop")).forEach((element, index) => {
      Array.from(document.querySelectorAll("input[form='" + element.getAttribute("id") + "'], textarea[form='" + element.getAttribute("id") + "']")).forEach(input => {
        data[input.getAttribute("name")] = input.value;
      });
      data.pendingReceiver = pendingReceiver;
      data.transferState = 1;
      multiTicket[index] = data;
      data = {};
    });
    postData.multiTicket = multiTicket;
    postData.TransferState = 1;
    postData.action = "transfer";
    postData.formKey = document.querySelector("#formKey").value;
    await rjdci.fetch_template({ url: "./deleteContractTicket.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      clearInterval(dots);
      if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if(data.indexOf("error") === - 1) {
        return workspace.innerHTML = data;
      } else {
        throw new Error(data);
      }
    })
    .then(async third => {
      return await rjdci.refreshTransfers();
    })
    .then(async fourth =>{
      return await rjdci.refreshRoute();
    })
    .catch(error => {
      console.error(error.message);
      clearInterval(dots);
      workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      setTimeout(() => {
        workspace.innerHTML = "";
        Array.from(rjdci.getClosest(workspace, ".sortable").querySelectorAll("button")).forEach(element => {
          element.disabled = false;
        });
      }, 5000);
    });
  }

  getTransferButton = type => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Confirm";
    element.classList.add("confirm"+ucfirst(type));
    element.addEventListener("click", processTransfer);
    return element;
  }

  rjdci.processTransfer = async eve => {
    let testArr = [ "confirmCancelTransfer", "confirmDeclineTransfer", "confirmAcceptTransfer" ],
      testClass,
      postData = {},
      workspace = rjdci.getClosest(eve.target, ".message2"),
      ticket = rjdci.getClosest(eve.target, ".tickets"),
      ele = document.createElement("span");
    ele.classList.add("ellipsis");
    ele.innerHTML = ".";
    Array.from(ticket.querySelectorAll("button")).forEach(element => {
      element.disabled = true;
    });
    workspace.innerHTML = "";
    workspace.appendChild(ele);
    let forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
    postData.formKey = document.querySelector("#formKey").value;
    postData.action = "transfer";
    postData.pendingReceiver = 0;
    Array.from(ticket.querySelectorAll("input, textarea")).forEach(element => {
      postData[element.getAttribute("name")] = element.value;
    });
    for (let i = 0; i < eve.target.classList.length; i++) {
      if (testArr.indexOf(eve.target.classList[i] !== - 1)) {
        testClass = eve.target.classList[i];
        break;
      }
    }
    switch(testClass) {
      case "confirmCancelTransfer":
        postData.TransferState = 2;
      break;
      case "confirmDeclineTransfer":
        postData.TransferState = 3;
      break;
      case "confirmAcceptTransfer":
        postData.TransferState = 4;
      break;
    }
    await rjdci.fetch_template({ url: "./deleteContractTicket.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      clearInterval(dots);
      if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if(data.indexOf("error") === - 1) {
        return workspace.innerHTML = data;
      } else {
        throw new Error(data);
      }
    })
    .then(async third => {
      if (ticket.querySelector(".rNum").innerText === "0") {
        return await rjdci.refreshOnCall();
      } else {
        return await rjdci.refreshRoute();
      }
    })
    .then(async fourth => {
      return await rjdci.refreshTransfers();
    })
    .catch(error => {
      console.error(error.message);
      clearInterval(dots);
      workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      setTimeout(() => {
        workspace.innerHTML = "";
        Array.from(ticket.querySelectorAll("button")).forEach(element => {
          element.disabled = false;
        });
      }, 5000);
    });
  }

  getTransferAllButton = type => {
    let element = document.createElement("button");
    element.type = "button";
    element.innerHTML = "Confirm";
    element.classList.add("confirm"+ucfirst(type));
    element.addEventListener("click", rjdci.processTransferAll);
    return element;
  }

  rjdci.processTransferAll = async eve => {
    let transferState,
      testArr = [ "confirmCancelTransferGroup", "confirmDeclineTransferGroup", "confirmAcceptTransferGroup" ],
      testClass,
      contractFlag = true,
      ticketData = {},
      postData = {},
      workspace = rjdci.getClosest(eve.target, ".message2"),
      ticketGroup = rjdci.getClosest(eve.target, ".sortable"),
      ele = document.createElement("span");
    postData.multiTicket = [];
    postData.formKey = document.querySelector("#formKey").value;
    ele.classList.add("ellipsis");
    ele.innerHTML = ".";
    workspace.innerHTML = "";
    workspace.appendChild(ele);
    let forward = true,
      dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
      for (let i = 0; i < eve.target.classList.length; i++) {
      if (testArr.indexOf(eve.target.classList[i] !== - 1)) {
        testClass = eve.target.classList[i];
        break;
      }
    }
    switch(testClass) {
      case "confirmCancelTransferGroup": transferState = 2; break;
      case "confirmDeclineTransferGroup": transferState = 3; break;
      case "confirmAcceptTransferGroup": transferState = 4; break;
    }
    postData.TransferState = transferState;
    Array.from(ticketGroup.querySelectorAll(".routeStop")).forEach((element, index) => {
      Array.from(document.querySelectorAll("input[form='" + element.getAttribute("id") + "'], textarea[form='" + element.getAttribute("id") + "']")).forEach(elem => {
        ticketData[elem.getAttribute("name")] = elem.value;
      });
      ticketData.transferState = transferState;
      ticketData.action = "transfer";
      postData.multiTicket[ index ] = ticketData;
      ticketData = {};
    });
    await rjdci.fetch_template({ url: "./deleteContractTicket.php", postData: postData })
    .then(result => {
      if (typeof result === "undefined") throw new Error("Result Undefined");
      if (result.ok) {
        return result.text();
      } else {
        throw new Error(result.status + " " + result.statusText);
      }
    })
    .then(data => {
      clearInterval(dots);
      if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
      document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
      if(data.indexOf("error") === - 1) {
        return workspace.innerHTML = data;
      } else {
        throw new Error(data);
      }
    })
    .then(async third => {
      return await rjdci.refreshRoute();
    })
    .then(async fourth => {
      return await rjdci.refreshTransfers();
    })
    .catch(error => {
      console.error(error.message);
      clearInterval(dots);
      workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
      setTimeout(() => {
        workspace.innerHTML = "";
        Array.from(ticketGroup.querySelectorAll("button")).forEach(element => {
          element.disabled = false;
        });
      }, 5000);
    });
  }

  rjdci.assignListeners = () => {
    if (document.querySelector("#deliveryRequest")) {
      assignTicketFormListeners(rjdci.getClosest(document.querySelector("#deliveryRequest"), ".page"));
      document.querySelector("#switchTerms").addEventListener("click", e => {
        document.querySelector("#deliveryTerms").style.display =
          (document.querySelector("#deliveryTerms").style.display == "none") ? "block" : "none";
      });
    }

    if (document.querySelector("form[id^='dispatchForm']")) {
      Array.from(document.querySelectorAll("form[id^='dispatchForm'] .stepTicket")).forEach(element => {
        element.removeEventListener("click", rjdci.stepTicket);
        element.addEventListener("click", rjdci.stepTicket);
      });
    }

    let colorChange = eve => {
      eve.target.classList.add("red");
      setTimeout(() => { eve.target.classList.remove("red"); }, 3000);
    };

    Array.from(document.querySelectorAll("button")).forEach(element => {
      element.addEventListener("click", colorChange);
    });
    // start driver and dispatcher app
    let updateNotes = async eve => {
      let workspace = rjdci.getClosest(eve.target, ".tickets").querySelector(".message2");
        postData = {};
      postData.ticket_index = document.querySelector(".ticket_index[form='" + eve.target.getAttribute("form") + "']").value;
      postData.notes = document.querySelector(".notes[form='" + eve.target.getAttribute("form") + "']").value;
      postData.formKey = document.querySelector("#formKey").value;
      await rjdci.fetch_template({ url: "./updateTicket.php", postData: postData })
      .then(result => {
        if (typeof result === "undefined") throw new Error("Result Undefined");
        if (result.ok) {
          return result.text();
        } else {
          throw new Error(result.status + " " + result.statusText);
        }
      })
      .then(data => {
        if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
        document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
        if (data.indexOf("error") === - 1) {
          let newDom = parser.parseFromString(data, "text/html");
          workspace.appendChild(newDom.querySelector(".result"));
          setTimeout(() => { workspace.innerHTML = ""; }, 3500)
        } else {
          throw new Error(data);
        }
      })
      .catch(error => {
        console.error(error.message);
        workspace.innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + "</p>";
        setTimeout(() => {
          workspace.innerHTML = "";
        }, 5000);
      });
    };
    Array.from(document.querySelectorAll(".updateNotes")).forEach(element => {
      element.removeEventListener("click", updateNotes);
      element.addEventListener("click", updateNotes);
    });
    Array.from(document.querySelectorAll(".dTicket")).forEach(element => {
      element.addEventListener("click", eve => {
        eve.preventDefault();
        //Clear all 'message2' containers
        let page = rjdci.getClosest(eve.target, ".page");
        Array.from(page.querySelectorAll(".cancelThis")).forEach(elem => {
          rjdci.triggerEvent(elem, "click");
        });
        //Disable other buttons in the ticket form
        let ticket = rjdci.getClosest(eve.target, ".tickets");
        Array.from(ticket.querySelectorAll("button")).forEach(elem => {
          elem.disabled = true;
        });
        let container = ticket.querySelector(".message2"),
          cancelButton = getCancelThis(),
          confirmButton = getStepTicket(eve.target.getAttribute("form"));
        container.innerHTML = "Confirm " + eve.target.innerText + ":<br>";
        container.appendChild(confirmButton);
        container.appendChild(cancelButton);
      });
    });

    Array.from(document.querySelectorAll(".confirmAll")).forEach(element => {
      element.addEventListener("click", eve => {
        eve.preventDefault();
        //Clear all 'message2' containers
        let page = rjdci.getClosest(eve.target, ".page");
        Array.from(page.querySelectorAll(".cancelThis")).forEach(elem => {
          rjdci.triggerEvent(elem, "click");
        });
        //Disable other buttons in the ticket form
        let ticket = rjdci.getClosest(eve.target, ".sortable");
        Array.from(ticket.querySelectorAll("button")).forEach(elem => {
          elem.disabled = true;
        });
        let container = ticket.querySelector("p.message2"),
          cancelButton = getCancelThis(),
          confirmButton = getStepAll();
        container.innerHTML = "Confirm Group Update:<br>";
        container.appendChild(confirmButton);
        container.appendChild(cancelButton);
      });
    });

    Array.from(document.querySelectorAll(".cancelRun, .deadRun, .declined")).forEach(element => {
      element.addEventListener("click", eve => {
        let testArr = [ "cancelRun", "deadRun", "declined" ],
          page = rjdci.getClosest(eve.target, ".page");
        Array.from(page.querySelectorAll(".cancelThis")).forEach(elem => {
          rjdci.triggerEvent(elem, "click");
        });
        let ticket = rjdci.getClosest(eve.target, ".tickets");
        Array.from(ticket.querySelectorAll("button")).forEach(elem => {
          elem.disabled = true;
        });
        let container = ticket.querySelector(".message2"),
            cancelButton = getCancelThis(),
            form = ticket.querySelector(".dTicket").getAttribute("form"),
            confirmButton;
        if (eve.target.classList.length === 1) {
          confirmButton = getCancelTicket(form, ucfirst(eve.target.getAttribute("class")));
        } else {
          for (let i = 0; i < eve.target.classList.length; i++) {
            if (testArr.indexOf(eve.target.classList[i] !== - 1)) {
              confirmButton = getCancelTicket(form, ucfirst(eve.target.classList[i]));
              break;
            }
          }
        }
        container.innerHTML = "Confirm " + eve.target.innerText + ":<br>";
        container.appendChild(confirmButton);
        container.appendChild(cancelButton);
      });
    });

    Array.from(document.querySelectorAll(".transferTicket")).forEach(element => {
      element.addEventListener("click", eve => {
        let page = rjdci.getClosest(eve.target, ".page");
        Array.from(page.querySelectorAll(".cancelThis")).forEach(elem => {
          rjdci.triggerEvent(elem, "click");
        });
        let ticket = rjdci.getClosest(eve.target, ".tickets");
        Array.from(ticket.querySelectorAll("button")).forEach(elem => {
          elem.disabled = true;
        });
        let container = ticket.querySelector(".message2"),
            cancelButton = getCancelThis(),
            confirmButton = getTransferTicket(eve.target.getAttribute("form"));
        container.innerHTML = "Confirm " + eve.target.innerText + ":<br>";
        container.appendChild(confirmButton);
        let input = document.createElement("input");
        input.setAttribute("name", "pendingReceiver");
        input.setAttribute("list", "receivers");
        input.classList.add("pendingReceiver");
        container.appendChild(input);
        container.appendChild(cancelButton);
      });
    });

    Array.from(document.querySelectorAll(".transferGroup")).forEach(element => {
      element.addEventListener("click", eve => {
        eve.preventDefault();
        //Clear all 'message2' containers
        let page = rjdci.getClosest(eve.target, ".page");
        Array.from(page.querySelectorAll(".cancelThis")).forEach(elem => {
          rjdci.triggerEvent(elem, "click");
        });
        //Disable other buttons in the ticket form
        let ticket = rjdci.getClosest(eve.target, ".sortable");
        Array.from(ticket.querySelectorAll("button")).forEach(elem => {
          elem.disabled = true;
        });
        let container = ticket.querySelector("p.message2"),
          cancelButton = getCancelThis(),
          confirmButton = getTransferGroup();
        container.innerHTML = "Confirm Transfer:<br>";
        let input = document.createElement("input");
        input.setAttribute("name", "pendingReceiver");
        input.setAttribute("list", "receivers");
        input.classList.add("pendingReceiver");
        container.appendChild(confirmButton);
        container.appendChild(input);
        container.appendChild(cancelButton);
      });
    });

    Array.from(document.querySelectorAll(".cancelTransfer, .declineTransfer, .acceptTransfer")).forEach(element => {
      element.addEventListener("click", eve => {
        eve.preventDefault();
        //Clear all 'message2' containers
        let testArr = [ "cancelTransfer", "declineTransfer", "acceptTransfer" ],
          page = rjdci.getClosest(eve.target, ".page");
        Array.from(page.querySelectorAll(".cancelThis")).forEach(elem => {
          rjdci.triggerEvent(elem, "click");
        });
        let ticket = rjdci.getClosest(eve.target, ".tickets");
        Array.from(ticket.querySelectorAll("button")).forEach(elem => {
          elem.disabled = true;
        });
        let container = ticket.querySelector(".message2"),
            cancelButton = getCancelThis(),
            confirmButton;
        if (eve.target.classList.length === 1) {
          confirmButton = getTransferButton(eve.target.getAttribute("class"));
        } else {
          for (let i = 0; i < eve.target.classList.length; i++) {
            if (testArr.indexOf(eve.target.classList[i] !== - 1)) {
              confirmButton = getTransferButton(eve.target.classList[i]);
              break;
            }
          }
        }
        container.innerHTML = "Confirm " + eve.target.innerText + ":<br>";
        container.appendChild(confirmButton);
        container.appendChild(cancelButton);
      });
    });

    Array.from(document.querySelectorAll(".acceptTransferGroup, .declineTransferGroup, .cancelTransferGroup")).forEach(element => {
      element.addEventListener("click", eve => {
        eve.preventDefault();
        //Clear all 'message2' containers
        let testArr = [ "acceptTransferGroup", "declineTransferGroup", "cancelTransferGroup" ],
          page = rjdci.getClosest(eve.target, ".page");
        Array.from(page.querySelectorAll(".cancelThis")).forEach(elem => {
          rjdci.triggerEvent(elem, "click");
        });
        let ticket = rjdci.getClosest(eve.target, ".sortable");
        Array.from(ticket.querySelectorAll("button")).forEach(elem => {
          elem.disabled = true;
        });
        let container = ticket.querySelector("p.message2"),
            cancelButton = getCancelThis(),
            confirmButton;
        if (eve.target.classList.length === 1) {
          confirmButton = getTransferAllButton(ucfirst(eve.target.getAttribute("class")));
        } else {
          let temp;
          for (let i = 0; i < eve.target.classList.length; i++) {
            if (testArr.indexOf(eve.target.classList[i] !== - 1)) {
              confirmButton = getTransferAllButton(ucfirst(eve.target.classList[i]));
              break;
            }
          }
        }
        container.innerHTML = "Confirm " + eve.target.innerText + ":<br>";
        container.appendChild(confirmButton);
        container.appendChild(cancelButton);
      });
    });

    if (document.querySelector("#ticketEditorSubmit")) {
      document.querySelector("#ticketEditorSubmit").addEventListener("click", async eve => {
        eve.preventDefault();
        let workspace = document.querySelector("#ticketEditorResultContainer"),
          postData = {},
          form = document.querySelector("#ticketEditor");
        Array.from(form.querySelectorAll("input[name], select")).forEach((item, i) => {
          postData[item.getAttribute("name")] = item.value;
        });
        postData.formKey = document.querySelector("#formKey").value;
        if (!postData.dispatchedTo || !postData.ticketEditorSearchDate) return false;
        let ele = document.createElement("span");
        ele.classList.add("ellipsis");
        ele.innerHTML = ".";
        workspace.innerHTML = "";
        workspace.appendChild(ele);
        let forward = true,
          dots = setInterval(() => {
            if (forward === true) {
              ele.innerHTML += "..";
              forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
            }
            if (forward === false) {
              ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
              forward = ele.innerHTML.length === 1;
            }
          }, 500);
        await rjdci.fetch_template({ url: "./activeTickets.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
            if (result.ok) {
              return result.text();
            } else {
              throw new Error(result.status + " " + result.statusText);
            }
        })
        .then(data => {
          clearInterval(dots);
          workspace.innerHTML = (data.indexOf("Session Error") === -1) ? '' : '<p class="center">Select Driver &amp; Ticket Type</p>';
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          let newDom = parser.parseFromString(data, "text/html"),
            docFrag = document.createDocumentFragment();
          Array.from(newDom.querySelectorAll(".tickets")).forEach(element => {
            docFrag.appendChild(element);
          });
          workspace.appendChild(docFrag);
          assignTicketEditorListener();
        })
        .catch(error => {
          console.error(error.message);
          clearInterval(dots);
          let element = document.createElement("p");
          element.classList.add("center");
          workspace.innerHTML = '';
          workspace.appendChild(element);
          element.innerHTML = '<span class="error">Error</span>: ' + error.message;
          setTimeout(() => { element.innerHTML = "Select Driver &amp; Ticket Type"; }, 3500);
        });
      });
      document.querySelector("#clearTicketEditorResults").addEventListener("click", eve => {
        eve.preventDefault();
        let form = document.querySelector("#ticketEditor"),
          d = new Date,
          day = (d.getDate() < 10) ? `0${d.getDate()}` : d.getDate(),
          test_month = d.getMonth() + 1,
          month = (test_month < 10) ? `0${test_month}` : test_month;
        form.querySelector(".dispatchedTo").value = "";
        form.querySelector(".contract").value = 0;
        form.querySelector("input.ticketEditorSearchDate").value = `${d.getFullYear()}-${month}-${day}`;
        document.querySelector("#ticketEditorResultContainer").innerHTML = '<p class="center">Select Driver &amp; Ticket Type</p>';
      });
    }

    if (document.querySelector("form#priceCalc")) {
      let workform = document.querySelector("form#priceCalc");
      workform.querySelector(".dryIce").addEventListener("change", eve => {
        let cell = rjdci.getClosest(eve.target, "td");
        if(eve.target.checked){
          cell.querySelector(".diWeight").value = "0";
          cell.querySelector(".diWeight").disabled = false;
          cell.querySelector(".diWeight").focus();
        } else{
          cell.querySelector(".diWeight").value = "0";
          cell.querySelector(".diWeight").disabled = true;
        }
      });
      Array.from(workform.querySelectorAll("input[list]")).forEach(element => {
        if (element.getAttribute("name").substr(1) === "Address1") {
          element.addEventListener("blur", eve => {
            let index;
            Array.from(document.querySelectorAll("#addy1 option")).forEach(ele => {
              if (ele.value === eve.target.value) index = ele.getAttribute("data-value");
            });
            if (typeof index === "undefined") return;
            Array.from(document.querySelectorAll("#addy2 option")).forEach(ele => {
              if (ele.getAttribute("data-value") === index) rjdci.getClosest(eve.target, "fieldset").querySelector("input[name='" + eve.target.getAttribute("name").slice(0, -1) + "2']").value = ele.value;
            });
          });
        }
      });
      workform.querySelector(".clear").addEventListener("click", eve => {
        workform.querySelector(".dryIce").checked = false;
        rjdci.triggerEvent(workform.querySelector(".dryIce"), "change");
        document.querySelector("#CalcCharge").value = "0";
        Array.from(document.querySelectorAll("#pNotice, #dNotice, #rangeResult, #diWeightResult, #diPriceResult, #runPriceResult, #ticketPriceResult")).forEach(element => {
          element.innerText = "";
        });
        Array.from(document.querySelectorAll("#priceResult .currencySymbol, #priceResult .weightMarker")).forEach(element => {
          element.style.display = "none";
        });
        Array.from(document.querySelectorAll("#price_calculator .elementError")).forEach(element => element.classList.remove("elementError"));
        rjdci.updateMap({mapDivID: "map2"});
      });
      workform.querySelector(".submitPriceQuery").addEventListener("click", async eve => {
        eve.preventDefault();
        Array.from(workform.querySelectorAll("button")).forEach(element => {
          element.disabled = true;
        });
        let breakFunction = false,
          postData = {},
          ele = document.createElement("span");
        ele.classList.add("ellipsis");
        ele.innerHTML = ".";
        workform.querySelector(".ticketError").innerHTML = "";
        workform.querySelector(".ticketError").appendChild(ele);
        let forward = true,
          dots = setInterval(() => {
            if (forward === true) {
              ele.innerHTML += "..";
              forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
            }
            if (forward === false) {
              ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
              forward = ele.innerHTML.length === 1;
            }
          }, 500);
        postData.formKey = document.querySelector("#formKey").value;
        Array.from(workform.querySelectorAll("input[name]")).forEach(input => {
          if (input.getAttribute("list") && input.disabled === false && input.value === "") {
            breakFunction = true;
            input.classList.add("elementError");
            setTimeout(() => { input.classList.remove("elementError"); }, 3500);
          } else {
            if (input.disabled === false && input.type !== "checkbox") postData[input.getAttribute("name")] = input.value;
          }
          if (input.type === "checkbox") {
            postData[input.getAttribute("name")] = (input.checked) ? 1 : 0;
          }
        });
        if (postData.dryIce === 1) {
          if (postData.diWeight === "0" || postData.diWeight % workform.querySelector(".diWeight").getAttribute("step") !== 0) {
            breakFunction = true;
            workform.querySelector(".diWeight").classList.add("elementError");
            workform.querySelector(".ticketError").innerHTML = "Dry Ice must be in increments of " + workform.querySelector(".diWeight").getAttribute("step");
          }
        }
        if (breakFunction) {
          clearInterval(dots);
          setTimeout(() => {
            Array.from(workform.querySelectorAll("button, .elementError")).forEach(element => {
              element.disabled = false;
              element.classList.remove("elementError");
            });
            workform.querySelector(".ticketError").innerHTML = "";
          }, 3500);
          return false;
        }
        await rjdci.fetch_template({ url: "../priceCalc.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
          if (result.ok) {
            return result.text();
          } else {
            throw new Error(result.status + " " + result.statusText);
          }
        })
        .then(data => {
          clearInterval(dots);
          workform.querySelector(".ticketError").innerHTML = "";
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          Array.from(document.querySelectorAll("#pNotice, #dNotice, #CalcError")).forEach(element => {
            element.innerHTML = "";
          });
          let obj;
          try {
            obj = JSON.parse(data);
          } catch( err ) {
            document.querySelector("#CalcError").innerHTML =  err.message;
            setTimeout(() => { document.querySelector("#CalcError").innerText = ""; }, 3500);
            return false;
          }
          Array.from(document.querySelectorAll("#priceResult .currencySymbol, #priceResult .weightMarker")).forEach(element => {
            element.classList.remove("hide");
          });
          document.querySelector("#rangeResult").innerText = obj.rangeDisplay;
          document.querySelector("#diWeightResult").innerText = obj.diWeight;
          document.querySelector("#runPriceResult").innerText = obj.runPrice;
          document.querySelector("#diPriceResult").innerText = obj.diPrice;
          document.querySelector("#ticketPriceResult").innerText = obj.ticketPrice;
          if (obj.pRangeTest > 15 && obj.pRangeTest < 20) {
            document.querySelector("#pNotice").innerText = "Pick Up address is outside of our standard range. Please call to confirm availability.";
          } else if (obj.pRangeTest > 20) {
            document.querySelector("#pNotice").innerText = "Pick Up address is outside of our extended range.";
            Array.from(document.querySelectorAll("#runPriceResult, #ticketPriceResult, #diPriceResult, #diWeightResult")).forEach(element => {
              element.innerText = "";
            });
            Array.from(document.querySelectorAll("#priceResult .currencySymbol, #priceResult .weightMarker")).forEach(element => {
              element.classList.add("hide");
            });
          }
          if (obj.dRangeTest > 15 && obj.dRangeTest < 20) {
            document.querySelector("#dNotice").innerText = "Delivery address is outside of our standard range. Please call to confirm availability.";
          } else if (obj.dRangeTest > 20) {
            document.querySelector("#dNotice").innerText = "Delivery address is outside of our extended range.";
            Array.from(document.querySelectorAll("#runPriceResult, #ticketPriceResult, #diPriceResult, #diWeightResult")).forEach(element => {
              element.innerText = "";
            });
            Array.from(document.querySelectorAll("#priceResult .currencySymbol, #priceResult .weightMarker")).forEach(element => {
              element.classList.add("hide");
            });
          }
          Array.from(workform.querySelectorAll("button")).forEach(element => {
            element.disabled = false;
          });
          rjdci.updateMap({mapDivID:"map2", coords1: obj.result1, address1: obj.address1, coords2: obj.result2, address2: obj.address2, center: obj.center});
        })
        .catch(error => {
          console.error(error.message);
          clearInterval(dots);
          workform.querySelector(".ticketError").innerHTML = '<span class="error">Error</span>: ' + error.message;
          setTimeout(() => {
            workform.querySelector(".ticketError").innerHTML = "";
            Array.from(workform.querySelectorAll("button")).forEach(element => {
              element.disabled = false;
            });
          }, 3500);
        });
      });
    }

    if (document.querySelector(".PWcontainer")) {
      document.querySelector(".PWcontainer .showText").addEventListener("change", eve => {
        Array.from(rjdci.getClosest(eve.target, "form").querySelectorAll("input.currentPw, .newPw1, .newPw2")).forEach(element => {
          element.type = (eve.target.checked) ? "text" : "password";
        });
      });
      document.querySelector(".clearPWform").addEventListener("click", eve => {
        rjdci.getClosest(eve.target, "form").querySelector(".showText").checked = false;
        rjdci.triggerEvent(rjdci.getClosest(eve.target, "form").querySelector(".showText"), "change");
      });
      Array.from(document.querySelectorAll(".newPw1, .newPw2, .currentPw")).forEach(element => {
        element.addEventListener("change", eve => {
          let workspace = rjdci.getClosest(eve.target, ".PWform"),
            submitButton = workspace.querySelector(".PWsubmit"),
            test0 = workspace.querySelector(".currentPw").value,
            test1 = workspace.querySelector(".newPw1").value,
            test2 = workspace.querySelector(".newPw2").value,
            error1 = document.createElement("p"),
            error2 = document.createElement("p"),
            error3 = document.createElement("p");
          error1.classList.add("error1");
          error1.innerHTML = '<span class="error">Error</span>: Password does not meet criteria.';
          error2.classList.add("error2");
          error2.innerHTML = '<span class="error">Error</span>: Password missmatch.';
          error3.classList.add("error3");
          error3.innerHTML = '<span class="error">Error:</span> Password should be changed.';
          if (test1 !== "") {
            if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^\&*\)\(\{\}\[\]\-_.=+\?\:;,])(?=.{8,}).*$/.test(test1)) {
              if (workspace.querySelector(".message .error1") !== null) {
                workspace.querySelector(".message").appendChild(error1);
                submitButton.disabled = true;
                return false;
              }
            } else {
              if (workspace.querySelector(".message .error1") !== null) workspace.querySelector(".message").removeChild(workspace.querySelector(".message .error1"));
              if (workspace.querySelector(".message p") === null) submitButton.disabled = false;
            }
            if (test0 !== "" && test1 === test0) {
              if (workspace.querySelector(".message .error3") === null) {
                workspace.querySelector(".message").appendChild(error3);
                submitButton.disabled = true;
              }
            } else {
              if (workspace.querySelector(".message .error3") !== null) workspace.querySelector(".message").removeChild(workspace.querySelector(".message .error3"));
              if (workspace.querySelector(".message p") === null) submitButton.disabled = false;
            }
          } else {
            if (workspace.querySelector(".message .error1") !== null) workspace.querySelector(".message").removeChild(workspace.querySelector(".message .error1"));
            if (workspace.querySelector(".message p") === null) submitButton.disabled = false;
          }
          if (test2 !== "") {
            if (test1 !== test2) {
              if (workspace.querySelector(".message .error2") === null) workspace.querySelector(".message").appendChild(error2);
              submitButton.disabled = true;
            } else {
              if (workspace.querySelector(".message .error2") !== null) workspace.querySelector(".message").removeChild(workspace.querySelector(".message .error2"));
              if (workspace.querySelector(".message p") === null) submitButton.disabled = false;
            }
          } else {
            if (workspace.querySelector(".message .error2") !== null) workspace.querySelector(".message").removeChild(workspace.querySelector(".message .error2"));
            if (workspace.querySelector(".message p") === null) submitButton.disabled = false;
          }
        });
      });
      Array.from(document.querySelectorAll(".PWsubmit")).forEach(element => {
        element.addEventListener("click", async eve => {
          eve.preventDefault();
          eve.target.disabled = true;
          let workspace = rjdci.getClosest(eve.target, ".PWform"),
            postData = {},
            ele = document.createElement("span");
          ele.classList.add("ellipsis");
          ele.innerHTML = ".";
          workspace.querySelector(".message").innerHTML = "";
          workspace.querySelector(".message").appendChild(ele);
          let forward = true,
            dots = setInterval(() => {
              if (forward === true) {
                ele.innerHTML += "..";
                forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
              }
              if (forward === false) {
                ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
                forward = ele.innerHTML.length === 1;
              }
            }, 500);
          postData.formKey = document.querySelector("#formKey").value;
          Array.from(workspace.querySelectorAll("input[name]")).forEach(input => {
            postData[input.getAttribute("name")] = input.value;
          });
          await rjdci.fetch_template({ url: "./changePW.php", postData: postData })
          .then(result => {
            if (typeof result === "undefined") throw new Error("Result Undefined");
            if (result.ok) {
              return result.text();
            } else {
              throw new Error(result.status + " " + result.statusText);
            }
          })
          .then(data => {
            clearInterval(dots);
            if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
            document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
            workspace.querySelector(".message").innerHTML = data;
            setTimeout(() => {
              Array.from(workspace.querySelectorAll(".currentPw, .newPw1, .newPw2")).forEach(element => {
                element.value = "";
              });
              workspace.querySelector(".message").innerHTML = "";
              workspace.querySelector(".currentPw").focus();
              eve.target.disabled = false;
              if (postData.flag !== "driver" && postData.flag !== "dispatch") {
                let test_ele,
                  page = rjdci.getClosest(eve.target, ".page"),
                  pwError = false;
                switch(postData.flag) {
                  case "daily":
                    test_ele = document.querySelector("a[data-id='change_password']");
                    pwError = (postData.newPw1 === "!Delivery1");
                  break;
                  case "admin":
                    test_ele = document.querySelector("a[data-id='change_admin_password']");
                    pwError = (postData.newPw1 === "!Delivery2");
                  break;
                  case "org":
                    test_ele = document.querySelector("a[data-id='change_password']");
                    pwError = (postData.newPw1 === "3Delivery!");
                  break;
                }
                if (data.search("Password Updated") !== -1) {
                  if (pwError === true) {
                    page.querySelector(".defaultWarning").classList.remove("hide");
                    test_ele.querySelector(".PWalert").innerText = "!";
                  } else {
                    if (!page.querySelector(".defaultWarning").classList.contains("hide")) page.querySelector(".defaultWarning").classList.add("hide");
                    test_ele.querySelector(".PWalert").innerText = "";
                  }
                }
              }
            }, 4000)
          })
          .catch(error => {
            console.error(error.message);
            clearInterval(dots);
            workspace.querySelector(".message").innerHTML = '<span class="error">Error</span>: ' + error.message;
            setTimeout(() => {
              workspace.querySelector(".message").innerHTML = "";
              eve.target.disabled = false;
            }, 3500);
          });
        });
      });
    }
    // end driver and dispatcher app
    // start client app
    if (document.querySelector("#invoiceQueryOptions")) {
      if (document.querySelector("#useInvoice")) document.querySelector("#useInvoice").addEventListener("change", eve => {
        document.querySelector("#invoiceNumber").disabled = eve.target.checked === false;
        rjdci.getClosest(eve.target, "#singleInvoiceQuery").querySelector(".dateIssuedMonth").disabled = eve.target.checked === true;
        rjdci.getClosest(eve.target, "#singleInvoiceQuery").querySelector(".dateIssuedMonth").required = eve.target.checked === false;

      });
      Array.from(document.querySelectorAll("#single, #multi")).forEach(box => {
        box.addEventListener("click", eve => {
          if (!eve.target.checked) eve.preventDefault();
        });
        box.addEventListener("change", eve => {
          let recheck = [];
          Array.from(document.querySelectorAll("#invoiceQueryOptions .orgMember")).forEach(element => {
            if (element.checked) recheck.push(element);
            element.checked = false;
            element.setAttribute("form", eve.target.getAttribute("form"));
          });
          let target = (eve.target.getAttribute("id") === "single") ? "multi" : "single",
            targetForm = document.querySelector("#" + target).getAttribute("form");
          if (target === "single") document.querySelector("#compareMembers").disabled = true;
          if (eve.target.checked) {
            document.querySelector("#" + target).checked = false;
          }
          if (recheck.length > 0) recheck[0].checked = true;
          document.querySelector("button[form='" + eve.target.getAttribute("form") + "']").disabled = false;
          document.querySelector("button[form='" + targetForm + "']").disabled = true;
        });
      });

      Array.from(document.querySelectorAll("#invoiceQueryOptions .orgMember")).forEach(box => {
        box.addEventListener("change", eve => {
          let count = 0;
          Array.from(document.querySelectorAll("#invoiceQueryOptions .orgMember")).forEach(b => {
            if (document.querySelector("#single").checked) {
              b.checked = b === eve.target;
            } else {
              count += b.checked;
            }
          });
          document.querySelector("#compareMembers").disabled = count < 2;
        });
      });

      if (document.querySelector("#single")) rjdci.triggerEvent(document.querySelector("#single"), "change");

      Array.from(document.querySelectorAll("#singleInvoice, #rangeInvoice")).forEach(button => {
        button.addEventListener("click", async eve => {
          eve.preventDefault();
          eve.target.disabled = true;
          let breakFunction = false,
            postData = {},
            ellipsis = document.createElement("span"),
            container = document.createElement("p");
          container.classList.add("center");
          ellipsis.innerHTML = ".";
          container.appendChild(ellipsis);
          document.querySelector("#invoiceQueryResults").innerHTML = "";
          document.querySelector("#invoiceQueryResults").appendChild(container);
          let forward = true,
            dots = setInterval(() => {
              if (forward === true) {
                ellipsis.innerHTML += "..";
                forward = ellipsis.innerHTML.length < 21 && ellipsis.innerHTML.length != 1;
              }
              if (forward === false) {
                ellipsis.innerHTML = ellipsis.innerHTML.substr(0,ellipsis.innerHTML.length - 2);
                forward = ellipsis.innerHTML.length === 1;
              }
            }, 500);
          Array.from(document.querySelectorAll("input[form='" + eve.target.getAttribute("form") + "'], select[form='" + eve.target.getAttribute("form") + "']")).forEach(input => {
            if (input.required && input.value === "") {
              breakFunction = true;
              input.classList.add("elementError");
              setTimeout(() => { input.classList.remove("elementError"); }, 3000);
            }
            if (input.getAttribute("name").slice(-2) === "[]") {
              if (typeof postData[input.getAttribute("name").slice(0, -2)] === "undefined") {
                if (input.type === "checkbox") {
                  if (input.checked === true && input.disabled === false) postData[input.getAttribute("name").slice(0, -2)] = [ input.value ];
                } else {
                  if (input.disabled === false) postData[input.getAttribute("name").slice(0, -2)] = [ input.value ];
                }
              } else {
                if (input.type === "checkbox") {
                  if (input.checked === true && input.disabled === false) postData[input.getAttribute("name").slice(0, -2)].push(input.value);
                } else {
                  if (input.disabled === false) postData[input.getAttribute("name").slice(0, -2)].push(input.value);
                }
              }
            } else {
              if (input.type === "checkbox") {
                if (input.checked === true && input.disabled === false) postData[input.getAttribute("name")] = input.value;
              } else {
                if (input.disabled === false) postData[input.getAttribute("name")] = input.value;
              }
            }
          });
          if (breakFunction) {
            clearInterval(dots);
            document.querySelector("#invoiceQueryResults").removeChild(container);
            eve.target.disabled = false;
            return false;
          }
          postData.formKey = document.querySelector("#formKey").value;
          await rjdci.fetch_template({ url: "./buildQuery.php", postData: postData })
          .then(result => {
            if (typeof result === "undefined") throw new Error("Result Undefined");
            if (result.ok) {
              return result.text();
            } else {
              throw new Error(result.status + " " + result.statusText);
            }
          })
          .then(data => {
            clearInterval(dots);
            document.querySelector("#invoiceQueryResults").removeChild(container);
            if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
            document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
            let newDom = parser.parseFromString(data, "text/html"),
              docFrag = document.createDocumentFragment();
            Array.from(newDom.querySelectorAll("#invoiceChartPDFform, .bargraph, .graphKey, #invoice, .invoiceTable, p.displayHeader, .result")).forEach(element => {
              docFrag.appendChild(element);
            });
            document.querySelector("#invoiceQueryResults").appendChild(docFrag);
            eve.target.disabled = false;
            assignQueriedInvoiceListeners();
          })
          .catch(error => {
            console.error(error.message);
            clearInterval(dots);
            document.querySelector("#invoiceQueryResults").innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + '</p>';
            setTimeout(() => { document.querySelector("#invoiceQueryResults").innerHTML = ""; eve.target.disabled = false; }, 3500);
          });
        });
      });
    }

    if (document.querySelector("#deliveryQuery")) {
      Array.from(document.querySelectorAll("#deliveryQuery .orgMember")).forEach(element => {
        element.addEventListener("change", eve => {
          rjdci.disableButtonsTickets();
          if (eve.target.checked) {
            document.querySelector("#ticketNumber").value =  "";
            document.querySelector("#ticketNumber").readOnly = true;
            rjdci.triggerEvent(document.querySelector("#ticketNumber"), "change");
          } else {
            document.querySelector("#ticketNumber").readOnly = document.querySelector("#display").value === "chart";
          }
        });
      });

      if (document.querySelector("#display")) {
        document.querySelector("#display").addEventListener("change", eve => {
          if (document.querySelector("#compareBox")) {
            Array.from(document.querySelectorAll("#compareBox, #compareMembersTickets")).forEach(box => {
              box.disabled = eve.target.value === "tickets";
              if (eve.target.value === "tickets") {
                box.checked = false;
              }
            });
          }
          document.querySelector("#ticketNumber").disabled = (eve.target.value === "chart" || (eve.target.value === "tickets" && document.querySelector("#allTime").checked === true));
          Array.from(document.querySelectorAll("#chargeHistory, #type")).forEach(element => {
            element.disabled = eve.target.value === "chart";
          });
          Array.from(document.querySelectorAll("#deliveryQuery .ticketDate, #deliveryQuery .chartDate")).forEach(element => {
            if (eve.target.value === "tickets") {
              element.style.display = (element.classList.contains("ticketDate")) ? "inline-block" : "none";
              if (element.querySelector("input")) {
                Array.from(element.querySelectorAll("input")).forEach(elm => {
                  elm.disabled = elm.classList.contains("startDateMonth") || elm.classList.contains("endDateMonth");
                  elm.value = "";
                  if (elm.disabled === false) elm.disabled = document.querySelector("#allTime").checked;
                });
              }
            } else {
              element.style.display = (element.classList.contains("ticketDate")) ? "none" : "inline-block";
              if (element.querySelector("input")) {
                Array.from(element.querySelectorAll("input")).forEach(elm => {
                  elm.disabled = !elm.classList.contains("startDateMonth") && !elm.classList.contains("endDateMonth");
                  elm.value = "";
                  if (elm.disabled === false) elm.disabled = document.querySelector("#allTime").checked;
                });
              }
            }
          });
        });
      }

      document.querySelector("#allTime").addEventListener("change", eve => {
        let selector = (document.querySelector("#display") && document.querySelector("#display").value === "chart") ? ".chartDate" : ".ticketDate";
        if (eve.target.checked === true) {
          Array.from(rjdci.getClosest(eve.target, "form").querySelectorAll(selector + " input, #ticketNumber")).forEach(element => { element.value = ""; element.disabled = true; });
        } else {
           Array.from(rjdci.getClosest(eve.target, "form").querySelectorAll(selector + " input")).forEach(element => { element.disabled = false; });
           (document.querySelector("#display")) ? document.querySelector("#ticketNumber").disabled = document.querySelector("#display").value === "chart" :  document.querySelector("#ticketNumber").disabled = false;
        }
      });

      document.querySelector("#ticketNumber").addEventListener("blur", eve => {
        if (eve.target.value !== "") {
          if (document.querySelector("#display")) {
            document.querySelector("#display").value = "tickets";
            rjdci.triggerEvent(document.querySelector("#display"), "change");
          }
          Array.from(document.querySelectorAll("#deliveryQuery .startDateDate, #deliveryQuery .endDateDate, #chargeHistory, #charge, #type, #allTime, #display, #compareBox, #compareMembersTickets, #deliveryQuery .orgMember")).forEach(input => {
            input.disabled = true;
            input.checked = false;
            input.required = false;
          });
          Array.from(document.querySelectorAll("#deliveryQuery .startDateMarker, #deliveryQuery .endDateMarker, #deliveryQuery .chargeMarker, #deliveryQuery .typeMarker, #displayMarker")).forEach(input => {
            input.disabled = false;
          });
        } else {
          Array.from(document.querySelectorAll("#deliveryQuery .startDateDate, #deliveryQuery .endDateDate, #chargeHistory, #charge, #type, #allTime, #display, #compareBox, #compareMembersTickets, #deliveryQuery .orgMember")).forEach(input => {
            input.disabled = false;
            input.required = input.classList.contains("startDate") || input.classList.contains("endDate");
          });
          Array.from(document.querySelectorAll("#deliveryQuery .startDateMarker, #deliveryQuery .endDateMarker, #deliveryQuery .chargeMarker, #deliveryQuery .typeMarker, #displayMarker")).forEach(input => {
            input.disabled = true;
          });
        }
      });

      if (!document.querySelector(".submitOrgTickets")) {
        document.querySelector(".clearTicketResults").addEventListener("click", eve => {
          document.querySelector("#ticketQueryResults").innerHTML = "";
        });

        document.querySelector(".resetTicketQuery").addEventListener("click", eve => {
          document.querySelector("#display").value = "tickets";
          rjdci.triggerEvent(document.querySelector("#display"), "change");
          Array.from(document.querySelectorAll("#deliveryQuery .elementError")).forEach(input => {
            input.classList.remove("elementError");
          });
        });

        document.querySelector(".submitTicketQuery").addEventListener("click", async eve => {
          eve.preventDefault();
          eve.target.disabled = true;
          let breakFunction = false,
            postData = {},
            ellipsis = document.createElement("span"),
            container = document.createElement("p");
          container.classList.add("center");
          ellipsis.innerHTML = ".";
          container.appendChild(ellipsis);
          Array.from(document.querySelectorAll("#deliveryQuery input, #deliveryQuery select")).forEach(input => {
            if (!input.disabled) {
              if (input.type !== "checkbox") {
                if (input.value)  postData[input.getAttribute("name")] = input.value;
              } else {
                postData[input.getAttribute("name")] = (input.checked) ? 1 : 0;
              }
            }
          });
          if (!postData.hasOwnProperty("ticketNumber") && !postData.allTime) {
            if ((!postData.hasOwnProperty("startDate") || !postData.startDate) || (!postData.hasOwnProperty("endDate") || !postData.endDate)) {
              breakFunction = true;
              Array.from(document.querySelectorAll("#deliveryQuery input[name='startDate'], #deliveryQuery input[name='endDate']")).forEach(input => {
                if (!input.value) {
                  input.classList.add("elementError");
                  setTimeout(() => { input.classList.remove("elementError"); }, 3500);
                }
              });
            }
          } else if (!postData.ticketNumber && !postData.allTime) {
            breakFunction = true;
            document.querySelector("#ticketNumber").classList.add("elementError");
            setTimeout(() => { document.querySelector("#ticketNumber").classList.remove("elementError"); }, 3500);
          }
          postData.formKey = document.querySelector("#formKey").value;
          if (breakFunction) {
            eve.target.disabled = false;
            return false;
          }
          document.querySelector("#ticketQueryResults").appendChild(container);
          let forward = true,
            dots = setInterval(() => {
              if (forward === true) {
                ellipsis.innerHTML += "..";
                forward = ellipsis.innerHTML.length < 21 && ellipsis.innerHTML.length != 1;
              }
              if (forward === false) {
                ellipsis.innerHTML = ellipsis.innerHTML.substr(0,ellipsis.innerHTML.length - 2);
                forward = ellipsis.innerHTML.length === 1;
              }
            }, 500);
          await rjdci.fetch_template({ url: "./buildQuery.php", postData: postData })
          .then(result => {
            if (typeof result === "undefined") throw new Error("Result Undefined");
            if (result.ok) {
              return result.text();
            } else {
              throw new Error(result.status + " " + result.statusText);
            }
          })
          .then(data => {
            eve.target.disabled = false;
            clearInterval(dots);
            if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
            document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
            let newDom = parser.parseFromString(data, "text/html"),
              docFrag = document.createDocumentFragment();
            Array.from(newDom.querySelectorAll("#ticketPDFform, .bargraph, .graphKey, .tickets, .ticketTable, .ticketGraphContainer, .result")).forEach(element => {
              docFrag.appendChild(element);
            });
            document.querySelector("#ticketQueryResults").innerHTML = "";
            document.querySelector("#ticketQueryResults").appendChild(docFrag);
            rjdci.assignQueriedTicketListeners();
          })
          .catch(error => {
            console.error(error.message);
            eve.target.disabled = false;
            clearInterval(dots);
            document.querySelector("#ticketQueryResults").innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + '</p>';
            setTimeout(() => { document.querySelector("#ticketQueryResults").innerHTML = ""; }, 3500);
          });
        });
      } else {
        document.querySelector(".submitOrgTickets").addEventListener("click", async eve => {
          eve.preventDefault();
          let breakFunction = false,
            postData = {},
            ellipsis = document.createElement("span"),
            container = document.createElement("p");
          postData.formKey = document.querySelector("#formKey").value;
          container.classList.add("center");
          ellipsis.innerHTML = ".";
          container.appendChild(ellipsis);
          document.querySelector("#ticketQueryResults").appendChild(container);
          let forward = true,
            dots = setInterval(() => {
              if (forward === true) {
                ellipsis.innerHTML += "..";
                forward = ellipsis.innerHTML.length < 21 && ellipsis.innerHTML.length != 1;
              }
              if (forward === false) {
                ellipsis.innerHTML = ellipsis.innerHTML.substr(0,ellipsis.innerHTML.length - 2);
                forward = ellipsis.innerHTML.length === 1;
              }
            }, 500);
          Array.from(document.querySelectorAll("#deliveryQuery input, #deliveryQuery select")).forEach(input => {
            if (!input.disabled && !input.readOnly) {
              if (input.getAttribute("name").slice(-2) === "[]") {
                if ((input.getAttribute("type") === "checkbox" && input.checked) || (input.getAttribute("type") !== "checkbox" && input.value !== "")) {
                  if (typeof(postData[input.getAttribute("name").slice(0, (input.getAttribute("name").length - 2))]) === "undefined") {
                    postData[input.getAttribute("name").slice(0, (input.getAttribute("name").length - 2))] = [ input.value ];
                  } else if (typeof(postData[input.getAttribute("name").slice(0, (input.getAttribute("name").length - 2))]) === "object" || typeof(postData[input.getAttribute("name").slice(0, (input.getAttribute("name").length - 2))]) === "array") {
                    postData[input.getAttribute("name").slice(0, (input.getAttribute("name").length - 2))].push(input.value);
                  }
                }
              } else {
                if (input.getAttribute("type") === "checkbox") {
                  if (input.checked) postData[input.getAttribute("name")] = input.value;
                } else {
                  postData[input.getAttribute("name")] = input.value;
                }
              }
            }
          });
          await rjdci.fetch_template({ url: "./buildQuery.php", postData: postData })
          .then(result => {
            if (typeof result === "undefined") throw new Error("Result Undefined");
            if (result.ok) {
              return result.text();
            } else {
              throw new Error(result.status + " " + result.statusText);
            }
          })
          .then(data => {
            eve.target.disabled = false;
            clearInterval(dots);
            if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
            document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
            let newDom = parser.parseFromString(data, "text/html"),
              docFrag = document.createDocumentFragment();
            Array.from(newDom.querySelectorAll("#ticketPDFform, .tickets, .ticketTable, .bargraph, .graphKey, .result")).forEach(element => {
              docFrag.appendChild(element);
            });
            document.querySelector("#ticketQueryResults").innerHTML = "";
            document.querySelector("#ticketQueryResults").appendChild(docFrag);
            rjdci.assignQueriedTicketListeners();
          })
          .catch(error => {
            console.error(error.message);
            eve.target.disabled = false;
            clearInterval(dots);
            document.querySelector("#ticketQueryResults").innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + '</p>';
            setTimeout(() => { document.querySelector("#ticketQueryResults").innerHTML = ""; }, 3500);
          });
        });
      }
    }

    if (document.querySelector("#clientUpdateForm")) {
      document.querySelector("#same").addEventListener("change", eve => {
        Array.from(document.querySelectorAll("input[name='BillingName'], input[name='BillingAddress1'], input[name='BillingAddress2'], input[name='BillingCountry']")).forEach(input => {
          input.disabled = eve.target.checked === true;
          input.required = eve.target.checked === false;
        });
        Array.from(document.querySelectorAll("fieldset[name='billingInfo'] .error")).forEach(element => {
          element.style.display = (eve.target.checked === true) ? "none" : "inline";
        });
        if (rjdci.getClosest(document.querySelector("input[name='BillingCountry'][form='clientUpdate']"), "tr").classList.contains("hide")) {
          document.querySelector("input[name='BillingCountry'][form='clientUpdate']").value = document.querySelector("input[name='ShippingCountry'][form='clientUpdate']").value;
        }
      });
      document.querySelector("#enableInfoUpdate").addEventListener("change", eve => {
        document.querySelector("#submitInfoUpdate").disabled = eve.target.checked === false;
      });
      document.querySelector("#submitInfoUpdate").addEventListener("click", async eve => {
        eve.preventDefault();
        eve.target.disabled = true;
        let postData = {},
          breakFunction = false,
          ellipsis = document.createElement("span");
        ellipsis.classList.add("ellipsis");
        ellipsis.innerHTML = ".";
        document.querySelector("#clientUpdateResult").appendChild(ellipsis);
        let forward = true,
          dots = setInterval(() => {
            if (forward === true) {
              ellipsis.innerHTML += ".";
              forward = ellipsis.innerHTML.length < 21 && ellipsis.innerHTML.length != 1;
            }
            if (forward === false) {
              ellipsis.innerHTML = ellipsis.innerHTML.substr(0,ellipsis.innerHTML.length - 2);
              forward = ellipsis.innerHTML.length === 1;
            }
          }, 500);
        Array.from(document.querySelectorAll("input[form='" + eve.target.getAttribute("form") + "']")).forEach(input => {
          if (input.required === true && input.value === "") {
            console.log(input);
            breakFunction = true;
            input.classList.add("elementError");
            setTimeout(() => { input.classList.remove("elementError"); }, 3000);
          }
          if (input.disabled === false) postData[input.getAttribute("name")] = input.value;
          if (input.type === "checkbox") {
            postData[input.getAttribute("name")] = (input.checked === true) ? 1 : 0;
          }
        });
        if (breakFunction === true) {
          clearInterval(dots);
          eve.target.disabled = false;
          document.querySelector("#clientUpdateResult").removeChild(ellipsis);
          return false;
        }
        postData.formKey = document.querySelector("#formKey").value;
        await rjdci.fetch_template({ url: "./updateClientInfo.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
          if (result.ok) {
            return result.text();
          } else {
            throw new Error(result.status + " " + result.statusText);
          }
        })
        .then(data => {
          eve.target.disabled = false;
          clearInterval(dots);
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          let newDom = parser.parseFromString(data, "text/html"),
            docFrag = document.createDocumentFragment();
          Array.from(newDom.querySelectorAll(".result")).forEach(element => {
            docFrag.appendChild(element);
          });
          document.querySelector("#clientUpdateResult").innerHTML = "";
          document.querySelector("#clientUpdateResult").appendChild(docFrag);
          setTimeout(() => {
            document.querySelector("#clientUpdateResult").innerHTML = "";
            document.querySelector("#enableInfoUpdate").checked = false;
            rjdci.triggerEvent(document.querySelector("#enableInfoUpdate"), "change");
          }, 3500);
        })
        .catch(error => {
          console.error(error.message);
          eve.target.disabled = false;
          clearInterval(dots);
          document.querySelector("#clientUpdateResult").innerHTML = '<span class="error">Error</span>: ' + error.message;
          setTimeout(() => { document.querySelector("#clientUpdateResult").innerHTML = ""; }, 3500);
        });
      });
    }
    // end client app
  }

  assignQueriedInvoiceListeners = () => {
    if (document.querySelector("#invoicePDF")) {
      document.querySelector("#invoicePDF").addEventListener("click", eve => {
        eve.preventDefault();
        let cln = document.querySelector("#invoice").cloneNode(true),
          compStyles,
          styles = {},
          styleNames = [ "position", "top", "left", "right", "font-size" ];
        if (document.querySelector("#invoice .vatNotice") !== null) {
          compStyles = getComputedStyle(document.querySelector("#invoice .vatNotice"));
          for (let styleName in compStyles) {
            if (styleNames.indexOf(styleName) !== -1) {
              styles[styleName] = compStyles.getPropertyValue(styleName);
            }
          }
        }
        cln.querySelector("#invoicePDFform").remove();
        Array.from(cln.querySelectorAll(".vatNotice")).forEach(element => {
          element.parentNode.style.position = "relative";
          for (let styleName in styles) {
            element.style[styleName] = styles[styleName];
          }
        });
        document.querySelector("#invoicePDFform input[name='content']").value = cln.outerHTML;
        document.querySelector("#invoicePDFform input[name='formKey']").value = document.querySelector("#formKey").value;
        document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
        document.querySelector("#invoicePDFform").submit();
      });
    }
    if (document.querySelector("#invoiceChartPDF")) {
      document.querySelector("#invoiceChartPDF").addEventListener("click", eve => {
        eve.preventDefault();
        let cln = document.querySelector("#invoiceQueryResults").cloneNode(true);
        cln.querySelector("#invoiceChartPDFform").remove();
        Array.from(cln.querySelectorAll(".invoiceQuery")).forEach(element => {
          let td = rjdci.getClosest(element, 'td'),
            text = element.innerHTML,
            classes = Array.from(element.classList);
          td.classList.add(...classes);
          td.innerHTML = text;
        });
        document.querySelector("#invoiceChartPDFform input[name='content']").value = cln.outerHTML;
        document.querySelector("#invoiceChartPDFform input[name='formKey']").value = document.querySelector("#formKey").value;
        document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
        document.querySelector("#invoiceChartPDFform").submit();
      });
    }
    Array.from(document.querySelectorAll("#invoiceQueryResults .invoiceQuery")).forEach(element => {
      element.addEventListener("click", async eve => {
        eve.preventDefault();
        eve.target.classList.add("red");
        eve.target.disabled = true;
        setTimeout(() => { eve.target.classList.remove("red"); }, 3500);
        let postData = {},
          ellipsis = document.createElement("p");
        ellipsis.classList.add("center");
        ellipsis.innerHTML = ".";
        rjdci.getClosest(eve.target, ".invoiceTable").appendChild(ellipsis);
        let forward = true,
          dots = setInterval(() => {
            if (forward === true) {
              ellipsis.innerHTML += "..";
              forward = ellipsis.innerHTML.length < 21 && ellipsis.innerHTML.length != 1;
            }
            if (forward === false) {
              ellipsis.innerHTML = ellipsis.innerHTML.substr(0,ellipsis.innerHTML.length - 2);
              forward = ellipsis.innerHTML.length === 1;
            }
          }, 500);
        Array.from(rjdci.getClosest(eve.target, "form").querySelectorAll("input")).forEach(input => {
          postData[input.getAttribute("name")] = input.value;
        });
        postData.formKey = document.querySelector("#formKey").value;
        await rjdci.fetch_template({ url: "./buildQuery.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
          if (result.ok) {
            return result.text();
          } else {
            throw new Error(result.status + " " + result.statusText);
          }
        })
        .then(data => {
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          let newDom = parser.parseFromString(data, "text/html"),
            docFrag = document.createDocumentFragment();
          Array.from(newDom.querySelectorAll("#invoice, .invoiceTable, .invoiceGraphContainer, p.displayHeader, .result")).forEach(element => {
            docFrag.appendChild(element);
          });
          document.querySelector("#invoiceQueryResults").innerHTML = "";
          document.querySelector("#invoiceQueryResults").appendChild(docFrag);
        })
        .catch(error => {
          console.log(error.message);
          clearInterval(dots);
          rjdci.getClosest(eve.target, ".invoiceTable").removeChild(ellipsis);
          errorMessage = document.createElement("p");
          errorMessage.classList.add("center");
          errorMessage.innerHTML = "<span class=\"error\">Error</span>: " + error.message;
          rjdci.getClosest(eve.target, ".invoiceTable").appendChild(errorMessage);
          setTimeout(() => { rjdci.getClosest(eve.target, ".invoiceTable").removeChild(errorMessage); eve.target.disabled = false; }, 3500)
        });
      });
    });

    if (document.querySelector("#invoiceQueryResults .invoiceTable")) {
      Array.from(document.querySelectorAll("#invoiceQueryResults .invoiceTable, #invoiceQueryResults .invoiceGraphContainer")).forEach(element => {
        element.addEventListener("touchstart", eve => {
          rjdciSwipe.disable();
        });
      });
      Array.from(document.querySelectorAll("#invoiceQueryResults .invoiceTable, #invoiceQueryResults .invoiceGraphContainer")).forEach(element => {
        element.addEventListener("touchend", eve => {
          rjdciSwipe.enable();
        });
      });
    }
  };

  rjdci.assignQueriedTicketListeners = () => {
    Array.from(document.querySelectorAll("#ticketQueryResults .invoiceQuery")).forEach(element => {
      element.addEventListener("click", async eve => {
        eve.preventDefault();
        let postData = {};
        Array.from(rjdci.getClosest(eve.target, "form").querySelectorAll("input")).forEach(input => {
          postData[input.getAttribute("name")] = input.value;
        });
        postData.formKey = document.querySelector("#formKey").value;
        document.querySelector("#invoiceQueryResults").innerHTML = '<p class="center"><span class="ellipsis">.</span></p>';
        rjdciSwipe.slide(document.querySelector("a.nav[data-id='invoice_query']").getAttribute("data-value"), 300);
        let forward = true,
          ellipsis = document.querySelector("#invoiceQueryResults .ellipsis"),
          dots = setInterval(() => {
            if (forward === true) {
              ellipsis.innerHTML += "..";
              forward = ellipsis.innerHTML.length < 21 && ellipsis.innerHTML.length != 1;
            }
            if (forward === false) {
              ellipsis.innerHTML = ellipsis.innerHTML.substr(0,ellipsis.innerHTML.length - 2);
              forward = ellipsis.innerHTML.length === 1;
            }
          }, 500);
        await rjdci.fetch_template({ url: "./buildQuery.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
          if (result.ok) {
            return result.text();
          } else {
            throw new Error(result.status + " " + result.statusText);
          }
        })
        .then(data => {
          clearInterval(dots);
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          let newDom = parser.parseFromString(data, "text/html"),
            docFrag = document.createDocumentFragment();
          Array.from(newDom.querySelectorAll("#invoice, .result")).forEach(element => {
            docFrag.appendChild(element);
          });
          document.querySelector("#invoiceQueryResults").innerHTML = "";
          document.querySelector("#invoiceQueryResults").appendChild(docFrag);
          assignQueriedInvoiceListeners();
        })
        .catch(error => {
          console.error(error.message);
          clearInterval(dots);
          document.querySelector("#invoiceQueryResults").innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + '</p>';
          setTimeout(() => { document.querySelector("#invoiceQueryResults").innerHTML = ""; }, 3500);
        });
      });
    });
    Array.from(document.querySelectorAll("#ticketQueryResults .sigPrint td")).forEach(element => {
      element.addEventListener("click", eve => {
        let target = eve.target.parentElement.nextElementSibling;
        target.style.display = (target.style.display === "none" || target.style.display === "") ? "block" : "none";
      });
    });
    Array.from(document.querySelectorAll("#ticketQueryResults .submitTicketQuery")).forEach(element => {
      element.addEventListener("click", async eve => {
        eve.preventDefault();
        eve.target.disabled = true;
        let breakFunction = false,
          postData = {},
          ellipsis = document.createElement("span"),
          container = document.createElement("p");
        container.classList.add("center");
        ellipsis.innerHTML = ".";
        container.appendChild(ellipsis);
        Array.from(rjdci.getClosest(eve.target, "form").querySelectorAll("input")).forEach(input => {
          if (input.value)  postData[input.getAttribute("name")] = input.value;
        });
        postData.formKey = document.querySelector("#formKey").value;
        if (breakFunction) {
          eve.target.disabled = false;
          return false;
        }
        document.querySelector("#ticketQueryResults").appendChild(container);
        let forward = true,
          dots = setInterval(() => {
            if (forward === true) {
              ellipsis.innerHTML += "..";
              forward = ellipsis.innerHTML.length < 21 && ellipsis.innerHTML.length != 1;
            }
            if (forward === false) {
              ellipsis.innerHTML = ellipsis.innerHTML.substr(0,ellipsis.innerHTML.length - 2);
              forward = ellipsis.innerHTML.length === 1;
            }
          }, 500);
        await rjdci.fetch_template({ url: "./buildQuery.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
          if (result.ok) {
            return result.text();
          } else {
            throw new Error(result.status + " " + result.statusText);
          }
        })
        .then(data => {
          eve.target.disabled = false;
          clearInterval(dots);
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          let newDom = parser.parseFromString(data, "text/html"),
            docFrag = document.createDocumentFragment();
          Array.from(newDom.querySelectorAll(".tickets, .result")).forEach(element => {
            docFrag.appendChild(element);
          });
          document.querySelector("#ticketQueryResults").innerHTML = "";
          document.querySelector("#ticketQueryResults").appendChild(docFrag);
          rjdci.assignQueriedTicketListeners();
        })
        .catch(error => {
          console.error(error.message);
          eve.target.disabled = false;
          clearInterval(dots);
          document.querySelector("#ticketQueryResults").innerHTML = '<p class="center"><span class="error">Error</span>: ' + error.message + '</p>';
          setTimeout(() => { document.querySelector("#ticketQueryResults").innerHTML = ""; }, 3500);
        });
      });
    });
    if (document.querySelector("#ticketPDF")) {
      document.querySelector("#ticketPDF").addEventListener("click", eve => {
        eve.preventDefault();
        let cln = document.querySelector("#ticketQueryResults").cloneNode(true);
          cln.querySelector("#ticketPDF").remove();
        Array.from(cln.querySelectorAll(".submitTicketQuery")).forEach(element => {
          element.parentNode.remove();
        });
        document.querySelector("#ticketPDFform input[name='content']").value = cln.outerHTML;
        document.querySelector("#ticketPDFform input[name='formKey']").value = document.querySelector("#formKey").value;
        document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
        document.querySelector("#ticketPDFform").submit();
      });
    }
    if (document.querySelector("#ticketQueryResults .bargraph")) {
      document.querySelector("#ticketQueryResults .bargraph").addEventListener("touchstart", eve => {
        rjdciSwipe.disable();
      });
      document.querySelector("#ticketQueryResults .bargraph").addEventListener("touchend", eve => {
        rjdciSwipe.enable();
      });
    }
  };

  assignTicketEditorListener = () => {
    Array.from(document.querySelectorAll("#ticketEditorResultContainer button.ticketEditor")).forEach(element => {
      if (element.getAttribute("data-assigned")) return;
      element.setAttribute("data-assigned", 1);
      element.addEventListener("click", async eve => {
        eve.target.classList.add("red");
        setTimeout(() => { eve.target.classList.remove("red"); }, 3000);
        let workspace = rjdci.getClosest(eve.target, ".sortable"),
          postData = {};
        postData.ticket_index = eve.target.getAttribute("data-index");
        postData.ticketEditor = 1;
        postData.formKey = document.querySelector("#formKey").value;
        await rjdci.fetch_template({ url: "./enterTicket.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
          if (result.ok) {
            return result.text();
          } else {
            throw new Error(result.status + " " + result.statusText);
          }
        })
        .then(data => {
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          let newDom = parser.parseFromString(data, "text/html"),
            docFrag = document.createDocumentFragment();
          Array.from(workspace.querySelectorAll("table, hr, button.ticketEditor")).forEach(element => {
            element.classList.add("hide");
          });
          Array.from(newDom.querySelectorAll(".removableByEditor")).forEach(element => {
            docFrag.appendChild(element);
          });
          workspace.appendChild(docFrag);
          if (workspace.parentNode.querySelector("datalist")) {
            while (workspace.parentNode.querySelector("datalist")) {
              workspace.parentNode.removeChild(workspace.parentNode.querySelector("datalist"));
            }
          }
          Array.from(newDom.querySelectorAll("datalist")).forEach(element => {
            workspace.parentNode.prepend(element);
          });
          assignTicketFormListeners(workspace);
        })
        .catch(error => {
          console.error(error.message);
          let message = document.createElement("p");
          message.classList.add("center");
          message.innerHTML = '<span class="error">Error</span>: ' + error.message;
          workspace.appendChild(message);
          setTimeout(() => { workspace.removeChild(message); }, 3500);
        });
      });
    });
  };

  assignTicketFormListeners = workspace => {
    let repeatHandler = eve => {
      workspace.querySelector(".billTo").setAttribute("list", (eve.target.checked) ? "t_clients" : "clients");
      workspace.querySelector(".billTo").value = "";
      if (workspace.querySelector("checkbox.contract")) {
        workspace.querySelector(".contract").disabled = eve.target.checked;
        if (eve.target.checked) workspace.querySelector(".contract").checked = false;
      }
    };
    if (workspace.querySelector(".repeat")) {
      workspace.querySelector(".repeat").removeEventListener("change", repeatHandler);
      workspace.querySelector(".repeat").addEventListener("change", repeatHandler);
    };

    let emailConfirmHandler = eve => {
      let form = rjdci.getClosest(eve.target, "form");
      if (eve.target.value !== "0") {
        form.querySelector(".emailAddress").required = true;
        if (form.querySelector(".emailNote")) form.querySelector(".emailNote").classList.remove("hide");
      } else {
        form.querySelector(".emailAddress").required = false;
        if (form.querySelector(".emailNote")) form.querySelector(".emailNote").classList.add("hide");
      }
    };
    workspace.querySelector(".emailConfirm").removeEventListener("change", emailConfirmHandler);
    workspace.querySelector(".emailConfirm").addEventListener("change", emailConfirmHandler);

    let dryIceHandler = eve => {
      let field = rjdci.getClosest(eve.target, "fieldset");
      if(eve.target.checked){
        field.querySelector(".diWeight").value = "0";
        field.querySelector(".diWeight").disabled = false;
        field.querySelector(".diWeight").focus();
        field.querySelector(".diWeightMarker").value = "0";
        field.querySelector(".diWeightMarker").disabled = true;
      } else{
        field.querySelector(".diWeightMarker").value = "0"
        field.querySelector(".diWeightMarker").disabled = false;
        field.querySelector(".diWeight").value = "0";
        field.querySelector(".diWeight").disabled = true;
      }
    };
    Array.from(workspace.querySelectorAll(".dryIce")).forEach(element => {
      element.removeEventListener("change", dryIceHandler);
      element.addEventListener("change", dryIceHandler);
    });

    let chargeHandler = eve => {
      Array.from(workspace.querySelectorAll(".rtMarker")).forEach(element => {
        if (eve.target.value === "6" || eve.target.value === "7") {
          element.style.display = "inline";
        } else {
          element.style.display = "none";
          element.checked = false;
        }
      });
      if (workspace.querySelector("input[name='d2TimeStamp']")) workspace.querySelector("input[name='d2TimeStamp']").disabled = (eve.target.value !== "6" && eve.target.value !== "7");
      if (eve.target.value === "7") {
        workspace.querySelector(".dedicatedNote").style.display = "block";
        Array.from(workspace.querySelectorAll("input[id^='pSigReq'], input[id^='dSigReq']")).forEach(element => {
          element.checked = true;
          rjdci.triggerEvent(element, "change");
        });
      } else {
        workspace.querySelector(".dedicatedNote").style.display = "none";
      }
    }
    Array.from(workspace.querySelectorAll(".charge")).forEach(element => {
      element.removeEventListener("change", chargeHandler);
      element.addEventListener("change", chargeHandler);
    });

    Array.from(workspace.querySelectorAll("input[type='number']")).forEach(element => {
      element.addEventListener("keydown", () => { if (element.value === "0") element.value = ""; });
    });

    Array.from(workspace.querySelectorAll("input[type='checkbox'][name='pSigReq'], input[type='checkbox'][name='dSigReq']")).forEach(element => {
      element.addEventListener("click", eve => {
        if (workspace.querySelector(".charge").value === "7") eve.preventDefault();
      });
    });

    Array.from(workspace.querySelectorAll("input[type='checkbox'][name='pSigReq'], input[type='checkbox'][name='dSigReq'], input[type='checkbox'][name='d2SigReq']")).forEach(element => {
      element.addEventListener("change", eve => {
        let x = workspace.querySelector("input[type='checkbox'][name='pSigReq']").checked,
          y = workspace.querySelector("input[type='checkbox'][name='dSigReq']").checked,
          z = workspace.querySelector("input[type='checkbox'][name='d2SigReq']").checked,
          sigNote = workspace.querySelector(".sigNote");
        if (!x && !y && !z) {
          if (!sigNote.classList.contains("hide")) sigNote.classList.add("hide");
        } else {
          sigNote.classList.remove("hide");
        }
      });
    });

    Array.from(workspace.querySelectorAll("input[list]")).forEach(element => {
      element.addEventListener("change", eve => {
        if (rjdci.isTarget(eve.target)) {
          let goodVals = [];
          Array.from(document.querySelectorAll("datalist#" + eve.target.getAttribute("list") + " option")).forEach(val => {
            let quarantine = new DOMParser().parseFromString(val.innerHTML, "text/html");
            quarantine.innerHTML = val.innerHTML;
            goodVals.push(quarantine.documentElement.textContent);
          });
          if (eve.target.value !== "" && goodVals.indexOf(eve.target.value) === -1) {
            let oldHolder = eve.target.getAttribute("placeholder") || "";
            eve.target.value = "";
            eve.target.classList.add("elementError");
            eve.target.placeholder = "Selection Not On File";
            rjdci.triggerEvent(eve.target, "change");
            setTimeout(() => { eve.target.classList.remove("elementError"); eve.target.placeholder = oldHolder; }, 3000);
            return;
          }
        }
        eve.target.title = eve.target.value;
      });
      if (element.getAttribute("name").substr(1) === "Address1") {
        element.addEventListener("blur", eve => {
          let index;
          Array.from(document.querySelectorAll("#addy1 option")).forEach(ele => {
            if (ele.value === eve.target.value) index = ele.getAttribute("data-value");
          });
          if (typeof index === "undefined") return;
          Array.from(document.querySelectorAll("#addy2 option")).forEach(ele => {
            if (ele.getAttribute("data-value") === index) rjdci.getClosest(eve.target, "fieldset").querySelector("input[name='" + eve.target.getAttribute("name").slice(0, -1) + "2']").value = ele.value;
          });
        });
      }
    });

    Array.from(workspace.querySelectorAll("#toMe, #fromMe")).forEach(element => {
      element.addEventListener("click", eve => {
        let neighbor = (rjdci.getClosest(eve.target, "fieldset").getAttribute("id") === "deliveryField") ? document.querySelector("#pickupField") : document.querySelector("#deliveryField"),
          testVal = "";
        Array.from(neighbor.querySelectorAll(".clientList")).forEach(input => {
          testVal += input.value;
        });
        let homeAddress = document.querySelector("input[name='ClientName'][form='javascriptVars']").value + document.querySelector("input[name='Department'][form='javascriptVars']").value + document.querySelector("input[name='ShippingAddress1'][form='javascriptVars']").value + document.querySelector("input[name='ShippingAddress2'][form='javascriptVars']").value;
        if(testVal === homeAddress) eve.preventDefault();
      });
      element.addEventListener("change", eve => {
        let neighbor = (rjdci.getClosest(eve.target, "fieldset").getAttribute("id") === "deliveryField") ? document.querySelector("#pickupField") : document.querySelector("#deliveryField");
        if (eve.target.checked) {
          rjdci.getClosest(eve.target, "thead").querySelector(".onFile").checked = false;
          rjdci.triggerEvent(rjdci.getClosest(eve.target, "thead").querySelector(".onFile"), "change");

          Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientList")).forEach(input => {
            input.disabled = false;
            input.required = false;
            input.readOnly = true;
            input.style.display = "inline";
          });
          Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientSelect")).forEach(input => {
            input.disabled = true;
            input.style.display = "none";
          });
          neighbor.querySelector(".me").disabled = true;
          rjdci.getClosest(eve.target, "fieldset").querySelector("[id$='Client']").value = document.querySelector("input[name='ClientName'][form='javascriptVars']").value;
          rjdci.getClosest(eve.target, "fieldset").querySelector("[id$='Department']").value = document.querySelector("input[name='Department'][form='javascriptVars']").value;
          rjdci.getClosest(eve.target, "fieldset").querySelector("[id$='Address1']").value = document.querySelector("input[name='ShippingAddress1'][form='javascriptVars']").value;
          rjdci.getClosest(eve.target, "fieldset").querySelector("[id$='Address2']").value = document.querySelector("input[name='ShippingAddress2'][form='javascriptVars']").value;
        } else {
          Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientList")).forEach(input => {
            input.readOnly = false;
          })
          neighbor.querySelector(".me").disabled = false;
        }
      });
    });

    Array.from(document.querySelectorAll(".onFile")).forEach(element => {
      element.addEventListener("change", eve => {
        if (eve.target.checked) {
          rjdci.getClosest(eve.target, "thead").querySelector(".me").checked = false;
          Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientList")).forEach(input => {
            input.disabled = true;
            input.required = false;
            input.readOnly = false;
            input.style.display = "none";
          });
          Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientSelect")).forEach(input => {
            input.disabled = false;
            input.style.display = "inline";
          });
          if (eve.target.id === "onFileP") {
            document.querySelector("#toMe").disabled = false;
          } else {
            document.querySelector("#fromMe").disabled = false;
          }
        } else {
          Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientList")).forEach(input => {
            input.disabled = false;
            input.required = input.getAttribute("name").substr(1) !== "Department";
            input.style.display = "inline";
          });
          Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientSelect")).forEach(input => {
            input.disabled = true;
            input.style.display = "none";
          });
        }
      });
    });

    Array.from(document.querySelectorAll(".clientSelect")).forEach(element => {
      element.addEventListener("change", eve => {
        let listIndex = eve.target.options[eve.target.selectedIndex].getAttribute("data-value");
        Array.from(rjdci.getClosest(eve.target, "fieldset").querySelectorAll(".clientSelect")).forEach(input => {
          Array.from(input.options).forEach(option => {
            option.selected = (option.getAttribute("data-value") === listIndex);
          });
        });
      });
    });

    workspace.querySelector(".receivedReady").addEventListener("change", eve => {
      Array.from(workspace.querySelectorAll(".readyNote, .readyDate")).forEach(element => {
        element.style.display = (element.style.display === "none") ? "inline-block" : "none";
        if (element.classList.contains("readyDate")) {
          if (eve.target.checked === true) {
            element.value = "";
          }
          rjdci.triggerEvent(element, "change");
          element.required = !eve.target.checked;
        }
      });
    });

    let readyHandler = eve => {
      if (/(deliveryRequest\d+)/.test(rjdci.getClosest(eve.target, "div").id)) return;
      let d1 = new Date(),
        d2 = new Date(eve.target.value);
      if (d1 >= d2) {
        let readyError = document.createElement("p");
        readyError.classList.add("readyError");
        readyError.innerHTML = 'Ready Time should be either "Now" or a time in the future.';
        if (!workspace.querySelector(".readyError")) {
          workspace.querySelector(".ticketError").appendChild(readyError);
        }
      } else {
        if (workspace.querySelector(".readyError")) {
          let element = workspace.querySelector(".readyError")
          element.parentNode.removeChild(element);
        }
      }
      workspace.querySelector(".submitForm").disabled = workspace.querySelector(".readyError") !== null;
    }
    workspace.querySelector(".readyDate").removeEventListener("change", readyHandler);
    workspace.querySelector(".readyDate").addEventListener("change", readyHandler);

    if (workspace.querySelector(".cancelTicketEditor")) {
      workspace.querySelector(".cancelTicketEditor").addEventListener("click", eve => {
        workspace.removeChild(workspace.querySelector(".removableByEditor"));
        if (document.querySelectorAll(".cancelTicketEditor").length === 0) {
          while (workspace.parentNode.querySelector("datalist")) {
            workspace.parentNode.removeChild(workspace.parentNode.querySelector("datalist"));
          }
        }
        Array.from(workspace.querySelectorAll("table.hide, hr.hide, button.hide")).forEach(element => {
          element.classList.remove("hide");
        })
      });
    }

    let submitHandler = async eve => {
      eve.preventDefault();
      eve.target.disabled = true;
      if (workspace.querySelector(".cancelTicketEditor"))
        workspace.querySelector(".cancelTicketEditor").disabled = true;
      let diStep = 1,
        ele = document.createElement("span");
      if (workspace.querySelector(".diWeight")) {
        diStep = workspace.querySelector(".diWeight").getAttribute("step");
      }
      ele.classList.add("ellipsis");
      ele.innerHTML = ".";
      workspace.querySelector(".ticketError").innerHTML = "";
      workspace.querySelector(".ticketError").appendChild(ele);
      let forward = true,
        dots = setInterval(() => {
          if (forward === true) {
            ele.innerHTML += "..";
            forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
          }
          if (forward === false) {
            ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
            forward = ele.innerHTML.length === 1;
          }
        }, 500);
      let breakFunction = false,
        formID = (workspace.querySelector(".removableByEditor").getAttribute("id") === "deliveryRequest") ? "" : workspace.querySelector(".removableByEditor").getAttribute("id").match(/\d+/)[0],
        requiredElements = ["pClient", "pAddress1", "pAddress2", "pCountry", "dClient", "dAddress1", "dAddress2", "dCountry", "dispatchedTo", "billTo"],
        postData = {};
      if (formID === "" || document.querySelector("#request" + formID + " input[name='contract']").value === "0") {
        requiredElements.push("requestedBy");
      }
      Array.from(workspace.querySelectorAll("input[form='request" + formID + "'], select[form='request" + formID + "'], textarea[form='request" + formID + "']")).forEach(element => {
        if (element.name) {
          if (element.type !== "checkbox" && element.disabled === false) {
            if (requiredElements.indexOf(element.getAttribute("name")) !== -1 && element.value === "") {
              breakFunction = true;
              element.classList.add("elementError");
              setTimeout(() => { element.classList.remove("elementError"); }, 3000);
            } else {
              element.classList.remove("elementError");
            }
            if (rjdci.getClosest(element, "div").id === "deliveryRequest") {
              if (element.getAttribute("name") === "readyDate" && element.style.display !== "none") {
                let readyError = document.createElement("p");
                  readyError.classList.add("readyError");
                  readyError.classList.add("center");
                  readyError.innerHTML = 'Ready Time should be either "Now" or a time in the future.',
                  d1 = new Date(),
                  d2 = new Date(element.value);
                if (d1 >= d2 || element.value === "") {
                  breakFunction = true;
                  if (!workspace.querySelector(".readyError")) {
                    workspace.querySelector(".ticketError").parentNode.appendChild(readyError);
                  }
                  element.classList.add("elementError");
                  setTimeout(() => {
                    element.classList.remove("elementError");
                    if (workspace.querySelector(".readyError")) readyError.parentNode.removeChild(readyError);
                  }, 3000);
                }
              }
            }
            if (/(deliveryRequest\d+)/.test(rjdci.getClosest(element, "div").id)) {
              let timingError = document.createElement("p");
              timingError.classList.add("timingError");
              timingError.classList.add("center");
              timingError.innerHTML = "Timing Error";
              switch (element.getAttribute("name")) {
                case "readyDate":
                  if (element.style.display !== "none") {
                    if (element.value === "") {
                      breakFunction = true;
                      if (!workspace.querySelector(".timingError")) {
                        workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                      }
                      element.classList.add("elementError");
                      setTimeout(() => {
                        element.classList.remove("elementError");
                        if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                      }, 3000);
                    }
                    if (
                      workspace.querySelector(".pTimeStamp").value !== "" &&
                      workspace.querySelector(".pTimeStamp").value < element.value
                    ) {
                      breakFunction = true;
                      if (!workspace.querySelector(".timingError")) {
                        workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                      }
                      workspace.querySelector(".pTimeStamp").classList.add("elementError");
                      setTimeout(() => {
                        workspace.querySelector(".pTimeStamp").classList.remove("elementError");
                        if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                      }, 3000);
                    }
                    if (
                      workspace.querySelector(".dTimeStamp").value !== "" &&
                      workspace.querySelector(".dTimeStamp").value < element.value
                    ) {
                      breakFunction = true;
                      if (!workspace.querySelector(".timingError")) {
                        workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                      }
                      workspace.querySelector(".dTimeStamp").classList.add("elementError");
                      setTimeout(() => {
                        workspace.querySelector(".dTimeStamp").classList.remove("elementError");
                        if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                      }, 3000);
                    }
                    if (
                      workspace.querySelector(".d2TimeStamp").value !== "" &&
                      workspace.querySelector(".d2TimeStamp").value < element.value
                    ) {
                      breakFunction = true;
                      if (!workspace.querySelector(".timingError")) {
                        workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                      }
                      workspace.querySelector(".d2TimeStamp").classList.add("elementError");
                      setTimeout(() => {
                        workspace.querySelector(".d2TimeStamp").classList.remove("elementError");
                        if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                      }, 3000);
                    }
                  }
                  break;
                case "d2TimeStamp":
                  if (element.value !== "") {
                    if (
                      (workspace.querySelector(".dTimeStamp").value === "" ||
                      workspace.querySelector(".dTimeStamp").value > element.value) ||
                      (workspace.querySelector(".pTimeStamp").value === "" ||
                      workspace.querySelector(".pTimeStamp").value > element.value) ||
                      (workspace.querySelector(".dispatchTimeStamp").value === "" ||
                      workspace.querySelector(".dispatchTimeStamp").value > element.value)
                    ) {
                      breakFunction = true;
                      if (!workspace.querySelector(".timingError")) {
                        workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                      }
                      element.classList.add("elementError");
                      setTimeout(() => {
                        element.classList.remove("elementError");
                        if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                      }, 3000);
                    }
                  }
                  break;
                case "dTimeStamp":
                  if (element.value !== "") {
                    if (
                      (workspace.querySelector(".pTimeStamp").value === "" ||
                      workspace.querySelector(".pTimeStamp").value > element.value) ||
                      (workspace.querySelector(".dispatchTimeStamp").value === "" ||
                      workspace.querySelector(".dispatchTimeStamp").value > element.value)
                    ) {
                      breakFunction = true;
                      if (!workspace.querySelector(".timingError")) {
                        workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                      }
                      element.classList.add("elementError");
                      setTimeout(() => {
                        element.classList.remove("elementError");
                        if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                      }, 3000);
                    }
                  }
                  break;
                case "pTimeStamp":
                  if (element.value !== "") {
                    console.log(workspace.querySelector(".dispatchTimeStamp").value, element.value, workspace.querySelector(".dispatchTimeStamp").value > element.value);
                    if (
                      workspace.querySelector(".dispatchTimeStamp").value === "" ||
                      workspace.querySelector(".dispatchTimeStamp").value > element.value
                    ) {
                      breakFunction = true;
                      if (!workspace.querySelector(".timingError")) {
                        workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                      }
                      element.classList.add("elementError");
                      setTimeout(() => {
                        element.classList.remove("elementError");
                        if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                      }, 3000);
                    }
                  }
                  break;
                case "dispatchTimeStamp":
                  if (element.value === "") {
                    breakFunction = true;
                    if (!workspace.querySelector(".timingError")) {
                      workspace.querySelector(".ticketError").parentNode.appendChild(timingError);
                    }
                    element.classList.add("elementError");
                    setTimeout(() => {
                      element.classList.remove("elementError");
                      if (workspace.querySelector(".timingError")) timingError.parentNode.removeChild(timingError);
                    }, 3000);
                  }
                  break;
              }
            }
            postData[element.getAttribute("name")] = element.value;
          } else if (element.type === "checkbox") {
            if (element.getAttribute("name") === "repeatClient") {
              postData[element.getAttribute("name")] = 1 - element.checked;
            } else {
              postData[element.getAttribute("name")] = 0 + element.checked;
            }
          }
        }
      });
      postData.formKey = document.querySelector("#formKey").value;
      postData.mapAvailable = (!rjdci.getClosest(workspace, ".page").querySelector(".mapContainer")) ? 0 : 1;
      if (postData.dryIce === 1 && (postData.diWeight % diStep !== 0 || postData.diWeight == 0)) {
        workspace.querySelector(".ticketError").innerHTML = `Dry Ice in increments of ${diStep} only.`;
        workspace.querySelector(".diWeight").classList.add("elementError");
        breakFunction = true;
        setTimeout(() => { workspace.querySelector(".ticketError").innerHTML = ""; workspace.querySelector(".diWeight").classList.remove("elementError"); }, 3500);
      }
      if (breakFunction === true) {
        clearInterval(dots);
        workspace.querySelector(".ticketError").innerHTML = "";
        Array.from(workspace.querySelectorAll(".submitForm, .cancelTicketEditor")).forEach(element => {
          element.disabled = false;
        });
        return false;
      }
      await rjdci.fetch_template({ url: "./enterTicket.php", postData: postData })
      .then(result => {
        if (typeof result === "undefined") throw new Error("Result Undefined");
        if (result.ok) {
          return result.text();
        } else {
          throw new Error(result.status + " " + result.statusText);
        }
      })
      .then(data => {
        clearInterval(dots);
        if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
        document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
        if (data.indexOf("data-value=\"error\"") !== -1) {
          workspace.querySelector(".ticketError").innerHTML = data;
          Array.from(workspace.querySelectorAll(".submitForm, .cancelTicketEditor")).forEach(element => {
            element.disabled = false;
          });
          throw new Error(data);
        } else {
          let newDom = parser.parseFromString(data, "text/html"),
            content = newDom.querySelector("#deliveryConfirmation") || newDom.querySelector(".editorConfirmation");
          workspace.querySelector(".removableByEditor").innerHTML = "";
          workspace.querySelector(".removableByEditor").appendChild(content);
          if (postData.mapAvailable) {
            scroll(0,0);
            let options = {};
            Array.from(document.querySelectorAll("input[form='coordinates']")).forEach(input => {
              options[input.getAttribute("name")] = input.value;
            });
            options.mapDivID = "map";
            rjdci.updateMap(options);
          }
          assignConfirmationListeners(workspace);
        }
      })
      .catch(error => {
        console.error(error.message);
        clearInterval(dots);
        workspace.querySelector(".ticketError").innerHTML = '<span class="error">Error</span>: ' + error.message;
        setTimeout(() => { workspace.querySelector(".ticketError").innerHTML = ""; }, 3500);
        Array.from(workspace.querySelectorAll(".submitForm, .cancelTicketEditor")).forEach(element => {
          element.disabled = false;
        });
      });
    }
    workspace.querySelector(".submitForm").removeEventListener("click", submitHandler);
    workspace.querySelector(".submitForm").addEventListener("click", submitHandler);
  }

  assignConfirmationListeners = workspace => {
    Array.from(workspace.querySelectorAll(".confirmed, .editForm")).forEach(element => {
      element.addEventListener("click", async eve => {
        eve.preventDefault();
        let formID = eve.target.getAttribute("form"),
          postData = {};
        postData.formKey = document.querySelector("#formKey").value;
        postData.ticketEditor = 1;
        Array.from(workspace.querySelectorAll("input[form='" + eve.target.getAttribute("form") + "']")).forEach(input => {
          postData[input.getAttribute("name")] = input.value;
        });
        if (eve.target.classList.contains("confirmed") && /\d/.test(eve.target.getAttribute("form"))) {
          postData.updateTicket = 1;
        }
        if (eve.target.classList.contains("editForm") && !/\d/.test(eve.target.getAttribute("form"))) {
          postData.ticketEditor = 0;
        }
        await rjdci.fetch_template({ url: "./enterTicket.php", postData: postData })
        .then(result => {
          if (typeof result === "undefined") throw new Error("Result Undefined");
          if (result.ok) {
            return result.text();
          } else {
            throw new Error(result.status + " " + result.statusText);
          }
        })
        .then(data => {
          if (data.indexOf("Session Error") !== -1) return rjdci.showLogin();
          document.querySelector("#formKey").value = Number(document.querySelector("#formKey").value) + 1;
          if (data.indexOf("data-value=\"error\"") !== -1) {
            throw new Error(data);
          }
          if (eve.target.classList.contains("editForm")) {
            Array.from(workspace.querySelectorAll("datalist, .removableByEditor")).forEach(element => {
              element.parentNode.removeChild(element);
            });
            let newDom = parser.parseFromString(data, "text/html"),
              docFrag = document.createDocumentFragment(),
              mapElement = document.querySelector(".subContainer");
            Array.from(newDom.querySelectorAll("datalist, div")).forEach(element => {
              docFrag.appendChild(element);
            });
            workspace.appendChild(docFrag);
            if (!(/\d/.test(eve.target.getAttribute("form")))) {
              workspace.appendChild(mapElement);
              rjdci.updateMap({mapDivID: "map"});
            }
            assignTicketFormListeners(workspace);
          } else if (eve.target.classList.contains("confirmed")) {
            if (data === "remove") {
              let newEle = document.createElement("p");
              newEle.classList.add("center");
              newEle.innerHTML = "Update Successful";
              workspace.appendChild(newEle);
              scrollTo(0, document.querySelector("header").offsetHeight + 5);
              setTimeout(() => { document.remove(workspace); }, 3000);
              return;
            }
            if (!rjdci.getClosest(workspace, ".page").querySelector(".mapContainer")) {
              let ticketContainer = document.querySelector("#ticketEditorResultContainer"),
                targetTicket = rjdci.getClosest(eve.target, ".sortable"),
                newDom = parser.parseFromString(data, "text/html"),
                docFrag = document.createDocumentFragment(),
                note = document.createElement("p");
              note.classList.add("center");
              note.innerHTML = "Update Successful";
              targetTicket.appendChild(note);
              docFrag.appendChild(newDom.querySelector(".sortable"));
              setTimeout(() => {
                ticketContainer.insertBefore(docFrag, targetTicket);
                ticketContainer.removeChild(targetTicket);
                assignTicketEditorListener();
              }, 3500);
            } else {
              let newDom = parser.parseFromString(data, "text/html");
              document.querySelector("#deliveryConfirmation").innerHTML = "";
              document.querySelector("#deliveryConfirmation").appendChild(newDom.querySelector("#deliveryRequestComplete"));
              setTimeout(rjdci.refreshTicketEntry, 3500);
            }
          }
        })
        .catch(error => {
          console.error(error.message);
          workspace.querySelector(".ticketError").innerHTML = '<p class="ceneter"><span class="error">Error</span>: ' + error.message + '</p>';
          setTimeout(() => { workspace.querySelector(".ticketError").innerHTML = ""; }, 3500);
        });
      });
    });
  }

}( window.rjdci = window.rjdci || {} ));

(() => {
  document.addEventListener("rjdci_resolutionchange", function() {
    setTimeout(() => { rjdci.fixDeadRunButton(); rjdci.centerForm(document.querySelector("#confirmLogin")); }, 500);
  });

  document.addEventListener("rjdci_loggedout", function() {
    setTimeout(rjdci.disableApp, 500);
  });

  document.addEventListener("rjdci_loggedin", function() {
    setTimeout(rjdci.enableApp, 500);
  });
// Start offline
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
      rjdci.toast("You are now offline..");
      header.classList.add("app__offline");
      menuHeader.style.background = "#9E9E9E";
    }
  }
// End offline
// Start navigatation
  let menuIconElement = document.querySelector(".header__icon"),
      menuElement = document.querySelector(".menu"),
      menuOverlayElement = document.querySelector(".menu__overlay"),
      subscriptionButton = document.querySelector(".fab__push");
  //Menu click event
  menuIconElement.addEventListener("click", showMenu, false);
  menuOverlayElement.addEventListener("click", hideMenu, false);
  Array.from(document.querySelectorAll(".menu__list li")).forEach(element => { element.addEventListener("click", hideMenu, false); } );
  if (subscriptionButton !== null) subscriptionButton.addEventListener("click", hideMenu, false);
  //To show menu
  function showMenu() {
    let canvasTest = document.querySelector("#signature-pad");
    if (canvasTest !== null) return false;
    if (document.querySelector("header").classList.contains("loggedout")) return false;
    let newUpdate = document.querySelector("#newUpdate");
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
  }
// End navigation
  domready(() => {
    let noData = false;
    // assign data-values to navigation links
    rjdci.assignLinkValues();

    document.querySelector("#logoutLink button").addEventListener("click", eve => {
      eve.preventDefault();
      rjdci.logout();
    });
    // only run this function if the login confirmation form is present indicating a client or driver is logged in
    if (document.querySelector("#confirmLogin")) {
      rjdci.populatePage()
      .then(() => {
        rjdci.assignListeners();
        rjdci.assignQueriedTicketListeners()
        document.dispatchEvent(rjdci.loaded)
      });
    }

    Array.from(document.querySelectorAll(".menu__list li")).forEach( element => { element.addEventListener("click", () => { if (element.querySelector("a")) rjdciSwipe.slide(element.querySelector("a").getAttribute("data-value"), 300); }) });

    document.querySelector(".header__icon").addEventListener("click", () => {
      let target1 = document.querySelector("#sig"),
          target2 = document.querySelector("#confirmLogin");
      if (target1 !== null || (target2.offsetWidth > 0 && target2.offsetHeight > 0)) return false;
    });

    document.querySelector(".refresh").addEventListener("click", () => {
      location.reload();
    });

    document.querySelector("#confirm").addEventListener("click", async eve  => {
      eve.preventDefault();
      let breakFunction = false;
      Array.from(document.querySelectorAll("#login input")).forEach(element => {
        if (element.id !== "function" && element.value === "") {
          breakFunction = true;
          element.classList.add("elementError");
          setTimeout(() => { element.classList.remove("elementError"); }, 3000);
        }
      });
      if (breakFunction === true) return false;
      let ele = document.createElement("span");
      ele.classList.add("ellipsis");
      ele.innerHTML = ".";
      document.querySelector("#confirmMessage").appendChild(ele);
      let forward = true,
          dots = setInterval(() => {
        if (forward === true) {
          ele.innerHTML += "..";
          forward = ele.innerHTML.length < 21 && ele.innerHTML.length != 1;
        }
        if (forward === false) {
          ele.innerHTML = ele.innerHTML.substr(0,ele.innerHTML.length - 2);
          forward = ele.innerHTML.length === 1;
        }
      }, 500);
      let data = {};
      data.clientID = document.querySelector("#uid").value;
      data.upw = document.querySelector("#upw").value;
      data.mobile = document.querySelector("#mobile").value;
      data.noSession = 1;
      await rjdci.fetch_template({ url: "./refreshFormKey.php" })
      .then(result => {
        if (typeof result === "undefined") throw new Error("Result Undefined");
        if (result.ok) {
          return result.text();
        } else {
          throw new Error(result.status + " " + result.statusText);
        }
      })
      .then(async newKey => {
        document.querySelector("#formKey").value = newKey;
        return await rjdci.fetch_template({ url: "../login.php", postData: data });
      })
      .then(login => {
        if (typeof login === "undefined") throw new Error(login.status + " " + login.statusText);
        if (login.ok) {
          return login.text();
        } else {
          throw new Error(login.status + " " + login.statusText);
        }
      })
      .then(loginResult => {
        clearInterval(dots);
        if (loginResult.indexOf("Session Error") !== - 1) throw new Error("Session Error");
        if (loginResult.indexOf("error") === - 1 && loginResult.indexOf("Invalid Credentials") === - 1) {
          document.querySelector("#confirmMessage").innerHTML = "User Confirmed";
          setTimeout(() => {
            document.querySelector("#confirmMessage").innerHTML = "";
            document.querySelector("#confirmLogin").classList.add("hide");
            if (document.querySelector("#function").value !== "") {
              let func = document.querySelector("#function").value;
              document.querySelector("#function").value = "";
              rjdci[func]();
            }
            document.dispatchEvent(rjdci.loggedin);
          }, 1000);
        } else {
          throw new Error(loginResult);
        }
      })
      .catch(error => {
        clearInterval(dots);
        document.querySelector("#confirmMessage").innerHTML = '<span class="error">Error</span>: ' + error.message;
        setTimeout(() => { document.querySelector("#confirmMessage").innerHTML = ""; }, 4000);
      });
    });

    document.querySelector("#cancel").addEventListener("click", () => {
      window.location.assign("./logout");
    });
  });
})();
