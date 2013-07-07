<?php
/**
 * File: class.directorywalker.php
 *
 * Simple Directory Walker
 *
 * Walks through a directory and retrieve the names of all files and directories
 * Optionally recursively walks child-directories
 * Optionally filter the retrieved list for files with comply with a list of allowed extensions
 * Results are cached for best performance
 *
 * @author	Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *	<simple.directory.walker@adviesenzo.nl>
 *
 * @version	1.0
 * @since	2013-07-05 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen �2013
 * @license http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 *
 */

if ( !class_exists( 'wpd_directory_walker' ) ) {

	class wpd_directory_walker {

		/**
		 * @const	string	version number of this class
		 */
		const VERSION = '1.0';

		/**
		 * @var array	$cache
		 */
		protected static $cache = array();


		/**
  		 * Retrieve the (cached) file list for path
  		 *
		 * @param	string			$path
		 * @param	bool			$recursive
		 * @param	string|array	$allowed_extensions
		 * @return	array
		 */
		static function get_file_list( $path, $recursive = false, $allowed_extensions = null ) {

			// Validate and prep received parameters
			if( !is_bool( $recursive ) ) {
				$recursive = false;
			}

			$ext_string = 'all';
			$allowed_extensions = self::prep_allowed_exts( $allowed_extensions );
			if( isset( $allowed_extensions ) ) {
				$ext_string = implode( '_', $allowed_extensions );
			}

			// Retrieve the file list if not in cache yet
			if( !isset( self::$cache[$path][$recursive][$ext_string] ) ) {

				if( count( $allowed_extensions ) > 0 ) {
					self::$cache[$path][$recursive][$ext_string] = self::traverse_directory( $path, $recursive, $allowed_extensions );
				}
				else {
					self::$cache[$path][$recursive][$ext_string] = self::traverse_directory( $path, $recursive );
				}
			}

			return self::$cache[$path][$recursive][$ext_string];
		}


		/**
         * Validate and type cast the passed $allowed_extensions
         *
		 * @param	mixed	$allowed_extensions
		 * @return	array|null
		 */
		static function prep_allowed_exts( $allowed_extensions = null ) {

			if( isset( $allowed_extensions ) ) {
				// Make sure it's an array
				if( is_string( $allowed_extensions ) && $allowed_extensions !== '' ) {
					$allowed_extensions = explode( ',', $allowed_extensions );
				}
				else {
					$allowed_extensions = (array) $allowed_extensions;
				}

				// Make the array content consistent
				if( count( $allowed_extensions ) > 0 ) {
					$allowed_extensions = array_map( 'strtolower', $allowed_extensions );
					sort( $allowed_extensions );
				}
				// or 'unset' it if there's nothing there
				else {
					$allowed_extensions = null;
				}
			}

			return $allowed_extensions;
		}


		/**
         * Traverse a directory listing and return an array with file names
         *
         * Purposefully ignores directory entries starting with a '.' so as to prevent 'unsafe'
         * files, such as .htaccess and higehr directories getting in the list.
         * Note: this also means that directories such as /.git/ and /.idea/ will be ignored too.
         *
		 * @param	string			$path
		 * @param	bool			$recursive
		 * @param	string|array	$allowed_extensions
		 * @param	string			$prefix
		 * @param	array			$file_list
		 * @return	array
		 */
		static function traverse_directory( $path, $recursive = false, $allowed_extensions = null, $prefix = '', $file_list = array() ) {

			$slash = ( strrchr( $path, DIRECTORY_SEPARATOR ) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );

			$list = scandir ( $path );

			if( is_array( $list ) && count( $list ) > 0 ) {

				foreach( $list as $filename ) {

					// Skip if the file is an 'unsafe' one such as .htaccess or
					// higher directory references
					if( strpos( $filename, '.' ) !== 0 ) {

						$path_to_file = $path . $slash . $filename;

						// If it's a file, check against valid extensions and add to the list
						if( is_file( $path_to_file ) === true ) {
							if( !isset( $allowed_extensions ) ) {
								$file_list[] = $prefix . $filename;
							}
							else if( self::is_allowed_file( $filename, $allowed_extensions ) === true ) {
								$file_list[] = $prefix . $filename;
							}
						}
						// If it's a directory and recursive is true, run this function on the subdirectory
						elseif( is_dir( $path_to_file ) === true && $recursive === true) {
							$file_list = self::traverse_directory( $path_to_file . DIRECTORY_SEPARATOR, $recursive, $allowed_extensions, $prefix . $filename . DIRECTORY_SEPARATOR, $file_list );
						}

						unset( $path_to_file );
					}
					unset( $filename );
				}
			}

			return $file_list;
		}


		/**
         * Check if a file name has one of the allowed extensions
         *
		 * @param	string	$file_name
		 * @param	array	$allowed_extensions
		 * @return	bool
		 */
		static function is_allowed_file( $file_name, $allowed_extensions ) {
			$pos = strrpos( $file_name, '.' );
			if( $pos !== false ) {
				// Strip everything before and including the '.'
				$file_ext = strtolower( substr( $file_name, ( $pos + 1 ) ) );
				// Check if the extension is in the allowed list
				if( in_array( $file_ext, $allowed_extensions ) ) {
					return true;
				}
			}
			return false;
		}


        /**
         * Clear the file list cache for one set of parameters or clear the complete cache if no path is given
         *
         * @param	string			$path
         * @param	bool			$recursive
         * @param	string|array	$allowed_extensions
         * @return	void
         */
        public static function clear_file_list( $path = null, $recursive = false, $allowed_extensions = null ) {

            // Validate and prep received parameters
            if( !is_bool( $recursive ) ) {
                $recursive = false;
            }

            $ext_string = 'all';
            $allowed_extensions = self::prep_allowed_exts( $allowed_extensions );
            if( isset( $allowed_extensions ) ) {
                $ext_string = implode( '_', $allowed_extensions );
            }

            // Clear (selected) cache
            if( is_string( $path ) ) {
                unset( self::$cache[$path][$recursive][$ext_string] );
            }
            else {
                self::$cache = array();
            }
        }


    } /* End of class */

} /* End of class-exists wrapper */

?>