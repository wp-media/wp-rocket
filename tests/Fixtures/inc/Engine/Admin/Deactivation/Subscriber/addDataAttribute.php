<?php

return [
	'shouldReturnSameWhenNoDeactivate' => [
		'actions' => [],
		'expected' => [],
	],
	'shouldReturnUpdatedDeactivate' => [
		'actions' => [
			'deactivate' => '<a href="">',
		],
		'expected' => [
			'deactivate' => '<a data-micromodal-trigger="wpr-deactivation-modal" href="">',
		],
	],
];
