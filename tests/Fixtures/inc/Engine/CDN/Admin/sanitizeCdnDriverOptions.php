<?php

return [
	'shouldReturnOneForBothWhenBothCheckboxesArePresent'  => [
		'config'   => [
			'input' => [ 'byocdn' => '1', 'rocketcdn' => '1' ],
		],
		'expected' => [
			'byocdn'   => 1,
			'rocketcdn' => 1,
		],
	],
	'shouldReturnZeroForBothWhenNeitherCheckboxIsPresent' => [
		'config'   => [
			'input' => [],
		],
		'expected' => [
			'byocdn'   => 0,
			'rocketcdn' => 0,
		],
	],
	'shouldReturnOneForByocdnOnlyWhenOnlyByocdnIsPresent' => [
		'config'   => [
			'input' => [ 'byocdn' => '1' ],
		],
		'expected' => [
			'byocdn'   => 1,
			'rocketcdn' => 0,
		],
	],
];
