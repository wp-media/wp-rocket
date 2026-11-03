<?php

return [
	'the language was in the file name, so the files under the old names go' => [
		'settings'     => [ 'browser' => 1, 'force_lang' => 0 ],
		'clean_domain' => true,
	],
	'the address carried the language, so nothing cached moves' => [
		'settings'     => [ 'browser' => 1, 'force_lang' => 1 ],
		'clean_domain' => false,
	],
];
