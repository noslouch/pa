/* app/views/sections/books.js - Main Book page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/collections/books',
    'app/views/partials/grid',
    'imagesLoaded'
], function( $, Backbone, _, bookCollection, G ) {

    // Book landing thumbnail
    var BookThumb = G.Thumb.extend({
        url : function() {
            return '/film/' + this.model.get('url-title')
        }
    })

    // BookGrid
    var BookGrid = G.Grid.extend({
        class : 'film-row',
        Thumb : BookThumb
    })

    var Book = G.Page.extend({
        class : 'film',
        Grid : BookGrid
    })

    return new Book({
        model : new Backbone.Model(),
        collection : bookCollection
    })
})
