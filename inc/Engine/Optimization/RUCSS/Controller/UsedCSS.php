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
	 * @param string $url The page URL.
	 *
	 * @return UsedCSS_Row
	 */
	public function get_used_css( string $url ) : UsedCSS_Row {
		return $this->used_css_query->get_item_by( 'url', $url );
	}

	/**
	 * TO DO - alter HTML and remove all CSS which was processed from HTML page.
	 *
	 * @param string $html            HTML content.
	 * @param array  $unprocessed_css List with unprocesses CSS links or inline.
	 *
	 * @return string HTML content.
	 */
	public function remove_used_css_from_html( string $html, array $unprocessed_css ) : string {
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
		return preg_replace( '/<\/title>/i', '$0<style rucss="usedcss" type="text/css">' . $used_css . '</style>', $html, 1 );
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
}
