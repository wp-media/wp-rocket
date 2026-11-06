<?php

$original = '<img width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" loading="lazy" srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" sizes="(max-width: 300px) 85vw, 300px" />';

$updated = '<img width="300" height="300" src="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" loading="lazy" data-lazy-srcset="http://example.org/wp-content/uploads/2019/05/beanie-300x300.jpg 300w, http://example.org/wp-content/uploads/2019/05/beanie-100x100.jpg 100w, http://example.org/wp-content/uploads/2019/05/beanie-600x600.jpg 600w, http://example.org/wp-content/uploads/2019/05/beanie-150x150.jpg 150w, http://example.org/wp-content/uploads/2019/05/beanie-768x768.jpg 768w, http://example.org/wp-content/uploads/2019/05/beanie.jpg 801w" data-lazy-sizes="(max-width: 300px) 85vw, 300px" />';

return [
	'shouldReturnSameWhenNative' => [
		'config' => [
			'is_native' => true,
		],
		'html' => $original,
		'expected' => $original,
	],
	'shouldReturnUpdatedWhenNotNative' => [
		'config' => [
			'is_native' => false,
		],
		'html' => $original,
		'expected' => $updated,
	],
];
