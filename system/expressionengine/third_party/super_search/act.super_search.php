<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Super Search - Actions
 *
 * Handles all form submissions and action requests.
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/super_search
 * @license		http://www.solspace.com/license_agreement
 * @version		2.0.6
 * @filesource	super_search/act.super_search.php
 */

require_once 'addon_builder/addon_builder.php';

class Super_search_actions extends Addon_builder_super_search
{
	var $errors				= array();

	var $module_preferences = array();

	// --------------------------------------------------------------------

	/**
	 * Clear cache
	 *
	 * Clear cache for a given site
	 *
	 * @access	public
	 * @return	bool
	 */

    function clear_cache ()
    {
		do
		{
			$this->EE->db->query(
				"DELETE FROM exp_super_search_cache
				WHERE site_id = ".$this->EE->config->item( 'site_id' )."
				LIMIT 1000 /* Super Search act.super_search.php clear_cache() */"
			);
		}
		while ( $this->EE->db->affected_rows() == 1000 );

		do
		{
			$this->EE->db->query(
				"DELETE FROM exp_super_search_history
				WHERE site_id = ".$this->EE->config->item( 'site_id' )."
				AND saved = 'n'
				AND cache_id NOT IN (
					SELECT cache_id
					FROM exp_super_search_cache
				)
				LIMIT 1000 /* Super Search act.super_search.php clear_cache() clear history */"
			);
		}
		while ( $this->EE->db->affected_rows() == 1000 );

		return TRUE;
    }

	//	End clear cache

	// --------------------------------------------------------------------

	/**
	 *	Database Character Set Switch
	 *
	 *	Used because the EE 1.x database was not UTF-8,
	 *	which was causing a problem with international
	 *	character support.  EE 2.x is magically delicious and UTF-8
	 *
	 * 	@deprecated this is just here until we remove all calls
	 *	@access		public
	 *	@param		string
	 *	@return		null
	 */

	public function db_charset_switch($type = 'utf-8')
	{

	}

	// End DB UTF-8 Switch

	// --------------------------------------------------------------------

	/**
	 *  Get the Preferences for This Module
	 *
	 * @access	public
	 * @return	array
	 */

	function module_preferences()
	{
	}

	//	END module_preferences()
}

/* END Class */