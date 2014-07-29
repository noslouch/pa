/* app/collections/books.js - Books collection
 * used on /books */
'use strict';

define([
    'backbone',
    'app/models/book'
], function( Backbone, Book ) {

    var PA = window.PA || {}

    if ( PA.books ) {
        return new Backbone.Collection( PA.books, { model : Book, parse : true } )
    } else {
        return new Backbone.Collection([], {
            model : Book
        })
    }
})
