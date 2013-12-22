<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
*	===============================
*	ZENBU LANGUAGE FILE
*	===============================
*	The Zenbu addon uses strings from the core ExpressionEngine language files,
*	in addition to the languages strings below
*
*/

$lang = array(

'zenbu_module_name'			=> 'Zenbu',

'zenbu_module_description'	=> 'See more data in your control panel entry listing',

/**
 *	General
 *	-------------
 */
'settings' 	=> 'Settings',

'entries' 	=> 'Entries',

'loading' 	=> 'Loading...',

/**
 *	=========
 *	INDEX
 *	=========
 */ 

'any_custom_fields_titles'		=> 'Any Title or Basic Custom Field',

'by_channel'					=> 'All Channels',

'by_category'					=> 'All Categories',

'by_author'						=> 'All Authors',

'by_status'						=> 'All Statuses',

'all_statuses'					=> 'All Statuses',

'is_sticky'						=> 'Sticky',

'not_sticky'					=> 'Not Sticky',

'sticky_both'					=> 'Sticky and Not Sticky',

'by_entry_date'					=> '...',

'by_limit'						=> 'Number of Entries',

'by_categories'					=> 'All Categories',

'entries_with_no_categories'	=> 'No Category',

'by_search_in'					=> 'Search in...',

'titles_and_fields'				=> 'Title and Basic Field Content',

'titles_only'					=> 'Title',

'entry_title'					=> 'Entry Title',

'entry_id'						=> 'Entry ID',

'id'							=> '#',

'focused_field_search'			=> 'Focused Field Search',

'keyword'						=> "Keyword  ",

'custom_fields'					=> 'Custom Fields',

'autosave'						=> 'Autosave',

'orderby'						=> "Order By",

'asc'							=> "Ascending Order",

'desc'							=> "Descending Order",

'in'							=> 'contains',

'not_in'						=> 'does not contain',

'is'							=> 'is',

'isnot'							=> 'is not',

'contains'						=> 'contains',

'doesnotcontain'				=> 'does not contain',

'beginswith'					=> 'begins with',

'doesnotbeginwith'				=> 'does not begin with',

'endswith'						=> 'ends with',

'doesnotendwith'				=> 'does not end with',

'containsexactly'				=> 'contains exactly',

'isempty'						=> 'is empty',

'isnotempty'					=> 'is not empty',


/**
 *	Date expressions
 *	----------------
 */

'in_past_day'			=> 'in the last 24 hours',

'in_past_week'			=> 'in the last 7 days',

'in_past_month'			=> 'in the last 30 days',

'in_past_six_months'	=> 'in the last 180 days',

'in_past_year'			=> 'in the last 365 days',

'next_day'				=> 'within the next 24 hours',

'next_week'				=> 'within the next 7 days',

'next_month'			=> 'within the next 30 days',

'next_six_months'		=> 'within the next 180 days',

'next_year'				=> 'within the next 365 days',

'between_these_dates'	=> 'within this date range:',


/**
 *	Results
 *	-------
 */

'search'					=> 'Search',

'searching'					=> 'Searching...',

'showing'					=> 'Showing ',

'to'						=> 'to',

'out_of'					=> 'out of',

'no_results'				=> 'No results found.',

'show_images'				=> 'Show Images',

'add_this_search_as_tab'	=> 'Add this search as a main menu item',

'add'						=> 'Add',

'remove'					=> 'Remove',

'add_filter_rule'			=> 'Add Entry Filtering Rule',

'remove_filter_rule'		=> 'Remove Entry Filtering Rule',

'last_author'				=> 'Last Edited By:',

'saved_searches_list'		=> 'Saved Searches',

'save_this_search'			=> 'Save this Search',

'delete_this_search'		=> 'Delete this Search',

'give_rule_label'			=> 'Label for Search Filter:',

'saved_search'				=> 'Saved Search',

'rapid_loading_error'		=> 'An error was detected, likely due to too rapid refreshing of the search form. Because not all search filters had time to load completely, default search conditionals were loaded instead.',

/**
 * 	Error - Warnings
 * 	----------------
 */

'saved_search_delete_warning'	=> 'Are you sure you want to delete this search?',

'unauthorized_access_channel'	=> 'You are not authorized to access this page: restricted channel access',

/**
 * 	===============
 * 	SETTINGS
 * 	===============
 */

'display_settings'					=> 'Display Settings',

'general_settings'					=> 'General Settings',

'max_results_per_page'				=> 'Custom Entry Limit per Page',

'max_results_per_page_note'			=> 'Added to the "Show X results" dropdown. <strong style="color: red">Warning: </strong>High values may affect query performance, leading to more time to display entry results or even timeouts',

'default_filter'					=> 'Default Rule Filter',

'default_filter_note'				=> 'Default first entry filtering rule on new pages (i.e. without pre-established filters)',

'default_limit'						=> 'Default Limit',

'default_limit_note'				=> 'Default limit value on new pages (i.e. without pre-established filters)',

'default_order_sort'				=> 'Default Ordering and Sorting',

'default_order_sort_note'			=> 'Default entry ordering and sorting on new pages (i.e. without pre-established filters)',

'enable_hidden_field_search'		=> 'Make All Custom Fields Available for Searching',

'enable_hidden_field_search_note'	=> 'When enabled, all custom fields can be used in searches, even if they are not set to be displayed in the result table',

'option'							=> 'Option',

'field'								=> 'Field',

'all_channels'						=> 'All Channels',

'multi_channel_entries'				=> 'Multi-channel Entry Listings',

'or_skip_to'						=> 'or skip to',

'extra_options'						=> 'Extra Options',

'save_settings'						=> 'Save Settings',

'message_settings_saved'			=> 'Settings saved successfully',

'error_not_numeric'					=> 'Error: Some entered values were not integers/numbers',

'field_order'						=> 'Field Order',

'date_format'						=> 'Date Format',

'date_format_future'				=> 'After Current Time',

'y'									=> 'Yes',

'n'									=> 'No',

'show_'								=> 'Show ',

'_in_row'							=> ' in row',

'warning_channel_fields_no_display'	=> 'No fields have been selected for the following channels:'."\n\n",

'warning_save_confirm'				=> "\n\n".'Entry results may be displayed as one column containing checkboxes only. Do you still want to save your settings?',

'warning_forgot_to_save'			=> 'You have made changes on this settings page which haven\'t been saved. If you continue you will lose this unsaved data. ' . "\n\n" . 'Do you still want to continue and discard changes?',

'check_all' 						=> 'Check all ',

/**
*	Setting options
*	---------------
*/

'edit_date'					=> 'Edit Date',

'view_count'				=> 'View Count',

'show_view_count'			=> 'Show View Count',

'show_last_author'			=> 'Last Author having Edited the Entry',

'show_autosave'				=> 'Autosaved Entries',

'word_limit'				=> 'Word Limit',

'show_channel_images_cover'	=> 'Show only image cover (or first image)',

'use_livelook_settings'		=> 'Use channel\'s Live Look settings',

'use_custom_segments'		=> 'Use custom segments (blank = No Live Look)',

'custom_segments'			=> 'Segments:',

'livelook_pages_override'	=> 'Override with Pages URL when available',

'livelook_not_set'			=> '(no template selected) ',

'show_html'					=> 'Display HTML markup in text',

'no_html'					=> 'Show text as plain text',

'convert_to_regular_number'	=> 'Convert Exponential Numbers to Decimal',

'number_of_decimals'		=> 'Number of Decimals: ',

'use_thumbnail'				=> 'Use thumbnail: ',

'standard_thumbs'			=> 'Default EE thumbnail size',

'no_categories_to_display' 	=> 'Number of Categories to Display: ',

/**
*	====================
*	SETTINGS FOR ADMIN
*	====================
*/
'member_access_settings'		=> 'Permissions',

'save_this_profile_for_link'	=> 'Copy this profile to member groups &raquo;',

'save_this_profile_for'			=> '... also copy this profile to the following member groups:',

'clear_individual_settings'		=> 'Clear individual settings for each member in the above *checked* member groups',

'member_group_name'				=> 'Member Group Name',

'can_admin'						=> 'Can access Permissions',

'can_admin_subtext'				=> 'Allows access to the <strong>Permissions</strong> section (this page)',

'can_copy_profile'				=> 'Can copy display settings to other member groups',

'can_copy_profile_subtext'		=> 'Allows copying of display settings to other member groups.',

'can_access_settings'			=> 'Can access Display Settings',

'can_access_settings_subtext'	=> 'Allows access to the <strong>Display Settings</strong> section',

'edit_replace'					=> 'Modify Edit link in Content &raquo; Edit',

'replace_links_for_zenbu'		=> 'Modify native EE links to Zenbu',

'edit_replace_subtext'			=> 'Modifies CP links to the native EE entry list to the Zenbu entry list',

'can_view_group_searches'		=> 'Can view own member group searches',

'can_view_group_searches_subtext' => 'In addition to individual saved searches, saved searches for the currently logged in member\'s group will be displayed. <strong>However, users can still only manage their own saved searches</strong>.',

'can_admin_group_searches'		=> 'Can administrate all group searches',

'can_admin_group_searches_subtext' => 'Allows editing and copying of a search to <strong>any</strong> other member group',

'enable_module_for'				=> 'Enable module for the following member groups:',

'enable_module_for_subtext'		=> '<em>NOTE:</em> Enabling this for the following member groups will also enable display and access to the <strong>ADD-ONS</strong> and <strong>ADD-ONS => Modules</strong> sections in the Control Panel\'s top navigation. This is a requirement of ExpressionEngine to be able to completely access Zenbu. To disable display/access to these sections afterwards, please visit the individual member group\'s preferences.',

/**
*	==================
* 	SEARCH MANAGER
*	==================
*/
'manage_saved_searches'		=> 'Saved Searches',

'your_searches'				=> 'Your Searches',

'group_searches'			=> 'Group Searches',

'assign'					=> 'Assign',

'edit_name'					=> 'Edit Search Label',

'no_searches_individual'	=> 'There are no personal saved searches.',

'copy_this_search_to'		=> 'Copy this search to the following member groups:',

'copy'						=> 'Copy',

/**
*	==================
* 	MULTI-ENTRY EDIT
*	==================
*/
'deleting'					=> 'Deleting...',

'saving'					=> 'Saving...',

'multi_set_all_status_to'	=> 'When applicable, set to',

'cancel_and_return'			=> 'Cancel and return to previous page',


/**
*	=====================
*	EXTENSION SETTINGS
*	=====================
*/
'license'	=> 'License',

/**
 * ============================
 * THIRD-PARTY LANGUAGE STRINGS
 * ============================
 */
'show_calendar_only'	=> 'Show associated calendar name only',

//
''=>''
);