'use strict';
var PA = PA || {}

var ImageThumb = Backbone.View.extend({
    tagName : "div",
    template : PA.jst.thumbTemplate,
    className : function() {
        if (this.options.cover) {
            return "thumb " + this.model.get('tags').join(' ') + (this.model.get('wide') ? " wide" : "")
        } else {
            return "thumb" + (this.model.get('wide') ? " wide" : "")
        }
    },
    render : function(){
        this.$el.html( this.template({
            url : this.model.get('url'),
            cover : this.options.cover,
            caption : this.model.get('caption'),
            thumb : this.model.get('thumb'),
            lg_thumb : this.model.get('lg_thumb'),
            large : this.options.large
        }) )
        return this.el
    },
    events : {
        click : "view"
    },
    view : function (e) {
        e.preventDefault()
        PA.router.navigate("projects/" + this.model.get('url'), {trigger : true} )
   }
})

var ImageShowcase = Backbone.View.extend({
    tagName : 'div',
    id : 'iso-grid',
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
    render : function(){
        this.collection.forEach(function(image) {
            var thumb = new ImageThumb({
                model : image,
                cover : this.options.cover ? true : false,
                large : this.collection.length < 5 && !this.options.cover
            })
            this.$el.append( thumb.render() )
        }, this)

        return this.el
    },
    isotope: function() {

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
                    console.dir(this)
                    $(this).css('overflow', 'visible')
                }
            })

            $img.addClass('loaded')
        })
    }
})

var FilmThumb = Backbone.View.extend({
    tagName : 'div',
    template : PA.jst.filmThumb,
    render : function() {
        var html = this.template( this.model.attributes )
        this.$el.append( html )
        return this.el
    }
})

var FilmShowcase = Backbone.View.extend({
    tagName : 'div',
    rowTmpl : PA.jst.filmRow,
    $row : undefined,
    render : function() {

        this.collection.forEach( function(model, index){
            if (index % 4 === 0) {
                this.$row = $( this.rowTmpl() )
                this.$el.append(this.$row)
            }
            this.$row.append( new FilmThumb({ model : model }).render() )
        }, this )

        return this.el
    }
})

var VideoShowcase = Backbone.View.extend({
    tagname : 'div',
    className : 'showcase video',
    videoCaption : PA.jst.videoCaption,
    initialize : function() {
        this.videoTmpl = this.model.get('video_id') ? PA.jst.videoID : PA.jst.iframeVideo
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

PA.ShowcaseContainer = Backbone.View.extend({
    tagName : 'div',
    id : 'showcaseContainer',
    className : 'container',
    render : function(){
        switch (this.options.type) {
            case 'image':
                // instantiated with options object, can be passed through render method
                // collection : new CoverGallery or new Gallery
                // cover : boolean
                var html = new ImageShowcase(this.options)
                this.$el.html( html.render() )
                html.isotope()
                fbLoader()
                break;
            case 'list':
                //this.$el.html( new ListShowcase(options).el )
                break;
            case 'text':
                //this.$el.html( new TextShowcase(options).el )
                break;
            case 'film':
                //this.$el.html ( new FilmShowcase(options).el )
                break;
            default:
                break;
        }

        return this.el

        if (this.options.cover) {
            //$('.page').html(this.el)
        }
    },
    initialize : function(options) {
        this.options = options
    }
})

