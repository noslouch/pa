<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	Pixel&Tonic's Assets field
*	@author	Pixel&tonic http://pixelandtonic.com
*	@link	http://pixelandtonic.com/assets
*	============================================
*	File assets.php
*	
*/

class Zenbu_assets_ft extends Assets_ft
{
	var $dropdown_type = "contains_doesnotcontain";
	
	/**
	*	Constructor
	*
	*	@access	public
	*/
	function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->lang->loadfile('assets');
		$this->EE->load->model('zenbu_get');
		$installed_addons = $this->EE->zenbu_get->_get_installed_addons();

		if(isset($installed_addons['modules_versions']['Assets']))
		{
			$this->assets_ver = $installed_addons['modules_versions']['Assets'];
		} else {
			$this->assets_ver = "";
		}

		$this->attr = array(
			array('col' => 'title', 'lang' => 'title'),
			array('col' => 'date', 'lang' => 'date'),
			array('col' => 'alt_text', 'lang' => 'alt_text'),
			array('col' => 'caption', 'lang' => 'caption'),
			array('col' => 'author', 'lang' => 'credit'),
			array('col' => 'desc', 'lang' => 'description'),
			array('col' => 'location', 'lang' => 'location'),
			array('col' => 'keywords', 'lang' => 'keywords'),
		);

		if(version_compare($this->assets_ver, '2.0.0', '>='))
		{
			$this->attr[] = array('col' => 'date_modified', 'lang' => 'date_modified');
			$this->attr[] = array('col' => 'kind', 'lang' => 'kind');
			$this->attr[] = array('col' => 'width', 'lang' => 'width');
			$this->attr[] = array('col' => 'height', 'lang' => 'height');
			$this->attr[] = array('col' => 'size', 'lang' => 'size');
		}

		$this->total_attr = count($this->attr);

		// Remove 0 from keys
		array_unshift($this->attr, "phoney");
    	unset($this->attr[0]);
	}
	
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
	*	@param  $matrix 			bool 	Checks if final output should be handled as a Matrix field or not
	*	@return	$output		The HTML used to display data
	*/
	function zenbu_display($entry_id, $channel_id, $data, $assets_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons, $matrix = FALSE)
	{

		$output = '&nbsp;';

		if(empty($assets_data) || ! isset($assets_data[$entry_id]))
		{
			return $output;
		}
		
		$this->EE->load->helper('display');
		$this->EE->load->helper('loader');
		$this->EE->load->helper(array('file', 'html', 'url'));

		//	----------------------------------------
		//	Display only images if nothing else is selected
		//	----------------------------------------
		$only_images = TRUE;

		for($i = 1; $i <= $this->total_attr; $i++)
		{
			if(isset($settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['assets_option_' . $i]))
			{
				$only_images = FALSE;
			}
		}

		if($only_images === TRUE)
		{
			$output = '';
			if(isset($assets_data[$entry_id]['field_id_'.$field_id]))
			{
				foreach($assets_data[$entry_id]['field_id_'.$field_id] as $key => $asset_array)
				{
					//	----------------------------------------
					//	Assets 2.x
					//	----------------------------------------
					if(version_compare($this->assets_ver, '2.0.0', '>='))
					{
						switch($asset_array['source_type'])
						{
							case 's3':
								$site_url = (substr($this->EE->config->item('site_url'), -1, 1) != '/') ? $this->EE->config->item('site_url').'/' : $this->EE->config->item('site_url');

								switch($asset_array['kind'])
								{
									case 'image':
										$file_url = $site_url."?ACT=".get_action_id('Assets_mcp', 'view_file').AMP."file_id=".$asset_array['file_id'];
										$file_url_thumb = $site_url."?ACT=".get_action_id('Assets_mcp', 'view_thumbnail').AMP."file_id=".$asset_array['file_id'].$file_url.AMP."size=80x80";
										$output .= anchor($file_url, img($file_url_thumb), 'class="fancyboxiframe" rel="#asset_'.$asset_array['file_id'].'" alt="'.$asset_array['file_name'].'" title="'.$asset_array['file_name'].'"');
									break;
									default:
										$file_url = $site_url."?ACT=".get_action_id('Assets_mcp', 'view_file').AMP."file_id=".$asset_array['file_id'];
										$output .= anchor($file_url, $asset_array['file_name'], 'alt="'.$asset_array['file_name'].'" title="'.$asset_array['file_name'].'"');
									break;
								}
								
							break;
							default:
								$unparsed_filedir = '{filedir_'.$asset_array['filedir_id'].'}'.$asset_array['full_path'].$asset_array['file_name'];
								$output .= display_file($field_id, $unparsed_filedir, $upload_prefs, $rules);
							break;
						}
						
					} 
					else
					//	----------------------------------------
					//	Assets 1.x
					//	---------------------------------------- 
					{
						$output .= display_file($field_id, $asset_array['file_path'], $upload_prefs, $rules);
					}
				}

				if( ! isset($settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['assets_show_in_row']) && isset($assets_data[$entry_id]['field_id_'.$field_id]) && $matrix === FALSE)
				{
					// Display table in a hidden div, then have a fancybox link show it on click
					$output = '<div style="display: none"><div id="e_'.$entry_id.'_f_'.$field_id.'">' . $output . '</div></div>';
					$link_to_matrix = '#e_'.$entry_id.'_f_'.$field_id;
					return anchor($link_to_matrix, $this->EE->lang->line('show_') . '(' . count($assets_data[$entry_id]['field_id_'.$field_id]) . ')', 'class="fancybox-inline"') . $output;
				} else {
					// ..else return the table as-is, and see it displayed directly in the row
					return $output;
				}
			}
			
		}


		//	----------------------------------------
		//	Build a data table of assets if
		//	other data is set to be displayed
		//	----------------------------------------
		$total_assets = '';
		if(isset($assets_data[$entry_id]['field_id_'.$field_id]))
		{
			$output = '<table class="mainTable matrixTable" width="" cellspacing="0" cellpadding="0" border="0">';
			$output .= '<tr><th style="padding: 0;">&nbsp;</th>';
			
			//	---------------
			//	Table header
			//	---------------
			for($i = 1; $i <= $this->total_attr; $i++)
			{
				if(isset($settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['assets_option_' . $i]))
				{
					$output .= '<th>'.$this->EE->lang->line($this->attr[$i]['lang']).'</th>';
				}
			}
			$output .= '</tr>';
			
			//	---------------
			//	Table rows
			//	---------------
			$total_assets = ' (' . count($assets_data[$entry_id]['field_id_'.$field_id]) . ')';
			foreach($assets_data[$entry_id]['field_id_'.$field_id] as $key => $asset_array)
			{
				// function display_file($field_id, $field_data, $upload_prefs, $rules = array())
				$output .= '<tr>';

				//	----------------------------------------
				//	Assets 2.x
				//	----------------------------------------
				if(version_compare($this->assets_ver, '2.0.0', '>='))
				{
					switch($asset_array['source_type'])
					{
						case 's3':
							$site_url = (substr($this->EE->config->item('site_url'), -1, 1) != '/') ? $this->EE->config->item('site_url').'/' : $this->EE->config->item('site_url');

							switch($asset_array['kind'])
							{
								case 'image':
									$file_url = $site_url."?ACT=".get_action_id('Assets_mcp', 'view_file').AMP."file_id=".$asset_array['file_id'];
									$file_url_thumb = $site_url."?ACT=".get_action_id('Assets_mcp', 'view_thumbnail').AMP."file_id=".$asset_array['file_id'].$file_url.AMP."size=80x80";
									$output .= '<td>'.anchor($file_url, img($file_url_thumb), 'class="fancyboxiframe" rel="#asset_'.$asset_array['file_id'].'" alt="'.$asset_array['file_name'].'" title="'.$asset_array['file_name'].'"').'</td>';
								break;
								default:
									$file_url = $site_url."?ACT=".get_action_id('Assets_mcp', 'view_file').AMP."file_id=".$asset_array['file_id'];
									$output .= '<td>'.anchor($file_url, $asset_array['file_name'], 'alt="'.$asset_array['file_name'].'" title="'.$asset_array['file_name'].'"').'</td>';
								break;
							}
							
						break;
						default:
							$unparsed_filedir = '{filedir_'.$asset_array['filedir_id'].'}'.$asset_array['full_path'].$asset_array['file_name'];
							$output .= '<td>'.display_file($field_id, $unparsed_filedir, $upload_prefs, $rules).'</td>';
						break;
					}
					
				} 
				else
				//	----------------------------------------
				//	Assets 1.x
				//	---------------------------------------- 
				{
					$output .= '<td>'.display_file($field_id, $asset_array['file_path'], $upload_prefs, $rules).'</td>';
				}

				//	Add extra columns based on settings
				//	----------------------------------
				for($i = 1; $i <= $this->total_attr; $i++)
				{
					if(isset($settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['assets_option_' . $i]))
					{
						if($i == 2 || $i == 9)
						{
							// Format date to something human-readable
							$output .= '<td>'.display_date('', '', $asset_array[$this->attr[$i]['col']], array(), '', array(), array(), 'unix').'</td>';
						} elseif($i == 13) {
							$output .= '<td>'.display_filesize($asset_array[$this->attr[$i]['col']]).'</td>';
						}else {
							$output .= '<td>'.$asset_array[$this->attr[$i]['col']].'</td>';
						}
					}
				}
				$output .= '</tr>';
			}
		
			$output .= '</table>';
		}
		
		if( ! isset($settings['setting'][$channel_id]['extra_options']['field_'.$field_id]['assets_show_in_row']) && isset($assets_data[$entry_id]['field_id_'.$field_id]))
		{
			// Display table in a hidden div, then have a fancybox link show it on click
			$output = '<div style="display: none"><div id="e_'.$entry_id.'_f_'.$field_id.'">' . $output . '</div></div>';
			$link_to_matrix = '#e_'.$entry_id.'_f_'.$field_id;
			return anchor($link_to_matrix, $this->EE->lang->line('show_') . $total_assets, 'class="fancybox-inline"') . $output;
		} else {
			// ..else return the table as-is, and see it displayed directly in the row
			return $output;
		}
		
		
		return $output;
		
	}
	
	
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
	*	@param	$settings				array	The settings array, containing saved field order, display, extra options etc settings (optional)
	*	@param	$rel_array				array	A simple array useful when using related entry-type fields (optional)
	*	@param 	$matrix 				bool 	Uses different queries if Assets is with Matrix
	*	@return	$output					array	An array of data (typically broken down by entry_id then field_id) that can be used and processed by the zenbu_display() method
	*/
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id, $matrix = FALSE)
	{
		$this->EE->load->model('zenbu_get');
		$installed_addons = $this->EE->zenbu_get->_get_installed_addons();

		//	----------------------------------------
		//	Return data if already cached
		//	----------------------------------------
		if($this->EE->session->cache('zenbu', 'asset_data') && $matrix === FALSE)
		{
			return $this->EE->session->cache('zenbu', 'asset_data');
		}

		if($this->EE->session->cache('zenbu', 'asset_data_for_matrix') && $matrix !== FALSE && is_numeric($matrix))
		{
			return $this->EE->session->cache('zenbu', 'asset_data_for_matrix');
		}

		//	----------------------------------------
		//	Assets 2.x
		//	----------------------------------------
		if(version_compare($this->assets_ver, '2.0.0', '>='))
		{
			$output = array();
			$asset = array();
			
			//	----------------------------------------
			//	Assets-in-Matrix query
			//	----------------------------------------
			if($matrix !== FALSE && is_numeric($matrix))
			{
				$this->EE->db->select('af.*,
				 asel.entry_id,
				 asel.field_id,
				 asel.col_id,
				 asel.row_id,
				 asel.var_id,
				 asel.sort_order,
				 afldr.full_path');
				$this->EE->db->from(array('exp_assets_selections asel'));
				$this->EE->db->join('assets_files af', 'asel.file_id = af.file_id');
				$this->EE->db->join('assets_sources asrc', 'af.source_id = asrc.source_id', 'left');
				$this->EE->db->join('assets_folders afldr', 'af.source_id = afldr.source_id OR af.filedir_id = afldr.filedir_id AND af.folder_id = afldr.folder_id', 'left');
				$this->EE->db->where_in('asel.field_id', $field_ids);
				$this->EE->db->where("asel.col_id", $matrix); // $matrix is a col_id
				//$this->EE->db->group_by('asel.file_id');
				$this->EE->db->order_by('asel.row_id', 'asc');
				$this->EE->db->order_by('asel.sort_order', 'asc');
				
				$query = $this->EE->db->get();
			
				if($query->num_rows() > 0)
				{
					foreach($query->result_array() as $row)
					{
						foreach($row as $key => $val)
						{
							$asset[$row['row_id']][$row['sort_order']][$row['entry_id']]['field_id_'.$row['field_id']][$row['file_id']][$key] 	= $val;
						}
					}
				}

				$this->EE->session->set_cache('zenbu', 'asset_data_for_matrix', $asset);

				return $asset;
			}

			//	----------------------------------------
			//	Regular Assets query
			//	----------------------------------------
			$this->EE->db->distinct();
			$this->EE->db->select('af.*,
			 asel.entry_id,
			 asel.field_id,
			 asel.col_id,
			 asel.row_id,
			 asel.var_id,
			 asel.sort_order,
			 afldr.full_path');
			$this->EE->db->from(array('exp_assets_selections asel'));
			$this->EE->db->join('assets_files af', 'asel.file_id = af.file_id');
			$this->EE->db->join('assets_sources asrc', 'af.source_id = asrc.source_id', 'left');
			$this->EE->db->join('assets_folders afldr', 'af.source_id = afldr.source_id OR af.filedir_id = afldr.filedir_id AND af.folder_id = afldr.folder_id', 'left');
			$this->EE->db->where_in('asel.field_id', $field_ids);
			$this->EE->db->where_in('asel.entry_id', $entry_ids);
			$this->EE->db->order_by('asel.sort_order');

			$query = $this->EE->db->get();
			
			if($query->num_rows() > 0)
			{
				foreach($query->result_array() as $row)
				{
					foreach($row as $key => $val)
					{
						$asset[$row['entry_id']]['field_id_'.$row['field_id']][$row['file_id']][$key] 	= $val;
					}
				}
			}

		} 
		else
		//	----------------------------------------
		//	Original Assets 1.x
		//	---------------------------------------- 
		{

			$output = array();
			$asset = array();
			
			$this->EE->db->distinct();
			$this->EE->db->select('exp_assets.*,
			 exp_assets_entries.entry_id,
			 exp_assets_entries.field_id,
			 exp_assets_entries.col_id,
			 exp_assets_entries.row_id,
			 exp_assets_entries.var_id,
			 exp_assets_entries.asset_order');
			$this->EE->db->from(array('assets_entries'));
			$this->EE->db->join('assets', 'exp_assets_entries.asset_id = exp_assets.asset_id');
			$this->EE->db->where_in('assets_entries.field_id', $field_ids);
			$this->EE->db->where_in('assets_entries.entry_id', $entry_ids);
			$this->EE->db->order_by('assets_entries.asset_order');
			$query = $this->EE->db->get();
			if($query->num_rows() > 0)
			{
				foreach($query->result_array() as $row)
				{
					foreach($row as $key => $val)
					{
						$asset[$row['entry_id']]['field_id_'.$row['field_id']][$row['asset_id']][$key] 	= $val;
					}
				}
			}

		}

		$this->EE->session->set_cache('zenbu', 'asset_data', $asset);

		return $asset;
	}
	
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
	function zenbu_field_extra_settings($table_col, $channel_id, $extra_options)
	{
		$extra_option = (isset($extra_options['assets_show_in_row'])) ? TRUE : FALSE;
		$output['assets_show_in_row'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][assets_show_in_row]', 'y', $extra_option).'&nbsp;'.$this->EE->lang->line('show_') . $this->EE->lang->line('_in_row')).'<br /><br />';
		
		for($i = 1; $i <= $this->total_attr; $i++)
		{
			$extra_option = 'extra_option_'.$i;
			$extra_option = (isset($extra_options['assets_option_'.$i])) ? TRUE : FALSE;
			$output['assets_option_'.$i] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][assets_option_' . $i . ']', 'y', $extra_option).'&nbsp;'.$this->EE->lang->line('show_').$this->EE->lang->line($this->attr[$i]['lang'])).'<br />';
		}
		
		return $output;
	}
	
	
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
	function zenbu_result_query($rules = array(), $field_id = "", $fieldtypes, $already_queried = FALSE)
	{
		//	----------------------------------------
		//	Assets 2.x
		//	----------------------------------------
		if(version_compare($this->assets_ver, '2.0.0', '>='))
		{
			$concat = "exp_assets_files.file_name, exp_assets_files.title, exp_assets_files.alt_text, exp_assets_files.caption, exp_assets_files.author, exp_assets_files.desc, exp_assets_files.location, exp_assets_files.keywords";

			$sql_part1 = "/* Zenbu: Search assets */\nSELECT exp_assets_files.file_id, exp_assets_selections.entry_id FROM exp_assets_files, exp_assets_selections WHERE \nCONCAT_WS(',', ".$concat.") \nLIKE '%";

			$sql_part2 = "%' AND exp_assets_files.file_id = exp_assets_selections.file_id";
		} 
		else
		//	----------------------------------------
		//	Original Assets 1.x
		//	---------------------------------------- 
		{
			$concat = "exp_assets.file_path, exp_assets.title, exp_assets.alt_text, exp_assets.caption, exp_assets.author, exp_assets.desc, exp_assets.location, exp_assets.keywords";
			
			$sql_part1 = "/* Zenbu: Search assets */\nSELECT exp_assets.asset_id, exp_assets_entries.entry_id FROM exp_assets, exp_assets_entries WHERE \nCONCAT_WS(',', ".$concat.") \nLIKE '%";

			$sql_part2 = "%' AND exp_assets.asset_id = exp_assets_entries.asset_id";
		}

		//	----------------------------------------
		//	Go through each Zenbu rule. Keyword will
		//	change, thus the two-part query string
		//	----------------------------------------
		foreach($rules as $rule)
		{
			$rule_field_id = (strncmp($rule['field'], 'field_', 6) == 0) ? substr($rule['field'], 6) : 0;

			if(isset($fieldtypes['fieldtype'][$rule_field_id]) && $fieldtypes['fieldtype'][$rule_field_id] == "assets")
			{
				$keyword = $rule['val'];
			
				$keyword_query = $this->EE->db->query($sql_part1 . $this->EE->db->escape_like_str($keyword) . $sql_part2);

				$where_in = array();
				
				if($keyword_query->num_rows() > 0)
				{
					foreach($keyword_query->result_array() as $row)
					{
						$where_in[] = $row['entry_id'];
					}
				}

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

		} // foreach	
		return;
		
	}
	
	
	
} // END CLASS

/* End of file assets.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/assets.php */
?>