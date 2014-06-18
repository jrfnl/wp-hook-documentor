<?php
/**
 * @package wp-hook-documentor
 * @version 1.0.
 */
/*
Plugin Name: WP Hook Documentor Testcase
Plugin URI: https://github.com/jrfnl/wp-hook-documentor
Description: This is *NOT* a working plugin, but simply a file to test whether the WP Hook Documentor works as expected. This file is *NOT* meant to be included or run.
Version: 1.0
Author: Juliette Reinders Folmer
Author URI: http://adviesenzo.nl/
Text Domain: wp-hook-documentor
Domain Path: /languages/
*/

/*
GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



if ( !class_exists( 'wp_hook_documentor_another_testcase' ) ) {

	/**
	 * @package WordPress\Plugins\WP Hook Documentor Testcase
	 * @version 1.0
	 * @link https://github.com/jrfnl/wp-hook-documentor WP Hook Documentor on GitHub
	 *
	 * @copyright 2013 Juliette Reinders Folmer
	 * @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2
	 */
	class wp_hook_documentor_another_testcase {


		/**
		 * Object constructor
		 */
		function __construct() {
		}


		/**
		 * A method
		 *
		 * @todo - figure out a way to deal with duplicate filter names (in different files)!
		 *
		 * @return void
		 */
		public function test_duplicate_filter_names() {

			$this->my_property = apply_filters( 'filter_apply', $this->my_property );
			$this->my_property = apply_filters_ref_array( 'filter_apply_with_ref', $this->my_property );
			$this->my_property = do_action( 'action_do', $this->my_property );
			$this->my_property = do_action_ref_array( 'action_do_with_ref', $this->my_property );

		}



		/* *** FRONT-END: DISPLAY METHODS *** */


		/**
		 * Test method
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_sorting_on_hookname_filename( $param ) {

			$param = apply_filters( 'second_file_filter_z', $param );

			$param = apply_filters( 'second_file_filter_q', $param );

			$param = apply_filters( 'second_file_filter_g', $param );
			
			$param = apply_filters( 'second_file_filter_a', $param );

			return $param;
		}
		
		
		function test_variable_hook_names( $param ) {

			$param = apply_filters( 'filter_' . $variable, $param );

			$param = apply_filters( $variable . 'filter', $param );

			$param = apply_filters( 'filter_' . $variable . '_filter', $param );

			$param = apply_filters( 'filter_' . $variable . "_filter", $param );

			return $param;
		}



	} /* End of class */


} /* End of class-exists wrapper */

?>