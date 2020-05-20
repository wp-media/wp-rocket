<?php

return [
	'testShouldDeleteOptionAndSendUserTokenDeletedJSONSuccessWhenValueIsNull' => [
		'config' => [
			'rocketcdn_user_token' => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
			'post_value'           => null,
		],
		'expected' => [
			'rocketcdn_user_token' => false,
			'response' => (object) [
				'success' => true,
				'data'    => 'user_token_deleted',
			]
		]
	],

	'testShouldSendInvalidTokenLengthJsonErrorWhenValueLengthIsNot40' => [
		'config' => [
			'rocketcdn_user_token' => null,
			'post_value'           => 'not40charslong',
		],
		'expected' => [
			'rocketcdn_user_token' => false,
			'response' => (object) [
				'success' => false,
				'data'    => 'invalid_token_length',
			]
		]
	],

	'testShouldUpdateOptionAndSendUserTokenSavedJsonSuccessWhenValueIsValid' => [
		'config' => [
			'rocketcdn_user_token' => false,
			'post_value'           => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
		],
		'expected' => [
			'rocketcdn_user_token' => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
			'response' => (object) [
				'success' => true,
				'data'    => 'user_token_saved',
			]
		]
	]
];
