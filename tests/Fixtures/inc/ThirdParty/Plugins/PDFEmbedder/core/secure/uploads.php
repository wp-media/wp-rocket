<?php

class pdfemb_SecureUploader {

	protected $pdfemb_secure = true;
	protected $pdfemb_cacheencrypted = true;
	public function __construct($pdfemb_secure=true, $pdfemb_cacheencrypted=true) {
		$this->pdfemb_secure = $pdfemb_secure;
		$this->pdfemb_cacheencrypted = $pdfemb_cacheencrypted;
	}
	
	public function intercept_uploads() { // Called in admin_init
		add_filter('wp_handle_upload_prefilter', array($this, 'custom_upload_filter') );
	}
	
	public function handle_downloads() { // Called in init
		if (!isset($_GET['pdfemb-serveurl']) && !isset($_GET['pdfemb-nonce'])) {
			return;
		}
		
		if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['pdfemb-nonce'])) {
			return;
		}

        $direct_download = false;
        if (isset($_GET['pdfemb-nonce'])) {
            if (!wp_verify_nonce($_GET['pdfemb-nonce'], 'pdfemb-secure-download-'.$_GET['pdfemb-serveurl'])) {
                return;
            }
            else {
                $direct_download = true;
            }
        }
		
		$pdfurl = $_GET['pdfemb-serveurl'];
		
		$filepath = $this->getSecurePath($pdfurl);
		if ($filepath != '' && file_exists($filepath)) {
			$filename = basename($filepath);

			if ( ! $this->is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
				@set_time_limit(0);
			}
			if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() && version_compare( phpversion(), '5.4', '<' ) ) {
				set_magic_quotes_runtime(0);
			}
			
			@session_write_close();
			if( function_exists( 'apache_setenv' ) ) {
				@apache_setenv('no-gzip', 1);
			}
			@ini_set( 'zlib.output_compression', 'Off' );

            if (ob_get_length()) {
	            ob_clean(); // Clear output buffer in case Unicode BOM was added by a PHP file saved in wrong encoding.
            }
			
			nocache_headers();
			header("Robots: none");
			header("Content-Type: application/octet-stream");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=\"" . $filename . ($direct_download ? "" : ".binary")."\"");
			header("Content-Transfer-Encoding: binary");
		
			if (!class_exists('pdfembSimpleRC4')) {
				include_once( dirname( __FILE__ ) . '/rc4_simple.php' );
			}

			$sk = $this->getSecretKey();

			$filetime = filemtime($filepath);
			$cache_to_file = ($filetime !== FALSE && !$direct_download && $this->pdfemb_cacheencrypted
				? $filepath.'.encrypted-cache.'.md5($filetime.'-'.$sk).'.pdf'
				: '');
			
			$myrc4 = $direct_download
                ? new pdfembDirectRC4('')
                : new pdfembSimpleRC4($sk);
			
			$this->readfile_chunked( $filepath, true, $myrc4, $cache_to_file );
			
		}
		else {
			header("Location: ".$pdfurl);
		}
		
		exit();
	}
	
	public function getSecurePath($pdfurl) {
		$upload_dir = wp_upload_dir();
		
		$basedir = trailingslashit($upload_dir['basedir']).'securepdfs/';
		$baseurl = trailingslashit(set_url_scheme($upload_dir['baseurl'])).'securepdfs/';
		
		$regex = '|^'.$baseurl.'(([^/]+/)*([^/]+\.pdf))$|';
		
		$matches = array();
		if (preg_match($regex, set_url_scheme($pdfurl), $matches)) {
			// Check for .. tricks

			if (!preg_match('|[\\\/]\.\.[\\\/]|', $pdfurl) && strpos($pdfurl, '\\') === FALSE) {
				return $basedir.$matches[1];
			}
			
		}
		return ''; // Wasn't a secure PDF
	}
	
	protected $myrc4;
	
	protected function readfile_chunked( $file, $retbytes=true, $myrc4, $cache_to_file='' ) {
	
		$chunksize = 1024 * 1024;
		$buffer    = '';
		$cnt       = 0;

		$reading_from_cache = false;
		$handle = false;
		$cache_handle = false;

		if ($cache_to_file != '') {
			$cache_handle    = @fopen( $cache_to_file, 'r' );
			if ($cache_handle === false) {
				$cache_handle    = @fopen( $cache_to_file, 'w' );
			}
			else {
				// Read from cache
				$reading_from_cache = true;
				$handle = $cache_handle;
				$cache_handle = false;
			}
		}

		$size = @filesize( $file );

		if ($reading_from_cache && @filesize( $cache_to_file ) != $size) {
			// Revert back to non-cached version since can't be trusted
			error_log("Cached encrypted PDF was not of correct filesize: ".$cache_to_file);
			$handle = false;
			$reading_from_cache = false;
			$cache_handle = false;
		}

		if ($handle === false) {
			$handle = @fopen( $file, 'r' );
		}

		if ( false === $handle ) {
			return false;
		}

		if ( $size = @filesize( $file ) ) {
			header("Content-Length: " . $size );
		}
	
		while ( ! @feof( $handle ) ) {
			$buffer = @fread( $handle, $chunksize );

			if (!$reading_from_cache) {
				$buffer = $myrc4->rc4_encrypt_block( $buffer );
			}
			
			echo $buffer;

			if (!$reading_from_cache && $cache_handle !== FALSE) {
				// Write to cache
				@fwrite($cache_handle, $buffer);
			}

			if ( $retbytes ) {
				$cnt += strlen( $buffer );
			}
		}
	
		$status = @fclose( $handle );

		if ($cache_handle !== FALSE) {
			@fclose( $cache_handle );
		}
	
		if ( $retbytes && $status ) {
			return $cnt;
		}
	
		return $status;
	}
	
	public function getSecretKey() {
		$sk = get_site_option("pdfemb-sk");
		
		if ($sk === FALSE) {
			$sk = md5(sprintf("pdfemb-%d-%d", rand(), time()));
			update_site_option("pdfemb-sk", $sk, 60*60*12);
		}
		
		return $sk;
	}
	
	// INTERCEPT UPLOADS
	
	public function custom_upload_filter($file) {
		/*
		 * array(5) {
			  ["name"]=>
			  string(43) "1306 Scottish Life Pension Plan Summary.pdf"
			  ["type"]=>
			  string(15) "application/pdf"
			  ["tmp_name"]=>
			  string(26) "/private/var/tmp/php6Kqybd"
			  ["error"]=>
			  int(0)
			  ["size"]=>
			  int(34165)
		 * }
		 *  
		 *  */

		if ($file['type'] == 'application/pdf' && $this->pdfemb_secure) {
			add_filter('upload_dir', array($this, 'custom_upload_dir'));
		}
		
		return $file;
	}
	
	protected function is_func_disabled( $function ) {
		$disabled = explode( ',',  ini_get( 'disable_functions' ) );
	
		return in_array( $function, $disabled );
	}
	
	public function custom_upload_dir($upload) {
		/*
		 * array(5) {
			  ["name"]=>
			  string(43) "Summary.pdf"
			  ["type"]=>
			  string(15) "application/pdf"
			  ["tmp_name"]=>
			  string(26) "/private/var/tmp/php6Kqybd"
			  ["error"]=>
			  int(0)
			  ["size"]=>
			  int(34165)
		 * }
		 *  
		 *  */
		
		// Override the year / month being based on the post publication date, if year/month organization is enabled
		if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
			// Generate the yearly and monthly dirs
			$time = current_time( 'mysql' );
			$y = substr( $time, 0, 4 );
			$m = substr( $time, 5, 2 );
			$upload['subdir'] = "/$y/$m";
		}
		
		$upload['subdir'] = '/securepdfs' . $upload['subdir'];
		$upload['path']   = $upload['basedir'] . $upload['subdir'];
		$upload['url']    = $upload['baseurl'] . $upload['subdir'];
		
		return $upload;
	}
	
	// HTACCESS FILES
	
	public function create_protection_files( $force = false, $method = false ) {
		if ( false === get_transient( 'pdfemb_check_protection_files' ) || $force ) {
			$wp_upload_dir = wp_upload_dir();
			$upload_path = $wp_upload_dir['basedir'] . '/securepdfs';
			wp_mkdir_p( $upload_path );
	
			// Make sure the /edd folder is created
			wp_mkdir_p( $upload_path );
	
			// Top level .htaccess file
			$rules = $this->get_htaccess_rules( $method );
			
			if (file_exists($upload_path.'/.htaccess')) {
				$contents = @file_get_contents($upload_path.'/.htaccess');
				if ($contents !== $rules) {
					// Update the .htaccess rules if they don't match
					@file_put_contents($upload_path.'/.htaccess', $rules);
				}
			} elseif(wp_is_writable($upload_path)) {
				// Create the file if it doesn't exist
				@file_put_contents($upload_path.'/.htaccess', $rules);
			}
	
			// Top level blank index.php
			if (!file_exists($upload_path.'/index.php') && wp_is_writable($upload_path) ) {
				@file_put_contents($upload_path.'/index.php', '<?php' . PHP_EOL . '// This file is intentionally blank.');
			}
	
			// Now place index.php files in all sub folders
			$folders = array();
			$this->scan_folders($upload_path, $folders);
			
			foreach ($folders as $folder) {
				// Create index.php, if it doesn't exist
				if (!file_exists($folder.'index.php') && wp_is_writable($folder)) {
					@file_put_contents($folder.'index.php', '<?php' . PHP_EOL . '// This file is intentionally blank.');
				}
			}
			// Check for the files every eight days
			set_transient('pdfemb_check_protection_files', true, 3600 * 24 * 8);
		}
	}
	
	protected function get_htaccess_rules( $method = false ) {
	
		if( empty( $method ) ) {
			$method = 'direct';
		}
	
		switch( $method ) {

			case 'redirect' :
				// Prevent directory browsing
				$rules = "Options -Indexes";
			break;
			
			case 'direct' :
			default :
				$rules = "Options -Indexes\n";
				$rules .= "Deny from all\n";
				$rules .= "<FilesMatch '\.(jpg|png|gif|mp3|ogg)$'>\n";
				$rules .= "Order Allow,Deny\n";
				$rules .= "Allow from all\n";
				$rules .= "</FilesMatch>\n";
				break;
				
		}
		return $rules;
	}
	
	protected function scan_folders( $path, &$return ) {
		$lists = @scandir($path);
	
		if (!empty($lists)) {
			foreach ($lists as $f) {
				if (is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != "..") {
					$finaldirpath = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );
					if (!in_array($finaldirpath, $return)) {
						$return[] = $finaldirpath;
					}
	
					$this->scan_folders($path . DIRECTORY_SEPARATOR . $f, $return);
				}
			}
		}
	}
	
}


?>