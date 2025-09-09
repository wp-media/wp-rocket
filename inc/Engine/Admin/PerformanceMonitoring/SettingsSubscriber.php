<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Handle Add-On license status display
 *
 * @since 3.20
 */
class SettingsSubscriber extends Abstract_Render implements Subscriber_Interface {
    /**
     * User API client
     *
     * @var User
     */
    private $user;

    /**
     * Instantiate the class
     *
     * @param User   $user          User API client.
     * @param string $template_path Path to the templates.
     */
    public function __construct(User $user, $template_path) {
        parent::__construct($template_path);
        $this->user = $user;
    }

    /**
     * Events this subscriber listens to
     *
     * @return array
     */
    public static function get_subscribed_events() {
        return [
            'rocket_dashboard_after_account_data' => ['display_addon_status', 20], // Higher priority than RocketCDN
        ];
    }

    /**
     * Displays the Add-On license status on the dashboard tab
     *
     * @since 3.20
     *
     * @return void
     */
    public function display_addon_status() {
        // Don't display for white label accounts
        if ((bool) rocket_get_constant('WP_ROCKET_WHITE_LABEL_ACCOUNT')) {
            return;
        }

		$container_class = ' wpr-flex--egal';
		$status_class    = ' wpr-isInvalid';
        $label           = '';
		$status_text     = __('No subscription', 'rocket');
        $service_name    = __('Rocket Insights', 'rocket');
		$sku = $this->user->get_pma_addon_sku_active();



		$is_active = $this->user->is_pma_addon_active($sku);

		$upgrade_link    = $this->user->get_pma_addon_btn_url($sku);

        if ($is_active) {
            $label        = __('Expiration Date', 'rocket');
            $status_class = ' wpr-isValid';
            $status_text  = date_i18n(get_option('date_format'), $this->user->get_pma_license_expiration());
		}

        $data = [
            'is_live_site'    => rocket_is_live_site(),
            'container_class' => $container_class,
            'label'           => $label,
            'status_class'    => $status_class,
            'status_text'     => $status_text,
            'is_active'       => $is_active,
            'service_name'    => $service_name,
        ];

		if($upgrade_link) {
			$data['upgrade_link'] = $upgrade_link;
			$data['upgrade_text'] = $this->user->get_pma_addon_btn_text($sku);
		}

        echo $this->generate('dashboard-addon-status', $data);
    }
}
