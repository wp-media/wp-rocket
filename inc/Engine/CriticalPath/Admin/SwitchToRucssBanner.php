<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CriticalPath\Admin;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Displays the "switch to Remove Unused CSS" banner in the License category of the Dashboard sidebar.
 */
class SwitchToRucssBanner extends Abstract_Render implements Subscriber_Interface {
	/**
	 * Key of the dismissal stored in the rocket_boxes user meta.
	 *
	 * @var string
	 */
	const DISMISS_KEY = 'switch_to_rucss_notice';

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * User instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options       Options instance.
	 * @param User         $user          User instance.
	 * @param string       $template_path Path to the templates folder.
	 */
	public function __construct( Options_Data $options, User $user, $template_path ) {
		parent::__construct( $template_path );

		$this->options = $options;
		$this->user    = $user;
	}

	/**
	 * Events this subscriber listens to.
	 *
	 * The lowest priority of the License category: the banner shows only when no license banner applies.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_dashboard_sidebar_license' => [ 'add_banner', 14 ],
		];
	}

	/**
	 * Adds the banner to the License category of the Dashboard sidebar.
	 *
	 * The first non-empty banner wins, so only one License banner is displayed.
	 *
	 * @since 3.24
	 *
	 * @param string $html Current HTML of the category.
	 *
	 * @return string
	 */
	public function add_banner( $html ): string {
		if ( '' !== trim( (string) $html ) ) {
			return $html;
		}

		return $this->get_banner();
	}

	/**
	 * Returns the banner HTML, or an empty string when it must not be displayed: white label accounts,
	 * dismissed by the user, Load CSS asynchronously off, expired license, or Remove Unused CSS disabled.
	 *
	 * @return string
	 */
	private function get_banner(): string {
		// Like the other License banners: white label hides it.
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return '';
		}

		$boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		if ( in_array( self::DISMISS_KEY, (array) $boxes, true ) ) {
			return '';
		}

		if ( ! $this->options->get( 'async_css', 0 ) ) {
			return '';
		}

		if ( $this->user->is_license_expired() ) {
			return '';
		}

		/**
		 * Filters the status of the RUCSS option.
		 *
		 * @param array $should_disable will return array with disable status and text.
		 */
		$rucss_status = wpm_apply_filters_typed(
			'array',
			'rocket_disable_rucss_setting',
			[
				'disable' => false,
				'text'    => '',
			]
		);

		if ( key_exists( 'disable', $rucss_status ) && $rucss_status['disable'] ) {
			return '';
		}

		return (string) $this->generate(
			'switch-to-rucss-banner',
			[
				'message'     => sprintf(
					// translators: %1$ = opening bold tag, %2$ = closing bold tag.
					__( 'We highly recommend the %1$supdated Remove Unused CSS%2$s for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.', 'rocket' ),
					'<b>',
					'</b>'
				),
				'switch_url'  => add_query_arg( [ 'action' => 'switch_to_rucss' ], wp_nonce_url( admin_url( 'admin-post.php' ), 'rucss_switch' ) ),
				'dismiss_url' => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box=' . self::DISMISS_KEY ), 'rocket_ignore_' . self::DISMISS_KEY ),
			]
		);
	}
}
