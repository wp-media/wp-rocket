<?php

/**
 * Plugin Name: PDF Embedder Premium
 * Plugin URI: http://wp-pdf.com/
 * Description: Embed mobile-friendly PDFs straight into your posts and pages. No third-party services required. Compatible With Gutenberg Editor Wordpress
 * Version: 4.4.1
 * Author: Lever Technology LLC
 * Author URI: http://wp-pdf.com/
 * Text Domain: pdf-embedder
 * License: Premium Paid per WordPress site
 * 
 * Do not copy, modify, or redistribute without authorization from author Lever Technology LLC (contact@wp-pdf.com)
 * 
 * You need to have purchased a license to install this software on each website.
 * 
 * You are not authorized to use, modify, or distribute this software beyond the single site license(s) that you
 * have purchased.
 * 
 * You must not remove or alter any copyright notices on any and all copies of this software.
 * 
 * This software is NOT licensed under one of the public "open source" licenses you may be used to on the web.
 * 
 * For full license details, and to understand your rights, please refer to the agreement you made when you purchased it 
 * from our website at https://wp-pdf.com/
 * 
 * THIS SOFTWARE IS SUPPLIED "AS-IS" AND THE LIABILITY OF THE AUTHOR IS STRICTLY LIMITED TO THE PURCHASE PRICE YOU PAID 
 * FOR YOUR LICENSE.
 * 
 * Please report violations to contact@wp-pdf.com
 * 
 * Copyright Lever Technology LLC, registered company in the United States of America
 * 
 */

require_once( plugin_dir_path(__FILE__).'/core/commercial_pdf_embedder.php' );

class pdfemb_premium_mobile_pdf_embedder extends pdfemb_commerical_pdf_embedder {

	protected $PLUGIN_VERSION = '4.4.1';
    protected $WPPDF_STORE_URL = 'http://wp-pdf.com/';
    protected $WPPDF_ITEM_NAME = 'PDF Embedder Premium';
	protected $WPPDF_ITEM_ID = 287;
	
	// Singleton
	private static $instance = null;
	
	public static function get_instance() {
		if (null == self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	// ACTIVATION

	// Premium specific

	
	// SHORTCODES

	
	// AUX

	protected function get_eddsl_optname() {
		return 'eddsl_pdfemb_mobile_ls';
	}

	protected function get_default_options() {
		return array_merge( parent::get_default_options(),
			Array(
				'pdfemb_download' => 'on'
			) );
	}

	protected function my_plugin_basename() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			$basename = basename(dirname(__FILE__)).'/'.basename(__FILE__);
		}
		return $basename;
	}
	
	protected function my_plugin_url() {
		$basename = plugin_basename(__FILE__);
		if ('/'.$basename == __FILE__) { // Maybe due to symlink
			return plugins_url().'/'.basename(dirname(__FILE__)).'/';
		}
		// Normal case (non symlink)
		return plugin_dir_url( __FILE__ );
	}
	
}

// Global accessor function to singleton
function pdfembPDFEmbedderMobile() {
	return pdfemb_premium_mobile_pdf_embedder::get_instance();
}

// Initialise at least once
pdfembPDFEmbedderMobile();

?>
