/*global PA*/
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
    'app/collections/projects',
    'utils/spinner',
    'app/views/partials/filterviews',
    'slick',
    'imagesLoaded'
], function( $, Backbone, _, TPL, V, ProjectModel, Projects, Spinner, FilterBar ) {

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
            if ( !Projects.length ) {
                Projects.add( PA.projects, { parse : true } )
            }
        },

        render : function(o) {
            // initialize custom version of slick slider with this.model.get('media')
            var media = o.project.get('media')

            if ( media.gallery ) {
                _.each(media.gallery.images, function(image, i) {
                    var d = document.createElement('div'),
                        img = new Image()
                    img.src = image.url
                    img.className = 'project-image'
                    d.appendChild(img)
                    this.$el.append(d)
                }, this)
            }

            if ( media.video ) {
                var d = document.createElement('div')
                d.appendChild( new V({
                    model : new Backbone.Model(media.video)
                }).render() )
                this.$el.append(d)
            }

            if ( media.summary ) {
                var d = document.createElement('div'),
                    summary = document.createElement('div')

                summary.innerHTML = media.summary
                summary.className = 'project-summary'
                d.appendChild(summary)
                this.$el.append(d)
            }

            $('#showcaseContainer').imagesLoaded().progress(function(il, image) {
                image.img.classList.add('loaded')
            })

            this.$el.slick({
                dots : true,
                fade : true,
                onInit : function() {
                    this.resizeHandler()
                    this.galleryControls()
                    setTimeout( function() {
                        $('.project-controls').addClass('loaded')
                    }, 200 )
                    $(window).on('resize', _.debounce(this.resizeHandler, 50, false))
                    $(window).on('keyup', this.keyHandler.bind(this))
                }.bind(this), // force bind b/c slick binds this to the slick object
                onBeforeChange : function(s, i) {},
                onAfterChange : function(s, i) {
                    $('#dot').animate({
                        left: $('.slick-dots li').eq(s.currentSlide).position().left
                    })
                }
            })
        },

        resizeHandler : function() {
            var targetHeight = $('#showcaseContainer').height(),
                $slickTarget = $('.slick-list')

            $slickTarget.height(targetHeight)
            $slickTarget.find('iframe').height(targetHeight)
            $slickTarget.find('.video').width(targetHeight / 0.5625)
        },

        keyHandler : function(e) {
            var key = e.which
            if ( key === 39 ) { // right arrow
                this.$el.slickNext()
            } else if ( key === 37 ) { // left arrow
                this.$el.slickPrev()
            } else if ( key === 27 ) { // excape key
                this.trigger('close')
            }
        },

        galleryControls : function() {
            var $dot = $('<div/>').attr('id','dot').addClass('dot'),
                $controls = $('<div/>').addClass('project-controls')

            $('.slick-dots').addClass('project-dots').append($dot).appendTo($controls)
            $controls.prepend( $('.slick-prev'), $('.slick-next'))
            $('#details').append($controls)
        }
    })

    var Details = Backbone.View.extend({
        events : {},
        template : TPL.projectDetails,
        render : function(o) {

            this.$el.html( this.template({
                htmlDate    : o.project.get('htmlDate'),
                date        : o.project.get('date').year(),
                title       : o.project.get('title')
            }) )

            this.$('#tags')
                .append( new TagRow({
                    type : 'Brand',
                    tags : o.project.get('brand_tags')
                }).render() )
                .append( new TagRow({
                    type : 'Industry',
                    tags : o.project.get('industry_tags')
                }).render() )
                .append( new TagRow({
                    type : 'Project Type',
                    tags : o.project.get('type_tags')
                }).render() )
        }
    })

    var ProjectView = Backbone.View.extend({
        tagName : "div",
        className : "project viewer",
        baseTmpl : TPL.viewer,
        initialize : function() {
            _.bindAll(this, 'render', 'renderOut', 'onClose' )

            this.$back = $('<button/>').attr({
                id : 'back',
                class : 'project-back'
            }).text('X')
            $(document).on('click', '#back', this.goBack.bind(this))
        },

        onClose : function() {
            $(window).off('resize')
            $(window).off('keyup')
            $(document).off('click', '#back', this.goBack)
            $('.page').removeClass('project-single')
            $('#nav').removeClass('is-notvisible')
            this.filterbar.close()
            this.$back.remove()
        },

        render : function( projectUrl, hidden, previous ) {
            $('.page').addClass('project-single')
            $('#nav').addClass('is-notvisible')
            this.$el.html( this.baseTmpl() )

            this.details = new Details({
                el : this.$('#details').addClass('details--project')
            })

            this.viewer = new ProjectGallery({
                className : 'project-gallery'
            })
            this.viewer.on('close', this.goBack.bind(this))

            this.filterbar = new FilterBar({
                el : '#filter-bar',
                collection : this.collection,
                parentSection : 'projects',
                previous : previous ? $.deparam.fragment(previous.hash) : null
            })

            this.$('#showcaseContainer').addClass('container--project').append(this.viewer.el)
            this.$back.appendTo('.site-header')

            this.delegateEvents()
            this.previous = previous ? $.deparam.fragment(previous.hash) : null

            if ( this.collection.length ) {
                this.model = this.collection.findWhere({ 'url-title' : projectUrl })
                setTimeout(this.renderOut, 0)
            } else {
                this.model.fetch({
                    url : '/api/projects/' + projectUrl + ( hidden ? '/private' : '' ),
                    success : this.renderOut
                })
            }

            return this.el
        },

        renderOut : function( model, response, ops ) {
            $('#showcaseContainer').after($('#details'))
            this.details.render({ project : this.model })
            this.filterbar.render()
            this.filterbar.delegateEvents()

            if ( _.isEmpty(this.model.get('media')) ) {
                this.viewer.$el.html('<p>No media for this project</p>')
            } else {
                this.viewer.render({ project : this.model })
            }

            this.trigger('rendered')
        },

        goBack : function(e) {
            if (e) {
                e.preventDefault()
            }
            if (this.previous) {
                history.go(-1)
            } else {
                Backbone.dispatcher.trigger( 'navigate:section', '/projects' )
            }
        }
    })

    return new ProjectView({
        collection : Projects
    })
})
