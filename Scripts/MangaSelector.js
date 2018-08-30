var x = document.getElementById("pageselect");
    for (var i = 0; i <= pages.length - 1; i++) {
        var option = document.createElement("option");
        option.text = i + 1;
        x.add(option);
    };
$( document ).ready(function() {
    fitWindow();
});

function fitWindow() {
	/*var iframe = $('#reader', parent.document.body);
    iframe.height(document.body.scrollHeight + 135);
    console.log('good');*/
}