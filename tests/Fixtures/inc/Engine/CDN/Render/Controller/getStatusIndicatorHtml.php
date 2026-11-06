<?php

return [
	// When no_status_indicator is true the AJAX endpoint must return an empty
	// string so the DOM element is removed, matching the full-page-load templates
	// (rocketcdn-paid.php:66, rocketcdn-free.php:79) which skip rendering it.
	'returnsEmptyStringWhenNoStatusIndicatorIsSet' => [
		'config'   => [
			'pages_count'    => 0,
			'is_paid'        => true,
			'no_status_indicator' => true,
		],
		'expected' => '',
	],
];
