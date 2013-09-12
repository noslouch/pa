(function($) {
  var collapsePostTimer,
      $structureUI = $('#structure-ui'),
      postCollapsed = function() {
        var collapsed = $structureUI.find('ul.state-collapsed').parent().map(function() {
          return this.id.replace(/^page-/,'');
        }).get();

        $.post(structure_settings.baseAjaxURL + 'ajax_collapse', {
          XID: structure_settings.xid,
          collapsed: collapsed
        });
      };

  // Types of collapsible events
  // these functions are called once per <li>
  var collapseTypes = {
    click: function(ul) {
      ul.toggleClass('state-collapsed');
    },
    expand: function(ul) {
      ul.removeClass('state-collapsed');
    },
    collapse: function(ul) {
      ul.addClass('state-collapsed');
    },

    // toggle is the default. happens on document ready
    toggle: function(ul, tab) {
      tab.toggleClass('ec-none', !ul.length);
    }
  };

  // Set up Collapsible elements
  $(document).bind('collapsibles.structure', function(event, opts) {
    if (!$structureUI.length) {
      $structureUI = $('#structure-ui');
    }

    var type = opts && opts.type || 'toggle',
        $listItems = type == 'click' ? $(event.target) : $structureUI.find('li');

    $listItems.each(function() {
      var li = this, $li = $(li),
          $childList = $li.children('ul'),
          $toggleTab = $li.children('div.item-wrapper').find('span.page-expand-collapse');

      // per li, per type functions
      collapseTypes[type]($childList, $toggleTab);

      // do this no matter what type of event triggered it
      if ($childList.length) {
        $toggleTab.find('a').toggleClass( 'collapsed', $childList.hasClass('state-collapsed') );
      }
    });

    if (type != 'toggle') {
      clearTimeout(collapsePostTimer);
      collapsePostTimer = setTimeout(postCollapsed, 400);
    }

  });

  // toggle collapsed state on click
  $('span.page-expand-collapse a').live('click.structure', function(event) {
    event.preventDefault();
    if ( !$(this).parent().hasClass('ec-none') ) {
      var opts = {type: event.type};
      $(this).closest('li').trigger('collapsibles', [opts]);
    }
  });

  // Toggle Collapsed State of Nested Lists on document ready
  $(document).ready(function() {
    $(document).trigger('collapsibles.structure');
  });

})(jQuery);