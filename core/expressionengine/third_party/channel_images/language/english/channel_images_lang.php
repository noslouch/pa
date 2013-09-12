<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(

// Required for MODULES page
'channel_images'					=>	'Channel Images',
'channel_images_module_name'		=>	'Channel Images',
'channel_images_module_description'	=>	'Enables images to be associated with an entry.',

//----------------------------------------
'ci:home'			=>	'Home',
'ci:legacy_settings'=>	'Legacy Settings',
'ci:docs' 			=>	'Documentation',
'ci:yes'			=>	'Yes',
'ci:no'				=>	'No',
'ci:pref'		=>	'Preference',
'ci:value'		=>	'Value',
'ci:sizes'		=>	'Sizes',
'ci:images'		=>	'Images',
'ci:repeat'     =>  'Repeat',
'ci:quality'	=>	'Quality',

// MCP
'ci:location_path'	=>	'Server Location Path',
'ci:location_url'	=>	'Location URL',
'ci:no_legacy'		=>	'No Legacy Settings Found',
'ci:regenerate_sizes'=>	'Regenerate Sizes',
'ci:ci_fields'		=>	'Channel Images Fields',
'ci:grab_images'	=>	'Grab Images',
'ci:start_resize'	=>	'Start the regeneration process.',
'ci:import'			=>	'Import Images',
'ci:transfer_field'	=>	'Transfer To',
'ci:column_mapping'	=>	'Column Mapping',
'ci:dont_transfer'	=>	'Do Not Transfer',
'ci:import_entries'	=>	'Entries to Process',
'ci:status'	=>	'Status',
'ci:select_regen_field'=>	'Select a field so we can start regenerating...',

'ci:view_mode'  => 'View Mode',
'ci:table_view' => 'Table',
'ci:tiles_view' => 'Tiles',

'ci:drag_images' => 'Drop files here<br>or',

//----------------------------------------
// FIELDTYPE
//----------------------------------------
'ci:new_image_file' =>  'New Image File',

// Actions
'ci:upload_actions'	=>	'Upload Actions',
'ci:click2edit'	=>	'<span>Click to edit..</span>',
'ci:hover2edit'	=>	'<span>Hover to edit..</span>',
'ci:wysiwyg'	=>	'WYSIWYG',
'ci:editable'   =>  'Editable',
'ci:small_prev'	=>	'Small Preview',
'ci:big_prev'	=>	'Big Preview',
'ci:step'		=>	'Step',
'ci:action'		=>	'Action',
'ci:actions'	=>	'Actions',
'ci:add_action'	=>	'Add an Action',
'ci:settings'	=>	'Settings',
'ci:add_action_group'=>	'Add New Size',
'ci:no_actions'	=>	'No actions have yet been defined',
'ci:show_settings'=>	'Show Settings',
'ci:hide_settings'=>	'Hide Settings',


'ci:loc_settings'	=>	'Upload Location Settings',
'ci:upload_location'=>	'Upload Location',
'ci:test_location'	=>	'Test Location',
'ci:specify_pref_cred' =>	'Specify Credential & Settings',
'ci:local'		=>	'Local Server',

// S3
'ci:s3'			=>	'Amazon S3',
'ci:s3:key'		=>	'AWS KEY',
'ci:s3:key_exp'	=>	'Amazon Web Services Key. Found in the AWS Security Credentials.',
'ci:s3:secret_key'	=>	'AWS SECRET KEY',
'ci:s3:secret_key_exp'	=>	'Amazon Web Services Secret Key. Found in the AWS Security Credentials.',
'ci:s3:bucket'		=>	'Bucket',
'ci:s3:bucket_exp'	=>	'Every object stored in Amazon S3 is contained in a bucket. Must be unique.',
'ci:s3:region'		=>	'Bucket Region',
'ci:s3:region:us-east-1'       => 'USA-East (Northern Virginia)',
'ci:s3:region:us-west-1'       => 'USA-West (Northern California)',
'ci:s3:region:us-west-2'       => 'USA-West 2 (Oregon)',
'ci:s3:region:eu'              => 'Europe (Ireland)',
'ci:s3:region:ap-southeast-1'  => 'Asia Pacific (Singapore)',
'ci:s3:region:ap-southeast-2'  => 'Asia Pacific (Sydney, Australia)',
'ci:s3:region:ap-northeast-1'  => 'Asia Pacific (Tokyo, Japan)',
'ci:s3:region:sa-east-1'       => 'South America - (Sao Paulo, Brazil)',
'ci:s3:acl'		=>	'ACL',
'ci:s3:acl_exp'	=>	'ACL is a mechanism which decides who can access an object.',
'ci:s3:acl:public-read'	=>	'Public READ',
'ci:s3:acl:authenticated-read'		=>	'Public Authenticated Read',
'ci:s3:acl:private'		=>	'Owner-only read',
'ci:s3:storage'	=>	'Storage Redundancy',
'ci:s3:storage:standard'=>	'Standard storage redundancy',
'ci:s3:storage:reduced'	=>	'Reduced storage redundancy (cheaper)',
'ci:s3:directory'	=>	'Subdirectory (optional)',
'ci:s3:cloudfrontd'	=>	'Cloudfront Domain (optional)',

// CloudFiles
'ci:cloudfiles'=>'Rackspace Cloud Files',
'ci:cloudfiles:username'	=>	'Username',
'ci:cloudfiles:api'			=>	'API Key',
'ci:cloudfiles:container'	=>	'Container',
'ci:cloudfiles:region'		=>	'Region',
'ci:cloudfiles:region:us'	=>	'United States',
'ci:cloudfiles:region:uk'	=>	'United Kingdom (London)',
'ci:cloudfiles:cdn_uri'		=>	'CDN URI Override',

'ci:fieldtype_settings'	=>	'Fieldtype Settings',
'ci:categories'	=>	'Categories',
'ci:categories_explain'=>	'Separate each category with a comma.',
'ci:keep_original'	=>	'Keep Original Image',
'ci:keep_original_exp'	=>	'WARNING: If you do not upload the original image you will not be able to change the size of your existing images again.',
'ci:show_stored_images'	=>	'Show Stored Images',
'ci:limt_stored_images_author'	=>	'Limit Stored Images by Author?',
'ci:limt_stored_images_author_exp'	=>	'When using the Stored Images feature, all images uploaded by everyone will be searched. <br />Select YES to limit the searching to images uploaded by the current member.',
'ci:stored_images_search_type'	=>	'Stored Images Search Type',
'ci:entry_based' =>	'Entry Based',
'ci:image_based' =>	'Image Based',
'ci:show_import_files'     =>	'Show Import Files',
'ci:show_import_files_exp' =>	'The Import Files feature allows you to add files from the local filesystem',
'ci:import_path'           =>	'Import Path',
'ci:import_path_exp'       =>	'Path where the files will be located',
'ci:show_image_edit'        =>  'Show Image Edit Button',
'ci:show_image_replace'     =>  'Show Image Replace Button',
'ci:allow_per_image_action'	=>	'Allow Per Image Action',
'ci:jeditable_event'=>	'Edit Field Event',
'ci:click'		=>	'Click',
'ci:hover'		=>	'Hover',
'ci:image_limit'	=>	'Image Limit',
'ci:image_limit_exp'=>	'Limit the amount of images a user can upload to this field. Leave empty to allow unlimited images.',
'ci:locked_url_fieldtype'	=>	'Obfuscate image URL\'s in the fieldtype',
'ci:locked_url_fieldtype_exp'=>	'Normally the Image URL\'s are direct links to the files. But in some cases you want the file location to be secret. Enable this option to encrypt the Image URL.<br>NOTE: This will prevent any WYSIWYG Channel Images plugin to work.',
'ci:act_url'		=>	'ACT URL',
'ci:act_url:exp'	=>	'This URL is going to be used for all AJAX calls and image uploads',
'ci:hybrid_upload'       => 'Hybrid Upload',
'ci:hybrid_upload_exp'   =>	'Enabling this option will turn on HTML 5 uploading, otherwise Flash uploading will occur.',
'ci:progressive_jpeg'	=>	'Create Progressive JPEG',
'ci:progressive_jpeg_exp'=>	'Enabling this will create progressive JPEG. Limitations: Does not work with CE Image Actions, Internet Explorer does not support progressive JPEGs',
'ci:wysiwyg_original'	=>	'Original Image Option in WYSIWYG',
'ci:save_data_in_field'	=>	'Save image metadata in Custom Field',
'ci:save_data_in_field_exp'	=>	'When you enable this Channel Images will store image titles/desc/etc in the Custom Field so it can be searched. <br>Note: It takes more space in your database.',
'ci:disable_cover'		=>	'Disable Cover button',
'ci:convert_jpg'        =>  'Convert all uploaded images to JPG',

'ci:embedded_data' => 'Embedded Data',
'ci:parse_iptc'    => 'Parse IPTC',
'ci:parse_exif'    => 'Parse EXIF',
'ci:parse_xmp'     => 'Parse XMP',

'ci:cover_first' => 'Move Cover images to the first position',

// Field Columns
'ci:field_columns'		=>	'Field Columns',
'ci:field_columns_exp'	=>	'Specify a label for each column, leave the field blank to disable the column.',
'ci:row_num'		=>	'#',
'ci:id'				=>	'ID',
'ci:image'			=>	'Image',
'ci:title'			=>	'Title',
'ci:url_title'		=>	'URL Title',
'ci:desc'			=>	'Description',
'ci:category'		=>	'Category',
'ci:filename'		=>	'Filename',
'ci:actions:edit'	=>	'Edit',
'ci:actions:cover'	=>	'Cover',
'ci:actions:move'	=>	'Move',
'ci:actions:del'	=>	'Delete',
'ci:actions:replace'   =>  'Replace Image',
'ci:actions:process_action'=>	'Process Action',
'ci:actions:unlink'	=>	'Unlink',
'ci:cifield_1'		=>	'Field 1',
'ci:cifield_2'		=>	'Field 2',
'ci:cifield_3'		=>	'Field 3',
'ci:cifield_4'		=>	'Field 4',
'ci:cifield_5'		=>	'Field 5',
'c:filesize'		=>	'Filesize',

// PBF
'ci:upload_images'	=>	'Upload Images',
'ci:stored_images'	=>	'Stored Images',
'ci:time_remaining'	=>	'Time Remaining',
'ci:stop_upload'	=>	'Stop Upload',
'ci:dupe_field'		=>	'Only one Channel Images field can be used at once.',
'ci:missing_settings'=>	'Missing Channel Images settings for this field.',
'ci:no_images'		=>	'No images have yet been uploaded.',
'ci:site_is_offline'=>	'Site is OFFLINE! Uploading images will/might not work.',
'ci:image_remain'	=>	'Images Remaining:',
'ci:crossdomain_detect' =>	'CROSSDOMAIN: The current domain does not mach the ACT URL domain. Upload may fail due crossdomain restrictions.',
'ci:drophere'           =>	'Drop Your Files Here....',

'ci:json:click2edit'		=> '<span>Click to edit..</span>',
'ci:json:mouseenter2edit'	=> '<span>Hover to edit..</span>',
'ci:json:file_limit_reached'=> 'ERROR: File Limit Reached',
'ci:json:xhr_reponse_error'	=> "Server response was not as expected, probably a PHP error. <a href='#' class='OpenError'>OPEN ERROR</a>",
'ci:json:xhr_status_error'	=> "Upload request failed, no HTTP 200 Return Code! <a href='#' class='OpenError'>OPEN ERROR</a>",
'ci:json:del_file'			=> "Are you sure you want to delete this file? \nTip: Press shift while clicking to delete all files at once",
'ci:json:unlink_file'		=> 'Are you sure you want to unlink this file?',
'ci:json:del_file_all'          => 'Are you sure you want to delete ALL files?',
'ci:json:linked_force_del'	=> "This file is linked with other entries, are you sure you want to delete it? \n The other references will also be deleted!",
'ci:json:submitwait'		=> 'You have uploaded file(s), those files are now being send to their final destination. This can take a while depending on the amount of files..',

'ci:add_image'		=>	'Add Image',
'ci:caption_text'	=>	'Image Caption:',

// Stored Images
'ci:last'			=>	'Last',
'ci:entries'		=>	'Entries',
'ci:filter_keywords'	=>	'Keywords',
'ci:entry_images'	=>	'Entry Images',
'ci:loading_images'	=>	'Loading Images...',
'ci:loading_entries'=>	'Loading Entries..',
'ci:no_entry_sel'	=>	'No entry has been selected.',
'ci:no_images'		=>	'No Images found..',

// Import Files
'ci:import_files'		=> 'Import Images',
'ci:import:bad_path'	=> 'The supplied import path does not exist (or is inaccessible).',
'ci:import:no_files'	=> 'No files..',

// Action Per Image
'ci:apply_action'	=>	'Apply Action',
'ci:apply_action_exp'=>	'Select an action to execute on the selected image size.',
'ci:select_action'	=>	'Select an Action',
'ci:applying_action'=>	'Applying your selected action, please wait...',
'ci:preview'		=>	'Preview',
'ci:save'			=>	'Save',
'ci:save_img'           =>  'Save Image',
'ci:cancel' =>  'Cancel',
'ci:original'		=>	'ORIGINAL',

// Edit Image
'ci:crop'   =>  'Crop',
'ci:rotate_left'=>  'Rotate Left',
'ci:rotate_right'=>  'Rotate Right',
'ci:flip_hor'=>  'Flip Horizontally',
'ci:flip_ver'=>  'Flip Vertically',
'ci:image_scaled_note'  =>  '<storng>Note:</storng> This is a scaled representation of the actual image size.',
'ci:regen_sizes'    =>  'Regenerate All Sizes',
'ci:apply_crop'     =>  'Apply Crop',
'ci:cancel_crop'    =>  'Cancel Crop',
'ci:set_crop_sel'   =>  'Set Selection',

'ci:zenbu_show_cover'   =>  'Show Cover image only (or first image)',

// Pagination
'ci:pag_first_link' => '&lsaquo; First',
'ci:pag_last_link' => 'Last &rsaquo;',

'ci:required_field'	=>	'REQUIRED FIELD: Please add at least one image.',

// Errors
'ci:file_arr_empty'	=> 'No file was uploaded or file is not allowed by EE.(See EE Mime-type settings).',
'ci:tempkey_missing'	=> 'The temp key was not found',
'ci:file_upload_error'	=> 'No file was uploaded. (Maybe filesize was too big)',
'ci:no_settings'		=> 'No settings exist for this fieldtype',
'ci:location_settings_failure'	=>	'Upload Location Settings Missing',
'ci:location_load_failure'	=>	'Failure to load Upload Location Class',
'ci:tempdir_error'		=>	'The Local Temp dir is either not writable or does not exist',

'ci:temp_dir_failure'		=>	'Failed to create the temp dir, through Upload Location Class',
'ci:file_upload_error'		=>	'Failed to upload the image, through Upload Location Class',



'ci:no_upload_location_found' => 'Upload Location has not been found!.',
'ci:file_to_big'		=> 'The file is too big. (See module settings for max file size).',
'ci:extension_not_allow'=> 'The file extension is not allowed. (See module settings for file extensions)',
'ci:targetdir_error'	=> 'The target directory is either not writable or does not exist',
'ci:file_move_error'	=> 'Failed to move uploaded file to the temp directory, please check upload path permissions etc.',

//----------------------------------------
// ACTIONS
//----------------------------------------
'ce:thickness'      =>  'Thickness',
'ce:thickness:exp'  =>  'The border thickness in pixels. Defaults to 1.',
'ce:color'          =>  'Color',
'ce:color:exp'      =>  "The color can be a 3 or 6 digit hexadecimal (with or without the #), and defaults to '000000' if not specified.",
'ce:border_exp' =>  "This setting allows you to create a solid color border around your image. <br /><strong>Note:</strong> Like borders applied with CSS, the border is created on the outer edge of the image (as opposed to an inner border). If you have a 100px by 100px image for example, with a border-width of 10 passed in, your final image dimensions would be 120px by 120px.",
'ce:brightness'     =>  'Brightness',
'ce:brightness:exp' =>  'The brightness level (-255 = min brightness, 0 = no change, +255 = max brightness).',
'ce:red'        =>  'Red',
'ce:red:exp'    =>  'Value of red component (-255 = min, 0 = no change, +255 = max).',
'ce:green'      =>  'Green',
'ce:green:exp'  =>  'Value of green component (-255 = min, 0 = no change, +255 = max).',
'ce:blue'       =>  'Blue',
'ce:blue:exp'   =>  'Value of blue component (-255 = min, 0 = no change, +255 = max).',
'ce:alpha'       =>  'Alpha channel',
'ce:alpha:exp'   =>  'A value between 0 and 127. 0 indicates completely opaque while 127 indicates completely transparent.',
'ce:colorize_exp'   =>  'Like grayscale, except you can specify the color.',
'ce:contrast'       =>  'Contrast',
'ce:contrast:exp'   =>  'The contrast level (-100 = max contrast, 0 = no change, +100 = min contrast (note the direction!)).',
'ci:rotate:degrees' =>  'Degrees',
'ci:rotate:exp' =>  'Rotates image specified number of degrees.',
'ci:rotate:only_if' =>  'Only If',
'ci:rotate:always'  =>  'Always Rotate',
'ci:rotate:width_bigger'=>  'Width is Longest (Landscape)',
'ci:rotate:height_bigger'=> 'Height is Longest (Portrait)',
'ci:rotate:bg_color' => 'BG Color',
'ce:block_size'     =>  'Block Size',
'ce:block_size:exp' =>  'Block size in pixels.',
'ce:advanced'       =>  'Advanced',
'ce:advanced:exp'   =>  'Whether to use advanced pixelation effect or not.',
'ce:pixelate_exp'   =>  'Applies pixelation effect to the image',
'ci:resize:width'   =>  'Width',
'ci:resize:height'  =>  'Height',
'ci:resize:quality' =>  'Quality',
'ci:crop:center_exp'        =>  'Crops an image from the center with provided dimensions. If no height is given, the width will be used as a height, thus creating a square crop.',
'ci:resize:startx'  =>  'Start X',
'ci:resize:starty'  =>  'Start Y',
'ci:resize:width'   =>  'Width',
'ci:resize:height'  =>  'Height',
'ci:resize:quality' =>  'Quality',
'ci:crop:standard_exp'      =>  'Vanilla Cropping - Crops from x,y with specified width and height.',
'ce:smooth'     =>  'Smooth',
'ce:smooth:exp' =>  'Applies a 9-cell convolution matrix where center pixel has the weight $value and others weight of 1.0. The result is normalized by dividing the sum with $value + 8.0 (sum of the matrix).',
'ce:contrast'       =>  'Contrast',
'ce:contrast:exp'   =>  'The contrast level (-100 = max contrast, 0 = no change, +100 = min contrast (note the direction!)).',
'ci:flip:axis'  =>  'Axis',
'ci:flip:exp'   =>  'Flips an image.',
'ci:flip:horizontal'=>  'Horizontal',
'ci:flip:vertical'  =>  'Vertical',
'ci:flip:both'      =>  'Both',
'ci:resize:percent' =>  'Percent',
'ci:resize:percent_exp' =>  'This resizes the image down by the amount of percent specified.',
'ce:edgedetect:exp' =>  'Uses edge detection to highlight the edges in the image.',
'ce:mean_removal:exp'   =>  'Uses mean removal to achieve a "sketchy" effect.',
'ce:gaussian_blur:exp'  =>  'Blurs the image using the Gaussian method.',
'ce:negate:exp' =>  'Reverses all colors of the image.',
'ci:greyscale:exp'  =>  'Creates a Black & White version of the image.',
'ce:emboss:exp' =>  'Embosses the image.',
'ci:sepia:exp'  =>  'Creates a Sepia version of the image.',
'ci:watermark:position_settings'    =>  'Position Settings',
'ci:watermark:padding'      => 'Padding',
'ci:watermark:padding:exp'  => 'The amount of padding, set in pixels, that will be applied to the watermark to set it away from the edge of your images.',
'ci:watermark:horalign'     => 'Horizontal Alignment',
'ci:watermark:horalign:exp' => 'Sets the horizontal alignment for the watermark image. (left, center, right)',
'ci:watermark:vrtalign'     => 'Vertical Alignment',
'ci:watermark:vrtalign:exp' => 'Sets the vertical alignment for the watermark image. (top, middle, bottom)',
'ci:watermark:horoffset'    => 'Horizontal Offset',
'ci:watermark:horoffset:exp'=> 'You may specify a horizontal offset (in pixels) to apply to the watermark position. The offset normally moves the watermark to the right, except if you have your alignment set to "right" then your offset value will move the watermark toward the left of the image.',
'ci:watermark:vrtoffset'    => 'Vertical Offset',
'ci:watermark:vrtoffset:exp'=> 'You may specify a vertical offset (in pixels) to apply to the watermark position. The offset normally moves the watermark down, except if you have your alignment set to "bottom" then your offset value will move the watermark toward the top of the image.',
'ci:watermark:text_pref'    => 'Text Preferences',
'ci:watermark:text'         => 'Text',
'ci:watermark:text:exp'     => 'The text you would like shown as the watermark. Typically this will be a copyright notice.',
'ci:watermark:font_path'            => 'Font Path',
'ci:watermark:font_path:exp'        => 'The server path to the True Type Font you would like to use. If you do not use this option, EE\'s default font will be used.',
'ci:watermark:font_size'            => 'Font Size',
'ci:watermark:font_size:exp'        => 'The size of the text. Note: If you are not using the True Type option above, the number is set using a range of 1 - 5. Otherwise, you can use any valid pixel size for the font you\'re using.',
'ci:watermark:font_color'           => 'Font Color',
'ci:watermark:font_color:exp'       => 'The font color, specified in hex. Note, you must use the full 6 character hex value (ie, 993300), rather than the three character abbreviated version (ie fff).',
'ci:watermark:shadow_color'         => 'Shadow Color',
'ci:watermark:shadow_color:exp'     => 'The color of the drop shadow, specified in hex. If you leave this blank a drop shadow will not be used. Note, you must use the full 6 character hex value (ie, 993300), rather than the three character abbreviated version (ie fff).',
'ci:watermark:shadow_distance'      => 'Shadow Distance',
'ci:watermark:shadow_distance:exp'  => 'The distance (in pixels) from the font that the drop shadow should appear.',
'ci:watermark:top'      =>  'Top',
'ci:watermark:middle'   =>  'Middle',
'ci:watermark:bottom'   =>  'Bottom',
'ci:watermark:left'     =>  'Left',
'ci:watermark:center'   =>  'Center',
'ci:watermark:right'    =>  'Right',
'ci:watermark:position_settings'    =>  'Position Settings',
'ci:watermark:overlay_pref'     => 'Image Overlay Preferences',
'ci:watermark:padding'      => 'Padding',
'ci:watermark:padding:exp'  => 'The amount of padding, set in pixels, that will be applied to the watermark to set it away from the edge of your images.',
'ci:watermark:horalign'     => 'Horizontal Alignment',
'ci:watermark:horalign:exp' => 'Sets the horizontal alignment for the watermark image. (left, center, right)',
'ci:watermark:vrtalign'     => 'Vertical Alignment',
'ci:watermark:vrtalign:exp' => 'Sets the vertical alignment for the watermark image. (top, middle, bottom)',
'ci:watermark:horoffset'    => 'Horizontal Offset',
'ci:watermark:horoffset:exp'=> 'You may specify a horizontal offset (in pixels) to apply to the watermark position. The offset normally moves the watermark to the right, except if you have your alignment set to "right" then your offset value will move the watermark toward the left of the image.',
'ci:watermark:vrtoffset'    => 'Vertical Offset',
'ci:watermark:vrtoffset:exp'=> 'You may specify a vertical offset (in pixels) to apply to the watermark position. The offset normally moves the watermark down, except if you have your alignment set to "bottom" then your offset value will move the watermark toward the top of the image.',
'ci:watermark:overlay_pref'     => 'Image Overlay Preferences',
'ci:watermark:overlay_path'     => 'Overlay Path',
'ci:watermark:overlay_path:exp' => 'The server path to the image you wish to use as your watermark. Required only if you are using the overlay method.',
'ci:watermark:opacity'          => 'Opacity',
'ci:watermark:opacity:exp'      => 'Image opacity. You may specify the opacity (i.e. transparency) of your watermark image. This allows the watermark to be faint and not completely obscure the details from the original image behind it. A 50% opacity is typical.',
'ci:watermark:x_trans'          => 'X-Transparent',
'ci:watermark:x_trans:exp'      => 'If your watermark image is a PNG or GIF image, you may specify a color on the image to be "transparent". This setting (along with the next) will allow you to specify that color. This works by specifying the "X" and "Y" coordinate pixel (measured from the upper left) within the image that corresponds to a pixel representative of the color you want to be transparent.',
'ci:watermark:y_trans'          => 'Y-Transparent',
'ci:watermark:y_trans:exp'      => 'Along with the previous setting, this allows you to specify the coordinate to a pixel representative of the color you want to be transparent.',
'ci:watermark:top'      =>  'Top',
'ci:watermark:middle'   =>  'Middle',
'ci:watermark:bottom'   =>  'Bottom',
'ci:watermark:left'     =>  'Left',
'ci:watermark:center'   =>  'Center',
'ci:watermark:right'    =>  'Right',
'im:radius' =>  'Radius',
'im:sigma'  =>  'Sigma',
'im:amount' =>  'Amount',
'im:threshold'  =>  'Threshold',

'im:radius:exp' =>  'An integer value defining the radius of the convolution kernel (i.e. the circle of pixels which are analysed by the unsharpmask filter), not including the center pixel. Setting this value to 0 will cause imagick to automatically choose an optimal radius based on chosen sigma value. Processing time will increase to approximately the square of the radius.',
'im:sigma:exp'  =>  ' Describes the relative weight of pixels as a function of their distance from the center of the convolution kernel. For small sigma, the outer pixels have little weight. Sigma is a decimal and should be smaller than or equal to the radius.',
'im:amount:exp' =>  ' The fraction (as a decimal) of the difference between the original and processed image that is added back into the original. Values from 0 to 1 would be normal, values above 1 provide a more extreme sharpening effect.',
'im:threshold:exp'  =>  'A decimal from 0 to 1 defining the amount of contrast required between the central and surrounding pixels in the convolution kernel in order for it to get sharpened. A value of 0 means all pixels will be sharpened equally. Higher values will leave smoother areas of an image unsharpened, whilst sharpening only edges.',
'ce:gap_height'     =>  'Gap Height',
'ce:gap_height:exp'     =>  'The vertical space, in pixels, between the image and its reflection.',
'ce:start_opacity'  =>  'Start Opacity',
'ce:start_opacity:exp'  =>  'The opacity of the reflection at the top of the reflection. This can be any number between 0 (completely transparent) and 100 (completely opaque). The default is 80.',
'ce:end_opacity'    =>  'End Opacity',
'ce:end_opacity:exp'    =>  'The opacity of the reflection at the bottom of the reflection. This can be any number between 0 (completely transparent) and 100 (completely opaque). The default is 0.',
'ce:reflection_height'  =>  'Reflection Height',
'ce:reflection_height:exp'  =>  "The height of the reflection, either in pixels, or as the percentage of the image. If specifying as a percentage string, then a % should be concatenated on the end. For example, if the made image was 100px tall, and a reflection height of '50%' was specified, then the resulting reflection would be 50px tall.",

'ce:reflection_exp' =>  'The reflection setting allows you to create a reflection for the image, otherwise known as the "wet floor effect."',
'ci:resize:width'   =>  'Width',
'ci:resize:height'  =>  'Height',
'ci:resize:percent' =>  'Percent',
'ci:resize:percent_adaptive_exp'    =>  '
This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
remaining overflow using a provided percentage to get the image to be the size specified.
<br /><br />
The percentage mean different things depending on the orientation of the original image.
<br /><br />
Note: that you can use any percentage between 1 and 100.
<br /><br />
For Landscape images:<br />
---------------------<br />
A percentage of 1 would crop the image all the way to the left.<br />
A percentage of 50 would crop the image to the center.<br />
A percentage of 100 would crop the image to the image all the way to the right, etc, etc.
<br /><br />
For Portrait images:<br />
--------------------<br />
This works the same as for Landscape images except that a percentage of 1 means top and 100 means bottom<br />

',
'ce:corner_identifier'      =>  'Corner Identifier',
'ce:corner_identifier:exp'  =>  "This index can have a value of any one of the following: all,tl,tr,bl,br. If 'all' is used, it will set the preferences for each of the corners.",
'ce:radius'     =>  'Radius',
'ce:radius:exp' =>  'The radius of the corner. If this is set to 0, the corner will not be rounded.',
'ce:rccolor'        =>  'Color',
'ce:rccolor:exp'    =>  'Can be left blank to indicate transparency or can be set to 3 or 6 digit hexadecimal number (with or without the #)',

'ce:rounded_corners_exp'    =>  'The rounded_corners setting allows you to round one or more corners to a certain radius with antialiasing. If you are saving to png format, you can also have the corners be completely transparent. Rounded corners are applied after all resizing, filters, and watermarking has been completed.',
'ce:amount'         =>  'Amount',
'ce:amount:exp'     =>  "How much of the effect you want, 100 is 'normal' (typically 50 to 200).",
'ce:radius'         =>  'Radius',
'ce:radius:exp'     =>  'Radius of the blurring circle of the mask. (typically 0.5 to 1).',
'ce:threshold'      =>  'Threshold',
'ce:threshold_sharpen:exp'  =>  'The least difference in color values that is allowed between the original and the mask. In practice this means that low-contrast areas of the picture are left un-rendered whereas edges are treated normally. This is good for pictures of e.g. skin or blue skies. (typically 0 to 5).',


'ce:sharpen_exp'    =>  'When images are resized by PHP, they often appear a bit blurry. You can use this filter to sharpen the images to your taste.',
'im:radius' =>  'Radius',
'im:sigma'  =>  'Sigma',
'im:image_sharpen_exp'  =>  '
<pre style="font-family:Helvetica,Arial,sans-serif;font-size:10px;">
The most important factor is the sigma. As it is the real control of thesharpening operation.
It can be any floating point value from  .1  for practically no sharpening to 3 or more for sever sharpening.
0.5 to 1.0 is rather good.

Radius is just a limit of the effect as is the threshold.

Radius is only in integer units as that is the way the algorithm works, the larger it is the slower it is.
But it should be at a minimum 1 or better still 2 times the sigma.
</pre>
',
'ci:resize:width'   =>  'Width',
'ci:resize:height'  =>  'Height',
'ci:resize:quality' =>  'Quality',
'ci:resize:upsizing'=>  'Allow Upsizing',
'ci:resize:adaptive_exp'    =>  'This function attempts to get the image to as close to the provided dimensions as possible, and then crops the remaining overflow (from the center) to get the image to be the size specified. Additionally, if ALLOW_UPSIZING is set to YES (NO by default), then this function will also scale the image up to the maximum dimensions provided.',
'ce:threshold'      =>  'Threshold',
'ce:threshold:exp'  =>  'The brightness level (-255 = min brightness, 0 = no change, +255 = max brightness).',
'ce:foreground'     =>  'Foreground Color',
'ce:foreground:exp' =>  'Hexadecimal color value (3 or 6 digits)',
'ce:background'     =>  'Background Color',
'ce:background:exp' =>  'Hexadecimal color value (3 or 6 digits)',

'ce:sobel_edgify_exp'   =>  'This is an edge detection filter that uses the sobel technique. CAUTION: This filter is very slow..',
'ci:resize:width'   =>  'Width',
'ci:resize:height'  =>  'Height',
'ci:resize:quality' =>  'Quality',
'ci:resize:upsizing'=>  'Allow Upsizing',
'ci:resize:exp'     =>  'Resizes an image to be no larger than WIDTH or HEIGHT. If either param is set to zero, then that dimension will not be considered as a part of the resize. Additionally, if ALLOW_UPSIZING is set to YES (NO by default), then this function will also scale the image up to the maximum dimensions provided.',















// END
''=>''
);

/* End of file channel_images_lang.php */
/* Location: ./system/expressionengine/third_party/channel_images/language/english/channel_images_lang.php */
