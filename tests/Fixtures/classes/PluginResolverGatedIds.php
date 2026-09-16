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
	 * Issue #8795 (part 2 of #8770) ports 9 non-lifecycle plugin-compat files into
	 * gated subscribers. None of their target plugins are present here, so all 9
	 * drop out of get_active_plugins(). They are new registry ids, so the active
	 * count is unchanged (43): each id appears in both the registry and this list,
	 * and array_diff() removes it from the resolved set.
	 *
	 * @var array<string>
	 */
	public const IDS = [
		'premium_seo_pack',
		'thrive_visual_editor',
		'visual_composer',
		'kk_star_ratings',
		'wp_postratings',
		'metaslider',
		'soliloquy',
		'wp_offload_s3',
		'wp_offload_s3_assets',
	];
}
