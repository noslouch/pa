<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * Dynamic Configs
 --------------------------------------------------------------------------------*/

$s = 'PETER ARNELL';

$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$base_url .= "://".$_SERVER['HTTP_HOST'];
$admin_url  = $base_url . '/admin.php';

$env = strpos($base_url, 'staging') ? 'staging' : 'production';
$env = strpos($base_url, 'local') ? 'local' : $env;

$config['app_version'] = "271";
$config['install_lock'] = "";
$config['license_number'] = 'CORE LICENSE';
$config['debug'] = '1';
$config['doc_url'] = 'http://ellislab.com/expressionengine/user-guide/';
$config['is_system_on'] = 'y';
$config['allow_extensions'] = 'y';
$config['system_folder'] = 'core';
$config['site_url'] = $base_url;
$config['server_path'] = FCPATH;
$config['cp_url'] = $admin_url;
$config['site_index'] = '';

$config['cookie_prefix'] = '';

$config['theme_folder_url'] = $config['site_url']."/themes/";
$config['theme_folder_path'] = $config['server_path']."themes/";
$config['save_tmpl_files'] = "y";
$config['tmpl_file_basepath'] = $config['server_path']."templates/";

$config['avatar_url'] = $base_url."/uploads/system/avatars/";
$config['avatar_path'] = $config['server_path']."uploads/system/avatars/";
$config['photo_url'] = $base_url."/uploads/system/member_photos/";
$config['photo_path'] = $config['server_path']."uploads/system/member_photos/";
$config['sig_img_url'] = $base_url."/uploads/system/signature_attachments/";
$config['sig_img_path'] = $config['server_path']."uploads/system/signature_attachments/";
$config['prv_msg_upload_path'] = $config['server_path']."uploads/system/pm_attachments/";

// END EE config items

/* 
 * CE Cache Config
 ------------------------------------------------------------------*/
$config['ce_cache_static_enabled'] = 'yes';
$config['ce_cache_static_path'] = $config['server_path'] . "/static/";
//$config['ce_cache_off'] = 'yes';

/* 
 * CodeIgniter Configuration
 ------------------------------------------------------------------*/
$config['base_url'] = $config['site_url'];
$config['uri_protocol'] = 'AUTO';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['subclass_prefix'] = 'EE_';
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\\-';
$config['enable_query_strings'] = FALSE;
$config['directory_trigger'] = 'D';
$config['controller_trigger'] = 'C';
$config['function_trigger'] = 'M';
$config['log_threshold'] = 0;
$config['log_path'] = '';
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['time_reference'] = 'local';

/* 
 * Universal database connection settings
 -------------------------------------------------------------------*/
$active_group = $env;
$active_record = TRUE;

$db['local']['hostname'] = '127.0.0.1';
$db['local']['username'] = 'root';   
$db['local']['password'] = 'root'; 
$db['local']['database'] = 'arnell_clone';   
$db['local']['dbprefix'] = 'exp_';     
$db['staging']['hostname'] = '127.0.0.1';
$db['staging']['username'] = 'dbuser';   
$db['staging']['password'] = '#098sdbA'; 
$db['staging']['database'] = 'staging';   
$db['staging']['dbprefix'] = 'exp_';     
$db['production']['hostname'] = '127.0.0.1';
$db['production']['username'] = 'dbuser';
$db['production']['password'] = '#098sdbA';
$db['production']['database'] = 'arnell';
$db['production']['dbprefix'] = 'exp_';

$db[$active_group]['dbdriver'] = "mysql";
$db[$active_group]['pconnect'] = FALSE;
$db[$active_group]['swap_pre'] = "exp_";
$db[$active_group]['db_debug'] = FALSE;
$db[$active_group]['cache_on'] = FALSE;
$db[$active_group]['autoinit'] = FALSE;
$db[$active_group]['char_set'] = "utf8";
$db[$active_group]['dbcollat'] = "utf8_general_ci";
$db[$active_group]['cachedir'] = $config['server_path'].$config['system_folder']."/expressionengine/cache/db_cache/";

/* End of file config.php */
/* Location: ./system/expressionengine/config/config.php */