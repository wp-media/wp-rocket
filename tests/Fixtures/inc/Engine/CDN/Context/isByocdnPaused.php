<?php

return [
	'shouldReturnTrueWhenByocdnOptionIsZero'  => [
		'config'   => [ 'byocdn' => 0 ],
		'expected' => true,
	],
	'shouldReturnTrueWhenByocdnOptionIsFalse' => [
		'config'   => [ 'byocdn' => false ],
		'expected' => true,
	],
	'shouldReturnFalseWhenByocdnOptionIsOne'  => [
		'config'   => [ 'byocdn' => 1 ],
		'expected' => false,
	],
	'shouldReturnFalseWhenByocdnOptionIsTrue' => [
		'config'   => [ 'byocdn' => true ],
		'expected' => false,
	],
];
