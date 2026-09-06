<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// Something hooked on the filters and probes this method runs wrote to the row while it was
		// deciding. Writing back the copy it started with would silently drop that key.
	'shouldKeepWhatWasWrittenToTheRowWhileItWasDeciding' => [
		'config'   => [ 'row_changes' => [ 'cache_ssl' => 1 ] ],
		'expected' => [ 'writes' => true, 'hold' => null ],
	],
		// A host that hides the switch has taken the decision away from its customers: switching on
		// here would leave them an add-on the settings page never renders and cannot turn off. Both
		// filters the settings page reads count — this case hides it through the display one.
	'shouldLeaveTheDecisionAloneWhereTheHostHidesTheSwitch' => [
		'config'   => [
			'offered' => false,
		],
		'expected' => [
			'writes' => false,
			'hold'   => 1800,
		],
	],
		// The other filter, through which a host keeps the add-on off its platform entirely: the field
		// is never registered, so a switch written here could never be turned back off.
	'shouldLeaveTheDecisionAloneWhereTheHostKeepsTheAddOnOff' => [
		'config'   => [
			'available_filter' => false,
		],
		'expected' => [
			'writes' => false,
			'hold'   => 1800,
		]
	],
		// A site that has told the plugin never to write .htaccess: the directives cannot reach the
		// file, so this would record a decision nobody made about a delivery that cannot happen.
	'shouldLeaveTheDecisionAloneWhereTheFileIsNeverWritten' => [
		'config'   => [
			'stored'          => [ 'cache_ssl' => 1 ],
			'writing_refused' => true,
		],
		'expected' => [
			'writes' => false,
			'hold'   => 1800,
		],
	],
	'shouldDoNothingWhenTheDecisionIsAlreadyRecorded'      => [
		'config'   => [
			'stored' => [ 'maxcache' => 0 ],
		],
		'expected' => [
			'writes' => false,
			'hold'   => null,
		]
	],
	'shouldDoNothingWhileTheHoldIsInPlace'                 => [
		'config'   => [
			'held' => true,
		],
		'expected' => [
			'writes' => false,
			'hold'   => null,
		]
	],
	'shouldHoldBrieflyWhenTheModuleIsAbsent'              => [
		'config'   => [
			'stored'        => [ 'cache_ssl' => 1 ],
			'available'     => false,
			'can_switch_on' => false,
		],
		'expected' => [
			'writes' => false,
			'hold'   => 1800,
		]
	],
	'shouldHoldWhenTheConfigurationRefuses'               => [
		// The dearest of the three answers, and the one that can change without anybody saving
		// settings — a plugin that vetoed serving by URI is deactivated. Held the same half hour.
		'config'   => [
			'stored'        => [ 'cache_ssl' => 1 ],
			'can_switch_on' => false,
		],
		'expected' => [
			'writes' => false,
			'hold'   => 1800,
		]
	],
		// Behind NGINX the same answer waits on a socket the daemon may never answer, so it is held
		// ten times longer than a refusal that only reads options.
	'shouldHoldOnNginxWhenTheDaemonDoesNotAnswer'         => [
		'config'   => [
			'stored'        => [ 'cache_ssl' => 1 ],
			'can_switch_on' => false,
			'nginx'         => true,
		],
		'expected' => [
			'writes' => false,
			'hold'   => 1800,
		]
	],
		// The request that activated the plugin: rocket_first_install_options() has not written the
		// row yet, so the refusal says nothing about this site and holding it would keep a fresh
		// install from switching itself on for half a day.
	'shouldNotHoldOnTheActivationRequest'                 => [
		'config'   => [
			'stored'        => [],
			'can_switch_on' => false,
		],
		'expected' => [
			'writes' => false,
			'hold'   => null,
		]
	],
		// No registered sanitize callback means nothing would drop the key again, and a stored
		// "ignore" would silence the "Settings saved." notice for every later write.
	'shouldWriteNoIgnoreKeyWithoutTheCallbackThatDropsIt' => [
		'config'   => [
			'stored'    => [],
			'sanitiser' => false,
		],
		'expected' => [
			'writes' => true,
			'hold'   => null,
		]
	],
		// The write returns nothing, and a filter on the row or a failed query can leave it as it was.
		// Announcing then would have this request answer 1 to everything and queue a notice for a week
		// while the row says the decision is still open. Held as well: without it every admin request
		// would probe, write, re-sanitize the row and rewrite .htaccess again for the same answer.
	'shouldAnnounceNothingWhereTheRowDidNotTakeTheWrite'  => [
		'config'   => [
			'stored'      => [ 'cache_ssl' => 1 ],
			'write_lands' => false,
		],
		'expected' => [
			'writes' => true,
			'hold'   => 1800,
			'told'   => false,
		]
	],
	'shouldSwitchOnAndQueueTheNotice'                      => [
		'config'   => [
			'stored' => [ 'cache_ssl' => 1 ],
		],
		'expected' => [
			'writes' => true,
			'hold'   => null,
		]
	],
		// admin_init also fires for admin-ajax.php, including for visitors who are not logged in.
	'shouldNotDecideForSomeoneWhoMayNotManageOptions'      => [
		'config'   => [
			'may_manage' => false,
		],
		'expected' => [
			'writes' => false,
			'hold'   => null,
		]
	],
	'shouldNotDecideWhileTheSettingsFormIsBeingSaved'  => [
		// admin_init runs before options.php applies the form, and that form was drawn before this
		// decision existed, so the unchecked box would overwrite it a moment later.
		'config'   => [
			'form' => true,
		],
		'expected' => [
			'writes' => false,
			'hold'   => null,
		],
	],
];
