<?php

$referer = 'http%3A%2F%2Fexample.org%2Fwp-admin%2Foptions-general.php%3Fpage%3Dwprocket%23dashboard';
$disabled = [
	[
		'id'           => 'rocket_clean_saas',
		'title'        => 'Used CSS',
		'description'  => 'Clear the used CSS files.',
		'button_label' => 'Clear used CSS',
		'button_url'   => '',
		'disabled'     => true,
	],
];
$base    = [ 'rucss' => 1, 'environment' => 'production', 'valid_key' => true, 'caps' => [ 'rocket_remove_unused_css' => true, 'rocket_manage_options' => true ] ];

return [
	'testShouldAppendADisabledActionWhenRucssIsOff' => [
		'config'   => array_merge( $base, [ 'rucss' => 0 ] ),
		'expected' => $disabled,
	],
	'testShouldAppendADisabledActionOnLocalEnvironmentWhenRucssIsOff' => [
		'config'   => array_merge( $base, [ 'rucss' => 0, 'environment' => 'local' ] ),
		'expected' => $disabled,
	],
	'testShouldReturnUnchangedWithoutCapabilityWhenRucssIsOff' => [
		'config'   => array_merge( $base, [ 'rucss' => 0, 'caps' => [ 'rocket_remove_unused_css' => false, 'rocket_manage_options' => true ] ] ),
		'expected' => [],
	],
	'testShouldAppendADisabledActionOnLocalEnvironment' => [
		'config'   => array_merge( $base, [ 'environment' => 'local' ] ),
		'expected' => $disabled,
	],
	'testShouldReturnUnchangedWithInvalidKey' => [
		'config'   => array_merge( $base, [ 'valid_key' => false ] ),
		'expected' => [],
	],
	'testShouldReturnUnchangedWithoutRucssCapability' => [
		'config'   => array_merge( $base, [ 'caps' => [ 'rocket_remove_unused_css' => false, 'rocket_manage_options' => true ] ] ),
		'expected' => [],
	],
	'testShouldReturnUnchangedWithoutManageOptionsCapability' => [
		'config'   => array_merge( $base, [ 'caps' => [ 'rocket_remove_unused_css' => true, 'rocket_manage_options' => false ] ] ),
		'expected' => [],
	],
	'testShouldAppendTheUsedCssAction' => [
		'config'   => $base,
		'expected' => [
			[
				'id'           => 'rocket_clean_saas',
				'title'        => 'Used CSS',
				'description'  => 'Clear the used CSS files.',
				'button_label' => 'Clear used CSS',
				'button_url'   => "http://example.org/wp-admin/admin-post.php?action=rocket_clean_saas&_wp_http_referer={$referer}|rocket_clean_saas",
			],
		],
	],
];
