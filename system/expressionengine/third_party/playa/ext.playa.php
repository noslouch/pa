<?php if (! defined('BASEPATH')) exit('No direct script access allowed');


require_once PATH_THIRD.'playa/config.php';


/**
 * Playa Extension Class for ExpressionEngine 2
 *
 * @package   Playa
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Playa_ext {

	var $name = PLAYA_NAME;
	var $version = PLAYA_VER;
	var $description = PLAYA_DESC;
	var $settings_exist = 'n';
	var $docs_url = PLAYA_DOCS;

	/**
	 * Extension Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['playa']))
		{
			$this->EE->session->cache['playa'] = array();
		}

		$this->cache =& $this->EE->session->cache['playa'];

		// -------------------------------------------
		//  Load the helper
		// -------------------------------------------

		if (! class_exists('Playa_Helper'))
		{
			require_once PATH_THIRD.'playa/helper.php';
		}

		$this->helper = new Playa_Helper();
	}

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		$this->EE->db->insert('extensions', array(
			'class'    => 'Playa_ext',
			'method'   => 'channel_entries_tagdata',
			'hook'     => 'channel_entries_tagdata',
			'settings' => '',
			'priority' => 9,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}

	/**
	 * Update Extension
	 */
	function update_extension($current = FALSE)
	{
		if (! $current || $current == $this->version)
		{
			return FALSE;
		}

		if (version_compare($current, '3.0.4', '<'))
		{
			$this->EE->db->where('class', 'Playa_ext');
			$this->EE->db->where('hook', 'channel_entries_tagdata');
			$this->EE->db->update('extensions', array('priority' => 9));
		}

		$this->EE->db->where('class', 'Playa_ext');
		$this->EE->db->update('extensions', array('version' => $this->version));
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		$this->EE->db->query('DELETE FROM exp_extensions WHERE class = "Playa_ext"');
	}

	// --------------------------------------------------------------------

	/**
	 * Get Site Fields
	 */
	private function _get_site_fields($site_id)
	{
		if (! isset($this->cache['site_fields'][$site_id]))
		{
			$this->EE->db->select('field_id, field_name');
			$this->EE->db->where('field_type', 'playa');
			if ($site_id) $this->EE->db->where('site_id', $site_id);

			$fields = $this->EE->db->get('channel_fields')
			                       ->result();

			if ($fields)
			{
				foreach ($fields as $field)
				{
					$this->cache['site_fields'][$site_id][$field->field_id] = $field->field_name;
				}
			}
			else
			{
				$this->cache['site_fields'][$site_id] = array();
			}
		}

		return $this->cache['site_fields'][$site_id];
	}

	/**
	 * channel_entries_tagdata hook
	 */
	function channel_entries_tagdata($tagdata, $row, &$Channel)
	{
		// has this hook already been called?
		if ($this->EE->extensions->last_call)
		{
			$tagdata = $this->EE->extensions->last_call;
		}

		// cache the row data
		if (! isset($this->cache['entry_rows'][$row['entry_id']]))
		{
			$this->cache['entry_rows'][$row['entry_id']] =& $row;
		}

		$this->row =& $row;

		// -------------------------------------------
		//  Parse module tags
		// -------------------------------------------

		$this->_parse_tags($tagdata, 'exp:playa:([a-z_]+)');

		// -------------------------------------------
		//  Parse fieldtype tags
		// -------------------------------------------

		// ignore if disable="custom_fields" set
		$disable = explode('|', $this->EE->TMPL->fetch_param('disable'));

		if (! in_array('custom_fields', $disable))
		{
			$site_id = isset($row['entry_site_id']) ? $row['entry_site_id'] : 0;
			$fields = $this->_get_site_fields($site_id);

			foreach ($fields as $field_id => $field_name)
			{
				$this->_parse_tags($tagdata, $field_name, $field_id);
			}
		}

		unset($this->row);

		return $tagdata;
	}

	/**
	 * Searhes for either Playa module or fieldtype tags,
	 * adds entry_id= and possibly field_id= params,
	 * and gives them a var prefix if there isn't one already.
	 *
	 * @param string &$tagdata
	 * @param string $tag_re
	 * @param int    $field_id
	 */
	private function _parse_tags(&$tagdata, $tag_re, $field_id = FALSE)
	{
		$offset = 0;
		while (preg_match('/\{('.$tag_re.')(?=[\s}])/', $tagdata, $match, PREG_OFFSET_CAPTURE, $offset))
		{
			$otag_pos = $match[0][1];
			$params_pos = $otag_pos + strlen($match[0][0]);

			// find the RD
			$ld_count = 1;
			$rd_count = 0;
			$rd_search_offset = $params_pos;
			$malformed_tag = FALSE;

			do {
				$rd_pos = strpos($tagdata, RD, $rd_search_offset);

				// If there's no RD, it's a malformed tag
				if ($rd_pos === FALSE)
				{
					$malformed_tag = TRUE;
					break;
				}

				$rd_count++;

				// get the total number of LDs we just went by
				$length = $rd_pos - $rd_search_offset;
				if ($length > 0)
				{
					$ld_count += substr_count($tagdata, LD, $rd_search_offset, $length);
				}

				// next time start searching right after the current RD
				$rd_search_offset = $rd_pos + 1;
			} while ($ld_count != $rd_count);

			if ($malformed_tag) break;

			$params = substr($tagdata, $params_pos, $rd_pos-$params_pos);
			$otag_endpos = $rd_search_offset;

			// add entry_id= and possibly field_id= to the params
			$extra_params = ' entry_id="'.$this->row['entry_id'].'"';
			if ($field_id) $extra_params .= ' field_id="'.$field_id.'"';
			$params = $extra_params.' '.$params;

			// find the closing tag
			$ctag = LD.'/'.$match[1][0].RD;
			$ctag_pos = strpos($tagdata, $ctag, $otag_endpos);
			if ($ctag_pos !== FALSE)
			{
				$inner_tagdata = substr($tagdata, $otag_endpos, $ctag_pos-$otag_endpos);

				// make sure that the closing tag doesn't belong to another opening tag
				if (strpos($inner_tagdata, $match[0][0].'}') !== FALSE || strpos($inner_tagdata, $match[0][0].' ') !== FALSE)
				{
					$ctag_pos = FALSE;
				}
			}

			if ($ctag_pos !== FALSE)
			{
				$ctag_endpos = $ctag_pos + strlen($ctag);
			}
			else
			{
				$ctag_endpos = $otag_endpos;
				$inner_tagdata = '';
			}

			// do our magic
			$before = substr($tagdata, 0, $otag_pos);
			$after = substr($tagdata, $ctag_endpos);

			$func = !empty($match[2][0]) ? $match[2][0] : 'children';
			$module_tag = $this->helper->create_module_tag($params, $inner_tagdata, $func);

			$tagdata = $before.$module_tag.$after;

			// update the offset for the next tag
			$offset = $otag_pos + strlen($module_tag);
		}
	}
}
