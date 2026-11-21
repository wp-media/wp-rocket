<?php

return [
	'expands the invalid_license_data sentinel to the translated message' => [
		'errors'       => [ 'invalid_license_data' ],
		'contains'     => [
			'The provided license data are not valid.',
			'contact support',
		],
		'not_contains' => [ 'invalid_license_data' ],
	],
	'echoes a server-returned string unchanged' => [
		'errors'       => [ 'License validation failed. This user account is blocked.' ],
		'contains'     => [ 'License validation failed. This user account is blocked.' ],
		'not_contains' => [ 'The provided license data are not valid.' ],
	],
];
