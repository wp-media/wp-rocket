<?php
return [
	'RefererAndCapacityShouldCheckAndFail' => [
		'config' => [
			'action' => 'referer_test',
			'capacities' => '',
			'user_can' => false
		],
		'expected' => [
			'referer' => 'referer_test',
			'capacity' => 'capacity_test',
			'result' => false,
		]
	],
	'NoCapacityShouldSucceed' => [
		'config' => [
			'action' => 'referer_test',
			'capacities' => 'capacity_test',
			'user_can' => true
		],
		'expected' => [
			'referer' => 'referer_test',
			'capacity' => 'capacity_test',
			'result' => true,
		]
	]
];
