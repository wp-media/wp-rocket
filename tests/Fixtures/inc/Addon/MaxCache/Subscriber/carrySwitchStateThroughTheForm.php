<?php
// Each case lists only what it changes; the rest comes from the defaults in the test.
return [
		// The switch is drawn, so the marker rides along and an unchecked box is an answer.
	'shouldCarryTheMarkerWhereTheSwitchIsDrawn'  => [
		'config'   => [],
		'expected' => [
			'carries'     => 'maxcache_offered',
			'not_carries' => 'maxcache',
		],
	],
		// The host keeps the add-on off its platform: the field is never registered, so the stored
		// value has to be carried through as itself or the next save would drop it.
	'shouldCarryTheValueWhereTheHostKeepsTheAddOnOff' => [
		'config'   => [ 'available' => false ],
		'expected' => [
			'carries'     => 'maxcache',
			'not_carries' => 'maxcache_offered',
		],
	],
		// The host hides the field through the other filter: same conclusion.
	'shouldCarryTheValueWhereTheFieldIsHidden'   => [
		'config'   => [ 'displayed' => false ],
		'expected' => [
			'carries'     => 'maxcache',
			'not_carries' => 'maxcache_offered',
		],
	],
		// The whole add-ons section is skipped while the licence is not valid.
	'shouldCarryTheValueWithoutAValidLicence'    => [
		'config'   => [ 'licensed' => false ],
		'expected' => [
			'carries'     => 'maxcache',
			'not_carries' => 'maxcache_offered',
		],
	],
];
