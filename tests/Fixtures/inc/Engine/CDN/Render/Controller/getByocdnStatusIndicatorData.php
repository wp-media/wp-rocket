<?php

return [
	'shouldReturnActiveDataWhenByocdnIsNotPaused' => [
		'config'   => [
			'is_byocdn_paused' => false,
		],
		'expected' => [
			'is_active'     => true,
			'is_paused'     => false,
			'hide_pause_btn' => false,
			'cdn_type'      => 'byocdn',
			'class'         => '',
			'status_text'   => 'Your CDN is active on your website',
		],
	],
	'shouldReturnPausedDataWhenByocdnIsPaused'    => [
		'config'   => [
			'is_byocdn_paused' => true,
		],
		'expected' => [
			'is_active'     => true,
			'is_paused'     => true,
			'hide_pause_btn' => false,
			'cdn_type'      => 'byocdn',
			'class'         => ' wpr-cdn-status--paused',
			'status_text'   => 'Your CDN is paused',
		],
	],
];
