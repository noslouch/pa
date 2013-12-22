<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
*	ZENBU THIRD-PARTY FIELDTYPE SUPPORT
*	============================================
*	DevDemon's Tagger field
*	@author	Brad Parscale - DevDemon http://www.devdemon.com/
*	@link	http://www.devdemon.com/tagger/
*	============================================
*	File tagger.php
*	
*/

class Zenbu_tagger_ft extends Tagger_ft
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
	function zenbu_display($entry_id, $channel_id, $data, $tagger_data = array(), $field_id, $settings, $rules = array())
	{
		if( ! isset($entry_id) || empty($entry_id) || empty($tagger_data))
		{
			return '&nbsp;';
		}
		$output = "";
	
		if(isset($tagger_data['entry_id_'.$entry_id]))
		{
			foreach($tagger_data['entry_id_'.$entry_id] as $key => $tag_name)
			{
				$output .= $tag_name.', ';
			}
			$output = substr($output, 0, -2);
		}
		
		$output = $this->EE->zenbu_display->highlight($output, $rules, 'field_'.$field_id);
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
	function zenbu_get_table_data($entry_ids, $field_ids, $channel_id)
	{
		$output = array();
		if( empty($entry_ids) || empty($field_ids))
		{
			return $output;
		}
		
		$this->EE->db->select("exp_tagger.tag_name");
		$this->EE->db->select("exp_tagger_links.entry_id");
		$this->EE->db->join("exp_tagger_links", "exp_tagger.tag_id = exp_tagger_links.tag_id");
		foreach($entry_ids as $key => $entry_id)
		{
			$this->EE->db->or_where("exp_tagger_links.entry_id", $entry_id);
		}
		$query = $this->EE->db->get("exp_tagger");
		
		if($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row)
			{
				$output['entry_id_'.$row['entry_id']][] = $row['tag_name'];
			}
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
	function zenbu_result_query($rules = array())
	{
		if(empty($rules))
		{
			return;
		}
		
		foreach($rules as $rule)
		{
			$in 		= $rule['cond'];
			$keyword 	= $rule['val'];
			if(strncmp($rule['field'], 'field_', 6) == 0 && ! empty($keyword))
			{
				if($in == "doesnotcontain")
				{
					$query_not_like = $this->EE->db->query("SELECT exp_tagger_links.entry_id FROM exp_tagger_links JOIN exp_tagger ON exp_tagger_links.tag_id = exp_tagger.tag_id WHERE exp_tagger.tag_name NOT LIKE '%".$this->EE->db->escape_like_str($keyword)."%' GROUP BY exp_tagger_links.entry_id");
					$query_like = $this->EE->db->query("SELECT exp_tagger_links.entry_id FROM exp_tagger_links JOIN exp_tagger ON exp_tagger_links.tag_id = exp_tagger.tag_id WHERE exp_tagger.tag_name LIKE '%".$this->EE->db->escape_like_str($keyword)."%' GROUP BY exp_tagger_links.entry_id");
					$entries_not_like = array();
					$entries_like = array();
					
					if($query_not_like->num_rows() > 0)
					{
						foreach($query_not_like->result_array() as $row)
						{
							$entries_not_like[] = $row['entry_id'];
						}
					}
					
					if($query_like->num_rows() > 0)
					{
						foreach($query_like->result_array() as $row)
						{
							$entries_like[] = $row['entry_id'];
						}
					}
					
					$entries = array_intersect($entries_not_like, $entries_like);
					if(empty($entries))
					{
						// Eg. Search tagger fields that do not contain a word that doesn't exist. This should show all entries. So just skip all this.
						return;
					}
				
				} else {
					$query_like = $this->EE->db->query("SELECT exp_tagger_links.entry_id FROM exp_tagger_links JOIN exp_tagger ON exp_tagger_links.tag_id = exp_tagger.tag_id WHERE exp_tagger.tag_name LIKE '%".$this->EE->db->escape_like_str($keyword)."%' GROUP BY exp_tagger_links.entry_id");
				}
			
			
				if($query_like->num_rows() > 0)
				{
					$entries = array();
					foreach($query_like->result_array() as $row)
					{
						$entries[] = $row['entry_id'];
					}
					if($in == "doesnotcontain")
					{
						$this->EE->db->where_not_in("exp_channel_titles.entry_id", $entries);
					} else {
						$this->EE->db->where_in("exp_channel_titles.entry_id", $entries);
					}
				} else {
					$entries[] = 0;
					$this->EE->db->where_in("exp_channel_titles.entry_id", $entries);
				}
			}
		}
	}
	
	
} // END CLASS

/* End of file tagger.php */
/* Location: ./system/expressionengine/third_party/zenbu/fieldtypes/tagger.php */
?>