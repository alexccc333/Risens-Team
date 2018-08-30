<?php
$head = array();

$head[] = new Extension('link', 
			'http://vjs.zencdn.net/6.4.0/video-js.css', 
			'stylesheet');

$head[] = new Extension('link', 
			'player/libjass.css', 
			'stylesheet');

$head[] = new Extension('script', 
			'http://vjs.zencdn.net/6.4.0/video.js');

$head[] = new Extension('script', 
			'player/libjass.js');

$head[] = new Extension('script', 
			'https://code.jquery.com/jquery-3.2.1.min.js',
			'',
			array(
				'integrity' => 'sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=',
				'crossorigin' => 'anonymous'
			));

$head[] = new Extension('link', 
			'player/videojs-ass.css', 
			'stylesheet');

$head[] = new Extension('script', 
			'player/videojs-ass.js');

$head[] = new Extension('link',
			'player/videojs-logobrand.css',
			'stylesheet');

$head[] = new Extension('script',
			'player/videojs-logobrand.js');

/*$head[] = new Extension('script',
			'player/videojs-preroll.js');*/

$head[] = new Extension('script',
			'player/videojs-nuevo.js');

$head[] = new Extension('link',
			'player/videojs-audiotracks.css',
			'stylesheet');

$head[] = new Extension('script', 
			'player/videojs-audiotracks.js');

$head[] = new Extension('script',
			'player/videojs-hotkeys.js');