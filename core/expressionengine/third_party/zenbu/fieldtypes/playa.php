<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	Pixel&Tonic's Playa field
*	@author	Pixel&tonic http://pixelandtonic.com
*	@link	http://pixelandtonic.com/playa
*	============================================
*	File playa.php
*	
*/

class Zenbu_playa_ft extends Playa_ft
{
	
	/**
	*	Constructor
	*
	*	@access	public
	*/
	function __construct()
	{
		$this->EE =& get_instance();
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
	function zenbu_display($entry_id, $channel_id, $field_data, $rel_data = array(), $field_id, $settings, $rules = array(), $upload_prefs = array(), $installed_addons)
	{
		$output = '&nbsp;';
		
		$this_entry_id = $entry_id;
		if( empty($rel_data))
		{
			return $output;
		}
		
		if(isset($installed_addons['modules_versions']['Playa']))
		{
			$playa_ver = $installed_addons['modules_versions']['Playa'];
		} else {
			$playa_ver = "";
		}

		// --------------------
		// Playa entry display
		// --------------------
		// Playa 4.0
		if(isset($rel_data['parent_id_'.$entry_id]['field_id_'.$field_id]) && version_compare($playa_ver, "4.0", '>='))
		{
			// With a $field_id, we should be all set up to get the info we need from $rel_data
			$output = '<ul>';
			$related_entries = $rel_data['parent_id_'.$entry_id]['field_id_'.$field_id];
			$extra_settings = $settings['setting'][$channel_id]['extra_options'];
			foreach($related_entries as $child_entry_id => $entry_data_array)
			{
				$entry_title = highlight($entry_data_array['title'], $rules, 'field_'.$field_id);
				$entry_id = $entry_data_array['entry_id'];
				$entry_id_prefix = (isset($extra_settings['field_'.$field_id]['rel_option_1']) && $extra_settings['field_'.$field_id]['rel_option_1'] == 'y') ? $entry_data_array['entry_id'] . ' - ' : '';
				$channel_id = $entry_data_array['channel_id'];
				$output .= '<li>'.anchor(BASE.AMP."C=content_publish".AMP."M=entry_form".AMP."channel_id=".$channel_id.AMP."entry_id=".$entry_id, $entry_id_prefix . $entry_title);
				$output .= (count($related_entries) > 1) ? '</li>' : '';
			}
			$output .= '</ul>';
			return $output;
		
		// Playa less than 4.0
		} elseif(isset($rel_data['parent_id_'.$entry_id]) && ! empty($playa_ver) && version_compare($playa_ver, "4.0", '<')) {
		
			// Read the data in the field and compare it to $rel_array to fetch entry data
			$field_data = explode('[', $field_data);
			$f_data = array();			
			foreach ($field_data as $key => $val)
			{
				if($key != 0)
				{
					$matches = preg_match('/(.*?)\]/', $val, $match);
					$f_data[$match[1]] = $match[1];
				}
			}
			
			// Go through previously read rel_ids and look for its reference in $rel_array
			$output = '<ul>';
			foreach($f_data as $key => $rel_id)
			{
				$related_entries = $rel_data['parent_id_'.$this_entry_id]['rel_id_'.$rel_id];
				foreach($related_entries as $child_id => $entry_data_array)
				{
					$entry_title = highlight($entry_data_array['title'], $rules, 'field_'.$field_id);
					$entry_id = $entry_data_array['entry_id'];
					$channel_id = $entry_data_array['channel_id'];
					$output .= '<li>'.anchor(BASE.AMP."C=content_publish".AMP."M=entry_form".AMP."channel_id=".$channel_id.AMP."entry_id=".$entry_id, $entry_id.' - '.$entry_title);
					$output .= (count($related_entries) > 1) ? '</li>' : '';
				}
			}
			$output .= '</ul>';
			return $output;
		
		// When Playa data is within Matrix, just get the entry data (id, title, channel_id) cell-by-cell
		} elseif(isset($rel_data['from_matrix']) && $rel_data['from_matrix'] == 'y') {
			
			$field_data = explode('[', $field_data);
			$f_data = array();			
			foreach ($field_data as $key => $val)
			{
				if($key != 0)
				{
					$matches = preg_match('/(.*?)\]/', $val, $match);
					$f_data[$match[1]] = $match[1];
				}
			}
			
			$entry_data = $this->EE->zenbu_get->_get_core_entry_data($f_data);
			$output = '<ul>';
			foreach($entry_data as $entry_id => $row)
			{
				$entry_title = highlight($row['title'], $rules, 'field_'.$field_id);
				$entry_id = $row['id'];
				$channel_id = $row['channel_id'];
				$output .= '<li>'.anchor(BASE.AMP."C=content_publish".AMP."M=entry_form".AMP."channel_id=".$channel_id.AMP."entry_id=".$entry_id, $entry_id.' - '.$entry_title);
			}
			$output .= '</ul>';
			return $output;
		}
		
		return $output;

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
		// Retrieve previous results if present
		$rel_option_1 = (isset($extra_options['rel_option_1']) && $extra_options['rel_option_1'] == 'y') ? TRUE : FALSE;
		
		// Option: Show related entry ID with related entry title 						
		$output['rel_option_1'] = form_label(form_checkbox('settings['.$channel_id.']['.$table_col.'][rel_option_1]', 'y', $rel_option_1) . '&nbsp;' . $this->EE->lang->line('show').'&nbsp;'.$this->EE->lang->line('entry_id'));
				
		// Output
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
	*	@param	$settings				array	The settings array, containing saved field order, display, extra options etc settings
	*	@param	$rel_array				array	A simple array useful when using related entry-type fields (optional)
	*	@return	$output					array	An array of data (typically broken down by entry_id then field_id) that can be used and processed by the zenbu_display() method
	*/
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id, $output_upload_prefs, $settings, $rel_array)
	{
		$output = array();
		
		if( empty($entry_ids) || empty($field_ids) || empty($rel_array))
		{
			return $output;
		}
		
		$this->EE->db->select("version");
		$this->EE->db->where("name", "playa");
		$playa_ver_query = $this->EE->db->get("fieldtypes");
		if ($playa_ver_query->num_rows() > 0)
		{
		   $row = $playa_ver_query->row_array();
		   
		   $playa_ver = $row['version'];
		}
		$playa_ver_query->free_result();
		
		$output['playa_ver'] = $playa_ver;
		
		
		/**
		 * Retrieve Playa child_entries (related entries)
		 * ----------------------------------------------
		 * Retrieve based on provided result entry_ids.
		 * Extra processing to get field_ids (those set to be displayed).
		 * Only first entry row is sufficient to get field_ids.
		 */
		$first_result = array_slice($rel_array, 0, 1);
		$first_result = $first_result[0];

		// Get target field_ids from this first entry row
		$parent_field_ids = array();
		foreach($first_result as $field => $data)
		{
			if(strncmp($field, 'field_id_', 9) == 0)
			{
				$field_id = substr($field, 9);
				$parent_field_ids[$field_id] = $field_id;
			}
		}
		
		// Build relationship data array
		// -----------------------------
		// Better than exploding values in exp_channel_data
		// based on the presence of a bracket.
		// $parent_field_ids is very broad so many 
		// entry_id/field_id combinations will not match, but that's ok
		if(version_compare($playa_ver, "4.0", '>='))
		{
			$this->EE->db->from("exp_playa_relationships");
			$this->EE->db->where_in("parent_entry_id", $entry_ids);
			$this->EE->db->where_in("parent_field_id", $parent_field_ids);
		} elseif(version_compare($playa_ver, "4.0", '<')) {
			$this->EE->db->from("exp_relationships");
			$this->EE->db->where_in("rel_parent_id", $entry_ids);
		}
		
		$rel_data_q = $this->EE->db->get();
		
		if($rel_data_q->num_rows() > 0)
		{
			foreach($rel_data_q->result_array() as $row)
			{
				$rel_data[$row['child_entry_id']] = $row['child_entry_id'];
			}
		}
		
		// If $rel_data only had 0 as entry values, skip the following.
		// This can happen if no entries have a related entry 
		if( ! empty($rel_data) && version_compare($playa_ver, "4.0", '>='))
		{
			// For Playa 4 and above, which store child_entry_id
			$this->EE->db->select(array("exp_playa_relationships.parent_entry_id", "exp_playa_relationships.child_entry_id", "exp_playa_relationships.parent_field_id", "exp_channel_titles.title", "exp_channel_titles.channel_id"));
			$this->EE->db->from("exp_playa_relationships");
			$this->EE->db->join("exp_channel_titles", "exp_channel_titles.entry_id = exp_playa_relationships.child_entry_id");
			$this->EE->db->where_in("playa_relationships.child_entry_id", $rel_data);
			//$this->EE->db->where_in("playa_relationships.parent_entry_id", $entry_ids);
			$this->EE->db->where_in("playa_relationships.parent_field_id", $field_ids);
			$this->EE->db->order_by("rel_order", "asc");
			$query = $this->EE->db->get();
			
			foreach($query->result_array() as $row)
			{
				$output['parent_id_'.$row['parent_entry_id']]['field_id_'.$row['parent_field_id']]['child_id_'.$row['child_entry_id']]['title'] = $row['title'];
				$output['parent_id_'.$row['parent_entry_id']]['field_id_'.$row['parent_field_id']]['child_id_'.$row['child_entry_id']]['entry_id'] = $row['child_entry_id'];
				$output['parent_id_'.$row['parent_entry_id']]['field_id_'.$row['parent_field_id']]['child_id_'.$row['child_entry_id']]['channel_id'] = $row['channel_id'];
			}
			$query->free_result();
			
		} elseif( ! empty($rel_data) && version_compare($playa_ver, "4.0", '<')) {
			
			// For Playa 3 and below, which store rel_id
			$this->EE->db->select(array("exp_channel_titles.entry_id", "exp_relationships.rel_child_id", "exp_relationships.rel_parent_id", "exp_relationships.rel_id", "exp_channel_titles.title", "exp_channel_titles.channel_id"));
			$this->EE->db->from("exp_channel_titles");
			$this->EE->db->join("exp_relationships", "exp_channel_titles.entry_id = exp_relationships.rel_child_id");
			$this->EE->db->where_in("exp_relationships.rel_parent_id", $entry_ids);
			$this->EE->db->where_in("exp_relationships.rel_id", $rel_data);
			$query = $this->EE->db->get();
			
			foreach($query->result_array() as $row)
			{
				$output['parent_id_'.$row['rel_parent_id']]['rel_id_'.$row['rel_id']]['child_id_'.$row['entry_id']]['title'] = $row['title'];
				$output['parent_id_'.$row['rel_parent_id']]['rel_id_'.$row['rel_id']]['child_id_'.$row['entry_id']]['entry_id'] = $row['entry_id'];
				$output['parent_id_'.$row['rel_parent_id']]['rel_id_'.$row['rel_id']]['child_id_'.$row['entry_id']]['channel_id'] = $row['channel_id'];
				
			}
			$query->free_result();
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
	*	@param	$installed_addons	array	An array of installed addons and their version numbers (optional)
	*	@return					A query to be integrated with entry results. Should be in CI Active Record format ($this->EE->db->…)
	*/
	function zenbu_result_query($rules = array(), $field_id = "", $fieldtypes, $already_queried = FALSE, $installed_addons)
	{
		// Let's not even go through this if there isn't a word or rules to search in the first place
		if(empty($rules))
		{
			return;
		}
		
		// Get Playa version given differences between versions 3 and 4
		$playa_ver = (isset($installed_addons['modules_versions']['Playa'])) ? $installed_addons['modules_versions']['Playa'] : '';
		$rel_linker = "";

		foreach($rules as $key => $rule)
		{
			$in = $rule['cond'];
			$keyword = $rule['val'];
			$rule_field_id = (strncmp($rule['field'], 'field_', 6) == 0) ? substr($rule['field'], 6) : '';

			// Blank values just return all entries, like this never happened
			if( ! in_array($in, array('isempty', 'isnotempty')) && empty($keyword))
			{
				return;
			}
			
			if(strncmp($rule['field'], 'field_', 6) == 0 && isset($fieldtypes['fieldtype'][$rule_field_id]) && $fieldtypes['fieldtype'][$rule_field_id] == "playa" && $rule_field_id == $field_id)
			{
				switch($in)
				{
					case 'contains':
						$like_query = "LIKE '%".$this->EE->db->escape_like_str($keyword)."%' ";
					break;
					case 'doesnotcontain':
						$like_query = "NOT LIKE '%".$this->EE->db->escape_like_str($keyword)."%' ";
					break;
					case 'beginswith':
						$like_query = "LIKE '".$this->EE->db->escape_like_str($keyword)."%' ";
					break;
					case 'doesnotbeginwith':
						$like_query = "NOT LIKE '".$this->EE->db->escape_like_str($keyword)."%' ";
					break;
					case 'endswith':
						$like_query = "LIKE '%".$this->EE->db->escape_like_str($keyword)."' ";
					break;
					case 'doesnotendwith':
						$like_query = "NOT LIKE '%".$this->EE->db->escape_like_str($keyword)."' ";
					break;
					case 'containsexactly':
						$like_query = "LIKE '".$this->EE->db->escape_like_str($keyword)."' ";
					break;
					case 'isempty':
						$where = '(exp_channel_data.field_id_' . $rule_field_id . ' = "" OR exp_channel_data.field_id_' . $rule_field_id . ' IS NULL)';
						$this->EE->db->where($where);
						return; // That's all we need in this case, so stop here
					break;
					case 'isnotempty':
						$where = '(exp_channel_data.field_id_' . $rule_field_id . ' != "0" AND exp_channel_data.field_id_' . $rule_field_id . ' != "" AND exp_channel_data.field_id_' . $rule_field_id . ' IS NOT NULL)';
						$this->EE->db->where($where);
						return; // That's all we need in this case, so stop here
					break;
				}

				$parent_entries = $this->_playa_keyword_query($playa_ver, $like_query, $field_id);	
				
				/**
				 * Extra query for negatives
				 * -------------------------
				 * Negative rules, such as "does not contain", need two-step verification before outputting
				 * the entry_id array for the final query. This is because some entries could be flagged as matching the query but
				 * based on another row in exp_playa_relationships. Eg. Entry has rel entries A,B,C. Searching "not A", the above
				 * would flag the entry based on B,C. 
				 * Therefore parent_ids from above are compared to the opposite query (giving opposite results) parent_ids below. 
				 * The results below are substracted from the results above.
				 */
				if(in_array($in, array('doesnotcontain', 'doesnotbeginwith', 'doesnotendwith')))
				{
					switch($in)
					{
						case 'doesnotcontain':
							$like_query_negatives = "LIKE '%".$this->EE->db->escape_like_str($keyword)."%' ";
						break;
						case 'doesnotbeginwith':
							$like_query_negatives = "LIKE '".$this->EE->db->escape_like_str($keyword)."%' ";
						break;
						case 'doesnotendwith':
							$like_query_negatives = "LIKE '%".$this->EE->db->escape_like_str($keyword)."' ";
						break;
					}

					$parent_entries_negatives = $this->_playa_keyword_query($playa_ver, $like_query_negatives, $field_id);	

					$parent_entries = isset($parent_entries_negatives) ? array_diff($parent_entries, $parent_entries_negatives) : $parent_entries;
				}
				
				if( ! empty($parent_entries))
				{
					$this->EE->db->where_in('exp_channel_titles.entry_id', $parent_entries);
				} else {
					$this->EE->db->where('exp_channel_titles.entry_id', "0");
				}
			} //if field, keyword, playa field checks
		} // foreach
	}
	
	/**
	 * ===============================
	 * function _playa_keyword_query 
	 * ===============================
	 * Builds an array of targeted entries based on a simple db query,
	 * used with zenbu_result_query method
	 * @param  string $playa_ver  The Playa version
	 * @param  string $like_query The query string, which changes based on filter rule
	 * @param  string $field_id   The target custom field_id
	 * @return array  $output_array	An array of result entries
	 */
	function _playa_keyword_query($playa_ver, $like_query, $field_id)
	{
		$output_array = array();
		
		// Slightly different queries based on Playa version
		if(version_compare($playa_ver, "4.0", '<'))
		{
			$rel_keyword_query = $this->EE->db->query("/* Zenbu: Playa query for entries (< v.4) */\nSELECT exp_channel_titles.entry_id, exp_channel_titles.title, exp_relationships.rel_id 
			FROM exp_channel_titles 
			JOIN exp_relationships 
			ON exp_channel_titles.entry_id = exp_relationships.rel_child_id 
			WHERE exp_channel_titles.title " . $like_query);
			$rel_linker = "rel_id";
		} elseif (version_compare($playa_ver, "4.0", '>=')) {
		
			$rel_keyword_query = $this->EE->db->query("/* Zenbu: Playa query for entries */\nSELECT p.parent_entry_id, e.entry_id, e. title 
			FROM exp_channel_titles e
			JOIN exp_playa_relationships p ON e.entry_id = p.child_entry_id 
			WHERE p.parent_field_id = " . $field_id . "
			AND e.title " . $like_query);
			$rel_linker = "parent_entry_id";
		}

		if($rel_keyword_query->num_rows() > 0)
		{
			foreach($rel_keyword_query->result_array() as $row)
			{
				$output_array[] = $row[$rel_linker];
			}
		}
		$rel_keyword_query->free_result();

		return $output_array;
	}

} // END CLASS

/* End of file playa.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/playa.php */
?>