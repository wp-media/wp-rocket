<?php

return [
	'testShouldReturnTrueForFreeMonitorSku' => [
		'sku'      => 'perf-monitor-free',
		'expected' => true,
	],
	'testShouldReturnFalseForPaidSku' => [
		'sku'      => 'perf-monitor-starter',
		'expected' => false,
	],
	'testShouldReturnFalseForAnotherPaidSku' => [
		'sku'      => 'perf-monitor-pro',
		'expected' => false,
	],
	'testShouldReturnFalseForEmptySku' => [
		'sku'      => '',
		'expected' => false,
	],
];
