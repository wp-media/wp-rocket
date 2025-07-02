<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\Head\ElementTrait;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager;
use WP_Rocket\Engine\Support\CommentTrait;

class UsedCSS {
	use RegexTrait;
	use CSSTrait;
	use CommentTrait;
	use ElementTrait;

	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	private $used_css_query;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Filesystem instance
	 *
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * RUCSS context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * External exclusions list, can be urls or attributes.
	 *
	 * @var array
	 */
	private $external_exclusions = [];

	/**
	 * Inline CSS attributes exclusions patterns to be preserved on the page after treeshaking.
	 *
	 * @var string[]
	 */
	private $inline_atts_exclusions = [];

	/**
	 * Inline CSS content exclusions patterns to be preserved on the page after treeshaking.
	 *
	 * @var string[]
	 */
	private $inline_content_exclusions = [];

	/**
	 * Above the fold Job Manager.
	 *
	 * @var Manager
	 */
	private $manager;

	/**
	 * Used CSS contents.
	 *
	 * @var string
	 */
	private $used_css_content = '';

	/**
	 * Preloaded font urls.
	 *
	 * @var array
	 */
	private $preloaded_fonts = [];

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data     $options Options instance.
	 * @param UsedCSS_Query    $used_css_query Usedcss Query instance.
	 * @param DataManager      $data_manager DataManager instance.
	 * @param Filesystem       $filesystem Filesystem instance.
	 * @param ContextInterface $context RUCSS context.
	 * @param Manager          $manager RUCSS manager.
	 */
	public function __construct(
		Options_Data $options,
		UsedCSS_Query $used_css_query,
		DataManager $data_manager,
		Filesystem $filesystem,
		ContextInterface $context,
		Manager $manager
	) {
		$this->options        = $options;
		$this->used_css_query = $used_css_query;
		$this->data_manager   = $data_manager;
		$this->filesystem     = $filesystem;
		$this->context        = $context;
		$this->manager        = $manager;
	}

	/**
	 * Check if RUCSS option is enabled.
	 *
	 * Used inside the CRON so post object isn't there.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) $this->options->get( 'remove_unused_css', 0 );
	}

	/**
	 * Start treeshaking the current page.
	 *
	 * @param string $html Buffet HTML for current page.
	 *
	 * @return string
	 */
	public function treeshake( string $html ): string {
		if ( ! $this->context->is_allowed() ) {
			return $html;
		}

		$clean_html = $this->hide_comments( $html );
		$clean_html = $this->hide_noscripts( $clean_html );
		$clean_html = $this->hide_scripts( $clean_html );

		if ( ! $this->html_has_title_tag( $clean_html ) ) {
			return $html;
		}

		global $wp;
		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();
		$used_css  = $this->used_css_query->get_row( $url, $is_mobile );

		if ( ! is_object( $used_css ) ) {
			$this->manager->add_url_to_the_queue( $url, $is_mobile );
			return $html;
		}

		if ( 'completed' !== $used_css->status || empty( $used_css->hash ) ) {
			return $html;
		}

		$used_css_content = $this->filesystem->get_used_css( $used_css->hash );

		if ( empty( $used_css_content ) ) {
			$this->used_css_query->delete_by_url( $url );
			return $html;
		}

		$this->used_css_content = $used_css_content;

		$html = $this->remove_used_css_from_html( $clean_html, $html );
		$this->add_used_fonts_preload( $used_css_content );
		$html = $this->remove_google_font_preconnect( $html );

		if ( ! empty( $used_css->id ) ) {
			$this->used_css_query->update_last_accessed( (int) $used_css->id );
		}

		return $this->add_meta_comment( 'remove_unused_css', $html );
	}

	/**
	 * Delete used css based on URL.
	 *
	 * @param string $url The page URL.
	 *
	 * @return boolean
	 */
	public function delete_used_css( string $url ): bool {
		$used_css_arr = $this->used_css_query->get_rows_by_url( $url );

		if ( empty( $used_css_arr ) ) {
			return false;
		}

		$deleted = true;

		foreach ( $used_css_arr as $used_css ) {
			if ( ! is_object( $used_css ) || empty( $used_css->id ) ) {
				continue;
			}

			$deleted = $deleted && $this->used_css_query->delete_item( $used_css->id );

			if ( ! empty( $used_css->hash ) ) {
				$count = $this->used_css_query->count_rows_by_hash( $used_css->hash );

				if ( 0 === $count ) {
					$this->filesystem->delete_used_css( $used_css->hash );
				}
			}
		}

		return $deleted;
	}

	/**
	 * Deletes all the used CSS files
	 *
	 * @since 3.11.4
	 *
	 * @return void
	 */
	public function delete_all_used_css() {
		$this->filesystem->delete_all_used_css();
	}

	/**
	 * Alter HTML and remove all CSS which was processed from HTML page.
	 *
	 * @param string $clean_html Cleaned HTML after removing comments, noscripts and scripts.
	 * @param string $html HTML content.
	 *
	 * @return string HTML content.
	 */
	private function remove_used_css_from_html( string $clean_html, string $html ): string {
		$this->set_inline_exclusions_lists();
		$html = $this->remove_external_styles_from_html( $clean_html, $html );
		return $this->remove_internal_styles_from_html( $clean_html, $html );
	}

	/**
	 * Remove external styles from the page's HTML.
	 *
	 * @param string $clean_html Cleaned HTML after removing comments, noscripts and scripts.
	 * @param string $html Actual page's HTML.
	 *
	 * @return string
	 */
	private function remove_external_styles_from_html( string $clean_html, string $html ) {
		$link_styles = $this->find(
			'<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>',
			$clean_html,
			'Uis'
		);

		$preserve_google_font = apply_filters( 'rocket_rucss_preserve_google_font', false );

		$external_exclusions = $this->validate_array_and_quote(
			/**
			 * Filters the array of external exclusions.
			 *
			 * @since 3.11.4
			 *
			 * @param array $external_exclusions Array of patterns used to match against the external style tag.
			 */
			(array) apply_filters( 'rocket_rucss_external_exclusions', $this->external_exclusions )
		);

		foreach ( $link_styles as $style ) {
			if (
				(
					! (bool) preg_match( '/rel=[\'"]?stylesheet[\'"]?/is', $style[0] )
					&&
					! ( (bool) preg_match( '/rel=[\'"]?preload[\'"]?/is', $style[0] ) && (bool) preg_match( '/as=[\'"]?style[\'"]?/is', $style[0] ) )
				)
				||
				( $preserve_google_font && strstr( $style['url'], '//fonts.googleapis.com/css' ) )
			) {
				continue;
			}

			if ( ! empty( $external_exclusions ) && $this->find( implode( '|', $external_exclusions ), $style[0] ) ) {
				continue;
			}

			$html = str_replace( $style[0], '', $html );
		}

		return (string) $html;
	}

	/**
	 * Remove internal styles from the page's HTML.
	 *
	 * @param string $clean_html Cleaned HTML after removing comments, noscripts and scripts.
	 * @param string $html Actual page's HTML.
	 *
	 * @return string
	 */
	private function remove_internal_styles_from_html( string $clean_html, string $html ) {
		$inline_styles = $this->find(
			'<style(?<atts>.*)>(?<content>.*)<\/style\s*>',
			$clean_html
		);

		$inline_atts_exclusions = $this->validate_array_and_quote(
			/**
			 * Filters the array of inline CSS attributes patterns to preserve
			 *
			 * @since 3.11
			 *
			 * @param array $inline_atts_exclusions Array of patterns used to match against the inline CSS attributes.
			 */
			(array) apply_filters( 'rocket_rucss_inline_atts_exclusions', $this->inline_atts_exclusions )
		);

		$inline_content_exclusions = $this->validate_array_and_quote(
			/**
			 * Filters the array of inline CSS content patterns to preserve
			 *
			 * @since 3.11
			 *
			 * @param array $inline_atts_exclusions Array of patterns used to match against the inline CSS content.
			 */
			(array) apply_filters( 'rocket_rucss_inline_content_exclusions', $this->inline_content_exclusions )
		);

		foreach ( $inline_styles as $style ) {
			if ( ! empty( $inline_atts_exclusions ) && $this->find( implode( '|', $inline_atts_exclusions ), $style['atts'] ) ) {
				continue;
			}

			if ( ! empty( $inline_content_exclusions ) && $this->find( implode( '|', $inline_content_exclusions ), $style['content'] ) ) {
				continue;
			}

			/**
			 * Filters the status of preserving inline style tags.
			 *
			 * @since 3.11.4
			 *
			 * @param bool $preserve_status Status of preserve.
			 * @param array $style Full match style tag.
			 */
			if ( apply_filters( 'rocket_rucss_preserve_inline_style_tags', true, $style ) ) {
				$content = trim( $style['content'] );

				if ( empty( $content ) ) {
					continue;
				}

				$empty_tag = str_replace( $style['content'], '', $style[0] );
				$html      = str_replace( $style[0], $empty_tag, $html );

				continue;
			}

			$html = str_replace( $style[0], '', $html );
		}

		return $html;
	}

	/**
	 * Add the used CSS style in <head> tag,
	 *
	 * @param array $items Head items.
	 *
	 * @return array Filtered head items.
	 */
	public function add_used_css_to_html( array $items ): array {
		$used_css = $this->get_used_css_content();
		if ( empty( $used_css ) ) {
			return $items;
		}

		// Remove locally hosted google fonts.
		$items = array_filter(
			$items,
			function ( $item ) {
				return ! isset( $item['data-wpr-hosted-gf-parameters'] );
			}
			);

		$items[] = $this->style_tag(
			$this->get_used_css_markup( $used_css ),
			[
				'id' => 'wpr-usedcss',
			]
		);
		return $items;
	}

	/**
	 * Insert preload fonts into page head.
	 *
	 * @param array $items Head elements.
	 * @return mixed
	 */
	public function insert_preload_fonts( $items ) {
		if ( ! $this->context->is_allowed() ) {
			return $items;
		}

		foreach ( $this->preloaded_fonts as $font ) {
			$items[] = $this->preload_link(
				[
					'href' => esc_url( $font ),
					'as'   => 'font',
					1      => 'crossorigin',
				]
			);
		}

		return $items;
	}

	/**
	 * Return Markup for used_css into the page.
	 *
	 * @param string $used_css Used CSS content.
	 *
	 * @return string
	 */
	private function get_used_css_markup( string $used_css ): string {
		/**
		 * Filters Used CSS content before output.
		 *
		 * @since 3.9.0.2
		 *
		 * @param string $used_css Used CSS content.
		 */
		$used_css = apply_filters( 'rocket_usedcss_content', $used_css );

		$used_css = str_replace( '\\', '\\\\', $used_css );// Guard the backslashes before passing the content to preg_replace.
		return $this->handle_charsets( $used_css, false );
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return boolean
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}

	/**
	 * Clear specific url.
	 *
	 * @param string $url Page url.
	 *
	 * @return void
	 */
	public function clear_url_usedcss( string $url ) {
		$this->delete_used_css( $url );

		/**
		 * Fires after clearing usedcss for specific url.
		 *
		 * @since 3.11
		 *
		 * @param string $url Current page URL.
		 */
		do_action( 'rocket_rucss_after_clearing_usedcss', $url );
	}

	/**
	 * Get the count of not completed rows.
	 *
	 * @return int
	 */
	public function get_not_completed_count() {
		return $this->used_css_query->get_not_completed_count();
	}

	/**
	 * Add preload links for the fonts in the used CSS
	 *
	 * @param string $used_css Used CSS content.
	 *
	 * @return void
	 */
	private function add_used_fonts_preload( string $used_css ): void {
		/**
		 * Filters the fonts preload from the used CSS
		 *
		 * @since 3.11
		 *
		 * @param bool $enable True to enable, false to disable.
		 */
		if ( ! apply_filters( 'rocket_enable_rucss_fonts_preload', true ) ) {
			return;
		}

		if ( ! preg_match_all( '/@font-face\s*{\s*(?<content>[^}]+)}/is', $used_css, $font_faces, PREG_SET_ORDER ) ) {
			return;
		}

		if ( empty( $font_faces ) ) {
			return;
		}

		/**
		 * Filters the list of fonts to exclude from preload
		 *
		 * @since 3.15.10
		 *
		 * @param array $excluded_fonts_preload List of fonts to exclude from preload
		 */
		$exclude_fonts_preload = wpm_apply_filters_typed( 'array', 'rocket_exclude_rucss_fonts_preload', [] );

		$urls = [];

		foreach ( $font_faces as $font_face ) {
			if ( empty( $font_face['content'] ) ) {
				continue;
			}

			$font_url = $this->extract_first_font( $font_face['content'] );

			/**
			 * Filters font URL with CDN hostname
			 *
			 * @since 3.11.4
			 *
			 * @param string $url url to be rewritten.
			 */
			$font_url = apply_filters( 'rocket_font_url', $font_url );

			if ( empty( $font_url ) ) {
				continue;
			}

			// Making sure the excluded fonts array isn't empty to avoid excluding all fonts.
			if ( ! empty( $exclude_fonts_preload ) ) {
				$exclude_fonts_preload = array_filter( $exclude_fonts_preload );

				// Combine the array elements into a single string with | as a separator and returning a pattern.
				$exclude_fonts_preload_pattern = implode(
					'|',
					array_map(
						function ( $item ) {
							return is_string( $item ) ? preg_quote( $item, '/' ) : '';
						},
						$exclude_fonts_preload
					)
				);

				// Check if the font URL matches any part of the exclude_fonts_preload array.
				if ( ! empty( $exclude_fonts_preload_pattern ) && preg_match( '/' . $exclude_fonts_preload_pattern . '/i', $font_url ) ) {
					continue; // Skip this iteration as the font URL is in the exclusion list.
				}
			}

			$urls[] = $font_url;
		}

		if ( empty( $urls ) ) {
			return;
		}

		$this->preloaded_fonts = array_unique( $urls );
	}

	/**
	 * Remove preconnect tag for google api.
	 *
	 * @param string $html html content.
	 *
	 * @return string
	 */
	protected function remove_google_font_preconnect( string $html ): string {
		$clean_html = $this->hide_comments( $html );
		$clean_html = $this->hide_noscripts( $clean_html );
		$clean_html = $this->hide_scripts( $clean_html );
		$links      = $this->find(
			'<link\s+([^>]+[\s"\'])?rel\s*=\s*[\'"]((preconnect)|(dns-prefetch))[\'"]([^>]+)?\/?>',
			$clean_html,
			'Uis'
		);

		foreach ( $links as $link ) {
			if ( preg_match( '/href=[\'"](https:)?\/\/fonts.googleapis.com\/?[\'"]/', $link[0] ) ) {
				$html = str_replace( $link[0], '', $html );
			}
		}

		return $html;
	}

	/**
	 * Extracts the first font URL from the font-face declaration
	 *
	 * Skips .eot fonts if it exists
	 *
	 * @since 3.11
	 *
	 * @param string $font_face Font-face declaration content.
	 *
	 * @return string
	 */
	private function extract_first_font( string $font_face ): string {
		if ( ! preg_match_all( '/src:\s*(?<urls>[^;}]*)/is', $font_face, $sources, PREG_SET_ORDER ) ) {
			return '';
		}

		foreach ( $sources as $src ) {
			if ( empty( $src['urls'] ) ) {
				continue;
			}

			$urls = explode( ',', $src['urls'] );

			foreach ( $urls as $url ) {
				if ( false !== strpos( $url, '.eot' ) ) {
					continue;
				}

				if ( ! preg_match( '/url\(\s*[\'"]?(?<url>[^\'")]+)[\'"]?\)/is', $url, $matches ) ) {
					continue;
				}

				return trim( $matches['url'] );
			}
		}

		return '';
	}

	/**
	 * Set Rucss inline attr exclusions
	 *
	 *  @return void
	 */
	private function set_inline_exclusions_lists() {
		$wpr_dynamic_lists               = $this->data_manager->get_lists();
		$this->inline_atts_exclusions    = isset( $wpr_dynamic_lists->rucss_inline_atts_exclusions ) ? $wpr_dynamic_lists->rucss_inline_atts_exclusions : [];
		$this->inline_content_exclusions = isset( $wpr_dynamic_lists->rucss_inline_content_exclusions ) ? $wpr_dynamic_lists->rucss_inline_content_exclusions : [];
	}

	/**
	 * Displays a notice if the used CSS folder is not writable
	 *
	 * @since 3.11.4
	 *
	 * @return void
	 */
	public function notice_write_permissions() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $this->filesystem->is_writable_folder() ) {
			return;
		}

		$message = rocket_notice_writing_permissions( trim( str_replace( rocket_get_constant( 'ABSPATH', '' ), '', rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH', '' ) ), '/' ) );

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}

	/**
	 * Validate the items in array to be strings only and preg_quote them.
	 *
	 * @param array $items Array to be validated and quoted.
	 *
	 * @return array|string[]
	 */
	private function validate_array_and_quote( array $items ) {
		$items_array = array_filter( $items, 'is_string' );

		return array_map(
			static function ( $item ) {
				return preg_quote( $item, '/' );
			},
			$items_array
		);
	}

	/**
	 * Check if database has at least one completed row.
	 *
	 * @return bool
	 */
	public function has_one_completed_row_at_least() {
		return $this->used_css_query->get_completed_count() > 0;
	}

	/**
	 * Get generated used CSS, getter method for used_css_content property.
	 *
	 * @return string
	 */
	public function get_used_css_content() {
		if ( ! $this->context->is_allowed() ) {
			return '';
		}
		return $this->used_css_content;
	}
}
