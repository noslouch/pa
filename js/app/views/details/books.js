/*global PA*/
/* app/views/details/book.js
 * details view for book galleries */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/models/book',
    'app/collections/books',
    'app/views/partials/album'
], function( $, Backbone, _, TPL, BookModel, Books, Album ) {

    try {
        Books.add( PA.books, { parse : true } )
    } catch (e) {
        Books.fetch()
    }

    var BookDetails = new Album({
        collection : Books,
        model : new BookModel(),
        section : 'books'
    })

    return BookDetails
})
