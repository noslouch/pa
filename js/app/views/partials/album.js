/* app/views/partials/album.js
 * detail view for image galleries in photography and books */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcases/video',
    'app/views/showcases/gallery',
    'app/views/partials/filterviews',
    'utils/spinner',
    'slick',
    'imagesLoaded'
], function( $, Backbone, _, TPL, V, G, FilterBar, Spinner ) {

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
                    section : this.options.section,
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

    var Details = Backbone.View.extend({
        events : {
            'click a' : function(e) {
                e.preventDefault()
                Backbone.dispatcher.trigger('navigate:section', e, this)
            }
        },

        template : TPL.projectDetails,
        render : function(options) {

            this.$el.html( this.template({
                htmlDate    : this.model.get('htmlDate'),
                date        : this.model.get('date').year(),
                title       : this.model.get('title')
            }) )

            if ( this.model.get('brand_tags').length ) {
                this.$('#tags')
                .append( new TagRow({
                    section : options.section,
                    type : 'Brand',
                    tags : this.model.get('brand_tags')
                }).render() )
            }
            if ( this.model.get('industry_tags').length ) {
                this.$('#tags')
                .append( new TagRow({
                    section : options.section,
                    type : 'Industry',
                    tags : this.model.get('industry_tags')
                }).render() )
            }
            if ( this.model.get('type_tags').length ) {
                this.$('#tags')
                .append( new TagRow({
                    section : options.section,
                    type : 'Project Type',
                    tags : this.model.get('type_tags')
                }).render() )
            }
        }
    })

    var Gallery = Backbone.View.extend({
        initialize: function() {
            _.bindAll(this, 'keyHandler', 'next', 'goToSlide')

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
                onInit : function(slider) {
                    this.resizeHandler()
                    this.galleryControls(slider)
                    setTimeout( function() {
                        $('.project-controls').addClass('loaded')
                    }, 400 )
                    $(window).on('resize', _.debounce(this.resizeHandler, 50, false))
                    $(window).on('keyup', this.keyHandler)
                    $('.slick-slide').click(this.next)
                }.bind(this), // force bind b/c slick binds this to the slick object
                onBeforeChange : function(s, i) {
                    // ideally we should change the dropdown menu here
                    // but we don't know which directin the gallery is moving
                },
                onAfterChange : function(s, i) {
                    $('#chooseSlide').val(i)
                    $('#dot').animate({
                        left: $('.slick-dots li').eq(s.currentSlide).position().left
                    })
                }
            })
        },

        next : function() {
            this.$el.slickNext()
        },

        goToSlide : function(e) {
            this.$el.slickGoTo( parseInt(e.currentTarget.value, 10) )
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

        galleryControls : function(slider) {
            var $dot = $('<div/>').attr('id','dot').addClass('dot'),
                $controls = $('<div/>').addClass('project-controls'),
                $dropdown, $option

            if ( slider.slideCount > 40 ) {
                $('.slick-dots').hide()
                $dropdown = $('<select />', { id : 'chooseSlide' }).addClass('project-dropdown')
                for (var i = 0; i < slider.slideCount; i++) {
                    $option = $('<option />').val(i).text(i+1)
                    $dropdown.append($option).change(this.goToSlide)
                }
                $controls.append( $dropdown, $('.slick-prev'), $('.slick-next') ).addClass('project-controls--nodots')
            } else {
                $('.slick-dots').addClass('project-dots').append($dot).appendTo($controls)
                $controls.prepend( $('.slick-prev'), $('.slick-next'))
            }
            $('#details').append($controls)
        }
    })


    var AlbumView = Backbone.View.extend({
        className : 'detail viewer',
        tagName : "div",
        baseTmpl : TPL.viewer,
        initialize : function(ops) {
            _.bindAll( this, 'render', 'renderOut', 'onClose', 'goBack' )

            this.section = ops.section

            this.$back = $('<button/>').attr({
                id : 'back',
                class : 'detail-back'
            }).text('X')

            this.filterbar = new FilterBar({
                el : '#filter-bar',
                collection : this.collection,
                parentSection : this.section
            })
        },

        render : function( urlTitle, hidden, previous ) {
            this.previous = previous ? $.deparam.fragment(previous.hash) : null

            $('body').addClass('detail-view')

            $('#nav').addClass('is-notvisible')
            $(document).on('click', '#back', this.goBack)

            this.$el.html( this.baseTmpl() )

            this.$back.appendTo('.site-header')

            this.delegateEvents()

            if ( this.collection.length ) {
                this.model = this.collection.findWhere({ 'url-title' : urlTitle })
                setTimeout(this.renderOut, 0)
            } else {
                this.model.fetch({
                    url : '/api/' + this.section + '/' + urlTitle + ( hidden ? '/private' : '' ),
                    success : this.renderOut
                })
            }

            return this.el
        },

        renderOut : function( model, response, ops ) {

            $('<p class="copyright" />')
                .text('Copyright ' + new Date().getFullYear() + ', Peter Arnell')
                .attr('id', 'copyright')
                .appendTo($('.page'))

            this.details = new Details({
                el : this.$('#details').addClass('details--detail'),
                model : this.model
            }).render({ section : this.section })

            this.viewer = new Gallery({
                className : 'slideshow',
                model : this.model
            }).on('close', this.goBack)

            this.filterbar.render({
                mixitup : false,
                previous : this.previous
            })

            this.filterbar.delegateEvents()

            this.$('#showcaseContainer')
                .addClass('container--detail')
                .append(this.viewer.el)

            // can change in TPL
            $('#showcaseContainer').after($('#details'))

            if ( _.isEmpty(this.model.get('media')) ) {
                this.viewer.$el.html('<p>No media for this project</p>')
            } else {
                this.viewer.render({ model : this.model })
            }

            this.trigger('rendered')
        },

        onClose : function() {
            $(window).off('resize')
            $(window).off('keyup')
            $(document).off('click', '#back', this.goBack)
            $('body').removeClass('detail-view')
            $('#copyright').remove()
            $('#nav').removeClass('is-notvisible')
            this.filterbar.close()
            this.$back.remove()
        },

        goBack : function(e) {
            if (e) {
                e.preventDefault()
            }
            if (this.previous) {
                history.go(-1)
            } else {
                Backbone.dispatcher.trigger( 'navigate:section', '/' + this.section )
            }
        }
    })

    return AlbumView
})
