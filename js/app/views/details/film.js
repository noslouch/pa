/* app/views/film.js
 * Single Film detail view */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcases/video',
    'app/views/partials/album',
    'app/models/film',
], function( $, Backbone, _, TPL, V, A, FilmModel ) {

    var FilmDetails = A.Details.extend({
        buttonText: 'Back to All Film',
        url : '/film'
    })

    var Film = A.Album.extend({
        className : 'film viewer',
        model : new FilmModel(),
        Details : FilmDetails,
        url : '/api/film/',
        namespace : 'film',
        renderOut : function( model, response, ops ) {
            this.details.render()
            var video = new V({
                model : this.model
            })
            this.$viewer.html( video.render() )
            this.trigger( 'rendered' )
        }
    })

    return new Film()
})
