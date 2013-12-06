/* app/views/sections/film.js - Main Film page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/collections/films',
    'app/views/partials/grid',
    'imagesLoaded'
], function( $, Backbone, _, filmCollection, G ) {

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

    return new Film({
        model : new Backbone.Model(),
        collection : filmCollection
    })
})
