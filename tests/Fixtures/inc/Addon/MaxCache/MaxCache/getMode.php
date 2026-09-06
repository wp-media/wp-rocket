<?php
// errno answered by the unix:// probe, per path. 2 = ENOENT, 13 = EACCES, 111 = ECONNREFUSED.
return [
	'shouldFindTheApacheModuleThroughPermissionDenied' => [
		// The normal shape on a server: the file is there, root owned, and the site is not root.
		'errno'    => [
			'/opt/cloudlinux/maxcache/.not-installed-probe' => 2,
			'/opt/cloudlinux/maxcache/.version'             => 13,
		],
		'expected' => 'apache',
	],
	'shouldFindTheNginxModule'                        => [
		'errno'    => [
			'/opt/cloudlinux/maxcache/.not-installed-probe' => 2,
			'/opt/cloudlinux/maxcache/.nginx-version'       => 13,
		],
		'expected' => 'nginx',
	],
	'shouldPreferNginxWhenBothAreInstalled'           => [
		'errno'    => [
			'/opt/cloudlinux/maxcache/.not-installed-probe' => 2,
			'/opt/cloudlinux/maxcache/.version'             => 13,
			'/opt/cloudlinux/maxcache/.nginx-version'       => 13,
		],
		'expected' => 'nginx',
	],
	'shouldFindNothingWhenTheDirectoryCannotBeSearched' => [
		// Every name inside answers the same way, including one that is never installed, so the
		// answers say nothing about which build is there. Both guesses would be harmful.
		'errno'    => [
			'/opt/cloudlinux/maxcache/.not-installed-probe' => 13,
			'/opt/cloudlinux/maxcache/.version'             => 13,
			'/opt/cloudlinux/maxcache/.nginx-version'       => 13,
			'/opt/cloudlinux/maxcache/notify.sock'          => 13,
		],
		'expected' => 'none',
	],
	'shouldFindNothingWhenNothingIsInstalled'         => [
		'errno'    => [],
		'expected' => 'none',
	],
];
