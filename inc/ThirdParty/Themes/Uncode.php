<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

class Uncode extends ThirdpartyTheme {
	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'uncode';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! self::is_current_theme() ) {
			return [];
		}
		return [
			'rocket_exclude_js'                 => 'exclude_js',
			'rocket_excluded_inline_js_content' => 'exclude_inline_js',
			'rocket_exclude_defer_js'           => 'exclude_defer_js',
			'rocket_delay_js_exclusions'        => 'exclude_delay_js',
		];
	}

	/**
	 * Excludes Uncode init and ai-uncode JS files from minification/combine
	 *
	 * @since 3.1
	 *
	 * @param array $excluded_js Array of JS filepaths to be excluded.
	 * @return array
	 */
	public function exclude_js( $excluded_js ): array {
		$excluded_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.js' );
		$excluded_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/init.min.js' );
		$excluded_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.min.js' );
		$excluded_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/ai-uncode.js' );
		$excluded_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/ai-uncode.min.js' );
		$excluded_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/ai-uncode.min.js' );

		return $excluded_js;
	}

	/**
	 * Excludes some Uncode inline scripts from combine JS
	 *
	 * @since 3.1
	 *
	 * @param array $inline_js Array of patterns to match for exclusion.
	 * @return array
	 */
	public function exclude_inline_js( $inline_js ): array {
		$inline_js[] = 'SiteParameters';
		$inline_js[] = 'script-';
		$inline_js[] = 'initBox';
		$inline_js[] = 'initHeader';
		$inline_js[] = 'fixMenuHeight';

		return $inline_js;
	}

	/**
	 * Excludes Uncode JS files from defer JS
	 *
	 * @since 3.2.5
	 *
	 * @param array $exclude_defer_js Array of JS filepaths to be excluded.
	 * @return array
	 */
	public function exclude_defer_js( $exclude_defer_js ): array {
		$exclude_defer_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.js' );
		$exclude_defer_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/init.min.js' );
		$exclude_defer_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.min.js' );
		$exclude_defer_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/ai-uncode.min.js' );
		$exclude_defer_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/ai-uncode.min.js' );
		$exclude_defer_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/ai-uncode.js' );
		return $exclude_defer_js;
	}

	/**
	 * Excludes Uncode JS files from delay JS
	 *
	 * @since 3.10.5
	 *
	 * @param array $exclude_delay_js Array of JS to be excluded.
	 * @return array
	 */
	public function exclude_delay_js( $exclude_delay_js ): array {
		$exclude_delay_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.js' );
		$exclude_delay_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/init.min.js' );
		$exclude_delay_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/init.min.js' );
		$exclude_delay_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/min/ai-uncode.min.js' );
		$exclude_delay_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/ai-uncode.min.js' );
		$exclude_delay_js[] = rocket_clean_exclude_file( get_template_directory_uri() . '/library/js/ai-uncode.js' );
		$exclude_delay_js[] = 'UNCODE\.';

		return $exclude_delay_js;
	}
}
