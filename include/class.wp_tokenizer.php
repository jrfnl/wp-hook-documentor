<?php
/**
 * File: class.wp_tokenizer.php
 *
 * Token Analyser with some extra WordPress specific methods
 *
 * Recursively walks through a directory and retrieve the names of all files and directories
 * Optionally filter the retrieved list for files with comply with a list of allowed extensions
 *
 * @author	Juliette Reinders Folmer, {@link http://www.adviesenzo.nl/ Advies en zo} -
 *  <wp.tokenizer@adviesenzo.nl>
 *
 * @version	1.0
 * @since	2013-07-05 // Last changed: by Juliette Reinders Folmer
 * @copyright	Advies en zo, Meedenken en -doen �2013
 * @license http://www.opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 *
 */

/**
 * @todo Work out nearest comment code for docblock above function definition
 *
 */


if ( !class_exists( 'wpd_wp_tokenizer' ) ) {

	require_once 'wp_tokenizer_includes/php-token-stream/Token/Stream/Autoload.php';


	class wpd_wp_tokenizer {

		/**
		 * @const	string	version number of this class
		 */
		const VERSION = '1.0';


		/**
		 * @param	array	$extensions		Which file extensions to look for
		 */
		private $extensions = array( 'php' );


		/**
		 * @param	string	$plugin_name	Store for the retrieved plugin name
		 */
		public $plugin_name = '';



		/**
		 * Constructor.
		 *
		 * @param	string	$path		Plugin/Theme path
		 */
		public function __construct( $path ) {

			$file_list = $this->get_files( $path, true, $this->extensions );

			$slash = ( strrchr( $path, DIRECTORY_SEPARATOR ) === DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );

			// Initialize & cache all the token streams
			foreach( $file_list as $file_name ) {
				$ts = PHP_Token_Stream_CachingFactory::get( $path . $slash . $file_name );

				/* Save some data we'll always need */

				// Only look for plugin name in top-level files
				if( strpos( $file_name, DIRECTORY_SEPARATOR ) === false ) {
					$this->plugin_name = $this->get_plugin_name( $ts, $this->plugin_name );
				}
			}
		}


        /**
         * Retrieve the (cached) file list for path
         *
         * @param	string			$path
         * @param	bool			$recursive
         * @param	array|string	$exts
         * @return	array			File list
         */
		public function get_files( $path, $recursive = true, $exts = null ) {
            if( !isset( $exts ) ) { $exts = $this->extensions; }
			include_once( 'wp_tokenizer_includes/directorywalker/class.directorywalker.php' );
			return wpd_directory_walker::get_file_list( $path, $recursive, $exts );
		}

		/**
		 * Retrieve the (cached) tokens object
		 *
		 * @param	string	$path_to_file
		 * @return	object
		 */
		public function get_tokens( $path_to_file ) {
			return PHP_Token_Stream_CachingFactory::get( $path_to_file );
		}


        /**
         * @param $tokens
         * @param null $name
         * @return null
         */
        public function get_plugin_name( $tokens, $name = null ) {

			foreach( $tokens as $token ) {

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


        /**
         * @param $tokens
         * @param $values
         * @param $types
         * @return array
         */
        public function filter_on_value_and_type( $tokens, $values, $types ) {
			$selection = $this->filter_on_value( $tokens, $values );
			return $this->filter_on_token_type( $selection, $types );
		}


        /**
         * @param $tokens
         * @param $values
         * @return array|bool
         */
        public function filter_on_value( $tokens, $values ) {
			// Nothing to filter on - break execution
			if( ( !is_string( $values ) && !is_array( $values ) ) || ( ( is_string( $values ) && $values === '' ) || ( is_array( $values ) && count( $values ) === 0 ) ) ) {
				return false;
			}

			if( is_string( $values ) ) {
				$values = array( $values );
			}

			$filtered_tokens = array();
			foreach( $tokens as $k => $token ) {
				if( in_array( $token->__toString(), $values ) ) {
					$filtered_tokens[$k] = $token;
				}
			}
			return $filtered_tokens;
		}


        /**
         * @param $tokens
         * @param $types
         * @return array
         */
        public function filter_on_token_type( $tokens, $types ) {

            // Nothing to filter on - break execution
            if( ( !is_string( $types ) && !is_array( $types ) ) || ( ( is_string( $types ) && $types === '' ) || ( is_array( $types ) && count( $types ) === 0 ) ) ) {
                return false;
            }

            // Make $types usable
            if( is_string( $types ) ) {
                $types = array( $types );
            }

            foreach( $types as $k => $type ) {
                $types[$k] = 'PHP_Token_' . substr( $type, 2);
            }


			// Do the filtering
			$filtered_tokens = array();
			foreach( $tokens as $k => $token ) {
                foreach( $types as $type ) {
                    if( $token instanceof $type ) {
                        $filtered_tokens[$k] = $token;
                    }
                }
			}
			return $filtered_tokens;
		}



		/**
		 * Get the signature of a function call rather than of a function definition
		 *
         * @param $tokens
         * @param $token
         * @param $key
         * @return mixed
         */
        public function get_signature( $tokens, $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $tokens, $key );
			$return = str_replace( 'anonymous function', $token->__toString(), $tmp_object->getSignature() );
			unset( $tmp_object );
			return $return;
		}

		/**
		 * Get the arguments of a function call rather than for a function definition
		 *
         * @param $tokens
         * @param $token
         * @param $key
         * @return array
         */
        public function get_arguments( $tokens, $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $tokens, $key );
			$return = $tmp_object->getArguments();
			unset( $tmp_object );
			return $return;
		}


		/**
		 * Get the DocBlock directly above a line (if any)
         *
         * @param $tokens
         * @param $token
         * @param $key
         * @return null|string
         */
        public function get_docblock( $tokens, $token, $key ) {
			$tmp_object = new PHP_Token_FUNCTION( $token->__toString(), $token->getLine(), $tokens, $key );
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
         * @todo deal with break_at
         * @todo deal with tag
		 *
         *
         * @param $tokens
         * @param $key
         * @param null $break_at
         * @param null $tag
         * @return string
         */
        public function get_nearest_comment( $tokens, $key, $break_at = null, $tag = null ) {

			if( isset( $break_at ) && is_string( $break_at ) ) { $break_at = array( $break_at ); }
			if( isset( $tag ) && is_string( $tag ) ) { $tag = array( $tag ); }


//			$tokens            = $this->tokens();
//pr_var( $tokens );
//pr_var( $key, 'key', true );
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


		/**
		 * Retrieve the nearest comment and parse it as a phpDoc style comment
		 *
         * @param $tokens
         * @param $key
         * @param null $tag
         * @return array
         */
        public function parse_nearest_comment( $tokens, $key, $tag = null ) {
			return $this->parse_comment( $this->get_nearest_comment( $tokens, $key, $tag ), $tag );
		}


		/**
		 * Parse comments to their individual parts
		 *
		 * Superfluous whitespace will be removed from the resulting values
		 * Note: new lines are *not* removed from values, other superfluous whitespace is.
		 * The reason for this is to allow people to use nl2br for displaying the comments.
		 * If you don't want the new lines, just str_replace() them in your own code.
		 *
		 * @todo may be figure out a way to deal with {inline @link} comments ? Probably not needed
		 *
		 * @param	string			$string		Comment string
		 * @param	array|string	$tags		a phpDoc tags or an array of phpDocs tags to filter for
		 * @return	array			Array containing the parsed comment, optionally filtered to only
		 *							contain instances of $tag
		 */
		public function parse_comment( $string, $tags = null ) {

			static $search = array( '`(?:^(/\*+)|(\*/)$|[\n\r][ \t]+(\*)[\s]|^(//)|^(#))`', '`([ \t\r]{2,})`' );
			static $replace = array( '', ' ' );

			// Parse out all the line endings and comment delimiters
			$string = trim( preg_replace( $search, $replace, trim( $string ) ) );

			// Match the individual comment parts
			$found = preg_match_all( '`(?:@([a-z-]+)\s+)?([^@]+)`', $string, $matches, PREG_SET_ORDER );

			if( $found > 0 ) {

				$comment = array();

				/* Create an array of the parsed comments */
				foreach( $matches as $match ) {
					$match = array_map( 'trim', $match );

					if( $match[1] === '' && $match[2] !== '' ) {
						// No @tag found, tag it as 'description'
						$comment['description'][] = $match[2];
					}
					else if( $match[2] !== '' ) {
						$parsed_line = $this->comment_parse_line( $match[2], $match[1] );
						if( $parsed_line !== false ) {
							$comment[$match[1]][] = $parsed_line;
						}
					}
					else {
						$comment[$match[1]][] = '';
					}
				}
				unset( $match );


				if( ! isset( $tags ) || ( ( !is_string( $tags ) && !is_array( $tags ) ) || ( ( is_string( $tags ) && $tags === '' ) || ( is_array( $tags ) && count( $tags ) === 0 ) ) ) ) {
                    return $comment;
                }
                else {
                    if( is_string( $tags ) ) {
                        $tags = array( $tags );
                    }
                    $tags = array_flip( $tags );
                    return array_intersect_key ( $comment, $tags );
                }
			}
			return false;
		}


		/**
		 * Parse a phpDoc style comment line for it's syntactical parts
		 *
		 * @link http://www.phpdoc.org/docs/latest/for-users/phpdoc/types.html
		 * @todo	work out parse routines for the other types
		 * @todo	re-work the function to allow developers to pass their own regex/routine for a certain tag
		 *
		 * @param	string			$string		Comment line string to be parsed according to phpDoc standard
		 * 										with the tag already removed
		 * @param	string			$tag		The associated tag which syntax should be followed
		 * @return	array|string	An array containing the parsed parts or the unaltered string if
		 * 							the line couldn't be parsed or has no syntax to parse by.
		 */
		public function comment_parse_line( $string, $tag ) {
// (?P<name>pattern).
			$return = $string;

			switch( $tag ) {

				case 'api': // no specified syntax, project dependent, but presume similar syntax
				case 'param': // @param [Type] [name] [<description>]
				case 'return': // @return [Type] [<description>]
				case 'var': // no docs yet, but presume similar syntax
				case 'staticvar': // phpDoc1 @staticvar datatype description

//					$found = preg_match( '`((?:\|?(?:string|integer|int|boolean|bool|float|double|object|mixed|array|resource|void|null|callback|false|true|self))+)(?:\s+(\$[\w]+))?(\s+[^$]*)?$`', $string, $match );
					$found = preg_match( '`(?P<type>(?:\|?(?:string|integer|int|boolean|bool|float|double|object|mixed|array|resource|void|null|callback|false|true|self))+)(?:\s+(?P<var_name>\$[\w]+))?(?P<description>\s+[^$]*)?$`', $string, $match );
//pr_var( $match );
					if( $found > 0 ) {
						$return = array( 'type' => $match[1] );
						if( isset( $match[2] ) && $match[2] !== '' ) { $return['var_name'] = $match[2]; }
						if( isset( $match[3] ) && $match[3] !== '' ) { $return['comment'] = trim( $match[3] ); }
//						return $return;
					}
					unset( $found, $match );
					break;


				case 'abstract': // phpDoc1 icm php4 - @abstract
				case 'final': // phpDoc1 icm php4 - @final
				case 'static': // phpDoc1 - @static
				case 'filesource': //@filesource
					//ignore as no content, shouldn't normally even be passed to this function
					$return = false;
					break;


				case 'access': // phpDoc1 @access private protected public


				case 'author': //@author [name] [<email address>]
					$found = preg_match( '`^([^<$]+)\s+(?:<([^>]+)>)?$`', $string, $match );
					if( $found > 0 ) {
						$return = array( 'name' => $match[1] );
						if( isset( $match[2] ) && $match[2] !== '' ) { $return['email'] = trim( $match[2] ); }
//						return $return;
					}
					unset( $found, $match );
					break;






				case 'link': // @link [URI] [<description>] OR phpDoc1 alternative syntax: @link URL, URL, URL...
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
							// phpDoc1 @see file.ext|elementname|class::methodname()|class::$variablename|functionname()|function functionname unlimited number of values separated by commas
				case 'uses': //@uses [FQSEN] [<description>]
							 // @uses file.ext|elementname|class::methodname()|class::$variablename|functionname()|function functionname description of how the element is used
				case 'used-by': //@used-by [FQSEN] [<description>]
				case 'usedby': // phpDoc1 syntax @usedby [FQSEN] [<description>]
//					break;


				case 'since': //@since [version] [<description>]
				case 'version': //@version [<vector>] [<description>]
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

				case 'tutorial': // phpDoc1 @tutorial package/ subpackage/ tutorialname.ext #section.subsection description
//					break;

				case 'access': // phpDoc1 @access private protected public
				case 'category': //@category [description]
				case 'copyright': //@copyright [description]
				case 'ignore': //@ignore [<description>]
				case 'internal': //@internal [description]
				case 'name': // phpDoc1 @name $globalvariablename
				case 'todo': //@todo [description]
				default: // proprietary tags
					$return = $string;
					break;
			}

			return $return;
		}



//			$this->print_to_table_helper( $ts, 0, 100 );
        /**
         * @param $tokens
         * @param $start_position
         * @param $end_position
         */
        function print_to_table_helper( $tokens, $start_position, $end_position ) {

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
			<th style="width: 40px;">Key</th>
			<th style="width: 40px;">Line</th>
			<th style="width: 140px;">Type</th>
			<th>Content</th>
		</tr>';

			$rows = array();
			if( $tokens->offsetExists( $start ) ) {

				$tokens->seek( $start );

				while( $tokens->key() < $end && $tokens->offsetExists( $tokens->key() )) {
//                for( $i = $start; $i < $end; $i++ ) {

					$rows[] = '
			<tr>
				<td>' . $tokens->key() . '</td>
				<td>' . $tokens->current()->getLine() . '</td>
				<td>' . get_class( $tokens->current() ) . '</td>
				<td>' . $tokens->current()->__toString() . '</td>
			</tr>';

/*
				<td>' . $this->tokens[$i]->key() . '</td>
				<td>' . $this->getLine() . '</td>
				<td>' . get_class( $token ) . '</td>
				<td>' . $this->__toString() . '</td>

 */
					if( $tokens->key() > $end ) {
						break;
					}
					$tokens->next();
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

        /**
         * @param $value
         * @param $key
         */
        static function print_it( $value, $key ) {
			print $value;
		}


	} /* End of class */


} /* End of class-exists wrapper */

?>