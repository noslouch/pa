"use strict";

var PA = PA || {}
PA.projects = PA.projects || []

PA.Project = Backbone.Model.extend({
    initialize : function(project, options) {
        this.set({
            coverImage : new PA.CoverImage( this.get('cover'), {
                tags : project.brand_tags.concat(project.type_tags).concat(project.industry_tags)
            }),
            showcases : new PA.Showcases(project.showcases)
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
            photos : new PA.Gallery(album.gallery)
        })
    }
})

PA.PhotoAlbums = Backbone.Collection.extend({
    model : PA.PhotoAlbum
})

PA.Film = Backbone.Model.extend({
})

PA.Films = Backbone.Collection.extend({
    model : PA.Film
})

PA.ProfileSection = Backbone.Model.extend()



PA.films = new Backbone.Collection()
PA.profile = new Backbone.Collection()

