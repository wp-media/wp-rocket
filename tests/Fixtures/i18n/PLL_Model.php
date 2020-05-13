<?php

class PLL_Model {
	public $langs = [];

	public function __construct( $options ) {
		require_once __DIR__ . '/PLL_Language.php';

		foreach ( $options as $option ) {
			$pll_lang              = new PLL_Language();
			$pll_lang->slug        = $option['slug'];
			$pll_lang->description = $option['locale'];
			$pll_lang->home_url    = $option['url'];
			$pll_lang->search_url  = $option['url'];
			$this->langs[]         = $pll_lang;
		}
	}

	public function get_languages_list() {
		return $this->langs;
	}
}
