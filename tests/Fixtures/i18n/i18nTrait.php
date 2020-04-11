<?php

namespace WP_Rocket\Tests\Fixtures\i18n;

use Brain\Monkey\Functions;
use PLL_Frontend;
use SitePress;

require_once __DIR__ . '/SitePress.php';
require_once __DIR__ . '/PLL_Frontend.php';

trait i18nTrait {
	protected $always_qtranxf_convertURL = false;
	protected $qtrans_convertURL         = false;
	protected $dataDefaults              = [
		'codes' => [],
		'langs' => [],
		'uris'  => [],
	];

	protected function getHomeUrl() {
		return home_url();
	}

	protected function setUpI18nPlugin( $lang, $config ) {
		$homeUrl = $this->getHomeUrl();
		$data    = array_merge( $this->dataDefaults, $config['data'] );

		switch ( $config['i18n_plugin'] ) {
			case 'wpml':
				return $this->setUpWpml( $data, $homeUrl );

			case 'qtranslate':
			case 'qtranslate-x':
				return $this->setUpQTranslate( $lang, $data, $homeUrl );

			case 'polylang':
				return $this->setUpPolylang( $lang, $data, $homeUrl );

			default:
				Functions\expect( 'get_rocket_i18n_code' )->never();
		}
	}

	protected function setUpWpml( $data, $homeUrl ) {
		$GLOBALS['sitepress']                   = new SitePress();
		$GLOBALS['sitepress']->active_languages = $data['codes'];
		$GLOBALS['sitepress']->home_root        = $homeUrl;
		$GLOBALS['sitepress']->uris_config      = $data['uris'];
	}

	protected function setUpQTranslate( $lang, $data, $homeUrl ) {
		$langs               = $data['langs'];
		$GLOBALS['q_config'] = [ 'enabled_languages' => $langs ];

		if ( ! $this->always_qtranxf_convertURL && ( empty( $lang ) || empty( $langs ) ) ) {
			Functions\expect( 'qtranxf_convertURL' )->with( $homeUrl, $langs, true )->never();
			Functions\expect( 'qtrans_convertURL' )->with( $homeUrl, $langs, true )->never();

			return;
		}

		if ( $this->qtrans_convertURL ) {
			Functions\expect( 'qtrans_convertURL' )
				->with( $homeUrl, $lang, true )
				->andReturnUsing(
					function ( $homeUrl, $lang ) use ( $data ) {
						return $this->buildUrl( $homeUrl, $lang, $data );
					}
				);
		} else {
			Functions\expect( 'qtranxf_convertURL' )
				->with( $homeUrl, $lang, true )
				->andReturnUsing(
					function ( $homeUrl, $lang ) use ( $data ) {
						return $this->buildUrl( $homeUrl, $lang, $data );
					}
				);
		}
	}

	protected function setUpPolylang( $lang, $data, $homeUrl ) {
//		$langs = $data['langs'];

		if ( empty( $data['langs'] ) ) {
			$GLOBALS['polylang'] = 'not-empty';
		} else {
			$GLOBALS['polylang'] = new PLL_Frontend( $data['options'] );

			Functions\expect( 'PLL' )->andReturn( $GLOBALS['polylang'] );
			Functions\expect( 'pll_home_url' )
				->with( $lang )
				->andReturnUsing(
					function ( $lang ) use ( $data, $homeUrl ) {
						return $this->buildUrl( $homeUrl, $lang, $data );
					}
				);
		}

		Functions\expect( 'pll_languages_list' )->andReturn( $data['langs'] );
	}

	protected function buildUrl( $homeUrl, $lang, $data ) {
		if ( empty( $lang ) ) {
			return $homeUrl;
		}

		if ( empty( $data['langs'] ) ) {
			return $homeUrl;
		}

		if ( $lang === $data['default_lang'] ) {
			return $homeUrl;
		}

		return trailingslashit( $homeUrl ) . $lang;
	}
}
