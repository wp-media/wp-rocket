<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'original' => [],
		'list' => (object) [
			'js_move_after_combine' => [
				'map_fusion_map_',
			],
		],
		'expected' => [
			'map_fusion_map_',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'original' => [
			'ec:addProduct',
		],
		'list' => (object) [
			'js_move_after_combine' => [
				'map_fusion_map_',
			],
		],
		'expected' => [
			'ec:addProduct',
			'map_fusion_map_',
		],
	],
];
