<?php

/**
 * DataGrab Feed import class
 *
 * Allows RSS & ATOM imports using MagpieRSS
 * 
 * @package   DataGrab
 * @author    Andrew Weaver <aweaver@brandnewbox.co.uk>
 * @copyright Copyright (c) Andrew Weaver
 */
class Ajw_feed extends Datagrab_type {

	var $datatype_info = array(
		'name'		=> 'RSS/ATOM feed (using Magpie)',
		'version'	=> '0.1',
		'allow_subloop' => TRUE
	);

	var $settings = array(
		"filename" => "",
		"filter" => ""
	);

	var $cache_name = 'magpie_cache';
	var $cache_refresh = 60;
	var $cache_data = '';
	var $cache_path = '';
	var $cache_tpath = '';

	var $items;
	var $sub_item_ptr;

	function settings_form( $values = array() ) {

		$form = array(
		array( 
			form_label('URL of feed', 'filename') .
			'<div class="subtext">Can be a file on the local file system or from a website site url (http://...)</div>', 
			form_input(
				array(
					'name' => 'filename',
					'id' => 'filename',
					'value' => $this->get_value( $values, "filename" ),
					'size' => '50'
					)
				) 
			),
		array(
			form_label('Filter entries', 'filter') .
			'<div class="subtext">Only import entries that match these values. Comma-separated keywords. Leave blank to import all.</div>', 
			form_input(
				array(
					'name' => 'filter',
					'id' => 'filter',
					'value' => $this->get_value( $values, "filter" ),
					'size' => '50'
					)
				)
			)
		);

		return $form;
	}

	function fetch() {

		// Retrieve feed

		if ( ! defined('MAGPIE_CACHE_DIR') ) {
		  	define('MAGPIE_CACHE_DIR', APPPATH.'cache/'.$this->cache_name.'/');
		}    
		if ( ! defined('MAGPIE_CACHE_AGE') ) {
			define('MAGPIE_CACHE_AGE',	$this->cache_refresh * 60);
		}

		if ( substr( $this->settings["filename"], 0, 7) == "feed://" ) {
			$this->settings["filename"] = "http://" . substr($this->settings["filename"], 7);
		}

		set_error_handler( array("Ajw_feed", "_catch_error") );
		$RSS = ajw_fetch_rss( $this->settings["filename"] );
		restore_error_handler();

		if( ! is_object( $RSS ) ) {
			$this->errors[] = "Cannot load the feed.";
			return -1;
		}

		$this->items = $RSS->items;
		
	}

	function next( $is_first=FALSE ) {
		
		$item = current( $this->items );
		next( $this->items );

		if( ! $is_first && isset( $this->settings["filter"] ) && $this->settings["filter"] != "" ) {

			$match = $this->_do_array_search( $this->settings["filter"], $item );
			while( ! $match ) {

				$item = current( $this->items );
				next( $this->items );

				if( ! is_array( $item ) ) {
					return $item;
				}

				$match = $this->_do_array_search( $this->settings["filter"], $item );
			}

		}

		return $item;
		
	}

	function fetch_columns() {
		
		$this->fetch();
		$columns = $this->next( TRUE );

		// Read the whole feed for columns
		/*
		while( $item = $this->next() ) {
			$columns = array_merge( $columns, $item );
		}
		*/
		
		$titles = array();
		$count = 0;
		foreach( $columns as $idx => $title ) {
			if( substr( $idx, -1, 1) != "#" ) {
				if( is_array( $title ) ) {
					foreach( $title as $idx2 => $title2 ) {
						if( substr( $idx2, -1, 1) != "#" ) {
							if ( strlen( $title2 ) > 64 ) {
								$title2 = substr( $title2, 0, 64 ) . "...";
							}
							$titles[ $idx . ":" . $idx2 ] = htmlspecialchars($idx . ":" . $idx2 . " - eg, " . $title2);
						}
					}
				} else {
					if ( strlen( $title ) > 64 ) {
						$title = substr( $title, 0, 64 ) . "...";
					}
					$titles[ $idx ] = $idx . " - eg, " . $title;
				}
			}
		}

		return $titles;
	}


	function get_item( $items, $id ) {

		if ( strpos( $id, ':' ) ) {
			$subfieldArray = explode( ":", $id );
			if ( isset( $items[ $subfieldArray[0] ][ $subfieldArray[1] ] ) ) {
				return( $items[ $subfieldArray[0] ][ $subfieldArray[1] ] );
			}
		} elseif ( isset( $items[ $id ] ) ) {
			return( $items[ $id ] );
		} else {
			return "";
		}
	
	}

	function initialise_sub_item( $item, $id, $config, $field ) {
		$this->sub_item_ptr = 0;
		return TRUE;
	}
	
	function get_sub_item( $item, $id, $config, $field ) {
		
		$this->sub_item_ptr++;
		
		$no_elements = $this->get_item( $item, $id . "#" );
		
		if( $no_elements == "" || $this->sub_item_ptr > $no_elements ) {
			return FALSE;
		}
		
		if ( $this->sub_item_ptr > 1 ) {
			$suffix = '#' . $this->sub_item_ptr;
		} else {
			$suffix = '';
		}
		
		return $this->get_item( $item, $id . $suffix );
	}

	function _do_array_search( $filter, $item ) {
		if( ! is_array( $item ) ) {
			return FALSE;
		}
		foreach( $item as $key => $value ) {
			$keywords = explode( ",", $filter );
			foreach( $keywords as $keyword ) {
				if( is_array( $value ) ) {
					foreach( $value as $key2 => $value2 ) {
						if( strpos( strtolower($value2), strtolower(trim($keyword)) ) !== FALSE ) {
							return TRUE;
						}
					}
				} else {
					if( strpos( strtolower($value), strtolower(trim($keyword)) ) !== FALSE ) {
						return TRUE;
					}
				}
			}
		}
	}

	function _catch_error($errno, $errstr, $errfile, $errline) {

		/* Don't execute PHP internal error handler */
		return true;

	}
	
}


/* ************************************************** 


/**
* Project:     MagpieRSS: a simple RSS integration tool
* File:        rss_parse.inc  - parse an RSS or Atom feed
*               return as a simple object.
*
* Handles RSS 0.9x, RSS 2.0, RSS 1.0, Atom 0.3, and Atom 1.0
*
* The lastest version of MagpieRSS can be obtained from:
* http://magpierss.sourceforge.net
*
* For questions, help, comments, discussion, etc., please join the
* Magpie mailing list:
* magpierss-general@lists.sourceforge.net
*
* @author           Kellan Elliott-McCrea <kellan@protest.net>
* @version          0.8
* @license          GPL
*
*/

if( !defined('RSS') ) define('RSS', 'RSS'); 
if( !defined('ATOM') ) define('ATOM', 'Atom');

/**
* Hybrid parser, and object, takes RSS as a string and returns a simple object.
*
* see: rss_fetch.inc for a simpler interface with integrated caching support
*
*/
class Ajw_MagpieRSS {
    var $parser;
    
    var $current_item   = array();  // item currently being parsed
    var $items          = array();  // collection of parsed items
    var $channel        = array();  // hash of channel fields
    var $textinput      = array();
    var $image          = array();
    var $feed_type;
    var $feed_version;
    var $encoding       = '';       // output encoding of parsed rss
    
    var $_source_encoding = '';     // only set if we have to parse xml prolog
    
    var $ERROR = "";
    var $WARNING = "";
    
    // define some constants
    
    var $_ATOM_CONTENT_CONSTRUCTS = array(
        'content', 'summary', 'title', /* common */
    'info', 'tagline', 'copyright', /* Atom 0.3 */
        'rights', 'subtitle', /* Atom 1.0 */
    );
    var $_XHTML_CONTENT_CONSTRUCTS = array('body', 'div');
    var $_KNOWN_ENCODINGS    = array('UTF-8', 'US-ASCII', 'ISO-8859-1');

    // parser variables, useless if you're not a parser, treat as private
    var $stack              = array(); // parser stack
    var $inchannel          = false;
    var $initem             = false;
    
    var $incontent          = array(); // non-empty if in namespaced XML content field
    var $exclude_top        = false; // true when Atom 1.0 type="xhtml"

    var $intextinput        = false;
    var $inimage            = false;
    var $current_namespace  = false;
    
    /**
     *  Set up XML parser, parse source, and return populated RSS object..
     *   
     *  @param string $source           string containing the RSS to be parsed
     *
     *  NOTE:  Probably a good idea to leave the encoding options alone unless
     *         you know what you're doing as PHP's character set support is
     *         a little weird.
     *
     *  NOTE:  A lot of this is unnecessary but harmless with PHP5 
     *
     *
     *  @param string $output_encoding  output the parsed RSS in this character 
     *                                  set defaults to ISO-8859-1 as this is PHP's
     *                                  default.
     *
     *                                  NOTE: might be changed to UTF-8 in future
     *                                  versions.
     *                               
     *  @param string $input_encoding   the character set of the incoming RSS source. 
     *                                  Leave blank and Magpie will try to figure it
     *                                  out.
     *                                  
     *                                   
     *  @param bool   $detect_encoding  if false Magpie won't attempt to detect
     *                                  source encoding. (caveat emptor)
     *
     */
    function Ajw_MagpieRSS ($source, $output_encoding='UTF-8', 
                        $input_encoding=null, $detect_encoding=true) 
    {   
    
        # if PHP xml isn't compiled in, die
        #
        if (!function_exists('xml_parser_create')) {
            $this->ajw_error( "Failed to load PHP's XML Extension. " . 
                          "http://www.php.net/manual/en/ref.xml.php",
                           E_USER_ERROR );
        }
        
        list($parser, $source) = $this->create_parser($source, 
                $output_encoding, $input_encoding, $detect_encoding);
        

        if (!is_resource($parser)) {
            $this->ajw_error( "Failed to create an instance of PHP's XML parser. " .
                          "http://www.php.net/manual/en/ref.xml.php",
                          E_USER_ERROR );
        }

        
        $this->parser = $parser;
        
        # pass in parser, and a reference to this object
        # setup handlers
        #
        xml_set_object( $this->parser, $this );
        xml_set_element_handler($this->parser, 
                'feed_start_element', 'feed_end_element' );
                        
        xml_set_character_data_handler( $this->parser, 'feed_cdata' ); 
    
        $status = xml_parse( $this->parser, $source );
        
        if (! $status ) {
            $errorcode = xml_get_error_code( $this->parser );
            if ( $errorcode != XML_ERROR_NONE ) {
                $xml_error = xml_error_string( $errorcode );
                $error_line = xml_get_current_line_number($this->parser);
                $error_col = xml_get_current_column_number($this->parser);
                $errormsg = "$xml_error at line $error_line, column $error_col";

                $this->ajw_error( $errormsg );
            }
        }
        
        xml_parser_free( $this->parser );

        $this->normalize();
    }
    
    function feed_start_element($p, $element, &$attrs) {
        $el = $element = strtolower($element);
        $attrs = array_change_key_case($attrs, CASE_LOWER);
        
        // check for a namespace, and split if found
        // Don't munge content tags
        if ( empty($this->incontent) ) {   
                $ns = false;
                if ( strpos( $element, ':' ) ) {
                   list($ns, $el) = explode( ':', $element, 2); 
                }
                if ( $ns and $ns != 'rdf' ) {
                   $this->current_namespace = $ns;
               }
          }

        # if feed type isn't set, then this is first element of feed
        # identify feed from root element
        #
        if (!isset($this->feed_type) ) {
            if ( $el == 'rdf' ) {
                $this->feed_type = RSS;
                $this->feed_version = '1.0';
            }
            elseif ( $el == 'rss' ) {
                $this->feed_type = RSS;
                $this->feed_version = $attrs['version'];
            }
            elseif ( $el == 'feed' ) {
                $this->feed_type = ATOM;
                    if ($attrs['xmlns'] == 'http://www.w3.org/2005/Atom') { // Atom 1.0
                        $this->feed_version = '1.0';
                    }
                    else { // Atom 0.3, probably.
                        $this->feed_version = $attrs['version'];
                    }
                $this->inchannel = true;
            }
            return;
        }
    
        // if we're inside a namespaced content construct, treat tags as text
        if ( !empty($this->incontent) ) 
        {
                if ((count($this->incontent) > 1) or !$this->exclude_top) {
                      // if tags are inlined, then flatten
                      $attrs_str = join(' ', 
                          array_map('feegrab_map_attrs', 
                          array_keys($attrs), 
                          array_values($attrs) ) 
                        );
                    
                        if (strlen($attrs_str) > 0) { $attrs_str = ' '.$attrs_str; }
        
                        $this->append_content( "<{$element}{$attrs_str}>"  );
                }
                array_push($this->incontent, $el); // stack for parsing content XML
        } 
        
        elseif ( $el == 'channel' )  {
            $this->inchannel = true;
        }
    
        elseif ($el == 'item' or $el == 'entry' ) 
        {
            $this->initem = true;
            if ( isset($attrs['rdf:about']) ) {
                $this->current_item['about'] = $attrs['rdf:about']; 
            }
        }

        // if we're in the default namespace of an RSS feed,
        //  record textinput or image fields
        elseif ( 
            $this->feed_type == RSS and 
            $this->current_namespace == '' and 
            $el == 'textinput' ) 
        {
            $this->intextinput = true;
        }
        
        elseif (
            $this->feed_type == RSS and 
            $this->current_namespace == '' and 
            $el == 'image' ) 
        {
            $this->inimage = true;
        }
        
        // set stack[0] to current element
        else {
              // Atom support many links per containing element.
              // Magpie treats link elements of type rel='alternate'
              // as being equivalent to RSS's simple link element.

              $atom_link = false;
              if ($this->feed_type == ATOM and $el == 'link') {
                    $atom_link = true;
                    if (isset($attrs['rel']) and $attrs['rel'] != 'alternate') {
                          $el = $el . "_" . $attrs['rel'];  // pseudo-element names for Atom link elements
                    }
              }
              # handle atom content constructs
              elseif ( $this->feed_type == ATOM and in_array($el, $this->_ATOM_CONTENT_CONSTRUCTS) )
              {
                    // avoid clashing w/ RSS mod_content
                    if ($el == 'content' ) {
                          $el = 'atom_content';
                    }

                    // assume that everything accepts namespaced XML
                    // (that will pass through some non-validating feeds;
                    // but so what? this isn't a validating parser)
                    $this->incontent = array();
                    array_push($this->incontent, $el); // start a stack

                    if ( isset($attrs['type']) and trim(strtolower($attrs['type']))=='xhtml') {
                        $this->exclude_top = true;
                    } else {
                        $this->exclude_top = false;
                    }
              }
              # Handle inline XHTML body elements --CWJ
              elseif (($this->current_namespace=='xhtml' or 
                        (isset($attrs['xmlns']) and $attrs['xmlns'] == 'http://www.w3.org/1999/xhtml'))
                      and in_array($el, $this->_XHTML_CONTENT_CONSTRUCTS) )
              {
                    $this->current_namespace = 'xhtml';
                    $this->incontent = array();
                    array_push($this->incontent, $el); // start a stack
                    $this->exclude_top = false;
              }

              array_unshift($this->stack, $el);
              $elpath = join('_', array_reverse($this->stack));
        
              $n = $this->element_count($elpath);
              $this->element_count($elpath, $n+1);
        
              if ($n > 0) {
                  array_shift($this->stack);
                  array_unshift($this->stack, $el.'#'.($n+1));
                  $elpath = join('_', array_reverse($this->stack));
              }

              // this makes the baby Jesus cry, but we can't do it in normalize()
              // because we've made the element name for Atom links unpredictable
              // by tacking on the relation to the end. -CWJ
              if ($atom_link and isset($attrs['href'])) {
                    $this->append($elpath, $attrs['href']);
              }
        
              // add attributes
              if (count($attrs) > 0) {
                    $this->append($elpath.'@', join(',', array_keys($attrs)));
                    foreach ($attrs as $attr => $value) {
                         $this->append($elpath.'@'.$attr, $value);
                    }
              }
       }
    }
    

    
    function feed_cdata ($p, $text) {
        
        if ($this->incontent) {
            $this->append_content( $text );
        }
        else {
            $current_el = join('_', array_reverse($this->stack));
            $this->append($current_el, $text);
        }
    }
    
    function feed_end_element ($p, $el) {
        $el = strtolower($el);

        if ( $this->incontent ) {
        $opener = array_pop($this->incontent);

        // Don't get bamboozled by namespace voodoo
        if (strpos($el, ':')) { list($ns, $closer) = explode(':', $el); }
        else { $ns = false; $closer = $el; }

        // Don't get bamboozled by our munging of <atom:content>, either
        if ($this->feed_type == ATOM and $closer == 'content') {
            $closer = 'atom_content';
        }

        // balance tags properly
        // note:  i don't think this is actually neccessary
        if ($opener != $closer) {
            array_push($this->incontent, $opener);
            $this->append_content("<$el />");
        } elseif ($this->incontent) { // are we in the content construct still?
            if ((count($this->incontent) > 1) or !$this->exclude_top) {
                $this->append_content("</$el>");
            }
        } else { // shift the opening of the content construct off the normal stack
            array_shift( $this->stack );
        }
        }
        elseif ( $el == 'item' or $el == 'entry' ) 
        {
            $this->items[] = $this->current_item;
            $this->current_item = array();
            $this->initem = false;

        $this->current_category = 0;
        }
       elseif ($this->feed_type == RSS and $this->current_namespace == '' and $el == 'textinput' ) 
        {
            $this->intextinput = false;
        }
        elseif ($this->feed_type == RSS and $this->current_namespace == '' and $el == 'image' ) 
        {
            $this->inimage = false;
        }
        elseif ($el == 'channel' or $el == 'feed' ) 
        {
            $this->inchannel = false;
        }
        else {
        array_shift( $this->stack );
        }
        
    if ( !$this->incontent ) { // Don't munge the namespace after finishing with elements in namespaced content constructs -CWJ
        $this->current_namespace = false;
    }
    }
    
    function concat (&$str1, $str2="") {
        if (!isset($str1) ) {
            $str1="";
        }
        $str1 .= $str2;
    }
    
    function append_content($text) {
    if ( $this->initem ) {
        if ($this->current_namespace) {
            $this->concat( $this->current_item[$this->current_namespace][ reset($this->incontent) ], $text );
        } else {
            $this->concat( $this->current_item[ reset($this->incontent) ], $text );
        }
    }
    elseif ( $this->inchannel ) {
        if ($this->current_namespace) {
            $this->concat( $this->channel[$this->current_namespace][ reset($this->incontent) ], $text );
        } else {
            $this->concat( $this->channel[ reset($this->incontent) ], $text );
        }
    }
    }
    
    // smart append - field and namespace aware
    function append($el, $text) {
        if (!$el) {
            return;
        }
        if ( $this->current_namespace ) 
        {
            if ( $this->initem ) {
            $this->concat(
                $this->current_item[ $this->current_namespace ][ $el ], $text);
            }
            elseif ($this->inchannel) {
        $this->concat(
            $this->channel[ $this->current_namespace][ $el ], $text );
        }
            elseif ($this->intextinput) {
                $this->concat(
                    $this->textinput[ $this->current_namespace][ $el ], $text );
            }
            elseif ($this->inimage) {
                $this->concat(
                    $this->image[ $this->current_namespace ][ $el ], $text );
            }
        }
        else {
            if ( $this->initem ) {
        $this->concat(
            $this->current_item[ $el ], $text);
            }
            elseif ($this->intextinput) {
                $this->concat(
                    $this->textinput[ $el ], $text );
            }
            elseif ($this->inimage) {
                $this->concat(
                    $this->image[ $el ], $text );
            }
            elseif ($this->inchannel) {
        $this->concat(
            $this->channel[ $el ], $text );
            }
            
        }
    }

    // smart count - field and namespace aware
    function element_count ($el, $set = NULL) {
        if (!$el) {
            return;
        }
        if ( $this->current_namespace ) 
        {
            if ( $this->initem ) {
            if (!is_null($set)) { $this->current_item[ $this->current_namespace ][ $el.'#' ] = $set; }
            $ret = (isset($this->current_item[ $this->current_namespace ][ $el.'#' ]) ?
            $this->current_item[ $this->current_namespace ][ $el.'#' ] : 0);
            }
            elseif ($this->inchannel) {
            if (!is_null($set)) { $this->channel[ $this->current_namespace ][ $el.'#' ] = $set; }
            $ret = (isset($this->channel[ $this->current_namespace][ $el.'#' ]) ?
            $this->channel[ $this->current_namespace][ $el.'#' ] : 0);
        }
        }
        else {
            if ( $this->initem ) {
            if (!is_null($set)) { $this->current_item[ $el.'#' ] = $set; }
            $ret = (isset($this->current_item[ $el.'#' ]) ?
            $this->current_item[ $el.'#' ] : 0);
            }
            elseif ($this->inchannel) {
            if (!is_null($set)) {$this->channel[ $el.'#' ] = $set; }
            $ret = (isset($this->channel[ $el.'#' ]) ?
            $this->channel[ $el.'#' ] : 0);
}
        }
    return $ret;
    }

    function normalize_enclosure (&$source, $from, &$dest, $to, $i) {
        $id_from = $this->element_id($from, $i);
        $id_to = $this->element_id($to, $i);
        if (isset($source["{$id_from}@"])) {
            foreach (explode(',', $source["{$id_from}@"]) as $attr) {
                if ($from=='link_enclosure' and $attr=='href') { // from Atom
                    $dest["{$id_to}@url"] = $source["{$id_from}@{$attr}"];
                    $dest["{$id_to}"] = $source["{$id_from}@{$attr}"];
                }
                elseif ($from=='enclosure' and $attr=='url') { // from RSS
                    $dest["{$id_to}@href"] = $source["{$id_from}@{$attr}"];
                    $dest["{$id_to}"] = $source["{$id_from}@{$attr}"];
                }
                else {
                    $dest["{$id_to}@{$attr}"] = $source["{$id_from}@{$attr}"];
                }
            }
        }
    }

    function normalize_atom_person (&$source, $person, &$dest, $to, $i) {
        $id = $this->element_id($person, $i);
        $id_to = $this->element_id($to, $i);

            // Atom 0.3 <=> Atom 1.0
        if ($this->feed_version >= 1.0) { $used = 'uri'; $norm = 'url'; }
        else { $used = 'url'; $norm = 'uri'; }

        if (isset($source["{$id}_{$used}"])) {
            $dest["{$id_to}_{$norm}"] = $source["{$id}_{$used}"];
        }

        // Atom to RSS 2.0 and Dublin Core
        // RSS 2.0 person strings should be valid e-mail addresses if possible.
        if (isset($source["{$id}_email"])) {
            $rss_author = $source["{$id}_email"];
        }
        if (isset($source["{$id}_name"])) {
            $rss_author = $source["{$id}_name"]
                . (isset($rss_author) ? " <$rss_author>" : '');
        }
        if (isset($rss_author)) {
            $source[$id] = $rss_author; // goes to top-level author or contributor
        $dest[$id_to] = $rss_author; // goes to dc:creator or dc:contributor
        }
    }

    // Normalize Atom 1.0 and RSS 2.0 categories to Dublin Core...
    function normalize_category (&$source, $from, &$dest, $to, $i) {
        $cat_id = $this->element_id($from, $i);
        $dc_id = $this->element_id($to, $i);

        // first normalize category elements: Atom 1.0 <=> RSS 2.0
        if ( isset($source["{$cat_id}@term"]) ) { // category identifier
            $source[$cat_id] = $source["{$cat_id}@term"];
        } elseif ( $this->feed_type == RSS ) {
            $source["{$cat_id}@term"] = $source[$cat_id];
        }
        
        if ( isset($source["{$cat_id}@scheme"]) ) { // URI to taxonomy
            $source["{$cat_id}@domain"] = $source["{$cat_id}@scheme"];
        } elseif ( isset($source["{$cat_id}@domain"]) ) {
            $source["{$cat_id}@scheme"] = $source["{$cat_id}@domain"];
        }

        // Now put the identifier into dc:subject
        $dest[$dc_id] = $source[$cat_id];
    }
    
    // ... or vice versa
    function normalize_dc_subject (&$source, $from, &$dest, $to, $i) {
        $dc_id = $this->element_id($from, $i);
        $cat_id = $this->element_id($to, $i);

        $dest[$cat_id] = $source[$dc_id];       // RSS 2.0
        $dest["{$cat_id}@term"] = $source[$dc_id];  // Atom 1.0
    }

    // simplify the logic for normalize(). Makes sure that count of elements and
    // each of multiple elements is normalized properly. If you need to mess
    // with things like attributes or change formats or the like, pass it a
    // callback to handle each element.
    function normalize_element (&$source, $from, &$dest, $to, $via = NULL) {
        if (isset($source[$from]) or isset($source["{$from}#"])) {
            if (isset($source["{$from}#"])) {
                $n = $source["{$from}#"];
                $dest["{$to}#"] = $source["{$from}#"];
            }
            else { $n = 1; }

            for ($i = 1; $i <= $n; $i++) {
                if (isset($via)) { // custom callback for ninja attacks
                    $this->{$via}($source, $from, $dest, $to, $i);
                }
                else { // just make it the same
                    $from_id = $this->element_id($from, $i);
                    $to_id = $this->element_id($to, $i);
										if (isset ( $source[$from_id] ) ) {
                    	$dest[$to_id] = $source[$from_id];
										}
                }
            }
        }
    }

    function normalize () {
        // if atom populate rss fields and normalize 0.3 and 1.0 feeds
        if ( $this->is_atom() ) {
        // Atom 1.0 elements <=> Atom 0.3 elements (Thanks, o brilliant wordsmiths of the Atom 1.0 standard!)
        if ($this->feed_version < 1.0) {
            $this->normalize_element($this->channel, 'tagline', $this->channel, 'subtitle');
            $this->normalize_element($this->channel, 'copyright', $this->channel, 'rights');
            $this->normalize_element($this->channel, 'modified', $this->channel, 'updated');
        } else {
            $this->normalize_element($this->channel, 'subtitle', $this->channel, 'tagline');
            $this->normalize_element($this->channel, 'rights', $this->channel, 'copyright');
            $this->normalize_element($this->channel, 'updated', $this->channel, 'modified');
        }
        $this->normalize_element($this->channel, 'author', $this->channel['dc'], 'creator', 'normalize_atom_person');
        $this->normalize_element($this->channel, 'contributor', $this->channel['dc'], 'contributor', 'normalize_atom_person');

        // Atom elements to RSS elements
        $this->normalize_element($this->channel, 'subtitle', $this->channel, 'description');
        
        if ( isset($this->channel['logo']) ) {
            $this->normalize_element($this->channel, 'logo', $this->image, 'url');
            $this->normalize_element($this->channel, 'link', $this->image, 'link');
            $this->normalize_element($this->channel, 'title', $this->image, 'title');
        }

        for ( $i = 0; $i < count($this->items); $i++) {
            $item = $this->items[$i];

            // Atom 1.0 elements <=> Atom 0.3 elements
            if ($this->feed_version < 1.0) {
                $this->normalize_element($item, 'modified', $item, 'updated');
                $this->normalize_element($item, 'issued', $item, 'published');
            } else {
                $this->normalize_element($item, 'updated', $item, 'modified');
                $this->normalize_element($item, 'published', $item, 'issued');
            }

            // "If an atom:entry element does not contain
            // atom:author elements, then the atom:author elements
            // of the contained atom:source element are considered
            // to apply. In an Atom Feed Document, the atom:author
            // elements of the containing atom:feed element are
            // considered to apply to the entry if there are no
            // atom:author elements in the locations described
            // above." <http://atompub.org/2005/08/17/draft-ietf-atompub-format-11.html#rfc.section.4.2.1>
            if (!isset($item["author#"])) {
                if (isset($item["source_author#"])) { // from aggregation source
                    $source = $item;
                    $author = "source_author";
                } elseif (isset($this->channel["author#"])) { // from containing feed
                    $source = $this->channel;
                    $author = "author";
                }

                $item["author#"] = $source["{$author}#"];
                for ($au = 1; $au <= $item["author#"]; $au++) {
                    $id_to = $this->element_id('author', $au);
                    $id_from = $this->element_id($author, $au);
                    
                    $item[$id_to] = $source[$id_from];
                    foreach (array('name', 'email', 'uri', 'url') as $what) {
                        if (isset($source["{$id_from}_{$what}"])) {
                            $item["{$id_to}_{$what}"] = $source["{$id_from}_{$what}"];
                        }
                    }
                }
            }

            // Atom elements to RSS elements
            $this->normalize_element($item, 'author', $item['dc'], 'creator', 'normalize_atom_person');
            $this->normalize_element($item, 'contributor', $item['dc'], 'contributor', 'normalize_atom_person');
            $this->normalize_element($item, 'summary', $item, 'description');
            $this->normalize_element($item, 'atom_content', $item['content'], 'encoded');
            $this->normalize_element($item, 'link_enclosure', $item, 'enclosure', 'normalize_enclosure');

            // Categories
            if ( isset($item['category#']) ) { // Atom 1.0 categories to dc:subject and RSS 2.0 categories
                $this->normalize_element($item, 'category', $item['dc'], 'subject', 'normalize_category');
            }
            elseif ( isset($item['dc']['subject#']) ) { // dc:subject to Atom 1.0 and RSS 2.0 categories
                $this->normalize_element($item['dc'], 'subject', $item, 'category', 'normalize_dc_subject');
            }

            // Normalized item timestamp
            $atom_date = (isset($item['published']) ) ? $item['published'] : $item['updated'];
            if ( $atom_date ) {
                $epoch = @parse_w3cdtf($atom_date);
                if ($epoch and $epoch > 0) {
                    $item['date_timestamp'] = $epoch;
                }
            }

            $this->items[$i] = $item;
        }
        }
        elseif ( $this->is_rss() ) {
        // RSS elements to Atom elements
        $this->normalize_element($this->channel, 'description', $this->channel, 'tagline'); // Atom 0.3
        $this->normalize_element($this->channel, 'description', $this->channel, 'subtitle'); // Atom 1.0 (yay wordsmithing!)
        $this->normalize_element($this->image, 'url', $this->channel, 'logo');

            for ( $i = 0; $i < count($this->items); $i++) {
                $item = $this->items[$i];
        
        // RSS elements to Atom elements
        $this->normalize_element($item, 'description', $item, 'summary');
                $this->normalize_element($item['content'], 'encoded', $item, 'atom_content');
        $this->normalize_element($item, 'enclosure', $item, 'link_enclosure', 'normalize_enclosure');

        // Categories
        if ( isset($item['category#']) ) { // RSS 2.0 categories to dc:subject and Atom 1.0 categories
            $this->normalize_element($item, 'category', $item['dc'], 'subject', 'normalize_category');
        }
        elseif ( isset($item['dc']['subject#']) ) { // dc:subject to Atom 1.0 and RSS 2.0 categories
            $this->normalize_element($item['dc'], 'subject', $item, 'category', 'normalize_dc_subject');
        }

        // Normalized item timestamp
                if ( $this->is_rss() == '1.0' and isset($item['dc']['date']) ) {
                    $epoch = @parse_w3cdtf($item['dc']['date']);
                    if ($epoch and $epoch > 0) {
                        $item['date_timestamp'] = $epoch;
                    }
                }
                elseif ( isset($item['pubdate']) ) {
                    $epoch = @strtotime($item['pubdate']);
                    if ($epoch > 0) {
                        $item['date_timestamp'] = $epoch;
                    }
                }

                $this->items[$i] = $item;
            }
        }
    }
    
    
    function is_rss () {
        if ( $this->feed_type == RSS ) {
            return $this->feed_version; 
        }
        else {
            return false;
        }
    }
    
    function is_atom() {
        if ( $this->feed_type == ATOM ) {
            return $this->feed_version;
        }
        else {
            return false;
        }
    }

    /**
    * return XML parser, and possibly re-encoded source
    *
    */
    function create_parser($source, $out_enc, $in_enc, $detect) {
        if ( substr(phpversion(),0,1) == 5) {
            $parser = $this->php5_create_parser($in_enc, $detect);
        }
        else {
            list($parser, $source) = $this->php4_create_parser($source, $in_enc, $detect);
        }
        if ($out_enc) {
            $this->encoding = $out_enc;
            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $out_enc);
        }
        return array($parser, $source);
    }
    
    /**
    * Instantiate an XML parser under PHP5
    *
    * PHP5 will do a fine job of detecting input encoding
    * if passed an empty string as the encoding. 
    *
    * All hail libxml2!
    *
    */
    function php5_create_parser($in_enc, $detect) {
        // by default php5 does a fine job of detecting input encodings
        if(!$detect && $in_enc) {
            return xml_parser_create($in_enc);
        }
        else {
            return xml_parser_create('');
        }
    }
    
    /**
    * Instaniate an XML parser under PHP4
    *
    * Unfortunately PHP4's support for character encodings
    * and especially XML and character encodings sucks.  As
    * long as the documents you parse only contain characters
    * from the ISO-8859-1 character set (a superset of ASCII,
    * and a subset of UTF-8) you're fine.  However once you
    * step out of that comfy little world things get mad, bad,
    * and dangerous to know.
    *
    * The following code is based on SJM's work with FoF
    * @see http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    *
    */
    function php4_create_parser($source, $in_enc, $detect) {
        if ( !$detect ) {
            return array(xml_parser_create($in_enc), $source);
        }
        
        if (!$in_enc) {
            if (preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m', $source, $m)) {
                $in_enc = strtoupper($m[1]);
                $this->source_encoding = $in_enc;
            }
            else {
                $in_enc = 'UTF-8';
            }
        }
        
        if ($this->known_encoding($in_enc)) {
            return array(xml_parser_create($in_enc), $source);
        }
        
        // the dectected encoding is not one of the simple encodings PHP knows
        
        // attempt to use the iconv extension to
        // cast the XML to a known encoding
        // @see http://php.net/iconv
       
        if (function_exists('iconv'))  {
            $encoded_source = iconv($in_enc,'UTF-8', $source);
            if ($encoded_source) {
                return array(xml_parser_create('UTF-8'), $encoded_source);
            }
        }
        
        // iconv didn't work, try mb_convert_encoding
        // @see http://php.net/mbstring
        if(function_exists('mb_convert_encoding')) {
            $encoded_source = @mb_convert_encoding($source, 'UTF-8', $in_enc );
            if ($encoded_source) {
                return array(xml_parser_create('UTF-8'), $encoded_source);
            }
        }
        
        // else 
        $this->ajw_error("Feed is in an unsupported character encoding. ($in_enc) " .
                     "You may see strange artifacts, and mangled characters.",
                     E_USER_NOTICE);
            
        return array(xml_parser_create(), $source);
    }
    
    function known_encoding($enc) {
        $enc = strtoupper($enc);
        if ( in_array($enc, $this->_KNOWN_ENCODINGS) ) {
            return $enc;
        }
        else {
            return false;
        }
    }

    function ajw_error ($errormsg, $lvl=E_USER_WARNING) {
        // append PHP's error message if track_errors enabled
        if ( isset($php_errormsg) ) { 
            $errormsg .= " ($php_errormsg)";
        }
        if ( MAGPIE_DEBUG ) {
            trigger_error( $errormsg, $lvl);        
        }
        else {
            error_log( $errormsg, 0);
        }
        
        $notices = E_USER_NOTICE|E_NOTICE;
        if ( $lvl&$notices ) {
            $this->WARNING = $errormsg;
        } else {
            $this->ERROR = $errormsg;
        }
    }

    // magic ID function for multiple elemenets.
    // can be called as static MagpieRSS::element_id()
    function element_id ($el, $counter) {
        return $el . (($counter > 1) ? '#'.$counter : '');
    }
} // end class RSS

function feegrab_map_attrs($k, $v) {
    return "$k=\"$v\"";
}

// patch to support medieval versions of PHP4.1.x, 
// courtesy, Ryan Currie, ryan@digibliss.com

if (!function_exists('array_change_key_case')) {
    define("CASE_UPPER",1);
    define("CASE_LOWER",0);


    function array_change_key_case($array,$case=CASE_LOWER) {
       if ($case==CASE_LOWER) $cmd='strtolower';
       elseif ($case==CASE_UPPER) $cmd='strtoupper';
       foreach($array as $key=>$value) {
               $output[$cmd($key)]=$value;
       }
       return $output;
    }

}

/*
 * Project:     MagpieRSS: a simple RSS integration tool
 * File:        rss_utils.inc, utility methods for working with RSS
 * Author:      Kellan Elliott-McCrea <kellan@protest.net>
 * Version:     0.51
 * License:     GPL
 *
 * The lastest version of MagpieRSS can be obtained from:
 * http://magpierss.sourceforge.net
 *
 * For questions, help, comments, discussion, etc., please join the
 * Magpie mailing list:
 * magpierss-general@lists.sourceforge.net
 */


/*======================================================================*\
    Function: parse_w3cdtf
    Purpose:  parse a W3CDTF date into unix epoch

    NOTE: http://www.w3.org/TR/NOTE-datetime
\*======================================================================*/

function parse_w3cdtf ( $date_str ) {
    
    # regex to match wc3dtf
    $pat = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/";
    
    if ( preg_match( $pat, $date_str, $match ) ) {
        list( $year, $month, $day, $hours, $minutes, $seconds) = 
            array( $match[1], $match[2], $match[3], $match[4], $match[5], $match[6]);
        
        # calc epoch for current date assuming GMT
        $epoch = gmmktime( $hours, $minutes, $seconds, $month, $day, $year);
        
        $offset = 0;
        if ( $match[10] == 'Z' ) {
            # zulu time, aka GMT
        }
        else {
            list( $tz_mod, $tz_hour, $tz_min ) =
                array( $match[8], $match[9], $match[10]);
            
            # zero out the variables
            if ( ! $tz_hour ) { $tz_hour = 0; }
            if ( ! $tz_min ) { $tz_min = 0; }
        
            $offset_secs = (($tz_hour*60)+$tz_min)*60;
            
            # is timezone ahead of GMT?  then subtract offset
            #
            if ( $tz_mod == '+' ) {
                $offset_secs = $offset_secs * -1;
            }
            
            $offset = $offset_secs; 
        }
        $epoch = $epoch + $offset;
        return $epoch;
    }
    else {
        return -1;
    }
}

/*
 * Project:     MagpieRSS: a simple RSS integration tool
 * File:        rss_fetch.inc, a simple functional interface
                to fetching and parsing RSS files, via the
                function ajw_fetch_rss()
 * Author:      Kellan Elliott-McCrea <kellan@protest.net>
 * License:     GPL
 *
 * The lastest version of MagpieRSS can be obtained from:
 * http://magpierss.sourceforge.net
 *
 * For questions, help, comments, discussion, etc., please join the
 * Magpie mailing list:
 * magpierss-general@lists.sourceforge.net
 *
 */
 
// Setup MAGPIE_DIR for use on hosts that don't include
// the current path in include_path.
// with thanks to rajiv and smarty
if (!defined('DIR_SEP')) {
    define('DIR_SEP', DIRECTORY_SEPARATOR);
}

if (!defined('MAGPIE_DIR')) {
    define('MAGPIE_DIR', dirname(__FILE__) . DIR_SEP);
}

/* 
 * CONSTANTS - redefine these in your script to change the
 * behaviour of ajw_fetch_rss() currently, most options effect the cache
 *
 * MAGPIE_CACHE_ON - Should Magpie cache parsed RSS objects? 
 * For me a built in cache was essential to creating a "PHP-like" 
 * feel to Magpie, see rss_cache.inc for rationale
 *
 *
 * MAGPIE_CACHE_DIR - Where should Magpie cache parsed RSS objects?
 * This should be a location that the webserver can write to.   If this 
 * directory does not already exist Mapie will try to be smart and create 
 * it.  This will often fail for permissions reasons.
 *
 *
 * MAGPIE_CACHE_AGE - How long to store cached RSS objects? In seconds.
 *
 *
 * MAGPIE_CACHE_FRESH_ONLY - If remote fetch fails, throw error
 * instead of returning stale object?
 *
 * MAGPIE_DEBUG - Display debugging notices?
 *
*/


/*=======================================================================*\
    Function: ajw_fetch_rss: 
    Purpose:  return RSS object for the give url
              maintain the cache
    Input:    url of RSS file
    Output:   parsed RSS object (see rss_parse.inc)

    NOTES ON CACHEING:  
    If caching is on (MAGPIE_CACHE_ON) ajw_fetch_rss will first check the cache.
    
    NOTES ON RETRIEVING REMOTE FILES:
    If conditional gets are on (MAGPIE_CONDITIONAL_GET_ON) ajw_fetch_rss will
    return a cached object, and touch the cache object upon recieving a
    304.
    
    NOTES ON FAILED REQUESTS:
    If there is an HTTP error while fetching an RSS object, the cached
    version will be return, if it exists (and if MAGPIE_CACHE_FRESH_ONLY is off)
\*=======================================================================*/

if( !defined('MAGPIE_VERSION') ) define('MAGPIE_VERSION', '0.79a');

$MAGPIE_ERROR = "";

function ajw_fetch_rss ($url) {
    // initialize constants

    ajw_init();
    
    if ( !isset($url) ) {
        ajw_error("ajw_fetch_rss called without a url");
        return false;
    }
    
    // if cache is disabled
    if ( !MAGPIE_CACHE_ON ) {
        // fetch file, and parse it
        $resp = _ajw_fetch_remote_file( $url );
        if ( ajw_is_success( $resp->status ) ) {
            return _ajw_response_to_rss( $resp );
        }
        else {
            ajw_error("Failed to fetch $url and cache is off");
            return false;
        }
    } 
    // else cache is ON
    else {
        // Flow
        // 1. check cache
        // 2. if there is a hit, make sure its fresh
        // 3. if cached obj fails freshness check, fetch remote
        // 4. if remote fails, return stale object, or error
        
        $cache = new Ajw_RSSCache( MAGPIE_CACHE_DIR, MAGPIE_CACHE_AGE );
        
        if (MAGPIE_DEBUG and $cache->ERROR) {
            ajw_debug($cache->ERROR, E_USER_WARNING);
        }
        
        
        $cache_status    = 0;       // response of check_cache
        $request_headers = array(); // HTTP headers to send with fetch
        $rss             = 0;       // parsed RSS object
        $errormsg        = 0;       // errors, if any
        
        // store parsed XML by desired output encoding
        // as character munging happens at parse time
        $cache_key       = $url . AJW_MAGPIE_OUTPUT_ENCODING;
        
        if (!$cache->ERROR) {
            // return cache HIT, MISS, or STALE
            $cache_status = $cache->check_cache( $cache_key);
        }
                
        // if object cached, and cache is fresh, return cached obj
        if ( $cache_status == 'HIT' ) {
            $rss = $cache->get( $cache_key );
            if ( isset($rss) and $rss ) {
                // should be cache age
                $rss->from_cache = 1;
                if ( MAGPIE_DEBUG > 1) {
                    ajw_debug("MagpieRSS: Cache HIT", E_USER_NOTICE);
                }
                return $rss;
            }
        }
        
        // else attempt a conditional get
        
        // setup headers
        if ( $cache_status == 'STALE' ) {
            $rss = $cache->get( $cache_key );
            if ( $rss and isset($rss->etag) and $rss->etag and $rss->last_modified ) {
                $request_headers['If-None-Match'] = $rss->etag;
                $request_headers['If-Last-Modified'] = $rss->last_modified;
            }
        }
        
        $resp = _ajw_fetch_remote_file( $url, $request_headers );
        
        if (isset($resp) and $resp) {
          if ($resp->status == '304' ) {
                // we have the most current copy
                if ( MAGPIE_DEBUG > 1) {
                    ajw_debug("Got 304 for $url");
                }
                // reset cache on 304 (at minutillo insistent prodding)
                $cache->set($cache_key, $rss);
                return $rss;
            }
            elseif ( ajw_is_success( $resp->status ) ) {
                $rss = _ajw_response_to_rss( $resp );
                if ( $rss ) {
                    if (MAGPIE_DEBUG > 1) {
                        ajw_debug("Fetch successful");
                    }
                    // add object to cache
                    $cache->set( $cache_key, $rss );
                    return $rss;
                }
            }
            else {
                $errormsg = "Failed to fetch $url ";
                if ( $resp->status == '-100' ) {
                    $errormsg .= "(Request timed out after " . MAGPIE_FETCH_TIME_OUT . " seconds)";
                }
                elseif ( $resp->error ) {
                    # compensate for Snoopy's annoying habbit to tacking
                    # on '\n'
                    $http_error = substr($resp->error, 0, -2); 
                    $errormsg .= "(HTTP Error: $http_error)";
                }
                else {
                    $errormsg .=  "(HTTP Response: " . $resp->response_code .')';
                }
            }
        }
        else {
            $errormsg = "Unable to retrieve RSS file for unknown reasons.";
        }
        
        // else fetch failed
        
        // attempt to return cached object
        if ($rss) {
            if ( MAGPIE_DEBUG ) {
                ajw_debug("Returning STALE object for $url");
            }
            return $rss;
        }
        
        // else we totally failed
        ajw_error( $errormsg ); 
        
        return false;
        
    } // end if ( !MAGPIE_CACHE_ON ) {
} // end ajw_fetch_rss()

/*=======================================================================*\
    Function:   error
    Purpose:    set MAGPIE_ERROR, and trigger error
\*=======================================================================*/

function ajw_error ($errormsg, $lvl=E_USER_WARNING) {
        global $MAGPIE_ERROR;
        
        // append PHP's error message if track_errors enabled
        if ( isset($php_errormsg) ) { 
            $errormsg .= " ($php_errormsg)";
        }
        if ( $errormsg ) {
            $errormsg = "MagpieRSS: $errormsg";
            $MAGPIE_ERROR = $errormsg;
            trigger_error( $errormsg, $lvl);             
        }
}

function ajw_debug ($debugmsg, $lvl=E_USER_NOTICE) {
    trigger_error("MagpieRSS [debug] $debugmsg", $lvl);
}
            
/*=======================================================================*\
    Function:   magpie_error
    Purpose:    accessor for the magpie error variable
\*=======================================================================*/
function ajw_magpie_error ($errormsg="") {
    global $MAGPIE_ERROR;
    
    if ( isset($errormsg) and $errormsg ) { 
        $MAGPIE_ERROR = $errormsg;
    }
    
    return $MAGPIE_ERROR;   
}

/*=======================================================================*\
    Function:   _ajw_fetch_remote_file
    Purpose:    retrieve an arbitrary remote file
    Input:      url of the remote file
                headers to send along with the request (optional)
    Output:     an HTTP response object (see Snoopy.class.inc)  
\*=======================================================================*/
function _ajw_fetch_remote_file ($url, $headers = "" ) {
    // Snoopy is an HTTP client in PHP
    $client = new Snoopy();
    $client->agent = MAGPIE_USER_AGENT;
    $client->read_timeout = MAGPIE_FETCH_TIME_OUT;
    $client->use_gzip = MAGPIE_USE_GZIP;
    if (is_array($headers) ) {
        $client->rawheaders = $headers;
    }
    
    @$client->fetch($url);
    return $client;

}

/*=======================================================================*\
    Function:   _ajw_response_to_rss
    Purpose:    parse an HTTP response object into an RSS object
    Input:      an HTTP response object (see Snoopy)
    Output:     parsed RSS object (see rss_parse)
\*=======================================================================*/
function _ajw_response_to_rss ($resp) {
    $rss = new Ajw_MagpieRSS( $resp->results, AJW_MAGPIE_OUTPUT_ENCODING, MAGPIE_INPUT_ENCODING, MAGPIE_DETECT_ENCODING );
    
    // if RSS parsed successfully       
    if ( $rss and !$rss->ERROR) {
        
        // find Etag, and Last-Modified
        foreach($resp->headers as $h) {
            // 2003-03-02 - Nicola Asuni (www.tecnick.com) - fixed bug "Undefined offset: 1"
            if (strpos($h, ": ")) {
                list($field, $val) = explode(": ", $h, 2);
            }
            else {
                $field = $h;
                $val = "";
            }
            
            if ( $field == 'ETag' ) {
                $rss->etag = $val;
            }
            
            if ( $field == 'Last-Modified' ) {
                $rss->last_modified = $val;
            }
        }
        
        return $rss;    
    } // else construct error message
    else {
        $errormsg = "Failed to parse RSS file.";
        
        if ($rss) {
            $errormsg .= " (" . $rss->ERROR . ")";
        }
        ajw_error($errormsg);
        
        return false;
    } // end if ($rss and !$rss->error)
}

/*=======================================================================*\
    Function:   ajw_init
    Purpose:    setup constants with default values
                check for user overrides
\*=======================================================================*/
function ajw_init () {
    if ( defined('AJW_MAGPIE_INITALIZED') ) {
        return;
    }
    else {
        define('AJW_MAGPIE_INITALIZED', true);
    }
    
    if ( !defined('MAGPIE_CACHE_ON') ) {
        define('MAGPIE_CACHE_ON', true);
    }

    if ( !defined('MAGPIE_CACHE_DIR') ) {
        define('MAGPIE_CACHE_DIR', './cache');
    }

    if ( !defined('MAGPIE_CACHE_AGE') ) {
        define('MAGPIE_CACHE_AGE', 60*60); // one hour
    }

    if ( !defined('MAGPIE_CACHE_FRESH_ONLY') ) {
        define('MAGPIE_CACHE_FRESH_ONLY', false);
    }

    if ( !defined('AJW_MAGPIE_OUTPUT_ENCODING') ) {
        define('AJW_MAGPIE_OUTPUT_ENCODING', 'UTF-8');
    }
    
    if ( !defined('MAGPIE_INPUT_ENCODING') ) {
        define('MAGPIE_INPUT_ENCODING', null);
    }
    
    if ( !defined('MAGPIE_DETECT_ENCODING') ) {
        define('MAGPIE_DETECT_ENCODING', false);
    }
    
    if ( !defined('MAGPIE_DEBUG') ) {
        define('MAGPIE_DEBUG', 0);
    }
    
    if ( !defined('MAGPIE_USER_AGENT') ) {
        $ua = 'MagpieRSS/'. MAGPIE_VERSION . ' (+http://magpierss.sf.net';
        
        if ( MAGPIE_CACHE_ON ) {
            $ua = $ua . ')';
        }
        else {
            $ua = $ua . '; No cache)';
        }
        
        define('MAGPIE_USER_AGENT', $ua);
    }
    
    if ( !defined('MAGPIE_FETCH_TIME_OUT') ) {
        define('MAGPIE_FETCH_TIME_OUT', 5); // 5 second timeout
    }
    
    // use gzip encoding to fetch rss files if supported?
    if ( !defined('MAGPIE_USE_GZIP') ) {
        define('MAGPIE_USE_GZIP', true);    
    }
}

// NOTE: the following code should really be in Snoopy, or at least
// somewhere other then rss_fetch!

/*=======================================================================*\
    HTTP STATUS CODE PREDICATES
    These functions attempt to classify an HTTP status code
    based on RFC 2616 and RFC 2518.
    
    All of them take an HTTP status code as input, and return true or false

    All this code is adapted from LWP's HTTP::Status.
\*=======================================================================*/


/*=======================================================================*\
    Function:   ajw_is_info
    Purpose:    return true if Informational status code
\*=======================================================================*/
function ajw_is_info ($sc) { 
    return $sc >= 100 && $sc < 200; 
}

/*=======================================================================*\
    Function:   ajw_is_success
    Purpose:    return true if Successful status code
\*=======================================================================*/
function ajw_is_success ($sc) { 
    return $sc >= 200 && $sc < 300; 
}

/*=======================================================================*\
    Function:   ajw_is_redirect
    Purpose:    return true if Redirection status code
\*=======================================================================*/
function ajw_is_redirect ($sc) { 
    return $sc >= 300 && $sc < 400; 
}

/*=======================================================================*\
    Function:   ajw_is_error
    Purpose:    return true if Error status code
\*=======================================================================*/
function ajw_is_error ($sc) { 
    return $sc >= 400 && $sc < 600; 
}

/*=======================================================================*\
    Function:   ajw_client_error
    Purpose:    return true if Error status code, and its a client error
\*=======================================================================*/
function ajw_client_error ($sc) { 
    return $sc >= 400 && $sc < 500; 
}

/*=======================================================================*\
    Function:   ajw_client_error
    Purpose:    return true if Error status code, and its a server error
\*=======================================================================*/
function is_ajw_server_error ($sc) { 
    return $sc >= 500 && $sc < 600; 
}

/*
 * Project:     MagpieRSS: a simple RSS integration tool
 * File:        rss_cache.inc, a simple, rolling(no GC), cache 
 *              for RSS objects, keyed on URL.
 * Author:      Kellan Elliott-McCrea <kellan@protest.net>
 * Version:     0.51
 * License:     GPL
 *
 * The lastest version of MagpieRSS can be obtained from:
 * http://magpierss.sourceforge.net
 *
 * For questions, help, comments, discussion, etc., please join the
 * Magpie mailing list
 * http://lists.sourceforge.net/lists/listinfo/magpierss-general
 *
 */

class Ajw_RSSCache {
    var $BASE_CACHE = './cache';    // where the cache files are stored
    var $MAX_AGE    = 3600;         // when are files stale, default one hour
    var $ERROR      = "";           // accumulate error messages
    
    function Ajw_RSSCache ($base='', $age='') {
        if ( $base ) {
            $this->BASE_CACHE = $base;
        }
        if ( $age ) {
            $this->MAX_AGE = $age;
        }
        
        // attempt to make the cache directory
        if ( ! file_exists( $this->BASE_CACHE ) ) {
            $status = @mkdir( $this->BASE_CACHE, 0755 );
            
            // if make failed 
            if ( ! $status ) {
                $this->ajw_error(
                    "Cache couldn't make dir '" . $this->BASE_CACHE . "'."
                );
            }
        }
    }
    
/*=======================================================================*\
    Function:   set
    Purpose:    add an item to the cache, keyed on url
    Input:      url from wich the rss file was fetched
    Output:     true on sucess  
\*=======================================================================*/
    function set ($url, $rss) {
        $this->ERROR = "";
        $cache_file = $this->file_name( $url );
        $fp = @fopen( $cache_file, 'w' );
        
        if ( ! $fp ) {
            $this->ajw_error(
                "Cache unable to open file for writing: $cache_file"
            );
            return 0;
        }
        
        
        $data = $this->serialize( $rss );
        fwrite( $fp, $data );
        fclose( $fp );
        
        return $cache_file;
    }
    
/*=======================================================================*\
    Function:   get
    Purpose:    fetch an item from the cache
    Input:      url from wich the rss file was fetched
    Output:     cached object on HIT, false on MISS 
\*=======================================================================*/ 
    function get ($url) {
        $this->ERROR = "";
        $cache_file = $this->file_name( $url );
        
        if ( ! file_exists( $cache_file ) ) {
            $this->ajw_debug( 
                "Cache doesn't contain: $url (cache file: $cache_file)"
            );
            return 0;
        }
        
        $fp = @fopen($cache_file, 'r');
        if ( ! $fp ) {
            $this->ajw_error(
                "Failed to open cache file for reading: $cache_file"
            );
            return 0;
        }
        
        if ($filesize = filesize($cache_file) ) {
        	$data = fread( $fp, filesize($cache_file) );
        	$rss = $this->unserialize( $data );
        
        	return $rss;
    	}
    	
    	return 0;
    }

/*=======================================================================*\
    Function:   check_cache
    Purpose:    check a url for membership in the cache
                and whether the object is older then MAX_AGE (ie. STALE)
    Input:      url from wich the rss file was fetched
    Output:     cached object on HIT, false on MISS 
\*=======================================================================*/     
    function check_cache ( $url ) {
        $this->ERROR = "";
        $filename = $this->file_name( $url );
        
        if ( file_exists( $filename ) ) {
            // find how long ago the file was added to the cache
            // and whether that is longer then MAX_AGE
            $mtime = filemtime( $filename );
            $age = time() - $mtime;
            if ( $this->MAX_AGE > $age ) {
                // object exists and is current
                return 'HIT';
            }
            else {
                // object exists but is old
                return 'STALE';
            }
        }
        else {
            // object does not exist
            return 'MISS';
        }
    }

	function cache_age( $cache_key ) {
		$filename = $this->file_name( $url );
		if ( file_exists( $filename ) ) {
			$mtime = filemtime( $filename );
            $age = time() - $mtime;
			return $age;
		}
		else {
			return -1;	
		}
	}
	
/*=======================================================================*\
    Function:   serialize
\*=======================================================================*/     
    function serialize ( $rss ) {
        return serialize( $rss );
    }

/*=======================================================================*\
    Function:   unserialize
\*=======================================================================*/     
    function unserialize ( $data ) {
        return unserialize( $data );
    }
    
/*=======================================================================*\
    Function:   file_name
    Purpose:    map url to location in cache
    Input:      url from wich the rss file was fetched
    Output:     a file name
\*=======================================================================*/     
    function file_name ($url) {
        $filename = md5( $url );
        return join( DIRECTORY_SEPARATOR, array( $this->BASE_CACHE, $filename ) );
    }

/*=======================================================================*\
    Function:   error
    Purpose:    register error
\*=======================================================================*/         
    function ajw_error ($errormsg, $lvl=E_USER_WARNING) {
        // append PHP's error message if track_errors enabled
        if ( isset($php_errormsg) ) { 
            $errormsg .= " ($php_errormsg)";
        }
        $this->ERROR = $errormsg;
        if ( MAGPIE_DEBUG ) {
            trigger_error( $errormsg, $lvl);
        }
        else {
            error_log( $errormsg, 0);
        }
    }
    
    function ajw_debug ($debugmsg, $lvl=E_USER_NOTICE) {
        if ( MAGPIE_DEBUG ) {
            $this->ajw_error("MagpieRSS [debug] $debugmsg", $lvl);
        }
    }

}

/*************************************************

Snoopy - the PHP net client
Author: Monte Ohrt <monte@ispi.net>
Copyright (c): 1999-2000 ispi, all rights reserved
Version: 1.0

 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

You may contact the author of Snoopy by e-mail at:
monte@ispi.net

Or, write to:
Monte Ohrt
CTO, ispi
237 S. 70th suite 220
Lincoln, NE 68510

The latest version of Snoopy can be obtained from:
http://snoopy.sourceforge.com

*************************************************/

class Snoopy
{
	/**** Public variables ****/
	
	/* user definable vars */

	var $host			=	"www.php.net";		// host name we are connecting to
	var $port			=	80;					// port we are connecting to
	var $proxy_host		=	"";					// proxy host to use
	var $proxy_port		=	"";					// proxy port to use
	var $agent			=	"Snoopy v1.0";		// agent we masquerade as
	var	$referer		=	"";					// referer info to pass
	var $cookies		=	array();			// array of cookies to pass
												// $cookies["username"]="joe";
	var	$rawheaders		=	array();			// array of raw headers to send
												// $rawheaders["Content-type"]="text/html";

	var $maxredirs		=	5;					// http redirection depth maximum. 0 = disallow
	var $lastredirectaddr	=	"";				// contains address of last redirected address
	var	$offsiteok		=	true;				// allows redirection off-site
	var $maxframes		=	0;					// frame content depth maximum. 0 = disallow
	var $expandlinks	=	true;				// expand links to fully qualified URLs.
												// this only applies to fetchlinks()
												// or submitlinks()
	var $passcookies	=	true;				// pass set cookies back through redirects
												// NOTE: this currently does not respect
												// dates, domains or paths.
	
	var	$user			=	"";					// user for http authentication
	var	$pass			=	"";					// password for http authentication
	
	// http accept types
	var $accept			=	"image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, */*";
	
	var $results		=	"";					// where the content is put
		
	var $error			=	"";					// error messages sent here
	var	$response_code	=	"";					// response code returned from server
	var	$headers		=	array();			// headers returned from server sent here
	var	$maxlength		=	500000;				// max return data length (body)
	var $read_timeout	=	0;					// timeout on read operations, in seconds
												// supported only since PHP 4 Beta 4
												// set to 0 to disallow timeouts
	var $timed_out		=	false;				// if a read operation timed out
	var	$status			=	0;					// http request status
	
	var	$curl_path		=	"/usr/bin/curl";
												// Snoopy will use cURL for fetching
												// SSL content if a full system path to
												// the cURL binary is supplied here.
												// set to false if you do not have
												// cURL installed. See http://curl.haxx.se
												// for details on installing cURL.
												// Snoopy does *not* use the cURL
												// library functions built into php,
												// as these functions are not stable
												// as of this Snoopy release.
	
	// send Accept-encoding: gzip?
	var $use_gzip		= true;	
	
	/**** Private variables ****/	
	
	var	$_maxlinelen	=	4096;				// max line length (headers)
	
	var $_httpmethod	=	"GET";				// default http request method
	var $_httpversion	=	"HTTP/1.0";			// default http request version
	var $_submit_method	=	"POST";				// default submit method
	var $_submit_type	=	"application/x-www-form-urlencoded";	// default submit type
	var $_mime_boundary	=   "";					// MIME boundary for multipart/form-data submit type
	var $_redirectaddr	=	false;				// will be set if page fetched is a redirect
	var $_redirectdepth	=	0;					// increments on an http redirect
	var $_frameurls		= 	array();			// frame src urls
	var $_framedepth	=	0;					// increments on frame depth
	
	var $_isproxy		=	false;				// set if using a proxy server
	var $_fp_timeout	=	5;					// timeout for socket connection

/*======================================================================*\
	Function:	fetch
	Purpose:	fetch the contents of a web page
				(and possibly other protocols in the
				future like ftp, nntp, gopher, etc.)
	Input:		$URI	the location of the page to fetch
	Output:		$this->results	the output text from the fetch
\*======================================================================*/

	function fetch($URI)
	{
	
		//preg_match("|^([^:]+)://([^:/]+)(:[\d]+)*(.*)|",$URI,$URI_PARTS);
		$URI_PARTS = parse_url($URI);
		if (!empty($URI_PARTS["user"]))
			$this->user = $URI_PARTS["user"];
		if (!empty($URI_PARTS["pass"]))
			$this->pass = $URI_PARTS["pass"];
				
		switch($URI_PARTS["scheme"])
		{
			case "http":
				$this->host = $URI_PARTS["host"];
				if(!empty($URI_PARTS["port"]))
					$this->port = $URI_PARTS["port"];
				if($this->_connect($fp))
				{
					if($this->_isproxy)
					{
						// using proxy, send entire URI
						$this->_httprequest($URI,$fp,$URI,$this->_httpmethod);
					}
					else
					{
						$path = $URI_PARTS["path"].(isset($URI_PARTS["query"]) ? "?".$URI_PARTS["query"] : "");
						// no proxy, send only the path
						$this->_httprequest($path, $fp, $URI, $this->_httpmethod);
					}
					
					$this->_disconnect($fp);

					if($this->_redirectaddr)
					{
						/* url was redirected, check if we've hit the max depth */
						if($this->maxredirs > $this->_redirectdepth)
						{
							// only follow redirect if it's on this site, or offsiteok is true
							if(preg_match("|^http://".preg_quote($this->host)."|i",$this->_redirectaddr) || $this->offsiteok)
							{
								/* follow the redirect */
								$this->_redirectdepth++;
								$this->lastredirectaddr=$this->_redirectaddr;
								$this->fetch($this->_redirectaddr);
							}
						}
					}

					if($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0)
					{
						$frameurls = $this->_frameurls;
						$this->_frameurls = array();
						
						while(list(,$frameurl) = each($frameurls))
						{
							if($this->_framedepth < $this->maxframes)
							{
								$this->fetch($frameurl);
								$this->_framedepth++;
							}
							else
								break;
						}
					}					
				}
				else
				{
					return false;
				}
				return true;					
				break;
			case "https":
				if(!$this->curl_path || (!is_executable($this->curl_path))) {
					$this->error = "Bad curl ($this->curl_path), can't fetch HTTPS \n";
					return false;
				}
				$this->host = $URI_PARTS["host"];
				if(!empty($URI_PARTS["port"]))
					$this->port = $URI_PARTS["port"];
				if($this->_isproxy)
				{
					// using proxy, send entire URI
					$this->_httpsrequest($URI,$URI,$this->_httpmethod);
				}
				else
				{
					$path = $URI_PARTS["path"].($URI_PARTS["query"] ? "?".$URI_PARTS["query"] : "");
					// no proxy, send only the path
					$this->_httpsrequest($path, $URI, $this->_httpmethod);
				}

				if($this->_redirectaddr)
				{
					/* url was redirected, check if we've hit the max depth */
					if($this->maxredirs > $this->_redirectdepth)
					{
						// only follow redirect if it's on this site, or offsiteok is true
						if(preg_match("|^http://".preg_quote($this->host)."|i",$this->_redirectaddr) || $this->offsiteok)
						{
							/* follow the redirect */
							$this->_redirectdepth++;
							$this->lastredirectaddr=$this->_redirectaddr;
							$this->fetch($this->_redirectaddr);
						}
					}
				}

				if($this->_framedepth < $this->maxframes && count($this->_frameurls) > 0)
				{
					$frameurls = $this->_frameurls;
					$this->_frameurls = array();

					while(list(,$frameurl) = each($frameurls))
					{
						if($this->_framedepth < $this->maxframes)
						{
							$this->fetch($frameurl);
							$this->_framedepth++;
						}
						else
							break;
					}
				}					
				return true;					
				break;
			default:
				// not a valid protocol
				$this->error	=	'Invalid protocol "'.$URI_PARTS["scheme"].'"\n';
				return false;
				break;
		}		
		return true;
	}



/*======================================================================*\
	Private functions
\*======================================================================*/
	
	
/*======================================================================*\
	Function:	_striplinks
	Purpose:	strip the hyperlinks from an html document
	Input:		$document	document to strip.
	Output:		$match		an array of the links
\*======================================================================*/

	function _striplinks($document)
	{	
		preg_match_all("'<\s*a\s+.*href\s*=\s*			# find <a href=
						([\"\'])?					# find single or double quote
						(?(1) (.*?)\\1 | ([^\s\>]+))		# if quote found, match up to next matching
													# quote, otherwise match up to next space
						'isx",$document,$links);
						

		// catenate the non-empty matches from the conditional subpattern

		while(list($key,$val) = each($links[2]))
		{
			if(!empty($val))
				$match[] = $val;
		}				
		
		while(list($key,$val) = each($links[3]))
		{
			if(!empty($val))
				$match[] = $val;
		}		
		
		// return the links
		return $match;
	}

/*======================================================================*\
	Function:	_stripform
	Purpose:	strip the form elements from an html document
	Input:		$document	document to strip.
	Output:		$match		an array of the links
\*======================================================================*/

	function _stripform($document)
	{	
		preg_match_all("'<\/?(FORM|INPUT|SELECT|TEXTAREA|(OPTION))[^<>]*>(?(2)(.*(?=<\/?(option|select)[^<>]*>[\r\n]*)|(?=[\r\n]*))|(?=[\r\n]*))'Usi",$document,$elements);
		
		// catenate the matches
		$match = implode("\r\n",$elements[0]);
				
		// return the links
		return $match;
	}

	
	
/*======================================================================*\
	Function:	_striptext
	Purpose:	strip the text from an html document
	Input:		$document	document to strip.
	Output:		$text		the resulting text
\*======================================================================*/

	function _striptext($document)
	{
		
		// I didn't use preg eval (//e) since that is only available in PHP 4.0.
		// so, list your entities one by one here. I included some of the
		// more common ones.
								
		$search = array("'<script[^>]*?".">.*?</script>'si",	// strip out javascript
						"'<[\/\!]*?[^<>]*?".">'si",			// strip out html tags
						"'([\r\n])[\s]+'",					// strip out white space
						"'&(quote|#34);'i",					// replace html entities
						"'&(amp|#38);'i",
						"'&(lt|#60);'i",
						"'&(gt|#62);'i",
						"'&(nbsp|#160);'i",
						"'&(iexcl|#161);'i",
						"'&(cent|#162);'i",
						"'&(pound|#163);'i",
						"'&(copy|#169);'i"
						);				
		$replace = array(	"",
							"",
							"\\1",
							"\"",
							"&",
							"<",
							">",
							" ",
							chr(161),
							chr(162),
							chr(163),
							chr(169));
					
		$text = preg_replace($search,$replace,$document);
								
		return $text;
	}

/*======================================================================*\
	Function:	_expandlinks
	Purpose:	expand each link into a fully qualified URL
	Input:		$links			the links to qualify
				$URI			the full URI to get the base from
	Output:		$expandedLinks	the expanded links
\*======================================================================*/

	function _expandlinks($links,$URI)
	{
		
		preg_match("/^[^\?]+/",$URI,$match);

		$match = preg_replace("|/[^\/\.]+\.[^\/\.]+$|","",$match[0]);
				
		$search = array( 	"|^http://".preg_quote($this->host)."|i",
							"|^(?!http://)(\/)?(?!mailto:)|i",
							"|/\./|",
							"|/[^\/]+/\.\./|"
						);
						
		$replace = array(	"",
							$match."/",
							"/",
							"/"
						);			
				
		$expandedLinks = preg_replace($search,$replace,$links);

		return $expandedLinks;
	}

/*======================================================================*\
	Function:	_httprequest
	Purpose:	go get the http data from the server
	Input:		$url		the url to fetch
				$fp			the current open file pointer
				$URI		the full URI
				$body		body contents to send if any (POST)
	Output:		
\*======================================================================*/
	
	function _httprequest($url,$fp,$URI,$http_method,$content_type="",$body="")
	{
		if($this->passcookies && $this->_redirectaddr)
			$this->setcookies();
			
		$URI_PARTS = parse_url($URI);
		if(empty($url))
			$url = "/";
		$headers = $http_method." ".$url." ".$this->_httpversion."\r\n";		
		if(!empty($this->agent))
			$headers .= "User-Agent: ".$this->agent."\r\n";
		if(!empty($this->host) && !isset($this->rawheaders['Host']))
			$headers .= "Host: ".$this->host."\r\n";
		if(!empty($this->accept))
			$headers .= "Accept: ".$this->accept."\r\n";
		
		if($this->use_gzip) {
			// make sure PHP was built with --with-zlib
			// and we can handle gzipp'ed data
			if ( function_exists(gzinflate) ) {
			   $headers .= "Accept-encoding: gzip\r\n";
			}
			else {
			   trigger_error(
			   	"use_gzip is on, but PHP was built without zlib support.".
				"  Requesting file(s) without gzip encoding.", 
				E_USER_NOTICE);
			}
		}
		
		if(!empty($this->referer))
			$headers .= "Referer: ".$this->referer."\r\n";
		if(!empty($this->cookies))
		{			
			if(!is_array($this->cookies))
				$this->cookies = (array)$this->cookies;
	
			reset($this->cookies);
			if ( count($this->cookies) > 0 ) {
				$cookie_headers .= 'Cookie: ';
				foreach ( $this->cookies as $cookieKey => $cookieVal ) {
				$cookie_headers .= $cookieKey."=".urlencode($cookieVal)."; ";
				}
				$headers .= substr($cookie_headers,0,-2) . "\r\n";
			} 
		}
		if(!empty($this->rawheaders))
		{
			if(!is_array($this->rawheaders))
				$this->rawheaders = (array)$this->rawheaders;
			while(list($headerKey,$headerVal) = each($this->rawheaders))
				$headers .= $headerKey.": ".$headerVal."\r\n";
		}
		if(!empty($content_type)) {
			$headers .= "Content-type: $content_type";
			if ($content_type == "multipart/form-data")
				$headers .= "; boundary=".$this->_mime_boundary;
			$headers .= "\r\n";
		}
		if(!empty($body))	
			$headers .= "Content-length: ".strlen($body)."\r\n";
		if(!empty($this->user) || !empty($this->pass))	
			$headers .= "Authorization: BASIC ".base64_encode($this->user.":".$this->pass)."\r\n";

		$headers .= "\r\n";
		
		// set the read timeout if needed
		if ($this->read_timeout > 0)
			socket_set_timeout($fp, $this->read_timeout);
		$this->timed_out = false;
		
		fwrite($fp,$headers.$body,strlen($headers.$body));
		
		$this->_redirectaddr = false;
		unset($this->headers);
		
		// content was returned gzip encoded?
		$is_gzipped = false;
						
		while($currentHeader = fgets($fp,$this->_maxlinelen))
		{
			if ($this->read_timeout > 0 && $this->_check_timeout($fp))
			{
				$this->status=-100;
				return false;
			}
				
		//	if($currentHeader == "\r\n")
			if(preg_match("/^\r?\n$/", $currentHeader) )
			      break;
						
			// if a header begins with Location: or URI:, set the redirect
			if(preg_match("/^(Location:|URI:)/i",$currentHeader))
			{
				// get URL portion of the redirect
				preg_match("/^(Location:|URI:)\s+(.*)/",chop($currentHeader),$matches);
				// look for :// in the Location header to see if hostname is included
				if(!preg_match("|\:\/\/|",$matches[2]))
				{
					// no host in the path, so prepend
					$this->_redirectaddr = $URI_PARTS["scheme"]."://".$this->host.":".$this->port;
					// eliminate double slash
					if(!preg_match("|^/|",$matches[2]))
							$this->_redirectaddr .= "/".$matches[2];
					else
							$this->_redirectaddr .= $matches[2];
				}
				else
					$this->_redirectaddr = $matches[2];
			}
		
			if(preg_match("|^HTTP/|",$currentHeader))
			{
                if(preg_match("|^HTTP/[^\s]*\s(.*?)\s|",$currentHeader, $status))
				{
					$this->status= $status[1];
                }				
				$this->response_code = $currentHeader;
			}
			
			if (preg_match("/Content-Encoding: gzip/", $currentHeader) ) {
				$is_gzipped = true;
			}
			
			$this->headers[] = $currentHeader;
		}

		# $results = fread($fp, $this->maxlength);
		$results = "";
		while ( $data = fread($fp, $this->maxlength) ) {
		    $results .= $data;
		    if (
		        strlen($results) > $this->maxlength ) {
		        break;
		    }
		}
		
		// gunzip
		if ( $is_gzipped ) {
			// per http://www.php.net/manual/en/function.gzencode.php
			$results = substr($results, 10);
			$results = gzinflate($results);
		}
		
		if ($this->read_timeout > 0 && $this->_check_timeout($fp))
		{
			$this->status=-100;
			return false;
		}
		
		// check if there is a a redirect meta tag
		
		if(preg_match("'<meta[\s]*http-equiv[^>]*?content[\s]*=[\s]*[\"\']?\d+;[\s]+URL[\s]*=[\s]*([^\"\']*?)[\"\']?".">'i",$results,$match))
		{
			$this->_redirectaddr = $this->_expandlinks($match[1],$URI);	
		}

		// have we hit our frame depth and is there frame src to fetch?
		if(($this->_framedepth < $this->maxframes) && preg_match_all("'<frame\s+.*src[\s]*=[\'\"]?([^\'\"\>]+)'i",$results,$match))
		{
			$this->results[] = $results;
			for($x=0; $x<count($match[1]); $x++)
				$this->_frameurls[] = $this->_expandlinks($match[1][$x],$URI_PARTS["scheme"]."://".$this->host);
		}
		// have we already fetched framed content?
		elseif(is_array($this->results))
			$this->results[] = $results;
		// no framed content
		else
			$this->results = $results;
		
		return true;
	}

/*======================================================================*\
	Function:	_httpsrequest
	Purpose:	go get the https data from the server using curl
	Input:		$url		the url to fetch
				$URI		the full URI
				$body		body contents to send if any (POST)
	Output:		
\*======================================================================*/
	
	function _httpsrequest($url,$URI,$http_method,$content_type="",$body="")
	{
		if($this->passcookies && $this->_redirectaddr)
			$this->setcookies();

		$headers = array();		
					
		$URI_PARTS = parse_url($URI);
		if(empty($url))
			$url = "/";
		// GET ... header not needed for curl
		//$headers[] = $http_method." ".$url." ".$this->_httpversion;		
		if(!empty($this->agent))
			$headers[] = "User-Agent: ".$this->agent;
		if(!empty($this->host))
			$headers[] = "Host: ".$this->host;
		if(!empty($this->accept))
			$headers[] = "Accept: ".$this->accept;
		if(!empty($this->referer))
			$headers[] = "Referer: ".$this->referer;
		if(!empty($this->cookies))
		{			
			if(!is_array($this->cookies))
				$this->cookies = (array)$this->cookies;
	
			reset($this->cookies);
			if ( count($this->cookies) > 0 ) {
				$cookie_str = 'Cookie: ';
				foreach ( $this->cookies as $cookieKey => $cookieVal ) {
				$cookie_str .= $cookieKey."=".urlencode($cookieVal)."; ";
				}
				$headers[] = substr($cookie_str,0,-2);
			}
		}
		if(!empty($this->rawheaders))
		{
			if(!is_array($this->rawheaders))
				$this->rawheaders = (array)$this->rawheaders;
			while(list($headerKey,$headerVal) = each($this->rawheaders))
				$headers[] = $headerKey.": ".$headerVal;
		}
		if(!empty($content_type)) {
			if ($content_type == "multipart/form-data")
				$headers[] = "Content-type: $content_type; boundary=".$this->_mime_boundary;
			else
				$headers[] = "Content-type: $content_type";
		}
		if(!empty($body))	
			$headers[] = "Content-length: ".strlen($body);
		if(!empty($this->user) || !empty($this->pass))	
			$headers[] = "Authorization: BASIC ".base64_encode($this->user.":".$this->pass);
			
		for($curr_header = 0; $curr_header < count($headers); $curr_header++)
			$cmdline_params .= " -H \"".$headers[$curr_header]."\"";
		
		if(!empty($body))
			$cmdline_params .= " -d \"$body\"";
		
		if($this->read_timeout > 0)
			$cmdline_params .= " -m ".$this->read_timeout;
		
		$headerfile = uniqid(time());
		
		# accept self-signed certs
		$cmdline_params .= " -k";
		exec($this->curl_path." -D \"/tmp/$headerfile\"".$cmdline_params." ".$URI,$results,$return);
		
		if($return)
		{
			$this->error = "Error: cURL could not retrieve the document, error $return.";
			return false;
		}
			
			
		$results = implode("\r\n",$results);
		
		$result_headers = file("/tmp/$headerfile");
						
		$this->_redirectaddr = false;
		unset($this->headers);
						
		for($currentHeader = 0; $currentHeader < count($result_headers); $currentHeader++)
		{
			
			// if a header begins with Location: or URI:, set the redirect
			if(preg_match("/^(Location: |URI: )/i",$result_headers[$currentHeader]))
			{
				// get URL portion of the redirect
				preg_match("/^(Location: |URI:)(.*)/",chop($result_headers[$currentHeader]),$matches);
				// look for :// in the Location header to see if hostname is included
				if(!preg_match("|\:\/\/|",$matches[2]))
				{
					// no host in the path, so prepend
					$this->_redirectaddr = $URI_PARTS["scheme"]."://".$this->host.":".$this->port;
					// eliminate double slash
					if(!preg_match("|^/|",$matches[2]))
							$this->_redirectaddr .= "/".$matches[2];
					else
							$this->_redirectaddr .= $matches[2];
				}
				else
					$this->_redirectaddr = $matches[2];
			}
		
			if(preg_match("|^HTTP/|",$result_headers[$currentHeader]))
			{
			    $this->response_code = $result_headers[$currentHeader];
			    if(preg_match("|^HTTP/[^\s]*\s(.*?)\s|",$this->response_code, $match))
			    {
				$this->status= $match[1];
                	    }
			}
			$this->headers[] = $result_headers[$currentHeader];
		}

		// check if there is a a redirect meta tag
		
		if(preg_match("'<meta[\s]*http-equiv[^>]*?content[\s]*=[\s]*[\"\']?\d+;[\s]+URL[\s]*=[\s]*([^\"\']*?)[\"\']?".">'i",$results,$match))
		{
			$this->_redirectaddr = $this->_expandlinks($match[1],$URI);	
		}

		// have we hit our frame depth and is there frame src to fetch?
		if(($this->_framedepth < $this->maxframes) && preg_match_all("'<frame\s+.*src[\s]*=[\'\"]?([^\'\"\>]+)'i",$results,$match))
		{
			$this->results[] = $results;
			for($x=0; $x<count($match[1]); $x++)
				$this->_frameurls[] = $this->_expandlinks($match[1][$x],$URI_PARTS["scheme"]."://".$this->host);
		}
		// have we already fetched framed content?
		elseif(is_array($this->results))
			$this->results[] = $results;
		// no framed content
		else
			$this->results = $results;

		unlink("/tmp/$headerfile");
		
		return true;
	}

/*======================================================================*\
	Function:	setcookies()
	Purpose:	set cookies for a redirection
\*======================================================================*/
	
	function setcookies()
	{
		for($x=0; $x<count($this->headers); $x++)
		{
		if(preg_match("/^set-cookie:[\s]+([^=]+)=([^;]+)/i", $this->headers[$x],$match))
			$this->cookies[$match[1]] = $match[2];
		}
	}

	
/*======================================================================*\
	Function:	_check_timeout
	Purpose:	checks whether timeout has occurred
	Input:		$fp	file pointer
\*======================================================================*/

	function _check_timeout($fp)
	{
		if ($this->read_timeout > 0) {
			$fp_status = socket_get_status($fp);
			if ($fp_status["timed_out"]) {
				$this->timed_out = true;
				return true;
			}
		}
		return false;
	}

/*======================================================================*\
	Function:	_connect
	Purpose:	make a socket connection
	Input:		$fp	file pointer
\*======================================================================*/
	
	function _connect(&$fp)
	{
		if(!empty($this->proxy_host) && !empty($this->proxy_port))
			{
				$this->_isproxy = true;
				$host = $this->proxy_host;
				$port = $this->proxy_port;
			}
		else
		{
			$host = $this->host;
			$port = $this->port;
		}
	
		$this->status = 0;
		
		if($fp = fsockopen(
					$host,
					$port,
					$errno,
					$errstr,
					$this->_fp_timeout
					))
		{
			// socket connection succeeded

			return true;
		}
		else
		{
			// socket connection failed
			$this->status = $errno;
			switch($errno)
			{
				case -3:
					$this->error="socket creation failed (-3)";
				case -4:
					$this->error="dns lookup failure (-4)";
				case -5:
					$this->error="connection refused or timed out (-5)";
				default:
					$this->error="connection failed (".$errno.")";
			}
			return false;
		}
	}
/*======================================================================*\
	Function:	_disconnect
	Purpose:	disconnect a socket connection
	Input:		$fp	file pointer
\*======================================================================*/
	
	function _disconnect($fp)
	{
		return(fclose($fp));
	}

	
/*======================================================================*\
	Function:	_prepare_post_body
	Purpose:	Prepare post body according to encoding type
	Input:		$formvars  - form variables
				$formfiles - form upload files
	Output:		post body
\*======================================================================*/
	
	function _prepare_post_body($formvars, $formfiles)
	{
		settype($formvars, "array");
		settype($formfiles, "array");

		if (count($formvars) == 0 && count($formfiles) == 0)
			return;
		
		switch ($this->_submit_type) {
			case "application/x-www-form-urlencoded":
				reset($formvars);
				while(list($key,$val) = each($formvars)) {
					if (is_array($val) || is_object($val)) {
						while (list($cur_key, $cur_val) = each($val)) {
							$postdata .= urlencode($key)."[]=".urlencode($cur_val)."&";
						}
					} else
						$postdata .= urlencode($key)."=".urlencode($val)."&";
				}
				break;

			case "multipart/form-data":
				$this->_mime_boundary = "Snoopy".md5(uniqid(microtime()));
				
				reset($formvars);
				while(list($key,$val) = each($formvars)) {
					if (is_array($val) || is_object($val)) {
						while (list($cur_key, $cur_val) = each($val)) {
							$postdata .= "--".$this->_mime_boundary."\r\n";
							$postdata .= "Content-Disposition: form-data; name=\"$key\[\]\"\r\n\r\n";
							$postdata .= "$cur_val\r\n";
						}
					} else {
						$postdata .= "--".$this->_mime_boundary."\r\n";
						$postdata .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
						$postdata .= "$val\r\n";
					}
				}
				
				reset($formfiles);
				while (list($field_name, $file_names) = each($formfiles)) {
					settype($file_names, "array");
					while (list(, $file_name) = each($file_names)) {
						if (!is_readable($file_name)) continue;

						$fp = fopen($file_name, "r");
						$file_content = fread($fp, filesize($file_name));
						fclose($fp);
						$base_name = basename($file_name);

						$postdata .= "--".$this->_mime_boundary."\r\n";
						$postdata .= "Content-Disposition: form-data; name=\"$field_name\"; filename=\"$base_name\"\r\n\r\n";
						$postdata .= "$file_content\r\n";
					}
				}
				$postdata .= "--".$this->_mime_boundary."--\r\n";
				break;
		}

		return $postdata;
	}
}
?>