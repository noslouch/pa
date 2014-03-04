<?php

// load dependencies
require_once PATH_THIRD.'assets/config.php';

$lang = array(

// -------------------------------------------
//  Module CP
// -------------------------------------------

'assets_module_name' => ASSETS_NAME,
'assets_module_description' => ASSETS_DESC,

'no_module' => 'You haven’t installed Assets’ module yet.',
'no_fieldtype' => 'You haven’t installed Assets’ fieldtype yet.',

// Page titles
'file_manager' => 'File Manager',
'update_indexes' => 'Update Indexes',
'external_sources' => 'External Sources',
'settings' => 'Settings',

// DB Update
'backup_db' => 'Backup your database.',
'backup_db_desc' => 'To complete the Assets update, some changes must be made to your database. It is <em>highly</em> recommended that you backup your database before proceeding.',
'update_assets' => 'Update Assets',
'updating' => 'Updating…',
'updater_316_required' => 'Updater 3.1.6 or later is required to update to Assets 2.',

// General settings
'general_settings' => 'General Settings',
'license_key' => 'License Key',

// Sources
'manage_sources' => 'External sources',
'no_sources_exist' => 'No sources exist yet.',
'add_new_source' => 'Add a New Source',
'access_key_id' => 'Access Key ID',
'secret_access_key' => 'Secret Access Key',
'edit_source' => 'Edit Source',
'source_name' => 'Source Name',
'source_name_instructions' => 'What this source will be called in the CP.',
'source_type' => 'Source Type',
'refresh' => 'Refresh',
'bucket_settings' => 'Bucket Settings',
'bucket' => 'Bucket',
'url_prefix' => 'URL Prefix',
'url_s3_prefix_instructions' => 'If you have set up a CNAME record pointing to this bucket, you can enter it here. Otherwise leave this setting alone.',
'url_gc_prefix_instructions' => 'If you have set up a CNAME record pointing to this bucket, you can enter it here. Otherwise leave this setting alone.',
'url_rs_prefix_instructions' => 'If you have set up a CNAME record pointing to this container, you can enter it here. Otherwise leave this setting alone.',
'save_source' => 'Save Source',
'source_saved' => 'Source saved.',
'confirm_delete_source' => 'Are you sure you want to delet the source “{source}”?',
'source_deleted' => 'Source deleted.',
'ee_sources' => 'Expression Engine File Upload Directories',
's3_sources' => 'Amazon S3 sources',
'rs_sources' => 'Rackspace Cloud Files sources',
'source_type_s3' => 'Amazon S3',
'source_type_gc' => 'Google Cloud Storage',
'source_type_rs' => 'Rackspace Cloud Files',
'rackspace_settings' => 'Rackspace Cloud Files Settings',
's3_settings' => 'Amazon S3 Settings',
'api_key' => 'API Key',
'container' => 'Container',
'source_subfolder' => 'Subfolder',
's3_source_subfolder_instructions' => 'If you want to set the source to a subfolder of your bucket, specify it here.',
'rs_source_subfolder_instructions' => 'If you want to set the source to a subfolder of your container, specify it here.',
'gc_source_subfolder_instructions' => 'If you want to set the source to a subfolder of your bucket, specify it here.',
'gc_settings' => 'Google Cloud Storage Settings',

// Indexing
'index_header' => 'Source indexing',
'index_in_progress' => 'Indexing is in progress...',
'index_complete' => 'Indexing is complete!',
'index_stale_entries_message' => 'There are some entries in the database that are out of date - please select the ones that you want to delete below',
'index_folders' => 'Folder list',
'index_files' => 'File list',
'wrong_credentials' => 'Access denied by target host.',

// -------------------------------------------
//  File Manager
// -------------------------------------------

'search_assets' => 'Search assets',
'search_nested_folders' => 'Search nested folders',

'view_files_as_thumbnails' => 'View files as thumbnails',
'view_files_as_big_thumbnails' => 'View files as big thumbnails',
'view_files_in_list' => 'View files in a list',
'refresh' => 'Refresh',

'name' => 'Name',
'date_modified' => 'Date Modified',
'size' => 'Size',
'folder' => 'Folder',

'upload_files' => 'Upload files',
'upload_status' => '{count} of {total} files uploaded',

'showing' => 'Showing',
'of' => 'of',
'file' => 'file',
'files' => 'files',
'selected' => 'selected',

'new_subfolder' => 'New subfolder…',
'rename' => 'Rename…',
'_delete' => 'Delete',
'view_file' => 'View file',
'edit_file' => 'Edit file',

'confirm_delete_folder' => 'Are you sure you want to delete the folder “{folder}” and all of its contents?',
'confirm_delete_file' => 'Are you sure you want to delete the file “{file}”?',
'confirm_delete_files' => 'Are you sure you want to delete the {num} selected files?',

'error_creating_folder' => 'There was an error creating your folder',
'error_moving_folder' => 'There was an error moving your folder “{folder}”: {error}',
'error_deleting_folder' => 'There was an error deleting your folder',
'error_uploading_file' => 'There was an error uploading your file: {error}',
'error_moving_file' => 'There was an error moving your file “{file}”: {error}',
'error_deleting_file' => 'There was an error deleting your file “{file}”: {error}',
'couldnt_download' => "Could not download the requested file.",
'couldnt_upload' => "Could not upload the file - server returned an unexpected response. Please check the server settings.",

'invalid_source_path' => 'Invalid Assets source path',
'invalid_filedir_path' => 'Invalid EE Upload Directory path',
'invalid_folder_path' => 'That wasn’t a valid folder path',
'invalid_folder_name' => 'That wasn’t a valid folder name',
'invalid_file_path' => 'That wasn’t a valid file path',
'invalid_file_name' => 'That wasn’t a valid file name',
'file_already_exists' => 'A folder or file with that name already exists',

'filedir_not_writable' => 'Your upload directory isn’t writable',
'no_files' => 'No files were uploaded',
'empty_file' => 'The uploaded file was empty',
'file_too_large' => 'Your upload directory only accepts files under {max_size}',
'images_only_allowed' => 'Your upload directory only accepts images',
'couldnt_save' => 'Your file could not be saved. Either the upload was cancelled, or there was a server error.',
'filetype_not_allowed' => 'This file type is disallowed by your EE configuration.',

'exception_error' => 'Assets could not complete the requested operation.',
'unknown_source' => 'An unknown source was requested.',

'file_already_exists__title' => 'A file named “{file}” already exists in that folder.',
'file_already_exists__keep_both' => 'Keep both',
'file_already_exists__replace' => 'Replace the existing file',
'file_already_exists__cancel' => 'Skip this file',

'folder_already_exists__title' => 'Folder “{folder}” already exists at the target location',
'folder_already_exists__replace' => 'Replace the existing folder',
'folder_already_exists__cancel' => 'Cancel the folder move',

'how_to_proceed' => 'What do you want to do?',
'apply_to_remaining_conflicts' => 'Do this for the {number} remaining conflicts?',
'perform_selected' => 'Perform selected action',

'unexpected_response' => 'Remote server returned an unexpected response.',
'connection_information_missing' => 'Connection information not found!',
'unrecognized_operation_type' => 'Unrecognized operation type!',

'recent_uploads' => 'Recent uploads',


// -------------------------------------------
//  Properties
// -------------------------------------------

'invalid_file' => 'Invalid file',

'kind' => 'Kind',
'image_size' => 'Image Size',

'title' => 'Title',
'alt_text' => 'Alt Text',
'caption' => 'Caption',
'description' => 'Description',
'credit' => 'Credit',
'author' => 'Author',
'date' => 'Date',
'location' => 'Location',
'keywords' => 'Keywords',

'cancel' => 'Cancel',
'save_changes' => 'Save Changes',

// -------------------------------------------
//  Field settings
// -------------------------------------------

'file_upload_directories' => 'File Upload Directories',
'file_upload_directories_info' => 'Which file upload directories should authors be allowed to choose files from?',
'no_file_upload_directories' => 'You don’t have any file upload directories yet.',
'allow_multiple_selections' => 'Allow multiple selections?',
'view_options' => 'View Options',
'view_files_as' => 'View files as',
'thumbnails' => 'Thumbnails',
'list' => 'List',
'thumb_size' => 'Thumbnail Size',
'small' => 'Small',
'large' => 'Large',
'show_filenames' => 'Show filenames?',
'columns' => 'Columns',

// -------------------------------------------
//  Field
// -------------------------------------------

// multiple selections
'add_files' => 'Add files',
'remove_files' => 'Remove files',

// single selection
'add_file' => 'Add file',
'remove_file' => 'Remove file',

'image_manipulations_not_supported' => 'Image manipulations are not supported for this source',
'file_not_found' => 'The file cannot be found',

''=>''
);
