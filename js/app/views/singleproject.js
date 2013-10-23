/* app/views/singleproject.js
 * detail view for projects */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcaseviews',
    'app/models/project'
], function( $, Backbone, _, TPL, S, ProjectModel ) {

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
            _.bindAll( this, 'render' )
        },
        render : function(model, value, options) {
            if (value) {

                var showcase
                switch( model.get('type') ) {
                    case 'gallery':
                        model.set('page', this)
                        showcase = new S.Image({
                            collection : model.get('gallery'),
                            model : model
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

        },

        events : {
            'click a' : 'toggleModel'
        },

        render : function() {
            this.listenTo(this.model, 'change:active', this.toggleView)
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
        },

        toggleView : function(model, value, options) {
            this.$('a').toggleClass('active', value)
        },

        toggleModel : function(e) {
            e.preventDefault()
            this.model.trigger('swap', this.model)
        }
    })

    var Details = Backbone.View.extend({
        events : {},
        template : TPL.projectDetails,
        render : function(options) {
            //this.model = options.model
            this.collection = options.collection

            this.$el.html( this.template({
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year(),
                title : this.model.get('title'),
                summary : this.model.get('summary')
            }) )

            this.collection.forEach( function(showcase) {
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
        }
    })

    var ProjectView = Backbone.View.extend({
        tagName : "div",
        className : "project viewer",
        baseTmpl : TPL.viewer,
        back : TPL.backButton,
        initialize : function() {
            _.bindAll(this, 'swap', 'render', 'renderOut' )
            this.model = new ProjectModel()
            this.$el.html( this.baseTmpl() )

            this.details = new Details({
                el : this.$('#details'),
                model : this.model
            })

            this.viewer = new Showcase({
                el : this.$('#showcaseContainer')
            })
        },

        events : {
            'click #back' : 'goBack'
        },

        render : function( projectUrl, showcaseUrl, previous ) {
            this.delegateEvents()
            this.details.$el.empty()
            this.viewer.$el.empty()
            this.previous = previous

            this.model.fetch({
                url : '/api/projects/' + projectUrl,
                success : this.renderOut,
                showcaseUrl : showcaseUrl
            })

            return this.el
        },

        renderOut : function( model, response, ops ) {
            this.collection = this.model.get('showcases')
            this.collection.on( 'change:active', this.viewer.render )
            this.collection.on( 'swap', this.swap )
            //this.viewer.listenTo( this.collection, 'change:active', this.viewer.render )
            //this.listenTo( this.collection, 'swap', this.swap  )

            this.details.render({
                collection : this.collection
            })

            this.$('#details').append( this.back({
                buttonText : 'Back to Projects',
                url : this.previous ? '/projects' + this.previous.hash : '/projects'
            }) )

            if ( ops.showcaseUrl ) {
                this.collection.findWhere({ url_title : ops.showcaseUrl }).activate()
            } else {
                this.collection.first().activate(true)
            }

            this.trigger('rendered')
        },

        swap : function(showcase) {
            this.collection.findWhere({ active : true }).deactivate()
            showcase.activate()
        },

        goBack : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:section', e)
            Backbone.dispatcher.trigger('projects:goBack')
        }
    })

    return new ProjectView()
})
