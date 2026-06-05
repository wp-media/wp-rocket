<?php

return [
	'shouldReturnTrueWhenRocketcdnOptionIsZero'  => [
		'config'   => [ 'rocketcdn' => 0 ],
		'expected' => true,
	],
	'shouldReturnTrueWhenRocketcdnOptionIsFalse' => [
		'config'   => [ 'rocketcdn' => false ],
		'expected' => true,
	],
	'shouldReturnFalseWhenRocketcdnOptionIsOne'  => [
		'config'   => [ 'rocketcdn' => 1 ],
		'expected' => false,
	],
	'shouldReturnFalseWhenRocketcdnOptionIsTrue' => [
		'config'   => [ 'rocketcdn' => true ],
		'expected' => false,
	],
];
