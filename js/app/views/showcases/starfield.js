/* app/views/showcases/starfield.js
 * Starfield view */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst'
], function( $, Backbone, _, TPL ) {

    // Star
    // Starfield item
    var Star = Backbone.View.extend({
        tagName : "a",
        initialize : function() {
            _.bindAll( this, 'render' )

            $('<img>')
            .appendTo( this.$el )
            .attr( 'src', this.model.get('thumb') )

            this.$el.css({
                left : this.options.HALF_WIDTH + this.randomRange(-this.options.HALF_WIDTH, this.options.HALF_WIDTH),
                top : this.options.HALF_HEIGHT + this.randomRange(-this.options.HALF_HEIGHT, this.options.HALF_HEIGHT)
            })
        },

        render : function(instagram) {
            this.$el
                .addClass( 'star' )

            if ( !!instagram ) {
                this.$el
                .attr( 'target' , '_blank' )
                .attr( 'href', this.model.get('url') )
            } else {
                var caption = document.createElement('div'),
                    p = document.createElement('p'),
                    span = document.createElement('span')

                $(p).html( this.model.get('title') )
                $(span).addClass('year').html( this.model.get('year') )
                $(caption).addClass('caption').append(p).append(span)

                this.$el
                .attr({
                    href : '/projects/' + this.model.get('url-title'),
                    id : this.model.id
                })
                .append(caption)
            }

            return this.el
        }
    })

    Star.prototype.randomRange = function (min, max) {
        return ((Math.random()*(max-min)) + min)
    }

    // Starfield
    // Zoom effect used on Projects landing page
    var Starfield = Backbone.View.extend({
        tagName : 'div',
        className : 'starfield showcase',
        id : 'showcase',
        initialize : function( collection, instagram ){
            _.bindAll( this, 'stagger' )
            this.instagram = instagram
        },

        stagger : function() {
            var SCREEN_WIDTH = window.innerWidth,
                SCREEN_HEIGHT = window.innerHeight,
                HALF_WIDTH = window.innerWidth / 2,
                HALF_HEIGHT = window.innerHeight / 2
                //imageLimit = SCREEN_WIDTH < 320 ? 12 : 48

            var i = 0,
                self = this

            function go(){
                self.$el.append(
                    new Star({
                        model : self.images.models[i],
                        HALF_HEIGHT : HALF_HEIGHT,
                        HALF_WIDTH : HALF_WIDTH
                    }).render(self.instagram) )
                i++

                // CHANGE TO IMAGELIMIT WHEN PROJECTS INCREASE
                if ( i < self.images.length ) {
                    setTimeout(go, 550)
                }
            }

            go()

            this.starsRunning = false
        },

        destroy : function() {
            this.starsRunning = false
            this.$el.empty()
            this.remove()
            this.unbind()
        },

        render : function() {
            this.starsRunning = true
            this.$el.empty()
            this.images = new Backbone.Collection( this.collection.shuffle() )
            setTimeout(this.stagger, 0)
            return this.el
        }
    })

    return Starfield
})
