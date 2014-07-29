/* app/models/album.js - Cover/Photo Gallery model */
'use strict';

define([
    'backbone'
], function( Backbone ) {

    var PhotoAlbum = Backbone.Model.extend({
        parse : function( response, options ) {
            response.photos = new Backbone.Collection( response.gallery )
            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )
            response.path = '/photography/' + response['url-title']

            return response
        },

        path : function() {
            return '/photography/' + this.get('url')
        }
    })

    return PhotoAlbum
})


