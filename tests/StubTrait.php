<?php

namespace WP_Rocket\Tests;

use Brain\Monkey\Functions;

trait StubTrait {
	protected $abspath = 'vfs://public/';
	protected $is_running_vfs = true;
	protected $mock_rocket_get_constant = true;
	protected $just_return_path = false;
	protected $wp_cache_constant = false;
	protected $wp_content_dir = 'vfs://public/wp-content';
	protected $script_debug = false;
	protected $rocket_version;
	protected $wp_rocket_debug = false;
	protected $wp_rocket_advanced_cache = true;
	protected $disable_wp_cron = false;
	protected $donotrocketoptimize = null;
	protected $white_label = false;
	protected $white_label_footprint = null;
	protected $plugin_name = 'WP Rocket';
	protected $rucss_api = 'http://localhost';
	protected $constants = [];
	protected $dontasynccss = false;
	protected $rest_request = false;

	protected function resetStubProperties() {
		$defaults = [
			'abspath'                  => 'vfs://public/',
			'disable_wp_cron'          => false,
			'mock_rocket_get_constant' => true,
			'wp_cache_constant'        => false,
			'wp_content_dir'           => 'vfs://public/wp-content',
			'script_debug'             => false,
			'rocket_version'           => null,
			'wp_rocket_debug'          => false,
			'wp_rocket_advanced_cache' => true,
			'donotrocketoptimize'      => null,
			'dontasynccss'             => false,
			'rest_request'             => false,
			'white_label'              => false,
			'white_label_footprint'    => null,
			'constants'                => [],
		];

		foreach ( $defaults as $property => $value ) {
			$this->$property = $value;
		}
	}

	protected function stubRocketGetConstant() {
		if ( ! $this->mock_rocket_get_constant ) {
			return;
		}

		Functions\when( 'rocket_get_constant' )->alias(
			function ( $constant_name, $default = null ) {
				return $this->getConstant( $constant_name, $default );
			}
		);
	}

	protected function getConstant( $constant_name, $default = null ) {
		switch ( $constant_name ) {
			case 'ABSPATH':
				return $this->abspath;

			case 'DISABLE_WP_CRON':
				return $this->disable_wp_cron;

			case 'DONOTROCKETOPTIMIZE' :
				return $this->donotrocketoptimize;

			case 'FS_CHMOD_DIR':
				return 0777;

			case 'FS_CHMOD_FILE':
				return 0666;

			case 'SCRIPT_DEBUG':
				return $this->script_debug;

			case 'REST_REQUEST':
				return $this->rest_request;

			case 'WP_CACHE':
				return $this->wp_cache_constant;

			case 'WP_CONTENT_DIR':
				return $this->wp_content_dir;

			case 'WP_ROCKET_ADVANCED_CACHE':
				return $this->wp_rocket_advanced_cache;

			case 'WP_ROCKET_PLUGIN_NAME':
				return 'WP Rocket';
			case 'WP_ROCKET_WEB_MAIN':
				return 'https://wp-rocket.me/';

			case 'WP_ROCKET_ASSETS_JS_URL':
				return 'http://example.org/wp-content/plugins/wp-rocket/assets/js/';

			case 'WP_ROCKET_CACHE_PATH':
				return "{$this->wp_content_dir}/cache/wp-rocket/";

			case 'WP_ROCKET_CONFIG_PATH':
				return "{$this->wp_content_dir}/wp-rocket-config/";

			case 'WP_ROCKET_CRITICAL_CSS_PATH':
				return "{$this->wp_content_dir}/cache/critical-css/";

			case 'WP_ROCKET_USED_CSS_PATH':
				return "{$this->wp_content_dir}/cache/used-css/";

			case 'WP_ROCKET_DEBUG':
				return $this->wp_rocket_debug;

			case 'WP_ROCKET_INC_PATH':
				return "{$this->wp_content_dir}/plugins/wp-rocket/inc/";

			case 'WP_ROCKET_MINIFY_CACHE_PATH':
				return "{$this->wp_content_dir}/cache/min/";

			case 'WP_ROCKET_MINIFY_CACHE_URL':
				return 'http://example.org/wp-content/cache/min/';

			case 'WP_ROCKET_CACHE_BUSTING_PATH':
				return "{$this->wp_content_dir}/cache/busting/";

			case 'WP_ROCKET_PATH':
				return "{$this->wp_content_dir}/plugins/wp-rocket/";

			case 'WP_ROCKET_PHP_VERSION':
				return '5.6';

			case 'WP_ROCKET_RUNNING_VFS':
				return $this->is_running_vfs;

			case 'WP_ROCKET_SLUG':
				return 'wp_rocket_settings';

			case 'WP_ROCKET_VENDORS_PATH':
				return "{$this->wp_content_dir}/plugins/wp-rocket/inc/vendors/";

			case 'WP_ROCKET_VERSION':
				if ( ! empty( $this->rocket_version ) ) {
					return $this->rocket_version;
				}

			case 'WP_ROCKET_WHITE_LABEL_ACCOUNT':
				return $this->white_label;

			case 'WP_ROCKET_WHITE_LABEL_FOOTPRINT':
				return $this->white_label_footprint;

			case 'WP_ROCKET_PLUGIN_NAME':
				return $this->plugin_name;

			case 'WP_ROCKET_SAAS_API_URL':
				return $this->rucss_api;

			default:
				if ( isset( $this->constants[ $constant_name ] ) ) {
					return $this->constants[ $constant_name ];
				}

				if ( ! rocket_has_constant( $constant_name ) ) {
					return $default;
				}

				return constant( $constant_name );
		}
	}

	protected function stubWpNormalizePath() {
		Functions\when( 'wp_normalize_path' )->alias(
			function ( $path ) {
				if ( true === $this->just_return_path ) {
					return $path;
				}

				$path = str_replace( '\\', '/', $path );
				$path = preg_replace( '|(?<=.)/+|', '/', $path );

				if ( ':' === substr( $path, 1, 1 ) ) {
					$path = ucfirst( $path );
				}

				return $path;
			}
		);
	}

	protected function stubGetRocketParseUrl( $url = '' ) {
		if ( empty( $url ) ) {
			Functions\when( 'get_rocket_parse_url' )
				->alias(
					function ( $url ) {
						return $this->get_rocket_parse_url( $url );
					}
				);
		} else {
			Functions\expect( 'get_rocket_parse_url' )
				->once()
				->with( $url )
				->andReturnUsing(
					function ( $url ) {
						return $this->get_rocket_parse_url( $url );
					}
				);
		}
	}

	protected function get_rocket_parse_url( $url ) {
		$parsed = parse_url( $url );

		$host     = isset( $parsed['host'] ) ? strtolower( urldecode( $parsed['host'] ) ) : '';
		$path     = isset( $parsed['path'] ) ? urldecode( $parsed['path'] ) : '';
		$scheme   = isset( $parsed['scheme'] ) ? urldecode( $parsed['scheme'] ) : '';
		$query    = isset( $parsed['query'] ) ? urldecode( $parsed['query'] ) : '';
		$fragment = isset( $parsed['fragment'] ) ? urldecode( $parsed['fragment'] ) : '';

		return [
			'host'     => $host,
			'path'     => $path,
			'scheme'   => $scheme,
			'query'    => $query,
			'fragment' => $fragment,
		];
	}

	protected function stubWpParseUrl() {
		Functions\when( 'wp_parse_url' )->alias(
			function ( $url, $component = - 1 ) {
				return parse_url( $url, $component );
			}
		);
	}

	protected function stubRocketRealpath() {
		Functions\when( 'rocket_realpath' )->alias(
			function ( $file ) {
				$wrapper = null;
				$path    = [];

				if ( false !== strpos( $file, '://' ) ) {
					list( $wrapper, $file ) = explode( '://', $file, 2 );
				}

				foreach ( explode( '/', $file ) as $part ) {
					if ( '' === $part || '.' === $part ) {
						continue;
					}

					if ( '..' !== $part ) {
						array_push( $path, $part );
					} elseif ( count( $path ) > 0 ) {
						array_pop( $path );
					}
				}

				$file = join( '/', $path );

				// Put the wrapper back on the target.
				if ( $wrapper !== null ) {
					return $wrapper . '://' . $file;
				}

				return $file;
			}
		);
	}

	protected function stubfillWpBasename() {
		Functions\when( 'wp_basename' )->alias(
			function ( $path, $suffix = '' ) {
				return urldecode( basename( str_replace( [ '%2F', '%5C' ], '/', urlencode( $path ) ), $suffix ) );
			}
		);
	}
}
