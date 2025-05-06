<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Context;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;

class Context implements ContextInterface {
	/**
	 * Instance of the Option_Data class.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Array of default fonts to exclude (mostly system fonts).
	 *
	 * @var array
	 */
	private $exclusions  = [
        'serif',
        'sans-serif',
        'monospace',
        'cursive',
        'fantasy',
        'system-ui',
        'ui-serif',
        'ui-sans-serif',
        'ui-monospace',
        'ui-rounded',
        'Arial',
        'Helvetica',
        'Times New Roman',
        'Times',
        'Courier New',
        'Courier',
        'Georgia',
        'Palatino',
        'Garamond',
        'Bookman',
        'Tahoma',
        'Trebuchet MS',
        'Arial Black',
        'Impact',
        'Comic Sans MS',
    ];

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Instance of the Option_Data class.
	 * @param DataManager  $data_manager DataManager instance.
	 */
	public function __construct( Options_Data $options, DataManager $data_manager ) {
		$this->options = $options;
		$this->data_manager = $data_manager;
	}

	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		if ( $this->options->get( 'wp_rocket_no_licence' ) ) {
			return false;
		}

		return (bool) $this->options->get( 'auto_preload_fonts', 0 );
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return bool
	 */
	public function is_mobile_allowed(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}

	/**
     * Get array of fonts to be excluded.
     *
     * @return array
     */
    public function get_exclusions(): array {
		$lists = $this->data_manager->get_lists();
		$lists = isset( $lists->delay_js_exclusions ) ? $lists->delay_js_exclusions : [];
        $exclusions = array_merge( $this->exclusions, $lists );

        /**
		 * Filters excluded fonts.

		 * @param array $exclusions Array of fonts to exclude.
		 */
        return wpm_apply_filters_typed('array', 'rocket_preload_fonts_excluded_fonts', $exclusions );
    }
}
