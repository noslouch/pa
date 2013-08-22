<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Playa Helper class for ExpressionEngine 2
*/
class Playa_Helper {

	/**
	 * Constructor
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
	}

	// --------------------------------------------------------------------

	/**
	 * Parameter => SQL
	 */
	function param2sql($param, &$not = NULL, $use_not = TRUE)
	{
		if (is_string($param))
		{
			if (strlen($param) > 4 && strtolower(substr($param, 0, 4)) == 'not ')
			{
				$not = TRUE;
				$param = substr($param, 4);
			}
			else
			{
				$not = FALSE;
			}

			$param = explode('|', $param);
		}
		else if ($not === NULL)
		{
			$not = FALSE;
		}

		if (count($param) == 1)
		{
			return ($not && $use_not ? '<>' : '=').' "'.$param[0].'"';
		}

		return ($not && $use_not ? 'NOT ' : '').'IN ("'.implode('","', $param).'")';
	}

	/**
	 * DB Where
	 */
	function db_where($col, $val)
	{
		if (! is_array($val))
		{
			$this->EE->db->where($col, $val);
		}
		elseif (count($val) == 1)
		{
			$this->EE->db->where($col, $val[0]);
		}
		else
		{
			$this->EE->db->where_in($col, $val);
		}
	}

	/**
	 * DB Where Not
	 */
	function db_where_not($col, $val)
	{
		if (! is_array($val))
		{
			$this->EE->db->where($col.' !=', $val);
		}
		elseif (count($val) == 1)
		{
			$this->EE->db->where($col.' !=', $val[0]);
		}
		else
		{
			$this->EE->db->where_not_in($col, $val);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Entries Query
	 */
	function entries_query($params, $add_to_sql = array())
	{
		// -------------------------------------------
		//  Param name mapping
		// -------------------------------------------

		$param_mapping = array(
			'author'      => 'author_id',
			'category_id' => 'category',
			'weblog'      => 'channel',
			'weblog_id'   => 'channel_id'
		);

		foreach ($param_mapping as $old_name => $new_name)
		{
			if (isset($params[$old_name]) AND (! isset($params[$new_name]) || ! $params[$new_name]))
			{
				$params[$new_name] = $params[$old_name];
				unset($params[$old_name]);
			}
		}

		// -------------------------------------------
		//  Prepare the SQL
		// -------------------------------------------

		$sql = 'SELECT '.(isset($params['count']) ? 'COUNT(ct.entry_id) count' : 'ct.*')
		     . (isset($add_to_sql['select']) ? ', '.$add_to_sql['select'] : '')
		     . ' FROM exp_channel_titles ct'.(isset($add_to_sql['from']) ? ', '.$add_to_sql['from'] : '');

		$where = array();

			// -------------------------------------------
			//  Author
			// -------------------------------------------

			if (isset($params['author_id']) && $params['author_id'])
			{
				$where[] = 'ct.author_id '.$this->param2sql($params['author_id']);
			}

			// -------------------------------------------
			//  Author Group
			// -------------------------------------------

			if (isset($params['group_id']) && $params['group_id'])
			{
				// get filtered list of author ids
				$not = NULL;
				$query = $this->EE->db->query('SELECT member_id FROM exp_members
				                               WHERE group_id '.$this->param2sql($params['group_id'], $not, FALSE));

				if (! $query->num_rows())
				{
					if (! $not) return FALSE;
				}
				else
				{
					$author_ids = array();
					foreach ($query->result_array() as $row)
					{
						$author_ids[] = $row['member_id'];
					}

					$where[] = 'ct.author_id '.$this->param2sql($author_ids, $not);
				}
			}

			// -------------------------------------------
			//  Category
			// -------------------------------------------

			if (isset($params['category']) && $params['category'])
			{
				// get filtered list of entry ids
				$not = NULL;
				$query = $this->EE->db->query('SELECT entry_id FROM exp_category_posts
				                               WHERE cat_id '.$this->param2sql($params['category'], $not, FALSE).'
				                               GROUP BY entry_id');

				if (! $query->num_rows())
				{
					if (! $not) return FALSE;
				}
				else
				{
					$entry_ids = array();
					foreach ($query->result_array() as $row)
					{
						$entry_ids[] = $row['entry_id'];
					}

					$where[] = 'ct.entry_id '.$this->param2sql($entry_ids, $not);
				}
			}

			// -------------------------------------------
			//  Category Group
			// -------------------------------------------

			if (isset($params['category_group']) && $params['category_group'])
			{
				// get filtered list of entry ids
				$not = NULL;
				$query = $this->EE->db->query('SELECT cp.entry_id FROM exp_category_posts cp, exp_categories c
				                               WHERE cp.cat_id = c.cat_id
				                                     AND c.group_id '.$this->param2sql($params['category_group'], $not, FALSE).'
				                               GROUP BY entry_id');

				if (! $query->num_rows())
				{
					if (! $not) return FALSE;
				}
				else
				{
					$entry_ids = array();
					foreach ($query->result_array() as $row)
					{
						$entry_ids[] = $row['entry_id'];
					}

					$where[] = 'ct.entry_id '.$this->param2sql($entry_ids, $not);
				}
			}

			// -------------------------------------------
			//  Dates
			// -------------------------------------------

			if (isset($params['start_on']) && $params['start_on'])
			{
				$where[] = 'ct.entry_date >= '.$this->EE->localize->convert_human_date_to_gmt($params['start_on']);
			}

			if (isset($params['stop_before']) && $params['stop_before'])
			{
				$where[] = 'ct.entry_date < '.$this->EE->localize->convert_human_date_to_gmt($params['stop_before']);
			}

			$timestamp = (isset($this->EE->TMPL) && $this->EE->TMPL->cache_timestamp) ? $this->EE->TMPL->cache_timestamp : $this->EE->localize->now;

			if (! isset($params['show_future_entries']) || $params['show_future_entries'] != 'yes')
			{
				$where[] = 'ct.entry_date < '.$timestamp;
			}

			if (! isset($params['show_expired']) || $params['show_expired'] != 'yes')
			{
				$where[] = '(ct.expiration_date = 0 OR ct.expiration_date > '.$timestamp.')';
			}

			// -------------------------------------------
			//  Entry ID
			// -------------------------------------------

			if (isset($params['entry_id']) && $params['entry_id'])
			{
				$where[] = 'ct.entry_id '.$this->param2sql($params['entry_id']);
			}

			// -------------------------------------------
			//  Status
			// -------------------------------------------

			if (isset($params['status']) && $params['status'])
			{
				$where[] = 'ct.status '.$this->param2sql($params['status']);
			}

			// -------------------------------------------
			//  URL Title
			// -------------------------------------------

			if (isset($params['url_title']) && $params['url_title'])
			{
				$where[] = 'ct.url_title '.$this->param2sql($params['url_title']);
			}

			// -------------------------------------------
			//  Site ID
			// -------------------------------------------

			if (! empty($params['site_id']))
			{
				$where[] = 'ct.site_id '.$this->param2sql($params['site_id']);
			}

			// -------------------------------------------
			//  Channel
			// -------------------------------------------

			if (isset($params['channel']) && $params['channel'])
			{
				// get channel IDs
				$not = NULL;
				$query = $this->EE->db->query('SELECT channel_id FROM exp_channels
				                               WHERE channel_name '.$this->param2sql($params['channel'], $not, FALSE));

				if (! $query->num_rows())
				{
					if (! $not) return FALSE;
				}
				else
				{
					$channel_ids = array();
					foreach ($query->result_array() as $row)
					{
						$channel_ids[] = $row['channel_id'];
					}

					$where[] = 'ct.channel_id '.$this->param2sql($channel_ids, $not);
				}
			}

			// -------------------------------------------
			//  Channel ID
			// -------------------------------------------

			if (isset($params['channel_id']) && $params['channel_id'])
			{
				$where[] = 'ct.channel_id '.$this->param2sql($params['channel_id']);
			}

			// -------------------------------------------
			//  Keywords
			// -------------------------------------------

			if (isset($params['keywords']) && $params['keywords'])
			{
				$where[] = 'ct.title LIKE "%'.$params['keywords'].'%"';
			}

		// -------------------------------------------
		//  Add WHERE to SQL
		// -------------------------------------------

		if ($where || isset($add_to_sql['where']))
		{
			$sql .= ' WHERE '
			      . implode(' AND ', $where)
			      . (isset($add_to_sql['where']) ? ($where ? ' AND ' : '') . $add_to_sql['where'] : '');
		}

		// -------------------------------------------
		//  Orberby + Sort
		// -------------------------------------------

		if (isset($params['orderby']) && $params['orderby'])
		{
			$orderbys = (is_array($params['orderby'])) ? $params['orderby'] : explode('|', $params['orderby']);
			$sorts    = (isset($params['sort']) && $params['sort']) ? (is_array($params['sort']) ? $params['sort'] : explode('|', $params['sort'])) : array();

			$all_orderbys = array();
			foreach ($orderbys as $i => $attr)
			{
				$sort = (isset($sorts[$i]) AND strtoupper($sorts[$i]) == 'DESC') ? 'DESC' : 'ASC';
				$all_orderbys[] = 'ct.'.$attr.' '.$sort;
			}

			$sql .=  ' ORDER BY '.implode(', ', $all_orderbys);
		}
		else if (isset($add_to_sql['orderby']))
		{
			$sql .= ' ORDER BY '.$add_to_sql['orderby'];

			if (isset($params['sort']) && strtoupper($params['sort']) == 'DESC')
			{
				$sql .= ' DESC';
			}
		}

		// -------------------------------------------
		//  Offset and Limit
		// -------------------------------------------

		if ((isset($params['limit']) && $params['limit']) || (isset($params['offset']) && $params['offset']))
		{
			$offset = (isset($params['offset']) && $params['offset']) ? $params['offset'] . ', ' : '';
			$limit  = (isset($params['limit']) && $params['limit']) ? $params['limit'] : 100;

			$sql .= ' LIMIT ' . $offset . $limit;
		}

		// -------------------------------------------
		//  Run and return
		// -------------------------------------------

		$query = $this->EE->db->query($sql);

		return isset($params['count']) ? $query->row('count') : $query->result();
	}

	// --------------------------------------------------------------------

	/**
	 * Sort Entries
	 */
	function sort_entries(&$entries, $sort, $orderby)
	{
		usort($entries, create_function('$a, $b',
			'return '.($sort == 'DESC' ? '-1 * ' : '').'strcmp($a->'.$orderby.', $b->'.$orderby.');'
		));
	}

	/**
	 * Strip Whitespace
	 */
	function strip_whitespace($html)
	{
		return preg_replace('/[\r\n\t]/', '', $html);
	}

	// --------------------------------------------------------------------

	/**
	 * Creates a Playa module tag, and sets a random var_prefix on it if it doesn't already have one.
	 * @param string $params
	 * @param string $tagdata
	 * @param string $func
	 * @return string
	 */
	function create_module_tag($params, $tagdata, $func)
	{
		// Set a random var prefix if there isn't one already
		if (! preg_match('/var_prefix=([\'"])(.+?)\1/', $params, $match))
		{
			$var_prefix = 'playa'.$this->EE->functions->random('alnum', 8);
			$params .= ' var_prefix="'.$var_prefix.'"';	

			$tagdata = preg_replace('/(\{\/?)(?!(\/|exp\:))/', "$1{$var_prefix}:", $tagdata);
		}

		// assemble and return the {exp:playa:xyz} tag pair
		$tag = 'exp:playa:'.$func;
		return LD.$tag.$params.RD.$tagdata.LD.'/'.$tag.RD;
	}

}
