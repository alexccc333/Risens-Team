var x = document.getElementById("pageselect");
    for (var i = 0; i <= pages.length - 1; i++) {
        var option = document.createElement("option");
        option.text = i + 1;
        x.add(option);
    };