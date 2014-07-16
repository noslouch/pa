/* app/views/showcases/gallery.js
 * PA galleries. tests for mobile and uses swipable gallery if so */
'use strict';

define([
    'require',
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'isotope',
    'imagesLoaded'
], function( require, $, Backbone, _, TPL ) {

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
            setTimeout(this.isotope.bind(this, options), 0) // triggers post-render callback
            // if ( this.options.path === 'photography' ||
            //      this.model.hasChanged('view') ||
            //      this.model.get('type') === 'gallery' ) {
                return this.el
            //}
        },

        isotope : function(options) {
            var self = this,
                model = this.model,
                $img = this.$('img'),
                rtl = this.$el.hasClass('rtl'),
                fixed = this.$el.hasClass('fixed'),
                $el = this.$el,
                path = 'ontouchstart' in window ? 'touchLoader' : 'fbLoader',
                isoOps = {
                    //containerStyle : null,
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

            if ( options.gallery ) {
                require(['utils/' + path], function(g) {
                    self.$el.imagesLoaded()
                    .progress(function(instance, image) {
                        image.img.classList.add('loaded')
                    }).done(function() {
                        g()
                        $el.isotope(isoOps)
                        //$el.isotope('on', 'layoutComplete', onLayout)

                        //$img.addClass('loaded')
                    })
                })
            } else {
                self.$el.imagesLoaded()
                .progress(function(instance, image) {
                    image.img.classList.add('loaded')
                })
                .done(function() { 
                    isoOps.filter = model.get('filter')
                    isoOps.sort = model.get('sort')

                    $el.isotope(isoOps)
                    //$img.addClass('loaded')

                    model.trigger('isotope:ready')
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

