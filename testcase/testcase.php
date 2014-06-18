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



if ( !class_exists( 'wp_hook_documentor_testcase' ) ) {

	/**
	 * @package WordPress\Plugins\WP Hook Documentor Testcase
	 * @version 1.0
	 * @link https://github.com/jrfnl/wp-hook-documentor WP Hook Documentor on GitHub
	 *
	 * @copyright 2013 Juliette Reinders Folmer
	 * @license http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2
	 */
	class wp_hook_documentor_testcase {


		/* *** CLASS CONSTANTS *** */

		/**
		 * @const string	Plugin version number
		 * @usedby upgrade_options(), __construct()
		 */
		const VERSION = '1.0';


		/* *** STATIC CLASS PROPERTIES *** */

		/**
		 * @staticvar	string	$my_static	This is a static class property
		 */
		public static $my_static;


		/* *** CLASS PROPERTIES *** */

		/**
		 * Tests for the @var/@param regex
		 * @var	string		$my_property	This is a normal class property
		 * @var	integer		$my_property	This is a normal class property
		 * @var	int			$my_property	This is a normal class property
		 * @var	boolean		$my_property	This is a normal class property
		 * @var	bool		$my_property	This is a normal class property
		 * @var	float		$my_property	This is a normal class property
		 * @var	double		$my_property	This is a normal class property
		 * @var	object		$my_property	This is a normal class property
		 * @var	mixed		$my_property	This is a normal class property
		 * @var	array		$my_property	This is a normal class property
		 * @var	resource	$my_property	This is a normal class property
		 * @var	void		$my_property	This is a normal class property
		 * @var	null		$my_property	This is a normal class property
		 * @var	callback	$my_property	This is a normal class property
		 * @var	false		$my_property	This is a normal class property
		 * @var	true		$my_property	This is a normal class property
		 * @var	self		$my_property	This is a normal class property
		 * @var	string|array	$my_property	This is a normal class property
		 * @var	int|bool	$my_property	This is a normal class property
		 */
		public $my_property;



		/**
		 * Object constructor
		 */
		function __construct() {
		}


		/**
		 * A method
		 *
		 * @return void
		 */
		public function test_hook_calls() {

			$this->my_property = apply_filters( 'filter_apply', $this->my_property );
			$this->my_property = apply_filters_ref_array( 'filter_apply_with_ref', $this->my_property );
			$this->my_property = do_action( 'action_do', $this->my_property );
			$this->my_property = do_action_ref_array( 'action_do_with_ref', $this->my_property );

		}






		/**
		 * Add method with parameters
		 *
		 * @since 1.0
		 *
		 * @param	array	$param1		A parameter
		 * @param	string	$param2		A second parameter
		 * @return	array
		 */
		function method_with_params( $param1, $param2 ) {
			return $param1 . $param2;
		}




		/**
		 * Test method
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_inline( $param ) {

			$param = apply_filters( 'no_comment', $param );

			// This is an inline comment
			$param = apply_filters( 'inline_slash', $param );

			# This is an inline comment
			$param = apply_filters( 'inline_hash', $param );

			/* This is an inline comment */
			$param = apply_filters( 'inline_starred', $param );

			/*
			 This is an inline comment
			 It spans two lines
			 */
			$param = apply_filters( 'inline_starred_multiline', $param );

			/* Add filter hook for param
			   @api string	$new_param Allows a developer to filter the param string
			   before it is send to the screen */
			$param = apply_filters( 'inline_star_with_tag', $param );

			/**
			 * This is an inline DocBlock comment
			 */
			$param = apply_filters( 'inline_docblock', $param );

			/**
			 * This is an inline DocBlock comment
			 * @api	string	$param	param description
			 */
			$param = apply_filters( 'inline_docblock_with_tag', $param );

			return $param;
		}

		/**
		 * Test method
		 *
		 * @api A description for the action
		 * @api string	A description of the parameters passed
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_docblock( $param ) {

			$param = do_action( 'top_docblock', $param );

			$param = do_action( 'top_docblock_ambigious', $param );

			return $param;
		}


		/**
		 * Test method
		 *
		 * @api A description for the action
		 * @api string	A description of the parameters passed
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_internal( $param ) {

			// @internal This is an internal comment - docblock should be used
			$param = apply_filters( 'filter-with-internal', $param );

			// This comment should not be ignored
			// @internal This is an internal comment and should be ignored
			$param = apply_filters( 'filter-with-internal-and-ok', $param );

			// @internal This is an internal comment - docblock should be ignored as ambiguous
			$param = apply_filters( 'filter-with-internal-and-ambigiuous', $param );

			return $param;
		}


		/**
		 * Test method
		 *
		 * @api A description for the filter
		 * @api string	A description of the string to be filtered
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_ignore( $param ) {

			// @ignore This is an ignored comment - docblock should be used
			$param = apply_filters( 'filter_with_ignore', $param );

			// This comment should not be ignored
			// @ignore This is an ignored comment and should be ignored
			$param = apply_filters( 'filter_with_ignore_and_ok', $param );

			// @ignore This is an ignored comment - docblock should be ignored as ambiguous
			$param = apply_filters( 'filter_with_ignore_and_ambigiuous', $param );

			return $param;
		}
		
		
		/**
		 * Test method
		 *
		 * @api A description for the filter
		 * @api string	A description of the string to be filtered
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_ignore_internal_combined( $param ) {

			// @ignore This is an ignored comment - docblock should be used
			// @internal This is an internal comment - docblock should be used
			$param = apply_filters( 'filter_with_ignore_internal', $param );

			// This comment should not be ignored
			// @ignore This is an ignored comment and should be ignored
			// @internal This is an internal comment and should be ignored
			$param = apply_filters( 'filter_with_ignore_internal_and_ok', $param );

			// @ignore This is an ignored comment - docblock should be ignored as ambiguous
			// @internal This is an internal comment - docblock should be ignored as ambiguous
			$param = apply_filters( 'filter_with_ignore_internal_and_ambigiuous', $param );

			return $param;
		}
		
		
		/**
		 * Test method
		 *
		 * @api A description for the action
		 * @api string	A description of the parameters passed
		 *
		 * @param 	string	$param
		 * @return string
		 */
		function test_comments_with_whitespace( $param ) {

			// This is an normal comment

			$param = apply_filters( 'filter-comment-extra-whitespace', $param );

			// This comment should not be ignored

			// @internal This is an internal comment and should be ignored


			$param = apply_filters( 'filter-with-internal-and-ok-extra-whitespace', $param );

			return $param;
		}



		function test_multiline_not_docblock( $param ) {

			// This line 1 of a slashed multiline comment
			// This line 2 of a slashed multiline comment
			$param = apply_filters( 'filter_multiline_slash', $param );

			# This line 1 of a hashed multiline comment
			# This line 2 of a hashed multiline comment
			$param = apply_filters( 'filter_multiline_hash', $param );

			/* This line 1 of a star-non DocBlock multiline comment */
			/* This line 2 of a star-non DocBlock comment */
			$param = apply_filters( 'filter_multiline_star', $param );

			// This line 1 of a slashed multiline comment
			# This line 2 of a hashed multiline comment
			/* This line 3 of a star-non DocBlock comment */
			$param = apply_filters( 'filter_multiline_mixed', $param );

			return $param;
		}


		function test_comment_not_alone_on_line( $param ) {

			$a = 1 + 2 + 3; // This comment belongs to this line, not to the line below
			$param = apply_filters( 'filter_comment_not_alone_on_line', $param );

			return $param;
		}


	} /* End of class */



	/* Instantiate our class */
	add_action( 'plugins_loaded', 'mimetypes_link_icons_init' );

	if( !function_exists( 'my_init' ) ) {
		/**
		 * Initialize the class
		 *
		 * @return void
		 */
		function my_init() {
			$GLOBALS['testcase'] = new wp_hook_documentor_testcase();
		}
	}



	if( !function_exists( 'do_something' ) ) {
		/**
		 * Function to do something
		 *
		 * @since 1.0
		 * @param	string	$content
		 * @return	string
		 */
		function do_something( $content ) {
			return $content;
		}
	}


} /* End of class-exists wrapper */

?>