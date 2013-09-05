/* models/project.js - Project model */

"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Project = Backbone.Model.extend({
    parse : function(response, options){

        response.htmlDate = this.makeHtmlDate( response.timestamp )

        _.each( response.brand_tags, function(el, i) {
            var filename = el.logo.split('/')[5]
            el.logo = 'http://assets.peterarnell.s3.amazonaws.com/logos/' + filename
        })

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

            coverImage : new PA.CoverImage( this.get('cover'), {
                tags : this.get('brand_tags')
                        .concat( this.get('type_tags') )
                        .concat( this.get('industry_tags') )
            } ),

            showcases : new PA.Showcases( this.get('showcases'), {
                path : this.url()
            } )
        })

        this.get('showcases').add([
            {
                type : 'info',
                title : 'Info',
                url_title : 'info',
                content : this.get('info')
            },
            {
                type : 'related',
                title : 'Related',
                url_title : 'related',
                links : this.get('relatedLinks')
            }
        ], {
            path : this.url()
        })

    },

    url : function() {
        return '/projects/' + this.get('url')
    }
})
