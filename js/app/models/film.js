/* app/models/film.js - Film model */
'use strict';

define([
    'backbone'
], function( Backbone ) {

    var Film = Backbone.Model.extend({
        parse : function( response, options ) {
            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )
            response.path = '/film/' + response['url-title']
            return response
        }

        //path : function() {
        //    return '/film/' + this.get('url')
        //}
    })

    return Film
})


