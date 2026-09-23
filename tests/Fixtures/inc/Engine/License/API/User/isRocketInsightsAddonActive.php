<?php

return [
	'testShouldReturnFalseForFreeMonitorSku' => [
		'sku'      => 'perf-monitor-free',
		'expected' => false,
	],
	'testShouldReturnTrueForPaidSku' => [
		'sku'      => 'perf-monitor-starter',
		'expected' => true,
	],
	'testShouldReturnTrueForAnotherPaidSku' => [
		'sku'      => 'perf-monitor-pro',
		'expected' => true,
	],
	'testShouldReturnTrueForEmptySku' => [
		'sku'      => '',
		'expected' => true,
	],
];
