<?php

return [
	'testShouldReturnFalseWhenBufferTooSmall' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => ''
		],
		'expected' => [
			'buffer_results' => false,
			'error' => [
				'message' => 'Buffer content under 255 caracters.',
				'data' => [],
			],
		],
	],
	'testShouldReturnFalseWhenResponseNot200' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 400
		],
		'expected' => [
			'buffer_results' => false,
			'error' => [
				'message' => 'Page is not a 200 HTTP response and cannot be cached.',
				'data' => [],
			],
		],
	],
	'testShouldReturnFalseWhenFailDoNotCache' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 200,
			'cache' => true,
		],
		'expected' => [
			'buffer_results' => false,
			'error' => [
				'message' => 'DONOTCACHEPAGE is defined. Page cannot be cached.',
				'data' => [],
			],
		],
	],
	'testShouldReturnFalseWhenFailIs404' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 200,
			'cache' => false,
			'404' => true,
		],
		'expected' => [
			'buffer_results' => false,
			'error' => [
				'message' => 'WP 404 page is excluded.',
				'data' => [],
			],
		],
	],
	'testShouldReturnFalseWhenFailIsSearch' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 200,
			'cache' => false,
			'404' => false,
			'search' => true,
		],
		'expected' => [
			'buffer_results' => false,
			'error' => [
				'message' => 'Search page is excluded.',
				'data' => [],
			],
		],
	],
	'testShouldReturnFalseWhenIsHtmlAndFailIsHTML' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 200,
			'cache' => false,
			'404' => false,
			'search' => false,
			'html' => [
				'is_html' => false,
				'is_feed' => false,
			],
		],
		'expected' => [
			'buffer_results' => false,
			'error' => [
				'message' => 'No closing </html> was found.',
				'data' => [],
			],
		],
	],
	'testShouldReturnTrueWhenIsHtmlAndSuccessIsHTML' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 200,
			'cache' => false,
			'404' => false,
			'search' => false,
			'html' => [
				'is_html' => true,
				'is_feed' => false,
			],
		],
		'expected' => [
			'buffer_results' => true,
			'error' => [
				'message' => '',
				'data' => [],
			],
		],
	],
	'testShouldReturnTrueWhenIsFeed' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 200,
			'cache' => false,
			'404' => false,
			'search' => false,
			'html' => [
				'is_html' => true,
				'is_feed' => true,
			],
		],
		'expected' => [
			'buffer_results' => true,
			'error' => [
				'message' => '',
				'data' => [],
			],
		],
	],
	'testShouldReturnTrueWhenIsAPI' => [
		'config' => [
			'rocket_exist' => true,
			'buffer' => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
			'response_code' => 200,
			'cache' => false,
			'404' => false,
			'search' => false,
			'html' => [
				'is_html' => true,
				'is_feed' => false,
				'is_rest' => true,
			],
		],
		'expected' => [
			'buffer_results' => true,
			'error' => [
				'message' => '',
				'data' => [],
			],
		],
	],
];
