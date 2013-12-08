<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Module file for Zoo Flexible Admin
 *
 * This file must be in your /system/third_party/zoo_flexible_admin directory of your ExpressionEngine installation
 *
 * @package             Zoo Flexible Admin for EE2
 * @author              Nico De Gols (nico@ee-zoo.com)
 * @copyright			Copyright (c) Nico De Gols
 * @link                http://www.www.ee-zoo.com
 */

class Zoo_flexible_admin
{

	function Zoo_flexible_admin()
	{
		$this->EE =& get_instance();
		$this->EE->output->enable_profiler(FALSE);

		$this->EE->load->library('Services_json');

	}

	function ajax_preview()
	{
		$this->EE->output->enable_profiler(FALSE);
		echo $this->get_tree_html($this->EE->input->post('jsontree'));
	}

	function my_strip_tags($str)
	{
		$str  = strip_tags($str, "<img>");
		$pos1 = strpos($str, '<img');
		$pos1 = (!$pos1) ? strpos($str, '<IMG') : $pos1;
		$pos2 = strpos($str, '>', $pos1);
		if ($pos1 && $pos2) {
			$cp_theme     = (!$this->EE->session->userdata('cp_theme')) ? $this->EE->config->item('cp_theme') : $this->EE->session->userdata('cp_theme');
			$cp_theme_url = $this->EE->config->slash_item('theme_folder_url') . 'cp_themes/' . $cp_theme . '/';

			$img = '<img src=' . $cp_theme_url . 'images/home_icon.png />';
			$str = substr_replace($str, $img, $pos1, $pos2 - $pos1 + 1);
		}
		return $str;
	}

	function get_tree_ul($child)
	{
		$navhtml = "";
		if (isset($child->children) && count($child->children) > 0) {
			$navhtml .= '<ul class="">';
			foreach ($child->children as $subchild) {
				$id        = (isset($subchild->id)) ? $subchild->id : '';
				$className = (isset($subchild->className)) ? $subchild->className : '';
				$url       = (isset($subchild->url)) ? $subchild->url : '';
				$title     = (isset($subchild->title)) ? $subchild->title : '';

				$navhtml .= '<li class="' . $className . '" id="' . $id . '"><a href="' . $url . '">' . $title . '</a>';
				$navhtml .= $this->get_tree_ul($subchild);
				$navhtml .= '</li>';
			}
			$navhtml .= '</ul>';
		}
		return $navhtml;
	}

	function get_tree_html($jsontree)
	{

		$this->EE->output->enable_profiler(FALSE);
		$cpurl = $this->EE->config->item('cp_url');

		$pieces      = explode("/", $cpurl);
		$cpurl_index = $pieces[count($pieces) - 1];

		$jsontree = str_replace("'", '"', (string)$jsontree);

		$jsontree = $this->my_strip_tags($jsontree);


		//$jsontree = str_replace('EDIT', '', $jsontree);
		$jsontree = str_replace('DELETE', '', $jsontree);
		$jsontree = str_replace('&nbsp;', '', $jsontree);

		$json = new Services_JSON();

		$json_decoded = $json->decode($jsontree);

		$navhtml = '';
		foreach ($json_decoded as $item) {
			$id        = (isset($item->id)) ? $item->id : '';
			$className = (isset($item->className)) ? $item->className : '';
			$navhtml .= '<li class="' . $className . '"  id="' . $id . '" ><a href="' . $item->url . '" class="first_level">' . str_replace("http://", "", str_replace($_SERVER['HTTP_HOST'], "", str_replace('"', '\"', $item->title))) . '</a>';
			$navhtml .= $this->get_tree_ul($item);
			$navhtml .= '</li>';
		}

		$navhtml = str_replace('[IMG]', '<img src="', $navhtml);
		$navhtml = str_replace('[/IMG]', '"/>', $navhtml);


		return $navhtml;
	}

	function ajax_load_tree()
	{
		$this->EE->output->enable_profiler(FALSE);

		$site_id = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$this->EE->db->select('nav');
		$this->EE->db->where('group_id', $this->EE->input->post("group_id"));
		$this->EE->db->where('site_id', $site_id);
		$this->EE->db->from($this->EE->db->dbprefix('zoo_flexible_admin_menus'));

		$query = $this->EE->db->get();

		if ($query->num_rows() == 0) {
			return "no_nav_found";
		} else {
			//echo strpos($query->row()->nav, "'");
			echo str_replace("'", '"', $query->row()->nav);
		}


		//XID needs to be restored, otherwise security check fails
		if (version_compare(APP_VER, 2.7, '>=')) {
			$this->EE->security->restore_xid();
		}


	}

	function ajax_load_settings()
	{
		$this->EE->output->enable_profiler(FALSE);

		//XID needs to be restored, otherwise security check fails
		if (version_compare(APP_VER, 2.7, '>=')) {
			$this->EE->security->restore_xid();
		}

		$site_id = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$this->EE->db->select('autopopulate, startpage, hide_sidebar');
		$this->EE->db->where('group_id', $this->EE->input->post("group_id"));
		$this->EE->db->where('site_id', $site_id);
		$this->EE->db->from($this->EE->db->dbprefix('zoo_flexible_admin_menus'));

		$query = $this->EE->db->get();

		if ($query->num_rows() == 0) {
			echo '{ "autopopulate" : "false", "startpage" : "", "hide_sidebar" : "0" }';
		} else {

			echo '{ "autopopulate" : "' . $query->row()->autopopulate . '", "startpage" : "' . $query->row()->startpage . '", "hide_sidebar" : "' . $query->row()->hide_sidebar . '"  }';
		}
	}

	function ajax_save_tree()
	{
		$this->EE->output->enable_profiler(FALSE);

		$new = TRUE;

		$site_id      = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$group_id     = $this->EE->input->post('group_id');
		$startpage    = $this->EE->input->post('startpage');
		$hide_sidebar = $this->EE->input->post('hide_sidebar');
		$autopopulate = $this->EE->input->post('autopopulate');
		$nav_content  = $this->get_tree_html($this->EE->input->post('jsontree'));

		//do not replace sessions id's in 2.6.0
		//$nav_content = str_replace('S=' . $this->EE->session->userdata("session_id") . '&', "", $nav_content);

		if ($group_id == '' || $nav_content == '') {
			echo "cannot_save";
		} else {

			$results = $this->EE->db->query("SELECT * FROM " . $this->EE->db->dbprefix('zoo_flexible_admin_menus') . " WHERE group_id = '" . $group_id . "' AND site_id = '" . $site_id . "'");

			if ($results->num_rows() == 0) {
				$data = array(
					'site_id'              => $site_id,
					'group_id'             => $group_id,
					'nav'                  => $nav_content,
					'autopopulate'         => $autopopulate,
					'startpage'			=> $startpage,
					'hide_sidebar'         => $hide_sidebar
				);

				$this->EE->db->query($this->EE->db->insert_string($this->EE->db->dbprefix('zoo_flexible_admin_menus'), $data));

				$cp_message = 'added';
			}
			else
			{
				$data = array(
					'nav'                  => $nav_content,
					'autopopulate'         => $autopopulate,
					'startpage'			=> $startpage,
					'hide_sidebar'         => $hide_sidebar
				);

				$where = array(
					'group_id'		 => $group_id,
					'site_id'          => $site_id
				);

				$this->EE->db->query($this->EE->db->update_string($this->EE->db->dbprefix('zoo_flexible_admin_menus'), $data, $where));
				$cp_message = "updated";
			}
			//if ($this->EE->db->affected_rows() > 0) {
			return $cp_message;
			//}else{
			//	echo "no_affected_rows";
			//}
		}

	}

	function ajax_remove_tree()
	{
		$this->EE->output->enable_profiler(FALSE);

		$site_id  = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$group_id = $this->EE->input->post('group_id');

		$this->EE->db->where('site_id', $site_id);
		$this->EE->db->where('group_id', $group_id);
		$this->EE->db->delete($this->EE->db->dbprefix('zoo_flexible_admin_menus'));

		if ($this->EE->db->affected_rows() > 0) {
			echo "removed";
		} else {
			echo "no_affected_rows";
		}
	}

	function ajax_copy_tree()
	{
		$this->EE->output->enable_profiler(FALSE);

		$site_id         = ($this->EE->input->post("site_id") != "") ? $this->EE->input->post("site_id") : $this->EE->config->item('site_id');
		$source_group_id = $this->EE->input->post('source_group_id');
		$target_group_id = $this->EE->input->post('target_group_id');
		$startpage       = $this->EE->input->post('startpage');
		$hide_sidebar    = $this->EE->input->post('hide_sidebar');
		$autopopulate    = $this->EE->input->post('autopopulate');

		$overwrite = ($this->EE->input->post('overwrite') == "true") ? true : false;

		if (!$overwrite) {
			//check if target tree already exists
			$this->EE->db->select('nav');
			$this->EE->db->where('group_id', $target_group_id);
			$this->EE->db->where('site_id', $site_id);
			$this->EE->db->from($this->EE->db->dbprefix('zoo_flexible_admin_menus'));

			$querycheck = $this->EE->db->get();

			if ($querycheck->num_rows() > 0) {

				echo "exists";

			} else {

				echo "does not exist";
			}

		}

		if ($overwrite) {
			$this->EE->db->select('nav');
			$this->EE->db->where('group_id', $source_group_id);
			$this->EE->db->where('site_id', $site_id);
			$this->EE->db->from($this->EE->db->dbprefix('zoo_flexible_admin_menus'));

			$query = $this->EE->db->get();

			if ($query->num_rows() == 0) {

				echo "no_nav_found";

			} else {

				$nav_content = $query->row()->nav;

				$results = $this->EE->db->query("SELECT * FROM " . $this->EE->db->dbprefix('zoo_flexible_admin_menus') . " WHERE group_id = '" . $target_group_id . "' AND site_id = '" . $site_id . "'");

				if ($results->num_rows() == 0) {
					$data = array(
						'site_id'              => $site_id,
						'group_id'             => $target_group_id,
						'nav'                  => $nav_content,
						'autopopulate'         => $autopopulate,
						'startpage'			=> $startpage,
						'hide_sidebar'         => $hide_sidebar
					);

					$this->EE->db->query($this->EE->db->insert_string($this->EE->db->dbprefix('zoo_flexible_admin_menus'), $data));

				}
				else
				{
					$data = array(
						'nav'                  => $nav_content,
						'autopopulate'         => $autopopulate,
						'startpage'			=> $startpage,
						'hide_sidebar'         => $hide_sidebar
					);

					$where = array(
						'group_id'		 => $target_group_id,
						'site_id'          => $site_id
					);

					$this->EE->db->query($this->EE->db->update_string($this->EE->db->dbprefix('zoo_flexible_admin_menus'), $data, $where));

				}

				if ($this->EE->db->affected_rows() > 0) {
					echo "copied";
				} else {
					echo "copied"; //"no_affected_rows";
				}
			}
		}
	}

}

/* End of file mod.zoo_flexible_admin.php */
/* Location: ./system/expressionengine/third_party/Cpnav/mod.zoo_flexible_admin.php */