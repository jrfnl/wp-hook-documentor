<?php
/**
 * File: index.php
 * @package wp-hook-documentor
 */

/**
 * WordPress Hook Documentor - Online frontend
 *
 * @package wp-hook-documentor
 * @author	Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *  <wp-hook-documentor@adviesenzo.nl>
 *
 * @version	0.2
 * @since	2013-07-03 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen �2013
 * @license http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @license	http://opensource.org/licenses/academic Academic Free License Version 1.2
 * @example	example/example.php
 *
 */

include_once( 'include/jrfdebug.inc.php' );


if ( !class_exists( 'wp_hook_documentor_frontend' ) ) {

	include( 'class.wp-hook-documentor.php' );

	class wp_hook_documentor_frontend extends wp_hook_documentor {

		public $is_post = false;

		public $show_docs = true;

		// @todo change this to the current directory
		// Current default is just for testing purposes
//		public $default_source = 'I:\000_GitHub\debug-bar-constants\debug-bar-constants';
//		public $default_source = 'I:\000_GitHub\WP Plugin Hook documentor\wp-hook-documentor\testcase';
//		public $default_source = 'I:\000_GitHub\WordPress_SEO_by_Yoast\wordpress-seo';
		public $default_source = 'testcase/';


        /**
         * Constructor
         */
        function __construct() {

			if( isset( $_POST ) && ( is_array( $_POST ) && count( $_POST ) > 0 ) ) {
if( self::DEV ) { pr_var( $_POST, '$_POST', true ); }
				$source = ( isset( $_POST['wpd-source'] ) ? $_POST['wpd-source'] : null );
//				$hierarchical = ( isset( $_POST['wpd-hierarchical'] ) && $_POST['wpd-hierarchical'] === '1' ? true : false );
				$sort_by = ( isset( $_POST['wpd-sort-by'] ) ? $_POST['wpd-sort-by'] : null );
				$style = ( isset( $_POST['wpd-style'] ) ? $_POST['wpd-style'] : null );
				$format = ( isset( $_POST['wpd-format'] ) ? $_POST['wpd-format'] : null );

				// Validates the passed variables and sets the properties
				parent::__construct( $source, /*$hierarchical,*/ $sort_by, $style, $format );
				$this->is_post = true;
				unset( $source, /*$hierarchical,*/ $sort_by, $style, $format );
			}
			else {
				parent::__construct();
			}

			$this->print_page();
		}


        /**
         * Print the page
         *
         * @param bool $fullpage
         */
        function print_page( $fullpage = true ) {

			if( $fullpage === true ) {
				echo $this->print_html_head();
			}

			echo $this->show_form();

			if( $this->is_post === true ) {
				echo $this->show_hooks();
			}

			if( $this->show_docs === true ) {
				echo $this->show_documentation();
			}

			if( $fullpage === true ) {
				echo $this->print_html_footer();
			}
		}

		/**
		 * HTML head for this front-end page
		 * @todo Make this nice & tidy html code ;-)
		 */
		function print_html_head() {
			return '<html>
<head>
	<title></title>
	<link rel="stylesheet" href="css/style.css" />
</head>
<body id="wpd">
';
		}

		/**
		 * HTML footer for this front-end page
		 */
		function print_html_footer() {
			return '
</body>
</html>';
		}


        /**
         * Generate the HTML form for the frontend
         *
         * @return string
         */
        function show_form() {
			$html = '
	<form method="post" id="wpd-form" enctype="multipart/form-data" accept-charset="utf-8">
		<fieldset>
		<div>
			<label for="wpd-source">Please provide the source location:
				<input id="wpd-source" name="wpd-source" type="text" value="' . ( $this->source !== '' ? $this->source : $this->default_source ) . '" />
			</label>
		</div>';
		

		if( count( $this->sort_options ) > 1 ) {
			$html .= '
		<div class="wpd-column">
			<p>How would you like the hooks to be sorted ?</p>';


			foreach( $this->sort_options as $key => $sort_by ) {
				$html .= '
			<label for="wpd-sort-by-' . $key . '">
				<input id="wpd-sort-by-' . $key . '" name="wpd-sort-by" type="radio" value="' . $key . '" ' . ( $key === $this->sort_by ? 'checked="checked"' : '' ) . '" />
				' . $sort_by . '
			</label><br />';
			}

			$html .= '
		</div>';
		}

		if( count( $this->styles ) > 1 ) {
			$html .= '
		<div class="wpd-column">
			<p>In which style would you like to receive the output ?</p>';

			foreach( $this->styles as $key => $style ) {
				$html .= '
			<label for="wpd-style-' . $key . '">
				<input id="wpd-style-' . $key . '" name="wpd-style" type="radio" value="' . $key . '" ' . ( $key === $this->style ? 'checked="checked"' : '' ) . '" />
				' . $style . '
			</label><br />';
			}

			$html .= '
		</div>';
		}


		if( count( $this->formats ) > 1 ) {
			$html .= '
		<div class="wpd-column">
			<p>In which format would you like to received the output ?</p>';

			foreach( $this->formats as $key => $format ) {
				$html .= '
			<label for="wpd-format-' . $key . '">
				<input id="wpd-format-' . $key . '" name="wpd-format" type="radio" value="' . $key . '" ' . ( $key === $this->format ? 'checked="checked"' : '' ) . '" />
				' . $format . '
			</label><br />';
			}

			$html .= '
		</div>';
		}
		
		$html .= '
		<div class="wpd-row">
			 <input type="submit" id="wpd-submit" value="Submit" />
		</div>
		</fieldset>

	</form>
			';

			return $html;
		}


        /**
         * Retrieve the hook output using the options posted
         * @return bool|string|void
         */
        function show_hooks() {
			return $this->get_output();
		}


        /**
         * Show documentation about this package
         *
         * @return string
         */
        function show_documentation() {
			return '';

			// Pull the readme file in ?
			// Pull in one-page documentation created by phpDocumentor ?

			// Online (static) use


			// Dynamic use


			// Use within a distributed plugin
			// How you can include this class to add for instance an API tab to your plugin interface which will always show the user the hooks available in the version they are using
			/*
			  Choices:
			  - ship the class file with your plugin
			  - require the seperate hook documentor plugin to be installed & hook into that from your plugin
			*/
		}



	} /* End of class */

	new wp_hook_documentor_frontend();


} /* End of class-exists wrapper */

?>