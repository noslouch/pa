<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Playa Helper class for ExpressionEngine 2
*/
class Playa_Helper {

	private static $_order_by = "";

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
	function param2sql($param)
	{
		$not = FALSE;

		if (is_string($param))
		{
			if (strlen($param) > 4 && (strtolower(substr($param, 0, 4)) == 'not ') || (strtolower(substr($param, 0, 4)) == 'not_'))
			{
				$not = TRUE;
				$param = substr($param, 4);
			}

			if (strtolower($param) == 'current_user')
			{
				$param = $this->EE->session->userdata('member_id');
			}

			$param = explode('|', $param);
		}
		if (is_integer($param))
		{
			$param = array($param);
		}
		if (count($param) == 1)
		{
			return ($not ? '<>' : '=').' "'.$param[0].'"';
		}

		return ($not ? 'NOT ' : '').'IN ("'.implode('","', $param).'")';
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
	function entries_query($params)
	{
		// -------------------------------------------
		//  Param name mapping
		// -------------------------------------------

		$param_mapping = array(
			'author'        => 'author_id',
			'category_id'   => 'category',
			'weblog'        => 'channel',
			'weblog_id'     => 'channel_id',
			'member_groups' => 'group_id'
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

		if (!empty($params['count']))
		{
			$sql_select = 'SELECT COUNT(ct.entry_id) count';
		}
		else
		{
			$sql_select = 'SELECT ct.*';
		}

		$sql_from = "\nFROM exp_channel_titles ct";

		$join_members = FALSE;
		$join_channels = FALSE;
		$join_cat_posts = FALSE;

		$where = array();

			// -------------------------------------------
			//  Author
			// -------------------------------------------

			if (!empty($params['author_id']))
			{
				$where[] = 'ct.author_id '.$this->param2sql($this->_escape_variable($params['author_id']));
			}

			// -------------------------------------------
			//  Author Group
			// -------------------------------------------

			if (!empty($params['group_id']))
			{
				$join_members = TRUE;
				$where[] = 'm.group_id '.$this->param2sql($this->_escape_variable($params['group_id']));
			}

			// -------------------------------------------
			//  Category
			// -------------------------------------------

			if (!empty($params['category']))
			{
				$join_cat_posts = TRUE;

				$category = $this->_escape_variable($params['category']);

				if (!is_array($category) && (strlen($category) > 4 && (strtolower(substr($category, 0, 4)) == 'not ') || (strtolower(substr($category, 0, 4)) == 'not_')))
				{
					$null_condition = ' OR cp.cat_id IS NULL';
				}
				else
				{
					$null_condition = '';
				}

				$where[] = '(cp.cat_id '.$this->param2sql($category) . $null_condition . ')';
			}

			// -------------------------------------------
			//  Category Group
			// -------------------------------------------

			if (!empty($params['category_group']))
			{
				$join_channels = TRUE;
				$where[] = 'c.cat_group '.$this->param2sql($this->_escape_variable($params['category_group']));
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

			if (! isset($params['show_expired']) || $params['show_expired'] != 'yes')
			{
				$where[] = '(ct.expiration_date = 0 OR ct.expiration_date > '.$timestamp.')';
			}

			if (! isset($params['show_future_entries']) || $params['show_future_entries'] != 'yes')
			{
				$where[] = 'ct.entry_date < '.$timestamp;
			}

			if (isset($params['only_show_editable_entries']) && $params['only_show_editable_entries'] == 'yes')
			{
				$where[] = 'ct.entry_date < '.$timestamp;
			}

			// -------------------------------------------
			//  Entry ID
			// -------------------------------------------

			if (isset($params['entry_id']) && $params['entry_id'])
			{
				$where[] = 'ct.entry_id '.$this->param2sql($this->_escape_variable($params['entry_id']));
			}

			// -------------------------------------------
			//  Status
			// -------------------------------------------

			if (isset($params['status']) && $params['status'])
			{
				$where[] = 'ct.status '.$this->param2sql($this->_escape_variable($params['status']));
			}

			// -------------------------------------------
			//  URL Title
			// -------------------------------------------

			if (isset($params['url_title']) && $params['url_title'])
			{
				$where[] = 'ct.url_title '.$this->param2sql($this->_escape_variable($params['url_title']));
			}

			// -------------------------------------------
			//  Site ID
			// -------------------------------------------

			if (! empty($params['site_id']))
			{
				$where[] = 'ct.site_id '.$this->param2sql($this->_escape_variable($params['site_id']));
			}

			// -------------------------------------------
			//  Channel
			// -------------------------------------------

			if (!empty($params['channel']))
			{
				$join_channels = TRUE;
				$where[] = 'c.channel_name '.$this->param2sql($this->_escape_variable($params['channel']));
			}

			// -------------------------------------------
			//  Channel ID
			// -------------------------------------------

			if (isset($params['channel_id']) && $params['channel_id'])
			{
				$where[] = 'ct.channel_id '.$this->param2sql($this->_escape_variable($params['channel_id']));
			}

			// -------------------------------------------
			//  Keywords
			// -------------------------------------------

			if (isset($params['keywords']) && $params['keywords'])
			{
				$params['keywords'] = $this->EE->db->escape_like_str($params['keywords']);
				$where[] = 'ct.title LIKE "%'.$params['keywords'].'%"';
			}

		// -------------------------------------------
		//  Assemble the SQL
		// -------------------------------------------

		$sql = $sql_select;

		// -------------------------------------------
		//  Add FROM
		// -------------------------------------------

		$sql .= $sql_from;

		// -------------------------------------------
		//  Add JOINs
		// -------------------------------------------

		if ($join_cat_posts)
		{
			$sql .= "\nLEFT JOIN exp_category_posts cp ON cp.entry_id = ct.entry_id";
		}

		if ($join_members)
		{
			$sql .= "\nINNER JOIN exp_members m ON m.member_id = ct.author_id";
		}

		if ($join_channels)
		{
			$sql .= "\nINNER JOIN exp_channels c ON c.channel_id = ct.channel_id";
		}

		// -------------------------------------------
		//  Add WHERE
		// -------------------------------------------

		if ($where || isset($add_to_sql['where']))
		{
			$sql .= "\nWHERE "
				  . implode("\nAND ", $where)
				  . (isset($add_to_sql['where']) ? ($where ? "\nAND " : '') . $add_to_sql['where'] : '');
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
				$attr = preg_replace('/[^a-z0-9\-_\.].*/', "", $attr);
				$all_orderbys[] = 'ct.'.$this->_escape_variable($attr).' '.$sort;
			}

			$sql .=  "\nORDER BY ".implode(', ', $all_orderbys);
		}
		else if (isset($add_to_sql['orderby']))
		{
			$sql .= "\nORDER BY ".$add_to_sql['orderby'];

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
			$offset = (isset($params['offset']) && $params['offset']) ? (int) $params['offset'] . ', ' : '';
			$limit  = (isset($params['limit']) && $params['limit']) ? (int) $params['limit'] : 100;

			$sql .= ' LIMIT ' . $offset . $limit;
		}

		// -------------------------------------------
		//  Run and return
		// -------------------------------------------
		$query = $this->EE->db->query($sql);

		return isset($params['count']) ? $query->row('count') : $query->result();
	}

	/**
	 * Escape a variable (array or plain) for SQL usage.
	 *
	 * @param $variable
	 * @return array
	 */
	function _escape_variable($variable)
	{
		if (is_array($variable))
		{
			foreach ($variable as &$value)
			{
				$value = $this->EE->db->escape_str($value);
			}
		}
		else
		{
			$variable = $this->EE->db->escape_str($variable);
		}

		return $variable;
	}
	// --------------------------------------------------------------------

	/**
	 * Sort Entries
	 */
	function sort_entries(&$entries, $sort, $orderby)
	{
		self::$_order_by = $orderby;

		usort($entries, array('Playa_Helper', 'entry_compare'));

		if ($sort == 'DESC') {
			$entries = array_reverse($entries);
		}

		self::$_order_by = NULL;
	}

	/**
	 * Compare two entries.
	 *
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public static function entry_compare($a, $b)
	{
		if (property_exists($a, self::$_order_by) && property_exists($b, self::$_order_by))
		{
			return 0;
		}
		return strcmp($a->{self::$_order_by}, $b->{self::$_order_by});
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

	/**
	 * Get JSON formatted data for any given data.
	 *
	 * @param $data
	 * @return string
	 */
	public function get_json($data)
	{
		if (version_compare(APP_VER, '2.6', '<') OR !function_exists('json_encode'))
		{
			return $this->EE->javascript->generate_json($data, TRUE);
		}
		else
		{
			return json_encode($data);
		}
	}
}
