<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Low_reorder_base'))
{
	require_once(PATH_THIRD.'low_reorder/base.low_reorder.php');
}

/**
 * Low Reorder Module class
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2012, Low
 */
class Low_reorder extends Low_reorder_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	* Return data
	*
	* @access      public
	* @var         string
	*/
	public $return_data = '';

	// --------------------------------------------------------------------

	/**
	* Set ID
	*
	* @access      public
	* @var         int
	*/
	private $set_id;

	/**
	* Category ID
	*
	* @access      public
	* @var         int
	*/
	private $cat_id;

	/**
	* Set instance
	*
	* @access      public
	* @var         array
	*/
	private $set;

	/**
	* Entry ids for set/cat combo
	*
	* @access      public
	* @var         array
	*/
	private $entry_ids = array();

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	* Display entries in order
	*
	* @access      public
	* @return      string
	*/
	public function entries()
	{
		// Set low_reorder param so extension kicks in
		$this->EE->TMPL->tagparams['low_reorder'] = 'yes';

		// --------------------------------------
		// Initiate set to get set_id, cat_id and entry_ids
		// --------------------------------------

		$this->_init_set();
		$this->_prep_no_results();

		// --------------------------------------
		// Cache the set_id and cat_it
		// --------------------------------------

		low_set_cache($this->package, 'set_id', $this->set_id);
		low_set_cache($this->package, 'cat_id', $this->cat_id);

		// --------------------------------------
		// Check fallback parameter
		// --------------------------------------

		$fallback = ($this->EE->TMPL->fetch_param('fallback') == 'yes')
		          ? '_channel_entries'
		          : '_empty_set';

		// --------------------------------------
		// Check if that results into entry_ids
		// --------------------------------------

		if (empty($this->entry_ids))
		{
			return $this->$fallback();
		}

		// --------------------------------------
		// Check existing entry_id parameter
		// --------------------------------------

		if (isset($this->EE->TMPL->tagparams['entry_id']) && strlen($this->EE->TMPL->tagparams['entry_id']))
		{
			$this->_log('entry_id parameter found, filtering ordered entries accordingly');

			// Get the parameter value
			list($ids, $in) = low_explode_param($this->EE->TMPL->tagparams['entry_id']);

			// Either remove $ids from $entry_ids OR limit $entry_ids to $ids
			$method = $in ? 'array_intersect' : 'array_diff';

			// Get list of entry ids that should be listed
			$this->entry_ids = $method($this->entry_ids, $ids);
		}

		// If that results in empty ids, bail out again
		if (empty($this->entry_ids))
		{
			return $this->$fallback();
		}

		// --------------------------------------
		// Add fixed_order to parameters
		// --------------------------------------

		$this->set['parameters']['fixed_order'] = implode('|', $this->entry_ids);

		// --------------------------------------
		// Set template parameters
		// --------------------------------------

		// Check whether to force template params or not
		$force = ($this->EE->TMPL->fetch_param('force_set_params', 'no') == 'yes');

		// Set the params
		$this->_set_template_parameters($force);

		// --------------------------------------
		// Use channel module to generate entries
		// --------------------------------------

		return $this->_channel_entries();
	}

	/**
	* Return pipe-delimited list of ordered entry_ids
	*
	* @access      public
	* @return      string
	*/
	public function entry_ids()
	{
		// --------------------------------------
		// Initiate set
		// --------------------------------------

		$this->_init_set();
		$this->_prep_no_results();

		// --------------------------------------
		// Get some parameters and check pair tag
		// --------------------------------------

		$pair       = ($tagdata = $this->EE->TMPL->tagdata) ? TRUE : FALSE;
		$no_results = $this->EE->TMPL->fetch_param('no_results');
		$separator  = $this->EE->TMPL->fetch_param('separator', '|');

		// --------------------------------------
		// Create single string from entry ids
		// --------------------------------------

		$entry_ids = empty($this->entry_ids) ? $no_results : implode($separator, $this->entry_ids);

		// --------------------------------------
		// Parse+return or just return, depending on tag pair or not
		// --------------------------------------

		if ($pair)
		{
			return $this->EE->TMPL->parse_variables_row($tagdata, array(
				'low_reorder:entry_ids' => $entry_ids
			));
		}
		else
		{
			return $entry_ids;
		}
	}

	// --------------------------------------------------------------------

	/**
	* Add leading 0's to count
	*
	* @access      public
	* @return      string
	*/
	public function pad()
	{
		// Get parameters
		$input  = (string) $this->EE->TMPL->fetch_param('input', '');
		$length = (int) $this->EE->TMPL->fetch_param('length', 1);
		$string = (string) $this->EE->TMPL->fetch_param('string', '0');
		$type   = ($this->EE->TMPL->fetch_param('type', 'left') != 'right') ?  STR_PAD_LEFT : STR_PAD_RIGHT;

		// Add padding if necessary
		if (strlen($input) < $length)
		{
			$input = str_pad($input, $length, $string, $type);
		}

		// return the formatted number
		return $input;
	}

	// --------------------------------------------------------------------

	/**
	* Get next entry in custom order
	*
	* @access      public
	* @return      string
	*/
	public function next_entry()
	{
		return $this->_prev_next('next');
	}

	/**
	* Get previous entry in custom order
	*
	* @access      public
	* @return      string
	*/
	public function prev_entry()
	{
		return $this->_prev_next('prev');
	}

	/**
	* Get next or previous entry in custom order
	*
	* @access      private
	* @param       string
	* @return      string
	*/
	private function _prev_next($which)
	{
		// --------------------------------------
		// Initiate set
		// --------------------------------------

		$this->_init_set();
		$this->_prep_no_results();

		// --------------------------------------
		// Get other parameters
		// --------------------------------------

		$params = array('entry_id', 'url_title', 'prefix', 'no_results', 'loop');

		foreach ($params AS $param)
		{
			$$param = $this->EE->TMPL->fetch_param($param);
		}

		// --------------------------------------
		// Get set entries
		// --------------------------------------

		if ( ! ($entries = $this->entry_ids))
		{
			return $this->_empty_set();
		}

		// --------------------------------------
		// We need a $entry_id or $url_title to go on
		// --------------------------------------

		if ( ! $entry_id && ! $url_title)
		{
			$this->_log('No entry_id or url_title given, returning empty string');
			return;
		}

		// --------------------------------------
		// Make sure we've got an entry id
		// --------------------------------------

		if ( ! $entry_id && strlen($url_title))
		{
			// Get entry id by url_title
			$entry_id = $this->_get_entry_id($url_title, $entries);
		}

		// Initiate row
		$row = array();

		// --------------------------------------
		// Get the current order and filter out current
		// --------------------------------------

		if ($entries && $entry_id)
		{
			// Reverse it for previous entries
			if ($which == 'prev')
			{
				$entries = array_reverse($entries);
			}

			// Get current entry's index
			$index = array_search($entry_id, $entries);

			// Get entries above current, if any, and if we're looping
			$top = ($loop == 'yes') ? array_slice($entries, 0, $index) : array();

			// Get entries below current
			$bottom = array_slice($entries, $index + 1);

			// Combine bottom and top to get stack of entries that could be the prev/next entry
			$entries = array_merge($bottom, $top);

			// --------------------------------------
			// If we still have entries, go and get them from the DB
			// --------------------------------------

			if ($entries)
			{
				// Log the entries for debugging purposes
				$this->_log("Getting {$which} entry from stack ".implode('|', $entries));

				/*
					// Using the Channel:entries tag for displaying
					// Strip optional prefix from template data
					$this->_strip_prefix($prefix);

					// Parameters to set
					$tagparams = array(
						'fixed_order' => implode('|', $entries),
						'limit'       => '1',
						'sort'        => 'asc',
						'dynamic'     => 'no'
					);

					// Check if the disable param is set
					if ( ! $this->EE->TMPL->fetch_param('disable'))
					{
						$tagparams['disable'] = 'categories|member_data|pagination|channel_fields';
					}

					// Add the params to the set
					$this->set['parameters'] = array_merge($this->set['parameters'], $tagparams);

					// Force params
					$this->_set_template_parameters(TRUE);

					// Unset before calling channel entries
					foreach ($params AS $param)
					{
						unset($this->EE->TMPL->tagparams[$param]);
					}

					$this->return_data = $this->_channel_entries();
				*/

				$params = $this->EE->low_reorder_set_model->get_params($this->set['parameters']);
				$params['category']   = $this->cat_id;
				$params['channel_id'] = implode('|', $this->set['channels']);
				$params['entry_id']   = implode('|', $entries);

				// Get site pages
				if ($pages = $this->EE->config->item('site_pages'))
				{
					$pages = $pages[$this->site_id];
				}

				// Get the entry and focus on the single row
				foreach ($this->get_entries($params, $entries, 1) AS $entry)
				{
					// Account for Pages uri / url
					$entry['page_uri'] = (isset($pages['uris'][$entry['entry_id']]))
					                   ? $pages['uris'][$entry['entry_id']]
					                   : '';

					$entry['page_url'] = (isset($pages['url']) && strlen($entry['page_uri']))
					                   ? $this->EE->functions->create_page_url($pages['url'], $entry['page_uri'])
					                   : '';

					foreach ($entry AS $key => $val)
					{
						$row[$prefix.$key] = $val;
					}
				}

				// Parse the single row
				$this->return_data = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $row);
			}
		}

		// --------------------------------------
		// Nothing to return? Trigger no_results
		// --------------------------------------

		if (empty($row))
		{
			$this->return_data = ($no_results === FALSE) ? $this->EE->TMPL->no_results() :  $no_results;
		}

		return $this->return_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Get a given entry id's index in a set
	 *
	 * @access     public
	 * @return     string
	 */
	public function entry_index()
	{
		// Initiate set
		$this->_init_set();

		// Initiate return value
		$it = 0;

		// Check if entry_id is given
		if (($entry_id = $this->EE->TMPL->fetch_param('entry_id')) &&
			($i = array_search($entry_id, $this->entry_ids)) !== FALSE)
		{
			$it = $i + 1;
		}

		// Please
		return $it;
	}

	/**
	 * Get the total amount of entries in a set
	 *
	 * @access     public
	 * @return     string
	 */
	public function total_entries()
	{
		// Initiate set
		$this->_init_set();

		// And return the count
		return count((array) $this->entry_ids);
	}

	// --------------------------------------------------------------------

	/**
	 * Initiate tag, set $this->set_id, $this->cat_id, $this->set and $this->entry_ids
	 *
	 * @access      private
	 * @param       int      set id
	 * @return      array
	 */
	private function _init_set()
	{
		// --------------------------------------
		// Get set_id and set details
		// --------------------------------------

		$set = $this->EE->TMPL->fetch_param('set');
		$set = $this->EE->TMPL->fetch_param('set_name', $set);
		$set = $this->EE->TMPL->fetch_param('set_id', $set);

		if ($set)
		{
			$this->set = $this->_get_set($set);
		}

		// --------------------------------------
		// Check category param if cat_option = one, default to 0
		// --------------------------------------

		$this->cat_id
			= (@$this->set['cat_option'] == 'one')
			? $this->_get_cat_id($this->set['cat_groups'])
			: 0;

		// --------------------------------------
		// Get entry ids for this set/cat
		// --------------------------------------

		if (isset($this->set[$this->cat_id]))
		{
			$this->entry_ids = $this->set[$this->cat_id];
		}

		// --------------------------------------
		// Filter entry ids so we're sure we've got the correct set
		// --------------------------------------

		if ( ! empty($this->entry_ids))
		{
			$this->_filter_entry_ids();
		}
	}

	/**
	 * Get Set details from Cache or DB
	 *
	 * @access      private
	 * @param       int      set id
	 * @return      array
	 */
	private function _get_set($set_id)
	{
		// --------------------------------------
		// We're search by either ID or name
		// --------------------------------------

		$attr = is_numeric($set_id) ? 'set_id' : 'set_name';

		// --------------------------------------
		// Get sets from cache, init set to return
		// --------------------------------------

		$sets = array_filter((array) low_get_cache(LOW_REORDER_PACKAGE, 'sets'));
		$set  = array();

		// --------------------------------------
		// Loop thru cache and search for attr
		// --------------------------------------

		foreach ($sets AS $row)
		{
			if ($row[$attr] == $set_id)
			{
				$this->_log('Retrieving set from cache');
				$set = $row;
				break;
			}
		}

		// --------------------------------------
		// If no set was found, query DB
		// --------------------------------------

		if (empty($set))
		{
			$this->_log('Retrieving set from database');

			// Get set and its orders for each category
			$query = $this->EE->db->select(array('s.set_id', 's.set_name', 's.channels',
				     's.cat_option', 's.cat_groups', 's.parameters', 'o.cat_id', 'o.sort_order'))
			       ->from($this->EE->low_reorder_set_model->table() . ' s')
			       ->join($this->EE->low_reorder_order_model->table() . ' o', 's.set_id = o.set_id')
			       ->where('s.'.$attr, $set_id)
			       ->where_in('s.site_id', $this->EE->TMPL->site_ids)
			       ->get();

			// Get the first row to initiate the set
			if ($set = $query->row_array())
			{
				// Decode some attributes
				$set['channels']   = low_delinearize($set['channels']);
				$set['cat_groups'] = low_delinearize($set['cat_groups']);
				$set['parameters'] = $this->EE->low_reorder_set_model->get_params($set['parameters']);

				// Clean up what we don't need now
				unset($set['cat_id'], $set['sort_order']);

				// Instead, add all orders to the set per category id
				foreach ($query->result() AS $row)
				{
					$set[$row->cat_id] = low_delinearize($row->sort_order);
				}

				// Add the set to all the sets
				$sets[] = $set;
			}

			// Register new sets array to cache
			low_set_cache(LOW_REORDER_PACKAGE, 'sets', $sets);
		}

		// --------------------------------------
		// Set the current set ID
		// --------------------------------------

		$this->set_id = (int) @$set['set_id'];

		// Return the requested set
		return $set;
	}

	/**
	 * Filter the set's entry ids according to parameters
	 *
	 * @access     private
	 * @return     void
	 */
	private function _filter_entry_ids()
	{
		// Set/Cat key
		$key = $this->set_id.'-'.$this->cat_id;

		// Get entries from cache
		$entries = array_filter((array) low_get_cache(LOW_REORDER_PACKAGE, 'entry_ids'));

		if ( ! isset($entries[$key]))
		{
			// Log to template
			$this->_log('Getting ordered entry_ids from database');

			// Add channel_id and entry_id as parameter
			$params = $this->set['parameters'];
			$params['channel_id'] = implode('|', $this->set['channels']);
			$params['entry_id']   = implode('|', $this->entry_ids);

			// Fetch from DB
			$filtered = low_flatten_results($this->get_entries($params, FALSE), 'entry_id');

			// Intersect to preserve the order
			$filtered = array_intersect($this->entry_ids, $filtered);

			// Add to cache
			$entries[$key] = $filtered;
			low_set_cache(LOW_REORDER_PACKAGE, 'entry_ids', $entries);

			// Clean up
			unset($filtered);
		}
		else
		{
			// Log to template
			$this->_log('Getting ordered entry_ids from cache');
		}

		// Set the working entry ids to the filtered ones
		$this->entry_ids = $entries[$key];
	}

	/**
	 * Get entry id from Cache or DB
	 *
	 * @access      private
	 * @param       string   url title
	 * @param       array    limited by these entry ids
	 * @return      int
	 */
	private function _get_entry_id($url_title, $entry_ids = array())
	{
		// Get entries from cache
		$entries = array_filter((array) low_get_cache(LOW_REORDER_PACKAGE, 'entries'));

		// If it's not set, get it from DB
		if ( ! isset($entries[$url_title]))
		{
			$this->_log("Retrieving entry_id from database");

			// Get get the entry id
			$query = $this->EE->db->select('entry_id')
			       ->from('channel_titles')
			       ->where('url_title', $url_title)
			       ->where_in('entry_id', $entry_ids)
			       ->where('site_id', $this->site_id)
			       ->limit(1)
			       ->get();

			// Add it to the entries array
			if ($query->num_rows())
			{
				$entries[$url_title] = $query->row('entry_id');
			}

			// Register new sets array to cache
			low_set_cache(LOW_REORDER_PACKAGE, 'entries', $entries);
		}
		else
		{
			$this->_log("Retrieving entry_id from cache");
		}

		// Return the requested set
		return (int) @$entries[$url_title];
	}

	/**
	 * Get category id from param, URI, DB or Cache
	 *
	 * @access      private
	 * @param       array    limited by these category groups
	 * @return      int
	 */
	private function _get_cat_id($cat_groups = array())
	{
		// --------------------------------------
		// Check category parameter first
		// --------------------------------------

		if ($cat_id = $this->EE->TMPL->fetch_param('category'))
		{
			$this->_log("Retrieving cat_id from parameter");
			return $cat_id;
		}

		// --------------------------------------
		// Check URI for C123
		// --------------------------------------

		if (preg_match('#/?C(\d+)(/|$)#', $this->EE->uri->uri_string(), $match))
		{
			$this->_log("Retrieving cat_id from URI");
			return $match[1];
		}

		// --------------------------------------
		// Check URI for category keyword
		// --------------------------------------

		// Check if cat group is not empty and reserved category word is valid
		if ($cat_groups &&
			($this->EE->config->item('use_category_name') == 'y') &&
			($cat_word = $this->EE->config->item('reserved_category_word')) != '')
		{
			// Check if reserved cat word is in URI and if there's a segment behind it
			if (($key = array_search($cat_word, $this->EE->uri->segment_array())) &&
				($cat_url_title = $this->EE->uri->segment($key + 1)))
			{
				// Get category cache
				$categories = (array) low_get_cache(LOW_REORDER_PACKAGE, 'categories');

				// Fetch cat_id from DB if not in cache
				if ( ! ($cat_id = (int) array_search($cat_url_title, $categories)))
				{
					$this->_log("Retrieving cat_id from database");

					$query = $this->EE->db->select('cat_id, cat_url_title')
					       ->from('categories')
					       ->where('cat_url_title', $cat_url_title)
					       ->where_in('group_id', $cat_groups)
					       ->get();

					$cat_id = $query->row('cat_id');
					$categories[$cat_id] = $query->row('cat_url_title');
					low_set_cache(LOW_REORDER_PACKAGE, 'categories', $categories);
				}
				else
				{
					$this->_log("Retrieving cat_id from cache");
				}

				// Return the cat id
				return $cat_id;
			}
		}

		// Return 0 by default if all else fails
		return 0;
	}

	/**
	 * Log empty set message to template debugger, return empty string
	 *
	 * @access      private
	 * @param       bool     use no_results() or not
	 * @return      string
	 */
	private function _empty_set($no_results = NULL)
	{
		$this->_log("Empty set for set_id {$this->set_id} / cat_id {$this->cat_id}");
		return is_string($no_results) ? $no_results : $this->EE->TMPL->no_results();
	}

	// --------------------------------------------------------------------

	/**
	 * Set template parameters based on given params
	 *
	 * @access      private
	 * @param       bool     Force overwrite or not
	 * @return      void
	 */
	private function _set_template_parameters($force = FALSE)
	{
		// For logging
		$params = array();

		foreach ($this->set['parameters'] AS $key => $val)
		{
			// Keep track of param
			$params[] = sprintf('%s="%s"', $key, $val);

			// Search parameter
			if (substr($key, 0, 7) == 'search:')
			{
				// Strip off the 'search:' prefix
				$key = substr($key, 7);

				if ($force || ! isset($this->EE->TMPL->search_fields[$key]))
				{
					$this->EE->TMPL->search_fields[$key] = $val;
				}
			}
			else
			{
				if ($force || ! isset($this->EE->TMPL->tagparams[$key]))
				{
					$this->EE->TMPL->tagparams[$key] = $val;
				}
			}
		}

		// Log this
		$this->_log('Setting parameters '.implode(' ', $params));
	}

	/**
	 * Reset current template vars
	 *
	 * @access      private
	 * @return      void
	 */
	private function _strip_prefix($prefix)
	{
		// Do nothing if no prefix is given
		if ( ! $prefix) return;

		// Shortcut to tagdata
		$td =& $this->EE->TMPL->tagdata;

		// Simple replace for prefixed vars
		$td = str_replace(LD.$prefix, LD, $td);

		// Check if there are conditionals
		if ($conds = $this->EE->functions->assign_conditional_variables($td))
		{
			foreach ($conds AS $cond)
			{
				$td = str_replace(
					$cond[0],
					preg_replace('#(\s)'.preg_quote($prefix).'([\w_])#', '$1$2', $cond[0]),
					$td
				);
			}
		}

		// Reset template vars
		$vars = $this->EE->functions->assign_variables($td);
		$this->EE->TMPL->var_single = $vars['var_single'];
		$this->EE->TMPL->var_pair   = $vars['var_pair'];
	}

	/**
	 * Call the native channel:entries method
	 *
	 * @access      private
	 * @return      string
	 */
	private function _channel_entries()
	{
		$this->_log('Calling the channel module');

		// --------------------------------------
		// Set dynamic="no" as default
		// --------------------------------------

		if ($this->EE->TMPL->fetch_param('dynamic') != 'yes')
		{
			$this->EE->TMPL->tagparams['dynamic'] = 'no';
		}

		// --------------------------------------
		// Take care of related entries
		// --------------------------------------

		// We must do this, 'cause the template engine only does it for
		// channel:entries or search:search_results. The bastard.
		$this->EE->TMPL->tagdata = $this->EE->TMPL->assign_relationship_data($this->EE->TMPL->tagdata);

		// Add related markers to single vars to trigger replacement
		foreach ($this->EE->TMPL->related_markers AS $var)
		{
			$this->EE->TMPL->var_single[$var] = $var;
		}

		// --------------------------------------
		// Include channel module
		// --------------------------------------

		if ( ! class_exists('channel'))
		{
			require_once PATH_MOD.'channel/mod.channel'.EXT;
		}

		// --------------------------------------
		// Create new Channel instance
		// --------------------------------------

		$channel = new Channel();

		// --------------------------------------
		// Let the Channel module do all the heavy lifting
		// --------------------------------------

		return $channel->entries();
	}

	// --------------------------------------------------------------------

	/**
	 * Check for {if low_reorder_no_results}
	 *
	 * @access      private
	 * @return      void
	 */
	private function _prep_no_results()
	{
		// Shortcut to tagdata
		$td =& $this->EE->TMPL->tagdata;
		$open = 'if '.LOW_REORDER_PACKAGE.'_no_results';
		$close = '/if';

		// Check if there is a custom no_results conditional
		if (strpos($td, $open) !== FALSE && preg_match('#'.LD.$open.RD.'(.*?)'.LD.$close.RD.'#s', $td, $match))
		{
			$this->_log("Prepping {$open} conditional");

			// Check if there are conditionals inside of that
			if (stristr($match[1], LD.'if'))
			{
				$match[0] = $this->EE->functions->full_tag($match[0], $td, LD.'if', LD.'\/if'.RD);
			}

			// Set template's no_results data to found chunk
			$this->EE->TMPL->no_results = substr($match[0], strlen(LD.$open.RD), -strlen(LD.$close.RD));

			// Remove no_results conditional from tagdata
			$td = str_replace($match[0], '', $td);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Quick TMPL log method
	 *
	 * @access     private
	 * @param      string
	 * @return     void
	 */
	private function _log($msg)
	{
		$this->EE->TMPL->log_item(LOW_REORDER_NAME.': '.$msg);
	}

	// --------------------------------------------------------------------

}
// END Low_reorder class