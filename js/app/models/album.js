/* app/models/album.js - Cover/Photo Gallery model */
'use strict';

define([
    'backbone',
    'app/models/cover',
    'app/collections/covergallery'
], function( Backbone, CoverImage, CoverGallery ) {

    var PhotoAlbum = Backbone.Model.extend({
        parse : function( response, options ) {
            response.coverImage = new CoverImage( response.cover, {} )
            response.photos = new CoverGallery( response.gallery )
            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )

            return response
        },

        path : function() {
            return '/photography/' + this.get('url')
        }
    })

    return PhotoAlbum
})


