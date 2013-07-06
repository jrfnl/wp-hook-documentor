<?php
/**
 * File: class.wp_plugin_tokenizer.php
 *
 * WordPress plugin Token Analyser
 *
 * Recursively walks through a directory and retrieve the names of all files and directories
 * Optionally filter the retrieved list for files with comply with a list of allowed extensions
 *
 * @author	Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *  <wp.plugin_tokenizer@adviesenzo.nl>
 *
 * @version	1.0
 * @since	2013-07-05 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen �2013
 * @license http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 *
 */
 
/**
 * @todo Work out nearest comment code for docblock above function definition
 * @todo Add method to retrieve plugin name from file
 *
 */


if ( !class_exists( 'wpphd_wp_plugin_tokenizer' ) ) {
	
	require_once 'php-token-stream/Token/Stream.php';
	require_once 'php-token-stream/Token/Stream/Autoload.php';


	class wpphd_wp_plugin_tokenizer extends PHP_Token_Stream {
		
		/**
		 * @const	string	version number of this class
		 */
		const VERSION = '1.0';
		
		
	    /**
	     * Constructor.
	     *
	     * @param string $sourceCode
	     */
	    public function __construct( $sourceCode ) {
			parent::__construct( $sourceCode );
	    }
	    

	    public function get_plugin_name( $name = null ) {

			foreach( $this as $k => $token ) {

				// Only look at the very start of the file, break out as soon as code is encountered
				if( ( ! $token instanceof PHP_Token_OPEN_TAG &&
					! $token instanceof PHP_Token_WHITESPACE ) &&
					( ! $token instanceof PHP_TOKEN_DOC_COMMENT &&
					! $token instanceof PHP_TOKEN_COMMENT ) ) {
					break;
				}

				// We have a comment - check to see if we can parse the plugin name based on the WP readme standard
				if( $token instanceof PHP_Token_DOC_COMMENT ||
					$token instanceof PHP_Token_COMMENT ) {
					if( preg_match( '`[\s\*]+Plugin Name: ([^\n\r]+)[\n\r]`', $token->__toString(), $matches ) ) {
						$name = $matches[1];
						break;
					}
				}
			}
			return $name;
		}
		
		
		public function filter_on_value_and_type( $value, $type ) {
			$selection = $this->filter_on_value( $value );
			return $this->filter_on_token_type( $type, $selection );
		}

		
		public function filter_on_value( $value, $selection = null ) {
			// Nothing to filter on - break execution
			if( ( !is_string( $value ) && !is_array( $value ) ) || ( ( is_string( $value ) && $value === '' ) || ( is_array( $value ) && count( $value ) === 0 ) ) ) {
				return false;
			}

			if( is_string( $value ) ) {
				$value = array( $value );
			}
			
			$array = ( isset( $selection ) ? $selection : $this );

			$filtered_tokens = array();
			foreach( $array as $k => $token ) {
				if( in_array( $token->__toString(), $value ) ) {
					$filtered_tokens[$k] = $token;
//					$filtered_tokens[$array->key()] = $token;
				}
			}
			return $filtered_tokens;
		}

		public function filter_on_token_type( $type, $selection = null ) {

			$type = 'PHP_Token_' . substr( $type, 2);
			$array = ( isset( $selection ) ? $selection : $this );

			// @todo work out a way to have $type be an array of types
			$filtered_tokens = array();
			foreach( $array as $k => $token ) {
				if( $token instanceof $type ) {
//					$filtered_tokens[$array->key()] = $token;
					$filtered_tokens[$k] = $token;
				}
			}
			return $filtered_tokens;
		}
		
		

		public function get_signature( $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $this, $key );
			$return = str_replace( 'anonymous function', $token->__toString(), $tmp_object->getSignature() );
			unset( $tmp_object );
			return $return;
		}

		public function get_arguments( $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $this, $key );
			$return = $tmp_object->getArguments();
			unset( $tmp_object );
			return $return;
		}

		
		public function get_docblock( $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $this, $key );
			$return = $tmp_object->getDocblock();
			unset( $tmp_object );
			return $return;
		}
		


		/**
		 * Get nearest comment above the current line
		 *
		 * Will stop searching if it reaches the start of the calling function, though it *will*
		 * check the docblock above the calling function.
		 *
		 * If $break_at is defined, it will stop searching if it comes across a string contained in the $break_at array to avoid ambiguity in interpreting the comment at the top of the function.
		 * If $tag is defined, it will only return the nearest comment (using above definition) if it contains
		 * the requested tag.
		 */
		public function get_nearest_comment( $token, $key, $break_at = null, $tag = null ) {
			
			if( is_string( $break_at ) ) { $break_at = array( $break_at ); }
			if( is_string( $tag ) ) { $tag = array( $tag ); }


	        $tokens            = $this->tokens();
	        $currentLineNumber = $tokens[$key]->getLine();
	        $prevLineNumber    = $currentLineNumber - 1;
	
	        for ($i = $key - 1; $i; $i--) {
	            if (!isset($tokens[$i])) {
	                return;
	            }
//pr_var( array( 'class' => get_class( $tokens[$i] ), 'line' => $tokens[$i]->getLine(), 'string' => $tokens[$i]->__toString() ), '$tokens['.$i. ']', true );
	            if ($tokens[$i] instanceof PHP_Token_FUNCTION ||
	                $tokens[$i] instanceof PHP_Token_CLASS ||
	                $tokens[$i] instanceof PHP_Token_TRAIT) {
	                // Some other trait, class or function, no docblock can be
	                // used for the current token
//print 'breaking because of function | class | trait<br>';
	                break;
	            }

	            $line = $tokens[$i]->getLine();

	            if ($line == $currentLineNumber ||
	                ($line == $prevLineNumber &&
	                 $tokens[$i] instanceof PHP_Token_WHITESPACE)) {
//print 'continue because of same line or prev line, but whitespace<br>';
	                continue;
	            }

                // @todo - work out a way to continue if the comment is @internal
                // @todo - work out a way to get the docblock for the calling function
	            if ($line < $currentLineNumber &&
	                ( !$tokens[$i] instanceof PHP_Token_DOC_COMMENT &&
					!$tokens[$i] instanceof PHP_Token_COMMENT ) ) {
//print 'breaking because doc comment found<br>';
	                break;
	            }
	
	            return (string)$tokens[$i];
	        }
	        
	        return;
		}
		
		/**
		 * @return int|false	Integer line number of the current line was contained within a function or false
		 */
/*		private function find_line_of_function_def( $token, $key ) {
	        for ($i = $key - 1; $i; $i--) {
	            if (!isset($tokens[$i])) {
	                return;
	            }
	            
	            if ($tokens[$i] instanceof PHP_Token_FUNCTION ||
	                $tokens[$i] instanceof PHP_Token_CLASS ||
	                $tokens[$i] instanceof PHP_Token_TRAIT) {
	                // Some other trait, class or function, no docblock can be
	                // used for the current token
//print 'breaking because of function | class | trait<br>';
	                break;
	            }

	            $line = $tokens[$i]->getLine();
			}
		}*/


		public function parse_nearest_comment( $token, $key, $tag = null ) {
			return $this->parse_comment( $this->get_nearest_comment( $token, $key, $tag ), $tag );
		}

		
		/**
		 * Parse comments to their individual parts
		 *
		 * Note: new lines are *not* removed from comments, other superfluous whitespace is.
		 * The reason for this is to allow people to use nl2br for displaying the comments.
		 * If you don't want the new lines, just str_replace() them in your own code.
		 *
		 * @todo make it so tag can be an array or may be remove altogether - is this really needed ?
		 */
		public function parse_comment( $string, $tag = null ) {

			static $search = array( '`(?:^(/\*+)|(\*/)$|[\n\r][ \t]+(\*)[\s]|^(//)|^(#))`', '`([ \t\r]{2,})`' );
			static $replace = array( '', ' ' );

			// Parse out all the line endings and comment delimiters
			$string = trim( preg_replace( $search, $replace, trim( $string ) ) );

			// @todo may be figure out a way to deal with {inline @link} comments ? Probably not needed
			$found = preg_match_all( '`(?:@([a-z-]+)\s+)?([^@]+)`', $string, $matches, PREG_SET_ORDER );

			if( $found > 0 ) {

				$comment = array();

				/* Create an array of the parsed comments */
				foreach( $matches as $match ) {
					$match = array_map( 'trim', $match );

					if( $match[1] === '' && $match[2] !== '' ) {
						// No @tag found, tag it as 'desciption'
						$comment['description'][] = $match[2];
					}
					else if( $match[2] !== '' ) {
						$parsed_line = $this->comment_parse_line( $match[2], $match[1] );
						if( $parsed_line !== false ) {
							$comment[$match[1]][] = $parsed_line;
						}
					}
				}
				unset( $match );


  				if( ! isset( $tag ) ) {
					return $comment;
				}
				else if( isset( $comment[$tag] ) ) {
					return $comment[$tag];
				}
			}
			return false;
		}

		/**
		 *
		 * @link http://www.phpdoc.org/docs/latest/for-users/phpdoc/types.html
		 * @todo	work out parse routines for the other types
		 *
		 * @param	string	$string		Comment line string to be parsed according to phpDoc standard
		 * @return	array|string		An array containing the parsed parts or the unaltered string if
		 * 								the line couldn't be parsed.
		 */
		public function comment_parse_line( $string, $tag ) {
			
			$string = trim( $string );
			$return = false;
			
			switch( $tag ) {

				case 'api': // no specified syntax, project dependent, but presume similar syntax
				case 'param': // @param [Type] [name] [<description>]
				case 'return': // @return [Type] [<description>]
				case 'var': // no docs yet, but presume similar syntax

					$found = preg_match( '`((?:\|?(?:string|integer|int|boolean|bool|float|double|object|mixed|array|resource|void|null|callback|false|true|self))+)(?:\s+(\$[\w]+))?(\s+[^$]*)?$`', $string, $match );
		
					if( $found > 0 ) {
						$return = array( 'type' => $match[1] );
						if( isset( $match[2] ) && $match[2] !== '' ) { $return['var_name'] = $match[2]; }
						if( isset( $match[3] ) && $match[3] !== '' ) { $return['comment'] = trim( $match[3] ); }
//						return $return;
					}
					break;
					

				case 'filesource': //@filesource
					//ignore as no content
					$return = false;
					break;





				case 'author': //@author [name] [<email address>]
//					break;


				case 'link': // @link [URI] [<description>]
				case 'license': // @license [<url>] [name]
//					break;


				case 'deprecated': //@deprecated [<version>] [<description>]
//					break;


/**
 * My function
 *
 * Here is an inline example:
 * <code>
 * <?php
 * echo strlen('6');
 * ?>
 * </code>
 * @example /path/to/example.php How to use this function
 * @example anotherexample.inc This example is in the "examples" subdirectory
 */
				case 'example': //@example [location] [<start-line> [<number-of-lines>] ] [<description>]
//					break;


				case 'global': //@global [Type] [name] || @global [Type] [description]
//					break;


				case 'package': //@package [level 1]\[level 2]\[etc.]
				case 'subpackage': //@subpackage [name]
//					break;


				case 'see': //@see [URI | FQSEN] [<description>]
				case 'uses': //@uses [FQSEN] [<description>]
				case 'used-by': //@used-by [FQSEN] [<description>]
//					break;


				case 'since': //@since [version] [<description>]
//					break;


				case 'throws': //@throws [Type] [<description>]
//					break;


				// Magic methods, very low priority
				case 'method': // @method [return type] [name]([[type] [parameter]<, ...>]) [<description>]
//					break;
				// Magic properties, very low priority
				case 'property': //@property [Type] [name] [<description>]
				case 'property-read': //@property-read [Type] [name] [<description>]
				case 'property-write': //@property-write [Type] [name] [<description>]
//					break;

				// phpDoc specific, very low priority
				case 'source': //@source [<start-line> [<number-of-lines>] ] [<description>]
//					break;

				case 'category': //@category [description]
				case 'copyright': //@copyright [description]
				case 'ignore': //@ignore [<description>]
				case 'internal': //@internal [description]
				case 'todo': //@todo [description]
				case 'version': //@version [<vector>] [<description>]
				default:
					$return = $string;
					break;
			}

			return $return;
		}



//			$this->print_to_table_helper( 0, 100 );
		function print_to_table_helper( $start_position, $end_position ) {

			$start = $start_position;
			$end = $end_position;
			$reverse = false;

			if( $end_position < $start_position ) {
				$reverse = true;
				$start = $end_position;
				$end = $start_position;
			}
			
			print '<table>
		<tr>
			<th width="40">Key</th>
			<th width="40">Line</th>
			<th width="140">Type</th>
			<th>Content</th>
		</tr>';

			$rows = array();
			if( $this->offsetExists( $start ) ) {

				$this->seek( $start );

				while( $this->key() < $end && $this->offsetExists( $this->key() )) {
//                for( $i = $start; $i < $end; $i++ ) {

					$rows[] = '
			<tr>
				<td>' . $this->key() . '</td>
				<td>' . $this->current()->getLine() . '</td>
				<td>' . get_class( $this->current() ) . '</td>
				<td>' . $this->current()->__toString() . '</td>
			</tr>';

/*
				<td>' . $this->tokens[$i]->key() . '</td>
				<td>' . $this->getLine() . '</td>
				<td>' . get_class( $token ) . '</td>
				<td>' . $this->__toString() . '</td>

 */
					if( $this->key() > $end ) {
						break;
					}
					$this->next();
				}
				if( $reverse === true ) {
					$rows = array_reverse( $rows );
				}
				array_walk( $rows, array('self','print_it') );

			}
			else {
				print '<tr><td colspan="4">No tokens found between the given positions.</td></tr>';
			}
			
			print '</table>';

		}


        static function print_it( $value, $key ) {
            print $value;
            return;
        }


	} /* End of class */





} /* End of class-exists wrapper */

?>