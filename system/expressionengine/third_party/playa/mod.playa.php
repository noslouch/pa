<?php if (! defined('BASEPATH')) exit('Invalid file request');

/**
 * Playa Module Class
 *
 * @package   Playa
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Playa
{

	public $is_draft = FALSE;

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		$this->EE->load->library('logger');
		$this->EE->lang->loadfile('playa');

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

		// -------------------------------------------
		//  Grab the entry_id's
		// -------------------------------------------

		$entry_ids = $this->EE->TMPL->fetch_param('entry_id');
		unset($this->EE->TMPL->tagparams['entry_id']);

		$this->and = (strpos($entry_ids, '&&') !== FALSE);
		$delimiter = $this->and ? '&&' : '|';
		$this->entry_ids = array_filter(explode($delimiter, $entry_ids));
		if (count($this->entry_ids) == 1) $this->and = FALSE;

		foreach ($this->entry_ids as $i => $entry_id)
		{
			if (! is_numeric($entry_id))
			{
				$this->entry_ids[$i] = 0;
			}
		}

		if (isset($this->EE->session->cache['ep_better_workflow']['is_draft']) && $this->EE->session->cache['ep_better_workflow']['is_draft'])
		{
			$this->is_draft = 1;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Tagdata
	 *
	 * If this tag is within an {exp:channel:entries} tag, we've cached its tagdata
	 * so that Channel::entries() doesn't parse our precious bodily variables
	 */
	private function _fetch_tagdata($debug=FALSE)
	{
		$tagdata = (string)$this->EE->TMPL->tagdata;
		$var_prefix = rtrim($this->EE->TMPL->fetch_param('var_prefix'), ':');

		if ($var_prefix)
		{
			$tagdata = str_replace(array(LD.$var_prefix.':', LD.'/'.$var_prefix.':'), array(LD, LD.'/'), $tagdata);
		}

		return $tagdata;
	}

	// --------------------------------------------------------------------

	/**
	 * Names to IDs
	 * @access private
	 * @param string $what What to fetch (field, col, variable)
	 * @param string $table Where to fetch it from
	 * @param array $names Names to search for
	 * @param int $site_id The site ID
	 * @return array The IDs
	 */
	private function _names_to_ids($what, $table, $names, $site_id)
	{
		$names = explode('|', $names);

		if (! isset($this->cache['site_'.$what.'_ids_by_name'][$site_id]))
		{
			$this->cache['site_'.$what.'_ids_by_name'][$site_id] = array();
		}

		$site_fields =& $this->cache['site_'.$what.'_ids_by_name'][$site_id];

		// figure out which ones are already cached
		$get_names = array();

		foreach ($names as $name)
		{
			if (!isset($site_fields[$name]))
			{
				$get_names[] = $name;

				// Ensure we don't run another query later
				$site_fields[$name] = array();
			}
		}

		// grab the ones we're missing
		if ($get_names)
		{
			$this->EE->db->select($what.'_id AS id, '.$what.'_name AS name');
			$this->helper->db_where($what.'_name', $get_names);
			// If searching for variables, make sure we don't load variables form other sites.
			if ($what == 'variable')
			{
				$this->helper->db_where('site_id', $site_id);
			}

			$query = $this->EE->db->get($table);

			foreach ($query->result() as $row)
			{
				$site_fields[$row->name][] = $row->id;
			}
		}

		// return the field IDs
		$ids = array();

		foreach ($names as $name)
		{
			$ids = array_merge($ids, $site_fields[$name]);
		}

		return $ids;
	}

	/**
	 * Fetch Variable IDs
	 *
	 * Returns an array of variable IDs associated with this tag,
	 * whether passed via the var_id= or var= params
	 */
	private function _fetch_var_ids()
	{
		if ($var_id = $this->EE->TMPL->fetch_param('var_id'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['var_id']);

			return explode('|', $var_id);
		}

		if (($var_names = $this->EE->TMPL->fetch_param('var')))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['var']);

			// which site are we looking at?
			$site_id = $this->EE->config->item('site_id');

			return $this->_names_to_ids('variable', 'global_variables', $var_names, $site_id);
		}
	}

	/**
	 * Fetch Field IDs
	 *
	 * Returns an array of field IDs associated with this tag,
	 * whether passed via the field_id= or field= params
	 */
	private function _fetch_field_ids()
	{
		if ($field_id = $this->EE->TMPL->fetch_param('field_id'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['field_id']);

			return explode('|', $field_id);
		}

		if (($field_names = $this->EE->TMPL->fetch_param('field')))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['field']);

			if ($this->entry_ids)
			{
				// which site are we looking at?
				$row = $this->_fetch_entry_row($this->entry_ids[0]);

				if (! empty($row['entry_site_id']))
				{
					return $this->_names_to_ids('field', 'channel_fields', $field_names, $row['entry_site_id']);
				}
			}
		}

		return array();
	}

	/**
	 * Fetch Column IDs
	 *
	 * Returns an array of col IDs associated with this tag,
	 * whether passed via the col_id= or col= params
	 */
	private function _fetch_col_ids()
	{
		if ($col_id = $this->EE->TMPL->fetch_param('col_id'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['col_id']);

			return explode('|', $col_id);
		}

		if ($col_names = $this->EE->TMPL->fetch_param('col'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['col']);

			// default to the current site
			$site_id = $this->EE->config->item('site_id');

			if ($this->entry_ids)
			{
				// get the entry's site_id
				$row = $this->_fetch_entry_row($this->entry_ids[0]);

				if (! empty($row['entry_site_id']))
				{
					$site_id = $row['entry_site_id'];
				}
			}

			return $this->_names_to_ids('col', 'matrix_cols', $col_names, $site_id);
		}
	}

	/**
	 * Fetch Row IDs
	 *
	 * Returns an array of row IDs associated with this tag via the row_id= param
	 */
	private function _fetch_row_ids()
	{
		if ($row_id = $this->EE->TMPL->fetch_param('row_id'))
		{
			// unset the param
			unset($this->EE->TMPL->tagparams['row_id']);

			return explode('|', $row_id);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Parse No Results Tag
	 */
	private function _parse_no_results_tag(&$tagdata, $tag)
	{
		$tagdata = preg_replace_callback("/\{if {$tag}\}(.*?)\{\/if\}/s", array(&$this, '_replace_no_results_tag'), $tagdata, 1);
	}

	/**
	 * Replace No Results Tag
	 */
	private function _replace_no_results_tag($m)
	{
		$this->EE->TMPL->no_results_block = $m[0];
		$this->EE->TMPL->no_results = $m[1];

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch Entry Row
	 * @access private
	 * @param int $entry_id
	 * @return array Essentially the exp_channel_titles row for the entry
	 */
	private function _fetch_entry_row($entry_id)
	{
		if (! isset($this->cache['entry_rows'][$entry_id]))
		{
			// just grab it from the DB
			$row = $this->EE->db->query("SELECT ct.author_id, c.channel_title, c.channel_id, c.channel_name, ct.entry_date, ct.site_id AS entry_site_id, ct.expiration_date, m.screen_name, ct.status, ct.title, ct.url_title
			                             FROM exp_channel_titles AS ct
			                             LEFT JOIN exp_channels AS c ON ct.channel_id = c.channel_id
			                             LEFT JOIN exp_members AS m ON ct.author_id = m.member_id
			                             WHERE ct.entry_id = '{$entry_id}'")
			                    ->row_array();

			$row['entry_id'] = $entry_id;
			$row['count'] = '1';

			// cache it for maybe later
			$this->cache['entry_rows'][$entry_id] =& $row;
		}

		return $this->cache['entry_rows'][$entry_id];
	}

	/**
	 * Fetch Entry Data
	 */
	private function _parse_relative_entry_tags(&$tagdata, $tag_prefix)
	{
		static $reported_messages = array();
		if (strpos($tagdata, $tag_prefix) !== FALSE)
		{
			preg_match('/\{' . $tag_prefix . '([^\}]+)\}/', $tagdata, $matches);

			if (!empty($matches[1]))
			{
				$tag = $matches[1];
			}
			else
			{
				$tag = '*';
			}

			$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
			$url = 'http'.($https ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

			$message = str_replace('{url}', $url, lang('deprecated_tag'));
			$message = str_replace('{tag}', "{".$tag_prefix.$tag."}", $message);

			if (empty($reported_messages[$message]))
			{
				$this->EE->logger->developer($message);
				$reported_messages[$message] = TRUE;
			}
		}

		// only worry about this if entry_id= is set to one entry
		if (count($this->entry_ids) != 1) return;

		// ignore if no relative entry tags
		if (strpos($tagdata, LD.$tag_prefix) === FALSE) return;

		// -------------------------------------------
		//  Get the entry's row data
		// -------------------------------------------

		$row = array_merge(
			array(
				'author_id' => '',
				'channel_title' => '',
				'channel_id' => '',
				'channel_name' => '',
				'count' => '',
				'entry_date' => '',
				'entry_id' => '',
				'entry_site_id' => '',
				'expiration_date' => '',
				'screen_name' => '',
				'status' => '',
				'title' => '',
				'url_title' => ''
			),
			$this->_fetch_entry_row($this->entry_ids[0])
		);

		// -------------------------------------------
		//  Parse the relative entry's tags
		// -------------------------------------------

		$rel_row_tags = array(
			$tag_prefix.'author_id'          => $row['author_id'],
			$tag_prefix.'channel'            => $row['channel_title'],
			$tag_prefix.'channel_id'         => $row['channel_id'],
			$tag_prefix.'channel_short_name' => $row['channel_name'],
			$tag_prefix.'count'              => $row['count'],
			$tag_prefix.'entry_date'         => $row['entry_date'],
			$tag_prefix.'entry_id'           => $row['entry_id'],
			$tag_prefix.'entry_site_id'      => $row['entry_site_id'],
			$tag_prefix.'expiration_date'    => $row['expiration_date'],
			$tag_prefix.'screen_name'        => $row['screen_name'],
			$tag_prefix.'status'             => $row['status'],
			$tag_prefix.'title'              => $row['title'],
			$tag_prefix.'url_title'          => $row['url_title']
		);

		$tagdata = $this->EE->TMPL->parse_variables_row($tagdata, $rel_row_tags);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Relationships
	 *
	 * Fools EE into thinking that this was an {exp:channel:entries} tag
	 */
	private function _parse_relationships($tagdata, $entry_ids, $fixed_order)
	{
		if (! $tagdata) return;

		// -------------------------------------------
		//  Parse the relationships
		// -------------------------------------------

		// make sure $entry_ids is a pipe delimited string
		if (is_array($entry_ids)) $entry_ids = implode('|', $entry_ids);

		// entry_id= or fixed_order=
		$entry_id_param = ($fixed_order && ! $this->EE->TMPL->fetch_param('orderby')) ? 'fixed_order' : 'entry_id';
		$this->EE->TMPL->tagparams[$entry_id_param] = '0|'.$entry_ids;

		// dynamic="nuts"
		$this->EE->TMPL->tagparams['dynamic'] = 'no';

		if (! isset($this->EE->TMPL->tagparams['disable']))
		{
			// disable everything but custom_fields by default
			$this->EE->TMPL->tagparams['disable'] = 'categories|category_fields|member_data|pagination';
		}

		// prep TMPL with the cached tagdata
		$this->EE->TMPL->tagdata = $tagdata;

		$vars = $this->EE->functions->assign_variables($tagdata);
		$this->EE->TMPL->var_single = $vars['var_single'];
		$this->EE->TMPL->var_pair   = $vars['var_pair'];

		// _fetch_site_ids is meant to be a private method,
		// so make sure it's still around before calling it
		if (method_exists($this->EE->TMPL, '_fetch_site_ids'))
		{
			$this->EE->TMPL->_fetch_site_ids();
		}

		// -------------------------------------------
		//  'playa_parse_relationships' hook
		//   - Make any last-minute changes ta $tagparams, etc., before we call $Channel->entries()
		//
			if ($this->EE->extensions->active_hook('playa_parse_relationships'))
			{
				$this->EE->extensions->call('playa_parse_relationships');
			}
		//
		// -------------------------------------------

		if (! class_exists('Channel'))
		{
			require PATH_MOD.'channel/mod.channel.php';
		}

		// create a new Channel object and run entries()
		$Channel = new Channel();
		return $Channel->entries();
	}

	// --------------------------------------------------------------------

	/**
	 * Returns the related entry IDs.
	 * @access private
	 * @param string $dir The direction of the relationship (children, parents, siblings, or coparents)
	 * @return array The related entry IDs
	 */
	private function _fetch_rel_entry_ids($dir)
	{
		// field, col, and row params
		$var_ids   = $this->_fetch_var_ids();
		$field_ids = $this->_fetch_field_ids();
		$col_ids   = $this->_fetch_col_ids();
		$row_ids   = $this->_fetch_row_ids();

		// get the list of entry IDs to include/exclude (if any)
		$filter_ids = array(
			'child'  => $this->EE->TMPL->fetch_param('child_id'),
			'parent' => $this->EE->TMPL->fetch_param('parent_id')
		);

		$show_future_entries = $this->EE->TMPL->fetch_param('show_future_entries', 'no');

		$imploded_entry_ids = implode(',', $this->entry_ids);

		// cached?
		$cache_key = $imploded_entry_ids . '|'
		           . ($this->and ? 'and' : 'or') . '|'
		           . $dir . '|'
		           . ($var_ids     ? implode(',', $var_ids)   : '*') . '|'
		           . ($field_ids   ? implode(',', $field_ids) : '*') . '|'
		           . ($col_ids     ? implode(',', $col_ids)   : '*') . '|'
		           . ($row_ids     ? implode(',', $row_ids)   : '*') . '|'
		           . ($filter_ids['child']  ? str_replace('|', ',', $filter_ids['child']) : '*')
		           . ($filter_ids['parent'] ? str_replace('|', ',', $filter_ids['parent']) : '*')
				   . $show_future_entries;

		// find the rels if they aren't already cached
		if (! isset($this->cache['rels'][$cache_key]))
		{
			$count_entry_ids = count($this->entry_ids);
			$where = array();
			$sql_end = array();

			$join = '';

			switch($dir)
			{
				case 'children':

					$sql = 'SELECT DISTINCT(rel.child_entry_id) AS entry_id
					        FROM exp_playa_relationships rel';

					if ($this->entry_ids)
					{
						if ($this->and)
						{
							$sql_end[] = "GROUP BY rel.child_entry_id HAVING COUNT(
							                  CASE WHEN rel.parent_entry_id IN ({$imploded_entry_ids}) THEN rel.parent_entry_id END
							              ) = {$count_entry_ids}";
						}
						else if (count($this->entry_ids) == 1)
						{
							$where[] = 'rel.parent_entry_id = '.$this->entry_ids[0];
						}
						else
						{
							$where[] = "rel.parent_entry_id IN ({$imploded_entry_ids})";
						}
					}

					$where[] = "rel.parent_is_draft = " . (int) $this->is_draft;

					if (strtolower($show_future_entries) != 'yes')
					{
						$join = ' INNER JOIN exp_channel_titles AS ct ON ct.entry_id = rel.child_entry_id AND ct.`entry_date` < "' . time() . '" ';
					}

					if (! $this->EE->TMPL->fetch_param('orderby'))
					{
						$sql_end[] = 'ORDER BY rel.parent_entry_id, rel.parent_field_id, rel.parent_row_id, rel.parent_col_id, rel.parent_var_id, rel.rel_order';
					}

					break;

				case 'parents':

					$sql = 'SELECT DISTINCT(rel.parent_entry_id) AS entry_id
					        FROM exp_playa_relationships rel';

					if ($this->entry_ids)
					{
						if ($this->and)
						{
							$sql_end[] = "GROUP BY rel.parent_entry_id HAVING COUNT(
							                  CASE WHEN rel.child_entry_id IN ({$imploded_entry_ids}) THEN rel.child_entry_id END
							              ) = {$count_entry_ids}";
						}
						else if ($count_entry_ids == 1)
						{
							$where[] = 'rel.child_entry_id = '.$this->entry_ids[0];
						}
						else
						{
							$where[] = "rel.child_entry_id IN ({$imploded_entry_ids})";
						}
					}

					if (strtolower($show_future_entries) != 'yes')
					{
						$join = ' INNER JOIN exp_channel_titles AS ct ON ct.entry_id = rel.parent_entry_id AND ct.`entry_date` < "' . time() . '" ';
					}


					break;

				case 'siblings';

					$sql = "SELECT rel.child_entry_id AS entry_id, rel.parent_entry_id AS link_id
					        FROM exp_playa_relationships rel
					        INNER JOIN exp_playa_relationships link ON link.parent_entry_id = rel.parent_entry_id";

					if ($this->entry_ids)
					{
						if ($count_entry_ids == 1)
						{
							$where[] = 'link.child_entry_id = '.$this->entry_ids[0];
							$where[] = 'rel.child_entry_id != '.$this->entry_ids[0];
						}
						else
						{
							$where[] = "link.child_entry_id IN ({$imploded_entry_ids})";
							$where[] = "rel.child_entry_id NOT IN ({$imploded_entry_ids})";
						}
					}

					if (strtolower($show_future_entries) != 'yes')
					{
						$join = ' INNER JOIN exp_channel_titles AS ct ON ct.entry_id = link.parent_entry_id AND ct.`entry_date` < "' . time() . '" ';
					}

					break;

				case 'coparents':

					$sql = 'SELECT rel.parent_entry_id AS entry_id, rel.child_entry_id AS link_id
					        FROM exp_playa_relationships rel
					        INNER JOIN exp_playa_relationships link ON link.child_entry_id = rel.child_entry_id';

					if ($this->entry_ids)
					{
						if ($count_entry_ids == 1)
						{
							$where[] = 'link.parent_entry_id = '.$this->entry_ids[0];
							$where[] = 'rel.parent_entry_id != '.$this->entry_ids[0];
						}
						else
						{
							$where[] = "link.parent_entry_id IN ({$imploded_entry_ids})";
							$where[] = "rel.parent_entry_id NOT IN ({$imploded_entry_ids})";
						}
						$where[] = 'rel.parent_entry_id != link.parent_entry_id';
					}

					if (strtolower($show_future_entries) != 'yes')
					{
						$join = ' INNER JOIN exp_channel_titles AS ct ON ct.entry_id = link.child_entry_id AND ct.`entry_date` < "' . time() . '" ';
					}

					break;
			}

			// filter by variadle?
			if ($var_ids)
			{
				if (count($var_ids) == 1)
				{
					$where[] = 'rel.parent_var_id = '.$var_ids[0];
				}
				else
				{
					$where[] = 'rel.parent_var_id IN ('.implode(',', $var_ids).')';
				}
			}

			// filter by field?
			if ($field_ids)
			{
				if (count($field_ids) == 1)
				{
					$where[] = 'rel.parent_field_id = '.$field_ids[0];

					if ($dir == 'siblings' || $dir == 'coparents')
					{
						$where[] = 'link.parent_field_id = '.$field_ids[0];
					}
				}
				else
				{
					$where[] = 'rel.parent_field_id IN ('.implode(',', $field_ids).')';

					if ($dir == 'siblings' || $dir == 'coparents')
					{
						$where[] = 'link.parent_field_id IN ('.implode(',', $field_ids).')';
					}
				}
			}

			// filter by column?
			if ($col_ids)
			{
				if (count($col_ids) == 1)
				{
					$where[] = 'rel.parent_col_id = '.$col_ids[0];

					if ($dir == 'siblings' || $dir == 'coparents')
					{
						$where[] = 'link.parent_col_id = '.$col_ids[0];
					}
				}
				else
				{
					$where[] = 'rel.parent_col_id IN ('.implode(',', $col_ids).')';

					if ($dir == 'siblings' || $dir == 'coparents')
					{
						$where[] = 'link.parent_col_id IN ('.implode(',', $col_ids).')';
					}
				}
			}

			// filter by row?
			if ($row_ids)
			{
				if (count($row_ids) == 1)
				{
					$where[] = 'rel.parent_row_id = '.$row_ids[0];

					if ($dir == 'siblings' || $dir == 'coparents')
					{
						$where[] = 'link.parent_row_id = '.$row_ids[0];
					}
				}
				else
				{
					$where[] = 'rel.parent_row_id IN ('.implode(',', $row_ids).')';

					if ($dir == 'siblings' || $dir == 'coparents')
					{
						$where[] = 'link.parent_row_id IN ('.implode(',', $row_ids).')';
					}
				}
			}

			// filter by entry ID?
			foreach ($filter_ids as $col => $entry_ids)
			{
				if ($entry_ids)
				{
					if ($not = (strncmp($entry_ids, 'not ', 4) == 0))
					{
						$entry_ids = substr($entry_ids, 4);
					}

					$entry_ids = explode('|', $entry_ids);

					if (count($entry_ids) == 1)
					{
						$where[] = "rel.{$col}_entry_id".($not ? ' <> ' : ' = ').$entry_ids[0];
					}
					else
					{
						$where[] = "rel.{$col}_entry_id".($not ? ' NOT' : '').' IN ('.implode(',', $entry_ids).')';
					}
				}
			}

			if ($join)
			{
				$sql .= $join;
			}

			if ($where)
			{
				$sql .= ' WHERE '.implode(' AND ', $where);
			}

			if ($sql_end)
			{
				$sql .= ' '.implode(' ', $sql_end);
			}

			// get the relationships

			// -------------------------------------------
			//  'playa_fetch_rels_query' hook
			//   - Override or update the query
			//
				if ($this->EE->extensions->active_hook('playa_fetch_rels_query'))
				{
					$data = array(
						'and' => $this->and,
						'dir' => $dir,
						'entry_ids' => $this->entry_ids,
						'var_ids' => $var_ids,
						'field_ids' => $field_ids,
						'col_ids' => $col_ids,
						'row_ids' => $row_ids,
						'filter_ids' => $filter_ids,
						'parent_is_draft' => $this->is_draft
					);

					$rels = $this->EE->extensions->call('playa_fetch_rels_query', $this, $sql, $data);
				}
				else
				{
					$rels = $this->EE->db->query($sql);
				}
			//
			// -------------------------------------------

			$rels = $rels->result_array();
			$entry_ids = array();

			// if this is siblings or co-parents, order by the number of common links
			if ($dir == 'siblings' || $dir == 'coparents')
			{
				$rels_per_link = array();

				foreach ($rels as $rel)
				{
					if (! isset($rels_per_link[$rel['entry_id']]))
					{
						$entry_ids[] = $rel['entry_id'];
						$rels_per_link[$rel['entry_id']] = 1;
					}
					else
					{
						$rels_per_link[$rel['entry_id']]++;
					}
				}

				array_multisort($rels_per_link, SORT_DESC, $entry_ids);
			}
			else
			{
				// just return the entry IDs
				foreach ($rels as $rel)
				{
					$entry_ids[] = $rel['entry_id'];
				}
			}


			// cache them in case an identical request comes later
			$this->cache['rels'][$cache_key] = $entry_ids;
		}


		return $this->cache['rels'][$cache_key];
	}

	/**
	 * Returns a pipe-delimited string of the related entry IDs.
	 * @access private
	 * @param string $dir     The direction of the relationship (children, parents, siblings, or coparents)
	 * @param bool   $ids_tag Whether this is coming from a *_ids tag
	 */
	private function _fetch_rel_entry_ids_str($dir, $ids_tag = FALSE)
	{
		$entry_ids = $this->_fetch_rel_entry_ids($dir);

		// get the delimiter
		$delimiter = ($ids_tag && ($delimiter = $this->EE->TMPL->fetch_param('delimiter')) !== FALSE) ? $delimiter : '|';

		// flatten the array
		$r = implode($delimiter, $entry_ids);

		// backspace=
		if ($ids_tag && ($backspace = $this->EE->TMPL->fetch_param('backspace')))
		{
			$r = substr($r, 0, -$backspace);
		}

		return $r;
	}

	/**
	 * Child IDs
	 */
	function child_ids($ids_tag = TRUE)
	{
		return $this->_fetch_rel_entry_ids_str('children', $ids_tag);
	}

	/**
	 * Parent IDs
	 */
	function parent_ids($ids_tag = TRUE)
	{
		return $this->_fetch_rel_entry_ids_str('parents', $ids_tag);
	}

	/**
	 * Sibling IDs
	 */
	function sibling_ids($ids_tag = TRUE)
	{
		if ($sibling_ids = $this->EE->TMPL->fetch_param('sibling_id'))
		{
			$this->EE->TMPL->tagparams['child_id'] = $sibling_ids;
		}

		return $this->_fetch_rel_entry_ids_str('siblings', $ids_tag);
	}

	/**
	 * Co-parent IDs
	 */
	function coparent_ids($ids_tag = TRUE)
	{
		if ($coparent_ids = $this->EE->TMPL->fetch_param('coparent_id'))
		{
			$this->EE->TMPL->tagparams['parent_id'] = $coparent_ids;
		}

		return $this->_fetch_rel_entry_ids_str('coparents', $ids_tag);
	}

	// --------------------------------------------------------------------

	/**
	 * Children
	 */
	function children()
	{
		$tagdata = $this->_fetch_tagdata();
		if (! $tagdata) return;

		// tagdata prep
		$tagdata = str_replace('total_related_entries', 'total_results', $tagdata);
		$this->_parse_no_results_tag($tagdata, 'no_children');
		$this->_parse_relative_entry_tags($tagdata, 'parent:');

		$rel_ids = $this->child_ids(FALSE);
		return $this->_parse_relationships($tagdata, $rel_ids, TRUE);
	}

	/**
	 * Parents
	 */
	function parents()
	{
		$tagdata = $this->_fetch_tagdata(TRUE);
		if (! $tagdata) return;

		// tagdata prep
		$this->_parse_no_results_tag($tagdata, 'no_parents');
		$this->_parse_relative_entry_tags($tagdata, 'child:');

		$rel_ids = $this->parent_ids(FALSE);
		return $this->_parse_relationships($tagdata, $rel_ids, FALSE);
	}

	/**
	 * Siblings
	 */
	function siblings()
	{
		$tagdata = $this->_fetch_tagdata();
		if (! $tagdata) return;

		// tagdata prep
		$tagdata = str_replace('total_related_entries', 'total_results', $tagdata);
		$this->_parse_no_results_tag($tagdata, 'no_siblings');
		$this->_parse_relative_entry_tags($tagdata, 'sibling:');

		$rel_ids = $this->sibling_ids(FALSE);
		return $this->_parse_relationships($tagdata, $rel_ids, TRUE);
	}

	/**
	 * Co-parents
	 */
	function coparents()
	{
		$tagdata = $this->_fetch_tagdata();
		if (! $tagdata) return;

		// tagdata prep
		$tagdata = str_replace('total_related_entries', 'total_results', $tagdata);
		$this->_parse_no_results_tag($tagdata, 'no_coparents');
		$this->_parse_relative_entry_tags($tagdata, 'parent:');

		$rel_ids = $this->coparent_ids(FALSE);
		return $this->_parse_relationships($tagdata, $rel_ids, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Entries Query
	 *
	 * Loads the Playa Helper class and calls its entries_query() function
	 */
	private function _total_entries($entry_ids)
	{
		// ignore if no entry_ids
		if (! $entry_ids) return 0;

		// -------------------------------------------
		//  Get the parameters
		// -------------------------------------------

		$params = array(
			'count'                      => TRUE,
			'entry_id'                   => $entry_ids,
			'status'                     => 'open',
			'show_expired'               => 'no',
			'show_future_entries'        => 'no',
			'only_show_editable_entries' => 'no',
		);

		$check_params = array('author_id', 'group_id', 'category', 'category_group', 'show_expired', 'show_future_entries', 'only_show_editable_entries', 'status', 'url_title', 'channel', 'channel_id', 'keywords', 'orderby', 'sort', 'limit', 'offset');

		foreach ($check_params as $param)
		{
			if (($val = $this->EE->TMPL->fetch_param($param)) !== FALSE)
			{
				$params[$param] = $val;
			}
		}

		return (int) $this->helper->entries_query($params);
	}

	/**
	 * Total Children
	 *
	 * Returns the total number of children for this entry/field/column/row
	 */
	function total_children()
	{
		$rel_ids = $this->child_ids(FALSE);
		return $this->_total_entries($rel_ids);
	}

	/**
	 * Total Parents
	 *
	 * Returns the total number of parents for this entry
	 */
	function total_parents()
	{
		$rel_ids = $this->parent_ids(FALSE);
		return $this->_total_entries($rel_ids);
	}

	/**
	 * Total Siblings
	 *
	 * Returns the total number of siblings for this entry
	 */
	function total_siblings()
	{
		$rel_ids = $this->sibling_ids(FALSE);
		return $this->_total_entries($rel_ids);
	}

	/**
	 * Total Co-parents
	 *
	 * Returns the total number of co-parents for this entry
	 */
	function total_coparents()
	{
		$rel_ids = $this->coparent_ids(FALSE);
		return $this->_total_entries($rel_ids);
	}

}
