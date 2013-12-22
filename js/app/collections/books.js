/* app/collections/books.js - Books collection
 * used on /books */
'use strict';

define([
    'backbone',
    'app/models/book'
], function( Backbone, Book ) {

    var Books = Backbone.Collection.extend({
        model : Book,
        url : '/api/books',
        path : 'books'
    })

    return new Books()
})
