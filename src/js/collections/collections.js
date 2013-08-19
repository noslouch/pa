"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Project = Backbone.Model.extend({
    initialize : function(project, options) {
        this.set({
            coverImage : new PA.CoverImage( this.get('cover'), {
                tags : project.brand_tags.concat(project.type_tags).concat(project.industry_tags)
            }),
            showcases : new PA.Showcases(project.showcases),
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date' ) )
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

PA.Projects = Backbone.Collection.extend({
    model : PA.Project
})

PA.CoverImage = Backbone.Model.extend({
    initialize: function(image, options) {
        this.set({ tags : options.tags || [] })
    }
})

PA.Covers = Backbone.Collection.extend({
    model : PA.CoverImage
})

PA.Showcase = Backbone.Model.extend({
    initialize: function(showcase, options){
        if ( showcase.type === 'gallery' ) {
            this.set({
                gallery : new PA.Gallery(showcase.images)
            })
        }
    }
})

PA.Showcases = Backbone.Collection.extend({
    model : PA.Showcase
})

PA.GalleryImage = Backbone.Model.extend({
    initialize : function(image, options) {
    }
})

PA.Gallery = Backbone.Collection.extend({
    model : PA.GalleryImage
})

PA.PhotoAlbum = Backbone.Model.extend({
    initialize : function(album, options) {
        this.set({
            coverImage : new PA.CoverImage( this.get('cover'), {} ),
            photos : new PA.Gallery(album.gallery),
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date' ) )
        })
    },
    url : function() {
        return '/photography/' + this.get('url')
    }
})

PA.PhotoAlbums = Backbone.Collection.extend({
    model : PA.PhotoAlbum
})

PA.Film = Backbone.Model.extend({
    url : function() {
        return '/film/' + this.get('url')
    }
})

PA.Films = Backbone.Collection.extend({
    model : PA.Film
})

PA.PressItem = Backbone.Model.extend({
    initialize : function(pressItem, options){
        this.set({
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date') )
        })
     },
    url : function() {
        return '/press/' + this.get('url')
    }
})

PA.PressCollection = Backbone.Collection.extend({
    model : PA.PressItem
})

PA.AwardItem = Backbone.Model.extend({
    initialize : function(awardItem, options){
        this.set({
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date' ) )
        })
     }
})

PA.AwardCollection = Backbone.Collection.extend({
    model : PA.AwardItem
})
PA.ProfileSection = Backbone.Model.extend()



PA.films = new PA.Films()
PA.profile = new PA.ProfileSection()
PA.projects = new PA.Projects()
PA.albums = new PA.PhotoAlbums()

