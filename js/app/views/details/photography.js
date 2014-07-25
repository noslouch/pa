/*global PA*/
/* app/views/details/photography.js
 * detail view for photo galleries */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/models/photo',
    'app/collections/photography',
    'app/views/partials/album',
], function( $, Backbone, _, TPL, PhotoModel, Photos, Album ) {

    try {
        Photos.add( PA.photography, { parse : true } )
    } catch (e) {
        Photos.fetch()
    }

    var PhotoDetails = new Album({
        collection : Photos,
        model : new PhotoModel(),
        section : 'photography'
    })

    return PhotoDetails

})
