<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CE Cache Extension
 *
 * @author		Aaron Waldon
 * @copyright	Copyright (c) 2013 Causing Effect
 * @license		http://www.causingeffect.com/software/expressionengine/ce-cache/license-agreement
 * @link		http://www.causingeffect.com
 */

if ( ! defined('CE_CACHE_VERSION') )
{
	include( PATH_THIRD . 'ce_cache/config.php' );
}

class Ce_cache_ext
{
	public $settings 		= array();
	public $description		= 'Fragment Caching for ExpressionEngine';
	public $docs_url		= 'http://www.causingeffect.com/software/expressionengine/ce-cache';
	public $name			= 'CE Cache';
	public $settings_exist	= 'n';
	public $version			= CE_CACHE_VERSION;

	private static $multi_updated = array();

	/**
	 * Constructor
	 *
	 * @param string $settings array or empty string if none exist.
	 */
	public function __construct( $settings = '' )
	{
		$this->EE = get_instance();
		$this->settings = $settings;
	}

	/**
	 * Activate the extension by entering it into the exp_extensions table
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		//settings
		$this->settings = array();

		$hooks = array(
			'entry_submission_end'			=> 'submitted',
			'safecracker_submit_entry_end'	=> 'safecracker_submitted',
			'delete_entries_start'			=> 'deleted',
			'update_multi_entries_loop'		=> 'multi_updated',
			'template_fetch_template'		=> 'template_pre_parse',
			'insert_comment_end'			=> 'comment_inserted'
		);

		foreach ( $hooks as $hook => $method )
		{
			//sessions end hook
			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> serialize( $this->settings ),
				'priority'	=> 9,
				'version'	=> $this->version,
				'enabled'	=> 'y'
			);
			$this->EE->db->insert( 'extensions', $data );
		}
	}

	/**
	 * Disables the extension by removing it from the exp_extensions table.
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	/**
	 * Updates the extension by performing any necessary db updates when the extension page is visited.
	 *
	 * @param string $current
	 * @return mixed void on update, false if none
	 */
	function update_extension( $current = '' )
	{
		if ( $current == '' OR $current == $this->version )
		{
			return false;
		}

		//some of the hooks have changed, so clear out all of the hooks and install them again
		if ( version_compare( $current, '1.7.5', '<' ) )
		{
			$this->disable_extension();
			$this->activate_extension();
		}

		return true;
	}

	/**
	 * Called when entries are published or edited with SafeCracker.
	 *
	 * @param array $data
	 * @return mixed
	 */
	public function safecracker_submitted( $data )
	{
		if ( isset( $data->entry['entry_id'] ) )
		{
			$this->break_cache( $data->entry['entry_id'] );
		}

		return $data;
	}

	/**
	 * Called when comments are added for an entry.
	 *
	 * @param array $data
	 * @param bool $comment_moderate
	 * @param int $comment_id
	 * @return void
	 */
	public function comment_inserted( $data, $comment_moderate, $comment_id )
	{
		if ( isset( $data['entry_id'] ) )
		{
			$this->break_cache( $data['entry_id'] );
		}
	}

	/**
	 * Trying to make lemonade out of this lemon of a hook. Why make a template pre-parse hook if you cannot even change the data?
	 *
	 * @param $row
	 * @return string
	 */
	public function template_pre_parse( $row )
	{
		//first check for template pre escaping
		$row['template_data'] = $this->pre_escape( $row['template_data'] );

		//then check globals for pre escaping
		foreach ( $this->EE->config->_global_vars as $index => $global )
		{
			$this->EE->config->_global_vars[$index] = $this->pre_escape( $global );
		}

		return $row;
	}

	/**
	 * Called when entries are published or edited.
	 *
	 * @param $entry_id
	 * @param $data
	 * @return void
	 */
	public function submitted( $entry_id, $data )
	{
		$this->break_cache( $entry_id );
	}

	/**
	 * Called when entries are deleted.
	 *
	 * @return void
	 */
	public function deleted()
	{
		$ids = $this->EE->input->post('delete');

		if ( empty( $ids ) )
		{
			return;
		}

		//break the entries
		$this->break_cache( $ids, true );
	}

	/**
	 * Called when multiple entries are updated. This method looks up and stores the channel ids for the updated entries, and then breaks the updated channel caches right before exiting the script.
	 *
	 * @param $entry_id
	 * @param $data
	 * @return void
	 */
	public function multi_updated( $entry_id, $data )
	{
		if ( empty( self::$multi_updated ) )
		{
			//register the shutdown function
			register_shutdown_function( array( $this, 'multi_updated_shut_down' ) );
		}

		//add the entry
		self::$multi_updated[] = $entry_id;
	}

	/**
	 * This is a shutdown function registered by the multi_updated method.
	 */
	public function multi_updated_shut_down()
	{
		//if there are no channel ids, bail
		if ( empty( self::$multi_updated ) )
		{
			return;
		}

		//grab the channel ids array
		$entry_ids = self::$multi_updated;

		//reset the array
		self::$multi_updated = array();

		//break the entries
		$this->break_cache( array_unique( $entry_ids ), true );
	}

	/**
	 * This will replace any pre-escape tags with a hash to protect them.
	 *
	 * @param $string
	 * @return mixed
	 */
	private function pre_escape( $string )
	{
		if ( strpos( $string, '{exp:ce_cache:escape' ) !== false )
		{
			preg_match_all( '@\{(exp\:ce\_cache\:escape\:(\S+))[^}]*\}(.*)\{/\\1\}@is', $string, $matches, PREG_SET_ORDER);

			foreach ( $matches as $match )
			{
				$this->EE->session->cache[ 'Ce_cache' ]['pre_escape'][ 'id_' . $match[2] ] = $match[3];
			}
		}

		return $string;
	}

	/**
	 * This method is used to break the cache.
	 *
	 * @param int|array $entry_ids
	 * @param bool $force_synchronous
	 * @return void
	 */
	private function break_cache( $entry_ids, $force_synchronous = false )
	{
		//if $entry_ids is a string, turn it into an array
		if ( is_numeric( $entry_ids ) )
		{
			$entry_ids = (array) $entry_ids;
		}

		if ( ! is_array( $entry_ids ) )
		{
			return;
		}

		//loop through the channel ids and validate them
		foreach ( $entry_ids as $index => $entry_id )
		{
			if ( ! is_numeric( $entry_id ) )
			{
				unset( $entry_ids[ $index ] );
			}
		}

		//prep the secret
		$secret = $this->EE->config->item( 'ce_cache_secret' );

		if ( ! $secret )
		{
			$secret = '';
		}

		$secret = substr( md5( $secret ), 0, 10 );

		//create the URL
		$url = $this->EE->functions->fetch_site_index( 0, 0 ) . QUERY_MARKER .  'ACT=' . $this->fetch_action_id( 'Ce_cache', 'break_cache' ) .  '&ids=' . implode( '|', $entry_ids ) . '&secret=' . $secret;

		//load the cache_break class
		if ( ! class_exists( 'Ce_cache_break' ) )
		{
			include PATH_THIRD . 'ce_cache/libraries/Ce_cache_break.php';
		}

		$cache_break = new Ce_cache_break();

		//make sure that allow_url_fopen is set to true if permissible
		@ini_set('allow_url_fopen', true);
		//some servers will not accept the asynchronous requests if there is no user_agent
		@ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:5.0) Gecko/20100101');

		if ( $force_synchronous || $this->EE->config->item( 'ce_cache_async' ) == 'no' ) //not asynchronously
		{
			$cache_break->break_cache_hook( $entry_ids, $secret );
			return;
		}

		//attempt to asynchronously send the secrets to the cache_break method of the module
		if ( $cache_break->curl_it( $url ) )
		{
			return;
		}
		else if ( $cache_break->fsockopen_it( $url ) )
		{
			return;
		}
		else //still no luck, just make it happen synchronously in this script...this could take a while
		{
			$cache_break->break_cache_hook( $entry_ids, $secret );
		}
	}

	/**
	 * This little helper function is the same one used in the cp class, but Datagrab apparently breaks that one when working with CE Cache.
	 *
	 * @param $class
	 * @param $method
	 * @return bool
	 */
	private function fetch_action_id( $class, $method )
	{
		$this->EE->db->select( 'action_id' );
		$this->EE->db->where( 'class', $class );
		$this->EE->db->where( 'method', $method );
		$query = $this->EE->db->get( 'actions' );

		if ( $query->num_rows() == 0 )
		{
			return false;
		}

		return $query->row( 'action_id' );
	}
}
/* End of file ext.ce_cache.php */
/* Location: /system/expressionengine/third_party/ce_cache/ext.ce_cache.php */