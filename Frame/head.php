<?php
$head = array();

$head[] = new Extension('link', 
			'Styles/GetNewAnime.css', 
			'stylesheet');

$head[] = new Extension('link',
			'https://cdnjs.cloudflare.com/ajax/libs/video.js/5.5.3/video-js.min.css',
			'stylesheet');

$head[] = new Extension('script',
			'https://cdnjs.cloudflare.com/ajax/libs/video.js/5.5.3/video.min.js');

$head[] = new Extension('script', 'https://code.jquery.com/jquery-3.2.1.min.js');

$head[] = new Extension('link', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', 'stylesheet');

$head[] = new Extension('link', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css', 'stylesheet');

$head[] = new Extension('script', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js');