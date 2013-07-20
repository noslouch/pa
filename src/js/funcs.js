"use strict";

function isoLoader(id) {
    $(id).isotope({
        itemSelector: '.thumb',
        layoutMode : 'masonry',
        masonry : {
            gutterWidth: 7,
            columnWidth: 164
        }
    })
}
