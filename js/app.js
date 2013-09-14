/* app.js */
'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'app/router'
], function( $, _, Backbone, Router ) {
    var init = function() {
        Router.init()
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
