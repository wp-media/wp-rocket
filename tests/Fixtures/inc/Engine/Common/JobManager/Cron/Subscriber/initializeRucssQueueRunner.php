<?php

return [
	'shouldNotInitWhenRucssIsDisabledAndRocketInsightsIsActive' => [
		'config' => [
			'has_rucss_factory' => true,
			'rucss_is_allowed'  => false,
		],
	],
	'shouldNotInitWhenRucssFactoryIsAbsent' => [
		'config' => [
			'has_rucss_factory' => false,
			'rucss_is_allowed'  => false,
		],
	],
];
