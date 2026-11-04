<?php

return [
	'test_data' => [
		'shouldKeepWhenNothing'                => [
			'config'   => [ 'cdn_state' => 'nothing' ],
			'expected' => false,
		],
		'shouldKeepWhenFree'                   => [
			'config'   => [ 'cdn_state' => 'rocketcdn_free' ],
			'expected' => false,
		],
		'shouldKeepWhenPaid'                   => [
			'config'   => [ 'cdn_state' => 'rocketcdn_paid' ],
			'expected' => false,
		],
		'shouldKeepWhenOtherCdnWithoutCnames'  => [
			'config'   => [
				'cdn_state'  => 'byocdn',
				'cdn_cnames' => [],
			],
			'expected' => false,
		],
		'shouldRemoveWhenOtherCdnWithCnames'   => [
			'config'   => [
				'cdn_state'  => 'byocdn',
				'cdn_cnames' => [ 'cdn.example.org' ],
			],
			'expected' => true,
		],
		'shouldRemoveWhenDisplayFilterReturnsFalse' => [
			'config'   => [
				'cdn_state' => 'nothing',
				'display'   => false,
			],
			'expected' => true,
		],
		'shouldKeepWhenDisplayFilterReturnsTrue' => [
			'config'   => [
				'cdn_state' => 'nothing',
				'display'   => true,
			],
			'expected' => false,
		],
	],
];
