<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Logger\Logger;

class HTML {
	use RegexTrait;
	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Array of excluded patterns from delay JS
	 *
	 * @since 3.9
	 *
	 * @var array
	 */
	protected $excluded = [];

	/**
	 * Allowed type attributes.
	 *
	 * @var array Array of allowed type attributes.
	 */
	private $allowed_types = [
		'text/javascript',
		'module',
		'application/javascript',
		'application/ecmascript',
		'application/x-ecmascript',
		'application/x-javascript',
		'text/ecmascript',
		'text/javascript1.0',
		'text/javascript1.1',
		'text/javascript1.2',
		'text/javascript1.3',
		'text/javascript1.4',
		'text/javascript1.5',
		'text/jscript',
		'text/livescript',
		'text/x-ecmascript',
		'text/x-javascript',
	];

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Creates an instance of HTML.
	 *
	 * @param Options_Data $options Plugin options instance.
	 * @param DataManager  $data_manager DataManager instance.
	 * @param Logger       $logger Logger instance.
	 */
	public function __construct( Options_Data $options, DataManager $data_manager, Logger $logger ) {
		$this->options      = $options;
		$this->data_manager = $data_manager;
		$this->logger       = $logger;
	}

	/**
	 * Adjust HTML to have delay js structure.
	 *
	 * @since 3.9 Updated to use exclusions list instead of inclusions list.
	 * @since 3.7
	 *
	 * @param string $html Buffer html for the page.
	 *
	 * @return string
	 */
	public function delay_js( $html ): string {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$this->set_exclusions();

		$this->excluded = array_merge( $this->excluded, $this->options->get( 'delay_js_exclusions', [] ) );
		$this->excluded = array_merge( $this->excluded, $this->options->get( 'delay_js_exclusions_selected_exclusions', [] ) );

		/**
		 * Filters the delay JS exclusions array
		 *
		 * @since 3.9
		 *
		 * @param array $excluded Array of excluded patterns.
		 */
		$this->excluded = (array) apply_filters( 'rocket_delay_js_exclusions', $this->excluded );
		$this->excluded = array_map(
			function ( $value ) {
				if ( ! is_string( $value ) ) {
					$value = (string) $value;
				}

				return str_replace(
					[ '+', '?ver', '#' ],
					[ '\+', '\?ver', '\#' ],
					$value
				);
			},
			$this->excluded
		);

		return $this->parse( $html );
	}

	/**
	 * Checks if is allowed to Delay JS.
	 *
	 * @since 3.7
	 *
	 * @return bool
	 */
	public function is_allowed(): bool {

		if ( rocket_bypass() ) {
			return false;
		}

		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'delay_js' ) ) {
			return false;
		}

		return (bool) $this->options->get( 'delay_js', 0 );
	}

	/**
	 * Gets Javascript to redirect IE visitors to the uncached page
	 *
	 * @since 3.9
	 *
	 * @return string
	 */
	public function get_ie_fallback(): string {
		return 'if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}';
	}

	/**
	 * Parse the html and add/remove attributes from specific scripts.
	 *
	 * @since 3.7
	 *
	 * @param string $html Buffer html for the page.
	 *
	 * @return string
	 */
	private function parse( $html ): string {

		if ( empty( $html ) ) {
			return $html;
		}

		$result = $this->replace_xmp_tags( $html );
		$result = $this->replace_svg_tags( $result );

		$replaced_html = preg_replace_callback(
			'/<\s*script(?<attr>\s*[^>]*?)?>(?<content>.*?)?<\s*\/\s*script\s*>/ims',
			[
				$this,
				'replace_scripts',
			],
			$result
		);
		if ( empty( $replaced_html ) ) {
			return $html;
		}

		$replaced_html = $this->restore_xmp_tags( $replaced_html );
		return $this->restore_svg_tags( $replaced_html );
	}

	/**
	 * Callback method for preg_replace_callback that is used to adjust attributes for scripts.
	 *
	 * @since 3.9 Use exclusions list & fake type attribute.
	 * @since 3.7
	 *
	 * @param array $matches Matches array for scripts regex.
	 *
	 * @return string
	 */
	public function replace_scripts( $matches ): string {
		foreach ( $this->excluded as $pattern ) {
			if ( preg_match( "#{$pattern}#i", $matches[0] ) ) {
				$this->logger->debug( "DelayJS: Script {$matches[0]} excluded by $pattern" );
				return $matches[0];
			}
		}

		if ( empty( $matches['attr'] ) ) {
			return '<script type="rocketlazyloadscript">' . $matches['content'] . '</script>';
		}

		$type_regex = '/type\s*=\s*(["\'])(?<type>.*)\1/i';
		preg_match( $type_regex . 'U', $matches['attr'], $type_matches );
		if (
			! empty( $type_matches )
			&&
			! empty( trim( $type_matches['type'] ) )
			&&
			! in_array( trim( $type_matches['type'] ), $this->allowed_types, true )
		) {
			return $matches[0];
		}

		$matches['attr'] = preg_replace( $type_regex, 'data-rocket-type=$1$2$1', $matches['attr'] );
		// To remove type attribute without any value.
		$matches['attr'] = preg_replace( '/(\s+type\s+)|(^type\s+)|(\s+type$)/i', '', $matches['attr'] );

		// Checks if script has src attribute so then treat as external script and replace src with data-rocket-src.
		$src_regex       = '/src\s*=\s*(["\'])(.*)\1/i';
		$matches['attr'] = preg_replace( $src_regex, 'data-rocket-src=$1$2$1', $matches['attr'] );

		return '<script type="rocketlazyloadscript" ' . trim( $matches['attr'] ) . '>' . $matches['content'] . '</script>';
	}


	/**
	 * Move meta charset to head if not found to the top of page content.
	 *
	 * @since 3.9.4
	 *
	 * @param string $html Html content.
	 *
	 * @return string
	 */
	public function move_meta_charset_to_head( $html ): string {
		$meta_pattern = "#<meta[^h]*(http-equiv[^=]*=[^\'\"]*[\'\" ]Content-Type[\'\"][ ]*[^>]*|)(charset[^=]*=[ ]*[\'\" ]*[^\'\"> ][^\'\">]+[^\'\"> ][\'\" ]*|charset[^=]*=*[^\'\"> ][^\'\">]+[^\'\"> ])([^>]*|)>(?=.*</head>)#Usmi";

		if ( ! preg_match( $meta_pattern, $html, $matches ) ) {
			return $html;
		}

		$replaced_html = preg_replace( "$meta_pattern", '', $html );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		if ( preg_match( '/<head\b/i', $replaced_html ) ) {
			$replaced_html = preg_replace( '/(<head\b[^>]*?>)/i', "\${1}{$matches[0]}", $replaced_html, 1 );

			if ( empty( $replaced_html ) ) {
				return $html;
			}

			return $replaced_html;
		}

		if ( preg_match( '/<html\b/i', $replaced_html ) ) {
			$replaced_html = preg_replace( '/(<html\b[^>]*?>)/i', "\${1}{$matches[0]}", $replaced_html, 1 );

			if ( empty( $replaced_html ) ) {
				return $html;
			}

			return $replaced_html;
		}

		$replaced_html = preg_replace( '/(<\w+)/', "{$matches[0]}\${1}", $replaced_html, 1 );

		if ( empty( $replaced_html ) ) {
			return $html;
		}

		return $replaced_html;
	}

	/**
	 * Get exclusions
	 *
	 * @return void
	 */
	private function set_exclusions() {
		$lists = $this->data_manager->get_lists();

		$this->excluded = isset( $lists->delay_js_exclusions ) ? $lists->delay_js_exclusions : [];
	}
}
