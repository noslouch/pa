"use strict";

function isoLoader(id) {
    var $id = $(id)
    $id.imagesLoaded( function(){ 
        $id.isotope({
            itemSelector: '.thumb',
            layoutMode : 'masonry',
            masonry : {
                gutterWidth: 7,
                columnWidth: 164
            }
        })
    })
}

