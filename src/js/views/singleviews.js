'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.TagRow = Backbone.View.extend({
    tagName : 'li',
    className : 'row',
    template : PA.jst.tagLinks,
    tag : PA.jst.tag,
    render : function() {
        this.$el.html( this.template({ 
            type : this.options.type
        }) )
        this.options.tags.forEach( function(tag, index, tags) {
            this.$('#tagLinks').append( this.tag({ 
                tag : tag.title,
                className : tag.className
            }) )
            if ( index < tags.length -1 ) { 
                this.$('#tagLinks').append( ', ' )
            }
        }, this )

        return this.el
    }
})

PA.ShowcaseViewer = Backbone.View.extend({
    initialize : function() {
        this.listenTo( this.collection, 'change:active', this.render )
    },

    render : function(model, value, options) {
        if (value) {

            var showcase

            switch( model.get('type') ) {
                case 'gallery':
                    showcase = new PA.ImageShowcase({
                        collection : model.get('gallery')
                    })
                    break;
                case 'video':
                    showcase = new PA.VideoShowcase({ model : model })
                    break;
                case 'info':
                    showcase = new PA.TextShowcase()
                    break;
                case 'related':
                    showcase = new PA.ListShowcase()
                    break;
                default:
                    break;
            }

            this.$el.html( showcase.render() )

            try {
                showcase.firstLoad()
            } catch(e1) {}
        }
    }
})

PA.ShowcaseLink = Backbone.View.extend({
    tagName : 'li',
    initialize: function() {
        _.bindAll( this, 'toggleView', 'toggleModel')

        this.listenTo(this.model, 'change:active', this.toggleView)
    },

    events : {
        'click a' : 'toggleModel'
    },

    toggleModel : function(e) {
        e.preventDefault()
        this.model.trigger('swap', this.model)
    },

    render : function() {
        var html = this.template({
            cid : this.model.cid,
            title : this.model.get('title')
        })

        this.$el.html(html)
        return this.el
    },

    template : PA.jst.showcaseLinks,

    toggleView : function(model, value, options) {
        this.$('a').toggleClass('active', value)
    }
})

PA.ProjectDetails = Backbone.View.extend({
    events : {},
    template : PA.jst.projectDetails,
    render : function(options) {
        this.$el.html( this.template({
            date : this.model.get('date'),
            htmlDate : this.model.get('date'),
            //htmlDate : this.model.get('htmlDate'),
            //date : this.model.get('date').year(),
            title : this.model.get('title'),
            summary : this.model.get('summary')
        }) )

        return this.el
    }
})

PA.ProjectViewer = Backbone.View.extend({
    tagName : "div",

    className : "project viewer",

    baseTmpl : PA.jst.viewer,

    back : PA.jst.backButton,

    initialize : function() {

        _.bindAll(this, 'swap')

        this.$el.html( this.baseTmpl() )

        this.showcases = this.model.get('showcases')

        this.listenTo( this.showcases, 'swap', this.swap  )

        this.details = new PA.ProjectDetails({ 
            el : this.$('#details'),
            model : this.model
        })

        this.viewer = new PA.ShowcaseViewer({
            el : this.$('#showcaseContainer'),
            collection : this.showcases
        })

    },

    render: function(options) {

        this.details.render()

        this.showcases.forEach( function(showcase) {
            this.$('#showcaseLinks')
                .append( new PA.ShowcaseLink({ 
                    model : showcase 
                }).render() )
        }, this )

        this.$('#tags')
            .append( new PA.TagRow({ 
                type : 'Brand', 
                tags : this.model.get('brand_tags') 
            }).render() )
            .append( new PA.TagRow({ 
                type : 'Industry', 
                tags : this.model.get('industry_tags') 
            }).render() )
            .append( new PA.TagRow({ 
                type : 'Project Type', 
                tags : this.model.get('type_tags') 
            }).render() )

        this.$('#details').append( this.back({
            buttonText : 'Back to Projects',
            url : '/projects'
        }) )

        return this.el
    },

    swap : function(showcase){
        this.showcases.findWhere({ active : true }).deactivate()
        showcase.activate()
    }

})

PA.AlbumDetails = Backbone.View.extend({
    template : PA.jst.textTemplate, // type, content
    header : PA.jst.textTemplateHeader, // title, htmlDate, date
    back : PA.jst.backButton, // buttonText, url
    render : function() {
        var $article = $( this.template({
            type : 'photo',
            content : this.model.get('summary')
        }) ).prepend( this.header({
            title : this.model.get('title'),
            htmlDate : this.model.get('htmlDate'),
            date : this.model.get('date')
        }) ).append( this.back({
            buttonText : 'View All Photo Albums',
            url : '/photography'
        }) )

        this.$el.append($article)
    }
})

PA.SingleAlbumView = Backbone.View.extend({
    tagName : 'div',
    className : 'photo viewer',
    baseTmpl : PA.jst.viewer,
    initialize : function() {
        this.$el.html( this.baseTmpl() )
        this.details = new PA.AlbumDetails({
            el : this.$('#details'),
            model : this.model
        })
    },
    render : function(options) {
        this.details.render()

        var gallery = new PA.ImageShowcase({
            collection : this.model.get('photos')
        })
        this.$('#showcaseContainer').html( gallery.render() )
        gallery.firstLoad()

        return this.el
    }
})

PA.FilmDetails = Backbone.View.extend({
    template : PA.jst.textTemplate, // type, content
    header : PA.jst.textTemplateHeader, // title, htmlDate, date
    back : PA.jst.backButton, // buttonText, url
    render : function() {
        var $article = $( this.template({
            type : 'film',
            content : this.model.get('content')
        }) )
        $article.prepend( this.header({
            title : this.model.get('title'),
            htmlDate : this.model.get('htmlDate'),
            date : this.model.get('date').year(),
        }) ).append( this.back({
            buttonText : 'View All Film',
            url : '/film'
        }) )

        this.$el.append($article)
    }
})

PA.SingleFilmView = Backbone.View.extend({
    tagName : 'div',
    className : 'film viewer',
    baseTmpl : PA.jst.viewer,
    initialize : function() {
        this.$el.html( this.baseTmpl() )
        this.details = new PA.FilmDetails({
            el : this.$('#details'),
            model : this.model
        })
    },
    render : function(options) {
        this.details.render()
        this.$('#showcaseContainer').html( new PA.VideoShowcase({
            model : this.model
        }).render() )

        return this.el
    }
})
