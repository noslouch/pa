<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_400
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
		// Drop sizes
		if ($this->EE->db->field_exists('sizes', 'channel_images') == TRUE)
		{
			$this->EE->dbforge->drop_column('channel_images', 'sizes');
		}

		// Add URL TITLE
		if ($this->EE->db->field_exists('url_title', 'channel_images') == FALSE)
		{
			$fields = array( 'url_title'=> array('type' => 'VARCHAR',	'constraint' => '250', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields, 'title');
		}

		// Add Filesize
		if ($this->EE->db->field_exists('filesize', 'channel_images') == FALSE)
		{
			$fields = array( 'filesize'=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0));
			$this->EE->dbforge->add_column('channel_images', $fields, 'mime');
		}

		// Add Width
		if ($this->EE->db->field_exists('width', 'channel_images') == FALSE)
		{
			$fields = array( 'width'=> array('type' => 'SMALLINT',	'unsigned' => TRUE, 'default' => 0));
			$this->EE->dbforge->add_column('channel_images', $fields, 'filesize');
		}

		// Add Height
		if ($this->EE->db->field_exists('height', 'channel_images') == FALSE)
		{
			$fields = array( 'height'=> array('type' => 'SMALLINT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'width');
		}

		// Add CIFIELD_1
		if ($this->EE->db->field_exists('cifield_1', 'channel_images') == FALSE)
		{
			$fields = array( 'cifield_1'=> array('type' => 'VARCHAR',	'constraint' => '250', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields);
		}

		// Add CIFIELD_2
		if ($this->EE->db->field_exists('cifield_2', 'channel_images') == FALSE)
		{
			$fields = array( 'cifield_2'=> array('type' => 'VARCHAR',	'constraint' => '250', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields);
		}

		// Add CIFIELD_3
		if ($this->EE->db->field_exists('cifield_3', 'channel_images') == FALSE)
		{
			$fields = array( 'cifield_3'=> array('type' => 'VARCHAR',	'constraint' => '250', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields);
		}

		// Add CIFIELD_4
		if ($this->EE->db->field_exists('cifield_4', 'channel_images') == FALSE)
		{
			$fields = array( 'cifield_4'=> array('type' => 'VARCHAR',	'constraint' => '250', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields);
		}

		// Add CIFIELD_5
		if ($this->EE->db->field_exists('cifield_5', 'channel_images') == FALSE)
		{
			$fields = array( 'cifield_5'=> array('type' => 'VARCHAR',	'constraint' => '250', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields);
		}

		// We need a new action
		$module = array('class' => 'Channel_images', 'method' => 'locked_image_url' );

		$this->EE->db->insert('actions', $module);
	}

	// ********************************************************************************* //

}

/* End of file 400.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/400.php */