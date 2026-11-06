<?php
return [
    'noFieldsShouldNotClean' => [
        'config' => [
              'old' => [

			  ],
			  'new' => [

			  ],
			  'should_clean' => false,
        ],
        'expected' => [

        ]
    ],
	'SameFieldsShouldNotClean' => [
		'config' => [
			'old' => [
				'urls' => [
					'directory_for_default_language' => false,
				]
			],
			'new' => [
				'urls' => [
					'directory_for_default_language' => false,
				]
			],
			'should_clean' => false,
		],
		'expected' => [
				'urls' => [
					'directory_for_default_language' => false,
					]
		]
	],
	'differentFieldsShouldClean' => [
		'config' => [
			'old' => [
				'urls' => [
					'directory_for_default_language' => false,
				]
			],
			'new' => [
				'urls' => [
					'directory_for_default_language' => true,
				]
			],
			'should_clean' => true,
		],
		'expected' => [
			'urls' => [
				'directory_for_default_language' => true,
			]
		]
	],

];
