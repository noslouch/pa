/* app/views/home.js
 * home page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'utils/quotes'
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
            this.$el.css({ 'padding-bottom' :  this.$noteworthy.hasClass('open') ? '' : 245 })
            this.$noteworthy.toggleClass('open')
        },

        render : function() {
            this.slideshow()
            this.poll()

            var brickClass

            switch( $('.brick').length ) {
                case 4:
                    brickClass = 'four'
                    break;
                case 3:
                    brickClass = 'three'
                    break;
                case 2:
                    brickClass = 'two'
                    break;
                case 1:
                    brickClass = 'one'
                    break;
                default:
                    break;
            }

            $('#brickRow').addClass(brickClass)

            setTimeout( function(){
                $('.site-header').removeClass('home')
                $('.n-wrapper').removeClass('home')
                $('#bullets').addClass('loaded')
            }, 2000 )
        }
    })

    //var home = new Home()

    var go = function(){
        //home.render()
    }

    return Home
})
