<?php
namespace WP_Rocket\Engine\Media\Lazyload\CSS\Admin;

use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Cache instance.
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * Instantiate class.
	 *
	 * @param CacheInterface $cache Cache instance.
	 */
	public function __construct( CacheInterface $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('hook_name' => 'method_name')
	 *  * array('hook_name' => array('method_name', $priority))
	 *  * array('hook_name' => array('method_name', $priority, $accepted_args))
	 *  * array('hook_name' => array(array('method_name_1', $priority_1, $accepted_args_1)), array('method_name_2', $priority_2, $accepted_args_2)))
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_meta_boxes_fields' => 'add_meta_box',
			'admin_notices'            => 'maybe_add_error_notice',
		];
	}

	/**
	 * Add the field to the metaboxes.
	 *
	 * @param array $fields Metaboxes fields.
	 * @return array
	 */
	public function add_meta_box( array $fields ) {
		$fields['disable_css_bg_img_lazyload'] = __( 'LazyLoad CSS backgrounds', 'rocket' );

		return $fields;
	}

	/**
	 * Maybe display the error notice.
	 *
	 * @return void
	 */
	public function maybe_add_error_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) || $this->cache->is_accessible() ) {
			return;
		}

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => rocket_notice_writing_permissions( $this->cache->get_root_path() ),
			]
			);
	}

}
