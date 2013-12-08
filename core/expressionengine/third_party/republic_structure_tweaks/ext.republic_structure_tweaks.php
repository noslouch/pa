<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*

                                    __/---\__
                     ,___     ___  /___o--\  \
                      \_ o---/ _/          )--)
                        \-----/           ______
                                          |    |
                                          |    |
                    ---_    ---_    ---_  |    |
                    |   \__ |   \__ |   \__    |
                    |      \__     \__     \__ o
                    |         `       `      \__
                    |                          |
                    |                          |
                    |__________________________|

                    | ) |_´ | ) | | |_) |  | / '
                    | \ |_, |´  \_/ |_) |_,| \_,
                            F A C T O R Y

Republic Structure Tweaks made by Republic Factory AB <http://www.republic.se> and is
licensed under a Creative Commons Attribution-NoDerivs 3.0 Unported License
<http://creativecommons.org/licenses/by-nd/3.0/>.

You can use it for free, both in personal and commercial projects as long as
this attribution in left intact. But, by downloading this add-on you also take
full responsibility for anything that happens while using it. The add-on is
made with love and passion, and is used by us on daily basis, but we cannot
guarantee that it works equally well for you.

See Republic Labs site <http://republiclabs.com> for more information.

*/

class Republic_structure_tweaks_ext
{
  public $settings       = array();
  public $name           = 'Republic Structure Tweaks';
  public $version        = '1.1.2';
  public $description    = 'Customize the Structure\'s channel picker';
  public $settings_exist = 'y';
  public $docs_url       = '';

  var $channels           = array();
  var $structure_channels = array();

  public function __construct($settings = '')
  {
    $this->EE =& get_instance();
    $this->settings = $settings;
    $this->classname = get_class($this);

    if (file_exists(PATH_THIRD.'structure/sql.structure.php'))
    {
      include_once PATH_THIRD.'structure/sql.structure.php';
      $this->structure_sql = new Sql_structure();
    }

    if (file_exists(PATH_THIRD.'structure/tab.structure.php'))
    {
      include_once PATH_THIRD.'structure/tab.structure.php';
      $this->structure_tab = new Structure_tab();
    }

    if (file_exists(PATH_THIRD.'structure/mod.structure.php'))
    {
      include_once PATH_THIRD.'structure/mod.structure.php';
      $this->structure_mod = new Structure();
    }

    $this->site_id = $this->EE->config->item('site_id');
    $this->EE->lang->loadfile('republic_structure_tweaks');
  }

  function activate_extension()
  {
    $hooks = array(
      'cp_js_end'
    );

    foreach($hooks as $hook)
    {
      $this->EE->db->insert('extensions', array(
        'class'    => $this->classname,
        'method'   => $hook,
        'hook'     => $hook,
        'settings' => '',
        'priority' => 10,
        'version'  => $this->version,
        'enabled'  => 'y'
      ));
    }
  }

  public function update_extension($current = '')
  {
    if ($current == '' OR $current == $this->version)
    {
      return FALSE;
    }

    $this->EE->db->update('extensions', array('version' => $this->version), array('class' => $this->classname));
  }

  public function disable_extension()
  {
    $this->EE->db->delete('extensions', array('class' => $this->classname));
  }

  public function settings()
  {
    $settings = array();

    return $settings;
  }

  function get_structure_javascript($settings, $channels)
  {
    $javascript = "";

    $channel_child_hide = (isset($settings['channel_child_hide']) && isset($settings['channel_child_hide'][$this->site_id])) ? json_encode($settings['channel_child_hide'][$this->site_id]) : 0;
    $entry_data         = (isset($settings['entry_data']) && isset($settings['entry_data'][$this->site_id])) ? json_encode($settings['entry_data'][$this->site_id]) : 0;
    $channel_count = sizeof($this->structure_channels);
    $channels = json_encode($this->channels);
    $show_channel_title = (isset($settings['show_channel_title']) && isset($settings['show_channel_title'][$this->site_id])) ? $settings['show_channel_title'][$this->site_id] : 'n';
    $show_status_closed = (isset($settings['show_status_closed']) && isset($settings['show_status_closed'][$this->site_id])) ? $settings['show_status_closed'][$this->site_id] : 'n';
    $text_closed = $this->EE->lang->line('republic_structure_tweaks_closed');

    // Init data for javascript
    $javascript .= <<<EOJS
      var channelChildHide  = ${channel_child_hide};
      var entryData         = ${entry_data};
      var channelCount      = parseInt(${channel_count}, 10);
      var structureChannels = ${channels};
      var showChannelTitle  = "${show_channel_title}";
      var showStatusClosed  = "${show_status_closed}";
      var textClosed        = "${text_closed}";

      if (showStatusClosed === 'y') {
        $(".status-closed").find('.page-title').append('<span style="font-weight: normal; padding-left: 5px; color: #ab7780">' + textClosed + '</a>');
      }
      // Hide 'Add page'-button where there are no child channels attached
      $('#page-ui .item-wrapper').live('mouseover', function (e) {

        var currentPage    = $(this).closest('li');
        var currentWrapper = $(this);

        // Show channel title
        if (showChannelTitle === 'y' && currentWrapper.find('.channel-name').length === 0) {
          jQuery.each(structureChannels, function (key, channel) {
            if (currentPage.hasClass('channel-' + channel.channel_id)) {
              currentWrapper.find('.page-controls span:first').before('<div class="channel-name" style="color: #CCC; display: inline; height: 22px; line-height: 25px; padding-right: 5px; float: left">' + channel.channel_title + '</div>');
            }
          });
        }


        // Check for mouseover on current page's channel
        if (channelChildHide !== 0) {
          jQuery.each(channelChildHide, function (parent_channel_id, childChannelId) {
            var channelCounter = (String(childChannelId)).split(',').length;

            if ((channelCount - channelCounter === 0) && currentPage.hasClass('channel-' + parent_channel_id)) {
              currentPage.find('.page-controls:first span:first').hide();
            }
          });
        }



        // Check for mouseover events for current page
        if (entryData !== 0) {

          jQuery.each(entryData, function (entryId, channel_id) {

            var channelCounter = (String(channel_id.channel_child_hide)).split(',').length;

            // Append to current
            if (currentPage.attr('id') === 'page-' + entryId) {
              currentPage.find('.page-controls span:first').show();

              if (channelCount - channelCounter === 0) {
                currentPage.find('.page-controls:first span:first').hide();
              }
            }

            // Check if any of the parents has specific rule that should override channel rule
            var parentPage = currentPage.closest('#page-' + entryId);

            if (parentPage.length > 0 && channel_id.append_rule_on_children) {
              currentPage.find('.page-controls span:first').show();

              if (channelCount - channelCounter === 0) {
                currentPage.find('.page-controls:first span:first').hide();
              }

            }
          });
        }
      });


      // Show hide pages in the Add-page-overlay depending on channel/page settings
      $('#structure-ui .page-controls span').live('click', function (e) {

        if ($(this).closest('.page-controls').find('span:first').index() !== $(this).index()) {
          return;
        }

        var currentPage      = $(this).closest('li');

        $('#structure-page-selector #add-dialog li').show();

        var channelIsset = false;
        var channel_titles = "";

        // Loop through general channel settings
        jQuery.each(channelChildHide, function (parent_channel_id, childChannelId) {

          if (currentPage.hasClass('channel-' + parent_channel_id)) {
            var childChannelsIds = (String(childChannelId)).split(',');

            for (var i = 0; i < childChannelsIds.length; i++) {
              $('#structure-page-selector #add-dialog li a').each(function () {
                if (structureChannels[childChannelsIds[i]].channel_title.replace('&', '&amp;') === $(this).html()) {
                  $(this).parent('li').hide();
                }
              });
            }
          }
        });


        // Loop through specific page settings
        jQuery.each(entryData, function (entryId, childChannelId) {
          var childChannelsIds = (String(childChannelId.channel_child_hide)).split(',');
          if (currentPage.attr('id') === 'page-' + entryId) {
            $('#structure-page-selector #add-dialog li').show();

            for (var i = 0; i < childChannelsIds.length; i++) {
              $('#structure-page-selector #add-dialog li a').each(function () {
                if (structureChannels[childChannelsIds[i]] && (structureChannels[childChannelsIds[i]].channel_title === $(this).html()) || structureChannels[childChannelsIds[i]].channel_title.replace('&', '&amp;') === $(this).html()) {
                  $(this).parent('li').hide();
                }
              });
            }
          }

          // Check if any of the parents has specific rule that should override channel rule
          var parentPage = currentPage.closest('#page-' + entryId);
          if (parentPage.length > 0 && childChannelId.append_rule_on_children) {
            $('#structure-page-selector #add-dialog li').show();
            currentPage.find('.page-controls span:first').show();

            for (var i = 0; i < childChannelsIds.length; i++) {
              $('#structure-page-selector #add-dialog li a').each(function () {
                if (structureChannels[childChannelsIds[i]] && structureChannels[childChannelsIds[i]].channel_title === $(this).html()) {
                  $(this).parent('li').hide();
                }
              });
            }
          }
        });
      });

EOJS;

    return $javascript;
  }

  public function cp_js_end()
  {

    $this->EE->load->helper('array');

    //get $_GET from the referring page
    parse_str(parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $get);
    $javascript = $this->EE->extensions->last_call;

    if (element('module', $get) !== 'structure')
    {
      return $javascript;
    }

    $this->fetch_settings();
    $this->fetch_channels();

    $settings = $this->settings;
    $channels = array();
    foreach($this->channels AS $channel) {
      $channels[$channel['channel_id']] = $channel['channel_title'];
    }

    $javascript .= <<<EOJS

    // Republic Structure Tweaks - START
    $(document).ready(function () {
EOJS;


    if ( version_compare(APP_VER, '2.6', '<') && $this->structure_sql->user_access('perm_admin_structure', $this->structure_sql->get_settings()))
    {
      $button_html  = $this->EE->lang->line('republic_structure_tweaks_structure_tweaks');
      $button_title = $this->EE->lang->line('republic_structure_tweaks_module_name');

      $javascript .= <<<EOJS

        var newNavButton = $(".rightNav .button:last").clone();
        newNavButton.find('a').attr('title', "{$button_title}").attr('href', EE.BASE + '&C=addons_extensions&M=extension_settings&file=republic_structure_tweaks').html("{$button_html}");
        var newNavButton = $(".rightNav .button:first").before(newNavButton);
EOJS;
    }


    if ($this->_get_structure_module_version() < '3.1' || empty($channels))
    {
      $javascript .= <<<EOJS

      });
      // Republic Structure Tweaks - END

EOJS;
      return $javascript;
    }

    $javascript .= $this->get_structure_javascript($settings, $channels);

    $javascript .= <<<EOJS

    });
    // Republic Structure Tweaks - END

EOJS;

    return $javascript;
  }

  /**
   * settings_form
   *
   * @access  public
   * @return  void
   */
  public function settings_form()
  {

    $this->fetch_channels();
    $this->fetch_settings();
    $this->fetch_entries();

    if ( ! $this->_get_structure_module_version())
    {
      $this->EE->load->view('no_structure', array(), TRUE);
    }

    $this->EE->load->library('javascript');
    $this->EE->load->library('table');
    $this->EE->load->helper('form');

    if (function_exists('ee')) {
      ee()->view->cp_page_title = $this->EE->lang->line('republic_structure_tweaks_module_name');
    } else {
      $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('republic_structure_tweaks_module_name'));
    }

    $vars = array(
      'site_id'                 => $this->EE->config->item('site_id'),
      'action_url'              => 'C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=republic_structure_tweaks',
      'settings'                => $this->settings,
      'channels'                => $this->channels,
      'structure_channels'      => $this->structure_channels,
      'entries'                 => $this->entries,
      'structure_less_than_3_1' => ($this->_get_structure_module_version() < '3.1') ? TRUE : FALSE,
    );

    return $this->EE->load->view('index', $vars, TRUE);
  }

  private function _get_structure_module_version()
  {
    $this->EE->db->select('module_version');
    $this->EE->db->where('module_name', 'Structure');
    $this->EE->db->limit(1);
    $query = $this->EE->db->get('modules');

    if ($query->num_rows() === 0)
    {
      return FALSE;
    }

    $structure_module_row = $query->row_array();
    return $structure_module_row['module_version'];

  }


  /**
   * save_settings
   *
   * @access  public
   * @return  void
   */
  public function save_settings()
  {
    $this->fetch_channels();
    $this->fetch_settings();

    $this->settings['channel_child_hide'][$this->site_id] = array();
    foreach ($this->channels as $row)
    {
      if ( ! empty($_POST['channel_child_hide'][$this->site_id][$row['channel_id']]))
      {
        $this->settings['channel_child_hide'][$this->site_id][$row['channel_id']] = xss_clean($_POST['channel_child_hide'][$this->site_id][$row['channel_id']]);
      }

      if ( ! empty($_POST['channel_hide_from_child_picker'][$this->site_id][$row['channel_id']]))
      {
        $this->settings['channel_hide_from_child_picker'][$this->site_id][$row['channel_id']] = xss_clean($_POST['channel_hide_from_child_picker'][$this->site_id][$row['channel_id']]);
        $this->settings['channel_child_hide'][$this->site_id][$row['channel_id']] = array();
      }
    }

    if (isset($this->settings['entry_data']))
    {
      unset($this->settings['entry_data'][$this->site_id]);
    }

    if ( ! empty($_POST['entry_settings'][$this->site_id]))
    {
      $entry_data = $_POST['entry_settings'][$this->site_id];

      foreach ($entry_data['entry'] AS $entry_id)
      {
        if ($entry_id !== '0')
        {
          $this->settings['entry_data'][$this->site_id][$entry_id]['channel_child_hide'] = isset($entry_data['channel_child_hide'][$entry_id]) ? $entry_data['channel_child_hide'][$entry_id] : array() ;
          $this->settings['entry_data'][$this->site_id][$entry_id]['append_rule_on_children'] = ( ! empty($entry_data['append_rule_on_children'][$entry_id]) ) ? TRUE : FALSE;
        }
      }
    }

    if (isset($this->settings['tweaks']))
    {
      unset($this->settings['tweaks'][$this->site_id]);
    }

    if ( ! empty($_POST['tweaks'][$this->site_id]))
    {
      $this->settings['tweaks'][$this->site_id]['toggle'] = $_POST['tweaks'][$this->site_id]['toggle'];
    }

    $this->settings['show_channel_title'][$this->site_id] = $this->EE->input->post('show_channel_title');
    $this->settings['show_status_closed'][$this->site_id] = $this->EE->input->post('show_status_closed');

    $this->EE->db->where('class', 'Republic_structure_tweaks_ext');
    $this->EE->db->update('extensions', array('settings' => serialize($this->settings)));

    $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('republic_structure_tweaks_updated'));
    $this->EE->functions->redirect(BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=republic_structure_tweaks');
  }

  /**
   * fetch_settings
   *
   * @access  public
   * @return  void
   */
  public function fetch_settings()
  {
    if ( ! empty($this->settings))
    {
      return;
    }

    $this->EE->db->select('settings');
    $this->EE->db->where('class', 'Republic_structure_tweaks_ext');
    $this->EE->db->limit(1);

    $query = $this->EE->db->get('extensions');

    $this->settings = ($query->row('settings')) ? $this->unserialize($query->row('settings')) : array();
  }

  function fetch_entries()
  {
    if (isset($this->entries))
    {
      return;
    }
    $this->entries = $this->structure_tab->get_parent_fields();
  }
  /**
   * fetch_channels
   *
   * @access  public
   * @return  void
   */
  public function fetch_channels()
  {
    if (!empty($this->channels))
    {
      return;
    }

    $this->EE->load->model('channel_model');

    $query = $this->structure_sql->get_structure_channels('page', '', 'alpha');
    $channels = ( ! empty($query)) ? $query : array();
    unset($query);

    foreach ($channels AS $key => $channel)
    {
      if (empty($channel['channel_id']))
      {
        $channels[$key]['channel_id'] = $key;
      }
    }


    foreach ($channels AS $channel)
    {
      if ($channel['show_in_page_selector'] === 'y')
      {
        $this->structure_channels[$channel['channel_id']] = $channel;
      }
      $this->channels[$channel['channel_id']] = $channel;
    }
  }

  /**
   * unserialize
   *
   * @access  public
   * @param mixed $data
   * @param mixed $base64_decode = FALSE
   * @return  void
   */
  public function unserialize($data, $base64_decode = FALSE)
  {
    if ($base64_decode)
    {
      $data = base64_decode($data);
    }

    $data = @unserialize($data);

    return (is_array($data)) ? $data : array();
  }

}
