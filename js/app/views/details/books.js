/* app/views/details/book.js
 * details view for book galleries */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/models/book',
    'app/views/partials/album'
], function( $, Backbone, _, TPL, B, A ) {

    var BookDetails = A.Details.extend({
        buttonText : 'Back to All Books',
        url : '/books'
    })

    var BookAlbum = A.Album.extend({
        model : new B(),
        Details : BookDetails,
        url : '/api/books/',
        namespace : 'books'
    })

    return new BookAlbum()
})
