<?php
/**
 * File: class.wp-plugin-hook-documentor.php
 * @package wp-plugin-hook-documentor
 */

/**
 * WordPress Plugin Hook Documentor
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
 
/**
 * Roadmap / Todo:
 * - Build the bloody thing ;-)
 *
 * Future:
 * - Allow .zip/.gz file inputs as source
 * - Compare two php hook arrays
 * - Allow second source input so two versions can be compared and diffed for new hooks/filters
 *
 */
 
if ( !class_exists( 'wp_plugin_hook_documentor' ) ) {

	class wp_plugin_hook_documentor {
		
		/**
		 * @const	bool	temporary constant for use during initial development
		 */
		const DEV = true;
		
		const VERSION = '0.1';
	
	
		/**
		 * @const
		 */
		
		/**
		 * @param
		 */
		public $source;
	
		/**
		 * @param
		 */
		public $hierarchical;
	
		/**
		 * @param	string	$style	html/xml/text/php
		 */
		public $style;
		
		private $styles = array( 'html', 'xml', 'text', 'php' );
	
		/**
		 * @param	string	$format	inline/view/file
		 */
		public $format;
		
		private $formats = array( 'inline', 'view', 'file' );
	
		/**
		 * @param
		 */
		public $hooks = array();
	
	
		function __construct( $source = '', $hierarchical = false, $style = 'html', $format = 'inline' ) {
	//		include( 'include/class.directoryinfo/directoryinfo.inc.php' );
	
			if( self::DEV === true ) {
				include( 'jrfdebug.inc.php' );
			}
	
			$this->validate_params( $source, 'source' );
			$this->validate_params( $hierarchical, 'hierarchical' );
			$this->validate_params( $style, 'style' );
			$this->validate_params( $format, 'format' );
		}
		
		
		function validate_params( $value, $param ) {
			switch( $param ) {
	
				// @todo: add validation for whether directory can be walked
				case 'source':
					if( is_string( $value ) ) {
						$this->source = $value;
					}
					break;
	
				// @todo: maybe add a way to allow 0/1 as int and/or string
				case 'hierarchical':
					if( is_bool( $value ) ) {
						$this->hierarchical = $value;
					}
					break;
	
				case 'style':
					if( in_array( $value, $this->styles ) ) {
						$this->style = $value;
					}
					break;
	
				case 'format':
					if( in_array( $value, $this->formats ) ) {
						$this->format = $value;
					}
					break;
	
			}
		}
	
		function get_hooks() {
		}
	
		
		function walk_source() {
			// use DirectoryIterator
		}
		
		
		function tokens_to_array() {
		}
	
		
	
		
		
		function get_output( $style = null ) {
			if( !isset( $style ) ) {
				$style = $this->style;
			}
			
			$output = $this->hooks;
	
			switch( $style ) {
				case 'html':
					$output = $this->generate_html_output();
					break;
	
				case 'xml':
					$output = $this->generate_xml_output();
					break;
	
				case 'text':
					$output = $this->generate_text_output();
					break;
					
				case 'php':
					$output = $this->generate_php_output();
					break;
			}
			
			return $output;
		}
		
		function generate_text_output() {
            return '';
		}
	
		function generate_html_output() {
            return '';
		}
	
		function generate_xml_output() {
            return '';
		}
		
		function generate_php_output() {
            return '';
		}
		
	
		function format_output( $format = null, $style = null ) {
			if( !isset( $format ) ) {
				$format = $this->format;
			}
			if( !isset( $style ) ) {
				$style = $this->style;
			}
			
			switch( $format ) {
				case 'inline':
					break;
				case 'view':
					break;
				case 'file':
					break;
			}
			return;
		}
	
		function format_inline_output() {
		}
		function format_view_output() {
		}
		function format_file_output() {
		}

	} /* End of class */
	

} /* End of class-exists wrapper */


?>