<?php

if (class_exists('core_pdf_embedder')) {
	global $pdfemb_core_already_exists;
	$pdfemb_core_already_exists = true;
}
else {
	require_once( plugin_dir_path( __FILE__ ) . '/core_pdf_embedder.php' );
}

class pdfemb_commerical_pdf_embedder extends core_pdf_embedder {

	// Premium specific

	public function pdfemb_wp_enqueue_scripts() {
		
		if (!$this->useminified()) {
			wp_register_script( 'pdfemb_grabtopan_js', $this->my_plugin_url().'js/grabtopan-premium-'.$this->PLUGIN_VERSION.'.js', array('jquery'), $this->PLUGIN_VERSION);
			wp_register_script( 'pdfemb_fullscreenpopup_js', $this->my_plugin_url().'js/jquery.fullscreen-popup-premium-'.$this->PLUGIN_VERSION.'.js', array('jquery'), $this->PLUGIN_VERSION);
			wp_register_script( 'pdfemb_pv_core_js', $this->my_plugin_url().'js/pdfemb-pv-core-'.$this->PLUGIN_VERSION.'.js',
								array('pdfemb_grabtopan_js', 'pdfemb_fullscreenpopup_js', 'jquery'), $this->PLUGIN_VERSION );
			wp_register_script( 'pdfemb_versionspecific_pdf_js', $this->my_plugin_url().'js/pdfemb-premium-'.$this->PLUGIN_VERSION.'.js', array('jquery', 'pdfemb_pv_core_js'), $this->PLUGIN_VERSION);
			wp_register_script( 'pdfemb_embed_pdf_js', $this->my_plugin_url().'js/pdfemb-embed-pdf-'.$this->PLUGIN_VERSION.'.js',
				array('pdfemb_pv_core_js', 'pdfemb_versionspecific_pdf_js'), $this->PLUGIN_VERSION );
		}
		else {
			wp_register_script( 'pdfemb_embed_pdf_js', $this->my_plugin_url().'js/all-pdfemb-premium-'.$this->PLUGIN_VERSION.'.min.js', array('jquery'), $this->PLUGIN_VERSION );
		}

		wp_localize_script( 'pdfemb_embed_pdf_js', 'pdfemb_trans', $this->get_translation_array() );

		wp_register_script( 'pdfemb_pdf_js', $this->my_plugin_url().'js/pdfjs/pdf-'.$this->PLUGIN_VERSION.''.($this->useminified() ? '.min' : '').'.js', array(), $this->PLUGIN_VERSION);
		
	}


	protected function get_extra_js_name() {
		return 'premium';
	}
	
	protected function add_actions() {
		parent::add_actions();

        // When viewing attachment page, embded document instead of link
        add_filter( 'the_content', array($this, 'pdfemb_the_content'), 20, 1 );

		add_action('wp_head', array($this, 'pdfemb_wp_head'));
	}
	
	public function pdfemb_wp_head() {
        $options = $this->get_option_pdfemb();
        if ( $options['pdfemb_resetviewport'] ) {
            echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />';
        }
	}

	public function pdfemb_activation_hook($network_wide) {
		global $pdfemb_core_already_exists;
		if ($pdfemb_core_already_exists) {
			deactivate_plugins( $this->my_plugin_basename() );
			echo( 'Please Deactivate the free version of PDF Embedder before you activate the new Premium version.' );
			exit;
		}
		parent::pdfemb_activation_hook($network_wide);
	}

	// ADMIN

    protected function draw_more_tabs() {
        ?>

        <a href="#license" id="license-tab" class="nav-tab"><?php esc_html_e('License', 'pdf-embedder'); ?></a>

        <?php
    }

    protected function pdfemb_mainsection_extra() {
        $options = $this->get_option_pdfemb();
        ?>
        <br class="clear" />
        <br class="clear" />

        <label for="pdfemb_scrollbar" class="textinput"><?php esc_html_e('Display Scrollbars', 'pdf-embedder'); ?></label>
        <span>

            <select name='<?php echo $this->get_options_name(); ?>[pdfemb_scrollbar]' id='pdfemb_scrollbar' class='select'>
                <option value="none" <?php echo $options['pdfemb_scrollbar'] == 'none' ? 'selected' : ''; ?>><?php esc_html_e('None', 'pdf-embedder'); ?></option>
                <option value="vertical" <?php echo $options['pdfemb_scrollbar'] == 'vertical' ? 'selected' : ''; ?>><?php esc_html_e('Vertical', 'pdf-embedder'); ?></option>
                <option value="horizontal" <?php echo $options['pdfemb_scrollbar'] == 'horizontal' ? 'selected' : ''; ?>><?php esc_html_e('Horizontal', 'pdf-embedder'); ?></option>
                <option value="both" <?php echo $options['pdfemb_scrollbar'] == 'both' ? 'selected' : ''; ?>><?php esc_html_e('Both', 'pdf-embedder'); ?></option>
            </select>

            <br class="clear"/>

        <p class="desc big"><i><?php _e('User can still use mouse if scrollbars not visible', 'pdf-embedder'); ?></i></p>

        </span>


        <label for="pdfemb_continousscroll" class="textinput"><?php esc_html_e('Continous Page Scrolling', 'pdf-embedder'); ?></label>
        <span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_continousscroll]' id='pdfemb_continousscroll' class='checkbox' <?php echo $options['pdfemb_continousscroll'] ? 'checked' : ''; ?> />
        <label for="pdfemb_continousscroll" class="checkbox plain"><?php esc_html_e('Allow user to scroll up/down between all pages in the PDF (if unchecked, user must click next/prev buttons to change page)', 'pdf-embedder'); ?></label>
        </span>

        <br class="clear" />
        <br class="clear" />

        <label for="pdfemb_download" class="textinput"><?php esc_html_e('Download Button', 'pdf-embedder'); ?></label>
        <span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_download]' id='pdfemb_download' class='checkbox' <?php echo $options['pdfemb_download'] == 'on' ? 'checked' : ''; ?> />
        <label for="pdfemb_download" class="checkbox plain"><?php esc_html_e('Provide PDF download button in toolbar', 'pdf-embedder'); ?></label>
        </span>

        <br class="clear" />
        <br class="clear" />

        <label for="pdfemb_tracking" class="textinput"><?php esc_html_e('Track Views/Downloads', 'pdf-embedder'); ?></label>
        <span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_tracking]' id='pdfemb_tracking' class='checkbox' <?php echo $options['pdfemb_tracking'] == 'on' ? 'checked' : ''; ?> />
        <label for="pdfemb_tracking" class="checkbox plain"><?php printf(__('Count number of views and downloads (figures will be shown in <a href="%s">Media Library</a>)', 'pdf-embedder'), admin_url( 'upload.php' )); ?></label>
        </span>

        <br class="clear" />
        <br class="clear" />

        <label for="pdfemb_newwindow" class="textinput"><?php esc_html_e('External Links', 'pdf-embedder'); ?></label>
        <span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_newwindow]' id='pdfemb_newwindow' class='checkbox' <?php echo $options['pdfemb_newwindow'] == 'on' ? 'checked' : ''; ?> />
        <label for="pdfemb_newwindow" class="checkbox plain"><?php esc_html_e('Open links in a new browser tab/window', 'pdf-embedder'); ?></label>
        </span>

	    <br class="clear" />
	    <br class="clear" />

	    <label for="pdfemb_scrolltotop" class="textinput"><?php esc_html_e('Scroll to Top', 'pdf-embedder'); ?></label>
	    <span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_scrolltotop]' id='pdfemb_scrolltotop' class='checkbox' <?php echo $options['pdfemb_scrolltotop'] == 'on' ? 'checked' : ''; ?> />
        <label for="pdfemb_scrolltotop" class="checkbox plain"><?php esc_html_e('Scroll to top of page when user clicks next/prev', 'pdf-embedder'); ?></label>
        </span>

	    <?php
    }

	protected function no_toolbar_option($options) {
		?>
		<option value="none" <?php echo $options['pdfemb_toolbar'] == 'none' ? 'selected' : ''; ?>><?php esc_html_e('No Toolbar', 'pdf-embedder'); ?></option>
		<?php
	}

	protected function pdfemb_mobilesection_text() {
        $options = $this->get_option_pdfemb();

		?>
		<h2><?php esc_html_e('Default Mobile Settings', 'pdf-embedder'); ?></h2>

        <p><?php esc_html_e("When the document is smaller than the width specified below, the document displays only as a 'thumbnail' with a large 'View in Full Screen' button for the user to click to open.", 'pdf-embedder'); ?></p>

        <label for="input_pdfemb_mobilewidth" class="textinput"><?php _e('Mobile Width', 'pdf-embedder'); ?></label>
        <input id='input_pdfemb_mobilewidth' class='textinput' name='<?php echo $this->get_options_name(); ?>[pdfemb_mobilewidth]' size='10' type='text' value='<?php echo esc_attr($options['pdfemb_mobilewidth']); ?>' />
		<br class="clear"/>

        <p class="desc big"><i><?php esc_html_e('Enter an integer number of pixels, or 0 to disable automatic full-screen', 'pdf-embedder'); ?></i></p>

        <br class="clear"/>

		<label for="pdfemb_resetviewport" class="textinput"><?php esc_html_e('Disable Device Zoom', 'pdf-embedder'); ?></label>
		<span>
        <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_resetviewport]' id='pdfemb_resetviewport' class='checkbox' <?php echo $options['pdfemb_resetviewport'] ? 'checked' : ''; ?> />
        <label for="pdfemb_resetviewport" class="checkbox plain"><?php esc_html_e('Enable if you are experiencing quality issues on mobiles', 'pdf-embedder'); ?></label>
        </span>

		<br class="clear"/>

		<p class="desc big"><i><?php esc_html_e('Some mobile browsers will use their own zoom, causing the PDF Embedder to render at a lower resolution than it should, or lose the toolbar off screen.', 'pdf-embedder'); ?>
				<?php esc_html_e('Enabling this option may help, but could potentially affect appearance in the rest of your site.', 'pdf-embedder'); ?>
				<?php printf(__('See <a href="%s" target="_blank">documentation</a> for details.', 'pdf-embedder'),
					'https://wp-pdf.com/troubleshooting/?utm_source=Premium%20ResetViewport&utm_medium=premium&utm_campaign=Premium#resetviewport'); ?>
			</i></p>

		<?php
	}

    protected function draw_extra_sections() {
        $options = $this->get_option_pdfemb();
        ?>
        <div id="license-section" class="pdfembtab">
        <p><?php _e('You should have received a license key when you purchased this premium version of PDF Embedder.', 'pdf-embedder'); ?></p>
        <p><?php printf( __('Please enter it below to enable automatic updates, or <a href="%s">email us</a> if you do not have one.', 'pdf-embedder'), 'mailto:contact@wp-pdf.com'); ?></p>

        <label for="input_pdfemb_license_key" class="textinput big"><?php esc_html_e('License Key', 'pdf-embedder'); ?></label>
        <input id='input_pdfemb_license_key' name='<?php echo $this->get_options_name(); ?>[pdfemb_license_key]' size='40' type='text' value='<?php echo esc_attr($options['pdfemb_license_key']); ?>' class='textinput' />
        <br class="clear" />

        <?php

        // Display latest license status

        $license_status = get_site_option($this->get_eddsl_optname(), true);

        if (is_array($license_status) && isset($license_status['license_id']) && $license_status['license_id'] != '') {
            echo '<br class="clear" />';
            echo '<table>';
            echo '<tr><td>'.esc_html__('Current License', 'pdf-embedder').': </td><td>'.htmlentities(isset($license_status['license_id']) ? $license_status['license_id'] : '').'</td></tr>';

            if (isset($license_status['status']) && $license_status['status'] != '') {
                echo '<tr><td>'.esc_html__('Status', 'pdf-embedder').': </td><td>'.htmlentities(strtoupper($license_status['status'])).'</td></tr>';
            }

            if (isset($license_status['last_check_time']) && $license_status['last_check_time'] != '') {
                echo '<tr><td>'.esc_html__('Last Checked', 'pdf-embedder').': </td><td>'.htmlentities(date("j M Y H:i:s",$license_status['last_check_time'])).'</td></tr>';
            }

            /* if (isset($license_status['first_check_time']) && $license_status['first_check_time'] != '') {
                echo '<p>Result First Seen: '.htmlentities(date("M j Y H:i:s",$license_status['first_check_time'])).'</p>';
            } */

            if (isset($license_status['expires_time'])) { // && $license_status['expires_time'] < time() + 24*60*60*30) {
                echo '<tr><td>'.esc_html__('License Expires', 'pdf-embedder').': </td><td>'.htmlentities(date("j M Y H:i:s",$license_status['expires_time'])).'</td></tr>';
            }

            /* if (isset($license_status['result_cleared'])) {
                echo '<p>Result cleared: '.($license_status['result_cleared'] ? 'yes' : 'no').'</p>';
            }*/

            echo '</table>';

            if (isset($license_status['expires_time']) && $license_status['expires_time'] < time() + 24*60*60*60) {
                echo '<p>';
                if (isset($license_status['renewal_link']) && $license_status['renewal_link']) {
                    printf(__('To renew your license, please <a href="%s" target="_blank">click here</a>.', 'pdf-embedder'), esc_attr($license_status['renewal_link']));
                }
                echo ' ';
                esc_html_e('You will receive a 50% discount if you renew before your license expires.', 'pdf-embedder');
                echo '</p>';
            }

	        echo '<br class="clear" />';

            ?>
            <span>
            <input type="checkbox" name='<?php echo $this->get_options_name(); ?>[pdfemb_allowbeta]' id='pdfemb_allowbeta' class='checkbox' <?php echo $options['pdfemb_allowbeta'] == 'on' ? 'checked' : ''; ?> />
            <label for="pdfemb_allowbeta" class="checkbox plain"><?php esc_html_e('Participate in future beta releases of the plugin', 'pdf-embedder'); ?></label>
            </span>

            <br class="clear" />

            <?php

	        if (isset($license_status['download_link'])) {
		        echo '<p>Download latest plugin ZIP <a href="'.$license_status['download_link'].'" target="_blank">here</a></p>';
		        echo '<br class="clear" />';
	        }

        }

        echo '</div>';

    }

    protected function edd_plugin_updater($license_key=null) {
	    $options = $this->get_option_pdfemb();
        if (is_null($license_key)) {
            $license_key = $options['pdfemb_license_key'];
        }

        if( !class_exists( 'EDD_SL_Plugin_Updater13' ) ) {
            // load our custom updater
            include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
        }

        // setup the updater
        $edd_updater = new EDD_SL_Plugin_Updater13( $this->WPPDF_STORE_URL, $this->my_plugin_basename(),
            array(
                'version' 	=> $this->PLUGIN_VERSION,
                'license' 	=> $license_key,
                'item_name' => $this->WPPDF_ITEM_NAME,
                'item_id' => $this->WPPDF_ITEM_ID,
                'author' 	=> 'Dan Lester',
                'beta'      => $options['pdfemb_allowbeta']
            ),
            $this->get_eddsl_optname(),
            $this->get_settings_url()."#license",
            false // Don't display admin panel warnings
        );

        return $edd_updater;
    }

    protected function get_eddsl_optname() {
        return null;
    }

    protected function edd_license_activate($license_key) {
        $edd_updater = $this->edd_plugin_updater($license_key);
        return $edd_updater->edd_license_activate();
    }

    public function pdfemb_admin_init() {
        $edd_updater = $this->edd_plugin_updater();
        $edd_updater->setup_hooks();

        $options = $this->get_option_pdfemb();
        if ($options['pdfemb_tracking'] == 'on') {
            add_action('wp_ajax_pdfemb_count_download', array($this, 'ajax_pdfemb_count_download'));
        }

        parent::pdfemb_admin_init();
    }

    protected function get_instructions_url() {
        return 'http://wp-pdf.com/premium-instructions/?utm_source=PDF%20Settings%20Main&utm_medium=premium&utm_campaign=Premium';
    }

    public function pdfemb_options_validate($input) {
        $newinput = parent::pdfemb_options_validate($input);

	    if (isset($input['pdfemb_scrollbar']) && in_array($input['pdfemb_scrollbar'], array('vertical', 'horizontal', 'both', 'none'))) {
		    $newinput['pdfemb_scrollbar'] = $input['pdfemb_scrollbar'];
	    }
	    else {
		    $newinput['pdfemb_scrollbar'] = 'none';
	    }
	    $newinput['pdfemb_continousscroll'] = isset($input['pdfemb_continousscroll']) && $input['pdfemb_continousscroll'];
        $newinput['pdfemb_download'] = isset($input['pdfemb_download']) && ($input['pdfemb_download'] === true || $input['pdfemb_download'] == 'on') ? 'on' : 'off';
        $newinput['pdfemb_tracking'] = isset($input['pdfemb_tracking']) && ($input['pdfemb_tracking'] === true || $input['pdfemb_tracking'] == 'on') ? 'on' : 'off';
  	    $newinput['pdfemb_newwindow'] = isset($input['pdfemb_newwindow']) && ($input['pdfemb_newwindow'] === true || $input['pdfemb_newwindow'] == 'on') ? 'on' : 'off';
	    $newinput['pdfemb_scrolltotop'] = isset($input['pdfemb_scrolltotop']) && ($input['pdfemb_scrolltotop'] === true || $input['pdfemb_scrolltotop'] == 'on') ? 'on' : 'off';
	    $newinput['pdfemb_resetviewport'] = isset($input['pdfemb_resetviewport']) && ($input['pdfemb_resetviewport'] === true || $input['pdfemb_resetviewport'] == 'on');

        $newinput['pdfemb_mobilewidth'] = $input['pdfemb_mobilewidth'];
        if (!isset($input['pdfemb_mobilewidth']) || !is_numeric($input['pdfemb_mobilewidth'])) {
            add_settings_error(
                'pdfemb_mobilewidth',
                'widtherror',
                self::get_error_string('pdfemb_mobilewidth|widtherror'),
                'error'
            );
        }

        // License Key
        $newinput['pdfemb_license_key'] = trim($input['pdfemb_license_key']);
        if ($newinput['pdfemb_license_key'] != '') {
            if(!preg_match('/^.{32}.*$/i', $newinput['pdfemb_license_key'])) {
                add_settings_error(
                    'pdfemb_license_key',
                    'tooshort_texterror',
                    self::get_error_string('pdfemb_license_key|tooshort_texterror'),
                    'error'
                );
            }
            else {
                // There is a valid-looking license key present

                $checked_license_status = get_site_option($this->get_eddsl_optname(), true);

                // Only bother trying to activate if we have a new license key OR the same license key but it was invalid on last check.
                $existing_valid_license = '';
                if (is_array($checked_license_status) && isset($checked_license_status['license_id']) && $checked_license_status['license_id'] != ''
                    && isset($checked_license_status['status']) && $checked_license_status['status'] == 'valid') {
                    $existing_valid_license = $checked_license_status['license_id'];
                }

                if ($existing_valid_license != $newinput['pdfemb_license_key']) {

                    $license_status = $this->edd_license_activate($newinput['pdfemb_license_key']);
                    if (isset($license_status['status']) && $license_status['status'] != 'valid') {
                        add_settings_error(
                            'pdfemb_license_key',
                            $license_status['status'],
                            self::get_error_string('pdfemb_license_key|'.$license_status['status']),
                            'error'
                        );
                    }
                }
            }
        }

        $newinput['pdfemb_allowbeta'] = isset($input['pdfemb_allowbeta']) && $input['pdfemb_allowbeta'];

        return $newinput;
    }

    protected function get_error_string($fielderror) {
        $premium_local_error_strings = Array(
            'pdfemb_mobilewidth|widtherror' => __('Mobile width should be an integer number of pixels, or 0 to turn off', 'pdf-embedder'),
            'pdfemb_license_key|tooshort_texterror' => __('License key is too short', 'pdf-embedder'),
            //	'valid', 'invalid', 'missing', 'item_name_mismatch', 'expired', 'site_inactive', 'inactive', 'disabled', 'empty'
            'pdfemb_license_key|invalid' => __('License key failed to activate', 'pdf-embedder'),
            'pdfemb_license_key|missing' => __('License key does not exist in our system at all', 'pdf-embedder'),
            'pdfemb_license_key|item_name_mismatch' => __('License key entered is for the wrong product', 'pdf-embedder'),
            'pdfemb_license_key|expired' => __('License key has expired', 'pdf-embedder'),
            'pdfemb_license_key|site_inactive' => __('License key is not permitted for this website', 'pdf-embedder'),
            'pdfemb_license_key|inactive' => __('License key is not active for this website', 'pdf-embedder'),
            'pdfemb_license_key|disabled' => __('License key has been disabled', 'pdf-embedder'),
            'pdfemb_license_key|empty' => __('License key was not provided', 'pdf-embedder')
        );
        if (isset($premium_local_error_strings[$fielderror])) {
            return $premium_local_error_strings[$fielderror];
        }
        return parent::get_error_string($fielderror);
    }

    protected function get_default_options() {
        return array_merge( parent::get_default_options(),
            Array(
                'pdfemb_continousscroll' => true,
	            'pdfemb_scrollbar' => 'none',
            	'pdfemb_mobilewidth' => '500',
                'pdfemb_license_key' => '',
                'pdfemb_tracking' => 'on',
	            'pdfemb_newwindow' => 'on',
	            'pdfemb_scrolltotop' => 'off',
	            'pdfemb_resetviewport' => false,
	            'pdfemb_allowbeta' => false
            ) );
    }

    protected function get_translation_array() {
	    $options = $this->get_option_pdfemb();
        return array_merge(parent::get_translation_array(),
            Array('continousscroll' => $options['pdfemb_continousscroll'],
                  'poweredby' => false,
                  'ajaxurl' => admin_url( 'admin-ajax.php' )));
    }

    public function pdfemb_attachment_fields_to_edit($form_fields, $post) {
        if ($post->post_mime_type == 'application/pdf') {
            $options = $this->get_option_pdfemb();
            if ($options['pdfemb_tracking'] == 'on') {

                $downloads = get_post_meta($post->ID, 'pdfemb-downloads', true);
                if (!is_numeric($downloads)) {
                    $downloads = 'None';
                }
                $views = get_post_meta($post->ID, 'pdfemb-views', true);
                if (!is_numeric($views)) {
                    $views = 'None';
                }
                $form_fields['pdfemb-downloads'] = array(
                    'value' => $downloads,
                    'input' => 'value',
                    'label' => __('Downloads'));
                $form_fields['pdfemb-views'] = array(
                    'value' => $views,
                    'input' => 'value',
                    'label' => __('Views'));

            }
        }
        return $form_fields;
    }

    public function pdfemb_init() {
        $options = $this->get_option_pdfemb();
        if ($options['pdfemb_tracking'] == 'on') {
            add_action('wp_ajax_nopriv_pdfemb_count_download', array($this, 'ajax_pdfemb_count_download'));
        }
        parent::pdfemb_init();
    }

	// Attachment page

	public function pdfemb_the_content( $content ) {
        global $post;
        if (isset($post) && 'attachment' == $post->post_type && is_singular() && isset($post->post_mime_type) && $post->post_mime_type == 'application/pdf') {
			$pdfurl = wp_get_attachment_url($post->ID);
	        if (!empty($pdfurl)) {
	            $content = $this->output_the_content($pdfurl);
	        }
        }
		return $content;
	}

	protected function output_the_content($pdfurl) {
	    return $this->pdfemb_shortcode_display_pdf(array('url' => $pdfurl));
	}

	// SHORTCODES

    private function shortcode_attr_data_on_off($keyname, $default, $atts, $options) {
	    $v = isset($atts[$keyname]) ? $atts[$keyname] : (isset($options['pdfemb_'.$keyname]) && $options['pdfemb_'.$keyname] == 'on' ? 'on' : 'off');
	    if (!in_array($v, array('on', 'off'))) {
		    $v = $default;
	    }
	    return ' data-'.$keyname.'="'.$v.'"';
    }

	protected function extra_shortcode_attrs($atts, $content=null) {
        $options = $this->get_option_pdfemb();

        $extraparams = '';

		$scrollbar = isset($atts['scrollbar']) && in_array($atts['scrollbar'], array('vertical', 'horizontal', 'both', 'none')) ? $atts['scrollbar'] : $options['pdfemb_scrollbar'];
		if (!in_array($scrollbar, array('vertical', 'horizontal', 'both', 'none'))) {
			$scrollbar = 'none';
		}

		$extraparams .= ' data-scrollbar="'.$scrollbar.'"';

		$extraparams .= $this->shortcode_attr_data_on_off('download', 'off', $atts, $options);

        if (isset($atts['page']) && preg_match('/^[0-9]+$/', $atts['page']) && $atts['page'] > 0) {
            $extraparams .= ' data-pagenum="'.esc_attr($atts['page']).'"';
        }

        $mobilewidth = '500';
		if (isset($atts['mobilewidth']) && is_numeric($atts['mobilewidth'])) {
            $mobilewidth = $atts['mobilewidth'];
		}
        elseif (isset($options['pdfemb_mobilewidth']) && is_numeric($options['pdfemb_mobilewidth'])) {
            $mobilewidth = $options['pdfemb_mobilewidth'];
        }

        // Record views if tracking enabled
        if ($options['pdfemb_tracking'] == 'on') {
            $this->count_views_or_downloads($atts['url'], 'views');
            $extraparams .= ' data-tracking="on"';
        }

		$extraparams .= $this->shortcode_attr_data_on_off('newwindow', 'on', $atts, $options);


		$pagetextbox = isset($atts['pagetextbox']) && $atts['pagetextbox'] == 'on' ? 'on' : 'off';
		$extraparams .= ' data-pagetextbox="'.$pagetextbox.'"';

		$extraparams .= $this->shortcode_attr_data_on_off('scrolltotop', 'off', $atts, $options);

		$zoom = isset($atts['zoom']) ? $atts['zoom'] : '100';
		if (!is_numeric($zoom) || $zoom < 20 || $zoom > 500) {
			$zoom = '100';
		}
		$extraparams .= ' data-startzoom="'.$zoom.'"';

		$fpzoom = isset($atts['fpzoom']) ? $atts['fpzoom'] : $zoom;
		if (!is_numeric($fpzoom) || $fpzoom < 20 || $fpzoom > 500) {
			$fpzoom = $zoom;
		}
		$extraparams .= ' data-startfpzoom="'.$fpzoom.'"';

		return 'data-mobile-width="'.esc_attr($mobilewidth).'" '.$extraparams;
	}

    protected function count_views_or_downloads($url, $type='views') {
        $count = 'N/A';
        $post_id = $this->get_attachment_id($url);
        if (!$post_id) {
            $post_id = $this->get_attachment_id($url, true);
        }
        if ($post_id) {
            $meta_name = 'pdfemb-' . $type;
            $count = get_post_meta($post_id, $meta_name, true);
            if (!is_numeric($count)) {
                $count = 0;
            }
            ++$count;
            update_post_meta($post_id, $meta_name, $count);
        }
        return $count;
    }

    protected function get_attachment_id($url, $invertscheme=false)
    {
        global $wpdb;

        if ($invertscheme) {
            $url = set_url_scheme($url, preg_match('#^https://#i', $url) ? 'http' : 'https');
        }

        $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url));
        if (is_array($attachment) && isset($attachment[0]) && $attachment[0] > 0) {
            return $attachment[0];
        }
        return false;
    }

    public function ajax_pdfemb_count_download() {
        $newcount = 'No pdf';
        if (isset($_POST['pdf_url'])) {
            $url = $_POST['pdf_url'];
            $matches = array();
            if (preg_match('#/\?pdfemb-serveurl\=([^&]+)#', $url, $matches)) {
                // Correct for Secure URLs
                $url = urldecode($matches[1]);
            }
            $newcount = $this->count_views_or_downloads($url, 'downloads');
        }

        wp_die($newcount);
    }

	// AUX

}

?>