<?php
namespace WP_Rocket\Engine\Plugin;

use WP_Rocket\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket updates.
 */
class ServiceProvider extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('plugin_updater_common_subscriber'),
			$this->generate_container_id('plugin_information_subscriber'),
			$this->generate_container_id('plugin_updater_subscriber'),
			$this->generate_container_id('plugin_updater_subscriber'),
		];
	}

	public function declare()
	{
		$api_url = wp_parse_url( WP_ROCKET_WEB_INFO );

		$this->register_service('plugin_updater_common_subscriber', function($id) use ($api_url) {
			$this->share( $id, UpdaterApiCommonSubscriber::class )
				->addArgument(
					[
						'api_host'           => $api_url['host'],
						'site_url'           => home_url(),
						'plugin_version'     => WP_ROCKET_VERSION,
						'settings_slug'      => WP_ROCKET_SLUG,
						'settings_nonce_key' => WP_ROCKET_PLUGIN_SLUG,
						'plugin_options'     => $this->getContainer()->get( 'options' ),
					]
				)
				->addTag( 'common_subscriber' );
		});

		$this->register_service('plugin_information_subscriber', function($id) {
			$this->share( $id, InformationSubscriber::class )
				->addArgument(
					[
						'plugin_file' => WP_ROCKET_FILE,
						'api_url'     => WP_ROCKET_WEB_INFO,
					]
				)
				->addTag( 'common_subscriber' );
		});

		$this->register_service('plugin_updater_subscriber', function($id) {
			$this->share( $id, UpdaterSubscriber::class )
				->addArgument(
					[
						'plugin_file'    => WP_ROCKET_FILE,
						'plugin_version' => WP_ROCKET_VERSION,
						'vendor_url'     => WP_ROCKET_WEB_MAIN,
						'api_url'        => WP_ROCKET_WEB_CHECK,
						'icons'          => [
							'2x' => WP_ROCKET_ASSETS_IMG_URL . 'icon-256x256.png',
							'1x' => WP_ROCKET_ASSETS_IMG_URL . 'icon-128x128.png',
						],
					]
				)
				->addTag( 'common_subscriber' );
		});
	}
}
