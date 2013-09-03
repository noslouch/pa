<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (isset($this->EE) == FALSE) $this->EE =& get_instance(); // For EE 2.2.0+


$config['updater_module_defaults']['debug'] = 'no';
$config['updater_module_defaults']['file_transfer_method'] = 'local';

$config['updater_module_defaults']['ftp']['hostname'] = '';
$config['updater_module_defaults']['ftp']['username'] = '';
$config['updater_module_defaults']['ftp']['password'] = '';
$config['updater_module_defaults']['ftp']['port'] = '21';
$config['updater_module_defaults']['ftp']['passive'] = 'yes';
$config['updater_module_defaults']['ftp']['ssl'] = 'no';

$config['updater_module_defaults']['sftp']['hostname'] = '';
$config['updater_module_defaults']['sftp']['username'] = '';
$config['updater_module_defaults']['sftp']['password'] = '';
$config['updater_module_defaults']['sftp']['port'] = '22';
$config['updater_module_defaults']['sftp']['auth_method'] = 'password';
$config['updater_module_defaults']['sftp']['key_contents'] = '';
$config['updater_module_defaults']['sftp']['key_password'] = '';
$config['updater_module_defaults']['sftp']['key_path'] = '';

$config['updater_module_defaults']['path_map']['root'] = '';
$config['updater_module_defaults']['path_map']['backup'] = '';
$config['updater_module_defaults']['path_map']['system'] = '';
$config['updater_module_defaults']['path_map']['system_third_party'] = '';
$config['updater_module_defaults']['path_map']['themes'] = '';
$config['updater_module_defaults']['path_map']['themes_third_party'] = '';

$config['updater_module_defaults']['menu_link']['root'] = 'yes';
$config['updater_module_defaults']['menu_link']['tools'] = 'yes';
$config['updater_module_defaults']['menu_link']['admin'] = 'yes';

$config['updater_module_defaults']['action_url']['actionGeneralRouter'] = '';

$config['updater_module_defaults']['track_stats'] = 'yes';
$config['updater_module_defaults']['infinite_memory'] = 'yes';

$config['updater_native_packages'] = array();

// Modules
$config['updater_native_packages'][] = 'blacklist';
$config['updater_native_packages'][] = 'channel';
$config['updater_native_packages'][] = 'comment';
$config['updater_native_packages'][] = 'email';
$config['updater_native_packages'][] = 'emoticon';
$config['updater_native_packages'][] = 'file';
$config['updater_native_packages'][] = 'ip_to_nation';
$config['updater_native_packages'][] = 'jquery';
$config['updater_native_packages'][] = 'mailinglist';
$config['updater_native_packages'][] = 'member';
$config['updater_native_packages'][] = 'metaweblog_api';
$config['updater_native_packages'][] = 'moblog';
$config['updater_native_packages'][] = 'pages';
$config['updater_native_packages'][] = 'query';
$config['updater_native_packages'][] = 'referrer';
$config['updater_native_packages'][] = 'rss';
$config['updater_native_packages'][] = 'rte';
$config['updater_native_packages'][] = 'safecracker';
$config['updater_native_packages'][] = 'search';
$config['updater_native_packages'][] = 'simple_commerce';
$config['updater_native_packages'][] = 'stats';
$config['updater_native_packages'][] = 'updated_sites';
$config['updater_native_packages'][] = 'wiki';
$config['updater_native_packages'][] = 'forum';

// Third Party folder
$config['updater_native_packages'][] = 'safecracker_file';

// Accessoiris
$config['updater_native_packages'][] = 'expressionengine_info';
$config['updater_native_packages'][] = 'learning';
$config['updater_native_packages'][] = 'news_and_stats';
$config['updater_native_packages'][] = 'quick_tips';


// Fieldtypes
$config['updater_native_packages'][] = 'checkboxes';
$config['updater_native_packages'][] = 'date';
$config['updater_native_packages'][] = 'file';
$config['updater_native_packages'][] = 'hidden';
$config['updater_native_packages'][] = 'multi_select';
$config['updater_native_packages'][] = 'radio';
$config['updater_native_packages'][] = 'rel';
$config['updater_native_packages'][] = 'select';
$config['updater_native_packages'][] = 'text';
$config['updater_native_packages'][] = 'textarea';
$config['updater_native_packages'][] = 'zero_wing';
$config['updater_native_packages'][] = 'magpie';
$config['updater_native_packages'][] = 'xml_encode';

//RTE
$config['updater_native_packages'][] = 'blockquote';
$config['updater_native_packages'][] = 'bold';
$config['updater_native_packages'][] = 'headings';
$config['updater_native_packages'][] = 'image';
$config['updater_native_packages'][] = 'italic';
$config['updater_native_packages'][] = 'link';
$config['updater_native_packages'][] = 'ordered_list';
$config['updater_native_packages'][] = 'underline';
$config['updater_native_packages'][] = 'unordered_list';
$config['updater_native_packages'][] = 'view_source';
