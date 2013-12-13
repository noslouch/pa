/* app/views/sections/film.js - Main Film page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/collections/films',
    'app/views/partials/grid',
    'app/views/partials/mixfilter'
], function( $, Backbone, _, filmCollection, G, Filter ) {

    // Film landing thumbnail
    var FilmThumb = G.Thumb.extend({
        url : function() {
            return '/film/' + this.model.get('url-title')
        }
    })

    // FilmGrid
    var FilmGrid = G.Grid.extend({
        class : 'film-row',
        Thumb : FilmThumb
    })

    var Film = G.Page.extend({
        class : 'film',
        Grid : FilmGrid
    })

    Backbone.dispatcher.on('film:ready', function(film){
        film.filter = new Filter({
            el : '#filter-bar',
            model : film.model,
            collection : film.collection
        })
        film.filter.render()
    })

    return new Film({
        model : new Backbone.Model(),
        collection : filmCollection
    })
})
