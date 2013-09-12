<?php if (!defined('BASEPATH')) die('No direct script access allowed');


// include config file
include_once dirname(dirname(__FILE__)).'/tagger/config.php';

/**
 * Tagger Module FieldType
 *
 * @package			DevDemon_Tagger
 * @version			2.1.5
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class Tagger_ft extends EE_Fieldtype
{
	/**
	 * Field info (Required)
	 *
	 * @var array
	 * @access public
	 */
	var $info = array(
		'name' 		=> DDTAGGER_NAME,
		'version'	=> DDTAGGER_VERSION
	);

	public $has_array_data = TRUE;


	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		if (version_compare(APP_VER, '2.1.4', '>')) { parent::__construct(); } else { parent::EE_Fieldtype(); }

		$this->EE->load->add_package_path(PATH_THIRD . 'tagger/');

		$this->EE->lang->loadfile('tagger');
		$this->EE->load->library('tagger_helper');

		$this->site_id = $this->EE->config->item('site_id');
		$this->EE->tagger_helper->define_theme_url();
	}

	// ********************************************************************************* //

	/**
	 * Display the field in the publish form
	 *
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 *
	 * $this->settings =
	 *  Array
	 *  (
	 *      [field_id] => nsm_better_meta__nsm_better_meta
	 *      [field_label] => NSM Better Meta
	 *      [field_required] => n
	 *      [field_data] =>
	 *      [field_list_items] =>
	 *      [field_fmt] =>
	 *      [field_instructions] =>
	 *      [field_show_fmt] => n
	 *      [field_pre_populate] => n
	 *      [field_text_direction] => ltr
	 *      [field_type] => nsm_better_meta
	 *      [field_name] => nsm_better_meta__nsm_better_meta
	 *      [field_channel_id] =>
	 *  )
	 */
	public function display_field($data)
	{
		// -----------------------------------------
		// Some Globals
		// -----------------------------------------
		$vData = array();
		$vData['assigned_tags'] = array();
		$vData['most_used_tags'] = array();
		$vData['field_name'] = $this->field_name;
		$vData['field_id'] = $this->field_id;
		$vData['channel_id'] = ($this->EE->input->get_post('channel_id') != FALSE) ? $this->EE->input->get_post('channel_id') : 0;

		if (REQ == 'CP')
		{
			$vData['field_name'] = 'field_id_' .$this->field_id;
		}

		// Post DATA?
		if (isset($_POST[$this->field_name])) {
			$data = $_POST[$this->field_name];
		}

		// -----------------------------------------
		// Add Global JS & CSS & JS Scripts
		// -----------------------------------------
		$this->EE->tagger_helper->addMcpAssets('gjs');
        $this->EE->tagger_helper->addMcpAssets('css', 'tagger_pbf.css?v='.DDTAGGER_VERSION, 'tagger', 'pbf');
        $this->EE->tagger_helper->addMcpAssets('js', 'jquery.tagsinput.js?v='.DDTAGGER_VERSION, 'jquery', 'multiselect');
        $this->EE->tagger_helper->addMcpAssets('js', 'tagger_pbf.js?v='.DDTAGGER_VERSION, 'tagger', 'pbf');

		$this->EE->cp->add_js_script(array('ui' => array('sortable', 'autocomplete')));


		// Defaults
		$vData['config'] = $this->EE->config->item('tagger_defaults');

		 // Existing?
		if (isset($this->settings['tagger']) == TRUE) $vData['config'] = array_merge($vData['config'], $this->settings['tagger']);

		// -----------------------------------------
		// Grab most used tags
		// -----------------------------------------
		$this->EE->db->select('tag_name');
		$this->EE->db->from('exp_tagger');
		$this->EE->db->where('total_entries >', 0);
		$this->EE->db->where('site_id', $this->site_id);
		$this->EE->db->order_by('total_entries', 'desc');
		$this->EE->db->limit(25);
		$query = $this->EE->db->get();

		foreach ($query->result() as $row)
		{
			$vData['most_used_tags'][] = $row->tag_name;
		}

		// Sometimes you forget to fill in field
		// and you will send back to the form
		// We need to fil lthe values in again.. *Sigh* (anyone heard about AJAX!)
		if (is_array($data) == TRUE && isset($data['tags']) == TRUE)
		{
			foreach ($data['tags'] as $tag)
			{
				$vData['assigned_tags'][] = $tag;
			}

			return $this->EE->load->view('pbf_field', $vData, TRUE);
		}

		// -----------------------------------------
		// Grab assigned tags
		// -----------------------------------------
		if ($this->EE->input->get_post('entry_id') != FALSE)
		{
			$this->EE->db->select('t.tag_name');
			$this->EE->db->from('exp_tagger_links tp');
			$this->EE->db->join('exp_tagger t', 'tp.tag_id = t.tag_id', 'left');
			$this->EE->db->where('tp.entry_id', $this->EE->input->get_post('entry_id'));
			$this->EE->db->where('tp.field_id', $vData['field_id']);
			$this->EE->db->where('tp.site_id', $this->site_id);
			$this->EE->db->where('tp.type', 1);
			$this->EE->db->order_by('tp.tag_order');
			$query = $this->EE->db->get();

			foreach ($query->result() as $row)
			{
				$vData['assigned_tags'][] = $row->tag_name;
			}
		}

		return $this->EE->load->view('pbf_field', $vData, TRUE);
	}

	// ********************************************************************************* //

	/**
	 * Validates the field input
	 *
	 * @param $data Contains the submitted field data.
	 * @return mixed Must return TRUE or an error message
	 */
	public function validate($data)
	{
		// Is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			if (isset($data['tags']) == FALSE OR empty($data['tags']) == TRUE)
			{
				return $this->EE->lang->line('tagger:required_field');
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Preps the data for saving
	 *
	 * @param $data Contains the submitted field data.
	 * @return string Data to be saved
	 */
	public function save($data)
	{
		// Single Field UI?
		if (isset($data['single_field']) == TRUE)
		{
			$data['tags'] = explode('||', $data['single_field']);
		}

		$this->EE->session->cache['Tagger']['FieldData'][$this->field_id] = $data;

		if (isset($data['tags']) == FALSE OR empty($data['tags']) == TRUE)
		{
			return '';
		}
		else
		{
			return implode(',', $data['tags']);
		}
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is saved.
	 * Called after an entry is added or updated.
	 * Available data is identical to save, but the settings array includes an entry_id.
	 *
	 * @param $data Contains the submitted field data. (Returned by save())
	 * @return void
	 */
	public function post_save($data)
	{
		if (isset($this->EE->session->cache['Tagger']['FieldData'][$this->field_id]) == FALSE) return;

		// -----------------------------------------
		// Some Vars
		// -----------------------------------------
		$data = $this->EE->session->cache['Tagger']['FieldData'][$this->field_id];
		$entry_id = $this->settings['entry_id'];
		$channel_id = $this->EE->input->post('channel_id');
		$field_id = $this->field_id;
		$author_id = $this->EE->input->post('author') ? $this->EE->input->post('author') : $this->EE->session->userdata['member_id'];

		// -----------------------------------------
		// Grab all existing tag links
		// -----------------------------------------
		$this->EE->db->select('tag_id, rel_id');
		$this->EE->db->from('exp_tagger_links');
		$this->EE->db->where('entry_id', $entry_id);
		$this->EE->db->where('field_id', $field_id);
		$query = $this->EE->db->get();

		// -----------------------------------------
		// Load Settings
		// -----------------------------------------
		$conf = $this->EE->tagger_helper->grab_settings($this->site_id);

		// lowecase?
		$lc = ($conf['lowercase_tags'] == 'yes') ? TRUE : FALSE;

		// -----------------------------------------
		// Our array empty? Delete them all!!
		// -----------------------------------------
		if (isset($data['tags']) == FALSE OR empty($data['tags']) == TRUE)
		{
			foreach ($query->result() as $row)
			{
				// Delete tag association
				$this->EE->db->where('rel_id', $row->rel_id);
				$this->EE->db->delete('exp_tagger_links');

				// Update total_items
				$this->EE->db->set('total_entries', '(`total_entries` - 1)', FALSE);
				$this->EE->db->where('tag_id', $row->tag_id);
				$this->EE->db->where('site_id', $this->site_id);
				$this->EE->db->update('exp_tagger');
			}

			return;
		}

		// We Only Want Uniques
		$data['tags'] = array_unique($data['tags']);

		// -----------------------------------------
		// Store the ones we already have
		// -----------------------------------------
		$dbtags = array();

		foreach ($query->result() as $trow)
		{
			$dbtags[ $trow->rel_id ] = $trow->tag_id;
		}

		// -----------------------------------------
		// Loop over all assigned tags
		// -----------------------------------------
		foreach ($data['tags'] as $i => $tag)
		{
			// Format the tag
			$tag = $this->EE->tagger_helper->format_tag($tag);

			// No "empty" tags
			if ($tag == FALSE) continue;

			if ($lc == TRUE) $tag = mb_strtolower($tag, 'UTF-8');

			// -----------------------------------------
			// Does it already exist?
			// -----------------------------------------
			$this->EE->db->select('tag_id');
			$this->EE->db->from('exp_tagger');
			$this->EE->db->where('tag_name', $tag);
			$this->EE->db->where('site_id', $this->site_id);
			$this->EE->db->limit(1);
			$q2 = $this->EE->db->get();

			if ($q2->num_rows() == 0) $tag_id = $this->EE->tagger_helper->create_tag($tag);
			else $tag_id = $q2->row('tag_id');

			// -----------------------------------------
			// Is it already assigned (to this entry)
			// -----------------------------------------
			if (in_array($tag_id, $dbtags) == FALSE)
			{
				// -----------------------------------------
				// Data array for insert
				// -----------------------------------------
				$data =	array(	'entry_id'	=>	$entry_id,
								'channel_id'=>	$channel_id,
								'field_id'	=>	$field_id,
								'tag_id'	=>	$tag_id,
								'site_id'	=>	$this->site_id,
								'author_id'	=>	$author_id,
								'type'		=>	1,
								'tag_order'	=>	$i + 1
						);

				// Insert
				$this->EE->db->insert('exp_tagger_links', $data);

				// -----------------------------------------
				// Update total_items
				// -----------------------------------------
				$this->EE->db->set('total_entries', '(`total_entries` + 1)', FALSE);
				$this->EE->db->where('tag_id', $tag_id);
				$this->EE->db->where('site_id', $this->site_id);
				$this->EE->db->update('exp_tagger');
			}
			else
			{
				// Get Rel_ID
				$rel_id = array_search($tag_id, $dbtags);

				// -----------------------------------------
				// Update
				// -----------------------------------------
				$this->EE->db->set('tag_order', $i + 1);
				$this->EE->db->where('rel_id', $rel_id);
				$this->EE->db->update('exp_tagger_links');

				// We need to unset the "dupe" tag
				unset($dbtags[$rel_id]);
			}

			// -----------------------------------------
			// Auto Assign Tags to group
			// -----------------------------------------
			if (isset($this->settings['tagger']['auto_assign_group']) === TRUE && $this->settings['tagger']['auto_assign_group'] > 0)
			{
				$group_id = $this->settings['tagger']['auto_assign_group'];

				// Does it already exists?
				$q = $this->EE->db->select('rel_id')->from('exp_tagger_groups_entries')->where('group_id', $group_id)->where('tag_id', $tag_id)->get();

				if ($q->num_rows() == 0)
				{
					$this->EE->db->insert('tagger_groups_entries', array('tag_id' => $tag_id, 'group_id' => $group_id));
				}
			}
		}

		// -----------------------------------------
		// Remove Old Ones
		// -----------------------------------------
		foreach ($dbtags as $rel_id => $tag_id)
		{
			// -----------------------------------------
			// Delete tag association
			// -----------------------------------------
			$this->EE->db->where('rel_id', $rel_id);
			$this->EE->db->delete('exp_tagger_links');

			// -----------------------------------------
			// Update total_items
			// -----------------------------------------
			$this->EE->db->set('total_entries', '(`total_entries` - 1)', FALSE);
			$this->EE->db->where('tag_id', $tag_id);
			$this->EE->db->where('site_id', $this->site_id);
			$this->EE->db->update('exp_tagger');
		}

		return;
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is deleted.
	 * Called after one or more entries are deleted.
	 *
	 * @param $ids array is an array containing the ids of the deleted entries.
	 * @return void
	 */
	public function delete($ids)
	{
		foreach ($ids as $entry_id)
		{
			// Grab the Tag ID
			$this->EE->db->select('tag_id, rel_id');
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->where('type', 1);
			$this->EE->db->where('site_id', $this->site_id);
			$query = $this->EE->db->get('exp_tagger_links');

			foreach ($query->result() as $row)
			{
				// Delete tag association
				$this->EE->db->where('rel_id', $row->rel_id);
				$this->EE->db->delete('exp_tagger_links');

				// Update total_items
				$this->EE->db->set('total_entries', '(`total_entries` - 1)', FALSE);
				$this->EE->db->where('tag_id', $row->tag_id);
				$this->EE->db->where('site_id', $this->site_id);
				$this->EE->db->update('exp_tagger');
			}

			// Resources are not free
			$query->free_result();
		}
	}

	// ********************************************************************************* //

	/**
	 * Replace the field tag on the frontend.
	 *
	 * @param $data mixed Contains the field data (or prepped data, if using pre_process)
	 * @param $params array Contains field parameters (if any)
	 * @param $tagdata string Contains data between tag (for tag pairs)
	 * @return string
	 */
	public function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		$data = explode(',', $data);

		// If no tagdata, return
		if ($tagdata == FALSE) return implode(', ', $data);

		// Have backspace?
		$backspace = (isset($params['backspace']) == TRUE) ? $params['backspace'] : 0;

		// Have prefix?
		$prefix = ((isset($params['prefix']) == TRUE && $params['prefix'] != FALSE) ? $params['prefix'] : 'tagger') . ':';

		$out = '';

		// Loop through the result
		foreach ($data as $tag)
		{
			$vars = array(	$prefix.'tag_name'		=> $tag,
							$prefix.'urlsafe_tagname' => $this->EE->tagger_helper->urlsafe_tag($tag),
						);

			$out .= $this->EE->TMPL->parse_variables_row($tagdata, $vars);
		}

		// Apply Backspace
		$out = ($backspace > 0) ? substr($out, 0, - $backspace): $out;

		return $out;
	}

	// ********************************************************************************* //

	/**
	 * Display the settings page. The default ExpressionEngine rows can be created using built in methods.
	 * All of these take the current $data and the fieltype name as parameters:
	 *
	 * @param $data array
	 * @access public
	 * @return void
	 */
	public function display_settings($data)
	{
		// -----------------------------------------
		// Defaults
		// -----------------------------------------
		$conf = $this->EE->config->item('tagger_defaults');

		 // Existing?
		if (isset($data['tagger']) == TRUE) $conf = array_merge($conf, $data['tagger']);

		// -----------------------------------------
		// Show Most Used Tags
		// -----------------------------------------
		$row = '';
		$options = array('no' => lang('tagger:no'), 'yes' => lang('tagger:yes'));
		$row .= form_dropdown('tagger[show_most_used]', $options, $conf['show_most_used']);
		$this->EE->table->add_row('<strong>'.lang('tagger:show_most_used').'</strong>', $row);

		// -----------------------------------------
		// Single Field Input
		// -----------------------------------------
		$row = '';
		$options = array('no' => lang('tagger:no'), 'yes' => lang('tagger:yes'));
		$row .= form_dropdown('tagger[single_field]', $options, $conf['single_field']);
		$this->EE->table->add_row('<strong>'.lang('tagger:single_field_input').'</strong>', $row);

		// -----------------------------------------
		// auto_assign_group
		// -----------------------------------------
		$row = '';
		$options = array('0' => $this->EE->lang->line('tagger:select_group'));


		$query = $this->EE->db->select('group_id, group_title')->from('exp_tagger_groups')->where('site_id', $this->site_id)->get();
		foreach ($query->result() as $group) $options[$group->group_id] = $group->group_title;

		if (empty($options) === false)
		{
			$row .= form_dropdown('tagger[auto_assign_group]', $options, $conf['auto_assign_group']);
			$this->EE->table->add_row('<strong>'.lang('tagger:auto_assign_group').'</strong>', $row);
		}

	}

	// ********************************************************************************* //

	/**
	 * Save the fieldtype settings.
	 *
	 * @param $data array Contains the submitted settings for this field.
	 * @access public
	 * @return array
	 */
	public function save_settings($data)
	{
		$settings = array('tagger' => array());

		if (isset($_POST['tagger']) == FALSE) return $settings;

		$P = $_POST['tagger'];
		$S = array();

		// Show Most Read
		$S['show_most_used'] = (isset($P['show_most_used']) == TRUE) ? $P['show_most_used'] : 'no';

		// Use Single Field
		$S['single_field'] = (isset($P['single_field']) == TRUE) ? $P['single_field'] : 'no';

		// auto_assign_group
		$S['auto_assign_group'] = (isset($P['auto_assign_group']) == TRUE) ? $P['auto_assign_group'] : 0;

		$settings['tagger'] = $S;

		return $settings;
	}

	// ********************************************************************************* //

	/**
	 * Replace Tag - Replace the field tag on the frontend.
	 *
	 * @param  mixed   $data    contains the field data (or prepped data, if using pre_process)
	 * @param  array   $params  contains field parameters (if any)
	 * @param  boolean $tagdata contains data between tag (for tag pairs)
	 * @return string           template data
	 */
	public function replace_tag2($data, $params=array(), $tagdata = FALSE)
	{
		// Variable prefix
		$prefix = isset($params['params']) ? $params['params'] . ':' : 'tagger:';

		// We need an entry_id
		$entry_id = $this->row['entry_id'];
		$field_id = $this->field_id;
		$orderby_list		= array('tag_name', 'entry_date', 'hits', 'total_entries', 'tag_order');

		// -----------------------------------------
		// Some Params
		// -----------------------------------------
		$orderby = (isset($params['orderby']) === TRUE && in_array($params['orderby'], $orderby_list)) ? 't.'.$params['orderby'] : 'tl.tag_order';
		$limit = isset($params['limit']) ? $params['limit'] : 99;
		$sort = (isset($params['backspace']) === TRUE && $params['backspace'] == 'desc' ) ? 'DESC': 'ASC';
		$backspace = isset($params['backspace']) ? $params['backspace'] : 0;

		// -----------------------------------------
		// Start SQL
		// -----------------------------------------
		$this->EE->db->select('t.*');
		$this->EE->db->from('exp_tagger t');
		$this->EE->db->join('exp_tagger_links tl', 'tl.tag_id = t.tag_id', 'left');
		$this->EE->db->where('tl.entry_id', $entry_id);
		$this->EE->db->where('tl.field_id', $field_id);
		$this->EE->db->where('tl.type', 1);
		$this->EE->db->order_by($orderby, $sort);
		$this->EE->db->limit($limit);
		$query = $this->EE->db->get();

		// -----------------------------------------
		// No Tags?
		// -----------------------------------------
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item('TAGGER: No tags found.');
			return $this->EE->tagger_helper->custom_no_results_conditional($prefix.'no_tags', $tagdata);
		}

		$out = '';
		$count = 0;
		$total = $query->num_rows();

		// -----------------------------------------
		// Loop through all tags
		// -----------------------------------------
		foreach ($query->result() as $row)
		{
			$count++;
			$vars = array(	$prefix.'tag_name'		=> $row->tag_name,
							$prefix.'urlsafe_tagname' => $this->EE->tagger_helper->urlsafe_tag($row->tag_name),
							$prefix.'unitag'		=> $this->EE->tagger_helper->unitag($row->tag_id, $row->tag_name),
							$prefix.'total_hits'	=> $row->hits,
							$prefix.'total_items'	=> $row->total_entries,
							$prefix.'count'			=> $count,
							$prefix.'total_tags'	=> $total,
						);

			$out .= $this->EE->TMPL->parse_variables_row($tagdata, $vars);
		}

		// Apply Backspace
		$out = ($backspace > 0) ? substr($out, 0, - $backspace): $out;

		// Resources are not free
		$query->free_result();

		return $out;
	}

	// ********************************************************************************* //


}

/* End of file ft.tagger.php */
/* Location: ./system/expressionengine/third_party/tagger/ft.tagger.php */
