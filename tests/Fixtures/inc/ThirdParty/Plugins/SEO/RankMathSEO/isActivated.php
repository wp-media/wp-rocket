<?php

return [
	'shouldReturnFalseWhenRankMathSEONotPresent' => [
		'config'   => [ 'rank_math_file' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenRankMathSEOPresent'     => [
		'config'   => [ 'rank_math_file' => '/path/to/rank-math.php' ],
		'expected' => true,
	],
];
