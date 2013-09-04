;(function(global, $){
    //es5 strict mode
    "use strict";

    var Updater = global.Updater = global.Updater || {};

    // ----------------------------------------------------------------------

    Updater.AddonListInit = function(){
        if (Updater.Wrap.find('.addonlist').length === 0) return;

        Updater.AddonListWrap = Updater.Wrap.find('.addonlist');
        Updater.AddonList = Updater.AddonListWrap.find('.addons');

        Updater.AddonList.isotope({
            itemSelector : '.addon',
            layoutMode : 'masonry',
            filter: '.filter-section_all.filter-thirdparty',
            sortBy: 'label',
            masonry: {
                columnWidth: 240
            },
            containerStyle: {position:'relative', overflow:'visible'},
            getSortData: {
                label:function(elem) {
                    return elem.find('.label').text();
                },
                install_status: function(elem) {
                    return elem.find('.status').text();
                }
            }
        });

        Updater.AddonListWrap.find('.filter').click(Updater.AddonListApplyFilter);
        Updater.AddonListWrap.find('.sort').click(Updater.AddonListApplySort);
        Updater.AddonListWrap.delegate('.addonoptions', 'click', Updater.AddonListToggleActions);
        Updater.AddonList.isotope('reLayout');
    };

    // ----------------------------------------------------------------------

    Updater.AddonListApplyFilter = function(e){
        e.preventDefault();
        var target = $(e.target);
        if (e.target.tagName == 'SPAN') target = target.parent();

        if (target.parent().hasClass('sectionfilter')) {
            target.parent().find('.filter').removeClass('active');
            target.addClass('active');
        } else {
            target.toggleClass('active');
        }

        var filters = [''];
        Updater.AddonListWrap.find('.filter.active').each(function(i, elem){
            filters.push(elem.getAttribute('data-filter'));
        });

        Updater.AddonList.isotope({filter: filters.join('.')});
    };

    // ----------------------------------------------------------------------

    Updater.AddonListApplySort = function(e){
        e.preventDefault();
        var target = $(e.target);
        if (e.target.tagName == 'SPAN') target = target.parent();

        target.toggleClass('active');

        var sorts = [];
        Updater.AddonListWrap.find('.sort.active').each(function(i, elem){
            sorts.push(elem.getAttribute('data-sort'));
        });

        Updater.AddonList.isotope({sortBy: sorts.join(', ')});
    };

    // ----------------------------------------------------------------------

    Updater.AddonListToggleActions = function(e){
        var target = $(e.target).closest('.addon');
        target.find('.actions').toggle();

        Updater.AddonList.isotope('reLayout');
    };

    // ----------------------------------------------------------------------

}(window, jQuery));

// ----------------------------------------------------------------------
