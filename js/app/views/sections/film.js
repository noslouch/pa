/* app/views/sections/film.js - Main Film page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/collections/films',
    'tpl/jst',
    'imagesLoaded'
], function( $, Backbone, _, filmCollection, TPL ) {

    // FilmThumb
    // Film grid thumbnail
    var FilmThumb = Backbone.View.extend({
        tagName : 'div',
        className : 'four-column-cell',
        template : TPL.filmThumb,
        render : function() {
            var html = this.template({
                url : this.model.get('path'),
                thumb : this.model.get('thumb'),
                title : this.model.get('title'),
                summary : this.model.get('summary')
            })
            this.$el.append( html )
            return this.el
        }
    })

    // FilmGrid
    var FilmGrid = Backbone.View.extend({
        tagName : 'div',
        className: 'four-column showcase',
        rowTmpl : TPL.filmRow,
        $row : undefined,
        initialize : function() {
            if ( this.$el.children().length ) { return this.el }

            this.collection.forEach( function(model, index){
                if (index % 4 === 0) {
                    this.$row = $( this.rowTmpl() ).addClass('film-row')
                    this.$el.append(this.$row)
                }
                this.$row.append( new FilmThumb({ 
                    model : model
                }).render() )
            }, this )

        },

        render : function() {
            this.delegateEvents()
            this.$('.four-column-row').imagesLoaded( function() {
                $('.four-column-row').addClass('loaded')
            })
            return this.el
        }
    })

    var Film = Backbone.View.extend({
        initialize : function(){
            _.bindAll( this, 'render', 'navigate' )
            var self = this

            this.collection.fetch({
                success : function(collection) {
                    self.filmThumbs = new FilmGrid({
                        collection : collection
                    })

                    Backbone.dispatcher.trigger('film:ready')
                }
            })
        },

        events : {
            'click .four-column-cell a' : 'navigate'
        },

        render : function() {
            this.$el.html( this.filmThumbs.render() )
        },

        onClose : function() {
            $('.page').removeClass('film')
        },

        init : function(spinner) {

            this.$el.addClass('film')
            if ( !this.collection.length ) {
                throw {
                    message : 'Films aren\'t loaded',
                    type : 'EmptyCollection'
                }
            }

            if (spinner) {spinner.detach()}
            this.render()
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
        }
    })

    return new Film({
        model : new Backbone.Model(),
        collection : filmCollection
    })
})
