<?php

return [
	'testShoulDoNothingWhenDisabled' => [
		'config' => [
			'filter' => false,
		],
		'expected' => "",
	],
	'testShoulDoReturnArgument' => [
		'config' => [
			'filter' => true,
		],
		'expected' => "/?wpr_imagedimensions=1",
	],
];
