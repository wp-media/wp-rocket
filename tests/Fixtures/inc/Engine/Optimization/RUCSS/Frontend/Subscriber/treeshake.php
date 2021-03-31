<?php

return [
	'test_data' => [
		// Testcases for Bailout/Short-circuit
		'shouldBailOutWhenNoOptimizeConstSet'  => [
			'config'       => [
				'no-optimize'     => true,
				'bypass'          => false,
				'rucss-enabled'   => true,
				'logged-in'       => true,
				'logged-in-cache' => true,
				'html'            => 'html content that should be left alone'
			],
			'api-response' => false,
			'expected'     => 'html content that should be left alone'
		],
		'shouldBailOutWhenRocketBypassEnabled' => [
			'config'       => [
				'no-optimize'     => false,
				'bypass'          => true,
				'rucss-enabled'   => true,
				'logged-in'       => true,
				'logged-in-cache' => true,
				'html'            => 'html content that should be left alone'
			],
			'api-response' => false,
			'expected'     => 'html content that should be left alone'
		],
		'shouldBailOutWhenRucssNotEnabled'     => [
			'config'       => [
				'no-optimize'     => false,
				'bypass'          => false,
				'rucss-enabled'   => false,
				'logged-in'       => true,
				'logged-in-cache' => true,
				'html'            => 'html content that should be left alone'
			],
			'api-response' => false,
			'expected'     => 'html content that should be left alone'
		],
		'shouldBailOutWhenUserLoggedIn'        => [
			'config'       => [
				'no-optimize'     => false,
				'bypass'          => false,
				'rucss-enabled'   => true,
				'logged-in'       => true,
				'logged-in-cache' => false,
				'html'            => 'html content that should be left alone'
			],
			'api-response' => false,
			'expected'     => 'html content that should be left alone'
		],
		'shouldBailOutWhenUserCached'          => [
			'config'       => [
				'no-optimize'     => false,
				'bypass'          => false,
				'rucss-enabled'   => true,
				'logged-in'       => false,
				'logged-in-cache' => true,
				'html'            => 'html content that should be left alone'
			],
			'api-response' => false,
			'expected'     => 'html content that should be left alone'
		],
		'shouldBailOutWhenApiErrors'           => [
			'config'       => [
				'no-optimize'     => false,
				'bypass'          => false,
				'rucss-enabled'   => true,
				'logged-in'       => false,
				'logged-in-cache' => false,
				'html'            => 'html content that should be left alone'
			],
			'api-response' => new WP_Error( 400, 'Not Available' ),
			'expected'     => 'html content that should be left alone'
		],
		// Testcase "Happy Path"
		'shouldRunRucssWhenExpected' => [
			'config'   => [
				'no-optimize'   => false,
				'bypass'        => false,
				'rucss-enabled' => true,
				'logged-in'     => false,
				'logged-in-cache' => false,
				'html'          => 'html content that should be processed'
			],
			'api-response' => [

			],
			'expected' => 'html content that should be processed'// 'This html has been successfully shaken!'
		],

	],
];
