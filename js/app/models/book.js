/* app/models/book.js - Book model */
'use strict';

define([
    'backbone'
], function( Backbone ) {

    var Book = Backbone.Model.extend({
        parse : function( response, options ) {
            response.photos = new Backbone.Collection( response.gallery )
            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )
            response.path = '/books/' + response['url-title']

            return response
        },

        path : function() {
            return '/books/' + this.get('url')
        }
    })

    return Book
})

 
