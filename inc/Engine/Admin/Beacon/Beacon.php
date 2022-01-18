<?php

namespace WP_Rocket\Engine\Admin\Beacon;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Helpscout Beacon integration
 *
 * @since  3.2
 */
class Beacon extends Abstract_Render implements Subscriber_Interface {
	/**
	 * Options_Data instance
	 *
	 * @since  3.2
	 *
	 * @var Options_Data $options
	 */
	private $options;

	/**
	 * Current user locale
	 *
	 * @since  3.2
	 *
	 * @var string $locale
	 */
	private $locale;

	/**
	 * Support data instance
	 *
	 * @var Data
	 */
	private $support_data;

	/**
	 * Constructor
	 *
	 * @since 3.2
	 *
	 * @param Options_Data $options       Options instance.
	 * @param string       $template_path Absolute path to the views/settings.
	 * @param Data         $support_data  Support data instance.
	 */
	public function __construct( Options_Data $options, $template_path, Data $support_data ) {
		parent::__construct( $template_path );

		$this->options      = $options;
		$this->support_data = $support_data;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.2
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_print_footer_scripts-settings_page_wprocket' => 'insert_script',
		];
	}

	/**
	 * Configures and returns beacon javascript
	 *
	 * @since  3.2
	 *
	 * @return void
	 */
	public function insert_script() {
		if (
			rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' )
			||
			! current_user_can( 'rocket_manage_options' )
		) {
			return;
		}

		switch ( $this->get_user_locale() ) {
			case 'fr':
				$form_id = '9db9417a-5e2f-41dd-8857-1421d5112aea';
				break;
			default:
				$form_id = '44cc73fb-7636-4206-b115-c7b33823551b';
				break;
		}

		$data = [
			'form_id'  => $form_id,
			'identify' => wp_json_encode( $this->identify_data() ),
			'session'  => wp_json_encode( $this->support_data->get_support_data() ),
			'prefill'  => wp_json_encode( $this->prefill_data() ),
			'config'   => wp_json_encode( $this->config_data() ),
		];

		echo $this->generate( 'beacon', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Sets the locale property with the current user locale if not set yet
	 *
	 * @since  3.5
	 *
	 * @return string
	 */
	private function get_user_locale() {
		if ( ! isset( $this->locale ) ) {
			$this->locale = current( array_slice( explode( '_', get_user_locale() ), 0, 1 ) );
		}

		/**
		 * Filters the locale ID for Beacon
		 *
		 * @since 3.6
		 *
		 * @param string $locale The locale ID.
		 */
		return apply_filters( 'rocket_beacon_locale', $this->locale );
	}

	/**
	 * Returns Identify data to pass to Beacon
	 *
	 * @since  3.0
	 *
	 * @return array
	 */
	private function identify_data() {
		$identify_data = [
			'email'   => $this->options->get( 'consumer_email' ),
			'Website' => home_url(),
		];
		$customer_data = get_transient( 'wp_rocket_customer_data' );

		if ( false !== $customer_data && isset( $customer_data->status ) ) {
			$identify_data['status'] = $customer_data->status;
		}

		return $identify_data;
	}

	/**
	 * Returns prefill data to pass to Beacon
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function prefill_data() {
		$prefill_data = [
			'fields' => [
				[
					'id'    => 21728,
					'value' => 108003, // default to nulled.
				],
			],
		];

		$customer_data = get_transient( 'wp_rocket_customer_data' );

		if ( false === $customer_data || ! isset( $customer_data->licence_account ) ) {
			return $prefill_data;
		}

		$licenses = [
			'Single'      => 108000,
			'Plus'        => 108001,
			'Infinite'    => 108002,
			'Unavailable' => 108003,
		];

		if ( isset( $licenses[ $customer_data->licence_account ] ) ) {
			$prefill_data['fields'][0]['value'] = $licenses[ $customer_data->licence_account ];
		}

		return $prefill_data;
	}

	/**
	 * Returns config data to pass to Beacon
	 *
	 * @since 3.8.5
	 *
	 * @return array
	 */
	private function config_data() : array {
		return [
			'display' => [
				'position' => is_rtl() ? 'left' : 'right',
			],
		];
	}

	/**
	 * Returns the IDs for the HelpScout docs for the corresponding section and language.
	 *
	 * @since  3.0
	 *
	 * @param string $doc_id Section identifier.
	 *
	 * @return string|array
	 */
	public function get_suggest( $doc_id ) {
		$suggest = [
			'faq'                        => [
				'en' => [
					[
						'id'    => '5569b671e4b027e1978e3c51',
						'url'   => 'https://docs.wp-rocket.me/article/99-pages-are-not-cached-or-css-and-js-minification-are-not-working/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Pages Are Not Cached or CSS and JS Minification Are Not Working',
					],
					[
						'id'    => '556778c8e4b01a224b426fad',
						'url'   => 'https://docs.wp-rocket.me/article/85-google-page-speed-grade-does-not-improve/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Google PageSpeed Grade does not Improve',
					],
					[
						'id'    => '556ef48ce4b01a224b428691',
						'url'   => 'https://docs.wp-rocket.me/article/106-my-site-is-broken/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'My Site Is Broken',
					],
					[
						'id'    => '6001a83b2e764327f87bf189',
						'url'   => 'https://docs.wp-rocket.me/article/1407-eliminate-render-blocking-resources/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Eliminate Render Blocking Resources',
					],
					[
						'id'    => '54e6f7e5e4b034c37ea9095f',
						'url'   => 'https://docs.wp-rocket.me/article/46-how-to-check-if-wp-rocket-is-caching-your-pages/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'How to check if WP Rocket is caching your pages',
					],
				],
				'fr' => [
					[
						'id'    => '5697d2dc9033603f7da31041',
						'url'   => 'https://fr.docs.wp-rocket.me/article/264-les-pages-ne-sont-pas-mises-en-cache-ou-la-minification-css-et-js-ne-fonctionne-pas/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Les pages ne sont pas mises en cache, ou la minification CSS et JS ne fonctionne pas',
					],
					[
						'id'    => '569564dfc69791436155e0b0',
						'url'   => 'https://fr.docs.wp-rocket.me/article/218-la-note-google-page-speed-ne-sameliore-pas/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => "La note Google Page Speed ne s'améliore pas",
					],
					[
						'id'    => '5697d03bc69791436155ed69',
						'url'   => 'https://fr.docs.wp-rocket.me/article/263-site-casse/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Mon site est cassé',
					],
					[
						'id'    => '601d4b83ac2f834ec5385ca5',
						'url'   => 'https://fr.docs.wp-rocket.me/article/1440-eliminez-les-ressources-qui-bloquent-le-rendu/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Éliminez les ressources qui bloquent le rendu',
					],
					[
						'id'    => '568fe9ebc69791436155cd32',
						'url'   => 'https://fr.docs.wp-rocket.me/article/180-verifier-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Comment vérifier si WP Rocket met bien en cache vos pages',
					],
				],
			],
			'user_cache_section'         => [
				'en' => '56b55ba49033600da1c0b687,587920b5c697915403a0e1f4,560c66b0c697917e72165a6d',
				'fr' => '56cb9ba990336008e9e9e3d9,5879230cc697915403a0e211,569410999033603f7da2fa94',
			],
			'user_cache'                 => [
				'en' => [
					'id'  => '56b55ba49033600da1c0b687',
					'url' => 'https://docs.wp-rocket.me/article/313-user-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56cb9ba990336008e9e9e3d9',
					'url' => 'https://fr.docs.wp-rocket.me/article/333-cache-utilisateurs-connectes/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'mobile_cache_section'       => [
				'en' => '577a5f1f903360258a10e52a,5678aa76c697914361558e92,5745b9a6c697917290ddc715',
				'fr' => '589b17a02c7d3a784630b249,5a6b32830428632faf6233dc,58a480e5dd8c8e56bfa7b85c',
			],
			'mobile_cache'               => [
				'en' => [
					'id'  => '577a5f1f903360258a10e52a',
					'url' => 'https://docs.wp-rocket.me/article/708-mobile-caching/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '589b17a02c7d3a784630b249',
					'url' => 'https://fr.docs.wp-rocket.me/article/934-mise-en-cache-pour-mobile/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_ssl'                  => [
				'en' => [
					'id'  => '56c24fd3903360436857f1ed',
					'url' => 'https://docs.wp-rocket.me/article/314-using-ssl-with-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56cb9d24c6979102ccfc801c',
					'url' => 'https://fr.docs.wp-rocket.me/article/335-utiliser-ssl-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_lifespan'             => [
				'en' => [
					'id'  => '555c7e9ee4b027e1978e17a5',
					'url' => 'https://docs.wp-rocket.me/article/78-how-often-is-the-cache-updated/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f7df49033603f7da2ec72',
					'url' => 'https://fr.docs.wp-rocket.me/article/171-intervalle-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_lifespan_section'     => [
				'en' => '555c7e9ee4b027e1978e17a5,5922fd0e0428634b4a33552c',
				'fr' => '568f7df49033603f7da2ec72,598080e1042863033a1b890e',
			],
			'nonce'                      => [
				'en' => [
					'id'  => '5922fd0e0428634b4a33552c',
					'url' => 'https://docs.wp-rocket.me/article/975-nonces-and-cache-lifespan/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '598080e1042863033a1b890e',
					'url' => 'https://fr.docs.wp-rocket.me/article/1015-nonces-delai-nettoyage-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'css_section'                => [
				'en' => '556ef48ce4b01a224b428691,6001a83b2e764327f87bf189,5569b671e4b027e1978e3c51,5d5214d10428631e94f94ae6',
				'fr' => '5697d2dc9033603f7da31041,5d5abcce0428634552d85c1c,5697d03bc69791436155ed69,601d4b83ac2f834ec5385ca5',
			],
			'js_section'                 => [
				'en' => '54b9509de4b07997ea3f27c7,59236dfb0428634b4a3358f9,5f359695042863444aa04e26,556ef48ce4b01a224b428691,6001a83b2e764327f87bf189',
				'fr' => '56967eebc69791436155e649,593fe9882c7d3a0747cddb77,5f523c46c9e77c0016384ba0,5697d03bc69791436155ed69,601d4b83ac2f834ec5385ca5',
			],
			'file_optimization'          => [
				'en' => [
					'id'  => '6001a83b2e764327f87bf189',
					'url' => 'https://docs.wp-rocket.me/article/1407-eliminate-render-blocking-resources/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '601d4b83ac2f834ec5385ca5',
					'url' => 'https://fr.docs.wp-rocket.me/article/1440-eliminez-les-ressources-qui-bloquent-le-rendu/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'combine'                    => [
				'en' => [
					'id'  => '596eaf7d2c7d3a73488b3661',
					'url' => 'https://docs.wp-rocket.me/article/1009-configuration-for-http-2/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '59a418ad042863033a1c572e',
					'url' => 'https://fr.docs.wp-rocket.me/article/1018-configuration-http-2/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'remove_unused_css'          => [
				'en' => [
					'id'  => '6076083ff8c0ef2d98df1f97',
					'url' => 'https://docs.wp-rocket.me/article/1529-remove-unused-css?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_inline_js'          => [
				'en' => [
					'id'  => '5b4879100428630abc0c0713',
					'url' => 'https://docs.wp-rocket.me/article/1104-excluding-inline-js-from-combine/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_js'                 => [
				'en' => [
					'id'  => '54b9509de4b07997ea3f27c7',
					'url' => 'https://docs.wp-rocket.me/article/39-excluding-external-js-from-concatenation/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'defer_js'                   => [
				'en' => [
					'id'  => '5d52138d2c7d3a68825e8faa',
					'url' => 'https://docs.wp-rocket.me/article/1265-load-javascript-deferred/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5d5ac08b2c7d3a7920be3649',
					'url' => 'https://fr.​docs.​wp-rocket.​me/article/1270-chargement-differe-des-fichiers-js/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'delay_js'                   => [
				'en' => [
					'id'  => '5f359695042863444aa04e26',
					'url' => 'https://docs.wp-rocket.me/article/1349-delay-javascript-execution/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'delay_js_exclusions'        => [
				'en' => [
					'id'  => '',
					'url' => 'https://docs.wp-rocket.me/article/1560-delay-javascript-execution-compatibility-exclusions/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'async'                      => [
				'en' => [
					'id'  => '5d52144c0428631e94f94ae2',
					'url' => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5d5abada0428634552d85bff',
					'url' => 'https://fr.​docs.​wp-rocket.​me/article/1268-optimiser-le-chargement-du-css/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'lazyload'                   => [
				'en' => [
					'id'  => '5c884cf80428633d2cf38314',
					'url' => 'https://docs.wp-rocket.me/article/1141-using-lazyload-in-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5c98ff532c7d3a1544614cf4',
					'url' => 'https://fr.docs.wp-rocket.me/article/1146-utiliser-lazyload-images-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'webp'                       => [
				'en' => [
					'id'  => '5d72919704286364bc8ed49d',
					'url' => 'https://docs.wp-rocket.me/article/1282-webp',
				],
			],
			'lazyload_section'           => [
				'en' => '5c884cf80428633d2cf38314,54b85754e4b0512429883a86,5418c792e4b0e7b8127bed99,569ec4a69033603f7da32c93,5419e246e4b099def9b5561e',
				'fr' => '56967a859033603f7da30858,56967952c69791436155e60a,56cb9c9d90336008e9e9e3dc,569676ea9033603f7da3083d',
			],
			'sitemap_preload'            => [
				'en' => '541780fde4b005ed2d11784c,5a71c8ab2c7d3a4a4198a9b3,55b282ede4b0b0593824f852',
				'fr' => '5693d582c69791436155d645',
			],
			'preload_bot'                => [
				'en' => '541780fde4b005ed2d11784c,55b282ede4b0b0593824f852,559113eae4b027e1978eba11',
				'fr' => '5693d582c69791436155d645,569433d1c69791436155d99c',
			],
			'bot'                        => [
				'en' => [
					'id'  => '541780fde4b005ed2d11784c',
					'url' => 'https://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5693d582c69791436155d645',
					'url' => 'https://fr.docs.wp-rocket.me/article/188-comment-est-pre-charge-le-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'dns_prefetch'               => [
				'en' => [
					'id'  => '5e055a602c7d3a7e9ae5881c',
					'url' => 'https://docs.wp-rocket.me/article/1302-prefetch-dns-requests/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5e1891892c7d3a7e9ae60983',
					'url' => 'https://fr.docs.wp-rocket.me/article/1303-prechargement-requetes-dns/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'fonts_preload'              => [
				'en' => [
					'id'  => '5eab7729042863474d19f647',
					'url' => 'https://docs.wp-rocket.me/article/1317-preload-fonts/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5eb3add02c7d3a5ea54aa66d',
					'url' => 'https://fr.docs.wp-rocket.me/article/1319-precharger-polices/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'preload_links'              => [
				'en' => [
					'id'  => '5f35939b042863444aa04df9',
					'url' => 'https://docs.wp-rocket.me/article/1348-preload-links/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5f58527cc9e77c001603746c',
					'url' => 'https://fr.docs.wp-rocket.me/article/1358-precharger-les-liens/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'ecommerce'                  => [
				'en' => [
					'id'  => '548f492de4b034fd4862493e',
					'url' => 'https://docs.wp-rocket.me/article/27-using-wp-rocket-on-your-ecommerce-site/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f8291c69791436155caea',
					'url' => 'https://fr.docs.wp-rocket.me/article/176-compatibilite-extensions-e-commerce/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_query_strings'        => [
				'en' => [
					'id'  => '590a83610428634b4a32d52c',
					'url' => 'https://docs.wp-rocket.me/article/971-caching-query-strings/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '597a04fd042863033a1b6da4',
					'url' => 'https://fr.docs.wp-rocket.me/article/1014-cache-query-strings/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_cache'              => [
				'en' => [
					'id'  => '5519ab03e4b061031402119f',
					'url' => 'https://docs.wp-rocket.me/article/54-exclude-pages-from-the-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56941c0cc69791436155d8ab',
					'url' => 'https://fr.docs.wp-rocket.me/article/196-exclure-pages-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_cookie'             => [
				'en' => [
					'id'  => '5fe5462df24ccf588e3fe804',
					'url' => 'https://docs.wp-rocket.me/article/1382-never-cache-cookies/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_user_agent'         => [
				'en' => [
					'id'  => '5ff728d3551e0c2853f3a245',
					'url' => 'https://docs.wp-rocket.me/article/1389-never-cache-user-agents/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'always_purge'               => [
				'en' => [
					'id'  => '5ff72b4dfd168b77735328b7',
					'url' => 'https://docs.wp-rocket.me/article/1391-always-purge-url-s/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'db_optimization'            => [
				'en' => [
					'id'  => '60259156b3ebfb109b58182d',
					'url' => 'https://docs.wp-rocket.me/article/1443-database-optimizations-are-not-working/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '6040c5b90a2dae5b58fb5d29',
					'url' => 'https://fr.docs.wp-rocket.me/article/1486-les-optimisations-de-la-base-de-donnees-ne-fonctionne-pas/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cdn_section'                => [
				'en' => '5e4c84bd04286364bc958833,54c7fa3de4b0512429885b5c,54a6d578e4b047ebb774a687,56b2b4459033603f7da37acf,566f749f9033603f7da28459,5434667fe4b0310ce5ee867a',
				'fr' => '5f351e42042863444aa04652,5696830b9033603f7da308ac,569685749033603f7da308c0,57a4961190336059d4edc9d8,5697d5f8c69791436155ed8e,569684d29033603f7da308b9',
			],
			'cdn'                        => [
				'en' => [
					'id'  => '54c7fa3de4b0512429885b5c',
					'url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5696830b9033603f7da308ac',
					'url' => 'https://fr.docs.wp-rocket.me/article/246-utiliser-wp-rocket-avec-un-cdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'rocketcdn'                  => [
				'en' => [
					'id'  => '5e4c84bd04286364bc958833',
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5f351e42042863444aa04652',
					'url' => 'https://fr.docs.wp-rocket.me/article/1343-comment-utiliser-rocketcdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_cdn'                => [
				'en' => [
					'id'  => '5434667fe4b0310ce5ee867a',
					'url' => 'https://docs.wp-rocket.me/article/24-resolving-issues-with-cdn-and-fonts-icons/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '569684d29033603f7da308b9',
					'url' => 'https://fr.docs.wp-rocket.me/article/248-resoudre-des-problemes-avec-cdn-et-les-polices-icones/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cloudflare_credentials'     => [
				'en' => [
					'id'  => '54205619e4b0e7b8127bf849',
					'url' => 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5696837e9033603f7da308ae',
					'url' => 'https://fr.docs.wp-rocket.me/article/247-utiliser-wp-rocket-avec-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cloudflare_settings'        => [
				'en' => [
					'id'  => '54205619e4b0e7b8127bf849',
					'url' => 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5696837e9033603f7da308ae',
					'url' => 'https://fr.docs.wp-rocket.me/article/247-utiliser-wp-rocket-avec-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cloudflare_credentials_api' => [
				'en' => [
					'id'  => '54205619e4b0e7b8127bf849',
					'url' => 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on',
				],
				'fr' => [
					'id'  => '5696837e9033603f7da308ae',
					'url' => 'https://fr.docs.wp-rocket.me/article/247-utiliser-wp-rocket-avec-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on',
				],
			],
			'sucuri_credentials'         => [
				'en' => [
					'id'  => '5bce07be2c7d3a04dd5bf94d',
					'url' => 'https://docs.wp-rocket.me/article/1120-sucuri-add-on/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5bcf39c72c7d3a4db66085b9',
					'url' => 'https://fr.docs.wp-rocket.me/article/1122-sucuri-add-on/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'varnish'                    => [
				'en' => [
					'id'  => '56f48132c6979115a34095bd',
					'url' => 'https://docs.wp-rocket.me/article/493-using-varnish-with-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56fd2f789033601d6683e574',
					'url' => 'https://fr.docs.wp-rocket.me/article/512-varnish-wp-rocket-2-7/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'heartbeat_settings'         => [
				'en' => [
					'id'  => '5bcdfecd042863158cc7b672',
					'url' => 'https://docs.wp-rocket.me/article/1119-control-wordpress-heartbeat-api/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5bcf4378042863215a46bc00',
					'url' => 'https://fr.docs.wp-rocket.me/article/1124-controler-api-wordpress-heartbeat/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'google_fonts'               => [
				'en' => [
					'id'  => '5e8687c22c7d3a7e9aea4c4a',
					'url' => 'https://docs.wp-rocket.me/article/1312-optimize-google-fonts',
				],
				'fr' => [
					'id'  => '5e970f512c7d3a7e9aeaf9fb',
					'url' => 'https://fr.docs.wp-rocket.me/article/1314-optimiser-les-google-fonts/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'image_dimensions'           => [
				'en' => [
					'id'  => '5fc70216de1bfa158fb54737',
					'url' => 'https://docs.wp-rocket.me/article/1366-add-missing-image-dimensions/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5fd20dcab6c6251cd1c35079',
					'url' => 'https://fr.docs.wp-rocket.me/article/1369-ajouter-les-dimensions-dimage-manquantes/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_defer_js'           => [
				'en' => [
					'id'  => '59236dfb0428634b4a3358f9',
					'url' => 'https://docs.wp-rocket.me/article/976-exclude-files-from-defer-js/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_lazyload'           => [
				'en' => [
					'id'  => '5418c792e4b0e7b8127bed99',
					'url' => 'https://docs.wp-rocket.me/article/15-disabling-lazy-load-on-specific-images/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'invalid_exclusions'         => [
				'en' => [
					'id'  => '619e90a3d3efbe495c3b26b8',
					'url' => 'https://docs.wp-rocket.me/article/1657-invalid-patterns-of-exclusions',
				],
				'fr' => [
					'id'  => '61b21c1297682b790dad345a',
					'url' => 'https://fr.docs.wp-rocket.me/article/1659-motifs-exclusion-non-valables',
				],
			],
		];

		return isset( $suggest[ $doc_id ][ $this->get_user_locale() ] )
			? $suggest[ $doc_id ][ $this->get_user_locale() ]
			: $suggest[ $doc_id ]['en'];
	}
}
