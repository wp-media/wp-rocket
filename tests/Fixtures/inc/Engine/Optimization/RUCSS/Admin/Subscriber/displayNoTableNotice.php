<?php
return [
	'onTableShouldDoNothing' => [
		'config' => [
			'exists' => true,
			'rucss' => true,
		],
		'expected' => ''
	],
	'onNoTableShouldDisplayNotice' => [
		'config' => [
			'exists' => true,
			'rucss' => false,
		],
		'expected' => ''
	]
];
