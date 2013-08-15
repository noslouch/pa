"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.ProfileLinks = Backbone.View.extend({
    tagName : 'ul',
    className : 'showcase-links',
    id : 'showcaseLinks',
    template : PA.jst.showcaseLinks,
    render : function() {},
    events : {}
})

/*
PA.ProfileViewer = Backbone.View.extend({
    tagName : 'div',
    className : 'profile viewer',
    baseTmpl : PA.jst.viewer,
    pLinks : PA.jst.profileLinks,
    initialize : function() {
        this.$el.html( this.baseTmpl() )
        this.$('#details').html( this.pLinks() )
        this.$('#bio').addClass('active')
        this.showcase = new PA.ShowcaseContainer({ el : this.$('#showcaseContainer') })
    },
    render : function() {
        return this.el
    }
})
*/

PA.ProfileViewer = Backbone.View.extend({
    initialize : function() {
        _.bindAll(this, 'contentLoader', 'profileLoader')
        this.links = this.$('#showcaseLinks')
    },
    render : function() {},
    events : {
        'click ul a' : 'profileLoader',
        'click .list a': 'contentLoader'
    },
    contentLoader : function(e){
        e.preventDefault()
        var model = this.collection.get(e.currentTarget.id),
            $container = this.$('#showcaseContainer'),
            layoutView = new PA.TextShowcase(),
            $layout = layoutView.render()

        console.log(model)
        console.log(model.get('gallery'))
        var $base = $( layoutView.base({
            type : 'press',
            content : model.get('content')
        }) )
        .prepend( layoutView.header({
            title : model.get('title'),
            htmlDate : model.get('htmlDate'),
            date : model.get('date')
        }) )
        .append( layoutView.gallery({
            images : model.get('gallery'),
            imageTemplate : PA.jst.textGalleryImage
        }) )
        .append( layoutView.back({
            url : '/profile',
            buttonText : 'See All Items'
        }) ).appendTo( $layout )

        $container.html( $layout )
    },
    profileLoader : function(e) {
        var $container = this.$('#showcaseContainer'),
            data = this.collection.where({ type : e.currentTarget.id })

        e.preventDefault()

        switch(e.currentTarget.id){
            case 'bio':
            var layoutView = new PA.TextShowcase(),
                $layout = layoutView.render(),
                $base = $( layoutView.base({
                    type : 'bio',
                    content : data[0].get('content')
                }) )
                .prepend( layoutView.bioImg({
                    bioImg : data[0].get('bioImg')
                }) )

                $layout.append($base)
                $container.html($layout)
                break;
            case 'press':
                var pressCollection = new PA.PressCollection()
                _.each(PA.groupedProfilePages.press, function(e) {
                    pressCollection.add(e.attributes) 
                })
                $container.html( new PA.ListShowcase({
                    groupedCollection : pressCollection.groupBy( function(e){ 
                        return e.get('date').getFullYear() 
                    } ),
                    path : 'profile'
                }).render() )
                break;
            case 'awards':
                var awardCollection = new PA.AwardCollection()
                _.each(PA.groupedProfilePages.awards, function(e) {
                    awardCollection.add(e.attributes) 
                })
                $container.html( new PA.ListShowcase({
                    groupedCollection : awardCollection.groupBy( function(e){ 
                        return e.get('date').getFullYear() 
                    } ),
                    path : 'profile'
                }).render() )
                break;
            case 'paPhotos':
                var album = new PA.PhotoAlbum( data[0].attributes )
                var images = new PA.ImageShowcase({
                    cover : false,
                    collection : album.get('photos')
                })
                $container.html( images.render() )
                images.firstLoad()
                break;
            case 'paAuthor':
                break;
            case 'paSubject':
                break;
            case 'interviews':
                break;
            case 'transcripts':
                break;
            case 'acknowledgements':
                break;
            default:
                break;
        }

    }
})
