<?php

return [
	'shouldNotScheduleCronDueToMissingSettings' => [
		'input' => [
			'remove_unused_css' => false,
		]
	],
	'shouldScheduleCron' => [
		'input' => [
			'remove_unused_css' => true,
		]
	],
];
