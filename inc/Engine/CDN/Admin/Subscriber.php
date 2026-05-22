<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_meta_boxes_fields' => [ 'add_meta_box', 9 ],
		];
	}

	/**
	 * Add the field to the WP Rocket metabox on the post edit page.
	 *
	 * @param string[] $fields Metaboxes fields.
	 *
	 * @return string[]
	 */
	public function add_meta_box( array $fields ) {
		/*
		 * Hiding the CDN option in the metabox for now.
		 * We will revisit this when handling CDN status for different pages/posts.
		 *
		 * $fields['cdn'] = __( 'CDN', 'rocket' );
		 */

		return $fields;
	}
}
