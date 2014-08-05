/* app/models/project.js - Project model */
'use strict';

define([
    'backbone',
    'underscore',
    'app/models/cover',
    'app/collections/showcases'
], function( Backbone, _, CoverImage, Showcases ) {

    var Project = Backbone.Model.extend({
        parse : function(response, options){
            var temp = response['brand_tags']
                .concat( response['type_tags'], response['industry_tags'] )

            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )

            if ( response.media.relatedLinks ) {
                _.each( response.media.relatedLinks, function(item, index){
                    var split = item.split('|')
                    response.media.relatedLinks[index] = {
                        'title' : split[0] ? split[0].trim() : '',
                        'url-title' : split[1] ? split[1].trim() : ''
                    }
                } )
            }

            response.coverImage = new CoverImage( response.cover, {
                tags : temp.length ? temp : false,
                year : response.date.year()
            } )

            return response
        },

        initialize : function() {},

        path : function() {
            return '/projects/' + this.get('url-title')
        },

        urlRoot : '/api/project'

    })

    return Project
})
