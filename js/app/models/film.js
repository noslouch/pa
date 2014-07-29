/* app/models/film.js - Film model */
'use strict';

define([
    'backbone'
], function( Backbone ) {

    var Film = Backbone.Model.extend({
        parse : function( response, options ) {
            var temp = response['brand_tags']
                .concat( response['type_tags'], response['industry_tags'] )
            if ( temp.length ) {
                response.tags = temp
            }
            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )
            response.path = '/film/' + response['url-title']
            return response
        }
    })

    return Film
})


