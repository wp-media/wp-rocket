<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Controller;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Query\UsedCSS as UsedCSS_Query;

class UsedCSS {
	/**
	 * UsedCss Query instance
	 *
	 * @var UsedCSS_Query
	 */
	private $used_css_query;


	/**
	 * Instantiate the class
	 *
	 * @param UsedCSS_Query $used_css_query UsedCSS Query instance.
	 */
	public function __construct( UsedCSS_Query $used_css_query ) {
		$this->used_css_query = $used_css_query;
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
	 * Insert or update used css row based on URL.
	 *
	 * @param  array $data {
	 *      Data to be saved / updated in database.
	 *
	 *      @type string $url             The page URL.
	 *      @type string $css             The page used css.
	 *      @type array  $unprocessed_css The page unprocessed CSS list.
	 *      @type int    $retries         No of automatically retries for generating the unused css.
	 *      @type bool   $is_mobile       Is mobile page.
	 * }
	 *
	 * @return UsedCSS_Row|false
	 */
	public function save_or_update_used_css( array $data ) {
		$used_css = $this->get_used_css( $data['url'], $data['is_mobile'] );

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
		if ( ! empty( $id ) ) {
			return $this->used_css_query->get_item( $id );
		}
		return false;
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

		if ( $updated ) {
			return $this->used_css_query->get_item( $id );
		}
		return false;
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
			if ( in_array( $style['url'], $unprocessed_links, true ) ) {
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
	 * @param string $html     HTML content.
	 * @param string $used_css Used CSS content.
	 *
	 * @return string HTML content.
	 */
	public function add_used_css_to_html( string $html, string $used_css ) : string {
		return preg_replace(
			'#</title>#iU',
			'</title><style id="rucss-usedcss">' . wp_strip_all_tags( $used_css ) . '</style>',
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
				'last_accessed' => current_time( 'mysql' ),
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
	 * Finds nodes matching the pattern in the HTML.
	 *
	 * @param string $pattern Pattern to match.
	 * @param string $html    HTML content.
	 *
	 * @return bool|array
	 */
	protected function find( string $pattern, string $html ) {
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		return $matches;
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
}
