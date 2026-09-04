<?php

return [
	'shouldReturnTrueWhenAdminAndPerformanceBasenameActive' => [
		'config'   => [
			'is_admin'         => true,
			'active_basenames' => [ 'hummingbird-performance/wp-hummingbird.php' ],
		],
		'expected' => true,
	],
	'shouldReturnTrueWhenAdminAndLegacyBasenameActive'      => [
		'config'   => [
			'is_admin'         => true,
			'active_basenames' => [ 'wp-hummingbird/wp-hummingbird.php' ],
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenAdminButNeitherBasenameActive'    => [
		'config'   => [
			'is_admin'         => true,
			'active_basenames' => [],
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenPluginActiveButNotAdmin'          => [
		'config'   => [
			'is_admin'         => false,
			'active_basenames' => [ 'hummingbird-performance/wp-hummingbird.php' ],
		],
		'expected' => false,
	],
];
