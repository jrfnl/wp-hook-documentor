<?php
/**
 * File: class.directorywalker.php
 *
 * Simple Directory Walker
 *
 * Recursively walks through a directory and retrieve the names of all files and directories
 * Optionally filter the retrieved list for files with comply with a list of allowed extensions
 *
 * @author	Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *  <simple.directory.walker@adviesenzo.nl>
 *
 * @version	1.0
 * @since	2013-07-05 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen ©2013
 * @license http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 *
 */

if ( !class_exists( 'wpphd_directory_walker' ) ) {

	class wpphd_directory_walker {
		
		/**
		 * @const	string	version number of this class
		 */
		const VERSION = '1.0';
		

		/**
		 *
		 */
		static function get_file_list( $pathtodir, $recursive = false, $allowed_extensions = null ) {

			if( !is_bool( $recursive ) ) $recursive = false;

			if( isset( $allowed_extensions ) ) {
				if( is_string( $allowed_extensions ) ) {
					$allowed_extensions = explode( ',', $allowed_extensions );
				}
				else {
					$allowed_extensions = (array) $allowed_extensions;
				}
				$allowed_extensions = array_map( 'strtolower', $allowed_extensions );
			}

			if( count( $allowed_extensions ) > 0 ) {
				$filelist = self::traverse_directory( $pathtodir, $recursive, $allowed_extensions );
			}
			else {
				$filelist = self::traverse_directory( $pathtodir, $recursive );
			}
			return $filelist;
		}
		

		/**
		 *
		 */
		static function traverse_directory( $pathtodir, $recursive = false, $allowed_extensions = null, $prefix = '', $filelist = array() ) {
			
  			$slash = ( strrchr( $pathtodir, DIRECTORY_SEPARATOR ) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );

  			$list = scandir ( $pathtodir );

  			foreach( $list as $filename ) {
				
				// Skip if the file is an 'unsafe' one such as .htaccess or
				// higher directory references
				if( strpos( $filename, '.' ) !== 0 ) {
					
					$pathtofile = $pathtodir . $slash . $filename;

					// If it's a file, check against valid extensions and add to the list
					if( is_file( $pathtofile ) === true ) {
						if( !isset( $allowed_extensions ) ) {
							$filelist[] = $prefix . $filename;
						}
						else if( self::is_allowed_file( $filename, $allowed_extensions ) === true ) {
							$filelist[] = $prefix . $filename;
						}
					}
					// If it's a directory and recursive is true, run this function on the subdirectory
					elseif( is_dir( $pathtofile ) === true && $recursive === true) {
						$filelist = self::traverse_directory( $pathtofile . DIRECTORY_SEPARATOR, $recursive, $allowed_extensions, $prefix . $filename . DIRECTORY_SEPARATOR, $filelist );
					}

					unset( $pathtofile );
				}
				unset( $filename );
			}
			return $filelist;
		}
		

		/**
		 *
		 */
		static function is_allowed_file( $filename, $allowed_extensions ) {
			$pos = strrpos( $filename, '.' );
			if( $pos !== false ) {
				// Strip everything before and including the '.'
				$file_ext = strtolower( substr( $filename, ( $pos + 1 ) ) );
				// Check if the extension is in the allowed list
				if( in_array( $file_ext, $allowed_extensions ) ) {
					return true;
				}
			}
			return false;
		}

	} /* End of class */

} /* End of class-exists wrapper */

?>