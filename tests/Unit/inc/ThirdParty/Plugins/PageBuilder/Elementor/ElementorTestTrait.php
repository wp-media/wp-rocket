<?php

namespace ThirdParty\Plugins\PageBuilder\Elementor;
use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;

trait ElementorTestTrait
{
	/**
	 * @var Options_Data
	 */
	protected $options;

	protected $filesystem;

	/**
	 * @var HTML
	 */
	protected $delayjs_html;

	/**
	 * @var UsedCSS
	 */
	protected $used_css;

	/**
	 * @var AjaxHandler
	 */
	protected $ajax_handler;

	/**
	 * @var Elementor
	 */
	protected $elementor;

	public function set_up() {
		parent::set_up();
		$this->options = Mockery::mock(Options_Data::class);
		$this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);
		$this->delayjs_html = Mockery::mock(HTML::class);
		$this->used_css = Mockery::mock(UsedCSS::class);
		$this->ajax_handler = Mockery::mock(AjaxHandler::class);

		$this->elementor = new Elementor($this->options, $this->filesystem, $this->delayjs_html, $this->used_css, $this->ajax_handler);
	}
}
