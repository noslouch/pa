/* app/views/home.js
 * home page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'utils/quotes',
    'lib/requirejs/domReady!'
], function( $, Backbone, _, quotes ) {

    var Home = Backbone.View.extend({
        el : '.page',
        initialize : function() {
            _.bindAll( this, 'open' )
            this.slideshow = quotes.slideshow.bulletBuilder
            this.poll = quotes.inspector

            this.slideshow = _.bind( this.slideshow, quotes.slideshow )

            this.$noteworthy = $('#n-container')
            this.$quotes = $('#quotes')
            $('#n-container header').click(this.open)
        },

        events : {
            //'click #n-container header' : 'open'
        },

        open : function(e) {
            e.preventDefault()
            this.$noteworthy.toggleClass('open')
            this.$quotes.toggleClass('short')

        },

        render : function() {
            this.slideshow()
            this.poll()

        }
    })

    var home = new Home()

    var go = function(){
        home.render()
        setTimeout( function(){
            $('.site-header').removeClass('home')
            $('.n-wrapper').removeClass('home')
            $('#bullets').addClass('loaded')
        }, 2000 )
    }
    return go
})
