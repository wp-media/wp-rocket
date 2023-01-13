<?php
namespace WP_Rocket\ServiceProvider;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Engine\Admin\Beacon\ServiceProvider as BeaconServiceProvider;
use WP_Rocket\Engine\CDN\ServiceProvider as CDNServiceProvider;
/**
 * Service provider for WP Rocket features common for admin and front
 *
 * @since 3.3
 */
class Common_Subscribers extends AbstractServiceProvider {

	public function get_common_subscribers(): array
	{
		return [
			$this->generate_container_id('webp_subscriber'),
			$this->generate_container_id('detect_missing_tags_subscriber'),
		];
	}

	public function declare()
	{
		$this->register_service('webp_subscriber', function ($id) {
			$this->share( $id, 'WP_Rocket\Subscriber\Media\Webp_Subscriber' )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $this->get_external( 'options_api' ) )
				->addArgument( $this->get_external( 'cdn_subscriber', CDNServiceProvider::class ) )
				->addArgument( $this->get_external( 'beacon', BeaconServiceProvider::class ) )
				->addTag( 'common_subscriber' );
		});

		$this->register_service('detect_missing_tags_subscriber', function ($id) {
			$this->share( $id, 'WP_Rocket\Subscriber\Tools\Detect_Missing_Tags_Subscriber' )
				->addTag( 'common_subscriber' );
		});
	}
}
