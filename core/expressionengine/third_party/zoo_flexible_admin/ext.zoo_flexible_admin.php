<?php if (!defined('APP_VER')) exit('No direct script access allowed');


class Zoo_flexible_admin_ext
{

	var $name = 'Zoo Flexible Admin';
	var $version = '1.79';
	var $description = '';
	var $settings_exist = 'n';
	var $docs_url = '';

	/**
	 * Class Constructor
	 */
	function Zoo_flexible_admin_ext($settings = array())
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();

		//grab the last_call here, before loading the session class
		//the session class has to be loaded because we need the userdata
		//since 2.6.0 the session class is not yet available in the cp_js_end hook
		$this->incoming = '';
		if ($this->EE->extensions->last_call == TRUE && $this->EE->extensions->last_call != '') {
			$this->incoming = $this->EE->extensions->last_call;
		}

		if (version_compare(APP_VER, 2.6, '>=')) {
			$this->EE->load->helper('compat');
			$this->EE->load->library(array('localize', 'remember', 'session'));
			$this->EE->extensions->last_call = $this->incoming;
		}

		$this->settings = $settings;
	}

	// --------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	function activate_extension()
	{
		// -------------------------------------------
		//  Add the extension hooks
		// -------------------------------------------

		$hooks = array(
			'cp_css_end',
			'cp_js_end',
			'sessions_end'
		);

		foreach ($hooks as $hook) {
			$this->EE->db->insert('extensions', array(
				'class'    => get_class($this),
				'method'   => $hook,
				'hook'     => $hook,
				'settings' => '',
				'priority' => 1,
				'version'  => $this->version,
				'enabled'  => 'y'
			));
		}
	}

	/**
	 * Update Extension
	 */
	function update_extension($current = '')
	{

		if ($current == '' OR $current == $this->version) {
			return FALSE;
		}

		if ($current < 1.3) {

			$data = array(
				'priority' => 1,
				'version'  => $this->version
			);

			$this->EE->db->where('class', get_class($this));
			$this->EE->db->update('extensions', $data);
		}
		return TRUE;
	}

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		// -------------------------------------------
		//  Delete the extension hooks
		// -------------------------------------------

		$this->EE->db->where('class', get_class($this))
			->delete('exp_extensions');
	}


	function get_css()
	{

		$this->EE->output->enable_profiler(FALSE);

		$css = '#navigationTabs li.parent:hover ul,
		#navigationTabs li.parent:hover li:hover ul,
		#navigationTabs li.parent:hover li:hover ul li:hover ul,
		#navigationTabs li.parent:hover li:hover ul li:hover ul li:hover ul{
		    display:block;
		}
		
		#navigationTabs li.parent:hover ul ul,
		#navigationTabs li.parent:hover li:hover ul ul,
		#navigationTabs li.parent:hover li:hover ul li:hover ul ul{
		    display: none;
		}';


		$this->site_id  = $this->EE->config->item('site_id');
		$this->group_id = $this->EE->session->userdata('group_id');
		$zoo_nav        = $this->_loadNavigation($this->site_id, $this->group_id);
		if ($zoo_nav) {
			$css .= '
					/*#mainMenu {height:18px;}*/
					#navigationTabs > li{display:none}
					#navigationTabs > li#developer_acc{display:block;}
					#navigationTabs > li.msm_sites{display:block}
					#breadCrumb li{ display:none}
					/*#breadCrumb{height:30px;}*/';
		}

		return $css;
	}

	function get_js()
	{

		$this->EE->output->enable_profiler(FALSE);

		$js = '';

		$this->site_id  = $this->EE->config->item('site_id');
		$this->group_id = $this->EE->session->userdata('group_id');
		$zoo_nav        = $this->_loadNavigation($this->site_id, $this->group_id);

		if ($zoo_nav) {
			$this->nav          = $zoo_nav[0];
			$this->autopopulate = $zoo_nav[1];
			$this->origArray    = $this->getOriginalArray($this->nav);

			if ($this->nav != "") {

				$s = $this->getSessionSegment();

				// Fetch Zenbu Module for a particular user
				$this->EE->db->select('modules.module_name');
				$this->EE->db->from('modules, module_member_groups');
				$this->EE->db->where('module_member_groups.group_id', $this->EE->session->userdata('group_id'));
				$this->EE->db->where('modules.module_id = ' . $this->EE->db->dbprefix('module_member_groups') . '.module_id', NULL, FALSE);
				$this->EE->db->where('modules.module_name', 'Zenbu');

				$query = $this->EE->db->get();

				if ($query->num_rows() == 0) {
					$this->zenbu_installed = 0;
				} else {
					$this->zenbu_installed = 1;
				}

				$toggle = ($this->group_id == 1) ? '$("#navigationTabs").append("<li><a class=\'first_level\' id=\'toggle_cpnav\' href=\'#\'>Hide custom menu</a></li>"); $("#toggle_cpnav").click(function() { $("#navigationTabs").html(jQuery.data(document.body, "originalnav")); $("#navigationTabs >li").show();  reloadMenu(); });' : '';

				$js .= '
					if($("#origtree")){
						$("#origtree").html($("#navigationTabs").html());
					}

					jQuery.data(document.body, "originalnav", $("#navigationTabs").html());
					ndg_msm_sites = $("#navigationTabs").find(".msm_sites").eq(0);
					par = ""; if(ndg_msm_sites.find("ul").length > 0){ par = \'parent\';}
					$("#navigationTabs").html(\'' . $this->nav . '<li class="\'+par+\' msm_sites" style="display:list-item;">\'+ndg_msm_sites.html()+\'</li>\');
					' . $toggle . '
					// $("#navigationTabs").append(ndg_msm_sites);


					$("#navigationTabs #modulefolder > a:first-child").attr("href", "' . $_SERVER["SCRIPT_NAME"] . '?S=' . $s . '&D=cp&C=addons_modules");
					$("#navigationTabs #modulefolder > a:first-child").click(function(){ window.location.href = $(this).attr("href"); return true; });

					if(' . $this->autopopulate . ' == 1){

						if($("#navigationTabs #publishfolder > ul").length == 0){ $("#navigationTabs #publishfolder").append(\'<ul></ul>\'); $("#navigationTabs #publishfolder").addClass(\'parent\')};
						$("#navigationTabs #editfolder > ul").html(\'' . $this->get_edit_channels() . '\');
						$("#navigationTabs #publishfolder > ul").html(\'' . $this->get_publish_channels() . '\');
						$("#navigationTabs #modulefolder > ul").html(\'' . $this->get_modules() . '\');

					}

					//ZENBU?
					if(' . $this->zenbu_installed . ' == 1){

						$("#breadCrumb").children("ol").children("li").eq(2).show();
						$.each($("a[href*=\"C=content_edit\"]"), function() {
							$(this).attr("href", $(this).attr("href").replace(/content_edit/, "addons_modules&M=show_module_cp&module=zenbu"));
						});
					}
					$("#navigationTabs > li").show();

					//CHANGE BREADCRUMBS

					var link = location.href;

					pos = link.indexOf("D=cp");
					link = link.substr(pos+5);

					var foundin = $("#navigationTabs").find("a[href$=\'"+link+"\']").slice(0,1);

					var current = foundin;

					if(foundin.length > 0){

						var bc = "";

						var p = foundin.parent().closest(".parent").find("a").slice(0,1);

						var fa_parent = foundin.closest(".parent")

						bc ="<li><a href=\"' . $_SERVER["SCRIPT_NAME"] . '?S=' . $s . '&D=cp&C=homepage\">' . $this->EE->config->item('site_name') . '</a></li>";

						if(parent.length > 0){
							var pp = fa_parent.parent().closest(".parent").find("a").slice(0,1);
							if(pp.length > 0){
								bc += "<li>"+pp.html()+"</li>"
							}
						}


						if(p.length > 0){ bc += "<li>"+p.html()+"</li>" }
						bc += "<li class=\"last\">"+current.html()+"</li>";


						$("#breadCrumb ol").html(bc);

						ajaxindicator = $(".contents H2:first span");

						h2children = "";
						if($(".contents H2:first").children().length > 0){
							h2children = $(".contents H2:first").children();
						}
						$(".contents H2:first").text(current.html()).append(ajaxindicator).append(h2children);

					}
					//$("#breadCrumb ol").show();
					$("#breadCrumb li").show();
					$("#breadCrumb").show();


					function reloadMenu(){
					var b=jQuery;EE.navigation={};var e=b("#navigationTabs"),d=b("#navigationTabs>li.parent"),k,l,c=!1;EE.navigation.delay_show_next=function(){window.clearTimeout(k);c=!0;k=window.setTimeout(function(){var a=b(l);a.parent().find(".active, .hover").removeClass("active").removeClass("hover");a.addClass("active").addClass("hover");a.closest("#navigationTabs > li").is(d.first())||EE.navigation.truncate_menus(a.children("ul"));c=!1},100)};EE.navigation.mouse_listen=function(){e.mouseleave(function(){e.find(".active").removeClass("active")});
d.mouseenter(function(){e.find(".active").length&&(e.find(".active").removeClass("active"),b(this).addClass("active"))});d.find("a.first_level").click(function(){var a=b(this).parent();a.hasClass("active")?a.removeClass("active"):a.addClass("active");return!1});d.find("ul li").hover(function(){l=this;c||EE.navigation.delay_show_next()},function(){b(this).removeClass("hover");c||EE.navigation.untruncate_menus(b(this).children("ul"))}).find(".parent>a").click(function(){return!1})};EE.navigation.truncate_menus=
function(a){var e=b(window).height();b.each(a,function(a,d){var g=b(this),f=g.offset().top,c=g.height(),h=g.find("li:first").height(),f=f+c-e,c=g.find("> li:has(> a[href*=tgpref]):first:visible");0<f?(h=Math.ceil(f/h)+2,f=g.find("> li.nav_divider:first:visible").prev().index(),g.find("> li:visible").slice(f-h,f).hide()):c.hide()})};EE.navigation.untruncate_menus=function(a){b.each(a,function(c,e){var d=b(this);d.is(":visible")?setTimeout(function(){EE.navigation.untruncate_menus(a)},15):d.find("> li:hidden").show()})};
EE.navigation.mouse_listen()
					}';

//				var c=jQuery;EE.navigation={};var d=c("#navigationTabs"),e=c("#navigationTabs>li.parent"),i,j,g=!1;EE.navigation.delay_show_next=function(){window.clearTimeout(i);g=!0;i=window.setTimeout(function(){var a=c(j);a.parent().find(".active, .hover").removeClass("active").removeClass("hover");a.addClass("active").addClass("hover");a.closest("#navigationTabs > li").is(e.first())||EE.navigation.truncate_menus(a.children("ul"));g=!1},60)};EE.navigation.mouse_listen=function(){d.mouseleave(function(){d.find(".active").removeClass("active")});
//e.mouseenter(function(){d.find(".active").length&&(d.find(".active").removeClass("active"),c(this).addClass("active"))});e.find("a.first_level").click(function(){var a=c(this).parent();a.hasClass("active")?a.removeClass("active"):a.addClass("active");return!1});e.find("ul li").hover(function(){j=this;g||EE.navigation.delay_show_next()},function(){c(this).removeClass("hover");g||EE.navigation.untruncate_menus(c(this).children("ul"))}).find(".parent>a").click(function(){return!1})};EE.navigation.move_top_level=
//function(a,b,c){b.parents(".active").removeClass("active");b=b.closest("#navigationTabs>li");c&&b[c]().length&&a.setFocus(b[c]().children("a"))};EE.navigation.keyboard_listen=function(){d.ee_focus("a.first_level",{removeTabs:"a",onEnter:function(a){a=c(a.target).parent();a.hasClass("parent")&&(a.addClass("active"),this.setFocus(a.find("ul>li>a").eq(0)))},onRight:function(a){var a=c(a.target),b=a.parent();b.hasClass("parent")&&!a.hasClass("first_level")?(b.addClass("active"),this.setFocus(b.find("ul>li>a").eq(0))):
//EE.navigation.move_top_level(this,b,"next")},onLeft:function(a){var a=c(a.target),b=a.parent();a.hasClass("first_level")&&b.prev().length?this.setFocus(b.prev().children("a")):(b=b.parent().closest(".parent"),b.removeClass("active"),b.children("a.first_level").length?EE.navigation.move_top_level(this,b,"prev"):this.setFocus(b.children("a").eq(0)))},onUp:function(a){var a=c(a.target),b=a.parent(),h=b.prevAll(":not(.nav_divider)");!a.hasClass("first_level")&&b.prev.length&&this.setFocus(h.eq(0).children("a"))},
//onDown:function(a){var a=c(a.target),b=a.parent(),h=b.nextAll(":not(.nav_divider)");!a.hasClass("first_level")&&h.length?this.setFocus(h.eq(0).children("a")):b.hasClass("parent")&&(b.addClass("active"),this.setFocus(b.find("ul>li>a").eq(0)))},onEscape:function(a){a=c(a.target).parent();EE.navigation.move_top_level(this,a)},onBlur:function(){this.getElements().parent.find(".active").removeClass("active")}})};EE.navigation.truncate_menus=function(a){var b=c(window).height();c.each(a,function(){var a=
//c(this),f=a.offset().top,d=a.height(),e=a.find("li:first").height(),f=f+d-b,d=a.find("> li:has(> a[href*=tgpref]):first:visible");f>0?(e=Math.ceil(f/e)+2,f=a.find("> li.nav_divider:first:visible").prev().index(),a.find("> li:visible").slice(f-e,f).hide()):d.hide()})};EE.navigation.untruncate_menus=function(a){c.each(a,function(){var b=c(this);b.is(":visible")?setTimeout(function(){EE.navigation.untruncate_menus(a)},15):b.find("> li:hidden").show()})};EE.navigation.mouse_listen();EE.navigation.keyboard_listen()};';

			}

		}

		return $js;
	}

	function cp_homepage_redirect($data)
	{

		$site_id  = (isset($data->userdata["site_id"])) ? $data->userdata["site_id"] : '1';
		$group_id = $data->userdata["group_id"];

		$this->EE->db->select('startpage, hide_sidebar');
		$this->EE->db->where('group_id', $group_id);
		$this->EE->db->where('site_id', $site_id);
		$this->EE->db->from($this->EE->db->dbprefix('zoo_flexible_admin_menus'));

		$query = $this->EE->db->get();

		if ($query->num_rows() > 0) {
			if (in_array("homepage", $_GET) && $query->row()->startpage != "" && strlen($query->row()->startpage) > 3) {
				$this->EE->load->library('user_agent');
				$prev = $this->EE->agent->referrer();

				if (strpos($prev, "C=homepage") === false) {

					$startpage = str_replace("'", '"', $query->row()->startpage);

					if (version_compare(APP_VER, 2.6, '>=')) {
						$replace = 0;
						switch ($this->EE->config->item('admin_session_type')) {
							case 's'    :
								$replace = $data->userdata['session_id'];
								break;
							case 'cs'    :
								$replace = $data->userdata['fingerprint'];
								break;
						}
						//replace all session id's with the current one
						$startpage = preg_replace('/S=.+?&D/', "S=" . $replace . "&D", $startpage);
					}
					//redirect, prevents loop
					header("Refresh: 0;url=" . $startpage);
					exit;
				}
			}

			if ($query->row()->hide_sidebar == "1") {
				$data->userdata["show_sidebar"] = "n";
			}

		}

	}

	// --------------------------------------------------------------------

	private function call($data, $which)
	{

		if ($which == "css") {
			$data = NL . $this->get_css();
		}

		if ($which == "js") {
			$data = NL . $this->get_js();
		}

		return $this->incoming . $data;
	}


	function cp_css_end($data)
	{

		if (REQ == "CP") return $this->call($data, 'css');
	}


	function cp_js_end($data)
	{
		if (REQ == "CP") return $this->call($data, 'js');
	}

	function sessions_end($SESS)
	{

		if (REQ == "CP") $this->cp_homepage_redirect($SESS);

		return $SESS;
	}


	/////////////////////////////////////////////////////////////////////////////////////////

	//LOAD THE CUSTOM NAVIGATION FOR THE CURRENTLY LOGGED IN MEMBERGROUP
	function _loadNavigation($site_id = "", $group_id = "")
	{

		if ($site_id == "" || $group_id == "" || !$this->EE->db->table_exists($this->EE->db->dbprefix('zoo_flexible_admin_menus'))) {

			return false;

		} else {

			$this->EE->db->select('nav, autopopulate, hide_sidebar');
			$this->EE->db->where('group_id', $group_id);
			$this->EE->db->where('site_id', $site_id);
			$this->EE->db->from($this->EE->db->dbprefix('zoo_flexible_admin_menus'));

			$query = $this->EE->db->get();

			if ($query->num_rows() == 0) {
				return false;

			} else {

				$s = $this->getSessionSegment();

				$nav = str_replace("'", '"', $query->row()->nav);

				//replace all session id's with the current one
				$replace = $s;

				$nav = preg_replace('/S=.+?&D/', "S=" . $replace . "&D", $nav);

				$nav = str_replace('[IMG]', '<img src="', $nav);
				$nav = str_replace('[/IMG]', '"/>', $nav);
				$nav = str_replace('?D=cp', '?S=' . $replace . '&D=cp', $nav);

				return array($nav, $query->row()->autopopulate, $query->row()->hide_sidebar);
			}
		}
	}


	function getSessionSegment()
	{
		$s = 0;

		switch ($this->EE->config->item('admin_session_type')) {
			case 's'    :
				$s = $this->EE->session->userdata('session_id', 0);
				break;
			case 'cs'    :
				$s = $this->EE->session->userdata('fingerprint', 0);
				break;
		}

		return $s;
	}

	function getOriginalArray($orig)
	{
		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		if (preg_match_all("/$regexp/siU", $orig, $matches)) {
			$names = $matches[3];
			$urls  = array();
			foreach ($matches[2] as $match) {
				array_push($urls, substr($match, strpos($match, "?") + 6));
			}
		}
		return array($urls, $names);
	}

	function get_modules()
	{

		$group_id = $this->EE->session->userdata['group_id'];
		if ($group_id == 1) {
			$query = $this->EE->db->query('SELECT module_name FROM exp_modules WHERE has_cp_backend = "y" ORDER BY module_name');
		} else {
			$query = $this->EE->db->query('SELECT m.module_name
		                               FROM exp_modules m, exp_module_member_groups mmg
		                               WHERE m.module_id = mmg.module_id
		                               AND mmg.group_id = ' . $group_id . '
		                               AND m.has_cp_backend = "y"
		                               ORDER BY m.module_name');
		}

		$modules = "";
		if ($query->num_rows()) {
			foreach ($query->result_array() as $row) {
				$class = strtolower($row['module_name']);
				$this->EE->lang->loadfile($class);
				$name        = $this->EE->lang->line($class . '_module_name');
				$url         = BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=' . $class;
				$orignamekey = array_search('C=addons_modules&M=show_module_cp&module=' . $class, $this->origArray[0]);
				if ($orignamekey) {
					$name = $this->origArray[1][$orignamekey];
				}
				$name = str_replace("'", "&#039;", $name);
				$modules .= '<li><a href="' . $url . '">' . $name . '</a>';
			}
			//$modules .= '<li class="bubble_footer"><a href="#"></a>';
		}

		return $modules;
	}


	function get_publish_channels($orig = "")
	{

		$this->EE->load->model("channel_model");
		$channel_data = $this->EE->channel_model->get_channels();

		$channels = "";
		if ($channel_data) {
			foreach ($channel_data->result() as $channel) {
				$url         = BASE . AMP . 'C=content_publish' . AMP . 'M=entry_form' . AMP . 'channel_id=' . $channel->channel_id;
				$name        = $channel->channel_title;
				$orignamekey = array_search('C=content_publish&M=entry_form&channel_id=' . $channel->channel_id, $this->origArray[0]);
				if ($orignamekey) {
					$name = $this->origArray[1][$orignamekey];
				}
				$name = str_replace("'", "&#039;", $name);
				$channels .= '<li><a href="' . $url . '">' . $name . '</a>';
			}
		}
		//$channels .= '<li class="bubble_footer"><a href="#"></a>';
		return $channels;
	}

	function get_edit_channels($list = "")
	{

		//just to prevent any errors
		if (!defined('BASE')) {
			$s = $this->getSessionSegment();
			define('BASE', SELF . '?S=' . $s . '&amp;D=cp');
		}


		$this->EE->load->model("channel_model");
		$channel_data = $this->EE->channel_model->get_channels();
		$channels     = "";
		if ($channel_data) {
			foreach ($channel_data->result() as $channel) {

				$url         = BASE . AMP . 'C=content_edit' . AMP . 'channel_id=' . $channel->channel_id;
				$name        = $channel->channel_title;
				$orignamekey = array_search('C=content_edit&channel_id=' . $channel->channel_id, $this->origArray[0]);
				if ($orignamekey) {
					$name = $this->origArray[1][$orignamekey];
				}
				$name = str_replace("'", "&#039;", $name);
				$channels .= '<li><a href="' . $url . '">' . $name . '</a>';
			}
		}
		//$channels .= '<li class="bubble_footer"><a href="#"></a>';
		return $channels;
	}


}