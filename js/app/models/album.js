/* app/models/album.js - Cover/Photo Gallery model */
'use strict';

define([
    'backbone',
    'app/models/cover',
    'app/collections/covergallery'
], function( Backbone, CoverImage, CoverGallery ) {

    var PhotoAlbum = Backbone.Model.extend({
        initialize : function(album, options) {
            this.set({
                coverImage : new CoverImage( this.get('cover'), {} ),
                photos : new CoverGallery(album.gallery),
                htmlDate : this.makeHtmlDate( this.get('date') ),
                date : this.parseDate( this.get('date' ) )
            })
        },
        url : function() {
            return '/photography/' + this.get('url')
        }
    })

    return PhotoAlbum
})


