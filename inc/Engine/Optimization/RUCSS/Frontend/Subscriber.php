<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\RUCSS\APIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Subscriber implements Subscriber_Interface {
	/**
	 * APIClient instance
	 *
	 * @var APIClient
	 */
	private $api;

	/**
	 * UsedCss instance
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Instantiate the class
	 *
	 * @param UsedCSS   $used_css Settings instance.
	 * @param APIClient $api      Database instance.
	 */
	public function __construct( UsedCSS $used_css, APIClient $api ) {
		$this->used_css = $used_css;
		$this->api      = $api;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_buffer' => [ 'treeshake', 1 ],
		];
	}

	/**
	 * Apply TreeShaked CSS to the current HTML page.
	 *
	 * @param string $html  HTML content.
	 *
	 * @return string  HTML content.
	 */
	public function treeshake( string $html ) : string {
		global $wp;
		$url      = home_url( $wp->request );
		$used_css = $this->used_css->get_used_css( $url );

		if ( empty( $used_css ) ) {
			// TO DO -> API optimize.
			// TO DO -> save to DB RUCSS result.
			// TO DO -> remove used_css from html.
			// TO DO -> add used css to HTML.

			// $html = $this->api->optimize( $html, [] );

			return $html;
		}

		$html = $this->used_css->remove_used_css_from_html( $html, $used_css->unprocessedcss );
		$html = $this->used_css->add_used_css_to_html( $html, $used_css->css );

		$this->used_css->update_last_accessed( (int) $used_css->id );

		return $html;
	}
}
