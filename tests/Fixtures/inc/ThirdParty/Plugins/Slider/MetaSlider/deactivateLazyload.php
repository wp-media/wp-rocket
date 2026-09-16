<?php

return [
	'shouldSetDataNoLazyOnSlide' => [
		'config'   => [
			'slide' => [ 'src' => 'https://example.org/slide.jpg' ],
		],
		'expected' => [
			'src'          => 'https://example.org/slide.jpg',
			'data-no-lazy' => 1,
		],
	],
];
