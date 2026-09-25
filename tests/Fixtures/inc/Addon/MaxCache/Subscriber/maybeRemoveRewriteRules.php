<?php
return [
	'shouldLeaveRulesUntouchedOnARefusal'       => [
		'config'   => [
			'enabled' => false,
			'nginx'   => false,
			'rules'   => "RewriteRule .* - [E=WPR:1]\n",
		],
		'expected' => "RewriteRule .* - [E=WPR:1]\n",
	],
	'shouldDropRulesOnApacheToo'                => [
		// Not kept behind an <IfModule !maxcache_module> guard: the server still reads and scans the
		// section on every request, which is the cost the module is there to remove.
		'config'   => [
			'enabled' => true,
			'nginx'   => false,
			'rules'   => "RewriteRule .* - [E=WPR:1]\n",
		],
		'expected' => '',
	],
	'shouldNotUndoAnEarlierRefusalToServeByUri' => [
		'config'   => [
			'enabled' => true,
			'nginx'   => false,
			'rules'   => false,
		],
		'expected' => false,
	],
		// The classic missing return. Casting it to a string here would hide the refusal from the
		// probe that reads this same chain to find out whether anyone objects to delivery by URI.
	'shouldPassOnARefusalThatCameBackAsNothing' => [
		'config'   => [
			'enabled' => true,
			'nginx'   => false,
			'rules'   => null,
		],
		'expected' => null,
	],
		// WP-CLI and cron on a site whose cache directory sits outside the install: the rules the
		// plugin writes there carry a filesystem path where a URL path belongs. Still the only rules
		// the file would have, and the module is not answering, so they are not this add-on's to take
		// away.
	'shouldKeepRulesTheModuleIsNotReplacing'    => [
		'config'   => [
			'enabled'           => false,
			'nginx'             => false,
			'rules_unbuildable' => true,
			'rules'             => "RewriteRule .* - [E=WPR:1]\n",
		],
		'expected' => "RewriteRule .* - [E=WPR:1]\n",
	],
	'shouldDropRulesNobodyReadsOnNginx'         => [
		'config'   => [
			'enabled' => true,
			'nginx'   => true,
			'rules'   => "RewriteRule .* - [E=WPR:1]\n",
		],
		'expected' => '',
	],
];
