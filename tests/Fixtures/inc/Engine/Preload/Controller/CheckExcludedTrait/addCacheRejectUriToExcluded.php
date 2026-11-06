<?php
return [
	'shouldAddRegex' => [
		'configs' => [
			'regexes' => [

			],
			'option_excluded_urls' => ['uri'],
			'excluded_urls' => 'uri',
		],
		'expected' => [
			'uri',
		]
	],
	'shouldReturn' => [
		'configs' => [
			'regexes' => [

			],
			'option_excluded_urls' => [],
			'excluded_urls' => '',
		],
		'expected' => [
		]
	]
];
