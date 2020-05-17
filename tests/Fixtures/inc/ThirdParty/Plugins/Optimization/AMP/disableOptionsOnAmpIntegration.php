<?php

return [
	'testShouldBailoutIfIsNotAmpEndpoint'      => [
		'config'  => [
			'amp_options'     => [ 'theme_support' => null ],
		],
		'expected' => [
			'bailout' => true,
		],
	],
	'testShouldDisableOptionForAmpExceptImageSrcSet'      => [
		'config'  => [
			'amp_options' => [ 'theme_support' => 'standard' ],
		],
		'expected' => [
			'bailout' => false,
		],
	],
	'testShouldDisableOptionForAmpExceptImageSrcSetAndThemeSupport'      => [
		'config'  => [
			'amp_options' => [ 'theme_support' => 'transitional' ],
		],
		'expected' => [
			'bailout' => false,
		],
	],
	'testShouldDisableOptionForAmpExceptImageSrcSetAndThemeSupport'      => [
		'config'  => [
			'amp_options' => [ 'theme_support' => 'reader' ],
		],
		'expected' => [
			'bailout' => false,
		],
	],
];
