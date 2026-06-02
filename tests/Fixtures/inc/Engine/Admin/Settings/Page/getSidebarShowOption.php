<?php

return [
	'shouldReturnOneWhenOptionIsOne'    => [
		'option_value' => 1,
		'expected'     => 1,
	],
	'shouldReturnZeroWhenOptionIsZero'  => [
		'option_value' => 0,
		'expected'     => 0,
	],
	'shouldReturnOneWhenOptionIsAbsent' => [
		'option_value' => 1, // Options_Data::get() returns the default (1) when absent.
		'expected'     => 1,
	],
];
