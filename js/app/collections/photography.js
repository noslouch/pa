/* app/collections/albums.js - Photography Albums collection
 * used on /photography */
'use strict';

define([
    'backbone',
    'app/models/album'
], function( Backbone, PhotoAlbum ) {

    var PhotoAlbums = Backbone.Collection.extend({
        model : PhotoAlbum,
        url : '/api/photography',
        path : 'photography'
    })

    return new PhotoAlbums()
})
