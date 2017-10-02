var player = videojs('player', {
        controls: true,
        nativeControlsForTouch: false,
        width: 640,
        height: 360,
        fluid: true,
        plugins: {
          ass: {
            'src': [subLink],
            'delay': 0,
            'videoWidth': 1280,
            'videoHeight': 720
          },
          logobrand: {
				image: "player/logo.png",
				destination: "http://risens.team/"
		  },
		  hotkeys: {
		    volumeStep: 0.1,
            seekStep: 5,
            enableModifiersForNumbers: false
		  }
        }
      });

var myVideo = document.getElementById("player");
    if (myVideo.addEventListener) {
        myVideo.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        }, false);
    } 
	else {
        myVideo.attachEvent('oncontextmenu', function() {
            window.event.returnValue = false;
        });
    }
	
