<?php

namespace WP_Rocket\Tests\Fixtures\classes;

/**
 * Single source of truth for the gated plugin-compat subscriber ids that report
 * inactive in this test environment.
 *
 * Shared by:
 * - tests/Unit/inc/ThirdParty/Plugins/PluginResolver/getActivePlugins.php
 * - tests/Integration/inc/ThirdParty/Plugins/PluginResolver/pluginCompatSubscribersBehaviorEquivalence.php
 *
 * Both suites autoload this class via the WP_Rocket\Tests\ PSR-4 mapping (composer.json
 * autoload-dev), so it can be referenced from Unit and Integration tests alike.
 */
class PluginResolverGatedIds {
	/**
	 * Ids gated behind PluginCompatibilityInterface whose target plugins are not
	 * installed/defined in this test environment, so they no longer default-active
	 * like the rest of the registry.
	 *
	 * @var array<string>
	 */
	public const IDS = [
		'elementor_subscriber',
		'beaverbuilder_subscriber',
		'simple_custom_css',
		'pdfembedder',
		'wordfence_subscriber',
		'unlimited_elements',
		'inline_related_posts',
		'rank_math_seo',
		'rocket_lazy_load',
		'the_events_calendar',
		'perfmatters',
		'weglot',
		'translatepress',
		'termly_subscriber',
		'optimole_subscriber',
		'convertplug',
		'syntaxhighlighter_subscriber',
		'ngg_subscriber',
		'pwa',
		'yoast_seo',
		'thirstyaffiliates',
		'autoptimize',
		'jetpack',
		'seopress',
		'the_seo_framework',
	];
}
