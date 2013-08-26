/* models/album.js - Cover/Photo Gallery model */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.PhotoAlbum = Backbone.Model.extend({
    initialize : function(album, options) {
        this.set({
            coverImage : new PA.CoverImage( this.get('cover'), {} ),
            photos : new PA.CoverGallery(album.gallery),
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date' ) )
        })
    },
    url : function() {
        return '/photography/' + this.get('url')
    }
})
