<?php


require_once PATH_THIRD.'playa/config.php';


$lang = array(


// -------------------------------------------
//  Module CP
// -------------------------------------------

'playa_module_name' => PLAYA_NAME,
'playa_module_description' => PLAYA_DESC,

// -------------------------------------------
//  Global Settings
// -------------------------------------------

'license_key' => 'License Key',

'filter_min' => 'Filter Minimum',
'filter_min_desc' => 'The minimum number of available entries required for Playa to show the Drop Panes filters',

'convert_rel_fields' => 'Convert Relationship fields?',
'convert_rel_fields_info' => 'Would you like Playa to convert any of these Relationship fields into Playa fields?',
'convert' => 'Convert?',

'convert_related_entries' => 'Convert Related Entries?',
'convert_related_entries_info' => 'It looks like you used to have Solspace Related Entries installed. Would you like Playa to convert the relationships you created with that into Playa fields?',

// -------------------------------------------
//  Field Settings
// -------------------------------------------

'allow_multiple_selections' => 'Allow multiple&nbsp;selections?',
'multi_info' => 'This preference determines whether your Playa field will show up as a simple dropdown or a full “drop panes” UI.',

'show_expired_entries' => 'Show expired entries?',
'show_future_entries' => 'Show future entries?',
'only_show_editable_entries' => 'Only show editable entries?',
'only_show_editable_entries_info' => 'Only show entries that the current user is allowed to edit.',

'channels' => 'Channels',
'cats' => 'Categories',
'authors' => 'Authors',
'statuses' => 'Statuses',

'no_channels' => 'No channels exist',
'no_cats' => 'No categories exist',
'current' => 'Current',

'limit_entries_to' => 'Limit entries to',
'all' => 'All',
'newest_entries' => 'Newest Entries',
'oldest_entries' => 'Oldest Entries',

'order_entries_by' => 'Order entries by',
'entry_title' => 'Entry Title',
'entry_date' => 'Entry Date',
'asc_order' => 'Ascending Order',
'desc_order' => 'Descending Order',


// -------------------------------------------
//  Field
// -------------------------------------------

'remove_filter' => 'Remove filter',
'add_filter' => 'Add filter',
'keywords_label' => 'Filter by keywords',
'erase_keywords' => 'Erase keywords',
'is' => 'is',
'showing' => 'Showing',
'of' => 'of',
'entries' => 'entries',
'select_an_entry' => 'Select an entry…',

'deprecated_tag' => 'A template loaded on <b><a href="{url}">{url}</a></b> is using the deprecated <b>{tag}</b> tag. Please use Playa’s <a href="http://pixelandtonic.com/playa/docs/templates">var_prefix</a> parameter instead.',

''=>''
);
