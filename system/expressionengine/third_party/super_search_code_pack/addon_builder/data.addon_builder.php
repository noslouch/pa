<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Addon Builder - Data Model
 *
 * Parent data model class (legacy, we want to move to real models)
 *
 * @package		Solspace:Addon Builder
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/
 * @license		http://www.solspace.com/license_agreement/
 * @version		1.3.2
 * @filesource 	addon_builder/data.addon_builder.php
 */

if ( ! class_exists('Addon_builder_super_search_code_pack'))
{
	require_once 'addon_builder.php';
}

class Addon_builder_data_super_search_code_pack
{
	/**
	 * local cache array
	 * @var array
	 */
	public $cached			= array();

	/**
	 * converts EE 1.x nomenclature to EE 2.x
	 * @deprecated no longer used, but some addons call it
	 * @var array
	 * @see translate_keys
	 */
	public $nomenclature	= array(
		'site_weblog_preferences'	=> 'site_channel_preferences',
		'can_admin_weblogs'			=> 'can_admin_channels',
		'weblog_id'					=> 'channel_id',
		'blog_name'					=> 'channel_name',
		'blog_title'				=> 'channel_title',
		'blog_url'					=> 'channel_url',
		'blog_description'			=> 'channel_description',
		'blog_lang'					=> 'channel_lang',
		'weblog_max_chars'			=> 'channel_max_chars',
		'weblog_notify'				=> 'channel_notify',
		'weblog_require_membership'	=> 'channel_require_membership',
		'weblog_html_formatting'	=> 'channel_html_formatting',
		'weblog_allow_img_urls'		=> 'channel_allow_img_urls',
		'weblog_auto_link_urls'		=> 'channel_auto_link_urls',
		'weblog_notify_emails'		=> 'channel_notify_emails',
		'field_pre_blog_id'			=> 'field_pre_channel_id'
	);


	/**
	 * names of methods from AOB that should NOT be used by call
	 * if the method already exists in this class, then the point is moot
	 * because __call will not be activated
	 * @var array
	 */
	private $aob_non_static_friendly = array(
		'actions',
		'database_version',
		'preference',
		'extensions_enabled',
		'ee_cp_view',
		'retrieve_remote_file',
		'cycle',
		'output',
		'build_crumbs',
		'add_crumb',
		'view',
		'file_view'
	);

	/**
	 * instance of main addon builder for __calls functions
	 * @var object
	 * @see __construct
	 * @see __call
	 */
	public $parent_aob_instance;

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object	this should be an instance of the parent object
	 * @return	null
	 */

	function __construct($parent_aob_instance = FALSE)
	{
		//this way we have a pointer to AOB
		//however, since this gets called from the child constructor,
		//AOB has to point itself to it.
		//this is just here in case
		if (is_object($parent_aob_instance))
		{
			$this->parent_aob_instance =& $parent_aob_instance;
		}

		$this->EE =& get_instance();

		//make an attempted to get the proper classname

		$name = preg_replace('/^Addon_builder_data_/ms', '', __CLASS__);
		$this->lower_name = strtolower($name);
		$this->class_name = ucfirst($this->lower_name);
		$this->addon_path = PATH_THIRD . $this->lower_name . '/';

		//--------------------------------------------
		// Prepare Caching
		//--------------------------------------------

		//helps clean this ugly ugly code
		$s = 'solspace';
		$g = 'global';
		$c = 'cache';
		$b = 'addon_builder';
		$a = 'addon';

		//no sessions? lets use global until we get here again
		if ( ! isset($this->EE->session) OR
			! is_object($this->EE->session))
		{
			if ( ! isset($GLOBALS[$s][$c][$b][$a][$this->lower_name]))
			{
				$GLOBALS[$s][$c][$b][$a][$this->lower_name] = array();
			}

			$this->cache =& $GLOBALS[$s][$c][$b][$a][$this->lower_name];

			if ( ! isset($GLOBALS[$s][$c][$b][$g]) )
			{
				$GLOBALS[$s][$c][$b][$g] = array();
			}

			$this->global_cache =& $GLOBALS[$s][$c][$b][$g];
		}
		//sessions?
		else
		{
			//been here before?
			if ( ! isset($this->EE->session->cache[$s][$b][$a][$this->lower_name]))
			{
				//grab pre-session globals, and only unset the ones for this addon
				if ( isset($GLOBALS[$s][$c][$b][$a][$this->lower_name]))
				{
					$this->EE->session->cache[$s][$b][$a][$this->lower_name] =
						$GLOBALS[$s][$c][$b][$a][$this->lower_name];

					//cleanup, isle 5
					unset($GLOBALS[$s][$c][$b][$a][$this->lower_name]);
				}
				else
				{
					$this->EE->session->cache[$s][$b][$a][$this->lower_name] = array();
				}
			}

			//check for solspace-wide globals
			if ( ! isset($this->EE->session->cache[$s][$b][$g]) )
			{
				if (isset($GLOBALS[$s][$c][$b][$g]))
				{
					$this->EE->session->cache[$s][$b][$g] = $GLOBALS[$s][$c][$b][$g];

					unset($GLOBALS[$s][$c][$b][$g]);
				}
				else
				{
					$this->EE->session->cache[$s][$b][$g] = array();
				}
			}

			$this->global_cache =& $this->EE->session->cache[$s][$b][$g];
			$this->cache 		=& $this->EE->session->cache[$s][$b][$a][$this->lower_name];
		}
	}
	// END __construct


	// --------------------------------------------------------------------

	/**
	 * __call
	 *
	 * intercepts calls to methods that do not exist. Magic ;)
	 * if the method is in AOB, return it
	 *
	 * @access	public
	 * @return	string
	 */

	public function __call($method, $args)
	{
		//attempt to first call it on the parent AOB object
		if (isset($this->parent_aob_instance) AND
			is_object($this->parent_aob_instance) AND
			is_callable(array($this->parent_aob_instance, $method)))
		{
			return call_user_func_array(array($this->parent_aob_instance, $method), $args);
		}

		//we have an array of items that should NOT be attempted to call
		if ( ! in_array($method, $this->aob_non_static_friendly) AND
			 is_callable(array('Addon_builder_super_search_code_pack', $method)))
		{
			return call_user_func_array(array('Addon_builder_super_search_code_pack', $method), $args);
		}
	}
	//end __call


	// --------------------------------------------------------------------

	/**
	 * Translate Keys from 2.0 to 1.0
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 * @deprecated		was just for EE 1.x usage
	 */

	function translate_keys($data = array())
	{
		return $data;
	}
	/* END translate_keys() */


	// --------------------------------------------------------------------

	/**
	 * Get author id from entry id
	 *
	 * @access	public
	 * @param	params	entry id
	 * @return	integer
	 */

	function get_author_id_from_entry_id( $entry_id = '' )
	{
		if ( is_numeric( $entry_id ) === FALSE ) return FALSE;

		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->get_channel_id_from_entry_id( $entry_id );

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		return $this->cached[$cache_name][$cache_hash];
	}
	/*	End get author id from entry id */


	// --------------------------------------------------------------------

	/**
	 * Get channel id from entry id
	 *
	 * @access	public
	 * @param	params	entry id
	 * @return	integer
	 */

	function get_channel_id_from_entry_id( $entry_id = '' )
	{
		if ( is_numeric( $entry_id ) === FALSE ) return FALSE;

		// --------------------------------------------
		//  Prep Cache, Return if Set
		// --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = FALSE;

		// --------------------------------------------
		//  Grab from DB
		// --------------------------------------------

		$query	= $this->EE->db->query(
			"SELECT author_id, " . $this->sc->db->channel_id . " AS channel_id
			 FROM	" . $this->sc->db->channel_titles . "
			 WHERE	entry_id = '" . $this->EE->db->escape_str( $entry_id ) . "'
			 LIMIT 1"
		);

		if ( $query->num_rows > 0 )
		{
			$this->cached[$cache_name][$cache_hash]	= $query->row('channel_id');
			$this->cached['get_author_id_from_entry_id'][$cache_hash]	= $query->row('author_id');
		}

		return $this->cached[$cache_name][$cache_hash];
	}

	/*	End get channel id from entry id */

	// --------------------------------------------------------------------

	/**
	 * List of Installations Sites
	 *
	 * @access	public
	 * @param	params	MySQL clauses, if necessary
	 * @return	array
	 */

	function get_sites()
	{
		//--------------------------------------------
		// SuperAdmins Alredy Have All Sites
		//--------------------------------------------

		if (isset($this->EE->session) AND
			is_object($this->EE->session) AND
			isset($this->EE->session->userdata['group_id']) AND
			$this->EE->session->userdata['group_id'] == 1 AND
			isset($this->EE->session->userdata['assigned_sites']) AND
			is_array($this->EE->session->userdata['assigned_sites']))
		{
			return $this->EE->session->userdata['assigned_sites'];
		}

		//--------------------------------------------
		// Prep Cache, Return if Set
		//--------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//--------------------------------------------
		// Perform the Actual Work
		//--------------------------------------------

		if ($this->EE->config->item('multiple_sites_enabled') == 'y')
		{
			$sites_query = $this->EE->db->query(
				"SELECT 	site_id, site_label
				 FROM 		exp_sites
				 ORDER BY 	site_label"
			);
		}
		else
		{
			$sites_query = $this->EE->db->query(
				"SELECT site_id, site_label
				 FROM 	exp_sites
				 WHERE 	site_id = '1'"
			);
		}

		foreach($sites_query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['site_id']] = $row['site_label'];
		}

		//--------------------------------------------
		// Return Data
		//--------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}
	//END get_sites()


	// --------------------------------------------------------------------

	/**
	 * List of All Member Fields
	 *
	 * @access	public
	 * @return	array
	 */

	function get_member_fields()
	{
		//--------------------------------------------
		// Prep Cache, Return if Set
		//--------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//--------------------------------------------
		// Perform the Actual Work
		//--------------------------------------------


		$query = $this->EE->db->query(
			"/* Solspace mfields */
			SELECT	m_field_id,
					m_field_name,
					m_field_label,
					m_field_type,
					m_field_list_items,
					m_field_required,
					m_field_public,
					m_field_fmt
			FROM exp_member_fields
		");

		foreach ($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['m_field_name']] = array(
				'id'		=> $row['m_field_id'],
				'label'		=> $row['m_field_label'],
				'type'		=> $row['m_field_type'],
				'list'		=> $row['m_field_list_items'],
				'required'	=> $row['m_field_required'],
				'public'	=> $row['m_field_public'],
				'format'	=> $row['m_field_fmt']
			);
		}

		//--------------------------------------------
		// Return Data
		//--------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_sites
}
// END CLASS Addon_builder_data
