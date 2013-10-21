/* app/collections/films.js - All Films
 * used on /films */
'use strict';

define([
    'backbone',
    'app/models/film'
], function( Backbone, Film ) {
    var Films = Backbone.Collection.extend({
        model : Film,
        url : '/api/film'
    })

    return new Films()
})
