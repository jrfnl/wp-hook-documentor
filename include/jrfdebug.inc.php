<?php
/*
if ( defined('IN_SITE_CALL') === FALSE ) {
	die('Hacking attempt');
}
*/


/*********************************************************************
* GENERAL DEBUGGING FUNCTION - SHOW ANY KIND OF VARIABLE
*********************************************************************/

function log_var_to_errorlog( $var, $title = '', $escape = false, $space = '' ) {
	$display_value =  ini_get( 'display_errors' );
	ini_set( 'display_errors', 0 );

	$string_var = get_var( $var, $title, $escape, $space );
	trigger_error( $string_var, E_USER_NOTICE );

	ini_set( 'display_errors', 1 );
}

function get_var( $var, $title = '', $escape = false, $space = '' ) {
	ob_start();
	pr_var( $var, $title, $escape, $space );
	$string_var = ob_get_clean();
	return $string_var;
}

// Catching of function calls as function was renamed
function pr_array( $var, $title = '' ) {
	pr_var( $var, $title );
}


/*function esc( $string, $encoding ) {
	if( $encoding === 'utf-8' ) {
	}
	else {
	}
}*/


function pr_var( $var, $title = '', $escape = false, $space = '', $short = false ) {

	if( $space === '' ) { print '<div class="pr_var">' . "\n"; }
	if ( !empty( $title ) ) {
		print '<h4 style="clear: both;">' . /*( $escape === true ? htmlentities(*/ $title /*, ENT_QUOTES ) : $title )*/ . "</h4>\n";
	}

	if ( is_array( $var ) ) {
		print 'Array: <br />' . $space . "(<br />\n";
		if( $short !== true ) {
			$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		else {
			$spacing = $space . '&nbsp;&nbsp;';
		}
		foreach( $var as $key => $value ) {
			print $spacing . '[' . ( $escape === true ? htmlentities( $key, ENT_QUOTES ): $key );
			if( $short !== true ) {
				print  ' ';
				switch( true ) {
					case ( is_string( $key ) ) :
						print '<span style="color: #336600; background-color: transparent;"><b><i>(string)</i></b></span>';
						break;
					case ( is_int( $key ) ) :
						print '<span style="color: #FF0000; background-color: transparent;"><b><i>(int)</i></b></span>';
						break;
					case ( is_float( $key ) ) :
						print '<span style="color: #990033; background-color: transparent;"><b><i>(float)</i></b></span>';
						break;
					default:
						print '(unknown)';
						break;
				}
			}
			print '] => ';
			pr_var( $value, '', $escape, $spacing, $short );
		}
//		print $space . ")<br /><br />\n\n";
		print $space . ")<br />\n\n";
	}
	elseif ( is_string( $var ) ) {
		print '<span style="color: #336600; background-color: transparent;">';
		if( $short !== true ) {
			print '<b><i>string['
				. strlen( $var )
			. ']</i></b> : ';
		}
		print '&lsquo;'
			. ( $escape === true ? str_replace( '  ', ' &nbsp;', htmlentities( $var, ENT_QUOTES ) ) : str_replace( '  ', ' &nbsp;', $var ) )
			. "&rsquo;</span><br />\n";
	}
	elseif ( is_bool( $var ) ) {
		print '<span style="color: #000099; background-color: transparent;">';
		if( $short !== true ) {
			print '<b><i>bool</i></b> : '
				. $var
				. ' ( = ';
		}
		else {
			print '<b><i>b</i></b> ';
		}
		print '<i>'
			. ( ( $var === false ) ? '<span style="color: #FF0000; background-color: transparent;">false</span>' : ( ( $var === true ) ? '<span style="color: #336600; background-color: transparent;">true</span>' : 'undetermined' ) );
		if( $short !== true ) {
			print ' </i>)';
		}
		print "</span><br />\n";
	}
	elseif ( is_int( $var ) ) {
		print '<span style="color: #FF0000; background-color: transparent;">';
		if( $short !== true ) {
			print '<b><i>int</i></b> : ';
		}
		print ( ( $var === 0 ) ? '<b>' . $var . '</b>' : $var )
			. "</span><br />\n";
	}
	elseif ( is_float( $var ) ) {
		print '<span style="color: #990033; background-color: transparent;">';
		if( $short !== true ) {
			print '<b><i>float</i></b> : ';
		}
		print $var
			. "</span><br />\n";
	}
	elseif ( is_null( $var ) ) {
		print '<span style="color: #666666; background-color: transparent;">';
		if( $short !== true ) {
			print '<b><i>';
		}
		print 'null';
		if( $short !== true ) {
			print '</i></b> : '
			. $var
			. ' ( = <i>NULL</i> )';
		}
		print "</span><br />\n";
	}
	elseif ( is_resource( $var ) ) {
		print '<span style="color: #666666; background-color: transparent;">';
		if( $short !== true ) {
			print '<b><i>resource</i></b> : ';
		}
		print $var;
		if( $short !== true ) {
			print ' ( = <i>RESOURCE</i> )';
		}
		print "</span><br />\n";
	}
	else if ( is_object( $var ) ) {
		print "object: <br />\n" . $space . "(<br />\n";
		if( $short !== true ) {
			$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		else {
			$spacing = $space . '&nbsp;&nbsp;';
		}
		object_info( $var, $escape, $spacing, $short );
		print $space . ")<br /><br />\n\n";
	}
	else {
		print 'I haven&#39;t got a clue what this is: ' . gettype( $var ) . "<br />\n";
	}
	if( $space === '' ) { print "</div>"; }
}

// TO DO: get object properties to show the variable type on one line with the 'property'
function object_info( $obj, $escape, $space, $short ) {
	print $space . '<b><i>Class</i></b>: ' . get_class( $obj ) . " (<br />\n";
	if( $short !== true ) {
		$spacing = $space . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	else {
		$spacing = $space . '&nbsp;&nbsp;';
	}
	$ov = get_object_vars( $obj );
	foreach( $ov as $var => $val ) {
		if ( is_array( $val ) ) {
			print $spacing . '<b><i>property</i></b>: ' . $var . "<b><i> (array)</i></b>\n";
			pr_var( $val, '' , $escape, $spacing, $short );
		} else {
			print $spacing . '<b><i>property</i></b>: ' . $var . ' = ';
			pr_var( $val, '' , $escape, $spacing, $short );
		}
	}
	unset( $ov, $var, $val );

	$om = get_class_methods( $obj );
	foreach( $om as $method ) {
		print $spacing . '<b><i>method</i></b>: ' . $method . "<br />\n";
	}
	unset( $om );
	print $space . ")<br /><br />\n\n";
}

function dump_all() {
	$var = get_defined_vars();
	pr_var( $var, 'Dump of all defined variables' );
}

function dump_all_not_template( $var ) {
	unset( $var['template'] );
	pr_var( $var, 'Dump of all defined variables excluding $template' );
}


// Catching of function calls as function was renamed
function pr_str( $var ) {
	pr_string( $var );
}

function pr_string( $var ) {
	if ( is_string( $var ) ) {
		print '&lsquo;' . str_replace( '  ', ' &nbsp;', $var ) . "&rsquo;\n";
	}
	else {
		print 'E: not a string';
	}
}


function pr_bool( $var ) {
	if ( is_bool( $var ) ) {
		if( $var === false ) {
			print '<span style="color: red; background-color: transparent;">' . 'false' . "</span>\n";
		}
		elseif( $var === true ) {
			print '<span style="color: green; background-color: transparent;">' . 'true' . "</span>\n";
		}
		else {
			print 'E: boolean value undetermined';
		}
	}
	else {
		print 'E: not boolean';
	}
}


function pr_int( $var ) {
	if ( is_int( $var ) ) {
		if( $var === 0 ) {
			print '<span style="color: red; background-color: transparent;">' . $var . "</span>\n";
		}
		else {
			print '<span style="color: green; background-color: transparent;">' . $var . "</span>\n";
		}
	}
	else {
		print 'E: not an integer';
	}
}


function pr_flt( $var ) {
	if ( is_float( $var ) ) {
		print '<span style="color: #990033; background-color: transparent;">' . $var . "</span>\n";
	}
	else {
		print 'E: not a float';
	}
}




/*********************************************************************
* STRING INPUT DEBUGGING FUNCTION
*********************************************************************/

function extended_string_compare( $string1, $string2 ) {

	pr_var( $string1, 'string1', true );
	pr_var( $string2, 'string2', true );

	$compared = strcmp ( $string1, $string2 );
	print '<h3>strcmp</h3>result from php strcmp is ' . $compared . '<br><br>';

	$count1 = count_chars ( $string1, 1 );
	$count2 = count_chars ( $string2, 1 );

	print '<h3>count_char compare based on string1</h3><table class="stringcmp">';
	print '<tr><th>key</th><th># in string1</th><th># in string2</th><th>char</th></tr>';
	foreach( $count1 as $key => $value ) {
		if ( isset( $count2[$key] ) === true && $value != $count2[$key] ) {
			$char = chr($key);
			print '<tr><td>' . $key . '</td><td>' . $value . '</td><td>' . $count2[$key] . '</td><td>' . $char . '</td></tr>';
		}
		elseif ( isset( $count2[$key] ) === false ) {
			$char = chr($key);
			print '<tr><td>' . $key . '</td><td>' . $value . '</td><td> - </td><td>' . $char . '</td></tr>';
		}
	}
	print '</table>';

	print '<h3>count_char compare based on string2</h3><table class="stringcmp">';
	print '<tr><th>key</th><th># in string1</th><th># in string2</th><th>char</th></tr>';
	foreach( $count2 as $key => $value ) {
		if ( isset( $count1[$key] ) === true && $value != $count1[$key] ) {
			$char = chr($key);
			print '<tr><td>' . $key . '</td><td>' . $count1[$key] . '</td><td>' . $value . '</td><td>' . $char . '</td></tr>';
		}
		elseif ( isset( $count1[$key] ) === false ) {
			$char = chr($key);
			print '<tr><td>' . $key . '</td><td> - </td><td>' . $value . '</td><td>' . $char . '</td></tr>';
		}
	}
	print '</table>';


	print '<h3>character for character compare</h3><table class="stringcmp">';
	print '<tr><th>i</th><th>char string1</th><th>ord string1</th><th>chr string1</th><th>char string2</th><th>ord string2</th><th>chr string2</th><th>same ?</th></tr>';
	$max = ( strlen( $string1 ) > strlen( $string2 ) ) ? strlen( $string1 ) : strlen( $string2 );
	for( $i = 0; $i < $max; $i++ ) {
		$char1 = substr( $string1, $i , 1 );
		$char2 = substr( $string2, $i , 1 );
		$ord1 = ord( $char1 );
		$ord2 = ord( $char2 );
		$chr1 = chr( $ord1 );
		$chr2 = chr( $ord2 );
		$color = ( $ord1 === $ord2 && $chr1 === $chr2 ) ? '008000' : '00FFFF';
		print '<tr><td>' . $i . '</td><td>' . $char1 . '</td><td>' . $ord1 . '</td><td>' . $chr1 . '</td><td>' . $char2 . '</td><td>' . $ord2 . '</td><td>' . $chr2 . '</td><td style="color: #000000; background-color: #' . $color . ';">&nbsp;</td></tr>';
	}
	print '</table>';
}






/*********************************************************************
* ADODB RELATED FUNCTIONS
*********************************************************************/

// Count all executed functions for debugging purposes
function& CountExecs( $db, $sql, $inputarray ) {
	global $EXECS;

	if ( !is_array( $inputarray ) ) {
		$EXECS++;
	}
	# handle 2-dimensional input arrays
	elseif ( is_array( reset( $inputarray ) ) ) {
		$EXECS += sizeof( $inputarray );
	}
	else {
		$EXECS++;
	}

 	// in PHP4.4 and PHP5, we need to return a value by reference
	return $null;
}

// Count all cached executed functions for debugging purposes
function CountCachedExecs( $db, $secs2cache, $sql, $inputarray ) {
	global $CACHED;
	$CACHED++;
}






// Example for errorhandler
// From : http://nl3.php.net/manual/nl/language.constants.php
function debug_ErrorHandler( $errno, $errstr, $errfile, $errline ) {
	print( 'PHP Error [' . $errno . '] [' . $errstr . '] at ' . $errline . ' in ' . $errfile . '.<br />');
}



// From : http://nl3.php.net/manual/nl/language.constants.php
/*if ( !function_exists( 'debug_print' ) ) {
	if ( defined('DEBUG') && TRUE === DEBUG ) {
		function debug_print( $string, $flag = NULL ) {
			// if second argument is absent or TRUE, print
			if ( !( FALSE === $flag ) ) {
				print 'DEBUG: ' . $string . "\n";
			}
		}
	}
	else {
		function debug_print( $string, $flag = NULL ) {
		}
	}
}
*/

if( !function_exists('do_error_backtrace') ) {
	function do_error_backtrace($errno, $errstr, $errfile, $errline) {
		if(!(error_reporting() & $errno))
			return;
		switch($errno) {
			case E_WARNING	    :
			case E_USER_WARNING :
			case E_STRICT	    :
			case E_NOTICE	    :
	//		case E_DEPRECATED   :
			case E_USER_NOTICE  :
				$type = 'warning';
				$fatal = false;
				break;
			default			 :
				$type = 'fatal error';
				$fatal = true;
				break;
		}
		$trace = debug_backtrace();
		array_shift($trace);
		if(php_sapi_name() == 'cli') {
			echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
			foreach($trace as $item)
				echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
		} else {
			echo '<p class="error_backtrace">' . "\n";
			echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
			echo '  <ol>' . "\n";
			foreach($trace as $item)
				echo '	<li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
			echo '  </ol>' . "\n";
			echo '</p>' . "\n";
		}
		if(ini_get('log_errors')) {
			$items = array();
			foreach($trace as $item)
				$items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';
			$message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
			error_log($message);
		}
	
		flush();
	
		if($fatal)
			exit(1);
	}
}



?>