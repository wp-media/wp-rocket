<?php
$ours = "# BEGIN MAx Cache\n<IfModule mod_mime.c>\nAddType text/html .html_gzip\n</IfModule>\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n";
$plugin_block = "# BEGIN WP Rocket\n<IfModule mod_expires.c>\nExpiresActive On\n</IfModule>\n";

return [
		// Everything the add-on did not write stays byte for byte, including the lines it puts
		// outside the module guard.
	'shouldTakeItsOwnSectionAndNothingElse'        => [
		'config'   => [
			'file' => "# other rules\n" . $plugin_block . $ours . "# END WP Rocket\n",
		],
		'expected' => [
			'removed' => true,
			'file'    => "# other rules\n" . $plugin_block . "# END WP Rocket\n",
		],
	],
		// A blank line the site owner put after our section is theirs, not ours: one line ending goes
		// with the closing marker, the rest of the file stays as it was.
	'shouldKeepTheBlankLineThatFollowsItsSection' => [
		'config'   => [
			'file' => $plugin_block . $ours . "\n# hand-maintained rule\n# END WP Rocket\n",
		],
		'expected' => [
			'removed' => true,
			'file'    => $plugin_block . "\n# hand-maintained rule\n# END WP Rocket\n",
		],
	],
		// The hosting panel writes a section of the same name in a block of its own. Ours is the one
		// inside the plugin's block, and the panel's is none of our business.
	'shouldLeaveTheSameSectionWrittenByThePanel'   => [
		'config'   => [
			'file' => "# BEGIN AccelerateWP\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END AccelerateWP\n" . $plugin_block . "# END WP Rocket\n",
		],
		'expected' => [
			'removed' => true,
			'file'    => "# BEGIN AccelerateWP\n# BEGIN MAx Cache\n<IfModule maxcache_module>\nMaxCache On\n</IfModule>\n# END MAx Cache\n# END AccelerateWP\n" . $plugin_block . "# END WP Rocket\n",
		],
	],
		// Reached exactly when the plugin refuses to write the file, so an unwritable one is a state
		// to expect rather than a surprise, and it must not leave a PHP warning on the page.
	'shouldSayNoWhereTheFileCannotBeWritten'       => [
		'config'   => [
			'file'     => "# BEGIN WP Rocket\n" . $ours . "# END WP Rocket\n",
			'writable' => false,
		],
		'expected' => [
			'removed' => false,
			'file'    => "# BEGIN WP Rocket\n" . $ours . "# END WP Rocket\n",
		],
	],
		// Nothing of ours in the file is the state this is asked to reach.
	'shouldReportTheFileAlreadyFreeOfItsSection'   => [
		'config'   => [
			'file' => $plugin_block . "# END WP Rocket\n",
		],
		'expected' => [
			'removed' => true,
			'file'    => $plugin_block . "# END WP Rocket\n",
		],
	],
];
