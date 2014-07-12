<?php
/*! pimpmylog - 1.0.5 - 304e44fae52b81256e7624dbca2a9cb3d005808e*/
/*
 * pimpmylog
 * http://pimpmylog.com
 *
 * Copyright (c) 2014 Potsky, contributors
 * Licensed under the GPLv3 license.
 */
?><?php
//////////////////////////
// Set common variables //
//////////////////////////
define( 'YEAR'                 , @date( "Y" ) );
define( 'PHP_VERSION_REQUIRED' , '5.2' );
define( 'HELP_URL'             , 'http://pimpmylog.com' );








/*
	Satya Prakash
	to check which logs are repeated
	@ar : contains only distinct logs
*/

$ar = array();
$ar_count = array();
$ar_index = array();

$index = 0;

function createIndex($log_str){
	global $index;
	global $ar_index;
	$ar_index[$log_str]=$index;
	// echo $index." ####create Index#### ".$log_str."<br>";
	$index++;
}

function getIndex($log_str){
	global $ar_index;
	// echo $ar_index[$log_str]." *****get Index****** ".$log_str."<br>";
	return $ar_index[$log_str];
}

function checkRepeatLog($log){
	global $ar;
	global $ar_count;
	global $ar_index;
	
	if (!in_array($log['Log'],$ar)){
		$ar_count[$log['Log']]=1;
		array_push($ar, $log['Log']);
		return false;
	}$ar_count[$log['Log']]++;
	
	// echo $ar_count[$log['Log']]."%%%%%check repeat log%%%%%".$ar_index[$log['Log']]."\n".$log['Log']."<br>";
	
	return true;
}

function countLog($log_str){
	global $ar_count;
	// echo $ar_count[$log_str]."@@@@@@@count Log@@@@@@@".$log_str."<br>";
	return $ar_count[$log_str];
}








/**
 * Simply return a localized text or empty string if the key is empty
 * Useful when localize variable which can be empty
 *
 * @param string  $text the text key
 * @return   string                      the translation
 */
function __( $text ) {
	if ( empty( $text ) )
		return '';
	else
		return gettext( $text );
}


/**
 * Simply echo a localized text
 *
 * @param string  $text the text key
 * @return   void
 */
function _e( $text ) {
	echo __( $text );
}








/**
 * The log parser
 *
 * @param string  $regex      The regex which describes the user log format
 * @param array   $match      An array which links internal tokens to regex matches
 * @param string  $log        The text log
 * @param string  $types      A array of types for fields
 *
 * @return  mixed             An array where keys are internal tokens and values the corresponding values extracted from the log file. Or false if line is not matchable.
 */
function parser( $regex , $match , $log , $types , $tz = NULL ) {

	$result = array();
	preg_match_all( $regex , $log , $out, PREG_PATTERN_ORDER );
	if ( @count( $out[0] )==0 ) {
		return false;
	}
	foreach ( $match as $token => $key ) {

		$type = ( isset ( $types[ $token ] ) ) ? $types[ $token ] : 'txt';

		if ( substr( $type , 0 , 4 ) === 'date' ) {

			// Date is an array description with keys ( 'Y' : 5 , 'M' : 2 , ... )
			if ( is_array( $key ) && ( is_assoc( $key ) ) ) {
				$newdate = array();
				foreach ( $key as $k => $v ) {
					$newdate[ $k ] = @$out[ $v ][ 0 ];
				}
				if ( isset( $newdate['M'] ) ) {
					$str = $newdate['M'] . ' ' . $newdate['d'] . ' ' . $newdate['H'] . ':' . $newdate['i'] . ':' . $newdate['s'] . ' ' . $newdate['Y'];
				}
				else if ( isset( $newdate['m'] ) ) {
					$str = $newdate['Y'] . '/' . $newdate['m'] . '/' . $newdate['d'] . ' ' . $newdate['H'] . ':' . $newdate['i'] . ':' . $newdate['s'];
				}
			}
			// Date is an array description without keys ( 2 , ':' , 3 , '-' , ... )
			else if ( is_array( $key ) ) {
				$str = '';
				foreach ( $key as $v ) {
					$str .= ( is_string( $v ) ) ? $v : @$out[ $v ][0];
				}
			}
			else {
				$str = @$out[ $key ][0];
			}

			// remove part next to the last /
			$dateformat = ( substr( $type , 0 , 5 ) === 'date:' ) ? substr( $type , 5 ) : 'Y/m/d H:i:s';
			if ( ( $p = strrpos(	$dateformat , '/' ) ) !== false ) {
				$dateformat = substr( $dateformat , 0 , $p );
			}
			if ( ( $timestamp = strtotime( $str ) ) === false ) {
				$date = "ERROR ! Unable to convert this string to date : <code>$str</code>";
			}
			else {
				$date = new DateTime( );
				$date->setTimestamp( $timestamp );
				if ( ! is_null( $tz ) ) {
					$date->setTimezone( new DateTimeZone( $tz ) );
				}
				$date = $date->format( $dateformat );
			}

			$result[ $token ] = $date;
		}
		// Array description without keys ( 2 , ':' , 3 , '-' , ... )
		else if ( is_array( $key ) ) {
			$r = '';
			foreach ( $key as $v ) {
				$r .= ( is_string( $v ) ) ? $v : @$out[ $v ][0];
			}
			$result[ $token ] = $r;
		}

		else {
			$result[ $token ] = @$out[ $key ][0];
		}
	}
	return $result;
}


/**
 * Set unset constants
 */
function init() {
	if ( ! defined( 'LOCALE'                     ) ) define( 'LOCALE'                     , 'gb_GB' );
	if ( ! defined( 'TITLE'                      ) ) define( 'TITLE'                      , 'Pimp my Log' );
	if ( ! defined( 'TITLE_FILE'                 ) ) define( 'TITLE_FILE'                 , 'Pimp my Log [%f]' );
	if ( ! defined( 'NAV_TITLE'                  ) ) define( 'NAV_TITLE'                  , '' );
	if ( ! defined( 'FOOTER'                     ) ) define( 'FOOTER'                     , '&copy; <a href="http://www.potsky.com" target="doc">Potsky</a> 2007-' . @date('Y') . ' - <a href="http://pimpmylog.com" target="doc">Pimp my Log</a>');
	if ( ! defined( 'LOGS_MAX'                   ) ) define( 'LOGS_MAX'                   , 50 );
	if ( ! defined( 'LOGS_REFRESH'               ) ) define( 'LOGS_REFRESH'               , 0 );
	if ( ! defined( 'NOTIFICATION'               ) ) define( 'NOTIFICATION'               , false );
	if ( ! defined( 'PULL_TO_REFRESH'            ) ) define( 'PULL_TO_REFRESH'            , true );
	if ( ! defined( 'NOTIFICATION_TITLE'         ) ) define( 'NOTIFICATION_TITLE'         , 'New logs [%f]' );
	if ( ! defined( 'GOOGLE_ANALYTICS'           ) ) define( 'GOOGLE_ANALYTICS'           , 'UA-XXXXX-X' );
	if ( ! defined( 'GEOIP_URL'                  ) ) define( 'GEOIP_URL'                  , 'http://www.geoiptool.com/en/?IP=%p' );
	if ( ! defined( 'CHECK_UPGRADE'              ) ) define( 'CHECK_UPGRADE'              , true );
	if ( ! defined( 'PIMPMYLOG_VERSION_URL'      ) ) define( 'PIMPMYLOG_VERSION_URL'      , 'http://demo.pimpmylog.com/version.js' );
	if ( ! defined( 'PIMPMYLOG_ISSUE_LINK'       ) ) define( 'PIMPMYLOG_ISSUE_LINK'       , 'https://github.com/potsky/PimpMyLog/issues/' );
	if ( ! defined( 'MAX_SEARCH_LOG_TIME'        ) ) define( 'MAX_SEARCH_LOG_TIME'        , 10 );
	if ( ! defined( 'FILE_SELECTOR'              ) ) define( 'FILE_SELECTOR'              , 'bs' );
}



/**
 * Load config file
 */
function config_load( $path = 'config.user.json' ) {
	global $files, $badges;
	$files  = array();
	$badges = array();

	if ( ! file_exists( $path ) ) {
		return false;
	}
	$config = json_decode( file_get_contents( $path ) , true );
	if ( $config == null ) {
		return false;
	}
	$badges = $config[ 'badges' ];

	foreach ( $config[ 'globals' ] as $cst => $val ) {
		if ( $cst == strtoupper( $cst ) ) {
			@define( $cst , $val );
		}
	}

	// Try to generate the files tree if there are globs...
	$files_tmp = $config[ 'files' ];
	$files     = array();

	foreach ( $files_tmp as $fileid => $file ) {

		$path   = $file['path'];
		$count  = max( 1 , @(int)$file['count']);
		$gpaths = glob( $path , GLOB_MARK | GLOB_NOCHECK );

		if ( count( $gpaths ) == 0 ) {
		}

		else if ( count( $gpaths ) == 1 ) {
			$files[ $fileid ]            = $file;
			$files[ $fileid ]['path']    = $gpaths[0];
		}

		else {
			$new_paths = array();
			$i         = 1;

			foreach ( $gpaths as $path ) {
				$new_paths[ $path ] = filemtime( $path );
			}

			arsort( $new_paths , SORT_NUMERIC );

			foreach ( $new_paths as $path => $lastmodified ) {
				$files[ $fileid . '_' . $i ]            = $file;
				$files[ $fileid . '_' . $i ]['path']    = $path;
				$files[ $fileid . '_' . $i ]['display'].= ' > ' . basename( $path );
				if ( $i >= $count ) {
					break;
				}
				$i++;
			}
		}
	}

	return true;
}



/**
 * Check the $files array and fix it with default values
 * If there is a problem, return an array of errors
 * If everything is ok, return true;
 *
 * @return  mixed  true if ok, otherwise an array of errors
 */
function config_check() {
	global $files;
	$errors = array();

	if ( count( $files ) == 0 ) {
		$errors[] = __( 'No file is defined in <code>files</code> array' );
		return $errors;
	}

	foreach ( $files as $file_id => &$file ) {
		// error
		foreach ( array( 'display' , 'path' , 'format' ) as $mandatory ) {
			if ( ! isset( $file[ $mandatory ] ) ) {
				$errors[] = sprintf( __( '<code>%s</code> is mandatory for file ID <code>%s</code>' ) , $mandatory , $file_id );
			}
		}
		// fix
		foreach ( array(
				'max'       => LOGS_MAX,         ///////////////////////////////////////
				'refresh'   => LOGS_REFRESH,
				'notify'    => NOTIFICATION,
		) as $fix => $value ) {
			if ( ! isset( $file[ $fix ] ) ) {
				$file[ $fix ] = $value;
			}
		}
	}

	if ( count($errors) == 0 ) {
		return true;
	}
	else {
		return $errors;
	}
}


/**
 * Get the list of refresh duration
 * The list is the default one below + :
 * - a custom value defined by user in PHP constant LOGS_REFRESH
 * - a custom value defined by user in all files in PHP array $files
 * The list must by unique and sorted
 *
 * @return  array  the list of selectable values
 */
function get_refresh_options() {
	global $files;
	$options = array(
		1  => 1,
		2  => 2,
		3  => 3,
		4  => 4,
		5  => 5,
		10 => 10,
		15 => 15,
		30 => 30,
		45 => 45,
		60 => 60
	);
	$options[ (int)LOGS_REFRESH ] = (int)LOGS_REFRESH;
	foreach ( $files as $file_id => $file ) {
		$options[ (int) @$file['refresh'] ] = (int) @$file['refresh'];
	}
	unset( $options[0] );
	sort( $options );
	return $options;
}


/**
 * Get the list of displayed logs count
 * The list is the default one below + :
 * - a custom value defined by user in PHP constant LOGS_MAX
 * - a custom value defined by user in all files in PHP array $files
 * The list must by unique and sorted
 *
 * @return  array  the list of selectable values
 */
function get_max_options() {
	global $files;
	$options = array(
		5   => 5,
		10  => 10,
		20  => 20,
		50  => 50,
		100 => 100,
		200 => 2000000000000000
	);
	$options[ (int)LOGS_MAX ] = (int)LOGS_MAX;
	foreach ( $files as $file_id => $file ) {
		$options[ (int) @$file['max'] ] = (int) @$file['max'];
	}
	unset( $options[0] );
	sort( $options );
	return $options;
}


/**
 * Return a human representation of a size
 *
 * @param   string   $bytes     the string representation (can be an int)
 * @param   integer  $decimals  the number of digits in the float part
 *
 * @return  string              the human size
 */
function human_filesize( $bytes, $decimals = 0 ) {
	$sz = __( 'B KBMBGBTBPB' );
	$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );
	return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . @$sz[$factor*2];
}


/**
 * Get a Cross Script Request Forgery token
 *
 * @return  string  a token
 */
function csrf_get() {
	session_start();
	if ( ! isset( $_SESSION[ 'csrf_token' ] ) ) {
		$_SESSION[ 'csrf_token' ] = md5( uniqid( '' , true ) );
	}
	session_write_close();
	return $_SESSION[ 'csrf_token' ];
}


/**
 * Verify a Cross Script Request Forgery token
 *
 * @return  boolean   verified ?
 */
function csrf_verify() {
	session_start();
	$s = @$_SESSION[ 'csrf_token' ];
	session_write_close();
	if ( ! isset( $_POST[ 'csrf_token' ] ) )
		return false;
	return ( $s === @$_POST[ 'csrf_token' ] );
}


/**
 * Indents a flat JSON string to make it more human-readable.
 * For PHP < 5.4
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function json_indent( $json ) {
	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;
	for ($i=0; $i<=$strLen; $i++) {
		$char = substr($json, $i, 1);
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++) {
				$result .= $indentStr;
			}
		}
		$result .= $char;
		if ( ( $char == ',' || $char == '{' || $char == '[' ) && $outOfQuotes ) {
			$result .= $newLine;
			if ( $char == '{' || $char == '[' ) {
				$pos ++;
			}
			for ( $j = 0 ; $j < $pos ; $j++ ) {
				$result .= $indentStr;
			}
		}
		$prevChar = $char;
	}
	return $result;
}


/**
 * Remove jsonp callback from a version file
 *
 * @param   string  $data  the json file with callback
 *
 * @return  string         the json file without callback
 */
function clean_json_version( $data ) {
	return str_replace(	array( '/*PSK*/pml_version_cb(/*PSK*/' , '/*PSK*/);/*PSK*/' , '/*PSK*/)/*PSK*/' ) , array( '' , '' , '' ) , $data );
}


/**
 * Try to guess who runs the server
 *
 * @return  string  a user information
 */
function get_server_user() {
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		return '';
	} else {
		return @exec( 'whoami' );
	}
}


/**
 * Tell whether this is a associative array (object in javascript) or not (array in javascript)
 *
 * @param   array   $arr  the array to test
 *
 * @return  boolean        true if $arr is an associative array
 */
function is_assoc( $arr ) {
    return array_keys( $arr ) !== range( 0 , count( $arr ) - 1 );
}


?>