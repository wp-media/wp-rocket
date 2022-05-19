<?php

return [
	'RUCSSEnableShouldDisable' => [
		'config' => [
			'rucss_enable' => true
		],
		'expected' => true
	],
	'RUCSSDisableShouldEnable' => [
		'config' => [
			'rucss_enable' => false
		],
		'expected' => false
	],
];
