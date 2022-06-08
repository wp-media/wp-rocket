<?php
return [
	'widgetActiveShouldDoNothing' => [
		'config' => [
			'is_eu_widget_present' => 'test',
			'is_active' => true,
		]
	],
	'widgetDisableButEUWidgetPresentShouldDoNothing' => [
		'config' => [
			'is_eu_widget_present' => 'test',
			'is_active' => false,
		]
	],
	'widgetDisableAndEuWidgetNotPresentShouldAddFilters' => [
		'config' => [
			'is_eu_widget_present' => '',
			'is_active' => false,
		]
	]
];
