<?php
// Each case lists only what it changes; the rest comes from the defaults below.
$default = [
	'directives' => true,
	'writable'   => true,
];

return [
		// The plugin's own removal could not run — a block whose closing marker is gone is left alone,
		// since where it ends cannot be told. What is ours is bounded by our own markers, so it can go
		// even then, and it has to: those directives keep the server serving for a plugin that is off.
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
