/* utils/isoLoader.js
 * sets isotope configs */
'use strict';

define([
    'jquery',
    'isotope'
], function( $ ) {

    function isoLoader(id) {
        var $id = $(id),
            $img = $('.image.showcase img'),
            showcase = document.getElementById('showcaseContainer'),
            rtl = $(showcase).hasClass('rtl'),
            fixed = $(showcase).hasClass('fixed')

        $id.imagesLoaded( function(){ 
            $id.isotope({
                transformsEnabled: !rtl,
                itemSelector: '.thumb',
                layoutMode : fixed ? 'masonry' : 'fitRows',
                masonry : {
                    gutterWidth: 7,
                    columnWidth: rtl ? 164*1.5 : 164
                },
                onLayout : function() { 
                    $(this).css('overflow', 'visible')
               }
            })

            $img.addClass('loaded')
        })

    }

    return isoLoader
})
