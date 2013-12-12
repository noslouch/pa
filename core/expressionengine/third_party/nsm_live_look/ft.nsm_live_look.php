<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Fieldtype for NSM Live Look: Displays the preview iframe
 *
 * @package			NsmLiveLook
 * @version			1.2.4
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2013 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */
class Nsm_live_look_ft extends EE_Fieldtype
{
	/**
	 * Field info - Required
	 * 
	 * @access public
	 * @var array
	 */
	public $info = array(
		'name'		=> 'NSM Live Look',
		'version'	=> '1.2.4'
	);

	public $field_id;
	public $field_name;
	public $EE;

	/**
	 * The fieldtype global settings array
	 * 
	 * @access public
	 * @var array
	 */
	public $settings = array();

	/**
	 * The field type - used for form field prefixes. Must be unique and match the class name. Set in the constructor
	 * 
	 * @access private
	 * @var string
	 */
	public $field_type = '';

	/**
	 * Constructor
	 * 
	 * @access public
	 */
	public function __construct()
	{
		$this->addon_id = $this->field_type = strtolower(substr(__CLASS__, 0, -3));
		parent::EE_Fieldtype();
	}	


	/**
	 * Display the field in the publish form
	 * 
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 */
	public function display_field($data)
	{
		$this->EE->lang->loadfile('nsm_live_look');

		$channel_id = $this->EE->input->get('channel_id');
		$entry_id = $this->EE->input->get('entry_id');

		if( ! class_exists('Nsm_live_look_ext') ) {
			require(PATH_THIRD . "nsm_live_look/ext.nsm_live_look.php");
		}

		$ext = new Nsm_live_look_ext();
		$channel_settings = $ext->_channelSettings($channel_id);
		$channel_urls = $channel_settings["urls"];

		# Load the library
		$this->EE->load->library($this->addon_id . "_helper");

		# Add the custom field stylesheet to the header 
		$this->EE->nsm_live_look_helper->addCSS('custom_field.css');

		# Load the JS for the iframe
		$this->EE->nsm_live_look_helper->addJS('../lib/jquery.cookie.js');
		$this->EE->nsm_live_look_helper->addJS('custom_field.js');

		if($entry_id && $channel_urls)
		{
			foreach ($channel_urls as &$url)
			{
				$url["url"] = $this->parse_url($url["url"], $entry_id, $url["page_url"]);
			}
		}

		$settings_url = BASE.'&amp;C=addons_extensions&amp;M=extension_settings&amp;file=nsm_live_look#nsm_live_look_config_';

		$field_data = array
		(
			'entry_id'		=> $entry_id,
			'field_name' 	=> $this->field_name,
			'settings_url'	=> $settings_url,
			'channel_id' 	=> $channel_id,
			'urls'			=> $channel_urls
		);

		return $this->EE->load->view('fieldtype/fieldtype', $field_data, TRUE);
	}

	/**
	 * Parses a preview url string and replaces each of the tags with the data
	 * from the entry id given as an argument.
	 *
	 * @author Anthony Short
	 * @param	$url		string		The URL string with {tags} to insert entry data into
	 * @param 	$entry_id	int   		The id of the channel entry to base the url on
	 * @return 	string
	 */
	private function parse_url($url,$entry_id, $page_url=FALSE)
	{
		if($page_url && isset($this->EE->config->config["site_pages"][SITE_ID]["uris"][$entry_id]))
		{
			$url = $this->EE->config->config['site_pages'][SITE_ID]['uris'][$entry_id];
		}
		else
		{
			$query = $this->EE->db
				->from('exp_channel_titles')
				->join('exp_channel_data', 'exp_channel_titles.entry_id = exp_channel_data.entry_id', 'LEFT')
				->where('exp_channel_titles.entry_id', $entry_id)
				->limit(1)
				->get()
				->result_array();

			if(count($query) > 0)
			{
				$data = $query[0];
	
				$data['entry_date_day'] 	= date('d', $data['entry_date']);
				$data['entry_date_month'] 	= date('m', $data['entry_date']);
				$data['entry_date_year'] 	= date('Y', $data['entry_date']);

				foreach ($data as $key => $value)
				{
					if(strpos($url, LD.$key.RD) !== FALSE)
					{
						$url = str_replace(LD.$key.RD, $value, $url);
					}
				}
			}
		}

		if(substr($url, 0, 4) !== "http"){
			return $this->EE->functions->create_url($url);
		}else{
			return $url;
		}
	}


}
//END CLASS
