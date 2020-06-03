<?php

return [
	'testShouldAddWPR' => [
		'excluded_inline' => [],
		'expected_inline' => [ 'wprRemoveCPCSS' ],
	],
	'testShouldAddWPRNotEmptyArray' => [
		'excluded_inline' => [ 'excluded' ],
		'expected_inline' => [  'excluded', 'wprRemoveCPCSS' ],
	],
	'testShouldAddWPREvenIfDuplicate' => [
		'excluded_inline' => [ 'excluded', 'wprRemoveCPCSS' ],
		'expected_inline' => [  'excluded', 'wprRemoveCPCSS', 'wprRemoveCPCSS' ],
	]
];
