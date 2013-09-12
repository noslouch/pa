<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Addon Builder - Module Builder
 *
 * A class that helps with the building of ExpressionEngine Modules
 *
 * @package		Solspace:Addon Builder
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/
 * @license		http://www.solspace.com/license_agreement/
 * @version		1.3.2
 * @filesource 	addon_builder/module_builder.php
 */

if ( ! class_exists('Addon_builder_super_search'))
{
	require_once 'addon_builder.php';
}

class Module_builder_super_search extends Addon_builder_super_search
{

	public $module_actions		= array();
	public $hooks				= array();

	// Defaults for the exp_extensions fields
	public $extension_defaults	= array();

	public $base				= '';

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	public function __construct()
	{
		parent::__construct();

		// --------------------------------------------
		//  Default CP Variables
		// --------------------------------------------

		if (REQ == 'CP')
		{
			//BASE is not set until AFTER sessions_end,
			//and we don't want to clobber it.
			$base_const = defined('BASE') ? BASE :  SELF . '?S=0';

			//2.x adds an extra param for base
			if (substr($base_const, -4) != 'D=cp')
			{
				$base_const .= '&amp;D=cp';
			}

			// For 2.0, we have '&amp;D=cp' with BASE and
			//	we want pure characters, so we convert it
			$this->base	= str_replace('&amp;', '&', $base_const) .
					'		&C=addons_modules&M=show_module_cp&module=' .
							$this->lower_name;

			$this->cached_vars['page_crumb']			= '';
			$this->cached_vars['page_title']			= '';
			$this->cached_vars['base_uri']				= $this->base;
			$this->cached_vars['module_menu']			= array();
			$this->cached_vars['module_menu_highlight'] = 'module_home';
			$this->cached_vars['module_version'] 		= $this->version;

			// --------------------------------------------
			//  Default Crumbs for Module
			// --------------------------------------------

			$this->add_crumb(
				lang($this->lower_name.'_module_name'),
				$this->base
			);
		}

		// --------------------------------------------
		//  Module Installed and Up to Date?
		// --------------------------------------------

		if (REQ == 'PAGE' AND
			constant(strtoupper($this->lower_name).'_VERSION') !== NULL AND
			($this->database_version() == FALSE OR
			 $this->version_compare(
				$this->database_version(), '<',
				constant(strtoupper($this->lower_name).'_VERSION')
			 )
			)
		 )
		{
			$this->disabled = TRUE;

			if (empty($this->cache['disabled_message']) AND
				! empty($this->EE->lang->language[$this->lower_name.'_module_disabled']))
			{
				trigger_error(lang($this->lower_name.'_module_disabled'), E_USER_NOTICE);

				$this->cache['disabled_message'] = TRUE;
			}
		}
	}
	// END Module_builder_super_search()


	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

	public function default_module_install()
	{
		$this->install_module_sql();
		$this->update_module_actions();
		$this->update_extension_hooks();

		return TRUE;
	}
	// END default_module_install()


	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * Looks for an db.[module].sql file as well as the old
	 * [module].sql file in the module's folder
	 *
	 * @access	public
	 * @return	bool
	 */

	public function default_module_uninstall()
	{
		//get module id
		$query = $this->EE->db
					->select('module_id')
					->where('module_name', $this->class_name)
					->get('modules');

		$files = array(
			$this->addon_path . $this->lower_name.'.sql',
			$this->addon_path . 'db.'.$this->lower_name.'.sql'
		);

		$this->EE->load->dbforge();

		foreach($files as $file)
		{
			if (file_exists($file))
			{
				if (preg_match_all(
					"/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`([^`]+)`/",
					file_get_contents($file),
					$matches)
				)
				{
					foreach($matches[1] as $table)
					{
						$this->EE->dbforge->drop_table(
							preg_replace(
								"/^" . preg_quote($this->EE->db->dbprefix) . "/ims",
								'',
								trim($table)
							)
						);
					}
				}

				break;
			}
		}

		$this->EE->db
				->where('module_id', $query->row('module_id'))
				->delete('module_member_groups');

		$this->EE->db
				->where('module_name', $this->class_name)
				->delete('modules');

		$this->EE->db
				->where('class', $this->class_name)
				->delete('actions');

		$this->remove_extension_hooks();

		return TRUE;
	}
	// END default_module_uninstall()


	// --------------------------------------------------------------------

	/**
	 * Module Update
	 *
	 * @access	public
	 * @return	bool
	 */

	public function default_module_update()
	{
		$this->update_module_actions();
		$this->update_extension_hooks();

		unset($this->cache['database_version']);

		return TRUE;
	}
	// END default_module_update()


	// --------------------------------------------------------------------

	/**
	 * Install Module SQL
	 *
	 * Looks for an db.[module].sql file as well as the
	 * old [module].sql file in the module's folder
	 *
	 * @access	public
	 * @return	null
	 */

	public function install_module_sql()
	{
		$sql = array();

		// --------------------------------------------
		//  Our Install Queries
		// --------------------------------------------

		$files = array(
			$this->addon_path . $this->lower_name.'.sql',
			$this->addon_path . 'db.'.$this->lower_name.'.sql'
		);

		foreach($files as $file)
		{
			if (file_exists($file))
			{
				$sql = preg_split(
					"/;;\s*(\n+|$)/",
					file_get_contents($file),
					-1,
					PREG_SPLIT_NO_EMPTY
				);

				foreach($sql as $i => $query)
				{
					$sql[$i] = trim($query);
				}

				break;
			}
		}

		// --------------------------------------------
		//  Module Install
		// --------------------------------------------

		foreach ($sql as $query)
		{
			$this->EE->db->query($query);
		}
	}
	//END install_module_sql()


	// --------------------------------------------------------------------

	/**
	 * Module Actions
	 *
	 * Insures that we have all of the correct
	 * actions in the database for this module
	 *
	 * @access	public
	 * @return	array
	 */

	public function update_module_actions()
	{
		$exists	= array();

		$query	= $this->EE->db
					->select('method')
					->where('class', $this->class_name)
					->get('actions');

		foreach ( $query->result_array() AS $row )
		{
			$exists[] = $row['method'];
		}

		// --------------------------------------------
		//  Actions of Module Actions
		// --------------------------------------------

		$actions = ( is_array($this->module_actions) AND
					 count($this->module_actions) > 0) ?
						$this->module_actions :
						array();

		// --------------------------------------------
		//  Add Missing Actions
		// --------------------------------------------

		foreach(array_diff($actions, $exists) as $method)
		{
			$this->EE->db->insert(
				'exp_actions',
				array(
					'class'		=> $this->class_name,
					'method'	=> $method
				)
			);
		}

		// --------------------------------------------
		//  Delete No Longer Existing Actions
		// --------------------------------------------

		$leftovers = array_diff($exists, $actions);

		if( ! empty($leftovers))
		{

			$this->EE->db
				->where('class', $this->class_name)
				->where_in('method',$leftovers)
				->delete('actions');
		}
	}
	// END update_module_actions()


	// --------------------------------------------------------------------

	/**
	 * Set Encryption Key
	 *
	 * Insures that we have an encryption key set in the EE 2.x Configuration File
	 *
	 * @access	public
	 * @return	array
	 */

	public function set_encryption_key()
	{
		if ($this->EE->config->item('encryption_key') != '') return;

		$config = array(
			'encryption_key' => md5(
				$this->EE->db->username .
				$this->EE->db->password .
				rand()
			)
		);

		if (is_callable(array($this->EE->config, '_update_config')))
		{
			return $this->EE->config->_update_config($config);
		}
	}
	// END set_encryption_key()


	// --------------------------------------------------------------------

	/**
	 * Module Specific No Results Parsing
	 *
	 * Looks for (your_module)_no_results and uses that,
	 * otherwise it returns the default no_results conditional
	 *
	 *	@access		public
	 *	@return		string
	 */

	public function no_results()
	{
		if ( preg_match(
				"/".LD."if " .preg_quote($this->lower_name)."_no_results" .
					RD."(.*?)".LD.preg_quote('/', '/')."if".RD."/s",
				$this->EE->TMPL->tagdata,
				$match
			)
		)
		{
			return $match[1];
		}
		else
		{
			return $this->EE->TMPL->no_results();
		}
	}
	// END no_results()


	// ------------------------------------------------------------------------

	/**
	 * Sanitize Search Terms
	 *
	 * Filters a search string for security
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function sanitize_search_terms($str)
	{
		$this->EE->load->helper('search');
		return sanitize_search_terms($str);
	}
	// END sanitize_search_terms()
}
// END Module_builder Class
