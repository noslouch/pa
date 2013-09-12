<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_540
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
		// Add sizes_metadata Column
		// -----------------------------------------
		if ($this->EE->db->field_exists('iptc', 'channel_images') == FALSE)
		{
			$fields = array( 'iptc'	=> array('type' => 'TEXT') );
			$this->EE->dbforge->add_column('channel_images', $fields, 'sizes_metadata');
		}

		if ($this->EE->db->field_exists('exif', 'channel_images') == FALSE)
		{
			$fields = array( 'exif'	=> array('type' => 'TEXT') );
			$this->EE->dbforge->add_column('channel_images', $fields, 'iptc');
		}

		if ($this->EE->db->field_exists('xmp', 'channel_images') == FALSE)
		{
			$fields = array( 'xmp'	=> array('type' => 'TEXT') );
			$this->EE->dbforge->add_column('channel_images', $fields, 'exif');
		}

		//exit();
	}

	// ********************************************************************************* //

}

/* End of file 500.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/520.php */
