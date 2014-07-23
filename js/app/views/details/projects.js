/* app/views/details/project.js
 * detail view for projects */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcases/video',
    'app/models/project',
    'utils/spinner',
    'slick'
], function( $, Backbone, _, TPL, V, ProjectModel, Spinner ) {

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

    var ProjectGallery = Backbone.View.extend({
        initialize: function() {
            if ( !$('#slickCSS').length ) {
                var link = document.createElement("link");
                link.id = 'slickCSS'
                link.type = "text/css";
                link.rel = "stylesheet";
                link.href = '/bower_components/slick-carousel/slick/slick.css'
                document.getElementsByTagName("head")[0].appendChild(link);
            }
        },
        render : function() {
            // initialize custom version of slick slider with this.model.get('media')
            var media = this.model.get('media')

            if (media.gallery) {
                _.each(media.gallery.images, function(image, i) {
                    var d = document.createElement('div'),
                        img = new Image()
                    img.src = image.url
                    img.className = 'project-image'
                    d.appendChild(img)
                    this.$el.append(d)
                }, this)
            }

            this.$el.slick({
                dots : true,
                fade : true,
                onInit : function() {
                    this.resizeHandler()
                    this.galleryControls()
                    $(window).on('resize', _.debounce(this.resizeHandler, 50, false))
                    $(window).on('keyup', this.keyHandler.bind(this))
                }.bind(this),
                onBeforeChange : function(s, i) {},
                onAfterChange : function(s, i) {
                    $('#dot').animate({
                        left: $('.slick-dots li').eq(s.currentSlide).position().left
                    })
                }
            })
        },

        resizeHandler : function() {
            var targetHeight = $('#showcaseContainer').height()
            $('.slick-list').height(targetHeight)
        },

        keyHandler : function(e) {
            var key = e.which
            if ( key === 39 ) { // right arrow
                this.$el.slickNext()
            } else if ( key === 37 ) { // left arrow
                this.$el.slickPrev()
            } else if ( key === 27 ) { // excape key
                //close()
            }
        },

        galleryControls : function() {
            var $dot = $('<div/>').attr('id','dot').addClass('dot'),
                $controls = $('<div/>').addClass('project-controls')

            $('.slick-dots').addClass('project-dots').append($dot).appendTo($controls)
            $controls.prepend( $('.slick-prev'), $('.slick-next'))
            $('#details').append($controls)
        },

        onClose : function() {}
    })

// NO MORE SHOWCASES
// var Showcase = Backbone.View.extend({
//     initialize : function() {
//         _.bindAll( this, 'render' )
//     },
//     render : function(model, value, options) {
//         if (value) {

//             var showcase
//             switch( model.get('type') ) {
//                 case 'gallery':
//                     model.set('page', this)
//                     showcase = new G({
//                         collection : model.get('gallery'),
//                         model : model
//                     })
//                     break;

//                 case 'video':
//                     showcase = new V({ model : model })
//                     break;

//                 case 'info':
    //                     showcase = new T()
    //                     showcase.$el.append( showcase.base({
    //                         type : '.project-info',
    //                         content : model.get('content')
    //                     }) )
    //                     break;

    //                 case 'related':
    //                     showcase = new l.SmList({
    //                         collection : model.get('links')
    //                     })
    //                     break;

    //                 default:
    //                     break;
    //             }

    //             this.$el.html( showcase.render({gallery : true}) )

    //             try {
    //                 showcase.firstLoad()
    //             } catch(e1) {}
    //         }
    //     }
    // })

    // no more showcases
    // var Link = Backbone.View.extend({
    //     tagName : 'li',
    //     template : TPL.showcaseLinks,
    //     initialize: function() {
    //         _.bindAll( this, 'toggleView', 'toggleModel')
    //     },

    //     events : {
    //         'click a' : 'toggleModel'
    //     },

    //     render : function() {
    //         this.listenTo(this.model, 'change:active', this.toggleView)
    //         var title
    //         switch( this.model.get('type') ) {
    //             case 'gallery':
    //                 title = 'Gallery'
    //                 break;
    //             case 'video':
    //                 title = 'Video'
    //                 break;
    //             default:
    //                 title = this.model.get('title')
    //                 break;
    //         }

    //         var html = this.template({
    //             cid : this.model.cid,
    //             title : title
    //         })

    //         this.$el.html(html)
    //         return this.el
    //     },

    //     toggleView : function(model, value, options) {
    //         this.$('a').toggleClass('active', value)
    //     },

    //     toggleModel : function(e) {
    //         e.preventDefault()
    //         this.model.trigger('swap', this.model)
    //     }
    // })

    var Details = Backbone.View.extend({
        events : {},
        template : TPL.projectDetails,
        render : function(options) {

            this.$el.html( this.template({
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year(),
                title : this.model.get('title')
            }) )

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
            _.bindAll(this, 'render', 'renderOut', 'onClose' )
            this.model = new ProjectModel()
        },

        events : {
            'click #back' : 'goBack'
        },

        onClose : function() {
            $(window).off('resize', this.viewer.resizeHandler)
            $(window).off('keyup', this.viewer.keyHandler)
            $('.page').removeClass('project-single')
        },

        render : function( projectUrl, hidden, previous ) {
            $('.page').addClass('project-single')
            this.$el.html( this.baseTmpl() )

            this.details = new Details({
                el : this.$('#details').addClass('details--project'),
                model : this.model
            })

            this.viewer = new ProjectGallery({
                className : 'project-gallery',
                model : this.model
            })

            this.$('#showcaseContainer').addClass('container--project').append(this.viewer.el)

            this.delegateEvents()
            this.previous = previous

            // TODO skip fetch if we already have all the projects
            this.model.fetch({
                url : '/api/projects/' + projectUrl + ( hidden ? '/private' : '' ),
                success : this.renderOut
            })

            return this.el
        },

        renderOut : function( model, response, ops ) {
            $('#showcaseContainer').after($('#details'))
            this.details.render()

            // TODO make this the close button
            // this.$('#details').prepend( this.back({
            //     buttonText : 'Back to All Projects',
            //     url : this.previous ? '/projects' + this.previous.hash : '/projects'
            // }) )

            try {
                // new kind of showcases
                if ( _.isEmpty(this.model.get('media')) ) {
                    throw 'NoMedia'
                } else {
                    this.viewer.render()
                }

                // detach spinner
                this.trigger('rendered')

                // different kind of showcase
                // if ( this.collection.findWhere({ active : true }).get('type') === 'gallery') {
                //     var projectTitle = this.model.get('title')
                //     $('#showcaseContainer a').each(function(idx, el) {
                //         $(el).attr('title', ( el.title ? projectTitle + ': ' + el.title : projectTitle ))
                //     })
                // }
            } catch(e) {
                this.viewer.$el.html('<p>No media for this project</p>')
                this.trigger('rendered')
            }
        },

        // no more showcases
        // swap : function(showcase) {
        //     this.collection.findWhere({ active : true }).deactivate()
        //     showcase.activate()
        // },

        goBack : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger( 'navigate:section', e )
            //Backbone.dispatcher.trigger( 'goBack', new Spinner(), 'projects' )
        }
    })

    return new ProjectView()
})
