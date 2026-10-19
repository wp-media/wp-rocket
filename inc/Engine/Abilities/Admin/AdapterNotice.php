<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities\Admin;

use WP_Rocket\Engine\Abilities\Context;

class AdapterNotice {
	const BOX_ID = 'mcp_adapter_notice';

	/**
	 * Abilities context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param Context $context Abilities context instance.
	 */
	public function __construct( Context $context ) {
		$this->context = $context;
	}

	/**
	 * Displays the notice asking existing MCP users to install the MCP Adapter plugin.
	 *
	 * @return void
	 */
	public function display(): void {
		if ( ! $this->should_display() ) {
			return;
		}

		rocket_notice_html(
			[
				'status'         => 'info',
				'dismissible'    => '',
				'message'        => sprintf(
					// translators: %1$s = opening link tag, %2$s = closing link tag.
					__( 'WP Rocket no longer bundles the MCP Adapter, which is now available as a standalone plugin on WordPress.org. To keep using MCP with WP Rocket, please install and activate the %1$sMCP Adapter plugin%2$s.', 'rocket' ),
					'<a href="' . esc_url( admin_url( 'plugin-install.php?s=mcp-adapter&tab=search&type=term' ) ) . '">',
					'</a>'
				),
				'dismiss_button' => self::BOX_ID,
			]
		);
	}

	/**
	 * Checks if the notice should be displayed to the current user.
	 *
	 * @return bool
	 */
	public function should_display(): bool {
		if ( ! $this->context->is_enabled() ) {
			return false;
		}

		if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		if ( $this->is_adapter_loaded() ) {
			return false;
		}

		$user_id = get_current_user_id();

		if ( ! $this->is_mcp_user( $user_id ) ) {
			return false;
		}

		return ! in_array( self::BOX_ID, (array) get_user_meta( $user_id, 'rocket_boxes', true ), true );
	}

	/**
	 * Checks if an MCP Adapter is loaded, from the standalone plugin or another bundled copy.
	 *
	 * @return bool
	 */
	protected function is_adapter_loaded(): bool {
		return class_exists( 'WP\MCP\Core\McpAdapter' );
	}

	/**
	 * Checks if the user has used MCP: adapter sessions or an MCP OAuth connection.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool
	 */
	private function is_mcp_user( int $user_id ): bool {
		$meta = get_user_meta( $user_id );

		if ( ! is_array( $meta ) ) {
			return false;
		}

		$session_keys = [ 'mcp_adapter_sessions', 'mcp_adapter_sessions_' . get_current_blog_id() ];

		foreach ( $meta as $key => $values ) {
			$key = (string) $key;

			if ( 0 === strpos( $key, 'mcp_refresh_jti_' ) ) {
				return true;
			}

			if ( in_array( $key, $session_keys, true ) && ! empty( maybe_unserialize( $values[0] ?? '' ) ) ) {
				return true;
			}
		}

		return false;
	}
}
