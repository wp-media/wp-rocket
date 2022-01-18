<?php
namespace WP_Rocket\Engine\Optimization\Minify\JS;

use WP_Rocket\Dependencies\Minify\JS as MinifyJS;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\Minify\ProcessorInterface;
use WP_Rocket\Logger\Logger;

/**
 * Combines JS files
 *
 * @since 3.1
 */
class Combine extends AbstractJSOptimization implements ProcessorInterface {
	/**
	 * Minifier instance
	 *
	 * @since 3.1
	 *
	 * @var MinifyJS
	 */
	private $minifier;

	/**
	 * Excluded defer JS pattern
	 *
	 * @since 3.8
	 *
	 * @var string
	 */
	private $excluded_defer_js;

	/**
	 * Scripts to combine
	 *
	 * @since 3.1
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Inline scripts excluded from combined and moved after the combined file
	 *
	 * @since 3.1.4
	 *
	 * @var array
	 */
	private $move_after = [];

	/**
	 * Constructor
	 *
	 * @since 3.1
	 *
	 * @param Options_Data     $options     Plugin options instance.
	 * @param MinifyJS         $minifier    Minifier instance.
	 * @param AssetsLocalCache $local_cache Assets local cache instance.
	 * @param DeferJS          $defer_js    Defer JS instance.
	 */
	public function __construct( Options_Data $options, MinifyJS $minifier, AssetsLocalCache $local_cache, DeferJS $defer_js ) {
		parent::__construct( $options, $local_cache );

		$this->minifier          = $minifier;
		$this->excluded_defer_js = implode( '|', $defer_js->get_excluded() );
	}

	/**
	 * Minifies and combines JavaScripts into one
	 *
	 * @since 3.1
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'JS COMBINE PROCESS STARTED.', [ 'js combine process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$scripts         = $this->find( '<script.*<\/script>', $html_nocomments );

		if ( ! $scripts ) {
			Logger::debug( 'No `<script>` tags found.', [ 'js combine process' ] );
			return $html;
		}

		Logger::debug(
			'Found ' . count( $scripts ) . ' `<script>` tag(s).',
			[
				'js combine process',
				'tags' => $scripts,
			]
		);

		$combine_scripts = $this->parse( $scripts );

		if ( empty( $combine_scripts ) ) {
			Logger::debug( 'No `<script>` tags to optimize.', [ 'js combine process' ] );
			return $html;
		}

		Logger::debug(
			count( $combine_scripts ) . ' `<script>` tag(s) remaining.',
			[
				'js combine process',
				'tags' => $combine_scripts,
			]
		);

		$content = $this->get_content();

		if ( empty( $content ) ) {
			Logger::debug( 'No JS content.', [ 'js combine process' ] );
			return $html;
		}

		$minify_url = $this->combine( $content );

		if ( ! $minify_url ) {
			Logger::error( 'JS combine process failed.', [ 'js combine process' ] );
			return $html;
		}

		$move_after = '';

		if ( ! empty( $this->move_after ) ) {
			foreach ( $this->move_after as $script ) {
				$move_after .= $script;
				$html        = str_replace( $script, '', $html );
			}
		}

		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$html = str_replace( '</body>', '<script src="' . esc_url( $minify_url ) . '" data-minify="1"></script>' . $move_after . '</body>', $html );

		foreach ( $combine_scripts as $script ) {
			$html = str_replace( $script[0], '', $html );
		}

		Logger::info(
			'Combined JS file successfully added.',
			[
				'js combine process',
				'url' => $minify_url,
			]
		);

		return $html;
	}

	/**
	 * Parses found nodes to keep only the ones to combine
	 *
	 * @since 3.1
	 *
	 * @param Array $scripts scripts corresponding to JS file or content.
	 * @return array
	 */
	protected function parse( $scripts ) {
		$excluded_externals = implode( '|', $this->get_excluded_external_file_path() );
		$scripts            = array_map(
			function( $script ) use ( $excluded_externals ) {
				preg_match( '/<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>/Umsi', $script[0], $matches );

				if ( isset( $matches['url'] ) ) {
					if ( $this->is_external_file( $matches['url'] ) ) {
						if ( preg_match( '#(' . $excluded_externals . ')#', $matches['url'] ) ) {
							Logger::debug(
								'Script is external.',
								[
									'js combine process',
									'tag' => $matches[0],
								]
							);
							return;
						}

						if ( $this->is_defer_excluded( $matches['url'] ) ) {
							return;
						}

						$this->scripts[] = [
							'type'    => 'url',
							'content' => $matches['url'],
						];

						return $script;
					}

					if ( $this->is_minify_excluded_file( $matches ) ) {
						Logger::debug(
							'Script is excluded.',
							[
								'js combine process',
								'tag' => $matches[0],
							]
						);
						return;
					}

					if ( $this->is_defer_excluded( $matches['url'] ) ) {
						return;
					}

					$file_path = $this->get_file_path( strtok( $matches['url'], '?' ) );

					if ( ! $file_path ) {
						return;
					}

					$this->scripts[] = [
						'type'    => 'file',
						'content' => $file_path,
					];
				} else {
					preg_match( '/<script\b(?<attrs>[^>]*)>(?:\/\*\s*<!\[CDATA\[\s*\*\/)?\s*(?<content>[\s\S]*?)\s*(?:\/\*\s*\]\]>\s*\*\/)?<\/script>/msi', $script[0], $matches_inline );

					$matches_inline = array_merge(
						[
							'attrs'   => '',
							'content' => '',
						],
						$matches_inline
					);

					if ( preg_last_error() === PREG_BACKTRACK_LIMIT_ERROR ) {
						Logger::debug(
							'PCRE regex execution Catastrophic Backtracking',
							[
								'inline JS backtracking error',
								'content' => $matches_inline['content'],
							]
						);
						return;
					}

					if ( strpos( $matches_inline['attrs'], 'type' ) !== false && ! preg_match( '/type\s*=\s*["\']?(?:text|application)\/(?:(?:x\-)?javascript|ecmascript)["\']?/i', $matches_inline['attrs'] ) ) {
						Logger::debug(
							'Inline script is not JS.',
							[
								'js combine process',
								'attributes' => $matches_inline['attrs'],
							]
						);
						return;
					}

					if ( false !== strpos( $matches_inline['attrs'], 'src=' ) ) {
						Logger::debug(
							'Inline script has a `src` attribute.',
							[
								'js combine process',
								'attributes' => $matches_inline['attrs'],
							]
						);
						return;
					}

					if ( in_array( $matches_inline['content'], $this->get_localized_scripts(), true ) ) {
						Logger::debug(
							'Inline script is a localize script',
							[
								'js combine process',
								'excluded_content' => $matches_inline['content'],
							]
						);
						return;
					}

					if ( $this->is_delayed_script( $matches_inline['attrs'] ) ) {
						return;
					}

					foreach ( $this->get_excluded_inline_content() as $excluded_content ) {
						if ( false !== strpos( $matches_inline['content'], $excluded_content ) ) {
							Logger::debug(
								'Inline script has excluded content.',
								[
									'js combine process',
									'excluded_content' => $excluded_content,
								]
							);
							return;
						}
					}

					foreach ( $this->get_move_after_inline_scripts() as $move_after_script ) {
						if ( false !== strpos( $matches_inline['content'], $move_after_script ) ) {
							$this->move_after[] = $script[0];
							return;
						}
					}

					$this->scripts[] = [
						'type'    => 'inline',
						'content' => $matches_inline['content'],
					];
				}

				return $script;
			},
			$scripts
		);

		return array_filter( $scripts );
	}

	/**
	 * Gets content for each script either from inline or from src
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	protected function get_content() {
		$content = '';

		foreach ( $this->scripts as $script ) {
			if ( 'file' === $script['type'] ) {
				$file_content = $this->get_file_content( $script['content'] );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( 'url' === $script['type'] ) {
				$file_content = $this->local_cache->get_content( rocket_add_url_protocol( $script['content'] ) );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( 'inline' === $script['type'] ) {
				$inline_js = rtrim( $script['content'], ";\n\t\r" ) . ';';
				$content  .= $inline_js;

				$this->add_to_minify( $inline_js );
			}
		}

		return $content;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 *
	 * @param string $content Content to minify & combine.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	protected function combine( $content ) {
		if ( empty( $content ) ) {
			return false;
		}

		$filename      = md5( $content . $this->minify_key ) . '.js';
		$minified_file = $this->minify_base_path . $filename;
		if ( ! rocket_direct_filesystem()->is_readable( $minified_file ) ) {
			$minified_content = $this->minify();

			if ( ! $minified_content ) {
				return false;
			}

			$minify_filepath = $this->write_file( $minified_content, $minified_file );

			if ( ! $minify_filepath ) {
				return false;
			}
		}

		return $this->get_minify_url( $filename );
	}

	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 *
	 * @return string|bool Minified content, false if empty
	 */
	protected function minify() {
		$minified_content = $this->minifier->minify();

		if ( empty( $minified_content ) ) {
			return false;
		}

		return $minified_content;
	}

	/**
	 * Adds content to the minifier
	 *
	 * @since  3.1
	 *
	 * @param string $content Content to minify/combine.
	 * @return void
	 */
	protected function add_to_minify( $content ) {
		$this->minifier->add( $content );
	}

	/**
	 * Patterns in content excluded from being combined
	 *
	 * @since  3.1
	 *
	 * @return array
	 */
	protected function get_excluded_inline_content() {
		$defaults = [
			'document.write',
			'google_ad',
			'edToolbar',
			'gtag',
			'_gaq.push',
			'_gaLt',
			'GoogleAnalyticsObject',
			'syntaxhighlighter',
			'adsbygoogle',
			'ci_cap_',
			'_stq',
			'nonce',
			'post_id',
			'LogHuman',
			'idcomments_acct',
			'ch_client',
			'sc_online_t',
			'_stq',
			'bannersnack_embed',
			'vtn_player_type',
			'ven_video_key',
			'ANS_customer_id',
			'tdBlock',
			'tdLocalCache',
			'wpRestNonce',
			'"url":',
			'lazyLoadOptions',
			'adthrive',
			'loadCSS',
			'google_tag_params',
			'clicky_custom',
			'clicky_site_ids',
			'NSLPopupCenter',
			'_paq',
			'gtm',
			'dataLayer',
			'RecaptchaLoad',
			'WPCOM_sharing_counts',
			'jetpack_remote_comment',
			'subscribe-field',
			'contextly',
			'_mmunch',
			'gt_request_uri',
			'doGTranslate',
			'docTitle',
			'bs_ajax_paginate_',
			'bs_deferred_loading_',
			'theChampRedirectionUrl',
			'theChampFBCommentUrl',
			'theChampTwitterRedirect',
			'theChampRegRedirectionUrl',
			'ESSB_CACHE_URL',
			'oneall_social_login_providers_',
			'betterads_screen_width',
			'woocommerce_wishlist_add_to_wishlist_url',
			'arf_conditional_logic',
			'heateorSsHorSharingShortUrl',
			'TL_Const',
			'bimber_front_microshare',
			'setAttribute("id"',
			'setAttribute( "id"',
			'TribeEventsPro',
			'peepsotimedata',
			'wphc_data',
			'hc_rand_id',
			'RBL_ADD',
			'AfsAnalyticsObject',
			'_thriveCurrentPost',
			'esc_login_url',
			'fwduvpMainPlaylist',
			'Bibblio.initRelatedContent',
			'showUFC()',
			'#iphorm-',
			'#fancy-',
			'ult-carousel-',
			'theChampLJAuthUrl',
			'f._fbq',
			'Insticator',
			'w2dc_js_objects',
			'cherry_ajax',
			'ad_block_',
			'elementorFrontendConfig',
			'zeen_',
			'disqusIdentifier',
			'currentAjaxUrl',
			'geodir_event_call_calendar_',
			'atatags-',
			'hbspt.forms.create',
			'function(c,h,i,m,p)',
			'dataTable({',
			'rankMath = {',
			'_atrk_opts',
			'quicklinkOptions',
			'ct_checkjs_',
			'WP_Statistics_http',
			'penci_block_',
			'omapi_localized',
			'omapi_data',
			'OptinMonsterApp',
			'tminusnow',
			'nfForms',
			'galleries.gallery_',
			'wcj_evt.prodID',
			'advads_tracking_ads',
			'advadsGATracking.postContext',
			'woopack_config',
			'ulp_content_id',
			'wp-cumulus/tagcloud.swf?r=',
			'ctSetCookie(\'ct_checkjs\'',
			'woof_really_curr_tax',
			'uLogin.customInit',
			'i18n_no_matching_variations_text',
			'alsp_map_markers_attrs',
			'var inc_opt =',
			'iworks_upprev',
			'yith_wcevti_tickets',
			'window.metrilo.ensure_cbuid',
			'metrilo.event',
			'wordpress_page_root',
			'wcct_info',
			'Springbot.product_id',
			'pysWooProductData',
			'dfd-heading',
			'owl=$("#',
			'penci_megamenu',
			'fts_security',
			'algoliaAutocomplete',
			'avia_framework_globals',
			'tabs.easyResponsiveTabs',
			'searchlocationHeader',
			'yithautocomplete',
			'data-parallax-speed',
			'currency_data=',
			'cedexisData',
			'function reenableButton',
			'#wpnbio-show',
			'e.Newsletter2GoTrackingObject',
			'var categories_',
			'"+nRemaining+"',
			'cartsguru_cart_token',
			'after_share_easyoptin',
			'location_data.push',
			'thirstyFunctions.isThirstyLink',
			'styles: \' #custom-menu-',
			'function svc_center_',
			'#svc_carousel2_container_',
			'advads.move',
			'elementid',
			'advads_has_ads',
			'wpseo_map_init',
			'mdf_current_page_url',
			'tptn_tracker',
			'dpsp_pin_button_data',
			'searchwp_live_search_params',
			'wpp_params',
			'top.location,thispage',
			'selection+pagelink',
			'ic_window_resolution',
			'PHP.wp_p_id',
			'ShopifyBuy.UI.onReady(client)',
			'orig_request_uri',
			'gie.widgets.load',
			'Adman.Flash',
			'PHP.wp_p_id',
			'window.broadstreetKeywords',
			'var productId =',
			'var flatsomeVars',
			'wc_product_block_data',
			'static.mailerlite.com',
			'amzn_assoc',
			'_bs_getParameterByName',
			'_stq.push',
			'h._remove',
			'var FlowFlowOpts',
			'var WCPFData =',
			'var _beeketing',
			'var _statcounter',
			'var actions =',
			'var current_url',
			'var object_name',
			'var the_ajax_script',
			'var wc_cart_fragments_params',
			'var woocommerce_params',
			'var wpml_cookies',
			'wc_add_to_cart_params',
			'window.broadstreetKeywords',
			'window.wc_ga_pro.available_gateways',
			'xa.prototype',
			'HOUZEZ_ajaxcalls_vars',
			'w2dc_maps_objects',
			'w2dc_controller_args_array',
			'w2dc_map_markers_attrs',
			'YT.Player',
			'WPFC.data',
			'function current_video_',
			'var videodiv',
			'var slider_wppasrotate',
			'wppas_ga',
			'var blockClass',
			'tarteaucitron',
			'pw_brand_product_list',
			'tminusCountDown',
			'pysWooSelectContentData',
			'wpvq_ans89733',
			'_isp_version',
			'price_range_data',
			'window.FeedbackCompanyWidgets',
			'woocs_current_currency',
			'woo_variation_swatches_options',
			'woocommerce_price_slider_params',
			'scriptParams',
			'form-adv-pagination',
			'borlabsCookiePrioritize',
			'urls_wpwidgetpolylang',
			'quickViewNonce',
			'frontendscripts_params',
			'nj-facebook-messenger',
			'var fb_mess_position',
			'init_particles_row_background_script',
			'setREVStartSize',
			'fl-node',
			'PPAccordion',
			'soliloquy_',
			'wprevpublicjs_script_vars',
			'DTGS_NONCE_FRONTEND',
			'et_animation_data',
			'archives-dropdown',
			'loftloaderCache',
			'SmartSliderSimple',
			'var nectarLove',
			'var incOpt',
			'RocketBrowserCompatibilityChecker',
			'RocketPreloadLinksConfig',
			'placementVersionId',
			'var useEdit',
			'var DTGS_NONCE_FRONTEND',
			'n2jQuery',
			'et_core_api_spam_recaptcha',
			'cnArgs',
			'__CF$cv$params',
			'trustbox_settings',
			'aepro',
			'cdn.jst.ai',
			'w2dc_fields_in_categories',
			'jetMenuPublicSettings',
			'JetTricksSettings',
			'aepc_pixel',
			'avadaWooCommerceVars',
			'var isb',
			'fcaPcPost',
			'csrf_token',
			'icwp_wpsf_vars_lpantibot',
			'wpvViewHead',
			'ed_school_plugin',
			'aps_comp_',
			'guaven_woos',
			'__lm_redirect_to',
			'__wpdm_view_count',
			'bookacti.booking_system',
			'nfFrontEnd',
			'view_quote_cart_link',
			'__eae_decode_emails',
			'divioverlays_ajaxurl',
			'var _EPYT_',
			'#ins-heading-',
			'#ins-button-',
			'tve_frontend_options',
			'lb24.src',
			'amazon_Login_accessToken',
			'porto_infinite_scroll',
			'.adace-loader-',
			'adace_load_',
			'tagGroupsAccordiontaggroupscloudaccordion',
			'tagGroupsTabstaggroupscloudtabs',
			'jrRelatedWidgets',
			'UNCODE.initRow',
			'amp_mobile_redirect_disabled',
			'wpgdprcData',
			'wpml_browser_redirect_params',
			'swPreRegister',
			'kboard_settings',
			'ct_ultimate_gdpr_cookie',
			'wcpv_registration_local',
			'www.idxhome.com',
			'arf_footer_cl_logic_call',
			'reload_attached_coupons',
			'var ftpp',
			'forminatorFront',
			'_EPYT_',
			'edd_free_downloads_vars',
			'edd_stripe_vars',
			'var ASP',
			'ecwidOriginalTitle',
			'defaultCategoryId',
			'translation-revision-date',
			'google_conversion_id',
			'hbspt',
			'var marker_locations_',
			'var AdmMyAjax',
			'ifso_page_url',
			'referrer_for_pageload',
			'WoocommerceWidget/woocommerceWidget.js',
			'var ht_ctc_chat_var',
			'spuvar',
			'var wpilFrontend',
			'urls_polylangREPLACETOID',
		];

		$excluded_inline = array_merge( $defaults, $this->options->get( 'exclude_inline_js', [] ) );

		/**
		 * Filters inline JS excluded from being combined
		 *
		 * @since 3.1
		 *
		 * @param array $pattern Patterns to match.
		 */
		return apply_filters( 'rocket_excluded_inline_js_content', $excluded_inline );
	}

	/**
	 * Patterns of inline JS to move after the combined JS file
	 *
	 * @since 3.1.4
	 *
	 * @return array
	 */
	protected function get_move_after_inline_scripts() {
		$move_after_scripts = [
			'map_fusion_map_',
			'ec:addProduct',
			'ec:addImpression',
			'clear_better_facebook_comments',
			'vc-row-destroy-equal-heights-',
			'dfd-icon-list-',
			'SFM_template',
			'WLTChangeState',
			'wlt_star_',
			'wlt_pop_distance_',
			'smart_list_tip',
			'gd-wgt-pagi-',
			'data-rf-id=',
			'tvc_po=',
			'scrapeazon',
			'startclock',
			'it_logo_field_owl-box_',
			'td_live_css_uid',
			'wpvl_paramReplace',
			'tdAjaxCount',
			'mec_skin_',
			'_wca',
			'_taboola',
			'fbq(\'trackCustom\'',
			'fbq(\'track\'',
			'data.token',
			'sharrre',
			'dfads_ajax_load_ads',
			'tie_postviews',
			'wmp_update',
			'h5ab-print-article',
			'gform_ajax_frame_',
			'gform_post_render',
			'mts_view_count',
			'act_css_tooltip',
			'window.SLB',
			'wpt_view_count',
			'var dateNow',
			'gallery_product_',
			'.flo-block-slideshow-',
			'data=\'api-key=ct-',
			'ip_common_function()',
			'("style#gsf-custom-css").append',
			'a3revWCDynamicGallery_',
			'#owl-carousel-instagram-',
			'window.FlowFlowOpts',
			'jQuery(\'.td_uid_',
			'jQuery(".slider-',
			'#dfd-vcard-widget-',
			'#sf-instagram-widget-',
			'.woocommerce-tabs-',
			'penci_megamenu__',
			'vc_prepareHoverBox',
			'wp-temp-form-div',
			'_wswebinarsystem_already_',
			'#views-extra-css").text',
			'fusetag.setTargeting',
			'hit.uptrendsdata.com',
			'callback:window.renderBadge',
			'test_run_nf_conditional_logic',
			'cb_nombre',
			'$(\'.fl-node-',
			'function($){google_maps_',
			'$("#myCarousel',
			'et_animation_data=',
			'current_url="',
			'CustomEvent.prototype=window.Event.prototype',
			'electro-wc-product-gallery',
			'woof_is_mobile',
			'jQuery(\'.videonextup',
			'wpp_params',
			'us.templateDirectoryUri=',
			'.fat-gallery-item',
			'.ratingbox',
			'user_rating.prototype.eraseCookie',
			'test_run_nf_conditional',
			'dpsp-networks-btns-wrapper',
			'pa_woo_product_info',
			'sharing_enabled_on_post_via_metabox',
			'#product-search-field-',
			'GOTMLS_login_offset',
			'berocket_aapf_time_to_fix_products_style',
			'window.vc_googleMapsPointer',
			'sinceID_',
			'#ut-background-video-ut-section',
			'+window.comment_tab_width+',
			'dfd-button-hover-in',
			'wpseo-address-wrapper',
			'platform.stumbleupon.com',
			'#woo_pp_ec_button_mini_cart',
			'#supercarousel',
			'blockClass',
			'tdbMenuItem',
			'tdbSearchItem',
			'best_seller_badge',
			'jQuery(\'#product-top-bar',
			'fb_desc-',
			'FC_regenerate_captcha',
			'wp_post_blocks_vars.listed_posts=[',
			'captcha-hash',
			'mapdata={',
			'.ywpc-char-',
			').countdowntimer(',
			'jQuery("#td_uid_',
			'find(\'#td_uid_',
			'variation_estimate_msg',
		];

		/**
		 * Filters inline JS to move after the combined JS file
		 *
		 * @since 3.1.4
		 *
		 * @param array $move_after_scripts Patterns to match.
		 */
		return apply_filters( 'rocket_move_after_combine_js', $move_after_scripts );
	}

	/**
	 * Gets all localized scripts data to exclude them from combine.
	 *
	 * @since 3.1.3
	 *
	 * @return array
	 */
	protected function get_localized_scripts() {
		static $localized_scripts;

		if ( isset( $localized_scripts ) ) {
			return $localized_scripts;
		}

		$localized_scripts = [];

		foreach ( array_unique( wp_scripts()->queue ) as $item ) {
			$data = wp_scripts()->print_extra_script( $item, false );

			if ( empty( $data ) ) {
				continue;
			}

			$localized_scripts[] = $data;
		}

		return $localized_scripts;
	}

	/**
	 * Is this script a delayed script or not.
	 *
	 * @since 3.7
	 *
	 * @param string $script_attributes Attributes beside the opening of script tag.
	 *
	 * @return bool True if it's a delayed script and false if not.
	 */
	private function is_delayed_script( $script_attributes ) {
		return false !== strpos( $script_attributes, 'data-rocketlazyloadscript=' );
	}

	/**
	 * Checks if the current URL is excluded from defer JS
	 *
	 * @since 3.8
	 *
	 * @param string $url URL to check.
	 * @return boolean
	 */
	private function is_defer_excluded( string $url ) : bool {
		if (
			! empty( $this->excluded_defer_js )
			&&
			preg_match( '#(' . $this->excluded_defer_js . ')#i', $url )
		) {
			Logger::debug(
				'Script is excluded from defer JS.',
				[
					'js combine process',
					'url' => $url,
				]
			);
			return true;
		}

		return false;
	}
}
