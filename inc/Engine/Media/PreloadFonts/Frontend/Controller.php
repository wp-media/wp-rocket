<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\Frontend\ControllerInterface;
use WP_Rocket\Engine\Media\PreloadFonts\Database\Queries\PreloadFonts as PFQuery;
use WP_Rocket\Engine\Media\PreloadFonts\Context\Context;

class Controller implements ControllerInterface {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Queries instance
	 *
	 * @var PFQuery
	 */
	private $query; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context; // @phpstan-ignore-line Use of this will come later.

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 * @param PFQuery      $query Queries instance.
	 * @param Context      $context Context instance.
	 */
	public function __construct( Options_Data $options, PFQuery $query, Context $context ) {
		$this->options = $options;
		$this->query   = $query;
		$this->context = $context;
	}

	/**
	 * Applies optimization.
	 *
	 * @param string $html HTML content.
	 * @param object $row Database Row.
	 *
	 * @return string
	 */
	public function optimize( string $html, $row ): string {
		// Implement the optimization logic here.
		return $html;
	}

	/**
	 * Add custom data like the List of elements to be considered for optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function add_custom_data( array $data ): array {
		$system_fonts = [
			'serif',
			'sans-serif',
			'monospace',
			'cursive',
			'fantasy',
			'system-ui',
			'ui-serif',
			'ui-sans-serif',
			'ui-monospace',
			'ui-rounded',
			'Arial',
			'Helvetica',
			'Times New Roman',
			'Times',
			'Courier New',
			'Courier',
			'Georgia',
			'Palatino',
			'Garamond',
			'Bookman',
			'Tahoma',
			'Trebuchet MS',
			'Arial Black',
			'Impact',
			'Comic Sans MS',
		];

		/**
		 * Filters the list of system fonts to be excluded from optimization.
		 *
			 * @param array $system_fonts Array of system fonts.
		 */
		$system_fonts = wpm_apply_filters_typed( 'array', 'rocket_preload_fonts_system_fonts', $system_fonts );

		// To Delete Mock Data during implementation of https://github.com/wp-media/wp-rocket/issues/7306
		$font_data = [
			'https://fonts.gstatic.com/s/poppins/v22/pxiAyp8kv8JHgFVrJJLmE0tMMPKhSkFEkm8.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '100',
					'style'        => 'italic',
					'unicodeRange' => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiAyp8kv8JHgFVrJJLmE0tCMPKhSkFE.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '100',
					'style'        => 'italic',
					'unicodeRange' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiDyp8kv8JHgFVrJJLmv1pVGdeOYktMqlap.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '200',
					'style'        => 'italic',
					'unicodeRange' => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiDyp8kv8JHgFVrJJLmv1pVF9eOYktMqg.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '200',
					'style'        => 'italic',
					'unicodeRange' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiDyp8kv8JHgFVrJJLm21lVGdeOYktMqlap.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '300',
					'style'        => 'italic',
					'unicodeRange' => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiDyp8kv8JHgFVrJJLm21lVF9eOYktMqg.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '300',
					'style'        => 'italic',
					'unicodeRange' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiEyp8kv8JHgFVrJJLmg1hHGpeKQEk.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '400',
					'style'        => 'italic',
					'unicodeRange' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiFyp8kv8JHgFVrJJLucHtUFMNEKQ.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '500',
					'style'        => 'italic',
					'unicodeRange' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiGyp8kv8JHgFVrJJLsbX9NE9eO.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '600',
					'style'        => 'italic',
					'unicodeRange' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiGyp8kv8JHgFVrJJLsbXpNE9eO.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '900',
					'style'        => 'italic',
					'unicodeRange' => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC…',
				],
			],
			'https://fonts.gstatic.com/s/poppins/v22/pxiGyp8kv8JHgFVrLPTufntAOvWDSHFF.woff2' => [
				[
					'family'       => 'Poppins',
					'weight'       => '100',
					'style'        => 'normal',
					'unicodeRange' => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7…',
				],
			],
			'https://fonts.cdnfonts.com/s/31427/Paper Sign.woff' => [
				[
					'family'       => 'Paper Sign',
					'weight'       => '400',
					'style'        => 'normal',
					'unicodeRange' => 'U+0000-10FFFF',
				],
			],
		];

		/**
		 * Filters the list of mock font urls.
		 *
			* @param array $font_data Array of font data.
		 */
		$font_data = wpm_apply_filters_typed( 'array', 'rocket_preload_fonts_font_data', $font_data );

		$data['system_fonts']            = $system_fonts;
		$data['font_data']               = $font_data;
		$data['status']['preload_fonts'] = $this->context->is_allowed();

		return $data;
	}
}
