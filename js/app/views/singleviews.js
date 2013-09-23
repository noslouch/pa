/* app/views/singleviews.js
 * detail view for projects, photo galleries, and films */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcaseviews'
], function( $, Backbone, _, TPL, S ) {

    var viewers = {}

    var TagRow = Backbone.View.extend({
        tagName : 'li',
        className : 'row',
        template : TPL.tagLinks,
        tag : TPL.tag,
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

    var Showcase = Backbone.View.extend({
        initialize : function() {
            this.listenTo( this.collection, 'change:active', this.render )
        },

        render : function(model, value, options) {
            if (value) {

                var showcase
                switch( model.get('type') ) {
                    case 'gallery':
                        showcase = new S.Image({
                            collection : model.get('gallery')
                        })
                        break;

                    case 'video':
                        showcase = new S.Video({ model : model })
                        break;

                    case 'info':
                        showcase = new S.Text()
                        showcase.$el.append( showcase.base({
                            type : '.project-info',
                            content : model.get('content')
                        }) )
                        break;

                    case 'related':
                        showcase = new S.SmallList({
                            collection : model.get('links')
                        })
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

    var Link = Backbone.View.extend({
        tagName : 'li',
        template : TPL.showcaseLinks,
        initialize: function() {
            _.bindAll( this, 'toggleView', 'toggleModel')

            this.listenTo(this.model, 'change:active', this.toggleView)
        },

        events : {
            'click a' : 'toggleModel'
        },

        toggleView : function(model, value, options) {
            this.$('a').toggleClass('active', value)
        },

        toggleModel : function(e) {
            e.preventDefault()
            this.model.trigger('swap', this.model)
        },

        render : function() {
            var title
            switch( this.model.get('type') ) {
                case 'gallery':
                    title = 'Gallery'
                    break;
                case 'video':
                    title = 'Video'
                    break;
                default:
                    title = this.model.get('title')
                    break;
            }

            var html = this.template({
                cid : this.model.cid,
                title : title
            })

            this.$el.html(html)
            return this.el
        }
    })

    var Details = Backbone.View.extend({
        events : {},
        template : TPL.projectDetails,
        render : function(options) {
            this.$el.html( this.template({
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year(),
                title : this.model.get('title'),
                summary : this.model.get('summary')
            }) )

            return this.el
        }
    })

    viewers.Project = Backbone.View.extend({
        tagName : "div",
        className : "project viewer",
        baseTmpl : TPL.viewer,
        back : TPL.backButton,
        initialize : function() {
            _.bindAll(this, 'swap')
            this.$el.html( this.baseTmpl() )
            this.showcases = this.model.get('showcases')
            this.listenTo( this.showcases, 'swap', this.swap  )

            this.details = new Details({
                el : this.$('#details'),
                model : this.model
            }).render()

            this.viewer = new Showcase({
                el : this.$('#showcaseContainer'),
                collection : this.showcases
            })

            this.showcases.forEach( function(showcase) {
                this.$('#showcaseLinks')
                    .append( new Link({
                        model : showcase
                    }).render() )
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

            this.$('#details').append( this.back({
                buttonText : 'Back to Projects',
                url : '/projects'
            }) )

            //return this.el
        },

        render: function(options) {
            return this.el
            //this.viewer.render(options)
        },

        swap : function(showcase){
            this.showcases.findWhere({ active : true }).deactivate()
            showcase.activate()
        }
    })

    var AlbumDetails = Backbone.View.extend({
        template : TPL.textTemplate, // type, content
        header : TPL.textTemplateHeader, // title, htmlDate, date
        back : TPL.backButton, // buttonText, url
        render : function() {
            var $article = $( this.template({
                type : 'photo',
                content : this.model.get('summary')
            }) ).prepend( this.header({
                title : this.model.get('title'),
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year()
            }) ).append( this.back({
                buttonText : 'Back to All Photography',
                url : '/photography'
            }) )

            this.$el.append($article)
        }
    })

    viewers.Album = Backbone.View.extend({
        tagName : 'div',
        className : 'photo viewer',
        baseTmpl : TPL.viewer,

        initialize : function() {
            this.$el.html( this.baseTmpl() )
            this.details = new AlbumDetails({
                el : this.$('#details'),
                model : this.model
            })
        },

        render : function(options) {
            this.details.render()
            var gallery = new S.Image({
                collection : this.model.get('photos')
            })
            this.$('#showcaseContainer').html( gallery.render() )
            gallery.firstLoad()

            return this.el
        }
    })

    var FilmDetails = Backbone.View.extend({
        template : TPL.textTemplate, // type, content
        header : TPL.textTemplateHeader, // title, htmlDate, date
        back : TPL.backButton, // buttonText, url
        render : function() {
            var $article = $( this.template({
                type : 'film',
                content : this.model.get('content')
            }) )
            $article.prepend( this.header({
                title : this.model.get('title'),
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year()
            }) ).append( this.back({
                buttonText : 'View All Film',
                url : '/film'
            }) )

            this.$el.append($article)
        }
    })

    viewers.Film = Backbone.View.extend({
        tagName : 'div',
        className : 'film viewer',
        baseTmpl : TPL.viewer,
        initialize : function() {
            this.$el.html( this.baseTmpl() )
            this.details = new FilmDetails({
                el : this.$('#details'),
                model : this.model
            })
        },
        render : function(options) {
            this.details.render()
            this.$('#showcaseContainer').html( new S.Video({
                model : this.model
            }).render() )

            return this.el
        }
    })

    return viewers
})
