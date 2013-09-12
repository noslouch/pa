<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_500
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		// Load dbforge
		$this->EE->load->dbforge();
	}

	// ********************************************************************************* //

	public function do_update()
	{
		// -----------------------------------------
		// Grab all channel images fields!
		// -----------------------------------------
		$query = $this->EE->db->query("SELECT field_id, field_settings FROM exp_channel_fields WHERE field_type = 'channel_images'");

		// -----------------------------------------
		// Loop over all fields
		// -----------------------------------------
		foreach ($query->result() as $field)
		{
			// New Settings Array
			$settings = array();

			// Parse Old Settings
			$oldsettings = unserialize(base64_decode($field->field_settings));

			// Simple check
			if (isset($oldsettings['channel_images']) == TRUE) continue;

			// -----------------------------------------
			// Simple Settings
			// -----------------------------------------
			$settings = $this->EE->config->item('ci_defaults');

			// Upload Location
			$settings['upload_location'] = 'local';

			// Location ID
			if (isset($oldsettings['ci_upload_location'])) $settings['locations']['local']['location'] = $oldsettings['ci_upload_location'];
			unset($oldsettings['ci_upload_location']);

			// Categories
			if (isset($oldsettings['ci_categories'])) $settings['categories'] = $oldsettings['ci_categories'];
			unset($oldsettings['ci_categories']);

			// Keep Original
			if (isset($oldsettings['ci_keep_original'])) $settings['keep_original'] = $oldsettings['ci_keep_original'];
			unset($oldsettings['ci_keep_original']);

			// Columns
			if (isset($oldsettings['ci_columns'])) $settings['columns'] = $oldsettings['ci_columns'];
			unset($oldsettings['ci_columns']);

			// Show Stored Images
			if (isset($oldsettings['ci_show_stored_images'])) $settings['show_stored_images'] = $oldsettings['ci_show_stored_images'];
			unset($oldsettings['ci_show_stored_images']);

			// Stored Image by Author
			if (isset($oldsettings['ci_stored_images_by_author'])) $settings['stored_images_by_author'] = $oldsettings['ci_stored_images_by_author'];
			unset($oldsettings['ci_stored_images_by_author']);

			// Image Limit
			if (isset($oldsettings['ci_image_limit'])) $settings['image_limit'] = $oldsettings['ci_image_limit'];
			unset($oldsettings['ci_image_limit']);

			// Allow Upsizing?
			$upsize = 'no';
			if (isset($oldsettings['ci_allow_upsizing']) && $oldsettings['ci_allow_upsizing'] == 'yes') $upsize = 'yes';
			unset($oldsettings['ci_allow_upsizing']);

			// -----------------------------------------
			// Sizes :O
			// -----------------------------------------
			$settings['action_groups'] = array();

			if (isset($oldsettings['ci_image_sizes']))
			{
				$count = 1;
				foreach ($oldsettings['ci_image_sizes'] as $size_name => $options)
				{
					$size = array();

					// Group Name?
					$size['group_name'] = $size_name;

					// WYSIWYG
					$size['wysiwyg'] = 'yes';

					// -----------------------------------------
					// Resize Action!
					// -----------------------------------------
					$size['actions']['resize_adaptive'] = array();
					$size['actions']['resize_adaptive']['step'] = 1;
					$size['actions']['resize_adaptive']['width'] = $options['width'];
					$size['actions']['resize_adaptive']['height'] = $options['height'];
					$size['actions']['resize_adaptive']['quality'] = $options['quality'];
					$size['actions']['resize_adaptive']['upsize'] = $upsize;

					// -----------------------------------------
					// Greyscale?
					// -----------------------------------------
					if (isset($options['greyscale']) == TRUE && $options['greyscale'] == 'y')
					{
						$size['actions']['greyscale'] = array();
						$size['actions']['greyscale']['step'] = 2;
					}

					// -----------------------------------------
					// Crop?
					// -----------------------------------------
					if (isset($options['crop']) == TRUE && $options['crop'] == 'y')
					{
						$size['actions']['resize'] = $size['actions']['resize_adaptive'];
						unset($size['actions']['resize_adaptive']);
					}

					// -----------------------------------------
					// Watermark?
					// -----------------------------------------
					if (isset($options['watermark']) == TRUE && $options['watermark'] == 'y' && isset($oldsettings['ci_watermark']) == TRUE && $oldsettings['ci_watermark']['type'] != 'none')
					{
						// Text Watermark?
						if ($oldsettings['ci_watermark']['type'] == 'text')
						{
							$size['actions']['watermark_text'] = $oldsettings['ci_watermark'];
							$size['actions']['watermark_text']['step'] = 3;
							unset($size['actions']['watermark_text']['overlay_path']);
							unset($size['actions']['watermark_text']['opacity']);
							unset($size['actions']['watermark_text']['x_transp']);
							unset($size['actions']['watermark_text']['y_transp']);
						}
						else if ($oldsettings['ci_watermark']['type'] == 'image')
						{
							$size['actions']['watermark_image'] = $oldsettings['ci_watermark'];
							$size['actions']['watermark_image']['step'] = 3;
							unset($size['actions']['watermark_image']['font_path']);
							unset($size['actions']['watermark_image']['font_size']);
							unset($size['actions']['watermark_image']['font_color']);
							unset($size['actions']['watermark_image']['shadow_color']);
							unset($size['actions']['watermark_image']['shadow_distance']);
						}
					}

					$settings['action_groups'][$count] = $size;
					$count++;
				}
			}
			unset($oldsettings['ci_image_sizes']);
			unset($oldsettings['ci_watermark']);

			// -----------------------------------------
			// Previews
			// -----------------------------------------
			if (isset($settings['action_groups'][1]))
			{
				$settings['small_preview'] = $settings['action_groups'][1]['group_name'];
				$settings['big_preview'] = $settings['action_groups'][1]['group_name'];
			}
			if (isset($settings['action_groups'][2]))
			{
				$settings['big_preview'] = $settings['action_groups'][2]['group_name'];
			}

			// -----------------------------------------
			// Put It Back
			// -----------------------------------------
			$oldsettings['channel_images'] = $settings;
			$oldsettings = base64_encode(serialize($oldsettings));

			$this->EE->db->set('field_settings', $oldsettings);
			$this->EE->db->where('field_id', $field->field_id);
			$this->EE->db->update('exp_channel_fields');

		}

		// -----------------------------------------
		// Add sizes_metadata Column
		// -----------------------------------------
		if ($this->EE->db->field_exists('sizes_metadata', 'channel_images') == FALSE)
		{
			$fields = array( 'sizes_metadata'	=> array('type' => 'VARCHAR',	'constraint' => '250', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields, 'cifield_5');
		}

		// -----------------------------------------
		// Add upload_date Column
		// -----------------------------------------
		if ($this->EE->db->field_exists('upload_date', 'channel_images') == FALSE)
		{
			$fields = array( 'upload_date'	=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'link_field_id');
		}

		// -----------------------------------------
		// Add new Action!
		// -----------------------------------------
		$query = $this->EE->db->query("SELECT action_id FROM exp_actions WHERE class = 'Channel_images' AND method = 'simple_image_url'");
		if ($query->num_rows() == 0)
		{
			$module = array('class' => 'Channel_images', 'method' => 'simple_image_url');
			$this->EE->db->insert('exp_actions', $module);
		}

		//exit();
	}

	// ********************************************************************************* //

}

/* End of file 500.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/500.php */