<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(
'updater'	=>	'Updater',
'updater_module_name'       =>  'Updater',
'updater_module_description'=>  'Updates ExpressionEngine & Installs/Updates Addons',
'nav_updater' => 'Updater',

'u:dashboard'   =>  'Dashboard',
'u:settings' => 'Settings',

'u:yes' =>  'Yes',
'u:no'  =>  'No',
'u:save'=>  'Save',
'u:close'=> 'Close',
'u:loading'=> 'loading...',
'u:only_super_admins' => 'Only Super Admins can access this area',
'u:queries_executed'  => 'SQL Queries Executed',
'u:warning' => 'Warning!',

// Home
'u:ee_and_addons' => 'Install/Update',
'u:current_ver' => 'Current Version',
'u:upload_max_size' =>  'Upload Max Size',
'u:upload_status' => 'Upload Status',
'u:no_files_up' => 'No files have yet been queued for upload',

// Drag & Drop
'u:drag_drop' => 'Drag and drop files here',
'u:or' => 'or',
'u:select_files' => 'Select Files',

// Backup
'u:backup' => 'Backup',
'u:backup_db' => 'Backup Database',
'u:backup_files'=> 'Backup Files',

// Actions
'u:actions' => 'Actions',
'u:actions_none' => 'No actions have yet been queued',
'u:actions_start' => 'Start Action Queue',

/*
'u:show_all' => 'Show All',
'u:module' => 'Module',
'u:modules' => 'Modules',
'u:fieldtype' => 'Fieldtype',
'u:fieldtypes' => 'Fieldtypes',
'u:extension' => 'Extension',
'u:extensions' => 'Extensions',
'u:plugin' => 'Plugin',
'u:plugins' => 'Plugins',
'u:accessory' => 'Accessory',
'u:accessories' => 'Accessories',
'u:rte_tool' => 'RTE Tool',
'u:rte_tools' => 'RTE Tools',

// Sort By
'u:sort_by' => 'Sort By',
'u:addon_name' => 'Addon Name',
'u:last_updated'=> 'Last Updated',
'u:install_status'=> 'Install Status',
'u:hide_native'=> 'Hide Native Addons',
'u:hide_notinstalled' => 'Hide Not Installed Addons',
'u:installed' => 'Installed',
'u:not_installed' => 'Not Installed',
'u:actions' =>  'Actions',
'u:install_addon'=> 'Install Addon',
'u:uninstall_addon'=> 'Uninstall Addon',
'u:delete_addon'=> 'Delete Addon',
*/

// Settings
'u:transfer_method' => 'File Transfer Method',
'u:test_settings'  => 'Test Settings',
'u:no_settings' => 'No settings to configure',
'u:update_settings' => 'Update Settings',
'u:act_url' => 'Action URL',
'u:act_change' => 'Only change this if you know what you are doing..',

// Settings Path Map
'u:path_map'      =>  'Path Mapping',
'u:path_map_exp'  =>  'If FTP or SFTP is used these paths can be different.',
'u:dir_root' => 'Site Root Dir',
'u:dir_backup' => 'Backup Dir',
'u:dir_system' => 'System Dir',
'u:dir_system_third_party' => 'Third Party Dir',
'u:dir_themes' => 'Themes Dir',
'u:dir_themes_third_party' => 'Third Party Themes Dir',
'u:browse'  => 'Browse',
'u:browse_error' => 'Error! (access denied?)',

// Settings - Menu Link
'u:menu_link' => 'Menubar Links',
'u:link_root' => 'Show Updater link in the Menu Root',
'u:link_tools' => 'Show Updater link under the Tools section',
'u:link_admin' => 'Show Updater link under the Admin section',

// Settings - Stats
'u:anon_stats' => 'Anonymous Statistics',
'u:anon_stats.exp' => 'Help us make Updater better by sending anonymous usage statistics. No personal data is sent.',
'u:anon_stats.what'=> 'What information is sent?',

// File Transfer Methods
'u:local'     =>  'Local File Transfer',
'u:ftp'       =>  'FTP',
'u:sftp'      =>  'SFTP',
'u:hostname'  =>  'Hostname',
'u:username'  =>  'Username',
'u:password'  =>  'Password',
'u:port'      =>  'Port',
'u:ssl'       =>  'SSL',
'u:passive'   =>  'Passive Mode',
'u:auth_method'=> 'Authentication Method',
'u:public_key'=>  'Public Key',
'u:key_contents'=> 'Key Contents',
'u:key_password'=> 'Key Password',
'u:key_path'=> 'Key Path',
'u:login_check'=> 'Login Check',
'u:login_testing'=> 'Testing login, please wait....',
'u:login_failed'=> 'Failed to login, please make sure the login credentials are correct',
'u:login_success'=> 'Successfully logged in with the supplied credentials',
'u:login_retest'=> 'Test Again',

// Stats
'u:yes:rec' =>  'Yes (recommended)',
'u:anon_stats' => 'Anonymous Statistics',
'u:anon_stats:exp' => 'Help us make Updater better by sending anonymous usage statistics. No personal data is sent.',
'u:anon_stats:what'=> 'What information is sent?',

// Test Transfer
'u:test_transfer_method'  =>  'Test Transfer Method',
'u:loading_wait'  =>  'Loading, please wait...',
'u:passed'    =>  'Passed',
'u:failed'    =>  'Failed',
'u:not_passed'=>  'Not Passed',
'u:connect'   =>  'Connect',
'u:chdir'     =>  'Change Dir',
'u:mkdir'     =>  'Create Dir',
'u:upload'    =>  'Upload',
'u:rename'    =>  'Rename',
'u:delete'    =>  'Delete',

// Errors
'u:error'         =>  'Error',

'error:no_settings' => 'No settings found, please head over to the settings page and review all options and click save.',
'error:test_ajax_failed' => 'Our test AJAX request failed! We sent an AJAX request to <strong><a href="" target="_blank" class="url"></a></strong> but the response was invalid.',

'error:local:not_writeable' => 'LOCAL: The destination dir is not writeable:<br>%s',
'error:local:chdir_fail' => 'LOCAL: Failed to chdir to:<br>%s',
'error:local:mkdir_fail' => 'LOCAL: Failed to create the directory',
'error:local:upload_fail' => 'LOCAL: Failed to upload/move %s:<br><strong>SOURCE:</strong> %s<br><strong>DEST:</strong> %s',
'error:local:rename_fail' => 'LOCAL: Failed to rename:<br><strong>FROM:</strong> %s<br><strong>TO:</strong> %s',
'error:local:delete_fail' => 'LOCAL: Failed to delete %s:<br>%s',

'error:ftp:login' => 'FTP: Failed to login',
'error:ftp:chdir_fail' => 'FTP: Failed to chdir to:<br>%s',
'error:ftp:mkdir_fail' => 'FTP: Failed to create the dir:<br>%s',
'error:ftp:after_mkdir_fail' => 'FTP: Failed to verify mkdir:<br>%s',
'error:ftp:upload_fail' => 'FTP: Failed to upload the %s:<br><strong>SOURCE:</strong> %s<br><strong>DEST:</strong> %s',
'error:ftp:rename_fail' => 'FTP: Failed to rename:<br><strong>FROM:</strong> %s<br><strong>TO:</strong> %s',
'error:ftp:delete_fail' => 'FTP: Failed to delete %s:<br>%s',

'error:sftp:login' => 'SFTP: Failed to login',
'error:sftp:chdir_fail' => 'SFTP: Failed to chdir to:<br>%s',
'error:sftp:mkdir_fail' => 'SFTP: Failed to create the dir:<br>%s',
'error:sftp:after_mkdir_fail' => 'SFTP: Failed to verify mkdir:<br>%s',
'error:sftp:upload_fail' => 'SFTP: Failed to upload the %s:<br><strong>SOURCE:</strong> %s<br><strong>DEST:</strong> %s',
'error:sftp:rename_fail' => 'SFTP: Failed to rename:<br><strong>FROM:</strong> %s<br><strong>TO:</strong> %s',
'error:sftp:delete_fail' => 'SFTP: Failed to delete %s:<br>%s',

'error:temp_dir_write' => 'The temp dir is not writable.<br>Hint: EE Cache Dir',
'error:no_files_up' => 'No files where uploaded! Upload error maybe?',
'error:upload_err' => 'An upload error occured.',
'error:move_upload' => 'Failed to move the uploaded file to the temp dir',
'error:zip_extract_fail' =>  'Failed to process the ZIP',
'error:detect_nothing' => 'No compatible type found (example: EE or Addon etc)',
'error:old_ee' => 'Older version of EE detected!',

// END
''=>''
);

/* End of file updater_lang.php */
/* Location: ./system/expressionengine/third_party/updater/updater_lang.php */
