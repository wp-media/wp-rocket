<?php

return [
	'testShouldReturnFalseWhenValueIsFalse' => [
		'config' => [
			'value' => false,
			'tag_name' => 'img',
			'lazyload' => true,
			'lazyload_filter' => true,
			'lazyload_iframes' => false,
			'lazyload_iframes_filter' => true,
		],
		'expected' => false,
	],
	'testShouldReturnDefaultWhenLazyloadImagesDisabled' => [
		'config' => [
			'value' => true,
			'tag_name' => 'img',
			'lazyload' => false,
			'lazyload_filter' => true,
			'lazyload_iframes' => false,
			'lazyload_iframes_filter' => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenLazyloadImagesEnabled' => [
		'config' => [
			'value' => true,
			'tag_name' => 'img',
			'lazyload' => true,
			'lazyload_filter' => true,
			'lazyload_iframes' => false,
			'lazyload_iframes_filter' => true,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenLazyloadImagesDisabledByFilter' => [
		'config' => [
			'value' => true,
			'tag_name' => 'img',
			'lazyload' => true,
			'lazyload_filter' => false,
			'lazyload_iframes' => false,
			'lazyload_iframes_filter' => true,
		],
		'expected' => true,
	],
	'testShouldReturnDefaultWhenLazyloadIframesDisabled' => [
		'config' => [
			'value' => true,
			'tag_name' => 'iframe',
			'lazyload' => false,
			'lazyload_filter' => true,
			'lazyload_iframes' => false,
			'lazyload_iframes_filter' => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenLazyloadIframesEnabled' => [
		'config' => [
			'value' => true,
			'tag_name' => 'iframe',
			'lazyload' => true,
			'lazyload_filter' => true,
			'lazyload_iframes' => true,
			'lazyload_iframes_filter' => true,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenLazyloadIframesDisabledByFilter' => [
		'config' => [
			'value' => true,
			'tag_name' => 'iframe',
			'lazyload' => true,
			'lazyload_filter' => false,
			'lazyload_iframes' => true,
			'lazyload_iframes_filter' => false,
		],
		'expected' => true,
	],
	'testShouldReturnDefaultWhenInvalidTag' => [
		'config' => [
			'value' => true,
			'tag_name' => 'div',
			'lazyload' => true,
			'lazyload_filter' => false,
			'lazyload_iframes' => true,
			'lazyload_iframes_filter' => false,
		],
		'expected' => true,
	],
];
