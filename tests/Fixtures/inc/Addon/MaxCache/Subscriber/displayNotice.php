<?php
// Each case lists only what it changes; the rest comes from the defaults below.
$default = [
	'allowed'    => true,
	'pending'    => true,
	'dismissed'  => false,
	'option'     => 1,
	'directives' => true,
	'enabled'    => true,
];

return [
	'shouldAnnounceWhereTheServerIsDelivering'        => [
		'config'   => $default,
		'expected' => true,
	],
	'shouldSayNothingWithoutTheCapability'            => [
		'config'   => array_merge( $default, [ 'allowed' => false ] ),
		'expected' => false,
	],
	'shouldSayNothingWhereNothingIsPending'           => [
		'config'   => array_merge( $default, [ 'pending' => false ] ),
		'expected' => false,
	],
	'shouldSayNothingOnceItWasDismissed'              => [
		'config'   => array_merge( $default, [ 'dismissed' => true ] ),
		'expected' => false,
	],
	'shouldSayNothingWithTheSwitchOff'                => [
		'config'   => array_merge( $default, [ 'option' => 0 ] ),
		'expected' => false,
	],
	'shouldSayNothingWithNothingOfOursInTheFile'      => [
		'config'   => array_merge( $default, [ 'directives' => false ] ),
		'expected' => false,
	],
		// The directives are in the file, but the configuration has turned into one the module cannot
		// reproduce and taking them out was refused: the add-on is standing by, and announcing delivery
		// would be dismissed for good on the way out.
	'shouldSayNothingWhileTheAddOnIsStandingBy'       => [
		'config'   => array_merge( $default, [ 'enabled' => false ] ),
		'expected' => false,
	],
];
