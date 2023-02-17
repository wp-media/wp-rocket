<?php

return [
	'shouldReturnEmptyArrayWhenNoMatchingProperty' => [
		'list' => (object) [],
		'expected' => [],
	],
	'shouldReturnArrayWhenMatchingProperty' => [
		'list' => (object) [
			'js_move_after_combine' => [
				'map_fusion_map_',
			],
		],
		'expected' => [
			'map_fusion_map_',
		],
	],
];
