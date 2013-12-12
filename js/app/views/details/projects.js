/* app/views/details/project.js
 * detail view for projects */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcases/gallery',
    'app/views/showcases/text',
    'app/views/showcases/list',
    'app/views/showcases/video',
    'app/models/project',
    'utils/spinner'
], function( $, Backbone, _, TPL, G, T, l, V, ProjectModel, Spinner ) {

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
                        showcase = new G({
                            collection : model.get('gallery'),
                            model : model
                        })
                        break;

                    case 'video':
                        showcase = new V({ model : model })
                        break;

                    case 'info':
                        showcase = new T()
                        showcase.$el.append( showcase.base({
                            type : '.project-info',
                            content : model.get('content')
                        }) )
                        break;

                    case 'related':
                        showcase = new l.SmList({
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

            if ( this.collection.length > 1 && this.collection.at(0).get('images').length > 1 ) {
                this.collection.forEach( function(showcase) {
                    this.$('#showcaseLinks')
                        .append( new Link({
                            model : showcase
                        }).render() )
                }, this )
            }

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
        },

        events : {
            'click #back' : 'goBack'
        },

        render : function( projectUrl, showcaseUrl, previous ) {
            this.$el.html( this.baseTmpl() )

            this.details = new Details({
                el : this.$('#details'),
                model : this.model
            })

            this.viewer = new Showcase({
                el : this.$('#showcaseContainer')
            })

            this.delegateEvents()
            this.previous = previous

            this.model.fetch({
                url : '/api/projects/' + projectUrl + (document.location.href.match(/private$/) ? '/private' : ''),
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

            this.$('#details').prepend( this.back({
                buttonText : 'Back to All Projects',
                url : this.previous ? '/projects' + this.previous.hash : '/projects'
            }) )

            try {
                if ( this.collection.at(0).get('images').length > 1 ) {
                    this.collection.at(0).activate()
                } else if ( this.collection.length > 1 ) {
                    this.collection.at(1).activate(true)
                } else {
                    throw 'NoMedia'
                }

                this.trigger('rendered')
                if ( this.collection.findWhere({ active : true }).get('type') === 'gallery') {
                    var projectTitle = this.model.get('title')
                    $('#showcaseContainer a').each(function(idx, el) {
                        $(el).attr('title', ( el.title ? projectTitle + ': ' + el.title : projectTitle ))
                    })
                }
            } catch(e) {
                this.viewer.$el.html('<p>No media for this project</p>')
                this.trigger('rendered')
            }
        },

        swap : function(showcase) {
            this.collection.findWhere({ active : true }).deactivate()
            showcase.activate()
        },

        goBack : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger( 'navigate:section', e )
            Backbone.dispatcher.trigger( 'goBack', new Spinner(), 'projects' )
        }
    })

    return new ProjectView()
})
