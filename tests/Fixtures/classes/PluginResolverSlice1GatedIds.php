<?php

namespace WP_Rocket\Tests\Fixtures\classes;

/**
 * Single source of truth for the issue #8789 slice 1 gated-inactive plugin ids.
 *
 * Shared by:
 * - tests/Unit/inc/ThirdParty/Plugins/PluginResolver/getActivePlugins.php
 * - tests/Integration/inc/ThirdParty/Plugins/PluginResolver/pluginCompatSubscribersBehaviorEquivalence.php
 *
 * Both suites autoload this class via the WP_Rocket\Tests\ PSR-4 mapping (composer.json
 * autoload-dev), so it can be referenced from Unit and Integration tests alike.
 */
class PluginResolverSlice1GatedIds {
	/**
	 * Ids gated by issue #8789 slice 1 that report inactive in this test
	 * environment (none of their target plugins are installed/defined), so
	 * they no longer default-active like the rest of the registry.
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
	];
}
