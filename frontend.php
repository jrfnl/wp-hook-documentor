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
 * @version	0.1
 * @since	2013-07-03 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen �2013
 * @license http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @license	http://opensource.org/licenses/academic Academic Free License Version 1.2
 * @example	example/example.php
 *
 */

if ( !class_exists( 'wp_plugin_hook_documentor_frontend' ) ) {

	include( 'include/class.wp-plugin-hook-documentor.php' );
	
	class wp_plugin_hook_documentor_frontend extends wp_plugin_hook_documentor {
	
		public $is_post = false;
		
		public $show_docs = true;
	
		function __construct() {
	
			if( isset( $_POST ) && ( is_array( $_POST ) && count( $_POST ) > 0 ) ) {
				$source = ( isset( $_POST['source'] ) ? $_POST['source'] : null );
				$hierarchical = ( isset( $_POST['hierarchical'] ) && $_POST['hierarchical'] === '1' ? true : false );
				$style = ( isset( $_POST['style'] ) ? $_POST['style'] : null );
				$format = ( isset( $_POST['format'] ) ? $_POST['format'] : null );
				parent::__construct( $source, $hierarchical, $style, $format );
				$this->is_post = true;
				unset( $source, $hierarchical, $style, $format );
			}
			else {
				parent::__construct();
			}
			
			$this->print_page();
		}


		function print_page( $fullpage = true ) {
			
			if( $fullpage === true ) {
				$this->print_html_head();
			}

			$this->show_form();

			if( $this->is_post === true ) {
				$this->show_output();
			}

			if( $this->show_docs === true ) {
				$this->show_documentation();
			}
			
			if( $fullpage === true ) {
				$this->print_html_footer();
			}
		}
		
		
		function print_html_head() {
			echo '<html>
<head>
	<title></title>
	<link rel="stylesheet" href="css/style.css" />
</head>
<body>
';
		}

		function print_html_footer() {
			echo '
</body>
</html>';
		}

	
		function show_form() {
	/*
	Form:
	Input field: url / browse button
	Input field: include file/path information ? i.e. make the list hierarchical ?
	Input field: output style: php array / txt / html / xml
	Input field: output type: inline / view / file
	*/
		}
		
		
		function show_output() {
		}
		
		
		function show_documentation() {
			
			// Pull the readme file in ?
			
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