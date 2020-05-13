<?php
namespace WP_Rocket\Busting;

use WP_Rocket\Logger\Logger;

trait File_Busting {
	/**
	 * Saves the content of the URL to bust to the busting file if it doesn't exist yet.
	 *
	 * @since  3.2.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to get the content from.
	 * @return bool
	 */
	public function save( $url ) {
		if ( $this->get_busting_version() ) {
			// We have a local copy.
			Logger::debug(
				'Found local file.',
				[
					self::LOGGER_CONTEXT,
					'path' => $this->get_busting_path(),
				]
			);
			return true;
		}

		if ( $this->refresh_save( $url ) ) {
			// We downloaded a fresh copy.
			Logger::debug(
				'New copy downloaded.',
				[
					self::LOGGER_CONTEXT,
					'path' => $this->get_busting_path(),
				]
			);
			return true;
		}

		return false;
	}

	/**
	 * Deletes the busting file.
	 *
	 * @since  3.1
	 * @since  3.2.4 Handle versioning.
	 * @access public
	 * @author Remy Perona
	 * @author Grégory Viguier
	 *
	 * @return bool True on success. False on failure.
	 */
	public function delete() {
		$files = $this->get_all_files();

		if ( false === $files ) {
			// Error.
			return false;
		}

		$this->file_version = null;

		if ( ! $files ) {
			// No local files yet.
			return true;
		}

		return $this->delete_files( \array_keys( $files ) );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** LOCAL FILE ============================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the version of the current busting file.
	 *
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string|bool Version of the file. False if the file does not exist.
	 */
	protected function get_busting_version() {
		if ( isset( $this->file_version ) ) {
			return $this->file_version;
		}

		$files = $this->get_all_files();

		if ( ! $files ) {
			// Error or no local files yet.
			return false;
		}

		// Since we're not supposed to have several files, return the first one.
		$this->file_version = \reset( $files );

		return $this->file_version;
	}

	/**
	 * Get all cached files in the directory.
	 * In a perfect world, there should be only one.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool|bool A list of file names (as array keys) and versions (as array values). False on failure.
	 */
	private function get_all_files() {
		$dir_path = \rtrim( $this->busting_path, '\\/' );

		if ( ! $this->filesystem->exists( $dir_path ) ) {
			return [];
		}

		if ( ! $this->filesystem->is_readable( $dir_path ) ) {
			Logger::error(
				'Directory is not readable.',
				[
					self::LOGGER_CONTEXT,
					'path' => $dir_path,
				]
			);
			return false;
		}

		$dir = $this->filesystem->dirlist( $dir_path );

		if ( false === $dir ) {
			Logger::error(
				'Could not get the directory contents.',
				[
					self::LOGGER_CONTEXT,
					'path' => $dir_path,
				]
			);
			return false;
		}

		if ( ! $dir ) {
			return [];
		}

		$list    = [];
		$pattern = $this->escape_file_name( $this->filename );
		$pattern = \sprintf( $pattern, '(?<version>(?:[a-f0-9]{32}|local))' );

		foreach ( $dir as $entry ) {
			if ( 'f' !== $entry['type'] ) {
				continue;
			}

			if ( \preg_match( '/^' . $pattern . '$/', $entry['name'], $matches ) ) {
				$list[ $entry['name'] ] = $matches['version'];
			}
		}

		return $list;
	}

	/**
	 * Get the final URL for the current cache busting file.
	 *
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string|bool URL of the file. False if the file does not exist.
	 */
	public function get_busting_url() {
		return $this->get_busting_file_url( $this->get_busting_version() );
	}

	/**
	 * Get the path to the current cache busting file.
	 *
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string|bool URL of the file. False if the file does not exist.
	 */
	protected function get_busting_path() {
		return $this->get_busting_file_path( $this->get_busting_version() );
	}

	/**
	 * Get the final URL for a cache busting file.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $version The file version.
	 * @return string|bool     URL of the file with this version. False if no versions are provided.
	 */
	private function get_busting_file_url( $version ) {
		if ( ! $version ) {
			return false;
		}

		$filename = $this->get_busting_file_name( $version );

		// This filter is documented in inc/functions/minify.php.
		return \apply_filters( 'rocket_js_url', $this->busting_url . $filename );
	}

	/**
	 * Get the local file name.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $version The file version.
	 * @return string|bool     The name of the file with this version. False if no versions are provided.
	 */
	private function get_busting_file_name( $version ) {
		if ( ! $version ) {
			return false;
		}

		return \sprintf( $this->filename, $version );
	}

	/**
	 * Get the local file path.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $version The file version.
	 * @return string|bool     Path to the file with this version. False if no versions are provided.
	 */
	private function get_busting_file_path( $version ) {
		if ( ! $version ) {
			return false;
		}

		return $this->busting_path . $this->get_busting_file_name( $version );
	}

	/**
	 * Escape a file name, to be used in a regex pattern (delimiter is `/`).
	 * `%s` conversion specifications are protected.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $file_name The file name.
	 * @return string
	 */
	private function escape_file_name( $file_name ) {
		$file_name = \explode( '%s', $file_name );
		$file_name = \array_map( 'preg_quote', $file_name );

		return \implode( '%s', $file_name );
	}

	/**
	 * Delete busting files.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  array $files A list of file names.
	 * @return bool         True if files have been deleted (or no files have been provided). False on failure.
	 */
	private function delete_files( $files ) {
		if ( ! $files ) {
			// ¯\_(ツ)_/¯
			return true;
		}

		$has_deleted = false;
		$error_paths = [];

		foreach ( $files as $file_name ) {
			if ( ! $this->filesystem->delete( $this->busting_path . $file_name, false, 'f' ) ) {
				$error_paths[] = $this->busting_path . $file_name;
			} else {
				$has_deleted = true;
			}
		}

		if ( $error_paths ) {
			// Group all deletion errors into one log.
			Logger::error(
				'Local file(s) could not be deleted.',
				[
					self::LOGGER_CONTEXT,
					'paths' => $error_paths,
				]
			);
		}

		return $has_deleted;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** UPDATE THE LOCAL FILE =================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Add new contents to a file. If the file doesn't exist, it is created.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $file_path     Path to the file to update.
	 * @param  string $file_contents New contents.
	 * @return string|bool           The file contents on success. False on failure.
	 */
	private function update_file_contents( $file_path, $file_contents ) {
		if ( ! $this->is_busting_dir_writable() ) {
			return false;
		}

		if ( ! \rocket_put_content( $file_path, $file_contents ) ) {
			Logger::error(
				'Contents could not be written into file.',
				[
					self::LOGGER_CONTEXT,
					'path' => $file_path,
				]
			);
			return false;
		}

		return $file_contents;
	}

	/**
	 * Tell if the directory containing the busting file is writable.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	private function is_busting_dir_writable() {
		if ( ! $this->filesystem->exists( $this->busting_path ) ) {
			\rocket_mkdir_p( $this->busting_path );
		}

		if ( ! $this->filesystem->is_writable( $this->busting_path ) ) {
			Logger::error(
				'Directory is not writable.',
				[
					self::LOGGER_CONTEXT,
					'paths' => $this->busting_path,
				]
			);
			return false;
		}

		return true;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** GET LOCAL/REMOTE CONTENTS =============================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get a file contents. If the file doesn't exist, new contents are fetched remotely.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $file_path Path to the file.
	 * @param  string $file_url  URL to the remote file.
	 * @return string|bool       The contents on success, false on failure.
	 */
	private function get_file_or_remote_contents( $file_path, $file_url ) {
		$content = $this->get_file_contents( $file_path );

		if ( $content ) {
			// We have a local file.
			return $content;
		}

		return $this->get_remote_contents( $file_url );
	}

	/**
	 * Get a file contents.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $file_path Path to the file.
	 * @return string|bool       The contents on success, false on failure.
	 */
	private function get_file_contents( $file_path ) {
		if ( ! $this->filesystem->exists( $file_path ) ) {
			Logger::error(
				'Local file does not exist.',
				[
					self::LOGGER_CONTEXT,
					'path' => $file_path,
				]
			);
			return false;
		}

		if ( ! $this->filesystem->is_readable( $file_path ) ) {
			Logger::error(
				'Local file is not readable.',
				[
					self::LOGGER_CONTEXT,
					'path' => $file_path,
				]
			);
			return false;
		}

		$content = $this->filesystem->get_contents( $file_path );

		if ( ! $content ) {
			Logger::error(
				'Local file is empty.',
				[
					self::LOGGER_CONTEXT,
					'path' => $file_path,
				]
			);
			return false;
		}

		return $content;
	}

	/**
	 * Get the contents of a URL.
	 *
	 * @since  3.2.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $url The URL to request.
	 * @return string|bool The contents on success. False on failure.
	 */
	private function get_remote_contents( $url ) {
		try {
			$response = \wp_remote_get( $url );
		} catch ( \Exception $e ) {
			Logger::error(
				'Remote file could not be fetched.',
				[
					self::LOGGER_CONTEXT,
					'url'      => $url,
					'response' => $e->getMessage(),
				]
			);
			return false;
		}

		if ( \is_wp_error( $response ) ) {
			Logger::error(
				'Remote file could not be fetched.',
				[
					self::LOGGER_CONTEXT,
					'url'      => $url,
					'response' => $response->get_error_message(),
				]
			);
			return false;
		}

		$contents = \wp_remote_retrieve_body( $response );

		if ( ! $contents ) {
			Logger::error(
				'Remote file could not be fetched.',
				[
					self::LOGGER_CONTEXT,
					'url'      => $url,
					'response' => $response,
				]
			);
			return false;
		}

		return $contents;
	}
}
