<?php

$head = array();

$head[] = new Extension('link', 
			'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
			'stylesheet',
			array(
				'integrity' => 'sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u',
				'crossorigin' => 'anonymous'
			));

$head[] = new Extension('script',
			'https://code.jquery.com/jquery-3.1.1.min.js');

$head[] = new Extension('script',
			'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
			array(
				'integrity' => 'sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa',
				'crossorigin' => 'anonymous',
			));

$head[] = new Extension('link',
		'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/css/bootstrap-select.min.css',
		'stylesheet');

$head[] = new Extension('script',
		'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.2/js/bootstrap-select.min.js');