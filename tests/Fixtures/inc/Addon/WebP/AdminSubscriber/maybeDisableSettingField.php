<?php

return [
	'shouldReturnSameWhenWebpCacheIsEnabled' => [
		'filter' => false,
		'field' => [ 'foo' => 'bar' ],
		'expected' => [ 'foo' => 'bar' ],
	],
	'shouldReturnUpdatedWhenWebpCacheIsDisabled' => [
		'filter' => true,
		'field' => [
			'foo'             => 'bar',
			'input_attr'      => [
				'data-foo' => 'bar',
				'disabled' => 0,
			],
			'container_class' => [
				'oh-no',
			],
		],
		'expected' => [
			'foo'             => 'bar',
			'input_attr'      => [
				'data-foo' => 'bar',
				'disabled' => 1,
			],
			'container_class' => [
				'oh-no',
			],
		],
	],
];
