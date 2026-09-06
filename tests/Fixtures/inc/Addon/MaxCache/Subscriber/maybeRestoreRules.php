<?php
// Each case lists only what it changes; the rest comes from the defaults below.
$default = [
	'allowed'    => true,
	'option'     => 1,
	'available'  => true,
	'held'       => false,
	'enabled'    => false,
	'directives' => true,
	'writable'   => true,
	// Whether this site has asked the plugin not to write the file at all.
	'writing_refused' => false,
	// Whether this site has ever answered the switch, either way.
	'decided'         => true,
	// Whether the module's nginx build is installed here.
	'nginx'           => false,
	// Whether the web server reads the file itself.
	'apache'          => true,
	// Whether a write of the file takes our section with it. It does not where the plugin's own block
	// has lost its closing marker: there the writer refuses, because it cannot tell where it ends.
	'flush_clears'    => true,
];

// Every corrective write is followed by a hold, whether it took or not: the verdict behind it can
// differ between two admin requests — a proxy fronting one and not the other — and without a hold the
// file would flip on each; and confirming a write that did take costs the probes, a read of the file
// and the whole reason chain over again.
return [
		// The module has stopped being the one to serve, whether because it went away, because the
		// configuration turned into one it cannot handle, or because the switch was turned off and
		// that write was refused. Nothing else would ever notice.
	'shouldPutTheRulesBackOnceTheModuleStopsServing'  => [
		'config'   => $default,
		'expected' => [ 'flushed' => true, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// Nothing to put right, and the probes it took to find that out are not worth repeating on
		// every admin page: a change outside the settings is noticed a quarter of an hour later.
		// The block lost its closing marker, so the plugin's writer will not touch the file at all —
		// and our directives would go on being served for a switch that is off. Our own writer finds
		// its section by its own markers and can take it out where that one cannot.
	'shouldTakeItsSectionOutWhereTheWriterWillNotTouchTheFile' => [
		'config'   => array_merge( $default, [ 'flush_clears' => false ] ),
		'expected' => [ 'flushed' => true, 'removed' => false, 'stripped' => true, 'held' => true ],
	],
		// The switch has never been answered here, so nothing of ours has ever been written: the
		// probes and the read of the file would only confirm what the row already says.
	'shouldAskNothingOfTheFileWhereTheSwitchWasNeverAnswered' => [
		'config'   => array_merge( $default, [ 'decided' => false ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// Off Apache the plugin has no serving rules in that file to put back — the web server never
		// reads it. Our own section goes out on its own, and behind NGINX the block that carried it
		// goes too: it was written for this add-on's sake, and nothing else there reads it.
	'shouldTakeItsSectionAndThenTheBlockOutBehindNginx' => [
		'config'   => array_merge( $default, [ 'nginx' => true, 'apache' => false ] ),
		// One change of that file, so the daemon is woken once: the write announces itself.
		'expected' => [ 'flushed' => true, 'removed' => true, 'stripped' => true, 'held' => true, 'told' => 1 ],
	
	],
		// The same server with the module taken off it: the mode is no longer 'nginx', and the file is
		// still read by nobody. This is the case the corrective check exists for.
	'shouldTakeItsSectionOutOffApacheWithTheModuleGone' => [
		'config'   => array_merge( $default, [ 'nginx' => false, 'apache' => false, 'available' => false ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => true, 'held' => true ],
	],
	'shouldLeaveTheFileAloneWhileTheModuleServes'     => [
		'config'   => array_merge( $default, [ 'enabled' => true ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// The switch is on and nothing of ours is in the file — a hosting panel rewrote it. What the
		// file needs is our section back, not the block taken away, even though the block is there.
	'shouldWriteTheSectionBackRatherThanRemoveTheBlock' => [
		'config'   => array_merge( $default, [ 'enabled' => true, 'directives' => false ] ),
		'expected' => [ 'flushed' => true, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// Nothing of ours in the file: the rules are already the plugin's own.
	'shouldLeaveTheFileAloneWithNothingOfItsOwnInIt'  => [
		'config'   => array_merge( $default, [ 'directives' => false ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// The module was taken off the host: the switch is still on, and this is the case nothing
		// else would ever notice.
	'shouldPutTheRulesBackAfterTheModuleLeftTheHost'  => [
		'config'   => array_merge( $default, [ 'available' => false ] ),
		'expected' => [ 'flushed' => true, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// Turned off by a write that was refused, so the module is still the one answering.
	'shouldTakeTheRulesOutAfterTheSwitchWentOff'      => [
		'config'   => array_merge( $default, [ 'option' => 0 ] ),
		'expected' => [ 'flushed' => true, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// No module, the switch off and a clean file: nothing here to put right, and the probes
		// that say so are not worth repeating on every admin request.
	'shouldStopProbingWithNothingLeftToPutRight'      => [
		'config'   => array_merge( $default, [ 'option' => 0, 'available' => false, 'directives' => false ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// The same host with our directives still in the file: the switch went off, that write was
		// refused, and the module was taken off afterwards. The rules have to come back.
	'shouldPutTheRulesBackWithBothTheModuleAndTheSwitchGone' => [
		'config'   => array_merge( $default, [ 'option' => 0, 'available' => false ] ),
		'expected' => [ 'flushed' => true, 'removed' => false, 'stripped' => false, 'held' => true ],
	],
		// The module is gone and the plugin may not write the file: the section answers nothing now,
		// but taking it out costs nothing either, and it is ours to take out.
	'shouldTakeItsSectionOutEvenWithTheModuleGone'    => [
		'config'   => array_merge( $default, [ 'available' => false, 'writing_refused' => true ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => true, 'held' => true ],
	],
		// admin_init also fires for admin-ajax.php, where the visitor may be nobody.
	'shouldNotProbeTheHostForSomeoneWhoCannotDecide'  => [
		'config'   => array_merge( $default, [ 'allowed' => false ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => false, 'held' => false ],
	],
		// Our own writer is asked too, and cannot write the file either.
	'shouldStopTryingWhenTheFileCannotBeWritten'      => [
		'config'   => array_merge( $default, [ 'writable' => false ] ),
		'expected' => [ 'flushed' => true, 'removed' => false, 'stripped' => true, 'held' => true ],
	],
		// A site that does not let the plugin write the file takes no rewrite of it, so only our own
		// section is taken out, and the rest of the block is left alone.
	'shouldTakeOnlyItsOwnSectionOutWhereWritingIsRefused' => [
		'config'   => array_merge( $default, [ 'writing_refused' => true ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => true, 'held' => true ],
	],
		// The same, and the file cannot be written either: worth trying again later, not on every
		// admin page.
	'shouldStopTryingWhenItsOwnSectionCannotBeTakenOut' => [
		'config'   => array_merge( $default, [ 'writing_refused' => true, 'writable' => false ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => true, 'held' => true ],
	],
	'shouldStayQuietWhileTheLastRefusalStands'        => [
		'config'   => array_merge( $default, [ 'held' => true ] ),
		'expected' => [ 'flushed' => false, 'removed' => false, 'stripped' => false, 'held' => false ],
	],
];
