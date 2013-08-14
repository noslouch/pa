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

var HomeView = Backbone.View.extend()
var ProjectsView = Backbone.View.extend()
var PhotoView = Backbone.View.extend()
var FilmView = Backbone.View.extend()
var ProfileView = Backbone.View.extend()
var ContactView = Backbone.View.extend()
var StreamView = Backbone.View.extend()

var BrandControls = Backbone.View.extend({
    tagName : 'div',
    className : 'controls',
    template : PA.jst.controlsPartial,
    events : {
        'click #logoView' : 'toggleView',
        'click #titleView' : 'toggleView',
        'click #close' : 'close'
    },
    render : function() {
        this.$el.html( this.template() )
        return this.el
    }
})


var ProjectFilterItem = Backbone.View.extend({
    tagName : 'li',
    template : PA.jst.namePartial,
    events : {
        'click' : 'debug'
    },
    render : function() {
        this.$el
            .html( this.template({ 
                tag : this.options.tag
            }) )
        return this.el
    }
})

var ProjectFilter = Backbone.View.extend({
    tagName : 'ul',
    className : 'names',
    render : function() {
        var filter
        switch (this.options.type) {
            case 'industry':
                filter = 'industry_tags'
                break;
            case 'type':
                filter = 'type_tags'
                break;
            default:
                break;
        }

        var tags = this.collection
            .pluck(filter)
            .reduceRight(function(a,b) {
                return a.concat(b) 
            }, [])

        tags.forEach( function(tag) {
            this.$el
                .append( new ProjectFilterItem({ tag : tag }).render() )
        }, this )

        return this.el
    }
})

// Each brand filter item will need a logo, a tag className (for isotope)
// and a tag displayName to be shown as a list.
var BrandFilterItem = Backbone.View.extend({
    tagName : 'li',
    logoTemplate : PA.jst.logoPartial,
    nameTemplate : PA.jst.namePartial,
    render : function() {
        this.$el.append( this.logoTemplate({ tag : this.options.tag, logo: this.options.logo }) )
        this.$el.append( this.nameTemplate({ tag : this.options.tag }) )
        return this.el
    }
})

var BrandFilter = Backbone.View.extend({
    tagName : 'ul',
    id : 'brandList',
    className : 'icons',
    render : function() {
        var tags = this.collection
            .pluck('brand_tags')
            .reduceRight( function(a,b) {
                return a.concat(b)
            }, [] )

        tags.forEach( function(tag) {
            this.$el
                .append( new BrandFilterItem({
                    tag : tag,
                    logo : 'http://placehold.it/80x45'
                })
                .render() )
        }, this)

        return this.el
    }
})

var FilterControls = Backbone.View.extend({

})

// instantiate with projects collection
var FilterBar = Backbone.View.extend({
    tagName: 'div',
    className : 'filter-bar',
    id : 'filter-bar',
    template : PA.jst.projectFilter,
    events : {
        "click" : "debug"
    },
    debug : function(e) { e.preventDefault(); },
    initialize : function() {
        this.$el.html( this.template() )
    },
    render : function() {
        this.$('#brand .wrapper')
            .append( new BrandControls().render() )
            .append( new BrandFilter({
                collection : this.collection
            }).render() )
        this.$('#industry .wrapper')
            .append( new ProjectFilter({
                type : 'industry',
                collection : this.collection
            }).render() )
        this.$('#type .wrapper')
            .append( new ProjectFilter({
                type : 'type',
                collection : this.collection
            }).render() )

        this.$el
            .append( PA.jst.jumps() )
            .append( PA.jst.sorts() )
            .append( PA.jst.views() )

        return this.el
    }
})

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
        return this.el
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
    tagName : 'div',
    id : 'iso-grid',
    className : function() {
        var classes = ['isotope-grid', 'showcase', 'image']
        if (this.options.cover) {
            return classes.concat(['fixed']).join(' ')
        } else if (this.collection.length < 5) {
            return classes.concat(['rtl']).join(' ')
        } else {
            return classes.join(' ')
        }
    },
    render : function(){
        this.collection.forEach(function(image) {
            var thumb = new Thumb({
                model : image,
                cover : this.options.cover ? true : false,
                large : this.collection.length < 5 && !this.options.cover
            })
            this.$el.append( thumb.render() )
        }, this)

        return this.el
    },
    isotope: function() {

        var $img = this.$('img'),
            rtl = this.$el.hasClass('rtl'),
            fixed = this.$el.hasClass('fixed')

        this.$el.imagesLoaded( function() {
            this.isotope({
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

var VideoShowcase = Backbone.View.extend({
    tagname : 'div',
    className : 'showcase video',
    videoCaption : PA.jst.videoCaption,
    initialize : function() {
        this.videoTmpl = this.model.get('video_id') ? PA.jst.videoID : PA.jst.iframeVideo
        this.videoSrc = this.model.get('video_id') ? this.model.get('video_id') : this.model.get('video_src')
    },
    render : function() {
        var film = this.videoTmpl({
            videoSrc : this.videoSrc,
            youtube : this.model.get('youtube')
        })
        var caption = this.videoCaption({
            title : this.model.get('title'),
            content : this.model.get('caption'),
        })

        this.$el
            .append(film)
            .append(caption)

        return this.el
    }
})

PA.ShowcaseContainer = Backbone.View.extend({
    tagName : 'div',
    id : 'showcaseContainer',
    className : 'container',
    render : function(options){
        switch (options.type) {
            case 'image':
                // instantiated with options object, can be passed through render method
                // collection : new CoverGallery or new Gallery
                // cover : boolean
                var html = new ImageShowcase(options)
                this.$el.html( html.render() )
                html.isotope()
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

        return this.el

        if (options.cover) {
            //$('.page').html(this.el)
        }
    }
})

var TagRow = Backbone.View.extend({
    tagName : 'li',
    className : 'row',
    template : PA.jst.tagLinks,
    tag : PA.jst.tag,
    render : function() {
        this.$el.html( this.template({ 
            type : this.options.type
        }) )
        this.options.tags.forEach( function(tag) {
            this.$('#tagLinks').append( this.tag({ tag : tag }) )
        }, this )

        return this.el
    }
})

// instantiated with el : #details
var ProjectDetails = Backbone.View.extend({
    events : {},
    template : PA.jst.projectDetails,
    render : function(options) {
        this.$el.html( this.template({
            htmlDate : new Date( parseInt(this.model.get('date'),10) ).getFullYear(),
            date : new Date( parseInt(this.model.get('date'),10) ).getFullYear(),
            title : this.model.get('title'),
            summary : this.model.get('summary')
        }) )

        return this.el
    }
})

var SingleView = Backbone.View.extend({
    tagName : "div",
    className : "project viewer",
    baseTmpl : PA.jst.viewer,
    initialize : function() {
        this.$el.html( this.baseTmpl() )
        this.details = new ProjectDetails({ 
            el : this.$('#details'),
            model : this.model
        })
        this.showcase = new PA.ShowcaseContainer({ el : this.$('#showcaseContainer') })
    },
    render: function(options) {
        this.details.render()

        var showcases = this.model.get('showcases')
        showcases.forEach( function(showcase) {
            this.$('#showcaseLinks')
                .prepend( PA.jst.showcaseLinks({
                    cid : showcase.cid,
                    title : showcase.get('title')
                }) )
        }, this )
        this.$('#tags')
            .append( new TagRow({ 
                type : 'Brand', 
                tags : this.model.get('brand_tags') 
            }).render() )
            .append( new TagRow({ 
                type : 'Industry', 
                tags : this.model.get('industry_tags') 
            }).render() )
            .append( new TagRow({ 
                type : 'Project Type', 
                tags : this.model.get('type_tags') 
            }).render() )

        return this.el
    },
    events : {
        "click .showcase-links a" : function(e) {
            e.preventDefault()
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
