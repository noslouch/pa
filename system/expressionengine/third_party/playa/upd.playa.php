<?php if (! defined('BASEPATH')) exit('Invalid file request');


if (! defined('PATH_THIRD')) define('PATH_THIRD', EE_APPPATH.'third_party/');
require_once PATH_THIRD.'playa/config.php';


/**
 * Playa Update Class
 *
 * @package   Playa
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Playa_upd {

	var $version = PLAYA_VER;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Install
	 */
	function install()
	{
		$this->EE->db->insert('modules', array(
			'module_name'        => 'Playa',
			'module_version'     => PLAYA_VER,
			'has_cp_backend'     => 'n',
			'has_publish_fields' => 'n'
		));

		$this->EE->db->insert('actions', array(
			'class'  => 'Playa_mcp',
			'method' => 'filter_entries'
		));

		return TRUE;
	}

	/**
	 * Uninstall
	 */
	function uninstall()
	{
		$this->EE->db->where('module_name', 'Playa')->delete('modules');
		$this->EE->db->where('class', 'Playa_mcp')->delete('actions');

		return TRUE;
	}

}
