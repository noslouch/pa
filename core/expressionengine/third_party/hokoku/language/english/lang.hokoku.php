<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(	

'hokoku_module_name'		=> 'Hokoku',

'hokoku_module_description'	=> 'Zenbu Add-On - Export entry data from Zenbu',

'module_home'				=> 'Hokoku Home',


/** Zenbu interface **/

'export_this_search'	=> 'Export this search',


/** 
*	Hokoku export options 
*/
'export'							=> 'Export',

'exporting'							=> 'Exporting...',

'cancel'							=> 'Cancel',

'cancelling'						=> 'Cancelling...',

'percent_complete'					=> '%',

'file_format'						=> 'File Format',

'filename'							=> 'Filename',

'upload_pref'						=> 'Upload directory',

'plain_text'						=> 'Plain text',

'download_to_browser'				=> 'Download file after export is completed',

'return_to_zenbu'					=> 'Return to previous page',

'return_to_zenbu_entry_list'		=> 'Return to entry listing',

'return_to_zenbu_main_page'			=> 'Return to channel entries',

'download_file'						=> 'Download exported file',

'go_to_file_manager'				=> 'View export directory contents',

'saved_searches'					=> 'Saved searches',

'show_data_sample'					=> 'Show data sample',

'hide_data_sample'					=> 'Hide data sample',

'profile_export_message'			=> 'Please click the "Export" button below to export the data in the following format: ',

'export_message'					=> 'Your file has been exported to your selected directory. You may also download the exported file by clicking "Download the file" below.',

'file_already_exists'				=> 'A file with the same name, format and upload directory already exists. If you continue, the previous version of the file will be deleted and replaced with this one. Do you want to continue?',

'progress_complete'					=> 'complete',

'not_logged_in_member_id_required'	=> 'A member ID is required while logged out',

'no_data'							=> 'No data to export.',

/**
 * Profiles
 */
'export_profiles'			=> 'Export profiles',

'export_profiles_select'	=> 'Please select an export profile:',

'no_profiles'				=> 'There are currently no saved profiles.',

'save_preset_short'			=> 'Save profile',

'save_preset'				=> 'Save this export profile',

'save_settings'				=> 'Save Settings',

'saving'					=> 'Saving ...',

'profile_name'				=> 'Profile Name',

'preset_saved'				=> 'Preset export profile saved',

'custom_profile'			=> 'Export using custom profile',

'copy_to_member_groups'		=> 'Save this profile to member groups',

'copy_profile_to'			=> 'Save this profile to the following groups: ',

'options'					=> 'Options',

/**
 * - Manage profiles
 */

'manage_profiles'				=> 'Manage Export Profiles',

'create_new_profile'			=> 'New Export Profile',

'return_to_manage_profiles'		=> 'Return to export profile manager',

'profile_delete_warning'		=> 'Are you sure you want to delete the following profile?',

'your_profiles'					=> 'Your profiles',

'group_profile'					=> 'Group profile',

'group_profiles'				=> 'Group profiles',

'cannot_access_profile_manager'	=> 'You are not allowed to access the profile manager',

/**
 * - Edit profiles
 */
'edit_profiles'					=> 'Edit Export Profile',

'cannot_access_edit_profiles'	=> 'You are not allowed to access this section',

/**
 * - Permissions
 */
'member_access_settings'		=> 'Permissions',

'member_group_name'				=> 'Member group',

'can_admin_own_profiles'		=> 'Can administrate own profiles',

'can_admin_own_profiles_subtext' => 'Allows the currently logged in member to <strong>create and edit their own export profiles</strong>',

'can_view_group_profiles'		=> 'Can view and use group profiles',

'can_view_group_profiles_subtext'		=> 'In addition to individual export profiles, profiles for the currently logged in member\'s group will be displayed. <strong>This does not grant permission to manage member group export profiles</strong>.',

'can_admin_group_profiles'		=> 'Can administrate group profiles',

'can_admin_group_profiles_subtext'		=> 'Allows editing and copying of an export profile to <strong>any</strong> other member group',

'can_access_access_settings'	=> 'Can access Permissions (this page)',

'can_access_access_settings_subtext'	=> 'Allows access to the <strong>Permissions</strong> section (this page)',

'enable_module_for'				=> 'Enable module for the following member groups:',

'enable_module_for_subtext'		=> '<em>NOTE:</em> Enabling this for the following member groups will also enable display and access to the <strong>ADD-ONS</strong> and <strong>ADD-ONS => Modules</strong> sections in the Control Panel\'s top navigation. This is a requirement of ExpressionEngine to be able to completely access Hokoku. To disable display/access to these sections afterwards, please visit the individual member group\'s preferences.',

/**
 * - Tag builder
 */
'export_tag_template_instructions'	=> '<p><strong>Instructions</strong>: Select an example EE tag from the following saved searches, and place this tag in a template.</p>
<p>Add <strong>member_id			="YOUR_OR_ANOTHER_USER\'S_MEMBER_ID"</strong> if exporting data while logged out. Add <strong>output_to_screen="y"</strong> (or yes, enable, on, etc) to display the export data on screen instead of a file to download.</p>',

'provide_profile_id'				=> 'You must provide a profile_id to view this page',

'template_code_example'				=> 'Template code example (click to select)',

'template_tags'						=> 'Template Tag Helper',

'get_template_tag'					=> 'View',

'no_saved_searches'					=> '<h3>There are no saved searches to use with the template tag.</h3>Saved searches are necessary to determine which data to pull for the export.',


/**
*	- CSV
*/
'delimiter'		=> 'Cell delimiter',

'enclosure'		=> 'Cell enclosure',

'excelcompat'	=> 'Attempt Excel compatibility',

'' => '',
	
);

/* End of file lang.hokoku.php */
/* Location: /system/expressionengine/third_party/hokoku/language/english/lang.hokoku.php */
