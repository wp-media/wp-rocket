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
		'shouldRemoveOnOneCom'                 => [
			'config'   => [
				'cdn_state' => 'nothing',
				'brand'     => 'one.com',
			],
			'expected' => true,
		],
		'shouldKeepOnOtherGroupOneBrand'       => [
			'config'   => [
				'cdn_state' => 'nothing',
				'brand'     => 'proisp.no',
			],
			'expected' => false,
		],
	],
];
