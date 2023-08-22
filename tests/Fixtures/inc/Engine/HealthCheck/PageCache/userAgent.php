<?php

return [
	'null' => [
		'request_uri' => null,
		'user_agent_default' => 'WordPress',
		'user_agent_expected' => 'WordPress',
	],
	'default' => [
		'request_uri' => '/wp-json/wp-site-health/v1/tests/https-status?_locale=user',
		'user_agent_default' => 'WordPress',
		'user_agent_expected' => 'WordPress',
	],
	'plugin' => [
		'request_uri' => '/wp-json/wp-site-health/v1/tests/page-cache?_locale=user',
		'user_agent_default' => 'WP Rocket',
		'user_agent_expected' => 'WP Rocket',
	],
];
