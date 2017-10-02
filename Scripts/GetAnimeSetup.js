var player = videojs('mv', {
        controls: true,
        nativeControlsForTouch: false,
        width: 640,
        height: 360,
        fluid: true,
        poster: poster
    });
		
var video = videojs('mv').ready(function(){
	var player = this;
	player.on('ended', function() {
		startPlayer();
        var elem = document.getElementById('mv');
		elem.parentNode.removeChild(elem);
	});
});

document.getElementById("selectors").opacity = 1;

var x = document.getElementById("episodes");
for (var i = 0; i <= eps.length - 1; i++) {
	var option = document.createElement("option");
	option.text = eps[i];
	x.add(option);
};

var selectedEpisode = 0;
var subs = true;
var isThereSubtitles = [];
var isThereDub = [];

for (var i = 0; i <= eps.length - 1; i++) {
	if (viS[i]) isThereSubtitles.push(1);
	else isThereSubtitles.push(0);
	if (viD[i]) isThereDub.push(1);
	else isThereDub.push(0);
};

function startPlayer() {
	changeEpisode(selectedEpisode);
	document.getElementById("selectors").style.display="inline-block";
	document.getElementById("player").style.display="inline-block";
}

function changeDubSub(e) {
    var type = "";
	if (e == "Субтитры") {
		type = "sub";
		subs = true;
	}
	if (e == "Озвучка") {
		type = "dub";
		subs = false;
	}
	document.getElementById("player").src="http://risensteam.ru/player.php?id="+ids[selectedEpisode]+"&type="+type;
}

function changeEpisode (e) {
	selectedEpisode = e;
	var d = document.getElementById("subdub");
	removeOptions(d);
	if (isThereSubtitles[e] == 1) {
		var optionSub = document.createElement("option");
		optionSub.text = "Субтитры";
		d.add(optionSub);
		if (subs == true) {
			optionSub.selected=true;
		}
	}
	if (isThereDub[e] == 1) {
		var optionDub = document.createElement("option");
		optionDub.text = "Озвучка";
		d.add(optionDub);
		if (subs == false) {
			optionDub.selected=true;
		}
	}

    if (subs == true) {
        if (isThereSubtitles[e] == 1) {
			changeDubSub("Субтитры");
		}
		else if (isThereDub[e] == 1) {
			changeDubSub("Озвучка");
		}
    }
    else {
        if (isThereDub[e] == 1) {
			changeDubSub("Озвучка");
		}
		else if (isThereSubtitles[e] == 1) {
			changeDubSub("Субтитры");
		}
    }	
}
	
function removeOptions(selectbox) {
	var i;
	for(i = selectbox.options.length - 1 ; i >= 0 ; i--) {
	    selectbox.remove(i);
	}
}
