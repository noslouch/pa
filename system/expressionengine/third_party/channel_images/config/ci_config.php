<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (isset($this->EE) == FALSE) $this->EE =& get_instance(); // For EE 2.2.0+

$config['ci_image_preview_size'] = '50px';

// Default Field Columns
$config['ci_columns']['row_num']	= $this->EE->lang->line('ci:row_num');
$config['ci_columns']['id']			= $this->EE->lang->line('ci:id');
$config['ci_columns']['image']		= $this->EE->lang->line('ci:image');
$config['ci_columns']['filename']	= '';
$config['ci_columns']['title']		= $this->EE->lang->line('ci:title');
$config['ci_columns']['url_title']	= '';
$config['ci_columns']['desc']		= $this->EE->lang->line('ci:desc');
$config['ci_columns']['category']	= '';
$config['ci_columns']['cifield_1']	= '';
$config['ci_columns']['cifield_2']	= '';
$config['ci_columns']['cifield_3']	= '';
$config['ci_columns']['cifield_4']	= '';
$config['ci_columns']['cifield_5']	= '';

// Defaults
$config['ci_defaults']['view_mode'] = 'table';
$config['ci_defaults']['no_sizes'] = 'no';
$config['ci_defaults']['keep_original'] = 'yes';
$config['ci_defaults']['upload_location'] = 'local';
$config['ci_defaults']['categories'] = array();
$config['ci_defaults']['show_stored_images'] = 'no';
$config['ci_defaults']['stored_images_by_author'] = 'no';
$config['ci_defaults']['stored_images_search_type'] = 'entry';
$config['ci_defaults']['show_import_files'] = 'no';
$config['ci_defaults']['import_path'] = '';
$config['ci_defaults']['jeditable_event'] = 'click';
$config['ci_defaults']['image_limit'] = '';
$config['ci_defaults']['hybrid_upload'] = 'yes';
$config['ci_defaults']['progressive_jpeg'] = 'no';
$config['ci_defaults']['wysiwyg_original'] = 'yes';
$config['ci_defaults']['save_data_in_field'] = 'no';
$config['ci_defaults']['show_image_edit'] = 'yes';
$config['ci_defaults']['show_image_replace'] = 'yes';
$config['ci_defaults']['allow_per_image_action'] = 'no';
$config['ci_defaults']['locked_url_fieldtype'] = 'no';
$config['ci_defaults']['disable_cover'] = 'no';
$config['ci_defaults']['convert_jpg'] = 'no';
$config['ci_defaults']['parse_exif'] = 'no';
$config['ci_defaults']['parse_xmp'] = 'no';
$config['ci_defaults']['parse_iptc'] = 'no';
$config['ci_defaults']['cover_first'] = 'yes';
$config['ci_defaults']['locations']['local']['location'] = 0;
$config['ci_defaults']['locations']['s3']['key'] = '';
$config['ci_defaults']['locations']['s3']['secret_key'] = '';
$config['ci_defaults']['locations']['s3']['bucket'] = '';
$config['ci_defaults']['locations']['s3']['region'] = 'us-east-1';
$config['ci_defaults']['locations']['s3']['acl'] = 'public-read';
$config['ci_defaults']['locations']['s3']['storage'] = 'standard';
$config['ci_defaults']['locations']['s3']['directory'] = '';
$config['ci_defaults']['locations']['s3']['cloudfront_domain'] = '';
$config['ci_defaults']['locations']['cloudfiles']['username'] = '';
$config['ci_defaults']['locations']['cloudfiles']['api'] = '';
$config['ci_defaults']['locations']['cloudfiles']['container'] = '';
$config['ci_defaults']['locations']['cloudfiles']['region'] = 'us';
$config['ci_defaults']['locations']['cloudfiles']['cdn_uri'] = '';


// Upload Locations
$config['ci_upload_locs']['local']	= $this->EE->lang->line('ci:local');
$config['ci_upload_locs']['s3']		= $this->EE->lang->line('ci:s3');
$config['ci_upload_locs']['cloudfiles'] = $this->EE->lang->line('ci:cloudfiles');

// S3
$config['ci_s3_regions']['us-east-1']   = 'REGION_US_E1';
$config['ci_s3_regions']['us-west-1']   = 'REGION_US_W1';
$config['ci_s3_regions']['us-west-2']   = 'REGION_US_W2';
$config['ci_s3_regions']['eu']          = 'REGION_EU_W1';
$config['ci_s3_regions']['ap-southeast-1']  = 'REGION_APAC_SE1';
$config['ci_s3_regions']['ap-southeast-2']  = 'REGION_APAC_SE2';
$config['ci_s3_regions']['ap-northeast-1']  = 'REGION_APAC_NE1';
$config['ci_s3_regions']['sa-east-1']  = 'REGION_SA_E1';

$config['ci_s3_acl']['private']	= 'ACL_PRIVATE';
$config['ci_s3_acl']['public-read']	= 'ACL_PUBLIC';
$config['ci_s3_acl']['authenticated-read']	= 'ACL_AUTH_READ';
$config['ci_s3_storage']['standard']= 'STORAGE_STANDARD';
$config['ci_s3_storage']['reduced']	= 'STORAGE_REDUCED';

// S3 Request Headers
//$config['ci_s3_headers']['Cache-Control'] = 'max-age=' . (30* 24 * 60 * 60);
//$config['ci_s3_headers']['Expires'] = gmdate("D, d M Y H:i:s T", strtotime('+1 month') );

// Cloudfiles
$config['ci_cloudfiles_regions']['us']	= 'US_AUTHURL';
$config['ci_cloudfiles_regions']['uk']	= 'UK_AUTHURL';

// Default Actions
$config['ci_default_action_groups'][1] = array(	'group_name' => 'small', 'wysiwyg' => 'yes',
												'actions' => array('resize_adaptive' => array('width' => 100, 'height' => 100, 'quality' => 80, 'upsizing' => 'no')));
$config['ci_default_action_groups'][2] = array(	'group_name' => 'medium', 'wysiwyg' => 'yes',
												'actions' => array('resize_adaptive' => array('width' => 450, 'height' => 300, 'quality' => 75, 'upsizing' => 'no')));
$config['ci_default_action_groups'][3] = array(	'group_name' => 'large', 'wysiwyg' => 'yes',
												'actions' => array('resize_adaptive' => array('width' => 800, 'height' => 600, 'quality' => 75, 'upsizing' => 'no')));
