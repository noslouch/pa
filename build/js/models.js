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

    }
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

PA.GalleryImage = Backbone.Model.extend({
    initialize : function(image, options) {
    }
})

PA.CoverImage = Backbone.Model.extend({
    initialize: function(image, options) {
        this.set( {tags : options.tags} )
    }
})

PA.Film = Backbone.Model.extend()
PA.ProfileSection = Backbone.Model.extend()
PA.PhotoAlbum = Backbone.Model.extend()

PA.Projects = Backbone.Collection.extend({
    model : PA.Project
})

PA.Showcases = Backbone.Collection.extend({
    model : PA.Showcase
})

PA.Gallery = Backbone.Collection.extend({
    model : PA.GalleryImage
})

PA.CoverGallery = Backbone.Collection.extend({
    model : PA.CoverImage
})

PA.photoAlbums = new Backbone.Collection({
    model : PA.PhotoAlbum
})


PA.films = new Backbone.Collection()
PA.profile = new Backbone.Collection()

