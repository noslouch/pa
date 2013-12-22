<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(
	'ce_cache_module_name' => 'CE Cache',
	'ce_cache_module_description' => 'Fragment caching via db, files, APC, Redis, Memcache, and/or Memcached + static file caching',
	'ce_cache_module_home' => 'CE Cache Home',
	'ce_cache_channel_cache_breaking' => 'Cache Breaking',
	'ce_cache_debug' => 'Debug',
	'ce_cache_channel_breaking_settings' => '&ldquo;%s&rdquo; Settings',
	'ce_cache_break_settings' => 'Cache Break Settings',
	'ce_cache_driver' => 'Driver',
	'ce_cache_channel' => 'Channel',
	'ce_cache_is_supported' => 'Supported',
	'ce_cache_yes' => 'Yes',
	'ce_cache_no' => 'No',
	'ce_cache_bytes' => 'Bytes',
	'ce_cache_size' => 'Size',
	'ce_cache_driver_file' => 'File',
	'ce_cache_driver_all' => 'All',
	'ce_cache_driver_apc' => 'APC',
	'ce_cache_driver_memcached' => 'Memcached',
	'ce_cache_driver_memcache' => 'Memcache',
	'ce_cache_driver_dummy' => 'Dummy',
	'ce_cache_driver_db' => 'Database',
	'ce_cache_driver_redis' => 'Redis',
	'ce_cache_driver_static' => 'Static',
	'ce_cache_clear_cache_question_site' => 'Clear Driver Site Cache?',
	'ce_cache_clear_cache_question_driver' => 'Clear Entire Driver Cache?',
	'ce_cache_clear_cache_site' => 'Clear Driver Site Cache',
	'ce_cache_clear_cache_driver' => 'Clear Entire Driver Cache',
	'ce_cache_clear_cache_all_drivers' => 'Clear Entire Cache For All Drivers',
	'ce_cache_clear_cache_site_all' => 'Clear Site Cache For All Drivers',
	'ce_cache_clear_cache' => 'Clear Cache',
	'ce_cache_view_items' => 'View Items',
	'ce_cache_view_item' => 'View Item',
	'ce_cache_view' => 'View',
	'ce_cache_id' => 'Cache Item Id',
	'ce_cache_seconds' => 'seconds',
	'ce_cache_seconds_from_now' => 'seconds from now',
	'ce_cache_created' => 'Created',
	'ce_cache_expires' => 'Expires',
	'ce_cache_ttl' => 'Time To Live',
	'ce_cache_content' => 'Content',
	'ce_cache_delete_children' => 'Delete Children',
	'ce_cache_delete_item' => 'Delete Item',
	'ce_cache_delete' => 'Delete',
	'ce_cache_back_to' => 'Back To',
	'ce_cache_viewing_item_meta' => 'You are viewing the &ldquo;%s&rdquo; cache item.',
	'ce_cache_clear_cache_success' => 'The cache has been cleared successfully.',
	'ce_cache_clear_all_cache_success' => 'The caches have been cleared successfully.',
	'ce_cache_clear_site_cache_success' => 'The site caches for all drivers have been cleared successfully.',
	'ce_cache_delete_item_success' => 'The item has been deleted successfully.',
	'ce_cache_delete_children_success' => 'The child items of the path have been deleted successfully.',
	'ce_cache_confirm_clear_site_driver' => 'Are you sure you want to clear the %s driver cache for the current site?',
	'ce_cache_confirm_clear_all_drivers' => 'Are you sure you want to clear the %s driver cache for all sites?',
	'ce_cache_confirm_clear_all_driver' => 'Are you sure you want to clear the entire cache for all drivers of the current site?',
	'ce_cache_confirm_clear_site_drivers' => 'Are you sure you want to clear the entire cache for all drivers of all sites?',
	'ce_cache_confirm_clear_button' => "Yes I'm Sure, Clear the Cache",
	'ce_cache_confirm_clear_all_button' => "Yes I'm Sure, Clear All Driver Caches",
	'ce_cache_confirm_clear_sites_button' => "Yes I'm Sure, Clear The Site Cache For All Drivers",
	'ce_cache_confirm_delete_button' => "Delete the Item",
	'ce_cache_confirm_delete_children_button' => "Yes I'm Sure, Delete the Child Items",
	'ce_cache_error_no_driver' => 'No driver was specified.',
	'ce_cache_no_items' => 'No items were found.',
	'ce_cache_no_more_items' => 'All found items were expired. Please refresh the page.',
	'ce_cache_error_no_path' => 'No path was specified.',
	'ce_cache_error_no_item' => 'No item was specified.',
	'ce_cache_error_invalid_driver' => 'The specified driver is not valid.',
	'ce_cache_error_invalid_path' => 'An item path was not received.',
	'ce_cache_error_getting_items' => 'No cache items were found.',
	'ce_cache_error_getting_meta' => 'No information could be found for the specified item.',
	'ce_cache_error_cleaning_cache' => 'Something went wrong and the cache was *not* cleaned successfully.',
	'ce_cache_error_cleaning_driver_cache' => 'The cache may have *not* been cleaned successfully for the %s driver.',
	'ce_cache_error_deleting_item' => 'Something went wrong and the item "%s" was *not* deleted successfully.',
	'ce_cache_error_no_channel' => 'No channel was specified.',
	'ce_cache_error_channel_not_found' => 'Channel not found.',
	'ce_cache_save_settings' => 'Save Settings',
	'ce_cache_save_settings_success' => 'Your cache break settings have been saved successfully.',
	'ce_cache_any_channel' => 'Any Channel',
	'ce_cache_add' => 'Add',
	'ce_cache_remove' => 'Remove',
	'ce_cache_tags' => 'Tags',
	'ce_cache_tag' => 'Tag',
	'ce_cache_items' => 'Items',
	'ce_cache_variables' => 'Variables',
	'ce_cache_error_module_not_installed' => 'The correct version of the module is not installed, so cache breaking cannot be implemented.',
	'ce_cache_error_invalid_refresh_time' => 'The refresh time must be a number between 0 and 5 inclusively.',
	'ce_cache_error_invalid_item_start' => 'This item must begin with <code>local/</code> or <code>global/</code> or <code>static/</code>.',
	'ce_cache_error_invalid_item_length' => 'This item must be less than or equal to 250 characters in length',
	'ce_cache_error_invalid_tag_character' => 'This tag contains one or more disallowed characters.',
	'ce_cache_error_invalid_tag_length' => 'This tag must be less than or equal to 100 characters in length.',
	'ce_cache_break_intro_html' => '<h3>Cache Breaking</h3>
		<p>This page allows you to remove certain cache items whenever one or more entries from the &ldquo;%s&rdquo; channel are added, updated, or deleted for the current site.</p>
		<p>You can choose to have cache items recreate themselves after they are removed. This will only work for local (non-global) items, as they contain a relative path to a specific page. However, any removed global items that happen to be on a refreshed page will also be recreated.</p>',
	'ce_cache_break_intro_any' => '<h3>Cache Breaking</h3>
			<p>This page allows you to remove certain cache items whenever one or more entries from any channel are added, updated, or deleted for the current site. Individual channel cache break settings will also be applied in addition to these settings.</p>
			<p>You can choose to have cache items recreate themselves after they are removed. This will only work for local (non-global) items, as they contain a relative path to a specific page. However, any removed global items that happen to be on a refreshed page will also be recreated.</p>',
	'ce_cache_refresh_cached_items_question' => 'Refresh cached items after deleting them?',
	'ce_cache_refresh_cached_items_instructions_html' => '<p>Please choose the number of seconds to wait between refreshing cached items. This can be really helpful if you are refreshing a large number of pages, and you don&rsquo;t want to bog down your server all at one time. However, keep in mind that this will take more time; if you have 200 pages with items to be refreshed, and you are delaying 2 seconds between each one, it will take at least 400 seconds (almost 7 minutes) for all of the cache items to be recreated. You will not need to stay on the page while the cache is being recreated.</p>',
	'ce_cache_breaking_tags_instructions_html' => '<p>In your templates, you can assign tags to the It, Save, and Static methods using the tags= parameter. You can specify one or more tags below, and any items that have those tags will be removed or refreshed when an entry in this channel changes.</p>
			<p>Click on the &ldquo;add&rdquo; icon below to add a tag.</p>',
	'ce_cache_breaking_tags_examples_html' => '<p>Examples:</p>
			<ul class="ce_cache_break_item_examples">
				<li>To clear all items with a tag of &ldquo;apple&rdquo;, you would add <code>apple</code></li>
				<li>To clear all items with a tag of the current channel name, you could add <code>{channel_name}</code></li>
			</ul>
			<p>Note: Tags are not case sensitive, so <code>apple</code> is considered the same as <code>Apple</code>. Although discouraged, spaces in your tags are allowed, so <code>bricks in the wall</code> is technically a valid tag. Tags may not contain any pipe (<code>|</code>) characters.</p>',
	'ce_cache_breaking_items_instructions_html' => '<p>You can add items or item parent paths to remove or refresh when an entry in this channel changes. Items should begin with either <code>global/</code>, <code>local/</code>, or <code>static/</code>, depending on their cache type. If you are specifying a parent path (as opposed to an item id), then be sure to give it a trailing slash (<code>/</code>).</p>
				<p>Click on the &ldquo;add&rdquo; icon below to add an item.</p>',
	'ce_cache_breaking_items_examples_html' => '<p>Here are some examples:</p>
			<ul class="ce_cache_break_item_examples">
				<li>To clear all static items for the entire site, you would add: <code>static/</code></li>
				<li>If you had a &ldquo;blog&rdquo; section of your site, and wanted to remove all local cached content under that section, you would add: <code>local/blog/</code></li>
				<li>If you wanted to clear a specific item, like your home page, you could add: <code>local/item</code> (assuming your home page has a cache item with the id &ldquo;item&rdquo;)</li>
				<li>To clear a global item with an id of &ldquo;footer&rdquo;, you could add: <code>global/footer</code></li>
				<li>To clear all local caches where {segment_1} matched the current {channel_name} and {segment_2} matched the {url_title}, use <code>local/{channel_name}/{url_title}/</code></li>
			</ul>',
	'ce_cache_breaking_variables_html' => '<p>The following variables can be used in your tag and item cache breaking settings below: <code>{entry_id}</code>, <code>{url_title}</code>, <code>{channel_id}</code>, <code>{channel_name}</code>, <code>{author_username}</code>, <code>{author_id}</code>, <code>{entry_date format=""}</code>, and <code>{edit_date format=""}</code>. The variables will be replaced with the corresponding values of the currently breaking entry. The two date variables can use <a href="http://expressionengine.com/user_guide/templates/date_variable_formatting.html" target="_blank">date variable formatting</a>.</p>',
	'ce_cache_clear_tagged_items' => 'Clear Tagged Items',
	'ce_cache_clear_tags_instructions' => '<p>The following tags represent cached tag items. Please select which tags you wish to clear.</p>',
	'ce_cache_no_tags' => 'No tags were found for the current site.',
	'ce_cache_confirm_delete_tags_button' => 'Clear The Selected Tags',
	'ce_cache_delete_tags_success' => 'The tags have been cleared successfully.',
	'ce_cache_static_installation' => 'Static Driver Installation',
	'ce_cache_static_instructions' => '
<h3>Overview</h3>
<p>These instructions are for setting up the CE Cache static driver for the current site only. Since these settings are dependent on the site&rsquo;s name for uniqueness, if you change the site&rsquo;s name (by going to Admin -> General Configuration -> "Name of your site" on a standalone installation, or by editing the Site Label for an MSM site), you will need to update these settings.</p>
<br>

<h3>Step 1 - Create The Static Cache Directory</h3>
<p>If you haven&rsquo;t already, create a directory named \'<i>static</i>\' in your site&rsquo;s web root directory. Make sure the directory is writable by Apache and can display the files (permissions of 0775 should do).</p>
<br>

<h3>Step 2 - The Cache Handler</h3>
<p>If you haven&rsquo;t already, upload the \'<i>_static_cache_handler.php</i>\' file to your site&rsquo;s web root directory. Change this line:
<br><code>private $cache_folder = \'\';</code>
<br>to this:
<br><code>private $cache_folder = \'static/{site}\';</code>
</p>
<br>

<h3>Step 3 - Setup .htaccess Rules</h3>
<p>If it doesn&rsquo;t exist already, create a .htaccess file in your web root. Merge the following rules into the .htaccess file:</p>

<pre><code style="display: block;">&lt;IfModule mod_rewrite.c&gt;
	RewriteEngine On

	#------------------- remove trailing slash -------------------
	RewriteCond %{REQUEST_URI} !^/system [NC]
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^(.+)/$ /$1 [R=301,L,QSA]

	#------------------- index.php -------------------
	#strip index.php from the URL if that is all that is given
	RewriteCond %{THE_REQUEST} ^[A-Z]{3,9}\\\ /([^/]+/)*index\\\.php\\\ HTTP/
	RewriteRule ^(([^/]+/)*)index\\\.php$ http://%{HTTP_HOST}/ [R=301,NS,L,QSA]
	#strip index.php/* from the URL
	RewriteCond %{THE_REQUEST} ^[^/]*/index\\\.php/ [NC]
	RewriteRule ^index\\\.php/(.+) http://%{HTTP_HOST}/$1 [R=301,L,QSA]

	#------------------- CE Cache Static Driver -------------------
	RewriteCond %{REQUEST_URI} !^/system [NC]
	RewriteCond %{QUERY_STRING} !ACT|URL [NC]
	RewriteCond %{REQUEST_METHOD} !=POST [NC]
	RewriteCond %{DOCUMENT_ROOT}/static/{site}/static%{REQUEST_URI}/index\\\.html -f
	RewriteRule (.*) /_static_cache_handler.php%{REQUEST_URI}/index\\\.html [L,QSA]

	#------------------- EE -------------------
	#rewrite all non-image/js/css urls back to index.php if they are not files or directories
	RewriteCond $1 !\\\.(css|js|gif|jpe?g|png) [NC]
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ /index.php/$1 [L,QSA]
&lt;/IfModule&gt;
</code></pre>

<br>

<h3>Step 4 - Configuration Setting</h3>
<p>Add <code>$config[\'ce_cache_static_enabled\'] = \'yes\';</code> to your <i>config.php</i> file (located in the <i>/system/expressionengine/config</i> directory by default).</p>

<br>

<h3>Step 5 - Test</h3>
<p>If the Static Driver cache directory is found, the driver should appear in the drivers table on the <a href="{ce_cache_home_link}" target="_blank">CE Cache Module home page</a>. If it is not showing up, you can override the path in config.php with the following setting:<br>
	<code>$config[\'ce_cache_static_path\'] = \'/server/path/to/web_root/static\';</code></p>

<p>Now add <code>&#123;exp:ce_cache:stat:ic&#125;</code> to one of your templates, and visit a URL that uses that template. The page should now be cached, and when you refresh the page again, it should be faster than ever.</p>

<br>

<h3>Troubleshooting</h3>
<h4>Debug Mode</h4>
 <p>If something is not working (like a redirect loop, or the page redirects to the homepage), or you want to see how fast the page is being rendered by the driver, you can enable debug mode by opening \'<i>/_static_cache_handler.php</i>\' and changing <code>private $debug = false;</code> to <code>private $debug = true;</code>. Debug messages will now be shown in an HTML comment at the bottom of the rendered pages (view source to see). Don&rsquo;t forget to set this back to <code>false</code> when you are done debugging.</p>

<h4>Query String Mode</h4>
<p>If your site links return 404 pages, a "No Input File Specified" error, or all links return the same content, you may need to modify your .htaccess file. You&rsquo;ll want to replace this line:
<code>	RewriteRule ^(.*)$ /index.php/$1 [L,QSA]</code> with this: <code>	RewriteRule ^(.*)$ /index.php?/$1 [L,QSA]</code>
and replace this line: <code>	RewriteRule (.*) /_static_cache_handler.php%{REQUEST_URI}/index\\\.html [L,QSA]</code> with this: <code>	RewriteRule (.*) /_static_cache_handler.php?%{REQUEST_URI}/index\\\.html [L,QSA]</code> (notice the added question marks).</p>

<h4>.htaccess Variable Problems</h4>
<p>Several people have reported that they have problems with htaccess variables on Rackspace hosting (this could potentially be an issue with other hosts as well). In particular, the <code>%{DOCUMENT_ROOT}</code> variable is not set to the correct value and will need to be replace with the actual server path to the document root (ex: <code>/path/to/web/root</code>). Additionally, you may need to hard code the <code>%{HTTP_HOST}</code> variable with your site&rsquo;s domain (ex: <code>example.com</code>).</p>

<h4>Stylesheets in EE</h4>
<p>If you are using your templates as stylesheets (which should be avoided), you&rsquo;ll need to add this line <code>RewriteCond %{QUERY_STRING} !css [NC]</code> after the <code>RewriteCond %{QUERY_STRING} !ACT [NC]</code> line.</p>

<h4>Logged Out Only</h4>
<p>If you only want to enable static caching when visitors are logged out, you can add <code>$config[\\\'ce_cache_static_logged_out_only\\\'] = \\\'yes\\\';</code> to your config.php, and then add this conditional to your .htaccess: <code>RewriteCond %{HTTP_COOKIE} !exp_sessionid= [NC]</code> (add that line directly after the line <code>RewriteCond %{QUERY_STRING} !ACT [NC]</code>). If you are using a different cookie prefix (other than \\\'exp\\\'), be sure to update your rewrite conditional accordingly. This is a new feature, and may or may not work on your host&hellip;</p>
<br>

<h3>Static Flat File Caching (Optional, Not Recommended)</h3>
<p>The static driver normally utilizes the _static_cache_handler.php script to give the static driver some extra functionality (like output headers and expire the cache when needed). However, if you would rather not have any PHP overhead (though it&rsquo;s quite negligible already), you can bypass the cache handler script completely. By doing this, all files cached with the static driver will not expire on their own; it effectively sets <code>seconds="0"</code> for every cache item. Cache breaking, tagging, and everything else should still work as expected though.</p>

<p>To use static flat file caching, you&rsquo;ll need to add this to config.php: <code>$config[\\\'ce_cache_static_flat\\\'] = \\\'yes\\\';</code></p>

<p>Next, <b>clear your static driver cache in the control panel</b>. This is important, as the cache files will now be flat HTML (as opposed to containing serialized data).</p>

<p>Finally, you&rsquo;ll want to replace this line: <code>	RewriteRule (.*) /_static_cache_handler.php%{REQUEST_URI}/index\\\.html [L,QSA]</code> with this: <code>	RewriteRule (.*) /static/{site}/static%{REQUEST_URI}/index\\\.html [L,QSA]</code> to ensure that .htaccess redirects directly to the cache files, and not to the cache handler script.</p>',

	//misc ajax errors
	'ce_cache_ajax_unknown_error' => 'An unknown error occurred.',
	'ce_cache_ajax_no_items_found' => 'No items were found.',
	'ce_cache_ajax_error' => 'An unexpected response was received:',
	'ce_cache_ajax_error_title' => 'Unexpected Response',
	'ce_cache_ajax_install_error' => 'An error has occurred! Please ensure the CE Cache module is installed correctly.',

	//delete child items
	'ce_cache_ajax_delete_child_items_confirmation' => 'Are you sure you want to delete all of the \\\"%s\\\" child items?',
	'ce_cache_ajax_delete_child_items_button' => 'Delete Child Items',
	'ce_cache_ajax_delete_child_items_refresh' => 'Refresh items after deleteing them?',
	'ce_cache_ajax_delete_child_items_refresh_time' => 'How many seconds would you like to wait between refreshing items?',
	//delete item
	'ce_cache_ajax_delete_child_item_confirmation' => 'Are you sure you want to delete the \\\"%s\\\" item?',
	'ce_cache_ajax_delete_child_item_refresh' => 'Refresh the item after it is deleted?',
	'ce_cache_ajax_delete_child_item_button' => 'Delete Item',
	//cancel button
	'ce_cache_ajax_cancel' => 'Cancel',
	//turned off
	'ce_cache_off' => '<p><b>Note</b>: All caching via CE Cache is currently turned off.</p>',
	//debug
	'ce_cache_debug_url' => '<p>Attempting to synchronously call <a href="%s">%s</a>.</p>',
	'ce_cache_debug_curl' => '<p>Synchronous cache breaking appears to be working with cURL.</p>',
	'ce_cache_debug_fsockopen' => '<p>Synchronous cache breaking appears to be working with fsockopen.</p>',
	'ce_cache_debug_not_working' => '<p>Synchronous cache breaking is not working.</p>',
	'ce_cache_debug_working' => '<p>Success!</p>'
);

/* End of file lang.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/language/english/lang.ce_cache.php */
