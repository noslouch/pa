/* models/project.js - Project model */

"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Project = Backbone.Model.extend({
    initialize : function(project, options) {
        this.set({
            coverImage : new PA.CoverImage( this.get('cover'), {
                tags : project.brand_tags
                        .concat(project.type_tags)
                        .concat(project.industry_tags)
            }),
            showcases : new PA.Showcases( project.showcases, {
                path : this.url()
            }),
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date' ) )
        })
        _.each( this.get('brand_tags'), function(el, i) {
            var filename = el.logo.split('/')[5]
            el.logo = 'http://assets.peterarnell.s3.amazonaws.com/logos/' + filename
        })
        _.each( this.get('relatedLinks'), function(item, index){
            var split = item.split('|')
            this.attributes.relatedLinks[index] = {
                'title' : split[0] ? split[0].trim() : '',
                'url' : split[1] ? split[1].trim() : ''
            }
        }, this)
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
