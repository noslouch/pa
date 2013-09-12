<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Super Search - User Side
 *
 * Handles template level functions.
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/super_search
 * @license		http://www.solspace.com/license_agreement
 * @version		2.0.6
 * @filesource	super_search/mod.super_search.php
 */

if ( ! defined('APP_VER')) define('APP_VER', '2.0'); // EE 2.0's Wizard doesn't like CONSTANTs

require_once 'addon_builder/module_builder.php';

class Super_search extends Module_builder_super_search
{
	public $TYPE;

	public $disable_caching			= FALSE;
	public $disable_history			= FALSE;
	public $cache_overridden		= FALSE;
	public $relevance_count_words_within_words	= FALSE;
	public $allow_wildcards 		= FALSE;
	public $allow_regex	 			= FALSE;

	public $minlength				= array( 3, 20000 );	// Searches on keywords that are too small return too many results. We force a limit on the DB query in those cases. First element is the minimum keyword length, second element is the limit we impose.

	public $wminlength				= array( 3, 500 );	//	Same as above except that the limits when using channel:entries are much lower.

	public $hash					= '';
	public $history_id				= 0;

	public $ampmarker				= '45lkj78fd23!lk';
	public $doubleampmarker			= '98lk7854cik3fgd9';
	public $negatemarker			= '87urnegate09u8';
	public $modifier_separator		= '-';
	public $parse_switch			= '';
	public $parser					= '&';
	public $separator				= '=';
	public $slash					= 'SLASH';
	public $spaces					= '+';
	public $pipes 					= '|';
	public $wildcard 				= '*';

	public $cur_page				= 0;
	public $current_page			= 0;
	public $limit					= 100;
	public $total_pages				= 0;
	public $page_count				= '';
	public $page_next				= '';
	public $page_previous			= '';
	public $pager					= '';
	public $paginate				= FALSE;
	public $paginate_data			= '';
	public $res_page				= '';
	public $urimarker				= 'jhgkjkajkmjksjkrlr3409oiu';
	public $inclusive_keywords	 	= TRUE;
	public $inclusive_categories 	= FALSE;
	public $has_regex				= FALSE;
	public $partial_author 			= TRUE;
	public $fuzzy_weight			= 1;
	public $fuzzy_weight_default	= 0.3;
	public $relevance_proximity_threshold 	= 999;
	public $relevance_proximity		= 0;
	public $relevance_proximity_default	= 1.3;
	public $relevance_frequency		= 1.3;

	public $arrays					= array();	// Enables a URI to contain multiple parameters of the same type in an array manner
	public $basic					= array(
									'author',
									'category',
									'categorylike',
									'category_like',
									'category-like',
									'dynamic',
									'exclude_entry_ids',
									'group',
									'include_entry_ids',
									'keywords',
									'limit',
									'allow_repeats',
									'num',
									'order',
									'orderby',
									'search-words-within-words',
									'search_words_within_words',
									'start',
									'status',
									'channel',
									'inclusive_keywords',
									'inclusive_categories',
									'keyword_search_author_name',
									'keyword_search_category_name',
									'use_ignore_word_list',
									'smart_excerpt',
									'search_in',
									'where',
									'partial_author',
									'relevance_proximity',
									'wildcard_character',
									'wildcard_fields',
									'allow_regex',
									'regex_fields',
									'fuzzy_weight',
									'site' );
	// Tests for simple parameters. Note that some are aliases such as limit as an alias of num.

	public $common					= array( 'a', 'and', 'of', 'or', 'the' );
	public $searchable_ct			= array( 'title' );	// We allow field and exact field searching on some of the columns in exp_channel_titles.
	public $sess					= array();

	public $_buffer 				= array();	// Cut and Paste Buffer
	public $marker 		 			= '';		// Cut and Paste Marker

	public $alphabet 				= "abcdefghijklmnopqrstuvwxyz";
	public $suggested 				= array();  // keyword suggestion buffer
	public $corrected 				= array();  // keyword correction buffer

	public $p_limit					= '';
	public $p_page					= '';


	// -------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	function __construct ()
	{
		parent::__construct('super_search');

		// -------------------------------------
		//  Module Installed?
		// -------------------------------------

		if ( $this->database_version() == FALSE )
		{
			$this->disabled = TRUE;

			trigger_error(lang('super_search_module_disabled'), E_USER_NOTICE);
		}

		// -------------------------------------
		//  Module Up to Date?
		// -------------------------------------

		if ( $this->version_compare($this->database_version(), '<', SUPER_SEARCH_VERSION) )
		{
			$this->disabled = TRUE;

			trigger_error(lang('super_search_module_out_of_date'), E_USER_NOTICE);
		}

		// -------------------------------------
		// Prepare for $this->EE->session->cache
		// -------------------------------------

		if ( isset( $this->EE->session->cache ) === TRUE )
		{
			if ( isset( $this->EE->session->cache['modules']['super_search'] ) === FALSE )
			{
				$this->EE->session->cache['modules']['super_search']	= array();
			}

			$this->sess	=& $this->EE->session->cache['modules']['super_search'];
		}

		// -------------------------------------
		//	'super_search_extra_basic_fields' hook.
		// -------------------------------------
		//	Pass $this by reference and allow for action.
		// -------------------------------------

		if ($this->EE->extensions->active_hook('super_search_extra_basic_fields') === TRUE)
		{
			$edata = $this->EE->extensions->universal_call('super_search_extra_basic_fields', $this);
		}
	}

	//	END Super search constructor

	// -------------------------------------------------------------

	/**
	 * Cache
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _cache( $q = array(), $ids = array(), $results = 0, $type = 'q' )
	{
		if ( empty( $q ) === TRUE ) return FALSE;
		if ( $this->disable_caching === TRUE )
		{
			if ( ! empty( $this->sess['uri']['keywords'] ) )
			{
				$results	= ( $results == 0 ) ? count( $ids ): $results;

				// Log this search
				$this->data->log_search( $this->sess );
			}

			return FALSE;
		}

		$q	= ( empty( $q['q'] ) ) ? array(): $q['q'];

		$this->hash	= ( $this->hash == '' ) ? $this->_hash_it( $q ): $this->hash;

		if ( ( $cache = $this->sess( 'searches', $this->hash ) ) !== FALSE ) return $cache;

		$ids		= ( is_array( $ids ) === FALSE ) ? array(): $ids;

		$results	= ( $results == 0 ) ? count( $ids ): $results;

		$ids		= ( empty( $ids ) === TRUE ) ? '': $this->_cerealize( $ids );

		$q	= base64_encode( serialize( $q ) );

		$cache_id	= 0;

		if ( $this->data->caching_is_enabled() === TRUE )
		{
			$sql	= $this->EE->db->insert_string(
				'exp_super_search_cache',
				array(
					'type'		=> $type,
					'hash'		=> $this->hash,
					'date'		=> $this->EE->localize->now,
					'results'	=> $results,
					'query'		=> $q,
					'ids'		=> $ids
					)
				);

			$sql	.= " /* Super Search mod.super_search.php _cache() */";

			$this->EE->db->query( $sql );

			$cache_id	= $this->EE->db->insert_id();
		}


		if ( ! empty( $this->sess['uri']['keywords'] ) )
		{
			// Log this search
			$this->data->log_search( $this->sess );
		}

		$this->_save_search_to_history( $cache_id, $results, $q );

		$this->sess['searches'][$this->hash]	= $ids;

		return TRUE;
	}

	//	End cache

	// -------------------------------------------------------------

	/**
	 * Cached?
	 *
	 * @access	private
	 * @return	array
	 */

	function _cached( $hash = '', $type = 'q' )
	{
		if ( $this->disable_caching === TRUE ) return FALSE;
		if ( is_string( $hash ) === FALSE OR $hash == '' ) return FALSE;
		if ( $this->data->caching_is_enabled() === FALSE ) return FALSE;

		if ( ( $cache = $this->sess( 'searches', $hash ) ) !== FALSE )
		{
			return $cache;
		}

		$this->_clear_cached();

		$sql	= "/* Super Search " . __FUNCTION__ . " */
		SELECT cache_id, query, results, ids
		FROM exp_super_search_cache
		WHERE hash = '".$this->EE->db->escape_str( $hash )."'
		LIMIT 1";

		$query	= $this->EE->db->query( $sql );

		if ( $query->num_rows() > 0 )
		{
			$cache	= ( $query->row('ids') == '' ) ? array(): $this->_uncerealize( $query->row('ids') );

			$this->sess['searches'][$hash]	= $cache;
			$this->sess['results']			= $query->row('results');

			$this->_save_search_to_history( $query->row('cache_id'), $query->row('results'),  $query->row('query') );

			if ( ! empty( $this->sess['uri']['keywords'] ) )
			{
				// Log this search
				$this->data->log_search( $this->sess );
			}

			return $cache;
		}

		return FALSE;
	}

	//	End cached?

	// -------------------------------------------------------------

	/**
	 * Cerealize
	 *
	 * serialize() and unserialize() add a bunch of characters that are not needed when storing a one dimensional indexed array. Why not just use a pipe?
	 *
	 * @access	private
	 * @return	string
	 */

	function _cerealize( $arr = array() )
	{
		if ( is_array($arr) === FALSE ) return '';
		if ( count( $arr ) == 1 ) return array_pop( $arr );
		return implode( '|', $arr );
	}

	//	End cerealize

	// -------------------------------------------------------------

	/**
	 * Chars decode
	 *
	 * Preps a string from oddball EE character conversions
	 *
	 * @access	private
	 * @return	str
	 */

	function _chars_decode( $str = '' )
	{
		if ( $str == '' ) return;

		if ( function_exists( 'htmlspecialchars_decode' ) === TRUE )
		{
			$str	= htmlspecialchars_decode( $str );
		}

		if ( function_exists( 'html_entity_decode' ) === TRUE )
		{
			$str	= html_entity_decode( $str );
		}

		$str	= str_replace( array( '&amp;', '&#47;', '&#39;', '\'' ), array( '&', '/', '', '' ), $str );

		$str	= stripslashes( $str );

		return $str;
	}

	//	End chars decode

	// -------------------------------------------------------------

	/**
	 * Check template params
	 *
	 * This method allows people to force params onto and override our query from template params
	 *
	 * @access	private
	 * @return	str
	 */

	function _check_tmpl_params( $key = '', $q = array() )
	{
		if ( $key == '' ) return $q;

		// -------------------------------------
		//	We completely skip some template params.
		// -------------------------------------

		if ( in_array( $key, array( 'order', 'orderby' ) ) === TRUE ) return $q;

		// -------------------------------------
		//	We allow some dynamic params to override template params. Note that channel and site are special cases that get tested further elsewhere.
		// -------------------------------------

		if ( in_array( $key, array( 'channel', 'limit', 'num', 'start', 'offset', 'site') ) === TRUE AND empty( $q[$key] ) === FALSE )
		{
			return $q;
		}

		if ( $this->EE->TMPL->fetch_param($key) !== FALSE AND $this->EE->TMPL->fetch_param($key) != '' )
		{
			$q[$key]	= $this->EE->TMPL->fetch_param($key);
		}

		// -------------------------------------
		//	Prep status / group
		// -------------------------------------

		foreach ( array( 'group', 'status' ) as $fix )
		{

			if ( isset( $q[ $fix ] ) === TRUE )
			{

				// -------------------------------------
				//	Simple multi-word statuses / groups need to be protected
				// -------------------------------------
				//	When someone uses the status template parameter and they call a multi-word status, they shouldn't be expected to put that in quotes. The status is already in quotes. That would be double double quotes which would be over the top. This conditional tests for that and forces quotes around multi-word statuses so that $$ knows what to do later.
				// -------------------------------------

				if ( strpos( $q[ $fix ], '|' ) === FALSE AND strpos( $q[ $fix ], '"' ) === FALSE AND strpos( $q[ $fix ], "'" ) === FALSE AND ( strpos( $q[ $fix ], 'not ' ) != 0 OR strpos( $q[ $fix ], 'not ' ) === FALSE ) AND (strpos( $q[ $fix ], '+') OR  strpos( $q[ $fix ], ' ')) )
				{
					if ( $fix == 'status' )
					{
						$this->_statuses( $this->EE->TMPL->site_ids );

						//last check - if we have no mulitword statuses in the system, this is redundant
						if ( isset($this->sess['statuses']['multiword_status']) )
						{
							//check the statuses array - if we have a straight match - wrap it up baby
							$matched = FALSE;

							foreach( $this->sess['statuses']['cleaned'] AS $status )
							{
								if ( strpos( $q[$fix] , $status ) !== FALSE AND strpos($status, "+"))
								{
									$matched = TRUE;
								}
							}

							if ($matched)
							{
								$q[ $fix ]	= '"' . $q[ $fix ] . '"';
							}

						}
					}
					else
					{
						//apply this blindly for status groups
						$q[ $fix ]	= '"' . $q[ $fix ] . '"';
					}
				}

			}
		}

		if ( isset( $q[$key] ) AND strpos( $q[$key], '&&' ) !== FALSE )
		{
			$q[$key]	= str_replace( '&&', $this->doubleampmarker, $q[$key] );
		}

		if ( isset( $q[$key] ) === TRUE AND ( strpos( $q[$key], $this->slash ) !== FALSE OR strpos( $q[$key], SLASH ) !== FALSE ) )
		{
			$q[$key]	= str_replace( array( $this->slash, SLASH ), '/', $q[$key] );
		}

		// -------------------------------------
		//	We convert the negation marker, which is a dash, to something obscure so that regular dashes in words do not create problems.
		// -------------------------------------

		if ( isset( $q[$key] ) AND strpos( $q[$key], '-' ) === 0 )
		{
			$q[$key]	= $this->separator.$this->negatemarker . trim( $q[$key], '-' );
		}

		if ( $key == 'orderby' AND empty( $q['orderby'] ) === FALSE )
		{
			$q['order']	= $this->_prep_order( $q['orderby'] );
		}
		elseif ( $key == 'order' AND empty( $q['order'] ) === FALSE )
		{
			$q['order']	= $this->_prep_order( $q['order'] );
		}

		return $q;
	}

	//	End check template params

	// -------------------------------------------------------------

	/**
	 * Clean numeric fields
	 *
	 * @access	private
	 * @return	array
	 */

	function _clean_numeric_fields( $arr = array() )
	{
		// -------------------------------------
		//	For each field...
		// -------------------------------------

		foreach ( array_keys( $arr ) as $key )
		{
			// -------------------------------------
			//	For each element
			// -------------------------------------

			foreach ( $arr[$key] as $k => $v )
			{
				// -------------------------------------
				//	If field expects numeric, try to convert punctuation. This is also the place to handle European monetary formats, but later.
				// -------------------------------------

				if ( $this->_get_field_type( $key ) !== FALSE AND $this->_get_field_type( $key ) == 'numeric' )
				{
					$arr[$key][$k]	= str_replace( array( ',', '$' ), '', $v );
				}
			}
		}

		return $arr;
	}

	//	End clean numeric fields

	// -------------------------------------------------------------

	/**
	 * Clear cached
	 *
	 * @access	private
	 * @return	array
	 */

	function _clear_cached()
	{
		if ( $this->sess( 'cleared' ) !== FALSE ) return FALSE;

		// -------------------------------------
		//	Should we refresh cache? Have we cleared it recently?
		// -------------------------------------

		if ( $this->data->time_to_refresh_cache( $this->EE->config->item( 'site_id' ) ) === FALSE )
		{
			$this->sess['cleared']	= TRUE;
			return FALSE;
		}

		// -------------------------------------
		//	Clear cache now
		// -------------------------------------

		do
		{
			$this->EE->db->query(
				"DELETE FROM exp_super_search_cache
				WHERE date < ".( $this->EE->localize->now - ( $this->data->get_refresh_by_site_id( $this->EE->config->item( 'site_id' ) ) * 60 ) )."
				LIMIT 1000 /* Super Search delete cache */"
			);
		}
		while ( $this->EE->db->affected_rows() == 1000 );

		do
		{
			$this->EE->db->query(" DELETE FROM exp_super_search_history
						WHERE search_date < ".( $this->EE->localize->now - ( 60 * 60 ) )."
						AND saved = 'n'
						AND cache_id NOT IN (
							SELECT cache_id
							FROM exp_super_search_cache )
						LIMIT 1000 /* Super Search clear search history */");
		}
		while ( $this->EE->db->affected_rows() == 1000 );

		$this->data->set_new_refresh_date( $this->EE->config->item( 'site_id' ) );
		$hash	= $this->data->_imploder( array( $this->EE->config->item( 'site_id' ) ) );
		$this->data->cached['time_to_refresh_cache'][ $hash ] = FALSE;
		$this->sess['cleared']	= TRUE;
	}

	//	End clear cached

	// -------------------------------------------------------------

	/**
	 *	Removes/Cuts A Piece of Content Out of String to Save it From Being Manipulated During a Find/Replace
	 *
	 *  Many thanks to gosha bine ("http://tagarga.com/blok/on/080307") for the code, it is rather brilliantly executed. -Paul
	 *
	 *	@access		public
	 *	@param		string
	 *	@param		bool|string
	 *	@return		string
	 */

	function cut($subject, $regex = FALSE)
	{
		if (is_array($subject))
		{
			$this->_buffer[md5($subject[0])] = $subject[0];
			return ' '.$this->marker.md5($subject[0]).$this->marker.' ';
		}

		return preg_replace_callback($regex, array(&$this, 'cut'), $subject);
	}

	//	END cut()

	// -------------------------------------------------------------

	/**
	 * Do search
	 *
	 * One of the main principles in Super Search is that MySQL performance is better with a greater number of simpler queries rather than a lesser numbers of queries but of greater complexity.
	 *
	 * Performance note: see notes on new_do_search. Table joins in our current case prevent us from taking good advantage of query caching and they are also generally slower, by half! Nice!
	 *
	 * @access	public
	 * @return	array
	 */

	function do_search_ct_cd( $q = array() )
	{
		if ( is_array( $q ) === FALSE OR count( $q ) == 0 ) return FALSE;

		// -------------------------------------
		//	If dynamic mode has been turned off, we need to clear the session cached vars and start over.
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param( 'dynamic' ) !== FALSE AND $this->check_no( $this->EE->TMPL->fetch_param( 'dynamic' ) === TRUE ) )
		{
			$this->sess['search']	= array();
		}

		$t	= microtime(TRUE);

		$this->EE->TMPL->log_item( 'Super Search: Starting do_search()' );
		$this->EE->TMPL->log_item( 'Super Search: Starting prep query' );

		// -------------------------------------
		//	Begin SQL
		// -------------------------------------

		$select	= '/* Super Search ct/cd search */ SELECT t.entry_id';
		$from	= ' FROM ' . $this->sc->db->titles . ' t LEFT JOIN ' . $this->sc->db->channel_data . ' cd ON cd.entry_id = t.entry_id  %indexes% ';

		if ( isset( $this->sess['uri']['keyword_search_author_name'] ) AND $this->check_yes( $this->sess['uri']['keyword_search_author_name'] ) )
		{
			$from  .= ' LEFT JOIN exp_members m ON t.author_id = m.member_id ';
		}

		if ( isset( $this->sess['uri']['keyword_search_category_name'] ) AND $this->check_yes( $this->sess['uri']['keyword_search_category_name'] ) )
		{
			$from  .= ' LEFT JOIN exp_categories cat ON cat.cat_id IN
					( SELECT cat_id FROM exp_category_posts cat_p WHERE cat_p.entry_id = t.entry_id )';
		}

		$where	= ' WHERE t.entry_id != 0 ';
		$and	= array();
		$not	= array();
		$or		= array();
		$subids	= array();
		$and_special = array();

		// -------------------------------------
		//	Show future?
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('show_future_entries') === FALSE OR $this->EE->TMPL->fetch_param('show_future_entries') != 'yes' )
		{
			$where	.= ' AND t.entry_date < '.$this->EE->localize->now;
		}

		// -------------------------------------
		//	Show expired?
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('show_expired') === FALSE OR $this->EE->TMPL->fetch_param('show_expired') != 'yes' )
		{
			$where	.= ' AND (t.expiration_date = 0 OR t.expiration_date > '.$this->EE->localize->now.')';
		}

		// -------------------------------------
		//	Prep site ids
		// -------------------------------------

		// Get our template params for the site marker
		$q['site']	= ( isset( $q['site'] ) === TRUE ) ? $q['site']: '';

		// split up the tmpl params, this also checks validity, returns only valid site_ids, or the current site if empty
		$passed_sites = $this->_prep_site_ids( $q['site'] );

		$tmpl_sites = $this->_prep_site_ids( $this->EE->TMPL->fetch_param('site') );

		$search_sites = array_intersect( $tmpl_sites, $passed_sites );

		if ( count( $search_sites ) == 0 ) return FALSE;

		$this->EE->TMPL->site_ids = $this->sess['search']['q']['site'] = $search_sites;

		// -------------------------------------
		//	Prep channel
		// -------------------------------------

		$q['channel']	= ( isset( $q['channel'] ) === TRUE ) ? $q['channel']: '';
		$this->sess['search']['q']['channel']	= $this->_prep_keywords( $q['channel'] );

		// -------------------------------------
		//	If we can't find valid channels, we need to fail out.
		// -------------------------------------

		if ( ( $this->sess['search']['q']['channel_ids'] = $this->_prep_channel( $q['channel'] ) ) === FALSE )
		{
			unset( $channel_ids );
			return FALSE;
		}

		// -------------------------------------
		//	Prep dynamic
		// -------------------------------------

		if ( isset( $q['dynamic'] ) === TRUE )
		{
			$this->sess['search']['q']['dynamic']	= $q['dynamic'];
		}

		// -------------------------------------
		//	Prep status / group
		// -------------------------------------

		foreach ( array( 'group', 'status' ) as $fix )
		{
			if ( isset( $q[ $fix ] ) === TRUE )
			{
				$this->sess['search']['q'][ $fix ]	= $this->_prep_keywords( $q[ $fix ] );
			}
		}

		// -------------------------------------
		//	Prep where
		// -------------------------------------

		if ( isset( $q['where'] ) === TRUE )
		{
			$this->sess['search']['q']['where']	= $q['where'];
		}

		// -------------------------------------
		//	Prep partial author
		// -------------------------------------

		if ( isset( $q['partial_author'] ) === TRUE )
		{
			$this->sess['search']['q']['partial_author'] = $q['partial_author'];
		}

		// -------------------------------------
		//	Prep wildcards
		// -------------------------------------
		//	In the class definition of $this->allow_wildcards, wild card searching is off by default. Turn it on with the allow_wildcards='y' parameter. By default, all fields will be wildcard searchable unless
		// -------------------------------------

		if ( isset( $q['wildcard_character'] ) === TRUE AND trim( $q['wildcard_character'] ) != '' )
		{
			$this->wildcard = $this->EE->db->escape_str( $q['wildcard_character'] );
		}

		if ( ! empty( $q['wildcard_fields'] ) )
		{
			$this->allow_wildcards = TRUE;	// This is set to FALSE in the class definition. But if someone merely indicates the wildcard_fields param, we turn the feature on and sort out the details in $this->data->flag_state(). If they exclude a field, then all the rest are fair game. If they include some fields, then only those can be searched. Either way, something is searchable.

			if ( strtolower(trim($q['wildcard_fields'])) == 'all' )
			{
				unset( $q['wildcard_fields'] );
			}
			else
			{
				$this->sess['uri']['wildcard_fields']	= $this->_prep_keywords( str_replace( '-', $this->negatemarker, $q['wildcard_fields'] ) );
			}
		}

		// -------------------------------------
		//	Prep regex fields
		// -------------------------------------

		if ( isset( $q['allow_regex'] ) === TRUE OR isset( $q['allow_regex'] ) === TRUE )
		{
			if ( isset( $q['allow_regex'] ) === TRUE AND $this->check_yes( $q['allow_regex'] ) === TRUE )
			{
				$this->allow_regex = TRUE;
			}

			if ( isset( $q['regex_fields'] ) === TRUE )
			{
				$this->sess['uri']['regex_fields']	= $this->_prep_keywords( str_replace( '-', $this->negatemarker, $q['regex_fields'] ) );
			}
		}

		// -------------------------------------
		//	Prep keywords
		// -------------------------------------

		if ( isset( $q['keywords'] ) === TRUE )
		{
			//we have the option to overload the default 'OR' searching
			//using the param 'inclusive_keywords', pass this to the prep_keywords method now,
			//as it gets used in a number of other places
			$inclusive_keywords = $this->inclusive_keywords;

			if (isset( $this->sess['uri']['inclusive_keywords'] ) AND $this->check_no($this->sess['uri']['inclusive_keywords']) )
			{
				$inclusive_keywords = FALSE;
			}

			if ( isset( $this->sess['uri']['where'] ) AND $this->sess['uri']['where'] == 'all' )
			{
				$inclusive_keywords = TRUE;
			}
			elseif ( isset( $this->sess['uri']['where'] ) AND $this->sess['uri']['where'] == 'any' )
			{
				$inclusive_keywords = FALSE;
			}

			// Set a clean search phrase here, before we start messing with our keywords, so we can
			// cleanly repopulate the search box later on
			$this->sess['search']['q']['keywords_phrase'] = $this->_clean_keywords( $q['keywords'] );

			// We might have enabled our ingore word list
			if ( isset( $q['use_ignore_word_list'] ) ) $q['keywords'] = $this->_prep_ignore_words( $q['keywords'], $q['use_ignore_word_list'] );
			else $q['keywords'] = $this->_prep_ignore_words( $q['keywords']);

			if ( $q['keywords'] === FALSE )
			{
				// The ignore list has wiped out any passed terms
				// We want to return the no_results condition here
				// rather than doing any more work
				return FALSE;
			}

			$this->sess['search']['q']['keywords']	= $this->_prep_keywords( $q['keywords'] , $inclusive_keywords, 'keywords' );
		}

		// -------------------------------------
		//	Prep search in
		// -------------------------------------

		if ( isset( $q['search_in'] ) === TRUE )
		{
			// We have a passed value for search_in
			// this allows users to override the default search behaviour
			// of 'keywords' to search specifically in any of the set
			// searchable fields. The same effect can be achieved passing the
			// field names directly, but this allows for dynamic changes
			// without hacks on the user side

			if ( isset( $this->sess['search']['q']['keywords'] ) === TRUE )
			{
				$search_in = $this->EE->db->escape_str( $q['search_in'] );

				// be kind to people (I've seen people acutally try this so give them a hand)
				$everywhere = array( 'everything', 'everywhere', 'all');
				$titles = array( 'title', 'titles');

				$everything_override = FALSE;

				$fields = array();

				$searches = explode( '|', $search_in );

				foreach( $searches as $search )
				{
					if ( !( in_array( $search, $everywhere ) === TRUE OR trim( $search ) == '' ) )
					{

						if ( in_array( $search, $titles ) === TRUE )
						{
							$fields['title'] = $this->sess['search']['q']['keywords'];
						}
						else
						{
							// Assume they're passing a valid fieldname. If they're not we'll just ignore their input later anyway
							$fields[ $search ] = $this->sess['search']['q']['keywords'];
						}
					}
					else
					{
						// We have an everywhere marker.
						// this overrides it all. Bail the whole thing
						$everything_override = TRUE;
					}
				}

				if ( !$everything_override )
				{
					$this->sess['search']['q']['search_in'] = $fields;

					$preload_fields	= TRUE;

					unset( $this->sess['search']['q']['keywords'] );
				}
			}
		}

		// -------------------------------------
		//	Prep fields
		// -------------------------------------

		if ( isset( $q['field'] ) === TRUE )
		{
			$preload_fields	= TRUE;

			foreach ( $q['field'] as $field => $val )
			{
				if ( $val == '' OR $field == '' ) continue;
				$fields[$field] = $this->_prep_keywords( $val );
			}

			if ( ! empty( $fields ) )
			{
				$this->sess['search']['q']['field']	= $fields;
			}
		}


		// -------------------------------------
		//	Prep exact fields
		// -------------------------------------

		if ( isset( $q['exactfield'] ) === TRUE )
		{
			$preload_fields	= TRUE;

			$exactfields	= array();
			$temp			= array();

			foreach ( $q['exactfield'] as $field => $val )
			{
				if ( $val == '' ) continue;

				if ( is_array( $val ) === TRUE )
				{
					foreach ( $val as $v )
					{
						if ( strpos( $v, $this->negatemarker ) === FALSE )
						{
							$exactfields[$field]['or'][] = trim( $v, '"' );	// We strip quotes out
						}
						else
						{
							$exactfields[$field]['not'][] = trim( str_replace( $this->negatemarker, '', $v ), '"' );	// We strip quotes out
						}
					}
				}
				else
				{
					if ( strpos( $val, $this->doubleampmarker ) !== FALSE )
					{
						if ( strpos( $val, $this->doubleampmarker . '-' ) !== FALSE )
						{
							$val	= str_replace( $this->doubleampmarker . '-', $this->doubleampmarker . $this->negatemarker, $val );
						}

						$temp	= explode( $this->doubleampmarker, trim( $val, '"' ) );	// We strip quotes out
					}
					elseif ( strpos( $val, $this->negatemarker ) !== FALSE )
					{
						$exactfields[$field]['not'][] = trim( str_replace( $this->negatemarker, '', $val ), '"' );	// We strip quotes out
					}
					else
					{
						$exactfields[$field]['or'][] = trim( $val, '"' );	// We strip quotes out
					}
				}

				// -------------------------------------
				//	We don't use the conjoin capability in the module proper, but there are extensions that might like to know when someone is doing a conjoined exact field search. Playa, for example, which is supported in the native Super Search extension.
				// -------------------------------------

				if ( ! empty( $temp ) )
				{
					foreach ( $temp as $t )
					{
						if ( strpos( $t, $this->negatemarker ) !== FALSE )
						{
							$exactfields[$field]['not'][] = trim( str_replace( $this->negatemarker, '', $t ), '"' );	// We strip quotes out
						}
						else
						{
							$exactfields[$field]['and'][] = trim( $t, '"' );	// We strip quotes out
						}
					}
				}
			}

			$this->sess['search']['q']['exactfield']	= $exactfields;
		}

		// -------------------------------------
		//	Prep empty fields
		// -------------------------------------
		//	People can search for fields whose value is empty.
		// -------------------------------------

		if ( isset( $q['empty'] ) === TRUE )
		{
			$preload_fields	= TRUE;

			$emptyfields	= array();

			foreach ( $q['empty'] as $field => $val )
			{
				if ( $val == '' OR strpos( $val, $this->spaces ) !== FALSE ) continue;
				$emptyfields[$field]['and'] = $val;
			}

			$emptyfields	= $this->_remove_empties( $emptyfields );

			asort( $emptyfields );
			$emptyfields	= $this->_clean_numeric_fields( $emptyfields );
			$this->sess['search']['q']['empty']	= $emptyfields;
		}

		// -------------------------------------
		//	Prep from fields
		// -------------------------------------
		//	People can search for fields whose value is greater than or equal to a value.
		// -------------------------------------

		if ( isset( $q['from'] ) === TRUE )
		{
			$preload_fields	= TRUE;

			$fromfields	= array();

			foreach ( $q['from'] as $field => $val )
			{
				if ( $val == '' OR strpos( $val, $this->spaces ) !== FALSE ) continue;
				$fromfields[$field]['and'] = $val;
			}

			$fromfields	= $this->_remove_empties( $fromfields );

			asort( $fromfields );
			$fromfields	= $this->_clean_numeric_fields( $fromfields );
			$this->sess['search']['q']['from']	= $fromfields;
		}

		// -------------------------------------
		//	Prep to fields
		// -------------------------------------
		//	People can search for fields whose value is less than or equal to a value.
		// -------------------------------------

		if ( isset( $q['to'] ) === TRUE )
		{
			$preload_fields	= TRUE;

			$tofields	= array();

			foreach ( $q['to'] as $field => $val )
			{
				if ( $val == '' OR strpos( $val, $this->spaces ) !== FALSE ) continue;
				$tofields[$field]['and'] = $val;
			}

			$tofields	= $this->_remove_empties( $tofields );

			asort( $tofields );
			$tofields	= $this->_clean_numeric_fields( $tofields );
			$this->sess['search']['q']['to']	= $tofields;
		}

		// -------------------------------------
		//	Preload field data
		// -------------------------------------
		//	We need to have custom field data available for the extension call we will make down below.
		// -------------------------------------

		if ( ! empty( $preload_fields ) )
		{
			$this->_fields( 'searchable', $this->EE->TMPL->site_ids );
		}

		// -------------------------------------
		//	Prep 'from date'
		// -------------------------------------
		//	People can search for entries from a certain date. 20090601 = June 1, 2009. 20090601053020 = 5:30 am and 20 seconds June 1, 2009.
		// -------------------------------------

		if ( isset( $q['datefrom'] ) === TRUE )
		{
			$this->sess['search']['q']['datefrom']	= $q['datefrom'];
		}

		// -------------------------------------
		//	Prep 'to date'
		// -------------------------------------
		//	People can search for entries to a certain date. 20090601 = June 1, 2009. 20090601053020 = 5:30 am and 20 seconds June 1, 2009.
		// -------------------------------------

		if ( isset( $q['dateto'] ) === TRUE )
		{
			$this->sess['search']['q']['dateto']	= $q['dateto'];
		}

		// -------------------------------------
		//	Prep 'expiry from date'
		// -------------------------------------
		//	People can search for entries from a certain date. 20090601 = June 1, 2009. 20090601053020 = 5:30 am and 20 seconds June 1, 2009, on their expiration dates
		// -------------------------------------

		if ( isset( $q['expiry_datefrom'] ) === TRUE )
		{
			$this->sess['search']['q']['expiry_datefrom']	= $q['expiry_datefrom'];
		}

		// -------------------------------------
		//	Prep 'expriy to date'
		// -------------------------------------
		//	People can search for entries to a certain date. 20090601 = June 1, 2009. 20090601053020 = 5:30 am and 20 seconds June 1, 2009, on their expiration dates
		// -------------------------------------

		if ( isset( $q['expiry_dateto'] ) === TRUE )
		{
			$this->sess['search']['q']['expiry_dateto']	= $q['expiry_dateto'];
		}

		// -------------------------------------
		//	Prep categories
		// -------------------------------------

		if ( isset( $q['category'] ) === TRUE )
		{
			if ( isset( $this->sess['uri']['inclusive_categories'] ) AND $this->check_yes($this->sess['uri']['inclusive_categories']) )
			{
				$this->inclusive_categories = TRUE;
			}
			elseif (isset( $this->sess['uri']['inclusive_categories'] ) AND $this->check_no($this->sess['uri']['inclusive_categories']) )
			{
				$this->inclusive_categories = FALSE;
			}

			$this->sess['search']['q']['category']	= $this->_prep_keywords( $q['category'], $this->inclusive_categories, 'category' );
		}

		// -------------------------------------
		//	Prep loose categories
		// -------------------------------------

		//	We're depracting catgeorylike in favor of category-like.
		if ( isset( $q['categorylike'] ) === TRUE AND isset( $q['category-like'] ) === FALSE )
		{
			$q['category-like']	= $q['categorylike'];
		}

		if ( isset( $q['category_like'] ) === TRUE AND isset( $q['category-like'] ) === FALSE )
		{
			$q['category-like']	= $q['category_like'];
		}

		if ( isset( $q['category-like'] ) === TRUE )
		{
			$this->sess['search']['q']['category-like']	= $this->_prep_keywords( $q['category-like'] );
		}

		// -------------------------------------
		//	Prep author
		// -------------------------------------

		if ( isset( $q['author'] ) === TRUE )
		{
			$this->sess['search']['q']['author']	= $this->_prep_keywords( $q['author'] );
		}

		// -------------------------------------
		//	Prep member group
		// -------------------------------------

		if ( isset( $q['group'] ) === TRUE )
		{
			$this->sess['search']['q']['group']	= $this->_prep_keywords( $q['group'] );
		}

		// -------------------------------------
		//	'super_search_extra_basic_fields' hook.
		// -------------------------------------
		//	We call this multiple times so that people can have $$ recognize extra arguments in template params and uri.
		// -------------------------------------

		if ($this->EE->extensions->active_hook('super_search_extra_basic_fields') === TRUE)
		{
			$basic_fields = $this->EE->extensions->universal_call('super_search_extra_basic_fields', $this);

			foreach ( $basic_fields as $bf )
			{
				if ( isset( $q[ $bf ] ) === TRUE )
				{
					$this->sess['search']['q'][ $bf ]	= $this->_prep_keywords( $q[ $bf ] );
				}
			}
		}

		// -------------------------------------
		//	Prep include entry ids
		// -------------------------------------

		if ( isset( $q['include_entry_ids'] ) === TRUE )
		{
			$include_entry_ids	= $this->_only_numeric( explode( '|', $q['include_entry_ids'] ) );
			sort( $include_entry_ids );
			$this->sess['search']['q']['include_entry_ids']	= $include_entry_ids;
		}

		// -------------------------------------
		//	Prep exclude entry ids
		// -------------------------------------

		if ( isset( $q['exclude_entry_ids'] ) === TRUE )
		{
			$exclude_entry_ids	= $this->_only_numeric( explode( '|', $q['exclude_entry_ids'] ) );
			sort( $exclude_entry_ids );
			$this->sess['search']['q']['exclude_entry_ids']	= $exclude_entry_ids;
		}

		// -------------------------------------
		//	Exclude entries already found in previous calls to Super Search during the same session.
		// -------------------------------------

		if ( isset( $q['allow_repeats'] ) === TRUE AND $q['allow_repeats'] == 'no' AND ! empty( $this->sess['previous_entries'] ) )
		{
			$previous_entries	= $this->_only_numeric( $this->sess['previous_entries'] );

			sort( $previous_entries );
			$this->sess['search']['q']['previous_entries']	= $previous_entries;

			if ( ! empty( $this->sess['search']['q']['exclude_entry_ids'] ) )
			{
				$this->sess['search']['q']['exclude_entry_ids']	= array_merge( $this->sess['search']['q']['exclude_entry_ids'], $previous_entries );
			}
			else
			{
				$this->sess['search']['q']['exclude_entry_ids']	= $previous_entries;
			}
		}

		// -------------------------------------
		//	'super_search_alter_search' hook.
		// -------------------------------------
		//	All the arguments are saved to the $this->EE->session->cache, read and write to that array.
		// -------------------------------------

		if ($this->EE->extensions->active_hook('super_search_alter_search') === TRUE)
		{
			$edata = $this->EE->extensions->call('super_search_alter_search', $this);
			if ($this->EE->extensions->end_script === TRUE) return FALSE;
		}

		$this->EE->TMPL->log_item( 'Super Search: Ending prep query('.(microtime(TRUE) - $t).')' );

		// -------------------------------------
		//	Do we have a valid search?
		// -------------------------------------

		if ( ( $this->sess( 'search', 'q' ) ) === FALSE )
		{
			return FALSE;
		}

		// -------------------------------------
		//	Prep order here so that it's part of the cache
		// -------------------------------------

		if ( ( $neworder = $this->sess( 'uri', 'orderby') ) !== FALSE )
		{
			$order	= $this->_prep_order( $neworder );
		}
		elseif ( ( $neworder = $this->sess( 'uri', 'order') ) !== FALSE )
		{
			$order	= $this->_prep_order( $neworder );
		}
		elseif ( $this->EE->TMPL->fetch_param('orderby') !== FALSE AND $this->EE->TMPL->fetch_param('orderby') != '' )
		{
			$order	= $this->_prep_order( $this->EE->TMPL->fetch_param('orderby') );
		}
		elseif ( $this->EE->TMPL->fetch_param('order') !== FALSE AND $this->EE->TMPL->fetch_param('order') != '' )
		{
			$order	= $this->_prep_order( $this->EE->TMPL->fetch_param('order') );
		}
		else
		{
			$order	= $this->_prep_order();
		}

		$this->sess['search']['q']['order']	= $order;

		// -------------------------------------
		//	Prep relevance to be part of cache as well
		// -------------------------------------

		$this->sess['search']['q']['relevance'] = $this->_prep_relevance();

		$this->sess['search']['q']['relevance_multiplier'] = $this->_prep_relevance_multiplier();

		$this->sess['search']['q']['relevance_proximity'] = $this->_prep_relevance_proximity( $q );

		$this->sess['search']['q']['fuzzy_weight'] = $this->_prep_fuzzy_weight( $q );

		// -------------------------------------
		//	Cached?
		// -------------------------------------

		if ( ( $ids = $this->_cached( $this->_hash_it( $this->sess( 'search' ) ) ) ) !== FALSE )
		{
			$this->EE->TMPL->log_item( 'Super Search: Ending cached do_search('.(microtime(TRUE) - $t).')' );

			if ( empty( $ids ) === TRUE )
			{
				return FALSE;
			}

			if ( is_string( $ids ) === TRUE )
			{
				$ids	= explode( "|", $ids );
			}

		}

		// -------------------------------------
		//	Are we working with categories?
		// -------------------------------------

		if ( ! empty( $this->sess['search']['q']['category'] ) )
		{
			if ( ( $tempids = $this->_get_ids_by_category( $this->sess['search']['q']['category'] ) ) !== FALSE )
			{
				$subids	= array_merge( $subids, $tempids );
			}

			// -------------------------------------
			//	Test category conditions
			// -------------------------------------

			if ( empty( $tempids ) )
			{
				$this->_cache( $this->sess( 'search' ), '', 0 );
				return FALSE;
			}
		}

		// -------------------------------------
		//	Are we working with loose categories?
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['category-like'] ) === FALSE )
		{
			if ( ( $tempids = $this->_get_ids_by_category_like( $this->sess['search']['q']['category-like'] ) ) !== FALSE )
			{
				$subids	= array_merge( $subids, $tempids );
			}

			// -------------------------------------
			//	Test category conditions
			// -------------------------------------
			//	If we're checking for categories with either 'or' or 'and' and we receive nothing back, we have to fail here.
			// -------------------------------------

			if ( empty( $tempids ) === TRUE )
			{
				$this->_cache( $this->sess( 'search' ), '', 0 );
				return FALSE;
			}
		}

		// -------------------------------------
		//	Are we looking for authors?
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['author'] ) === FALSE )
		{
			// -------------------------------------
			//	No authors?
			// -------------------------------------
			//	If we were looking for authors and we found none in the DB by the names provided, we have to fail out right here.
			// -------------------------------------

			if ( ( $author = $this->_prep_author( $this->sess['search']['q']['author'] ) ) === FALSE )
			{
				$this->_cache( $this->sess( 'search' ), '', 0 );
				return FALSE;
			}

			$and[]	= 't.author_id IN ('.implode( ',', $author ).')';
		}

		// -------------------------------------
		//	Are we looking for member groups?
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['group'] ) === FALSE )
		{
			// -------------------------------------
			//	No groups?
			// -------------------------------------
			//	If we were looking for groups and we found none in the DB by the names provided, we have to fail out right here.
			// -------------------------------------

			if ( ( $group = $this->_prep_group( $this->sess['search']['q']['group'] ) ) === FALSE )
			{
				$this->_cache( $this->sess( 'search' ), '', 0 );
				return FALSE;
			}

			$and[]	= 't.author_id IN ('.implode( ',', $group ).')';
		}

		// -------------------------------------
		//	Are we looking to include entry ids?
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['include_entry_ids'] ) === FALSE )
		{
			$and[]	= 't.entry_id IN ('.implode( ',', $this->sess['search']['q']['include_entry_ids'] ).')';
		}

		// -------------------------------------
		//	Are we looking to exclude entry ids?
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['exclude_entry_ids'] ) === FALSE )
		{
			$and[]	= 't.entry_id NOT IN ('.implode( ',', $this->sess['search']['q']['exclude_entry_ids'] ).')';
		}

		// -------------------------------------
		//	Prep status
		// -------------------------------------

		$force_status	= TRUE;

		if ( ! empty( $this->sess['search']['q']['status'] ) )
		{
			if ( ( $temp = $this->_prep_sql( 'not', 't.status', $this->sess['search']['q']['status'], 'exact' ) ) !== FALSE )
			{
				$force_status	= FALSE;
				$not[]	= $temp;
			}

			if ( ( $temp = $this->_prep_sql( 'or', 't.status', $this->sess['search']['q']['status'], 'exact' ) ) !== FALSE )
			{
				$force_status	= FALSE;
				$and[]	= $temp;
			}
		}

		if ( $force_status === TRUE )
		{
			$and[]	= 't.status = \'open\'';
		}

		// -------------------------------------
		//	Prep keyword search
		// -------------------------------------

		if ( ! empty( $this->sess['search']['q']['keywords'] ) )
		{
			// -------------------------------------
			//	Set a variable to control exact keyword searching. search-words-within-words set to no overrides default behavior and requires that searches return only for exact words not their conjugates.
			// -------------------------------------

			$exact	= 'notexact';

			if ( isset( $q['where'] ) === TRUE AND $q['where'] == 'exact' )
			{
				$exact	= 'exact';
			}

			if ( ( isset( $q['search-words-within-words'] ) === TRUE AND $this->check_no( $q['search-words-within-words'] ) === TRUE )
				OR ( isset( $q['search_words_within_words'] ) === TRUE AND $this->check_no( $q['search_words_within_words'] ) === TRUE )
				OR ( isset( $q['where'] ) === TRUE AND $q['where'] == 'word' ) )
			{
				$exact	= 'no-search-words-within-words';
			}

			// -------------------------------------
			//	Prep title for keyword search
			// -------------------------------------

			if ( ( $temp = $this->_prep_sql( 'not', 't.title', $this->sess['search']['q']['keywords'], $exact ) ) !== FALSE )
			{
				$not[]	= $temp;
			}

			if ( ( $temp = $this->_prep_sql( 'or', 't.title', $this->sess['search']['q']['keywords'],  $exact) ) !== FALSE )
			{
				$or[]	= $temp;
			}

			// -------------------------------------
			//	Prep author's screen_name for keyword search
			// -------------------------------------

			if ( isset( $this->sess['uri']['keyword_search_author_name'] ) AND $this->check_yes( $this->sess['uri']['keyword_search_author_name'] ) )
			{
				if ( ( $temp = $this->_prep_sql( 'not', 'm.screen_name', $this->sess['search']['q']['keywords'], $exact ) ) !== FALSE )
				{
					$not[]	= $temp;
				}

				if ( ( $temp = $this->_prep_sql( 'or', 'm.screen_name', $this->sess['search']['q']['keywords'],  $exact) ) !== FALSE )
				{
					$or[]	= $temp;
				}

				if ( ( $temp = $this->_prep_sql( 'and', 'm.screen_name', $this->sess['search']['q']['keywords'],  $exact) ) !== FALSE )
				{
					// This is special.
					// In the case we're doing inclusive searches, we actually want the category name searching to be part of the or search set
					$and_special[]	= $temp;
				}
			}

			// -------------------------------------
			//	Prep post's category name for category fuzzy search on keywords
			// -------------------------------------

			if ( isset( $this->sess['uri']['keyword_search_category_name'] ) AND $this->check_yes( $this->sess['uri']['keyword_search_category_name'] ) )
			{
				if ( ( $temp = $this->_prep_sql( 'not', 'cat.cat_name', $this->sess['search']['q']['keywords'], $exact ) ) !== FALSE )
				{
					$not[]	= $temp;
				}
				if ( ( $temp = $this->_prep_sql( 'or', 'cat.cat_name', $this->sess['search']['q']['keywords'],  $exact) ) !== FALSE )
				{
					$or[]	= $temp;
				}

				if ( ( $temp = $this->_prep_sql( 'and', 'cat.cat_name', $this->sess['search']['q']['keywords'],  $exact) ) !== FALSE )
				{
					// This is special.
					// In the case we're doing inclusive searches, we actually want the category name searching to be part of the or search set
					$and_special[]	= $temp;
				}
			}

			// -------------------------------------
			//	Prep custom fields for keyword search
			// -------------------------------------

			if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE )
			{
				foreach ( $customfields as $key => $val )
				{
					if ( is_numeric( $val ) === FALSE ) continue;

					//Handle the case of duplicate channel names across MSM sites
					if ( array_key_exists( 'supersearch_msm_duplicate_fields', $customfields) AND  array_key_exists( $key , $customfields['supersearch_msm_duplicate_fields'] ) )
					{
						//we have the duplicate field name/channel name case to handle
						foreach( $customfields['supersearch_msm_duplicate_fields'][$key] AS $subkey => $subval )
						{
							if ( ( $temp = $this->_prep_sql( 'not', 'cd.field_id_'.$subval, $this->sess['search']['q']['keywords'], $exact, $subval, $key ) ) !== FALSE )
							{
								$not[]	= $temp;
							}

							if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$subval, $this->sess['search']['q']['keywords'], $exact, $subval, $key ) ) !== FALSE )
							{
								$or[]	= $temp;
							}
						}
					}
					else
					{
						if ( ( $temp = $this->_prep_sql( 'not', 'cd.field_id_'.$val, $this->sess['search']['q']['keywords'], $exact, $val, $key ) ) !== FALSE )
						{
							$not[]	= $temp;
						}

						if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$val, $this->sess['search']['q']['keywords'], $exact, $val, $key ) ) !== FALSE )
						{
							$or[]	= $temp;
						}
					}
				}
			}
		}

		// -------------------------------------
		//	Prep for conjoined keyword search
		// -------------------------------------

		if ( ! empty( $this->sess['search']['q']['keywords']['and'] ) )
		{
			foreach ( $this->sess['search']['q']['keywords']['and'] as $keyword )
			{
				$temp_conjoin_arr	= array();

				if ( ( $temp = $this->_prep_sql( 'or', 't.title', array( 'or' => array( $keyword ) ), $exact ) ) !== FALSE )
				{
					$temp_conjoin_arr[]	= $temp;
				}

				// -------------------------------------
				//	Prep custom fields for conjoined keyword search
				// -------------------------------------

				if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE )
				{
					foreach ( $customfields as $key => $val )
					{
						if ( is_numeric( $val ) === FALSE ) continue;

						//Handle the case of duplicate channel names across MSM sites
						if ( array_key_exists( 'supersearch_msm_duplicate_fields', $customfields) AND  array_key_exists( $key , $customfields['supersearch_msm_duplicate_fields'] ) )
						{
							//we have the duplicate field name/channel name case to handle
							foreach( $customfields['supersearch_msm_duplicate_fields'][$key] AS $subkey => $subval )
							{

								if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$subval, array( 'or' => array( $keyword ) ), $exact, $subval, $key ) ) !== FALSE )
								{
									$temp_conjoin_arr[]	= $temp;
								}
							}
						}
						else
						{
							if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$val, array( 'or' => array( $keyword ) ), $exact, $val, $key ) ) !== FALSE )
							{
								$temp_conjoin_arr[]	= $temp;
							}
						}
					}
				}

				// -------------------------------------
				//	Prep for inclusive conjoined fuzzy searches
				// -------------------------------------

				if ( ! empty( $this->sess['search']['q']['keywords']['and_fuzzy'][ $keyword ] ) AND is_array( $this->sess['search']['q']['keywords']['and_fuzzy'][ $keyword ] ) )
				{

					foreach( $this->sess['search']['q']['keywords']['and_fuzzy'][ $keyword ] as $fuzzy_keyword )
					{
						if ( ( $temp = $this->_prep_sql( 'or', 't.title', array( 'or' => array( $fuzzy_keyword ) ), $exact ) ) !== FALSE )
						{
							$temp_conjoin_arr[]	= $temp;
						}

						// -------------------------------------
						//	Prep custom fields for conjoined keyword search
						// -------------------------------------

						if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE )
						{
							foreach ( $customfields as $key => $val )
							{
								if ( is_numeric( $val ) === FALSE ) continue;

								//Handle the case of duplicate channel names across MSM sites
								if ( array_key_exists( 'supersearch_msm_duplicate_fields', $customfields) AND  array_key_exists( $key , $customfields['supersearch_msm_duplicate_fields'] ) )
								{
									//we have the duplicate field name/channel name case to handle
									foreach( $customfields['supersearch_msm_duplicate_fields'][$key] AS $subkey => $subval )
									{

										if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$subval, array( 'or' => array( $fuzzy_keyword ) ), $exact, $subval, $key ) ) !== FALSE )
										{
											$temp_conjoin_arr[]	= $temp;
										}
									}
								}
								else
								{
									if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$val, array( 'or' => array( $fuzzy_keyword ) ), $exact, $val, $key ) ) !== FALSE )
									{
										$temp_conjoin_arr[]	= $temp;
									}
								}
							}
						}
					}
				}

				// -------------------------------------
				//	Prep for inclusive category and author name searches
				// -------------------------------------

				if ( ! empty( $and_special ) )
				{
					$temp_conjoin_arr = array_merge( $temp_conjoin_arr, $and_special );
				}

				$and[]	= '(' . implode( ' OR ', $temp_conjoin_arr ) . ')';
			}
		}

		// -------------------------------------
		//	Prep fields for per-field search
		// -------------------------------------
		//	While in our loop, if we discover that someone is searching on a field that does not exist, we want to return FALSE. We don't want to give them results for bunk searches.
		// -------------------------------------

		if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE AND empty( $this->sess['search']['q']['field'] ) === FALSE )
		{
			// -------------------------------------
			//	Set a variable to control field searching. search-words-within-words set to no overrides default behavior and requires that searches return only for exact words not their conjugates.
			// -------------------------------------

			$exact	= 'notexact';

			if ( isset( $q['where'] ) === TRUE AND $q['where'] == 'exact' )
			{
				$exact	= 'exact';
			}

			if ( ( isset( $q['search-words-within-words'] ) === TRUE AND $this->check_no( $q['search-words-within-words'] ) === TRUE )
			OR ( isset( $q['search_words_within_words'] ) === TRUE AND $this->check_no( $q['search_words_within_words'] ) === TRUE )
			OR ( isset( $q['where'] ) === TRUE AND $q['where'] == 'word' ) )
			{
				$exact	= 'no-search-words-within-words';
			}

			foreach ( $this->sess['search']['q']['field'] as $key => $val )
			{
				if ( empty( $customfields[$key] ) === TRUE ) return FALSE;

				// -------------------------------------
				//	We expect searching on custom fields, but also allow searching on some exp_channel_titles fields.
				// -------------------------------------

				if ( is_numeric( $customfields[$key] ) !== FALSE )
				{
					if ( isset($customfields['supersearch_msm_duplicate_fields'][$key]) )
					{
						//MSM Duplicate key handling

						$temp_and = array();
						$temp_not = array();

						foreach($customfields['supersearch_msm_duplicate_fields'][$key] as $subkey => $subval )
						{

							if ( ( $temp = $this->_prep_sql( 'not', 'cd.field_id_'.$subkey, $val, $exact, $subkey, $key ) ) !== FALSE )
							{
								$temp_not[]	= $temp;
							}

							if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$subkey, $val, $exact, $subkey, $key ) ) !== FALSE )
							{
								$temp_and[]	= $temp;
							}

						}

						//	Collapse the temp and array to be logically complete
						$temp = '(';

						foreach($temp_and as $condition)
						{
							$temp .= $condition;
							$temp .= " OR ";
						}

						$temp .= " 1=0 )";
						$and[] = $temp;


						//	Collapse the temp not array to be logically complete
						$temp = '(';

						foreach($temp_not as $condition)
						{
							$temp .= $condition;
							$temp .= " AND ";
						}

						$temp .= " 1=1 )";
						$not[] = $temp;

					}
					else
					{
						// Standard handling

						if ( ( $temp = $this->_prep_sql( 'not', 'cd.field_id_'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
						{
							$not[]	= $temp;
						}

						if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
						{
							$and[]	= $temp;
						}
					}

					// -------------------------------------
					//	If someone is doing conjoined searching, we detect that and format the MySQL to be super bad.
					// -------------------------------------

					if ( ! empty( $val['and'] ) )
					{
						$temp_conjoin_arr	= array();

						foreach ( $val['and'] as $keyword )
						{
							if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$customfields[$key], array( 'or' => array( $keyword ) ), $exact, $customfields[$key], $key ) ) !== FALSE )
							{
								$temp_conjoin_arr[]	= $temp;
							}
						}

						$and[]	= '(' . implode( ' AND ', $temp_conjoin_arr ) . ')';
					}
				}
				elseif ( in_array( $customfields[$key], $this->searchable_ct ) === TRUE )
				{
					if ( ( $temp = $this->_prep_sql( 'not', 't.'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
					{
						$not[]	= $temp;
					}

					if ( ( $temp = $this->_prep_sql( 'or', 't.'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
					{
						$and[]	= $temp;
					}

					// -------------------------------------
					//	If someone is doing conjoined searching, we detect that and format the MySQL to be super bad.
					// -------------------------------------

					if ( ! empty( $val['and'] ) )
					{
						$temp_conjoin_arr	= array();

						foreach ( $val['and'] as $keyword )
						{
							if ( ( $temp = $this->_prep_sql( 'or', 't.'.$customfields[$key], array( 'or' => array( $keyword ) ), $exact, $customfields[$key], $key ) ) !== FALSE )
							{
								$temp_conjoin_arr[]	= $temp;
							}
						}

						$and[]	= '(' . implode( ' AND ', $temp_conjoin_arr ) . ')';
					}
				}
			}

			unset( $customfields );
		}

		// -------------------------------------
		//	Prep fields for search_in per field search
		// -------------------------------------
		//	While in our loop, if we discover that someone is searching on a field that does not exist, we want to return FALSE. We don't want to give them results for bunk searches.
		// -------------------------------------

		if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE AND empty( $this->sess['search']['q']['search_in'] ) === FALSE )
		{
			// -------------------------------------
			//	Set a variable to control field searching. search-words-within-words set to no overrides default behavior and requires that searches return only for exact words not their conjugates.
			// -------------------------------------

			$exact	= 'notexact';

			if ( isset( $q['where'] ) === TRUE AND $q['where'] == 'exact' )
			{
				$exact	= 'exact';
			}

			if ( ( isset( $q['search-words-within-words'] ) === TRUE AND $this->check_no( $q['search-words-within-words'] ) === TRUE )
				OR ( isset( $q['search_words_within_words'] ) === TRUE AND $this->check_no( $q['search_words_within_words'] ) === TRUE )
				OR ( isset( $q['where'] ) === TRUE AND $q['where'] == 'word' ) )
			{
				$exact	= 'no-search-words-within-words';
			}

			foreach ( $this->sess['search']['q']['search_in'] as $key => $val )
			{
				if ( empty( $customfields[$key] ) === TRUE ) continue;

				// -------------------------------------
				//	We expect searching on custom fields, but also allow searching on some exp_channel_titles fields.
				// -------------------------------------

				if ( is_numeric( $customfields[$key] ) !== FALSE )
				{
					if ( isset($customfields['supersearch_msm_duplicate_fields'][$key]) )
					{
						//MSM Duplicate key handling

						$temp_or  = array();
						$temp_not = array();

						foreach($customfields['supersearch_msm_duplicate_fields'][$key] as $subkey => $subval )
						{

							if ( ( $temp = $this->_prep_sql( 'not', 'cd.field_id_'.$subkey, $val, $exact, $subkey, $key ) ) !== FALSE )
							{
								$temp_not[]	= $temp;
							}

							if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$subkey, $val, $exact, $subkey, $key ) ) !== FALSE )
							{
								$temp_or[]	= $temp;
							}

						}

						//Collapse the temp and array to be logically complete
						$temp = '(';

						foreach($temp_or as $condition)
						{
							$temp .= $condition;
							$temp .= " OR ";
						}

						$temp .= " 1=0 )";
						$or[] = $temp;


						//Collapse the temp not array to be logically complete
						$temp = '(';

						foreach($temp_not as $condition)
						{
							$temp .= $condition;
							$temp .= " AND ";
						}

						$temp .= " 1=1 )";
						$not[] = $temp;

					}
					else
					{
						//Standard handling

						if ( ( $temp = $this->_prep_sql( 'not', 'cd.field_id_'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
						{
							$not[]	= $temp;
						}

						if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
						{
							$or[]	= $temp;
						}
					}

					// -------------------------------------
					//	If someone is doing conjoined searching, we detect that and format the MySQL to be super bad.
					// -------------------------------------

					if ( ! empty( $val['and'] ) )
					{
						$temp_conjoin_arr	= array();

						foreach ( $val['and'] as $keyword )
						{
							if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$customfields[$key], array( 'or' => array( $keyword ) ), $exact, $customfields[$key], $key ) ) !== FALSE )
							{
								$temp_conjoin_arr[]	= $temp;
							}
						}

						$or[]	= '(' . implode( ' AND ', $temp_conjoin_arr ) . ')';
					}
				}
				elseif ( in_array( $customfields[$key], $this->searchable_ct ) === TRUE )
				{
					if ( ( $temp = $this->_prep_sql( 'not', 't.'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
					{
						$not[]	= $temp;
					}

					if ( ( $temp = $this->_prep_sql( 'or', 't.'.$customfields[$key], $val, $exact, $customfields[$key], $key ) ) !== FALSE )
					{
						$or[]	= $temp;
					}

					// -------------------------------------
					//	If someone is doing conjoined searching, we detect that and format the MySQL to be super bad.
					// -------------------------------------

					if ( ! empty( $val['and'] ) )
					{
						$temp_conjoin_arr	= array();

						foreach ( $val['and'] as $keyword )
						{
							if ( ( $temp = $this->_prep_sql( 'or', 't.'.$customfields[$key], array( 'or' => array( $keyword ) ), $exact, $customfields[$key], $key ) ) !== FALSE )
							{
								$temp_conjoin_arr[]	= $temp;
							}
						}

						$or[]	= '(' . implode( ' AND ', $temp_conjoin_arr ) . ')';
					}
				}
			}

			unset( $customfields );
		}

		// -------------------------------------
		//	Prep fields for per-field exact search
		// -------------------------------------
		//	While in our loop, if we discover that someone is searching on a field that does not exist, we want to return FALSE. We don't want to give them results for bunk searches.
		// -------------------------------------

		if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE AND empty( $this->sess['search']['q']['exactfield'] ) === FALSE )
		{
			foreach ( $this->sess['search']['q']['exactfield'] as $key => $val )
			{
				if ( empty( $customfields[$key] ) === TRUE ) return FALSE;

				// -------------------------------------
				//	We expect searching on custom fields, but also allow searching on some exp_channel_titles fields.
				// -------------------------------------

				if ( is_numeric( $customfields[$key] ) !== FALSE )
				{
					if ( ( $temp = $this->_prep_sql( 'not', 'cd.field_id_'.$customfields[$key], $val, 'exact', $customfields[$key], $key ) ) !== FALSE )
					{
						$not[]	= $temp;
					}

					if ( ( $temp = $this->_prep_sql( 'or', 'cd.field_id_'.$customfields[$key], $val, 'exact', $customfields[$key], $key ) ) !== FALSE )
					{
						$and[]	= $temp;
					}
				}
				elseif ( in_array( $customfields[$key], $this->searchable_ct ) === TRUE )
				{
					if ( ( $temp = $this->_prep_sql( 'not', 't.'.$customfields[$key], $val, 'exact', $customfields[$key], $key ) ) !== FALSE )
					{
						$not[]	= $temp;
					}

					if ( ( $temp = $this->_prep_sql( 'or', 't.'.$customfields[$key], $val, 'exact', $customfields[$key], $key ) ) !== FALSE )
					{
						$and[]	= $temp;
					}
				}
			}
		}

		// -------------------------------------
		//	Prep fields for empty search
		// -------------------------------------

		if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE AND empty( $this->sess['search']['q']['empty'] ) === FALSE )
		{
			foreach ( $this->sess['search']['q']['empty'] as $key => $val )
			{
				if ( empty( $customfields[$key]['and'] ) === TRUE ) return FALSE;
				if ( is_numeric( $customfields[$key] ) === FALSE ) continue;
				if ( isset( $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] ) === FALSE ) return FALSE;

				$operator	= ( $val['and'] == 'y' ) ? '=': '!=';

				// -------------------------------------
				//	Below you will see that once had this code set up so that if someone submitted a search to return entries where a specific field was empty, the implication would be that only that connected channel should be searched. This means that other entries not having the custom field assigned would also be blocked from showing. We've had a complaint about this. So I am changing behavior for now and we'll see what the response is. mitchell@solspace.com 2010 12 03
				// -------------------------------------

				if ( $this->_get_field_type( $key ) == 'numeric' )
				{
					$and[]	= 'cd.field_id_'.$customfields[$key]." ".$operator." 0 ";
				}
				else
				{
					$and[]	= 'cd.field_id_'.$customfields[$key]." ".$operator." ''";
				}
			}
		}

		// -------------------------------------
		//	Prep fields for greater than search
		// -------------------------------------

		if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE AND empty( $this->sess['search']['q']['from'] ) === FALSE )
		{
			foreach ( $this->sess['search']['q']['from'] as $key => $val )
			{
				if ( empty( $customfields[$key]['and'] ) === TRUE ) return FALSE;
				if ( isset( $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] ) === FALSE ) return FALSE;

				if ( is_numeric( $customfields[$key] ) !== FALSE )
				{
					if ( $this->_get_field_type( $key ) == 'numeric' AND is_numeric( $val['and'] ) === TRUE )
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." >= " . $val['and'] . $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] . ')';
					}
					else
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." >= '" . $val['and'] . "'" . $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] . ")";
						$and[]	= '(cd.field_id_'.$customfields[$key]." != ''" . $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] . ")";
					}
				}
				elseif ( in_array( $customfields[$key], $this->searchable_ct ) === TRUE )
				{
					$and[]	= 't.'.$customfields[$key]." >= '" . $val['and'] . "'";
					$and[]	= 't.'.$customfields[$key]." != ''";
				}
			}
		}

		// -------------------------------------
		//	Prep fields for less than search
		// -------------------------------------

		if ( ( $customfields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE AND empty( $this->sess['search']['q']['to'] ) === FALSE )
		{
			foreach ( $this->sess['search']['q']['to'] as $key => $val )
			{
				if ( empty( $customfields[$key]['and'] ) === TRUE ) return FALSE;
				if ( isset( $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] ) === FALSE ) return FALSE;

				if ( is_numeric( $customfields[$key] ) !== FALSE )
				{
					if ( $this->_get_field_type( $key ) == 'numeric' AND is_numeric( $val['and'] ) === TRUE )
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." <= ".$val['and'] . $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] . ')';
					}
					else
					{
						$and[]	= '(cd.field_id_'.$customfields[$key]." <= '" . $val['and'] . "'" . $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] . ")";
						$and[]	= '(cd.field_id_'.$customfields[$key]." != ''" . $this->sess['field_to_channel_map_sql'][ $customfields[$key] ] . ")";
					}
				}
				elseif ( in_array( $customfields[$key], $this->searchable_ct ) === TRUE )
				{
					$and[]	= 't.'.$customfields[$key]." <= '" . $val['and'] . "'";
					$and[]	= 't.'.$customfields[$key]." != ''";
				}
			}
		}

		// -------------------------------------
		//	Prep 'from date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['datefrom'] ) === FALSE AND is_numeric( $this->sess['search']['q']['datefrom'] ) === TRUE )
		{
			$and[]	= $this->_parse_date_to_timestamp( $this->sess['search']['q']['datefrom'], 't.entry_date >= ', FALSE );
		}

		// -------------------------------------
		//	Prep 'to date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['dateto'] ) === FALSE AND is_numeric( $this->sess['search']['q']['dateto'] ) === TRUE )
		{
			$and[]	= $this->_parse_date_to_timestamp( $this->sess['search']['q']['dateto'], 't.entry_date <= ', TRUE );
		}

		// -------------------------------------
		//	Prep 'exprity from date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['expiry_datefrom'] ) === FALSE AND is_numeric( $this->sess['search']['q']['expiry_datefrom'] ) === TRUE )
		{
			$and[]	= $this->_parse_date_to_timestamp( $this->sess['search']['q']['expiry_datefrom'], 't.expiration_date >= ', FALSE );
		}

		// -------------------------------------
		//	Prep 'expirty to date' search
		// -------------------------------------
		//	We'll allow simple year indicators, year + month, year + month + day, all the way up to full seconds indicators. The string we expect is additive. All values except year are expected in two digits.
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['expiry_dateto'] ) === FALSE AND is_numeric( $this->sess['search']['q']['expiry_dateto'] ) === TRUE )
		{
			$and[]	= $this->_parse_date_to_timestamp( $this->sess['search']['q']['expiry_dateto'], 't.expiration_date <= ', TRUE );
		}

		// -------------------------------------
		//	Add site ids
		// -------------------------------------

		$and[]	= 't.site_id IN (' . implode( ',', $this->EE->TMPL->site_ids ) . ')';

		// -------------------------------------
		//	Manipulate $and, $not, $or
		// -------------------------------------

		if ($this->EE->extensions->active_hook('super_search_do_search_and_array') === TRUE)
		{
			$arr	= $this->EE->extensions->universal_call( 'super_search_do_search_and_array', $this, array( 'and' => $and, 'not' => $not, 'or' => $or, 'subids' => $subids ) );

			if ($this->EE->extensions->end_script === TRUE)
			{
				$this->_cache( $this->sess( 'search' ), '', 0 );
				return FALSE;
			}

			$and	= ( empty( $arr['and'] ) ) ? $and: $arr['and'];
			$not	= ( empty( $arr['not'] ) ) ? $not: $arr['not'];
			$or		= ( empty( $arr['or'] ) ) ? $or: $arr['or'];
			$subids	= ( empty( $arr['subids'] ) ) ? $subids: $arr['subids'];
		}

		/*echo '<pre>AND';
		print_r( $and );
		echo '<hr />';
		echo 'NOT';
		print_r( $not );
		echo '<hr />';
		echo 'OR';
		print_r( $or );
		echo '<hr />';
		print_r( $where );
		echo '<hr /></pre>';*/

		// -------------------------------------
		//	Anything to query?
		// -------------------------------------

		if ( empty( $and ) === TRUE AND empty( $not ) === TRUE AND empty( $or ) === TRUE AND empty( $subids ) === TRUE )
		{
			$this->_cache( $this->sess( 'search' ), '', 0 );
			return FALSE;
		}

		// -------------------------------------
		//	Ordering by relevance?
		// -------------------------------------
		//	Warning: On large sets of data retrieved from the DB, pulling more than just the entry id will result in memory errors on most shared hosting environments. Therefore, warnings should be issued to users about searching with keywords, particularly short ones, that will return large data sets.
		//	Consider defining a MySQL level function to count and order strings like this: http://forge.mysql.com/tools/tool.php?id=65
		// -------------------------------------

		if ( ( 	!empty( $this->sess['search']['q']['keywords']['or'] )
				OR ! empty( $this->sess['search']['q']['keywords']['and'] ) )
			AND ( isset($this->sess['search']['q']['relevance']) !== FALSE
				  AND !empty( $this->sess['search']['q']['relevance'] ) ) )
		{
			if ( array_key_exists( 'title', $this->sess['search']['q']['relevance'] ) === TRUE )
			{
				$select	.= ', t.title';
			}

			if ( count( $this->sess['search']['q']['relevance'] ) > 0 AND ( $fields = $this->_fields( 'all', $this->EE->TMPL->site_ids ) ) !== FALSE )
			{
				foreach ( $this->sess['search']['q']['relevance'] as $key => $val )
				{
					if ( isset( $fields[$key] ) === TRUE )
					{
						$select	.= ', field_id_'.$fields[$key].' AS `'.$key.'`';
					}
				}
			}
		}

		// -------------------------------------
		//	Weighing relevance by mutliplier fields
		// -------------------------------------
		//	Warning: On large sets of data retrieved from the DB, pulling more than just the entry id will result in memory errors on most shared hosting environments. Therefore, warnings should be issued to users about searching with keywords, particularly short ones, that will return large data sets.
		//	Consider defining a MySQL level function to count and order strings like this: http://forge.mysql.com/tools/tool.php?id=65
		// -------------------------------------

		if ( ( 	!empty( $this->sess['search']['q']['keywords']['or'] )
				OR ! empty( $this->sess['search']['q']['keywords']['and'] ) )
			AND ( isset($this->sess['search']['q']['relevance']) !== FALSE
				  AND !empty( $this->sess['search']['q']['relevance'] ) )
			AND ( isset($this->sess['search']['q']['relevance_multiplier']) !== FALSE
				  AND !empty( $this->sess['search']['q']['relevance_multiplier'] ) ) )
		{
			if ( array_key_exists( 'title', $this->sess['search']['q']['relevance_multiplier'] ) === TRUE )
			{
				$select	.= ', t.title';
				unset( $this->sess['search']['q']['relevance_multiplier']['title'] );
			}

			if ( count( $this->sess['search']['q']['relevance_multiplier'] ) > 0 AND ( $fields = $this->_fields( 'all', $this->EE->TMPL->site_ids ) ) !== FALSE )
			{
				foreach ( $this->sess['search']['q']['relevance_multiplier'] as $key => $val )
				{
					if ( isset( $fields[$key] ) === TRUE )
					{
						$select	.= ', field_id_'.$fields[$key].' AS `'.$key.'`';
					}
				}
			}
		}

		// -------------------------------------
		//	Some assembly required
		// -------------------------------------

		$sql	= $select.$from.$where;

		// -------------------------------------
		//	Continue 'where'
		// -------------------------------------

		// $and[]	= "(/*Begin second OR statement*/((t.title LIKE '%more%')) OR ((cd.field_id_2 LIKE '%more%') AND cd.weblog_id = 1) OR ((cd.field_id_4 LIKE '%more%') AND cd.weblog_id = 2) OR ((cd.field_id_5 LIKE '%more%') AND cd.weblog_id = 2) OR ((cd.field_id_6 LIKE '%more%') AND cd.weblog_id = 2)/*End second OR statement*/)";

		// $and[]	= "(/*Begin third OR statement*/((t.title LIKE '%juice%')) OR ((cd.field_id_2 LIKE '%juice%') AND cd.weblog_id = 1) OR ((cd.field_id_4 LIKE '%juice%') AND cd.weblog_id = 2) OR ((cd.field_id_5 LIKE '%juice%') AND cd.weblog_id = 2) OR ((cd.field_id_6 LIKE '%juice%') AND cd.weblog_id = 2)/*End third OR statement*/)";

		if ( empty( $this->sess['search']['q']['channel_ids'] ) === FALSE )
		{
			$sql	.= ' AND t.' . $this->sc->db->channel_id . ' IN ('.implode( ',', $this->sess['search']['q']['channel_ids'] ).')';
		}

		if ( empty( $and ) === FALSE )
		{
			$sql	.= ' AND '.implode( ' AND ', $and );
		}

		if ( empty( $not ) === FALSE )
		{
			$sql	.= ' AND '.implode( ' AND ', $not );
		}

		if ( empty( $subids ) === FALSE )
		{
			$sql	.= " /*Begin subids statement*/ AND t.entry_id IN ('".implode( "','", $subids )."') /*End subids statement*/ ";
		}

		if ( empty( $or ) === FALSE )
		{
			$sql	.= ' AND (/*Begin OR statement*/'.implode( ' OR ', $or ).'/*End OR statement*/)';
		}

		// -------------------------------------
		//	Add order
		// -------------------------------------

		$sql	.= $order;

		// -------------------------------------
		//	Force limits?
		// -------------------------------------

		if ( isset( $this->sess['search']['q']['keywords']['or'] ) === TRUE )
		{
			$limit	= '';

			foreach ( $this->sess['search']['q']['keywords']['or'] as $k )
			{
				if ( strlen( $k ) < $this->minlength[0] OR in_array( $k, $this->common ) === TRUE )
				{
					$limit = ' LIMIT '.$this->minlength[1];
				}
			}

			$sql	.= $limit;
		}

		// -------------------------------------
		//	Handled Third Party Search Indexes
		// -------------------------------------

		if ( $this->EE->config->item('third_party_search_indexes') != '' )
		{
			// We may have a third party search index to handle

			$new_sql = $sql;

			$have_indexes = FALSE;

			// Loop and replace
			foreach( explode( "|", $this->EE->config->item('third_party_search_indexes')) AS $field_id )
			{
				// Does this field_id exist in our search string?
				$count = 0;

				$new_sql = str_replace( 'cd.'.$field_id, 'ci.'.$field_id, $new_sql, $count );

				if ( $count > 0 ) $have_indexes = TRUE;
			}

			if ( $have_indexes === TRUE )
			{
				$join_str = " LEFT JOIN exp_super_search_indexes ci ON ci.entry_id = t.entry_id ";

				$new_sql = str_replace( '%indexes%', $join_str , $new_sql );
			}
			else
			{
				// Clean up our %indexes% marker
				$new_sql = str_replace( '%indexes%', '', $new_sql );
			}

			$sql = $new_sql;
		}
		else
		{
			// Clean up our %indexes% marker
			$sql = str_replace( '%indexes%', '', $sql );
		}

		// -------------------------------------
		// Do we have any regex searches - new in 2.0?
		// if we do, we need to be extra careful with
		// our db query. They could be passing a bad
		// regex, and that'll CRASH EVERYTHING.
		// so, at the cost of an extra query,
		// we can protect ourselves from it.
		// -------------------------------------

		$query_checked = NULL;

		if ( $this->has_regex )
		{
			$query_checked = $this->data->check_sql( $sql );

			if ( $query_checked === FALSE )
			{
				// Whoa there bad boy.

				// This is a bad sql string.
				// return false rather than running it.
				// Maybe they're doing something they shouldn't be

				return FALSE;
			}
		}

		//	Print_r the master $sql
		// print_r( $sql );

		// If we've already run this query don't repeat ourselves
		if ( $query_checked !== NULL )
		{
			$query = $query_checked;
		}
		else
		{
			// -------------------------------------
			//	Hit the DB - this is the core SuperSearch query
			// -------------------------------------

			$query	= $this->EE->db->query( $sql );
		}

		$this->sess['results']	= $query->num_rows();

		if ( $query->num_rows() == 0 AND $this->EE->extensions->active_hook('super_search_alter_ids') === FALSE )
		{
			$this->_cache( $this->sess( 'search' ), '', 0 );

			return FALSE;
		}

		// -------------------------------------
		//	Ordering by relevance?
		// -------------------------------------
		//	We load the entry ids into an array. We group them by their relevance count. Then within that grouping, we let them retain their order based on the other supplied order params from elsewhere. The we sort on the relevance key. Then we loop the ids back out into a normal array and hand that off to $$ to do with it what it will. This causes entries sharing a given relevance count to not lose their secondary and tertiary sorting.
		// -------------------------------------

		$ids	= array();

		if ( ( 	!empty( $this->sess['search']['q']['keywords']['or'] )
				OR ! empty( $this->sess['search']['q']['keywords']['and'] ) )
			AND ( isset($this->sess['search']['q']['relevance']) !== FALSE
				  AND !empty( $this->sess['search']['q']['relevance'] ) ) )
		{
			foreach ( $query->result_array() as $row )
			{
				$count	= $this->_relevance_count( $row );

				$rel[ (string) $count ][ $row['entry_id'] ]	= $row['entry_id'];
			}

			if ( ! empty( $rel ) )
			{
				krsort( $rel, SORT_NUMERIC );

				foreach ( $rel as $cnt => $temp )
				{
					$ids	= array_merge( $ids, $temp );
				}

				unset( $rel );
			}
		}
		else
		{
			foreach ( $query->result_array() as $row )
			{
				$ids[]	= $row['entry_id'];
			}
		}

		// -------------------------------------
		//	'super_search_alter_ids' hook.
		// -------------------------------------
		//	Alter the master list of ids.
		// -------------------------------------

		$pure_ids	= array_keys( array_unique( $ids ) );

		if ( $this->EE->extensions->active_hook('super_search_alter_ids') === TRUE )
		{
			$ext_ids = $this->EE->extensions->call( 'super_search_alter_ids', $ids, $this );

			//double bag it
			if (is_array($ext_ids))
			{
				$ids = $ext_ids;
			}

			if ($this->EE->extensions->end_script === TRUE) return FALSE;

			// -------------------------------------
			//	If include_entry_ids has been provided, then only those ids are eligible. Whatever the extension is sending to us must meet that requirement
			// -------------------------------------

			if ( ! empty( $this->sess['search']['q']['include_entry_ids'] ) )
			{
				$ids	= array_intersect( $ids, $this->sess['search']['q']['include_entry_ids'] );

				if ( empty( $ids ) )
				{
					$this->_cache( $this->sess( 'search' ), '', 0 );

					return FALSE;
				}
			}
		}

		// -------------------------------------
		//	Make unique
		// -------------------------------------

		$ids	= array_unique( $ids );

		// print_r( $this->sess );

		// -------------------------------------
		//	'super_search_alter_ids' hook cleanup
		// -------------------------------------
		//	Reorder the list if we've had changes
		// -------------------------------------

		if ( $this->EE->extensions->active_hook('super_search_alter_ids') === TRUE AND $pure_ids != $ids )
		{
			// Some third_party has interferred with our $ids
			// Curse those third_parties!!!
			// If we have an order param in our $q, we need to resort

			if ( isset( $this->sess['search']['q']['order'] ) AND !empty( $this->sess['search']['q']['order'] ) )
			{
				// If we're ordering by relevance we don't really want to reorder
				// again. The hook has fired and we now have a polluted id list
				// We can't reorder it by relevance again as we have no way to
				// calculate the relevance on these new 'alien' ids.
				if ( !( isset($this->sess['search']['q']['relevance']) !== FALSE
				  AND !empty( $this->sess['search']['q']['relevance'] ) ) )
				{
					$query = $this->EE->db->query( "
								SELECT  t.entry_id
									FROM {$this->sc->db->channel_titles} t
									LEFT JOIN {$this->sc->db->channel_data} cd ON t.entry_id = cd.entry_id
									WHERE t.entry_id IN (". implode(',', $ids) . ") "
									. $this->sess['search']['q']['order'] );

					$ids = array();

					foreach( $query->result_array() AS $result )
					{
						$ids[] = $result['entry_id'];
					}
				}
			}
		}

		// -------------------------------------
		//	Save to cache
		// -------------------------------------

		$this->sess['results']	= count( $ids );

		if ( $this->sess['results'] == 0 )
		{
			$this->_cache( $this->sess( 'search' ), '', 0 );

			return FALSE;
		}

		$this->_cache( $this->sess( 'search' ), $ids, $this->sess['results'] );

		// -------------------------------------
		//	Return ids
		// -------------------------------------

		$this->EE->TMPL->log_item( 'Super Search: Ending do_search_ct_cd('.(microtime(TRUE) - $t).' Results '.$query->num_rows().')' );

		return $ids;
	}

	//	End do search

	// -------------------------------------------------------------

	/**
	 * Entries
	 *
	 * @access	public
	 * @return	string
	 */

	function _entries ( $ids = array(), $params = array() )
	{
		$t	= microtime(TRUE);

		$this->EE->TMPL->log_item( 'Super Search: Starting _entries()' );

		// -------------------------------------
		//	Execute?
		// -------------------------------------

		if ( count( $ids ) == 0 ) return FALSE;

		// -------------------------------------
		//	Parse search total
		// -------------------------------------

		$prefix	= $this->either_or($this->EE->TMPL->fetch_param('prefix'), 'super_search_');

		// -------------------------------------
		//	Invoke channel class
		// -------------------------------------

		if ( APP_VER < 2.0 )
		{
			if ( class_exists('Weblog') === FALSE )
			{
				require PATH_THIRD.'/weblog/mod.weblog'.EXT;
			}

			$channel = new Weblog;
		}
		else
		{
			if ( class_exists('Channel') === FALSE )
			{
				require PATH_MOD.'channel/mod.channel'.EXT;
			}

			$channel = new Channel;
		}

		// -------------------------------------
		//  Invoke Pagination for EE 2.4 and Above
		// -------------------------------------

		if (APP_VER >= '2.4.0')
		{
			$this->EE->load->library('pagination');
			$channel->pagination = new Pagination_object('Channel');

			// Used by pagination to determine whether we're coming from the cache
			$channel->pagination->dynamic_sql = FALSE;
		}

		// -------------------------------------
		//	Plant a flag and claim $$ ownership of the $channel object we created for use in the $$ extension in the channel_entries_query_result() method.
		// -------------------------------------

		$channel->is_super_search	= TRUE;

		// -------------------------------------
		//	Invoke typography if necessary
		// -------------------------------------

		if ( APP_VER < 2.0 )
		{
			if ( class_exists('Typography') === FALSE )
			{
				require PATH_CORE.'core.typography'.EXT;
			}

			$channel->TYPE = new Typography;

			if ( isset( $channel->TYPE->convert_curly ) )
			{
				$channel->TYPE->convert_curly	= FALSE;
			}
		}
		else
		{
			$this->EE->load->library('typography');
			$this->EE->typography->initialize();
			$this->EE->typography->convert_curly = FALSE;
		}

		// -------------------------------------
		//	Alias tag params. Template params trump URI params
		// -------------------------------------

		foreach ( array( 'num' => 'limit' ) as $key => $val )
		{
			// -------------------------------------
			//	We prefer to find the array value as a template param
			// -------------------------------------

			if ( ! empty( $this->EE->TMPL->tagparams[ $val ] ) )
			{
				unset( $this->EE->TMPL->tagparams[ $key ] );
			}

			// -------------------------------------
			//	We'll accept the array key as a template param next
			// -------------------------------------

			if ( ! empty( $this->EE->TMPL->tagparams[ $key ] ) )
			{
				$this->EE->TMPL->tagparams[ $val ]	= $this->EE->TMPL->tagparams[ $key ];
			}

			// -------------------------------------
			//	We'll next accept our array val as a URI param
			// -------------------------------------

			if ( ! empty( $this->sess['uri'][ $val ] ) )
			{
				$this->EE->TMPL->tagparams[ $val ]	= $this->sess['uri'][ $val ];
				unset( $this->EE->TMPL->tagparams[ $key ] );
			}

			// -------------------------------------
			//	We'll lastly accept our array key as a URI param
			// -------------------------------------

			if ( ! empty( $this->sess['uri'][ $key ] ) )
			{
				$this->EE->TMPL->tagparams[ $val ]	= $this->sess['uri'][ $key ];
				unset( $this->EE->TMPL->tagparams[ $key ] );
			}
		}

		// -------------------------------------
		//	Force limits?
		// -------------------------------------

		if ( ( $keywords = $this->sess( 'search', 'q', 'keywords', 'or' ) ) !== FALSE )
		{
			$limit	= ( ! empty( $this->EE->TMPL->tagparams['limit'] ) ) ? $this->EE->TMPL->tagparams['limit']: '';

			foreach ( $keywords as $k )
			{
				if ( strlen( $k ) < $this->wminlength[0] )
				{
					if ( $limit > $this->wminlength[1] )
					{
						$limit	= $this->wminlength[1];
					}
				}
			}

			$this->EE->TMPL->tagparams['limit']	= ( count( $ids ) > $limit ) ? $limit: count( $ids );
		}

		// -------------------------------------
		//	Pass params
		// -------------------------------------

		$this->EE->TMPL->tagparams['category']	= '';	// This forces exp:channel:entries to ignore the category param. People can provide a category in the param, but $$ knows what to do with it and should not be bothered by native EE.
		$this->EE->TMPL->tagparams['inclusive']	= '';

		$this->EE->TMPL->tagparams['show_pages']	= 'all';

		$this->EE->TMPL->tagparams['dynamic']	= ( APP_VER < 2.0 ) ? 'off': 'no';

		// -------------------------------------
		//	Force status
		// -------------------------------------
		//	Someone's query could call for a combo of possible statuses. This would return a number of search results greater than that which EE would show if we did not force those same statuses into the status template param.
		// -------------------------------------

		if ( ! empty( $this->sess['search']['q']['status']['not'] ) )
		{
			$this->EE->TMPL->tagparams['status']	= 'not ' . implode( '|', $this->sess['search']['q']['status']['not'] );
		}
		elseif ( ! empty( $this->sess['search']['q']['status']['or'] ) )
		{
			$this->EE->TMPL->tagparams['status']	= implode( '|', $this->sess['search']['q']['status']['or'] );
		}

		// -------------------------------------
		//	Load params
		// -------------------------------------

		foreach ( $params as $key => $val )
		{
			$this->EE->TMPL->tagparams[$key]	= $val;
		}

		// -------------------------------------
		//	Pre-process related data
		// -------------------------------------

		$this->EE->TMPL->tagdata		= $this->EE->TMPL->assign_relationship_data( $this->EE->TMPL->tagdata );
		$this->EE->TMPL->var_single	= array_merge( $this->EE->TMPL->var_single, $this->EE->TMPL->related_markers );

		// -------------------------------------
		//	Execute needed methods
		// -------------------------------------

		if ( APP_VER < 2.0 )
		{
			$channel->fetch_custom_weblog_fields();
		}
		else
		{
			$channel->fetch_custom_channel_fields();
		}

		$channel->fetch_custom_member_fields();

		// -------------------------------------
		//  Pagination Tags Parsed Out
		// -------------------------------------

		if (APP_VER >= '2.4.0')
		{
			$channel->pagination->get_template();
		}
		else
		{
			$channel->fetch_pagination_data();
		}

		// -------------------------------------
		//	Prep pagination
		// -------------------------------------
		//	We like to use the 'offset' (or 'start') param to tell pagination which page we want. EE uses P20 or the like. Let's allow someone to use our 'offset' (or 'start') param in the context of performance off, but only when the standard EE pagination indicator is absent from the QSTR.
		// -------------------------------------

		if ( isset( $this->sess['newuri'] ) === TRUE )
		{
			if ( strpos( $this->sess['newuri'], '/' ) !== FALSE )
			{
				$this->sess['newuri']	= str_replace( '/', $this->slash, $this->sess['newuri'] );
			}

			// -------------------------------------
			//	Exception for people using the 'search' parameter
			// -------------------------------------

			if ( $this->EE->TMPL->fetch_param('search') !== FALSE AND $this->EE->TMPL->fetch_param('search') != '' AND preg_match( '/offset' . $this->separator . '(\d+)?/s', $this->sess['newuri'], $match ) )
			{
				$this->sess['newuri']	= 'search' . $this->parser . 'offset' . $this->separator . $match['1'];
			}

			// -------------------------------------
			//	Force paginate base
			// -------------------------------------

			if ( $this->EE->TMPL->fetch_param('paginate_base') !== FALSE AND $this->EE->TMPL->fetch_param('paginate_base') != '' )
			{
				$this->EE->TMPL->tagparams['paginate_base']	= rtrim( $this->EE->TMPL->fetch_param('paginate_base'), '/' ) . '/' . ltrim( $this->sess['newuri'], '/' );
			}
			else
			{
				// -------------------------------------
				//	If someone is using the template param called 'search' they may not have a full URI saved in sess['olduri'] so we try to fake it. The better approach is for them to use the paginate_base param above.
				// -------------------------------------

				if ( $this->EE->TMPL->fetch_param('search') !== FALSE AND $this->EE->TMPL->fetch_param('search') != '' AND isset( $this->EE->uri->segments[1] ) === TRUE AND strpos( $this->sess['olduri'], $this->EE->uri->segments[1] ) !== 0 )
				{
					$temp[]	= $this->EE->uri->segments[1];

					if ( isset( $this->EE->uri->segments[2] ) === TRUE )
					{
						$temp[]	= $this->EE->uri->segments[2];
					}

					$temp[]	= $this->sess['olduri'];

					$this->sess['olduri']	= implode( '/', $temp );
				}

				// -------------------------------------
				//	Force paginate_base
				// -------------------------------------
				//	If we don't tell EE otherwise, it will try and generate pagination links using what it thinks is the page URI. And when it does, it runs that string through some heavy duty filters that strip out important characters like single and double quotes. We run our own sanitize methods on the URI and need to force our version into the pagination engine, otherwise people's pagination links will have vital data stripped out and they will lose their search filters.
				// -------------------------------------

				$this->EE->TMPL->tagparams['paginate_base']	= $this->_prep_paginate_base();
			}
		}

		//Previous to SuperSearch 1.4 pagination was calculated right here.
		//This didn't take into account any additional search params that were passed as part of the tmpl
		//We've moved it later on, but just incase we ever need to reproduce that behviour, this comment
		//will stand as a memorium for it

		// -------------------------------------
		//	Trim the $ids array down to only what is called for through pagination or our upper limits in order to improve performance.
		// -------------------------------------

		$limit	= ( isset( $limit ) === TRUE AND is_numeric( $limit ) === TRUE ) ? $limit: $this->wminlength[1];
		$start	= ( isset( $start ) === TRUE AND is_numeric( $start ) === TRUE ) ? $start: 0;

		// -------------------------------------
		//	I had to comment this trimming code out. I was trying not to send too many entry ids over to the weblog / channel for parsing. But if I don't send a complete set over, pagination will not be built properly over there. For now, we have to sacrifice performance for correctly functioning pagination. mitchel@solspace.com 2011 03 11
		// -------------------------------------

		// $ids	= array_slice( $ids, $start, $limit );

		// -------------------------------------
		//	Load entry ids into tagparam so that EE will know what to do for us.
		// -------------------------------------

		$this->EE->TMPL->tagparams['fixed_order']	= $this->_cerealize( $ids );

		// -------------------------------------
		//	Grab entry data
		// -------------------------------------

		$channel->build_sql_query();

		if ( $channel->sql == '' )
		{
			return FALSE;
		}

		$channel->query = $this->EE->db->query( $channel->sql );

		if ($channel->query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item( 'Super Search: Ending _entries('.(microtime(TRUE) - $t).')' );
			return FALSE;
		}

		$used_ids	= array();

		// -------------------------------------
		//	Prep relevance
		// -------------------------------------

		$relevance	= $this->_prep_relevance();

		// -------------------------------------
		//	If someone uses the search:body="something" template param, the counts can be thrown off. This conditional is a patch that will catch some of the cases.
		// -------------------------------------

		$search_total	= count( $ids );

		if ( APP_VER < 2.0 )
		{
			$QSTR	= $channel->QSTR;

			if ($channel->total_rows > $search_total) $search_total = $channel->total_rows;

			if ( ! preg_match("#^P(\d+)|/P(\d+)#", $channel->QSTR, $match ) )
			{
				if ( $start = $this->sess( 'uri', 'offset' ) !== FALSE )
				{
					$channel->QSTR	= rtrim( $channel->QSTR, '/' ) . '/P' . $start;
				}
			}
			else
			{
				$start	= ( ! empty( $match['1'] ) ) ? $match['1']: $match['0'];
			}

			$channel->create_pagination( $search_total );

			$channel->QSTR	= $QSTR;
		}
		else
		{
			$QSTR	= $channel->query_string;

			if ( ! preg_match("#^P(\d+)|/P(\d+)#", $channel->query_string, $match ) )
			{
				if ( ( $start = $this->sess( 'uri', 'offset' ) ) !== FALSE )
				{
					$channel->query_string	= rtrim( $channel->query_string, '/' ) . '/P' . $start;
				}
			}
			else
			{
				$start	= ( ! empty( $match['1'] ) ) ? $match['1']: $match['0'];
			}

			if (APP_VER >= '2.4.0')
			{
				// After EE 2.4, it goes after as build() is the one that sets ->total_rows
				if ($channel->pagination->total_rows > $search_total) $search_total = $channel->pagination->total_rows;

				$transfer = array(	'total_pages' 	=> 'total_pages',
									'current_page'	=> 'current_page',
									'offset'		=> 'offset',
									'page_next'		=> 'page_next',
									'page_previous'	=> 'page_previous',
									'page_links'	=> 'pagination_links', // different!
									'total_rows'	=> 'total_rows',
									'per_page'		=> 'per_page');

				foreach($transfer as $from => $to)
				{
					$channel->$to = $channel->pagination->$from;
				}
			}
			else
			{
				// Prior to EE 2.4, it goes before.
				if ($channel->total_rows > $search_total) $search_total = $channel->total_rows;

				$channel->create_pagination($search_total);
			}

			$channel->query_string	= $QSTR;
		}

		//	$channel->build_sql_query() rewrites our total_pages. So we save our version and then reset it after $channel->build_sql_query() runs.
		$total_pages_from_channel	= $channel->total_pages;
		$current_page_from_channel	= $channel->current_page;
		$sites_cache = array();

		if ( strpos( $this->EE->TMPL->template, LD.'super_search_total_results'.RD ) !== FALSE )
		{
			$this->EE->TMPL->template	= str_replace( LD.'super_search_total_results'.RD, $search_total, $this->EE->TMPL->template );
		}

		// -------------------------------------
		//	Inject additional vars
		// -------------------------------------

		$previous_title_letter	= '';

		foreach ( $channel->query->result_array() as $key => $row )
		{
			$used_ids[]							= $row['entry_id'];
			$row['super_search_total_results']	= $search_total;
			$row['super_search_keywords_url']	= '';
			$row['super_search_keywords']		= '';

			if ( ! empty( $this->sess['uri']['keywords'] ) )
			{
				$row['super_search_keywords_url']	= $this->sess['uri']['keywords'];
				$row['super_search_keywords']		= str_replace( $this->spaces, ' ', $this->sess['uri']['keywords'] );
			}

			// -------------------------------------
			//	Prepare relevance count
			// -------------------------------------

			if ( isset( $relevance ) === TRUE AND $relevance !== FALSE )
			{
				$row['relevance_count']	= $this->_relevance_count( $row );
			}
			else
			{
				$row['relevance_count']	= '';
			}

			// -------------------------------------
			//	Prepare auto_path
			// -------------------------------------

			$channel_ids = $this->_channel_ids();

			$path = '';

			if ( isset($channel_ids[ $row [ $this->sc->db->channel_id ] ]) )
			{
				$path = ( empty( $channel_ids[ $row[ $this->sc->db->channel_id ] ]['search_results_url'] ) ) ? $channel_ids[ $row[ $this->sc->db->channel_id ] ][ $this->sc->db->channel_url ] : $channel_ids[ $row[ $this->sc->db->channel_id ] ]['search_results_url'];
			}

			$row['auto_path']	= rtrim( $path, '/') . '/' . $row['url_title'] . '/';

			// -------------------------------------
			//	Highlight keywords in searchable fields
			// -------------------------------------

			foreach( $this->sess['fields']['searchable'] AS $field_name => $field_id )
			{
				//Special handling for the title field
				if ($field_name == 'title' )
				{
					if ( ! empty( $row[$field_name] ) )
					{
						$row['title']	= $this->_highlight_keywords( $row['title'] );
					}

				}
				elseif ( is_numeric( $field_id ) )
				{
					//Handle the case of duplicate channel names across MSM sites
					if ( array_key_exists( 'supersearch_msm_duplicate_fields', $this->sess['fields']['searchable']) AND  array_key_exists( $field_name , $this->sess['fields']['searchable']['supersearch_msm_duplicate_fields'] ) )
					{
						foreach( $this->sess['fields']['searchable']['supersearch_msm_duplicate_fields'][$field_name] AS $subkey )
						{
							if ( ! empty( $row['field_id_'.$subkey] ) )
							{
								$row['field_id_'.$subkey]	= $this->_highlight_keywords( $row['field_id_'.$subkey] );
							}

						}
					}
					else
					{
						if ( ! empty( $row['field_id_'.$field_id] ) )
						{
							$row['field_id_'.$field_id]	= $this->_highlight_keywords( $row['field_id_'.$field_id] );
						}
					}
				}
			}

			// -------------------------------------
			//	Check for excerpt
			// -------------------------------------

			if ( ! empty( $this->sess['search']['channels'][$row[ $this->sc->db->channel_id ]]['search_excerpt'] ) )
			{
				if ( $this->sess['search']['channels'][$row[ $this->sc->db->channel_id ]]['search_excerpt'] != 0 )
				{
					$field_id		= $this->sess['search']['channels'][$row[ $this->sc->db->channel_id ]]['search_excerpt'];

					$excerpt	= strip_tags( $row['field_id_' . $field_id ] );
					$excerpt_before	= trim( preg_replace( "/(\015\012)|(\015)|(\012)/", " ", $excerpt ) );

					// Check our default site setting
					$use_smart_excerpt = ( $this->EE->config->item('enable_smart_excerpt' ) != 'n' ) ? TRUE : FALSE;

					// Let this be overridden from the template
					if ( isset( $this->sess['uri']['smart_excerpt'] ) )
					{
						if ( $this->check_no( $this->sess['uri']['smart_excerpt'] ) )
						{
							$use_smart_excerpt = FALSE;
						}
						elseif ( $this->check_yes( $this->sess['uri']['smart_excerpt'] ) )
						{
							$use_smart_excerpt = TRUE;
						}
						elseif ( $this->sess['uri']['smart_excerpt'] == 'toggle' )
						{
							$use_smart_excerpt = !$use_smart_excerpt;
						}
					}

					if ( $use_smart_excerpt )
					{
						$keywords = ( isset( $this->sess['search']['q']['keywords'] ) ) ? $this->sess['search']['q']['keywords'] : array();

						$excerpt = $this->_smart_excerpt( $excerpt_before, $keywords, 50 );
					}
					else
					{
						$excerpt	= $this->EE->functions->word_limiter( $excerpt_before, 50 );
					}

					if ( APP_VER < 2.0 )
					{
						$field_content = $channel->TYPE->parse_type(
							$excerpt,
							array(
								  'text_format'		=> ( isset( $row[ 'field_ft_' . $field_id ] ) === TRUE ) ? $row[ 'field_ft_' . $field_id ]: 'none',
								  'html_format'		=> ( isset( $channels[$row[ $this->sc->db->channel_id ]] ) === TRUE ) ? $channels[$row[ $this->sc->db->channel_id ]][ $this->sc->channel . '_html_formatting']: 'all',
								  'auto_links'		=> ( isset( $channels[$row[ $this->sc->db->channel_id ]] ) === TRUE ) ? $channels[$row[ $this->sc->db->channel_id ]][ $this->sc->channel . '_auto_link_urls']: 'n',
								  'allow_img_url'	=> ( isset( $channels[ $row[ $this->sc->db->channel_id ] ] ) === TRUE ) ? $channels[ $row[ $this->sc->db->channel_id ]][ $this->sc->channel . '_allow_img_urls' ]: 'y'
								)
						);
					}
					else
					{
						$field_content = $this->EE->typography->parse_type(
							$excerpt,
							array(
								  'text_format'		=> ( isset( $row[ 'field_ft_' . $field_id ] ) === TRUE ) ? $row[ 'field_ft_' . $field_id ]: 'none',
								  'html_format'		=> ( isset( $channels[$row[ $this->sc->db->channel_id ]] ) === TRUE ) ? $channels[$row[ $this->sc->db->channel_id ]][ $this->sc->channel . '_html_formatting']: 'all',
								  'auto_links'		=> ( isset( $channels[$row[ $this->sc->db->channel_id ]] ) === TRUE ) ? $channels[$row[ $this->sc->db->channel_id ]][ $this->sc->channel . '_auto_link_urls']: 'n',
								  'allow_img_url'	=> ( isset( $channels[ $row[ $this->sc->db->channel_id ] ] ) === TRUE ) ? $channels[ $row[ $this->sc->db->channel_id ]][ $this->sc->channel . '_allow_img_urls' ]: 'y'
								)
						);
					}

					// -------------------------------------
					//	Highlight keywords
					// -------------------------------------

					$field_content	= $this->_highlight_keywords( $field_content );

					$row['excerpt']	= $field_content;
				}
			}

			$row['excerpt']	= ( isset( $row['excerpt'] ) === FALSE OR is_string( $row['excerpt'] ) === FALSE ) ? '': $row['excerpt'];	// This patches a problem that I could not find in _highlight_keywords() where sometimes a string would not be returned.

			// -------------------------------------
			//	Add additional MSM values
			// -------------------------------------

			if ( $row['entry_site_id'] == $this->EE->config->item('site_id') )
			{
				$row['entry_site_name'] 		= $this->EE->config->item('site_name');
				$row['entry_site_label'] 		= $this->EE->config->item('site_name');
				$row['entry_site_description'] 	= $this->EE->config->item('site_description');
				$row['entry_site_short_name'] 	= $this->EE->config->item('site_short_name');
				$row['entry_site_url']		 	= $this->EE->config->item('site_url');
			}
			else
			{
				if ( count( $sites_cache ) < 1 )
				{
					// We'll have to dig these details out unfortunately
					$squery = $this->EE->db->query( " SELECT site_id, site_label, site_name, site_description, site_system_preferences FROM exp_sites ");

					foreach( $squery->result_array() as $srow )
					{
						// Decode as appropriate
						if ( APP_VER < 2.0 )
						{
							$srow['site_system_preferences'] = unserialize( $srow['site_system_preferences'] );
						}
						else
						{
							$srow['site_system_preferences'] = unserialize( base64_decode( $srow['site_system_preferences'] ) );
						}

						$sites_cache[ $srow['site_id'] ] = $srow;
					}
				}

				$row['entry_site_name'] 		= $sites_cache[ $row['entry_site_id'] ]['site_label'];
				$row['entry_site_label']		= $sites_cache[ $row['entry_site_id'] ]['site_label'];
				$row['entry_site_description'] 	= $sites_cache[ $row['entry_site_id'] ]['site_description'];
				$row['entry_site_short_name'] 	= $sites_cache[ $row['entry_site_id'] ]['site_name'];
				$row['entry_site_url']		 	= $sites_cache[ $row['entry_site_id'] ]['site_system_preferences']['site_url'];
			}

			// -------------------------------------
			//	Set the first letter variable
			// -------------------------------------

			$row[$prefix.'previous_title_letter']	= $previous_title_letter;
			$row[$prefix.'current_title_letter']	= $previous_title_letter = strtoupper( substr( $row['title'], 0, 1 ) );

			// -------------------------------------
			//	Manipulate $row
			// -------------------------------------

			if ($this->EE->extensions->active_hook('super_search_entries_row_inject') === TRUE)
			{
				$row	= $this->EE->extensions->universal_call( 'super_search_entries_row_inject', $this, $row );
			}

			// -------------------------------------
			//	Reload
			// -------------------------------------

			$channel->query->result[ $key ]	= $row;
		}

		unset( $sites_cache );

		// -------------------------------------
		//	Let's save this channel query object to get around an EE 2 problem with result_array().
		// -------------------------------------

		$this->sess['channel_query_object']	= $channel->query;

		// -------------------------------------
		//	Save ids so that our allow_repeats param will work. This lets is exclude entries from showing again in the same session if we have already retrieved them. This is dependent on the linear parsing order of course. You can't know what a later super search call will retrieve and you don't care. Linear is sufficient.
		// -------------------------------------

		if ( empty( $this->sess['previous_entries'] ) )
		{
			$this->sess['previous_entries']	= array();
		}

		$this->sess['previous_entries']	= array_merge( $this->sess['previous_entries'], array_unique( $used_ids ) );

		// -------------------------------------
		//	Invoke typography if necessary
		// -------------------------------------

		if ( APP_VER < 2.0 )
		{
			if ( class_exists('Typography') === FALSE )
			{
				require PATH_CORE.'core.typography'.EXT;
			}

			$channel->TYPE = new Typography;

			if ( isset( $channel->TYPE->convert_curly ) )
			{
				$channel->TYPE->convert_curly	= FALSE;
			}
		}

		if ( $this->EE->TMPL->fetch_param('disable') === FALSE OR $this->EE->TMPL->fetch_param('disable') == '' OR strpos( $this->EE->TMPL->fetch_param('disable'), 'categories' ) === FALSE )
		{
			$channel->fetch_categories();
		}

		// -------------------------------------
		//	Parse and return entry data
		// -------------------------------------

		if ( APP_VER < 2.0 )
		{
			$channel->parse_weblog_entries();
		}
		else
		{
			$channel->parse_channel_entries();
		}

		// -------------------------------------
		//	Add and correct pagination data
		// -------------------------------------

		foreach ( array( 'pagination_links', 'page_previous', 'page_next' ) as $val )
		{
			$channel->$val	= str_replace( array( ';=', ';-', ';_' ), array( '=', '-', '_' ), $channel->$val );
		}

		if (APP_VER >= '2.4.0')
		{
			$channel->return_data = $channel->pagination->render($channel->return_data);
		}
		else
		{
			$channel->add_pagination_data();
		}

		// -------------------------------------
		//	Related entries
		// -------------------------------------

		// -------------------------------------
		//	Note the trick here with unsetting our little critter variable $channel->is_super_search.
		// -------------------------------------

		unset( $channel->is_super_search );

		if (count($this->EE->TMPL->related_data) > 0 AND count($channel->related_entries) > 0)
		{
			$channel->parse_related_entries();
		}

		// -------------------------------------
		//	Reverse related entries
		// -------------------------------------

		if (count($this->EE->TMPL->reverse_related_data) > 0 AND count($channel->reverse_related_entries) > 0)
		{
			$channel->parse_reverse_related_entries();
		}

		$channel->is_super_search	= TRUE;

		$tagdata = $channel->return_data;

		$this->EE->TMPL->log_item( 'Super Search: Ending _entries('.(microtime(TRUE) - $t).')' );

		return $tagdata;
	}

	//	End entries

	// -------------------------------------------------------------

	/**
	 * Extract vars from query
	 *
	 * @access	private
	 * @return	array
	 */

	 function _extract_vars_from_query( $q = array() )
	 {
		if ( empty( $q ) ) return array();

		$prefix	= 'super_search_';

		if ( function_exists( 'andornot' ) === FALSE )
		{
			function andornot( $q = array() )
			{
				$temp		= array();

				if ( empty( $q ) OR is_array( $q ) === FALSE ) return '';

				foreach ( $q as $key => $arr )
				{
					if ( $key == 'and' AND ! empty( $arr ) )
					{
						if ( is_array( $arr ) === TRUE )
						{
							foreach ($arr as $v )
							{
								// handle the case where we have a search 'phrase' that would have been in "quotes"
								if ( is_string( $v ) === TRUE AND strpos( $v , ' ' ) !== FALSE )
								{
									$v = '&quot;'. $v . '&quot;';
								}

								$temp[]	=	$v;
							}
						}
						else
						{
							// handle the case where we have a search 'phrase' that would have been in "quotes"

							if ( strpos( $arr, ' ' ) !== FALSE )
							{
								$arr = '&quot;'. $arr . '&quot;';
							}

							$temp[]	= $arr;
						}
					}

					if ( $key == 'not' AND ! empty( $arr ) )
					{
						if ( is_array( $arr ) === TRUE )
						{
							$temp[]	= '-' . implode( ' -', $arr );
						}
						else
						{
							$temp[]	= '-' . $arr;
						}
					}

					if ( $key == 'or' AND ! empty( $arr ) )
					{
						if ( is_array( $arr ) === TRUE )
						{
							foreach ($arr as $v )
							{
								//handle the case where we have a search 'phrase' that would have been in "quotes"
								if ( is_string( $v ) === TRUE AND strpos( $v , ' ' ) !== FALSE )
								{
									$v = '&quot;'. $v . '&quot;';
								}

								$temp[]	=	$v;
							}
						}
						else
						{
							if ( strpos( $arr , ' ' ) !== FALSE )
							{
								$arr = '&quot;'. $arr . '&quot;';
							}

							$temp[]	= $arr;
						}
					}
				}

				return implode( ' ', $temp );
			}
		}

		$vars	= array();

		foreach ( $q as $key => $arr )
		{
			if ( empty( $arr ) ) continue;

			if ( in_array( $key, array( 'channel', 'status', 'category' ) ) === TRUE )
			{
				if ( isset( $arr['and'] ) === TRUE )
				{
					foreach ( $arr['and'] as $val )
					{
						$val	= str_replace( ' ', '_', $val );

						$vars[ $prefix . $key . '_' . $val ]	= TRUE;
					}
				}

				if ( isset( $arr['or'] ) === TRUE )
				{
					foreach ( $arr['or'] as $val )
					{
						$val	= str_replace( ' ', '_', $val );

						$vars[ $prefix . $key . '_' . $val ]	= TRUE;
					}
				}

				if ( isset( $arr['not'] ) === TRUE )
				{
					foreach ( $arr['not'] as $val )
					{
						$val	= str_replace( ' ', '_', $val );

						$vars[ $prefix . $key . '_not_' . $val ]	= TRUE;
					}
				}
			}
			elseif ( $key == 'field' )
			{
				foreach ( $arr as $k => $v )
				{
					$vars[$prefix.$k]	= andornot( $v );
				}
			}
			elseif ( $key == 'exactfield' )
			{
				foreach ( $arr as $k => $v )
				{
					// assign both forms
					$vars[$prefix.'exact'.$this->modifier_separator.$k]	= andornot( $v );
					$vars[$prefix.$k.$this->modifier_separator.'exact']	= andornot( $v );
				}
			}
			elseif ( $key == 'empty' )
			{
				foreach ( $arr as $k => $v )
				{
					$vars[$prefix.$k.$this->modifier_separator.'empty']	= andornot( $v );
				}
			}
			elseif ( $key == 'from' )
			{
				foreach ( $arr as $k => $v )
				{
					$vars[$prefix.$k.$this->modifier_separator.'from']	= andornot( $v );
				}
			}
			elseif ( $key == 'to' )
			{
				foreach ( $arr as $k => $v )
				{
					$vars[$prefix.$k.$this->modifier_separator.'to']	= andornot( $v );
				}
			}
			elseif ( $key == 'datefrom' )
			{
				// assign both forms
				$vars[$prefix.'entry_date'.$this->modifier_separator.'from']	= $arr;
				$vars[$prefix.'date'.$this->modifier_separator.'from']	= $arr;
			}
			elseif ( $key == 'dateto' )
			{
				// assign both forms
				$vars[$prefix.'entry_date'.$this->modifier_separator.'to']	= $arr;
				$vars[$prefix.'date'.$this->modifier_separator.'to']	= $arr;
			}
			elseif ( $key == 'expiry_datefrom' )
			{
				$vars[$prefix.'expiry_date'.$this->modifier_separator.'from']	= $arr;
			}
			elseif ( $key == 'expiry_dateto' )
			{
				$vars[$prefix.'expiry_date'.$this->modifier_separator.'to']	= $arr;
			}
			elseif ( $key == 'channel_ids' )
			{
				$vars[$prefix.'channel_ids'] = implode($arr, ' ');
			}
			elseif ( $key == 'site' )
			{
				$vars[$prefix.'site'] = implode($arr, ' ');
			}
			elseif ( $key == 'site_id' )
			{
				$vars[$prefix.'site'] = implode($arr, ' ');
			}
			elseif ( $key == 'keywords_phrase' )
			{
				$vars[$prefix.$key] = $arr;
			}
			elseif ( $key == 'search_in' )
			{
				$vars[$prefix.$key] = implode( "|", array_keys( $arr ) );
			}
			elseif ( $key == 'where' )
			{
				$vars[$prefix.$key] = $arr;
			}
			elseif ( $key == 'partial_author' )
			{
				$vars[$prefix.$key] = $arr;
			}
			elseif ( $key == 'orderby' )
			{
				$vars[$prefix.'order'] = $arr;
				$vars[$prefix.$key] = $arr;
			}
			elseif ( $key == 'order' )
			{
				$vars[$prefix.'orderby'] = $arr;
				$vars[$prefix.$key] = $arr;
			}
			else
			{
				$vars[$prefix.$key]	= andornot( $arr );
			}
		}

		// Override the 'super_search_keywords' value with the _keywords_phrase value
		if ( isset( $vars[ $prefix . 'keywords_phrase' ]) ) $vars[ $prefix . 'keywords' ] = $vars[ $prefix . 'keywords_phrase' ];

		return $vars;
	 }

	//	End extract vars from query

	// -------------------------------------------------------------

	/**
	 * Fields
	 *
	 * We later wrote a channel routine that could speed this up by eliminating the JOIN. Revisit.
	 *
	 * @access	private
	 * @return	array
	 */

	function _fields( $channel = 'searchable', $site_ids = array() )
	{
		if ( empty( $this->sess['search']['q']['channel_ids'] ) AND ( $fields = $this->sess( 'fields', $channel ) ) !== FALSE )
		{
			return $fields;
		}
		elseif ( ! empty( $this->sess['search']['q']['channel_ids'] ) AND ( $fields = $this->sess( 'fields' . md5( implode( '', $this->sess['search']['q']['channel_ids'] ) ), $channel ) ) !== FALSE )
		{
			return $fields;
		}

		if ( empty( $site_ids ) === TRUE )
		{
			if ( is_object( $this->EE->TMPL ) === TRUE )
			{
				$site_ids	= $this->EE->TMPL->site_ids;
			}
			else
			{
				$site_ids	= array( $this->EE->config->item('site_id') );
			}
		}

		$columns	= array(
			'cf.field_id',
			'cf.field_name',
			'cf.field_search',
			'cf.field_type',
			'cf.field_text_direction',
			'c.' . $this->sc->db->channel_id . ' AS channel_id',
			'c.' . $this->sc->db->channel_name . ' AS channel_name'
		);

		// -------------------------------------
		//	Begin SQL
		// -------------------------------------

		$sql	= "/* Super Search get fields */ SELECT " . implode( ',', $columns ) . "
					FROM " . $this->sc->db->fields . " cf
					LEFT JOIN " . $this->sc->db->channels . " c ON c.field_group = cf.group_id
					WHERE cf.site_id IN (".implode( ",", $site_ids ).")
					AND c." . $this->sc->db->channel_id . " != ''";

		// -------------------------------------
		//	Filter out a custom field by the name of keywords? 'keywords' is a reserved word in Super Search. We're going to get into trouble for this one.
		// -------------------------------------

		$sql	.= " AND cf.field_name != 'keywords'";

		// -------------------------------------
		//	Channel id restriction?
		// -------------------------------------

		if ( ( $channel_ids = $this->sess( 'search', 'q', 'channel_ids' ) ) !== FALSE )
		{
			$sql	.= " AND c." . $this->sc->db->channel_id . " IN (" . implode( ',', $channel_ids ) . ")";
		}

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= $this->EE->db->query( $sql );

		$arr						= array();
		$fmt						= array();
		$field_to_channel_map		= array();
		$field_to_channel_map_sql	= array();
		$general_field_data			= array();

		if ( $query->num_rows() > 0 )
		{
			foreach ( $query->result_array() as $row )
			{

				$arr[$row['channel_name']]['title']		= 'title';
				$arr['searchable']['title']				= 'title';
				$fmt[$row['field_name']]				= 'ltr';
				$field_to_channel_map[ 'title' ][ $row['channel_id'] ]	= $row['channel_id'];


				// Handle fields with the same name across MSMs
				if ( isset($arr['all'][$row['field_name']]) )
				{
					if ( $arr['all'][$row['field_name']] != $row['field_id'] )
					{
						if ( !isset( $arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']] ) )
						{
							//is the the first duplicate?
							if ( !isset($arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']]) )
							{
								//move the first field_id, now that we know it has a sibling
								$arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']][$arr['all'][$row['field_name']]] = $arr['all'][$row['field_name']];
							}

							//this field_id is already in the main array
							$arr['all']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']] = $row['field_id'];
						}
					}

				}

				$arr['all'][$row['field_name']]			= $row['field_id'];

				if ( $row['field_search'] == 'y' )
				{
					if ( empty( $channel_ids ) OR in_array( $row['channel_id'], $channel_ids ) === TRUE )
					{
						$arr[$row['channel_name']][$row['field_name']]	= $row['field_id'];
						$fmt[$row['field_name']]						= $row['field_text_direction'];
						$field_to_channel_map[ $row['field_id'] ][ $row['channel_id'] ]	= $row['channel_id'];
						$general_field_data[ $row['field_name'] ]		= $row;


						// Handle fields with the same name across MSMs
						if ( isset($arr['searchable'][$row['field_name']]) )
						{
							if ( $arr['searchable'][$row['field_name']] != $row['field_id'] )
							{
								if ( !isset( $arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']] ) )
								{
									//is the the first duplicate?
									if ( !isset($arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']]) )
									{
										//move the first field_id, now that we know it has a sibling
										$arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']][$arr['searchable'][$row['field_name']]] = $arr['searchable'][$row['field_name']];
									}

									//this field_id is already in the main array
									$arr['searchable']['supersearch_msm_duplicate_fields'][$row['field_name']][$row['field_id']] = $row['field_id'];
								}
							}

						}

						$arr['searchable'][$row['field_name']]			= $row['field_id'];
					}
				}
			}

			if ( ! empty( $this->sess['search']['q']['channel_ids'] ) )
			{
				$this->sess['fields'.md5( implode( '', $this->sess['search']['q']['channel_ids'] ) )]	= $arr;
			}
		}

		// -------------------------------------
		//	Add Gypsy test
		// -------------------------------------
		//	Gypsy is an extension by Brandon Kelly that allows one channel field to be used by multiple channels regardless of whether the field belongs to the field group assigned a given channel or not.
		// -------------------------------------

		if ( ! empty( $this->EE->extensions->version_numbers['Gypsy'] ) )
		{
			// -------------------------------------
			//	Begin SQL
			// -------------------------------------

			$sql	= "/* Super Search get fields */ SELECT
						cf.field_id,
						cf.field_name,
						cf.field_search,
						cf.field_type,
						cf.field_text_direction,
						cf.gypsy_" . $this->sc->channels . " AS gypsy_channel_ids
						FROM " . $this->sc->db->fields . " cf
						WHERE cf.site_id IN (".implode( ",", $site_ids ).")
						AND field_search = 'y'
						AND cf.field_is_gypsy = 'y'
						AND cf.gypsy_" . $this->sc->channels . " != ''";

			// -------------------------------------
			//	Filter out a custom field by the name of keywords? 'keywords' is a reserved word in Super Search. We're going to get into trouble for this one.
			// -------------------------------------

			$sql	.= " AND cf.field_name != 'keywords'";

			// -------------------------------------
			//	Run query
			// -------------------------------------

			$query	= $this->EE->db->query( $sql );

			// -------------------------------------
			//	Set channels
			// -------------------------------------

			$channels	= $this->data->get_channels();

			// -------------------------------------
			//	Loop
			// -------------------------------------

			foreach ( $query->result_array() as $row )
			{
				// -------------------------------------
				//	Prep $arr['all'] and $arr['searchable'] and $fmt[]
				// -------------------------------------

				$arr['all'][$row['field_name']]			= $row['field_id'];
				$fmt[$row['field_name']]				= $row['field_text_direction'];

				// Handle fields with the same name across MSMs
				if (isset($arr['searchable'][$row['field_name']]))
				{
					if (!is_array($arr['searchable'][$row['field_name']]))
					{
						if ($arr['searchable'][$row['field_name']] != $row['field_id'])
						{
							$temp_id = $arr['searchable'][$row['field_name']];
							unset($arr['searchable'][$row['field_name']]);

							$arr['searchable'][$row['field_name']][] = $temp_id;
							$arr['searchable'][$row['field_name']][] = $row['field_id'];

							unset($temp_id);
						}
					}
					else
					{
						if (!in_array($row['field_id'],$arr['searchable'][$row['field_name']]))
						{
							$arr['searchable'][$row['field_name']][] = $row['field_id'];
						}
					}
				}
				else
				{
					$arr['searchable'][$row['field_name']]	= $row['field_id'];
				}

				// -------------------------------------
				//	Break out channel ids from Brandon's clown ass data structure
				// -------------------------------------

				$gypsy_channel_ids	= $this->_remove_empties( preg_split( '/\s+|\|/s', $row['gypsy_channel_ids'] ) );

				// -------------------------------------
				//	Prep $arr['channel_name']
				// -------------------------------------

				foreach ( $gypsy_channel_ids as $id )
				{
					if ( ! empty( $channels[ $id ] ) )
					{
						$arr[ $channels[ $id ]['channel_name'] ][$row['field_name']]	= $row['field_id'];

						$field_to_channel_map[ $row['field_id'] ][ $channels[ $id ]['channel_id'] ]	= $channels[ $id ]['channel_id'];
					}
				}
			}
		}

		// -------------------------------------
		//	Prepare field to channel map
		// -------------------------------------

		foreach ( $field_to_channel_map as $field_id => $temp_channel_ids )
		{
			if ( count( $temp_channel_ids ) > 1 )
			{
				$field_to_channel_map_sql[ $field_id ]	= ' AND cd.' . $this->sc->db->channel_id . ' IN (' . implode( ',', $temp_channel_ids ) . ')';
			}
			elseif ( count( $temp_channel_ids ) == 1 )
			{
				$field_to_channel_map_sql[ $field_id ]	= ' AND cd.' . $this->sc->db->channel_id . ' = ' . implode( '', $temp_channel_ids );
			}
		}

		$this->sess['fields']							= $arr;
		$this->sess['fields_fmt']						= $fmt;
		$this->sess['field_to_channel_map']				= $field_to_channel_map;
		$this->sess['field_to_channel_map_sql']			= $field_to_channel_map_sql;
		$this->sess['general_field_data']['searchable']	= $general_field_data;

		return ( isset( $arr[$channel] ) === TRUE ) ? $arr[$channel]: FALSE;
	}

	//	End _fields


	/**
	 * Statuses
	 *
	 * This is a cleanup function to handle the ambigious nature of passed statuses.
	 *
	 * @access	private
	 * @return	array
	 */

	function _statuses( $site_ids = array() )
	{
		if ( empty( $site_ids ) === TRUE )
		{
			if ( is_object( $this->EE->TMPL ) === TRUE )
			{
				$site_ids	= $this->EE->TMPL->site_ids;
			}
			else
			{
				$site_ids	= array( $this->EE->config->item('site_id') );
			}
		}

		$sql = " SELECT status_id, status, site_id, group_id FROM exp_statuses WHERE site_id IN (".implode( ',', $site_ids ) . ")";

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= $this->EE->db->query( $sql );

		$arr						= array();

		if ( $query->num_rows() > 0 )
		{
			foreach ( $query->result_array() as $row )
			{
				$arr[$row['site_id']][] 	= $row['status'];
				$arr['all'][]				= $row['status'];
				$arr['cleaned'][]			= str_replace( " ", "+" , $row['status']);

				if ( strpos($row['status'] , ' ') ) $arr['multiword_status'] = TRUE;

			}

		}

		$this->sess['statuses']							= $arr;

		return;
	}

	//	End fields

	// -------------------------------------------------------------

	/**
	 * Forget last search
	 *
	 * This method deletes the user's last search from the DB if it is found.
	 *
	 * @access	private
	 * @return	string
	 */

	function forget_last_search()
	{
		$tagdata	= $this->EE->TMPL->tagdata;

		// -------------------------------------
		//	Delete
		// -------------------------------------

		$sql	= "DELETE FROM exp_super_search_history
					WHERE site_id = ".$this->EE->db->escape_str( $this->EE->config->item('site_id') );

		$sql	.= " AND saved = 'n'
					AND ( (
							member_id != 0
							AND member_id = ".$this->EE->db->escape_str( $this->EE->session->userdata('member_id') )." )";

		$sql	.= " OR ( cookie_id = '".$this->EE->db->escape_str( $this->_get_users_cookie_id() )."' ) )
					LIMIT 1";

		$this->EE->db->query( $sql );

		if ( $this->EE->db->affected_rows() == 0 )
		{
			$message	= lang( 'no_search_history_was_found' );

			$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, array( 'failure' => TRUE, 'success' => FALSE ) );
			$tagdata	= str_replace( LD.'message'.RD, $message, $tagdata );
			return $tagdata;
		}
		else
		{
			$message	= lang( 'last_search_cleared' );

			$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, array( 'failure' => FALSE, 'success' => TRUE ) );
			$tagdata	= str_replace( LD.'message'.RD, $message, $tagdata );
			return $tagdata;
		}
	}

	//	End forget last search

	// -------------------------------------------------------------

	/**
	 * Form (sub)
	 *
	 * This method receives form config info and returns a properly formated EE form.
	 *
	 * @access	private
	 * @return	string
	 */

	function _form( $data = array() )
	{
		if ( count( $data ) == 0 ) return '';

		if ( ! isset( $data['tagdata'] ) OR $data['tagdata'] == '' )
		{
			$tagdata	=	$this->EE->TMPL->tagdata;
		}
		else
		{
			$tagdata	= $data['tagdata'];
			unset( $data['tagdata'] );
		}

		// -------------------------------------
		//  Special Handling for return="" parameter
		// -------------------------------------

		foreach( array('return', 'RET') as $val )
		{
			if ( isset( $data[$val] ) AND $data[$val] !== FALSE AND $data[$val] != '' )
			{
				$data[$val] = str_replace(SLASH, '/', $data[$val]);

				if ( preg_match( "/".LD."\s*path=(.*?)".RD."/", $data[$val], $match ))
				{
					$data[$val] = $this->EE->functions->create_url( $match['1'] );
				}
				elseif ( stristr( $data[$val], "http://" ) === FALSE )
				{
					$data[$val] = $this->EE->functions->create_url( $data[$val] );
				}
			}
		}

		// -------------------------------------
		//	Generate form
		// -------------------------------------

		$arr	=	array(
						'action'		=> $this->EE->functions->fetch_site_index(),
						'id'			=> $data['form_id'],
						'enctype'		=> '',
						'onsubmit'		=> ( isset( $data['onsubmit'] ) ) ? $data['onsubmit'] : ''
						);

		$arr['onsubmit'] = ( $this->EE->TMPL->fetch_param('onsubmit') ) ? $this->EE->TMPL->fetch_param('onsubmit') : $arr['onsubmit'];

		if ( isset( $data['name'] ) !== FALSE )
		{
			$arr['name']	= $data['name'];
			unset( $data['name'] );
		}

		unset( $data['form_id'] );
		unset( $data['onsubmit'] );

		$arr['hidden_fields']	= $data;

		// -------------------------------------
		//  HTTPS URLs?
		// -------------------------------------

		if ($this->EE->TMPL->fetch_param('secure_action') == 'yes')
		{
			if (isset($arr['action']))
			{
				$arr['action'] = str_replace('http://', 'https://', $arr['action']);
			}
		}

		if ($this->EE->TMPL->fetch_param('secure_return') == 'yes')
		{
			foreach(array('return', 'RET') as $return_field)
			{
				if (isset($arr['hidden_fields'][$return_field]))
				{
					if ( preg_match( "/".LD."\s*path=(.*?)".RD."/", $arr['hidden_fields'][$return_field], $match ) > 0 )
					{
						$arr['hidden_fields'][$return_field] = $this->EE->functions->create_url( $match['1'] );
					}
					elseif ( stristr( $arr['hidden_fields'][$return_field], "http://" ) === FALSE )
					{
						$arr['hidden_fields'][$return_field] = $this->EE->functions->create_url( $arr['hidden_fields'][$return_field] );
					}

					$arr['hidden_fields'][$return_field] = str_replace('http://', 'https://', $arr['hidden_fields'][$return_field]);
				}
			}
		}

		// -------------------------------------
		//  Override Form Attributes with form:xxx="" parameters
		// -------------------------------------

		$extra_attributes = array();

		if ( is_object( $this->EE->TMPL ) === TRUE AND ! empty( $this->EE->TMPL->tagparams ) )
		{
			foreach( $this->EE->TMPL->tagparams as $key => $value )
			{
				if ( strncmp($key, 'form:', 5) == 0 )
				{
					if (isset($arr[substr($key, 5)]))
					{
						$arr[substr($key, 5)] = $value;
					}
					else
					{
						$extra_attributes[substr($key, 5)] = $value;
					}
				}
			}
		}

		// -------------------------------------
		//  Create Form
		// -------------------------------------

		$r	= $this->EE->functions->form_declaration( $arr );

		$r	.= stripslashes($tagdata);

		$r	.= "</form>";

		// -------------------------------------
		//	 Add <form> attributes from
		// -------------------------------------

		$allowed = array('accept', 'accept-charset', 'enctype', 'method', 'action',
						 'name', 'target', 'class', 'dir', 'id', 'lang', 'style',
						 'title', 'onclick', 'ondblclick', 'onmousedown', 'onmousemove',
						 'onmouseout', 'onmouseover', 'onmouseup', 'onkeydown',
						 'onkeyup', 'onkeypress', 'onreset', 'onsubmit');

		foreach($extra_attributes as $key => $value)
		{
			if ( ! in_array($key, $allowed)) continue;

			$r = str_replace( "<form", '<form '.$key.'="'.htmlspecialchars($value).'"', $r );
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return str_replace('{/exp:', LD.T_SLASH.'exp:', str_replace(T_SLASH, '/', $r));
	}

	//	End form

	// -------------------------------------------------------------

	/**
	 * Cloud
	 *
	 * @access	public
	 * @return	string
	 */

	function cloud ()
	{
		$max 					= 1;  // Must be 1, cannot divide by zero!

		$rank_by				= 'count';

		$groups					= ( ctype_digit( $this->EE->TMPL->fetch_param('groups') ) === TRUE ) ?
									$this->EE->TMPL->fetch_param('groups') : 5;

		$start					= ( ctype_digit( $this->EE->TMPL->fetch_param('start') ) === TRUE ) ?
									$this->EE->TMPL->fetch_param('start') : 10;

		$step					= ( ctype_digit( $this->EE->TMPL->fetch_param('step') ) === TRUE ) ?
									$this->EE->TMPL->fetch_param('step') : 2;

		$day_limit				= $this->either_or($this->EE->TMPL->fetch_param('day_limit'), '');

		$start_on				= $this->either_or($this->EE->TMPL->fetch_param('start_on'), '');

		$stop_on				= $this->either_or($this->EE->TMPL->fetch_param('stop_on'), '');

		$site_id				= $this->either_or($this->EE->TMPL->fetch_param('site_id'), $this->EE->config->item('site_id') );

		$term 					= $this->either_or($this->EE->TMPL->fetch_param('term'), '');

		$term_id				= $this->either_or($this->EE->TMPL->fetch_param('term_id'), '');

		$exclude_term 			= $this->either_or($this->EE->TMPL->fetch_param('exclude_term'), '');

		$exclude_term_id		= $this->either_or($this->EE->TMPL->fetch_param('exclude_term_id'), '');

		$searched_only			= ( $this->check_no( $this->EE->TMPL->fetch_param('searched_only') ) ? FALSE : TRUE );

		$most_popular			= ( $this->check_no( $this->EE->TMPL->fetch_param('most_popular') ) ? FALSE : TRUE );

		$prefix					= $this->either_or($this->EE->TMPL->fetch_param('prefix'), 'super_search_');

		// -------------------------------------
		//  Fixed Order - Override of term_id="" parameter
		// -------------------------------------

		// fixed entry id ordering
		if (($fixed_order = $this->EE->TMPL->fetch_param('fixed_order')) === FALSE OR
			 preg_match('/[^0-9\|]/', $fixed_order))
		{
			$fixed_order = FALSE;
		}
		else
		{
			// Override Term ID parameter to get exactly these terms
			// Other parameters will still affect results. I blame the user for using them if it
			// does not work they way they want.
			$this->EE->TMPL->tagparams['term_id'] = $fixed_order;

			$fixed_order = preg_split('/\|/', $fixed_order, -1, PREG_SPLIT_NO_EMPTY);

			// A quick and easy way to reverse the order of these entries.  People might like this.
			if ($this->EE->TMPL->fetch_param('sort') == 'desc')
			{
				$fixed_order = array_reverse($fixed_order);
			}
		}

		$sql = " SELECT term_id, site_id, term, first_seen, last_seen, count, entry_count
					FROM exp_super_search_terms WHERE 1=1 ";

		if ( $searched_only )
		{
			$sql .= " AND count > 0 ";
		}

		$site_ids = array();

		if ( $site_id == 'all')
		{
			$site_ids = $this->EE->db->escape_str( $this->EE->TMPL->site_ids );

		}
		elseif ( is_numeric( $site_id ) AND in_array( $site_id, $this->EE->TMPL->site_ids ) )
		{
			$site_ids[] = $this->EE->db->escape_str( $site_id );
		}
		else
		{
			//lets be safe
			foreach( explode( array('|','+','&',' ') , $site_id ) as $site )
			{
				if ( is_numeric( $site ) AND in_array( $site, $this->EE->TMPL->site_ids ) )
				{
					$site_ids[] = $this->EE->db->escape_str( $site );
				}
			}

			if ( empty( $site_ids ) )
			{
				$site_ids[] = $this->EE->db->escape_str( $this->EE->config->item('site_id') );
			}
		}

		$sql .= " AND site_id IN ('" . implode( "','", $site_ids ) . "') ";

		//	----------------------------------------
		//	 Narrow Tags via Terms
		//	----------------------------------------

		$sql .= $this->_param_split( $term );

		$sql .= $this->_param_split( $term_id, 'term_id' );

		$sql .= $this->_param_split( $exclude_term, 'term NOT ' );

		$sql .= $this->_param_split( $exclude_term_id, 'term_id NOT ' );

		//	----------------------------------------
		//	Limit query by number of days (recently)
		//	----------------------------------------

		if ( $day_limit != '' )
		{
			$time = $this->EE->localize->now - ( $day_limit * 60 * 60 * 24);

			$sql .= " AND last_seen >= '".$time."'";
		}
		else // OR
		{
			//	----------------------------------------
			//	Limit query by date range given in tag parameters
			//	----------------------------------------

			if ( $start_on != '' )
			{
				$sql .= " AND last_seen >= '".$this->EE->localize->convert_human_date_to_gmt($start_on)."'";
			}

			if ( $stop_on != '' )
			{
				$sql .= " AND last_seen < '".$this->EE->localize->convert_human_date_to_gmt($stop_on)."'";
			}

		}

		// --------------------------------------
		//  Pagination checkeroo! - Do Before GROUP BY!
		// --------------------------------------

		$sqla = preg_replace("/SELECT(.*?)\s+FROM\s+/is", 'SELECT COUNT(DISTINCT term_id) AS count FROM ', $sql);

		$query = $this->EE->db->query( $sqla );


		if ($query->row('count') == 0 AND
			 strpos( $this->EE->TMPL->tagdata, 'paginate' ) !== FALSE)
		{
			$this->actions()->db_charset_switch('default');
			return $this->return_data = $this->no_results('super_search');
		}

		$this->p_limit  	= ( ! $this->EE->TMPL->fetch_param('limit'))  ? 20 : $this->EE->TMPL->fetch_param('limit');
		$this->total_rows 	= $query->row('count');
		$this->p_page 		= ($this->p_page == '' || ($this->p_limit > 1 AND $this->p_page == 1)) ? 0 : $this->p_page;

		if ($this->p_page > $this->total_rows)
		{
			$this->p_page = 0;
		}

		//get pagination info
		$pagination_data = $this->universal_pagination(array(
			'sql'					=> preg_replace("/SELECT(.*?)\s+FROM\s+/is", 'SELECT COUNT(DISTINCT term_id) AS count FROM ', $sql),
			'total_results'			=> $this->total_rows,
			'tagdata'				=> $this->EE->TMPL->tagdata,
			'limit'					=> $this->p_limit,
			'uri_string'			=> $this->EE->uri->uri_string,
			'current_page'			=> $this->p_page,
			'paginate_prefix'		=> $prefix,
		));

		//if we paginated, sort the data
		if ($pagination_data['paginate'] === TRUE)
		{
			$this->paginate			= $pagination_data['paginate'];
			$this->page_next		= $pagination_data['page_next'];
			$this->page_previous	= $pagination_data['page_previous'];
			$this->p_page			= $pagination_data['pagination_page'];
			$this->current_page		= $pagination_data['current_page'];
			$this->pagination_links = $pagination_data['pagination_links'];
			$this->basepath			= $pagination_data['base_url'];
			$this->total_pages		= $pagination_data['total_pages'];
			$this->paginate_data	= $pagination_data['paginate_tagpair_data'];
			$this->EE->TMPL->tagdata		= $pagination_data['tagdata'];
		}

		//	----------------------------------------
		//	Fix current page if discrepancy between total results and limit
		//	----------------------------------------

		if ( $this->current_page > 1 AND $pagination_data['total_results'] <= $this->p_limit )
		{
			$this->current_page	= 1;
		}

		//	----------------------------------------
		//	Find Max for All Pages
		//	----------------------------------------

		if ($this->paginate === TRUE)
		{
			$query = $this->EE->db->query($sql." ORDER BY count DESC LIMIT 0, 1");

			if ($query->num_rows() > 0)
			{
				$max = $query->row('count');
			}
		}

		//	----------------------------------------
		//	Set order by
		//	----------------------------------------

		$sort = '';

		$ord	= " ORDER BY count DESC ";

		if ($fixed_order !== FALSE)
		{
			$ord = ' ORDER BY FIELD(term_id, '.implode(',', $fixed_order).') ';
		}
		elseif ( $this->EE->TMPL->fetch_param('orderby') !== FALSE AND $this->EE->TMPL->fetch_param('orderby') != '' )
		{
			foreach ( array(
					'random' 			=> "rand()",
					'count' 			=> 'count',
					'term' 				=> 'term',
					'first_seen'		=> 'first_seen',
					'last_seen'			=> 'last_seen',
					'entry_count'		=> 'entry_count'
				) as $key => $val )
			{
				if ( $key == $this->EE->TMPL->fetch_param('orderby') )
				{
					if ( ! $most_popular )
					{
						$ord = " ORDER BY ".$val;

						if ( $key == 'term' )
						{
							$sort = " ASC ";
						}
					}

				}

			}
		}

		if ( ($this->EE->TMPL->fetch_param('sort') !== FALSE AND
				( $this->EE->TMPL->fetch_param('sort') == 'desc' OR  $this->EE->TMPL->fetch_param('sort') == 'asc') ) AND ! $most_popular )
		{
			$sort	= $this->EE->TMPL->fetch_param('sort');
		}

		$sql_a = $sql . $ord . ' ' . $sort .' ';

		//	----------------------------------------
		//	Set numerical limit
		//	----------------------------------------

		if ($this->paginate === TRUE AND $this->total_rows > $this->p_limit)
		{
			$sql_a .= " LIMIT ".$this->p_page.', '.$this->p_limit;
		}
		else
		{
			$sql_a .= ( ctype_digit( $this->EE->TMPL->fetch_param('limit') ) === TRUE ) ? ' LIMIT '.$this->EE->TMPL->fetch_param('limit') : ' LIMIT 20';
		}

		//	----------------------------------------
		//	Query
		//	----------------------------------------

		$query	= $this->EE->db->query( $sql_a );

		//	----------------------------------------
		//	Empty?
		//	----------------------------------------

		if ( $query->num_rows() == 0 )
		{
			$this->actions()->db_charset_switch('default');
			return $this->no_results('super_search');
		}

		if ( $this->EE->TMPL->fetch_param('orderby') !== FALSE AND $this->EE->TMPL->fetch_param('orderby') != ''  AND $most_popular )
		{
			foreach ( array(
					'random' 			=> "rand()",
					'count' 			=> 'count',
					'term' 				=> 'term',
					'first_seen'		=> 'first_seen',
					'last_seen'			=> 'last_seen',
					'entry_count'		=> 'entry_count'
				) as $key => $val )
			{
				if ( $key == $this->EE->TMPL->fetch_param('orderby') )
				{
					$ord = " ORDER BY ".$val;

					$sort = " DESC";

					if ( $key == 'term' )
					{
						$sort = " ASC";
					}
				}
			}

			if ( ($this->EE->TMPL->fetch_param('sort') !== FALSE AND
				( $this->EE->TMPL->fetch_param('sort') == 'desc' OR  $this->EE->TMPL->fetch_param('sort') == 'asc') ) )
			{
				$sort = " " . $this->EE->TMPL->fetch_param('sort');
			}

			$temp = array();

			foreach( $query->result_array() AS $row )
			{
				$temp[] = "'" . $row['term_id'] . "'";
			}

			$where = " AND term_id IN (" . implode(',', $temp) . ") ";

			$sql_b = $sql . $where . $ord . $sort;

			$query = $this->EE->db->query( $sql_b );
		}

		//	----------------------------------------
		//	What's the max?
		//	----------------------------------------

		// If we have Pagination, we find the MAX value up above.
		// If not, we find it based on the current results.

		if ($this->paginate !== TRUE)
		{
			foreach ( $query->result_array() as $row )
			{
				$max	= ( $row['count'] > $max ) ? $row['count']: $max;
			}
		}

		//	----------------------------------------
		//	Order alpha
		//	----------------------------------------

		$terms	= array();

		foreach ( $query->result_array() as $row )
		{
			$terms[$row['term']]['term_id']				= $row['term_id'];
			$terms[$row['term']]['term']				= $row['term'];
			$terms[$row['term']]['count']				= $row['count'];
			$terms[$row['term']]['entry_count']			= $row['entry_count'];
			$terms[$row['term']]['first_seen']			= $row['first_seen'];
			$terms[$row['term']]['last_seen']			= $row['last_seen'];
			$terms[$row['term']]['site_id']				= $row['site_id'];

			$terms[$row['term']]['size']				= ceil( $row['count'] / ( $max / $groups ) );

			$terms[$row['term']]['step']				= $terms[$row['term']]['size'] * $step + $start;
		}

		//	----------------------------------------
		//	Parse
		//	----------------------------------------

		$r		= '';
		$count	= 0;

		$qs	= ($this->EE->config->item('force_query_string') == 'y') ? '' : '?';

		$total_results = count($terms);

		foreach ( $terms as $key => $row )
		{
			$tagdata	= $this->EE->TMPL->tagdata;

			$count++;
			$row['absolute_count']	= ( $this->current_page < 2 ) ? $count: ( $this->current_page - 1 ) * $this->p_limit + $count;
			$row['total_results'] 		= $total_results;
			$row['absolute_results'] 	= $this->total_rows;

			//	----------------------------------------
			//	Conditionals
			//	----------------------------------------

			foreach( $row AS $subkey => $subval )
			{
				$row[ $prefix . $subkey ] = $subval;
			}

			$cond							= $row;
			$cond['term']					= $key;
			$cond[$prefix.'term']			= $key;
			$tagdata						= $this->EE->functions->prep_conditionals( $tagdata, $cond );

			//	----------------------------------------
			//	Parse Switch
			//	----------------------------------------

			if ( preg_match( "/".LD."(switch\s*=.+?)".RD."/is", $tagdata, $match ) > 0 )
			{
				$sparam = $this->EE->functions->assign_parameters($match['1']);

				$sw = '';

				if ( isset( $sparam['switch'] ) !== FALSE )
				{
					$sopt = explode("|", $sparam['switch']);

					$sw = $sopt[($count + count($sopt)) % count($sopt)];
				}

				$tagdata = $this->EE->TMPL->swap_var_single($match['1'], $sw, $tagdata);
			}

			//	----------------------------------------
			//	Parse singles
			//	----------------------------------------

			$tagdata = str_replace( LD.'term'.RD, $key, $tagdata );
			$tagdata = str_replace( LD.'term_id'.RD, $row['term_id'], $tagdata );
			$tagdata = str_replace( LD.'total_searches'.RD, $row['count'], $tagdata );
			$tagdata = str_replace( LD.'site_id'.RD, $row['site_id'], $tagdata );
			$tagdata = str_replace( LD.'size'.RD, $row['size'], $tagdata );
			$tagdata = str_replace( LD.'step'.RD, $row['step'], $tagdata );
			$tagdata = str_replace( LD.'count'.RD, $count, $tagdata );
			$tagdata = str_replace( LD.'absolute_count'.RD, $row['absolute_count'], $tagdata );
			$tagdata = str_replace( LD.'absolute_results'.RD, $row['absolute_results'], $tagdata );
			$tagdata = str_replace( LD.'total_results'.RD, $row['total_results'], $tagdata );


			$tagdata = str_replace( LD.$prefix.'term'.RD, $key, $tagdata );
			$tagdata = str_replace( LD.$prefix.'term_id'.RD, $row['term_id'], $tagdata );
			$tagdata = str_replace( LD.$prefix.'total_searches'.RD, $row['count'], $tagdata );
			$tagdata = str_replace( LD.$prefix.'site_id'.RD, $row['site_id'], $tagdata );
			$tagdata = str_replace( LD.$prefix.'size'.RD, $row['size'], $tagdata );
			$tagdata = str_replace( LD.$prefix.'step'.RD, $row['step'], $tagdata );
			$tagdata = str_replace( LD.$prefix.'count'.RD, $count, $tagdata );
			$tagdata = str_replace( LD.$prefix.'absolute_count'.RD, $row['absolute_count'], $tagdata );
			$tagdata = str_replace( LD.$prefix.'absolute_results'.RD, $row['absolute_results'], $tagdata );
			$tagdata = str_replace( LD.$prefix.'total_results'.RD, $row['total_results'], $tagdata );


			foreach (array('last_seen', 'first_seen', $prefix.'last_seen', $prefix.'first_seen') as $val)
			{
				if (preg_match_all("/".LD.$val."\s+format=([\"'])([^\\1]*?)\\1".RD."/s", $tagdata, $matches))
				{
					for($i=0, $s=count($matches[2]); $i < $s; ++$i)
					{
						$str	= $matches[2][$i];

						$codes	= $this->EE->localize->fetch_date_params( $matches[2][$i] );

						foreach ( $codes as $code )
						{
							$str	= str_replace( $code, $this->EE->localize->convert_timestamp( $code, $row[$val], TRUE ), $str );
						}

						$tagdata	= str_replace( $matches[0][$i], $str, $tagdata );
					}
				}
			}

			//	----------------------------------------
			//	Concat
			//	----------------------------------------

			$r	.= $tagdata;
		}

		//	----------------------------------------
		//	Backspace
		//	----------------------------------------

		$backspace			= ( ctype_digit( $this->EE->TMPL->fetch_param('backspace') ) === TRUE ) ? $this->EE->TMPL->fetch_param('backspace'): 0;

		// Clean up our no_results condition before backspacing
		if ( $backspace > 0 )
		{
			if ( preg_match(
				"/".LD."if " .preg_quote($this->lower_name)."_no_results" .
					RD."(.*?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s",
				$r,
				$match ) )
			{
				$r = str_replace( $match[0], '', $r );
			}

		}

		$this->return_data	= ( $backspace > 0 ) ? substr( $r, 0, - $backspace ): $r;

		// -------------------------------------
		//  Pagination?
		// -------------------------------------

		if ($this->paginate == TRUE)
		{
			$this->paginate_data = str_replace(LD.'current_page'.RD, $this->current_page, $this->paginate_data);
			$this->paginate_data = str_replace(LD.'total_pages'.RD,	$this->total_pages, $this->paginate_data);
			$this->paginate_data = str_replace(LD.'pagination_links'.RD, $this->pagination_links, $this->paginate_data);
			$this->paginate_data = str_replace(LD.$prefix.'current_page'.RD, $this->current_page, $this->paginate_data);
			$this->paginate_data = str_replace(LD.$prefix.'total_pages'.RD,	$this->total_pages, $this->paginate_data);
			$this->paginate_data = str_replace(LD.$prefix.'pagination_links'.RD, $this->pagination_links, $this->paginate_data);

			if (preg_match("/".LD."if previous_page".RD."(.+?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s", $this->paginate_data, $match))
			{
				if ($this->page_previous == '')
				{
					 $this->paginate_data = preg_replace("/".LD."if previous_page".RD.".+?".LD.preg_quote(T_SLASH, '/')."if".RD."/s", '', $this->paginate_data);
				}
				else
				{
					$match['1'] = preg_replace("/".LD.'path.*?'.RD."/", 	$this->page_previous, $match['1']);
					$match['1'] = preg_replace("/".LD.'auto_path'.RD."/",	$this->page_previous, $match['1']);

					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
			}

			if (preg_match("/".LD."if ".$prefix."previous_page".RD."(.+?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s", $this->paginate_data, $match))
			{
				if ($this->page_previous == '')
				{
					 $this->paginate_data = preg_replace("/".LD."if ".$prefix."previous_page".RD.".+?".LD.preg_quote(T_SLASH, '/')."if".RD."/s", '', $this->paginate_data);
				}
				else
				{
					$match['1'] = preg_replace("/".LD.$prefix.'path.*?'.RD."/", 	$this->page_previous, $match['1']);
					$match['1'] = preg_replace("/".LD.$prefix.'auto_path'.RD."/",	$this->page_previous, $match['1']);

					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
			}

			if (preg_match("/".LD."if next_page".RD."(.+?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s", $this->paginate_data, $match))
			{
				if ($this->page_next == '')
				{
					 $this->paginate_data = preg_replace("/".LD."if next_page".RD.".+?".LD.preg_quote(T_SLASH, '/')."if".RD."/s", '', $this->paginate_data);
				}
				else
				{
					$match['1'] = preg_replace("/".LD.'path.*?'.RD."/", 	$this->page_next, $match['1']);
					$match['1'] = preg_replace("/".LD.'auto_path'.RD."/",	$this->page_next, $match['1']);

					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
			}

			if (preg_match("/".LD."if ".$prefix."next_page".RD."(.+?)".LD.preg_quote(T_SLASH, '/')."if".RD."/s", $this->paginate_data, $match))
			{
				if ($this->page_next == '')
				{
					 $this->paginate_data = preg_replace("/".LD."if ".$prefix."next_page".RD.".+?".LD.preg_quote(T_SLASH, '/')."if".RD."/s", '', $this->paginate_data);
				}
				else
				{
					$match['1'] = preg_replace("/".LD.$prefix.'path.*?'.RD."/", 	$this->page_next, $match['1']);
					$match['1'] = preg_replace("/".LD.$prefix.'auto_path'.RD."/",	$this->page_next, $match['1']);

					$this->paginate_data = str_replace($match['0'],	$match['1'], $this->paginate_data);
				}
			}

			$position = ( ! $this->EE->TMPL->fetch_param('paginate')) ? '' : $this->EE->TMPL->fetch_param('paginate');

			switch ($position)
			{
				case "top"	: $this->return_data  = $this->paginate_data.$this->return_data;
					break;
				case "both"	: $this->return_data  = $this->paginate_data.$this->return_data.$this->paginate_data;
					break;
				default		: $this->return_data .= $this->paginate_data;
					break;
			}
		}

		$this->actions()->db_charset_switch('default');

		return $this->return_data;
	}

	//	END cloud

	/* -------------------------------------------------------------------------- */

	/**
	 * Get cat group ids
	 *
	 * @access	private
	 * @return	array
	 */

	function _get_cat_group_ids()
	{
		// -------------------------------------
		//	Get channel ids
		// -------------------------------------
		//	It helps to have a channel to make sense of the textual categories provided. By this point we already have determined channel ids, but we'll be cautious.
		// -------------------------------------

		if ( ( $channel_ids = $this->sess( 'search', 'channel_ids' ) ) === FALSE )
		{
			return FALSE;
		}

		// -------------------------------------
		//	Already fetched groups?
		// -------------------------------------

		if ( $this->sess( 'cat_group_ids' ) === FALSE )
		{
			$sql	= '/* Super Search fetch group ids */ SELECT group_id FROM exp_category_groups WHERE site_id IN ('.implode( ',', $this->EE->TMPL->site_ids ).')';

			$query	= $this->EE->db->query( $sql );

			$group_ids	= array();

			foreach ( $query->result_array() as $row )
			{
				$group_ids[$row['group_id']]	= $row;
			}

			$this->sess['cat_group_ids']	= $group_ids;
		}

		// -------------------------------------
		//	Loop and return group ids
		// -------------------------------------

		$ids	= array();

		foreach ( $channel_ids as $id )
		{
			if ( ( $gid = $this->_channels( $id, 'cat_group' ) ) !== FALSE )
			{
				$ids	= array_merge( $ids, explode( '|', $gid ) );
			}
		}

		if ( count( $ids ) == 0 ) return FALSE;

		return $ids;
	}

	//	End get cat group ids

	// -------------------------------------------------------------

	/**
	 * Get field type
	 *
	 * Sometimes we need to know the actual MySQL field type so that we can format our search strings correctly. For example, when we use range searching on a custom field and that field contains price data, we need to strip the $ from the search string so that the search will run correctly.
	 *
	 * @access		private
	 * @argument	$field	text
	 * @return		string
	 */

	function _get_field_type( $field = '' )
	{
		if ( $field == '' ) return FALSE;

		// -------------------------------------
		//	Saved in cache?
		// -------------------------------------

		if ( empty( $this->sess['field_types'][$field] ) === FALSE )
		{
			return $this->sess['field_types'][$field];
		}

		// -------------------------------------
		//	Get all fields from DB
		// -------------------------------------

		$query	= $this->EE->db->query( "/* Super Search mod.super_search.php _get_field_type() */ DESCRIBE " . $this->sc->db->channel_data );

		$flipfields	= array_flip( $this->_fields() );

		foreach ( $query->result_array() as $row )
		{
			if ( strpos( $row['Field'], 'field_id_' ) !== FALSE )
			{
				$num	= str_replace( 'field_id_', '', $row['Field'] );

				if ( isset( $flipfields[$num] ) === TRUE )
				{
					if ( strpos( $row['Type'], 'decimal' ) !== FALSE OR strpos( $row['Type'], 'float' ) !== FALSE OR strpos( $row['Type'], 'int' ) !== FALSE )
					{
						$this->sess['field_types'][ $flipfields[$num] ]	= 'numeric';
					}
					else
					{
						$this->sess['field_types'][ $flipfields[$num] ]	= 'textual';
					}
				}
			}
		}

		// -------------------------------------
		//	How about now?
		// -------------------------------------

		if ( empty( $this->sess['field_types'][$field] ) === FALSE )
		{
			return $this->sess['field_types'][$field];
		}

		return FALSE;
	}

	//	End get field type

	// -------------------------------------------------------------

	/**
	 * Get ids by category
	 *
	 * We don't allow any fudge here. Provided categories must be exact.
	 *
	 * @access	private
	 * @return	array
	 */

	function _get_ids_by_category( $category = array() )
	{
		// -------------------------------------
		//	Anything to work with?
		// -------------------------------------

		if ( is_array( $category ) === FALSE OR count( $category ) == 0 ) return FALSE;

		$t	= microtime(TRUE);
		$this->EE->TMPL->log_item( 'Super Search: Beginning _get_ids_by_category()' );

		// -------------------------------------
		//	Get category group ids
		// -------------------------------------

		if ( ( $group_ids = $this->_get_cat_group_ids() ) === FALSE )
		{
			$group_ids = array();
		}

		// -------------------------------------
		//	Do we have 'and's?
		// -------------------------------------

		if ( empty( $category['and'] ) === FALSE )
		{
			foreach ( $category['and'] as $val )
			{
				if ( $val == '' ) continue;

				$and[]	= $this->EE->db->escape_str( $val );
			}
		}

		// -------------------------------------
		//	Do we have 'not's?
		// -------------------------------------

		if ( empty( $category['not'] ) === FALSE )
		{
			foreach ( $category['not'] as $val )
			{
				if ( $val == '' ) continue;

				$not[]	= $this->EE->db->escape_str( $val );
			}
		}

		// -------------------------------------
		//	Do we have 'or's?
		// -------------------------------------

		if ( empty( $category['or'] ) === FALSE )
		{
			foreach ( $category['or'] as $val )
			{
				if ( $val == '' ) continue;

				$or[]	= $this->EE->db->escape_str( $val );
			}
		}

		// -------------------------------------
		//	Empty?
		// -------------------------------------

		if ( empty( $and ) === TRUE AND empty( $not ) === TRUE AND empty( $or ) === TRUE ) return FALSE;

		// -------------------------------------
		//	Query by cat_url_title or cat_name?
		// -------------------------------------

		$cat_name_query	= ( $this->EE->TMPL->fetch_param('category_indicator') !== FALSE AND $this->EE->TMPL->fetch_param('category_indicator') == 'category_url_title' ) ? 'c.cat_url_title': 'c.cat_name';

		// -------------------------------------
		//	Assemble sql
		// -------------------------------------

		$select	= '/* Super Search get entries by category for later comparison */ SELECT cp.entry_id';
		$from	= ' FROM exp_category_posts cp';
		$join	= ' LEFT JOIN exp_categories c ON cp.cat_id = c.cat_id';
		$where	= ' WHERE c.site_id IN ('.implode( ',', $this->EE->TMPL->site_ids ).')';

		// -------------------------------------
		//	Handle uncategorized posts
		// -------------------------------------

		$include_uncategorized = ( $this->EE->TMPL->fetch_param('include_uncategorized') !== FALSE AND $this->check_yes( $this->EE->TMPL->fetch_param('include_uncategorized') ) ) ? TRUE: FALSE;

		// -------------------------------------
		//	Group ids?
		// -------------------------------------

		if ( count( $group_ids ) > 0 )
		{
			$where	.= ' AND c.group_id IN ('.implode( ',', $group_ids ).')';
		}

		// -------------------------------------
		// And's
		// -------------------------------------
		//	This is our gnarly case. We're assembling an array of entry ids that belong to all the 'and'ed categories. Then passing that to our main query as a requirement.
		// -------------------------------------

		if ( ! empty( $and ) )
		{
			if ( ( $newand = $this->_separate_numeric_from_textual( $and ) ) !== FALSE )
			{
				$sql	= "/* Super Search fetch gnarly conjoined category entries for later comparison */ SELECT cp.cat_id, cp.entry_id".$from.$join.$where." AND ( c.cat_id IN (".implode( ",", $newand['numeric'] ).")";

				if ( empty( $newand['textual'] ) === FALSE )
				{
					$sql	.= " OR ".$cat_name_query." IN ('".implode( "','", $newand['textual'] )."')";
				}

				$sql	.= ")";
			}
			else
			{
				$sql	= '/* Super Search fetch entries by category */ SELECT cp.cat_id, cp.entry_id'.$from.$join.$where.' AND '.$cat_name_query." IN ('".implode( "','", $and )."')";
			}

			unset( $newand );

			$query	= $this->EE->db->query( $sql );

			if ( $query->num_rows() > 0 )
			{
				$ids	= array();
				$chosen	= array();

				foreach ( $query->result_array() as $row )
				{
					$ids[ $row['cat_id'] ][]	= $row['entry_id'];
					$chosen[]	= $row['entry_id'];
				}

				if ( count( $ids ) > 1 )
				{
					$chosen = call_user_func_array('array_intersect', $ids);
					$chosen	= array_unique( $chosen );
				}

				// -------------------------------------
				//	If the count of array keys of $ids is less than the count of $and, we have not found a match against each category. This means we did a conjoined search and asked for a category that had no entries assigned. The test for the category should still be counted and thus, this search needs to fail.
				// -------------------------------------

				if ( count( array_keys( $ids ) ) < count( $and ) )
				{
					return FALSE;
				}

				unset( $ids );

				// -------------------------------------
				//	Get out now?
				// -------------------------------------
				//	If we have no $or or $not tests, then we care only about entry ids that belong to ALL our $and categories inclusive. We can escape now since hitting the DB would be redundant.
				// -------------------------------------

				if ( empty( $not ) === TRUE AND empty( $or ) === TRUE AND empty( $chosen ) === FALSE )
				{
					return $chosen;
				}

				// -------------------------------------
				//	Add $chosen to our eventual query
				// -------------------------------------

				if ( empty( $chosen ) === FALSE )
				{
					$where	.= ' AND cp.entry_id IN ('.implode( ',', $chosen ).')';
				}
			}

			// -------------------------------------
			//	Fail-safe test for $and
			// -------------------------------------
			//	If we had an 'and' query, but it returned no entry ids, then we need to fail out, because nothing beyond this point will meet our requirements.
			// -------------------------------------

			if ( empty( $chosen ) === TRUE )
			{
				return FALSE;
			}
		}

		// -------------------------------------
		//	Not's
		// -------------------------------------
		//	We need a subquery to make this negation thing work because of the EE category structure.
		// -------------------------------------

		if ( ! empty( $not ) )
		{
			$notwhere	= 'SELECT cp.entry_id FROM exp_category_posts cp LEFT JOIN exp_categories c ON c.cat_id = cp.cat_id WHERE 0=0';

			if ( ( $newnot = $this->_separate_numeric_from_textual( $not ) ) !== FALSE )
			{
				$notwhere	.= ' AND c.cat_id IN ('.implode( ',', $newnot['numeric'] ).')';

				if ( empty( $newnot['textual'] ) === FALSE )
				{
					$notwhere	.= ' AND '.$cat_name_query." IN ('".implode( "','", $newnot['textual'] )."')";
				}
			}
			else
			{
				$notwhere	.= ' AND '.$cat_name_query." IN ('".implode( "','", $not )."')";
			}

			$where	.= ' AND ( cp.entry_id NOT IN (' . $notwhere . ') ';

			$where	.= ' )';

			unset( $newnot, $notwhere );
		}

		// -------------------------------------
		//	Or's
		// -------------------------------------

		if ( empty( $or ) === FALSE )
		{
			if ( ( $newor = $this->_separate_numeric_from_textual( $or ) ) !== FALSE )
			{
				$where	.= ' AND c.cat_id IN ('.implode( ',', $newor['numeric'] ).')';

				if ( empty( $newor['textual'] ) === FALSE )
				{
					$where	.= ' AND '.$cat_name_query." IN ('".implode( "','", $newor['textual'] )."')";
				}
			}
			else
			{
				$where	.= ' AND '.$cat_name_query." IN ('".implode( "','", $or )."')";
			}
		}

		// -------------------------------------
		// Run it
		// -------------------------------------

		$sql	= $select.$from.$join.$where;

		//print_r( $sql );

		$query	= $this->EE->db->query( $sql );

		if ( $query->num_rows() == 0 )
		{
			$this->EE->TMPL->log_item( 'Super Search: Ending _get_ids_by_category() ('.(microtime(TRUE) - $t).')' );
			return FALSE;
		}

		$ids	= array();

		foreach ( $query->result_array() as $row )
		{
			$ids[]	= $row['entry_id'];
		}

		if ($include_uncategorized)
		{
			$sql = "SELECT t.entry_id FROM {$this->sc->db->channel_titles} t LEFT OUTER JOIN exp_category_posts cp ON t.entry_id = cp.entry_id WHERE cp.entry_id IS NULL";

			$query = $this->EE->db->query( $sql );

			if ( $query->num_rows() == 0 )
			{
				$this->EE->TMPL->log_item( 'Super Search: Handling uncategorized posts, none found ('.(microtime(TRUE) - $t).')' );
			}
			else
			{
				foreach ( $query->result_array() as $row )
				{
					$ids[]	= $row['entry_id'];
				}

			}
		}

		$this->EE->TMPL->log_item( 'Super Search: Ending _get_ids_by_category() ('.(microtime(TRUE) - $t).')' );

		return $ids;
	}

	//	End get ids by category

	// -------------------------------------------------------------

	/**
	 * Get ids by category like
	 *
	 * We don't allow any fudge above, but we do here. This method allows people to supply a category approximation in the search like 'categorylike+bedford'. This will return entries with the category of 'Bedford Stuyvesant' or 'Bedford Place' or 'Bedford'.
	 *
	 * @access	private
	 * @return	array
	 */

	function _get_ids_by_category_like( $category = array() )
	{
		// -------------------------------------
		//	Anything to work with?
		// -------------------------------------

		if ( is_array( $category ) === FALSE OR count( $category ) == 0 ) return FALSE;

		$t	= microtime(TRUE);
		$this->EE->TMPL->log_item( 'Super Search: Beginning _get_ids_by_category_like()' );

		// -------------------------------------
		//	Get category group ids
		// -------------------------------------

		if ( ( $group_ids = $this->_get_cat_group_ids() ) === FALSE )
		{
			$group_ids = array();
		}

		// -------------------------------------
		//	Do we have 'not's?
		// -------------------------------------

		if ( empty( $category['not'] ) === FALSE )
		{
			foreach ( $category['not'] as $val )
			{
				if ( $val == '' ) continue;

				$not[]	= $this->EE->db->escape_str( $val );
			}
		}

		// -------------------------------------
		//	Do we have 'or's?
		// -------------------------------------

		if ( empty( $category['or'] ) === FALSE )
		{
			foreach ( $category['or'] as $val )
			{
				if ( $val == '' ) continue;

				$or[]	= $this->EE->db->escape_str( $val );
			}
		}

		// -------------------------------------
		//	Empty?
		// -------------------------------------

		if ( empty( $not ) === TRUE AND empty( $or ) === TRUE ) return FALSE;

		// -------------------------------------
		//	Query by cat_url_title or cat_name?
		// -------------------------------------

		$cat_name_query	= ( $this->EE->TMPL->fetch_param('category_indicator') !== FALSE AND $this->EE->TMPL->fetch_param('category_indicator') == 'category_url_title' ) ? ' c.cat_url_title': ' c.cat_name';

		// -------------------------------------
		//	Assemble sql
		// -------------------------------------

		$select	= '/* Super Search fetch entries by loose categories */ SELECT cp.entry_id';
		$from	= ' FROM exp_category_posts cp';
		$join	= ' LEFT JOIN exp_categories c ON cp.cat_id = c.cat_id';
		$where	= ' WHERE c.site_id IN ('.implode( ',', $this->EE->TMPL->site_ids ).')';

		// -------------------------------------
		//	Group ids?
		// -------------------------------------

		if ( count( $group_ids ) > 0 )
		{
			$where	.= ' AND c.group_id IN ('.implode( ',', $group_ids ).')';
		}

		// -------------------------------------
		//	Not's
		// -------------------------------------
		//	We need a subquery to make this negation thing work because of the EE category structure.
		// -------------------------------------

		if ( empty( $not ) === FALSE )
		{
			$arr	= array();

			if ( ( $newnot = $this->_separate_numeric_from_textual( $not ) ) !== FALSE )
			{
				$arr[]	= 'c.cat_id IN ('.implode( ',', $newnot['numeric'] ).')';

				if ( empty( $newnot['textual'] ) === FALSE )
				{
					foreach ( $newnot['textual'] as $newnottextual )
					{
						$arr[]	= $cat_name_query." LIKE '%" . $newnottextual . "%'";
					}
				}
			}
			else
			{
				foreach ( $not as $newnottextual )
				{
					$arr[]	= $cat_name_query." LIKE '%" . $newnottextual . "%'";
				}
			}

			$notwhere	= 'SELECT cp.entry_id FROM exp_category_posts cp LEFT JOIN exp_categories c ON c.cat_id = cp.cat_id WHERE (' . implode( ' OR ', $arr ) . ')';

			$where	.= ' AND cp.entry_id NOT IN (' . $notwhere . ')';

			unset( $newnot, $notwhere );
		}

		// -------------------------------------
		//	Or's
		// -------------------------------------

		if ( empty( $or ) === FALSE )
		{
			$where	.= ' AND (';
			foreach ( $or as $val )
			{
				$where	.= $cat_name_query." LIKE '%".$val."%' OR";
			}
			$where	= rtrim( $where, 'OR' );
			$where	.= ')';
		}

		// -------------------------------------
		// Run it
		// -------------------------------------

		$sql	= $select.$from.$join.$where;

		$query	= $this->EE->db->query( $sql );

		if ( $query->num_rows() == 0 )
		{
			$this->EE->TMPL->log_item( 'Super Search: Ending _get_ids_by_category_like() ('.(microtime(TRUE) - $t).')' );
			return FALSE;
		}

		$ids	= array();

		foreach ( $query->result_array() as $row )
		{
			$ids[]	= $row['entry_id'];
		}

		$this->EE->TMPL->log_item( 'Super Search: Ending _get_ids_by_category_like() ('.(microtime(TRUE) - $t).')' );

		return $ids;
	}

	//	End get ids by category like

	// -------------------------------------------------------------

	/**
	 * Get uri
	 *
	 * EE applies some filtering to $this->EE->uri->uri_string that prevents us from using quotes and = signs in the uri. It strips those as a security measure just in case someone uses a segment in an EE tag param. We need and want those for our queries and will not be making our $uri available to other parts of EE. This method goes through most of the EE security routines and skips the part where EE strips out what we want.
	 *
	 * @access	private
	 * @return	string
	 */

	function _get_uri($method = 'fetch')
	{
		$this->EE->load->helper('string');

		if ( APP_VER < 2.0 )
		{
			if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!='' && $method=='parse')
			{
				return rtrim( $this->EE->security->xss_clean( $this->_sanitize( trim_slashes( $_SERVER['REQUEST_URI'] ) ) ), '/' ) . '/';
			}
			else
			{
				return rtrim( $this->EE->security->xss_clean( $this->_sanitize( trim_slashes( $GLOBALS['uri'] ) ) ), '/' ) . '/';
			}

		}
		else
		{
			// -------------------------------------
			//	EE 2 has one too many security provisions that filter the URI. EE 2, through CI, strips out our extra ampersands. We need those. The strippage happens not in /system/core/URI.php but in /system/core/Router.php where $this->uri->_explode_segments() is called. We are allowed by the code to call _fetch_uri_string() again and grab the uri_string variable before it is filtered further. We apply our own security to it.
			// -------------------------------------

			// -------------------------------------
			// To get our uri's to be compatibile with Google Analytics they require a '?' in the search uri.  If we use the standard _fetch_uri_string() method to fetch out uri it gets filtered out well before we ever see it. So in the case where we require GA recording we'll use _parse_request_uri() instead, and get the full unabridged uri back to play with. Joel 2011 05 11
			// -------------------------------------

			if ($method == 'parse')
			{
				return rtrim( $this->EE->security->xss_clean( $this->_sanitize( trim_slashes( $_SERVER['REQUEST_URI'] ) ) ), '/' ) . '/';
			}
			else
			{
				$this->EE->uri->_fetch_uri_string();

				return rtrim( $this->EE->security->xss_clean( $this->_sanitize( trim_slashes( $this->EE->uri->uri_string() ) ) ), '/' ) . '/';
			}
		}
	}

	// End get uri

	// -------------------------------------------------------------

	/**
	 * Get users cookie id
	 *
	 * This method gets a user's cookie id if they have already been cookied. Otherwise a cookie id is created for them and provided.
	 *
	 * @access	private
	 * @return	string
	 */

	function _get_users_cookie_id()
	{
		// -------------------------------------
		//	Have we already done this?
		// -------------------------------------

		if ( isset( $this->sess['cookie_id'] ) === TRUE )
		{
			return $this->sess['cookie_id'];
		}

		// -------------------------------------
		//	Is their cookie already set?
		// -------------------------------------

		$input_cookie = $this->EE->input->cookie('super_search_history', TRUE);

		if ( $input_cookie !== FALSE AND
			 is_numeric($input_cookie) AND
			 $input_cookie >= 10000 AND
			 $input_cookie <= 999999)
		{
			return $this->sess['cookie_id']	= $input_cookie;
		}

		// -------------------------------------
		//	Create a cookie, set it and return it
		// -------------------------------------

		$cookie	= mt_rand( 10000, 999999 );
		$this->EE->functions->set_cookie( 'super_search_history', $cookie, 86500 );
		return $this->sess['cookie_id']	= $cookie;
	}

	//	End get users cookie id

	// -------------------------------------------------------------

	/**
	 * Hash it
	 *
	 * @access	private
	 * @return	string
	 */

	function _hash_it( $arr = array() )
	{
		if ( is_array( $arr ) === FALSE OR count( $arr ) == 0 ) return FALSE;

		ksort( $arr );

		$this->hash	= md5( serialize( $arr ) );

		return $this->hash;
	}

	//	End hash it

	// -------------------------------------------------------------

	/**
	 * Highlight keywords
	 *
	 * I know you probably want me to use regular expressions here. My experiment is to test whether
	 * rand() plus simple str_replace is faster than some complex REGEX that I wouldn't be able to
	 * write in the first place.
	 *
	 * @access	private
	 * @return	string
	 */

	function _highlight_keywords( $str = '' )
	{
		$t = microtime(TRUE);

		if ( $str == '' OR
			$this->EE->TMPL->fetch_param('highlight_keywords') == '' OR
			$this->EE->TMPL->fetch_param('highlight_keywords') == 'no' OR
			( empty( $this->sess['search']['q']['keywords']['or'] ) === TRUE AND
			  empty( $this->sess['search']['q']['keywords']['and'] ) === TRUE) ) return $str;

		$tag	= 'em';

		if ( $this->EE->TMPL->fetch_param('highlight_keywords') !== FALSE AND $this->EE->TMPL->fetch_param('highlight_keywords') != '' )
		{
			switch ( $this->EE->TMPL->fetch_param('highlight_keywords') )
			{
				case 'b':
					$tag	= 'b';
					break;
				case 'i':
					$tag	= 'i';
					break;
				case 'span':
					$tag	= 'span';
					break;
				case 'strong':
					$tag	= 'strong';
					break;
				case 'mark':
					$tag	= 'mark';
					break;
				default:
					$tag	= 'em';
					break;
			}
		}

		// -------------------------------------
		//  Prepare Keywords for Highlighting
		// -------------------------------------

		$main = array();


		if ( ! empty( $this->sess['search']['q']['keywords']['or'] ) )
		{
			$main = $this->sess['search']['q']['keywords']['or'];

			if ( isset( $this->sess['search']['q']['keywords']['or_fuzzy'] )
				AND is_array( $this->sess['search']['q']['keywords']['or_fuzzy'] ) )
			{

				foreach( $this->sess['search']['q']['keywords']['or_fuzzy'] as $fuzzy_set )
				{
					$main = array_merge( $main, $fuzzy_set );
				}
			}
		}
		elseif ( ! empty( $this->sess['search']['q']['keywords']['and'] ) )
		{
			$main = $this->sess['search']['q']['keywords']['and'];

			if ( isset( $this->sess['search']['q']['keywords']['and_fuzzy'] )
				AND is_array( $this->sess['search']['q']['keywords']['and_fuzzy'] ) )
			{

				foreach( $this->sess['search']['q']['keywords']['and_fuzzy'] as $fuzzy_set )
				{
					$main = array_merge( $main, $fuzzy_set );
				}
			}
		}

		$phrases	= array();
		$words		= array();

		foreach ( $main as $key => $word )
		{
			if ( stripos( $str, ''.$word ) === FALSE ) continue;

			if ( strpos( $word, ' ' ) !== FALSE )
			{
				$phrases[]	= $word;
			}
			else
			{
				$words[] = $word;
			}
		}

		// Phrases happen *before* words.
		$replace = array_merge($phrases, $words);

		// -------------------------------------
		//  No Words or Phrases for Highlighting? Return
		// -------------------------------------

		if ( empty( $replace ) ) return $str;

		// -------------------------------------
		//  Cut Out Valid HTML Elements
		// -------------------------------------

		$this->marker = md5(time());

		$html_tag = <<<EVIL
								#
									</?\w+
											(
												"[^"]*" |
												'[^']*' |
												[^"'>]+
											)*
									>
								#sx
EVIL;

		$str = $this->cut($str, $html_tag);

		// -------------------------------------
		//  Do the Replace Magic
		// -------------------------------------

		// we require a trailing space (it gets collapsed on display anyway) for the prep_replace to properly markup keywords at the very end of strings
		$str .= " ";

		foreach($replace as $item)
		{
			$str = preg_replace("/([^.\/?&]\b|^)(".preg_quote($item).")(\b[^:])/imsU" , "$1<".preg_quote($tag).">$2</".preg_quote($tag).">$3", $str);
			$str = preg_replace("|(<[A-Za-z]* [^>]*)<".preg_quote($tag).">(".preg_quote($item).")</".preg_quote($tag).">([^<]*>)|imsU" , "$1$2$3" , $str);
		}

		$this->EE->TMPL->log_item( 'Super Search: Ending highlight_keywords('.(microtime(TRUE) - $t) );

		return $this->paste($str);
	}

	//	End highlight keywords

	// -------------------------------------------------------------

	/**
	 * History
	 *
	 * @access	public
	 * @return	string
	 */

	function history()
	{
		// -------------------------------------
		//	Who is this?
		// -------------------------------------

		// if ( isset( $this->EE->session->userdata ) === FALSE ) return $this->no_results( 'super_search' );

		if ( ( $member_id = $this->EE->session->userdata('member_id') ) === 0 )
		{
			if ( ( $cookie_id = $this->_get_users_cookie_id() ) === FALSE )
			{
				return $this->no_results( 'super_search' );
			}
		}

		// -------------------------------------
		//	Start the SQL
		// -------------------------------------

		$sql	= "/* Super Search fetch history items */ SELECT history_id AS super_search_id, search_date AS super_search_date, search_name AS super_search_name, results AS super_search_results, saved AS super_search_saved, query
					FROM exp_super_search_history
					WHERE site_id IN ( ".implode( ',', $this->EE->TMPL->site_ids )." )";

		if ( empty( $member_id ) === FALSE )
		{
			$sql	.= " AND member_id = ".$this->EE->db->escape_str( $member_id );
		}
		elseif ( empty( $cookie_id ) === FALSE )
		{
			$sql	.= " AND cookie_id = ".$this->EE->db->escape_str( $cookie_id );
		}

		// -------------------------------------
		//	Filter on saved?
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('saved') !== FALSE AND $this->EE->TMPL->fetch_param('saved') != '' )
		{
			if ( $this->EE->TMPL->fetch_param('saved') == 'yes' )
			{
				$sql	.= " AND saved = 'y'";
			}
			elseif ( $this->EE->TMPL->fetch_param('saved') == 'no' )
			{
				$sql	.= " AND saved = 'n'";
			}
		}
		else
		{
			$sql	.= " AND saved = 'y'";
		}

		// -------------------------------------
		//	Order
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('orderby') !== FALSE AND in_array( $this->EE->TMPL->fetch_param('orderby'), array( 'results', 'saved', 'search_date' ) ) === TRUE )
		{
			$sql	= " ORDER BY ".$this->EE->TMPL->fetch_param('orderby');

			if ( $this->EE->TMPL->fetch_param('sort') !== FALSE AND in_array( $this->EE->TMPL->fetch_param('sort'), array( 'asc', 'desc' ) ) === TRUE )
			{
				$sql	.= " ".$this->EE->TMPL->fetch_param('sort');
			}
		}
		elseif ( $this->EE->TMPL->fetch_param('order') !== FALSE AND in_array( $this->EE->TMPL->fetch_param('order'), array( 'results', 'saved', 'search_date' ) ) === TRUE )
		{
			$sql	= " ORDER BY ".$this->EE->TMPL->fetch_param('order');

			if ( $this->EE->TMPL->fetch_param('sort') !== FALSE AND in_array( $this->EE->TMPL->fetch_param('sort'), array( 'asc', 'desc' ) ) === TRUE )
			{
				$sql	.= " ".$this->EE->TMPL->fetch_param('sort');
			}
		}
		else
		{
			$sql	.= " ORDER BY search_date DESC";
		}

		// -------------------------------------
		//	Limit
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('limit') !== FALSE AND is_numeric( $this->EE->TMPL->fetch_param('limit') ) === TRUE )
		{
			$sql	.= " LIMIT ".$this->EE->TMPL->fetch_param('limit');
		}

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= $this->EE->db->query( $sql );

		if ($query->num_rows() === 0 )
		{
			return $this->no_results( 'super_search' );
		}

		// -------------------------------------
		//	Find out what we need from tagdata
		// -------------------------------------

		$i	= 0;

		foreach ( $this->EE->TMPL->var_single as $key => $val )
		{
			$i++;

			if ( strpos( $key, 'format=' ) !== FALSE )
			{
				$full	= $key;
				$key	= preg_replace( "/(.*?)\s+format=[\"'](.*?)[\"']/s", '\1', $key );
				$dates[$key][$i]['format']	= $val;
				$dates[$key][$i]['full']	= $full;
			}
		}

		// -------------------------------------
		//	Localize
		// -------------------------------------

		if ( empty( $dates ) === FALSE )
		{
			setlocale( LC_TIME, $this->EE->session->userdata('time_format') );
		}

		// -------------------------------------
		//	Parse
		// -------------------------------------

		$prefix	= 'super_search_';
		$r		= '';
		$vars	= array();
		$i  	= 0;

		foreach ( $query->result_array() as $row )
		{
			$i++;

			$tagdata	= $this->EE->TMPL->tagdata;

			// -------------------------------------
			//	Prep query into row
			// -------------------------------------

			if ( $row['query'] != '' )
			{
				$vars	= $this->_extract_vars_from_query( unserialize( base64_decode( $row['query'] ) ) );
			}

			// -------------------------------------
			//	Add some additional data into our vars
			// -------------------------------------

			$row[$prefix.'count'] = $i;
			$row['count'] = $i;
			$row[$prefix.'total_results'] = $query->num_rows();
			$row['total_results'] = $query->num_rows();

			// -------------------------------------
			//	Conditionals and switch
			// -------------------------------------

			$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, $row );
			$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, $vars );
			$tagdata	= $this->_parse_switch( $tagdata );

			// -------------------------------------
			//	Loop for dates
			// -------------------------------------

			if ( empty( $dates ) === FALSE )
			{
				foreach ( $dates as $field => $date )
				{
					foreach ( $date as $key => $val )
					{
						if ( isset( $row[$field] ) === TRUE AND is_numeric( $row[$field] ) === TRUE )
						{
							$tagdata	= str_replace( LD.$val['full'].RD, $this->_parse_date( $val['format'], $row[$field] ), $tagdata );
						}
					}
				}
			}

			unset( $row['super_search_date'] );

			// -------------------------------------
			//	Regular parse
			// -------------------------------------

			foreach ( $row as $key => $val )
			{
				$key	= $key;

				if ( strpos( LD.$key, $tagdata ) !== FALSE ) continue;

				$tagdata	= str_replace( LD.$key.RD, $val, $tagdata );
			}

			// -------------------------------------
			//	Variable parse
			// -------------------------------------

			foreach ( $vars as $key => $val )
			{
				$key	= $key;

				if ( strpos( LD.$key, $tagdata ) !== FALSE ) continue;

				$tagdata	= str_replace( LD.$key.RD, $val, $tagdata );
			}

			// -------------------------------------
			//	Parse empties
			// -------------------------------------

			$tagdata	= $this->_strip_variables( $tagdata );

			$r	.= $tagdata;
		}

		return $r;
	}

	//	End history

	// -------------------------------------------------------------

	/**
	 * Homogenize var name
	 *
	 * This methods adds the appropriate prefix of 'super_search' to the front of strings.
	 *
	 * @access	private
	 * @return	string
	 */

	function _homogenize_var_name( $str = '' )
	{
		if ( strncmp( 'super_', $str, 6 ) == 0 )
		{
			$str	= str_replace( 'super_', '', $str );
		}

		if ( strncmp( 'search_', $str, 7 ) == 0 )
		{
			$str	= str_replace( 'search_', '', $str );
		}

		return 'super_search_' . $str;
	}

	//	End homogenize var name

	// -------------------------------------------------------------

	/**
	 * In array insensitive
	 *
	 * PHP's in_array is case sensitive. We sometimes need a looser version
	 *
	 * @access	private
	 * @return	boolean
	 */

	private function _in_array_insensitive( $str = '', $array = array() )
	{
		return in_array( strtolower( $str ), array_map( 'strtolower', $array ) );
	}

	//	End in array insensitive

	// -------------------------------------------------------------

	/**
	 * Only numeric
	 *
	 * Returns an array containing only numeric values
	 *
	 * @access		private
	 * @return		array
	 */

	function _only_numeric( $array )
	{
		if ( empty( $array ) === TRUE ) return array();

		if ( is_array( $array ) === FALSE )
		{
			$array	= array( $array );
		}

		foreach ( $array as $key => $val )
		{
			if ( preg_match( '/[^0-9]/', $val ) != 0 OR trim($val) == '' ) unset( $array[$key] );
		}

		if ( empty( $array ) === TRUE ) return array();

		return $array;
	}

	//	End only numeric

	// -------------------------------------------------------------

	/**
	 * Parse date
	 *
	 * Parses an EE date string.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_date( $format = '', $date = 0 )
	{
		if ( $format == '' OR $date == 0 ) return '';

		// -------------------------------------
		//	strftime is much faster, but we have to convert date codes from what EE users expect to use
		// -------------------------------------

		// return strftime( $format, $date );

		// -------------------------------------
		//	EE's built in date parser is slow, but for now we'll use it
		// -------------------------------------

		$codes	= $this->EE->localize->fetch_date_params( $format );

		if ( empty( $codes ) ) return '';

		foreach ( $codes as $code )
		{
			$format	= str_replace( $code, $this->EE->localize->convert_timestamp( $code, $date, TRUE, TRUE ), $format );
		}

		return $format;
	}

	//	End parse date

	// -------------------------------------------------------------

	/**
	 * Parse date to timestamp
	 *
	 * Parses Super Search date string to a timestamp.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_date_to_timestamp( $date = '', $prefix = '', $full_day = FALSE )
	{
		if ( $date == '' ) return '';

		$return	= '';

		$hour = 0; $minute = 0; $second = 0; $month = 1; $day = 1;

		if ( $full_day === TRUE )
		{
			$hour = 23; $minute = 59; $second = 59; $month = 12; $day = 31;
		}

		$thedate	= $this->_split_date( $date );

		//	mktime( hour, minute, second, month, day, year )

		switch ( count( $thedate ) )
		{
			case 1: // We have a mistake in the date somehow, fail gracefully by forcing the whole SQL query that this is built into to return no results.
				$return = '0=1';
				break;
			case 2:	// We have year only
				$day	= ( $full_day === FALSE ) ? $day: $this->EE->localize->fetch_days_in_month( $month, $thedate[0].$thedate[1] );
				$return	= $prefix .mktime( $hour, $minute, $second, $month, $day, $thedate[0].$thedate[1] );
				break;
			case 3:	// We have year and month
				$day	= ( $full_day === FALSE ) ? $day: $this->EE->localize->fetch_days_in_month( $thedate[2], $thedate[0].$thedate[1] );
				$return	= $prefix . mktime( $hour, $minute, $second, $thedate[2], $day, $thedate[0].$thedate[1] );
				break;
			case 4:	// We have year, month, day
				$return	= $prefix . mktime( $hour, $minute, $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1] );
				break;
			case 5:	// We have year, month, day and hour
				$return	= $prefix . mktime( $thedate[4], $minute, $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1] );
				break;
			case 6:	// We have year, month, day, hour and minute
				$return	= $prefix . mktime( $thedate[4], $thedate[5], $second, $thedate[2], $thedate[3], $thedate[0].$thedate[1] );
				break;
			case 7:	// We have year, month, day, hour, minute and second
				$return	= $prefix . mktime( $thedate[4], $thedate[5], $thedate[6], $thedate[2], $thedate[3], $thedate[0].$thedate[1] );
				break;
		}

		return $return;
	}

	//	End parse date to timestamp

	// -------------------------------------------------------------

	/**
	 * Parse from template params
	 *
	 * @access	private
	 * @return	string
	 */

	function _parse_from_tmpl_params()
	{
		// -------------------------------------
		//	Parse basic parameters
		// -------------------------------------
		//	We are looking for a parameter that we expect to occur only once. Its argument can contain multiple terms following the Google syntax for 'and' 'or' and 'not'.
		// -------------------------------------

		foreach ( $this->basic as $key )
		{
			if ( $this->EE->TMPL->fetch_param($key) !== FALSE AND $this->EE->TMPL->fetch_param($key) != '' )
			{
				$param	= $this->EE->TMPL->fetch_param($key);

				// -------------------------------------
				//	Convert protected strings
				// -------------------------------------

				//	Double ampersands are allowed and indicate inclusive searching

				if ( strpos( $param, '&&' ) !== FALSE )
				{
					$param	= str_replace( '&&', $this->doubleampmarker, $param );
				}

				//	Protect dashes for negation so that we don't have conflicts with dash in url titles

				if ( strpos( $param, $this->separator.'-' ) !== FALSE )
				{
					$param	= str_replace( $this->separator.'-', $this->negatemarker, $param );
				}

				if ( strpos( $param, '-' ) === 0 )
				{
					$param	= str_replace( '-', $this->negatemarker, $param );
				}

				if ( strpos( $param, SLASH ) !== FALSE OR strpos( $param, $this->slash ) !== FALSE )
				{
					$param	= str_replace( array( SLASH, $this->slash ), '/', $param );
				}

				$q[$key]	= $param;
			}
		}

		if ( empty( $q ) === TRUE )
		{
			// If the template params are totaly empty, default the limit
			// to the channel limit, just so we emulate what the
			// channel:entries tag does when passed no params
			// otherwise we'll cause issues with no params at all
			$q['limit'] = 100;
		}

		ksort( $q );
		$this->sess['uri']	= $q;

		return $q;
	}

	//	End parse from template params

	// -------------------------------------------------------------

	/**
	 * Parse no results condition
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_no_results_condition( $q = array() )
	{
		if ( strpos( $this->EE->TMPL->template, LD.'super_search_total_results'.RD ) !== FALSE )
		{
			$this->EE->TMPL->template	= str_replace( LD.'super_search_total_results'.RD, '0', $this->EE->TMPL->template );
		}

		if ( strpos( $this->EE->TMPL->template, LD.'super_search_suggestion'.RD ) !== FALSE )
		{
			if ( !isset( $q['suggestion'] ) )
			{
				$this->EE->TMPL->template	= str_replace( LD.'super_search_suggestion'.RD, '', $this->EE->TMPL->template );
			}
			else
			{
				$this->EE->TMPL->template	= str_replace( LD.'super_search_suggestion'.RD, $q['suggestion'], $this->EE->TMPL->template );
			}
		}

		return TRUE;
	}

	//	End parse no results condition

	// -------------------------------------------------------------

	/**
	 * Parse required condition
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_required_condition( $tagdata = '', $required = array() )
	{
		// -------------------------------------
		//	Just cleaning up?
		// -------------------------------------

		if ( empty( $required ) )
		{
			$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, array( 'super_search_missing_required_fields' => FALSE ) );
			$tagdata	= str_replace( LD . 'super_search_required_fields' . RD, '', $tagdata );

			return $tagdata;
		}

		// -------------------------------------
		//	Total results
		// -------------------------------------

		if ( strpos( $this->EE->TMPL->template, LD.'super_search_total_results'.RD ) !== FALSE )
		{
			$this->EE->TMPL->template	= str_replace( LD.'super_search_total_results'.RD, '0', $this->EE->TMPL->template );
		}

		// -------------------------------------
		//	Conditionals
		// -------------------------------------

		$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, array( 'super_search_missing_required_fields' => TRUE ) );

		// -------------------------------------
		//	Variable pair for super_search_required_fields
		// -------------------------------------

		$name	= 'super_search_required_fields';

		if ( preg_match_all("|".LD.$name.'.*?'.RD.'(.*?)'.LD.preg_quote(T_SLASH, '/').$name.RD."|s", $tagdata, $matches) )
		{
			foreach ( $matches[0] as $key => $match )
			{
				$r		= '';

				foreach ( $required as $k => $v )
				{
					$tdata	= $matches[1][$key];
					$tdata	= $this->EE->functions->prep_conditionals( $tdata, array( 'super_search_name' => $k, 'super_search_label' => $v ) );
					$tdata	= str_replace( array( LD.'super_search_name'.RD, LD.'super_search_label'.RD ), array( $k, $v ), $tdata );
					$r	.= $tdata;
				}

				$tagdata	= str_replace( $matches[0][$key], $r, $tagdata );
			}
		}

		// -------------------------------------
		//	Pagination
		// -------------------------------------

		if ( strpos( $tagdata, LD . 'paginate' ) !== FALSE )
		{
			$tagdata	= preg_replace( "/" . LD . "paginate" . RD . "(.*?)" . LD . preg_quote(T_SLASH, '/') . "paginate" . RD . "/s", "", $tagdata );
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $tagdata;
	}

	//	End parse required condition

	// -------------------------------------------------------------

	/**
	 * Parse template vars
	 *
	 * @access	private
	 * @return	string
	 */

	function _parse_template_vars()
	{
		$p		= 'super_search_';
		$parse	= array();

		if ( ( $sess = $this->sess( 'uri' ) ) === FALSE )
		{
			$sess	= array();
		}

		foreach ( $this->basic as $key )
		{
			if ( strpos( $this->EE->TMPL->template, $p . $key ) === FALSE ) continue;

			$parse[ $p . $key ]	= '';

			if ( isset( $sess[$key] ) === TRUE )
			{
				// -------------------------------------
				//	Convert protected strings
				// -------------------------------------

				$sess[$key]	= str_replace( array( $this->negatemarker ), array( '-' ), $sess[$key] );

				// -------------------------------------
				//	Parse
				// -------------------------------------

				$parse[ $p . $key ]	= ( strpos( $sess[$key], $this->doubleampmarker ) === FALSE ) ? $sess[$key]: str_replace( $this->doubleampmarker, '&&', $sess[$key] );
			}
		}

		// -------------------------------------
		//	Prepare boolean variables
		// -------------------------------------
		//	Some search parameters can have multiple values, like status. People can search for multiple status params at once. We would like to be able to evaluate for the presence of each of those statuses as boolean variables. So this: search&status=open+closed+"First+Looks+-empty" would allow {super_search_status_open}, {super_search_status_closed}, {super_search_status_First_Looks} and {super_search_status_not_empty} to evaluate as true. We replace spaces with underscores and quotes with nothing.
		// -------------------------------------

		foreach ( array( 'channel', 'status', 'category' ) as $key )
		{
			if ( isset( $sess[$key] ) === FALSE ) continue;

			$temp	= $this->_prep_keywords( str_replace( '-', $this->negatemarker, $sess[$key] ) );

			if ( isset( $temp['or'] ) === TRUE )
			{
				foreach ( $temp['or'] as $val )
				{
					$val	= str_replace( ' ', '_', $val );

					$parse[ $p . $key . '_' . $val ]	= TRUE;
				}
			}

			if ( isset( $temp['not'] ) === TRUE )
			{
				foreach ( $temp['not'] as $val )
				{
					$val	= str_replace( ' ', '_', $val );

					$parse[ $p . $key . '_not_' . $val ]	= TRUE;
				}
			}
		}

		// -------------------------------------
		//	Prepare date from and date to
		// -------------------------------------

		$parse[ $p.'date'.$this->modifier_separator.'from' ]	= '';
		$parse[ $p.'date'.$this->modifier_separator.'to' ]		= '';

		if ( isset( $sess['datefrom'] ) === TRUE )
		{
			$parse[ $p.'date'.$this->modifier_separator.'from' ]	= $sess['datefrom'];
		}

		if ( isset( $sess['dateto'] ) === TRUE )
		{
			$parse[ $p.'date'.$this->modifier_separator.'to' ]	= $sess['dateto'];
		}

		// -------------------------------------
		//	Allow an alias of date for entry_date
		// -------------------------------------

		$parse[ $p.'entry_date'.$this->modifier_separator.'from' ]	= '';
		$parse[ $p.'entry_date'.$this->modifier_separator.'to' ]		= '';

		if ( isset( $sess['datefrom'] ) === TRUE )
		{
			$parse[ $p.'entry_date'.$this->modifier_separator.'from' ]	= $sess['datefrom'];
		}

		if ( isset( $sess['dateto'] ) === TRUE )
		{
			$parse[ $p.'entry_date'.$this->modifier_separator.'to' ]	= $sess['dateto'];
		}

		// -------------------------------------
		//	Prepare expiry date from and date to
		// -------------------------------------

		$parse[ $p.'expiry_date'.$this->modifier_separator.'from' ]	= '';
		$parse[ $p.'expiry_date'.$this->modifier_separator.'to' ]		= '';

		if ( isset( $sess['expiry_datefrom'] ) === TRUE )
		{
			$parse[ $p.'expiry_date'.$this->modifier_separator.'from' ]	= $sess['expiry_datefrom'];
		}

		if ( isset( $sess['expiry_dateto'] ) === TRUE )
		{
			$parse[ $p.'expiry_date'.$this->modifier_separator.'to' ]	= $sess['expiry_dateto'];
		}

		// -------------------------------------
		//	Prepare custom fields
		// -------------------------------------

		if ( ( $fields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE )
		{
			foreach ( $fields as $key => $val )
			{
				if ( strpos( $this->EE->TMPL->template, $p.$key ) === FALSE
					AND strpos( $this->EE->TMPL->template, $p.'exact'.$this->modifier_separator.$key ) === FALSE
					AND strpos( $this->EE->TMPL->template, $p.$key.$this->modifier_separator.'exact' ) === FALSE
					AND strpos( $this->EE->TMPL->template, $p.$key.$this->modifier_separator.'empty' ) === FALSE
					AND strpos( $this->EE->TMPL->template, $p.$key.$this->modifier_separator.'from' ) === FALSE
					AND strpos( $this->EE->TMPL->template, $p.$key.$this->modifier_separator.'to' ) === FALSE ) continue;

				$parse[ $p.$key ]			= '';
				$parse[ $p.'exact'.$this->modifier_separator.$key ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'exact' ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'empty' ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'from' ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'to' ]	= '';

				if ( isset( $sess['field'][$key] ) === TRUE )
				{
					$parse[ $p.$key ]	= ( strpos( $sess['field'][$key], $this->doubleampmarker ) === FALSE ) ? $sess['field'][$key]: str_replace( $this->doubleampmarker, '&&', $sess['field'][$key] );
				}

				if ( isset( $sess['exactfield'][$key] ) === TRUE )
				{

					if ( is_array( $sess['exactfield'][$key] ) )
					{
						$collapased = implode( '|',  $sess['exactfield'][$key] );

						$parse[ $p . 'exact' . $this->modifier_separator . $key ]	=
							( strpos( $collapased, $this->doubleampmarker ) === FALSE )
								? $collapased
								: str_replace( $this->doubleampmarker, '&&', $collapased );

					}
					else
					{
						$parse[ $p.$key.$this->modifier_separator.'exact' ]	=
							( strpos( $sess['exactfield'][$key], $this->doubleampmarker ) === FALSE )
								? $sess['exactfield'][$key]
								: str_replace( $this->doubleampmarker, '&&', $sess['exactfield'][$key] );

					}
				}

				if ( isset( $sess['empty'][$key] ) === TRUE )
				{
					$parse[ $p.$key.$this->modifier_separator.'empty' ]	= $sess['empty'][$key];
				}

				if ( isset( $sess['from'][$key] ) === TRUE )
				{
					$parse[ $p.$key.$this->modifier_separator.'from' ]	= $sess['from'][$key];
				}

				if ( isset( $sess['to'][$key] ) === TRUE )
				{
					$parse[ $p.$key.$this->modifier_separator.'to' ]	= $sess['to'][$key];
				}
			}
		}

		// -------------------------------------
		//	Revert fake ampersands to real ones
		// -------------------------------------

		$parse	= str_replace( $this->ampmarker, '&', $parse );

		// -------------------------------------
		//	Manipulate $q
		// -------------------------------------

		if ( $this->EE->extensions->active_hook('super_search_parse_template_vars_end') === TRUE )
		{
			$parse	= $this->EE->extensions->universal_call( 'super_search_parse_template_vars_end', $this, $parse, $p );
		}

		// -------------------------------------
		//	Hack 'n' chomp
		// -------------------------------------

		$this->EE->TMPL->template	= $this->EE->functions->prep_conditionals( $this->EE->TMPL->template, $parse );

		foreach ( $parse as $key => $val )
		{
			if ( in_array( $key, array( $p.'date'.$this->modifier_separator.'from', $p.'date'.$this->modifier_separator.'to' ) ) === TRUE )
			{
				if ( strpos( $this->EE->TMPL->template, 'format=' ) !== FALSE )
				{
					if ( preg_match_all( "/" . LD . $key . "\s+format=[\"'](.*?)[\"']" . RD . "/s", $this->EE->TMPL->template, $format ) )
					{
						$full_day	= ( $key == $p.'date'.$this->modifier_separator.'to' ) ? TRUE: FALSE;

						foreach ( $format[0] as $k => $v )
						{
							if ( isset( $format[1][$k] ) === TRUE )
							{
								$this->EE->TMPL->template	= str_replace( $format[0][$k], $this->_parse_date( $format[1][$k], $this->_parse_date_to_timestamp( $val, '', $full_day ) ), $this->EE->TMPL->template );
							}
						}
					}
				}
			}

			$val	= ( strpos( $val, '"' ) === FALSE AND strpos( $val, "'" ) === FALSE ) ? $val: str_replace( array( '"', "'" ), array( '&quot;', '&#039;' ), stripslashes( $val ) );

			// -------------------------------------
			//	Special handling for custom order cases. You can provide this as a search argument: order=custom_field+custom+"A+,A,A-,B+,B,B-" and Super Search will sort results using the order rules specified on the custom field indicated. The plus and minus signs have to be protected.
			// -------------------------------------

			if ( $key == 'super_search_order' AND strpos( $val, 'custom' ) !== FALSE )
			{
				$this->EE->TMPL->template	= str_replace( LD.$key.RD, $val, $this->EE->TMPL->template );
			}
			else
			{
				$this->EE->TMPL->template	= str_replace( LD.$key.RD, str_replace( $this->spaces, ' ', $val ), $this->EE->TMPL->template );
			}
		}
	}

	//	End parse template vars

	// -------------------------------------------------------------

	/**
	 * Parse URI
	 *
	 * Tests for a URI segment with prefix of 'search&'. When found, explodes and parses that segment into search parameters. We remember to construct and save a URI appropriate query for use in pagination later.
	 *
	 * @access	private
	 * @return	array
	 */

	function _parse_uri( $str = '' )
	{
		if ( $this->EE->TMPL->fetch_param( 'dynamic' ) !== FALSE AND $this->check_no( $this->EE->TMPL->fetch_param( 'dynamic' ) ) === TRUE ) return FALSE;

		// -------------------------------------
		//	Parse URI into search array
		// -------------------------------------

		$q		= array();

		$str	= ( $str == '' ) ? $this->_get_uri('parse') : $str;

		if ( strpos( $str, 'search'.$this->parser ) !== FALSE OR strpos( $str, 'search?' ) !== FALSE )
		{
			// This is a bit of hacky work around to get the search results uri working with Google Anyalicts
			// For GA to parse the search results uri properly it needs a '?' in there
			// We let the form pass it through, then replace it with our parser marker so the regex stays happy
			$str = str_replace('search?','search'.$this->parser,$str);

			if ( preg_match( "/search".$this->parser."(.*?)\//s", $str, $match ) )
			{
				//remove any pagination data that's coming after the face. We dont want this later
				preg_match("#(^|\/)P(\d+)#", $str, $page_match);
				if (isset($page_match[0])) $str = str_replace( $page_match[0] , '' , $str );

				$olduri	= str_replace( rtrim( $match[0], '/' ), $this->urimarker, $str );
				$this->sess['olduri']	= $olduri;

				// -------------------------------------
				//	Convert protected entities
				// -------------------------------------

				if ( strpos( $match[1], '&#36;' ) !== FALSE )
				{
					$match[1]	= str_replace( array( '&#36;' ), array( '$' ), $match[1] );
				}

				if ( strpos( $match[1], $this->slash ) !== FALSE OR strpos( $match[1], SLASH ) !== FALSE )
				{
					$match[1]	= str_replace( array( SLASH, $this->slash ), '/', $match[1] );
				}

				$match[1]	= str_replace( ';', '', $match[1] );

				$newuri[]	= 'search';

				// -------------------------------------
				//	Convert protected strings
				// -------------------------------------

				//	Double ampersands are allowed and indicate inclusive searching

				if ( strpos( $match[1], '&&' ) !== FALSE )
				{
					// There is an edge condition where if a passed uri without a clean end
					// has double ampersands the parse incorrectly builds the inclusive set
					// 		ie. category=1&&2&&keywords=test
					// in this case the trailing && is replacing the native html '&' parser break
					// and fouling up, searching for categories 1, 2 and 'keywords=test' inclusively.
					// This breaks things.

					// to fix this we'll look for any &&'s and if there's a trailing '&' and the
					// following block contains a '=' we'll dynamically fix it, by removing one
					// of the trailing &'s

					$double_pattern = '/(&&)([a-zA-Z0-9_\-\.\+]+)=/i';

					if ( preg_match_all($double_pattern, $match[1], $submatches) )
					{
						// We have a match against our pattern, replace the stagglers with a single &

						foreach( $submatches[0] as $submatch )
						{
							$cleaned = str_replace( '&&', '&', $submatch );

							$match[1] = str_replace( $submatch, $cleaned, $match[1]);
						}
					}

					$match[1]	= str_replace( '&&', $this->doubleampmarker, $match[1] );
				}

				//	Protect dashes for negation so that we don't have conflicts with dash in url titles. Note that we only care about dashes preceded by a separator or spacer character, any other dash could be part of a valid word.

				if ( strpos( $match[1], $this->separator.'-' ) !== FALSE )
				{
					$match[1]	= str_replace( $this->separator.'-', $this->separator.$this->negatemarker, $match[1] );
				}

				if ( strpos( $match[1], $this->spaces.'-' ) !== FALSE )
				{
					$match[1]	= str_replace( $this->spaces.'-', $this->spaces.$this->negatemarker, $match[1] );
				}

				// -------------------------------------
				//	Explode the query into an array and prep it
				// -------------------------------------

				$tmp	= explode( $this->parser, $match[1] );

				foreach ( $tmp as $val )
				{
					// -------------------------------------
					//	Parse custom fields
					// -------------------------------------
					//	We loop through our searchable custom fields, see if they are in the URI, log them and move on.
					// -------------------------------------

					if ( $this->_fields( 'searchable' ) !== FALSE )
					{
						foreach ( $this->_fields() as $key => $v )
						{
							if ( strpos( $val, $key.$this->separator ) === 0 )
							{
								$newuri[]	= $val;
								$q['field'][$key]	= str_replace( $key.$this->separator, '', $val );
							}

							// -------------------------------------
							//	We're looking for custom fields with the prefix of 'exact'. They indicate that we want an exact match on the value of that field.
							// -------------------------------------

							if ( strpos( $val, 'exact'.$this->modifier_separator.$key.$this->separator ) === 0 )
							{
								$newuri[]	= $val;
								$q['exactfield'][$key]	= str_replace( 'exact'.$this->modifier_separator.$key.$this->separator, '', $val );
							}

							// -------------------------------------
							// Also allow the 'exact' prefix to come after the marker
							// -------------------------------------

							if ( strpos( $val, $key.$this->modifier_separator.'exact'.$this->separator ) === 0 )
							{
								$newuri[]	= $val;
								$q['exactfield'][$key]	= str_replace( $key.$this->modifier_separator.'exact'.$this->separator, '', $val );
							}

							// -------------------------------------
							//	We're looking for custom fields with the prefix of 'exact' that are sent through the query string as an array. They indicate that we want an exact match on the value of that field where several values are acceptable exact matches.
							// -------------------------------------

							if ( strpos( $val, 'exact'.$this->modifier_separator.$key ) === 0 AND preg_match( '/exact'.$this->modifier_separator.$key.'\_\d+/s', $val, $match ) )
							{
								$newuri[]	= $val;
								$temp = explode( $this->separator, $val );
								if ( isset( $temp[1] ) === FALSE ) continue;
								$q['exactfield'][$key][]	= $temp[1];
							}

							// -------------------------------------
							// Also allow the 'exact' prefix to come after the marker
							// -------------------------------------

							if ( strpos( $val, $key.$this->modifier_separator.'exact' ) === 0 AND preg_match( '/'.$key.$this->modifier_separator.'exact\_\d+/s', $val, $match ) )
							{
								$newuri[]	= $val;
								$temp = explode( $this->separator, $val );
								if ( isset( $temp[1] ) === FALSE ) continue;
								$q['exactfield'][$key][]	= $temp[1];
							}

							if ( strpos( $val, $key.$this->modifier_separator.'empty'.$this->separator ) === 0 )
							{
								$newuri[]	= $val;
								$q['empty'][$key]	= str_replace( $key.$this->modifier_separator.'empty'.$this->separator, '', $val );
							}

							if ( strpos( $val, $key.$this->modifier_separator.'from'.$this->separator ) === 0 )
							{
								$newuri[]	= $val;
								$q['from'][$key]	= str_replace( $key.$this->modifier_separator.'from'.$this->separator, '', $val );
							}

							if ( strpos( $val, $key.$this->modifier_separator.'to'.$this->separator ) === 0 )
							{
								$newuri[]	= $val;
								$q['to'][$key]	= str_replace( $key.$this->modifier_separator.'to'.$this->separator, '', $val );
							}
						}
					}

					if ( isset( $q['exactfield'] ) === TRUE ) ksort( $q['exactfield'] );
					if ( isset( $q['field'] ) === TRUE ) ksort( $q['field'] );

					// -------------------------------------
					//	Parse date ranges
					// -------------------------------------
					//	datefrom and dateto can be provided in order to find ranges of entries by date. 20090601 = June 1, 2009. 2009 = 2009. 200906 = June 2009. 2009060105 = 5 am June 1, 2009. 20090601053020 = 5:30 am and 20 seconds June 1, 2009. 2009060123 = 11 pm June 1, 2009.
					//	We parse the expiry and standard date together to stop substring matches for 'date-from' getting 'expiry_date-from'
					// -------------------------------------

					if ( strpos( $val, 'expiry_date'.$this->modifier_separator.'from' ) !== FALSE )
					{
						$newuri[]	= $val;
						$q['expiry_datefrom']	= str_replace( 'expiry_date'.$this->modifier_separator.'from'.$this->separator, '', $val );
					}
					else if ( strpos( $val, 'entry_date'.$this->modifier_separator.'from' ) !== FALSE )
					{
						$newuri[]	= $val;
						$q['datefrom']	= str_replace( 'entry_date'.$this->modifier_separator.'from'.$this->separator, '', $val );
					}
					else if ( strpos( $val, 'date'.$this->modifier_separator.'from' ) !== FALSE )
					{
						$newuri[]	= $val;
						$q['datefrom']	= str_replace( 'date'.$this->modifier_separator.'from'.$this->separator, '', $val );
					}

					if ( strpos( $val, 'expiry_date'.$this->modifier_separator.'to' ) !== FALSE )
					{
						$newuri[]	= $val;
						$q['expiry_dateto']	= str_replace( 'expiry_date'.$this->modifier_separator.'to'.$this->separator, '', $val );
					}
					else if ( strpos( $val, 'entry_date'.$this->modifier_separator.'to' ) !== FALSE )
					{
						$newuri[]	= $val;
						$q['dateto']	= str_replace( 'entry_date'.$this->modifier_separator.'to'.$this->separator, '', $val );
					}
					else if ( strpos( $val, 'date'.$this->modifier_separator.'to' ) !== FALSE )
					{
						$newuri[]	= $val;
						$q['dateto']	= str_replace( 'date'.$this->modifier_separator.'to'.$this->separator, '', $val );
					}

					// -------------------------------------
					//	Parse Inclusive Keywords
					// -------------------------------------
					//	We're allowing the user to pass through a marker to turn the individual keywords into an inclusive search rather than the standard 'or' search
					// -------------------------------------

					if ( strpos( $val, 'inclusive_keywords' ) !== FALSE )
					{
						$q['inclusive_keywords']	= str_replace( 'inclusive_keywords'.$this->separator, '', $val );
					}

					// -------------------------------------
					//	We allow users to enable keyword searching on author names, if they're passing this param set the marker
					// -------------------------------------

					if ( strpos( $val, 'keyword_search_author_name' ) !== FALSE )
					{
						$q['keyword_search_author_name']	= str_replace( 'keyword_search_author_name'.$this->separator, '', $val );
					}

					// -------------------------------------
					//	We allow users to enable keywords searching on category names, if they're passing this param set the marker
					// -------------------------------------

					if ( strpos( $val, 'keyword_search_category_name' ) !== FALSE )
					{
						$q['keyword_search_category_name']	= str_replace( 'keyword_search_category_name'.$this->separator, '', $val );
					}

					// -------------------------------------
					//	Parse basic parameters
					// -------------------------------------
					//	We are looking for a parameter that we expect to occur only once. Its argument can contain multiple terms following the Google syntax for 'and' 'or' and 'not'.
					// -------------------------------------

					foreach ( $this->basic as $key )
					{
						if ( strpos( $val, $key.$this->separator ) === 0 )
						{
							$newuri[]	= $val;

							$q[$key]	= str_replace( array( $key.$this->separator ), '', $val );
						}

						$q	= $this->_check_tmpl_params( $key, $q );
					}

					// -------------------------------------
					//	Parse array parameters
					// -------------------------------------
					//	We are looking for a parameter that we expect to occur once or more. Each of these will have an argument which will in turn serve as a parameter / argument set.
					// -------------------------------------

					foreach ( $this->arrays as $key )
					{
						if ( strpos( $val, $key.$this->separator ) === 0 )
						{
							$newuri[]	= $val;
							$argument	= str_replace( $key.$this->separator, '', $val );

							if ( strpos( $argument, $this->spaces ) !== FALSE )
							{
								$temp	= explode( $this->spaces, $argument );

								$q[$key][ array_shift( $temp ) ]	= implode( $this->spaces, $temp );
							}
						}

						if ( isset( $q[$key] ) === TRUE ) ksort( $q[$key] );
					}
				}

				ksort( $q );
				$this->sess['uri']	= $q;

				// -------------------------------------
				//	Save new uri
				// -------------------------------------
				//	We will very likely be paginating later. We will need a coherent search string for each pagination link. And at the very end of the string we place the 'start' parameter. Our pagination routine then appends the start number to that string.
				// -------------------------------------

				if ( ! empty( $newuri ) )
				{
					$newuri	= str_replace( array( $this->doubleampmarker, $this->negatemarker, '"', '\'' ), array( '&&', '-', '%22', '%27' ), implode( $this->parser, $newuri ) );

					if ( preg_match( '/offset' . $this->separator . '(\d+)?/s', $newuri ) == 0 )
					{
						// $newuri	.= $this->parser . 'offset' . $this->separator . '0';
					}

					$this->sess['newuri']	= $newuri;
				}

				// -------------------------------------
				//	Parse search vars in template
				// -------------------------------------

				$this->_parse_template_vars();

				// -------------------------------------
				//	Manipulate $q
				// -------------------------------------

				if ($this->EE->extensions->active_hook('super_search_parse_uri_end') === TRUE)
				{
					$q	= $this->EE->extensions->universal_call( 'super_search_parse_uri_end', $this, $q );
				}

				return $q;
			}
		}

		// -------------------------------------
		//	Parse search vars in template
		// -------------------------------------

		$this->_parse_template_vars();

		return FALSE;
	}

	//	End parse URI

	// -------------------------------------------------------------

	/**
	 * Parse param
	 *
	 * Tests for a URI segment with prefix of 'search&'. When found, explodes and parses that segment into search parameters.
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_param()
	{
		if ( $this->EE->TMPL->fetch_param('query') !== FALSE AND $this->EE->TMPL->fetch_param('query') != '' )
		{
			if ( strpos( $this->EE->TMPL->fetch_param('query'), 'search&' ) === FALSE )
			{
				return $this->_parse_uri( 'search&' . $this->EE->TMPL->fetch_param('query') );
			}
			else
			{
				return $this->_parse_uri( $this->EE->TMPL->fetch_param('query') );
			}
		}

		return FALSE;
	}

	//	End parse param

	// -------------------------------------------------------------

	/**
	 * Parse post
	 *
	 * We remember to construct and save a URI appropriate query for use in pagination later.
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _parse_post()
	{
		// -------------------------------------
		//	Prep
		// -------------------------------------

		if ( empty( $_POST ) === TRUE ) return FALSE;

		if ( $this->EE->TMPL->fetch_param( 'dynamic' ) !== FALSE AND $this->check_no( $this->EE->TMPL->fetch_param( 'dynamic' ) ) === TRUE ) return FALSE;

		$_POST	= $this->EE->security->xss_clean( $_POST );

		unset( $_POST['submit'] );

		// -------------------------------------
		//	Parse POST into search array
		// -------------------------------------

		$str	= '';
		$parsed	= array();
		$redirect_post = TRUE;
		$redirect_post_forced = FALSE;

		foreach ( $_POST as $key => $val )
		{
			if ( $val == '' OR in_array( $key, $parsed ) === TRUE ) continue;

			// -------------------------------------
			//	Redirect_post gets special handling
			// -------------------------------------

			if ( $key == 'redirect_post' )
			{
				if ( $this->check_no( $val ) === TRUE )
				{
					$redirect_post = FALSE;
				}

				$redirect_post_forced = TRUE;
			}

			// -------------------------------------
			//	Correct for some EE and user funky bits
			// -------------------------------------

			if ( is_array( $val ) === TRUE )
			{
				foreach ( $val as $k => $v )
				{
					if ( is_string( $v ) === TRUE )
					{
						$val[$k]	= str_replace( array( '/', ';', '&' ), array( $this->slash, '', '%26' ), $v );
					}
				}
			}

			if ( is_string( $val ) === TRUE )
			{
				$val	= str_replace( array( '/', ';', '&' ), array( $this->slash, '', '%26' ), $val );
			}

			// -------------------------------------
			//	Exact field arrays get special treatment
			// -------------------------------------

			if ( is_array( $val ) === TRUE AND strpos( $key, 'exact' ) === 0 )
			{
				$temp	= '';

				foreach ( $val as $k => $v )
				{
					if ( strpos( $v, '&&' ) !== FALSE )
					{
						$parsed[]	= $key.'_'.$k;
						$temp	.= $v;
					}
					else
					{
						$parsed[]	= $key.'_'.$k;
						$str	.= $key.'_'.$k.$this->separator.$v.$this->parser;
					}
				}

				if ( ! empty( $temp ) )
				{
					$str	.= $key . $this->separator . rtrim( $temp, '&' ) . $this->parser;
				}
			}

			// -------------------------------------
			//	Order field as an array gets special handling
			// -------------------------------------

			elseif ( $key == 'order' AND is_array( $val ) === TRUE )
			{
				$str	.= $key.$this->spaces;

				foreach ( $val as $v )
				{
					if ( $v == '' ) continue;
					$str	.= $v.$this->spaces;
				}

				$str	.= $this->parser;
			}

			// -------------------------------------
			//	Handle post arrays
			// -------------------------------------

			elseif ( is_array( $val ) === TRUE )
			{
				$str	.= $key.$this->separator;
				$temp	= '';

				foreach ( $val as $k => $v )
				{
					$v	= str_replace( '%26%26', '&&', $v );

					if ( strpos( $v, '&&' ) !== FALSE )
					{
						$parsed[]	= $key.'_'.$k;

						// -------------------------------------
						//	Spaces in an array POST value indicate that someone wants to do an exact phrase search, so we should put those in quotes for later parsing.
						// -------------------------------------

						if ( strpos( $v, ' ' ) !== FALSE )
						{
							$v	= '"' . str_replace( ' ', $this->spaces, rtrim( $v, '&' ) ) . '"&&';
						}

						$temp	.= $v;
					}
					else
					{
						$v		= stripslashes( $v );
						$parsed[]	= $key.'_'.$k;

						// -------------------------------------
						//	Spaces in an array POST value indicate that someone wants to do an exact phrase search, so we should put those in quotes for later parsing.
						// -------------------------------------

						if ( strpos( $v, ' ' ) !== FALSE )
						{
							$v	= '"' . str_replace( ' ', $this->spaces, $v ) . '"';
						}

						$str	.= $v.$this->spaces;
					}
				}

				if ( ! empty( $temp ) )
				{
					$str	.= rtrim( $temp, '&' );
				}

				$str	= rtrim( $str, $this->spaces );

				$str	.= $this->parser;
			}
			else
			{
				$str	.= $key.$this->separator.$val.$this->parser;
			}
		}

		$str	= rtrim( stripslashes( $str ), $this->parser );

		$str 	= str_replace(' ', $this->spaces, $str);

		if ( $str == '' ) return FALSE;

		// -------------------------------------
		//	Are we redirecting POST searches to the query string method?
		// -------------------------------------

		// We may have a race condition here
		// We're defaulting to yes, but that can get overridden by posts and template variables

		if ( $redirect_post_forced !== TRUE )
		{
			// We haven't been passed a value to use from the post data
			// Inspect the tmpl params to see if its anywhere there
			if ( $this->EE->TMPL->fetch_param('redirect_post') !== FALSE )
			{
				if ( $this->check_no( $this->EE->TMPL->fetch_param('redirect_post') ) )
				{
					$redirect_post = FALSE;
				}
				elseif ( $this->check_yes( $this->EE->TMPL->fetch_param('redirect_post') ) )
				{
					$redirect_post = TRUE;
				}
			}
		}

		if ( $redirect_post === TRUE )
		{
			$str	= trim( str_replace( array( SLASH, '%26%26' ), array( $this->slash, '&&' ), $str ), '&' );

			$return = '';

			if ( $redirect_post == FALSE )
			{
				$return	= $this->EE->TMPL->fetch_param('redirect_post');
			}

			$return	= $this->_chars_decode( $this->_prep_return( $return ) ) . 'search'.$this->parser.$str.'/';

			if ( $return != '' )
			{
				$this->EE->functions->redirect( $return );
				exit();
			}
		}

		// -------------------------------------
		//	Send it to _parse_uri()
		// -------------------------------------

		if ( ( $q = $this->_parse_uri( $this->EE->uri->uri_string . 'search' . $this->parser . $str . '/' ) ) === FALSE )
		{
			return FALSE;
		}

		// print_r( $q );

		return $q;
	}

	//	End parse post

	// -------------------------------------------------------------

	/**
	 * Parse search
	 *
	 * This routine hunts for a search string across template params, post, uri until it can return something juicy.
	 *
	 * @access	public
	 * @return	string
	 */

	function _parse_search()
	{
		// -------------------------------------
		//	Hardcoded query
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('search') !== FALSE AND $this->EE->TMPL->fetch_param('search') != '' )
		{
			$str	= ( strpos( $this->EE->TMPL->fetch_param('search'), 'search&' ) === FALSE ) ? 'search&' . $this->EE->TMPL->fetch_param('search'): $this->EE->TMPL->fetch_param('search');

			// -------------------------------------
			//	Handle special case of start param for pagination. When users say they want pagination but they are using the 'search' param, we need to reach into the URI and try to find the 'start' param and work from there. Kind of duct tape like.
			// -------------------------------------

			if ( $this->EE->TMPL->fetch_param( 'paginate' ) !== FALSE AND $this->EE->TMPL->fetch_param( 'paginate' ) != '' )
			{
				if ( preg_match( '/' . $this->parser . 'offset' . $this->separator . '(\d+)' . '/s', $this->EE->uri->uri_string, $match ) )
				{
					if ( preg_match( '/' . $this->parser . 'offset' . $this->separator . '(\d+)' . '/s', $str, $secondmatch ) )
					{
						$str	= str_replace( $secondmatch[0], $match[0], $str );
					}
					else
					{
						$str	= str_replace( 'search' . $this->parser, 'search' . $this->parser . trim( $match[0], $this->parser ) . $this->parser, $str );
					}
				}
			}

			// -------------------------------------
			//	Handle the special case where users have given the inclusive_keywords param
			// -------------------------------------

			if ( $this->EE->TMPL->fetch_param( 'inclusive_keywords' ) !== FALSE AND $this->check_no( $this->EE->TMPL->fetch_param('inclusive_keywords') ) )
			{
				$str	= str_replace( 'search' . $this->parser, 'search' . $this->parser . 'inclusive_keywords' . $this->separator . 'no' . $this->parser, $str );
			}

			if ( ( $q = $this->_parse_uri( $str.'/' ) ) === FALSE )
			{
				return FALSE;
			}
		}

		// -------------------------------------
		//	Otherwise we accept search queries
		//	from either	URI or POST. See if either
		//	is present, defaulting to POST.
		// -------------------------------------

		else
		{
			if ( ( $q = $this->_parse_post() ) === FALSE )
			{
				if ( ( $q = $this->_parse_uri() ) === FALSE )
				{
					if ( ( $q = $this->_parse_from_tmpl_params() ) === FALSE )
					{
						return FALSE;
					}
				}
			}
		}

		// -------------------------------------
		//	Good job get out?
		// -------------------------------------

		if ( empty( $q ) )
		{
			return FALSE;
		}

		return $q;
	}

	//	End parse search

	// -------------------------------------------------------------

	/**
	 * Parse switch
	 *
	 * Parses the friends_switch variable so that admins can create zebra stripe UI's.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_switch( $tagdata = '' )
	{
		if ( $tagdata == '' ) return '';

		// -------------------------------------
		//	Parse Switch
		// -------------------------------------

		if ( $this->parse_switch != '' OR preg_match( "/".LD."(switch\s*=(.+?))".RD."/is", $tagdata, $match ) > 0 )
		{
			$this->parse_switch	= ( $this->parse_switch != '' ) ? $this->parse_switch: $match;

			$val	= $this->cycle( explode( '|', str_replace( array( '"', "'" ), '', $this->parse_switch['2'] ) ) );

			$tagdata = str_replace( $this->parse_switch['0'], $val, $tagdata );
		}

		return $tagdata;
	}

	//	End parse date

	// -------------------------------------------------------------

	/**
	 * Parse for required
	 *
	 * If some search arguments have been required to make for a valid search, test their presence here.
	 *
	 * @access	private
	 * @return	str
	 */

	function _parse_for_required( $q = array() )
	{
		// -------------------------------------
		//	Is anything being required?
		// -------------------------------------

		if ( isset( $this->EE->TMPL ) === TRUE AND $this->EE->TMPL->fetch_param('required') !== FALSE AND $this->EE->TMPL->fetch_param('required') != '' )
		{
			$required	= explode( '|', $this->EE->TMPL->fetch_param('required') );

			foreach ( $required as $val )
			{
				$out[$val]	= ucwords( str_replace( '_', ' ', $val ) );
			}

			$required	= $out;
		}
		else
		{
			return FALSE;
		}

		// -------------------------------------
		//	Loop through our query and see what's required
		// -------------------------------------

		foreach ( $q as $key => $val )
		{
			if ( is_string( $val ) === TRUE AND ! empty( $val ) )
			{
				unset( $required[$key] );
			}

			if ( is_array( $val ) === TRUE )
			{
				foreach ( $val as $k => $v )
				{
					if ( is_string( $v ) === TRUE AND ! empty( $v ) )
					{
						unset( $required[$k] );
					}
				}
			}
		}

		// -------------------------------------
		//	Is there anything left in $required?
		// -------------------------------------

		if ( ! empty( $required ) )
		{
			return $required;
		}

		// -------------------------------------
		//	Return happy.
		// -------------------------------------

		return FALSE;
	}

	//	End parse for required

	// -------------------------------------------------------------

	/**
	 *	Paste Removed Data Back into a String
	 *
	 *	@access		public
	 *	@param		string
	 *	@return		string
	 */

	function paste($subject)
	{
		foreach($this->_buffer as $key => $val)
		{
			$subject = str_replace(' '.$this->marker.$key.$this->marker.' ', $val, $subject);
		}

		return $subject;
	}
	// END paste()

	// -------------------------------------------------------------

	/**
	 * Prep author
	 *
	 * Important: If channel entries have gotten into EE in some atypical way and the total_entries count is 0 for a valid author, the search will fail for that author.
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_author( $author = array() )
	{
		if ( empty( $author['not'] ) === TRUE AND empty( $author['or'] ) === TRUE ) return FALSE;

		$indicator	= 'username';

		if ( $this->EE->TMPL->fetch_param('author_indicator') !== FALSE AND in_array( $this->EE->TMPL->fetch_param('author_indicator'), array( 'author_id', 'member_id', 'screen_name', 'username' ) ) === TRUE )
		{
			$indicator	= $this->EE->TMPL->fetch_param('author_indicator');
		}

		$indicator	= ( $indicator == 'author_id' ) ? 'member_id': $indicator;

		$sql	= '/* Super Search: ' . __FUNCTION__ . ' */
		SELECT member_id FROM exp_members WHERE total_entries != 0';

		if ( ! empty( $author['not'] ) )
		{
			foreach ( $author['not'] as $key => $val )
			{
				$author['not'][$key]	= $this->EE->db->escape_str( $val );
			}

			if ( isset( $this->sess['search']['q']['partial_author'] ) === TRUE AND $this->check_yes( $this->sess['search']['q']['partial_author'] ) == TRUE AND $indicator != 'member_id' AND $indicator != 'author_id')
			{
				$sql .= ' AND ( ';

				foreach( $author['not'] AS $single )
				{
					$sql .= '( ' . $indicator . ' NOT LIKE \'%' . $this->EE->db->escape_str( $single ) . '%\' ) AND ';
				}

				$sql .= ' 1=1 ) ';

			}
			else
			{
				$sql	.= ' AND '.$indicator.' NOT IN (\''.implode( "','", $author['not'] ).'\')';
			}
		}

		if ( empty( $author['or'] ) === FALSE )
		{
			foreach ( $author['or'] as $key => $val )
			{
				$author['or'][$key]	= $this->EE->db->escape_str( $val );
			}

			if ( isset( $this->sess['search']['q']['partial_author'] ) === TRUE AND $this->check_yes( $this->sess['search']['q']['partial_author'] ) == TRUE AND $indicator != 'member_id' AND $indicator != 'author_id' )
			{
				$sql .= ' AND ( ';

				foreach( $author['or'] AS $single )
				{
					$sql .= '( ' . $indicator . ' LIKE \'%' . $this->EE->db->escape_str( $single ) . '%\' ) OR ';
				}

				$sql .= ' 1=0 ) ';

			}
			else
			{
				$sql	.= ' AND '.$indicator.' IN (\''.implode( "','", $author['or'] ).'\')';
			}
		}

		unset( $author );

		$query	= $this->EE->db->query( $sql );

		if ( $query->num_rows() == 0 ) return FALSE;

		foreach ( $query->result_array() as $row )
		{
			$author[]	= $row['member_id'];
		}

		return $author;
	}

	//	End prep author

	// -------------------------------------------------------------

	/**
	 * Prep channel
	 *
	 * Returns an array of channel ids. This method has a wide ranging effect on behaviour. It looks for hard coded channel ids in a template param. As well, it looks for channel names provided in either a template param called 'channel' or in the URI preceded by the marker 'channel'. In either of these last two cases, regular search syntax can be used to include or exclude channels for search.
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_channel( $channel_string = '' )
	{
		// -------------------------------------
		//	Do we have hardcoded channel ids?
		// -------------------------------------

		$channel_ids	= array();

		if ( $this->EE->TMPL->fetch_param( $this->sc->db->channel_id ) !== FALSE AND $this->EE->TMPL->fetch_param( $this->sc->db->channel_id ) != '' )
		{
			$channel_ids	= explode( '|', $this->EE->TMPL->fetch_param( $this->sc->db->channel_id ) );
		}

		// -------------------------------------
		//	Channel names in a param or in the URI.
		// -------------------------------------
		//	Remember, these can come through the 'channel' template param or through the 'channel' marker in the URI. Search syntax applies such that negated channels have their entries excluded from search.
		// -------------------------------------

		if ( ! empty( $channel_string ) )
		{
			$channel_names = $this->_prep_keywords( $channel_string );

			// -------------------------------------
			//	Do we have hardcoded channel names?
			// -------------------------------------

			if ( $this->EE->TMPL->fetch_param( 'channel' ) !== FALSE AND $this->EE->TMPL->fetch_param( 'channel' ) != '' )
			{
				// -------------------------------------
				//	Are we negating?
				// -------------------------------------

				if ( strpos( $this->EE->TMPL->fetch_param( 'channel' ), 'not ' ) === 0 )
				{
					$channel_names['not']	= ( isset( $channel_names['not'] ) === TRUE ) ? $channel_names['not']: array();
					$channel_names['not']	= array_merge( explode( "|", str_replace( "not ", "", $this->EE->TMPL->fetch_param( 'channel' ) ) ) );
				}
				else
				{
					$channel_names['or']	= ( isset( $channel_names['or'] ) === TRUE ) ? $channel_names['or']: array();
					$channel_names['or']	= array_intersect( explode( "|", $this->EE->TMPL->fetch_param( 'channel' ) ), $channel_names['or'] );

					// -------------------------------------
					//	If this specific filter § in no channel names, then the user asked to search for channels that were not allowed in the channel param. We should fail out in this condition.
					// -------------------------------------

					if ( empty( $channel_names['or'] ) )
					{
						return FALSE;
					}
				}
			}
		}

		// -------------------------------------
		//	Loop and filter
		// -------------------------------------

		$ids		= array();
		$channels	= array();

		foreach ( $this->data->get_channels_by_site_id_and_channel_id( $this->EE->TMPL->site_ids, $channel_ids ) as $row )
		{
			// -------------------------------------
			//	We don't want excluded blogs in our arrays
			// -------------------------------------

			if ( ! empty( $channel_names['not'] ) AND in_array( $row['channel_name'], $channel_names['not'] ) === TRUE ) continue;

			// -------------------------------------
			//	And if we only want certain blogs, then filter as well.
			// -------------------------------------

			if ( ! empty( $channel_names['or'] ) AND in_array( $row['channel_name'], $channel_names['or'] ) === FALSE ) continue;

			// -------------------------------------
			//	Populate arrays.
			// -------------------------------------

			$ids[]							= $row['channel_id'];
			$channels[$row['channel_id']]	= $row;
		}

		// -------------------------------------
		//	Empty after filtering? Fail out
		// -------------------------------------

		if ( count( $ids ) == 0 ) return FALSE;

		// -------------------------------------
		//	Add results to cache and return
		// -------------------------------------

		sort( $ids );
		$this->sess['search']['channel_ids']	= $ids;
		$this->sess['search']['channels']		= $channels;

		return $ids;
	}

	//	End prep channel

	// -------------------------------------------------------------

	/**
	 * Prep group
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_group( $group = array() )
	{
		if ( empty( $group['not'] ) AND empty( $group['or'] ) ) return FALSE;

		$sql	= '/* Super Search: ' . __FUNCTION__ . ' */
		SELECT m.member_id FROM exp_members m LEFT JOIN exp_member_groups mg ON mg.group_id = m.group_id WHERE mg.site_id = ' . $this->EE->db->escape_str( $this->EE->config->item('site_id') );

		if ( ! empty( $group['not'] ) )
		{
			foreach ( $group['not'] as $key => $val )
			{
				$group['not'][$key]	= $this->EE->db->escape_str( $val );
			}

			$sql	.= ' AND mg.group_title NOT IN (\''.implode( "','", $group['not'] ).'\')';
			$sql	.= ' AND mg.group_id NOT IN (\''.implode( "','", $group['not'] ).'\')';
		}

		if ( ! empty( $group['or'] ) )
		{
			foreach ( $group['or'] as $key => $val )
			{
				$group['or'][$key]	= $this->EE->db->escape_str( $val );
			}

			$sql	.= ' AND ( mg.group_title IN (\''.implode( "','", $group['or'] ).'\')';
			$sql	.= ' OR mg.group_id IN (\''.implode( "','", $group['or'] ).'\') )';
		}

		unset( $group );

		$query	= $this->EE->db->query( $sql );

		if ( $query->num_rows() == 0 ) return FALSE;

		foreach ( $query->result_array() as $row )
		{
			$group[]	= $row['member_id'];
		}

		return $group;
	}

	//	End prep group

	// -------------------------------------------------------------

	/**
	 * Prep site ids
	 *
	 *  We want to be able to dynamically assign site ids, but we need to be careful here on what we're letting through
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_site_ids( $site = '' )
	{
		if ( $site == '' ) return $this->EE->TMPL->site_ids;

		$arr = array();

		$str = $this->spaces;

		$sites = $this->EE->TMPL->sites;

		if ( strstr( $site, $this->pipes ) !== FALSE ) $str = $this->pipes;

		foreach( explode($str, $site) AS $site_id => $val )
		{
			if ( is_numeric( $val ) )
			{
				if ( isset( $sites[ $val ] ) ) $arr[] = $val;
			}
			else
			{
				foreach( $this->EE->TMPL->sites as $site_id => $site_name )
				{
					if ( $site_name == $val ) $arr[] = $site_id;
				}
			}
		}

		if ( empty ( $arr ) ) $arr = $this->EE->TMPL->site_ids;
		else $arr = array_unique( $arr );

		return $arr;
	}

	//	End prep_site_ids

	// -------------------------------------------------------------

	/**
	 * Clean keywords
	 *
	 *
	 * @access	private
	 * @return	string
	 */

	function _clean_keywords( $keywords = '' )
	{
		// -------------------------------------
		//	Convert spaces
		// -------------------------------------

		if ( strpos( $keywords, $this->spaces ) !== FALSE )
		{
			$keywords	= str_replace( $this->spaces, ' ', $keywords );
		}

		return $keywords;
	}

	//	End clean_keywords

	// -------------------------------------------------------------

	/**
	 * Prep keywords
	 *
	 * REGEX is expensive stuff. We could rewrite this method to explode into individual characters, loop through the resulting array, flag our identifiers like negation, quotes and such, and assemble keywords again as we go. Might be much faster. But as it stands, the method, on most keyword strings, executes silly fast.
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_keywords( $keywords = '' , $inclusive = FALSE, $type = '')
	{
		if ( is_string( $keywords ) === FALSE OR $keywords == '' ) return FALSE;

		$arr	= array( 'and' => array(), 'not' => array(), 'or' => array() );

		// -------------------------------------
		//	Are we using standard EE status syntax?
		// -------------------------------------

		if ( strpos( $keywords, '|' ) !== FALSE OR strpos( $keywords, 'not ' ) === 0 )
		{
			// -------------------------------------
			//	Are we negating?
			// -------------------------------------

			if ( strpos( $keywords, 'not ' ) === 0 )
			{
				$arr['not']	= explode( '|', str_replace( 'not ' , '', $keywords ) );
			}
			else
			{
				$arr['or']	= explode( '|', $keywords );
			}

			// -------------------------------------
			//	Save
			// -------------------------------------

			$arr['not']	= $this->_remove_empties( $arr['not'] );
			$arr['or']	= $this->_remove_empties( $arr['or'] );

			sort( $arr['not'] );
			sort( $arr['or'] );
			ksort( $arr );

			return $arr;
		}

		// -------------------------------------
		//	Basic cleanup
		// -------------------------------------

		$keywords = $this->_clean_keywords( $keywords );

		// -------------------------------------
		//	Parse out negated but quoted strings
		// -------------------------------------

		if ( preg_match_all( '/' . $this->negatemarker . '["](.*?)["]/s', $keywords, $match ) )
		{
			foreach ( $match[1] as $val )
			{
				$arr['not'][]	= $this->EE->db->escape_str( $val );
			}

			$keywords	= preg_replace( '/' . $this->negatemarker . '["](.*?)["]/s', '', $keywords );
		}

		// -------------------------------------
		//	Parse out inclusive strings
		// -------------------------------------
		//	This is a special case, not too common
		//	People can do inclusive category
		//	searching which means they can require
		//	that an entry belong to all of a given
		//	set of categories.
		// -------------------------------------

		$and	= 'or';

		if ( strpos( $keywords, $this->doubleampmarker ) !== FALSE )
		{
			$and		= 'and';
			$keywords	= explode( $this->doubleampmarker, $keywords );
		}
		else
		{
			$keywords	= array( $keywords );
		}

		// -------------------------------------
		//	Let's loop and parse our strings
		// -------------------------------------

		foreach ( $keywords as $phrase )
		{
			// -------------------------------------
			//	Parse out quoted strings
			// -------------------------------------

			if ( preg_match_all( '/["](.*?)["]/s', $phrase, $match ) )
			{
				foreach ( $match[1] as $val )
				{
					// -------------------------------------
					//	Filter and / or depending on inclusion
					// -------------------------------------
					//	This is deceptively simple and may just	plain not work. If we're in the context	of inclusion, quoted phrases go to the 'and' array, otherwise the 'or' array.
					// -------------------------------------

					$arr[$and][]	= $this->EE->db->escape_str( $val );
				}

				$phrase	= preg_replace( '/["](.*?)["]/s', '', $phrase );
			}

			// -------------------------------------
			//	Parse out negated strings
			// -------------------------------------

			if ( preg_match_all( "/".$this->negatemarker."([a-zA-Z0-9_]+)/s", $phrase, $match ) )
			{
				foreach ( $match[1] as $val )
				{
					$arr['not'][]	= $this->EE->db->escape_str( $val );
				}

				$phrase	= preg_replace( "/".$this->negatemarker."([a-zA-Z0-9_]+)/s", '', $phrase );
			}

			// -------------------------------------
			//	Load remaining OR keywords
			// -------------------------------------
			//	If we're in the context of inclusion, the first word in the phrase is added to the 'and' array while the others are given to the 'or' array. This means when I can ask for 'apples&&oranges bananas' I will end up retrieving entries that have both 'apples' and 'oranges' or 'bananas'.
			// -------------------------------------

			$temp	= explode( ' ', trim( $this->EE->db->escape_str( $phrase ) ) );

			if ( empty( $temp ) === FALSE AND $and == 'and' )	// That was fun to type :-)
			{
				$arr['and'][]	= array_shift( $temp );
			}

			if ( empty( $temp ) === FALSE )
			{
				$arr['or']	= array_merge( $arr['or'], $temp );
			}
		}

		// ---------------------------------------
		//	Inclusive Keywords
		// ---------------------------------------
		//	If we've been passed an inclusive variable we'll turn all our hard won $arr['or'] into $arr['and']. This can be set on the results loop, or passed through on the search params
		// --------------------------------------- */

		if ($inclusive AND isset($arr['or']) AND ( $type = 'keywords' OR $type = 'category' ) )
		{
			// Only turn keyword chunks that have more than one keyword
			// into inclusive sets, to avoid odd edge cases

			if ( count( $arr['or'] ) > 1 )
			{
				$arr['and'] = array_merge( $arr['and'], $arr['or'] );

				$arr['or'] = array();
			}
		}

		// -------------------------------------
		//	Save
		// -------------------------------------

		$arr['and']	= $this->_remove_empties( $arr['and'] );
		$arr['not']	= $this->_remove_empties( $arr['not'] );
		$arr['or']	= $this->_remove_empties( $arr['or'] );

		sort( $arr['and'] );
		sort( $arr['not'] );
		sort( $arr['or'] );
		ksort( $arr );

		// Do we want fuzzy matching?
		if ( $this->EE->config->item('enable_fuzzy_searching') == 'y' AND $type == 'keywords' AND APP_VER >= 2.0 )
		{
			$arr = $this->_prep_fuzzy_keywords( $arr );
		}

		//if ( $type == 'keywords' ) echo('<pre>'.print_R($arr,1).'</pre>');

		return $arr;
	}

	//	End prep keywords

	// -------------------------------------------------------------

	/**
	 * Prep Fuzzy Keywords
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_keywords( $arr = array() )
	{
		// -------------------------------------
		//	Pre prep hook
		// -------------------------------------

		if ( $this->EE->extensions->active_hook('super_search_prep_fuzzy_keywords_start') === TRUE )
		{
			$arr	= $this->EE->extensions->universal_call( 'super_search_prep_fuzzy_keywords_start', $this, $arr );
		}

		// We only want to use the fuzzy methods that are enabled
		// in this order

		// 1. Phonetics
		// 2. Plurals
		// 3. Basic spelling

		if ( $this->EE->config->item('enable_fuzzy_searching_phonetics') == 'y' AND ! empty( $arr ) )
		{
			$arr = $this->_prep_fuzzy_phonetics( $arr );
		}

		if ( $this->EE->config->item('enable_fuzzy_searching_plurals') == 'y' AND ! empty( $arr ) )
		{
			$arr = $this->_prep_fuzzy_plurals( $arr );
		}

		if ( $this->EE->config->item('enable_fuzzy_searching_spelling') == 'y' AND ! empty( $arr ) )
		{
			$arr = $this->_prep_fuzzy_spelling( $arr );
		}

		// -------------------------------------
		//	Post prep hook
		// -------------------------------------

		if ( $this->EE->extensions->active_hook('super_search_prep_fuzzy_keywords_end') === TRUE )
		{
			$arr	= $this->EE->extensions->universal_call( 'super_search_prep_fuzzy_keywords_end', $this, $arr );
		}

		return $arr;
	}

	//	End prep fuzzy keywords

	// -------------------------------------------------------------

	/**
	 * Prep Fuzzy Keywords - phonetics
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_phonetics( $arr = array() )
	{
		$this->EE->TMPL->log_item( 'Super Search: preping for fuzzy search by phonetics ' );

		$terms = array();

		// Handle 'ye ORs
		foreach( $arr['or'] as $or )
		{
			if ( ctype_digit( $or ) === TRUE ) continue;

			$terms[] = " SOUNDEX('".$this->EE->db->escape_str( $or )."') ";
		}

		// Handle 'ye ANDs
		foreach( $arr['and'] as $and )
		{
			if ( ctype_digit( $and ) === TRUE ) continue;

			$terms[] = " SOUNDEX('".$this->EE->db->escape_str( $and )."') ";
		}

		if ( empty( $terms ) ) return $arr;

		$sql = " SELECT term, term_soundex, first_seen, last_seen, count, entry_count
					FROM exp_super_search_terms
					WHERE term_soundex IN
						( " . implode( ", ", $terms ) . " ) ";

		// print_r( $sql );

		$query = $this->data->check_sql( $sql );

		if ( $query === FALSE ) return $arr;

		$temp = array();

		foreach( $query->result_array() as $row )
		{
			// We should have our original terms in here too
			if ( $this->_in_array_insensitive( $row['term'], array_values( $arr['or'] ) ) )
			{
				$temp[ $row['term_soundex'] ] = $row['term'];
			}

			// We should have our original terms in here too
			if ( $this->_in_array_insensitive( $row['term'], array_values( $arr['and'] ) ) )
			{
				$temp[ $row['term_soundex'] ] = $row['term'];
			}
		}

		foreach( $query->result_array() as $row )
		{
			// Get the matching top term for this $row
			if ( !( $this->_in_array_insensitive( $row['term'], array_values( $arr['or'] ) )
				OR $this->_in_array_insensitive( $row['term'], array_values( $arr['and'] ) ) ) )
			{
				// This is not one of the original terms
				// find it's matching parent

				// just to be safe
				if ( isset( $temp[ $row['term_soundex'] ] ) )
				{
					$parent = $temp[ $row['term_soundex'] ];

					// Append this to the proper place
					if ( $this->_in_array_insensitive( $parent, array_values( $arr['or'] ) ) )
					{
						//$arr['or'][] = $row['term'];

						if ( isset( $arr['or_fuzzy'][ $parent ] ) === FALSE )
						{
							$arr['or_fuzzy'][ $parent ] = array();
						}

						$arr['or_fuzzy'][ $parent ][] =$row['term'];
					}

					// Append this to the proper place
					if ( $this->_in_array_insensitive( $parent, array_values( $arr['and'] ) ) )
					{
						//$arr['and'][] = $row['term'];

						if ( isset( $arr['and_fuzzy'][ $parent ] ) === FALSE )
						{
							$arr['and_fuzzy'][ $parent ] = array();
						}

						$arr['and_fuzzy'][ $parent ][] =$row['term'];
					}
				}
			}
		}

		$this->EE->TMPL->log_item( 'Super Search: finished fuzzy phonetic keyword adjustment' );

		return $arr;
	}

	//	End prep fuzzy keywords

	// -------------------------------------------------------------


	/**
	 * Prep Fuzzy Keywords - pluarls
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_plurals( $arr = array() )
	{
		$this->EE->TMPL->log_item( 'Super Search: preping for fuzzy search by plurals ' );

		$terms = array();

		// Handle 'ye ORs
		foreach( $arr['or'] as $or )
		{
			$terms[] = $this->EE->db->escape_str( $or );
		}

		// Handle 'ye ANDs
		foreach( $arr['and'] as $and )
		{
			$terms[] = $this->EE->db->escape_str( $and );
		}

		if ( empty( $terms ) ) return $arr;

		$suggestions = array();
		$all = array();

		// Get our list of potential pluarls (and singulars)
		foreach( $terms as $term )
		{
			$temp = $this->_suggestion_plurals( $term );

			$suggestions[ $term ] = $temp;

			$all = array_merge( $all, $temp );
		}

		// Test these variants to see if they exist in the lexicon
		$sql = " SELECT term FROM exp_super_search_terms
					WHERE term in ('" . implode( "','", $all ) . "')
					AND entry_count > 0 ";

		$query = $this->data->check_sql( $sql );

		if ( $query === FALSE ) return $arr;

		$suggestions_valid = array();

		// now filter it down with just the valid terms in our lexicon
		if ( $query->num_rows() > 0 )
		{
			foreach( $query->result_array() as $row )
			{
				foreach( $suggestions as $parent => $variants )
				{
					if ( in_array( $row['term'], $variants ) )
					{
						$suggestions_valid[ $parent ][] = $row['term'];
					}
				}
			}
		}

		if ( count( $suggestions_valid ) > 0 )
		{
			// We have some valid suggestions

			foreach( $suggestions_valid as $parent => $valid )
			{
				if ( $this->_in_array_insensitive( $parent, $arr['or'] ) )
				{
					if ( isset( $arr['or_fuzzy'][ $parent ] ) === FALSE )
					{
						$arr['or_fuzzy'][ $parent ] = array();
					}

					$arr['or_fuzzy'][ $parent ] = array_merge( $arr['or_fuzzy'][ $parent ], $valid);
				}

				if ( $this->_in_array_insensitive( $parent, $arr['and'] ) )
				{
					//$arr['and'] = array_merge( $arr['and'], $valid );

					if ( isset( $arr['and_fuzzy'][ $parent ] ) === FALSE )
					{
						$arr['and_fuzzy'][ $parent ] = array();
					}

					$arr['and_fuzzy'][ $parent ] = array_merge( $arr['and_fuzzy'][ $parent ], $valid);
				}
			}
		}

		$this->EE->TMPL->log_item( 'Super Search: finished fuzzy plurals keyword adjustment' );

		return $arr;
	}

	//	End prep fuzzy pluarls

	// -------------------------------------------------------------

	/**
	 * Prep Fuzzy Keywords - spelling
	 *
	 * @access private
	 * @return array
	 */

	function _prep_fuzzy_spelling( $arr = array() )
	{
		$this->EE->TMPL->log_item( 'Super Search: prepping for fuzzy search by spelling ' );

		$terms = array();

		// Handle 'ye ORs
		foreach( $arr['or'] as $or )
		{
			$terms[ $or ] = $this->EE->db->escape_str( $or );
		}

		// Handle 'ye ANDs
		foreach( $arr['and'] as $and )
		{
			$terms[ $and ] = $this->EE->db->escape_str( $and );
		}

		if ( empty( $terms ) ) return $arr;

		$suggestions = array();
		$all = array();

		// Now we only have a terms list that contains invalid spellings (at least spellings that don't appear
		// in our corpus. ) Get our list of potential spellings
		$suggestions_valid = $this->_suggestion_spelling( $terms );

		if ( count( $suggestions_valid ) > 0 )
		{
			// We have some valid suggestions
			foreach( $suggestions_valid as $parent => $valid )
			{
				if ( $this->_in_array_insensitive( $parent, $arr['or'] ) )
				{
					if ( isset( $arr['or_fuzzy'][ $parent ] ) === FALSE )
					{
						$arr['or_fuzzy'][ $parent ] = array();
					}

					$arr['or_fuzzy'][ $parent ][] = $valid;
				}

				if ( $this->_in_array_insensitive( $parent, $arr['and'] ) )
				{
					if ( isset( $arr['and_fuzzy'][ $parent ] ) === FALSE )
					{
						$arr['and_fuzzy'][ $parent ] = array();
					}

					$arr['and_fuzzy'][ $parent ][] = $valid;
				}
			}
		}

		$this->EE->TMPL->log_item( 'Super Search: finished fuzzy spelling keyword adjustment' );

		return $arr;
	}

	//	End prep fuzzy spelling

	// -------------------------------------------------------------

	/**
	 * Prep order
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_order( $order = '' )
	{
		$arr	= array();

		// -------------------------------------
		//	Sticky test
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('sticky') === FALSE OR $this->check_no( $this->EE->TMPL->fetch_param('sticky') ) === FALSE )
		{
			$arr[]	= 't.sticky DESC';
		}

		// -------------------------------------
		//	Graceful fail
		// -------------------------------------

		if ( $order == '' )
		{
			$arr[]	= 't.entry_date DESC';
			$arr[]	= 't.entry_id DESC';
			return ' ORDER BY '.implode( ',', $arr );
		}

		// -------------------------------------
		//	Allow random ordering
		// -------------------------------------

		if ( $order == 'random' )
		{
			$arr[] = 'RAND()';
		}

		// -------------------------------------
		//	Protect custom ordering punctuation like +, -, etc. Some people will use Super Search to sort lists of students and their grades; A+ A B- etc. We need to protect the punctuation there.
		// -------------------------------------

		if ( strpos( $order, 'custom' ) !== FALSE )
		{
			if ( preg_match_all( '/([a-zA-Z0-9\-\_]+)\\' . $this->spaces . 'custom\\' . $this->spaces . '[\'\"]([\w\+\-,]+)[\'\"]/s', $order, $match ) )
			{
				foreach ( $match[0] as $key => $val )
				{
					$order	= str_replace( $val, '<<replace' . $key . 'replace>>', $order );

					if ( isset( $match[1][$key] ) === TRUE )
					{
						$custom_orders[$key]['field']	= $match[1][$key];

						if ( isset( $match[2][$key] ) === TRUE )
						{
							$custom_orders[$key]['value']	= $match[2][$key];
						}
					}
				}
			}
		}

		// -------------------------------------
		//	Convert order string to array
		// -------------------------------------

		if ( strpos( $order, $this->spaces ) === FALSE AND strpos( $order, ' ' ) === FALSE )
		{
			$order	= $order . "|asc";
		}
		else
		{
			$order	= str_replace( array( $this->spaces.$this->spaces.$this->spaces, $this->spaces.$this->spaces, $this->spaces ), ' ', strtolower( $order ) );

			if ( strpos( $order, ' desc' ) !== FALSE )
			{
				$order	= str_replace( ' desc', '|desc', $order );
			}

			if ( strpos( $order, ' asc' ) !== FALSE )
			{
				$order	= str_replace( ' asc', '|asc', $order );
			}
		}

		$order	= explode( ' ', $order );

		// -------------------------------------
		//	Process orders
		// -------------------------------------

		if ( is_array( $order ) === TRUE )
		{
			$customfields	= $this->_fields('all');
			$fields			= $this->_table_columns( $this->sc->db->titles );

			if ( isset( $custom_orders ) AND is_array( $custom_orders ) )
			{
				foreach( $custom_orders as $custom_order )
				{
					if ( $custom_order['field'] == 'entry_id' )
					{
						//For the odd circumstance where we're passed an order by entry_id
						$temp	= 'FIELD(';
						$temp	.= 'cd.entry_id';
						$temp	.= ',';
						$temp	.= "'" . str_replace( ",", "','", $custom_order['value'] ) . "'";
						$temp	.= ')';
						$arr[]	= $temp;
					}
				}
			}

			foreach ( $order as $str )
			{
				// -------------------------------------
				//	Can we detect custom orders?
				// -------------------------------------

				if ( preg_match( '/<<replace(\d+)replace>>/s', $str, $match ) )
				{
					if ( ! empty( $custom_orders[ $match[1] ]['field'] ) AND ! empty( $custom_orders[ $match[1] ]['value'] ) AND isset( $customfields[ $custom_orders[ $match[1] ]['field'] ] ) === TRUE )
					{
						$temp	= 'FIELD(';
						$temp	.= 'cd.field_id_';
						$temp	.= $customfields[ $custom_orders[ $match[1] ]['field'] ];
						$temp	.= ',';
						$temp	.= "'" . str_replace( ",", "','", $custom_orders[ $match[1] ]['value'] ) . "'";
						$temp	.= ')';
						$arr[]	= $temp;
					}

					continue;
				}

				// -------------------------------------
				//	Proceed as normal
				// -------------------------------------

				$ord	= explode( '|', $str );

				if ( isset( $fields[ $ord[0] ] ) === TRUE )
				{
					$arr[]	= ( isset( $ord[1] ) === TRUE AND in_array( $ord[1], array( 'asc', 'desc' ) ) ) ? 't.'.$fields[ $ord[0] ].' '.strtoupper( $ord[1] ): 't.'.$fields[ $ord[0] ].' ASC';
				}

				if ( isset( $customfields[ $ord[0] ] ) === TRUE AND is_numeric( $customfields[ $ord[0] ] ) === TRUE )
				{
					$arr[]	= ( isset( $ord[1] ) === TRUE AND in_array( $ord[1], array ( 'asc', 'desc' ) ) ) ? 'cd.field_id_'.$customfields[ $ord[0] ].' '.strtoupper( $ord[1] ): 'cd.field_id_'.$customfields[ $ord[0] ].' ASC';
				}

				if ( $ord[0] == $this->sc->channel OR $ord[0] == $this->sc->db->channel_title OR $ord[0] == $this->sc->db->channel_name )
				{
					// We don't actually have the channel_names available to use at this point,
					// so we'll do a sub-query to get the list then convert to their respective ids

					// TODO
					// get this to honor the site_ids passed
					$subsql = " SELECT {$this->sc->db->channel_id} FROM {$this->sc->db->channels} ";

					$subsql .= " WHERE site_id IN ('" . implode( "','", $this->_only_numeric(  $this->sess['search']['q']['site'] ) ) . "') ORDER BY ";

					if ( $ord[0] == $this->sc->channel ) $ord[0] = $this->sc->db->channel_name;

					$subsql  .= ( isset( $ord[1] ) === TRUE AND in_array( $ord[1], array ( 'asc', 'desc' ) ) ) ? $ord[0] .' '.strtoupper( $ord[1] ): $ord[0].' ASC';

					$subquery = $this->EE->db->query( $subsql );

					$channel_ids_ordered = array();

					foreach( $subquery->result_array() AS $result )
					{
						$channel_ids_ordered[] = $result[ $this->sc->db->channel_id ];
					}

					if ( count( $channel_ids_ordered ) > 0 )
					{
						// Now we've got our channel_ids in the correct order, pretend this is a custom_order param
						$temp  =  ' FIELD(';
						$temp  .= 't.'.$this->sc->db->channel_id;
						$temp  .= ',';
						$temp  .= "'" . implode( "','", $channel_ids_ordered ) . "'";
						$temp  .= ')';

						$arr[]	= $temp;
					}
				}

				if ( $ord[0] == 'random' )
				{
					$arr[] = "RAND()";
				}
			}
		}

		// -------------------------------------
		//	Manipulate order
		// -------------------------------------

		if ( $this->EE->extensions->active_hook('super_search_prep_order') === TRUE )
		{
			$arr	= $this->EE->extensions->universal_call( 'super_search_prep_order', $this, $arr );
		}

		// -------------------------------------
		//	Remove empties
		// -------------------------------------

		$arr	= $this->_remove_empties( $arr );

		return ' ORDER BY '.implode( ', ', $arr );
	}

	//	End prep order

	// -------------------------------------------------------------

	/**
	 * Prep paginate base
	 *
	 * When we have to force pagination base, we have to do a lot of gymnastics.
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_paginate_base()
	{
		$str	= str_replace( $this->urimarker, str_replace( '/', $this->slash, $this->sess['newuri'] ), $this->sess['olduri'] );

		// -------------------------------------
		//	Sometimes people run EE from a subdir and that appears in the uri. EE only wants to see template segments in the uri, plus a query string. So we want to strip out that subdir.
		// -------------------------------------

		if ( preg_match( "/https*:\/\/(.+)\/(.+)/s", $this->EE->config->item('site_url'), $match ) )
		{
			if ( ! empty( $match['2'] ) )
			{
				$str	= str_replace( $match['2'], '', $str );
			}
		}

		$str	= str_replace( $this->EE->config->item('site_index'), '', $str );

		// -------------------------------------
		//	Return
		// -------------------------------------

		return $str;
	}

	//	End prep paginate base

	// -------------------------------------------------------------

	/**
	 * Prep pagination universal
	 *
	 * This pagination uses the universal_pagination method from the Bridge.
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_pagination_universal( $sql, $total_results, $url_suffix = '' )
	{
		$this->cur_page = 0;

		// -------------------------------------
		//	Alter limit if necessary
		// -------------------------------------
		//	'num' is an alias of 'limit'. 'limit' dominates.
		// -------------------------------------

		// -------------------------------------
		//	We prefer to find the array value as a template param
		// -------------------------------------

		if ( ! empty( $this->EE->TMPL->tagparams[ 'limit' ] ) )
		{
			$this->limit	= $this->EE->TMPL->tagparams[ 'limit' ];
		}

		// -------------------------------------
		//	We'll accept the array key as a template param next
		// -------------------------------------

		if ( ! empty( $this->EE->TMPL->tagparams[ 'num' ] ) )
		{
			$this->limit	= $this->EE->TMPL->tagparams[ 'num' ];
		}

		// -------------------------------------
		//	We'll next accept our array val as a URI param
		// -------------------------------------

		if ( ! empty( $this->sess['uri'][ 'limit' ] ) )
		{
			$this->limit	= $this->sess['uri'][ 'limit' ];
		}

		// -------------------------------------
		//	We'll lastly accept our array key as a URI param
		// -------------------------------------

		if ( ! empty( $this->sess['uri'][ 'num' ] ) )
		{
			$this->limit	= $this->sess['uri'][ 'num' ];
		}

		// -------------------------------------
		//	Set initial new uri
		// -------------------------------------

		if ( ( $newuri = $this->sess( 'newuri' ) ) === FALSE )
		{
			$newuri	= 'search' . $this->parser . 'offset' . $this->separator . '0';
		}

		// -------------------------------------
		//	Handle stupid slashes
		// -------------------------------------

		if ( strpos( $newuri, '/' ) !== FALSE )
		{
			$newuri	= str_replace( '/', $this->slash, $newuri );
		}

		// -------------------------------------
		//	Prep basepath
		// -------------------------------------
		//	We need to negotiate different possible ways of assembling the pagination basepath.
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('paginate_base') !== FALSE AND $this->EE->TMPL->fetch_param('paginate_base') != '' )
		{
			$paginate_base	= $this->EE->TMPL->fetch_param('paginate_base');

			if ( strpos( $paginate_base, LD.'path=' ) !== FALSE)
			{
				$paginate_base  = (preg_match("#".LD."path=(.+?)".RD."#", $paginate_base, $match)) ? $this->EE->functions->create_url($match['1']) : $this->EE->functions->create_url("SITE_INDEX");
			}
			elseif ( strpos( $paginate_base, 'http' ) === FALSE )
			{
				// Load the string helper
				$this->EE->load->helper('string');

				$paginate_base	= $this->EE->functions->create_url( trim_slashes( $paginate_base ) );
			}

			$newuri	= rtrim( $paginate_base, '/' ) . '/' . ltrim( $newuri, '/' );
		}
		elseif ( ! empty( $this->sess['olduri'] ) AND strpos( $this->sess['olduri'], $this->urimarker ) !== FALSE )
		{
			$newuri	= str_replace( $this->urimarker, $this->sess['olduri'], $newuri );
		}

		// -------------------------------------
		//	Exception for people using the 'search' parameter. Strip out all arguments except for the start argument
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('search') !== FALSE AND $this->EE->TMPL->fetch_param('search') != '' AND preg_match( '/offset' . $this->separator . '(\d+)?/s', $this->sess['newuri'], $match ) )
		{
			$newuri	= 'search' . $this->parser . 'offset' . $this->separator . $match['1'];
		}

		// -------------------------------------
		//	If someone is using the template param called 'search' they may not have a full URI saved in sess['olduri'] so we try to fake it. The better approach is for them to use the paginate_base param above.
		// -------------------------------------

		if ( isset( $this->EE->uri->segments[1] ) === TRUE AND strpos( $newuri, $this->EE->uri->segments[1] . '/' ) !== 0 AND ( $this->EE->TMPL->fetch_param('paginate_base') === FALSE OR $this->EE->TMPL->fetch_param('paginate_base') == '' ) )
		{
			$temp[]	= $this->EE->uri->segments[1];

			if ( isset( $this->EE->uri->segments[2] ) === TRUE AND strpos( $this->EE->uri->segments[2], 'search&' ) === FALSE )
			{
				$temp[]	= $this->EE->uri->segments[2];
			}

			$temp[]	= $newuri;

			$newuri	= implode( '/', $temp );
		}

		// -------------------------------------
		//	Prep pagination data
		// -------------------------------------

		$pagination_config	= array();

		if ( ! empty( $paginate_base ) )
		{
			$pagination_config['base_url']	= $paginate_base;
		}

		$this->cur_page	= ( ( $cur = $this->sess( 'uri', 'offset' ) ) === FALSE ) ? 0: $cur;

		$pagination_data	= $this->universal_pagination(
			array(
				'current_page'			=> $this->cur_page,
				'limit'					=> $this->limit,
				'offset'				=> ( ! $this->EE->TMPL->fetch_param('offset') OR ! is_numeric($this->EE->TMPL->fetch_param('offset'))) ? '0' : $this->EE->TMPL->fetch_param('offset'),
				'query_string_segment'	=> 'offset=',
				'sql'					=> $sql,
				'tagdata'				=> $this->EE->TMPL->tagdata,
				'total_results'			=> $total_results,
				'uri_string'			=> $newuri,
				'pagination_config'		=> $pagination_config
			)
		);

		//if we paginated, sort the data
		if ($pagination_data['paginate'] === TRUE)
		{
			$sql					= $pagination_data['sql'];
			$this->paginate			= $pagination_data['paginate'];
			$this->page_next		= $pagination_data['page_next'];
			$this->page_previous	= $pagination_data['page_previous'];
			$this->cur_page			= $pagination_data['pagination_page'];
			$this->current_page		= $pagination_data['current_page'];
			$this->pager			= $pagination_data['pagination_links'];
			$this->basepath			= $pagination_data['base_url'];
			$this->total_pages		= $pagination_data['total_pages'];
			$this->paginate_data	= $pagination_data['paginate_tagpair_data'];
			$this->EE->TMPL->tagdata		= $pagination_data['tagdata'];
		}

		// -------------------------------------
		//	Pagination cleanup
		// -------------------------------------

		foreach ( array( 'page_next', 'page_previous', 'pager' ) as $k )
		{
			if ( isset( $this->$k ) === FALSE ) continue;

			$this->$k	= str_replace( array( $this->separator . '/', '/offset' . $this->separator ), array( $this->separator, 'offset' . $this->separator ), $this->$k );
		}

		return $sql;
	}

	//	End prep pagination universal

	// -------------------------------------------------------------

	/**
	 * Prep relevance
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_relevance()
	{
		// -------------------------------------
		//	Check params
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('relevance') === FALSE OR $this->EE->TMPL->fetch_param('relevance') == '' )
		{
			return FALSE;
		}

		// -------------------------------------
		//	Convert spaces
		// -------------------------------------

		$relevance	= str_replace( ' ', $this->spaces, $this->EE->TMPL->fetch_param('relevance') );

		// -------------------------------------
		//	Count words within words?
		// -------------------------------------

		if ( strpos( $relevance, 'count_words_within_words' ) !== FALSE )
		{
			$this->relevance_count_words_within_words	= TRUE;

			$relevance	= trim( str_replace( 'count_words_within_words', '', $relevance ), $this->spaces );
		}

		// -------------------------------------
		//	Simple argument?
		// -------------------------------------

		if ( strpos( $relevance, $this->spaces ) === FALSE )
		{
			$temp	= explode( $this->separator, $relevance );

			if ( count( $temp ) > 1 )
			{
				$arr[$temp[0]]		= $temp[1];
			}
			else
			{
				$arr[$relevance]	= 1;
			}

			return $arr;
		}

		// -------------------------------------
		//	Compound argument?
		// -------------------------------------

		$arr	= array();

		foreach ( explode( $this->spaces, $relevance ) as $val )
		{
			$temp	= explode( $this->separator, $val );

			if ( count( $temp ) > 1 )
			{
				$arr[ $temp[0] ]	= $temp[1];
			}
			else
			{
				$arr[ $temp[0] ]	= 1;
			}
		}

		if ( empty( $arr ) === TRUE ) return FALSE;

		return $arr;
	}

	//	End prep relevance

	// -------------------------------------------------------------

	/**
	 * Prep relevance multiplier
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_relevance_multiplier()
	{
		// -------------------------------------
		//	Check params
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('relevance_multiplier') === FALSE OR $this->EE->TMPL->fetch_param('relevance_multiplier') == '' )
		{
			return FALSE;
		}

		// -------------------------------------
		//	Convert spaces
		// -------------------------------------

		$multiplier	= str_replace( ' ', $this->spaces, $this->EE->TMPL->fetch_param('relevance_multiplier') );

		// -------------------------------------
		//	Simple argument?
		// -------------------------------------

		if ( strpos( $multiplier, $this->spaces ) === FALSE )
		{
			$temp	= explode( $this->separator, $multiplier );

			if ( count( $temp ) > 1 )
			{
				$arr[$temp[0]]		= $temp[1];
			}
			else
			{
				$arr[$multiplier]	= 1;
			}

			return $arr;
		}

		// -------------------------------------
		//	Compound argument?
		// -------------------------------------

		$arr	= array();

		foreach ( explode( $this->spaces, $multiplier ) as $val )
		{
			$temp	= explode( $this->separator, $val );

			if ( count( $temp ) > 1 )
			{
				$arr[ $temp[0] ]	= $temp[1];
			}
			else
			{
				$arr[ $temp[0] ]	= 1;
			}
		}

		if ( empty( $arr ) === TRUE ) return FALSE;

		return $arr;
	}

	//	End prep relevance multiplier

	// -------------------------------------------------------------

	/**
	 * Prep relevance proxmity
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_relevance_proximity( $q = array() )
	{
		if ( isset( $q['relevance_proximity'] ) )
		{
			if ( $this->check_yes( $q['relevance_proximity'] ) == TRUE )
			{
				$this->relevance_proximity = $this->relevance_proximity_default;
			}
			elseif ( is_numeric( $q['relevance_proximity'] ) == TRUE )
			{
				$this->relevance_proximity = $q['relevance_proximity'];
			}
		}

		return $this->relevance_proximity;
	}

	//	End prep relevance proximity

	// -------------------------------------------------------------

	/**
	 * Prep fuzzy weight
	 *
	 * @access	private
	 * @return	array
	 */

	function _prep_fuzzy_weight( $q = array() )
	{
		if ( isset( $q['fuzzy_weight'] ) )
		{
			if ( $this->check_yes( $q['fuzzy_weight'] ) == TRUE )
			{
				$this->fuzzy_weight = $this->fuzzy_weight_default;
			}
			elseif ( $this->check_no( $q['fuzzy_weight'] ) == TRUE )
			{
				// No fuzzy weight : ie. fuzzy matches get the same value as normal keywords
				$this->fuzzy_weight = 1;
			}
			elseif ( is_numeric( $q['fuzzy_weight'] ) == TRUE )
			{
				$this->fuzzy_weight = $q['fuzzy_weight'];
			}
		}

		return $this->fuzzy_weight;
	}

	//	End prep return

	// -------------------------------------------------------------

	/**
	 * Prep sql
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_sql( $type = 'or', $field = '', $keywords = array(), $exact = 'notexact', $field_id = '', $field_name = '' )
	{
		// -------------------------------------
		//	Basic validity test
		// -------------------------------------

		if ( $field == '' OR is_array( $keywords ) === FALSE OR count( $keywords ) == 0 ) return FALSE;

		// -------------------------------------
		//	EE stores custom field data in columns of a single DB table. These columns can contain data for a blog entry even when the custom field no longer belongs to that channel. Janky architecture. We have to correct against that by forcing a channel test attached to any custom field test we run. This might even speed things up.
		// -------------------------------------

		$exceptions	= array( 't.title', 't.status' );

		if ( isset( $this->sess['uri']['keyword_search_author_name'] ) AND $this->check_yes( $this->sess['uri']['keyword_search_author_name'] ) )
		{
			$exceptions[] = 'm.screen_name';
		}

		if ( isset( $this->sess['uri']['keyword_search_category_name'] ) AND $this->check_yes( $this->sess['uri']['keyword_search_category_name'] ) )
		{
			$exceptions[] = 'cat.cat_name';
		}

		if ( isset( $this->sess['field_to_channel_map_sql'][$field_id] ) === FALSE AND in_array( $field, $exceptions ) === FALSE ) return FALSE;

		// -------------------------------------
		//	Are we ignoring any fields via template param?
		// -------------------------------------

		if ( $field_name != '' AND $this->EE->TMPL->fetch_param( 'ignore_field' ) !== FALSE AND in_array( $field_name, explode( "|", $this->EE->TMPL->fetch_param( 'ignore_field' ) ) ) === TRUE )
		{
			return FALSE;
		}

		// -------------------------------------
		//	Check the state of our logical flag
		// -------------------------------------

		$allow_regex 		= FALSE;
		$allow_wildcards 	= FALSE;

		// Wildcard and regex searching is ee2.x only
		if ( APP_VER >= 2.0 )
		{
			if ( ! empty( $this->sess['uri']['regex_fields'] ) )
			{
				$allow_regex = $this->data->flag_state(
					$this->allow_regex,
					$this->sess['uri']['regex_fields'],
					$field,
					$this->sess['fields']['searchable']
				);
			}

			if ( ! empty( $this->sess['uri']['wildcard_fields'] ) )
			{
				$allow_wildcards = $this->data->flag_state(
					$this->allow_wildcards,
					$this->sess['uri']['wildcard_fields'],
					$field,
					$this->sess['fields']['searchable']
				);
			}
		}

		$this->has_regex = FALSE;

		// -------------------------------------
		//	Go!
		// -------------------------------------

		$arr	= array();

		// -------------------------------------
		//	Prep conjunction
		// -------------------------------------

		if ( $type == 'and' AND empty( $keywords['and'] ) === FALSE )
		{
			$temp	= array();

			foreach ( $keywords['and'] as $val )
			{
				if ( $val == '' ) continue;

				if ( strpos( $val, $this->spaces ) !== FALSE )
				{
					$val	= str_replace( $this->spaces, ' ', $val );
				}

				if ( $exact == 'exact' )
				{
					$temp[]	= $field." = '".$this->EE->db->escape_str( $val )."'";
				}
				elseif ( $exact == 'no-search-words-within-words' )
				{
					$temp[]	= $field." REGEXP '[[:<:]]".$this->EE->db->escape_str( $val )."[[:>:]]'";
				}
				else
				{
					if ( $allow_regex )
					{
						$this->has_regex = TRUE;

						$temp[]	= $field." REGEXP '".$this->EE->db->escape_str( $val )."'";
					}
					elseif ( $allow_wildcards AND stripos( $val, $this->wildcard ) !== FALSE )
					{
						$temp[]	= $field." REGEXP '".str_replace( $this->wildcard, '[a-zA-Z0-9]+', $this->EE->db->escape_str( $val ) ) ."'";
					}
					else
					{
						$temp[]	= $field." LIKE '%".$this->EE->db->escape_str( $val )."%'";
					}
				}
			}

			if ( count( $temp ) > 0 )
			{
				$arr[]	= '('.implode( ' AND ', $temp ).')';
			}
		}

		// -------------------------------------
		//	Prep exclusion
		// -------------------------------------

		if ( $type == 'not' AND empty( $keywords['not'] ) === FALSE )
		{
			$temp	= array();

			foreach ( $keywords['not'] as $val )
			{
				if ( $val == '' ) continue;

				if ( strpos( $val, $this->spaces ) !== FALSE )
				{
					$val	= str_replace( $this->spaces, ' ', $val );
				}

				if ( $exact == 'exact' )
				{
					$temp[]	= $field." != '".$this->EE->db->escape_str( $val )."'";
				}
				elseif ( $exact == 'no-search-words-within-words' )
				{
					$temp[]	= $field." NOT REGEXP '[[:<:]]".$this->EE->db->escape_str( $val )."[[:>:]]'";
				}
				else
				{
					if ( $allow_regex )
					{
						$this->has_regex = TRUE;

						$temp[]	= $field." REGEXP '".$this->EE->db->escape_str( $val )."'";
					}
					elseif ( $allow_wildcards AND stripos( $val, $this->wildcard ) !== FALSE )
					{
						$temp[]	= $field." NOT REGEXP '".str_replace( $this->wildcard, '[a-zA-Z0-9]+', $this->EE->db->escape_str( $val ) ) ."'";
					}
					else
					{
						$temp[]	= $field." NOT LIKE '%".$this->EE->db->escape_str( $val )."%'";
					}
				}
			}

			if ( count( $temp ) > 0 )
			{
				$arr[]	= '('.implode( ' AND ', $temp ).')';
			}
		}

		// -------------------------------------
		//	Prep inclusion
		// -------------------------------------

		if ( $type == 'or' AND empty( $keywords['or'] ) === FALSE )
		{
			$temp_keywords = $keywords['or'];

			if ( isset( $keywords['or_fuzzy'] ) AND is_array( $keywords['or_fuzzy'] ) )
			{
				foreach( $keywords['or_fuzzy'] as $fuzzy_set )
				{
					$temp_keywords = array_merge( $temp_keywords, $fuzzy_set );
				}
			}

			$temp	= array();

			foreach ( $temp_keywords as $val )
			{
				if ( $val == '' ) continue;

				if ( strpos( $val, $this->spaces ) !== FALSE )
				{
					$val	= str_replace( $this->spaces, ' ', $val );
				}

				if ( $exact == 'exact' )
				{
					$temp[]	= $field." = '".$this->EE->db->escape_str( $val )."'";
				}
				elseif ( $exact == 'no-search-words-within-words' )
				{
					$temp[]	= $field." REGEXP '[[:<:]]".$this->EE->db->escape_str( $val )."[[:>:]]'";
				}
				else
				{
					if ( $allow_regex )
					{
						$this->has_regex = TRUE;

						$temp[]	= $field." REGEXP '".$this->EE->db->escape_str( $val )."'";
					}
					elseif ( $allow_wildcards AND stripos( $val, $this->wildcard ) !== FALSE )
					{
						$temp[]	= $field." REGEXP '".str_replace( $this->wildcard, '[a-zA-Z0-9]+', $this->EE->db->escape_str( $val ) ) ."'";
					}
					else
					{
						$temp[]	= $field." LIKE '%".$this->EE->db->escape_str( $val )."%'";
					}
				}
			}

			if ( count( $temp ) > 0 )
			{
				$arr[]	= '('.implode( ' OR ', $temp ).')';
			}
		}

		// -------------------------------------
		//	Convert fake ampersands back into normal ampersands for the DB query
		// -------------------------------------

		$arr	= str_replace( $this->ampmarker, '&', $arr );

		// -------------------------------------
		//	Glue
		// -------------------------------------

		if ( empty( $arr ) === TRUE ) return FALSE;

		if ( in_array( $field, $exceptions ) === TRUE OR empty( $this->sess['field_to_channel_map_sql'][$field_id] ) )
		{
			return '(' . implode( ' AND ', $arr ) . ')';
		}
		else
		{
			if ( $type == 'not' )
			{
				return '(' . implode( ' AND ', $arr ) . ')';
			}

			return '(' . implode( ' AND ', $arr ) . $this->sess['field_to_channel_map_sql'][$field_id] . ')';
		}
	}

	//	End prep sql

	// -------------------------------------------------------------

	/**
	 * Prep ignore words
	 *
	 * @access	private
	 * @return	string
	 */

	function _prep_ignore_words( $keywords = '', $use_ignore_word_list_passed = '' )
	{
		// -------------------------------------
		//	Basic validity test
		// -------------------------------------

		if ( $keywords == '' ) return '';

		$ignore_word_list = $this->EE->config->item('ignore_word_list');

		// nothing to do anyway, bail
		if ( $ignore_word_list == '' ) return $keywords;

		// Is filtering enabled or overridden?
		$use_ignore_word_list = $this->check_yes( $this->EE->config->item( 'use_ignore_word_list' ) );

		if ( $use_ignore_word_list_passed != '' )
		{
			if ( $this->check_yes( $use_ignore_word_list_passed ) ) $use_ignore_word_list = TRUE;

			elseif ( $this->check_no( $use_ignore_word_list_passed ) ) $use_ignore_word_list = FALSE;

			elseif ( $use_ignore_word_list_passed == 'toggle' ) $use_ignore_word_list = !$use_ignore_word_list;
		}

		// This has been turned off, return
		if ( !$use_ignore_word_list ) return $keywords;

		$keywords = ' '.$keywords;

		$keywords_start = $keywords;

		// We need to filter our keywords
		$words = explode( '||', $this->EE->config->item('ignore_word_list') );

		foreach( $words AS $word )
		{
			// Test to see if this string is in the keywords
			if ( stripos( $keywords, $word ) )
			{
				// regex is expensive, so only do this when we have a candidate for matching
				$pattern = "/(?:^|[^a-zA-Z])" . preg_quote($word, '/') . "(?:$|[^a-zA-Z])/i";

				$keywords = preg_replace( $pattern, ' ', $keywords );
			}
		}

		// Clean up any double spaces we might have
		$keywords = preg_replace('!\s+!', ' ', $keywords);

		if ( $keywords_start != $keywords )
		{
			// Add a marker to say we've replaced something
			$this->sess['search']['q']['ignore_word_list_used'] = 'yes';
			$this->sess['search']['q']['pre_replace_keywords'] = $keywords_start;

			if ( trim($keywords) == '' )
			{
				return FALSE;
			}
		}


		return $keywords;
	}

	//	End prep ignore words

	/**
	 * Relevance count
	 *
	 * @access	private
	 * @return	integer
	 */

	function _relevance_count( $row = array() )
	{
		// -------------------------------------
		//	If there are no keywords, we don't do relevance.
		// -------------------------------------

		if ( empty( $this->sess['search']['q']['keywords']['or'] ) AND
			 empty( $this->sess['search']['q']['keywords']['and'] ) ) return 0;

		// -------------------------------------
		//	Get our relevance and multiplier arrays
		// -------------------------------------

		$relevance = array();

		$relevance_multiplier = array();

		if ( isset( $this->sess['search']['q']['relevance'] ) !== FALSE
			AND !empty( $this->sess['search']['q']['relevance'] ) )
		{
			$relevance = $this->sess['search']['q']['relevance'];
		}

		if ( isset( $this->sess['search']['q']['relevance_multiplier'] ) !== FALSE
			AND !empty( $this->sess['search']['q']['relevance_multiplier'] ) )
		{
			$relevance_multiplier = $this->sess['search']['q']['relevance_multiplier'];
		}

		// -------------------------------------
		//	Keyword list, we're searching on both 'or' and 'and'keywords, merge them
		// -------------------------------------

		$keywords = array_merge( $this->sess['search']['q']['keywords']['or'], $this->sess['search']['q']['keywords']['and'] );
		$fuzzy_keywords = array();
		$term_proximity = array();
		$term_frequency = array();

		if ( isset( $this->sess['search']['q']['keywords']['and_fuzzy'] ) AND is_array( $this->sess['search']['q']['keywords']['and_fuzzy'] ) )
		{
			foreach( $this->sess['search']['q']['keywords']['and_fuzzy'] as $fuzzy_set )
			{
				$fuzzy_keywords = array_merge( $fuzzy_keywords, $fuzzy_set );
			}
		}

		if ( isset( $this->sess['search']['q']['keywords']['or_fuzzy'] ) AND is_array( $this->sess['search']['q']['keywords']['or_fuzzy'] ) )
		{
			foreach( $this->sess['search']['q']['keywords']['or_fuzzy'] as $fuzzy_set )
			{
				$fuzzy_keywords = array_merge( $fuzzy_keywords, $fuzzy_set );
			}
		}

		// -------------------------------------
		//	Boundary
		// -------------------------------------

		$boundary	= ( $this->relevance_count_words_within_words == TRUE ) ? '': '\b';
		// This additional flag goes into our regular expression below and controls whether we count our keywords if they appear within other words.

		$n		= 0;
		$f 		= 0;
		$p 		= 0;
		$q 		= 0;
		$hash	= md5( serialize( $relevance ) );

		if ( isset( $row['entry_id'] ) === TRUE AND isset( $this->sess['search']['relevance_count'][$hash][ $row['entry_id'] ] ) === TRUE )
		{
			return $this->sess['search']['relevance_count'][$hash][ $row['entry_id'] ];
		}

		// -------------------------------------
		//	Frequency keywords relevance adjustment
		// -------------------------------------

		// This just isn't working atm
		// I'll revisit it when I find some time, but it's baking my noodle
		// at the moment. The logic is flipped but I also need to find a better
		// way to allow dynamic scaling on the frequency matching for
		// better ranking on low variance sites while still working with
		// high variance sites. Exactly. Dropped for now, tomorrow is another day

		/*
		if ( $this->relevance_frequency > 0 )
		{

			if ( isset( $this->sess['search']['relevance_frequcny'] ) === FALSE)
			{
				$fsql = " SELECT term, entry_count FROM exp_super_search_terms
							WHERE term IN ('" . implode( "','", $this->EE->db->escape_str($keywords) ) . "') ";

				$fquery = $this->EE->db->query( $fsql );

				foreach( $fquery->result_array() as $frow )
				{
					$term_frequency[ $frow['term'] ] = $frow['entry_count'];
				}

				if ( count( $term_frequency ) > 0 )
				{
					$min = 9999;
					$max = 0;

					foreach( $term_frequency as $term => $count )
					{
						if ( $count < $min ) $min = $count;
						if ( $count > $max ) $max = $count;
					}

					$range = $max - $min;

					if ( $range > 0 )
					{

						foreach( $term_frequency as $term => $count )
						{
							// TODO THIS is WRONG
							// the logic needs to be flipped.
							$term_frequency[ $term ] = 1 / ($max / $count);

						}
					}
				}

				$this->sess['search']['relevance_frequency'] = $term_frequency;
				//echo('<pre>'.print_R($this->sess['search']['relevance_frequency'],1).'</pre>');
			}

		}
		*/

		/* End frequency calculations */




		foreach ( $relevance as $key => $val )
		{
			if ( empty( $row[$key] ) === TRUE OR empty( $keywords ) ) continue;

			foreach ( $keywords as $w )
			{
				if ( function_exists( 'stripos' ) === FALSE OR stripos( $row[$key], $w ) !== FALSE )
				{
					// -------------------------------------
					//	This is still a boneheaded relevance algorithm. But at least with preg_match the counts can be a bit more accurate.
					// -------------------------------------

					$match	= '/' . $boundary . $w . $boundary . '/is';

					if ( preg_match_all( $match, $row[$key], $matches ) )
					{
						if ( isset( $matches[0] ) === TRUE )
						{

							// -------------------------------------
							//	Term frequency relevance
							// -------------------------------------

							if ( isset( $this->sess['search']['relevance_frequency'][ $w ] ) AND $this->relevance_frequency > 0 )
							{
								$n = $n + ( ( count( $matches[0] ) * $val ) * ( $this->sess['search']['relevance_frequency'][ $w ] * $this->relevance_frequency ) );
							}
							else
							{
								$n	= $n + ( count( $matches[0] ) * $val );
							}
						}
					}
				}
			}
		}

		// -------------------------------------
		//	Proximity of keywords relevance adjustment
		// -------------------------------------

		// Only do proximity based relevance when we have more than one keyword
		if ( count( $keywords ) > 1 AND $this->relevance_proximity > 0 )
		{
			$holder = array();

			// Do some fancier relevance calculations based on proximity
			foreach( $relevance as $key => $val )
			{
				if ( empty( $row[$key] ) === TRUE OR empty( $keywords ) ) continue;

				//TODO : fix this so it honors the boundary setting

				foreach( $keywords as $w )
				{
					$finished = FALSE;

					$offset = 0;

					$total_length = strlen( $row[$key] );

					while( !$finished )
					{
						$offset = stripos( $row[$key], $w, $offset );

						if ( $offset !== FALSE OR $offset > $total_length - strlen( $w ) )
						{
							$holder[ $key ][ $w ][] = $offset;

							$offset = $offset + strlen( $w );
						}
						else
						{
							$finished = TRUE;
						}
					}
				}

				// Drop any fields that only have a single term in
				if ( isset( $holder[ $key ] ) AND count( $holder[ $key ] ) < 2 ) unset( $holder[ $key ] );
			}

			// The real grunt work
			foreach( $holder as $key => $thisrow )
			{
				$checked = array();

				foreach( $thisrow as $term1 => $loc1 )
				{
					if ( ! isset( $checked[ $term1 ]  ) )
					{
						foreach( $thisrow as $term2 => $loc2 )
						{
							if ( $term2 != $term1 AND ! isset( $checked[ $term2 ] ) )
							{
								// Set our marker so we don't repeat ourselves
								$checked[ $term1 ] = $term2;

								$best = $this->relevance_proximity_threshold;

								// Get the best location proximty

								foreach( $loc1 as $pos1 )
								{
									foreach( $loc2 as $pos2 )
									{
										// Figure out where these terms are relative to each other
										$lt = ( $pos1 < $pos2 ? $pos1 : $pos2 );
										$rt = ( $pos1 < $pos2 ? $pos2 : $pos1 );
										$adjust =  ( $pos1 < $pos2 ? strlen($term1) : strlen($term2) );

										if ( ( $rt - ( $lt + $adjust ) ) < $best )
										{
											// New Best!
											$best = $rt - ( $lt + $adjust );
										}
									}
								}

								if ( $best < $this->relevance_proximity_threshold )
								{
									$term_proximity[] = $best;
								}
							}
						}
					}
				}
			}
		}

		//	End proxmimity calculations

		// -------------------------------------
		//	Fuzzy keywords relevance
		// -------------------------------------

		if ( count( $fuzzy_keywords ) > 0 )
		{
			// Because we're being fuzzy here, allow sub-word matches
			$boundary	= "\b";

			foreach ( $relevance as $key => $val )
			{
				if ( empty( $row[$key] ) === TRUE OR empty( $fuzzy_keywords ) ) continue;

				foreach ( $fuzzy_keywords as $w )
				{
					if ( function_exists( 'stripos' ) === FALSE OR stripos( $row[$key], $w ) !== FALSE )
					{
						// -------------------------------------
						//	This is still a boneheaded relevance algorithm. But at least with preg_match the counts can be a bit more accurate.
						// -------------------------------------

						$match	= '/' . $boundary . $w . $boundary . '/is';

						if ( preg_match_all( $match, $row[$key], $matches ) )
						{
							if ( isset( $matches[0] ) === TRUE )
							{
								$f	= $f + ( count( $matches[0] ) * $val );
							}
						}
					}
				}
			}

			// Reweight our fuzzy relevance by our adjustment value
			$f = $f * $this->fuzzy_weight;
		}

		$n = $n + $f;

		// -------------------------------------
		//	Term proximity relevance
		// -------------------------------------

		if ( count( $term_proximity ) > 0 )
		{
			$d = 0;

			foreach( $term_proximity as $distance )
			{
				// Closer is better!
				// Grade the proximity against an inverse square
				// then adjusted with our proxmity_weight var
				// this then gets taken as the sum and further adjusts the total relevance

				if ( $distance == 0 ) continue;

				$d = $d + $this->relevance_proximity / ( $distance * $distance );
			}

			if ( $d > 0 ) $n = $n + ( $n * $d );
		}

		// -------------------------------------
		//	If we have a relevance multiplication marker adjust our multiplier accordingly
		// -------------------------------------

		if ( count( $relevance_multiplier ) > 0 )
		{
			$adjust = FALSE;

			$m = 0;

			foreach( $relevance_multiplier as $key => $val )
			{
				if ( isset( $row[ $key ] ) AND is_numeric( $row[ $key ] ) AND is_numeric( $val ) )
				{
					$adjust = TRUE;

					$m += $row[ $key ] * $val;
				}
			}

			if ( $adjust === TRUE ) $n = $n * $m;
		}

		$this->sess['search']['relevance_count'][$hash][ $row['entry_id'] ]	= $n;

		return $n;
	}

	//	End relevance count

	// -------------------------------------------------------------

	/**
	 * Remove empties
	 *
	 * @access	private
	 * @return	array
	 */

	function _remove_empties( $arr = array() )
	{
		$a	= array();

		foreach ( $arr as $key => $val )
		{
			if ( $val == '' ) continue;

			$a[$key]	= $val;
		}

		return $a;
	}

	//	End remove empties

	// -------------------------------------------------------------

	/**
	 * Results
	 *
	 * @access	public
	 * @return	string
	 */

	function results()
	{
		$t	= microtime(TRUE);

		$this->EE->TMPL->log_item( 'Super Search: Starting results()' );

		// -------------------------------------
		//	Are they allowed here?
		// -------------------------------------

		if ( $this->_security() === FALSE )
		{
			$this->_parse_no_results_condition();
			$this->save_search_form( $this->EE->TMPL->template, 'just_replace' );
			return $this->no_results( 'super_search' );
		}

		// -------------------------------------
		//	Is there a search
		// -------------------------------------

		if ( ( $q = $this->_parse_search() ) === FALSE )
		{
			$this->_parse_no_results_condition();
			$this->save_search_form( $this->EE->TMPL->template, 'just_replace' );
			return $this->no_results( 'super_search' );
		}

		// -------------------------------------
		//	Are there required search arguments?
		// -------------------------------------

		if ( ( $required = $this->_parse_for_required( $q ) ) !== FALSE )
		{
			$this->save_search_form( $this->EE->TMPL->template, 'just_replace' );
			return $this->EE->TMPL->tagdata	= $this->_parse_required_condition( $this->EE->TMPL->tagdata, $required );
		}
		else
		{
			$this->EE->TMPL->tagdata	=	$this->_parse_required_condition( $this->EE->TMPL->tagdata );
		}

		// -------------------------------------
		//	Do channel title and channel data search
		// -------------------------------------

		if ( ( $ids = $this->do_search_ct_cd( $q ) ) === FALSE )
		{
			$this->_parse_no_results_condition( $q );
			$this->save_search_form( $this->EE->TMPL->template, 'just_replace' );
			return $this->no_results( 'super_search' );
		}

		$params	= array();

		if ( ( $tagdata = $this->_entries( $ids, $params ) ) === FALSE )
		{
			$this->_parse_no_results_condition();
			$this->save_search_form( $this->EE->TMPL->template, 'just_replace' );
			return $this->no_results( 'super_search' );
		}

		$this->EE->TMPL->log_item( 'Super Search: Ending results() '.(microtime(TRUE) - $t) );

		$this->save_search_form( $this->EE->TMPL->template, 'just_replace' );
		return $tagdata;
	}

	//	End results

	// -------------------------------------------------------------

	/**
	 * Return message
	 *
	 * @access	public
	 * @return	string
	 */

	function _return_message( $post = FALSE, $tagdata = '', $cond = array() )
	{
		if ( empty( $cond['message'] ) ) return FALSE;

		if ( $post === TRUE )
		{
			return $this->EE->output->show_user_error( 'general', $cond['message'] );
		}

		$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, $cond );
		$tagdata	= str_replace( LD.'message'.RD, $cond['message'], $tagdata );

		return $tagdata;
	}

	//	End return message

	// -------------------------------------------------------------

	/**
	 * Sanitize
	 *
	 * This method is a holdover from EE 1.x. I don't know if we need it yet.
	 *
	 * @access	private
	 * @return	string
	 */

	function _sanitize( $str = '' )
	{
		$bad	= array('$', 		'(', 		')',	 	'%26',		'%28', 		'%29');
		$good	= array('&#36;',	'&#40;',	'&#41;',	$this->ampmarker,		'&#40;',	'&#41;');

		return str_replace($bad, $good, $str);
	}

	//	End sanitize

	// -------------------------------------------------------------

	/**
	 * Save search
	 *
	 * This method allows people to save a search that has been cached in order to prevent it from being uncached.
	 *
	 * @access	public
	 * @return	string
	 */

	function save_search()
	{
		// -------------------------------------
		//	Security
		// -------------------------------------

		if ( $this->_security('posting') === FALSE )
		{
			return FALSE;
		}

		$post	= ( empty( $_POST ) ) ? FALSE: TRUE;

		$search_name	= $this->EE->input->get_post('super_search_name');

		$return	= ( ! empty( $_POST['return'] ) ) ? $this->EE->input->get_post('return'): $this->EE->input->get_post('RET');

		$return	= str_replace( array( '&amp;', ';' ), array( '&', '' ), $return );

		// -------------------------------------
		//	Get search id
		// -------------------------------------

		if ( isset( $this->EE->TMPL ) === TRUE AND $this->EE->TMPL->fetch_param('search_id') !== FALSE AND $this->EE->TMPL->fetch_param('search_id') != '' )
		{
			$search_id	= $this->EE->TMPL->fetch_param('search_id');
		}
		elseif ( $this->EE->input->get_post('super_search_id') !== FALSE AND is_numeric( $this->EE->input->get_post('super_search_id') ) === TRUE )
		{
			$search_id	= $this->EE->input->get_post('super_search_id');
		}
		elseif ( preg_match( '/\/(\d+)/s', $this->EE->uri->uri_string, $match ) )
		{
			$search_id	= $match['1'];
		}
		else
		{
			return $this->_return_message( $post, '', array( 'message' => lang('search_not_found') ) );
		}

		// -------------------------------------
		//	Delete mode?
		// -------------------------------------

		if ( ( isset( $this->EE->TMPL ) === TRUE AND $this->EE->TMPL->fetch_param('delete') !== FALSE AND $this->EE->TMPL->fetch_param('delete') == 'yes' ) OR strpos( $this->EE->uri->uri_string, '/delete' ) !== FALSE OR $this->EE->input->get_post('delete_mode') == 'yes' )
		{
			$sql	= "DELETE FROM exp_super_search_history
						WHERE history_id = ".$this->EE->db->escape_str( $search_id )
						." AND
						(
							( member_id != 0
							AND member_id = ".$this->EE->db->escape_str( $this->EE->session->userdata('member_id') ).")
							OR
							( cookie_id = ".$this->EE->db->escape_str( $this->_get_users_cookie_id() )." )
						)
						LIMIT 1";

			$this->EE->db->query( $sql );

			if ( $post === FALSE )
			{
				return $this->_return_message( $post, $this->EE->TMPL->tagdata, array( 'failure' => FALSE, 'success' => TRUE, 'message' => lang('search_successfully_deleted') ) );
			}
			else
			{
				$this->EE->functions->redirect( $return );
			}
		}

		// -------------------------------------
		//	Search name?
		// -------------------------------------

		if ( empty( $search_name ) )
		{
			return $this->_return_message( $post, '', array( 'message' => lang('missing_name') ) );
		}
		elseif ( preg_match("/[^a-zA-Z0-9\_\-\.\s]/", $search_name ) )
		{
			// return $this->_return_message( $post, '', array( 'message' => lang('invalid_name') ) );
			//	mitchell@solspace.com probably originally put this test in here in $$ v1. I don't know if it's needed though. My tests indicate that foreign language values submitted here go through fine.
		}

		// -------------------------------------
		//	Get all of this user's history for testing
		// -------------------------------------

		$sql	= "/* Super Search get user's search history for validation */ SELECT *
					FROM exp_super_search_history
					WHERE
					(
						member_id != 0
						AND member_id = ".$this->EE->db->escape_str( $this->EE->session->userdata('member_id') ).")
						OR
						( cookie_id = ".$this->EE->db->escape_str( $this->_get_users_cookie_id() )." )";

		$query	= $this->EE->db->query( $sql );

		// -------------------------------------
		//	No history at all?
		// -------------------------------------

		if ( $query->num_rows() == 0 )
		{
			return $this->_return_message( $post, '', array( 'message' => lang('search_not_found') ) );
		}

		// -------------------------------------
		//	Prepare helper arrays
		// -------------------------------------

		foreach ( $query->result_array() as $row )
		{
			$cache_ids[ $row['cache_id'] ]		= $row;
			$history_ids[ $row['history_id'] ]	= $row;
			$names[ $row['search_name'] ]		= $row;
		}

		// -------------------------------------
		//	Is our search found?
		// -------------------------------------

		if ( isset( $history_ids[ $search_id ] ) === FALSE )
		{
			return $this->_return_message( $post, '', array( 'message' => lang('search_not_found') ) );
		}

		// -------------------------------------
		//	Are we changing a name? Is it unique?
		// -------------------------------------

		if (
			$history_ids[ $row['history_id'] ]['search_name'] != $search_name
			AND isset( $names[ $search_name ] ) === TRUE
			AND $names[ $search_name ]['history_id'] != $search_id
			)
		{
			return $this->_return_message( $post, '', array( 'message' => lang('duplicate_name') ) );
		}

		// -------------------------------------
		//	Update DB
		// -------------------------------------

		$sql	= $this->EE->db->update_string(
			'exp_super_search_history',
			array(
				'search_name'	=> $search_name,
				'saved'			=> 'y'
				),
			array(
				'history_id'	=> $search_id
				)
			);

		// $sql	.= " ON DUPLICATE KEY UPDATE search_name = VALUES(search_name), saved = VALUES(saved)";

		if ( $history_ids[ $row['history_id'] ]['search_name'] != $search_name )
		{
			$this->EE->db->query( $sql );
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		if ( $post === FALSE )
		{
			return $this->_return_message( $post, $this->EE->TMPL->tagdata, array( 'failure' => FALSE, 'success' => TRUE, 'message' => lang('search_successfully_saved') ) );
		}
		else
		{
			$this->EE->functions->redirect( $return );
		}
	}

	//	End save search

	// -------------------------------------------------------------

	/**
	 * Save search form
	 *
	 * This method creates a form that users can submit to save searches to their histories.
	 *
	 * @access	public
	 * @return	string
	 */

	function save_search_form( $tagdata = '', $just_replace = '' )
	{
		// -------------------------------------
		//	Just return save search form?
		// -------------------------------------

		if ( $just_replace != '' AND preg_match( "/".LD.'super_search_save_search_form'.RD."(.*?)".LD.'\\'.T_SLASH.'super_search_save_search_form'.RD."/s", $this->EE->TMPL->template, $match ) )
		{
			$this->EE->TMPL->template	= str_replace( $match[0], $this->save_search_form( $match[1] ), $this->EE->TMPL->template );
		}

		// -------------------------------------
		//	Do we have a search by id?
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('search_id') !== FALSE AND is_numeric( $this->EE->TMPL->fetch_param('search_id') ) === TRUE )
		{
			$search_id	= $this->EE->TMPL->fetch_param('search_id');
		}

		// -------------------------------------
		//	We may already know the history id and cache ids
		// -------------------------------------

		if ( ! empty( $this->sess['search_history'] ) )
		{
			$results	= $this->sess['search_history'];
		}
		else
		{
			// -------------------------------------
			//	Check the DB for a search history
			// -------------------------------------

			$sql	= "/* Super Search find user's last search */ SELECT history_id, cache_id, results AS super_search_results, search_name AS super_search_name, search_date AS super_search_date
						FROM exp_super_search_history
						WHERE site_id = '".$this->EE->config->item('site_id')."'";

			if ( ! empty( $search_id ) )
			{
				$sql	.= " AND history_id = ".$this->EE->db->escape_str( $search_id );
			}
			else
			{
				$sql	.= " AND saved = 'n'";	// We're looking for the single search history entry that captures the very last search they conducted.

				if ( $this->EE->session->userdata('member_id') != 0 )
				{
					$sql	.= " AND ( member_id = ".$this->EE->db->escape_str( $this->EE->session->userdata('member_id') );
					$sql	.= " OR cookie_id = ".$this->EE->db->escape_str( $this->_get_users_cookie_id() )." )";
				}
				else
				{
					$sql	.= " AND cookie_id = ".$this->EE->db->escape_str( $this->_get_users_cookie_id() );
				}
			}

			$sql	.= " ORDER BY search_date DESC";
			$sql	.= " LIMIT 1";

			$query	= $this->EE->db->query( $sql );

			if ( $query->num_rows() == 0 )
			{
				return $this->no_results( 'super_search' );
			}

			$results	= $query->row_array();
		}

		// -------------------------------------
		//	Prep tagdata
		// -------------------------------------

		$tagdata	= ( $tagdata != '' ) ? $tagdata: $this->EE->TMPL->tagdata;

		foreach ( $results as $key => $val )
		{
			$key	= $this->_homogenize_var_name( $key );

			if ( strpos( $tagdata, LD.$key ) === FALSE ) continue;

			if ( $key == 'super_search_date' AND preg_match_all( "/".$key."\s+format=[\"'](.*?)[\"']/s", $tagdata, $matches ) )
			{
				foreach ( $matches[0] as $k => $v )
				{
					$tagdata	= str_replace( LD.$v.RD, $this->_parse_date( $matches[1][$k], $val ), $tagdata );
				}
			}

			$tagdata	= str_replace( LD.$key.RD, $val, $tagdata );
		}

		// -------------------------------------
		//	Prep data
		// -------------------------------------

		$config['ACT']				= $this->EE->functions->fetch_action_id('Super_search', 'save_search');

		$config['RET']				= (isset($_POST['RET'])) ? $_POST['RET'] : $this->EE->functions->fetch_current_uri();
		$config['RET']				= str_replace( array( '&amp;', ';' ), array( '&', '' ), $config['RET'] );

		$config['tagdata']			= $tagdata;

		$config['super_search_id']	= $results['history_id'];

		$config['cache_id']			= $results['cache_id'];

		$config['delete_mode']		= ( $this->EE->TMPL->fetch_param('delete_mode') == 'yes' ) ? 'yes': '';

		$config['form_id']			= ( $this->EE->TMPL->fetch_param('form_id') ) ? $this->EE->TMPL->fetch_param('form_id'): 'save_search_form';

		$config['form_name']		= ( $this->EE->TMPL->fetch_param('form_name') ) ? $this->EE->TMPL->fetch_param('form_name'): 'save_search_form';

		$config['return']			= ( $this->EE->TMPL->fetch_param('return') ) ? $this->EE->TMPL->fetch_param('return'): '';

		// -------------------------------------
		//	Declare form
		// -------------------------------------

		return $this->_form( $config );
	}

	//	End save search form

	// -------------------------------------------------------------

	/**
	 * Save search to history
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _save_search_to_history( $cache_id = 0, $results = 0, $q = '' )
	{
		if ( $this->disable_history === TRUE ) return FALSE;
		if ( empty( $this->sess['uri'] ) === TRUE ) return FALSE;

		// -------------------------------------
		//	Let's set a history cookie for them
		// -------------------------------------

		if ( ( $cookie_id = $this->_get_users_cookie_id() ) === FALSE )
		{
			return FALSE;
		}

		// -------------------------------------
		//	Save to DB
		// -------------------------------------

		$newuri	= ( empty( $this->sess['newuri'] ) === FALSE ) ? $this->sess['newuri']: '';

		$arr	= array(
				'cache_id'		=> $cache_id,
				'member_id'		=> $this->EE->session->userdata('member_id'),
				'cookie_id'		=> $cookie_id,
				'ip_address'	=> $this->EE->input->ip_address(),
				'site_id'		=> $this->EE->config->item('site_id'),
				'search_date'	=> $this->EE->localize->now,
				'search_name'	=> lang('search'),
				'results'		=> $results,
				'hash'			=> $this->hash,
				'query'			=> $q,
			);

		$sql	= $this->EE->db->insert_string( 'exp_super_search_history', $arr );

		$sql	.= " ON DUPLICATE KEY UPDATE cache_id = VALUES(cache_id), member_id = VALUES(member_id), cookie_id = VALUES(cookie_id), search_date = VALUES(search_date), saved = 'n', results = VALUES(results), hash = VALUES(hash), query = VALUES(query) /* Super Search save search to history */";

		$this->EE->db->query( $sql );

		$arr['history_id']				= $this->EE->db->insert_id();
		$this->sess['search_history']	= $arr;

		return TRUE;
	}

	//	End save search to history

	// -------------------------------------------------------------

	/**
	 * Search
	 *
	 * This method is really not as dramatic as it sounds. It just lets people parse search variables from $tagdata so that they can let people come back to a remembered search and execute it again.
	 *
	 * @access	public
	 * @return	string
	 */

	function search()
	{
		// -------------------------------------
		//	We need to know about a search id for later
		// -------------------------------------

		if ( $this->EE->TMPL->fetch_param('search_id') !== FALSE AND is_numeric( $this->EE->TMPL->fetch_param('search_id') ) === TRUE )
		{
			$search_id	= $this->EE->TMPL->fetch_param('search_id');
		}

		// -------------------------------------
		//	Is this being called before a results() call in the same template?
		// -------------------------------------

		if ( empty( $search_id ) )
		{
			// -------------------------------------
			//	Is this being called before a results() call in the same template?
			// -------------------------------------

			$results_test	= FALSE;

			foreach ( $this->EE->TMPL->tag_data as $val )
			{
				if ( $val['class'] == 'super_search' )
				{
					if ( $val['method'] == 'search' )
					{
						$results_test	= TRUE;
					}

					if ( $results_test === TRUE AND $val['method'] == 'results' )
					{
						return $this->EE->TMPL->tagdata;
					}
				}
			}
		}

		// -------------------------------------
		//	Who is this?
		// -------------------------------------

		if ( ( $member_id = $this->EE->session->userdata('member_id') ) === 0 )
		{
			if ( ( $cookie_id = $this->_get_users_cookie_id() ) === FALSE )
			{
				return $this->_strip_variables( $this->EE->TMPL->tagdata );
			}
		}

		// -------------------------------------
		//	Start the SQL
		// -------------------------------------

		$sql	= "/* Super Search grab last search for vars */ SELECT history_id AS search_id, search_date AS super_search_date, search_name AS name, results, saved, query
					FROM exp_super_search_history
					WHERE site_id IN ( ".implode( ',', $this->EE->TMPL->site_ids )." )";

		if ( empty( $member_id ) === FALSE )
		{
			$sql	.= " AND member_id = ".$this->EE->db->escape_str( $member_id );
		}
		elseif ( empty( $cookie_id ) === FALSE )
		{
			$sql	.= " AND cookie_id = ".$this->EE->db->escape_str( $cookie_id );
		}

		// -------------------------------------
		//	Do we have a search id?
		// -------------------------------------
		//	If we have a search id, we pull that search. If we do not have an id then we will pull the user's last search which we know if the only search in their history that has not yet been saved by them. We save the last search for each user who touches the system.
		// -------------------------------------

		if ( ! empty( $search_id ) )
		{
			$sql	.= " AND history_id = ".$this->EE->db->escape_str( $this->EE->TMPL->fetch_param('search_id') );
		}
		else
		{
			$sql	.= " AND saved = 'n'";
		}

		// -------------------------------------
		//	Order
		// -------------------------------------

		$sql	.= " ORDER BY search_date DESC";

		// -------------------------------------
		//	Limit
		// -------------------------------------

		$sql	.= " LIMIT 1";

		// -------------------------------------
		//	Run query
		// -------------------------------------

		$query	= $this->EE->db->query( $sql );

		if ($query->num_rows() === 0 )
		{
			return $this->_strip_variables( $this->EE->TMPL->tagdata );
		}

		// -------------------------------------
		//	Find out what we need from tagdata
		// -------------------------------------

		$i	= 0;

		foreach ( $this->EE->TMPL->var_single as $key => $val )
		{
			$i++;

			if ( strpos( $key, 'format=' ) !== FALSE )
			{
				$full	= $key;
				$key	= preg_replace( "/(.*?)\s+format=[\"'](.*?)[\"']/s", '\1', $key );
				$dates[$key][$i]['format']	= $val;
				$dates[$key][$i]['full']	= $full;
			}
		}

		// -------------------------------------
		//	Localize
		// -------------------------------------

		if ( empty( $dates ) === FALSE )
		{
			setlocale( LC_TIME, $this->EE->session->userdata('time_format') );
		}

		// -------------------------------------
		//	Parse
		// -------------------------------------

		$prefix	= 'super_search_';
		$r		= '';
		$vars	= array();

		foreach ( $query->result_array() as $row )
		{
			$tagdata	= $this->EE->TMPL->tagdata;

			// -------------------------------------
			//	Prep query into row
			// -------------------------------------

			if ( $row['query'] != '' )
			{
				$vars	= $this->_extract_vars_from_query( unserialize( base64_decode( $row['query'] ) ) );
			}

			// -------------------------------------
			//	Save vars into cache for later parsing by variables()
			// -------------------------------------

			$this->sess['parsables']	= $vars;

			// -------------------------------------
			//	Conditionals and switch
			// -------------------------------------

			$tagdata	= $this->EE->functions->prep_conditionals( $tagdata, array_merge( $row, $vars ) );

			// -------------------------------------
			//	Loop for dates
			// -------------------------------------

			if ( empty( $dates ) === FALSE )
			{
				foreach ( $dates as $field => $date )
				{
					foreach ( $date as $key => $val )
					{
						if ( isset( $row[$field] ) === TRUE AND is_numeric( $row[$field] ) === TRUE )
						{
							$tagdata	= str_replace( LD.$val['full'].RD, $this->_parse_date( $val['format'], $row[$field] ), $tagdata );
						}
					}
				}
			}

			unset( $row['super_search_date'] );

			// -------------------------------------
			//	Convert ampersands back into ampersands
			// -------------------------------------

			$row	= str_replace( array( $this->doubleampmarker, $this->ampmarker ), array( '&&', '&' ), $row );
			$vars	= str_replace( array( $this->doubleampmarker, $this->ampmarker ), array( '&&', '&' ), $vars );

			// -------------------------------------
			//	Regular parse
			// -------------------------------------

			foreach ( $row as $key => $val )
			{
				$key	= $prefix.$key;

				if ( strpos( LD.$key, $tagdata ) !== FALSE ) continue;

				$tagdata	= str_replace( LD.$key.RD, $val, $tagdata );
			}

			// -------------------------------------
			//	Variable parse
			// -------------------------------------

			foreach ( $vars as $key => $val )
			{
				if ( strpos( LD.$key, $tagdata ) !== FALSE ) continue;

				$tagdata	= str_replace( LD.$key.RD, stripslashes( $val ), $tagdata );
			}

			// -------------------------------------
			//	Parse empties
			// -------------------------------------

			$tagdata	= $this->_strip_variables( $tagdata );

			$r	.= $tagdata;
		}

		return $r;
	}

	//	End search

	// -------------------------------------------------------------

	/**
	 * Suggestion Plurals
	 *
	 * Gets the most likely plural versions of a passed word, and returns the array
	 * for validation checking
	 *
	 * @access	private
	 * @param 	array
	 * @return	array
	 */

	function _suggestion_plurals( $word )
	{
		// We follow the standard 5 regular plural rules to start
		// Technically we should detect the phontic morphemes, but
		// thats hard, instead attempt the same thing with some
		// rough rules. It's not as complete, but we're checking the
		// validity agains the lexicon later, so it's ok

		// Of note : this isn't perfect. But it's good enough for now
		// It's english specific, but if theres a need we can expand on this
		// to add other language rules too.

		// 1. Ends in -s, -e or -o, add -es (kiss->kisses, phase->phases, hero->heroes)
		// 2. Ends in -y, drop the last -y, add -ies (cherry->cherries)
		// 3. Ends in -f or -fe, drop the -f or -fe, add -ves (leaf->leaves, knife->knives)
		// 4. Ends in -a, becomes -ae (or -æ) (formula->forumlae)
		// 5. Ends in -ex or -ix, becomes -ices or -es (matrix->matrices, index->indices)
		// 6. Ends in -is, becomes -es (axis->axes, crisis->crises)
		// 7. Ends in -ies, stays as is (series->series, species->species)
		// 8. Ends in -on, becomes -a (criterion->criteria)
		// 9. Ends in -um, becomes -a (millennium->millennia)
		// 10. Ends in -us, becomes -i, -era, -ora or -es (alumnus->alumni, cactus->cacti, uterus->uteri->uteruses)
		// x. Special cases
		//		foot -> feet
		//		goose -> geese
		//		man -> men
		//		mouse -> mice
		// 		tooth -> teeth
		// 		woman -> women

		$arr = array();

		$plural = $this->data->get_plural( $word );

		$single = $this->data->get_singular( $word );

		if ( $plural != $word ) $arr[] = $plural;

		if ( $single != $word ) $arr[] = $single;

		$arr = array_unique( $arr );

		return $arr;

	}

	//	End _suggestion_plurals

	// -------------------------------------------------------------

	/**
	 * Suggestion Spelling
	 *
	 * Gets some a whole bunch of variants on the passed terms ordered by their levenshtein distance
	 * these will later get checked against the db for fitness
	 *
	 * @access	private
	 * @param 	array
	 * @return	array
	 */

	function _suggestion_spelling( $words = array() )
	{
		return $this->data->spelling_suggestions( $words );
	}

	//	End _suggestion_spelling

	// -------------------------------------------------------------

	/**
	 * _suggestions
	 *
	 *  We follow a very similar method to the one described here : http://www.norvig.com/spell-correct.html
	 *  Basically it boils down to the follow steps :
	 *  1. Check if it's a known word
	 *  2. Check for variants of distance = 1
	 *  3. Check for variants of distance = 2
	 *  4. Mark it as an unknown, move on.
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestions( $q, $count = 0)
	{
		if ( $count == 0 AND isset( $q['keywords'] ) )
		{
			$this->EE->TMPL->log_item( 'Super Search: Staring to look for search suggestions' );

			// This is our last hope. Correct at all costs



			// Break the phrase up
			$keywords_str = $q['keywords'];

			if ( strpos( $keywords_str, $this->spaces ) !== FALSE )
			{
				$keywords_str	= str_replace( $this->spaces, ' ', $keywords_str );
			}

			$keywords = explode( ' ', $keywords_str);

			// ----------------------------------
			//  These 100 words make up 1/2 of all written material
			//  and by not checking them we should be able to greatly
			//  speed up the spellchecker
			// ----------------------------------
			//  'Borrowed' directly from EE's Spellcheck.php
			//   / with love - jb.
			// ----------------------------------

			$common = array('the', 'of', 'and', 'a', 'to', 'in', 'is', 'you', 'that',
							'it', 'he', 'was', 'for', 'on', 'are', 'as', 'with', 'his',
							'they', 'I', 'at', 'be', 'this', 'have', 'from', 'or', 'one',
							'had', 'by', 'word', 'but', 'not', 'what', 'all', 'were', 'we',
							'when', 'your', 'can', 'said', 'there', 'use', 'an', 'each',
							'which', 'she', 'do', 'how', 'their', 'if', 'will', 'up',
							'other', 'about', 'out', 'many', 'then', 'them', 'these', 'so',
							'some', 'her', 'would', 'make', 'like', 'him', 'into', 'time',
							'has', 'look', 'two', 'more', 'write', 'go', 'see', 'number',
							'no', 'way', 'could', 'people', 'my', 'than', 'first', 'water',
							'been', 'call', 'who', 'oil', 'its', 'now', 'find', 'long',
							'down', 'day', 'did', 'get', 'come', 'made', 'may', 'part');


			// Make sure we're clean
			$this->suggested = array();
			$this->corrected = array();

			// First check if it's a known word

			foreach( $keywords AS $keyword )
			{
				if ( ! in_array( strtolower($keyword) , $common ) )
				{
					$this->suggested[ strtolower( $keyword ) ] =  strtolower( $keyword );
				}
			}

			if ( count( $this->suggested ) > 0 )
			{
				// We have some values to check in our lexicon
				$this->EE->TMPL->log_item( 'Super Search: Checking the keywords are known' );

				$this->_suggestion_known( $this->suggested );
			}

			// Now check for edits with distance 1
			$attempt = array();

			if ( count( $this->suggested ) > 0 )
			{
				$this->EE->TMPL->log_item( 'Super Search: Looking for search terms with a lehvinstein distance = 1' );

				// We have to do this per each keyword
				// so we an map it back later

				foreach( $this->suggested AS $suggested )
				{
					$this_attempt = @array_merge(
						$this->_suggestion_deletion($suggested),
						$this->_suggestion_transposition($suggested),
						$this->_suggestion_alteration($suggested),
						$this->_suggestion_insertion($suggested));

					$attempt = array_merge( $attempt, $this_attempt );
				}

				// Now recheck against our known results
				$this->_suggestion_known( $attempt );
			}

			// We could check for spelling suggestions with a further edit distance,
			// but it gets expensive quickly.
			// Better to keep a note of it and look at our lesuire later on

			// Nothing we can do, bail
			if ( count( $this->suggested ) > 0 )
			{
				// Record the terms we can't find suggestions for
				// so we can work on them later at our lesuire
				$this->_suggestions_remember();

				$this->EE->TMPL->log_item( 'Super Search: Still have search terms we don\'t know. Stopping suggestion search' );
			}
		}

		if ( count( $this->corrected ) > 0 )
		{
			// We have at least one suggestion
			// YEAH! Go for a bit of substitution

			// This really needs to be the search phrase, not just the keywords
			$q['suggestion'] = strtolower( $q['keywords'] );

			if ( strpos( $q['suggestion'], $this->spaces ) !== FALSE )
			{
				$q['suggestion']	= str_replace( $this->spaces, ' ', $q['suggestion'] );
			}

			$q['suggestion'] = str_replace( $this->corrected, array_keys( $this->corrected ), $q['suggestion'] );

		}

		$this->EE->TMPL->log_item( 'Super Search: Finished looking for suggestions' );

		return $q;
	}

	//	End _suggestion

	// -------------------------------------------------------------

	/**
	 * _suggestion_remember
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestions_remember()
	{
		// We have failed to find any suggestions for these keywords
		// So record them for more intesive searching at another time

		$sql = " INSERT INTO exp_super_search_lexicon (term, type, size, lang, term_date )
					VALUES ";

		$temp = array();

		foreach( $this->suggested AS $suggest )
		{
			$temp[] = " ( '" . $suggest . "', 'unknown', '" . strlen( $suggest ) . "', 'en', '" . time() . "' ) ";
		}

		$sql .= implode( ' , ', $temp );

		$this->EE->db->query( $sql );
	}

	//	End suggest remember

	// -------------------------------------------------------------

	/**
	 * _suggestion_known
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_known( $attempt = array() )
	{
		if ( count( $attempt ) == 0 ) return;

		$sql = " SELECT term, term_id, type
					FROM exp_super_search_terms
					WHERE term IN ('" . implode( "','", array_keys( $attempt ) ) . "')";

		$query = $this->EE->db->query( $sql );

		if ( $query->num_rows > 0 )
		{
			// We have at least one of our terms in our lexicon, deal with it

			foreach( $query->result_array() AS $key => $row )
			{
				if ( $row['type'] == 'misspelling' )
				{
					// This is a recognized misspelling of a corrected word
					// Update the corrected array with the proper spelling

					if ( $lookup_multi )
					{
						// Find the base term that we used to get here
					}
					$this->corrected[ $row['term'] ] = $attempt[ $row['term'] ];

					unset( $this->suggested[ $row['term'] ] );
				}
				elseif ( $row['type'] == 'variant' )
				{
					// This is a variant of a different word, with better results
					// ie. a plural etc..

					$this->corrected[ $row['term'] ] = $attempt[ $row['term'] ];

					unset( $this->suggested[ $attempt[ $row['term'] ] ] );
				}
				elseif ( $row['type'] == 'valid' )
				{
					// this appears to be valid word, we just have not results for
					// this filter set.
					// Remove from the suggested array, but leave out of the corrected

					$this->corrected[ $row['term'] ] = $attempt[ $row['term'] ];

					unset( $this->suggested[ $attempt[ $row['term'] ] ] );
				}
				elseif ( $row['type'] == 'unknown' )
				{
					// We've tried to correct this before and couldn't, stop here

					unset( $this->suggested[ $attempt[ $row['term'] ] ] );

				}
				elseif ( $row['type'] == 'seed' )
				{
					// We have a term from the seed lexicon
					if ( $row['term'] != $attempt[ $row['term'] ] ) $this->corrected[ $row['term'] ] = $attempt[ $row['term'] ];

					unset( $this->suggested[ $attempt[ $row['term'] ] ] );
				}
				else
				{
					// This is strange
				}
			}
		}
	}

	//	End suggestion known

	// -------------------------------------------------------------

	/**
	 * _suggestion_deletion
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_deletion($word)
	{
		for($x=0; $x<strlen($word); $x++)
		{
		  $newword = substr($word, 0, $x) . substr($word, $x+1, strlen($word));

		  $results[ $newword ] = $word;
		}

		return $results;
	}

	//	End suggestion deletion

	// -------------------------------------------------------------

	/**
	 * _suggestion_transposition
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_transposition($word)
	{
		for($x=0; $x<strlen($word)-1; $x++)
		{
			$newword = substr($word, 0, $x) . $word[$x+1] . $word[$x] . substr($word, $x+2, strlen($word));

			if ( $newword != $word ) $results[ $newword ] = $word;
		}

		return $results;
	}

	//	End suggestion transposition

	// -------------------------------------------------------------

	/**
	 * _suggestion_alteration
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_alteration($word)
	{
		for($c=0; $c<strlen($this->alphabet); $c++)
		{
		  for($x=0; $x<strlen($word); $x++)
		  {
			  $newword = substr($word, 0, $x) . $this->alphabet[$c] . substr($word, $x+1, strlen($word));

			  if ( $newword != $word ) $results[ $newword ] = $word;
		  }
		}

		  return $results;
	}

	//	End suggestion alteration

	// -------------------------------------------------------------

	/**
	 * _suggestion_insertion
	 *
	 * @access	private
	 * @return	array
	 */

	function _suggestion_insertion($word)
	{
		for($c=0;$c<strlen($this->alphabet);$c++)
		{
		  for($x=0;$x<strlen($word)+1;$x++)
		  {
			$newword = substr($word, 0, $x) . $this->alphabet[$c] . substr($word, $x, strlen($word));

			if ( $newword != $word ) $results[ $newword ] = $word;
		  }
		}

		return $results;
	}

	//	End suggestion insertion

	// -------------------------------------------------------------

	/**
	 * Security
	 *
	 * @access	private
	 * @return	boolean
	 */

	function _security( $posting = 'not_posting' )
	{
		// -------------------------------------
		//  Is the current user allowed to search?
		// -------------------------------------

		if ( $this->EE->session->userdata['can_search'] == 'n' AND $this->EE->session->userdata['group_id'] != 1 )
		{
			return $this->EE->output->show_user_error('general', array(lang('search_not_allowed')));
			return FALSE;
		}

		// -------------------------------------
		//	Is the user banned?
		// -------------------------------------

		if ( $this->EE->session->userdata['is_banned'] === TRUE )
		{
			return $this->EE->output->show_user_error('general', array(lang('search_not_allowed')));
			return FALSE;
		}

		// -------------------------------------
		//	Is the IP address and User Agent required?
		// -------------------------------------

		if ( $this->EE->config->item('require_ip_for_posting') == 'y' AND $posting == 'posting' )
		{
			if ( ( $this->EE->input->ip_address() == '0.0.0.0' OR $this->EE->session->userdata['user_agent'] == '' ) AND $this->EE->session->userdata['group_id'] != 1 )
			{
				return $this->EE->output->show_user_error('general', array(lang('search_not_allowed')));
				return FALSE;
			}
		}

		// -------------------------------------
		//	Is the nation of the user bannend?
		// -------------------------------------

		if ( isset($this->EE->TMPL->module_data['Ip_to_nation']))
		{
			$this->EE->session->nation_ban_check();
		}

		// -------------------------------------
		//	Blacklist / Whitelist Check
		// -------------------------------------

		if ( $this->EE->blacklist->blacklisted == 'y' && $this->EE->blacklist->whitelisted == 'n' )
		{
			return $this->EE->output->show_user_error('general', array(lang('search_not_allowed')));
			return FALSE;
		}

		// -------------------------------------
		//	Return
		// -------------------------------------

		return TRUE;
	}

	//	End security

	// -------------------------------------------------------------

	/**
	 * Separate numeric from textual
	 *
	 * We want two arrays derived from one. One array will contain the numeric values in a given array and the other will contain all other values. We use this when we are searching by categories and we might receive some cat ids as well as some cat names or cat urls in an array of search terms.
	 * If no numeric values are found, we return FALSE.
	 *
	 * @access	private
	 * @return	array
	 */

	function _separate_numeric_from_textual( $arr = array() )
	{
		if ( empty( $arr ) === TRUE ) return FALSE;

		$new['textual']	= array(); $new['numeric'] = array();

		foreach ( $arr as $val )
		{
			if ( is_numeric( $val ) === TRUE )
			{
				$new['numeric'][]	= $val;
			}
			else
			{
				$new['textual'][]	= $val;
			}
		}

		if ( empty( $new['numeric'] ) === TRUE ) return FALSE;

		return $new;
	}

	//	End separate numeric from textual

	// -------------------------------------------------------------

	/**
	 * Sess
	 *
	 * This is a really convenient utility, but it takes up extra fractions of milliseconds. We should phase it out.
	 *
	 * @access	public
	 * @return	null
	 */

	function sess()
	{
		$s = func_num_args();

		if ($s == 0)
		{
			return FALSE;
		}

		// -------------------------------------
		//  Find Our Value, If It Exists
		// -------------------------------------

		$value = (isset($this->sess[func_get_arg(0)])) ? $this->sess[func_get_arg(0)] : FALSE;

		for($i = 1; $i < $s; ++$i)
		{
			if ( ! isset($value[func_get_arg($i)]))
			{
				return FALSE;
			}

			$value = $value[func_get_arg($i)];
		}

		return $value;
	}

	//	End sess

	// -------------------------------------------------------------

	/**
	 * Smart excerpt
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */

	function _smart_excerpt($str = '', $keywords = array(), $num = 100 )
	{
		if ( strlen($str) < $num )
		{
			return $str;
		}

		$terms = array();

		if ( ! empty( $keywords['and'] ) )
		{
			foreach ( $keywords['and'] as $val )
			{
				$terms[]	= $this->data->get_singular( strtolower( $val ) );
			}
		}

		if ( ! empty( $keywords['or'] ) )
		{
			foreach ( $keywords['or'] as $val )
			{
				$terms[]	= $this->data->get_singular( strtolower( $val ) );
			}
		}

		//	This orders our terms from longest to shortest. For me, this is a cheap and easy way to show an excerpt highlighting the most complex term in the search, which in a lot of cases is the more important one.
		if ( ! function_exists( 'str_sort' ) )
		{
			function str_sort($a,$b) {return strlen($b)-strlen($a);}
		}

		usort( $terms, 'str_sort' );

		// -------------------------------------
		//	Now load on fuzzies. We want them ranked last.
		// -------------------------------------

		if ( ! empty( $keywords['or_fuzzy'] ) )
		{
			$fuzzy_terms	= array();

			foreach ( $keywords['or_fuzzy'] as $key => $temp )
			{
				foreach ( $temp as $val )
				{
					$fuzzy_terms[]	= $this->data->get_singular( strtolower( $val ) );
				}
			}

			usort( $fuzzy_terms, 'str_sort' );

			$terms	= array_merge( $terms, $fuzzy_terms );
		}

		// -------------------------------------
		//	Find our term in our string and try to do so from the middle out.
		// -------------------------------------

		$smart_excerpt_threshold	= 10 * 5;

		foreach ( $terms as $term )
		{
			if ( ( $shorty = stristr( $str, $term ) ) !== FALSE )
			{
				//	If the excerpt is the same length as the term, we found the term at the end of a sentence and would only be returning that word as the excerpt. That's a joke. Let's back that thing up a little bit.
				if ( ( strlen( $shorty ) - strlen( $term ) ) < $smart_excerpt_threshold )
				{
					// allows the split to work properly with multi-byte Unicode characters
					if (is_php('4.3.2') === TRUE)
					{
						$arr = preg_split('/' . $term . '/u', $str, -1, PREG_SPLIT_NO_EMPTY);
					}
					else
					{
						$arr = preg_split('/' . $term . '/', $str, -1, PREG_SPLIT_NO_EMPTY);
					}

					if ( str_word_count( $arr[0] ) >= ( $num - str_word_count( $term ) ) AND ! empty( $arr[0] ) )
					{

						if (is_php('4.3.2') === TRUE)
						{
							$temp = preg_split('/\s/u', $arr[0], -1, PREG_SPLIT_NO_EMPTY);
						}
						else
						{
							$temp = preg_split('/\s/', $arr[0], -1, PREG_SPLIT_NO_EMPTY);
						}

						while ( count( $temp ) >= ( $num - str_word_count( $term ) ) )
						{
							array_shift( $temp );
						}

						$arr[0]	= implode( ' ', $temp );
					}

					$shorty	= $arr[0] . ' ' . $term;
				}

				return ' &#8230;' . $this->EE->functions->word_limiter( $shorty, $num );
			}
		}

		return ' &#8230;' . $this->EE->functions->word_limiter( $str, $num );
	}

	//	end smart excerpt

	// -------------------------------------------------------------

	/**
	 * Split date
	 *
	 * Break a date string into chunks of 2 chars each.
	 *
	 * @access	private
	 * @return	array
	 */

	function _split_date( $str = '' )
	{
		if ( $str == '' ) return array();

		if ( function_exists( 'str_split' ) )
		{
			$thedate	= str_split( $str, 2 ); unset( $str );
			return $thedate;
		}

		$temp	= preg_split( '//', $str, -1, PREG_SPLIT_NO_EMPTY );

		do
		{
			$t = array();

			for ( $i=0; $i<2; $i++ )
			{
				$t[]	= array_shift( $temp );
			}

			$thedate[]	= implode( '', $t );
		}
		while ( count( $temp ) > 0 );

		return $thedate;
	}

	//	End split date

	// -------------------------------------------------------------

	/**
	 * Strip variables
	 *
	 * This quick method strips variables like this {super_search_some_value} and like this {/super_search_some_value} from a string.
	 *
	 * @access	private
	 * @return	string
	 */

	function _strip_variables( $tagdata = '' )
	{
		if ( $tagdata == '' ) return '';

		$tagdata	= preg_replace(
			"/" . LD . "(" . preg_quote(T_SLASH, '/') . ")?super_search_(.*?)" . RD . "/s",
			"",
			$tagdata
		);

		return $tagdata;
	}

	//	End strip variables

	// -------------------------------------------------------------

	/**
	 * Table columns
	 *
	 * Retrieves, stores and returns an array of the columns in a table
	 * At the moment, I've decided it's all stupid. We'll just go static
	 *
	 * @access	private
	 * @return	array
	 */

	function _table_columns( $table = '' )
	{
		if ( $table == '' ) return FALSE;

		// -------------------------------------
		//	Make it static, make it fast
		// -------------------------------------

		$fields[ $this->sc->db->titles ]	= array(
			'entry_id' => 'entry_id',
			'site_id' => 'site_id',
			$this->sc->db->channel_id => $this->sc->db->channel_id,
			'author_id' => 'author_id',
			'pentry_id' => 'pentry_id',
			'forum_topic_id' => 'forum_topic_id',
			'ip_address' => 'ip_address',
			'title' => 'title',
			'url_title' => 'url_title',
			'status' => 'status',
			'versioning_enabled' => 'versioning_enabled',
			'view_count_one' => 'view_count_one',
			'view_count_two' => 'view_count_two',
			'view_count_three' => 'view_count_three',
			'view_count_four' => 'view_count_four',
			'allow_comments' => 'allow_comments',
			'allow_trackbacks' => 'allow_trackbacks',
			'sticky' => 'sticky',
			'date' => 'entry_date',
			'entry_date' => 'entry_date',
			'dst_enabled' => 'dst_enabled',
			'year' => 'year',
			'month' => 'month',
			'day' => 'day',
			'expiration_date' => 'expiration_date',
			'comment_expiration_date' => 'comment_expiration_date',
			'edit_date' => 'edit_date',
			'recent_comment_date' => 'recent_comment_date',
			'comment_total' => 'comment_total',
			'trackback_total' => 'trackback_total',
			'sent_trackbacks' => 'sent_trackbacks',
			'recent_trackback_date' => 'recent_trackback_date'
		);

		$fields['exp_members']	= array(
			'member_id' => 'member_id',
			'username' => 'username',
			'screen_name' => 'screen_name',
			'email' => 'email',
			'url' => 'url',
			'location' => 'location',
			'occupation' => 'occupation',
			'interests' => 'interests',
			'bday_d' => 'bday_d',
			'bday_m' => 'bday_m',
			'bday_y' => 'bday_y',
			'bio' => 'bio',
			'signature' => 'signature',
			'avatar_filename' => 'avatar_filename',
			'avatar_width' => 'avatar_width',
			'avatar_height' => 'avatar_height',
			'photo_filename' => 'photo_filename',
			'photo_width' => 'photo_width',
			'photo_height' => 'photo_height',
			'sig_img_filename' => 'sig_img_filename',
			'sig_img_width' => 'sig_img_width',
			'sig_img_height' => 'sig_img_height',
			'join_date' => 'join_date',
			'last_visit' => 'last_visit',
			'last_activity' => 'last_activity',
			'total_entries' => 'total_entries',
			'total_comments' => 'total_comments',
			'total_forum_topics' => 'total_forum_topics',
			'total_forum_posts' => 'total_forum_posts',
			'last_entry_date' => 'last_entry_date',
			'last_comment_date' => 'last_comment_date',
			'language' => 'language',
			'timezone' => 'timezone',
			'daylight_savings' => 'daylight_savings',
			'time_format' => 'time_format'
		);

		// -------------------------------------
		//	And slow it down with custom shat
		// -------------------------------------

		if ( isset($this->EE->TMPL->module_data['Rating']) && ! isset( $this->sess['fields']['searchable']['rating'] ) )
		{
			$fields[ $this->sc->db->titles ]['rating']	= 'rating_avg';
		}

		// -------------------------------------
		//	Manipulate $fields
		// -------------------------------------

		if ($this->EE->extensions->active_hook('super_search_table_columns') === TRUE)
		{
			$fields	= $this->EE->extensions->universal_call( 'super_search_table_columns', $this, $fields );
		}

		if ( isset( $fields[$table] ) === FALSE ) return FALSE;

		return $fields[$table];
	}

	//	End table columns

	// -------------------------------------------------------------

	/**
	 * Translator for Cut/Paste
	 *
	 * @access	public
	 * @return	string
	 */

	function _translator($n)
	{
		static $dc = "0123456789";
		static $sc = "\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19";

		return (intval($n)) ? strtr($n, $dc, $sc) : intval(strtr($n, $sc, $dc));
	}

	//	END _translator()

	// -------------------------------------------------------------

	/**
	 * Uncerealize
	 *
	 * serialize() and unserialize() add a bunch of characters that are not needed when storing a one dimensional indexed array. Why not just use a pipe?
	 *
	 * @access	private
	 * @return	string
	 */

	function _uncerealize( $str = '' )
	{
		return explode( '|', $str );
	}

	//	End uncerealize

	// -------------------------------------------------------------

	/**
	 * Variables
	 *
	 * @access	public
	 * @return	string
	 */

	function variables()
	{
		$p		= 'super_search_';
		$parse	= array();

		// -------------------------------------
		//	Carry on with the prep of our parsing array.
		// -------------------------------------

		if ( ( $sess = $this->sess( 'uri' ) ) === FALSE )
		{
			// -------------------------------------
			//	Our parse array may have already been prepped in search().
			// -------------------------------------

			if ( ( $parsables = $this->sess( 'parsables' ) ) !== FALSE )
			{
				$parse	= $parsables;

				// -------------------------------------
				//	Manipulate $q
				// -------------------------------------

				if ( $this->EE->extensions->active_hook('super_search_array_variables') === TRUE )
				{
					$parse	= $this->EE->extensions->universal_call( 'super_search_array_variables', $this, $parse, $p );
				}

				// -------------------------------------
				//	The rest is as they say, easy
				// -------------------------------------

				return $this->EE->TMPL->parse_variables( $this->EE->TMPL->tagdata, array( $parse ) );
			}

			$sess	= array();
		}

		// -------------------------------------
		//	Start looping.
		// -------------------------------------

		foreach ( $this->basic as $key )
		{
			if ( strpos( $this->EE->TMPL->tagdata, $p . $key ) === FALSE ) continue;

			$parse[ $p . $key ]	= '';

			if ( isset( $sess[$key] ) === TRUE )
			{
				// -------------------------------------
				//	Convert protected strings
				// -------------------------------------

				$sess[$key]	= str_replace( array( $this->negatemarker ), array( '-' ), $sess[$key] );

				// -------------------------------------
				//	Parse
				// -------------------------------------

				$parse[ $p . $key ]	= ( strpos( $sess[$key], $this->doubleampmarker ) === FALSE ) ? $sess[$key]: str_replace( $this->doubleampmarker, '&&', $sess[$key] );
			}
		}

		// -------------------------------------
		//	Prepare boolean variables
		// -------------------------------------
		//	Some search parameters can have multiple values, like status. People can search for multiple status params at once. We would like to be able to evaluate for the presence of each of those statuses as boolean variables. So this: search&status=open+closed+"First+Looks+-empty" would allow {super_search_status_open}, {super_search_status_closed}, {super_search_status_First_Looks} and {super_search_status_not_empty} to evaluate as true. We replace spaces with underscores and quotes with nothing.
		// -------------------------------------

		foreach ( array( 'channel', 'status', 'category' ) as $key )
		{
			if ( isset( $sess[$key] ) === FALSE ) continue;

			//	Protect dashes for negation so that we don't have conflicts with dash in url titles
			if ( strpos( $sess[$key], $this->separator.'-' ) !== FALSE )
			{
				$sess[$key]	= str_replace( $this->separator.'-', $this->negatemarker, $sess[$key] );
			}

			if ( strpos( $sess[$key], '-' ) === 0 )
			{
				$sess[$key]	= str_replace( '-', $this->negatemarker, $sess[$key] );
			}

			$temp	= $this->_prep_keywords( $sess[$key] );

			if ( isset( $temp['and'] ) === TRUE )
			{
				foreach ( $temp['and'] as $val )
				{
					$val	= str_replace( ' ', '_', $val );

					$parse[ $p . $key . '_' . $val ]	= TRUE;
				}
			}

			if ( isset( $temp['or'] ) === TRUE )
			{
				foreach ( $temp['or'] as $val )
				{
					$val	= str_replace( ' ', '_', $val );

					$parse[ $p . $key . '_' . $val ]	= TRUE;
				}
			}

			if ( isset( $temp['not'] ) === TRUE )
			{
				foreach ( $temp['not'] as $val )
				{
					$val	= str_replace( ' ', '_', $val );

					$parse[ $p . $key . '_not_' . $val ]	= TRUE;
				}
			}
		}

		// -------------------------------------
		//	Prepare date from and date to
		// -------------------------------------

		$parse[ $p.'date'.$this->modifier_separator.'from' ]	= '';
		$parse[ $p.'date'.$this->modifier_separator.'to' ]		= '';

		if ( isset( $sess['datefrom'] ) === TRUE )
		{
			$parse[ $p.'date'.$this->modifier_separator.'from' ]	= $sess['datefrom'];
		}

		if ( isset( $sess['dateto'] ) === TRUE )
		{
			$parse[ $p.'date'.$this->modifier_separator.'to' ]	= $sess['dateto'];
		}

		// -------------------------------------
		//	Allow an alias of date for entry_date
		// -------------------------------------

		$parse[ $p.'entry_date'.$this->modifier_separator.'from' ]	= '';
		$parse[ $p.'entry_date'.$this->modifier_separator.'to' ]		= '';

		if ( isset( $sess['datefrom'] ) === TRUE )
		{
			$parse[ $p.'entry_date'.$this->modifier_separator.'from' ]	= $sess['datefrom'];
		}

		if ( isset( $sess['dateto'] ) === TRUE )
		{
			$parse[ $p.'entry_date'.$this->modifier_separator.'to' ]	= $sess['dateto'];
		}

		// -------------------------------------
		//	Prepare expiry date from and date to
		// -------------------------------------

		$parse[ $p.'expiry_date'.$this->modifier_separator.'from' ]	= '';
		$parse[ $p.'expiry_date'.$this->modifier_separator.'to' ]		= '';

		if ( isset( $sess['expiry_datefrom'] ) === TRUE )
		{
			$parse[ $p.'expiry_date'.$this->modifier_separator.'from' ]	= $sess['expiry_datefrom'];
		}

		if ( isset( $sess['expiry_dateto'] ) === TRUE )
		{
			$parse[ $p.'expiry_date'.$this->modifier_separator.'to' ]	= $sess['expiry_dateto'];
		}

		// -------------------------------------
		//	Prepare custom fields
		// -------------------------------------

		if ( ( $fields = $this->_fields( 'searchable', $this->EE->TMPL->site_ids ) ) !== FALSE )
		{
			foreach ( $fields as $key => $val )
			{
				if ( strpos( $this->EE->TMPL->tagdata, $p.$key ) === FALSE
					AND strpos( $this->EE->TMPL->tagdata, $p.'exact'.$this->modifier_separator.$key ) === FALSE
					AND strpos( $this->EE->TMPL->tagdata, $p.$key.$this->modifier_separator.'exact' ) === FALSE
					AND strpos( $this->EE->TMPL->tagdata, $p.$key.$this->modifier_separator.'empty' ) === FALSE
					AND strpos( $this->EE->TMPL->tagdata, $p.$key.$this->modifier_separator.'from' ) === FALSE
					AND strpos( $this->EE->TMPL->tagdata, $p.$key.$this->modifier_separator.'to' ) === FALSE ) continue;

				$parse[ $p.$key ]			= '';
				$parse[ $p.'exact'.$this->modifier_separator.$key ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'exact' ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'empty' ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'from' ]	= '';
				$parse[ $p.$key.$this->modifier_separator.'to' ]	= '';

				if ( isset( $sess['field'][$key] ) === TRUE )
				{
					$parse[ $p.$key ]	= ( strpos( $sess['field'][$key], $this->doubleampmarker ) === FALSE ) ? $sess['field'][$key]: str_replace( $this->doubleampmarker, '&&', $sess['field'][$key] );
				}

				if ( isset( $sess['exactfield'][$key] ) === TRUE )
				{

					if ( is_array( $sess['exactfield'][$key] ) )
					{
						$collapased = implode( '|',  $sess['exactfield'][$key] );

						$parse[ $p . 'exact' . $this->modifier_separator . $key ]	=
							( strpos( $collapased, $this->doubleampmarker ) === FALSE )
								? $collapased
								: str_replace( $this->doubleampmarker, '&&', $collapased );
					}
					else
					{
						$parse[ $p.$key.$this->modifier_separator.'exact' ]	=
							( strpos( $sess['exactfield'][$key], $this->doubleampmarker ) === FALSE )
								? $sess['exactfield'][$key]
								: str_replace( $this->doubleampmarker, '&&', $sess['exactfield'][$key] );
					}
				}

				if ( isset( $sess['empty'][$key] ) === TRUE )
				{
					$parse[ $p.$key.$this->modifier_separator.'empty' ]	= $sess['empty'][$key];
				}

				if ( isset( $sess['from'][$key] ) === TRUE )
				{
					$parse[ $p.$key.$this->modifier_separator.'from' ]	= $sess['from'][$key];
				}

				if ( isset( $sess['to'][$key] ) === TRUE )
				{
					$parse[ $p.$key.$this->modifier_separator.'to' ]	= $sess['to'][$key];
				}
			}
		}

		// -------------------------------------
		//	Revert fake ampersands to real ones
		// -------------------------------------

		$parse	= str_replace( $this->ampmarker, '&', $parse );

		// -------------------------------------
		//	Manipulate $q
		// -------------------------------------

		if ( $this->EE->extensions->active_hook('super_search_array_variables') === TRUE )
		{
			$parse	= $this->EE->extensions->universal_call( 'super_search_array_variables', $this, $parse, $p );
		}

		// -------------------------------------
		//	The rest is as they say, easy
		// -------------------------------------

		return $this->EE->TMPL->parse_variables( $this->EE->TMPL->tagdata, array( $parse ) );
	}

	//	END variables()

	// -------------------------------------------------------------

	/**
	 * Channel ids
	 *
	 * @access	private
	 * @return	array
	 */

	function _channel_ids( $id = '', $param = '' )
	{
		// -------------------------------------
		//	Already done?
		// -------------------------------------

		if ( ( $channel_ids = $this->sess( 'channel_ids' ) ) === FALSE )
		{
			// -------------------------------------
			//	Fetch
			// -------------------------------------

			$channels	= $this->data->get_channels_by_site_id_keyed_to_name( $this->EE->TMPL->site_ids );
			$channel_ids	= $this->data->get_channels_by_site_id( $this->EE->TMPL->site_ids );

			$this->sess['channels']		= $channels;
			$this->sess['channel_ids']	= $channel_ids;
		}

		if ( $id == '' )
		{
			return $channel_ids;
		}

		if ( $id != '' AND $param != '' AND isset( $channel_ids[$id][$param] ) === TRUE )
		{
			return $channel_ids[$id][$param];
		}

		if ( isset( $channel_ids[$id] ) === TRUE )
		{
			return $id;
		}

		return FALSE;
	}

	//	End channel ids

	// -------------------------------------------------------------

	/**
	 * Prep return
	 *
	 * @access		private
	 * @return		string
	 */

	function _prep_return( $return = '' )
	{
		if ( $this->EE->input->get_post('return') !== FALSE AND
			 $this->EE->input->get_post('return') != '' )
		{
			$return	= $this->EE->input->get_post('return');
		}
		elseif ( $this->EE->input->get_post('RET') !== FALSE AND
				 $this->EE->input->get_post('RET') != '' )
		{
			$return	= $this->EE->input->get_post('RET');
		}
		else
		{
			$return = $this->EE->functions->fetch_current_uri();
		}

		if ( preg_match( "/".LD."\s*path=(.*?)".RD."/", $return, $match ) )
		{
			$return	= $this->EE->functions->create_url( $match['1'] );
		}
		elseif ( stristr( $return, "http://" ) === FALSE AND
				 stristr( $return, "https://" ) === FALSE )
		{
			$return	= $this->EE->functions->create_url( $return );
		}

		if ( substr($return, -1) != '/' )
		{
			$return .= '/';
		}

		return $return;
	}

	// End prep return

	// -------------------------------------------------------------

	/**
	 * Channels
	 *
	 * @access	private
	 * @return	array
	 */

	function _channels( $channel = '', $param = '' )
	{
		// -------------------------------------
		//	Already done?
		// -------------------------------------

		if ( ( $channel_ids = $this->sess( 'channel_ids' ) ) === FALSE )
		{
			// -------------------------------------
			//	Fetch
			// -------------------------------------

			$channels		= $this->data->get_channels_by_site_id_keyed_to_name( $this->EE->TMPL->site_ids );
			$channel_ids	= $this->data->get_channels_by_site_id( $this->EE->TMPL->site_ids );

			$this->sess['channels']		= $channels;
			$this->sess['channel_ids']	= $channel_ids;
		}

		if ( $channel == '' )
		{
			return FALSE;
		}

		if ( $channel != '' AND $param != '' AND isset( $channels[$channel][$param] ) === TRUE )
		{
			return $channels[$channel][$param];
		}

		if ( isset( $channels[$channel] ) === TRUE )
		{
			return $channel;
		}

		return FALSE;
	}

	//	End channels

	// -------------------------------------------------------------

	/**
	 * Param split
	 *
	 * @access	private
	 * @return	string
	 */

	 function _param_split( $term = '', $sql_marker = ' term ' )
	 {
		if ( $term != '' )
		{
			$terms = array();

			$splitters = array( '|', '+', '&', ' ', ',' );

			$has_spliter = FALSE;

			foreach( $splitters AS $split )
			{
				if ( strpos( $term, $split ) != FALSE )
				{
					foreach( explode( $split, $term ) as $single )
					{
						if ( $single != '' )	$terms[] = $this->EE->db->escape_str( $single );
					}
				}
			}

			if ( count( $terms ) < 1 )
			{
				$terms[] = $this->EE->db->escape_str( $term );
			}

			return " AND " . $sql_marker . " IN ('" . implode( "','", $terms ) . "') ";

		}
		else
		{
			return '';
		}
	 }

	 //	End param split
}

// END CLASS Super search

// -------------------------------------------------------------
