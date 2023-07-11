<?php
return [
	'shouldUsePrefixOnInvalidFilterPrefix' => [
		'config' => [
			'prefix' => 'WP Rocket Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
			'filter' => '',

		],
		'expected' => 'WP Rocket Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
	],
	'shouldUseFilterPrefixOnFilterPrefix' => [
		'config' => [
			'prefix' => 'WP Rocket Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
			'filter' => 'new_prefix',

		],
		'expected' => 'new_prefix'
	]
];
