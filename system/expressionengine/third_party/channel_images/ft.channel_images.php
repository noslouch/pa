<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_images/config'.EXT;

/**
 * Channel Images Module FieldType
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class Channel_images_ft extends EE_Fieldtype
{

	/**
	 * Field info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'name' 		=> CHANNEL_IMAGES_NAME,
		'version'	=> CHANNEL_IMAGES_VERSION,
	);

	/**
	 * The field settings array
	 *
	 * @access public
	 * @var array
	 */
	public $settings = array();

	public $has_array_data = TRUE;
	public $dropdown_type = "contains_doesnotcontain"; // Zenbu

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		if (version_compare(APP_VER, '2.1.4', '>')) { parent::__construct(); } else { parent::EE_Fieldtype(); }

		if ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		else $this->site_id = $this->EE->config->item('site_id');

		$this->EE->load->add_package_path(PATH_THIRD . 'channel_images/');
		$this->EE->lang->loadfile('channel_images');
		$this->EE->load->library('image_helper');
		$this->EE->load->model('channel_images_model');
		$this->EE->image_helper->define_theme_url();

		$this->EE->config->load('ci_config');

		if (isset($this->EE->channel_images) === FALSE) $this->EE->channel_images = new stdClass();

		// Set the EE Cache Path? (hell you can override that)
		if (!isset($this->EE->channel_images->cache_path)) $this->EE->channel_images->cache_path = $this->EE->config->item('cache_path') ? $this->EE->config->item('cache_path') : APPPATH.'cache/';
	}

	// ********************************************************************************* //

	/**
	 * Display the field in the publish form
	 *
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 *
	 */
	function display_field($data)
	{
		//----------------------------------------
		// Global Vars
		//----------------------------------------
		$vData = array();
		$vData['missing_settings'] = FALSE;
		$vData['field_name'] = $this->field_name;
		$vData['field_id'] = $this->field_id;
		$vData['temp_key'] = $this->EE->localize->now;
		$vData['entry_id'] = ($this->EE->input->get_post('entry_id') != FALSE) ? $this->EE->input->get_post('entry_id') : FALSE;
		$vData['total_images'] = 0;
		$vData['assigned_images'] = array();

		//----------------------------------------
		// Add Global JS & CSS & JS Scripts
		//----------------------------------------
		$this->EE->image_helper->mcp_js_css('gjs');
		$this->EE->image_helper->mcp_js_css('css', 'channel_images_pbf.css?v='.CHANNEL_IMAGES_VERSION, 'channel_images', 'main');
		$this->EE->image_helper->mcp_js_css('css', 'jquery.colorbox.css', 'jquery', 'colorbox');
		$this->EE->image_helper->mcp_js_css('js', 'jquery.editable.js', 'jquery', 'editable');
		$this->EE->image_helper->mcp_js_css('js', 'jquery.base64.js', 'jquery', 'base64');
		$this->EE->image_helper->mcp_js_css('js', 'jquery.liveurltitle.js', 'jquery', 'liveurltitle');
		$this->EE->image_helper->mcp_js_css('js', 'jquery.colorbox.js', 'jquery', 'colorbox');
		$this->EE->image_helper->mcp_js_css('js', 'jquery.jcrop.min.js', 'jquery', 'jcrop');
		$this->EE->image_helper->mcp_js_css('js', 'hogan.min.js', 'hogan', 'main');
		$this->EE->image_helper->mcp_js_css('js', 'json2.js', 'json2', 'main');
		$this->EE->image_helper->mcp_js_css('js', 'swfupload.js', 'swfupload', 'main');
		$this->EE->image_helper->mcp_js_css('js', 'swfupload.queue.js', 'swfupload', 'queue');
		$this->EE->image_helper->mcp_js_css('js', 'swfupload.speed.js', 'swfupload', 'speed');
		$this->EE->image_helper->mcp_js_css('js', 'channel_images_pbf.js?v='.CHANNEL_IMAGES_VERSION, 'channel_images', 'main');

		$this->EE->cp->add_js_script(array(
		        'ui'        => array('sortable', 'tabs')
		    )
		);

		//----------------------------------------
		// Settings
		//----------------------------------------
		$settings = $this->settings;

		// Settings SET?
		if ( (isset($settings['channel_images']['action_groups']) == FALSE OR empty($settings['channel_images']['action_groups']) == TRUE) && (isset($settings['channel_images']['no_sizes']) == FALSE OR $settings['channel_images']['no_sizes'] != 'yes') )
		{
			$vData['missing_settings'] = TRUE;
			return $this->EE->load->view('pbf_field', $vData, TRUE);
		}

		// Map it Back
		$settings = $settings['channel_images'];
		$defaults = $this->EE->config->item('ci_defaults');

		// Columns?
		if (isset($settings['columns']) == FALSE) $settings['columns'] = $this->EE->config->item('ci_columns');

		// Stored Images
		if (isset($settings['show_stored_images']) == FALSE) $settings['show_stored_images'] = $defaults['show_stored_images'];


		// Limit Images?
		if (isset($settings['image_limit']) == FALSE OR trim($settings['image_limit']) == FALSE) $settings['image_limit'] = 999999;

		if (isset($this->session->cache['ChannelImages']['PerImageActionHolder']) == FALSE && $settings['allow_per_image_action'] == 'yes')
		{
			$vData['actions'] = &$this->EE->image_helper->get_actions();
			$this->session->cache['ChannelImages']['PerImageActionHolder'] = TRUE;
		}

		$vData['settings'] = $this->EE->image_helper->array_extend($defaults, $settings);


		//----------------------------------------
		// Field JSON
		//----------------------------------------
		$vData['field_json'] = array();
		$vData['field_json']['key'] = $vData['temp_key'];
		$vData['field_json']['field_name'] = $this->field_name;
		$vData['field_json']['field_label'] = $this->settings['field_label'];
		$vData['field_json']['settings'] = $vData['settings'];
		$vData['field_json']['categories'] = array();

		// Add Categories
		if (isset($settings['categories']) == TRUE && empty($settings['categories']) == FALSE)
		{
			$vData['field_json']['categories'][''] = '';
			foreach ($settings['categories'] as $cat) $vData['field_json']['categories'][$cat] = $cat;
		}

		// Remove some unwanted stuff
		unset($vData['field_json']['settings']['categories']);
		unset($vData['field_json']['settings']['locations']);
		unset($vData['field_json']['settings']['import_path']);

		//----------------------------------------
		// JS Templates
		//----------------------------------------
		$vData['js_templates'] = FALSE;
		if (isset( $this->EE->session->cache['ChannelImages']['JSTemplates'] ) === FALSE)
		{
			$vData['js_templates'] = TRUE;
			$this->EE->session->cache['ChannelImages']['JSTemplates'] = TRUE;

			$vData['langjson'] = array();

			foreach ($this->EE->lang->language as $key => $val)
			{
				if (strpos($key, 'ci:json:') === 0)
				{
					$vData['langjson'][substr($key, 8)] = $val;
					unset($this->EE->lang->language[$key]);
				}

			}

			$vData['langjson'] = $this->EE->image_helper->generate_json($vData['langjson']);
		}

		//----------------------------------------
		// Auto-Saved Entry?
		//----------------------------------------
		if ($this->EE->input->get('use_autosave') == 'y')
		{
			$vData['entry_id'] = FALSE;
			$old_entry_id = $this->EE->input->get_post('entry_id');
			$query = $this->EE->db->select('original_entry_id')->from('exp_channel_entries_autosave')->where('entry_id', $old_entry_id)->get();
			if ($query->num_rows() > 0 && $query->row('original_entry_id') > 0) $vData['entry_id'] = $query->row('original_entry_id');
		}

		//----------------------------------------
		// Existing Entry?
		//----------------------------------------
		if ($vData['entry_id'] != FALSE)
		{
			// -----------------------------------------
			// Grab all Images
			// -----------------------------------------
			$this->EE->db->select('*');
			$this->EE->db->from('exp_channel_images');
			$this->EE->db->where('entry_id', $vData['entry_id']);
			$this->EE->db->where('field_id', $this->field_id);

			if (isset($this->EE->session->cache['ep_better_workflow']['is_draft']) && $this->EE->session->cache['ep_better_workflow']['is_draft'])
			{
  				$this->EE->db->where('is_draft', 1);
			}
			else
			{
				$this->EE->db->where('is_draft', 0);
			}

			if ($vData['settings']['cover_first'] == 'yes') $this->EE->db->order_by('cover', 'desc');
			$this->EE->db->order_by('image_order');
			$query = $this->EE->db->get();

			// -----------------------------------------
			// Which Previews?
			// -----------------------------------------
			if (isset($settings['small_preview']) == FALSE OR $settings['small_preview'] == FALSE)
			{
				$temp = reset($settings['action_groups']);
				$settings['small_preview'] = $temp['group_name'];
			}

			if (isset($settings['big_preview']) == FALSE OR $settings['big_preview'] == FALSE)
			{
				$temp = reset($settings['action_groups']);
				$settings['big_preview'] = $temp['group_name'];
			}

			// Preview URL
			$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');

			foreach ($query->result() as $image)
			{
				// We need a good field_id to continue
				$image->field_id = $this->EE->channel_images_model->get_field_id($image);

				// Is it a linked image?
				// Then we need to "fake" the channel_id/field_id
				if ($image->link_image_id >= 1)
				{
					$image->entry_id = $image->link_entry_id;
					$image->field_id = $image->link_field_id;
					$image->channel_id = $image->link_channel_id;
				}

				// Just in case lets try to get the field_id again
				$image->field_id = $this->EE->channel_images_model->get_field_id($image);

				// Get settings for that field..
				$temp_settings = $this->EE->channel_images_model->get_field_settings($image->field_id);

				$act_url_params = "&amp;fid={$image->field_id}&amp;d={$image->entry_id}";

				if ( empty($settings['action_groups']) == FALSE && (isset($settings['no_sizes']) == FALSE OR $settings['no_sizes'] != 'yes') )
				{
					// Display SIzes URL
					$small_filename = str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", urlencode($image->filename) );
					$big_filename = str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", urlencode($image->filename) );

					if ($this->EE->config->item('ci_encode_filename_url') == 'yes')
					{
						$small_filename = base64_encode($small_filename);
						$big_filename = base64_encode($big_filename);
					}

					$image->small_img_url = "{$preview_url}&amp;f={$small_filename}{$act_url_params}";
					$image->big_img_url = "{$preview_url}&amp;f={$big_filename}{$act_url_params}";
				}
				else
				{
					$small_filename = $image->filename;
					$big_filename = $image->filename;

					if ($this->EE->config->item('ci_encode_filename_url') == 'yes')
					{
						$small_filename = base64_encode($small_filename);
						$big_filename = base64_encode($big_filename);
					}

					// Display SIzes URL
					$image->small_img_url = "{$preview_url}&amp;f={$small_filename}{$act_url_params}";
					$image->big_img_url = "{$preview_url}&amp;f={$big_filename}{$act_url_params}";
				}

				// ReAssign Field ID (WE NEED THIS)
				$image->field_id = $this->field_id;

				$image->title = str_replace('&quot;', '"', $image->title);
				$image->description = str_replace('&quot;', '"', $image->description);
				$image->cifield_1 = str_replace('&quot;', '"', $image->cifield_1);
				$image->cifield_2 = str_replace('&quot;', '"', $image->cifield_2);
				$image->cifield_3 = str_replace('&quot;', '"', $image->cifield_3);
				$image->cifield_4 = str_replace('&quot;', '"', $image->cifield_4);
				$image->cifield_5 = str_replace('&quot;', '"', $image->cifield_5);

				$vData['assigned_images'][] = $image;

				unset($image);
			}

			$vData['total_images'] = $query->num_rows();
		}

		//----------------------------------------
		// Form Submission Error?
		//----------------------------------------
		if (isset($_POST[$this->field_name]) OR isset($_POST['field_id_' . $this->field_id]))
		{
			// Post DATA?
			if (isset($_POST[$this->field_name])) {
				$data = $_POST[$this->field_name];
			}

			if (isset($_POST['field_id_' . $this->field_id])) {
				$data = $_POST['field_id_' . $this->field_id];
			}

			// First.. The Key!
			$vData['field_json']['key'] = $data['key'];
			$vData['temp_key'] = $data['key'];

			if (isset($data['images']) == TRUE)
			{
				$vData['assigned_images'] = '';

				// Preview URL
				$preview_url = $this->EE->image_helper->get_router_url('url', 'simple_image_url');

				foreach($data['images'] as $num => $img)
				{
					$img = $this->EE->image_helper->decode_json(html_entity_decode($img['data']));

					// Existing? lets get it!
					if ($img->image_id > 0)
					{
						$image = $img;
					}
					else
					{
						$image = $img;

						if ($image->link_image_id > 0)
						{
							continue;
						}

						$image->image_id = 0;
						$image->extension = substr( strrchr($image->filename, '.'), 1);
						$image->field_id = $this->field_id;

						// Display SIzes URL
						$image->small_img_url = $preview_url . '&amp;temp_dir=yes&amp;fid='.$this->field_id.'&amp;d=' . $vData['temp_key'] . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['small_preview']}.{$image->extension}", $image->filename);
						$image->big_img_url = $preview_url . '&amp;temp_dir=yes&amp;fid='.$this->field_id.'&amp;d=' . $vData['temp_key'] . '&amp;f=' . str_replace('.'.$image->extension, "__{$settings['big_preview']}.{$image->extension}", $image->filename);
					}

					// We need a good field_id to continue
					$image->field_id = $this->EE->channel_images_model->get_field_id($image);

					// Is it a linked image?
					// Then we need to "fake" the channel_id/field_id
					if ($image->link_image_id >= 1)
					{
						$image->entry_id = $image->link_entry_id;
						$image->field_id = $image->link_field_id;
						$image->channel_id = $image->link_channel_id;
					}

					// Just in case lets try to get the field_id again
					$image->field_id = $this->EE->channel_images_model->get_field_id($image);

					// ReAssign Field ID (WE NEED THIS)
					$image->field_id = $this->field_id;

					$vData['assigned_images'][] = $image;

					unset($image);
				}
			}
		}

		$vData['field_json']['images'] = $vData['assigned_images'];
		$vData['field_json'] = base64_encode($this->EE->image_helper->generate_json($vData['field_json']));
		// Base64encode why? Safecracker loves to mess with quotes/unicode etc!!

		return $this->EE->load->view('pbf_field', $vData, TRUE);
	}

	// ********************************************************************************* //

	/**
	 * Validates the field input
	 *
	 * @param $data Contains the submitted field data.
	 * @return mixed Must return TRUE or an error message
	 */
	public function validate($data)
	{
		// Is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			if (isset($data['images']) == FALSE OR empty($data['images']) == TRUE)
			{
				return $this->EE->lang->line('ci:required_field');
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Preps the data for saving
	 *
	 * @param $data Contains the submitted field data.
	 * @return string Data to be saved
	 */
	function save($data)
	{
		$this->EE->session->cache['ChannelImages']['FieldData'][$this->field_id] = $data;

		if (isset($data['images']) == FALSE)
		{
			return '';
		}
		else
		{
			$field_data = '';

			// -----------------------------------------
			// Save Data in Custom Field
			// -----------------------------------------
			if (isset($this->settings['channel_images']['save_data_in_field']) == TRUE && $this->settings['channel_images']['save_data_in_field'] == 'yes')
			{
				foreach ($data['images'] as $order => $file)
				{
					$file = $this->EE->image_helper->decode_json($file['data']);
					if (isset($file->delete) === TRUE) continue;

					$field_data .= "{$file->filename} - {$file->title}\n{$file->description} {$file->cifield_1} {$file->cifield_2} {$file->cifield_3} {$file->cifield_4} {$file->cifield_5}\n\n";
				}
			}
			else
			{
				$field_data = 'ChannelImages';
			}

			return $field_data;
		}
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is saved.
	 * Called after an entry is added or updated.
	 * Available data is identical to save, but the settings array includes an entry_id.
	 *
	 * @param $data Contains the submitted field data. (Returned by save())
	 * @access public
	 * @return void
	 */
	function post_save($data)
	{
		@set_time_limit(0);
		@ini_set('memory_limit', '64M');
		@ini_set('memory_limit', '96M');
		@ini_set('memory_limit', '128M');
		@ini_set('memory_limit', '160M');
		@ini_set('memory_limit', '192M');
		@ini_set('memory_limit', '256M');
		@ini_set('memory_limit', '320M');
		@ini_set('memory_limit', '512M');

		return $this->_process_post_save($data);
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is deleted.
	 * Called after one or more entries are deleted.
	 *
	 * @param $ids array is an array containing the ids of the deleted entries.
	 * @access public
	 * @return void
	 */
	function delete($ids)
	{
		foreach ($ids as $entry_id)
		{
			// -----------------------------------------
			// ENTRY TO FIELD (we need settigns :()
			// -----------------------------------------
			$this->EE->db->select('field_id');
			$this->EE->db->from('exp_channel_images');
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			if ($query->num_rows() == 0) continue;

			$field_id = $query->row('field_id');

			// Grab the field settings
			$settings = $this->EE->image_helper->grab_field_settings($field_id);
			$settings = $settings['channel_images'];

			// -----------------------------------------
			// Load Location
			// -----------------------------------------
			$location_type = $settings['upload_location'];
			$location_class = 'CI_Location_'.$location_type;

			// Load Settings
			if (isset($settings['locations'][$location_type]) == FALSE)
			{
				$o['body'] = $this->EE->lang->line('ci:location_settings_failure');
				exit( $this->EE->image_helper->generate_json($o) );
			}

			$location_settings = $settings['locations'][$location_type];

			// Load Main Class
			if (class_exists('Image_Location') == FALSE) require PATH_THIRD.'channel_images/locations/image_location.php';

			// Try to load Location Class
			if (class_exists($location_class) == FALSE)
			{
				$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';

				if (file_exists($location_file) == FALSE)
				{
					$o['body'] = $this->EE->lang->line('ci:location_load_failure');
					exit( $this->EE->image_helper->generate_json($o) );
				}

				require $location_file;
			}

			// Init
			$LOC = new $location_class($location_settings);

			// -----------------------------------------
			// Delete From DB
			// -----------------------------------------
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->or_where('link_entry_id', $entry_id);
			$this->EE->db->delete('exp_channel_images');

			// -----------------------------------------
			// Delete!
			// -----------------------------------------
			$LOC->delete_dir($entry_id);
		}

	}

	// ********************************************************************************* //

	/**
	 * Display the settings page. The default ExpressionEngine rows can be created using built in methods.
	 * All of these take the current $data and the fieltype name as parameters:
	 *
	 * @param $data array
	 * @access public
	 * @return void
	 */
	public function display_settings($data)
	{
		$vData = array();

		// -----------------------------------------
		// Defaults
		// -----------------------------------------
		$vData = $this->EE->config->item('ci_defaults');

		// -----------------------------------------
		// Add JS & CSS
		// -----------------------------------------
		$this->EE->image_helper->mcp_meta_parser('gjs', '', 'ChannelImages');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.css', 'jquery.colorbox');
		$this->EE->image_helper->mcp_meta_parser('css', CHANNELIMAGES_THEME_URL . 'channel_images_fts.css', 'ci-fts');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.editable.js', 'jquery.editable', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.base64.js', 'jquery.base64', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'jquery.colorbox.js', 'jquery.colorbox', 'jquery');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'hogan.min.js', 'hogan', 'hogan');
		$this->EE->image_helper->mcp_meta_parser('js', CHANNELIMAGES_THEME_URL . 'channel_images_fts.js', 'ci-fts');
		$this->EE->cp->add_js_script(array('ui' => array('tabs', 'draggable', 'sortable')));

		$this->EE->load->library('javascript');
		$this->EE->javascript->output('ChannelImages.Init();');


		// -----------------------------------------
		// Upload Location
		// -----------------------------------------
		$vData['upload_locations'] = $this->EE->config->item('ci_upload_locs');

		// S3 Stuff
		$vData['s3']['regions'] = $this->EE->config->item('ci_s3_regions');
		foreach($vData['s3']['regions'] as $key => $val) $vData['s3']['regions'][$key] = $this->EE->lang->line('ci:s3:region:'.$key);
		$vData['s3']['acl'] = $this->EE->config->item('ci_s3_acl');
		foreach($vData['s3']['acl'] as $key => $val) $vData['s3']['acl'][$key] = $this->EE->lang->line('ci:s3:acl:'.$key);
		$vData['s3']['storage'] = $this->EE->config->item('ci_s3_storage');
		foreach($vData['s3']['storage'] as $key => $val) $vData['s3']['storage'][$key] = $this->EE->lang->line('ci:s3:storage:'.$key);

		// Cloudfiles Stuff
		$vData['cloudfiles']['regions'] = $this->EE->config->item('ci_cloudfiles_regions');
		foreach($vData['cloudfiles']['regions'] as $key => $val) $vData['cloudfiles']['regions'][$key] = $this->EE->lang->line('ci:cloudfiles:region:'.$key);

		// Local
		$vData['local']['locations'] = array();
		$locs = $this->EE->image_helper->get_upload_preferences();
		foreach ($locs as $loc) $vData['local']['locations'][ $loc['id'] ] = $loc['name'];

		// -----------------------------------------
		// Fieldtype Columns
		// -----------------------------------------
		$vData['columns'] = $this->EE->config->item('ci_columns');

		// -----------------------------------------
		// ACT URL
		// -----------------------------------------
		$vData['act_url'] = $this->EE->image_helper->get_router_url();

		// -----------------------------------------
		// Actions!
		// -----------------------------------------
		$vData['actions'] = &$this->EE->image_helper->get_actions();

		$vData['action_groups'] = array();

		if (isset($data['channel_images']['action_groups']) == FALSE && (isset($data['channel_images']['no_sizes']) == FALSE OR $data['channel_images']['no_sizes'] != 'yes') )
		{
			$vData['action_groups'] = $this->EE->config->item('ci_default_action_groups');
		}
		else
		{
			$vData = $this->EE->image_helper->array_extend($vData, $data['channel_images']);
		}

		foreach($vData['action_groups'] as &$group)
		{
			$actions = $group['actions'];
			$group['actions'] = array();

			foreach($actions AS $action_name => &$settings)
			{
				$new = array();
				$new['action'] = $action_name;
				$new['action_name'] = $vData['actions'][$action_name]->info['title'];
				$new['action_settings'] = $vData['actions'][$action_name]->display_settings($settings);
				$group['actions'][] = $new;
			}

			if (isset($group['wysiwyg']) == TRUE && $group['wysiwyg'] == 'no') unset($group['wysiwyg']);
			if (isset($group['editable']) == TRUE && $group['editable'] == 'no') unset($group['editable']);
		}

		// -----------------------------------------
		// Previews
		// -----------------------------------------
		if (isset($vData['small_preview']) == FALSE OR $vData['small_preview'] == FALSE)
		{
			$temp = reset($vData['action_groups']);
			$vData['small_preview'] = $temp['group_name'];
		}

		// Big Preview
		if (isset($vData['big_preview']) == FALSE OR $vData['big_preview'] == FALSE)
		{
			$temp = reset($vData['action_groups']);
			$vData['big_preview'] = $temp['group_name'];
		}

		$vData['action_groups'] = $this->EE->image_helper->generate_json($vData['action_groups']);


		// -----------------------------------------
		// Merge Settings
		// -----------------------------------------
		$vData = $this->EE->image_helper->array_extend($vData, $data);

		// Tiles as default!
		if ($this->EE->input->get('field_id') == false)
		{
			$vData['view_mode'] = 'tiles';
		}

		// -----------------------------------------
		// Display Row
		// -----------------------------------------
		$row = $this->EE->load->view('fts_settings', $vData, TRUE);
		$this->EE->table->add_row(array('data' => $row, 'colspan' => 2));
	}

	// ********************************************************************************* //

	/**
	 * Save the fieldtype settings.
	 *
	 * @param $data array Contains the submitted settings for this field.
	 * @access public
	 * @return array
	 */
	public function save_settings($data)
	{
		$settings = array();

		//print_r($_POST);exit();

		// Is it there?
		if (isset($_POST['channel_images']) == FALSE) return $settings;

		$P = $_POST['channel_images'];

		// We need this for the url_title() method!
		$this->EE->load->helper('url');

		// Get Actions
		$actions = &$this->EE->image_helper->get_actions();

		// -----------------------------------------
		// Loop over all action_groups (if any)
		// -----------------------------------------
		if (isset($P['action_groups']) == TRUE)
		{
			foreach($P['action_groups'] as $order => &$group)
			{
				// Format Group Name
				$group['group_name'] = str_replace('@', '123atsign123', $group['group_name']); // Preserve the @ sign
				$group['group_name'] = strtolower(url_title($group['group_name']));
		    	$group['group_name'] = str_replace('123atsign123', '@', $group['group_name']); // Put it back!

				$group['final_size'] = FALSE;

				// WYSIWYG
				if (isset($group['wysiwyg']) == FALSE OR $group['wysiwyg'] == FALSE)
				{
					$group['wysiwyg'] = 'no';
				}

				// Editable
				if (isset($group['editable']) == FALSE OR $group['editable'] == FALSE)
				{
					$group['editable'] = 'no';
				}

				// -----------------------------------------
				// Process Actions
				// -----------------------------------------
				if (isset($group['actions']) == FALSE OR empty($group['actions']) == TRUE)
				{
					unset($P['action_groups'][$order]);
					continue;
				}

				foreach($group['actions'] as $action => &$action_settings)
				{
					$this->EE->cache['channel_images']['group_final_size'] = FALSE;

					if (isset($actions[$action]) == FALSE)
					{
						unset($group['actions'][$action]);
						continue;
					}

					$action_settings = $actions[$action]->save_settings($action_settings);

					if ($this->EE->cache['channel_images']['group_final_size'] != FALSE)
					{
						$group['final_size'] = $this->EE->cache['channel_images']['group_final_size'];
					}
				}
			}

			// -----------------------------------------
			// Previews
			// -----------------------------------------
			if (isset($P['small_preview']) == TRUE && $P['small_preview'] != FALSE)
			{
				$P['small_preview'] = $P['action_groups'][$P['small_preview']]['group_name'];
			}
			else
			{
				$P['small_preview'] = $P['action_groups'][1]['group_name'];
			}

			// Big Preview
			if (isset($P['big_preview']) == TRUE && $P['big_preview'] != FALSE)
			{
				$P['big_preview'] = $P['action_groups'][$P['big_preview']]['group_name'];
			}
			else
			{
				$P['big_preview'] = $P['action_groups'][1]['group_name'];
			}
		}
		else
		{
			// Mark it as having no sizes!
			$P['no_sizes'] = 'yes';
			$P['action_groups'] = array();
		}


		// -----------------------------------------
		// Parse categories
		// -----------------------------------------
		$categories = array();
		foreach (explode(',', $P['categories']) as $cat)
		{
			$cat = trim ($cat);
			if ($cat != FALSE) $categories[] = $cat;
		}

		$P['categories'] = $categories;


		if (substr($P['import_path'], -1) != '/') $P['import_path'] .= '/';

		// -----------------------------------------
		// Put it Back!
		// -----------------------------------------
		$settings['channel_images'] = $P;

		return $settings;
	}

	// ********************************************************************************* //

	/**
	 * Allows the specification of an array of fields to be added,
	 * modified or dropped when custom fields are created, edited or deleted.
	 *
	 * $data contains the settings for this field as well an indicator of
	 * the action being performed ($data['ee_action'] with a value of delete, add or get_info).
	 *
	 *  By default, when a new custom field is created,
	 *  2 fields are added to the exp_channel_data table.
	 *  The content field (field_id_x) is a text field and the format field (field_ft_x)
	 *  is a tinytext NULL default. You may override or add to those defaults
	 *  by including an array of fields and field formatting options in this method.
	 *
	 * @param $data array Contains the submitted settings for this field.
	 * @access public
	 * @return array
	 */
	function settings_modify_column($data)
	{
		if ($data['ee_action'] == 'delete')
		{
			// Load the API
			if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
			$API = new Channel_Images_API();

			$field_id = $data['field_id'];

			// Grab all images
			$this->EE->db->select('image_id, field_id, entry_id, filename, extension');
			$this->EE->db->from('exp_channel_images');
			$this->EE->db->where('field_id', $field_id);
			$this->EE->db->where('link_image_id', 0);
			$query = $this->EE->db->get();

			foreach ($query->result() as $row)
			{
				$API->delete_image($row);
			}
		}

		$fields = parent::settings_modify_column($data);

		return $fields;
	}

	// ********************************************************************************* //

	/**
	 * Replace Tag - Replace the field tag on the frontend.
	 *
	 * @param  mixed   $data    contains the field data (or prepped data, if using pre_process)
	 * @param  array   $params  contains field parameters (if any)
	 * @param  boolean $tagdata contains data between tag (for tag pairs)
	 * @return string           template data
	 */
	public function replace_tag($data, $params=array(), $tagdata = FALSE)
	{
		// We always need tagdata
		if ($tagdata === FALSE) return '';

		if (isset($params['prefetch']) == TRUE && $params['prefetch'] == 'yes')
		{
			// In some cases EE stores the entry_ids of the whole loop
			// We can use this to our advantage by grabbing
			if (isset($this->EE->session->cache['channel']['entry_ids']) === TRUE)
			{
				$this->EE->channel_images_model->pre_fetch_data($this->EE->session->cache['channel']['entry_ids'], $params);
			}
		}

		return $this->EE->channel_images_model->parse_template($this->row['entry_id'], $this->field_id, $params, $tagdata);
	}

	// ********************************************************************************* //

	public function draft_save($data, $draft_action)
	{
		// -----------------------------------------
		// Are we creating a new draft?
		// -----------------------------------------
		if ($draft_action == 'create')
		{

			// We are doing this because if you delete an image in live mode
			// and hit the draft button, we need to reflect that delete action in the draft
			$images = array();
			if (isset($data['images']) == TRUE)
			{
				foreach ($data['images'] as $key => $file)
				{
					$file = $this->EE->image_helper->decode_json($file['data']);
					if (isset($file->delete) === TRUE)
					{
						unset($data['images'][$key]);
						continue;
					}

					if (isset($file->image_id) === TRUE && $file->image_id > 0) $images[] = $file->image_id;
				}
			}

			if (count($images) > 0)
			{
				// Grab all existing images
				$query = $this->EE->db->select('*')->from('exp_channel_images')->where_in('image_id', $images)->get();

				foreach ($query->result_array() as $row)
				{
					$row['is_draft'] = 1;
					unset($row['image_id']);
					$this->EE->db->insert('exp_channel_images', $row);
				}
			}
		}

		$this->_process_post_save($data, $draft_action);

		if (isset($data['images']) == FALSE) return '';
		else return 'ChannelImages';
	}

	// ********************************************************************************* //

	public function draft_discard()
	{
		$entry_id = $this->settings['entry_id'];
		$field_id = $this->settings['field_id'];

		// Load the API
		if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
		$API = new Channel_Images_API();

		// Grab all existing images
		$query = $this->EE->db->select('*')->from('exp_channel_images')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 1)->get();

		foreach ($query->result() as $row)
		{
			$API->delete_image($row);
		}
	}

	// ********************************************************************************* //

	public function draft_publish()
	{
		// Load the API
		if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
		$API = new Channel_Images_API();

		// Grab all existing images
		$query = $this->EE->db->select('*')->from('exp_channel_images')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 0)->get();

		foreach ($query->result() as $row)
		{
			$API->delete_image($row);
		}

		// Grab all existing images
		$query = $this->EE->db->select('image_id')->from('exp_channel_images')->where('entry_id', $this->settings['entry_id'])->where('field_id', $this->settings['field_id'])->where('is_draft', 1)->get();

		foreach ($query->result() as $row)
		{
			$this->EE->db->set('is_draft', 0);
			$this->EE->db->where('image_id', $row->image_id);
			$this->EE->db->update('exp_channel_images');
		}
	}

	// ********************************************************************************* //

	private function _process_post_save($data, $draft_action=NULL)
	{
		//print_r($data); exit();

		$this->EE->load->library('image_helper');
		$this->EE->load->helper('url');

		// Are we using Better Workflow?
		if ($draft_action !== NULL)
		{
			$is_draft = 1;
			$entry_id = $this->settings['entry_id'];
			$field_id = $this->settings['field_id'];
			$channel_id = $this->settings['channel_id'];
			$settings = $this->EE->channel_images_model->get_field_settings($field_id);
			$settings = $settings['channel_images'];
		}
		else
		{
			$is_draft = 0;
			$data = (isset($this->EE->session->cache['ChannelImages'])) ? $this->EE->session->cache['ChannelImages']['FieldData'][$this->field_id] : FALSE;
			$entry_id = $this->settings['entry_id'];
			$channel_id = $this->EE->input->post('channel_id');
			$field_id = $this->field_id;

			// Grab Settings
			$settings = $this->settings['channel_images'];
		}

//print_r($data);
		// Do we need to skip?
		if (isset($data['images']) == FALSE) return;
		if (is_array($data['images']) === FALSE) return;

		// Our Key
		$key = $data['key'];

		// -----------------------------------------
		// Load Location
		// -----------------------------------------
		$location_type = $settings['upload_location'];
		$location_class = 'CI_Location_'.$location_type;
		$location_settings = $settings['locations'][$location_type];

		// Load Main Class
		if (class_exists('Image_Location') == FALSE) require PATH_THIRD.'channel_images/locations/image_location.php';

		// Try to load Location Class
		if (class_exists($location_class) == FALSE)
		{
			$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';

			require $location_file;
		}

		// Init!
		$LOC = new $location_class($location_settings);

		// Create the DIR!
		$LOC->create_dir($entry_id);

		// Image Widths,Height,Filesize
		$metadata = array();

		// Try to load Location Class
		if (class_exists($location_class) == FALSE)
		{
			$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';

			require $location_file;
		}

		// Load the API
		if (class_exists('Channel_Images_API') != TRUE) include 'api.channel_images.php';
		$API = new Channel_Images_API();

		// -----------------------------------------
		// Loop over all images and delete!
		// -----------------------------------------
		foreach ($data['images'] as $order => $file)
		{
			$file = $this->EE->image_helper->decode_json($file['data']);
			$data['images'][$order] = $file;
			if (isset($file->delete) === TRUE)
			{
				$API->delete_image($file);
				unset($data['images'][$order]);
			}
		}

		// -----------------------------------------
		// Upload all Images!
		// -----------------------------------------
		$temp_dir = $this->EE->channel_images->cache_path.'channel_images/field_'.$field_id.'/'.$key;

		// Loop over all files
		$tempfiles = @scandir($temp_dir);

		if (is_array($tempfiles) == TRUE)
		{
			foreach ($tempfiles as $tempfile)
			{
				if ($tempfile == '.' OR $tempfile == '..') continue;

				$file	= $temp_dir . '/' . $tempfile;

				$res = $LOC->upload_file($file, $tempfile, $entry_id);

		    	if ($res == FALSE)
		    	{

		    	}

		    	// Parse Image Size
		    	$imginfo = @getimagesize($file);

				// Metadata!
				$metadata[$tempfile] = array('width' => @$imginfo[0], 'height' => @$imginfo[1], 'size' => @filesize($file));

				@unlink($file);
			}
		}

		@rmdir($temp_dir);

		// -----------------------------------------
		// Grab all the files from the DB
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_images');
		$this->EE->db->where('entry_id', $entry_id);
		$this->EE->db->where('field_id', $field_id);

		if ($is_draft === 1 && $draft_action == 'update')
		{
			$this->EE->db->where('is_draft', 1);
		}
		else
		{
			$this->EE->db->where('is_draft', 0);
		}

		$query = $this->EE->db->get();

		// -----------------------------------------
		// Lets create an image hash! So we can check for unique images
		// -----------------------------------------
		$dbimages = array();
		foreach ($query->result() as $row)
		{
			$dbimages[] = $row->image_id.$row->filename;
		}

		if ($is_draft === 1 && $draft_action == 'create')
		{
			$dbimages = array();
		}

		$field_data = '';

		if (count($dbimages) > 0)
		{
			// Not fresh, lets see whats new.
			foreach ($data['images'] as $order => $file)
			{
				//Extension
				$extension = substr( strrchr($file->filename, '.'), 1);

				// Mime type
				$filemime = 'image/jpeg';
				if ($extension == 'png') $filemime = 'image/png';
				elseif ($extension == 'gif') $filemime = 'image/gif';

				// Check for link_image_id
				if (isset($file->link_image_id) == FALSE) $file->link_image_id = 0;
				$file->link_entryid = 0;
				$file->link_channelid = 0;
				$file->link_fieldid = 0;

				// Check URL Title
				if (isset($file->url_title) == FALSE OR $file->url_title == FALSE)
				{
					$file->url_title = url_title(trim(strtolower($file->title)));
				}

				if ($this->EE->image_helper->in_multi_array($file->image_id.$file->filename, $dbimages) === FALSE)
				{
					// Parse Image Size
					$width=''; $height=''; $filesize='';

					// -----------------------------------------
					// Parse width/height/field_id/channel_id/entry_id
					// -----------------------------------------
					if ($file->link_image_id > 0)
					{
						$imgquery = $this->EE->db->query("SELECT entry_id, field_id, channel_id, filesize, width, height, sizes_metadata FROM exp_channel_images WHERE image_id = {$file->link_image_id} ");
						$file->link_entryid = $imgquery->row('entry_id');
						$file->link_channelid = $imgquery->row('channel_id');
						$file->link_fieldid = $imgquery->row('field_id');
						$width = $imgquery->row('width');
						$height = $imgquery->row('height');
						$filesize = $imgquery->row('filesize');
						$mt = $imgquery->row('sizes_metadata');
						if (is_string($mt) == FALSE) $mt = ''; // Some installs get weird mysql errors
					}
					else
					{
						$width = isset($metadata[$file->filename]['width']) ? $metadata[$file->filename]['width'] : 0;
						$height = isset($metadata[$file->filename]['height']) ? $metadata[$file->filename]['height'] : 0;
						$filesize = isset($metadata[$file->filename]['size']) ? $metadata[$file->filename]['size'] : 0;

						// -----------------------------------------
						// Parse Size Metadata!
						// -----------------------------------------
						$mt = '';
						foreach($settings['action_groups'] as $group)
						{
							$name = strtolower($group['group_name']);
							$size_filename = str_replace('.'.$extension, "__{$name}.{$extension}", $file->filename);

							$mt .= $name.'|' . implode('|', $metadata[$size_filename]) . '/';
						}
					}

					// -----------------------------------------
					// New File
					// -----------------------------------------
					$data = array(	'site_id'	=>	$this->site_id,
									'entry_id'	=>	$entry_id,
									'channel_id'=>	$channel_id,
									'member_id'	=>	$this->EE->session->userdata['member_id'],
									'is_draft'	=>	$is_draft,
									'link_image_id' => $file->link_image_id,
									'link_entry_id' => $file->link_entryid,
									'link_channel_id' => $file->link_channelid,
									'link_field_id' => $file->link_fieldid,
									'upload_date' => $this->EE->localize->now,
									'field_id'	=>	$field_id,
									'image_order'	=>	$order,
									'filename'	=>	$file->filename,
									'extension' =>	$extension,
									'mime'		=>	$filemime,
									'filesize'	=>	$filesize,
									'width'		=>	$width,
									'height'	=>	$height,
									'title'		=>	$API->process_field_string($file->title),
									'url_title'	=>	$API->process_field_string($file->url_title),
									'description' => $API->process_field_string($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cifield_1'	=>	$API->process_field_string($file->cifield_1),
									'cifield_2'	=>	$API->process_field_string($file->cifield_2),
									'cifield_3'	=>	$API->process_field_string($file->cifield_3),
									'cifield_4'	=>	$API->process_field_string($file->cifield_4),
									'cifield_5'	=>	$API->process_field_string($file->cifield_5),
									'cover'		=>	$file->cover,
									'sizes_metadata' => $mt,
									'iptc'		=>	$file->iptc,
									'exif'		=>	$file->exif,
									'xmp'		=>	$file->xmp,
								);

					$this->EE->db->insert('exp_channel_images', $data);
				}
				else
				{
					// -----------------------------------------
					// Old File
					// -----------------------------------------
					$data = array(	'cover'		=>	$file->cover,
									'channel_id'=>	$channel_id,
									'field_id'	=>	$field_id,
									'is_draft'	=>	$is_draft,
									'image_order'=>	$order,
									'title'		=>	$API->process_field_string($file->title),
									'url_title'	=>	$API->process_field_string($file->url_title),
									'description' => $API->process_field_string($file->description),
									'category' 	=>	(isset($file->category) == true) ? $file->category : '',
									'cifield_1'	=>	$API->process_field_string($file->cifield_1),
									'cifield_2'	=>	$API->process_field_string($file->cifield_2),
									'cifield_3'	=>	$API->process_field_string($file->cifield_3),
									'cifield_4'	=>	$API->process_field_string($file->cifield_4),
									'cifield_5'	=>	$API->process_field_string($file->cifield_5),
									'mime'		=>	$filemime,
								);

					$this->EE->db->update('exp_channel_images', $data, array('image_id' =>$file->image_id));
				}
			}
		}
		else
		{

			// No previous entries, fresh fresh
			foreach ($data['images'] as $order => $file)
			{
				// If we are creating a new draft, we already copied all data.. So lets kill the ones that came through POST
				if ($is_draft === 1 && $file->image_id > 0)
				{
					// -----------------------------------------
					// Old File
					// -----------------------------------------
					$this->EE->db->set('cover', $file->cover);
					$this->EE->db->set('image_order', $order);
					$this->EE->db->set('title', $API->process_field_string($file->title) );
					$this->EE->db->set('url_title', $API->process_field_string($file->url_title) );
					$this->EE->db->set('description', $API->process_field_string($file->description) );
					$this->EE->db->set('category', ((isset($file->category) == true) ? $file->category : '') );
					$this->EE->db->set('cifield_1', $API->process_field_string($file->cifield_1) );
					$this->EE->db->set('cifield_2', $API->process_field_string($file->cifield_2) );
					$this->EE->db->set('cifield_3', $API->process_field_string($file->cifield_3) );
					$this->EE->db->set('cifield_4', $API->process_field_string($file->cifield_4) );
					$this->EE->db->set('cifield_5', $API->process_field_string($file->cifield_5) );
					$this->EE->db->where('filename', $file->filename);
					$this->EE->db->where('entry_id', $file->entry_id);
					$this->EE->db->where('filesize', $file->filesize);
					$this->EE->db->where('is_draft', 1);
					$this->EE->db->update('exp_channel_images');

					continue;
				}

				if ($file->image_id > 0)
				{
					continue;
				}

				//Extension
				$extension = substr( strrchr($file->filename, '.'), 1);

				// Mime type
				$filemime = 'image/jpeg';
				if ($extension == 'png') $filemime = 'image/png';
				elseif ($extension == 'gif') $filemime = 'image/gif';

				// Check for link_image_id
				if (isset($file->link_image_id) == FALSE) $file->link_image_id = 0;
				$file->link_entryid = 0;
				$file->link_channelid = 0;
				$file->link_fieldid = 0;

				// Parse Image Size
				$width=''; $height=''; $filesize='';

				// Lets grab original width/height/field_id/channel_id/entry_id
				if ($file->link_image_id > 0)
				{
					$imgquery = $this->EE->db->query("SELECT entry_id, field_id, channel_id, filesize, width, height FROM exp_channel_images WHERE image_id = {$file->link_image_id} ");
					$file->link_entryid = $imgquery->row('entry_id');
					$file->link_channelid = $imgquery->row('channel_id');
					$file->link_fieldid = $imgquery->row('field_id');
					$width = $imgquery->row('width');
					$height = $imgquery->row('height');
					$filesize = $imgquery->row('filesize');
					$mt = $imgquery->row('sizes_metadata');
					if (is_string($mt) == FALSE) $mt = ''; // Some installs get weird mysql errors
				}
				else
				{
					$width = isset($metadata[$file->filename]['width']) ? $metadata[$file->filename]['width'] : 0;
					$height = isset($metadata[$file->filename]['height']) ? $metadata[$file->filename]['height'] : 0;
					$filesize = isset($metadata[$file->filename]['size']) ? $metadata[$file->filename]['size'] : 0;

					// -----------------------------------------
					// Parse Size Metadata!
					// -----------------------------------------
					$mt = '';
					foreach($settings['action_groups'] as $group)
					{
						$name = strtolower($group['group_name']);
						$size_filename = str_replace('.'.$extension, "__{$name}.{$extension}", $file->filename);

						$mt .= $name.'|' . implode('|', $metadata[$size_filename]) . '/';
					}
				}

				// Check URL Title
				if (isset($file->url_title) OR $file->url_title == FALSE)
				{
					$file->url_title = url_title(trim(strtolower($file->title)));
				}

				// -----------------------------------------
				// New File
				// -----------------------------------------
				$data = array(	'site_id'	=>	$this->site_id,
								'entry_id'	=>	$entry_id,
								'channel_id'=>	$channel_id,
								'member_id'=>	$this->EE->session->userdata['member_id'],
								'is_draft'	=>	$is_draft,
								'link_image_id' => $file->link_image_id,
								'link_entry_id' => $file->link_entryid,
								'link_channel_id' => $file->link_channelid,
								'link_field_id' => $file->link_fieldid,
								'upload_date' => $this->EE->localize->now,
								'field_id'=>	$field_id,
								'image_order'	=>	$order,
								'filename'	=>	$file->filename,
								'extension' =>	$extension,
								'mime'		=>	$filemime,
								'filesize'	=>	$filesize,
								'width'		=>	$width,
								'height'	=>	$height,
								'title'		=>	$API->process_field_string($file->title),
								'url_title'	=>	$API->process_field_string($file->url_title),
								'description' => $API->process_field_string($file->description),
								'category' 	=>	(isset($file->category) == true) ? $file->category : '',
								'cifield_1'	=>	$API->process_field_string($file->cifield_1),
								'cifield_2'	=>	$API->process_field_string($file->cifield_2),
								'cifield_3'	=>	$API->process_field_string($file->cifield_3),
								'cifield_4'	=>	$API->process_field_string($file->cifield_4),
								'cifield_5'	=>	$API->process_field_string($file->cifield_5),
								'cover'		=>	$file->cover,
								'sizes_metadata' => $mt,
								'iptc'		=>	$file->iptc,
								'exif'		=>	$file->exif,
								'xmp'		=>	$file->xmp,
							);

				$this->EE->db->insert('exp_channel_images', $data);
			}
		}

		// -----------------------------------------
		// WYGWAM
		// -----------------------------------------

		// Which field_group is assigned to this channel?
		$query = $this->EE->db->select('field_group')->from('exp_channels')->where('channel_id', $channel_id)->get();
		if ($query->num_rows() == 0) return;
		$field_group = $query->row('field_group');

		// Which fields are WYGWAM/wyvern AND Textarea
		$this->EE->db->select('field_id');
		$this->EE->db->from('exp_channel_fields');
		$this->EE->db->where('group_id', $field_group);
		$this->EE->db->where('field_type', 'wygwam');
		$this->EE->db->or_where('field_type', 'wyvern');
		$this->EE->db->or_where('field_type', 'textarea');
		$this->EE->db->or_where('field_type', 'rte');
		$this->EE->db->or_where('field_type', 'editor');
		$query = $this->EE->db->get();
		if ($query->num_rows() == 0) return;

		// Harvest all of them
		$fields = array();

		foreach ($query->result() as $row)
		{
			$fields[] = 'field_id_' . $row->field_id;
		}

		if (count($fields) > 0)
		{
			// Grab them!
			foreach ($fields as $field)
			{
				$this->EE->db->set($field, "
				 REPLACE(
				 	REPLACE(
				 		REPLACE(
				 			REPLACE({$field}, 'temp_dir=yes', ''),
				 		'temp_dir=yes', ''),
					'd={$key}&amp;', 'd={$entry_id}&amp;'),
				'd={$key}&', 'd={$entry_id}&')

				", FALSE);
				$this->EE->db->where('entry_id', $entry_id);
				$this->EE->db->update('exp_channel_data');
			}

		}

		// Delete old dirs
		$API->clean_temp_dirs($field_id);

		//preg_match_all('/\< *[img][^\>]*[src] *= *[\"\']{0,1}([^\"\'\ >]*)/i', $field, $matches);

		// -----------------------------------------
		// Just to be sure (if save_data_in_field is yes, we could overwrite previous data..)
		// -----------------------------------------
		if ( (isset($settings['save_data_in_field']) == FALSE || $settings['save_data_in_field'] == 'no') && $is_draft == 0 )
		{
			$query = $this->EE->db->select('image_id')->from('exp_channel_images')->where('field_id', $field_id)->where('entry_id', $entry_id)->where('is_draft', 0)->get();
			if ($query->num_rows() == 0) $this->EE->db->set('field_id_'.$field_id, '');
			else $this->EE->db->set('field_id_'.$field_id, 'ChannelImages');
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->update('exp_channel_data');
		}

		return;
	}

	// ********************************************************************************* //

	/**
	*	======================
	*	function zenbu_display
	*	======================
	*	Set up display in entry result cell
	*
	*	@param	$entry_id			int		The entry ID of this single result entry
	*	@param	$channel_id			int		The channel ID associated to this single result entry
	*	@param	$data				array	Raw data as found in database cell in exp_channel_data
	*	@param	$table_data			array	Data array usually retrieved from other table than exp_channel_data
	*	@param	$field_id			int		The ID of this field
	*	@param	$settings			array	The settings array, containing saved field order, display, extra options etc settings
	*	@param	$rules				array	An array of entry filtering rules
	*	@param	$upload_prefs		array	An array of upload preferences (optional)
	*	@param 	$installed_addons	array	An array of installed addons and their version numbers (optional)
	*	@param	$fieldtypes			array	Fieldtype of available fieldtypes: id, name, etc (optional)
	*	@return	$output		The HTML used to display data
	*/
	public function zenbu_display($entry_id, $channel_id, $field_data, $ch_img_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons)
	{
		$output = '&nbsp;';

		if (isset($ch_img_data['entries'][$entry_id][$field_id]) === FALSE) return $output;
		if (isset($ch_img_data['field_settings'][$field_id]['channel_images']['small_preview']) === FALSE) return $output;

		$small_preview = $ch_img_data['field_settings'][$field_id]['channel_images']['small_preview'];
		$big_preview = $ch_img_data['field_settings'][$field_id]['channel_images']['big_preview'];

		$max = 999;
		$extra_options = $settings['setting'][$channel_id]['extra_options'];
		if (isset($extra_options['field_'.$field_id]['ci_img_show_cover']) === TRUE && $extra_options['field_'.$field_id]['ci_img_show_cover'] == 'yes')
		{
			$max = 1;
		}

		foreach ($ch_img_data['entries'][$entry_id][$field_id] as $count => $image)
		{
			if ($max == $count) break;

			if ($image->link_image_id >= 1)
			{
				$image->entry_id = $image->link_entry_id;
				$image->field_id = $image->link_field_id;
			}

			$act_url_params = "&amp;fid={$image->field_id}&amp;d={$image->entry_id}";

			// Display SIzes URL
			$small_filename = str_replace('.'.$image->extension, "__{$small_preview}.{$image->extension}", urlencode($image->filename) );
			$big_filename = str_replace('.'.$image->extension, "__{$big_preview}.{$image->extension}", urlencode($image->filename) );

			$image->small_img_url = "{$ch_img_data['preview_url']}&amp;f={$small_filename}{$act_url_params}";
			$image->big_img_url = "{$ch_img_data['preview_url']}&amp;f={$big_filename}{$act_url_params}";

			$output .= anchor($image->big_img_url, "<img src='{$image->small_img_url}' width='".$this->EE->config->item('ci_image_preview_size')."' style='margin-right:5px;margin-bottom:5px;'>", 'class="fancybox" rel="ci_img_'.$entry_id.'" title="'.$image->title.'"');
		}

		return $output;
	}

	// ********************************************************************************* //

	/**
	*	=============================
	*	function zenbu_get_table_data
	*	=============================
	*	Retrieve data stored in other database tables
	*	based on results from Zenbu's entry list
	*	@uses	Instead of many small queries, this function can be used to carry out
	*			a single query of data to be later processed by the zenbu_display() method
	*
	*	@param	$entry_ids				array	An array of entry IDs from Zenbu's entry listing results
	*	@param	$field_ids				array	An array of field IDs tied to/associated with result entries
	*	@param	$channel_id				int		The ID of the channel in which Zenbu searched entries (0 = "All channels")
	*	@param	$output_upload_prefs	array	An array of upload preferences
	*	@param	$settings				array	The settings array, containing saved field order, display, extra options etc settings
	*	@param	$rel_array				array	A simple array useful when using related entry-type fields (optional)
	*	@return	$output					array	An array of data (typically broken down by entry_id then field_id) that can be used and processed by the zenbu_display() method
	*/
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id, $output_upload_prefs, $settings)
	{
		$output = array();
		if(empty($entry_ids) || empty($field_ids) || empty($output_upload_prefs) || empty($channel_id))
		{
			return $output;
		}
		$output['preview_url'] = $this->EE->image_helper->get_router_url('url', 'simple_image_url');
		$output['entries'] = array();
		$output['field_settings'] = array();

		// Get channel images field settings
		$this->EE->db->select(array("field_id", "field_settings"));
		$this->EE->db->from("exp_channel_fields");
		$this->EE->db->where("field_type", "channel_images");
		$this->EE->db->where_in("field_id", $field_ids);
		$field_settings_query = $this->EE->db->get();

		if($field_settings_query->num_rows() > 0)
		{
			foreach($field_settings_query->result_array() as $row)
			{
				$output['field_settings'][$row['field_id']] = unserialize(base64_decode($row['field_settings']));
			}
		}


		// Perform the query
		$this->EE->db->select('field_id, entry_id, filename, extension, link_image_id, link_entry_id, link_field_id, title');
		$this->EE->db->from('exp_channel_images');
		$this->EE->db->where_in("entry_id", $entry_ids);
		$this->EE->db->where_in("field_id", $field_ids);
		$this->EE->db->where("channel_id", $channel_id);
		$this->EE->db->order_by("cover", "desc");
		$this->EE->db->order_by("image_order", "asc");
		$query = $this->EE->db->get();

		// Create array for output
		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				if (isset($output['entries'][$row->entry_id][$row->field_id]) === FALSE) $output['entries'][$row->entry_id][$row->field_id] = array();
				$output['entries'][$row->entry_id][$row->field_id][] = $row;
			}
		}

		return $output;
	}

	// ********************************************************************************* //

	/**
	*	===================================
	*	function zenbu_field_extra_settings
	*	===================================
	*	Set up display for this fieldtype in "display settings"
	*
	*	@param	$table_col			string	A Zenbu table column name to be used for settings and input field labels
	*	@param	$channel_id			int		The channel ID for this field
	*	@param	$extra_options		array	The Zenbu field settings, used to retieve pre-saved data
	*	@return	$output		The HTML used to display setting fields
	*/
	public function zenbu_field_extra_settings($table_col, $channel_id, $extra_options)
	{

		$option_label_array = array(
			'ci_img_show_cover' => $this->EE->lang->line('ci:zenbu_show_cover'),
		);

		foreach($option_label_array as $label => $lang_string)
		{
			$checked = (isset($extra_options[$label])) ? TRUE : FALSE;
			$output[$label] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.']['.$label.']', 'yes', $checked).'&nbsp;'.$lang_string).'<br />';
		}

		return $output;
	}

	// ********************************************************************************* //

	/**
	*	===================================
	*	function zenbu_result_query
	*	===================================
	*	Extra queries to be intergrated into main entry result query
	*
	*	@param	$rules				int		An array of entry filtering rules
	*	@param	$field_id			array	The ID of this field
	*	@param	$fieldtypes			array	$fieldtype data
	*	@param	$already_queried	bool	Used to avoid using a FROM statement for the same field twice
	*	@return					A query to be integrated with entry results. Should be in CI Active Record format ($this->EE->db->…)
	*/
	public function zenbu_result_query($rules = array(), $field_id = "", $fieldtypes, $already_queried = FALSE)
	{
		if(empty($rules))
		{
			return;
		}

		if($already_queried === FALSE)
		{
			$this->EE->db->from("exp_channel_images");
		}

		$this->EE->db->where("exp_channel_images.field_id", $field_id);
		$col_query = $this->EE->db->query("/* Zenbu: Show columns for Channel Images */\nSHOW COLUMNS FROM exp_channel_images");
		$concat = "";
		$where_in = array();
		$db_columns = array("filename", "title", "description", "category");

		if($col_query->num_rows() > 0)
		{
			foreach($col_query->result_array() as $row)
			{

				if(in_array($row['Field'], $db_columns))
				{
					$concat .= 'exp_channel_images.'.$row['Field'].', ';
				}

			}
			$concat = substr($concat, 0, -2);
		}

		if( ! empty($concat))
		{
			// Find entry_ids that have the keyword
			foreach($rules as $rule)
			{
				$rule_field_id = (strncmp($rule['field'], 'field_', 6) == 0) ? substr($rule['field'], 6) : 0;
				if(isset($fieldtypes['fieldtype'][$rule_field_id]) && $fieldtypes['fieldtype'][$rule_field_id] == "channel_images")
				{
					$keyword = $rule['val'];

					$keyword_query = $this->EE->db->query("/* Zenbu: Search Channel Images */\nSELECT entry_id FROM exp_channel_images WHERE \nCONCAT_WS(',', ".$concat.") \nLIKE '%".$this->EE->db->escape_like_str($keyword)."%'");
					$where_in = array();
					if($keyword_query->num_rows() > 0)
					{
						foreach($keyword_query->result_array() as $row)
						{
							$where_in[] = $row['entry_id'];
						}
					}
				} // if
			} // foreach

			// If $keyword_query has hits, $where_in should not be empty.
			// In that case finish the query
			if( ! empty($where_in))
			{
				if($rule['cond'] == "doesnotcontain")
				{
					// …then query entries NOT in the group of entries
					$this->EE->db->where_not_in("exp_channel_titles.entry_id", $where_in);
				} else {
					$this->EE->db->where_in("exp_channel_titles.entry_id", $where_in);
				}
			} else {
			// However, $keyword_query has no hits (like on an unexistent word), $where_in will be empty
			// Send no results for: "search field containing this unexistent word".
			// Else, just show everything, as obviously all entries will not contain the odd word
				if($rule['cond'] == "contains")
				{
					$where_in[] = 0;
					$this->EE->db->where_in("exp_channel_titles.entry_id", $where_in);
				}
			}

		} // if
	}
}

/* End of file ft.channel_images.php */
/* Location: ./system/expressionengine/third_party/channel_images/ft.channel_images.php */
