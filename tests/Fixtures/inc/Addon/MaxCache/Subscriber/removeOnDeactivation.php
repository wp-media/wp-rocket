<?php
// Each case lists only what it changes; the rest comes from the defaults below.
$default = [
	'directives' => true,
	'writable'   => true,
];

return [
		// A block whose closing marker is gone is left alone by the plugin's own removal, since where it
		// ends cannot be told. Ours is bounded by our own markers, and it has to go: the server still serves.
	'shouldTakeItsOwnSectionOutWhereTheDirectivesAreStillThere' => [
		'config'   => $default,
		'expected' => [ 'stripped' => true, 'told' => true ],
	],
		// Nothing of ours in the file: the plugin's own removal did its work, or there was none to do.
	'shouldDoNothingWhereTheFileHoldsNothingOfIts'              => [
		'config'   => array_merge( $default, [ 'directives' => false ] ),
		'expected' => [ 'stripped' => false, 'told' => false ],
	],
		// The file cannot be written: nothing was taken out, so there is nothing to tell the daemon.
	'shouldTellTheDaemonNothingWhereTheWriteWasRefused'         => [
		'config'   => array_merge( $default, [ 'writable' => false ] ),
		'expected' => [ 'stripped' => true, 'told' => false ],
	],
];
