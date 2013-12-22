<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	Pixel&Tonic's Matrix field
*	@author	Pixel&tonic http://pixelandtonic.com
*	@link	http://pixelandtonic.com/matrix
*	============================================
*	File matrix.php
*	
*/

class Zenbu_matrix_ft extends Matrix_ft
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
		$this->EE->lang->loadfile('matrix');
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
	*	@return	$output		The HTML used to display data
	*/
	function zenbu_display($entry_id, $channel_id, $data, $matrix_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons)
	{
		$output = '&nbsp;';

		// If matrix_data is empty (no results), stop here and return a space for the field data
		if(isset($matrix_data['entry_id_'.$entry_id]['field_id_'.$field_id]))
		{
			$table_data = $matrix_data['entry_id_'.$entry_id]['field_id_'.$field_id];
		} else {
			return $output;
		}
		$output = "";
		$row_array = array();
		$this->EE->load->helper('display');

		// Process field data
		foreach($table_data as $row => $col_array_raw)
		{

			foreach($col_array_raw as $col_order => $col_array)
			{
				foreach($col_array as $col_id => $col_data)
				{
					if(substr($col_id, 0, 7) == "col_id_")
					{
						$num_col_id = substr($col_id, 7);
						$num_row_id = substr($row, 7);
						
						// Create header array for view
						$row_array['headers'][$num_col_id] = $matrix_data['entry_id_'.$entry_id]['field_id_'.$field_id]['headers'][$num_col_id]['data'];
						$row_array['column_fieldtype'][$num_col_id] = $matrix_data['entry_id_'.$entry_id]['field_id_'.$field_id]['headers'][$num_col_id]['fieldtype'];
						
						// Create cell data array for view
						switch ($row_array['column_fieldtype'][$num_col_id])
						{
							case "file": case "safecracker_file":
								$cell = display_file($field_id, $col_data, $upload_prefs, $rules);
							break;
							case "assets":

								$this->EE->load->helper('loader');
								if( ! class_exists('Assets_ft'))
								{
									load_ft_class('assets_ft');
								}

								$assets = create_object('assets_ft');
								$cell = '';

								//	----------------------------------------
								//	Assets 2.x
								//	----------------------------------------
								if(version_compare($assets->assets_ver, '2.0.0', '>='))
								{
									$assets_data = $assets->zenbu_get_table_data(array($entry_id), array($field_id), $channel_id, $num_col_id);

									if(isset($assets_data[$num_row_id]))
									{
										foreach($assets_data[$num_row_id] as $asset_row => $asset_array)
										{
											// That last param (TRUE) prevents creating "Show (X)" links with fancybox
											$cell .= $assets->zenbu_display($entry_id, $channel_id, $data, $asset_array, $field_id, $settings, $rules, $upload_prefs, $installed_addons, TRUE) . NBS; 
										}
									}
									
								}
								else
								//	----------------------------------------
								//	Assets 1.x
								//	---------------------------------------- 
								{
									$asset_files = explode("\n", $col_data);
									foreach($asset_files as $key => $asset_data)
									{
										$cell .= display_file($field_id, $asset_data, $upload_prefs, $rules) . NBS;
									}
								}

							break;
							case "date":
								//$output_date = $this->EE->zenbu_get->_get_member_date_settings();
								$cell = display_date($entry_id, $channel_id, $col_data, $matrix_data, $field_id, $settings, $rules);
							break;
							case "playa": case "structure_playa":
								// Digging too deep: don't have Playa-within-matrix field-relationship $playa_data array as of this writing.
								// Query entry ids, titles and channel_ids per matrix within the _display_rel function, using the from_matrix = y array
								$ft_class_withinmatrix = ''.$row_array['column_fieldtype'][$num_col_id].'_ft';
								
								load_ft_class($row_array['column_fieldtype'][$num_col_id].'_ft');
								
								$ft_table_data = $row_array['column_fieldtype'][$num_col_id].'_data';
								$table_data = (isset($$ft_table_data)) ? $$ft_table_data : array();
								$table_data['from_matrix'] = 'y';
				
								if(class_exists($ft_class_withinmatrix))
								{
									// Destroy cache for each Playa set query. 
									// Possibly not great for performance, but without this
									// all Matix Playa columns will have the same data
									$this->EE->session->set_cache('zenbu', 'core_entry_data', FALSE);
									
									$ft_object_withinmatrix = create_object($ft_class_withinmatrix);
									$field_data = (method_exists($ft_object_withinmatrix, 'zenbu_display')) ? $ft_object_withinmatrix->zenbu_display($entry_id, $channel_id, $col_data, $table_data, $field_id, $settings, $rules, $upload_prefs, $installed_addons) : '--';
									
								} else {
									$field_data = '-';
								}
								
								$cell = $field_data;
							break;
							default:
								$cell = display_text($entry_id, $channel_id, $col_data, $matrix_data, $field_id, $settings, $rules);
							break;
						}
						$row_array['rows'][$row][$col_id] = $cell;
					}
				}
			}
		}
							
				
		$table_id['table_id'] = $entry_id.'-'.$field_id;
		
		$vars = array_merge($row_array, $table_id);
		
		$output = $this->EE->load->view('_matrix', $vars, TRUE);
	
		/**
		* Displaying the matrix inline or as a fancybox link
		* Based on user group setting
		*/	
		$extra_options = $settings['setting'][$channel_id]['extra_options'];

		if( ! isset($extra_options["field_".$field_id]['matrix_option_1']))
		{
			// Display table in a hidden div, then have a fancybox link show it on click
			$output = '<div style="display: none"><div id="e_'.$entry_id.'_f_'.$field_id.'">' . $output . '</div></div>';
			$link_to_matrix = '#e_'.$entry_id.'_f_'.$field_id;
			return anchor($link_to_matrix, $this->EE->lang->line('show_').$this->EE->lang->line('matrix'), 'class="fancybox-inline" rel="#field_id_'.$field_id.'"') . $output;
		} else {
			// ..else return the table as-is, and see it displayed directly in the row
			return $output;
		}
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
	*	@return	$output					array	An array of data (typically broken down by entry_id then field_id) that can be used and processed by the zenbu_display() method
	*/
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id)
	{
		$output = array();
		if( empty($entry_ids) || empty($field_ids))
		{
			return $output;
		}
		
		//
		// Get matrix field data as an array also used for headers
		//
		$this->EE->db->select("col_id, col_type, col_order, col_label, field_id");
		$this->EE->db->from("matrix_cols");
		$this->EE->db->where("site_id", $this->EE->session->userdata['site_id']);
		$this->EE->db->order_by('col_order', 'asc');
		$query = $this->EE->db->get();
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$headers_array['id'][$row['col_id']] = $row['col_id'];
				$headers_array['col_order'][$row['col_id']] = $row['col_order'];
				$headers_array['label'][$row['col_id']] = $row['col_label'];
				$headers_array['fieldtype'][$row['col_id']] = $row['col_type'];
				$col_params['field_id_'.$row['field_id']]['col_id_'.$row['col_id']] = array(
					"id" => $row['col_id'],
					"col_order" => $row['col_order'],
					"label" => $row['col_label'],
					"fieldtype" => $row['col_type'], 
				);
			}
		} else {
				$headers_array['id'][] = '';
				$headers_array['label'][] = '';
				$headers_array['fieldtype'][] = '';
		}
		
		
		//
		// Get Matrix data
		//
		//$field_ids = array("" => 30);
		$this->EE->db->from("matrix_data");
		$this->EE->db->where_in("entry_id", $entry_ids);
		$this->EE->db->where_in("field_id", $field_ids);
		$this->EE->db->where("site_id", $this->EE->session->userdata['site_id']);
		$this->EE->db->order_by("field_id", "asc");
		$this->EE->db->order_by("row_order", "asc");
		$results = $this->EE->db->get();
		if($results->num_rows() > 0)
		{
		
			// Create an array for col_type query and setup data for view
			$col_ids = array();
			foreach($results->result_array() as $row => $array)
			{
				$entry_id = $array['entry_id'];
				$f_id = $array['field_id'];
				$row_id = $array['row_id'];
				if(in_array($f_id, $field_ids))
				{
					foreach($array as $data_field => $data)
					{
						
						if(strncmp($data_field, "col_id_", 7) == 0 && ! is_null($data))
						{
							// Array for col_type and col_label
							$col_id_number = substr($data_field, 7);
							
							if(isset($col_params['field_id_'.$f_id]['col_id_'.$col_id_number]['col_order']))
							{
								$col_order = $col_params['field_id_'.$f_id]['col_id_'.$col_id_number]['col_order'];
							}
							
							// Data rows
							if(isset($col_params['field_id_'.$f_id]['col_id_'.$col_id_number]) && $col_id_number == $col_params['field_id_'.$f_id]['col_id_'.$col_id_number]['id'])
							{
								$row_array['entry_id_'.$entry_id]['field_id_'.$f_id]['row_id_'.$row_id]['col_order_'.$col_order]['col_id_'.$col_id_number] = str_replace('&amp;', '&', htmlspecialchars($data));
								ksort($row_array['entry_id_'.$entry_id]['field_id_'.$f_id]['row_id_'.$row_id]);
							}
						}
					}
				}
			}
			
			
			foreach($results->result_array() as $row => $array)
			{
				$entry_id = $array['entry_id'];
				$f_id = $array['field_id'];
				$row_id = $array['row_id'];
				if(in_array($f_id, $field_ids))
				{
					foreach($array as $data_field => $data)
					{
						
						if(strncmp($data_field, 'col_id_', 7) == 0 && ! is_null($data))
						{
							// Array for col_type and col_label
							$col_id_number = substr($data_field, 7);
							
							// Data rows
							$row_array['entry_id_'.$entry_id]['field_id_'.$f_id]['headers'][$col_id_number]['data'] = isset($headers_array['label'][$col_id_number]) ? $headers_array['label'][$col_id_number] : '-';
							$row_array['entry_id_'.$entry_id]['field_id_'.$f_id]['headers'][$col_id_number]['fieldtype'] = isset($headers_array['fieldtype'][$col_id_number]) ? $headers_array['fieldtype'][$col_id_number] : '-';
							
						}
					}
				}
			}
			
			$results->free_result();
							
			$table_id['table_id'] = $entry_id;
			
			$output = array_merge($row_array, $table_id);
			return $output;
				
		} else {
			// If matrix is empty, return a space character for the cell
			return $output;
		}
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
		$extra_option_1 = (isset($extra_options['matrix_option_1'])) ? TRUE : FALSE;
		$output['matrix_option_1'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][matrix_option_1]', 'y', $extra_option_1).'&nbsp;'.$this->EE->lang->line('show_').$this->EE->lang->line('matrix').$this->EE->lang->line('_in_row'));
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
		
		if(empty($rules))
		{
			return;
		}
		
		if($already_queried === FALSE)
		{
			$this->EE->db->from("exp_matrix_data"); 
		}
		
		$this->EE->db->where("exp_matrix_data.field_id", $field_id);
		$col_query = $this->EE->db->query("/* Zenbu: Show columns for matrix */\nSHOW COLUMNS FROM exp_matrix_data");
		$concat = "";
		$where_in = array();
		
		if($col_query->num_rows() > 0)
		{
			foreach($col_query->result_array() as $row)
			{	
				if(strchr($row['Field'], 'col_id_') !== FALSE)
				{
					$concat .= 'exp_matrix_data.'.$row['Field'].', ';
				}								
			}
			$concat = substr($concat, 0, -2);
		}
		
		$col_query->free_result();
		
		if( ! empty($concat))
		{
			// Find entry_ids that have the keyword
			foreach($rules as $rule)
			{
				$rule_field_id = (strncmp($rule['field'], 'field_', 6) == 0) ? substr($rule['field'], 6) : 0;
				if(isset($fieldtypes['fieldtype'][$rule_field_id]) && $fieldtypes['fieldtype'][$rule_field_id] == "matrix")
				{
					$keyword = $rule['val'];
				
					$keyword_query = $this->EE->db->query("/* Zenbu: Search matrix */\nSELECT entry_id FROM exp_matrix_data WHERE \nCONCAT_WS(',', ".$concat.") \nLIKE '%".$this->EE->db->escape_like_str($keyword)."%'");
					$where_in = array();
					if($keyword_query->num_rows() > 0)
					{
						foreach($keyword_query->result_array() as $row)
						{
							$where_in[] = $row['entry_id'];
						}
					}
				} // if
			
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
			
			} // foreach
			
			
			
		} // if
	}
	
	
	
} // END CLASS

/* End of file matrix.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/matrix.php */
?>