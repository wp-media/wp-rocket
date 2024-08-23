<?php

return [
	'shouldOutputLinkButton' => [
		'config'   => [
			'title_attr_text' => 'Title text',
			'environment'     => 'production',
			'label'           => 'Button label',
			'action'          => 'menu-action',
			'title'           => 'Menu title',
			'context'         => true,
		],
		'expected' => <<<HTML
<div class="wpr-field">
	<h4 class="wpr-title3">Button label</h4>
	<a href="" class="wpr-button wpr-button--icon wpr-button--small wpr-icon-trash" title="Title text">Menu title</a>
</div>
HTML
	],
	'shouldOutputEmptyWhenContextIsFalse' => [
		'config'   => [
			'title_attr_text' => 'Title text',
			'environment'     => 'production',
			'label'           => 'Button label',
			'action'          => 'menu-action',
			'title'           => 'Menu title',
			'context'         => false,
		],
		'expected' => null
	],
	'shouldOutputEmptyWhenEnvironmentIsLocal' => [
		'config'   => [
			'title_attr_text' => 'Title text',
			'environment'     => 'local',
			'label'           => 'Button label',
			'action'          => 'menu-action',
			'title'           => 'Menu title',
			'context'         => false,
		],
		'expected' => null
	],
	'shouldOutputLinkButtonWithoutLinkTitleAttribute' => [
		'config'   => [
			'title_attr_text' => '',
			'environment'     => 'production',
			'label'           => 'label',
			'action'          => 'menu-action',
			'title'           => 'Title',
			'context'         => true,
		],
		'expected' => <<<HTML
<div class="wpr-field">
	<h4 class="wpr-title3">label</h4>
	<a href="" class="wpr-button wpr-button--icon wpr-button--small wpr-icon-trash">Title</a>
</div>
HTML
	],
];
