/* app/views/showcaseviews.js
 * the many showcases of Peter Arnell */
'use strict';

define([
    'module',
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'utils/fbLoader'
], function( $, Backbone, _, templates, fbLoader ) {

    var cases = {}

    Backbone.View.prototype.destroy = function() {
        this.remove()
        this.unbind()
    }

    cases.ImageThumb = Backbone.View.extend({
        tagName : "div",
        template : templates.thumbTemplate,
        className : function() {
            if (this.options.cover) {
                var tags = []
                _.each( this.model.get('tags'), function(obj) {
                    tags.push( obj.className )
                }, this )
                return "thumb " + tags.join(' ') + (this.model.get('wide') ? " wide" : "")
            } else {
                return "thumb" + (this.model.get('wide') ? " wide" : "")
            }
        },
        render : function(){
            this.$el.html( this.template({
                url : this.options.path ? this.options.path + '/' + this.model.get('url') : this.model.get('url'),
                cover : this.options.cover,
                caption : this.model.get('caption'),
                thumb : this.model.get('thumb'),
                lg_thumb : this.model.get('lg_thumb'),
                large : this.options.large
            }) )
            return this.el
        }
    })

// instantiate with
// cover : boolean
// collection/model : of images
cases.ImageShowcase = Backbone.View.extend({
    tagName : 'div',
    id : 'iso-grid',

    initialize : function() {
        _.bindAll(this, 'render', 'firstLoad', 'filter')

        this.collection.forEach(function(image) {
            var thumb = new cases.ImageThumb({
                model : image,
                cover : this.options.cover ? true : false,
                large : this.collection.length < 5 && !this.options.cover,
                path : this.options.path
            })
            this.$el.append( thumb.render() )
        }, this)

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
        this.on('filter', this.filter)
        fbLoader()
        return this.el
    },

    firstLoad: function() {

        var $img = this.$('img'),
            rtl = this.$el.hasClass('rtl'),
            fixed = this.$el.hasClass('fixed')

        this.$el.imagesLoaded( function() {
            this.isotope({
                transformsEnabled: !rtl,
                itemSelector: '.thumb',
                layoutMode : fixed ? 'masonry' : 'fitRows',
                masonry : {
                    gutterWidth: 7,
                    columnWidth: rtl ? 164*1.5 : 164
                },
                onLayout : function() {
                    $(this).css('overflow', 'visible')
                }
            })

            $img.addClass('loaded')
        })
    },

    filter : function(filter) {
        this.$el.isotope({ filter : filter })
    }
})

cases.FilmThumb = Backbone.View.extend({
    tagName : 'div',
    template : templates.filmThumb,
    render : function() {
        var html = this.template({
            url : this.model.url(),
            thumb : this.model.get('thumb'),
            title : this.model.get('title'),
            summary : this.model.get('summary')
        })
        this.$el.append( html )
        return this.el
    }
})

cases.FilmThumbLayout = Backbone.View.extend({
    tagName : 'div',
    className: 'film-container',
    rowTmpl : templates.filmRow,
    $row : undefined,
    render : function() {

        this.collection.forEach( function(model, index){
            if (index % 4 === 0) {
                this.$row = $( this.rowTmpl() )
                this.$el.append(this.$row)
            }
            this.$row.append( new cases.FilmThumb({ 
                model : model 
            }).render() )
        }, this )

        this.$('.film-row').imagesLoaded( function() {
            $(this).addClass('loaded')
        })

        return this.el
    }
})

cases.VideoShowcase = Backbone.View.extend({
    tagname : 'div',
    className : 'showcase video',
    videoCaption : templates.videoCaption,
    initialize : function() {
        this.videoTmpl = this.model.get('video_id') ? templates.videoID : templates.iframeVideo
        this.videoSrc = this.model.get('video_id') ? this.model.get('video_id') : this.model.get('video_src')
    },
    render : function() {
        var film = this.videoTmpl({
            videoSrc : this.videoSrc,
            youtube : this.model.get('youtube')
        })
        var caption = this.videoCaption({
            title : this.model.get('title'),
            content : this.model.get('caption'),
        })

        this.$el
            .append(film)
            .append(caption)

        return this.el
    }
})

cases.TextShowcase = Backbone.View.extend({
    tagName : 'div',
    className : 'showcase text',
    base : templates.textTemplate,
    header : templates.textTemplateHeader,
    bioImg : templates.bioImage,
    gallery : templates.textGallery,
    back : templates.backButton,
    render : function() {
        return this.$el
    }
})

cases.ListItem = Backbone.View.extend({

    tagName : 'li',

    initialize : function(model, options) {
        _.bindAll( this, 'toggle' )
    },

    events : {
        'click a' : 'toggle'
    },

    toggle : function(e) {
        if ( !$(e.currentTarget).parents('.projects').length ) {
            e.preventDefault()
            this.model.activate()
        }
    },

    template : templates.listItemPartial,

    render : function() {
        this.$el.append( this.template({
            id : this.model.id,
            title : this.model.get('title'),
            summary : this.model.get('summary'),
            url : this.options.url,
            path : this.options.path
        }) )
        return this.el
    }
})

cases.ListView = Backbone.View.extend({
    tagName : 'section',
    header : templates.listHeaderPartial,

    render : function() {
        var listItems = this.options.listItems,
            path = this.options.path,
            date = this.options.date,
            url = this.options.url === false ? false : true

        this.$el.append( '<ul />')
        this.$('ul').append( this.header({ 
            htmlDate : date,
            date : date 
        }) )

        _.each( listItems, function(listItem) {
            this.$('ul')
                .append( new cases.ListItem({
                    model : listItem,
                    path : path ? path : '',
                    url : url ? listItem.url() : false
                }).render() )
        }, this )

        return this.el
    }

})

cases.ListShowcase = Backbone.View.extend({
    tagName : 'div',
    className : 'showcase list',
    initialize : function() {
        var self = this

        this.byDate = this.collection.groupBy(function(model) {
            return model.get('date').year()
        })

        this.byFirst = (function() {
            var sorted = self.collection.sortBy('title')
            return _.groupBy(sorted, function(model) {
                var t = model.get('title')
                return t[0]
            })
        }())

    },

    render : function(sort){
        console.log('ListShowcase render')
        this.on('sort', this.render)
        this.on('jump', this.jump)

        var group = sort === 'date' ? this.byDate : this.byFirst

        this.$el.empty()
        _.each( group, function(v,k){
            var html = new cases.ListView({
                id : k.toLowerCase(),
                date : k,
                listItems : v,
                path : this.options.path,
                url : this.options.url
            })
            this.$el.append( html.render() )
        }, this )

        return this.el
    },

    jump : function(jump) {
        $('html, body').animate({
            scrollTop : $('#' + jump).offset().top - 200
        })
    }
})

cases.SimpleList = Backbone.View.extend({
    tagName : 'div',
    className : 'showcase list',
    initialize : function() {},
    render : function() {
        this.$el.append('<ul/>')
        _.each( this.collection, function(el, idx) {
            var li = document.createElement('li'),
                a = document.createElement('a'),
                h4 = document.createElement('h4')

            $(a).attr('href', el.url).text(el.title)
            $(a).appendTo(h4)
            $(h4).appendTo(li)
            this.$('ul').append(li)
        }, this )

        return this.el
    }

})

cases.StarThumb = Backbone.View.extend({
    tagName : "a",
    initialize : function() {
        _.bindAll( this, 'render' )

        $('<img>').appendTo( this.$el )
            .attr( 'src', this.model.get('thumb') )

        this.$el.css({
            left : this.options.HALF_WIDTH + this.randomRange(-this.options.HALF_WIDTH, this.options.HALF_WIDTH),
            top : this.options.HALF_HEIGHT + this.randomRange(-this.options.HALF_HEIGHT, this.options.HALF_HEIGHT)
        })
    },
    render : function(instagram) {

        this.$el
            .addClass( 'star' )

        if ( !!instagram ) {
            this.$el
            .attr( 'target' , '_blank' )
            .attr( 'href', this.model.get('url') )
        } else {
            var caption = document.createElement('div'),
                p = document.createElement('p')
            $(p).html( this.model.get('caption') )
            $(caption).addClass('caption').append(p)

            this.$el
            .attr( 'href', '/projects/' + this.model.get('url') )
            .prepend(caption)
        }

        return this.el
    }
})

cases.StarThumb.prototype.randomRange = function (min, max) {
    return ((Math.random()*(max-min)) + min)
}

cases.Starfield = Backbone.View.extend({
    tagName : 'div',
    className : 'starfield',
    id : 'starfield',
    initialize : function( collection, instagram ){
        var SCREEN_WIDTH = window.innerWidth,
            SCREEN_HEIGHT = window.innerHeight,
            HALF_WIDTH = window.innerWidth / 2,
            HALF_HEIGHT = window.innerHeight / 2
            //imageLimit = SCREEN_WIDTH < 320 ? 12 : 48


        this.stagger = function() {
            var i = 0,
                self = this

            function go(){
                self.$el.append(
                    new cases.StarThumb({
                        model : self.images.models[i],
                        HALF_HEIGHT : HALF_HEIGHT,
                        HALF_WIDTH : HALF_WIDTH
                    }).render(instagram) )
                i++

                // CHANGE TO IMAGELIMIT WHEN PROJECTS INCREASE
                if ( i < self.images.length ) {
                    setTimeout(go, 550)
                }
            }

            go()
        }

        this.starsRunning = false
    },

    destroy : function() {
        this.starsRunning = false
        this.$el.empty()
        this.remove()
        this.unbind()
    },

    render : function() {
        this.starsRunning = true
        this.$el.empty()
        this.images = new Backbone.Collection( this.collection.shuffle() )
        this.stagger()
        return this.el
    }
})
    return {}
})

