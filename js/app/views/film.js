/* app/views/film.js - Main Film page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/collections/films',
    'app/views/showcaseviews'
], function( $, Backbone, _, filmCollection, S ) {

    var Film = Backbone.View.extend({
        initialize : function(){
            _.bindAll( this, 'render', 'navigate' )
            var self = this

            this.collection.fetch({
                success : function(collection) {
                    self.filmThumbs = new S.FilmGrid({
                        collection : collection
                    })

                    Backbone.dispatcher.trigger('film:ready')
                }
            })
        },

        events : {
            'click .film-thumb a' : 'navigate'
        },

        render : function() {
            this.$el.html( this.filmThumbs.render() )
        },

        init : function(spinner) {
            if ( !this.collection.length ) {
                throw {
                    message : 'Films aren\'t loaded',
                    type : 'EmptyCollection'
                }
            }

            spinner.detach()
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
