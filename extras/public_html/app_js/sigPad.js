rjdci.signatureListener = eve => {
  let target,
    canvas,
    canvasWidth,
    canvasHeight,
    signaturePad,
    wrapper,
    printName,
    requiredState,
    boundingRect,
    workspace = rjdci.getClosest(eve.target, ".sortable"),
    page = rjdci.getClosest(eve.target, ".page");
  Array.from(page.querySelectorAll("button, input, textarea")).forEach(input => {
    input.disabled = true;
    input.readonly = true;
  });
  target = workspace.querySelector(".signature-pad");
  if (target.classList.contains("sigField")) {
    boundingRect = workspace.getBoundingClientRect();
    canvasWidth = boundingRect.width;
    canvasHeight = boundingRect.height * 0.65;
    if (workspace.querySelector(".tickets") !== null) {
      let newBoundingRect = workspace.querySelector(".tickets").getBoundingClientRect();
      canvasHeight = newBoundingRect.height;
    }
    target.classList.remove("sigField");
    target.classList.add("field");
    target.id = "signature-pad";
    target.innerHTML = '<canvas id="sig" style="height:' + canvasHeight +'px;width:' + canvasWidth + 'px;"></canvas><button style="float:left;" type="button" data-action="clear">Clear</button><button style="float:right;" type="button" data-action="save">OK</button>';
    canvas = document.getElementById("sig");
    signaturePad = new SignaturePad(canvas);
    wrapper = document.getElementById("signature-pad"),
    clearButton = wrapper.querySelector("[data-action='clear']"),
    saveButton = wrapper.querySelector("[data-action='save']"),
    canvas = wrapper.querySelector("#sig"),
    signaturePad.velocityFilterWeight = 0.7;
    signaturePad.minWidth = .5;
    signaturePad.maxWidth = 2;
    signaturePad.penColor = "red";
    signaturePad.backgroundColor = "rgba(0,0,0,1)";
  }
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

  saveButton.addEventListener("click", eve => {
    printName = workspace.querySelector(".printName");
    if (signaturePad.isEmpty()) {
      workspace.querySelector("input[name='sigImage']").value = "";
      rjdci.toast("Signature Cleared.");
    } else {
      workspace.querySelector("input[name='sigImage']").value = signaturePad.toDataURL();
    }
    requiredState = (workspace.querySelector("input[name='sigImage']").value !== "") || printName.classList.contains("pulse");
    target.classList.remove("field");
    target.classList.add("sigField");
    target.id = "";
    target.innerHTML = "";
    printName.required = requiredState;
    Array.from(page.querySelectorAll("button, input, textarea")).forEach(input => {
      input.disabled = false;
      input.readonly = false;
    });
  });
  clearButton.addEventListener("click", eve => {
    signaturePad.clear();
  });
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
document.addEventListener("rjdci_loaded", eve => {
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
              element.addEventListener("click", eve => { rjdci.signatureListener(eve); });
            });
          });
        }
      },
      observer = new MutationObserver(callback);
    observer.observe(document.querySelector("#route"), config);
    observer.observe(document.querySelector("#on_call"), config);
  }
});
