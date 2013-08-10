"use strict";

var PA = PA || {}

// Navigation Controller

PA.header = new Backbone.View({
    el : ".site-header nav",
    events : {
        click : function(e){
            e.preventDefault()
            PA.router.navigate(e.target.pathname, {trigger: true})
        }
    }
})

// Base Templates

var ChromeView = Backbone.View.extend()

var HomeView = Backbone.View.extend()
var ProjectsView = Backbone.View.extend()
var PhotoView = Backbone.View.extend()
var FilmView = Backbone.View.extend()
var ProfileView = Backbone.View.extend()
var ContactView = Backbone.View.extend()
var StreamView = Backbone.View.extend()

var thumbTemplate = _.template('<div class="wrapper"><a href="<%= url %>"<% if (!cover) { %> class="fancybox" rel="gallery"<% } %>><% if (caption) { %> <div class="caption"><p><%= caption %></p></div><% } %><img src="<% large ? print(lg_thumb) : print(thumb) %>"></a></div>')


var Thumb = Backbone.View.extend({
    tagName : "div",
    template : thumbTemplate,
    className : function() {
        if (this.options.cover) {
            return "thumb " + this.model.get('tags').join(' ') + (this.model.get('wide') ? " wide" : "")
        } else {
            return "thumb" + (this.model.get('wide') ? " wide" : "")
        }
    },
    render : function(){
        this.$el.html( this.template({
            url : this.model.get('url'),
            cover : this.options.cover,
            caption : this.model.get('caption'),
            thumb : this.model.get('thumb'),
            lg_thumb : this.model.get('lg_thumb'),
            large : this.options.large
        }) )
        return this
    },
    events : {
        click : "view"
    },
    view : function (e) {
        e.preventDefault()
        PA.router.navigate("projects/" + this.model.get('url'), {trigger : true} )
   }
})

var ImageShowcase = Backbone.View.extend({
    tagName : "div",
    className : function() {
        var classes = ["isotope-grid", "showcase", "image"]
        if (this.options.cover) {
            return classes.concat(["fixed"]).join(' ')
        } else if (this.collection.length < 5) {
            return classes.concat(["rtl"]).join(' ')
        } else {
            return classes.join(' ')
        }
    },
    id : "iso-grid",
    render : function(){
        this.collection.forEach(function(image) {
            var thumb = new Thumb({ 
                model : image,
                cover : this.options.cover ? true : false,
                large : this.collection.length < 5
            })
            this.$el.append( thumb.render().el )
        }, this)
    },
    initialize: function() {
        this.$el.html( this.render() )
        var $el = this.$el,
            $img = $el.find('img'),
            rtl = $el.hasClass('rtl'),
            fixed = $el.hasClass('fixed')

        $el.imagesLoaded( function() {
            $el.isotope({
                transformsEnabled: !rtl,
                itemSelector: '.thumb',
                layoutMode : fixed ? 'masonry' : 'fitRows',
                masonry : {
                    gutterWidth: 7,
                    columnWidth: rtl ? 164*1.5 : 164
                },
                onLayout : function() { 
                    $(this).css('overflow', 'visible')
                }
            })

            $img.addClass('loaded')
        })
    }
})

PA.ShowcaseContainer = Backbone.View.extend({
    tagName : 'div',
    id : 'showcaseContainer',
    className : 'container',
    render : function(options){
        switch (options.type) {
            case 'image':
                this.$el.html( new ImageShowcase(options).el )
                fbLoader()
                break;
            case 'list':
                //this.$el.html( new ListShowcase(options).el )
                break;
            case 'text':
                //this.$el.html( new TextShowcase(options).el )
                break;
            default:
                break;
        }

        if (options.cover) {
            $('.page').html(this.el)
        }
    }
})

var SingleView = Backbone.View.extend({
    tagName : "div",
    className : "project viewer",
    render: function() {
        var attr = this.model.attributes
        this.$el.html( this.template( {
            title : attr.title,
            date : attr.date,
            summary : attr.summary,
            showcases : this.model.get('showcases'),
            brand_tags : attr.brand_tags,
            industry_tags : attr.industry_tags,
            type_tags : attr.type_tags
        }))
    },
    initialize : function() {
        this.template = _.template( $('#single-project').html() )
        this.render()
        $('.page').html(this.el)
    },
    events : {
        "click .showcase-links a" : function(e) {
            var showcaseModel = this.model.get('showcases').get(e.target.id)
            if (showcaseModel.get('type') === 'gallery') {
                var gallery = showcaseModel.get('gallery')
                PA.showcase = new PA.ShowcaseContainer({el : "#showcaseContainer"})
                PA.showcase.render({
                    collection : gallery,
                    cover : false,
                    type: 'image'
                })
            }
        }
    }
})
