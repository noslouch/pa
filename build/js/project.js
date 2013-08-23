/* models/project.js - Project model */

"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Project = Backbone.Model.extend({
    initialize : function(project, options) {
        this.set({
            coverImage : new PA.CoverImage( this.get('cover'), {
                tags : project.brand_tags.concat(project.type_tags).concat(project.industry_tags)
            }),
            showcases : new PA.Showcases(project.showcases, { path : this.url() }),
            //htmlDate : this.makeHtmlDate( this.get('date') ),
            //date : this.parseDate( this.get('date' ) )
            htmlDate : this.get('date')
        })
        /*
        this.get('showcases').add({
            type : 'info',
            content : this.get('infoText')
        },
        {
            type : 'related',
            links : this.get('relatedLinks')
        })
        */
    },
    url : function() {
        return '/projects/' + this.get('url')
    }
})
