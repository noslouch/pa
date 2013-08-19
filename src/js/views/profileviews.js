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

PA.ProfileViewer = Backbone.View.extend({
    initialize : function() {
        _.bindAll(this, 'contentLoader', 'sectionLoader','toggleActive', 'navigate')
        this.links = this.$('#showcaseLinks')
    },
    render : function() {},
    events : {
        'click #profileLinks a' : 'navigate',
        'click .list a': 'navigate',
        'click #back' : 'navigate'
    },
    navigate : function(e) {
        e.preventDefault()
        PA.router.navigate(e.currentTarget.pathname, {trigger : true})
    },
    contentLoader : function(section, urlTitle) {
        var model = this.collection.where({ url : urlTitle })[0],
            $container = this.$('#showcaseContainer'),
            layoutView = new PA.TextShowcase(),
            $layout = layoutView.render(),
            $base

        $base = $( layoutView.base({
            type : 'press',
            content : model.get('content')
        }) )
        .prepend( layoutView.header({
            title : model.get('title'),
            htmlDate : Backbone.Model.prototype.makeHtmlDate( model.get('date') ),
            date : Backbone.Model.prototype.parseDate( model.get('date') ).format('MMMM DD, YYYY')
        }) )
        .append( layoutView.gallery({
            images : model.get('gallery'),
            imageTemplate : PA.jst.textGalleryImage
        }) )
        .append( layoutView.back({
            url : '/profile/' + section,
            buttonText : 'See All Items'
        }) ).appendTo( $layout )

        $container.html( $layout )
    },
    sectionLoader : function(urlTitle) {
        var $container = this.$('#showcaseContainer'),
            data = this.collection.where({ type : urlTitle })

        switch(urlTitle){
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
                PA.pressCollection = new PA.PressCollection()
                _.each(PA.groupedProfilePages.press, function(e) {
                    PA.pressCollection.add(e.attributes)
                })
                $container.html( new PA.ListShowcase({
                    groupedCollection : PA.pressCollection.groupBy( function(e){
                        return e.get('date').year()
                    } ),
                    path : '/profile'
                }).render() )
                break;
            case 'awards':
                var awardCollection = new PA.AwardCollection()
                _.each(PA.groupedProfilePages.awards, function(e) {
                    awardCollection.add(e.attributes) 
                })
                $container.html( new PA.ListShowcase({
                    groupedCollection : awardCollection.groupBy( function(e){ 
                        return e.get('date').year()
                    } ),
                    path : false,
                    url : false
                }).render() )
                break;
            case 'photos-of-pa':
                var album = new PA.PhotoAlbum( data[0].attributes )
                var images = new PA.ImageShowcase({
                    cover : false,
                    collection : album.get('photos')
                })
                $container.html( images.render() )
                images.firstLoad()
                break;
            case 'articles-by-pa':
                break;
            case 'articles-about-pa':
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

    },
    toggleActive : function(section){
        this.$('.active').removeClass('active')
        $('#'+section).addClass('active')
    }
})
