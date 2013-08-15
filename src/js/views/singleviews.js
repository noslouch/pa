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
        this.options.tags.forEach( function(tag) {
            this.$('#tagLinks').append( this.tag({ tag : tag }) )
        }, this )

        return this.el
    }
})

PA.ProjectDetails = Backbone.View.extend({
    events : {},
    template : PA.jst.projectDetails,
    render : function(options) {
        this.$el.html( this.template({
            //htmlDate : new Date( parseInt(this.model.get('date'),10) ).getFullYear(),
            //date : new Date( parseInt(this.model.get('date'),10) ).getFullYear(),
            htmlDate : this.model.get('htmlDate'),
            date : this.model.get('date').getFullYear(),
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
    initialize : function() {
        _.bindAll(this, 'showcaseHandler')

        this.$el.html( this.baseTmpl() )
        this.details = new PA.ProjectDetails({ 
            el : this.$('#details'),
            model : this.model
        })
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

        this.showcaseHandler( this.$('#showcaseLinks li:first-child a')[0].id )

        return this.el
    },
    events : {
        "click .showcase-links a" : function(e) {
            e.preventDefault()
            this.showcaseHandler(e.currentTarget.id)
        }
    },
    showcaseHandler : function(id) {
        var showcaseModel = this.model.get('showcases').get(id),
            showcase

        switch( showcaseModel.get('type') ) {
            case 'gallery':
                showcase = new PA.ImageShowcase({
                    collection : showcaseModel.get('gallery')
                })
                break;
            case 'video':
                showcase = new PA.VideoShowcase({ model : showcaseModel })
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

        this.$('#showcaseContainer').html( showcase.render() )

        try {
            showcase.firstLoad()
        } catch(e1) {}
    }
})

PA.AlbumDetails = Backbone.View.extend({
    template : PA.jst.textTemplate, // type, content
    header : PA.jst.textTemplateHeader, // title, htmlDate, date
    back : PA.jst.backButton, // buttonText, url
    render : function() {
        var $article = $( this.template({
            type : 'photo',
            content : this.model.get('content')
        }) ).prepend( this.header({
            title : this.model.get('title'),
            //htmlDate : this.makeHtmlDate( this.model.get('date') ),
            //date : this.parseDate( this.model.get('date') ).getFullYear()
            htmlDate : this.model.get('htmlDate'),
            date : this.model.get('date').getFullYear()
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
            //htmlDate : this.makeHtmlDate( this.model.get('date') ),
            //date : this.parseDate( this.model.get('date') ).getFullYear()
            htmlDate : this.model.get('htmlDate'),
            date : this.model.get('date').getFullYear()
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
