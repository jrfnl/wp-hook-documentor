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
 * @version	0.2
 * @since	2013-07-03 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen ©2013
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
 * - Hook into GitHub API to allow git:// urls as inputs for source
 * - Compare two php hook arrays
 * - Allow second source input so two versions can be compared and diffed for new hooks/filters
 *
 */

if ( !class_exists( 'wp_plugin_hook_documentor' ) ) {

	class wp_plugin_hook_documentor {
		
		/**
		 * @const	string	version number of this class
		 */
		const VERSION = '0.2';

		/**
		 * @const	bool	temporary constant for use during initial development
		 */
		const DEV = true;



		/**
		 * @param	string	$source		path to the source directory to walk
		 */
		public $source;
	
		/**
		 * @param
		 */
//		public $hierarchical;
	
	
		/**
		 * @param	string	$sort_by	chosen sort method
		 */
		public $sort_by;
		
		/**
		 * To translate the text strings, override the value strings of the property in your extended class
		 * If you want to add sorting methods, make sure you override the sort_hooks() method as well
		 *
		 * @param	array	$styles		available output styles
		 */
		public $sort_options = array(
			'name'				=>	'Hook name',
			'file_line'			=>	'File and then line number',
			'type_name'			=>	'Hook type and then hook name',
			'type_file_line'	=>	'Hook type and then file name and line number',
		);

		/**
		 * @param	string	$style		chosen output style
		 */
		public $style;

		/**
		 * To translate the text strings, override the value strings of the property in your extended class
		 *
		 * @param	array	$styles		available output styles
		 */
		public $styles = array(
			   'html'	=>	'XHTML',
			   'xml'	=>	'XML1.0',
			   'text'	=>	'Text',
			   'php'	=>	'PHP',
		);

		/**
		 * @param	string	$format		chosen output format
		 */
		public $format;

		/**
		 * To translate the text strings, override the value strings of the property in your extended class
		 *
		 * @param	array	$formats	available output formats
		 */
		public $formats = array(
			'return'	=>	'Return the string/xml object/php array',
			'textarea'	=>	'Show the resulting code in a textarea',
			'view'		=>	'Show the result in the chosen style',
			'file'		=>	'Present the result as a file',
		);
	
	
		/**
		 * @param	string	$plugin_name	Store for the retrieved plugin name
		 */
		public $plugin_name = '';

		/**
		 * @param	array	$hooks		Store for the retrieved hook information
		 *								Format:
		 *								$a['hook_name'] = array(
		 *									'file'		=>	$file_name,
		 *									'line'		=>	$line_number,
		 *									'type'		=>	'action' / 'filter',
		 *									'params'	=>	$hook_params,
		 *									'docs'		=>	$documentation,
		 *								)
		 */
		public $hooks = array();
	
	
		function __construct( $source = '', /*$hierarchical = false,*/ $sort_by = 'name', $style = 'html', $format = 'return' ) {
	//		include( 'include/class.directoryinfo/directoryinfo.inc.php' );

			if( version_compare( PHP_VERSION, '5', '<' ) === true ) {
				trigger_error( 'The WP plugin hook documentor requires PHP5+', E_USER_NOTICE );
				exit;
			}

			if( self::DEV === true ) {
				include_once( 'jrfdebug.inc.php' );
			}

			$this->validate_params( $source, 'source' );
//			$this->validate_params( $hierarchical, 'hierarchical' );
			$this->validate_params( $sort_by, 'sort_by' );
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
/*				case 'hierarchical':
					if( is_bool( $value ) ) {
						$this->hierarchical = $value;
					}
					break;
*/

				case 'sort_by':
					if( array_key_exists( $value, $this->sort_options ) ) {
						$this->sort_by = $value;
					}
					break;

				case 'style':
					if( array_key_exists( $value, $this->styles ) ) {
						$this->style = $value;
					}
					break;
	
				case 'format':
					if( array_key_exists( $value, $this->formats ) ) {
						$this->format = $value;
					}
					break;
			}
		}
	

		
		
		function get_output() {
			
			$this->hooks = $this->get_hooks();

			if( is_array( $this->hooks ) && count( $this->hooks ) > 0 ) {

				$this->sort_hooks();

                $output = null;
	
				switch( $this->style ) {
					case 'html':
						$output = $this->generate_html_output(); // html string
						break;
	
					case 'xml':
						$output = $this->generate_xml_output(); // simpleXML xml object
						break;
	
					case 'text':
						$output = $this->generate_text_output(); // text string
						break;
	
					case 'php':
						$output = $this->generate_php_output(); // php array
						break;
				}
                return $this->format_output( $output );
			}
            else {
                return false;
            }
		}

		
		
		function get_hooks() {
			// Efficiency - in case someone wants the same output in several ways, no need to get the hooks again
			if( isset( $this->hooks ) && ( is_array( $this->hooks ) && count( $this->hooks ) > 0 ) ) {
				return $this->hooks;
			}
			
			return $this->walk_source();
			

		}
	
		
		function walk_source() {

		}
		

		function tokens_to_array() {
			
			
/*
apply_filters(), apply_filters_ref_array(), do_action(), and do_action_ref_array()
*/

// Find plugin name as well
		}
	
		
		/**
		 * Sorts the hooks array by the hook name using natural case sorting
		 * Alters the value of $this->hooks
		 *
		 * Make sure to override this method in your own class extension if you want
		 * to use a different sorting mechanism or add more sorting methods
		 *
		 * @since 0.2
		 * @author Juliette Reinders Folmer
		 *
		 * @return void
		 */
		function sort_hooks() {
			
			switch( $this->sort_by ) {
				case 'name':
					$this->sort_hooks_by_name();
					break;
				case 'file_line':
					$this->sort_hooks_by_file_and_line_nr();
					break;
				case 'type_name':
					$this->sort_hooks_by_type_and_name();
					break;
				case 'type_file_line':
					$this->sort_hooks_by_type_file_and_line_nr();
					break;
			}
			return;
		}
		
		/**
		 * Sort the hooks array by hook name
		 *
		 * @since 0.2
		 * @author Juliette Reinders Folmer
		 *
		 * @return void
		 */
		function sort_hooks_by_name() {
			uksort( $this->hooks, 'strnatcasecmp' );
		}

		/**
		 * Sort the hooks array by file name and line number
		 *
		 * @since 0.2
		 * @author Juliette Reinders Folmer
		 *
		 * @return void
		 */
		function sort_hooks_by_file_and_line_nr() {
			foreach( $this->hooks as $key => $array ) {
			    $file[$key]	= $array['file'];
			    $line[$key]	= $array['line'];
			}
			array_multisort( $file, SORT_ASC, $line, SORT_ASC, $this->hooks );
		}


		/**
		 * Sort the hooks array by hook type and hook name
		 *
		 * @since 0.2
		 * @author Juliette Reinders Folmer
		 *
		 * @return void
		 */
		function sort_hooks_by_type_and_name() {
			foreach( $this->hooks as $key => $array ) {
			    $type[$key]	= $array['type'];
			    $name[$key]	= $key;
			}
			array_multisort( $type, SORT_ASC, $name, SORT_ASC, $this->hooks );
		}


		/**
		 * Sort the hooks array by hook type, file name and line number
		 *
		 * @since 0.2
		 * @author Juliette Reinders Folmer
		 *
		 * @return void
		 */
		function sort_hooks_by_type_file_and_line_nr() {
			foreach( $this->hooks as $key => $array ) {
			    $type[$key]	= $array['type'];
			    $file[$key]	= $array['file'];
			    $line[$key]	= $array['line'];
			}
			array_multisort( $type, SORT_ASC, $file, SORT_ASC, $line, SORT_ASC, $this->hooks );
		}



		/**
		 *
		 *
		 * Want different text output ? Just override this method in your own extended class.
		 *
		 * @since 0.1
		 * @author Juliette Reinders Folmer
		 *
		 * @return string
		 */
		function generate_text_output() {
			$len = ( function_exists( 'mb_strlen' ) ? mb_strlen( $this->plugin_name, 'UTF-8' ) : strlen( $this->plugin_name ) );
			$string = $this->plugin_name . "\r\n" . str_repeat( '=', $len ) . "\r\n\r\n";

			foreach( $this->hooks as $key => $hook ) {
				$string .= 'Hook: ' . "\t\t\t\t" . $key . "\n\r" .
					'File Name: ' . "\t\t\t" . $hook['file'] . "\n\r" .
					'Line Number: ' . "\t\t\t" . $hook['line'] . "\n\r" .
					'Hook Type: ' . "\t\t\t" . $hook['type'] . "\n\r" .
					'Parameters: ' . "\t\t\t" . $hook['params'] . "\n\r" .
					'Available documentation: ' . "\t" . ( isset( $hook['docs'] ) ? $hook['docs'] : 'None available' ) . "\n\r\n\r";
			}
			unset( $key, $hook );

            return $string;
		}
	
		/**
		 *
		 *
		 * Want different html output ? Just override this method in your own extended class.
		 *
		 * @since 0.1
		 * @author Juliette Reinders Folmer
		 *
		 * @return string
		 */
		function generate_html_output() {
			$string = '
	<h2>' . $this->plugin_name . '</h2>';

			foreach( $this->hooks as $key => $hook ) {
				$string .= '
	<h3>' . ucfirst( $hook['type'] ) . ' : ' . $key . '</h3>

				';
				$string .= 'Hook: ' . "\t\t\t\t" . $key . "\n\r" .
					'File Name: ' . "\t\t\t" . $hook['file'] . "\n\r" .
					'Line Number: ' . "\t\t\t" . $hook['line'] . "\n\r" .
					'Hook Type: ' . "\t\t\t" . $hook['type'] . "\n\r" .
					'Parameters: ' . "\t\t\t" . $hook['params'] . "\n\r" .
					'Available documentation: ' . "\t" . ( isset( $hook['docs'] ) ? $hook['docs'] : 'None available' ) . "\n\r\n\r";
			}
			unset( $key, $hook );

            return $string;
		}
	

		/**
		 *
		 *
		 * Want different xml output ? Just override this method in your own extended class.
		 *
		 * @since 0.1
		 * @author Juliette Reinders Folmer
		 *
		 * @return object
		 */
		function generate_xml_output() {

			$xml = new SimpleXMLElement('<plugin/>');
			$xml->addAttribute( 'name', $this->plugin_name );
			$xml->addAttribute( 'application', 'WordPress' );
			$hooks_node = $xml->addChild( 'hooks' );

			foreach( $this->hooks as $key => $hook ) {
				$node = $hooks_node->addChild( 'hook' );
				$node->addAttribute( 'type', $hook['type'] );
				$node->addAttribute( 'file', $hook['file'] );
				$node->addAttribute( 'line_number', $hook['line'] );
				$node->addChild( 'name', $key );
				$node->addChild( 'parameters', $hook['params'] );
				$node->addChild( 'documentation', $hook['docs'] );
			}
			unset( $hooks_node, $key, $hook, $node );

            return $xml;
		}
		
		/**
		 *
		 *
		 * Want different php output ? Just override this method in your own extended class.
		 *
		 * @since 0.2
		 * @author Juliette Reinders Folmer
		 *
		 * @return array
		 */
		function generate_php_output() {
			$output = array( $this->plugin_name => $this->hooks );
            return $output;
		}
		
	
		/**
		 *
		 */
		function format_output( $output ) {

			switch( $this->format ) {
				case 'textarea':
					break;
				case 'view':
					break;
				case 'file':
					break;
			}
			
			// in the case of 'return', just return the output unchanged
			return $output;
		}



		function format_textarea_output( $output ) {
			$string = '<textarea>';

			switch( $this->style ) {
				case 'html':
					$string .= htmlspecialchars ( $output, ENT_QUOTES | ENT_XHTML, 'UTF-8', true );
					break;
	
				case 'xml':
					$string .= htmlspecialchars ( $output->asXML(), ENT_QUOTES | ENT_XML1, 'UTF-8', true );
					break;
	
				case 'text':
					$string .= htmlspecialchars ( $output, ENT_QUOTES | ENT_HTML401, 'UTF-8', true );
					break;
					
				case 'php':
					$output = '<?php' . "\n\r\n\r" . print_r( $output, true ) . "\n\r\n\r" . '?>';
					$string .= htmlspecialchars ( $output, ENT_QUOTES | ENT_XHTML, 'UTF-8', true );
					break;
			}
			$string .= '</textarea>';
			return $string;
		}
		

		function format_view_output( $output ) {
			
			// xml - header('Content-type: text/xml');
		}
		

		function format_file_output( $output ) {
			//send file header
			
			// xml $output->asXML( 'filename' )
		}

	} /* End of class */
	

} /* End of class-exists wrapper */


?>