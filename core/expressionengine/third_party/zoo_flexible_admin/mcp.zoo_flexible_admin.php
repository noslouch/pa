<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//error_reporting(1);
//ini_set('display_errors', TRUE);

/**
 * Control Panel for Zoo Flexible Admin
 *
 * This file must be in your /system/third_party/zoo_flexible_admin directory of your ExpressionEngine installation
 *
 * @package             Zoo Flexible Admin for EE2
 * @author              Nico De Gols (nico@ee-zoo.com)
 * @copyright            Copyright (c) Nico De Gols
 * @link                http://www.ee-zoo.com
 */

class Zoo_flexible_admin_mcp
{

	function Zoo_flexible_admin_mcp()
	{
		$this->EE =& get_instance();

		require('mod.zoo_flexible_admin.php');

		$this->EE->flexible_admin = new Zoo_flexible_admin;

	}

	function index()
	{


		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		//$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('zoo_flexible_admin_module_name'));

		$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url') . 'third_party/';
		$theme_url        = $theme_folder_url . 'zoo_flexible_admin';

		$cp_url = $this->EE->config->item('site_url');

		$ACT_script_path = str_replace("?", "", $this->EE->functions->fetch_site_index());

		$js_url = '?D=cp&amp;C=javascript&amp;M=load&amp;package=zoo_flexible_admin&amp;file=';

		$this->EE->cp->add_to_head("<link rel='stylesheet' href='{$theme_url}/css/ui.tree.css'>");

		$this->EE->load->add_package_path(PATH_THIRD . 'zoo_flexible_admin/');

		$js = array('jquery-1.3.2.min', 'ui.core', 'effects.core', 'effects.blind', 'effects.blind', 'ui.draggable', 'ui.droppable', 'ui.tree', 'cpnav');

		foreach ($js as $file) {
			$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $theme_url . '/javascript/' . $file . '.js"></script>');
		}


		$this->EE->cp->add_js_script(array(
				'plugin'    => array('toolbox.expose', 'overlay')
			)
		);

		$this->EE->cp->add_js_script(array(
				'package'    => array('toolbox.expose', 'overlay')
			)
		);

		$data = array();

		$results   = $this->EE->db->query("SELECT module_id FROM " . $this->EE->db->dbprefix('modules') . " WHERE module_name = 'Zoo_flexible_admin'");
		$module_id = $results->row('module_id');


		$data['action_url']  = 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=zoo_flexible_admin&action=update';
		$data['attributes']  = array('class' => 'form',
		                             'id'    => 'cpnavform');
		$data['preview_url'] = '?C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=zoo_flexible_admin&action=preview';
		$data['form_hidden'] = NULL;

		$data["groups"] = $this->get_member_groups();

		$data["navhtml"] = "";

		$parts       = explode('/', $_SERVER["SCRIPT_NAME"]);
		$script_name = './' . $parts[count($parts) - 1];

		$script_name = str_replace('&amp;', '&', BASE);

		//$script_name = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];

		$this->EE->cp->add_to_head('
		<script type="text/javascript">
			var cpnav_settings = {
				"ajax_preview": ' . $this->EE->cp->fetch_action_id('Zoo_flexible_admin', 'ajax_preview') . ',
				"ajax_preview_method": "' . $script_name . '&C=addons_modules&M=show_module_cp&module=zoo_flexible_admin&method=action&func=ajax_preview",
				"ajax_load_tree": ' . $this->EE->cp->fetch_action_id('Zoo_flexible_admin', 'ajax_load_tree') . ',
				"ajax_load_tree_method": "' . $script_name . '&C=addons_modules&M=show_module_cp&module=zoo_flexible_admin&method=action&func=ajax_load_tree",
				"ajax_load_settings": ' . $this->EE->cp->fetch_action_id('Zoo_flexible_admin', 'ajax_load_settings') . ',
				"ajax_load_settings_method": "' . $script_name . '&C=addons_modules&M=show_module_cp&module=zoo_flexible_admin&method=action&func=ajax_load_settings",
				"ajax_save": ' . $this->EE->cp->fetch_action_id('Zoo_flexible_admin', 'ajax_save_tree') . ',
				"ajax_save_method": "' . $script_name . '&C=addons_modules&M=show_module_cp&module=zoo_flexible_admin&method=action&func=ajax_save_tree",
				"ajax_copy": ' . $this->EE->cp->fetch_action_id('Zoo_flexible_admin', 'ajax_copy_tree') . ',
				"ajax_copy_method": "' . $script_name . '&C=addons_modules&M=show_module_cp&module=zoo_flexible_admin&method=action&func=ajax_copy_tree",
				"ajax_remove": ' . $this->EE->cp->fetch_action_id('Zoo_flexible_admin', 'ajax_remove_tree') . ',
				"ajax_remove_method": "' . $script_name . '&C=addons_modules&M=show_module_cp&module=zoo_flexible_admin&method=action&func=ajax_remove_tree",
				"site_url": "' . $this->EE->config->item('site_url') . '",
				"site_id": "' . $this->EE->config->item('site_id') . '",
				"act_script_path": "' . $ACT_script_path . '",
				"first_group": "' . key($data["groups"]) . '",
				"lang_help": "' . $this->EE->lang->line("nav_help") . '",
				"modules" : \'' . $this->generate_zfa_json($this->get_modules(), TRUE) . '\',
				"br_pages" : \'' . $this->generate_zfa_json($this->getBR(), TRUE) . '\',
				"module_menu_name" : \'' . $this->EE->lang->line('nav_modules') . '\',
				"content_menu_name" : \'' . $this->EE->lang->line('nav_content') . '\',
				"edit_menu_name" : \'' . $this->EE->lang->line('nav_edit') . '\',
				"publish_menu_name" : \'' . $this->EE->lang->line('nav_publish') . '\',
				"edit_channels" : \'' . $this->generate_zfa_json($this->get_edit_channels(), TRUE) . '\',
				"channel_edit_menu_name" : \'' . $this->EE->lang->line('nav_edit') . '\'
				};
				
		</script>');


		return $this->EE->load->view('index', $data, TRUE);


	}

	function generate_zfa_json($data)
	{

		if (version_compare(APP_VER, 2.6, '<')) {
			$json = $this->EE->javascript->generate_json($data, TRUE);
		} else {
			$json = json_encode($data);
		}

		return $json;
	}

	function action()
	{

		$this->send_ajax_response(call_user_func(array($this->EE->flexible_admin, $_GET['func'])));

	}

	function send_ajax_response($msg, $error = FALSE)
	{
		$this->EE->output->enable_profiler(FALSE);

		@header('Content-Type: text/html; charset=UTF-8');

		exit($msg);
	}

	function get_modules()
	{

		$query = $this->EE->db->query('SELECT module_name FROM exp_modules WHERE has_cp_backend = "y" ORDER BY module_name');

		$modules = array();

		if ($query->num_rows()) {
			foreach ($query->result_array() as $row) {
				$class = strtolower($row['module_name']);
				$this->EE->lang->loadfile($class);
				$name      = htmlspecialchars($this->EE->lang->line($class . '_module_name'), ENT_QUOTES);
				$url       = BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=' . $class;
				$modules[] = array($name, $url, $class);
			}
		}

		return $modules;
	}

	function getBR()
	{

		$methods = array(""         => "Dashboard",
		                 "customer" => "Customers",
		                 "order"    => "Orders",
		                 "product"  => "Products",
		                 "promo"    => "Promotions",
		                 "report"   => "Reports",
		                 "config"   => "Settings");

		$br = array();

		foreach ($methods as $m => $title) {
			$name = htmlspecialchars($title, ENT_QUOTES);
			$page = ($m != "") ? '&method=' . $m : "";
			$url  = BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=brilliant_retail' . $page;
			$br[] = array($name, $url);
		}

		return $br;

	}

	function get_edit_channels()
	{

		$this->EE->load->model('channel_model');

		$channel_data = $this->EE->channel_model->get_channels();

		$channels = array();

		if ($channel_data) {
			foreach ($channel_data->result() as $channel) {

				$url  = BASE . AMP . 'C=content_edit' . AMP . 'channel_id=' . $channel->channel_id;
				$name = htmlspecialchars($channel->channel_title, ENT_QUOTES);

				$channels[] = array($name, $url);
			}
		}

		return $channels;
	}

	function get_member_groups()
	{
		$groups  = array();
		$site_id = $this->EE->config->item('site_id');
		$sql     = "SELECT memgroup.group_id AS id, memgroup.group_title AS title
				FROM exp_member_groups AS memgroup
				WHERE memgroup.can_access_cp = 'y' 
					AND memgroup.group_id <> 0 
					AND memgroup.site_id = $site_id
				GROUP BY memgroup.group_id 
				ORDER BY memgroup.group_id";

		$groupsdb = $this->EE->db->query($sql)->result_array();
		if (empty($groupsdb)) {
			$groups = false;
		} else {
			foreach ($groupsdb as $row) {
				$groups[$row["id"]] = $row["title"];
			}
		}
		return $groups;
	}


	function startpage()
	{
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		$data           = array();
		$data["groups"] = $this->get_member_groups();

		return $this->EE->load->view('startpage', $data, TRUE);
	}

}
// END CLASS

/* End of file mcp.zoo_flexible_admin.php */
/* Location: ./system/expressionengine/third_party/modules/zoo_flexible_admin/mcp.zoo_flexible_admin.php */