<?php

return [
	'shouldAddByocdnAndRocketcdnToEmptyFields'    => [
		'config'   => [],
		'expected' => [ 'byocdn', 'rocketcdn' ],
	],
	'shouldAppendByocdnAndRocketcdnToExistingFields' => [
		'config'   => [ 'cdn_type' ],
		'expected' => [ 'cdn_type', 'byocdn', 'rocketcdn' ],
	],
];
