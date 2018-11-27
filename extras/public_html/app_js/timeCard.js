function initTimeCard() {
  return ajax_template("POST", "./refreshTimeCard.php", "html", { formKey: $("#formKey").val() });
}
(() => {
  let timeCardCheck = setInterval(() => {
    if ($(".currentDay").length > 0) {
      let targetLink = $("#timeCard .currentDay").closest("div").find("a:first");
      if (targetLink.hasClass("showWeek1")) targetLink.trigger("click");
      clearInterval(timeCardCheck);
    } else if ($("#ticketQuery").length > 0 || $("#orgTickets").length > 0) {
      clearInterval(timeCardCheck);
    }
  }, 1000);
})();
$(window).bind("timeCardVisible", function() {
  const $element = $(".currentDay");
  if ($element.length !== 0) {
    let temp = $element.parents("form")[0].getBoundingClientRect();
    let scrollOffset = document.getElementsByTagName("header")[0].offsetHeight;
    scrollTo(0,(temp.top - scrollOffset));
  }
});
// https://stackoverflow.com/a/5347371
jQuery.fn.tagName = function() {
  return this.prop("tagName");
};
// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/round#Example:_Decimal_rounding
//http://stackoverflow.com/questions/10015027/ddg#23560569
(function(){

	/**
	 * Decimal adjustment of a number.
	 *
	 * @param	{String}	type	The type of adjustment.
	 * @param	{Number}	value	The number.
	 * @param	{Integer}	exp		The exponent (the 10 logarithm of the adjustment base).
	 * @returns	{Number}			The adjusted value.
	 */
	function decimalAdjust(type, value, exp) {
		// If the exp is undefined or zero...
		if (typeof exp === 'undefined' || +exp === 0) {
			return Math[type](value);
		}
		value = +value;
		exp = +exp;
		// If the value is not a number or the exp is not an integer...
		if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
			return NaN;
		}
		// Shift
		value = value.toString().split('e');
		value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
		// Shift back
		value = value.toString().split('e');
		return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
	}
	// Decimal round
	if (!Math.round10) {
		Math.round10 = function(value, exp) {
			return decimalAdjust('round', value, exp);
		};
	}
	// Decimal floor
	if (!Math.floor10) {
		Math.floor10 = function(value, exp) {
			return decimalAdjust('floor', value, exp);
		};
	}
	// Decimal ceil
	if (!Math.ceil10) {
		Math.ceil10 = function(value, exp) {
			return decimalAdjust('ceil', value, exp);
		};
	}

  Number.prototype.toFixedB = function toFixed ( precision ) {
    let multiplier = Math.pow( 10, precision + 1 ),
    wholeNumber = Math.floor( this * multiplier );
    return (Math.round( wholeNumber / 10 ) * 10 / multiplier).toFixed(precision);
  }

  Number.prototype.toFixed10 = function(precision) {
    return Math.round10(this, -precision).toFixed(precision);
  }
})();

function fixDecimal(value) {
  let test = parseFloat(value);
  return (test % 1 === 0) ? test : test.toFixed10(2);
}

function disableInput() {
  if ($(".in1").val() != '') {
    $(this).closest(".out1").prop("disabled", false);
    $(this).closest(".nextDay").prop("disabled", true).prop("checked", false);
  }
  else {
    $(this).closest(".out1, .in2, .out2").prop("disabled", true).val('');
    $(this).closest(".nextDay").prop("disabled", true).prop("checked", false);
  }

  if ($(".out1").val() != '') {
    $(this).closest(".in2").prop("disabled", false);
    $(this).closest(".nextDay").prop("disabled", true).prop("checked", false);
  }
  else {
    $(this).closest(".in2, .out2").prop("disabled", true).val('');
    $(this).closest(".nextDay").prop("disabled", true).prop("checked", false);
  }

  if ($(".in2").val() != '') {
    $(this).closest(".out2").prop("disabled", false);
    $(this).closest(".nextDay").prop("disabled", false);
  }
  else {
    $(this).closest(".out2").prop("disabled", true).val('');
  }
}

function timeTotal(val1, val2, val3, val4, nextDay) {
  let msMinute = 60*1000;
  let diff1 = 0;
  let diff2 = 0;
  let d1 = '';
  let d2 = '';
  let d3 = '';
  let d4 = '';
  if (val1.search('NULL') === -1 && val2.search('NULL') === -1) {
    d1 = new Date(val1);
    d2 = new Date(val2);
    diff1 = (d2 - d1) / msMinute;
  }
  if (val3.search('NULL') === -1 && val4.search('NULL') === -1) {
    d3 = new Date(val3);
    d4 = new Date(val4);
    nextDay ? d4.setDate(d4.getDate() + 1) : null;
    diff2 = (d4 - d3) / msMinute;
  }
  return fixDecimal((diff1 + diff2) / 60);
}

function solveTime() {
  $(".timeCardContainer").each(function() {
    let periodTotal = 0;
    $(this).find(".timeCard").each(function() {
      periodTotal += Number($(this).find(".dayTotal").text());
    });
    $(this).find(".periodTotal").text(periodTotal);
  });
}

function solveWeekTime(start) {
  let weekTotal = 0;
  start.parents("div[class^='week']").find(".dayTotal").each(function() {
    weekTotal += Number($(this).text());
  }).end().find(".weekTotal").text(weekTotal);
}

function clearMessage() {
  $(".timeCardContainer .message").html("");
}

$(document).ready(function() {
  
  $(document).on("click", ".showWeek1, .showWeek2", function() {
    $("#" + $(this).attr("data-id") + "_week1, #" + $(this).attr("data-id") + "_week2").toggleClass("hide");
  });
  
  $(document).on("click", ".updateTime", function(e) {
    e.preventDefault();
    let space = $(this).parents(".timeCard");
    space.find(".message").text('');
    let in1 = (space.find(".in1").val() === '') ? 'NULL' : space.find(".in1").val();
    let out1 = (space.find(".out1").val() === '') ? 'NULL' : space.find(".out1").val();
    let in2 = (space.find(".in2").val() === '') ? 'NULL' : space.find(".in2").val();
    let out2 = (space.find(".out2").val() === '') ? 'NULL' : space.find(".out2").val();
    let formKey = $("#formKey").val();
    let date = $(this).attr("data-date");
    let nextDay = space.find(".nextDay").is(":checked");
    let driverID = $(this).attr("data-driverid");
    space.find(".message").append('<span class="ellipsis">.</span>');
    let $ele =  $(this).parents("form").find(".ellipsis");
    let forward = true;
    let dots = window.setInterval(function() {
      if (forward === true) {
        $ele.append("..");
        forward = $ele.text().length < 21 && $ele.text().length != 1;
      }
      if (forward === false) {
        $ele.text($ele.text().substr(0,$ele.text().length - 2));
        forward = $ele.text().length === 1;
      }
    }, 500);
    let updateAttempt = ajax_template("POST", "./updateTimeCard.php", "html", { formKey:formKey, date:date, driverID:driverID, in1:in1, out1:out1, in2:in2, out2:out2, nextDay:nextDay })
    .done(function(result) {
      clearInterval(dots);
      if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
      space.find(".message").html(result).end().find(".dayTotal").text(timeTotal(date + ' ' + in1, date + ' ' + out1, date + ' ' + in2, date + ' ' + out2, nextDay));
      solveTime();
      solveWeekTime(space);
      setTimeout ( clearMessage, 4000 );
    })
    .fail(function(jqXHR, status, error) {
      space.find(".message").text(error);
    });
  });
  
  $(document).on("change", ".in1", function() {
    let space = $(this).parents(".timeCard");
    if ($(this).val() != '') {
      space.find(".out1").prop("disabled", false);
    }
    else {
      space.find(".out1, .in2, .out2").prop("disabled", true).val('');
    }
  }).change();
  
  $(document).on("change", ".out1", function() {
    let space = $(this).parents(".timeCard");
    if ($(this).val() != '') {
      space.find(".in2").prop("disabled", false);
    }
    else {
      space.find(".in2, .out2").prop("disabled", true).val('');
    }
  }).change();
  
  $(document).on("change", ".in2", function() {
    let space = $(this).parents(".timeCard");
    if ($(this).val() != '') {
      space.find(".out2").prop("disabled", false);
      space.find(".nextDay").prop("disabled", false);
    }
    else {
      space.find(".out2").prop("disabled", true).val('');
      space.find(".nextDay").prop("disabled", true).prop("checked", false);
    }
  }).change();
  
  $(document).on("change", ".nextDay", function() {
    $(this).is(":checked") ? null : $(this).parents(".timeCard").find(".out2").val('');
  }).change();
  
  $(document).on("click", ".submitTimeCard", function() {
    $(this).prop("disabled", true);
    if ($("#timeCard").find("#signature").val() === '') {
      $(this).parents(".timeCard").find(".message").text("Please Sign Before Submitting");
      setTimeout(() => { $(this).parents(".timeCard").find(".message").html(""); $(this).prop("disabled", false); }, 3500);
    } else {
      $(this).parents(".timeCard").find(".message").html('<p>Please review your time card to ensure that all entries are correct.</p><button type="button" class="confirmSubmit">Confirm</button>');
    }
  });
  
  $(document).on("click", ".confirmSubmit", function() {
    $(this).prop("disabled", true);
    let space = $(this).parents(".timeCard");
    let close = 'true';
    let endDate = $("#timeCard").find(".updateTime:last").attr("data-date");
    let driverID = $("#timeCard").find(".updateTime:last").attr("data-driverid");
    let sig = $(this).parents("#timeCard").find("#signature").val();
    let submitTimeCardAttempt = ajax_template("POST", "./updateTimeCard.php", "html", { formKey:$("#formKey").val(), endDate:endDate, driverID:driverID, close:close, sig:sig })
    .done((result) => {
      if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
      if (result.search("error") !== -1) {
        $(this).parents(".message").html(result);
        $(this).prop("disabled", false);
      } else {
        let refreshTimeCardAttempt = ajax_template("POST", "./refreshTimeCard.php", "html", { formKey:$("#formKey").val() } )
        .done((result1) => {
          if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
          space.find(".message").html(result)
          setTimeout(() => {
            $("#timeCard").html(result1);
            scrollTo(0,0);
          }, 5000);
        })
        .fail((jqXHR, status, error) => {
          space.find(".message").html('<p class="center">' + error + '</p>');
        });
      }
    })
    .fail((jqXHR, status, error) => {
      $(this).parents(".message").html('<p class="center">' + error + '</p>');
    });
  });
  
  $(document).on("click", ".signTimeCard", function() {
    let button = $(this);
    button.prop("disabled", true);
    setTimeout(function() { button.prop("disabled", false); }, 3000);
    var target = $(this).parents("table").find(".signature-pad");
    if (target.hasClass("sigField")) {
      target.removeClass("sigField").addClass("field").attr("id", "signature-pad").html('<button style="float:left;" type="button" data-action="clear">Clear</button><button style="float:right;" type="button" data-action="save">OK</button><canvas id="sig" class="wide" style="border:1px solid white;"></canvas>');
      var canvas = document.getElementById("sig");
      var signaturePad = new SignaturePad(canvas);
      var wrapper = document.getElementById("signature-pad"),
      clearButton = wrapper.querySelector("[data-action='clear']"),
      saveButton = wrapper.querySelector("[data-action='save']"),
      canvas = wrapper.querySelector("canvas"),
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
      var ratio =  Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);
    }
    //Resize the canvas so that signature-pad sees the whole area
    resizeCanvas();

    saveButton.addEventListener("click", function (event) {
      if (signaturePad.isEmpty()) {
        target.parents(".timeCard").find(".message").text("Signature Cleared");
        $("#signature").val("");
        setTimeout(() => { target.parents(".timeCard").find(".message").text(""); }, 3000);
        target.removeClass("field").addClass("sigField").html('').attr("id", "");
      } else {
        $("#signature").val(signaturePad.toDataURL());
        target.removeClass("field").addClass("sigField").html('').attr("id", "");
      }
    });
    
    clearButton.addEventListener("click", function(event) {
      signaturePad.clear();
    });
    //The canvas is holding the page background color. Calling the clear function fixes this.
    signaturePad.clear();
  });
  
  $(document).on("click", "#timeCard .currentTC, #timeCard .TCMGR", function(e) {
    e.preventDefault();
    if ($(this).hasClass("TCMGR")) {
      $("#timeCard>.timeCardContainer").addClass("hide");
      $("#timeCard #reviewContainer").removeClass("hide");
    }
    else {
      $("#timeCard>.timeCardContainer").removeClass("hide");
      $("#timeCard #reviewContainer").addClass("hide");
    }
  });
  
  $(document).on("change", "#driverName", function() {
    let index = $(this).val();
    $(this).parent("td").next("td").children().each(function() {
      if ($(this).prop("id") === index) {
        $(this).show();
        $("#submitTimeCardReview").prop("disabled", $(this).tagName() !== "SELECT");
      } else {
        $(this).hide();
      }
    });
  });
  
  $(document).on("click", "#clearTimeCardReview", function() {
    $("#timeCardReviewResult").html("");
  });
  
  $(document).on("click", "#submitTimeCardReview", function() {
    let formKey = $("#formKey").val();
    let request = "currentTC";
    let driverID = $("#driverName").val();
    let date;
    $(this).parent("td").prev("td").children().each(function() {
      if ($(this).is(":visible")) date = $(this).val();
    });
    let timeCardManagerAttempt = ajax_template("POST", "./timeCardManager.php", "html", { formKey:formKey, driverID:driverID, request:request, date:date })
    .done(function(result) {
      if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
      $("#timeCardReviewResult").html(result);
    })
    .fail(function(jqXHR, status, error) {
      $("#employeeID").after('<p class="center">' + error + '</p>');
    });
  });
  
  $(document).on("click", "#reopenTimeCard", function() {
    let formKey = $("#formKey").val();
    let driverID = $(".updateTime:first").attr("data-driverid");
    let endDate = $(this).parents("#timeCardReviewResult").find(".updateTime:last").attr("data-date");
    let reopenAttempt = ajax_template("POST", "./updateTimeCard.php", "html", { formKey:formKey, driverID:driverID, reopen:'reopen', endDate:endDate })
    .done(result => {
      if (result.indexOf("Session Error") !== -1) return $("#confirmLogin").removeClass("hide");
      $(this).after('<p>' + result + '</p>');
      setTimeout(() => { $(this).parent("td").html(""); }, 3000);
    })
    .fail((jqXHR, status, error) => {
      $(this).after('<p class="ajaxError">' + error + '</p>');
      setTimeout(() => { $(".ajaxError").remove(); }, 3000);
    });
  });
});