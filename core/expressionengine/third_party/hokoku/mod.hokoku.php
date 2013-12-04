<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Hokoku Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Nicolas Bottari
 * @link		http://nicolasbottari.com/expressionengine_cms/hokoku
 */

class Hokoku {
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// ----------------------------------------------------------------

	public function export($limit = 100, $perpage = 0, $total_results = 0)
	{
		// Avoids errors with BASE being undefined when loading Zenbu class directly
		if ( ! defined('BASE')) define('BASE', SELF);

		$profile_id			= $this->EE->TMPL->fetch_param('profile_id');
		$rule_id			= $this->EE->TMPL->fetch_param('search_id');
		$member_id			= $this->EE->TMPL->fetch_param('member_id');
		$filename_override	= $this->EE->TMPL->fetch_param('filename');
		$output_to_screen	= $this->EE->TMPL->fetch_param('output_to_screen') ? $this->EE->TMPL->fetch_param('output_to_screen') : 'n';
		
		if($profile_id === FALSE || $rule_id === FALSE)
		{
			return '';
		}

		$this->EE->load->helper('html');
		$this->EE->load->helper('file');
		$this->EE->load->helper('output');

		//	----------------------------------------
		//	Validate the member_id
		//	Needed to know which Zenbu display to use
		//	----------------------------------------
		if( $member_id !== FALSE && ! empty($member_id) && is_numeric($member_id))
		{
			
			$this->EE->session->set_cache('zenbu', 'member_id', $member_id);
		
		} else {
			
			// If logged out and member_id="" is not used, exit  
			if($this->EE->session->userdata['group_id'] == 3) // 3: Guests
			{
				return lang('not_logged_in_member_id_required');
			}

			// Default to the currently logged in user
			$this->EE->session->set_cache('zenbu', 'member_id', $this->EE->session->userdata['group_id']);
		}

		//	----------------------------------------
		//	Get export profile data
		//	---------------------------------------- 
		if( ! $this->EE->session->cache('hokoku', 'profile_data') )
		{
			$this->EE->load->add_package_path(PATH_THIRD.'hokoku');
			
			// Load mcp to give us access to hokoku_get and hokoku_pack, which extends mcp	
			if(read_file(PATH_THIRD.'hokoku/mcp.hokoku.php') !== FALSE){
				
				require_once PATH_THIRD.'hokoku/mcp.hokoku.php';
				$this->EE->load->model('hokoku_get');
				$this->EE->load->model('hokoku_pack');
				$this->EE->load->model('hokoku_db');
				
			}

			$profile_data = $this->EE->hokoku_get->_get_export_profiles($profile_id);
			$profile_data = $profile_data['by_profile_id'][$profile_id];

			// Filename override
			if($filename_override !== FALSE && ! empty($filename_override))
			{
				$profile_data['export_filename'] = $filename_override;
			}

			$this->EE->session->set_cache('hokoku', 'profile_data', $profile_data);
		
		} else {
		
			$profile_data = $this->EE->session->cache('hokoku', 'profile_data');

		}

		//	----------------------------------------
		//	Process filename and export path
		//	----------------------------------------
		$export_format			= $profile_data['export_format'] ? '.' . $profile_data['export_format'] : '.txt';
		$export_filename 		= parse_filename($profile_data['export_filename']) . $export_format;
		$path_to_file			= $this->EE->hokoku_get->_get_cache_destination() . $export_filename;

		/**
		*	==============================	
		*	Loading Zenbu index method
		* 	==============================
		*/
		$this->EE->load->add_package_path(PATH_THIRD.'zenbu');
		
		$zenbu_class = 'Zenbu_mcp';
		
		if( ! class_exists($zenbu_class))
		{
			
			if(read_file(PATH_THIRD.'zenbu/mcp.zenbu.php') !== FALSE){
				
				require_once PATH_THIRD.'zenbu/mcp.zenbu.php';
				
			}
		}
		
		if(class_exists($zenbu_class))
		{
			$zenbu 	= new $zenbu_class();
			$this->EE->load->model('zenbu_get');

			//	----------------------------------------
			//	Retrieve the filter rules
			//	----------------------------------------
			if( ! $this->EE->session->cache('hokoku', 'rules') )
			{
				$rules = $this->EE->zenbu_get->_get_search_rules($rule_id);
				$this->EE->session->set_cache('hokoku', 'rules', $rules);
			} else {
				$rules = $this->EE->session->cache('hokoku', 'rules');
			}

			//	----------------------------------------
			// 	Store rules as session variable
			//	----------------------------------------
			// 	Zenbu will pick this up and consider the rules in its calculations
			if (session_id() == '')
			{
				session_start();
			}
			
			$_SESSION['zenbu']['rule'] = serialize($rules); 
			
			//	----------------------------------------
			//	Get Zenbu result array
			//	----------------------------------------
			$vars = $zenbu->index($limit, $perpage);
		}


		$total_results = $this->EE->session->cache('zenbu', 'total_results');

		$final_query = $limit + $perpage >= $total_results ? TRUE : FALSE;

		/**
		* 	==============================
		* 	END of Zenbu loading
		*/
	

		switch($export_format)
		{
			case '.csv':
				$vars = $this->EE->hokoku_pack->pack_csv($vars, $perpage, $final_query);
			break;
			case '.html':
				$vars = $this->EE->hokoku_pack->pack_html($vars, $perpage, $final_query);
			break;
			case '.json':
				$vars = $this->EE->hokoku_pack->pack_json($vars, $perpage);
			break;
		}
		
		$perpage = $limit + $perpage;
		
		if($perpage < $total_results)
		{

			// Continue exporting
			return $this->export($limit, $perpage, $total_results);

		} else {
			
			//	----------------------------------------
			//	Clean up old progress records
			//	----------------------------------------
			$this->EE->hokoku_db->purge_old_progress_records();

			if(AJAX_REQUEST)
			{
			
				// Reponse for ajax request
				return base64_encode($export_filename);
			
			} else {

				//	----------------------------------------
				//	Response for non-ajax request
				//	----------------------------------------

				// Output to screen
				if( in_array($output_to_screen, array('y', 'yes', 'on', 'enable', 'enabled', 'oui', 'hai')) )
				{
					if(read_file($path_to_file))
					{

						$output = read_file($path_to_file); // Read the file's contents
						return $output;

					} else {

						$output = 'Cannot read file.';
						return $output;

					}
					
				}

				// Output to browser
				$this->EE->load->helper('download');
				$filedata = read_file($path_to_file); // Read the file's contents
				force_download($export_filename, $filedata);
			
			}
		}

	}
	
}
/* End of file mod.hokoku.php */
/* Location: /system/expressionengine/third_party/hokoku/mod.hokoku.php */