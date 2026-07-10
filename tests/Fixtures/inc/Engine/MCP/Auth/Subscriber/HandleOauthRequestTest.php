<?php

return [
	'testShouldDispatchToAuthorizeEndpoint'         => [
		'config' => [
			'endpoint' => 'authorize',
			'mock'     => 'authorize_endpoint',
		],
	],
	'testShouldDispatchToAuthorizeCallbackEndpoint' => [
		'config' => [
			'endpoint' => 'authorize-callback',
			'mock'     => 'authorize_callback',
		],
	],
	'testShouldDispatchToConsentEndpoint'           => [
		'config' => [
			'endpoint' => 'consent',
			'mock'     => 'consent_endpoint',
		],
	],
	'testShouldDispatchToRevokeEndpoint'            => [
		'config' => [
			'endpoint' => 'revoke',
			'mock'     => 'revoke_endpoint',
		],
	],
	'testShouldDispatchToTokenEndpoint'             => [
		'config' => [
			'endpoint' => 'token',
			'mock'     => 'token_endpoint',
		],
	],
];
