<?php

use WP_Rocket\Engine\Fleet\Route;

return [
	// A read demands the read scope. A grant minted for reading must not be
	// enough to change anything.
	'testShouldDemandReadForAGet'                    => [
		'config'   => [
			'method'        => 'GET',
			'authorization' => 'Bearer read-command',
			'consent'       => 'read-grant',
			'body'          => '',
		],
		'expected' => [ 'scope' => Route::SCOPE_READ ],
	],

	// A write demands the write scope.
	'testShouldDemandWriteForAPatch'                 => [
		'config'   => [
			'method'        => 'PATCH',
			'authorization' => 'Bearer write-command',
			'consent'       => 'write-grant',
			'body'          => '{"option_name":"cache_logged_user","option_value":1}',
		],
		'expected' => [ 'scope' => Route::SCOPE_WRITE ],
	],

	// Anything that is not a GET is treated as a write. The route registers
	// only GET and PATCH, but the scope must not be decided by an allowlist
	// that a future method could be added outside of.
	'testShouldDemandWriteForAnythingThatIsNotAGet'  => [
		'config'   => [
			'method'        => 'POST',
			'authorization' => 'Bearer command',
			'consent'       => 'grant',
			'body'          => '{}',
		],
		'expected' => [ 'scope' => Route::SCOPE_WRITE ],
	],

	// A lowercase method still reads as a read. `get` reaching the write branch
	// would demand a scope the caller has, so it would not fail closed — it
	// would quietly demand more than the request needs and refuse a legitimate
	// read from a read-only licence.
	'testShouldTreatALowercaseMethodAsTheSameMethod' => [
		'config'   => [
			'method'        => 'get',
			'authorization' => 'Bearer command',
			'consent'       => 'grant',
			'body'          => '',
		],
		'expected' => [ 'scope' => Route::SCOPE_READ ],
	],

	// The command is verified over the raw body. The digest covers the bytes
	// that travelled, so re-encoding parsed parameters would produce different
	// ones and every write would be refused.
	'testShouldVerifyTheCommandOverTheRawBody'       => [
		'config'   => [
			'method'        => 'PATCH',
			'authorization' => 'Bearer command',
			'consent'       => 'grant',
			'body'          => '{"options":[{"option_name":"cache_logged_user","option_value":0}]}',
		],
		'expected' => [ 'scope' => Route::SCOPE_WRITE ],
	],
];
