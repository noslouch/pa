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
    'isotope'
], function( $, Backbone, _, TPL, Spinner, g ) {

    // Thumb
    // Image Showcase thumbnail used in Isotope
    var Thumb = Backbone.View.extend({
        tagName : "div",
        template : TPL.thumbTemplate,

        className : function() {
            if (this.options.cover) {
                var tags = []
                _.each( this.model.get('tags'), function(obj) {
                    tags.push( obj.className )
                }, this )
                return "thumb " + tags.join(' ') + (this.model.get('wide') ? " wide" : "") + " " + this.model.get('year')
            } else {
                return "thumb" + (this.model.get('wide') ? " wide" : "") + " " + this.model.get('year')
            }
        },

        render : function(){
            this.$el.html( this.template({
                url : this.options.path ? this.options.path + '/' + this.model.get('url-title') : this.model.get('url'),
                cover : this.options.cover,
                caption : this.options.path === 'projects' ? this.model.get('title') : this.model.get('caption'),
                year : this.options.path === 'projects' ? this.model.get('year') : '',
                thumb : this.model.get('thumb'),
                lg_thumb : this.model.get('lg_thumb'),
                large : this.options.large,
                id : this.model.id
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
                    model : image,
                    cover : this.options.cover ? true : false,
                    large : this.collection.length < 5 && !this.options.cover,
                    path : this.options.path
                })
                this.$el.append( thumb.render() )
            }, this)

            this.on('render', this.isotope)
        },

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

        render : function(options){
            this.trigger('render')
            if ( this.options.path === 'photography' ||
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
                transformsEnabled: !rtl,
                itemSelector: '.thumb',
                layoutMode : fixed ? 'masonry' : 'fitRows',
                masonry : {
                    gutterWidth: 7,
                    columnWidth: rtl ? 164*1.5 : 164
                },
                onLayout : function() {
                    $(this).css('overflow', 'visible')
                },
                getSortData : {
                    name : function($el) {
                        return $el.find('.caption p').text()
                    },
                    date : function($el) {
                        return parseInt( $el.find('.year').text(), 10 )
                    }
                }
            }

            if ( this.$el.hasClass('isotope') ) {
                this.$el.isotope(isoOps)
                this.$el.isotope( 'updateSortData', $('.thumb') )
                this.filter( this.model.get('filter') )
                this.sort( this.model.get('sort') )
            } else {
                var spinner = new Spinner()
                this.$el.imagesLoaded( function() {
                    g()
                    $el.isotope(isoOps)

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

