$(document).ready(function() {
  $("#testButton").click(function( e ) {
    e.preventDefault();
    let postData = {};
    $("#testQuery").find("input").each(function() {
      if ($(this).attr("type") === "checkbox") {
        postData[$(this).attr("class")] = ($(this).is(":checked")) ? 1 : 0;
      } else {
        if (!postData.hasOwnProperty($(this).attr("class"))) {
          postData[$(this).attr("class")] = [];
        }
        postData[$(this).attr("class")].push($(this).val());
      }
    });
    postData.formKey = $("#formKey").val();
    console.log(postData);
    let attempt = ajax_template("POST", "test.php", "html", postData)
    .done(result => {
      console.log(result);
    })
    .fail((jqXHR, status, error) => {
      console.error(error);
    });
  });
});