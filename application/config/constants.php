<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| WEPOS CONSTANT
|--------------------------------------------------------------------------
|
| These constants use by wepos
|
*/

// Base URL (keeps this crazy sh*t out of the config.php
if(isset($_SERVER['HTTP_HOST']))
{
	$base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
	$base_url .= '://'. $_SERVER['HTTP_HOST'];
	$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
	
	// Base URI (It's different to base URL!)
	$base_uri = parse_url($base_url, PHP_URL_PATH);
	if(substr($base_uri, 0, 1) != '/') $base_uri = '/'.$base_uri;
	if(substr($base_uri, -1, 1) != '/') $base_uri .= '/';
}
else
{
	$base_url = 'http://localhost/';
	$base_uri = '/';
}

// Define these values to be used later on
//$APPPATH_PATH = str_replace('/','',APPPATH);
$APPPATH_PATH = APPPATH;
define('BASE_URL', $base_url);
define('BASE_URI', $base_uri);
define('APP_URI', BASE_URI.APPPATH);
define('APP_PATH', FCPATH.$APPPATH_PATH);
define('APP_URL', BASE_URL.APPPATH);
define('MODULE_PATH', $APPPATH_PATH.'modules/');
define('MODULE_URL', BASE_URL.APPPATH.'modules/');

// We dont need these variables any more
unset($base_uri, $base_url);

//URL suffix
$config['url_suffix'] = ".html";
define('F_EXT', $config['url_suffix']);

//Session Prefix
$config['session_prefix'] = "cixt_";
define('S_EXT', $config['session_prefix']);

//assets base web url files
$assets_folder = 'assets';
define('ASSETS_URL', BASE_URL .$assets_folder.'/');
define('JS_URL', BASE_URL .$assets_folder. '/js/');
define('RESOURCES_URL', BASE_URL .$assets_folder. '/resources/');

//base web path files
define('BASE_PATH', FCPATH);
define('ASSETS_PATH', FCPATH .$assets_folder.'/');
define('JS_PATH', FCPATH .$assets_folder.'/js/');
define('RESOURCES_PATH', FCPATH .$assets_folder.'/resources/');

//EMAIL CONFIG
define('BASE_EMAIL', "angga.nugraha@gmail.com");
define('BASE_EMAIL_NAME', "HopesApps");
define('FROM_EMAIL', BASE_EMAIL_NAME." <".BASE_EMAIL.">");

//SMTP CONFIG
define('SMTP_HOST', "ssl://smtp.gmail.com");
define('SMTP_USER', "xxx@gmail.com");
define('SMTP_PASS', "xxx");
define('SMTP_PORT', "465");

/*BASE PAGINATION*/
define('CMS_PERPAGE', 10);
define('PAGE_PREFIX', 'hal');
define('CMS_PERPAGE_PREFIX', PAGE_PREFIX.'/');

/*NUMBER CONSTANT*/
define('ONE_DAY_UNIX', 86400);
define('ONE_COMP_SYS', 1);
define('ONE_COMP_CORE', 0);
define('USE_GZIP_MODE', false);

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
