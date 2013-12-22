<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'structure/helper.php';

/**
 * SQL Model for Structure
 *
 * This file must be in your /system/third_party/structure directory of your ExpressionEngine installation
 *
 * @package             Structure
 * @author              Jack McDade (jack@jackmcdade.com)
 * @author              Travis Schmeisser (travis@rockthenroll.com)
 * @copyright           Copyright (c) 2013 Travis Schmeisser
 * @link                http://buildwithstructure.com
 */

class Sql_structure
{

	var $site_id;
	var $data  = array();
	var $cids  = array();
	var $lcids = array();

	function Sql_structure()
	{
		$this->EE =& get_instance();
		$this->EE->load->add_package_path(PATH_THIRD . 'structure/');
		$this->EE->load->library('sql_helper');

		$this->site_id = $this->get_site_id();

		// -------------------------------------------
		//  Prepare Cache
		// -------------------------------------------

		if (! isset($this->EE->session->cache['structure']))
		{
			$this->EE->session->cache['structure'] = array();
		}
		$this->cache =& $this->EE->session->cache['structure'];
	}


	/**
	 * Get global and MSM specific settings
	 * from exp_structure_settings table
	 *
	 * @return array
	 **/
	function get_settings()
	{
		static $settings = NULL;

		if (is_array($settings) || ! $this->module_is_installed()) {
			return $settings;
		}

		$site_id = '0,'.$this->site_id;

		$sql = "SELECT var_value, var FROM exp_structure_settings WHERE site_id IN ({$site_id})";
		$result = $this->EE->db->query($sql);

		$settings = array(
			'show_picker'          => 'y',
			'show_view_page'       => 'y',
			'show_status'          => 'y',
			'show_page_type'       => 'y',
			'show_global_add_page' => 'y',
			'redirect_on_login'    => 'n',
			'redirect_on_publish'  => 'n',
			'add_trailing_slash'   => 'y'
		);

		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				if ($row['var_value'] != '')
				{
					$settings[$row['var']] = $row['var_value'];
				}
			}
		}

		return $settings;
	}

	function get_categories($group_id)
	{
		$sql = "SELECT * from exp_categories where group_id={$group_id}";
		
		$result = $this->EE->db->query($sql);		
		
		$data = $result->result_array();
		
		
		// =debug
		header('Content-Type: text/plain; charset=iso-8859-1');
		print_r($cats);
		exit;
		
		return $data;
	}

	/**
	 * Get all data for all Structure Channels
	 *
	 * @return array
	 */
	function get_data($entryid=0)
	{
		$data = array();

		$sql = "SELECT node.*, (COUNT(parent.entry_id) - 1) AS depth, expt.title, expt.status
				FROM exp_structure AS node
				INNER JOIN exp_structure AS parent
					ON node.lft BETWEEN parent.lft AND parent.rgt
				INNER JOIN exp_channel_titles AS expt
					ON node.entry_id = expt.entry_id
				WHERE parent.lft > 1";
				
		if($entryid!=0)
		{
			$sql .= " AND expt.entry_id !=".$entryid;
			$sql .= " AND node.parent_id !=".$entryid;
		}
				
		$sql .=	" AND node.site_id = {$this->site_id}
				AND parent.site_id = {$this->site_id}
				GROUP BY node.entry_id
				ORDER BY node.lft";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				$data[$row['entry_id']] = $row;
			}
		}

		// -------------------------------------------
		// 'structure_get_data_end' hook.
		//
			if ($this->EE->extensions->active_hook('structure_get_data_end') === TRUE)
			{
				$data = $this->EE->extensions->call('structure_get_data_end', $data);
			}
		//
		// -------------------------------------------

		return $data;
	}

	function get_status_colors()
	{
		$data = array();
		$sql = "SELECT status, highlight FROM exp_statuses WHERE site_id = $this->site_id";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				$data[$row['status']] = $row['highlight'];
			}
		}
		return $data;

	}


	/**
	 * Get selective data on all Structure Channels
	 *
	 * @return array
	 */
	function get_selective_data($site_id, $current_id, $branch_entry_id, $mode, $show_depth, $max_depth, $status, $include, $exclude, $show_overview, $rename_overview, $show_expired, $show_future, $override_hidden_state="no")
	{
		
		$parent_id = $this->get_parent_id($current_id);

		$settings = $this->get_settings();
		$trailing_slash = isset($settings['add_trailing_slash']) && $settings['add_trailing_slash'] === 'y';

		$pages = $this->get_site_pages();

		/*
		Trimming Control Params:

		The tree trimmer loads all the entire tree/branch for all nodes
		within the branch_entry_id (which is set by 'start_from' param).

		The trimmer then removes nodes based on the following:
		start_from/branch_entry_id = The root of this nav
		show_depth =	Depth past 'start_from' node which should always be shown
						(use -1 to disable will also disable 'expand_depth')
		expand_depth =	If current node is at the edge, or past the edge of show_depth
						then it will keep (aka expand) this much further
						(use -1 to disable note: only a depth of one currently works)

		---
		Then something crazy happens- purify_bloodlines is called on the current node
		removing all cousins, 2nd cousins etc.

		To prevent un-wanted carnage the active sub-branch (the limb which contains the
		current node on it) can be severed from it's parent ($node->parent = NULL;)
		stopping the bloodline.
		---

		max_depth =		Depth past 'start_from' which you never want to see. Happens
						last which means it will over-ride all the others.
						(use -1 to disable)
		*/

		switch ($mode)
		{
			case 'full':
				// show everything
				$branch_entry_id = 0;	// start from root
				$show_depth = -1;		// defaults to show full tree, passed by the param
				$expand_depth = -1;		// don't trim past current
				// $max_depth = -1;	(can be specified by tag)
				$current_id = FALSE;
				break;
			case 'main':
				// show top nav but never any children
				$branch_entry_id = 0;	// start from root
				$show_depth = -1;		// show full tree
				$expand_depth = -1;		// don't trim past current
				$max_depth = 1;			// only show top level
				$current_id = FALSE;
				break;
			case 'sub':
				$expand_depth = 1;		// show child of current node
				// am I a listing?
				if ($current_id !== FALSE && $this->is_listing_entry($current_id))
					$current_id = $parent_id;
				break;
		}

		if ($show_depth == 'all')
			$show_depth = -1;

		$status = strtolower($status);
		$status_exclude = FALSE;
		if (strncmp($status, 'not ', 4) == 0)
		{
			$status_exclude = TRUE;
			$status = substr($status, 4);
		}
		$statuses = explode('|', $status);

		if ( ! is_array($include))
			$include = array_filter(explode('|', $include), 'ctype_digit');

		if ( ! is_array($exclude))
			$exclude = array_filter(explode('|', $exclude), 'ctype_digit');

		// ---
		// Retreive branch data from DB
		// ---

		// generate flash-data cache name
		$cache_name = 'root='.$branch_entry_id;
		if (count($exclude))
		{
			sort($exclude, SORT_NUMERIC);
			$cache_name .= '-'.implode(',', $exclude);
		}

		// check the flash-data cache
		$results = @$this->EE->session->cache['structure'][$cache_name];
		$results = '';
		if ( ! is_array($results))
		{
			$where_exclude = '';

			foreach ($exclude as $id)
			{
				if ($id != '' && array_key_exists($id, $pages['uris']))
					$where_exclude .= " AND structure.lft NOT BETWEEN (SELECT lft FROM exp_structure WHERE entry_id = '$id') AND (SELECT rgt FROM exp_structure WHERE entry_id = '$id')";
			}

			// $where_include = '';
			// foreach ($include as $id)
			// {
			// 	if ($id != '' && array_key_exists($id, $pages['uris']))
			// 		$where_include .= " AND structure.lft BETWEEN (SELECT lft FROM exp_structure WHERE entry_id = '$id') AND (SELECT rgt FROM exp_structure WHERE entry_id = '$id')";
			// }

			$timestamp = ($this->EE->TMPL->cache_timestamp != '') ? $this->EE->TMPL->cache_timestamp : $this->EE->localize->now;

			if ($show_future == 'no')
			{
				$where_exclude .= " AND (structure.entry_id = 0 OR titles.entry_date < ".$timestamp.") ";
			}

			if ($show_expired == 'no')
			{
				$where_exclude .= " AND (structure.entry_id = 0 OR titles.expiration_date = 0 OR titles.expiration_date > ".$timestamp.") ";
			}


			$sql = "SELECT structure.*, titles.title, titles.entry_date, titles.expiration_date,  LOWER(titles.status) AS status
					FROM
						exp_structure AS structure
						LEFT JOIN exp_channel_titles AS titles
							ON (structure.entry_id = titles.entry_id)
						JOIN (
							SELECT entry_id, lft, rgt
							FROM exp_structure
							WHERE entry_id = '$branch_entry_id' AND site_id IN (0,'$site_id')
						) AS root_node
							ON (structure.lft BETWEEN root_node.lft AND root_node.rgt)
								OR structure.entry_id = root_node.entry_id
					WHERE
						structure.site_id IN (0,'$site_id')
						AND (titles.entry_id IS NOT NULL OR structure.entry_id = 0)
						$where_exclude
					ORDER BY structure.lft";
			// echo $sql;
			$query = $this->EE->db->query($sql);
			$results = $query->result_array();
			$query->free_result();
			
			// -------------------------------------------
			// 'structure_get_selective_data_results' hook.
			//
				if ($this->EE->extensions->active_hook('structure_get_selective_data_results') === TRUE)
				{
					$results = $this->EE->extensions->call('structure_get_selective_data_results', $results);
				}
			//
			// -------------------------------------------

			$this->EE->session->cache['structure'][$cache_name] = $results;
		}
		
		// ---
		// Return empty array with no nav
		// ---
		if (count($results) == 0)
			return array();

		// ---
		// Build branch tree and trim
		// ---

		$tree = structure_leaf::build_from_results($results);

		// find the current page in the tree
		$cur_leaf = FALSE;
		if ($current_id !== FALSE)
			$cur_leaf = $tree->find_ancestor('entry_id', $current_id);
		// note: if cur_leaf = FALSE then the current page is not in this sub nav

		if ($cur_leaf === FALSE)
		{
			// the current page is not in this branch
			// use root as current
			$cur_leaf = $tree;
		}

		// limit the shown depth (-1 for show all)
		if ($show_depth >= 0)
		{
			foreach ($tree->children as $child)
			{
				if ($child->has_ancestor($cur_leaf))
				{
					$cur_depth = $cur_leaf->depth();
					if ($cur_depth < $show_depth)
					{
						// not past show_depth yet (no expansion)
						$child->prune_children($show_depth-1);
						// prevent purify_bloodlines from working
						$cur_leaf->parent = NULL;
					}
					elseif ($expand_depth >= 0)
					{
						// expand past current node
						// while preserving show_depth of other branches
						$cur_leaf->prune_children($expand_depth);
						foreach ($child->children as $grandchild)
						{
							if ($grandchild->has_ancestor($cur_leaf))
							{
								// protect non-active branches from
								// purify_bloodlines
								$grandchild->parent = NULL;
							}
							else
							{
								// but don't forget to prune them to
								// the correct show_depth
								$grandchild->prune_children($show_depth-2);
							}
						}
					}
					else
					{
						$child->prune_children($show_depth-1);
						// prevent purify_bloodlines from working
						$cur_leaf->parent = NULL;
					}
				}
				else
				{
					// keep show_depth of non-active branches
					$child->prune_children($show_depth-1);
				}
			}
		}

		// gets rid of cousins and 2nd cousins
		// keeps children, parents and uncles
		$cur_leaf->purify_bloodline();

		// limit overall depth shown (-1 for infinite)
		if ($max_depth >= 0)
			$tree->prune_children($max_depth);

		// limit based on status
		if (count($statuses))
		{
			if ($tree->row['entry_id'] != 0) // don't test structure ROOT (would always fail)
			{
				if ($tree->is_of_value('status', $statuses, $status_exclude))
					return array(); // root node removed based on critera
			}

			$tree->selective_prune('status', $statuses, $status_exclude);
		}

		if($override_hidden_state)
		{
		if ($override_hidden_state != 'yes')
		  $tree->selective_prune('hidden', array('y'), TRUE);
		}

		// limit to 'include' ids
		if (count($include))
		{
			array_unshift($include, $branch_entry_id); // add current root node as valid
			if ($tree->is_of_value('entry_id', $include, FALSE))
				return array(); // root node removed based on critera

			$tree->selective_prune_alt('entry_id', $include, FALSE);
		}

		// add 'depth' to the rows
		// (happens here, because the tree is already trimmed = less waste)
		$tree->add_row_depth();

		// rebuild results from what is left in the tree
		$results = $tree->get_results();
		
		if ($show_overview)
		{
			// add sql to get this entry
			$overview = $this->get_overview($branch_entry_id);
			$rename_overview = $rename_overview == 'title' ? $overview['title'] : $rename_overview; // override if "title"

			$overview['title'] = $rename_overview;
			$overview['overview'] = $rename_overview;

			array_unshift($results, $overview);
		}

		$data = array();
		foreach ($results as $row)
		{
			if ( ! isset($row['entry_id'])) continue;

			if (isset($pages['uris'][$row['entry_id']]))
			{
				$data[$row['entry_id']] = $row;
				$data[$row['entry_id']]['uri'] = $this->EE->functions->create_page_url($pages['url'], $pages['uris'][$row['entry_id']], $trailing_slash);
				$data[$row['entry_id']]['slug'] = $pages['uris'][$row['entry_id']];
				$data[$row['entry_id']]['classes'] = array();
				$data[$row['entry_id']]['ids'] = array();
				// echo $data[$row['entry_id']]['uri'].' ';
			}
		}
		
		return $data;
	}


	function count_segments($uri)
	{
		if ($uri != "")
		{
			$uri = Structure_Helper::remove_double_slashes(trim(html_entity_decode($uri), '/'));
			$segment_array = explode('/', $uri);
			return count($segment_array);
		}
		return NULL;
	}


	/**
	 * Get a single path of data, useful for breadcrumbs etc
	 *
	 * @return array
	 */
	function get_single_path($entry_id)
	{
		$listing_ids = $this->get_listing_entry_ids();
		if (array_key_exists($entry_id, $listing_ids))
		{
			$entry_id = $this->get_parent_id($entry_id);
		}

		$sql = "SELECT parent.lft, parent.rgt,expt.title, expt.entry_id
				FROM exp_structure AS node,
					exp_structure AS parent
				INNER JOIN exp_channel_titles AS expt ON parent.entry_id = expt.entry_id
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.entry_id = '$entry_id'
					AND expt.site_id = '$this->site_id'
				ORDER BY parent.lft";

		$result = $this->EE->db->query($sql);

		$data = array();
		$pages = $this->get_site_pages();

		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				if (array_key_exists($row['entry_id'], $pages['uris'])) {
					$data[$row['entry_id']] = $row;
					$data[$row['entry_id']]['uri'] = $this->EE->functions->create_page_url($pages['url'], $pages['uris'][$row['entry_id']], false);
				}
			}
		}

		return $data;

	}

	function reindex_at_one($array)
	{
		$start_at = 1;
		$new_array = array();
		foreach ($array as $key => $row)
		{
			$array[$start_at] = $row;
			$start_at++;
		}
		return $array;
	}

	function add_attributes($pages, $entry_id, $mode,$override_hidden_state="no")
	{
		
		$top_array = array();
		$separator = $this->EE->config->item('word_separator') != "dash" ? '_' : '-';
		$root_id = $this->EE->TMPL->fetch_param('css_id', 'nav' . $separator . $mode);
		$root_id = $root_id == "none" ? 'nav' : $root_id;

		$path_pages = $this->get_single_path($entry_id);
		$parent_id = $this->get_parent_id($entry_id);
		$listing_ids = $this->get_listing_entry_ids();

		$zero_index_pages = array_values($pages);
		
		$i = 1;
		foreach ($zero_index_pages as $index => $page)
		{
			$key = $page['entry_id'];

			// level classes
			if ($this->EE->TMPL->fetch_param('add_level_classes', FALSE) !== FALSE)
			{
				$pages[$key]['classes'][] = 'level' . $separator . $page['depth'];
			}

			// here class
			$current_class = $this->EE->TMPL->fetch_param('current_class', 'here');

			if ($page['entry_id'] == $entry_id && $current_class != "no" && $current_class != "off" && $current_class != "none")
			{
				$pages[$key]['classes'][] = $current_class;
			}

			// here class to listing parent
			if (array_key_exists($entry_id, $listing_ids) && $page['entry_id'] == $parent_id)
			{
				$pages[$parent_id]['classes'][] = $current_class;
			}

			// parent-here class
			if (array_key_exists($page['entry_id'], $path_pages) && $page['entry_id'] != $entry_id && ! array_key_exists('overview', $page))
			{
				if ($mode == "main")
				{
					$pages[$key]['classes'][] = $current_class;
				}
				else
				{
					$pages[$key]['classes'][] = 'parent' . $separator . $current_class;
				}

			}

			// has-children class
			$has_children_class = $this->EE->TMPL->fetch_param('has_children_class', 'no');

			if ($has_children_class != 'no' && $page['rgt'] - $page['lft'] > 1
				&& ! array_key_exists('overview', $page)
				&& (isset($zero_index_pages[$index+1]['parent_id']) && $zero_index_pages[$index+1]['parent_id'] == $key)
			)
			{
				if ($has_children_class !== "yes")
				{
					$pages[$key]['classes'][] = $has_children_class;
				}
				else
				{
					$pages[$key]['classes'][] = 'has' . $separator . 'children';
				}
			}

			// unique ids
			if ($this->EE->TMPL->fetch_param('add_unique_ids', FALSE) == "yes" || $this->EE->TMPL->fetch_param('add_unique_ids', FALSE) == "on")
			{
				$slugs = $this->get_slug($page['slug'],true);
				
				$pageslug='';
				
				foreach($slugs as $s)
				{
					$pageslug .= $separator . $s;
				}
				
				$pages[$key]['ids'][] = $page['slug'] == '/' ? $root_id . $separator . 'home' : $root_id . $pageslug;
			}
			elseif ($this->EE->TMPL->fetch_param('add_unique_ids', FALSE) == "entry_ids" || $this->EE->TMPL->fetch_param('add_unique_ids', FALSE) == "entry_id")
			{
				$pages[$key]['ids'][] = $root_id . $separator . $page['entry_id'];
			}

			if (array_key_exists('overview', $page))
			{
				$pages[$key]['classes'][] = 'first';
				$pages[$key]['classes'][] = 'overview';
			}

			// first class
			if (($i == 1
				 && ( ! in_array('first', $pages[$page['entry_id']]['classes']))) #first page
					|| ($page['parent_id'] != 0
				&& array_key_exists($page['parent_id'], $pages)
				&& ($page['lft']-1) == $pages[$page['parent_id']]['lft']
				&& ( ! in_array('overview', $pages[$page['parent_id']]['classes']))

				))
			{
				$pages[$key]['classes'][] = 'first';
			}

			
			// last class
			if ($page['parent_id'] != 0 && array_key_exists($page['parent_id'], $pages) && ($page['rgt']+1) == $pages[$page['parent_id']]['rgt'])
			{
					
					// If this is the last but it's set to hidden, we want to go back and set the 
					// previous entry and then remove it from the nav.
					if($pages[$key]['hidden']=="y" && $override_hidden_state!="yes")
					{
						$pages[$key-1]['classes'][] = 'last';
						unset($pages[$key]);
					}
					else
					{
						$pages[$key]['classes'][] = 'last';	
					}
					
			}

			// Build array of top level items
			if ($page['depth'] == 1)
			{
				$top_array[] = $page;
			}

			$i++;
		}

		$first = reset($top_array);


		// Account for the very first top level item and add class="first"
		// if (count($top_array) > 0 && ! in_array('last', $pages[$first['entry_id']]['classes']))
			// $pages[$first['entry_id']]['classes'][] = 'first';

		$last = end($top_array);

		// Account for the very last top level item and add class="last"
		if (count($top_array) > 0 && ! in_array('last', $pages[$last['entry_id']]['classes']))
			$pages[$last['entry_id']]['classes'][] = 'last';

		return $pages;
	}

	function get_slug($uri = FALSE,$all=false)
	{
		if ($uri !== FALSE)
		{
			$segments = explode('/', trim($uri, '/'));
			if ($all)
			{
				return $segments;	
			}
			else
			{
				return end($segments);
			}
		}
		return FALSE;
	}

	/**
	 * Get the HTML code for an unordered list of the tree
	 * @return string HTML code for an unordered list of the whole tree
	 */
	function generate_nav($selective_data, $current_id, $entry_id, $mode, $show_overview, $rename_overview,$override_hidden_state="no")
	{
		
		$html = '';
		$separator = $this->EE->config->item('word_separator') != "dash" ? '_' : '-';

		// Fallback to entry_id if no current_id (e.g. sitemap usage)
		if ($current_id === FALSE)
			$current_id = $entry_id;

		$pages = $this->add_attributes($selective_data, $current_id, $mode,$override_hidden_state);
		

		// Now we've got the data, we need to do a cleanup to remove any child entries which don't have the parents
			//$lp=0;
			//
			//foreach($pages as $row)
			//{
			//	//Ignore the Root Node
			//	if (isset($row['parent_id']))
			//	{
			//		if($row['parent_id']!="0")
			//		{
			//			if (!isset($pages[$row['parent_id']]))
			//			{
			//				unset($pages[$lp]);	
			//			}
			//		}
			//		$lp++;
			//	}	
			//}		

		$tree = array_values($pages);
		$tree_count = count($tree);
		
		if ($tree_count < 1)
			return NULL;

		$custom_title_fields = $this->create_custom_titles();

		for ($i=0; $i < $tree_count; $i++)
		{
			// if ($i == 0) {
			// 	$tree[$i]['classes'][] = 'first';
			// }

			// Build class string if any exist
			$classes = count($tree[$i]['classes']) > 0 ? ' class="'. implode(' ',$tree[$i]['classes']) .'"' : NULL;
			
			// Build id string if any exist
			$ids = count($tree[$i]['ids']) > 0 ? ' id="'. implode(' ',$tree[$i]['ids']) .'"' : NULL;

			// Title field: custom|title
			$title = $custom_title_fields !== FALSE ? $custom_title_fields[$tree[$i]['entry_id']] : $tree[$i]['title'];

			if ($this->EE->TMPL->fetch_param('encode_titles', 'yes') === "yes") {
				$title = htmlspecialchars($title);
			}

			// Add span hook if desired
			$title_output = $this->EE->TMPL->fetch_param('add_span', FALSE) == "yes" ? "<span>" . $title . "</span>" : $title;

			// -------------------------------------------
			//  The list item itself
			// -------------------------------------------

			$html .= '<li'. $classes . $ids . '><a href="' . $tree[$i]['uri'] . '">' . $title_output .'</a>';

			// Closing up a level
			if ($tree[$i]['depth'] < @$tree[$i+1]['depth'])
			{
				$html .= "\n<ul>\n";
				
				if ($show_overview)
				{
					if ($tree[$i]['depth']=="1")
					{
					
					if($rename_overview=="title")
					{
						$title = $tree[$i]['title'];
					}
					else
					{
						$title = $rename_overview;
					}
					
					$html .= '<li'. $classes . $ids . '><a href="' . $tree[$i]['uri'] . '">'.$title.'</a>';	
					}
				}
				
				
			}
			// Closing up a list item
			elseif (@$tree[$i]['depth'] == @$tree[$i+1]['depth'])
			{
				$html .= "</li>\n";
			}
			// Closing up multiple levels and list items
			else
			{
				$diff = (array_key_exists($i+1, $tree)) ? $tree[$i]['depth'] - $tree[$i+1]['depth'] : $tree[$i]['depth'] - 1 ;
				$html .= str_repeat("</li>\n</ul>\n", $diff) . "</li>\n";
			}
		}


		// Add the unordered list element
		if ($this->EE->TMPL->fetch_param('include_ul', 'yes') == 'yes')
		{
			// Add css class
			$css_class = $this->EE->TMPL->fetch_param('css_class', NULL);
			if ($css_class !== NULL && $css_class != '')
				$css_class = " class=\"$css_class\"";


			$css_id = $this->EE->TMPL->fetch_param('css_id', 'nav' . $separator . $mode);
			$root_id =  " id=\"" . $css_id . "\"";

			if ($css_id == 'none' || $css_id == 'no')
				$root_id = NULL;


			$html = $this->EE->TMPL->fetch_param('wrap_start', NULL) . "<ul$root_id$css_class>\n". $html ."</ul>" . $this->EE->TMPL->fetch_param('wrap_end', NULL);
		}

		return $html;
	}


	function create_custom_titles($include_listings = FALSE)
	{
		$custom_titles = $this->EE->TMPL->fetch_param('channel:title', FALSE);
		if ($custom_titles === FALSE) return FALSE;

		$custom_titles = explode('|', $custom_titles);

		// -------------------------------------------
		// 'structure_create_custom_titles' hook.
		//
        if ($this->EE->extensions->active_hook('structure_create_custom_titles') === TRUE)  {
			$page_titles = $this->EE->extensions->call('structure_create_custom_titles', $custom_titles);

			return $page_titles;
		}
	    //
	    // -------------------------------------------

		// Load the Channel API
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_fields');

		$title_fields = array();

		if ( ! isset($this->cache['custom_titles']))
		{
			$query = $this->EE->db->query("SELECT channel_id, channel_name FROM exp_channels WHERE site_id = '$this->site_id'");

			foreach ($query->result_array() as $row)
			{
				$this->cache['custom_titles'][$row['channel_name']] = $row;
			}
		}

		foreach ($custom_titles as $pair)
		{
			if (strstr($pair, ':') === FALSE)
				return FALSE;

			$exploded = explode(':', $pair);

			// This should never run, but keep it here just incase.
			if ( ! isset($this->cache['custom_titles'][$exploded[0]]))
			{
				$result = $this->EE->sql_helper->row("SELECT channel_id, channel_name FROM exp_channels WHERE channel_name = '$exploded[0]' AND site_id = '$this->site_id'");

				$this->cache['custom_titles'][$exploded[0]] = $result;
			}

			if ($this->cache['custom_titles'][$exploded[0]] !== NULL)
				$title_fields[$this->cache['custom_titles'][$exploded[0]]['channel_id']] = $exploded[1];
		}


		$c_fields = $this->EE->api_channel_fields->fetch_custom_channel_fields();
		$c_fields = array_key_exists($this->site_id, $c_fields['custom_channel_fields']) ? $c_fields['custom_channel_fields'][$this->site_id] : NULL;

		$sql_fields = array();
		foreach ($title_fields as $channel_id => $field)
		{
			if ( ! is_array($c_fields))
				continue;

			$sql_fields[$channel_id]['field_id'] = array_key_exists($field, $c_fields) ? $c_fields[$field] : FALSE;
			$sql_fields[$channel_id]['field_name'] = array_key_exists($field, $c_fields) ? $field : FALSE;
		}

		$structure_channels = $this->get_structure_channels('page');

		if ($include_listings === TRUE)
		{
			$add_ch_list = $this->get_structure_channels('listing');
			if (is_array($add_ch_list)) // no listing === FALSE so typecheck!
				$structure_channels += $add_ch_list;
		}
		$sql_channels = implode(',', array_keys($structure_channels));

		$select_statement = array();
		foreach ($sql_fields as $channel_id => $field)
		{
			if ($field['field_id'] !== FALSE)
			{
				// $select_statement[] = '(c_data.field_id_' . $field['field_id'] . ') AS ' . $field['field_name'];
				$select_statement[] = '(c_data.field_id_' . $field['field_id'] . ') AS `' . $field['field_name'] . '`';

			}
		}

		if (count($select_statement) == 0)
			return FALSE;

		$sql = "SELECT c_data.channel_id, c_data.entry_id, ". implode(',', $select_statement) .", c_titles.title
				FROM exp_channel_data AS c_data
					INNER JOIN exp_channel_titles AS c_titles ON c_data.entry_id = c_titles.entry_id
				WHERE c_data.channel_id IN ($sql_channels)
				AND c_data.site_id = $this->site_id";

		$result = $this->EE->db->query($sql);

		$page_titles = array();
		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $page)
			{
				if (array_key_exists($page['channel_id'], $sql_fields)
				&& $sql_fields[$page['channel_id']]['field_name'] !== FALSE
				&& $page[$sql_fields[$page['channel_id']]['field_name']] !== NULL
				&& $page[$sql_fields[$page['channel_id']]['field_name']] != '')
				{
					$page_titles[$page['entry_id']] = $page[$sql_fields[$page['channel_id']]['field_name']];
				}
				else
				{
					$page_titles[$page['entry_id']] = $page['title'];
				}
			}
		}

		return $page_titles;

	}


	function get_overview($entry_id)
	{
		$sql = "SELECT node.*, (1) AS depth,
					if((node.rgt - node.lft) = 1,1,0) AS isLeaf,
					((node.rgt - node.lft - 1) DIV 2) AS numChildren, expt.status, expt.title
				FROM exp_structure AS node
				INNER JOIN exp_structure AS parent
					ON node.lft BETWEEN parent.lft AND parent.rgt
				INNER JOIN exp_channel_titles AS expt
					ON node.entry_id = expt.entry_id
				WHERE node.entry_id = '$entry_id'
				AND parent.site_id IN (0,$this->site_id)
				GROUP BY node.lft
				LIMIT 1";

		$result = $this->EE->db->query($sql);

		if ($result->num_rows == 0)
		{
			return FALSE;
		}

		$result_row = "";
		foreach ($result->result_array() as $row)
			$result_row = $row;

		return $result_row;

	}


	function get_member_settings()
	{
		$data = array(
			'site_id' => $this->site_id,
			'member_id' => $member_id = $this->EE->session->userdata('member_id')
		);

		$results = $this->EE->db->get_where('structure_members', $data, 1);
		if ($results->num_rows > 0)
		{
			if ( ! function_exists('json_decode'))
				$this->EE->load->library('Services_json');

			$member_settings = $results->row_array();
			$member_settings['nav_state'] = json_decode($member_settings['nav_state']);

			return $member_settings;
		}
		return NULL;

	}


	function get_parent_id($entry_id, $default = 'home')
	{
		if (is_numeric($entry_id))
		{
			$listing_ids = $this->get_listing_entry_ids();
			if (array_key_exists($entry_id, $listing_ids)) {
				$sql = "SELECT parent_id FROM exp_structure_listings WHERE entry_id = $entry_id AND site_id = $this->site_id";
			} else {
				$sql = "SELECT parent_id FROM exp_structure WHERE entry_id = $entry_id AND site_id = $this->site_id";
			}

			$result = $this->EE->sql_helper->row($sql);

			// Get homepage instead
			if ($result['parent_id'] !== 0) {
				return $result['parent_id'];
			} elseif ($default == 'home') {
				$sql = "SELECT entry_id FROM exp_structure WHERE lft = 2 AND site_id = $this->site_id";
				$result = $this->EE->sql_helper->row($sql);

				if (isset($result['entry_id']) && is_numeric($result['entry_id'])) {
					return $result['entry_id'];
				}
			}
		}

		return FALSE;
	}

	function get_home_page_id()
	{
		$sql = "SELECT entry_id FROM exp_structure WHERE lft = 2 AND site_id = $this->site_id";
		$result = $this->EE->sql_helper->row($sql);

		return $result['entry_id'];
	}

	function get_home_node()
	{
		$sql = "SELECT * FROM exp_structure WHERE entry_id = 0";
		$result = $this->EE->sql_helper->row($sql);
		// print_r($result);

		return $result;
	}


	function get_page_title($entry_id)
	{
		if (is_numeric($entry_id))
		{
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->limit(1);

			$query = $this->EE->db->get('exp_channel_titles');

			if ($query->num_rows() == 1)
			{
				 $row = $query->row();
				 return $row->title;
			}
		}
		return FALSE;
	}

	function is_listing_entry($entry_id)
	{
		$listing_entries = $this->get_listing_entry_ids();

		return isset($listing_entries[$entry_id]);
	}


	function get_listing_entry_ids()
	{
		if ( ! isset($this->EE->session->cache['structure']['listing_ids']))
		{
			$sql = "SELECT entry_id FROM exp_structure_listings WHERE site_id = $this->site_id";
			$result = $this->EE->db->query($sql);

			if ($result->num_rows > 0)
			{
				$data = array();
				foreach ($result->result_array() as $row)
				{
					$this->EE->session->cache['structure']['listing_ids'][$row['entry_id']] = $row['entry_id'];
				}
			}
			else
			{
				$this->EE->session->cache['structure']['listing_ids'] = array();
			}
		}
		return $this->EE->session->cache['structure']['listing_ids'];
	}


	/**
	 * Get all data from the exp_structure_channels table
	 * @param $type|unmanaged|page|listing|asset
	 * @param $channel_id you can pass a channel_id to retreive it's data
	 * @param $order pass it 'alpha' to order by channel title
	 * @return array An array of channel_ids and it's associated template_id, type and channel_title
	 */
	function get_structure_channels($type = '', $channel_id = '', $order = '', $selector = FALSE)
	{
		$site_id = $this->EE->config->item('site_id');

		// Get Structure Channel Data
		$sql = "SELECT ec.channel_id, ec.channel_title, ec.site_id, esc.template_id, esc.type, esc.split_assets, esc.show_in_page_selector
				FROM exp_channels AS ec
				LEFT JOIN exp_structure_channels as esc USING (channel_id)
				WHERE ec.site_id = '$site_id'";
		if ($type != '') $sql .= " AND esc.type = '$type'";
		if ($channel_id != '') $sql .= " AND esc.channel_id = '$channel_id'";
		if ($selector == TRUE) $sql .= " AND esc.show_in_page_selector = 'y'";
		if ($order == 'alpha') $sql .= " ORDER BY ec.channel_title";

		$results = $this->EE->db->query($sql);

		if ($results->num_rows > 0)
		{
			// Format the array nicely
			$channel_data = array();
			foreach($results->result_array() as $key => $value)
			{
				$channel_data[$value['channel_id']] = $value;
			}
			return $channel_data;
		}

		return FALSE;
	}

	function get_channel_type($channel_id)
	{
		if (is_numeric($channel_id))
		{
			$sql = "SELECT type FROM exp_structure_channels WHERE channel_id = '$channel_id' AND site_id = '$this->site_id' LIMIT 1";
			$query = $this->EE->db->query($sql);
			if ($query->num_rows > 0)
			{
				$row = $query->row();
				return $row->type;
			}
		}
		return FALSE;
	}

	function get_default_template($channel_id)
	{
		if (is_numeric($channel_id))
		{
			$sql = "SELECT template_id FROM exp_structure_channels WHERE channel_id = '$channel_id' AND site_id = '$this->site_id' LIMIT 1";
			$query = $this->EE->db->query($sql);
			if ($query->num_rows > 0)
			{
				$row = $query->row();
				return $row->template_id;
			}
		}
		return FALSE;
	}


	function get_child_entries($parent_id,$cat='',$include_hidden='n')
	{
		$entries = array();
		$catarray = array();
		
		if($cat!='')
		{
			$cat_entries = $this->get_entries_by_category($cat);
			
			foreach($cat_entries as $entry)
			{
				$catarray[] = $entry['entry_id'];
			}
			
			$catarray = implode(",",$catarray);
			
		}
		
		if ($parent_id !== FALSE && is_numeric($parent_id))
		{
			
			$sql = "select entry_id from exp_structure where
						parent_id = ".$parent_id." AND
						entry_id!=0 AND
						site_id = ".$this->site_id;
			
			if($include_hidden=='n')
			{
				$sql .= " AND hidden != 'y' ";
			}
						
			// I've had to remove this from Active Record because CI adds backticks on a 
			// where_in clause which is a bit crap when you want to do an inclusive array of entry_id's!
			
			//$this->EE->db->select('entry_id')->from('structure')->where(array(
			//	'parent_id' => $parent_id,
			//	'entry_id !=' => 0,
			//	'hidsden !=' => 'y',
			//	'site_id' => $this->site_id
			//));
			
			if ($catarray)
			{
				$sql .= "	AND entry_id IN(".$catarray.")";
			}
			
			$sql .= "	order by lft asc";
			
			$results = $this->EE->db->query($sql);

			if ($results->num_rows() > 0)
			{
				foreach ($results->result_array() as $row)
				{
					$entries[] = $row['entry_id'];
				}
			}
		}
		
		return $entries;
	}
	
	function get_entries_by_category($cat)
	{
		//firstly, lets see whether we have a category id or a word
		if (is_numeric($cat))
		{
			//it's a number, so we'll assume a cat_id and not bother with the lookup.
			//I'm going to leave this if conditional in here though as I might want to use it at some point.
		}
		else
		{
			//it's not a number so we need to get a cat id;
			$result = $this->EE->db->select("cat_id")->from("categories")->where("cat_url_title",$cat)->get();
			
			if ($result->num_rows() > 0)
			{
				$cat = $result->row()->cat_id;
			}
		}
		
		// Now, lets get an array of all entries which are in this category_id
		return $this->EE->db->select("entry_id")->from("category_posts")->where("cat_id",$cat)->get()->result_array();
		
	}

	function get_channel_by_entry_id($entry_id)
	{
		$this->EE->db->select('channel_id')->from('channel_data')->where('entry_id', $entry_id);
		$result = $this->EE->db->get();
		if ($result->num_rows() > 0)
		{

			return $result->row()->channel_id;
		}
		return NULL;
	}

	function get_channel_name_by_channel_id($channel_id)
	{
		$this->EE->db->select('channel_name')->from('channels')->where('channel_id', $channel_id);
		$result = $this->EE->db->get();
		if ($result->num_rows() > 0)
		{
			return $result->row()->channel_name;
		}
		return NULL;
	}


	/**
	 * Get data from the exp_sites table
	 *
	 * @return array with site_id as key
	 */
	function get_site_pages($cache_bust=false, $override_slash=false)
	{
		$settings = $this->get_settings();

		$trailing_slash = $override_slash === false && isset($settings['add_trailing_slash']) && $settings['add_trailing_slash'] === 'y';

		$blank_pages = array(
			'url' => '',
			'uris' => array(),
			'templates' => array()
		);

		if ($cache_bust === true) {
			$sql = "SELECT site_pages FROM exp_sites WHERE site_id = $this->site_id";
			$pages_array = $this->EE->sql_helper->row($sql);
			$all_pages = unserialize(base64_decode($pages_array['site_pages']));
		} else {
			$all_pages = $this->EE->config->item('site_pages');
		}

		if (is_array($all_pages) && isset($all_pages[$this->site_id]) && is_array($all_pages[$this->site_id])) {
			$site_pages = array_merge($blank_pages, $all_pages[$this->site_id]);
		} else {
			$site_pages = $blank_pages;
		}

		if ($trailing_slash) {
			foreach ($site_pages['uris'] as $key => $uri) {
				$site_pages['uris'][$key] = Structure_Helper::remove_double_slashes($uri.'/');
			}
		} else {
			foreach ($site_pages['uris'] as $key => $uri) {
				if ($site_pages['uris'][$key] !== '/') {
					$site_pages['uris'][$key] = rtrim($uri, '/');
				}
			}
		}

		return $site_pages;
	}

	function get_page_count()
	{
		return $this->EE->db->count_all('structure') -1;

	}

	function get_channel_data($channel_id)
	{
		$sql = "SELECT * FROM exp_structure_channels
				WHERE channel_id = $channel_id
				AND site_id = $this->site_id";

		return $this->EE->sql_helper->row($sql);
	}


	/**
	 * Get Templates
	 *
	 * @return Single dimensional array of templates, ids and names
	 **/
	function get_templates()
	{
		$sql = "SELECT tg.group_name, t.template_id, t.template_name
				FROM   exp_template_groups tg, exp_templates t
				WHERE  tg.group_id = t.group_id
				AND tg.site_id = '$this->site_id'
				ORDER BY tg.group_name, t.template_name";
		$query = $this->EE->db->query($sql);
		$templates = $query->result_array();

		$settings = $this->get_settings();

		if (isset($settings['hide_hidden_templates']) && $settings['hide_hidden_templates'] == 'y')
		{
			$hidden_indicator = $this->EE->config->item('hidden_template_indicator') ? $this->EE->config->item('hidden_template_indicator') : '.';

			foreach ($templates as $key => $row)
			{
				if (substr($row['template_name'], 0, 1) == $hidden_indicator)
					unset ($templates[$key]);
			}
		}

		return $templates;
	}


	function get_listing_entry($entry_id)
	{
		$sql = "SELECT * FROM exp_structure_listings WHERE entry_id = '$entry_id' AND site_id = '$this->site_id'";
		$result = $this->EE->sql_helper->row($sql);

		if ($result !== NULL)
			return $result;

		return FALSE;
	}

	function get_listing_parent($channel_id)
	{
		// $sql = "SELECT entry_id FROM exp_structure WHERE listing_cid = '$channel_id' AND site_id = '$this->site_id'";
		$result = $this->EE->db->select('entry_id')
			->from('structure')
			->where('listing_cid', $channel_id)
			->where('site_id', $this->site_id)
			->get();

		if ($result->num_rows() > 0) {
			return $result->row()->entry_id;
		}

		return null;
	}

	function get_listing_channel($entry_id)
	{
		if (is_numeric($entry_id))
		{
			$listing_ids = $this->get_listing_entry_ids();

			if (array_key_exists($entry_id, $listing_ids))
				$entry_id = $this->get_parent_id($entry_id);

			$sql = "SELECT listing_cid FROM exp_structure
					WHERE entry_id = $entry_id
					AND site_id = $this->site_id";
			$result = $this->EE->sql_helper->row($sql);

			if ($result !== NULL && $result['listing_cid'] != '0')
				return $result['listing_cid'];
		}
		return FALSE;
	}

	function get_listing_channel_by_id($entry_id)
	{
		$result = $this->EE->db->get_where('structure', array('entry_id' => $entry_id, 'site_id' => $this->site_id));
		if ($result->num_rows() > 0)
			return $result->row()->listing_cid;

		return false;
	}

	function get_listing_channel_short_name($channel_id)
	{
		$sql = "SELECT channel_name FROM exp_channels
				WHERE channel_id = $channel_id
				AND site_id = $this->site_id";
		$result = $this->EE->sql_helper->row($sql);

		if ($result !== NULL && $result['channel_name'] != '0')
			return $result['channel_name'];

		return FALSE;
	}


	function get_cp_asset_data()
	{
		$asset_data = $this->get_structure_channels('asset', '', 'alpha');
		$split_assets = $this->get_split_assets();

		if ($asset_data === FALSE)
			return FALSE;

		$cp_asset_data = array();
		foreach ($asset_data as $channel_id => $row)
		{
			if ($row['split_assets'] == 'n')
			{
				$cp_asset_data[$row['channel_title']] = array(
					'title' => $row['channel_title'],
					'channel_id' => $channel_id,
					'split_assets' => 'n'
				);

			}
			else
			{
				foreach ($split_assets[$row['channel_id']] as $split_channel_id => $split_row)
				{
					$cp_asset_data[$split_row['title']] = array(
						'title' => $split_row['title'],
						'channel_id' => $row['channel_id'],
						'entry_id' => $split_row['entry_id'],
						'split_assets' => 'y'
					);
				}
			}
		}
		ksort($cp_asset_data);

		return $cp_asset_data;
	}


	/**
	 * Module Is Installed
	 *
	 * @return bool TRUE if installed
	 * @return bool FALSE if not installed
	 */
	function module_is_installed()
	{
		if ( ! isset($this->cache['module_id_query']))
		{
			$results = $this->EE->db->query("SELECT module_id FROM exp_modules WHERE module_name = 'Structure'");

			$this->cache['module_id_query'] = $results;
		}

	    if ($this->cache['module_id_query']->num_rows > 0)
			return TRUE;

		return FALSE;
	}


	/**
	 * Extension Is Installed
	 *
	 * @return bool TRUE if installed
	 * @return bool FALSE if not installed
	 */
	function extension_is_installed()
	{
		$results = $this->EE->db->query("SELECT * FROM exp_extensions WHERE class = 'Structure_ext' AND enabled='y'");
	    if ($results->num_rows > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Get Module ID
	 *
	 * @return numeral Module's ID
	 */
	function get_module_id()
	{
		if ( ! isset($this->cache['module_id_query']))
		{
			$results = $this->EE->db->query("SELECT module_id FROM exp_modules WHERE module_name = 'Structure'");

			$this->cache['module_id_query'] = $results;
		}

		if ($this->cache['module_id_query']->num_rows > 0)
		{
			return $this->cache['module_id_query']->row('module_id');
		}
		else
		{
			return FALSE;
		}
	}

	function is_duplicate_listing_uri($entry_id, $uri, $parent_id)
	{
		$query = $this->EE->db->get_where('structure_listings', array('uri' => $uri, 'parent_id' => $parent_id, 'entry_id !=' => $entry_id));

		if ($query->num_rows > 0)
		{
			// Let's see how many dupes there really are. -1, -2, -3 is nicer than -1, -1-1, -1-1-1
			$sql = "SELECT * FROM exp_structure_listings WHERE parent_id={$parent_id} AND uri REGEXP '^{$uri}.[0-9]'";
			$result = $this->EE->db->query($sql);

			return $result->num_rows + 1; // we're implying 1, and the regex will not factor the "root" or original url
		}

		return FALSE;
	}

	function is_duplicate_page_uri($entry_id, $uri)
	{
		$site_pages_array = $this->get_site_pages();
		$pages = $site_pages_array['uris'];

		unset($pages[$entry_id]);

		$word_separator = $this->EE->config->item('word_separator');
		$separator = $word_separator != 'dash' ? '_' : '-';

		if (in_array($uri, $pages))
		{
			$uri = rtrim($uri, '/').$separator.'1/';

			if (in_array($uri, $pages))
				$uri = rtrim($uri, '-1/').$separator.'2/';

			return $uri;
		}

		return FALSE;
	}

	public function is_valid_template($template_id)
	{
		if ( ! is_numeric($template_id))
			return FALSE;

		$result = $this->EE->db->get_where('templates', array('template_id' => $template_id));

		return ($result->num_rows() > 0);
	}

	/*
	* @param submitted_uri
	* @param default_uri
	*/
	function create_uri($uri, $url_title = '')
	{
		// if structure_uri is not entered use url_title
		$uri = $uri ? $uri : $url_title;
		// Clean it up TODO replace with EE create URL TITLE?
		$uri = preg_replace("#[^a-zA-Z0-9_\-\.]+#i", '', $uri);
		// Make sure there are no "_" underscores at the beginning or end
		return trim($uri, "_");
	}

	/*
	* @param parent_uri
	* @param page_uri/slug
	*/
	function create_page_uri($parent_uri, $page_uri = '')
	{
		$parent_uri = preg_replace("#[^a-zA-Z0-9_\-]+#i", '', $parent_uri);
		$page_uri = preg_replace("#[^a-zA-Z0-9_\-]+#i", '', $page_uri);

		// prepend the parent uri
		$uri = $parent_uri . '/' . $page_uri . '/';

		// ensure beginning and ending slash
		$uri = '/' . trim($uri, '/') . '/';

		// if double slash, reduce to one
		return str_replace('//', '/', $uri);
	}

	/*
	* @param parent_uri
	* @param listing_uri/slug
	*/
	function create_full_uri($parent_uri, $listing_uri)
	{
	    $uri = $this->create_uri($listing_uri);
	    // prepend the parent uri
	    $uri = $parent_uri . '/' . $uri;
	    // ensure beginning and ending slash
	    $uri = '/' . trim($uri, '/') . '/';
	    // if double slash, reduce to one
	    return str_replace('//', '/', $uri);
	}

	function set_site_pages($site_id, $site_pages)
	{
		if (empty($site_id))
			$site_id = $this->EE->config->item('site_id');

		$pages[$site_id] = $site_pages;

		unset($site_pages);

		$this->EE->db->query($this->EE->db->update_string('exp_sites',
		array('site_pages' => base64_encode(serialize($pages))),
		"site_id = '".$this->EE->db->escape_str($site_id)."'"));
	}


	/*
	@ param
		$data = array(
			'site_id' => $site_id,
			'entry_id' => $entry_id,
			'parent_id' => $pid,
			'channel_id' => $channel_id,
			'template_id' => $template_id,
			'uri' => $slug
		);
	*/
	function set_listing_data($data, $site_pages = FALSE)
	{
		$entry_id = $data['entry_id'];
		if ($site_pages === FALSE)
			$site_pages = $this->get_site_pages();

		// Update the entry for our listing item in site_pages
		$site_pages['uris'][$data['entry_id']] = $this->create_full_uri($data['parent_uri'], $data['uri']);
		$site_pages['templates'][$data['entry_id']] = $data['template_id'];
		$site_id = $this->EE->config->item('site_id');

		$this->set_site_pages($site_id, $site_pages);

		// Our listing table doesn't need this anymore, so remove it.
		unset($data['listing_cid']);
		unset($data['parent_uri']);
		unset($data['hidden']);

		$data['uri'] = trim($data['uri'], '/'); // Just keeping our data clean.

		// See if row exists first
		$query = $this->EE->db->get_where('structure_listings', array('entry_id' => $data['entry_id']));

		// We have an entry, so we're modifying existing data
		if($query->num_rows() == 1)
		{
			unset($data['entry_id']);
			$sql = $this->EE->db->update_string('structure_listings', $data, "entry_id = $entry_id");
		}
		else // This is a new entry
		{
			$sql = $this->EE->db->insert_string('structure_listings', $data);
		}

		// Update our listing table
		$this->EE->db->query($sql);
		$this->update_root_node();
	}

	/**
	 * Get Member Groups
	 *
	 * @return Array of member groups with access to Structure
	 */
	function get_member_groups()
	{
		$sql = "SELECT mg.group_id AS id, mg.group_title AS title
				FROM exp_member_groups AS mg
				INNER JOIN exp_module_member_groups AS modmg
				ON (mg.group_id = modmg.group_id)
				WHERE mg.can_access_cp = 'y'
					AND mg.can_access_publish = 'y'
					AND mg.can_access_edit = 'y'
					AND mg.group_id <> 1
					AND modmg.module_id = {$this->get_module_id()}
					AND mg.site_id = {$this->site_id}
				ORDER BY mg.group_id";

		$groups = $this->EE->db->query($sql);

		if ($groups->num_rows > 0)
		{
			return $groups->result_array();
		}
		else
		{
			return FALSE;
		}
	}


	/**
	 * Get Entry Title
	 *
	 * @param string $entry_id
	 * @return string Entry Title or NULL
	 */
	function get_entry_title($entry_id)
	{
		if ( ! is_numeric($entry_id))
			return NULL;

		$sql = "SELECT title FROM exp_channel_titles WHERE entry_id = $entry_id";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows > 0)
		{
			return $result->row('title');
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Get entries by channel
	 *
	 * @param int $channel_id
	 * @return $entry_ids array of entry_ids or FALSE
	 **/
	function get_entries_by_channel($channel_id)
	{
		if ( ! is_numeric($channel_id))
			return FALSE;

		$sql = "SELECT entry_id FROM exp_channel_titles WHERE channel_id = $channel_id AND site_id = $this->site_id";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows = 0)
			return FALSE;

		$entry_ids = array();
		foreach ($result->result_array() as $row)
		{
			$entry_ids[] = $row['entry_id'];
		}

		return $entry_ids;
	}

	function get_entry_titles_by_channel($channel_id)
	{
		if ( ! is_numeric($channel_id))
			return FALSE;

		$sql = "SELECT entry_id, title FROM exp_channel_titles WHERE channel_id = $channel_id AND site_id = $this->site_id ORDER BY title";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows = 0)
			return FALSE;

		$entry_ids = array();
		foreach ($result->result_array() as $row)
		{
			$entry_ids[] = array('entry_id' => $row['entry_id'], 'title' => $row['title']);
		}
		return $entry_ids;
	}

	function get_split_assets()
	{
		$sql = "SELECT channel_id FROM exp_structure_channels WHERE type = 'asset' AND split_assets = 'y'";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows > 0)
		{
			$data = array();
			foreach ($result->result_array() as $channel_id => $row)
			{
				$data[$row['channel_id']] = $this->get_entry_titles_by_channel($row['channel_id']);
			}
			return $data;
		}
		return NULL;
	}

	function get_listing_channel_data($channel_id = false)
	{

		$data = $this->EE->db->select('*')
			->from('channel_titles')
			->where('channel_id', $channel_id)->get();

		return $data->result_array();
	}

	function get_pid_for_listing_entry($entry_id)
	{
		// get entry's channel id
		$sql = "SELECT channel_id
				FROM exp_channel_data
				WHERE entry_id = $entry_id
				LIMIT 1";
		$result = $this->EE->db->query($sql);

		$lcid = $result->row('channel_id');

		if (is_array($lcid))
			return FALSE;

		// get entry's parent id
		$sql = "SELECT entry_id
				FROM exp_structure
				WHERE listing_cid = $lcid
				LIMIT 1";
		$result = $this->EE->db->query($sql);
		$pid = $result->row('entry_id');

		return $pid;
	}

	function get_channel_listing_entries($channel_id)
	{
		if ( ! is_numeric($channel_id))
			return FALSE;

		$sql = "SELECT * FROM exp_structure_listings WHERE channel_id = $channel_id AND site_id = $this->site_id";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows > 0)
		{
			$listings = array();
			foreach ($result->result_array() as $entry)
			{
				$listings[$entry['entry_id']] = $entry;
			}

			return $listings;
		}
		return FALSE;
	}

	function get_hidden_state($entry_id)
	{
		$this->EE->db->select('hidden')->from('structure')->where(array('entry_id' => $entry_id, 'site_id' => $this->site_id));
		$result = $this->EE->db->get();

		if ($result->num_rows > 0) {
			$row = $result->row();

			return $row->hidden;
		}

		return 'n';
	}

	function cleanup_check()
	{
		$vals = array();

		// Remove extraneous entries in exp_structure
		$site_pages = $this->get_site_pages();
		$keys = array_keys($site_pages['uris']);
		$entry_ids = implode(",", $keys);

		$sql = "SELECT * FROM exp_structure
				WHERE site_id = $this->site_id
				AND entry_id NOT IN ($entry_ids)";

		$query = $this->EE->db->query($sql);
 		$vals['duplicate_entries'] = $query->num_rows();

		// Duplicate Right Values
		$sql = "SELECT entry_id, rgt,
		 			COUNT(rgt) AS duplicates
				FROM exp_structure
				WHERE site_id = $this->site_id
				GROUP BY rgt
					HAVING ( COUNT(rgt) > 1 )";

		$query = $this->EE->db->query($sql);
		$vals['duplicate_rights'] = $query->num_rows();

		// Duplicate Left Values
		$sql = "SELECT entry_id, lft,
		 			COUNT(rgt) AS duplicates
				FROM exp_structure
				WHERE site_id = $this->site_id
				GROUP BY lft
					HAVING ( COUNT(lft) > 1 )";

		$query = $this->EE->db->query($sql);
		$vals['duplicate_lefts'] = $query->num_rows();

		return $vals;
	}


	/**
	 * Clean up invalid Structure data
	 **/
	function cleanup()
	{
		$vars = array();

		// Remove extraneous entries in exp_structure
		$site_pages = $this->get_site_pages();
		$keys = array_keys($site_pages['uris']);
		$entry_ids = implode(",", $keys);

		// Delete Structure entries
		$sql = "DELETE FROM exp_structure
				WHERE site_id = $this->site_id
				AND entry_id NOT IN ($entry_ids)";

		$query = $this->EE->db->query($sql);

		// Delete Listing entries
		$sql = "DELETE FROM exp_structure_listings
				WHERE site_id = $this->site_id
				AND entry_id NOT IN ($entry_ids)";

		$query = $this->EE->db->query($sql);

		// Adjust the root node's right value
		$sql = "SELECT MAX(rgt) AS max_right FROM exp_structure where site_id != 0";
		$query = $this->EE->db->query($sql);
		$max_right = $query->row('max_right') + 1;

		$sql = "UPDATE exp_structure SET rgt = $max_right WHERE site_id = 0";
		$this->EE->db->query($sql);

		return $vars;
	}

	function restore_site_pages_templates()
	{
		$site_pages = $this->get_site_pages();
		$uris = $site_pages['uris'];
		$templates = $site_pages['templates'];
		$template_defaults = $this->get_structure_channels();

		foreach ($uris as $key => $row)
		{
			if ( ! array_key_exists($key, $templates))
			{
				$sql = "SELECT channel_id FROM exp_channel_data WHERE site_id = '$this->site_id' AND entry_id = '$key'";
				$query = $this->EE->db->query($sql);
				$channel_id = $query->row()->channel_id;

				$templates[$key] = $template_defaults[$channel_id]['template_id'];
			}
		}

		$new_site_pages_array = array();
		$new_site_pages_array[$this->site_id]['uris'] = $uris;
		$new_site_pages_array[$this->site_id]['templates'] = $templates;
		$new_site_pages_array[$this->site_id]['url'] = $this->EE->functions->fetch_site_index(1, 0);
		$new_site_pages_array_insert = base64_encode(serialize($new_site_pages_array));

		$data = array('site_pages' => $new_site_pages_array_insert);
		$this->EE->db->where('site_id', $this->site_id);
		$this->EE->db->update('sites', $data);
	}

	function update_root_node()
	{
		$sql = "SELECT MAX(rgt) AS max_right FROM exp_structure where site_id != 0";
		$query = $this->EE->db->query($sql);
		$max_right = $query->row('max_right') + 1;

		$sql = "UPDATE exp_structure SET rgt = $max_right WHERE site_id = 0";
		$this->EE->db->query($sql);
	}

	function get_parent_uri_depth($uri)
	{
		if ($uri === NULL)
			return 0;

		$uri = trim($uri, '/');
		$parent_uri_array = explode('/', $uri);

		return count($parent_uri_array);
	}

	function get_uri()
	{
		$settings = $this->get_settings();
		$trailing_slash = isset($settings['add_trailing_slash']) && $settings['add_trailing_slash'] === 'y' ? '/' : null;

		$uri = preg_replace("/(\/P\d*)/", '', Structure_Helper::remove_double_slashes('/'.$this->EE->uri->uri_string().$trailing_slash));

		if ($uri == '')
		{
			$uri = '/'; # e.g. pagination segment off homepage
		}

		return $uri;
	}

	function create_site_pages_array()
	{
		$data = array();

		$sql = "SELECT node.*, (COUNT(parent.entry_id) - 1) AS depth, expt.title, expt.status, expt.url_title
				FROM exp_structure AS node
				INNER JOIN exp_structure AS parent
					ON node.lft BETWEEN parent.lft AND parent.rgt
				INNER JOIN exp_channel_titles AS expt
					ON node.entry_id = expt.entry_id
				WHERE parent.lft > 1
				AND node.site_id = {$this->site_id}
				AND parent.site_id = {$this->site_id}
				GROUP BY node.entry_id
				ORDER BY node.lft";
		$result = $this->EE->db->query($sql);

		if ($result->num_rows() > 0)
		{
			foreach ($result->result_array() as $row)
			{
				$data[$row['entry_id']] = $row;
			}
		}
		$uris = array();
		foreach ($data as $key => $row)
		{
			$uris[$key] = '/';
			if ($row['parent_id'] != 0)
			{
				if($data[$row['parent_id']]['parent_id'] != 0)
				{
					if ($data[$data[$row['parent_id']]['parent_id']]['parent_id'] != 0)
					{
						// too much recursion... will fix if need be
						// if ($data[$data[$data[$row['parent_id']]['parent_id']]['parent_id']] != 0)
						// {
						// 	$new_array[$key] .= $data[$data[$data[$data[$row['parent_id']]['parent_id']]['parent_id']]['parent_id']]['url_title'].'/';
						// }
						$uris[$key] .= $data[$data[$data[$row['parent_id']]['parent_id']]['parent_id']]['url_title'].'/';
					}
					$uris[$key] .= $data[$data[$row['parent_id']]['parent_id']]['url_title'].'/';
				}
				$uris[$key] .= $data[$row['parent_id']]['url_title'].'/';
			}
			$uris[$key] .= $row['url_title'].'/';
		}

		// Replace underscores with hyphens
		$uris = str_replace('_', '-', $uris);

		$listings = array();
		$sql = "SELECT * FROM exp_structure_listings WHERE site_id = $this->site_id";
		$results = $this->EE->db->query($sql);

		foreach ($results->result_array() as $row)
		{
			$uris[$row['entry_id']] = $uris[$row['parent_id']].$row['uri'].'/';
		}

		// Get Structure Channel Data
		$sql = "SELECT ec.channel_id, ec.channel_title, esc.template_id, esc.type, ec.site_id
				FROM exp_channels AS ec
				LEFT JOIN exp_structure_channels AS esc ON ec.channel_id = esc.channel_id
				WHERE ec.site_id = '$this->site_id'";

		$results = $this->EE->db->query($sql);


		// Format the array nicely
		$channel_data = array();
		foreach($results->result_array() as $key => $value)
		{
			$channel_data[$value['channel_id']] = $value;
			unset($channel_data[$value['channel_id']]['channel_id']);
		}

		$templates = array();

		foreach ($data as $key => $row)
		{
			$templates[$key] = $channel_data[$row['channel_id']]['template_id'];
		}

		$new_site_pages_array = array();
		$new_site_pages_array[$this->site_id]['uris'] = $uris;
		$new_site_pages_array[$this->site_id]['templates'] = $templates;
		$new_site_pages_array[$this->site_id]['url'] = $this->EE->functions->fetch_site_index(1, 0);

		$new_site_pages_array_insert = base64_encode(serialize($new_site_pages_array));

		echo $new_site_pages_array_insert;
		// die();
	}

	function get_site_id()
	{
		$site_id = is_numeric($this->EE->config->item('site_id')) ? $this->EE->config->item('site_id') : 1;
		return $site_id;
	}

	function theme_url()
	{
		if (! isset($this->cache['theme_url']))
		{
			$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';
			$this->cache['theme_url'] = $theme_folder_url.'structure/';
		}

		return $this->cache['theme_url'];
	}



	function user_access($perm, $settings = array())
	{
		$site_id = $this->EE->config->item('site_id');
		$group_id = $this->EE->session->userdata['group_id'];

		// super admins always have access
		if ($group_id == 1)
		{
			if ($perm == 'perm_delete' || $perm == 'perm_reorder')
				return 'all';

			return TRUE;
		}

		$admin_perm = 'perm_admin_structure_' . $group_id;
		$this_perm	= $perm . '_' . $group_id;

		if ($settings !== array())
		{
			if (isset($settings[$this_perm]))
				return $settings[$this_perm] == 'y' ? TRUE : $settings[$this_perm];

			return FALSE;
		}

		// settings were not passed we have to go to the DB for the check
		$result = $this->EE->db->select('var')
				->from('structure_settings')
				->where('var', $admin_perm)
				->or_where('var', $this_perm);

		if ($result->num_rows() > 0)
		{
			if ($perm == 'perm_delete' || $perm == 'perm_reorder')
				return 'all';

			return TRUE;
		}

		return FALSE;
	}
}

class structure_leaf
{
	var $row;
	var $parent;
	var $children = array();

	/**
	 * Leaf constructor
	 * @param object $row
	 * @param leaf $parent
	 */
	function __construct($row, $parent = NULL)
	{
		$this->row = $row;
		$this->parent = $parent;
	}

	/**
	 * Yep, you know how to make children right?
	 * @param leaf $leaf
	 */
	function add_child($leaf)
	{
		$leaf->parent = $this;
		if ( ! in_array($leaf, $this->children))
			$this->children[] = $leaf;
	}

	/**
	 * On the fly depth caculation
	 */
	function depth()
	{
		if (is_null($this->parent))
			return 0;
		else
			return $this->parent->depth() + 1;
	}

	/**
	 * Add the 'depth' key to the row entry
	 * for this and all of it's children
	 */
	function add_row_depth($depth = 0)
	{
		$this->row['depth'] = $depth;
		foreach ($this->children as $child)
			$child->add_row_depth($depth + 1);
	}

	/**
	 * Prune all leaves past the specified depth
	 * @param integer $depth 0 = kill children, 1 = kill grandchildren etc...
	 */
	function prune_children($depth = 0)
	{
		if ($depth <= 0)
			$this->children = array();
		else
			foreach ($this->children as $child)
				$child->prune_children($depth - 1);
	}

	/**
	 * Prune tree based on provided params
	 * @param string key Key of the row data
	 * @param array $values List of values
	 * @param boolean $exclude Exclude = FALSE and non matching items are deleted, Exclude = TRUE and matching items are deleted
	 */
	function selective_prune($key, $values, $exclude = FALSE)
	{
		foreach ($this->children as $id => $child)
		{
			if ($is_val = $child->is_of_value($key, $values, $exclude))
			{
				unset($this->children[$id]);
				continue;
			}
			if ($exclude || !$is_val)
				$child->selective_prune($key, $values, $exclude);
		}
	}

	/**
	 * Prune tree based on provided params
	 * @param string key Key of the row data
	 * @param array $values List of values
	 * @param boolean $exclude Exclude = FALSE and non matching items are deleted, Exclude = TRUE and matching items are deleted
	 */
	function selective_prune_alt($key, $values, $exclude = FALSE)
	{
		foreach ($this->children as $id => $child)
		{
			if ($is_val = $child->is_of_value($key, $values, $exclude))
			{
				unset($this->children[$id]);
				continue;
			}
		}
	}


	/**
	 * Does this leaf match the provided values
	 * @param string key Key of the row data
	 * @param array $values List of values
	 * @param boolean $exclude Exclude = FALSE non-matching items are deleted, Exclude = TRUE and matching items are deleted
	 */
	function is_of_value($key, $values, $exclude = FALSE)
	{
		$is_of_value = in_array($this->row[$key], $values);
		// echo $this->row['title'].' of '.$key.' '.$this->row[$key].'<br>';
		return ($is_of_value && $exclude) || (! $is_of_value && ! $exclude);
	}

	/**
	 * Find the leaf with the specified row data
	 * @param string $key Key of the row data
	 * @param seting $data Data to match to row[$key]
	 */
	function find_ancestor($key, $data)
	{
		if (array_key_exists($key, $this->row) && $this->row[$key] == $data)
			return $this;

		foreach ($this->children as $child)
		{
			$found = $child->find_ancestor($key, $data);
			if ($found !== FALSE)
				return $found;
		}
		return FALSE;
	}

	/**
	 * Determins if the provided leaf is in this this branch
	 * @param leaf $leaf Possible child leaf
	 */
	function has_ancestor($leaf, $compare_on='entry_id')
	{
		if ( ! array_key_exists($compare_on, $leaf->row))
			return FALSE;

		if (array_key_exists($compare_on, $this->row) && $leaf->row[$compare_on] == $this->row[$compare_on])
			return TRUE;

		foreach ($this->children as $child)
		{
			$found = $child->has_ancestor($leaf);
			if ($found)
				return TRUE;
		}
		return FALSE;
	}

	/**
	 * Cut this branch off at the specified depth
	 * Note: will change the calculated depth for all items which remain on this branch
	 * @param integer $height 0 = kill parent, 1 = kill grandparent etc..
	 */
	function prune_ancestors($height = 0)
	{
		if ($height <= 0)
			$this->parent = NULL;
		elseif ( ! is_null($this->parent))
			$this->parent->prune_ancestors($height - 1);
	}

	/**
	 * Cut off all leaves that are not children or parents of this item
	 * This includes siblings, cousins etc
	 */
	function purify_bloodline()
	{
		$this->prune_nephews();

		if ( ! is_null($this->parent))
			$this->parent->purify_bloodline();
	}
	/**
	 * Remove all niece/nephew leaves
	 */
	function prune_nephews()
	{
		if ( ! is_null($this->parent))
		{
			foreach ($this->parent->children as $brother)
			{
				if ($brother !== $this)
					$brother->prune_children();
			}
		}
	}

	/**
	 * Get all the rows as a results array
	 */
	function get_results()
	{
		$results = array();
		foreach ($this->children as $child)
		{
			$results[] = $child->row;
			$results = array_merge($results, $child->get_results($results));
		}
		return $results;
	}

	/**
	 * Text printout of the tree
	 * @param string $inset
	 */
	function print_branch($inset = '')
	{
		echo '('.$this->row['entry_id'].') '.$inset.$this->row['title']."\n";
		$inset .= ' ';
		foreach ($this->children as $child)
			$child->print_branch($inset);
	}

	/**
	 * HTML nexted ul of the tree
	 */
	function list_branch($inset = '')
	{
		if ($inset == '')
			echo '<ul>';
		echo '('.$this->row['entry_id'].') '.$this->row['title'];
		if (count($this->children))
		{
			echo "<ul>\n";
			$inset .= '	';
			foreach ($this->children as $child)
			{
				echo $inset.'<li>';
				$child->list_branch($inset);
				echo "</li>\n";
			}
			echo substr($inset, 1)."</ul>\n";
		}
		if ($inset == '')
			echo "\n</ul>\n";
	}

	/**
	 * Converts the nested set results into an tree
	 * @param array $results
	 */
	static function build_from_results($results)
	{
		// !assumes first row is root
		$row = array_shift($results);
		$tree = new structure_leaf($row);
		$leaf = $tree;
		$lft = $leaf->row['lft'];
		$rgt = $leaf->row['rgt'];
		foreach ($results as $row)
		{
			$new = new structure_leaf($row);

			if ($row['lft'] < $rgt)
			{
				$leaf->add_child($new);
			}
			else
			{
				while ( ! is_null($leaf->parent) && $row['lft'] > $leaf->row['rgt'])
					$leaf = $leaf->parent;

				$leaf->add_child($new);
			}

			if ($row['rgt'] - $row['lft'] > 1)
			{
				// only change leaf if new leaf can hold sub items
				$leaf = $new;
				$lft = $leaf->row['lft'];
				$rgt = $leaf->row['rgt'];
			}
		}
		return $tree;
	}
}
