<?php

return [
	'shouldReturnActiveDataWhenByocdnIsNotPausedAndCnamesConfigured'    => [
		'config'   => [
			'is_byocdn_paused' => false,
			'options'          => [ 'cdn_cnames' => [ 'https://cdn.example.com' ] ],
		],
		'expected' => [
			'is_active'      => true,
			'is_paused'      => false,
			'hide_pause_btn' => false,
			'cdn_type'       => 'byocdn',
			'class'          => '',
			'status_text'    => 'Your CDN is active on your website',
		],
	],
	'shouldReturnInactiveDataWhenNoCnamesConfigured'                     => [
		'config'   => [
			'is_byocdn_paused' => false,
			'options'          => [],
		],
		'expected' => [
			'is_active'      => false,
			'is_paused'      => false,
			'hide_pause_btn' => false,
			'cdn_type'       => 'byocdn',
			'class'          => '',
			'status_text'    => 'Your CDN is active on your website',
		],
	],
	'shouldReturnPausedDataWhenByocdnIsPaused'                          => [
		'config'   => [
			'is_byocdn_paused' => true,
			'options'          => [ 'cdn_cnames' => [ 'https://cdn.example.com' ] ],
		],
		'expected' => [
			'is_active'      => true,
			'is_paused'      => true,
			'hide_pause_btn' => false,
			'cdn_type'       => 'byocdn',
			'class'          => ' wpr-cdn-status--paused',
			'status_text'    => 'Your CDN is paused',
		],
	],
];
