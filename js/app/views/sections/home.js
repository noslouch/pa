/* app/views/sections/home.js
 * home page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'utils/quotes',
    'tpl/jst'
], function( $, Backbone, _, Q, TPL ) {

    var Home = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'open' )
        },

        render : function(spinner) {
            this.$el.addClass( 'home' )

            if ( !$('#n-container').length ) {
                var q = new Backbone.Collection({}, { url : '/api/quotes' }),
                    n = new Backbone.Collection({}, { url : '/api/noteworthy' }),
                    promiseStack = [],
                    self = this

                promiseStack.push(q.fetch(), n.fetch())
                $.when.apply( $, promiseStack ).done(function(quotesRes, bricksRes){
                    self.quoteTemplate(quotesRes[0])
                    self.noteworthyTemplate(bricksRes[0])
                    self.init(spinner)
                })
            } else {
                this.init(spinner)
            }
        },

        init : function(spinner) {
            this.$noteworthy = $('#n-container')
            $('#n-container header').click(this.open)

            this.slideshow = (function(){
                var c = document.getElementById('qContainer')
                var i = new Q.Quotes(c)
                return _.bind( i.init, i )
            }())

            this.slideshow()
            Q.inspector()

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

            spinner.detach()
        },

        quoteTemplate : function(quotes) {
            var $quotes = $(TPL.quotes()),
                container = $quotes.find('#qContainer'),
                self = this

            _.each( quotes, function(quote) {
                var $slide = $( TPL.quoteSlide() ),
                    h3 = $slide.find('h3')

                if ( quote.type === 'image' ) {
                    var img = document.createElement('img')
                    img.src = quote.src
                    img.classList.add('blind', 'closed')
                    img.title = quote.title
                    $(h3).append(img)
                } else {
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
                }
                $(container).append($slide)
            } )
            this.$el.html($quotes)
        },

        noteworthyTemplate : function(bricks) {
            var $noteworthy = $(TPL.noteworthy()),
                row = $noteworthy.find('#brickRow'),
                imgSize

            switch(bricks.length) {
                case 4:
                    imgSize = 'one-quarter'
                    break;
                case 3:
                    imgSize = 'one-third'
                    break;
                case 2:
                case 1:
                    imgSize = 'one-half'
                    break;
                default:
                    break;
            }

            _.each( bricks, function(brick) {
                $(row).append( TPL.brick({
                    src : brick['image-sizes'] ? brick['image-sizes'][this] : '',
                    link : brick.link,
                    external : brick.external ? ' target="_blank"' : '',
                    title : brick.title,
                    summary : brick.summary
                }) )
            }, imgSize )
            this.$el.append($noteworthy)
        },

        open : function(e) {
            e.preventDefault()
            this.$el.css({ 'padding-bottom' :  this.$noteworthy.hasClass('open') ? '' : 245 })
            this.$noteworthy.toggleClass('open')
        },

        onClose : function(){
            $('.page').removeClass('home')
            $('.page')[0].style.removeProperty('padding-bottom')
        }
    })
    return new Home()
})
