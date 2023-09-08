<?php
return [
    'shouldReturnSameWhenEmptyGlobal' => [
		'config' => [
			'trp_language' => '',
		],
		'current_language' => '',
		'expected' => '',
    ],
    'shouldReturnTRPLanguageWhenNotEmptyGlobal' => [
        'config' => [
              'trp_language' => 'FR',
        ],
		'current_language' => '',
        'expected' => 'FR',
    ],
];
