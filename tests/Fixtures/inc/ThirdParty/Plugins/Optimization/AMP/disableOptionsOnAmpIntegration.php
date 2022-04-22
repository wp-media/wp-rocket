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
	'testShouldDisableOptionForAmpExceptImageSrcSetAndThemeSupportTransitional'      => [
		'config'  => [
			'amp_options' => [ 'theme_support' => 'transitional' ],
		],
		'expected' => [
			'bailout' => false,
		],
	],
	'testShouldDisableOptionForAmpExceptImageSrcSetAndThemeSupportReader'      => [
		'config'  => [
			'amp_options' => [ 'theme_support' => 'reader' ],
		],
		'expected' => [
			'bailout' => false,
		],
	],
];
