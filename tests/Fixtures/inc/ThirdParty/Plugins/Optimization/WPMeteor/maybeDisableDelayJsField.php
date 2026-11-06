<?php

return [
	'shouldReturnDefaultFieldWhenWPMeteorDisabled' => [
		'config' => [
			'plugin_active' => false,
		],
		'field' => [],
		'expected' => [],
	],
	'shouldReturnUpdateFieldWhenWPMeteorActive' => [
		'config' => [
			'plugin_active' => true,
		],
		'field' => [],
		'expected' => [
			'container_class' => [
				'wpr-isDisabled',
			],
			'value' => 0,
			'input_attr' => [
				'disabled' => 1,
			],
			'helper' => 'Delay JS is currently activated in WP Meteor. If you want to use WP Rocket’s delay JS, disable WP Meteor',
		],
	],
];
