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
            },
            onLayout : function() { 
                $(this).css('overflow', 'visible')
           }
        })
    })
}

function makeTime(date) {
    var res = []
    var d = new Date(date)
    res[0] = d.getFullYear()
    res[1] = d.getMonth() + 1
    res[2] = d.getDate()
    return res.join("-")
}

function fbLoader(){
    $('.mockFancybox').fancybox({
        padding: 0,
        margin: 0,
        type : 'image',
        helpers : {
            overlay : {
                css : {
                    'background' : 'white'
                }
            }
        }
    })
}

