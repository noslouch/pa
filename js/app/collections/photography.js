/* app/collections/photography.js - Photography Albums collection
 * used on /photography */
'use strict';

define([
    'backbone',
    'app/models/photo'
], function( Backbone, PhotoAlbum ) {

    var PA = window.PA || {}

    if ( PA.photography ) {
        return new Backbone.Collection( PA.photography, { model : PhotoAlbum, parse : true })
    } else {
        return new Backbone.Collection([], {
            model : PhotoAlbum
        })
    }
})
