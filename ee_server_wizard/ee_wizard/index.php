<?php
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2009, EllisLab, Inc.
 * @license		http://expressionengine.com/docs/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (version_compare(PHP_VERSION, '5.3') < 0)
{
	@set_magic_quotes_runtime(0); // Kill magic quotes
}

define('SERVER_WIZ', TRUE);

global $vars, $requirements;
$vars = array();
load_defaults();

// AcceptPathInfo or similar support, i.e. no need for query strings
// check this first so it's already known before we go through
// the trouble of having the user fill out the database form
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
{	
	if ( ! isset($_COOKIE['wizard_segments']) && ! isset($_GET['cookie_check']))
	{
		setcookie('wizard_segments', 'check', time() + 60*60*2, '/', '', 0);
		
		@header("Location: index.php?cookie_check=yes");
	}
	elseif (isset($_GET['cookie_check']))
	{
		if (isset($_COOKIE['wizard_segments']))
		{
			@header("Location: index.php/segment_test/");		
		}
		else
		{
			$vars['errors'] = 'Cookies must be enabled';
		}		
	}	
	elseif($_COOKIE['wizard_segments'] == 'check')
	{
		$pathinfo = pathinfo(__FILE__);
		$self = ( ! isset($pathinfo['basename'])) ? 'index'.$ext : $pathinfo['basename'];
		$path_info = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
		$orig_path_info = str_replace($_SERVER['SCRIPT_NAME'], '', (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO'));
		
		if ($path_info != '' && $path_info != "/".$self)
		{
			$requirements['segment_support']['supported'] = 'y';
		}
		elseif($orig_path_info != '' && $orig_path_info != "/".$self)
		{
			$requirements['segment_support']['supported'] = 'y';
		}
		else
		{
			$requirements['segment_support']['supported'] = 'n';
		}
		
		setcookie('wizard_segments', $requirements['segment_support']['supported'], time() + 60*60*2, '/', '', 0);
		@header("Location: ../../index.php");
	}
	else
	{
		$requirements['segment_support']['supported'] = ($_COOKIE['wizard_segments'] == 'y') ? 'y' : 'n';
	}
}
else
{
	$requirements['segment_support']['supported'] = 'n'; // Windows rarely has support for URL Segments and is rather likely to screw up here.
}

// Memory Limit
$memory_limit = @ini_get('memory_limit');

sscanf($memory_limit, "%d%s", $limit, $unit);

if ($limit >= 32)
{
    $requirements['memory_limit']['supported'] = 'y';
}

// --------------------------------------------------------------------
// Display the form if this is the first load
// --------------------------------------------------------------------

if ( ! isset($_GET['wizard']) OR $_GET['wizard'] != 'run')
{
	$vars['content'] = view('db_form', $vars, TRUE);
	display_and_exit();
}

// --------------------------------------------------------------------
// Validate form
// --------------------------------------------------------------------

$db = array(
				'db_hostname'			=> '',
				'db_username'			=> '',
				'db_password'			=> '',
				'db_name'				=> ''
			);

foreach ($db as $key => $val)
{
	if ( ! isset($_POST[$key]) OR ($_POST[$key] == '' && $key != 'db_password'))
	{
		$vars['message'] = 'The field '.ucfirst(str_replace('db_', '', $key)).' is required.';
		$vars['content'] = view('error_message', $vars, TRUE);
		display_and_exit();
	}
	
	$db[$key] = $_POST[$key];
}

// Database check
if (check_db($db) === TRUE)
{
	$requirements['mysql']['supported'] = 'y';
}

// PHP Version
if (version_compare(phpversion(), '5.2.4', '>='))
{
	$requirements['php']['supported'] = 'y';
}
else
{
	$vars['errors'][] = "Your PHP version does not meet the minimum requirements";
}

// Check for json_encode and decode
if ( ! function_exists('json_encode'))
{
	$vars['errors'][] = 'Your instance of PHP does not support the json_encode method.';
}
if ( ! function_exists('json_decode'))
{
	$vars['errors'][] = 'Your instance of PHP does not support the json_decode method.';
}

// CAPTCHAS need imagejpeg()
if (function_exists('imagejpeg'))
{
	$requirements['captchas']['supported'] = 'y';
}

// Image properties
if (function_exists('gd_info'))
{
	$requirements['image_properties']['supported'] = 'y';
}

// Image thumbnailing
if (function_exists('gd_info') OR function_exists('exec'))
{
	$requirements['image_resizing']['supported'] = 'y';
}

// GIF resizing
if (function_exists('imagegif'))
{
	$requirements['gif_resizing']['supported'] = 'y';
}

// JPG resizing
if (function_exists('imagejpeg'))
{
	$requirements['jpg_resizing']['supported'] = 'y';
}

// PNG resizing
if (function_exists('imagepng'))
{
	$requirements['png_resizing']['supported'] = 'y';
}

// Pings
if (function_exists('fsockopen') && 
	function_exists('xml_parser_create') &&
	@fsockopen('www.google.com', 80, $errno, $errstr, 2))
{
	$requirements['pings']['supported'] = 'y';

	// can use Google Spellcheck too
	if (extension_loaded('openssl'))
	{
		$requirements['spellcheck']['supported'] = 'y';
	}
}

// Native Spellcheck
if (function_exists('pspell_check'))
{
	$requirements['spellcheck']['supported'] = 'y';
}

// one last try for spellcheck
if ($requirements['spellcheck']['supported'] != 'y' && function_exists('curl_init'))
{
	$url = 'https://www.google.com/tbproxy/spell?lang=en&hl=en';
	
	$payload = 	'<spellrequest textalreadyclipped="0" ignoredups="1" ignoredigits="1" ignoreallcaps="0"><text>'
				.	'test content'
				.'</text></spellrequest>';

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_POST, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

	$info = curl_exec($ch);

	curl_close($ch);

	if ($info != '')
	{
		$requirements['spellcheck']['supported'] = 'y';
	}
}

$vars['requirements'] = $requirements;
$vars['content'] = view('requirements_table', $vars, TRUE);
display_and_exit();


/**
 * Check DB
 *
 * @access	public
 * @param	array
 * @return	bool
 */
function check_db($db_config)
{
	global $vars;
	
	foreach ($db_config as $key => $val)
	{
		$db_config[$key] = addslashes(trim($val));
	}

	$conn = @mysql_connect($db_config['db_hostname'], $db_config['db_username'], $db_config['db_password']);
	
	if ( ! $conn)
	{
		$vars['errors'][] = 'Unable to connect to your database server';
	}
	else
	{
		if ( ! @mysql_select_db($db_config['db_name'], $conn))
		{
			$vars['errors'][] = 'Unable to select your database';
		}
		else
		{
			// Check version requirement
			if (version_compare(@mysql_get_server_info(), '5.0.3', '>=') !== TRUE)
			{
				$vars['errors'][] = "Your MySQL server version does not meet the minimum requirements";
			}
			
			$Q = array();
			$Q['create'] = "CREATE TABLE IF NOT EXISTS ee_test (
					ee_id int(2) unsigned NOT NULL auto_increment,
					ee_text char(2) NOT NULL default '',
					PRIMARY KEY (ee_id))";
			$Q['alter'] = "ALTER TABLE ee_test CHANGE COLUMN ee_text ee_text char(3) NOT NULL";
			$Q['insert'] = "INSERT INTO ee_test (ee_text) VALUES ('hi')";
			$Q['update'] = "UPDATE ee_test SET ee_text = 'yo'";
			$Q['drop'] = "DROP TABLE IF EXISTS ee_test";
			
			foreach($Q as $type => $sql)
			{
				if ( ! $query = @mysql_query($sql, $conn))
				{
					$vars['errors'][] = "Your MySQL user does not have ".strtoupper($type)." permissions";
				}
			}
		}
	}
	
	return (count($vars['errors']) > 0) ? FALSE : TRUE;
}


// --------------------------------------------------------------------

/**
 * Display and Exit
 *
 * @access	public
 * @return	void
 */
function display_and_exit()
{
	global $vars;
	echo view('container', $vars);
	exit;
}

// --------------------------------------------------------------------

/**
 * Load default variables
 *
 * @access	public
 * @return	void
 */
function load_defaults()
{
	global $vars, $requirements;
	
	$vars['heading']		= "ExpressionEngine 2.x Server Compatibility Wizard";
	$vars['title']			= "ExpressionEngine 2.x Server Compatibility Wizard";
	$vars['content']		= '';
	$vars['errors']			= array();
	$vars['db_hostname']	= (isset($_POST['db_hostname'])) ? $_POST['db_hostname'] : '';
	$vars['db_username']	= (isset($_POST['db_username'])) ? $_POST['db_username'] : '';
	$vars['db_password']	= (isset($_POST['db_password'])) ? $_POST['db_password'] : '';
	$vars['db_name']		= (isset($_POST['db_name'])) ? $_POST['db_name'] : '';
	
	
	$requirements = array('php' 			=>	array(	'item'			=> "PHP Version 5.2.4 or greater",
											 			'severity'		=> "required",
										 				'supported'		=> 'n'),

						 'mysql'			=>	array(	'item'			=> "MySQL (Version 5.0.3) support in PHP",
						 								'severity'		=> "required",
						 								'supported'		=> 'n'),
						'memory_limit'		=> array(	'item'			=> '>= 32 MB Memory Allocated to PHP',
														'severity'		=> 'required',
														'supported'		=> 'n' ),
						 'segment_support'	=>	array(	'item'			=>	"URL Segment Support",
						 								'severity'		=>	"suggested",
						 								'supported'		=>	'n'),

						 'captchas'			=>	array(	'item'			=> "CAPTCHAs feature and watermarking in Image Gallery",
						 								'severity'		=> "suggested",
						 								'supported'		=> 'n'),

						 'pings'			=>	array(	'item'			=> "Ability to send Pings",
						 								'severity'		=> "suggested",
						 								'supported'		=> 'n'),

						 'image_properties'	=>	array('item'		=> "Image property calculations using GD",
						 								'severity'		=> "suggested",
						 								'supported'		=> 'n'),

						 'image_resizing'	=>	array(	'item'			=> "Image Thumbnailing using GD, GD2, Imagemagick or NetPBM",
						 								'severity'		=> "suggested",
						 								'supported'		=> 'n'),

						 'gif_resizing'		=>	array(	'item'			=> "GIF Image Resizing Using GD (or GD 2)",
						 								'severity'		=> "optional",
						 								'supported'		=> 'n'),

						 'jpg_resizing'		=>	array(	'item'			=> "JPEG Image Resizing Using GD (or GD 2)",
						 								'severity'		=> "optional",
						 								'supported'		=> 'n'),

						 'png_resizing'		=>	array(	'item'			=> "PNG Image Resizing Using GD (or GD 2)",
						 								'severity'		=> "optional",
						 								'supported'		=> 'n'),

						 'spellcheck'		=>	array(	'item'			=> "Built in Spellchecker",
						 								'severity'		=> "optional",
						 								'supported'		=> 'n'),
					);
}

// --------------------------------------------------------------------

/**
 * Load View
 *
 * This function is used to load a "view" file.  It has three parameters:
 *
 * 1. The name of the "view" file to be included.
 * 2. An associative array of data to be extracted for use in the view.
 * 3. TRUE/FALSE - whether to return the data or load it.  In
 * some cases it's advantageous to be able to return data so that
 * a developer can process it in some way.
 *
 * @access	public
 * @param	string
 * @param	array
 * @param	bool
 * @return	void
 */
function view($view, $vars = array(), $return = FALSE)
{
	return _mini_loader(array('_ci_view' => $view, '_ci_vars' => $vars, '_ci_return' => $return));
}

// --------------------------------------------------------------------


/**
 * Loader
 *
 * This function is used to load views and files.
 * Variables are prefixed with _ci_ to avoid symbol collision with
 * variables made available to view files
 *
 * @access	private
 * @param	array
 * @return	void
 */
function _mini_loader($_ci_data)
{
	static $_ci_cached_vars = array();
	
	// Set the default data variables
	foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val)
	{
		$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
	}

	// Set the path to the requested file
	if ($_ci_path == '')
	{
		$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
		$_ci_file = ($_ci_ext == '') ? $_ci_view.'.php' : $_ci_view;
		$_ci_path = './content/'.$_ci_file;
	}
	else
	{
		$_ci_x = explode('/', $_ci_path);
		$_ci_file = end($_ci_x);
	}
	
	if ( ! file_exists($_ci_path))
	{
		exit('Unable to load the requested file: '.$_ci_file);
	}

	/*
	 * Extract and cache variables
	 *
	 * You can either set variables using the dedicated $this->load_vars()
	 * function or via the second parameter of this function. We'll merge
	 * the two types and cache them so that views that are embedded within
	 * other views can have access to these variables.
	 */	
	if (is_array($_ci_vars))
	{
		$_ci_cached_vars = array_merge($_ci_cached_vars, $_ci_vars);
	}
	extract($_ci_cached_vars);
			
	/*
	 * Buffer the output
	 *
	 * We buffer the output for two reasons:
	 * 1. Speed. You get a significant speed boost.
	 * 2. So that the final rendered template can be
	 * post-processed by the output class.  Why do we
	 * need post processing?  For one thing, in order to
	 * show the elapsed page load time.  Unless we
	 * can intercept the content right before it's sent to
	 * the browser and then stop the timer it won't be accurate.
	 */
	ob_start();
			
	// If the PHP installation does not support short tags we'll
	// do a little string replacement, changing the short tags
	// to standard PHP echo statements.
	
	include($_ci_path); // include() vs include_once() allows for multiple views with the same name

	$buffer = ob_get_contents();
	@ob_end_clean();
	return $buffer;
}

// --------------------------------------------------------------------

/* End of file index.php */
/* Location: ./index.php */
