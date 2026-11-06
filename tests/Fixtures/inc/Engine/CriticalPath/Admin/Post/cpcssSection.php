<?php

$cpcss_content = <<<HTML
<p class="cpcss_generate ">
	Generate specific Critical Path CSS for this post.<a href="https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">
	More info</a>
</p>
<p class="cpcss_regenerate hidden">
	This post uses specific Critical Path CSS.<a href="https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">
	More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary"  disabled='disabled'>
		<span style="display: none;" class="spinner">
		</span>
		<span class="rocket-generate-post-cpss-btn-txt">
			Generate Specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate hidden">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary"  disabled='disabled'>
	<span>
		Revert back to the default CPCSS</span>
	</button>
</div>
HTML
;

$cpcss_content_not_disabled = <<<HTML
<p class="cpcss_generate ">
	Generate specific Critical Path CSS for this post.<a href="https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">
	More info</a>
</p>
<p class="cpcss_regenerate hidden">
	This post uses specific Critical Path CSS.<a href="https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">
	More info</a>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" >
		<span style="display: none;" class="spinner">
		</span>
		<span class="rocket-generate-post-cpss-btn-txt">
			Generate Specific CPCSS</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate hidden">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" >
	<span>
		Revert back to the default CPCSS</span>
	</button>
</div>
HTML
;

return [

	'testShouldDisplayAllWarnings' => [
		'config' => [
			'options'            => [
				'async_css' => 0,
				'async_css_mobile' => 0,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],

		'expected' => [
			// For Unit Test: the data the "generate" method should receive.
			'data'               => [
				'disabled_description' => 'Publish the post, Enable Load CSS asynchronously in WP Rocket settings, and Enable Load CSS asynchronously in the options above to use this feature.',
			],

			// For the integration test.
			'html' => <<<HTML
 <div class="inside">
	<h3>Critical Path CSS</h3>
	<div id="rocket-metabox-cpcss-content">
		{$cpcss_content}
	</div>
</div>
<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
	<div class="components-notice__content">
		<p>Publish the post, Enable Load CSS asynchronously in WP Rocket settings, and Enable Load CSS asynchronously in the options above to use this feature.</p>
	</div>
</div>
HTML
			,
		],
	],

	'testShouldDisplayPostNotPublishedAndOptionExcludedWarning' => [
		'config'   => [
			'options'            => [
				'async_css' => 1,
				'async_css_mobile' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => [
			// For Unit Test: the data the "generate" method should receive.
			'data'               => [
				'disabled_description' => 'Publish the post and Enable Load CSS asynchronously in the options above to use this feature.',
			],

			// For the integration test.
			'html' => <<<HTML
<div class="inside">
	<h3>Critical Path CSS</h3>
	<div id="rocket-metabox-cpcss-content">
		{$cpcss_content}
	</div>
</div>
<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
	<div class="components-notice__content">
	<p>Publish the post and Enable Load CSS asynchronously in the options above to use this feature.</p>
	</div>
</div>
HTML
			,
		],
	],

	'testShouldDisplayPostNotPublishedWarning' => [
		'config'   => [
			'options'            => [
				'async_css' => 1,
				'async_css_mobile' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'draft',
				'post_type'   => 'post',
			],
			'is_option_excluded' => false,
		],
		'expected' => [
			// For Unit Test: the data the "generate" method should receive.
			'data'               => [
				'disabled_description' => 'Publish the post to use this feature.',
			],

			// For the integration test.
			'html' => <<<HTML
<div class="inside">
	<h3>Critical Path CSS</h3>
	<div id="rocket-metabox-cpcss-content">
		{$cpcss_content}
	</div>
</div>
<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
	<div class="components-notice__content">
	<p>Publish the post to use this feature.</p>
	</div>
</div>
HTML
			,
		],
	],

	'testShouldDisplayOptionExcludedFromPostWarning' => [
		'config'   => [
			'options'            => [
				'async_css' => 1,
				'async_css_mobile' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'publish',
				'post_type'   => 'post',
			],
			'is_option_excluded' => true,
		],
		'expected' => [
			// For Unit Test: the data the "generate" method should receive.
			'data'               => [
				'disabled_description' => 'Enable Load CSS asynchronously in the options above to use this feature.',
			],

			// For the integration test.
			'html' => <<<HTML
<div class="inside">
	<h3>Critical Path CSS</h3>
	<div id="rocket-metabox-cpcss-content">
		{$cpcss_content}
	</div>
</div>
<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
	<div class="components-notice__content">
	<p>Enable Load CSS asynchronously in the options above to use this feature.</p>
	</div>
</div>
HTML
			,
		],
	],

	'testShouldNoWarning' => [
		'config' => [
			'options'            => [
				'async_css' => 1,
				'async_css_mobile' => 1,
			],
			'post'               => (object) [
				'ID'          => 1,
				'post_status' => 'publish',
				'post_type'   => 'post',
			],
			'is_option_excluded' => false,
		],

		'expected' => [
			// For Unit Test: the data the "generate" method should receive.
			'data' => [
				'disabled_description' => '',
			],

			// For the integration test.
			'html' => <<<HTML
<div class="inside">
	<h3>Critical Path CSS</h3>
	<div id="rocket-metabox-cpcss-content">
		{$cpcss_content_not_disabled}
	</div>
</div>
<div id="cpcss_response_notice" class="components-notice is-notice is-warning hidden">
	<div class="components-notice__content"></div>
</div>
HTML
			,
		],
	],

];
