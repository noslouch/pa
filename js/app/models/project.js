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

            _.each( response.relatedLinks, function(item, index){
                var split = item.split('|')
                response.relatedLinks[index] = {
                    'title' : split[0] ? split[0].trim() : '',
                    'url' : split[1] ? split[1].trim() : ''
                }
            } )

            return response
        },

        initialize : function() {
            this.set({

                date : this.parseDate( this.get('timestamp') ),

                coverImage : new CoverImage( this.get('cover'), {
                    tags : this.get('brand_tags')
                            .concat( this.get('type_tags') )
                            .concat( this.get('industry_tags') ),
                    year : this.parseDate( this.get('timestamp') ).year(),
                } ),

                showcases : new Showcases( this.get('showcases'), {
                    path : this.url()
                } )
            })

            if ( this.get('info') ) {
                this.get('showcases').add({
                    type : 'info',
                    title : 'Info',
                    url_title : 'info',
                    content : this.get('info')
                }, {
                    path : this.url()
                })
            }

            if ( this.get('relatedLinks') ) {
                this.get('showcases').add({
                    type : 'related',
                    title : 'Related',
                    url_title : 'related',
                    links : this.get('relatedLinks')
                }, {
                    path : this.url()
                })
            }
        },

        url : function() {
            return '/projects/' + this.get('url')
        }
    })

    return Project
})
