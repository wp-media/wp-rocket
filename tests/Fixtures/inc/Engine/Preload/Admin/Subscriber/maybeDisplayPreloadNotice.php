<?php

$html = <<<HTML
	<div class="notice notice-info is-dismissible" id="rocket-notice-preload-processing">
		<p><strong>WP Rocket</strong>: Please wait. The preload service is processing your pages.</p>
	</div>
HTML;


return [
	'noRightShouldDisplayNothing' => [
		'config' => [
			'activated' => true,
			'cap' => false,
			'screen' => 'settings_page_wprocket',
		],
		'expected' => [
			'should_contain' => false,
			'html' => $html
		],
	],
	'wrongScreenShouldDisplayNothing' => [
		'config' => [
			'activated' => true,
			'cap' => true,
			'transient' => false,
			'screen' => 'front',
		],
		'expected' => [
			'should_contain' => false,
			'html' => $html
		],
	],
	'disableShouldNotDisplayNotice' => [
		'config' => [
			'activated' => false,
			'cap' => true,
			'transient' => true,
			'screen' => 'settings_page_wprocket',
		],
		'expected' => [
			'should_contain' => false,
			'html' => $html
		],
	],
	'noProcessShouldDisplayNothing' => [
		'config' => [
			'activated' => true,
			'cap' => true,
			'transient' => false,
			'screen' => 'settings_page_wprocket',
		],
		'expected' => [
			'should_contain' => false,
			'html' => $html
		],
	],
	'shouldDisplayNotice' => [
		'config' => [
			'activated' => true,
			'cap' => true,
			'transient' => true,
			'screen' => 'settings_page_wprocket',
		],
		'expected' => [
			'should_contain' => true,
			'html' => $html
		],
	]
];
