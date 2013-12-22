$(document).ready(function() {
  $('body').addClass('structure-ui');
  // Elements
  var actionLink,
      $structureUI = $('#structure-ui'),
      $treeSwitcher = $('#tree-switcher'),
      $treeControls = $('#tree-controls');

  // TREE SWITCHER
  $treeSwitcher.find('a')
  .each(function() {
    var $rel = $(this).attr('rel');
    
    if ( !$("#"+$rel).length ) {
      $(this).hide();
    }
  })
  .bind('click', function(event) {
    event.preventDefault();
    
    var $rel = $(this).attr('rel');
    
    var $a = $(this),
        $li = $a.parent();
    
    if ( !$li.hasClass('here') ) {
      $("#"+$rel).removeClass('hide-alt');
      $li.addClass('here');
      $li.siblings().removeClass('here')
        .find('a').each(function() {
          var $rel2 = $(this).attr('rel');
          
          $("#"+$rel2).addClass('hide-alt');
        });
    }
  });

  // TREE CONTROLS
  var treeControls = {
    expand: function(event) {
      event.preventDefault();
      $(document).trigger('collapsibles.structure', {type: 'expand'});
    },
    collapse: function(event) {
      event.preventDefault();
      $(document).trigger('collapsibles.structure', {type: 'collapse'});
    },
    add: function(event) {
      event.preventDefault();
      $structureUI.data('structureParentId', '0');
      $('#add-dialog').dialog('open');
    }
  };
  
  $('.tree-add-solo').click(function(){
    event.preventDefault();
    $structureUI.data('structureParentId', '0');
    $('#add-dialog').dialog('open');
  });

  $.each(treeControls, function(name, fn) {
    $treeControls.find('.tree-' + name + ' a').live('click', fn);
  });

  // DIALOGS / ROW CONTROLS
  // settings
  var dialogs = {
    defaults: {
      autoOpen: false,
      modal: true,
      minHeight: 60
    },
    add: {
      dialogClass: 'structure-page-selector',
      open: function(event, ui) {
        var $items = $(this).find('li a'),
            parentId = $structureUI.data('structureParentId');

        $.each(structure_settings.dialogs.add.urls, function(i, val) {
          $items.eq(i).attr({href: val.replace(/&amp;/g,'&') + '&parent_id=' + parentId});
        });
      }
    },
    del: {
      dialogClass: 'structure-delete'
    }
  },
  buttons = {
    del: function(event) {
      location.href = $structureUI.data('structureHref');
      $(this).dialog('close');
    },
    cancel: function() {
      $(this).dialog('close');
    }
  };

  $.each(structure_settings.dialogs, function(key, dialog) {
    var dial = dialogs[key];
    // merge default settings with dialog-specific settings
    var opts = {
      title: dialog.title,
      buttons: {},
      open: function(event, ui) {
        if (dial.open) {
          dial.open.call(this, event, ui);
        }
      }
    };

    // text of buttons is from structure_settings.dialogs
    // but we need to attach functions to them.
    $.each(dialog.buttons, function(id, text) {
      opts.buttons[text] = buttons[id] || $.noop;
    });

    // create dialogs
    opts = $.extend({}, dialogs.defaults, opts);
    $('<div></div>', {
      id: key + '-dialog',
      html: dialog.body
    }).appendTo('body')
    .dialog(opts)
      .parent().attr('id', dial.dialogClass)
      .find('button').attr('class', function() {
        return $(this).text().replace(/^\s*(\w+).*$/,'$1').toLowerCase();
      });

    // delegate click handlers to row buttons
    if ( key != 'add' || structure_settings.show_picker == 'y' ) {

      $structureUI.delegate('span.control-' + key + ' a', 'click', function(event) {
        event.preventDefault();
        var link = $(this);
        $structureUI.data('structureParentId', link.data('parent_id') || link.attr('data-parent_id') || '0');
        $structureUI.data( 'structureHref', link.attr('href') );
        $('#' + key + '-dialog').dialog('open');
      });
    }
  });

  $structureUI.delegate('div.item-wrapper', 'mouseenter mouseleave', function(event) {
    $(this).toggleClass('hover', event.type == 'mouseenter');
  });

  $(document).click(function(event) {

    if ( $(event.target).hasClass('ui-widget-overlay') ) {
      $('div.ui-dialog:visible').find('button.cancel').triggerHandler('click');
    }
  });

});
