<?php

$contains = [ 'data-wap-prompt="I need help with &#039;Page title&#039; &gt; &#039;Section title&#039;"', 'wpr-infoAction--help', 'Need Help?' ];
$as_link  = [ 'href="https://docs.example.org/help"', 'target="_blank"', 'rel="noopener noreferrer"' ];
$cdn_ctx  = 'data-wpr_track_context="Content Delivery"';
$driver   = '<span class="rocketcdn-driver-js">RocketCDN</span>';

return [
	'fields_container_keeps_need_help' => [
		'config'   => [ 'type' => 'fields_container' ],
		'expected' => [ 'contains' => array_merge( $contains, $as_link, [ 'data-wpr_track_context="Settings"' ] ), 'not_contains' => [] ],
	],
	'fields_container_on_cdn_page_tracks_content_delivery' => [
		'config'   => [ 'type' => 'fields_container', 'page' => 'page_cdn' ],
		'expected' => [ 'contains' => array_merge( $contains, [ $cdn_ctx ] ), 'not_contains' => [] ],
	],
	'fields_container_on_rocket_insights_page_tracks_rocket_insights' => [
		'config'   => [ 'type' => 'fields_container', 'page' => 'rocket_insights' ],
		'expected' => [ 'contains' => array_merge( $contains, [ 'data-wpr_track_context="Rocket Insights"' ] ), 'not_contains' => [] ],
	],
	'addons_container_keeps_need_help' => [
		'config'   => [ 'type' => 'addons_container' ],
		'expected' => [ 'contains' => array_merge( $contains, [ '<button', 'data-wpr_track_context="Addons"' ] ), 'not_contains' => [ 'href=', 'target="_blank"' ] ],
	],
	'nocontainer_with_title_keeps_need_help' => [
		'config'   => [ 'type' => 'nocontainer_with_title' ],
		'expected' => [ 'contains' => array_merge( $contains, $as_link, [ 'data-wpr_track_context="Settings"' ] ), 'not_contains' => [ 'exclude-cdn-help-js', 'data-rocketcdn-url' ] ],
	],
	'nocontainer_with_title_on_cdn_page_keeps_rocketcdn_data' => [
		'config'   => [
			'type' => 'nocontainer_with_title',
			'page' => 'page_cdn',
			'help' => [
				'id'            => 'abc',
				'url'           => 'https://docs.example.org/help',
				'rocketcdn_url' => 'https://docs.example.org/rocketcdn',
				'other_cdn_url' => 'https://docs.example.org/other-cdn',
			],
		],
		'expected' => [
			'contains'     => array_merge(
				$contains,
				[
					$cdn_ctx,
					'class="wpr-infoAction wpr-infoAction--help wpr-icon-help exclude-cdn-help-js"',
					'data-rocketcdn-url="https://docs.example.org/rocketcdn"',
					'data-other-cdn-url="https://docs.example.org/other-cdn"',
				]
			),
			'not_contains' => [ 'data-rocketcdn-id', 'data-other-cdn-id', 'data-beacon-id' ],
		],
	],
	'purge_cdn_cache_section_keeps_need_help' => [
		'config'   => [ 'type' => 'purge_cdn_cache_section' ],
		'expected' => [ 'contains' => array_merge( $contains, $as_link, [ $cdn_ctx, 'wpr-cdn-purge__learn-more', 'Learn more about Purge' ] ), 'not_contains' => [] ],
	],
	'nocontainer_with_title_prompt_has_no_markup' => [
		'config'   => [ 'type' => 'nocontainer_with_title', 'page' => 'page_cdn', 'title' => "Manage {$driver} Exclusions" ],
		'expected' => [
			'contains'     => [ "<h3 class=\"wpr-title2\">Manage {$driver} Exclusions</h3>", 'data-wap-prompt="I need help with &#039;Page title&#039; &gt; &#039;Manage RocketCDN Exclusions&#039;"' ],
			'not_contains' => [ 'data-wap-prompt="I need help with &#039;Page title&#039; &gt; &#039;Manage &lt;span' ],
		],
	],
	'purge_cdn_cache_section_prompt_has_no_markup' => [
		'config'   => [ 'type' => 'purge_cdn_cache_section', 'title' => "Purge {$driver} Cache" ],
		'expected' => [
			'contains'     => [ "<h3 class=\"wpr-title2\">Purge {$driver} Cache</h3>", 'data-wap-prompt="I need help with &#039;Page title&#039; &gt; &#039;Purge RocketCDN Cache&#039;"' ],
			'not_contains' => [ 'data-wap-prompt="I need help with &#039;Page title&#039; &gt; &#039;Purge &lt;span' ],
		],
	],
	'your_own_cdn_keeps_need_help' => [
		'config'   => [ 'type' => 'your_own_cdn' ],
		'expected' => [ 'contains' => array_merge( $contains, $as_link, [ $cdn_ctx ] ), 'not_contains' => [] ],
	],
	'rocketcdn_free_keeps_need_help' => [
		'config'   => [ 'type' => 'rocketcdn_free' ],
		'expected' => [ 'contains' => array_merge( $contains, $as_link, [ $cdn_ctx ] ), 'not_contains' => [] ],
	],
	'rocketcdn_paid_keeps_need_help' => [
		'config'   => [ 'type' => 'rocketcdn_paid' ],
		'expected' => [ 'contains' => array_merge( $contains, $as_link, [ $cdn_ctx ] ), 'not_contains' => [] ],
	],
];
