<?php
namespace WP_Rocket\Admin\Settings;

use WP_Rocket\Admin\Options_Data;

/**
 * Helpscout Beacon integration
 *
 * @since 3.2
 * @author Remy Perona
 */
class Beacon {
	/**
	 * Options_Data instance
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Options_Data $options
	 */
	private $options;

	/**
	 * Current user locale
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var string $locale
	 */
	private $locale;

	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
		$this->locale  = current( array_slice( explode( '_', get_user_locale() ), 0, 1 ) );
	}

	/**
	 * Configures and returns beacon javascript
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function insert_script() {
		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		switch ( $this->locale ) {
			case 'fr':
				$form_id = '9db9417a-5e2f-41dd-8857-1421d5112aea';
				break;
			default:
				$form_id = '44cc73fb-7636-4206-b115-c7b33823551b';
				break;
		}

		return '<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
			<script type="text/javascript">window.Beacon(\'init\', \'' . $form_id . '\')</script>
			<script>window.Beacon("identify", ' . wp_json_encode( $this->identify_data() ) . ');</script>
			<script>window.Beacon("session-data", ' . wp_json_encode( $this->session_data() ) . ');</script>
			<script>window.addEventListener("hashchange", function () {
				window.Beacon("suggest");
			  }, false);</script>';
	}

	/**
	 * Returns Session specific data to pass to Beacon
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function session_data() {
		global $wp_version;

		$options_to_send = [
			'cache_mobile'            => 'Mobile Cache',
			'do_caching_mobile_files' => 'Specific Cache for Mobile',
			'cache_logged_user'       => 'User Cache',
			'emoji'                   => 'Disable Emojis',
			'embeds'                  => 'Disable Embeds',
			'defer_all_js'            => 'Defer JS',
			'defer_all_js_safe'       => 'Defer JS Safe',
			'async_css'               => 'Optimize CSS Delivery',
			'lazyload'                => 'Lazyload Images',
			'lazyload_iframes'        => 'Lazyload Iframes',
			'lazyload_youtube'        => 'Lazyload Youtube',
			'minify_css'              => 'Minify CSS',
			'minify_concatenate_css'  => 'Combine CSS',
			'minify_js'               => 'Minify JS',
			'minify_concatenate_js'   => 'Combine JS',
			'minify_google_fonts'     => 'Combine Google Fonts',
			'minify_html'             => 'Minify HTML',
			'manual_preload'          => 'Preload',
			'sitemap_preload'         => 'Sitemap Preload',
			'remove_query_strings'    => 'Remove Query Strings',
			'cdn'                     => 'CDN Enabled',
			'do_cloudflare'           => 'Cloudflare Enabled',
			'varnish_auto_purge'      => 'Varnish Purge Enabled',
			'google_analytics_cache'  => 'Google Tracking Add-on',
			'facebook_pixel_cache'    => 'Facebook Tracking Add-on',
			'control_heartbeat'       => 'Hearbeat Control',
			'sucury_waf_cache_sync'   => 'Sucuri Add-on',
		];

		$active_options = array_filter( $this->options->get_options() );
		$active_options = array_intersect_key( $options_to_send, $active_options );
		$theme          = wp_get_theme();

		return [
			'Website'                  => home_url(),
			'WordPress Version'        => $wp_version,
			'WP Rocket Version'        => WP_ROCKET_VERSION,
			'Theme'                    => $theme->get( 'Name' ),
			'Plugins Enabled'          => substr( implode( ' - ', rocket_get_active_plugins() ), 0, 200 ),
			'WP Rocket Active Options' => implode( ' - ', $active_options ),
		];
	}

	/**
	 * Returns Identify data to pass to Beacon
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function identify_data() {
		return [
			'email'   => $this->options->get( 'consumer_email' ),
			'Website' => home_url(),
		];
	}

	/**
	 * Returns the IDs for the HelpScout docs for the corresponding section and language.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $doc_id Section identifier.
	 * @return string|array
	 */
	public function get_suggest( $doc_id ) {
		$suggest = [
			'faq'                    => [
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
						'id'    => '54205957e4b099def9b55df0',
						'url'   => 'https://docs.wp-rocket.me/article/19-resolving-issues-with-file-optimization/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Resolving Issues with File Optimization',
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
						'id'    => '56967d73c69791436155e637',
						'url'   => 'https://fr.docs.wp-rocket.me/article/241-problemes-minification/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => "Résoudre les problèmes avec l'optimisation des fichiers",
					],
				],
			],
			'user_cache_section'     => [
				'en' => '56b55ba49033600da1c0b687,587920b5c697915403a0e1f4,560c66b0c697917e72165a6d',
				'fr' => '56cb9ba990336008e9e9e3d9,5879230cc697915403a0e211,569410999033603f7da2fa94',
			],
			'user_cache'             => [
				'en' => [
					'id'  => '56b55ba49033600da1c0b687',
					'url' => 'https://docs.wp-rocket.me/article/313-user-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56cb9ba990336008e9e9e3d9',
					'url' => 'https://fr.docs.wp-rocket.me/article/333-cache-utilisateurs-connectes/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'mobile_cache_section'   => [
				'en' => '577a5f1f903360258a10e52a,5678aa76c697914361558e92,5745b9a6c697917290ddc715',
				'fr' => '589b17a02c7d3a784630b249,5a6b32830428632faf6233dc,58a480e5dd8c8e56bfa7b85c',
			],
			'mobile_cache'           => [
				'en' => [
					'id'  => '577a5f1f903360258a10e52a',
					'url' => 'https://docs.wp-rocket.me/article/708-mobile-caching/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '589b17a02c7d3a784630b249',
					'url' => 'https://fr.docs.wp-rocket.me/article/934-mise-en-cache-pour-mobile/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_ssl'              => [
				'en' => [
					'id'  => '56c24fd3903360436857f1ed',
					'url' => 'https://docs.wp-rocket.me/article/314-using-ssl-with-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56cb9d24c6979102ccfc801c',
					'url' => 'https://fr.docs.wp-rocket.me/article/335-utiliser-ssl-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_lifespan'         => [
				'en' => [
					'id'  => '555c7e9ee4b027e1978e17a5',
					'url' => 'https://docs.wp-rocket.me/article/78-how-often-is-the-cache-updated/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f7df49033603f7da2ec72',
					'url' => 'https://fr.docs.wp-rocket.me/article/171-intervalle-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_lifespan_section' => [
				'en' => '555c7e9ee4b027e1978e17a5,5922fd0e0428634b4a33552c',
				'fr' => '568f7df49033603f7da2ec72,598080e1042863033a1b890e',
			],
			'nonce'                  => [
				'en' => [
					'id'  => '5922fd0e0428634b4a33552c',
					'url' => 'https://docs.wp-rocket.me/article/975-nonces-and-cache-lifespan/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '598080e1042863033a1b890e',
					'url' => 'https://fr.docs.wp-rocket.me/article/1015-nonces-delai-nettoyage-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'basic_section'          => [
				'en' => '55231415e4b0221aadf25676,588286b32c7d3a4a60b95b6c,58869c492c7d3a7846303a3d',
				'fr' => '569568269033603f7da30334,58e3be72dd8c8e5c57311c6e,59b7f049042863033a1cc5d0',
			],
			'css_section'            => [
				'en' => '54205957e4b099def9b55df0,5419ec47e4b099def9b5565f,5578cfbbe4b027e1978e6bb1,5569b671e4b027e1978e3c51,5923772c2c7d3a074e8ab8b9',
				'fr' => '56967d73c69791436155e637,56967e80c69791436155e646,56957209c69791436155e0f6,5697d2dc9033603f7da31041593fec6d2c7d3a0747cddb93',
			],
			'js_section'             => [
				'en' => '54205957e4b099def9b55df0,5419ec47e4b099def9b5565f,5578cfbbe4b027e1978e6bb1,587904cf90336009736c678e,54b9509de4b07997ea3f27c7,59236dfb0428634b4a3358f9',
				'fr' => '56967d73c69791436155e637,56967e80c69791436155e646,56957209c69791436155e0f6,58a337c12c7d3a576d352cde,56967eebc69791436155e649,593fe9882c7d3a0747cddb77',
			],
			'remove_query_strings'   => [
				'en' => [
					'id'  => '55231415e4b0221aadf25676',
					'url' => 'https://docs.wp-rocket.me/article/56-remove-query-string-from-static-resources/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '569568269033603f7da30334',
					'url' => 'https://fr.docs.wp-rocket.me/article/219-supprimer-les-chaines-de-requetes-sur-les-ressources-statiques/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'file_optimization'      => [
				'en' => [
					'id'  => '54205957e4b099def9b55df0',
					'url' => 'https://docs.wp-rocket.me/article/19-resolving-issues-with-file-optimization/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56967d73c69791436155e637',
					'url' => 'https://fr.docs.wp-rocket.me/article/241-problemes-minification/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'combine'                => [
				'en' => [
					'id'  => '596eaf7d2c7d3a73488b3661',
					'url' => 'https://docs.wp-rocket.me/article/1009-configuration-for-http-2/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '59a418ad042863033a1c572e',
					'url' => 'https://fr.docs.wp-rocket.me/article/1018-configuration-http-2/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_inline_js'      => [
				'en' => [
					'id'  => '5b4879100428630abc0c0713',
					'url' => 'https://docs.wp-rocket.me/article/1104-excluding-inline-js-from-combine/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_js'             => [
				'en' => [
					'id'  => '54b9509de4b07997ea3f27c7',
					'url' => 'https://docs.wp-rocket.me/article/39-excluding-external-js-from-concatenation/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'defer'                  => [
				'en' => [
					'id'  => '5578cfbbe4b027e1978e6bb1',
					'url' => 'https://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56957209c69791436155e0f6',
					'url' => 'https://fr.docs.wp-rocket.me/article/230-javascript-et-css-bloquant-le-rendu-pagespeed/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'lazyload'               => [
				'en' => [
					'id'  => '5c884cf80428633d2cf38314',
					'url' => 'https://docs.wp-rocket.me/article/1141-using-lazyload-in-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5c98ff532c7d3a1544614cf4',
					'url' => 'https://fr.docs.wp-rocket.me/article/1146-utiliser-lazyload-images-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'lazyload_section'       => [
				'en' => '5c884cf80428633d2cf38314,54b85754e4b0512429883a86,5418c792e4b0e7b8127bed99,569ec4a69033603f7da32c93,5419e246e4b099def9b5561e,5a299b332c7d3a1a640cb402',
				'fr' => '56967a859033603f7da30858,56967952c69791436155e60a,56cb9c9d90336008e9e9e3dc,569676ea9033603f7da3083d,5a3a66f52c7d3a1943676524',
			],
			'sitemap_preload'        => [
				'en' => '541780fde4b005ed2d11784c,5a71c8ab2c7d3a4a4198a9b3,55b282ede4b0b0593824f852',
				'fr' => '5693d582c69791436155d645',
			],
			'preload_bot'            => [
				'en' => '541780fde4b005ed2d11784c,55b282ede4b0b0593824f852,559113eae4b027e1978eba11',
				'fr' => '5693d582c69791436155d645,569433d1c69791436155d99c',
			],
			'bot'                    => [
				'en' => [
					'id'  => '541780fde4b005ed2d11784c',
					'url' => 'https://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5693d582c69791436155d645',
					'url' => 'https://fr.docs.wp-rocket.me/article/188-comment-est-pre-charge-le-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'dns_prefetch'           => [
				'en' => '541780fde4b005ed2d11784c',
				'fr' => '5693d582c69791436155d645',
			],
			'never_cache'            => [
				'en' => '5519ab03e4b061031402119f,559110d0e4b027e1978eba09,56b55ba49033600da1c0b687,553ac7bfe4b0eb143c62af44,587920b5c697915403a0e1f4,5569b671e4b027e1978e3c51',
				'fr' => '56941c0cc69791436155d8ab,56943395c69791436155d99a,56cb9ba990336008e9e9e3d9,56942fc3c69791436155d987,5879230cc697915403a0e211,5697d2dc9033603f7da31041',
			],
			'always_purge_section'   => [
				'en' => '555c7e9ee4b027e1978e17a,55151406e4b0610314020a3f,5632858890336002f86d903e,5792c0c1903360293603896b',
				'fr' => '568f7df49033603f7da2ec72,5694194d9033603f7da2fb00,56951208c69791436155de2a,57a4a0c3c697910783242008',
			],
			'query_strings'          => [
				'en' => '590a83610428634b4a32d52c',
				'fr' => '597a04fd042863033a1b6da4',
			],
			'ecommerce'              => [
				'en' => [
					'id'  => '555c619ce4b027e1978e1767',
					'url' => 'https://docs.wp-rocket.me/article/75-is-wp-rocket-compatible-with-e-commerce-plugins/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f8291c69791436155caea',
					'url' => 'https://fr.docs.wp-rocket.me/article/176-compatibilite-extensions-e-commerce/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_query_strings'    => [
				'en' => [
					'id'  => '590a83610428634b4a32d52c',
					'url' => 'https://docs.wp-rocket.me/article/971-caching-query-strings/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '597a04fd042863033a1b6da4',
					'url' => 'https://fr.docs.wp-rocket.me/article/1014-cache-query-strings/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_cache'          => [
				'en' => [
					'id'  => '5519ab03e4b061031402119f',
					'url' => 'https://docs.wp-rocket.me/article/54-exclude-pages-from-the-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56941c0cc69791436155d8ab',
					'url' => 'https://fr.docs.wp-rocket.me/article/196-exclure-pages-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'always_purge'           => [
				'en' => [
					'id'  => '555c7e9ee4b027e1978e17a5',
					'url' => 'https://docs.wp-rocket.me/article/78-how-often-is-the-cache-updated/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f7df49033603f7da2ec72',
					'url' => 'https://fr.docs.wp-rocket.me/article/171-intervalle-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cleanup'                => [
				'en' => '55dcaa28e4b01d7a6a9bd373,578cd762c6979160ca1441cd,5569d11ae4b01a224b427725',
				'fr' => '5697cebbc69791436155ed5e,58b6e7a0dd8c8e56bfa819f5,5697cd85c69791436155ed50',
			],
			'slow_admin'             => [
				'en' => [
					'id'  => '55dcaa28e4b01d7a6a9bd373',
					'url' => 'https://docs.wp-rocket.me/article/121-wp-admin-area-is-slow/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5697cebbc69791436155ed5e',
					'url' => 'https://fr.docs.wp-rocket.me/article/260-la-zone-d-administration-wp-est-lente/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cdn_section'            => [
				'en' => '54c7fa3de4b0512429885b5c,54205619e4b0e7b8127bf849,54a6d578e4b047ebb774a687,56b2b4459033603f7da37acf,566f749f9033603f7da28459,5434667fe4b0310ce5ee867a',
				'fr' => '5696830b9033603f7da308ac,5696837e9033603f7da308ae,569685749033603f7da308c0,57a4961190336059d4edc9d8,5697d5f8c69791436155ed8e,569684d29033603f7da308b9',
			],
			'cdn'                    => [
				'en' => [
					'id'  => '54c7fa3de4b0512429885b5c',
					'url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5696830b9033603f7da308ac',
					'url' => 'https://fr.docs.wp-rocket.me/article/246-utiliser-wp-rocket-avec-un-cdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_cdn'            => [
				'en' => [
					'id'  => '5434667fe4b0310ce5ee867a',
					'url' => 'https://docs.wp-rocket.me/article/24-resolving-issues-with-cdn-and-fonts-icons/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '569684d29033603f7da308b9',
					'url' => 'https://fr.docs.wp-rocket.me/article/248-resoudre-des-problemes-avec-cdn-et-les-polices-icones/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cloudflare_credentials' => [
				'en' => [
					'id'  => '54205619e4b0e7b8127bf849',
					'url' => 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5696837e9033603f7da308ae',
					'url' => 'https://fr.docs.wp-rocket.me/article/247-utiliser-wp-rocket-avec-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cloudflare_settings'    => [
				'en' => [
					'id'  => '54205619e4b0e7b8127bf849',
					'url' => 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5696837e9033603f7da308ae',
					'url' => 'https://fr.docs.wp-rocket.me/article/247-utiliser-wp-rocket-avec-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'sucuri_credentials'     => [
				'en' => [
					'id'  => '5bce07be2c7d3a04dd5bf94d',
					'url' => 'https://docs.wp-rocket.me/article/1120-sucuri-add-on/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5bcf39c72c7d3a4db66085b9',
					'url' => 'https://fr.docs.wp-rocket.me/article/1122-sucuri-add-on/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'varnish'                => [
				'en' => [
					'id'  => '56f48132c6979115a34095bd',
					'url' => 'https://docs.wp-rocket.me/article/493-using-varnish-with-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56fd2f789033601d6683e574',
					'url' => 'https://fr.docs.wp-rocket.me/article/512-varnish-wp-rocket-2-7/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'heartbeat_settings'     => [
				'en' => [
					'id'  => '5bcdfecd042863158cc7b672',
					'url' => 'https://docs.wp-rocket.me/article/1119-control-wordpress-heartbeat-api/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5bcf4378042863215a46bc00',
					'url' => 'https://fr.docs.wp-rocket.me/article/1124-controler-api-wordpress-heartbeat/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'google_tracking'        => [
				'en' => [
					'id'  => '5b4693220428630abc0bf97b',
					'url' => 'https://docs.wp-rocket.me/article/1103-google-tracking-add-on/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'facebook_tracking'      => [
				'en' => [
					'id'  => '5bc904e7042863158cc79d57',
					'url' => 'https://docs.wp-rocket.me/article/1117-facebook-pixel-add-on/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5bcf3d35042863215a46bb7f',
					'url' => 'https://fr.docs.wp-rocket.me/article/1123-add-on-facebook-pixel/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
		];

		return isset( $suggest[ $doc_id ][ $this->locale ] ) ? $suggest[ $doc_id ][ $this->locale ] : $suggest[ $doc_id ]['en'];
	}
}
