<?php

return [
	'testShouldDoNothingWhenVersionAbove36' => [
		'versions' => [
			'new' => '3.7',
			'old' => '3.6.1',
		],
		'update' => false,
	],
	'testShouldUpdateOptionWhenVersionUnder36' => [
		'versions' => [
			'new' => '3.6',
			'old' => '3.5.5',
		],
		'update' => true,
	],
];