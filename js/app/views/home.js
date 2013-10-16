/* app/views/home.js
 * home page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'utils/quotes',
    'tpl/jst'
], function( $, Backbone, _, quotes, TPL ) {

    var Home = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'open' )

            this.slideshow = quotes.slideshow.bulletBuilder
            this.slideshow = _.bind( this.slideshow, quotes.slideshow )
            this.poll = quotes.inspector
        },

        quoteBuilder : function(quotes) {
            var $quotes = $(TPL.quotes()),
                container = $quotes.find('#qContainer'),
                self = this

            _.each( quotes, function(quote) {
                var $slide = $(TPL.quoteSlide()),
                    h3 = $slide.find('h3')

                _.each( quote.lines, function(lineObj) {
                    var blind = document.createElement('div')
                    $(blind).addClass('blind closed')
                    $(blind).html(lineObj.line)
                    $(h3).append(blind)
                } )
                if ( quote.link ){
                    var a = document.createElement('a')
                    $(a).attr({
                        href : quote.link,
                        class : 'button closed',
                        target : quote.external ? '_blank' : ''
                    })
                }
                $(container).append($slide)
            } )
            self.$el.html($quotes)
        },

        noteworthyBuilder : function() {
            var n = new Backbone.Collection({}, { url : '/api/noteworthy' })
            n.fetch().done(function() {})
        },

        open : function(e) {
            e.preventDefault()
            this.$el.css({ 'padding-bottom' :  this.$noteworthy.hasClass('open') ? '' : 245 })
            this.$noteworthy.toggleClass('open')
        },

        render : function() {
            if ( !$('#n-container').length ) {
                var q = new Backbone.Collection({}, { url : '/api/quotes' })
                q.fetch().done(this.quoteBuilder(this))
                this.noteworthyBuilder()
            }

            this.$noteworthy = $('#n-container')
            this.$quotes = $('#quotes')
            $('#n-container header').click(this.open)

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
    return new Home()
})
