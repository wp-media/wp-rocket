<?php

return [
	'shouldReturnTrueWhenPluginActiveWithNoCredentials' => [
		'config'   => [ 'plugin_active' => true ],
		'expected' => true,
	],
	'shouldReturnFalseWhenPluginInactive'               => [
		'config'   => [ 'plugin_active' => false ],
		'expected' => false,
	],
];
