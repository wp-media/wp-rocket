<?php

return [
	'shouldOutputLinkButtonWithDescription' => [
		'config'   => [
			'description'     => 'Short description for button',
			'environment'     => 'production',
			'label'           => 'Button label',
			'action'          => 'menu-action',
			'title'           => 'Menu title',
			'context'         => true,
		],
		'expected' => <<<HTML
<div class="wpr-field">
	<h4 class="wpr-title3">Button label</h4>
	<p>Short description for button</p>
	<a href="" class="wpr-button wpr-button--icon wpr-button--small wpr-icon-trash">Menu title</a>
</div>
HTML
	],
	'shouldOutputEmptyWhenContextIsFalse' => [
		'config'   => [
			'description' => 'Title text',
			'environment' => 'production',
			'label'       => 'Button label',
			'action'      => 'menu-action',
			'title'       => 'Menu title',
			'context'     => false,
		],
		'expected' => null
	],
	'shouldOutputEmptyWhenEnvironmentIsLocal' => [
		'config'   => [
			'description' => 'Title text',
			'environment' => 'local',
			'label'       => 'Button label',
			'action'      => 'menu-action',
			'title'       => 'Menu title',
			'context'     => false,
		],
		'expected' => null
	],
	'shouldOutputLinkButtonWithoutLinkDescription' => [
		'config'   => [
			'description' => '',
			'environment' => 'production',
			'label'       => 'label',
			'action'      => 'menu-action',
			'title'       => 'Title',
			'context'     => true,
		],
		'expected' => <<<HTML
<div class="wpr-field">
	<h4 class="wpr-title3">label</h4>
	<a href="" class="wpr-button wpr-button--icon wpr-button--small wpr-icon-trash">Title</a>
</div>
HTML
	],
];
