<?php

return [
	'RUCSSDisableShouldEnable' => [
		'config' => [
			'rucss_enable' => false
		],
		'expected' => false
	],
	'RUCSSEnableShouldDisable' => [
		'config' => [
			'rucss_enable' => true
		],
		'expected' => true
	],
];
