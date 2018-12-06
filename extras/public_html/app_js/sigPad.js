// open signature pad
$(document).ready(function() {
  $(document).on("click", ".pGetSig, .dGetSig, .d2GetSig", function() {
    let target,
        canvas,
        canvasWidth,
        canvasHeight,
        signaturePad,
        wrapper,
        printName,
        requiredState;
    //Disable the other buttons in the div
    $("input, button, textarea").prop("disabled", true);
    //Close any other open signature pad
    $(".signature-pad").each(function() {
      $(this).addClass("sigField").removeClass("field").html("");
    });
    target = $(this).parents(".sortable").find(".signature-pad");
    if (target.hasClass("sigField")) {
      canvasWidth = $(this).parents(".sortable").innerWidth();
      canvasHeight = ($(this).closest(".sortable").find(".tickets").length === 0) ? $(this).parents(".sortable").innerHeight() * .65 : $(this).closest(".sortable").find(".tickets").innerHeight();
      target.removeClass("sigField").addClass("field").attr("id", "signature-pad").html('<canvas id="sig" style="height:' + canvasHeight +'px;width:' + canvasWidth + 'px;"></canvas><button style="float:left;" type="button" data-action="clear">Clear</button><button style="float:right;" type="button" data-action="save">OK</button>');
      canvas = document.getElementById("sig");
      signaturePad = new SignaturePad(canvas);
      wrapper = document.getElementById("signature-pad"),
      clearButton = wrapper.querySelector("[data-action='clear']"),
      saveButton = wrapper.querySelector("[data-action='save']"),
      canvas = wrapper.querySelector("#sig"),
      signaturePad;
      signaturePad.minWidth = .5;
      signaturePad.maxWidth = 2;
      signaturePad.penColor = "red";
      signaturePad.backgroundColor = "black";
    };

    function resizeCanvas() {
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
    window.scroll(0,findPos(document.getElementById("sig")));

    saveButton.addEventListener("click", function (event) {
      printName = $(this).parents(".sortable").find(".printName");
      if (signaturePad.isEmpty()) {
        $(this).parents(".sortable").find("input[name='sigImage']").val("");
        toast("Signature Cleared.");
      } else {
        $(this).parents(".sortable").find("input[name='sigImage']").val(signaturePad.toDataURL());
      }

      requiredState = (printName.parents(".sortable").find("input[name='sigImage']").val() !== "" && typeof(printName.parents(".sortable").find("input[name='sigImage']").val()) !== undefined && printName.parents(".sortable").find("input[name='sigImage']").val() !== null) || printName.hasClass("pulse");

      target.removeClass("field").addClass("sigField").html('').attr("id", "");
      target.parents("body").find("input, button, textarea").prop("disabled", false);
      printName.prop("required", requiredState);
    });

    clearButton.addEventListener("click", function(event) {
      signaturePad.clear();
    });
    // The canvas is holding the page background color. Calling the clear function fixes this.
    signaturePad.clear();
  });

  $(document).on("touchstart", "#sig", function() {
    disable_scroll();
    mySwipe.disable();
  });

  $(document).on("touchend", "#sig", function() {
    enable_scroll();
    mySwipe.enable();
  });
});
