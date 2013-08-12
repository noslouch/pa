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

var ProjectListView = Backbone.View.extend({
    tagName : 'section',
    header : PA.jst.listHeaderPartial,
    partial : PA.jst.listItemPartial,

    render : function() {
        var projects = this.options.projects
        var path = this.options.path
        var date = this.options.date

        this.$el.append( '<ul />')
        this.$('ul').append( this.header({ date : date }) )

        _.each( projects, function(project) {

            this.$('ul')
            .append( this.partial({
                path : path ? path + "/" : "",
                url : project.get('url'),
                title : project.get('title'),
                summary : path === 'projects' ? project.get('summary') : ''
            }) )

        }, this )

        return this.el
    }

})

var ProjectListShowcase = Backbone.View.extend({
    tagName : 'div',
    className : 'showcase list',
    render : function(){
        this.$el.empty()

        // groupedCollection is an object of years paired with project objects that fall within that year.
        _.each( this.options.groupedCollection, function(v,k){
            var html = new ProjectListView({
                date : k,
                projects : v,
                path : this.options.path
            })
            this.$el.append( html.render() )
        }, this )
        return this.el
    }
})

var FilmThumb = Backbone.View.extend({
    tagName : 'div',
    template : PA.jst.filmThumb,
    render : function() {
        var html = this.template( this.model.attributes )
        this.$el.append( html )
        return this.el
    }
})

var FilmShowcase = Backbone.View.extend({
    tagName : 'div',
    rowTmpl : PA.jst.filmRow,
    $row : undefined,
    render : function() {

        this.collection.forEach( function(model, index){
            if (index % 4 === 0) {
                this.$row = $( this.rowTmpl() )
                this.$el.append(this.$row)
            }
            this.$row.append( new FilmThumb({ model : model }).render() )
        }, this )

        return this.el
    }
})


var Thumb = Backbone.View.extend({
    tagName : "div",
    template : PA.jst.thumbTemplate,
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
    thumbs : [],
    render : function(){
        this.collection.forEach(function(image) {
            var thumb = new Thumb({ 
                model : image,
                cover : this.options.cover ? true : false,
                large : this.collection.length < 5 && !this.options.cover
            })
            this.$el.append( thumb.render().el )
            this.thumbs.push(thumb)
        }, this)
    },
    initialize: function() {
        this.render()

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
            case 'film':
                //this.$el.html ( new FilmShowcase(options).el )
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
