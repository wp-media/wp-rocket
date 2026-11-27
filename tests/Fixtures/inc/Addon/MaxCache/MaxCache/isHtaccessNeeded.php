<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// The file is there and cannot be read — root-owned, hardened by a panel. Answered as "nothing of
		// ours", so a plugin that cannot touch the file does not block its own deactivation over it.
	'shouldAskNothingOfAFileItCannotRead' => [
		'config'   => [ 'removing' => true, 'file' => '', 'unreadable' => true, 'writable' => false ],
		'expected' => false,
	],
	'shouldRefuseARemovalWhereThePathCannotBeResolved' => [
		// WP-CLI on an install in a subdirectory: get_home_path() answers "/", and flush_rocket_htaccess()
		// would touch a stray /.htaccess while the site's own file keeps its directives.
		'config'   => [
			'removing'  => true,
			'home_path' => '/',
			'file'      => '',
		],
		'expected' => false,
	],
	'shouldNotOpenTheFileOnASiteThatNeverAnsweredTheSwitch' => [
		// Every flush on every host that is not Apache reaches this: the ones with no module and no
		// answer stored have nothing of ours in the file and are not worth a read of it.
		'config'   => [
			'mode'    => 'none',
			'option'  => 0,
			'decided' => false,
			'file'    => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n# END MAx Cache\n# END WP Rocket\n",
		],
		'expected' => false,
	],
	'shouldAgreeWithAnyoneWhoAlreadySaidYes'                      => [
		'config'   => [
			'needed' => true,
			'mode'   => 'apache',
		],
		'expected' => true,
	],
		// The same site from WP-CLI or cron: the reasons this add-on refuses for are read from the request,
		// so a write from there could undo what a refusal on a real request has just done.
	'shouldNotWriteTheBlockFromARequestThatCannotJudgeTheServer' => [
		'config'   => [
			'option'          => 1,
			'server_software' => null,
		],
		'expected' => false,
	],
	'shouldNeedTheFileWhileTheAddOnIsOnBehindNginx'               => [
		'config'   => [
			'option' => 1,
		],
		'expected' => true,
	],
		// The switch is off, and this next write is what removes the directives and notifies the
		// daemon. Answering false here would leave the module serving after the user turned it off.
	'shouldNeedTheFileToTakeItsOwnDirectivesOut'                  => [
		'config'   => [
			'file' => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END WP Rocket\n",
		],
		'expected' => true,
	],
	'shouldStopOnceTheDirectivesAreGone'                          => [
		'config'   => [
			'file' => "# BEGIN WP Rocket\n# END WP Rocket\n",
		],
		'expected' => false,
	],
		// There the web server reads the file itself, so the plugin writes it without being asked.
	'shouldLeaveApacheAlone'                                      => [
		'config'   => [
			'mode'   => 'apache',
			'option' => 1,
		],
		'expected' => false,
	],
		// Deactivation on a host where the module is gone but our directives are not.
	'shouldClaimARemovingFlushWhileItsOwnDirectivesAreThere'      => [
		'config'   => [
			'removing' => true,
			'mode'     => 'none',
			'file'     => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END WP Rocket\n",
		],
		'expected' => true,
	],
	'shouldLeaveThePluginsOwnBlockToThePluginWhenRemoving' => [
		// Nothing of ours in it: claiming it would let an unwritable file block deactivation on a
		// site this add-on never wrote to.
		'config'   => [
			'removing' => true,
			'mode'     => 'apache',
			'file'     => "# BEGIN WP Rocket\n# END WP Rocket\n",
		],
		'expected' => false,
	],
		// Nothing of ours in the file and no module on the host: claiming it would have an
		// unwritable .htaccess block deactivation on a site this add-on never touched.
	'shouldLeaveALeftoverBlockAloneWhereThereIsNoModule'          => [
		'config'   => [
			'removing' => true,
			'mode'     => 'none',
			'file'     => "# BEGIN WP Rocket\n# END WP Rocket\n",
		],
		'expected' => false,
	],
	'shouldNotClaimARemovingFlushWithNothingToRemove'             => [
		'config'   => [
			'removing' => true,
			'mode'     => 'none',
		],
		'expected' => false,
	],
		// Mode is "none" now, and only this add-on knows that block is its own.
	'shouldTakeItsDirectivesOutAfterTheModuleIsGone'              => [
		'config'   => [
			'mode' => 'none',
			'file' => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END WP Rocket\n",
		],
		'expected' => true,
	],
		// Nothing to take out. Saying yes here would have deactivation refuse to proceed over a
		// file that is not there.
	'shouldNotClaimARemovingFlushWhenTheAddOnIsOnButTheFileIsGone'=> [
		'config'   => [
			'removing' => true,
			'option'   => 1,
		],
		'expected' => false,
	],
		// The hosting panel writes its own <IfModule maxcache_module> section. Behind nginx the plugin writes
		// no block of its own, so what is left after our directives went out is ours to take away.
	'shouldTakeItsOwnLeftoverBlockAwayBehindNginx'                => [
		'config'   => [
			'removing' => true,
			'file'     => "# BEGIN WP Rocket\n<IfModule mod_rewrite.c>\nRewriteEngine On\n</IfModule>\n# END WP Rocket\n",
		],
		'expected' => true,
	],
		// The same leftover on a file that cannot be written. Removing it is not worth stopping
		// deactivation over: behind nginx those rules serve nothing.
	'shouldNotStopDeactivationOverALeftoverItCannotRemove'        => [
		'config'   => [
			'removing' => true,
			'writable' => false,
			'file'     => "# BEGIN WP Rocket\n<IfModule mod_rewrite.c>\nRewriteEngine On\n</IfModule>\n# END WP Rocket\n",
		],
		'expected' => false,
	],
		// Directives of ours are a different matter: they keep the server serving after the plugin
		// is gone, so an unwritable file is worth stopping for.
	'shouldStopDeactivationWhileItsOwnDirectivesCannotBeRemoved'  => [
		'config'   => [
			'removing' => true,
			'writable' => false,
			'file'     => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END WP Rocket\n",
		],
		'expected' => true,
	],
		// WP-CLI and cron: get_rocket_htaccess_mod_rewrite() writes no rules from there when the cache
		// directory sits outside the install, so a write would drop the rules a real request put in.
	'shouldNotAskForAWriteItCannotFill'                           => [
		'config'   => [
			'option'        => 1,
			'document_root' => null,
			'cache_path'    => '/var/cache/wp-rocket/',
		],
		'expected' => false,
	],
		// Unless there is something of ours to take out: leaving the module answering for an add-on
		// that is off is worse than a block that carries no serving rules until the next write.
	'shouldLeaveItsDirectivesForARequestThatCanWriteTheRulesThatReplaceThem' => [
		// The write that takes our section out puts the plugin's own rules in, and from WP-CLI those carry a
		// filesystem path where a URL path belongs. Our section waits for a request with a document root.
		'config'   => [
			'file'          => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END WP Rocket\n",
			'document_root' => null,
			'cache_path'    => '/var/cache/wp-rocket/',
		],
		'expected' => false,
	],
	'shouldStillTakeItsDirectivesOutFromTheCommandLine'           => [
		// The same call on the ordinary layout, where the rules that replace ours are built from
		// ABSPATH and need no document root.
		'config'   => [
			'file'          => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END WP Rocket\n",
			'document_root' => null,
		],
		'expected' => true,
	],
	'shouldReadABlockLeftWithoutItsClosingMarker'                 => [
		// A write cut short, or a hand-edit: what is left of the block runs to the end of the file, and
		// asking strpos() for an offset past the end is a fatal error on PHP 8.
		'config'   => [
			'file' => "# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n",
		],
		'expected' => true,
	],
	'shouldFindItsSectionInASecondBlockOfThePlugins'              => [
		// A panel that regenerates the file can leave two of the plugin's blocks in it; looking at the first
		// alone would report our section gone while the server goes on serving from it.
		'config'   => [
			'file' => "# BEGIN WP Rocket\n# END WP Rocket\n# BEGIN WP Rocket\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END WP Rocket\n",
		],
		'expected' => true,
	],
	'shouldIgnoreTheSameTagWrittenOutsideThePluginsBlock'         => [
		'config'   => [
			'file' => "# BEGIN AccelerateWP\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END AccelerateWP\n# BEGIN WP Rocket\n# END WP Rocket\n",
		],
		'expected' => false,
	],
];
