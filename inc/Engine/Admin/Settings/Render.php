<?php
namespace WP_Rocket\Engine\Admin\Settings;

use stdClass;
use WP_Rocket\Abstract_Render;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;

defined( 'ABSPATH' ) || exit;

/**
 * Handle rendering of HTML content for the settings page.
 *
 * @since 3.5.5 Moves into the new architecture.
 * @since 3.0
 */
class Render extends Abstract_Render {
	/**
	 * Settings array.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * Hidden settings array.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $hidden_settings;

	/**
	 * Plugin family
	 *
	 * @var PluginFamily
	 *
	 * @since 3.17.2
	 */
	protected $plugin_family;

	/**
	 * Creates an instance of the object.
	 *
	 * @param string       $template_path Template path.
	 * @param PluginFamily $plugin_family Plugin Family Instance.
	 */
	public function __construct( string $template_path, PluginFamily $plugin_family ) {
		parent::__construct( $template_path );
		$this->plugin_family = $plugin_family;
	}

	/**
	 * Sets the settings value.
	 *
	 * @since 3.0
	 *
	 * @param array $settings Array of settings.
	 * @return void
	 */
	public function set_settings( $settings ) {
		$this->settings = (array) $settings;
	}

	/**
	 * Sets the hidden settings value.
	 *
	 * @since 3.0
	 *
	 * @param array $hidden_settings Array of hidden settings.
	 */
	public function set_hidden_settings( $hidden_settings ) {
		$this->hidden_settings = $hidden_settings;
	}

	/**
	 * Renders the page sections navigation.
	 *
	 * @since 3.0
	 */
	public function render_navigation() {
		/**
		 * Filters WP Rocket settings page navigation items.
		 *
		 * @since 3.0
		 *
		 * @param array $navigation {
		 *     Items to populate the navigation.
		 *
		 *     @type string $id               Page section identifier.
		 *     @type string $title            Menu item title.
		 *     @type string $menu_description Menu item description.
		 *     @type string $class            Menu item classes
		 *     @type string $group            Identifier of the navigation group the item belongs to. Optional.
		 *     @type string $url              External URL. When set, the item is a link instead of a page. Optional.
		 *     @type string $target           Link target, used with `url`. Optional.
		 * }
		 */
		$navigation = (array) apply_filters( 'rocket_settings_menu_navigation', $this->settings );

		$default = [
			'id'               => '',
			'title'            => '',
			'menu_description' => '',
			'class'            => '',
			'group'            => '',
			'url'              => '',
			'target'           => '',
		];

		$navigation = array_map(
			function ( array $item ) use ( $default ) {
				$item = wp_parse_args( $item, $default );

				if ( ! empty( $item['class'] ) ) {
					$item['class'] = implode( ' ', array_map( 'sanitize_html_class', $item['class'] ) );
				}

				unset( $item['sections'] );
				return $item;
			},
			$navigation
		);

		echo $this->generate( 'navigation', $this->group_navigation( $navigation ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Gets the collapsible navigation groups, in display order.
	 *
	 * Groups only hold other items, they have no page content of their own.
	 *
	 * @since 3.24
	 *
	 * @return array[] {
	 *     Groups keyed by identifier.
	 *
	 *     @type string   $title            Group title.
	 *     @type string   $menu_description Group summary.
	 *     @type string[] $items            Identifiers of the items of the group, in display order.
	 * }
	 */
	private function get_navigation_groups(): array {
		return [
			'cache'             => [
				'title'            => __( 'Cache', 'rocket' ),
				'menu_description' => __( 'Content caching', 'rocket' ),
				'items'            => [ 'preload', 'advanced_cache' ],
			],
			'page_optimization' => [
				'title'            => __( 'Page Optimization', 'rocket' ),
				'menu_description' => __( 'Image, JavaScript & CSS', 'rocket' ),
				'items'            => [ 'media', 'file_optimization' ],
			],
			'backend'           => [
				'title'            => __( 'Backend', 'rocket' ),
				'menu_description' => __( 'Database & Heartbeat', 'rocket' ),
				'items'            => [ 'database', 'heartbeat' ],
			],
			'tools'             => [
				'title'            => __( 'Tools', 'rocket' ),
				'menu_description' => __( 'Tools & Add-ons', 'rocket' ),
				'items'            => [ 'tools', 'addons' ],
			],
			'help'              => [
				'title'            => __( 'Help', 'rocket' ),
				'menu_description' => __( 'Support & resources', 'rocket' ),
				'items'            => [ 'support', 'tutorials', 'documentation', 'plugins' ],
			],
		];
	}

	/**
	 * Arranges the navigation items into the top-level entries of the menu.
	 *
	 * Items belonging to a known group are nested under it. Items without a group, or with an unknown one,
	 * stay standalone, which keeps items added by third parties through `rocket_settings_menu_navigation` working.
	 *
	 * @since 3.24
	 *
	 * @param array[] $navigation Normalized navigation items.
	 *
	 * @return array[] List of entries, each with a `type` of `item` or `group`.
	 */
	private function group_navigation( array $navigation ): array {
		$groups   = $this->get_navigation_groups();
		$children = [];
		$items    = [];

		foreach ( $navigation as $item ) {
			if ( ! empty( $item['group'] ) && isset( $groups[ $item['group'] ] ) ) {
				$children[ $item['group'] ][ $item['id'] ] = $item;
				continue;
			}

			$items[ $item['id'] ] = $item;
		}

		$order = [
			'dashboard',
			'rocket_insights',
			'page_cdn',
			'cache',
			'page_optimization',
			'backend',
			'tools',
			'help',
			'account',
		];

		$entries = [];

		foreach ( $order as $id ) {
			if ( isset( $groups[ $id ], $children[ $id ] ) ) {
				$group  = $groups[ $id ];
				$sorted = [];

				foreach ( $group['items'] as $child_id ) {
					if ( isset( $children[ $id ][ $child_id ] ) ) {
						$sorted[] = $children[ $id ][ $child_id ];
						unset( $children[ $id ][ $child_id ] );
					}
				}

				// Items added to the group by third parties go after the known ones.
				$sorted = array_merge( $sorted, array_values( $children[ $id ] ) );

				$entries[] = [
					'type'             => 'group',
					'id'               => $id,
					'title'            => $group['title'],
					'menu_description' => $group['menu_description'],
					'children'         => $sorted,
				];
				continue;
			}

			if ( isset( $items[ $id ] ) ) {
				$entries[] = [ 'type' => 'item' ] + $items[ $id ];
				unset( $items[ $id ] );
			}
		}

		foreach ( $items as $item ) {
			$entries[] = [ 'type' => 'item' ] + $item;
		}

		return $entries;
	}

	/**
	 * Render the page sections.
	 *
	 * @since 3.0
	 */
	public function render_form_sections() {
		foreach ( $this->settings as $id => $args ) {
			$default = [
				'title'            => '',
				'menu_description' => '',
				'class'            => '',
			];

			$args = wp_parse_args( $args, $default );
			$id   = str_replace( '_', '-', $id );

			echo $this->generate( 'page-sections/' . $id, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
		}
	}

	/**
	 * Renders the Imagify banner at the top of the Image & Media page.
	 *
	 * Renders the enabled banner while the Imagify plugin is active, the promo banner otherwise.
	 * Renders nothing for white label accounts, or when an API key is saved and Imagify is inactive.
	 *
	 * @since 3.24
	 *
	 * @param array $args Array of arguments to populate the template.
	 *
	 * @return void
	 */
	public function render_imagify_banner( $args ) {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( \Imagify_Partner::is_imagify_activated() ) {
			echo $this->generate( 'partials/imagify-banner-enabled', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.

			return;
		}

		// The partner install/activate flow is not available once an API key is saved, same as the former Imagify page.
		if ( \Imagify_Partner::has_imagify_api_key() ) {
			return;
		}

		echo $this->generate( 'partials/imagify-banner', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Returns the HTML of the Dashboard sidebar, one block per category.
	 *
	 * A category without content is not rendered, title included.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	public function get_dashboard_sidebar(): string {
		/**
		 * Filters the License banner of the dashboard sidebar. The first non-empty value wins, so only one banner is displayed.
		 *
		 * @since 3.24
		 *
		 * @param string $html HTML of the License banner.
		 */
		$license = wpm_apply_filters_typed( 'string', 'rocket_dashboard_sidebar_license', '' );

		/**
		 * Filters the Promos banners of the dashboard sidebar.
		 *
		 * @since 3.24
		 *
		 * @param string $html HTML of the Promos banners.
		 */
		$promos = wpm_apply_filters_typed( 'string', 'rocket_dashboard_sidebar_promos', '' );

		/**
		 * Filters the add-on cards of the dashboard sidebar.
		 *
		 * Each callback appends the data of its card, without any HTML: the card is rendered by WP Rocket.
		 *
		 * @since 3.24
		 *
		 * @param array $cards {
		 *     Card data, one array per card.
		 *
		 *     @type array $card {
		 *         @type string   $id           Unique card identifier, 40 characters max (a longer id gets no close button).
		 *         @type string   $title        Card title.
		 *         @type array    $image        {
		 *             @type string $src    Image URL, empty for no image.
		 *             @type string $alt    Image alternative text, empty when decorative.
		 *             @type int    $width  Optional image width, avoids a layout shift.
		 *             @type int    $height Optional image height.
		 *         }
		 *         @type string   $lead         Bold sentence under the image.
		 *         @type string   $text         Text following the lead, next to the "Learn more" toggle.
		 *         @type string[] $list         Benefits shown when the card is expanded. Only <strong> is allowed.
		 *         @type string   $button_label Button label.
		 *         @type string   $button_url   Button URL. When empty, a <button> is rendered if a class or attributes are given.
		 *         @type string   $button_class Optional extra CSS classes for the button.
		 *         @type array    $button_attributes Optional HTML attributes of the button, as attribute name => value.
		 *         @type bool     $is_external  Whether the button opens an external site in a new tab.
		 *     }
		 * }
		 */
		$cards = wpm_apply_filters_typed( 'array', 'rocket_dashboard_sidebar_addons', [] );

		$addons = '';

		foreach ( $cards as $card ) {
			if ( ! is_array( $card ) ) {
				continue;
			}

			if ( $this->is_addon_card_dismissed( (string) ( $card['id'] ?? '' ) ) ) {
				continue;
			}

			$addons .= (string) $this->generate( 'partials/sidebar/addon-card', $card );
		}

		$categories = [
			__( 'License', 'rocket' ) => $license,
			__( 'Promos', 'rocket' )  => $promos,
			__( 'Add-ons', 'rocket' ) => $addons,
		];

		$html = '';

		foreach ( $categories as $title => $content ) {
			if ( '' === trim( $content ) ) {
				continue;
			}

			$html .= (string) $this->generate(
				'partials/sidebar/category',
				[
					'title'   => $title,
					'content' => $content,
				]
			);
		}

		return $html;
	}

	/**
	 * Returns the key under which the dismissal of an add-on card is stored in the user's dismissed boxes.
	 *
	 * @since 3.24
	 *
	 * @param string $id Card identifier.
	 *
	 * @return string
	 */
	public function get_addon_card_box_key( string $id ): string {
		return 'rocket_addon_card_' . sanitize_key( $id );
	}

	/**
	 * Checks if the current user dismissed an add-on card of the Dashboard sidebar.
	 *
	 * @since 3.24
	 *
	 * @param string $id Card identifier.
	 *
	 * @return bool
	 */
	public function is_addon_card_dismissed( string $id ): bool {
		if ( '' === $id ) {
			return false;
		}

		$boxes = (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true );

		return in_array( $this->get_addon_card_box_key( $id ), $boxes, true );
	}

	/**
	 * Returns the data of the Imagify card of the Dashboard sidebar.
	 *
	 * Same rules as render_imagify_banner(): nothing for white label accounts, while Imagify is active,
	 * or when an API key is saved.
	 *
	 * @since 3.24
	 *
	 * @return array Card data, empty when the card must not be displayed.
	 */
	public function get_imagify_addon_card_data(): array {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return [];
		}

		$imagify = $this->get_imagify_partner_data();

		if ( $imagify['activated'] || $imagify['has_api_key'] ) {
			return [];
		}

		return [
			'id'           => 'imagify',
			'title'        => __( 'Reduce image size by 20%', 'rocket' ),
			'image'        => [
				'src'    => rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL' ) . 'wpr-imagify-card.jpg',
				'alt'    => __( 'Comparison between an unoptimized 1.4MB image and the same image optimized with Imagify at 0.4MB', 'rocket' ),
				'width'  => 266,
				'height' => 137,
			],
			'lead'         => __( 'Give your website an extra speed boost', 'rocket' ),
			'text'         => __( 'Images can account for 50% of your loading time.', 'rocket' ),
			'list'         => array_column( $this->get_imagify_benefits(), 'text' ),
			'button_label' => __( 'Install Imagify', 'rocket' ),
			'button_url'   => $imagify['url'],
			'is_external'  => false,
		];
	}

	/**
	 * Returns the Imagify benefits, shared by the Imagify banner and the Imagify card.
	 *
	 * @since 3.24
	 *
	 * @return array[] Benefits, with the image file name and the text (only <strong> tags).
	 */
	public function get_imagify_benefits(): array {
		return [
			[
				'image' => 'wpr-imagify-benefit-1.png',
				// Translators: %1$s = <strong>, %2$s = </strong>.
				'text'  => sprintf( esc_html__( '%1$sCompress all your images%2$s in one click', 'rocket' ), '<strong>', '</strong>' ),
			],
			[
				'image' => 'wpr-imagify-benefit-2.png',
				// Translators: %1$s = <strong>, %2$s = </strong>.
				'text'  => sprintf( esc_html__( '%1$sConvert images%2$s to WebP and Avif', 'rocket' ), '<strong>', '</strong>' ),
			],
			[
				'image' => 'wpr-imagify-benefit-3.png',
				// Translators: %1$s = <strong>, %2$s = </strong>.
				'text'  => sprintf( esc_html__( '%1$sResize your image%2$s on the fly', 'rocket' ), '<strong>', '</strong>' ),
			],
			[
				'image' => 'wpr-imagify-benefit-4.png',
				// Translators: %1$s = <strong>, %2$s = </strong>.
				'text'  => sprintf( esc_html__( '%1$sFree plan includes 20MB/month%2$s (around 200 images)', 'rocket' ), '<strong>', '</strong>' ),
			],
		];
	}

	/**
	 * Returns the Imagify partner state used by the Imagify card.
	 *
	 * @since 3.24
	 *
	 * @return array {
	 *     @type bool   $activated   Whether Imagify is active.
	 *     @type bool   $has_api_key Whether an Imagify API key is saved.
	 *     @type string $url         Post install URL of the Imagify partner flow (installs when needed, then activates).
	 * }
	 */
	protected function get_imagify_partner_data(): array {
		return [
			'activated'   => (bool) \Imagify_Partner::is_imagify_activated(),
			'has_api_key' => (bool) \Imagify_Partner::has_imagify_api_key(),
			'url'         => (string) ( new \Imagify_Partner( 'wp-rocket' ) )->get_post_install_url(),
		];
	}

	/**
	 * Render the Tutorials page section.
	 *
	 * @since 3.4
	 */
	public function render_tutorials_section() {
		echo $this->generate( 'page-sections/tutorials' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Render the tools page section.
	 *
	 * @param array $data Data to pass to the template.
	 *
	 * @since 3.0
	 */
	public function render_tools_section( $data = [] ) {
		echo $this->generate( 'page-sections/tools', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Render the plugins page section.
	 *
	 * @since 3.17.2
	 */
	public function render_plugin_section() {
		$plugin_family = $this->plugin_family->get_filtered_plugins( 'wp-rocket/wp-rocket' );

		$data = $plugin_family['categorized'];

		echo $this->generate( 'page-sections/plugins', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Renders the settings sections for a page section.
	 *
	 * @since 3.0
	 *
	 * @param string $page Page section identifier.
	 * @return void
	 */
	public function render_settings_sections( $page ) {
		if ( ! isset( $this->settings[ $page ]['sections'] ) ) {
			return;
		}

		foreach ( $this->settings[ $page ]['sections'] as $args ) {
			$default = [
				'type'        => 'fields_container',
				'title'       => '',
				'description' => '',
				'class'       => '',
				'help'        => '',
				'helper'      => '',
				'page'        => '',
			];

			$args = wp_parse_args( $args, $default );

			if ( ! empty( $args['class'] ) ) {
				$args['class'] = implode( ' ', array_map( 'sanitize_html_class', $args['class'] ) );
			}

			call_user_func_array( [ $this, $args['type'] ], [ $args ] );
		}
	}

	/**
	 * Renders the settings fields for a setting section and page.
	 *
	 * @since 3.0
	 *
	 * @param string $page    Page section identifier.
	 * @param string $section Settings section identifier.
	 * @return void
	 */
	public function render_settings_fields( $page, $section ) {
		if ( ! isset( $this->settings[ $page ]['sections'][ $section ]['fields'] ) ) {
			return;
		}
		$this->render_fields( $this->settings[ $page ]['sections'][ $section ]['fields'] );
	}

	/**
	 * Renders hidden fields in the form.
	 *
	 * @since 3.0
	 */
	public function render_hidden_fields() {
		foreach ( $this->hidden_settings as $setting ) {
			call_user_func_array( [ $this, 'hidden' ], [ $setting ] );
		}
	}

	/**
	 * Displays the fields container section template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function fields_container( $args ) {
		echo $this->generate( 'sections/fields-container', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the no container section template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function nocontainer( $args ) {
		echo $this->generate( 'sections/nocontainer', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the add-ons container section template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function addons_container( $args ) {
		echo $this->generate( 'sections/addons-container', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the Your Own CDN section template.
	 *
	 * This is a section for configuring your own CDN settings.
	 * It includes the PAUSE CDN button.
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function your_own_cdn( $args ) {
		echo $this->generate( 'sections/cdn/your-own-cdn', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the RocketCDN Free section template.
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function rocketcdn_free( $args ) {
		echo $this->generate( 'sections/cdn/rocketcdn-free', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the RocketCDN Paid section template.
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function rocketcdn_paid( $args ) {
		echo $this->generate( 'sections/cdn/rocketcdn-paid', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the purge CDN cache section template.
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function purge_cdn_cache_section( $args ) {
		echo $this->generate( 'sections/cdn/purge-cdn-cache', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the no container with title section template.
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function nocontainer_with_title( $args ) {
		echo $this->generate( 'sections/nocontainer-with-title', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the text field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function text( $args ) {
		echo $this->generate( 'fields/text', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the checkbox field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function checkbox( $args ) {
		echo $this->generate( 'fields/checkbox', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the textarea field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function textarea( $args ) {
		if ( is_array( $args['value'] ) ) {
			$args['value'] = implode( "\n", $args['value'] );
		}

		$args['value'] = empty( $args['value'] ) ? '' : $args['value'];

		echo $this->generate( 'fields/textarea', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the textarea with container field template.
	 *
	 * @since 3.22
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function textarea_with_container( $args ) {
		if ( is_array( $args['value'] ) ) {
			$args['value'] = implode( "\n", $args['value'] );
		}

		$args['value'] = empty( $args['value'] ) ? '' : $args['value'];

		echo $this->generate( 'fields/textarea-with-container', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the sliding checkbox field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function sliding_checkbox( $args ) {
		echo $this->generate( 'fields/sliding-checkbox', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the number input field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function number( $args ) {
		echo $this->generate( 'fields/number', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the multiselect field template.
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function categorized_multiselect( $args ) {
		$args['items']    = empty( $args['items'] ) ? new stdClass() : $args['items'];
		$args['selected'] = get_rocket_option( sanitize_key( $args['id'] ), [] );

		echo $this->generate( 'fields/categorized_multiselect', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the select field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function select( $args ) {
		echo $this->generate( 'fields/select', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the clear cache lifespan block template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function cache_lifespan( $args ) {
		echo $this->generate( 'fields/cache-lifespan', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the hidden field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function hidden( $args ) {
		if ( is_array( $args['value'] ) ) {
			$args['value'] = implode( "\n", $args['value'] );
		}

		echo $this->generate( 'fields/hidden', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the CDN CNAMES template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function cnames( $args ) {
		echo $this->generate( 'fields/cnames', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the RocketCDN template.
	 *
	 * @since 3.5
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function rocket_cdn( $args ) {
		echo $this->generate( 'fields/rocket-cdn', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the one-click add-on field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function one_click_addon( $args ) {
		echo $this->generate( 'fields/one-click-addon', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the Rocket add-on field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function rocket_addon( $args ) {
		echo $this->generate( 'fields/rocket-addon', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the import form template.
	 *
	 * @since 3.0
	 */
	public function render_import_form() {
		$args = [];

		/**
		 * Filter the maximum allowed upload size for import files.
		 *
		 * @since (WordPress) 2.3.0
		 *
		 * @see wp_max_upload_size()
		 *
		 * @param int $max_upload_size Allowed upload size. Default 1 MB.
		 */
		$args['bytes']       = apply_filters( 'import_upload_size_limit', wp_max_upload_size() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$args['size']        = size_format( $args['bytes'] );
		$args['upload_dir']  = wp_upload_dir();
		$args['action']      = 'rocket_import_settings';
		$args['submit_text'] = __( 'Upload file and import settings', 'rocket' );

		echo $this->generate( 'fields/import-form', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the radio_buttons field template.
	 *
	 * @since 3.10
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function radio_buttons( $args ) {
		echo $this->generate( 'fields/radio-buttons', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Renders the fields.
	 *
	 * @since 3.10
	 *
	 * @param array $fields   fields to render.
	 *
	 * @return void
	 */
	public function render_fields( $fields ) {

		foreach ( $fields as $id => $args ) {
			$default = [
				'type'              => 'text',
				'label'             => '',
				'description'       => '',
				'class'             => '',
				'container_class'   => '',
				'default'           => '',
				'helper'            => '',
				'placeholder'       => '',
				'parent'            => '',
				'section'           => '',
				'page'              => '',
				'sanitize_callback' => 'sanitize_text_field',
				'input_attr'        => '',
				'warning'           => [],
			];

			$args = wp_parse_args( $args, $default );

			if ( empty( $args['id'] ) ) {
				$args['id'] = $id;
			}

			if ( ! empty( $args['input_attr'] ) ) {
				$input_attr = '';

				foreach ( $args['input_attr'] as $key => $value ) {
					if ( 'disabled' === $key ) {
						if ( 1 === $value ) {
							$input_attr .= ' disabled';
						}

						continue;
					}

					$input_attr .= ' ' . sanitize_key( $key ) . '="' . esc_attr( $value ) . '"';
				}

				$args['input_attr'] = $input_attr;
			}

			if ( ! empty( $args['parent'] ) ) {
				$args['parent'] = ' data-parent="' . esc_attr( $args['parent'] ) . '"';
			}

			if ( ! empty( $args['class'] ) ) {
				$args['class'] = implode( ' ', array_map( 'sanitize_html_class', $args['class'] ) );
			}

			if ( ! empty( $args['container_class'] ) ) {
				$args['container_class'] = implode( ' ', array_map( 'sanitize_html_class', $args['container_class'] ) );
			}

			call_user_func_array( [ $this, $args['type'] ], [ $args ] );
		}
	}
}
