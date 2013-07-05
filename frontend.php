<?php
/**
 * File: index.php
 * @package wp-plugin-hook-documentor
 */

/**
 * WordPress Plugin Hook Documentor - Online frontend
 *
 * @package wp-plugin-hook-documentor
 * @author	Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *  <wp-plugin-hook-documentor@adviesenzo.nl>
 *
 * @version	0.2
 * @since	2013-07-03 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen ©2013
 * @license http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @license	http://opensource.org/licenses/academic Academic Free License Version 1.2
 * @example	example/example.php
 *
 */

include_once( 'include/jrfdebug.inc.php' );


if ( !class_exists( 'wp_plugin_hook_documentor_frontend' ) ) {

	include( 'class.wp-plugin-hook-documentor.php' );
	
	class wp_plugin_hook_documentor_frontend extends wp_plugin_hook_documentor {
	
		public $is_post = false;
		
		public $show_docs = true;
		
		// @todo change this to the current directory
		// Current default is just for testing purposes
//		public $default_source = 'I:\000_GitHub\debug-bar-constants\debug-bar-constants';
		public $default_source = 'I:\000_GitHub\MimeTypes-Link-Icons';

	
		function __construct() {

			if( isset( $_POST ) && ( is_array( $_POST ) && count( $_POST ) > 0 ) ) {
if( self::DEV ) { pr_var( $_POST, '$_POST', true ); }
				$source = ( isset( $_POST['wpphd-source'] ) ? $_POST['wpphd-source'] : null );
//				$hierarchical = ( isset( $_POST['wpphd-hierarchical'] ) && $_POST['wpphd-hierarchical'] === '1' ? true : false );
				$sort_by = ( isset( $_POST['wpphd-sort-by'] ) ? $_POST['wpphd-sort-by'] : null );
				$style = ( isset( $_POST['wpphd-style'] ) ? $_POST['wpphd-style'] : null );
				$format = ( isset( $_POST['wpphd-format'] ) ? $_POST['wpphd-format'] : null );
				parent::__construct( $source, /*$hierarchical,*/ $sort_by, $style, $format );
				$this->is_post = true;
				unset( $source, /*$hierarchical,*/ $sort_by, $style, $format );
			}
			else {
				parent::__construct();
			}
			
			$this->print_page();
		}


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
<body id="wpphd">
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

	
		function show_form() {
			$html = '
	<form method="post" id="wpphd-form" enctype="multipart/form-data" accept-charset="utf-8">
		<fieldset>
		<div>
			<label for="wpphd-source">Please provide the source location:
				<input id="wpphd-source" name="wpphd-source" type="text" value="' . ( $this->source !== '' ? $this->source : $this->default_source ) . '" />
			</label>
		</div>
		' .

/*		<div>
			<label for="wpphd-hierarchical"> Hierarchical ?
				<input id="wpphd-hierarchical" name="wpphd-hierarchical" type="checkbox" value="1" ' . ( true === $this->hierarchical ? 'checked="checked"' : '' ) . ' />
			</label>
		</div>
*/

  		'<div class="wpphd-column">
			<p>How would you like the hooks to be sorted ?</p>';

		foreach( $this->sort_options as $key => $sort_by ) {
			$html .= '
			<label for="wpphd-sort-by-' . $key . '">
				<input id="wpphd-sort-by-' . $key . '" name="wpphd-sort-by" type="radio" value="' . $key . '" ' . ( $key === $this->sort_by ? 'checked="checked"' : '' ) . '" />
				' . $sort_by . '
			</label><br />';
		}

		$html .= '
		</div>

  		<div class="wpphd-column">
			<p>In which style would you like to receive the output ?</p>';

		foreach( $this->styles as $key => $style ) {
			$html .= '
			<label for="wpphd-style-' . $key . '">
				<input id="wpphd-style-' . $key . '" name="wpphd-style" type="radio" value="' . $key . '" ' . ( $key === $this->style ? 'checked="checked"' : '' ) . '" />
				' . $style . '
			</label><br />';
		}

		$html .= '
		</div>

		<div class="wpphd-column">
			<p>In which format would you like to received the output ?</p>';
		foreach( $this->formats as $key => $format ) {
			$html .= '
			<label for="wpphd-format-' . $key . '">
				<input id="wpphd-format-' . $key . '" name="wpphd-format" type="radio" value="' . $key . '" ' . ( $key === $this->format ? 'checked="checked"' : '' ) . '" />
				' . $format . '
			</label><br />';
		}

		$html .= '
		</div>
		<div class="wpphd-row">
			 <input type="submit" id="wpphd-submit" value="Submit" />
		</div>
		</fieldset>

	</form>
			';
			
			return $html;


		}
		



		function show_hooks() {
			$this->get_output();
			return '';
		}
		

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
	
	new wp_plugin_hook_documentor_frontend();


} /* End of class-exists wrapper */


?>