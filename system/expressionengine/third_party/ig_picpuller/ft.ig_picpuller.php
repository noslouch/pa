<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include (PATH_THIRD.'ig_picpuller/config.php');

class Ig_picpuller_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'Pic Puller for Instagram Browser',
		'version'	=> PP_IG_VERSION
	);

	static $counter = 0;

	// --------------------------------------------------------------------

	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	$data existing data from the field
	 * @return	string of HTML field
	 *
	 */
	function display_field($data)
	{
		$this->EE->cp->load_package_css('colorbox');
		$this->EE->cp->load_package_css('style');
		$this->EE->cp->load_package_js('jquery.ppcolorbox-min');
		$this->EE->cp->load_package_js('jquery-ui-1.8.17.custom.min');
		$this->EE->cp->load_package_js('scripts');

		$this->EE->lang->loadfile('ig_picpuller');

		$pp_theme_views = ((defined('URL_THIRD_THEMES'))
		           ? URL_THIRD_THEMES.'ig_picpuller/views/'
		           : $this->EE->config->item('url_third_themes') .'ig_picpuller/views/');

		$this->EE->cp->add_to_head('<style>#ppcboxLoadingGraphic{background:url('.$pp_theme_views.'images/loading.gif) no-repeat center center;};</style>');

		////////////////
		// Get oAuth  //
		////////////////

		$user_id = $this->EE->session->userdata('member_id');
		$oauth = $this->getAuthCredsForUser($user_id);

		$pp_engine_url = $pp_theme_views.'pp_engine.php';

		if ($oauth != '') {
			$pp_select = $pp_theme_views.'pp_select.php?access_token='.$oauth;
			$pp_search = $pp_theme_views.'pp_search.php?access_token='.$oauth;

			if ($this->settings['display_pp_instructions'] === 'yes') {
				$instructions = '<div class="instruction_text"><p style="margin-left: 1px;">'.lang('default_instructions').'</p></div>';
			} else {
				$instructions = '';
			}

			// Check to see if this particular field has settings for this instance

			if(isset($this->settings['display_pp_stream'])) {
				$display_pp_stream = $this->settings['display_pp_stream'];
			} else {
				// if no settings are found, try to use the global settings, if those are not present, default to "yes"
				$display_pp_stream = isset($data['display_pp_stream']) ? $data['display_pp_stream'] : 'yes';
			}

			if ($display_pp_stream === 'yes') {
				$stream_button = "<a class='igbrowserbt' href='$pp_select' style='display:none;'>".lang('launch_browser')." &raquo;</a> ";
			} else {
				$stream_button = '';
			}

			// Check to see if this particular field has settings for this instance

			if(isset($this->settings['display_pp_search'])) {
				$display_pp_search = $this->settings['display_pp_search'];
			} else {
				// if no settings are found, try to use the global settings, if those are not present, default to "yes"
				$display_pp_search = isset($data['display_pp_search']) ? $data['display_pp_search'] : 'yes';
			}

			if ($display_pp_search === 'yes') {
				$search_button = "<a class='igsearchbt' href='$pp_search' style='display:none;'>".lang('launch_search_browser')." &raquo;</a>";
			} else {
				$search_button = '';
			}

			$input = '<div class="ig_pp_fieldset">'.$instructions . '' .
				form_input(array(
				'name'  => $this->field_name,
				'class' => 'ig_media_id_field',
				'value' => $data
			))."<a href='$pp_engine_url?method=media&access_token=$oauth&media_id=' class='ig_preview_bt hidden'>Preview</a><div class='thumbnail preview'><img src='".$pp_theme_views."images/loading.gif' class='ig_pp_loader_gr'>	<img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAMAAABHPGVmAAAABlBMVEXd4uUAAAC4cpOLAAAARElEQVRoBe3QgQAAAADDoPlTX+EAhVBhwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMPAMDJ3QAAViTWAEAAAAASUVORK5CYII=' width=100 height=100 border=0 class='theImage'><div class='theHeadline'><em>looking up</em></div></div><br>$stream_button$search_button".'</div>';

				// /v1/media/368715424533973737_1500897?access_token=1500897.1fb234f.2f8ff3b7ca7543d68543061cd2854f82

			return $input;
		}
		else
		{
			////////////////////////////////////////////////////////////////
			// no oauth means the user has not authorized with Instagram. //
			////////////////////////////////////////////////////////////////

			return lang('unauthorized_field_type_access');
		}
	}

	/**
	 * Display the Cell for Matrix
	 * @param  $data existing data from the field
	 * @return string of HTML to display in Matrix
	 */
	function display_cell( $data )
	{
		$this->EE->cp->load_package_css('colorbox');
		$this->EE->cp->load_package_js('jquery.ppcolorbox-min');
		$this->EE->cp->load_package_js('jquery-ui-1.8.17.custom.min');
		$this->EE->cp->load_package_css('style');
		$this->EE->cp->load_package_js('scripts');

		$this->EE->lang->loadfile('ig_picpuller');

		$pp_theme_views = defined( 'URL_THIRD_THEMES' )
			? URL_THIRD_THEMES.'ig_picpuller/views/'
			: $this->EE->config->item('theme_folder_url') . 'third_party/ig_picpuller/views/';
		$pp_engine_url = $pp_theme_views.'pp_engine.php';

		$this->EE->cp->add_to_head('<style>#cboxLoadingGraphic{background:url('.$pp_theme_views.'images/loading.gif) no-repeat center center;};</style>');

		////////////////
		// Get oAuth  //
		////////////////

		$user_id = $this->EE->session->userdata('member_id');
		$oauth = $this->getAuthCredsForUser($user_id);

		if ($oauth != '') {

			$pp_select = $pp_theme_views.'pp_select.php?access_token='.$oauth;
			$pp_search = $pp_theme_views.'pp_search.php?access_token='.$oauth;

			if ($this->settings['display_pp_instructions'] === 'yes') {
				$instructions = '<div class="instruction_text"><p style="margin-left: 0px;">'.lang('default_instructions').'</p></div>';
			} else {
				$instructions = '';
			}

			// Check to see if the Matrix field has settings for this particular instance

			if(isset($this->settings['display_pp_stream'])) {
				$display_pp_stream = $this->settings['display_pp_stream'];
			} else {
				// if no settings are found, try to use the global settings, if those are not present, default to "yes"
				$display_pp_stream = isset($data['display_pp_stream']) ? $data['display_pp_stream'] : 'yes';
			}

			if ($display_pp_stream === 'yes') {
				$stream_button = "<a class='igbrowserbtmatrix' href='$pp_select' style='display:none;'>".lang('launch_browser')." &raquo;</a> ";
			} else {
				$stream_button = '';
			}

			// Check to see if the Matrix field has settings for this particular instance

			if(isset($this->settings['display_pp_search'])) {
				$display_pp_search = $this->settings['display_pp_search'];
			} else {
				// if no settings are found, try to use the global settings, if those are not present, default to "yes"
				$display_pp_search = isset($data['display_pp_search']) ? $data['display_pp_search'] : 'yes';
			}

			if ($display_pp_search === 'yes') {
				$search_button = "<a class='igsearchbtmatrix' href='$pp_search' style='display:none;'>".lang('launch_search_browser')." &raquo;</a>";
			} else {
				$search_button = '';
			}
			//$html = $instructions.'<a href="#">SHOW</a><input value="'.$data.'" name="'.$this->cell_name.'" style="width: 90%; padding: 2px; margin: 5px 0;"><br>'.$stream_button.$search_button;

			$html =$instructions . '<div class="ig_pp_fieldset"><input value="'.$data.'" name="'.$this->cell_name.'"  class="ig_media_id_field matrix_version">'."<a href='$pp_engine_url?method=media&access_token=$oauth&media_id=' class='ig_preview_bt hidden'>Preview</a><div class='thumbnail preview'><img src='".$pp_theme_views."images/loading.gif' class='ig_pp_loader_gr'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAMAAABHPGVmAAAABlBMVEXd4uUAAAC4cpOLAAAARElEQVRoBe3QgQAAAADDoPlTX+EAhVBhwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMGDBgwIABAwYMPAMDJ3QAAViTWAEAAAAASUVORK5CYII=' width=100 height=100 border=0 class='theImage'><div class='theHeadline'><em>looking up</em></div></div><br>$stream_button$search_button".'</div>';

			return $html;
		}
		else
		{
			////////////////////////////////////////////////////////////////
			// no oauth means the user has not authorized with Instagram. //
			////////////////////////////////////////////////////////////////

			return lang('unauthorized_field_type_access');
		}
	}

	/**
	 * Display the global settings for the field type
	 * @return string that is the HTML of the form that lets user alter settings
	 */
	function display_global_settings()
	{
		// load the language file
		$this->EE->lang->loadfile('ig_picpuller');

		// load the table library
		$this->EE->load->library('table');

		$val = array_merge($this->settings, $_POST);

		// Get the instructions prefs
		// See if there are global setting set for this option, if not, default to "yes"
		$display_pp_instructions = isset($val['display_pp_instructions']) ? $val['display_pp_instructions'] : 'yes';

		$checked_instr = TRUE;

		if ($display_pp_instructions === 'no') {
			$checked_instr = FALSE;
		}

		$radio1 = array(
			'name' => 'display_pp_instructions',
			'value' => 'yes',
			'checked' => $checked_instr
		);

		$radio2 = array(
			'name' => 'display_pp_instructions',
			'value' => 'no',
			'checked' => !$checked_instr
		);

		// Get the personal stream browser prefs
		// See if there are global setting set for this option, if not, default to "yes"
		$display_pp_stream = isset($val['display_pp_stream']) ? $val['display_pp_stream'] : 'yes';

		$checked_stream = TRUE;

		if ($display_pp_stream === 'no') {
			$checked_stream = FALSE;
		}

		$radio3 = array(
			'name' => 'display_pp_stream',
			'value' => 'yes',
			'checked' => $checked_stream
		);

		$radio4 = array(
			'name' => 'display_pp_stream',
			'value' => 'no',
			'checked' => !$checked_stream
		);

		// Get the search browser prefs
		// See if there are global setting set for this option, if not, default to "yes"
		$display_pp_search = isset($val['display_pp_search']) ? $val['display_pp_search'] : 'yes';

		$checked_search = TRUE;

		if ($display_pp_search === 'no') {
			$checked_search = FALSE;
		}

		$radio5 = array(
			'name' => 'display_pp_search',
			'value' => 'yes',
			'checked' => $checked_search
		);

		$radio6 = array(
			'name' => 'display_pp_search',
			'value' => 'no',
			'checked' => !$checked_search
		);

		$this->EE->table->set_template(array(
			'table_open'    => '<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">',
			'row_start'     => '<tr class="even">',
			'row_alt_start' => '<tr class="odd">'
		));

		$this->EE->table->set_heading(array('data' => lang('preference'), 'style' => 'width: 50%'), lang('setting'));

		$this->EE->table->add_row(
			lang('display_instructions_option_text', 'display_instructions_option_text'),
			 'Yes: '.form_radio($radio1).NBS.NBS.' No: '.form_radio($radio2)
		);

		$this->EE->table->add_row(
			lang('display_personal_stream_option_text', 'display_personal_stream_option_text'),
			 'Yes: '.form_radio($radio3).NBS.NBS.' No: '.form_radio($radio4)
		);

		$this->EE->table->add_row(
			lang('display_search_option_text', 'display_search_option_text'),
			 'Yes: '.form_radio($radio5).NBS.NBS.' No: '.form_radio($radio6)
		);

		return $this->EE->table->generate();

	}

	/**
	 * Saves the global settings
	 * @return an array of the settings
	 */
	function save_global_settings()
	{
		return array_merge($this->settings, $_POST);
	}

	/**
	 * Display settings for an individual instance of a Pic Puller fieldtype
	 * @param  $data existing settings for this fieldtype
	 * @return string that is the HTML of the form that lets user alter settings
	 */
	function display_settings($data)
	{
		$this->EE->lang->loadfile('ig_picpuller');

		// Get the instructions prefs
		// Check to see if particular field has settings for this particular instance

		// echo '<pre>';
		// print_r($data['display_pp_instructions']);
		// echo '</pre>';

		if(isset($data['display_pp_instructions'])) {
			$display_pp_instructions = $data['display_pp_instructions'];
		} else {
			// if no settings are found, try to use the global settings, if those are not present, default to "yes"
			$display_pp_instructions = isset($this->settings['display_pp_instructions']) ? $this->settings['display_pp_instructions'] : 'yes';
		}

		$checked_instr = TRUE;

		if ($display_pp_instructions === 'no') {
			$checked_instr = FALSE;
		}

		$radio1 = array(
			'name' => 'display_pp_instructions',
			'value' => 'yes',
			'checked' => $checked_instr
		);

		$radio2 = array(
			'name' => 'display_pp_instructions',
			'value' => 'no',
			'checked' => !$checked_instr
		);



		// Get the personal stream browser prefs
		// Check to see if particular field has settings for this particular instance

		if(isset($data['display_pp_stream'])) {
			$display_pp_stream = $data['display_pp_stream'];
		} else {
			// if no settings are found, try to use the global settings, if those are not present, default to "yes"
			$display_pp_stream = isset($this->settings['display_pp_stream']) ? $this->settings['display_pp_stream'] : 'yes';
		}

		$checked_stream = TRUE;

		if ($display_pp_stream === 'no') {
			$checked_stream = FALSE;
		}

		$radio3 = array(
			'name' => 'display_pp_stream',
			'value' => 'yes',
			'checked' => $checked_stream
		);

		$radio4 = array(
			'name' => 'display_pp_stream',
			'value' => 'no',
			'checked' => !$checked_stream
		);


		// Get the search browser prefs
		// Check to see if particular field has settings for this particular instance

		if(isset($data['display_pp_search'])) {
			$display_pp_search = $data['display_pp_search'];
		} else {
			// if no settings are found, try to use the global settings, if those are not present, default to "yes"
			$display_pp_search = isset($this->settings['display_pp_search']) ? $this->settings['display_pp_search'] : 'yes';
		}

		$checked_search = TRUE;

		if ($display_pp_search === 'no') {
			$checked_search = FALSE;
		}

		$radio5 = array(
			'name' => 'display_pp_search',
			'value' => 'yes',
			'checked' => $checked_search
		);

		$radio6 = array(
			'name' => 'display_pp_search',
			'value' => 'no',
			'checked' => !$checked_search
		);

		$this->EE->table->add_row(
			lang('display_instructions_option_text', 'display_instructions_option_text'),
			'Yes: '.form_radio($radio1).NBS.NBS.' No: '.form_radio($radio2)
		);

		$this->EE->table->add_row(
			lang('display_personal_stream_option_text', 'display_personal_stream_option_text'),
			 'Yes: '.form_radio($radio3).NBS.NBS.' No: '.form_radio($radio4)
		);

		$this->EE->table->add_row(
			lang('display_search_option_text', 'display_search_option_text'),
			 'Yes: '.form_radio($radio5).NBS.NBS.' No: '.form_radio($radio6)
		);
	}

	/**
	 * Display settings for an individual instance of a Pic Puller fieldtype in Matrix
	 * @param  $data existing settings for this fieldtype
	 * @return string that is the HTML of the form that lets user alter settings
	 */
	function display_cell_settings( $data )
	{
		$this->EE->lang->loadfile('ig_picpuller');

		// Get the instructions prefs
		// Check to see if particular field has settings for this particular instance
		if(isset($data['display_pp_instructions'])) {
			$display_pp_instructions = $data['display_pp_instructions'];
		} else {
			// if no settings are found, try to use the global settings, if those are not present, default to "yes"
			$display_pp_instructions = isset($this->settings['display_pp_instructions']) ? $this->settings['display_pp_instructions'] : 'yes';
		}

		$checked_instr = TRUE;

		if ($display_pp_instructions === 'no') {
			$checked_instr = FALSE;
		}

		$radio1 = array(
		'name' => 'display_pp_instructions',
		'value' => 'yes',
		'checked' => $checked_instr
		);

		$radio2 = array(
			'name' => 'display_pp_instructions',
			'value' => 'no',
			'checked' => !$checked_instr
		);

		// Get the personal stream browser prefs
		// Check to see if particular field has settings for this particular instance
		if(isset($data['display_pp_stream'])) {
			$display_pp_stream = $data['display_pp_stream'];
		} else {
			// if no settings are found, try to use the global settings, if those are not present, default to "yes"
			$display_pp_stream = isset($this->settings['display_pp_stream']) ? $this->settings['display_pp_stream'] : 'yes';
		}

		$checked_stream = TRUE;

		if ($display_pp_stream === 'no') {
			$checked_stream = FALSE;
		}

		$radio3 = array(
			'name' => 'display_pp_stream',
			'value' => 'yes',
			'checked' => $checked_stream
		);

		$radio4 = array(
			'name' => 'display_pp_stream',
			'value' => 'no',
			'checked' => !$checked_stream
		);

		// Get the search browser prefs
		// Check to see if particular field has settings for this particular instance
		if(isset($data['display_pp_search'])) {
			$display_pp_search = $data['display_pp_search'];
		} else {
			// if no settings are found, try to use the global settings, if those are not present, default to "yes"
			$display_pp_search = isset($this->settings['display_pp_search']) ? $this->settings['display_pp_search'] : 'yes';
		}

		$checked_search = TRUE;

		if ($display_pp_search === 'no') {
			$checked_search = FALSE;
		}

		$radio5 = array(
			'name' => 'display_pp_search',
			'value' => 'yes',
			'checked' => $checked_search
		);

		$radio6 = array(
			'name' => 'display_pp_search',
			'value' => 'no',
			'checked' => !$checked_search
		);

		return array(
		array (
			lang('display_instructions_option_text', 'display_instructions_option_text') ,
			'Yes: '.form_radio($radio1).NBS.NBS.' No: '.form_radio($radio2)
			 ),
		array (
			lang('display_personal_stream_option_text', 'display_personal_stream_option_text'),
			'Yes: '.form_radio($radio3).NBS.NBS.' No: '.form_radio($radio4)
			),
		array (
			lang('display_search_option_text', 'display_search_option_text'),
			'Yes: '.form_radio($radio5).NBS.NBS.' No: '.form_radio($radio6)
			)
		);


	}

	// --------------------------------------------------------------------

	/**
	 * Replace tag
	 *
	 * @access	public
	 * @param	existing data
	 * @return	field html
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
		static $script_on_page = FALSE;
		$ret = '';
		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Save Settings
	 *
	 * @access	public
	 * @return	field settings
	 *
	 */
	function save_settings($data)
	{
		return array(
			'ig_media_id' => '',
			'display_pp_instructions'  => $this->EE->input->post('display_pp_instructions'),
			'display_pp_stream'  => $this->EE->input->post('display_pp_stream'),
			'display_pp_search'  => $this->EE->input->post('display_pp_search'),
			'the_function' => 'media_recent'
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Install Fieldtype
	 *
	 * @access	public
	 * @return	default global settings
	 *
	 */
	function install()
	{
		return array(
			'ig_media_id'	=> '',
			'display_pp_instructions' => 'yes',
			'display_pp_stream' => 'yes',
			'display_pp_search' => 'yes',
			'the_function' => 'media_recent'
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Authorization Credentials for an EE user
	 *
	 * Get the authorization credentials from the Pic Puller oAuths table for a specified Expression Engine user Pic Puller application
	 *
	 * @access	private
	 * @param	string - User ID number for an EE member
	 * @return	mixed - returns Instagram oAuth credentials for a user if available in DB, or FALSE if unavailable
	 */

	private function getAuthCredsForUser($user_id)
	{
		$appID = $this->getCurrentAppId();
		$this->EE->db->select('oauth');
		$this->EE->db->where("member_id = " . $user_id );
		$this->EE->db->where("app_id",  $appID);
		$this->EE->db->limit(1);
		$query = $this->EE->db->get('ig_picpuller_oauths');
		foreach ($query->result() as $row)
		{
			$oauth = $row->oauth;
		}
		if (isset($oauth)){
			return $oauth;
		} else {
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get the Current PP App ID for this Instagram App
	 *
	 * Get the authorization credentials from the Pic Puller oAuths table for a specified Expression Engine user Pic Puller application
	 *
	 * @access	private
	 * @param	none
	 * @return	int
	 */

	private function getCurrentAppId()
	{
		$this->EE->db->select('app_id');
		$this->EE->db->where('ig_site_id', $this->EE->config->config['site_id']);
		$this->EE->db->limit(1);
		$this->EE->db->from('ig_picpuller_credentials');

		$query = $this->EE->db->get();

		foreach ($query->result() as $row)
		{
			$current_app_id = $row->app_id;
		}
		/*
		 echo '<pre>';
		 echo $current_app_id;
		 echo '</pre>';
		*/

		if (isset($current_app_id)){
			return $current_app_id;
		} else {
			return false;
		}
	}

}

/* End of file ft.ig_picpuller.php */
/* Location: ./system/expressionengine/third_party/ig_picpuller/ft.ig_picpuller.php */