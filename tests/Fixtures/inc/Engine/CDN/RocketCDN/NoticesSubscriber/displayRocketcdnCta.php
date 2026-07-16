<?php

return [
	'testShouldNotGenerateWhenResellerAccount' => [
		'config'   => [
			'filter_result' => true,
			'is_reseller'   => true,
		],
		'expected' => false,
	],
	'testShouldNotGenerateWhenNotLiveSite'     => [
		'config'   => [
			'filter_result' => true,
			'is_reseller'   => false,
			'is_live_site'  => false,
		],
		'expected' => false,
	],
];
