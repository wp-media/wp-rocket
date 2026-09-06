<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// Nothing of the add-on's to say: the plugin's own line goes through untouched.
	'shouldSayNothingWhileTheModuleIsDelivering'      => [
		'config'   => [],
		'expected' => [
			'contains'     => [ 'the plugin says nothing about this add-on' ],
			'not_contains' => [ 'Status:' ],
		],
	],
	'shouldSayNothingWhileTheSwitchIsOffAndTheFileIsClean' => [
		'config'   => [
			'option'     => 0,
			'enabled'    => false,
			'directives' => false,
		],
		'expected' => [
			'contains'     => [ 'the plugin says nothing about this add-on' ],
			'not_contains' => [ 'Status:' ],
		],
	],
		// Switched off and the directives are still in the file: the write that should have taken
		// them out was refused, and the server is answering for a switch that is off.
	'shouldSayItIsStillDeliveringAfterARefusedRemoval' => [
		'config'   => [
			'option'     => 0,
			'enabled'    => false,
			'directives' => true,
		],
		'expected' => [
			'contains'     => [ 'Status: still delivering.' ],
			'not_contains' => [ 'Status: waiting.' ],
		],
	],
		// A configuration the module cannot reproduce: the reason is what the tab is for.
	'shouldStandByWithTheReasonWhenTheConfigurationIsRefused' => [
		'config'   => [
			'enabled'    => false,
			'directives' => false,
			'reason'     => 'Caching for mobile devices is disabled.',
		],
		'expected' => [
			'contains'     => [ 'Status: standing by.', 'Caching for mobile devices is disabled.', 'WP Rocket is delivering the cache itself.' ],
			'not_contains' => [ 'Status: waiting.' ],
		],
	],
		// On, supported, and nothing in the file: the write has not happened yet.
	'shouldWaitWhileTheRulesHaveNotReachedTheFile'    => [
		'config'   => [
			'directives' => false,
		],
		'expected' => [
			'contains'     => [ 'Status: waiting.', 'Check that the file is writable.' ],
			'not_contains' => [ 'standing by' ],
		],
	],
		// The same state on a site that has told the plugin not to write the file at all: nothing is
		// wrong with the file, so the line must not send anyone to check its permissions.
	'shouldNotBlameTheFileWhereThisSiteForbidsWritingIt' => [
		'config'   => [
			'directives'      => false,
			'writing_refused' => true,
		],
		'expected' => [
			'contains'     => [ 'Status: standing by.', 'does not let WP Rocket write the .htaccess file' ],
			'not_contains' => [ 'Check that the file is writable.' ],
		],
	],
		// Delivering, but not to logged-in visitors: the narrowing is said rather than left to be
		// discovered.
	'shouldNameTheLoggedInNarrowingWhileDelivering'   => [
		'config'   => [
			'cache_logged_user' => 1,
			'serves_logged_in'  => false,
		],
		'expected' => [
			'contains'     => [ 'Status: delivering.', 'Logged-in visitors are served by WP Rocket itself' ],
			'not_contains' => [ 'standing by' ],
		],
	],
	'shouldSayNothingWhereLoggedInVisitorsAreServedToo' => [
		'config'   => [
			'cache_logged_user' => 1,
			'serves_logged_in'  => true,
		],
		'expected' => [
			'contains'     => [ 'the plugin says nothing about this add-on' ],
			'not_contains' => [ 'Status:' ],
		],
	],
];
