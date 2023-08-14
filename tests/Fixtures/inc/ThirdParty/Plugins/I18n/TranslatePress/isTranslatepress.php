<?php
return [
    'shouldReturnSameWhenEmptyLanguages' => [
		'config' => [
			'languages' => [],
		],
		'identifier' => '',
		'expected' => '',
    ],
    'shouldReturnTranslatePressWhenNotEmptyLanguages' => [
        'config' => [
              'languages' => [
				'fr',
				'us',
			  ],
        ],
		'identifier' => '',
        'expected' => 'translatepress',
    ],
];
