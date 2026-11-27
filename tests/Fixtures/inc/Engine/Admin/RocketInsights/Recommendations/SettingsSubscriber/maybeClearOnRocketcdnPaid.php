<?php

return [
	'test_data' => [
		'shouldClearWhenNothingToPaid'       => [
			'config'   => [
				'old_options' => [ 'cdn_state' => 'nothing' ],
				'new_options' => [ 'cdn_state' => 'rocketcdn_paid' ],
			],
			'expected' => true,
		],
		'shouldClearWhenFreeToPaid'          => [
			'config'   => [
				'old_options' => [ 'cdn_state' => 'rocketcdn_free' ],
				'new_options' => [ 'cdn_state' => 'rocketcdn_paid' ],
			],
			'expected' => true,
		],
		'shouldClearWhenOldStateIsMissing'   => [
			'config'   => [
				'old_options' => [],
				'new_options' => [ 'cdn_state' => 'rocketcdn_paid' ],
			],
			'expected' => true,
		],
		'shouldNotClearWhenAlreadyPaid'      => [
			'config'   => [
				'old_options' => [ 'cdn_state' => 'rocketcdn_paid' ],
				'new_options' => [ 'cdn_state' => 'rocketcdn_paid' ],
			],
			'expected' => false,
		],
		'shouldNotClearWhenPaidToFree'       => [
			'config'   => [
				'old_options' => [ 'cdn_state' => 'rocketcdn_paid' ],
				'new_options' => [ 'cdn_state' => 'rocketcdn_free' ],
			],
			'expected' => false,
		],
		'shouldNotClearWhenNothingToFree'    => [
			'config'   => [
				'old_options' => [ 'cdn_state' => 'nothing' ],
				'new_options' => [ 'cdn_state' => 'rocketcdn_free' ],
			],
			'expected' => false,
		],
		'shouldNotClearWhenNothingToByocdn'  => [
			'config'   => [
				'old_options' => [ 'cdn_state' => 'nothing' ],
				'new_options' => [ 'cdn_state' => 'byocdn' ],
			],
			'expected' => false,
		],
		'shouldNotClearWhenStateUnchanged'   => [
			'config'   => [
				'old_options' => [ 'cdn_state' => 'nothing', 'minify_css' => 0 ],
				'new_options' => [ 'cdn_state' => 'nothing', 'minify_css' => 1 ],
			],
			'expected' => false,
		],
	],
];
