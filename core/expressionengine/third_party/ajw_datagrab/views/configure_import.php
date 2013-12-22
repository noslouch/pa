<?php 
	echo form_open( $form_action ); 
	echo form_hidden( "datagrab_step", "configure_import" ); 
?>

<?php 

$this->table->set_template($cp_table_template);
$this->table->set_heading("Default Fields", "Value");

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Choose which values to use for the standard channel fields',
		'class' => 'box'
	)
);

/* Standard fields */

$this->table->add_row(
	form_label('Title', 'title') .
	'<div class="subtext">The entry\'s title.</div>', 
	form_dropdown("title", $data_fields, 
		isset($default_settings["config"]["title"]) ? $default_settings["config"]["title"] : '' )
);
$this->table->add_row(
	form_label('URL', 'url_title') .
	'<div class="subtext">The entry\'s URL title. If this is not set then the URL title will be derived from the entry\'s title.</div>', 
	form_dropdown("url_title", $data_fields, 
		isset($default_settings["config"]["url_title"]) ? $default_settings["config"]["url_title"] : '' )
);
$this->table->add_row(
	form_label('Date', 'date') .
	'<div class="subtext">Leave blank to set the entry\'s date to the time of import</div>', 
	form_dropdown("date", $data_fields, 
		isset($default_settings["config"]["date"]) ? $default_settings["config"]["date"] : '')
);
$this->table->add_row(
	form_label('Expiry date', 'expiry_date') .
	'<div class="subtext">Leave blank if you do not want to set an expiry date</div>', 
	form_dropdown("expiry_date", $data_fields, 
		isset($default_settings["config"]["expiry_date"]) ? $default_settings["config"]["expiry_date"] : '')
);

echo $this->table->generate();
echo $this->table->clear();

/* Custom fields */

$this->table->set_template($cp_table_template);
$this->table->set_heading("Custom Fields", "Value");

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Assign values to use for the channel\'s custom fields. You can leave values blank.',
		'class' => 'box'
	)
);

foreach( $cf_config as $cf ) {
	$this->table->add_row( $cf["label"], $cf["value"] );
}

echo $this->table->generate();
echo $this->table->clear();

if( $pages_installed ) {

$this->table->set_template($cp_table_template);
$this->table->set_heading("Pages", "Value");

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Add entries as pages to the Pages module',
		'class' => 'box'
	)
);
$field_name = "ajw_pages";
$this->table->add_row(
	form_label( "Add as page?" ) .
	'<div class="subtext">Check this box if you want to add the imported entries as Pages</div>', 
	form_checkbox( $field_name, "y", 
		isset($default_settings["cf"][$field_name]) && $default_settings["cf"][$field_name] == "y" ? TRUE : FALSE)
);
$field_name = "ajw_pages_url";
$this->table->add_row(
	form_label( "Page URL" ) .
	'<div class="subtext">Leave blank to generate the page url title automatically</div>', 
	form_dropdown( $field_name, $data_fields, 
		isset($default_settings["cf"][$field_name]) ? $default_settings["cf"][$field_name] : '' )
);
$field_name = "ajw_pages_template";
$this->table->add_row(
	form_label( "Page Template" ) .
	'<div class="subtext">Leave blank to use the channel\'s default Page template</div>', 
	form_dropdown( $field_name, $data_fields, 
		isset($default_settings["cf"][$field_name]) ? $default_settings["cf"][$field_name] : '' )
);

echo $this->table->generate();
echo $this->table->clear();

}

if( $seo_lite_installed ) {

$this->table->set_template($cp_table_template);
$this->table->set_heading("SEO Lite", "Value");

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Add tags to the SEO Lite module',
		'class' => 'box'
	)
);
$field_name = "ajw_seo_lite_title";
$this->table->add_row(
	form_label( "SEO Lite Title" ), 
	form_dropdown( $field_name, $data_fields, 
		isset($default_settings["cf"][$field_name]) ? $default_settings["cf"][$field_name] : '' )
);
$field_name = "ajw_seo_lite_keywords";
$this->table->add_row(
	form_label( "SEO Lite Keywords" ), 
	form_dropdown( $field_name, $data_fields, 
		isset($default_settings["cf"][$field_name]) ? $default_settings["cf"][$field_name] : '' )
);
$field_name = "ajw_seo_lite_description";
$this->table->add_row(
	form_label( "SEO Lite Description" ), 
	form_dropdown( $field_name, $data_fields, 
		isset($default_settings["cf"][$field_name]) ? $default_settings["cf"][$field_name] : '' )
);

echo $this->table->generate();
echo $this->table->clear();

}

/*
if( $tags_installed ) {

$this->table->set_template($cp_table_template);
$this->table->set_heading("Tags", "Value");

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Add tags to the Solspace Tag module',
		'class' => 'box'
	)
);
$field_name = "ajw_solspace_tag";
$this->table->add_row(
	form_label( "Tags" ), 
	form_dropdown( $field_name, $data_fields, 
		isset($default_settings["cf"][$field_name]) ? $default_settings["cf"][$field_name] : '' )
);


echo $this->table->generate();
echo $this->table->clear();

}
*/

/* Categories */

$this->table->set_template($cp_table_template);
$this->table->set_heading("Categories", "Value");
$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Add categories to the entry',
		'class' => 'box'
	)
);

/*

$this->table->add_row(
	form_label("Default category value") .
	'<div class="subtext">Assign this category to every entry</div>', 
	form_input("category_value",  
		isset($default_settings["config"]["category_value"]) ? $default_settings["config"]["category_value"] : '' )
);

$this->table->add_row(
	form_label("Category field") .
	'<div class="subtext">Assign categories from this value to the entry</div>', 
	form_dropdown("cat_field", $data_fields, 
		isset($default_settings["config"]["cat_field"]) ? $default_settings["config"]["cat_field"] : '')
);

$this->table->add_row(
	form_label("Add new categories to this group")  .
	'<div class="subtext">Choose which group new categories should be added to</div>', 
	form_dropdown("cat_group", $category_groups, 
		isset($default_settings["config"]["cat_group"]) ? $default_settings["config"]["cat_group"] : '' )
);

$this->table->add_row(
	form_label("Category delimiter") .
	'<div class="subtext">eg, "One, Two, Three" will create 3 categories if the delimiter is a comma</div>', 
	form_input("cat_delimiter",  
		isset($default_settings["config"]["cat_delimiter"]) ? $default_settings["config"]["cat_delimiter"] : ',' )
);
*/

if( count( $category_groups ) == 0 ) {
	$this->table->add_row(
		array(
			'colspan' => 2,
			'data' => 'No category groups are assigned to this channel.'
		)
	);
}

$c_groups = array();
foreach( $category_groups as $group_id => $label ) {
	$c_groups[] = $group_id;
	$this->table->add_row(
		array(
			'colspan' => 2,
			'data' => 'Add categories to the category group: <strong>' . $label . '</strong>'
		)
	);

	$this->table->add_row(
		form_label("Default category value") .
		'<div class="subtext">Assign this category to every entry</div>', 
		form_input("cat_default_".$group_id,  
			isset($default_settings["config"]["cat_default_".$group_id]) ? $default_settings["config"]["cat_default_".$group_id] : '' )
	);	

	$this->table->add_row(
		form_label("Category group: " . $label ) .
		'<div class="subtext">Assign categories from this value to the entry</div>', 
		form_dropdown("cat_field_".$group_id, $data_fields, 
			isset($default_settings["config"]["cat_field_".$group_id]) ? $default_settings["config"]["cat_field_".$group_id] : '')
	);
	
	$this->table->add_row(
		form_label("Category delimiter") .
		'<div class="subtext">eg, "One, Two, Three" will create 3 categories if the delimiter is a comma</div>', 
		form_input("cat_delimiter_".$group_id,  
			isset($default_settings["config"]["cat_delimiter_".$group_id]) ? $default_settings["config"]["cat_delimiter_".$group_id] : ',',
			' style="width: 50px"'
		)
	);
	
}

echo $this->table->generate();
echo $this->table->clear();

echo form_hidden( "c_groups", implode("|", $c_groups) );

/* Duplicate entries/updates */

$this->table->set_template($cp_table_template);
$this->table->set_heading("Check for duplicate entries", "Value");

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Determine what happens if the import is run again',
		'class' => 'box'
	)
);
$this->table->add_row(
	form_label("Entry id") .
	'<div class="subtext">Specify the entry\'s id.</div>', 
	form_dropdown("ajw_entry_id", $data_fields, 
		isset($default_settings["config"]["ajw_entry_id"]) ? $default_settings["config"]["ajw_entry_id"] : '' )
);

// Unique fields
if( isset($default_settings["config"]) && 
		isset($default_settings["config"]["unique"]) &&
		is_array( $default_settings["config"]["unique"] ) && 
		( count( $default_settings["config"]["unique"] ) > 0 ) ) {

	// Import has multiple/array of unique fields
		
	$unique_form = "";
	foreach( $default_settings["config"]["unique"] as $unique_value ) {
		if( $unique_value != "" ) {
			$unique_form .= form_dropdown("unique[]", 
				$unique_fields, $unique_value )	. BR . BR ;
		}
	}
	
	// Make sure there is always atleast one
	if( $unique_form == "" ) {
		$unique_form .= form_dropdown("unique[]", 
			$unique_fields, "") ;
	}
		
	$this->table->add_row(
		form_label("Use this field to check for duplicates") .
		'<div class="subtext">If an entry with this field\'s value already exists, do not create a new entry.</div>', 
		$unique_form
	);
	// todo:  You can use a single field or a combination of 2 fields.
} else {

	// Handle legacy imports with single unique field

	$this->table->add_row(
		form_label("Use this field to check for duplicates") .
		'<div class="subtext">If an entry with this field\'s value already exists, do not create a new entry</div>', 
		form_dropdown("unique[]", $unique_fields, 
			isset($default_settings["config"]["unique"]) ? $default_settings["config"]["unique"] : '' )
		/* form_dropdown("unique[]", $unique_fields, 
			isset($default_settings["config"]["unique"]) ? $default_settings["config"]["unique"] : '' )	. BR . BR . 
		form_dropdown("unique[]", $unique_fields, '' ) */
	);
}
$this->table->add_row(
	form_label("Update existing entries") .
	'<div class="subtext">If the unique field matches, then update the original entry, otherwise ignore it</div>', 
	form_hidden("update", "n") . 
	form_checkbox("update", "y", (isset($default_settings["config"]["update"]) && $default_settings["config"]["update"] == "y" ? TRUE : FALSE) )
);
$this->table->add_row(
	form_label("Delete old entries") .
	'<div class="subtext">Delete entries from this channel that are not updated by this import</div>', 
	form_hidden("delete_old", "n") . 
	form_checkbox("delete_old", "y", (isset($default_settings["config"]["delete_old"]) && $default_settings["config"]["delete_old"] == "y" ? TRUE : FALSE) )
);
$this->table->add_row(
	form_label("Add a timestamp to this field") .
	'<div class="subtext">Add the time of the import to this custom field.</div>', 
	form_dropdown("timestamp", $unique_fields,
	 	isset($default_settings["config"]["timestamp"]) ? $default_settings["config"]["timestamp"] : '' )
);
$this->table->add_row(
	form_label("Delete old entries by timestamp") .
	'<div class="subtext">Delete entries from this channel whose timestamp has not been updated</div>', 
	form_hidden("delete_by_timestamp", "n") . 
	form_checkbox("delete_by_timestamp", "y", (isset($default_settings["config"]["delete_by_timestamp"]) && $default_settings["config"]["delete_by_timestamp"] == "y" ? TRUE : FALSE) )
);
$this->table->add_row(
	form_label("Delete entries with old timestamp") .
	'<div class="subtext">Set how old (in seconds) entries can be before being deleted</div>', 
	form_input("delete_by_timestamp_duration", 
	 	isset($default_settings["config"]["delete_by_timestamp_duration"]) ? $default_settings["config"]["delete_by_timestamp_duration"] : '86400' )
);

echo $this->table->generate();
echo $this->table->clear();

/* Comments */

if( $allow_comments ) {

	$this->table->set_template($cp_table_template);
	$this->table->set_heading("Comments", "Value");

	$this->table->add_row(
		array(
			'colspan' => 2,
			'data' => 'Import comments. NOTE: comments are only added when an entry in imported for the first time. Running a subsequent import will update the entry, but not the comments. Please delete the entry to force new comments to be added.',
			'class' => 'box'
		)
	);

	$this->table->add_row(
		form_label("Import comments?") .
		'<div class="subtext">Add comments for this entry</div>', 
		form_hidden("import_comments", "n") . 
		form_checkbox("import_comments", "y", (isset($default_settings["config"]["import_comments"]) && $default_settings["config"]["import_comments"] == "y" ? TRUE : FALSE) )
	);
	
	$this->table->add_row(
		form_label("Comment Author"), 
		form_dropdown( 'comment_author', $data_fields, 
			isset($default_settings["config"]['comment_author']) ? $default_settings["config"]['comment_author'] : '' )
	);

	$this->table->add_row(
		form_label("Comment Author Email"), 
		form_dropdown( 'comment_email', $data_fields, 
			isset($default_settings["config"]['comment_email']) ? $default_settings["config"]['comment_email'] : '' )
	);

	$this->table->add_row(
		form_label("Comment Author URL"), 
		form_dropdown( 'comment_url', $data_fields, 
			isset($default_settings["config"]['comment_url']) ? $default_settings["config"]['comment_url'] : '' )
	);

	$this->table->add_row(
		form_label("Comment Date"), 
		form_dropdown( 'comment_date', $data_fields, 
			isset($default_settings["config"]['comment_date']) ? $default_settings["config"]['comment_date'] : '' )
	);

	$this->table->add_row(
		form_label("Comment Body"), 
		form_dropdown( 'comment_body', $data_fields, 
			isset($default_settings["config"]['comment_body']) ? $default_settings["config"]['comment_body'] : '' )
	);


	echo $this->table->generate();
	echo $this->table->clear();
	
}

/* Other parameters */

$this->table->set_template($cp_table_template);
$this->table->set_heading("Other settings", "Value");

$this->table->add_row(
	array(
		'colspan' => 2,
		'data' => 'Some additional options',
		'class' => 'box'
	)
);
$this->table->add_row(
	form_label("Default Author") . 
	'<div class="subtext">By default, assign entries to this author</div>', 
	form_dropdown("author", $authors, 
	 	isset($default_settings["config"]["author"]) ? $default_settings["config"]["author"] : '1' )
);
$this->table->add_row(
	form_label("Author") .
	NBS . anchor("http://brandnewbox.co.uk/support/details/assigning_authors_to_entries_with_datagrab", "More details", 'class="help" title="Help"') .
	'<div class="subtext">Assign the entry to the member in this field.<br/>Note: members will not be created. If the member does not exist the default author will be used.</div>'
	, 
	form_dropdown("author_field", $data_fields, 
	 	isset($default_settings["config"]["author_field"]) ? $default_settings["config"]["author_field"] : '' )
);
$this->table->add_row(
	form_label("Author Field Value") .
	'<div class="subtext">Select the type of member data that the author field contains</div>', 
	form_dropdown("author_check", $author_fields, 
	 	isset($default_settings["config"]["author_check"]) ? $default_settings["config"]["author_check"] : 'screen_name' )
);

$this->table->add_row(
	form_label("Status") .
	'<div class="subtext">Choose the entry\'s status</div>', 
	form_dropdown("status", $status_fields, 
	 	isset($default_settings["config"]["status"]) ? $default_settings["config"]["status"] : 'default' )
);


$this->table->add_row(
	form_label("Offset (in seconds)") .
	'<div class="subtext">Apply an offset to the publish date</div>', 
	form_input("offset", 
	 	isset($default_settings["config"]["offset"]) ? $default_settings["config"]["offset"] : '0' )
);

$this->table->add_row(
	form_label("Import in batches") .
	'<div class="subtext">Limits the size of the import when you have memory or time-out difficulties</div>', 
	form_input("limit", 
	 	isset($default_settings["import"]["limit"]) ? $default_settings["import"]["limit"] : '0' )
);


echo $this->table->generate();
echo $this->table->clear();

?>

<p style="float:left"><input type="submit" name="import" value="Do Import" class="submit" />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="<?php echo $back_link ?>">Back to Settings</a></p>

<?php
if( isset( $id ) && $id != "" ) {
	echo form_hidden("id", $id);
}
?>

<p style="float:right"><input type="submit" name="save" value="Save import" class="submit" /></p>

<?php echo form_close(); ?>
