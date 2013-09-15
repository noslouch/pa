/* app.js */
'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'app/router',
    'app/views/chromeviews'
], function( $, _, Backbone, Router, Chrome ) {
    var init = function() {
        Backbone.history.start({ pushState : true, root : '/' })
    }

    return { init : init }
})

/*
PA.app = new PA.App({ el : document })
Backbone.history.start({pushState: true, root: "/"})

$(function(){
    var qContainer = document.getElementById('quotes')

    $('#n-container header').click(function(e){
        e.preventDefault()
        $('#n-container').toggleClass('open')
        $(qContainer).toggleClass('short')
    })

    try {
        fbLoader()
    } catch(e) {
        console.log('no fancybox')
    }

    checkScroll()

})
*/
