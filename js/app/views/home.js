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
        initialize : function() {
            _.bindAll( this, 'open' )
            this.slideshow = quotes.slideshow.bulletBuilder
            this.slideshow = _.bind( this.slideshow, quotes.slideshow )

            this.poll = quotes.inspector

            this.$noteworthy = $('#n-container')
            this.$quotes = $('#quotes')
            $('#n-container header').click(this.open)
        },

        open : function(e) {
            e.preventDefault()
            this.$noteworthy.toggleClass('open')
            this.$quotes.toggleClass('short')
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
    return Home
})
