/* app/models/film.js - Film model */
'use strict';

define([
    'backbone'
], function( Backbone ) {

    var Film = Backbone.Model.extend({
        initialize : function() {
            this.set({
                htmlDate : this.makeHtmlDate( this.get('date') ),
                date : this.parseDate( this.get('date') )
            })
        },

        url : function() {
            return '/film/' + this.get('url')
        }
    })

    return Film
})


