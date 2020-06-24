<?php

return [
	'testRunRocketFailedDueToWpeParam' => [
		'config' => [
			'wpe_param'           => false,
		],
		'expected' => false,
	],
	'testRunRocketFailedDueToPWPConst' => [
		'config' => [
			'wpe_param'           => true,
			'pwp_constant'        => false,
		],
		'expected' => false,
	],
	'testRunRocketFailedDueToAdmin'    => [
		'config' => [
			'wpe_param'           => true,
			'pwp_constant'        => true,
			'check_admin_referer' => false,
		],
		'expected' => false,
	],
	'testRunRocket'                    => [
		'config' => [
			'wpe_param'           => true,
			'pwp_constant'        => true,
			'check_admin_referer' => true,
		],
		'expected' => true,
	],
];
