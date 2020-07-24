<?php

/**
 * Plugin Name: PDF Embedder Premium Secure
 * Plugin URI: http://wp-pdf.com/
 * Description: Embed secure undownloadable PDFs straight into your posts and pages, with flexible width and height. No third-party services required. Compatible With Gutenberg Editor Wordpress
 * Version: 4.4.1
 * Author: Lever Technology LLC
 * Author URI: http://wp-pdf.com/
 * Text Domain: pdf-embedder
 * License: Premium Paid per WordPress site
 *
 * Do not copy, modify, or redistribute without authorization from author Lesterland Ltd (contact@wp-pdf.com)
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
 * Copyright Levertechnology LLC, registered company in the United States of America
 *
 */

require_once( plugin_dir_path(__FILE__).'/core/commercial_pdf_embedder.php' );

class pdfemb_premium_secure_pdf_embedder extends pdfemb_commerical_pdf_embedder {

	protected $PLUGIN_VERSION = '4.4.1';
    protected $WPPDF_STORE_URL = 'http://wp-pdf.com/';
    protected $WPPDF_ITEM_NAME = 'PDF Embedder Secure';
	protected $WPPDF_ITEM_ID = 17;

	// Singleton
	private static $instance = null;

	public static function get_instance() {
		if (null == self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	// ACTIVATION
	public function pdfemb_activation_hook($network_wide) {
		$su = $this->get_SecureUploader();
		$su->create_protection_files(true);
		parent::pdfemb_activation_hook($network_wide);
	}

	// Premium specific

	protected function get_translation_array() {

		$su = $this->get_SecureUploader();

		$options = $this->get_option_pdfemb();

		return array_merge(parent::get_translation_array(),
				Array('k' => $su->getSecretKey(),
					  'is_admin' => (current_user_can('manage_options')),
					  'watermark_map' => apply_filters('pdfemb_watermark_map', $this->calculate_watermark_map()),
					  'watermark_evenpagesonly' => $options['pdfemb_wm_evenpages']));
	}

	protected function calculate_watermark_map() {
		$options = $this->get_option_pdfemb();

		$text_array = preg_split('/(\r\n|\n\r|\n|\r)/', $options['pdfemb_wm_text'], -1);

		if (count($text_array) == 0) {
			return array();
		}

		$notloggedin_text = __( 'Not logged in', 'pdf-embedder' );
		$var_names = array("{fullname}", "{username}", "{email}");
		$var_values = array($notloggedin_text, $notloggedin_text, $notloggedin_text);

		$current_user = wp_get_current_user();
		if ( ($current_user instanceof WP_User) && $current_user->ID ) {
			$var_values = array(
				$current_user->first_name . ' ' . $current_user->last_name,
				$current_user->user_login,
				$current_user->user_email
			);
		}

		$var_names = apply_filters('pdfemb_watermark_var_names', $var_names);
		$var_values = apply_filters('pdfemb_watermark_var_values', $var_values);

		$watermark_map = array();
		$fontsize = $this->int_or_default($options['pdfemb_wm_fontsize'], 20);

		$voffset = $this->int_or_default($options['pdfemb_wm_voffset'], 30);
		$cssstyles = array(
			'globalAlpha' => $this->int_or_default($options['pdfemb_wm_opacity'], 20)/100
		);
		foreach ($text_array as $text) {
			$watermark_map[] = array(
				str_replace($var_names, $var_values, $text),
				5, $voffset, $options['pdfemb_wm_halign'], $options['pdfemb_wm_rotate'], $fontsize, $cssstyles);
			$voffset += $fontsize/6.5;
		}

		return $watermark_map;
	}

	protected function int_or_default($val, $default) {
		if (is_numeric($val)) {
			return intval($val);
		}
		return $default;
	}

	// SHORTCODES

	protected function modify_pdfurl($url) {
		$su = $this->get_SecureUploader();

		$securepdfpath = $su->getSecurePath($url);

		if ($securepdfpath !== '') {
			// Turn into a secure version of the URL
			$url = parse_url(home_url('/'),  PHP_URL_PATH).'?pdfemb-serveurl='.urlencode($url);
		}

		return parent::modify_pdfurl($url);
	}

	// Downloader

	public function pdfemb_admin_init() {
		parent::pdfemb_admin_init();
		$su = $this->get_SecureUploader();
		$su->intercept_uploads();
		$su->create_protection_files(false);

		// For PDF Thumbnails mainly
		add_filter('pdfth_pdf_direct_download_url', array($this, 'pdfth_pdf_direct_download_url'), 1, 10);
		add_filter('pdfth_pdf_is_secure', array($this, 'pdfth_pdf_is_secure'), 2, 10);
	}

	public function pdfemb_init() {
		$su = $this->get_SecureUploader();
		$su->handle_downloads();
		parent::pdfemb_init();
	}

	protected $_secureUploader = null;
	protected function get_SecureUploader() {
		if (is_null($this->_secureUploader)) {
			include_once( dirname( __FILE__ ) . '/core/secure/uploads.php' );
            $options = $this->get_option_pdfemb();
			$this->_secureUploader = new pdfemb_SecureUploader($options['pdfemb_secure'], $options['pdfemb_cacheencrypted']);
		}
		return $this->_secureUploader;
	}

    protected function extra_shortcode_attrs($atts, $content=null)
    {
        $options = $this->get_option_pdfemb();

        $securemore = '';
	    $disablerightclick_html = '';

        if (isset($atts['url'])) {

            $su = $this->get_SecureUploader();

            $securepdfpath = $su->getSecurePath($atts['url']);

            if ($securepdfpath !== '') {
                // Is a secure PDF

                $download = isset($atts['download']) ? $atts['download'] : (isset($options['pdfemb_download']) && $options['pdfemb_download'] ? 'on' : 'off');
                if (!in_array($download, array('on', 'off'))) {
                    $download = 'off';
                }

                if ($download == 'on') {
                    $securemore = ' data-download-nonce="' . wp_create_nonce('pdfemb-secure-download-' . $atts['url']) . '"';
                }

	            $disablerightclick = isset($atts['disablerightclick']) ? $atts['disablerightclick'] : (isset($options['pdfemb_disablerightclick']) && $options['pdfemb_disablerightclick'] ? 'on' : 'off');
	            if (!in_array($disablerightclick, array('on', 'off'))) {
		            $disablerightclick = 'off';
	            }

	            if ($disablerightclick == 'on') {
		            $disablerightclick_html = ' data-disablerightclick="on"';
	            }

            }
        }

        return parent::extra_shortcode_attrs($atts, $content).$securemore.$disablerightclick_html;
    }

	// Attachment page

	protected function output_the_content($pdfurl) {
		$options = $this->get_option_pdfemb();
		if (!$options['pdfemb_secureattpages'] && $this->is_secure($pdfurl)) {
			$content = '<p>'.esc_html__('Attachment Pages are disabled for secure PDFs (your admin can enable them in the Secure tab of PDF Embedder settings).', 'pdf-embedder').'</p>';
		}
		else {
			$content = parent::output_the_content($pdfurl);
		}
		return $content;
	}

	protected function is_secure($pdfurl) {
		$su = $this->get_SecureUploader();
		$securepdfpath = $su->getSecurePath($pdfurl);
		return $securepdfpath !== '';
	}

	// For PDF Thumbnails
	public function pdfth_pdf_direct_download_url($pdfurl) {

		$su = $this->get_SecureUploader();
		$securepdfpath = $su->getSecurePath($pdfurl);

		if ($securepdfpath !== '') {
			$pdfurl = parse_url(home_url('/'),  PHP_URL_PATH).'?pdfemb-serveurl='.urlencode($pdfurl).'&pdfemb-nonce='.wp_create_nonce('pdfemb-secure-download-' . $pdfurl);
		}

		return $pdfurl;
	}

    public function pdfth_pdf_is_secure($pdfurl) {
	    $su = $this->get_SecureUploader();
	    $securepdfpath = $su->getSecurePath($pdfurl);
        return $securepdfpath !== '';
    }

	// AUX

	protected function pdfemb_securesection_text()
	{
		$options = $this->get_option_pdfemb();
		?>

		<h2><?php _e('Secure PDFs', 'pdf-embedder'); ?></h2>

		<label for="pdfemb_secure" class="textinput"><?php _e('Secure PDFs', 'pdf-embedder'); ?></label>
		<span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_secure]' id='pdfemb_secure' class='checkbox' <?php echo $options['pdfemb_secure'] ? 'checked' : ''; ?> />
        <label for="pdfemb_secure" class="checkbox plain"><?php _e("Send new PDF media uploads to 'securepdfs' folder", 'pdf-embedder'); ?></label>
        </span>

		<br class="clear" />


		<label for="pdfemb_disablerightclick" class="textinput"><?php _e('Disable Right Click', 'pdf-embedder'); ?></label>
		<span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_disablerightclick]' id='pdfemb_disablerightclick' class='checkbox' <?php echo $options['pdfemb_disablerightclick'] == 'on' ? 'checked' : ''; ?> />
        <label for="pdfemb_disablerightclick" class="checkbox plain"><?php _e("Disable right-click mouse menu (affects secure PDFs only)", 'pdf-embedder'); ?></label>
        </span>

        <br class="clear" />


        <label for="pdfemb_cacheencrypted" class="textinput"><?php _e('Cache Encrypted PDFs', 'pdf-embedder'); ?></label>
        <span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_cacheencrypted]' id='pdfemb_cacheencrypted' class='checkbox' <?php echo $options['pdfemb_cacheencrypted'] == 'on' ? 'checked' : ''; ?> />
        <label for="pdfemb_cacheencrypted" class="checkbox plain"><?php _e("Cache encrypted versions of secure PDFs on the server (increase download speed)", 'pdf-embedder'); ?></label>
        </span>

        <br class="clear" />


        <label for="pdfemb_secureattpages" class="textinput"><?php _e('Attachment Pages', 'pdf-embedder'); ?></label>
        <span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_secureattpages]' id='pdfemb_secureattpages' class='checkbox' <?php echo $options['pdfemb_secureattpages'] == 'on' ? 'checked' : ''; ?> />
        <label for="pdfemb_secureattpages" class="checkbox plain"><?php _e("Auto-generate Attachment Pages for Secure PDFs", 'pdf-embedder'); ?></label>
        </span>


        <br class="clear" />
		<br class="clear" />

		<h2><?php _e('Watermark', 'pdf-embedder'); ?></h2>

		<label for="pdfemb_wm_text" class="textinput"><?php _e('Text to display on secure PDFs', 'pdf-embedder'); ?></label>
		<textarea id='pdfemb_wm_text' class='textinput' name='<?php echo $this->get_options_name(); ?>[pdfemb_wm_text]'><?php echo htmlentities($options['pdfemb_wm_text']); ?></textarea>
		<br class="clear"/>

		<p class="desc big"><i>Leave blank for no watermark on secure PDFs.
		<br />Variables you can use in the text: {fullname}, {username}, {email}</i></p>


		<label for="pdfemb_wm_halign" class="textinput"><?php esc_html_e('Horizontal alignment', 'pdf-embedder'); ?></label>
		<select name='<?php echo $this->get_options_name(); ?>[pdfemb_wm_halign]' id='pdfemb_wm_halign' class='select'>
			<option value="left" <?php echo $options['pdfemb_wm_halign'] == 'left' ? 'selected' : ''; ?>><?php esc_html_e('Left', 'pdf-embedder'); ?></option>
			<option value="center" <?php echo $options['pdfemb_wm_halign'] == 'center' ? 'selected' : ''; ?>><?php esc_html_e('Center', 'pdf-embedder'); ?></option>
			<option value="right" <?php echo $options['pdfemb_wm_halign'] == 'right' ? 'selected' : ''; ?>><?php esc_html_e('Right', 'pdf-embedder'); ?></option>
		</select>

		<br class="clear"/>

		<label for="pdfemb_wm_voffset" class="textinput"><?php _e('Vertical offset (%)', 'pdf-embedder'); ?></label>
		<input id='pdfemb_wm_voffset' class='textinput' name='<?php echo $this->get_options_name(); ?>[pdfemb_wm_voffset]' size='10' type='text' value='<?php echo esc_attr($options['pdfemb_wm_voffset']); ?>' />

		<br class="clear"/>

		<label for="pdfemb_wm_fontsize" class="textinput"><?php _e('Font Size (pt)', 'pdf-embedder'); ?></label>
		<input id='pdfemb_wm_fontsize' class='textinput' name='<?php echo $this->get_options_name(); ?>[pdfemb_wm_fontsize]' size='10' type='text' value='<?php echo esc_attr($options['pdfemb_wm_fontsize']); ?>' />

		<br class="clear"/>

		<label for="pdfemb_wm_opacity" class="textinput"><?php _e('Opacity (%)', 'pdf-embedder'); ?></label>
		<input id='pdfemb_wm_opacity' class='textinput' name='<?php echo $this->get_options_name(); ?>[pdfemb_wm_opacity]' size='10' type='text' value='<?php echo esc_attr($options['pdfemb_wm_opacity']); ?>' />

		<br class="clear"/>

		<label for="pdfemb_wm_rotate" class="textinput"><?php _e('Rotation (degrees)', 'pdf-embedder'); ?></label>
		<input id='pdfemb_wm_rotate' class='textinput' name='<?php echo $this->get_options_name(); ?>[pdfemb_wm_rotate]' size='10' type='text' value='<?php echo esc_attr($options['pdfemb_wm_rotate']); ?>' />

		<br class="clear"/>

		<label for="pdfemb_wm_evenpages" class="textinput"><?php _e('Page Display', 'pdf-embedder'); ?></label>
		<span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_wm_evenpages]' id='pdfemb_wm_evenpages' class='checkbox' <?php echo $options['pdfemb_wm_evenpages'] ? 'checked' : ''; ?> />
        <label for="pdfemb_wm_evenpages" class="checkbox plain"><?php _e("Show only on even page numbers", 'pdf-embedder'); ?></label>
        </span>

		<br class="clear"/>
		<br class="clear"/>

		<hr />

		<p><?php _e("If 'Secure PDFs' is checked above, your PDF uploads will be 'secure' by default.
            That is, they should be uploaded to a 'securepdfs' sub-folder of your uploads area. These files should not be accessible directly,
            and the plugin provides a backdoor method for the embedded viewer to obtain the file contents.", 'pdf-embedder'); ?></p>

		<p><?php _e("This means that your PDF is unlikely to be shared outside your site where you have no control over who views, prints, or shares it.
            Please note that it is still always possible for a determined user to obtain the original file. Sensitive information should never be presented to viewers in any form.", 'pdf-embedder'); ?></p>

		<p><?php _e('See <a href="http://wp-pdf.com/secure-instructions/?utm_source=PDF%20Settings%20Secure&utm_medium=premium&utm_campaign=Premium" target="_blank">Instructions</a> for more details.', 'pdf-embedder'); ?>
		</p>

		<?php
	}

	protected function get_eddsl_optname() {
		return 'eddsl_pdfemb_secure_ls';
	}

	protected function get_default_options() {
		return array_merge( parent::get_default_options(),
			Array(
				'pdfemb_download' => 'off',
				'pdfemb_secure' => true,
				'pdfemb_disablerightclick' => 'off',
                'pdfemb_secureattpages' => false,
                'pdfemb_cacheencrypted' => true,
				'pdfemb_wm_text' => '',
				'pdfemb_wm_halign' => 'center',
				'pdfemb_wm_voffset' => '30',
				'pdfemb_wm_fontsize' => '20',
				'pdfemb_wm_opacity' => '20',
				'pdfemb_wm_rotate' => '35',
				'pdfemb_wm_evenpages' => false
			) );
	}

	public function pdfemb_options_validate($input)
	{
		$newinput = parent::pdfemb_options_validate($input);

		$newinput['pdfemb_secure'] = isset($input['pdfemb_secure']) && ($input['pdfemb_secure'] === true || $input['pdfemb_secure'] == 'on');
		$newinput['pdfemb_disablerightclick'] = isset($input['pdfemb_disablerightclick']) && ($input['pdfemb_disablerightclick'] === true || $input['pdfemb_disablerightclick'] == 'on');

		$newinput['pdfemb_cacheencrypted'] = isset($input['pdfemb_cacheencrypted']) && ($input['pdfemb_cacheencrypted'] === true || $input['pdfemb_cacheencrypted'] == 'on');

		$newinput['pdfemb_secureattpages'] = isset($input['pdfemb_secureattpages']) && ($input['pdfemb_secureattpages'] === true || $input['pdfemb_secureattpages'] == 'on');

		$newinput['pdfemb_wm_text'] = isset($input['pdfemb_wm_text']) && !preg_match('/^[\n\r ]+$/', $input['pdfemb_wm_text']) ? $input['pdfemb_wm_text'] : '';

		if (isset($input['pdfemb_wm_halign']) && in_array($input['pdfemb_wm_halign'], array('left', 'center', 'right'))) {
			$newinput['pdfemb_wm_halign'] = $input['pdfemb_wm_halign'];
		}
		else {
			$newinput['pdfemb_wm_halign'] = 'center';
		}

		$newinput['pdfemb_wm_voffset'] = isset($input['pdfemb_wm_voffset']) ? $input['pdfemb_wm_voffset'] : '5';
		if (!is_numeric($newinput['pdfemb_wm_voffset'])) {
			add_settings_error(
				'pdfemb_wm_voffset',
				'notnumeric',
				self::get_error_string('pdfemb_wm_voffset|notnumeric'),
				'error'
			);
		}

		$newinput['pdfemb_wm_fontsize'] = isset($input['pdfemb_wm_fontsize']) ? $input['pdfemb_wm_fontsize'] : '20';
		if (!is_numeric($newinput['pdfemb_wm_fontsize'])) {
			add_settings_error(
				'pdfemb_wm_fontsize',
				'notnumeric',
				self::get_error_string('pdfemb_wm_fontsize|notnumeric'),
				'error'
			);
		}

		$newinput['pdfemb_wm_opacity'] = isset($input['pdfemb_wm_opacity']) ? $input['pdfemb_wm_opacity'] : '20';
		if (!is_numeric($newinput['pdfemb_wm_opacity'])) {
			add_settings_error(
				'pdfemb_wm_opacity',
				'notnumeric',
				self::get_error_string('pdfemb_wm_opacity|notnumeric'),
				'error'
			);
		}

		$newinput['pdfemb_wm_rotate'] = isset($input['pdfemb_wm_rotate']) ? $input['pdfemb_wm_rotate'] : '35';
		if (!is_numeric($newinput['pdfemb_wm_rotate'])) {
			add_settings_error(
				'pdfemb_wm_rotate',
				'notnumeric',
				self::get_error_string('pdfemb_wm_rotate|notnumeric'),
				'error'
			);
		}

		$newinput['pdfemb_wm_evenpages'] = isset($input['pdfemb_wm_evenpages']) && ($input['pdfemb_wm_evenpages'] === true || $input['pdfemb_wm_evenpages'] == 'on');

		return $newinput;
	}

	protected function get_error_string($fielderror) {
		$secure_local_error_strings = Array(
			'pdfemb_wm_voffset|notnumeric' => __('Watermark Vertical offset should be a numerical value between 0 and 100', 'pdf-embedder'),
			'pdfemb_wm_fontsize|notnumeric' => __('Font Size should be a numerical value', 'pdf-embedder'),
			'pdfemb_wm_opacity|notnumeric' => __('Watermark Opacity should be a numerical value between 0 and 100', 'pdf-embedder'),
			'pdfemb_wm_rotate|notnumeric' => __('Watermark Rotation should be a numerical value in degrees (0 to 360)', 'pdf-embedder')
		);
		if (isset($secure_local_error_strings[$fielderror])) {
			return $secure_local_error_strings[$fielderror];
		}
		return parent::get_error_string($fielderror);
	}

	// PDF Direct Links

    public function pdfemb_shortcode_direct_link($atts, $content=null) {
	    if (!isset($atts['url'])) {
		    return '<b>pdf-direct-link requires a url attribute</b>';
	    }

        $url = $this->pdfth_pdf_direct_download_url(set_url_scheme($atts['url']));

	    $innerhtml = '';
	    if (!empty($content)) {
		    $innerhtml = do_shortcode($content);
	    }

	    if (strlen($innerhtml) == 0) {
		    $innerhtml = isset($atts['text']) ? $atts['text'] : $atts['url'];
        }

	    $returnhtml = '<a href="'.esc_attr($url).'">'.$innerhtml.'</a>';

	    return $returnhtml;
    }

	protected function add_actions() {
		parent::add_actions();

		add_shortcode( 'pdf-direct-link', array($this, 'pdfemb_shortcode_direct_link') );
	}

    // Aux

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
function pdfembPDFEmbedderSecure() {
	return pdfemb_premium_secure_pdf_embedder::get_instance();
}

// Initialise at least once
pdfembPDFEmbedderSecure();

?>
