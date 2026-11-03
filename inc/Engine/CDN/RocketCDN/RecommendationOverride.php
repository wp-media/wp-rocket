<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Override_Interface;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\ThirdParty\Hostings\HostResolver;

/**
 * Adapts the Rocket Insights RocketCDN recommendation to the site.
 *
 * It points to the Pro upgrade when RocketCDN Free is active and is removed when another CDN is in use or on one.com.
 * Other states keep the copy returned by the API.
 */
class RecommendationOverride implements Override_Interface {

	/**
	 * CDN context.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Subscription controller.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Context                $context                 CDN context.
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 * @param Options_Data           $options                 Options instance.
	 */
	public function __construct( Context $context, SubscriptionController $subscription_controller, Options_Data $options ) {
		$this->context                 = $context;
		$this->subscription_controller = $subscription_controller;
		$this->options                 = $options;
	}

	/**
	 * Gets the slug of the recommendation this override handles.
	 *
	 * @return string
	 */
	public function get_option_slug(): string {
		return 'plugin_rocketcdn';
	}

	/**
	 * Removes the recommendation on one.com hosting or when another CDN is configured with CNAMEs.
	 *
	 * @param array $recommendation Formatted recommendation.
	 * @return bool
	 */
	public function should_remove( array $recommendation ): bool {
		if ( 'onecom' === HostResolver::get_host_service() ) {
			return true;
		}

		return Context::BYOCDN_TYPE === $this->context->get_cdn_state()
			&& ! empty( $this->options->get( 'cdn_cnames', [] ) );
	}

	/**
	 * Adapts the recommendation copy and CTA to the RocketCDN Pro upgrade flow when RocketCDN Free is active.
	 *
	 * @param array $recommendation Formatted recommendation.
	 * @return array
	 */
	public function apply( array $recommendation ): array {
		if ( Context::ROCKETCDN_FREE_TYPE !== $this->context->get_cdn_state() ) {
			return $recommendation;
		}

		$recommendation['title']       = __( 'Upgrade to RocketCDN Pro', 'rocket' );
		$recommendation['description'] = __( 'Speed up your entire site worldwide by delivering content from servers closer to your visitors.', 'rocket' );
		$recommendation['cta_label']   = __( 'Upgrade', 'rocket' );

		$express_url = $this->subscription_controller->get_express_checkout_url();

		$recommendation['cta_url']        = $express_url;
		$recommendation['cta_class']      = 'wpr-rocketcdn-open';
		$recommendation['cta_attributes'] = empty( $express_url ) ? [ 'data-micromodal-trigger' => 'wpr-rocketcdn-modal' ] : [];

		return $recommendation;
	}
}
