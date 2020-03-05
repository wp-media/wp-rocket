<?php

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WpeCommon' ) && function_exists( 'wpe_param' ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/wpengine.php';
}

if ( class_exists( 'FlywheelNginxCompat' ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/flywheel.php';
}

if ( defined( 'DB_HOST' ) && strpos( DB_HOST, '.wpserveur.net' ) !== false ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/wp-serveur.php';
}

if ( rocket_is_plugin_active( 'sg-cachepress/sg-cachepress.php' ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/siteground.php';
}

if ( class_exists( '\\Savvii\\CacheFlusherPlugin' ) & class_exists( '\\Savvii\\Options' ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/savvii.php';
}

if ( class_exists( 'WPaaS\Plugin' ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php';
}

if ( isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/kinsta.php';
}

if ( defined( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php';
}

if ( defined( 'PL_INSTANCE_REF' ) && class_exists( '\Presslabs\Cache\CacheHandler' ) && file_exists( WP_CONTENT_DIR . '/advanced-cache.php' ) ) {
	require WP_ROCKET_3RD_PARTY_PATH . 'hosting/presslabs.php';
}

require WP_ROCKET_3RD_PARTY_PATH . 'hosting/pagely.php';
require WP_ROCKET_3RD_PARTY_PATH . 'hosting/nginx.php';
require WP_ROCKET_3RD_PARTY_PATH . 'hosting/pressidium.php';

require WP_ROCKET_3RD_PARTY_PATH . 'plugins/geotargetingwp.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/slider/revslider.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/slider/layerslider.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/slider/meta-slider.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/slider/soliloquy.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/i18n/wpml.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/i18n/polylang.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/aelia-currencyswitcher.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/aelia-prices-by-country.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/aelia-tax-display-by-country.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/woocommerce-multilingual.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/woocommerce-currency-converter-widget.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/edd-software-licencing.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/easy-digital-downloads.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/ithemes-exchange.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/jigoshop.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/wpshop.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/ecommerce/give.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/age-verify.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/appbanners.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/autoptimize.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/envira-gallery.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/cookies/cookie-notice.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/cookies/uk-cookie-consent.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/cookies/eu-cookie-law.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/cookies/weepie-cookie-allow.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/cookies/gdpr.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/rating/kk-star-ratings.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/rating/wp-postratings.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/wp-print.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/buddypress.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/disqus.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/custom-login.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/mobile/amp.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/mobile/wp-appkit.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/seo/seopress.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/seo/rank-math-seo.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/seo/yoast-seo.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/seo/the-seo-framework.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/seo/all-in-one-seo-pack.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/seo/premium-seo-pack.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/wp-rest-api.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/page-builder/beaver-builder.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/page-builder/thrive-visual-editor.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/page-builder/visual-composer.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/security/secupress.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/security/sf-move-login.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/security/wps-hide-login.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/simple-custom-css.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/varnish-http-purge.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/thrive-leads.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/mailchimp.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/advanced-custom-fields.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/wp-offload-s3.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/wp-offload-s3-assets.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/s2member.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/sumome.php';
require WP_ROCKET_3RD_PARTY_PATH . 'plugins/nginx-helper.php';

require WP_ROCKET_3RD_PARTY_PATH . 'themes/divi.php';
require WP_ROCKET_3RD_PARTY_PATH . 'themes/avada.php';
require WP_ROCKET_3RD_PARTY_PATH . 'themes/studiopress.php';
require WP_ROCKET_3RD_PARTY_PATH . 'themes/uncode.php';
