<?php if ( ! defined('EXT')) exit('No direct script access allowed');

 /**
 * Solspace - Code Pack
 *
 * @package		Solspace:Code Pack
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2009-2012, Solspace, Inc.
 * @link		http://www.solspace.com/docs/addon/c/Code_Pack/
 * @version		1.2.2
 * @filesource 	./system/expressionengine/third_party/code_pack/
 */

 /**
 * Code Pack - User Side
 *
 * @package 	Solspace:Code pack
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/code_pack/mod.code_pack.php
 */

require_once 'addon_builder/module_builder.php';

class Code_pack extends Module_builder_code_pack {

	var $return_data	= '';

	var $disabled		= FALSE;

    // --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	function Code_pack()
	{
		parent::Module_builder('code_pack');

        /** -------------------------------------
		/**  Module Installed and Up to Date?
		/** -------------------------------------*/

		if ($this->database_version() == FALSE OR $this->version_compare($this->database_version(), '<', CODE_PACK_VERSION))
		{
			$this->disabled = TRUE;

			trigger_error(ee()->lang->line('code_pack_module_disabled'), E_USER_NOTICE);
		}
	}
	/* END Code_pack() */

}
// END CLASS Code_pack