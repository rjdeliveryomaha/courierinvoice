rjdci.signatureListener = eve => {
  eve.target.classList.add("red");
  setTimeout(() => { eve.target.classList.remove("red"); }, 3000);
  let target,
    canvasWidth,
    canvasHeight,
    signaturePad,
    wrapper,
    printName,
    requiredState,
    boundingRect,
    workspace = rjdci.getClosest(eve.target, ".sortable"),
    page = rjdci.getClosest(eve.target, ".page"),
    canvas = document.createElement("canvas"),
    clearButton = document.createElement("button"),
    saveButton = document.createElement("button"),
    cancelButton = document.createElement("button"),
    sigElements = document.createDocumentFragment();
    sigButtonsParent = document.createElement("p");
  sigButtonsParent.style.display = "flex";
  canvas.id = "sig";
  sigElements.appendChild(canvas);
  clearButton.innerHTML = "Clear";
  clearButton.classList.add("sigButton");
  clearButton.addEventListener("click", eve => {
    signaturePad.clear();
  });
  sigButtonsParent.appendChild(clearButton);
  cancelButton.innerHTML = "Cancel";
  cancelButton.addEventListener("click", eve => {
    target.classList.remove("field");
    target.classList.add("sigField");
    target.id = "";
    target.innerHTML = "";
    printName = workspace.querySelector(".printName");
    printName.required = (workspace.querySelector("input[name='sigImage']").value !== "") || printName.required;
    Array.from(page.querySelectorAll("button")).forEach(input => {
      input.disabled = false;
    });
    Array.from(page.querySelectorAll(".printName, .notes")).forEach(input => {
      input.readOnly = false;
    });
  });
  sigButtonsParent.appendChild(cancelButton);
  saveButton.innerHTML = "OK";
  saveButton.classList.add("sigButton");
  saveButton.addEventListener("click", eve => {
    printName = workspace.querySelector(".printName");
    if (signaturePad.isEmpty()) {
      workspace.querySelector("input[name='sigImage']").value = "";
      rjdci.toast("Signature Cleared.");
    } else {
      workspace.querySelector("input[name='sigImage']").value = signaturePad.toDataURL();
    }
    target.classList.remove("field");
    target.classList.add("sigField");
    target.id = "";
    target.innerHTML = "";
    printName.required = (workspace.querySelector("input[name='sigImage']").value !== "") || printName.required;
    Array.from(page.querySelectorAll("button")).forEach(input => {
      input.disabled = false;
    });
    Array.from(page.querySelectorAll(".printName, .notes")).forEach(input => {
      input.readOnly = false;
    });
  });
  sigButtonsParent.appendChild(saveButton);
  sigElements.appendChild(sigButtonsParent);
  Array.from(page.querySelectorAll("button")).forEach(input => {
    input.disabled = true;
  });
  Array.from(page.querySelectorAll(".printName, .notes")).forEach(input => {
    input.readOnly = true;
  });
  target = workspace.querySelector(".signature-pad");
  boundingRect = workspace.getBoundingClientRect();
  canvas.style.width = boundingRect.width + "px";
  canvas.style.height = boundingRect.height * 0.65 + "px";
  if (workspace.querySelector(".tickets") !== null) {
    let newBoundingRect = workspace.querySelector(".tickets").getBoundingClientRect();
    canvasHeight = newBoundingRect.height;
  }
  target.classList.remove("sigField");
  target.classList.add("field");
  target.id = "signature-pad";
  target.appendChild(sigElements);
  signaturePad = new SignaturePad(canvas);
  signaturePad.velocityFilterWeight = 0.7;
  signaturePad.minWidth = .5;
  signaturePad.maxWidth = 2;
  signaturePad.penColor = "red";
  signaturePad.backgroundColor = "rgba(0,0,0,1)";

  resizeCanvas = () => {
    // When zoomed out to less than 100%, for some very strange reason,
    // some browsers report devicePixelRatio as less than 1
    // and only part of the canvas is cleared then.
    let ratio =  Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);
  }
  //Resize the canvas so that signature-pad sees the whole area
  resizeCanvas();
  // scroll to the sig pad
  window.scroll(0,rjdci.findPos(document.getElementById("sig")));

  // The canvas is holding the page background color. Calling the clear function fixes this.
  signaturePad.clear();
  canvas.addEventListener("touchstart", eve => {
    rjdci.disable_scroll();
    rjdciSwipe.disable();
  });
  canvas.addEventListener("touchend", eve => {
    rjdci.enable_scroll();
    rjdciSwipe.enable();
  });
};
// https://stackoverflow.com/a/11986374
// Finds y value of given object
// offset the position by the height of the header element
rjdci.findPos = obj => {
  let curtop = 0;
  if (obj.offsetParent) {
    do {
      curtop += obj.offsetTop;
    } while (obj = obj.offsetParent);
    return curtop - document.getElementsByTagName("header")[0].offsetHeight;
  }
};
document.addEventListener("rjdci_loaded", () => {
  if (document.querySelector("#route")) {
    Array.from(document.querySelectorAll(".getSig")).forEach(element => {
      element.addEventListener("click", eve => { rjdci.signatureListener(eve); });
    });
    let config = { attributes: false, childList: true, subtree: false },
      callback = (mutationsList, observer) => {
        for (let mutation of mutationsList) {
          if (mutation.type !== "childList") return;
          mutation.addedNodes.forEach(node => {
            Array.from(node.querySelectorAll(".getSig")).forEach(element => {
              if (!element.classList.contains("assigned")) {
                element.classList.add("assigned");
                element.addEventListener("click", eve => { rjdci.signatureListener(eve); });
              }
            });
          });
        }
      },
      observer = new MutationObserver(callback);
    observer.observe(document.querySelector("#route"), config);
    observer.observe(document.querySelector("#on_call"), config);
  }
});
