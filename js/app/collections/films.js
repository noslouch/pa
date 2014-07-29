/* app/collections/films.js - All Films
 * used on /films */
'use strict';

define([
    'backbone',
    'app/models/film'
], function( Backbone, Film ) {
    var PA = window.PA || {}

    if ( PA.film ) {
        return new Backbone.Collection( PA.film, { model : Film, parse : true } )
    } else {
        return new Backbone.Collection([], {
            model : Film
        })
    }
})
