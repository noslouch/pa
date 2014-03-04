<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'structure/config.php';
require_once PATH_THIRD.'structure/sql.structure.php';
require_once PATH_THIRD.'structure/helper.php';

class Structure_upd {

    var $version = STRUCTURE_VERSION;

    function Structure_upd($switch = TRUE)
    {
		$this->EE =& get_instance();
		$this->sql = new Sql_structure();

		$this->EE->load->dbforge();
    }

	function tabs()
	{
		return array('structure' => array(
			'parent_id' => array(
				'visible'		=> TRUE,
				'collapse'		=> FALSE,
				'htmlbuttons'	=> 'true',
				'width'			=> '100%'
				),
			'uri' => array(
				'visible'		=> TRUE,
				'collapse'		=> FALSE,
				'htmlbuttons'	=> 'true',
				'width'			=> '100%'
				),
			'template_id' => array(
				'visible'		=> TRUE,
				'collapse'		=> FALSE,
				'htmlbuttons'	=> 'true',
				'width'			=> '100%'
				),
			'hidden' => array(
				'visible'		=> TRUE,
				'collapse'		=> FALSE,
				'htmlbuttons'	=> 'true',
				'width'			=> '100%'
				),
			'listing_channel' => array(
				'visible'		=> TRUE,
				'collapse'		=> FALSE,
				'htmlbuttons'	=> 'true',
				'width'			=> '100%'
				)
			)
		);
	}


	function install()
	{
		$pages_check = $this->EE->db->query("SELECT * FROM exp_modules WHERE module_name = 'Pages'");
		if ($pages_check->num_rows > 0)
		{
			show_error('Please Uninstall the "Pages" module before installing Structure. You\'ll be happy you did.', 500, 'Ruh Roh!');
			return FALSE;
		}

		$this->EE->load->dbforge();

		// Module data
		$data = array(
			'module_name' => STRUCTURE_NAME,
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'y'
		);

		$this->EE->db->insert('modules', $data);

		// Insert actions
		$data = array(
			'class' => 'Structure',
			'method' => 'ajax_move_set_data'
		);

		$this->EE->db->insert('actions', $data);

		$results = $this->EE->db->query("SELECT * FROM exp_sites");
		
		if ( ! in_array('site_pages', $results->result_array()))
		{
			// ALTER EE TABLES
			$fields = array('site_pages' => array('type' => 'longtext'));
			$this->EE->dbforge->add_column('sites', $fields);
		}

		// Create Structure Settings Table
		$fields = array(
			'id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'auto_increment' => TRUE),
			'site_id'	=>	array('type' => 'int', 'constraint' => '8', 'unsigned' => TRUE, 'null' => FALSE, 'default' => '1'),
			'var'		=>	array('type' => 'varchar', 'constraint' => '60', 'null' => FALSE),
			'var_value'	=>	array('type' => 'varchar', 'constraint' => '100', 'null' => FALSE)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('structure_settings');

		// Create Structure Table
		$fields = array(
			'site_id'		=>	array('type' => 'int', 'constraint' => '4',  'unsigned' => TRUE, 'null' => FALSE, 'default' => '1'),
			'entry_id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
			'parent_id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
			'channel_id'	=>	array('type' => 'int', 'constraint' => '6',  'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
			'listing_cid'	=>	array('type' => 'int', 'constraint' => '6',  'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
			'lft'			=>	array('type' => 'smallint', 'constraint' => '5',   'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
			'rgt'			=>	array('type' => 'smallint', 'constraint' => '5',   'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
			'dead'			=>	array('type' => 'varchar',  'constraint' => '100', 'null' => FALSE),
			'hidden'        => array('type' => 'char', 'null' => FALSE, 'default' => 'n')

		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('entry_id', TRUE);
		$this->EE->dbforge->create_table('structure');

		$this->create_table_structure_channels();
		$this->create_table_structure_listings();
		$this->populate_listings();
		$this->create_table_structure_members();

		// Insert the root node
		$data = array('site_id' => '0', 'entry_id' => '0', 'parent_id' => '0', 'channel_id' => '0', 'listing_cid' => '0', 'lft' => '1', 'rgt' => '2', 'dead' => 'root');
		$sql = $this->EE->db->insert_string('structure', $data);
		$this->EE->db->query($sql);

		// Insert the action id
		$action_id  = $this->EE->cp->fetch_action_id('Structure', 'ajax_move_set_data');
		$data = array('site_id' => 0, 'var' => 'action_ajax_move', 'var_value' => $action_id);
		$sql = $this->EE->db->insert_string('structure_settings', $data);
		$this->EE->db->query($sql);

		// Insert the module id
		$results = $this->EE->db->query("SELECT * FROM exp_modules WHERE module_name = 'Structure'");
		$module_id = $results->row('module_id');

		$sql = array();
		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(0, 'module_id', " . $module_id . ")";

		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(1, 'show_picker', 'y')";

		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(1, 'show_view_page', 'y')";

		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(1, 'show_global_add_page', 'y')";

		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(1, 'hide_hidden_templates', 'y')";

		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(1, 'redirect_on_login', 'n')";

		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(1, 'redirect_on_publish', 'n')";

		$sql[] =
					"INSERT IGNORE INTO exp_structure_settings ".
					"(site_id, var, var_value) VALUES ".
					"(1, 'add_trailing_slash', 'y')";


		foreach ($sql as $query)
		{
			$this->EE->db->query($query);
		}

		$this->EE->load->library('layout');
		$this->EE->layout->add_layout_tabs($this->tabs(), 'structure');

	    return TRUE;

	}


	function uninstall()
	{

		$this->EE->load->dbforge();
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Structure'));

		$this->EE->db->where('module_name', 'Structure');
		$this->EE->db->delete('modules');

		$this->EE->db->where('class', 'ajax_move_set_data');
		$this->EE->db->delete('actions');

		$this->EE->db->where('class', 'Structure');
		$this->EE->db->delete('actions');

		$this->EE->db->where('class', 'Structure_mcp');
		$this->EE->db->delete('actions');

		$this->EE->db->query("ALTER TABLE exp_sites DROP site_pages");

		$this->EE->dbforge->drop_table('structure_members');
		$this->EE->dbforge->drop_table('structure_settings');
		$this->EE->dbforge->drop_table('structure_channels');
		$this->EE->dbforge->drop_table('structure_listings');
		$this->EE->dbforge->drop_table('structure');

		$this->EE->load->library('layout');
		$this->EE->layout->delete_layout_tabs($this->tabs());

	    return TRUE;

	}


	function create_table_structure_members()
	{
		if ( ! $this->EE->db->table_exists('structure_members'))
		{
			$fields = array(
				'member_id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => TRUE, 'default' => 0),
				'site_id'		=>	array('type' => 'int', 'constraint' => '4',  'unsigned' => TRUE, 'null' => FALSE, 'default' => '1'),
				'nav_state'		=>	array('type' => 'text', 'null' => TRUE)
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key(array('site_id', 'member_id'), TRUE);
			$this->EE->dbforge->create_table('structure_members');
		}
	}

	function create_table_structure_channels()
	{
		if ( ! $this->EE->db->table_exists('structure_channels')) {
			// Create Structure Channels Table
			$fields = array(
				'site_id' 		=> array('type' => 'smallint', 	'unsigned' => TRUE, 'null' => FALSE),
				'channel_id' 	=> array('type' => 'mediumint', 'unsigned' => TRUE, 'null' => FALSE),
				'template_id' 	=> array('type' => 'int', 'unsigned' => TRUE, 'null' => FALSE),
				'type' 			=> array('type' => 'enum', 'constraint' => '"page", "listing", "asset", "unmanaged"', 'null' => FALSE, 'default' => 'unmanaged'),
				'split_assets'	=> array('type' => 'enum', 'constraint' => '"y", "n"', 'null' => FALSE, 'default' => 'n'),
				'show_in_page_selector' => array('type' => 'char', 'null' => FALSE, 'default' => 'y')
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key(array('site_id', 'channel_id'), TRUE);
			$this->EE->dbforge->create_table('structure_channels');
		}
	}


	function update($current = '')
	{
		if ($current == '' || $current == $this->version || $this->EE->input->get('M') == "do_update")
			return FALSE;

		if ($current < '3.0' && ! $this->EE->db->table_exists('structure_members') && ! $this->EE->db->table_exists('structure_listings')) {
			$this->upgrade_to_ee2();
		}

		if ($current < '2.2.2') {

			$data = array(
				array(
					'site_id' => 1,
					'var' => 'redirect_on_login',
					'var_value' => 'n'
				),
				array(
					'site_id' => 1,
					'var' => 'redirect_on_publish',
					'var_value' => 'n'
				)
			);

			$this->EE->db->insert_batch('structure_settings', $data);
		}


		if ($current < '3.0.3' && ! $this->EE->db->field_exists('split_assets', 'structure_channels')) {
			$sql = "ALTER TABLE `exp_structure_channels` ADD `split_assets` enum('y','n') NOT NULL default 'n'";
			$this->EE->db->query($sql);
		}


		if ($current < '3.0.4'){
			$sql = array(
				"ALTER TABLE `exp_structure` ADD INDEX `lft` (`lft`)",
				"ALTER TABLE `exp_structure` ADD INDEX `rgt` (`rgt`)"
			);

			foreach ($sql as $query)
    			$this->EE->db->query($query);
		}


		if ($current < '3.1') {

			$this->create_table_structure_members();

			if ( ! $this->EE->db->field_exists('hidden', 'structure')) {
				$this->EE->db->query("ALTER TABLE `exp_structure` ADD `hidden` char NOT NULL default 'n'");
			}

			if ( ! $this->EE->db->field_exists('show_in_page_selector', 'structure_channels')) {
				$this->EE->db->query("ALTER TABLE `exp_structure_channels` ADD `show_in_page_selector` char NOT NULL default 'y'");
			}

			$this->EE->load->library('layout');
			$this->EE->layout->delete_layout_tabs($this->tabs()); // Blow out the old tab
			$this->EE->layout->add_layout_tabs($this->tabs(), 'structure'); // Update to new tab for "Hide From Nav field"
		}


		if ($current < '3.2.2') {
			$this->EE->db->query("ALTER TABLE exp_structure_members drop primary key");
			$this->EE->db->query("ALTER TABLE exp_structure_members ADD primary key (site_id,member_id)");
		}


		// Strip trailing slashes on module update
		if ($current < '3.3') {

			$site_pages = $this->sql->get_site_pages();
			$uris = $site_pages['uris'];

			foreach ($uris as $entry_id => $uri) {
				if ($uri != "/") {
					$site_pages['uris'][$entry_id] = rtrim($uri, '/');
				}
			}

			$this->sql->set_site_pages($this->EE->config->item('site_id'), $site_pages);
		}


		if ($current < '3.3.1') {

			$data = array(
			   'site_id' => 1,
			   'var' => 'add_trailing_slash',
			   'var_value' => 'y'
			);

			$this->EE->db->insert('structure_settings', $data);
		}

	}

	function populate_listings()
	{
		require_once('libraries/nestedset/structure_nestedset.php');
		require_once('libraries/nestedset/structure_nestedset_adapter_ee.php');

		$adapter = new Structure_Nestedset_Adapter_Ee('exp_structure', 'lft', 'rgt', 'entry_id');
		$this->nset = new Structure_Nestedset($adapter);

		$site_pages = $this->sql->get_site_pages();

		foreach($site_pages['uris'] as $entry_id => $uri) {
			$slug = explode('/', $uri);

			// Knock the first and last elements off the array, they're blank.
			array_pop($slug);
			array_shift($slug);

			// Get the last segment, the Structure URI for the page.
			$slug = end($slug);

			// See if its a node or listing item
			$node = $this->nset->getNode($entry_id);

			// If we have an entry id but no node, we have listing entry
			if ($entry_id && ! $node) {
				$pid = $this->sql->get_pid_for_listing_entry($entry_id);

				// Get the channel ID for the listing
				$results = $this->EE->db->select('channel_id')->from('channel_titles')->where('entry_id', $entry_id)->get();
				$channel_id = $results->row('channel_id');

				// Get the template ID for the listing
				$template_id = $site_pages['templates'][$entry_id];

				// Insert the root node
				$data = array(
					'site_id' => $site_id,
					'entry_id' => $entry_id,
					'parent_id' => $pid,
					'channel_id' => $channel_id,
					'template_id' => $template_id,
					'uri' => $slug
				);
				$sql = $this->EE->db->insert_string('structure_listings', $data);

				$this->EE->db->query($sql);
			}
		}

	}

	function create_table_structure_listings()
	{
		if ( ! $this->EE->db->table_exists('structure_listings')) {

			$site_id = $this->EE->config->item('site_id');

			// Create Structure Listing Table
			$fields = array(
				'site_id'		=>	array('type' => 'int', 'constraint' => '4',  'unsigned' => TRUE, 'null' => FALSE, 'default' => '1'),
				'entry_id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
				'parent_id'		=>	array('type' => 'int', 'constraint' => '10', 'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
				'channel_id'	=>	array('type' => 'int', 'constraint' => '6',  'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
				'template_id'	=>	array('type' => 'int', 'constraint' => '6',  'unsigned' => TRUE, 'null' => FALSE, 'default' => '0'),
				'uri'			=>	array('type' => 'varchar', 'constraint' => '75', 'null' => FALSE)
			);

			$this->EE->dbforge->add_field($fields);
			$this->EE->dbforge->add_key('entry_id', TRUE);
			$this->EE->dbforge->create_table('structure_listings');

		}
	}

	function upgrade_to_ee2()
	{

		/*
		|--------------------------------------------------------------------------
		| Adios, sayonara, and farewell to "Weblogs"!
		|--------------------------------------------------------------------------
		|
		| We need to remap any references to weblogs or "wid"s to their proper
		| channel equivelants.
		|
		*/

		if ( ! $this->EE->db->field_exists('channel_id', 'structure') && ! $this->EE->db->field_exists('listing_cid', 'structure')) {
			$this->EE->dbforge->modify_column('structure', array(
				'weblog_id' => array('name' => 'channel_id', 'type' => 'INT', 'constraint' => 6),
				'listing_wid' => array('name' => 'listing_cid', 'type' => 'INT', 'constraint' => 6)
			));
		}

		/*
		|--------------------------------------------------------------------------
		| Table: Structure Channels
		|--------------------------------------------------------------------------
		|
		| EE1's table structure was heinous. We need to juggle data around for
		| a bit and reformat it before sticking it in the database.
		|
		*/

		// Grab the EE1 settings and empty them out.
		$ee1_settings = $this->EE->db->get('structure_settings')->result_array();
		$this->EE->db->empty_table('structure_settings');

		// Prep the new table
		$this->create_table_structure_channels();
		$structure_channels = array();

		// Convert the old data format
		foreach ($ee1_settings as $setting) {

			if ($setting['var'] == "action_ajax_move" || $setting['var'] == "module_id" || $setting['var'] == "picker" || $setting['var'] == "url")
				continue;

			if (strpos($setting['var'], 'type_weblog_') !== FALSE) {
				$channel_id = str_replace('type_weblog_', '', $setting['var']);
				$structure_channels[$channel_id]['channel_id'] = $channel_id;
				$structure_channels[$channel_id]['type'] = $this->resolve_channel_type($setting['var_value']);
			} elseif (strpos($setting['var'], 'template_weblog_') !== FALSE) {
				$channel_id = str_replace('template_weblog_', '', $setting['var']);
				$structure_channels[$channel_id]['channel_id'] = $channel_id;
				$structure_channels[$channel_id]['template_id'] = $setting['var_value'];
			} else {
				// How...?
			}

			$structure_channels[$channel_id]['site_id']    = $setting['site_id'];
			$structure_channels[$channel_id]['channel_id'] = $channel_id;
			$structure_channels[$channel_id]['split_assets'] = 'n';
			$structure_channels[$channel_id]['show_in_page_selector'] = 'y';

			// @todo listing channel check
		}

		// Populate the Structure Channels table
		foreach ($structure_channels as $data)  {
			$this->EE->db->insert('structure_channels', $data);
		}

		/*
		|--------------------------------------------------------------------------
		| Table: Structure Listings
		|--------------------------------------------------------------------------
		|
		| Structure for EE1 just jammed everything into two tables without respect
		| or a care in the world . Let's try to be better citizens, shall we?
		|
		*/

		$this->create_table_structure_listings();

		//get all structure entries that have listings
		$structure_entries = $this->EE->db->from('structure as s')
			->join('structure_channels as sc', 'sc.channel_id = s.listing_cid')
			->where('listing_cid !=', 0)
			->get()
			->result_array();

		$site_pages = $this->EE->config->item('site_pages');

		foreach ($structure_entries as $listing_entry) {
			$data = array(
				'site_id' 		=> $listing_entry['site_id'],
				'parent_id' 	=> $listing_entry['channel_id'],
				'channel_id' 	=> $listing_entry['listing_cid'],
				'template_id' 	=> $listing_entry['template_id'],
			);

			//find all the entries for this listing
			$channel_entries = $this->EE->db->from('channel_titles')
				->where('channel_id', $listing_entry['listing_cid'])
				->get()
				->result_array();

			foreach ($channel_entries as $channel_entry) {

				//if this entry is in site_pages get it uri and add it to the structure listings table
				if (isset($site_pages[$channel_entry['site_id']]['uris'][$channel_entry['entry_id']])) {

					$listing_entry = array_merge($data, array(
						'entry_id'  =>  $channel_entry['entry_id'],
						'uri'  		=>  Structure_Helper::get_slug($site_pages[$channel_entry['site_id']]['uris'][$channel_entry['entry_id']])
					));

					$this->EE->db->insert('structure_listings', $listing_entry);
				}
			}
		}

		/*
		|--------------------------------------------------------------------------
		| Extension
		|--------------------------------------------------------------------------
		|
		| We don't store any data in the extension, so it's much simpler to just
		| blow it out and reinstall it ourselves.
		|
		*/

		$this->EE->db->delete('extensions', array('class' => 'Structure_ext'));

		require_once PATH_THIRD.'structure/ext.structure.php';
		$ext = new Structure_ext();
		$ext->activate_extension();

		/*
		|--------------------------------------------------------------------------
		| Publish Tab
		|--------------------------------------------------------------------------
		|
		| We need to tell ExpressionEngine that we have a pile of useful fields to
		| add into the Publish Tab.
		|
		*/

		$this->EE->load->library('layout');
		$this->EE->layout->add_layout_tabs($this->tabs(), 'structure');

		$this->EE->db->where('module_name', "Structure");
		$this->EE->db->update('modules', array('has_publish_fields' => 'y'));

	}

	function resolve_channel_type($type)
	{
		if ($type == "structure") {
			return "page";
		} elseif ($type == "asset") {
			return "asset";
		} else {
			return "unmanaged";
		}
	}


}
/* End of file upd.structure.php */
