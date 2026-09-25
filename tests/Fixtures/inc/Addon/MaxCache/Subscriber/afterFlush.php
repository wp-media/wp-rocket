<?php
// Each case lists only what it changes; the rest comes from the defaults below. This action fires
// after the write, and a settings save has dropped the earlier read before it: what the file holds
// here is what the write has just left behind, never what stood there before it.
$default = [
	'changed'    => true,
	'option'     => 1,
	'directives' => true,
	// Whether this site has ever answered the switch, either way.
	'decided'    => true,
];

return [
		// The file now holds our directives, and the daemon compiles it into its own configuration.
	'shouldTellTheDaemonAboutAWriteThatChangedTheFile' => [
		'config'   => $default,
		'expected' => [ 'told' => true, 'forgot' => true ],
	],
		// A flush that wrote nothing leaves the file as this request read it, so neither the daemon
		// nor the reader of that file has anything to be told. A change made by somebody else in the
		// meantime is caught by the stamp read_htaccess() keeps, not by this.
	'shouldKeepWhatWasReadWhenTheFlushWroteNothing' => [
		'config'   => array_merge( $default, [ 'changed' => false ] ),
		'expected' => [ 'told' => false, 'forgot' => false ],
	],
		// The write that took the directives out: the switch is off and nothing of ours is left in the
		// file, which is exactly when the daemon has to be told to stop serving by them. A flush this
		// add-on has nothing to do with looks the same from here, and is told the same: the daemon
		// compiles whatever the file now says, and only hosts running the module are told at all.
	'shouldTellTheDaemonWhereTheWriteLeftNothingOfOursBehind' => [
		'config'   => array_merge( $default, [ 'option' => 0, 'directives' => false ] ),
		'expected' => [ 'told' => true, 'forgot' => true ],
	],
		// A flush on a site that has never answered the switch: nothing of ours has ever been in that
		// file, so the daemon has nothing to recompile on our account and is not woken for it.
	'shouldSayNothingWhereTheSwitchWasNeverAnswered' => [
		'config'   => array_merge( $default, [ 'decided' => false ] ),
		'expected' => [ 'told' => false, 'forgot' => true ],
	],
];
