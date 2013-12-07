/* app/views/details/photography.js
 * detail view for photo galleries */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/models/photo',
    'app/views/partials/album',
], function( $, Backbone, _, TPL, P, A ) {

    var PhotoDetails = A.Details.extend({
        buttonText : 'Back to All Photography',
        url : '/photography'
    })

    var PhotoAlbum = A.Album.extend({
        model : new P(),
        Details : PhotoDetails,
        url : '/api/photography/',
        namespace : 'photography'
    })

    return new PhotoAlbum()
})
