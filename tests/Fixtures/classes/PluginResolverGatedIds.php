<?php

namespace WP_Rocket\Tests\Fixtures\classes;

/**
 * Single source of truth for the issue #8789 gated-inactive plugin ids, across
 * all slices.
 *
 * Shared by:
 * - tests/Unit/inc/ThirdParty/Plugins/PluginResolver/getActivePlugins.php
 * - tests/Integration/inc/ThirdParty/Plugins/PluginResolver/pluginCompatSubscribersBehaviorEquivalence.php
 *
 * Both suites autoload this class via the WP_Rocket\Tests\ PSR-4 mapping (composer.json
 * autoload-dev), so it can be referenced from Unit and Integration tests alike.
 *
 * Renamed from PluginResolverSlice1GatedIds (issue #8789 slice 2): the list now
 * spans multiple slices, so a slice-agnostic name keeps it a single source of
 * truth as later slices append their own gated ids instead of forking the list.
 */
class PluginResolverGatedIds {
	/**
	 * Ids gated by issue #8789 (slices 1-2, so far) that report inactive in this
	 * test environment (none of their target plugins are installed/defined), so
	 * they no longer default-active like the rest of the registry.
	 *
	 * @var array<string>
	 */
	public const IDS = [
		// Slice 1.
		'elementor_subscriber',
		'beaverbuilder_subscriber',
		'simple_custom_css',
		'pdfembedder',
		'wordfence_subscriber',
		'unlimited_elements',
		'inline_related_posts',
		// Slice 2.
		'rank_math_seo',
		'rocket_lazy_load',
		'the_events_calendar',
		'perfmatters',
		'weglot',
		'translatepress',
		'termly_subscriber',
		'optimole_subscriber',
		'convertplug',
	];
}
