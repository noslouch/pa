/* app/views/showcases/gallery.js
 * PA galleries. tests for mobile and uses swipable gallery if so */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'utils/spinner',
    'is!mobile?utils/touchLoader:utils/fbLoader',
    'isotope',
    'imagesLoaded'
], function( $, Backbone, _, TPL, Spinner, g ) {

    // Thumb
    // Image Showcase thumbnail used in Isotope
    var Thumb = Backbone.View.extend({
        tagName : "div",

        className : function() {
            if ( this.model.has('tags') ){
                var tags = []
                _.each( this.model.get('tags'), function(obj) {
                    tags.push( obj.className )
                }, this )
                return "thumb " + tags.join(' ') + (this.model.get('wide') ? " wide" : "") + " " + this.model.get('year')
            } else {
                return "thumb" + (this.model.get('wide') ? " wide" : "")
            }
        },

        render : function(){
            this.$el.html( this.options.template({
                url : this.options.url,
                caption : this.options.caption,
                year : this.options.year,
                thumb : this.options.thumb,
                id : this.id
            }) )
            return this.el
        }
    })

    // Image
    // Image Showcase container. controls Isotope
    var Image = Backbone.View.extend({
        tagName : 'div',
        id : 'iso-grid',

        initialize : function() {
            _.bindAll(this, 'render', 'filter', 'isotope')

            this.collection.forEach(function(image) {
                var thumb = new Thumb({
                    template : this.options.projects ? TPL.projectCover : TPL.thumbTemplate,
                    url : this.options.projects ? '/projects/' + image.get('url-title') : image.get('url'),
                    caption : this.options.projects ? image.get('title') : image.get('caption'),
                    year : this.options.projects ? image.get('year') : '',
                    thumb : this.collection.length < 5 && !this.options.projects ? image.get('lg_thumb') : image.get('thumb'),
                    id : image.id,
                    model : image
                })
                this.$el.append( thumb.render() )
            }, this)

        },

        className : function() {
            var classes = ['isotope-grid', 'showcase', 'image']
            if (this.options.projects) {
                return classes.concat(['fixed']).join(' ')
            } else if (this.collection.length < 5) {
                return classes.concat(['rtl']).join(' ')
            } else {
                return classes.join(' ')
            }
        },

        render : function(options){
            setTimeout(this.isotope, 0) // triggers post-render callback
            if ( //this.options.path === 'photography' ||
                 this.model.hasChanged('view') ||
                 this.model.get('type') === 'gallery' ) {
                return this.el
            }
        },

        isotope : function() {
            var self = this,
                $img = this.$('img'),
                rtl = this.$el.hasClass('rtl'),
                fixed = this.$el.hasClass('fixed'),
                $el = this.$el,
                isoOps = {
                    isFitWidth : true,
                    itemSelector: '.thumb',
                    layoutMode : fixed ? 'masonry' : 'fitRows',
                    masonry : {
                        gutter: 7,
                        columnWidth: 164
                    },
                    getSortData : {
                        name : '.caption p',
                        date : function(el) {
                            return parseInt( $(el).find('.year').text(), 10 )
                        }
                    }
                }

            function onLayout( iso ) {
                console.log('layout complete')
                $(iso.element).css('overflow', 'visible')
                $('html, body').animate({
                    scrollTop : 0
                })
            }

            if ( this.$el.hasClass('isotope') ) {
                this.$el.isotope(isoOps)
                this.$el.isotope('on', 'layoutComplete', onLayout)
                this.$el.isotope( 'updateSortData', $('.thumb') )
                this.filter( this.model.get('filter') )
                this.sort( this.model.get('sort') )
            } else {
                var spinner = new Spinner()
                this.$el.imagesLoaded( function() {
                    g()
                    $el.isotope(isoOps)
                    $el.isotope('on', 'layoutComplete', onLayout)

                    spinner.detach()
                    $img.addClass('loaded')

                    if ( self.model.has('filter') ) {
                        $el.isotope( 'updateSortData', $('.thumb') )
                        self.filter( self.model.get('filter') )
                        self.sort( self.model.get('sort') )
                        self.model.trigger('layout')
                    }
                })
            }
        },

        filter : function(filter) {
            this.$el.isotope({ filter : filter })
        },

        sort : function(sort) {
            this.$el.isotope({ sortBy : sort })
        }
    })

    return Image
})

