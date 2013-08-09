"use strict";

var coverTemplate = _.template('<div class="wrapper"><% if (cover.caption) { %><div class="caption"><p><%= cover.caption %></p></div><% } %><img src="<%= cover.src %>"></div>')

var galleryTemplate = _.template('<div class="thumb <%= orientation %>"><div class="wrapper"><a href="<%= large_url %>" class="fancybox" rel="gallery" <% if (caption) { %> title="<%= caption %>"><div class="caption"><p><%= caption %></p></div><% } else { %>><% } %><img src="<%= thumb_url %>"></a></div></div>')

var GalleryView = Backbone.View.extend({
    tagName : "div",
    className : "clearfix isotope-grid showcase image",
    id : "iso-grid",
    template : galleryTemplate,
    render : function(){

    }
})


var CoverThumb = Backbone.View.extend({
    tagName : "div",
    template : coverTemplate,
    className : function() {
        return "thumb " + this.model.get('allTags').join(' ') + (this.model.get('cover').wide ? " wide" : "")
    },
    render : function(){
        this.$el.html( this.template(this.model.attributes) )
        return this
    },
    events : {
        click : "view"
    },
    view : function () {
        console.log('view clicked')
        console.dir(arguments)
        console.dir(this)
   }
})

var CoverView = Backbone.View.extend({
    tagName : "div",
    className : "clearfix isotope-grid showcase image",
    id : "iso-grid",
    render : function() {
        var projects = this.collection.models
        _.each(projects, function(project) {
            var coverThumb = new CoverThumb({ model : project })
            this.$el.append( coverThumb.render().el )
        }, this)
    },
    initialize: function() {
        this.render()
        this.$el.appendTo( this.options.container )
        isoLoader( "#" + this.id )
    }
})

var SingleView = Backbone.View.extend({ 
    className : "project viewer",
    template : _.template( $('#single-project').html() ), 
    render: function() { 
        this.$el.html( this.template( this.model.attributes ) ) 
    }, 
    initialize : function() { 
        this.render()
        this.$el.appendTo('.page')
    }
})
