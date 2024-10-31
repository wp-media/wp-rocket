<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

class Filesystem {
    /**
     * WP Filesystem instance
     *
     * @var WP_Filesystem_Direct
     */
    private $filesystem;

    /**
     * Path to the fonts storage
     *
     * @var string
     */
    private $path;

    /**
     * Instantiate the class
     *
     * @param string               $base_path Base path to the fonts storage.
     * @param WP_Filesystem_Direct $filesystem WP Filesystem instance.
     */
    public function __construct( $base_path, $filesystem = null ) {
        $this->filesystem = is_null( $filesystem ) ? rocket_direct_filesystem() : $filesystem;
        $this->path       = $base_path . get_current_blog_id() . '/';
    }

    /**
     * Write font css to path
     *
     * @param string $url
     * @param string $font_url
     *
     * @return bool
    */
    public function write_font_css( string $url, string $font_url ): bool {
        $font_provider = $this->get_font_provider_path( $font_url );
        $file          = $this->get_fonts_full_path( $url, $font_provider );

        if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
            return false;
        }

        $css_content = $this->download_font( $font_url );

        if( ! $css_content ) {
            return false;
        }

        preg_match_all('/url\((https:\/\/[^)]+)\)/', $css_content, $matches);
        $font_urls = $matches[1];
        $local_css = $css_content;

        foreach ( $font_urls as $font_url ) {
            $parsed_url = parse_url( $font_url );
            $path_parts = explode( '/', trim( $parsed_url['path'], '/' ) );
            $local_path = $file . implode('/', $path_parts );
            $local_dir = dirname( $local_path );

            rocket_mkdir_p( dirname( $local_dir ) );

            if ( ! file_exists( $local_path ) ) {
				$font_data = wp_remote_get( $font_url );

				if ( is_wp_error( $font_data ) ) {
					error_log($font_data->get_error_message());
					continue;
				}

				$font_content = wp_remote_retrieve_body( $font_data );

				error_log(print_r(
					$font_content
					, true )
				);

				if ( ! $this->filesystem->put_contents( $local_path, 'pododl', true ) ) {
					// Output error message for debugging
					error_log( print_r( $this->filesystem->errors, true ) );
				}
            }

			$local_url = content_url('/cache/wp-rocket/fonts/google-fonts/' . implode('/', $path_parts ) );
			$local_css = str_replace( $font_url, $local_url, $local_css );
        }
return true;
        //error_log(print_r($css_content, true ));die;
    }

    private function download_font( string $url ) {
        $content = wp_remote_retrieve_body( wp_remote_get( $url, [
			'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
			'httpversion' => '2.0',
		] ) );

        if ( ! $content ) {
            return false;
        }

        return $content;
    }

    /**
     * Get the fonts path for the css file.
     *
     * @param string $url Url of the page.
     *
     * @return string Path for the font file.
     */
    private function get_fonts_full_path( string $url, string $font_provider_path ) {
        return $this->path . $font_provider_path . md5( $url ) . '/';
    }

    /**
     * Get the fonts provider path
     *
     * @param string $font_url The url of the fonts.
     *
     * @return string
    */
    private function get_font_provider_path( string $font_url ): string {
        return 'google-fonts/';
    }
}
