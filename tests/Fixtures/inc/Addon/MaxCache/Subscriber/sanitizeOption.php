<?php
return [
	'shouldKeepADisabledAddOnOffWhereTheFormNeverShowedTheSwitch' => [
		'config'   => [
			'input'  => [ 'cache_ssl' => 1 ],
			'form'   => true,
			'stored' => 0,
		],
		'expected' => 0,
	],
	'shouldRecordACheckedBox'                                 => [
		'config'   => [
			'input'  => [ 'maxcache' => '1' ],
			'form'   => true,
			'stored' => null,
		],
		'expected' => 1,
	],
	'shouldRecordTheOptOutOfAnEnabledAddOn'                   => [
		// The switch was on the page and came back unchecked: that is the user turning it off.
		'config'   => [
			'input'  => [ 'cache_ssl' => 1 ],
			'form'   => true,
			'stored' => 1,
			'marker' => true,
		],
		'expected' => 0,
	],
	'shouldKeepAnEnabledAddOnOnWhereTheFormNeverShowedTheSwitch' => [
		// The same save from a page drawn before the switch existed — a stale tab, or an upgrade
		// between the render and the save. Nobody answered for it there, so an enabled add-on stays
		// enabled; reading that silence as a "no" would turn it off behind its owner, and the
		// automatic decision would not put it back.
		'config'   => [
			'input'  => [ 'cache_ssl' => 1 ],
			'form'   => true,
			'stored' => 1,
			'marker' => false,
		],
		'expected' => 1,
	],
	'shouldKeepWhatTheHiddenFieldCarriesWhereTheSwitchIsNotOffered' => [
		// No module here, so the plugin submits the stored answer as a hidden field.
		'config'   => [
			'input'  => [ 'maxcache' => 1 ],
			'form'   => true,
			'stored' => 1,
		],
		'expected' => 1,
	],
	'shouldRecordAZeroWrittenOnPurposeBeforeAnythingWasDecided' => [
		// An ability, WP-CLI or the add-on switch itself: update_option() runs this filter for all of
		// them, and dropping the value would leave the caller holding an answer this site does not
		// have — the ability reports the write as successful.
		'config'   => [
			'input'  => [ 'maxcache' => 0 ],
			'form'   => false,
			'stored' => null,
		],
		'expected' => 0,
	],
	'shouldRecordAZeroWrittenOnAHostThatHasTheModule'         => [
		// The same write where the switch is on offer, which is the only place an unchecked box can
		// mean "no".
		'config'   => [
			'input'   => [ 'maxcache' => 0 ],
			'form'    => false,
			'stored'  => null,
			'marker' => true,
		],
		'expected' => 0,
	],
	'shouldRecordAnUncheckedBoxFromAPageThatShowedTheSwitch'  => [
		// The same form, with the switch on the page: there the unchecked box is the answer, and not
		// writing it down would have the automatic decision undo the save a minute later.
		'config'   => [
			'input'   => [ 'cache_ssl' => 1 ],
			'form'    => true,
			'stored'  => null,
			'marker' => true,
		],
		'expected' => 0,
	],
		// A settings page opened before the switch existed, or a module installed between the render
		// and the save: the form carries neither the box nor the marker, so nobody was asked and the
		// automatic decision stays open. Without this the site would never switch itself on again.
	'shouldLeaveTheDecisionOpenForAFormDrawnBeforeTheSwitchExisted' => [
		'config'   => [
			'input'  => [ 'cache_ssl' => 1 ],
			'form'   => true,
			'stored' => null,
			'marker' => false,
		],
		'expected' => null,
	],
	'shouldLeaveTheDecisionOpenWhenThePluginWritesItsOwnRow'  => [
		// An upgrade or a licence refresh, not someone answering: recording a zero here would close
		// the automatic decision before the switch has ever been on a page.
		'config'   => [
			'input'  => [ 'cache_ssl' => 1 ],
			'form'   => false,
			'stored' => null,
		],
		'expected' => null,
	],
];
