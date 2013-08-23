'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.ImageThumb = Backbone.View.extend({
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
            url : this.options.path ? this.options.path + '/' + this.model.get('url') : this.model.get('url'),
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
        //e.preventDefault()
        //PA.router.navigate("projects/" + this.model.get('url'), {trigger : true} )
   }
})

// instantiate with
// cover : boolean
// collection/model : of images
PA.ImageShowcase = Backbone.View.extend({
    tagName : 'div',
    id : 'iso-grid',
    initialize : function() {
        _.bindAll(this, 'render', 'firstLoad', 'filter')
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
        this.collection.forEach(function(image) {
            var thumb = new PA.ImageThumb({
                model : image,
                cover : this.options.cover ? true : false,
                large : this.collection.length < 5 && !this.options.cover,
                path : this.options.path
            })
            this.$el.append( thumb.render() )
        }, this)

        //this.isotope()
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

PA.FilmThumb = Backbone.View.extend({
    tagName : 'div',
    template : PA.jst.filmThumb,
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

PA.FilmThumbLayout = Backbone.View.extend({
    tagName : 'div',
    className: 'film-container',
    rowTmpl : PA.jst.filmRow,
    $row : undefined,
    render : function() {

        this.collection.forEach( function(model, index){
            if (index % 4 === 0) {
                this.$row = $( this.rowTmpl() )
                this.$el.append(this.$row)
            }
            this.$row.append( new PA.FilmThumb({ 
                model : model 
            }).render() )
        }, this )

        this.$('.film-row').imagesLoaded( function() {
            $(this).addClass('loaded')
        })

        return this.el
    }
})

PA.VideoShowcase = Backbone.View.extend({
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

PA.TextShowcase = Backbone.View.extend({
    tagName : 'div',
    className : 'showcase text',
    base : PA.jst.textTemplate,
    header : PA.jst.textTemplateHeader,
    bioImg : PA.jst.bioImage,
    gallery : PA.jst.textGallery,
    back : PA.jst.backButton,
    render : function() {
        return this.$el
    }
})

PA.ListItem = Backbone.View.extend({

    tagName : 'li',

    initialize : function(model, options) {
        _.bindAll( this, 'toggle' )
    },

    events : {
        'click a' : 'toggle'
    },

    toggle : function(e) {
        e.preventDefault()
        this.model.activate()
    },

    template : PA.jst.listItemPartial,

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

PA.ListView = Backbone.View.extend({
    tagName : 'section',
    header : PA.jst.listHeaderPartial,

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
                .append( new PA.ListItem({
                    model : listItem,
                    path : path ? path : '',
                    url : url ? listItem.url() : false
                }).render() )
        }, this )

        return this.el
    }

})

PA.ListShowcase = Backbone.View.extend({
    tagName : 'div',
    className : 'showcase list',

    render : function(){

        // groupedCollection is an object of years paired with project objects that fall within that year.
        _.each( this.options.groupedCollection, function(v,k){
            var html = new PA.ListView({
                date : k,
                listItems : v,
                path : this.options.path,
                url : this.options.url
            })
            this.$el.append( html.render() )
        }, this )
        return this.el
    }
})
