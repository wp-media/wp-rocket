<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;

class UsedCSS {
	use RegexTrait;

	/**
	 * UsedCss Query instance
	 *
	 * @var UsedCSS_Query
	 */
	private $used_css_query;

	/**
	 * Purge instance
	 *
	 * @var Purge
	 */
	private $purge;

	/**
	 * Status of CPCSS option.
	 *
	 * @var bool
	 */
	private $cpcss_enabled = false;

	/**
	 * Instantiate the class
	 *
	 * @param UsedCSS_Query $used_css_query UsedCSS Query instance.
	 * @param Purge         $purge          Purge instance.
	 */
	public function __construct( UsedCSS_Query $used_css_query, Purge $purge ) {
		$this->used_css_query = $used_css_query;
		$this->purge          = $purge;
	}

	/**
	 * Get UsedCSS from DB table based on page url.
	 *
	 * @param string $url       The page URL.
	 * @param bool   $is_mobile Page is_mobile.
	 *
	 * @return UsedCSS_Row|false
	 */
	public function get_used_css( string $url, bool $is_mobile = false ) {
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
	 * Resets retries to 1 and cleans URL cache for retrying the regeneration of the used CSS.
	 *
	 * @return void
	 */
	public function retries_pages_with_unprocessed_css() {
		$used_css_list = $this->get_used_css_with_unprocessed_css();

		foreach ( $used_css_list as $used_css_item ) {
			// Resets retries to 1.
			$updated = $this->used_css_query->update_item(
							$used_css_item->id,
							[ 'retries' => 1 ]
						);
			// Cleans page cache.
			$this->purge->purge_url( $used_css_item->url );
		}
	}
	/**
	 * Get UsedCSS from DB table which has unprocessed CSS files.
	 *
	 * @return array
	 */
	public function get_used_css_with_unprocessed_css() {
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
	 * @param  array $data {
	 *      Data to be saved / updated in database.
	 *
	 *      @type string $url             The page URL.
	 *      @type string $css             The page used css.
	 *      @type string  $unprocessedcss A json_encoded array of the page unprocessed CSS list.
	 *      @type int    $retries         No of automatically retries for generating the unused css.
	 *      @type bool   $is_mobile       Is mobile page.
	 * }
	 *
	 * @return UsedCSS_Row|false
	 */
	public function save_or_update_used_css( array $data ) {
		$used_css = $this->get_used_css( $data['url'], $data['is_mobile'] );

		$data['css'] = preg_replace( '/content\s*:\s*"\\\\f/i', 'shaker-parser:"dashf', $data['css'] );
		$data['css'] = preg_replace( '/content\s*:\s*"\\\\e/i', 'shaker-parser:"dashe', $data['css'] );
		$data['css'] = preg_replace( '/content\s*:\s*\'\\\\f/i', 'shaker-parser:\'dashf', $data['css'] );
		$data['css'] = preg_replace( '/content\s*:\s*\'\\\\e/i', 'shaker-parser:\'dashe', $data['css'] );

		if ( empty( $used_css ) ) {
			return $this->insert_used_css( $data );
		}

		return $this->update_used_css( (int) $used_css->id, $data );
	}

	/**
	 * Insert used CSS.
	 *
	 * @param array $data Data to be inserted in used_css table.
	 *
	 * @return UsedCSS_Row|false
	 */
	public function insert_used_css( array $data ) {
		$id = $this->used_css_query->add_item( $data );
		if ( empty( $id ) ) {
			return false;
		}

		// Save used_css into filesystem.
		$this->save_used_css_in_filesystem( $data['url'], $data['css'], $data['is_mobile'] );

		return $this->used_css_query->get_item( $id );
	}

	/**
	 * Update used CSS.
	 *
	 * @param integer $id   Used CSS ID.
	 * @param array   $data Data to be updated in used_css table.
	 *
	 * @return UsedCSS_Row|false
	 */
	public function update_used_css( int $id, array $data ) {
		$updated = $this->used_css_query->update_item( $id, $data );

		if ( ! $updated ) {
			return false;
		}

		// Save used_css into filesystem.
		$this->save_used_css_in_filesystem( $data['url'], $data['css'], $data['is_mobile'] );

		return $this->used_css_query->get_item( $id );
	}

	/**
	 * Delete used css based on URL.
	 *
	 * @param string $url The page URL.
	 *
	 * @return boolean
	 */
	public function delete_used_css( string $url ) : bool {
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
	 * Alter HTML and remove all CSS which was processed from HTML page.
	 *
	 * @param string $html            HTML content.
	 * @param array  $unprocessed_css List with unprocesses CSS links or inline.
	 *
	 * @return string HTML content.
	 */
	public function remove_used_css_from_html( string $html, array $unprocessed_css ) : string {
		$html_nocomments    = $this->hide_comments( $html );
		$link_styles        = $this->find( '<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );
		$inline_styles      = $this->find( '<style.*>(?<content>.*)<\/style>', $html_nocomments );
		$unprocessed_links  = $this->unprocessed_flat_array( 'link', $unprocessed_css );
		$unprocessed_styles = $this->unprocessed_flat_array( 'inline', $unprocessed_css );

		foreach ( $link_styles as $style ) {
			if (
				! (bool) preg_match( '/rel=[\'"]stylesheet[\'"]/is', $style[0] )
				||
				strstr( $style['url'], '//fonts.googleapis.com/css' )
				||
				in_array( $style['url'], $unprocessed_links, true )
				) {
				continue;
			}
			$html = str_replace( $style[0], '', $html );
		}

		foreach ( $inline_styles as $style ) {
			if ( in_array( $this->strip_line_breaks( $style['content'] ), $unprocessed_styles, true ) ) {
				continue;
			}
			$html = str_replace( $style[0], '', $html );
		}

		return $html;
	}

	/**
	 * Alter HTML string and add the used CSS style in <head> tag,
	 *
	 * @param string $html HTML content.
	 * @param string $used_css Used CSS content.
	 * @param string $url Page Url.
	 * @param bool   $is_mobile For mobile or desktop.
	 *
	 * @return string HTML content.
	 */
	public function add_used_css_to_html( string $html, string $used_css, $url = '', bool $is_mobile = false ) : string {
		return preg_replace(
			'#</title>#iU',
			'</title>' . $this->get_used_css_markup( $used_css, $url, $is_mobile ),
			$html,
			1
		);
	}

	/**
	 * Update UsedCSS Row last_accessed date to current date.
	 *
	 * @param int $id Used CSS id.
	 *
	 * @return bool
	 */
	public function update_last_accessed( int $id ) : bool {
		return (bool) $this->used_css_query->update_item(
			$id,
			[
				'last_accessed' => current_time( 'mysql', true ),
			]
		);
	}

	/**
	 * Hides unwanted blocks from the HTML to be parsed.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	protected function hide_comments( string $html ) : string {
		$html = preg_replace( '#<!--\s*noptimize\s*-->.*?<!--\s*/\s*noptimize\s*-->#is', '', $html );
		$html = preg_replace( '/<!--(.*)-->/Uis', '', $html );

		return $html;
	}

	/**
	 * Create dedicated array of unprocessed css.
	 *
	 * @param string $type            CSS type (link / inline).
	 * @param array  $unprocessed_css Array with unprocessed CSS.
	 *
	 * @return array Array with type of unprocessed CSS.
	 */
	protected function unprocessed_flat_array( string $type, array $unprocessed_css ) : array {
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
	protected function strip_line_breaks( string $value ) : string {
		$value = str_replace( [ "\r", "\n", "\r\n", "\t" ], '', $value );
		return trim( $value );
	}

	/**
	 * Set status of CPCSS option
	 *
	 * @param bool $status Status of CPCSS option.
	 */
	public function set_cpcss_enabled( bool $status ) {
		$this->cpcss_enabled = (bool) $status;
	}

	/**
	 * Save Used CSS into filesystem in case CPCSS is enabled only.
	 *
	 * @param string $url Page Url.
	 * @param string $used_css Used CSS contents.
	 * @param bool   $is_mobile Used CSS for mobile or desktop.
	 *
	 * @return bool
	 */
	private function save_used_css_in_filesystem( string $url, string $used_css, bool $is_mobile = false ) : bool {

		if ( ! $this->cpcss_enabled ) {
			return false;
		}

		$used_css_path = rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' );

		if ( ! rocket_direct_filesystem()->is_dir( $used_css_path ) ) {
			if ( rocket_mkdir_p( $used_css_path ) ) {
				rocket_direct_filesystem()->touch( $used_css_path . 'index.html' );
			}
		}

		$used_css_filepath = rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) . $this->get_used_css_filename( $url, $is_mobile );

		return rocket_put_content( $used_css_filepath, $used_css );
	}

	/**
	 * Get Used CSS filename.
	 *
	 * @param string $url Page Url.
	 * @param bool   $is_mobile For mobile version or desktop.
	 *
	 * @return string
	 */
	private function get_used_css_filename( string $url, bool $is_mobile = false ) : string {
		return md5( $url ) . ( $is_mobile ? '-mobile' : '' ) . '.css';
	}

	/**
	 * Return Markup for used_css into the page.
	 *
	 * @param string $used_css Used CSS contents.
	 * @param string $url Page url.
	 * @param bool   $is_mobile Used CSS for mobile or desktop.
	 *
	 * @return string
	 */
	private function get_used_css_markup( string $used_css, string $url = '', bool $is_mobile = false ) : string {
		if ( ! $this->cpcss_enabled ) {
			return sprintf(
				'<style id="wpr-usedcss">%s</style>',
				wp_strip_all_tags( $used_css )
			);
		}

		$used_css_filename = $this->get_used_css_filename( $url, $is_mobile );
		$used_css_filepath = rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) . $used_css_filename;

		if ( ! rocket_direct_filesystem()->exists( $used_css_filepath ) ) {
			$this->save_used_css_in_filesystem( $url, $used_css, $is_mobile );
		}

		return sprintf(
			'<link rel="stylesheet" id="wpr-usedcss-css" href="%s">', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			rocket_get_constant( 'WP_ROCKET_USED_CSS_URL' ) . $used_css_filename
		);
	}
}
