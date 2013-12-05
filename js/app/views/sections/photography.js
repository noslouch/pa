/* app/views/sections/photography.js - Main Photography page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/collections/photography',
    'app/views/partials/photogrid',
    'imagesLoaded'
], function( $, Backbone, _, photoCollection, G ) {

    // Photo landing thumbnail
    var PhotoThumb = G.Thumb.extend({
        url : function() {
            return '/photography/' + this.model.get('url-title')
        }
    })

    // PhotoGrid
    var PhotoGrid = G.Grid.extend({
        Thumb : PhotoThumb
    })

    var Photography = G.Page.extend({
        class : 'photography',
        Grid : PhotoGrid
    })

    return new Photography({
        model : new Backbone.Model(),
        collection : photoCollection
    })
})
