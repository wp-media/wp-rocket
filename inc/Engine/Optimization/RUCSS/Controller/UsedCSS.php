<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Dependencies\Minify\CSS as MinifyCSS;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Logger\Logger;

class UsedCSS {
	use RegexTrait, CSSTrait;

	/**
	 * UsedCss Query instance.
	 *
	 * @var UsedCSS_Query
	 */
	private $used_css_query;

	/**
	 * Resources Query instance.
	 *
	 * @var ResourcesQuery
	 */
	private $resources_query;

	/**
	 * Purge instance
	 *
	 * @var Purge
	 */
	private $purge;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * APIClient instance
	 *
	 * @var APIClient
	 */
	private $api;

	/**
	 * Inline exclusions regexes not to removed from the page after treeshaking.
	 *
	 * @var string[]
	 */
	private $inline_exclusions = [
		'rocket-lazyload-inline-css',
	];

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data   $options         Options instance.
	 * @param UsedCSS_Query  $used_css_query  Usedcss Query instance.
	 * @param ResourcesQuery $resources_query Resources Query instance.
	 * @param Purge          $purge           Purge instance.
	 * @param APIClient      $api             Apiclient instance.
	 */
	public function __construct(
		Options_Data $options,
		UsedCSS_Query $used_css_query,
		ResourcesQuery $resources_query,
		Purge $purge,
		APIClient $api
	) {
		$this->options         = $options;
		$this->used_css_query  = $used_css_query;
		$this->resources_query = $resources_query;
		$this->purge           = $purge;
		$this->api             = $api;
	}

	/**
	 * Determines if we treeshake the CSS.
	 *
	 * @return boolean
	 */
	public function is_allowed(): bool {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( rocket_bypass() ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'remove_unused_css' ) ) {
			return false;
		}

		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return false;
		}

		// Bailout if user is logged in and cache for logged in customers is active.
		if ( is_user_logged_in() && (bool) $this->options->get( 'cache_logged_user', 0 ) ) {
			return false;
		}

		$wp_rocket_prewarmup_stats = get_option( 'wp_rocket_prewarmup_stats', [] );
		$allow_optimization        = $wp_rocket_prewarmup_stats['allow_optimization'] ?? false;
		if ( ! $allow_optimization ) {
			return false;
		}

		return true;
	}

	/**
	 * Apply TreeShaked CSS to the current HTML page.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string  HTML content.
	 */
	public function treeshake( string $html ): string {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		global $wp;
		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();
		$used_css  = $this->get_used_css( $url, $is_mobile );

		/**
		 * Filters the RUCSS safelist
		 *
		 * @since 3.11
		 *
		 * @param array $safelist Array of safelist values.
		 */
		$safelist = apply_filters( 'rocket_rucss_safelist', $this->options->get( 'remove_unused_css_safelist', [] ) );

		if ( empty( $used_css ) || ( $used_css->retries < 3 ) ) {
			$config = [
				'treeshake'      => 1,
				'rucss_safelist' => $safelist,
			];

			$treeshaked_result = $this->api->optimize( $html, $url, $config );

			if ( 200 !== $treeshaked_result['code'] ) {
				Logger::error(
					'Error when contacting the RUCSS API.',
					[
						'rucss error',
						'url'     => $url,
						'code'    => $treeshaked_result['code'],
						'message' => $treeshaked_result['message'],
					]
				);

				return $html;
			}

			$retries = 0;
			if ( isset( $used_css->retries ) ) {
				$retries = $used_css->retries;
			}

			if ( ! empty( $treeshaked_result['unprocessed_css'] ) ) {
				$this->schedule_rucss_retry();
			}

			$data = [
				'url'            => $url,
				'css'            => $treeshaked_result['css'],
				'unprocessedcss' => wp_json_encode( $treeshaked_result['unprocessed_css'] ),
				'retries'        => empty( $treeshaked_result['unprocessed_css'] ) ? 3 : $retries + 1,
				'is_mobile'      => $is_mobile,
				'modified'       => current_time( 'mysql', true ),
			];

			$used_css = $this->save_or_update_used_css( $data );

			if ( ! $used_css ) {
				return $html;
			}
		}

		if ( 3 === $used_css->retries && ! empty( $used_css->unprocessedcss ) ) {
			$this->remove_unprocessed_from_resources( $used_css->unprocessedcss );
		}

		$html = $this->remove_used_css_from_html( $html, $used_css->unprocessedcss );
		$html = $this->add_used_css_to_html( $html, $used_css );

		$this->update_last_accessed( (int) $used_css->id );

		return $html;
	}

	/**
	 * Delete used css based on URL.
	 *
	 * @param string $url The page URL.
	 *
	 * @return boolean
	 */
	public function delete_used_css( string $url ): bool {
		$used_css_arr = $this->used_css_query->query( [ 'url' => $url ] );

		if ( empty( $used_css_arr ) ) {
			return false;
		}

		$deleted = true;

		foreach ( $used_css_arr as $used_css ) {
			if ( empty( $used_css->id ) ) {
				continue;
			}

			$deleted = $deleted && $this->used_css_query->delete_item( $used_css->id );
		}

		return $deleted;
	}

	/**
	 * Resets retries to 1 and cleans URL cache for retrying the regeneration of the used CSS.
	 *
	 * @return void
	 */
	public function retries_pages_with_unprocessed_css() {
		if ( ! (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		$used_css_list = $this->get_used_css_with_unprocessed_css();

		foreach ( $used_css_list as $used_css_item ) {
			// Resets retries to 1.
			$this->used_css_query->update_item(
				$used_css_item->id,
				[ 'retries' => 1 ]
			);
			// Cleans page cache.
			$this->purge->purge_url( $used_css_item->url );
		}
	}

	/**
	 * Get UsedCSS from DB table based on page url.
	 *
	 * @param string $url       The page URL.
	 * @param bool   $is_mobile Page is_mobile.
	 *
	 * @return UsedCSS_Row|false
	 */
	private function get_used_css( string $url, bool $is_mobile = false ) {
		$query = $this->used_css_query->query(
			[
				'url'       => $url,
				'is_mobile' => $is_mobile,
			]
		);

		if ( empty( $query[0] ) ) {
			return false;
		}

		return $query[0];
	}

	/**
	 * Get UsedCSS from DB table which has unprocessed CSS files.
	 *
	 * @return array
	 */
	private function get_used_css_with_unprocessed_css() {
		$query = $this->used_css_query->query(
			[
				'unprocessedcss__not_in' => [
					'not_in' => '[]',
				],
			]
		);

		return $query;
	}

	/**
	 * Insert or update used css row based on URL.
	 *
	 * @param array $data           {
	 *                              Data to be saved / updated in database.
	 *
	 * @type string $url            The page URL.
	 * @type string $css            The page used css.
	 * @type string $unprocessedcss A json_encoded array of the page unprocessed CSS list.
	 * @type int    $retries        No of automatically retries for generating the unused css.
	 * @type bool   $is_mobile      Is mobile page.
	 * }
	 *
	 * @return UsedCSS_Row|false
	 */
	private function save_or_update_used_css( array $data ) {
		$used_css = $this->get_used_css( $data['url'], $data['is_mobile'] );

		$data['css'] = $this->apply_font_display_swap( $data['css'] );
		$minifier    = new MinifyCSS( $data['css'] );

		/**
		 * Filters Used CSS content before saving into DB.
		 *
		 * @since 3.9.0.2
		 *
		 * @param string $usedcss Used CSS.
		 */
		$data['css'] = apply_filters( 'rocket_usedcss_content', $minifier->minify() );

		if ( empty( $used_css ) ) {
			$inserted = $this->insert_used_css( $data );

			if ( ! $inserted ) {
				return false;
			}
			return $inserted;
		}

		$updated = $this->update_used_css( (int) $used_css->id, $data );

		if ( ! $updated ) {
			return false;
		}
		return $updated;
	}

	/**
	 * Insert used CSS.
	 *
	 * @param array $data Data to be inserted in used_css table.
	 *
	 * @return object|false
	 */
	private function insert_used_css( array $data ) {
		$id = $this->used_css_query->add_item( $data );

		if ( empty( $id ) ) {
			return false;
		}

		return $this->used_css_query->get_item( $id );
	}

	/**
	 * Update used CSS.
	 *
	 * @param integer $id   Used CSS ID.
	 * @param array   $data Data to be updated in used_css table.
	 *
	 * @return object|false
	 */
	private function update_used_css( int $id, array $data ) {
		$updated = $this->used_css_query->update_item( $id, $data );
		if ( ! $updated ) {
			return false;
		}

		return $this->used_css_query->get_item( $id );
	}

	/**
	 * Alter HTML and remove all CSS which was processed from HTML page.
	 *
	 * @param string $html            HTML content.
	 * @param array  $unprocessed_css List with unprocesses CSS links or inline.
	 *
	 * @return string HTML content.
	 */
	private function remove_used_css_from_html( string $html, array $unprocessed_css ): string {
		$clean_html = $this->hide_comments( $html );
		$clean_html = $this->hide_noscripts( $clean_html );
		$clean_html = $this->hide_scripts( $clean_html );

		$link_styles = $this->find(
			'<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>',
			$clean_html,
			'Uis'
		);

		$inline_styles = $this->find(
			'<style(?<atts>.*)>(?<content>.*)<\/style\s*>',
			$clean_html
		);

		$unprocessed_links  = $this->unprocessed_flat_array( 'link', $unprocessed_css );
		$unprocessed_styles = $this->unprocessed_flat_array( 'inline', $unprocessed_css );

		foreach ( $link_styles as $style ) {
			if (
				! (bool) preg_match( '/rel=[\'"]stylesheet[\'"]/is', $style[0] )
				||
				strstr( $style['url'], '//fonts.googleapis.com/css' )
				||
				in_array( htmlspecialchars_decode( $style['url'] ), $unprocessed_links, true )
			) {
				continue;
			}
			$html = str_replace( $style[0], '', $html );
		}

		$inline_exclusions = (array) array_map(
			function ( $item ) {
				return preg_quote( $item, '/' );
			},
			$this->inline_exclusions
		);

		foreach ( $inline_styles as $style ) {
			if ( in_array( $this->strip_line_breaks( $style['content'] ), $unprocessed_styles, true ) ) {
				continue;
			}

			if ( ! empty( $inline_exclusions ) && $this->find( implode( '|', $inline_exclusions ), $style['atts'] ) ) {
				continue;
			}

			$html = str_replace( $style[0], '', $html );
		}

		return $html;
	}

	/**
	 * Alter HTML string and add the used CSS style in <head> tag,
	 *
	 * @param string      $html     HTML content.
	 * @param UsedCSS_Row $used_css Used CSS DB row.
	 *
	 * @return string HTML content.
	 */
	private function add_used_css_to_html( string $html, UsedCSS_Row $used_css ): string {
		$replace = preg_replace(
			'#</title>#iU',
			'</title>' . $this->get_used_css_markup( $used_css ),
			$html,
			1
		);

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Update UsedCSS Row last_accessed date to current date.
	 *
	 * @param int $id Used CSS id.
	 *
	 * @return bool
	 */
	private function update_last_accessed( int $id ): bool {
		return (bool) $this->used_css_query->update_item(
			$id,
			[
				'last_accessed' => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Hides <noscript> blocks from the HTML to be parsed.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function hide_noscripts( string $html ): string {
		$replace = preg_replace( '#<noscript[^>]*>.*?<\/noscript\s*>#mis', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Hides unwanted blocks from the HTML to be parsed.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function hide_comments( string $html ): string {
		$replace = preg_replace( '#<!--\s*noptimize\s*-->.*?<!--\s*/\s*noptimize\s*-->#is', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		$replace = preg_replace( '/<!--(.*)-->/Uis', '', $replace );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Hides scripts from the HTML to be parsed when removing CSS from it
	 *
	 * @since 3.10.2
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function hide_scripts( string $html ): string {
		$replace = preg_replace( '#<script[^>]*>.*?<\/script\s*>#mis', '', $html );

		if ( null === $replace ) {
			return $html;
		}

		return $replace;
	}

	/**
	 * Create dedicated array of unprocessed css.
	 *
	 * @param string $type            CSS type (link / inline).
	 * @param array  $unprocessed_css Array with unprocessed CSS.
	 *
	 * @return array Array with type of unprocessed CSS.
	 */
	private function unprocessed_flat_array( string $type, array $unprocessed_css ): array {
		$unprocessed_array = [];
		foreach ( $unprocessed_css as $css ) {
			if ( $type === $css['type'] ) {
				$unprocessed_array[] = $this->strip_line_breaks( $css['content'] );
			}
		}

		return $unprocessed_array;
	}

	/**
	 * Strip line breaks.
	 *
	 * @param string $value - Value to be processed.
	 *
	 * @return string
	 */
	private function strip_line_breaks( string $value ): string {
		$value = str_replace( [ "\r", "\n", "\r\n", "\t" ], '', $value );

		return trim( $value );
	}

	/**
	 * Return Markup for used_css into the page.
	 *
	 * @param UsedCSS_Row $used_css Used CSS DB Row.
	 *
	 * @return string
	 */
	private function get_used_css_markup( UsedCSS_Row $used_css ): string {
		$used_css_contents = $this->handle_charsets( $used_css->css, false );
		return sprintf(
			'<style id="wpr-usedcss">%s</style>',
			$used_css_contents
		);
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return boolean
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&&
			$this->options->get( 'do_caching_mobile_files', 0 )
			&&
			wp_is_mobile();
	}

	/**
	 * Schedules RUCSS to retry pages with missing CSS files.
	 * Retries happen after 30 minutes.
	 *
	 * @return void
	 */
	private function schedule_rucss_retry() {
		$scheduled = wp_next_scheduled( 'rocket_rucss_retries_cron' );

		if ( $scheduled ) {
			return;
		}

		wp_schedule_single_event( time() + ( 0.5 * HOUR_IN_SECONDS ), 'rocket_rucss_retries_cron' );
	}

	/**
	 * Remove any unprocessed items from the resources table.
	 *
	 * @since 3.9
	 *
	 * @param array $unprocessed_css Unprocessed CSS Items.
	 *
	 * @return void
	 */
	private function remove_unprocessed_from_resources( $unprocessed_css ) {
		foreach ( $unprocessed_css as $resource ) {
			$this->resources_query->remove_by_url( $resource['content'] );
		}
	}
}
