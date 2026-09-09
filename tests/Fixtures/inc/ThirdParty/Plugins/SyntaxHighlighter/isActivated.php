<?php

return [
	'shouldReturnFalseWhenSyntaxHighlighterNotPresent' => [
		'config'   => [ 'define_syntaxhighlighter' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenSyntaxHighlighterPresent'     => [
		'config'   => [ 'define_syntaxhighlighter' => true ],
		'expected' => true,
	],
];
