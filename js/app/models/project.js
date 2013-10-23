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

            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )

            if ( response.relatedLinks[0] ) {
                _.each( response.relatedLinks, function(item, index){
                    var split = item.split('|')
                    response.relatedLinks[index] = {
                        'title' : split[0] ? split[0].trim() : '',
                        'url-title' : split[1] ? split[1].trim() : ''
                    }
                } )
            } else {
                response.relatedLinks = false
            }

            response.coverImage = new CoverImage( response.cover, {
                tags : response['brand_tags']
                        .concat( response['type_tags'] )
                        .concat( response['industry_tags'] ),
                year : response.date.year()
            } )

            response.showcases = new Showcases( response.showcases, {
                path : '/projects/' + response['url-title']
            } )

            if ( response.info ) {
                response.showcases.add({
                    type : 'info',
                    title : 'Info',
                    url_title : 'info',
                    content : response.info
                }, {
                    path : '/projects/' + response['url-title']
                })
            }

            if ( response.relatedLinks ) {
                response.showcases.add({
                    type : 'related',
                    title : 'Related',
                    url_title : 'related',
                    links : response.relatedLinks
                }, {
                    path : '/projects/' + response['url-title']
                })
            }
            return response
        },

        initialize : function() {

            //if ( this.get('info') ) {
            //    this.get('showcases').add({
            //        type : 'info',
            //        title : 'Info',
            //        url_title : 'info',
            //        content : this.get('info')
            //    }, {
            //        path : '/projects/' + this.get('url-title')
            //    })
            //}

            //if ( this.get('relatedLinks') ) {
            //    this.get('showcases').add({
            //        type : 'related',
            //        title : 'Related',
            //        url_title : 'related',
            //        links : this.get('relatedLinks')
            //    }, {
            //        path : '/projects/' + this.get('url-title')
            //    })
            //}
        },

        defaults : {
            active : false
        },

        path : function() {
            return '/projects/' + this.get('url-title')
        },

        activate : function(){
            this.set('active', true)
        },

        deactivate : function(){
            this.set('active', false)
        }
    })

    return Project
})
