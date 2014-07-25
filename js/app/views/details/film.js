/*global PA*/
/* app/views/film.js
 * Single Film detail view */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/models/film',
    'app/collections/films',
    'app/views/partials/album'
], function( $, Backbone, _, TPL, FilmModel, Films, Album) {

    try {
        Films.add( PA.film, { parse : true } )
    } catch (e) {
        Films.fetch()
    }

    var FilmDetails = new Album({
        collection : Films,
        model : new FilmModel(),
        section : 'film'
    })

    return FilmDetails
})
