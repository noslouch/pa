/* app/views/profileviews.js - Profile Page Views and Section Views */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcaseviews',
    //'app/collections/photography',
    'tpl/jst',
    //'app/collections/profilesections',
    //'app/models/profilesections'
], function( $, Backbone, _, S, TPL, Collections, Models ) {

    var Content = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'render', 'contentController' )
            this.listenTo( Backbone.dispatcher, 'profile:sectionActivate', this.render )
            this.listenTo( Backbone.dispatcher, 'profile:listItemActivate', this.contentController )
        },

        render : function(model){

            var showcase,
                $layout,
                $base,
                album

            switch(model.section) {

                case 'bio':
                    showcase = new S.Text()
                    $layout = showcase.render()
                    $base = $( showcase.base({
                        type : 'bio',
                        content : model.get('content')
                    }) )
                    .prepend( showcase.bioImg({
                        bioImg : model.get('bioImg')
                    }) )
                    $layout.append($base)
                    this.$el.html($layout)
                    break;

                case 'press':
                    showcase = new S.List({
                        collection : model,
                        path : '/profile/press/'
                    })
                    this.$el.html( showcase.render() )
                    break;

                case 'awards':
                    showcase = new S.List({
                        collection : model,
                        path : false,
                        url : false
                    })
                    this.$el.html( showcase.render() )
                    break;

                case 'photos-of-pa':
                    
                    album = new Album( model.attributes )
                    showcase = new S.Image({
                        collection : album.get('photos')
                    })
                    this.$el.html( showcase.render() )
                    showcase.firstLoad()
                    break;

                case 'articles-by-pa':
                    showcase = new S.List({
                        collection : model,
                        path : '/profile/articles-by-pa/'
                    })
                    this.$el.html( showcase.render() )
                    break;

                case 'articles-about-pa':
                    showcase = new S.List({
                        collection : model,
                        path : '/profile/articles-by-pa/'
                    })
                    this.$el.html( showcase.render() )
                    break;

                case 'interviews':
                    showcase = new S.List({
                        collection : model,
                        path : '/profile/interviews/'
                    })
                    this.$el.html( showcase.render() )
                    break;

                case 'transcripts':
                    showcase = new S.List({
                        collection : model,
                        path : '/profile/transcripts/'
                    })
                    this.$el.html( showcase.render() )
                    break;

                case 'acknowledgements':
                    showcase = new S.Text()
                    $layout = showcase.render()
                    $base = $( showcase.base({
                        type : 'bio',
                        content : model.get('content')
                    }) )
                    $layout.append($base)
                    this.$el.html($layout)
                    break;

                default:
                    break;
            }
        },

        contentController : function(model){
            var layout = new S.Text(),
                $layout = layout.render(),
                $base

            $base = $( layout.base({
                type : 'press',
                content : model.get('content')
            }) )
            .prepend( layout.header({
                title : model.get('title'),
                htmlDate : model.get('htmlDate'),
                date : model.get('date').format('MMMM Do, YYYY')
            }) )
            .append( layout.gallery({
                images : model.get('gallery'),
                imageTemplate : TPL.textGalleryImage
            }) )
            .append( layout.back({
                url : '/profile/' + model.collection.section,
                buttonText : 'See All Items'
            }) ).appendTo( $layout )

            this.$el.html( $layout )
        },

    })

    var Link = Backbone.View.extend({

        initialize : function() {
            _.bindAll( this, 'toggleSection', 'toggleView' )
            this.listenTo( Backbone.dispatcher, 'profile:sectionActivate', this.toggleView )
        },

        events : {
            'click' : 'toggleSection'
        },

        toggleSection : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger( 'profile:swap', this.model || this.collection )
        },

        toggleView : function() {
            this.$el.toggleClass('active', this.model.active )
        }

    })

    var Page = Backbone.View.extend({

        initialize : function() {
            _.bindAll( this, 'swap', 'back' )
            this.bio = new Models.Bio() // model
            this.press = new Collections.Press()
            this.awards = new Collections.Awards()
            this.photosOf = new Models.PhotosOf() // model
            this.articlesBy = new Collections.ArticlesBy()
            this.articlesAbout = new Collections.ArticlesAbout()
            this.interviews = new Collections.Interviews()
            this.transcripts = new Collections.Transcripts()
            this.acknowledgements = new Models.Acknowledgements() // model

            this.sections = []
            this.sections.push(
                this.bio
                , this.press
                , this.awards
                , this.photosOf
                , this.articlesBy
                , this.articlesAbout
                , this.interviews
                , this.transcripts
                , this.acknowledgements
            )

            this.listenTo( Backbone.dispatcher, 'profile:swap', this.swap )

            this.links = this.$('#profileLinks a')
        },

        events : {
            'click #back' : 'back'
        },

        back : function(e) {
            e.preventDefault()
            var sectionName = e.currentTarget.pathname

            switch(sectionName.slice(9)) {
                case 'photos-of-pa':
                    this['photosOf'].activate()
                    break;

                case 'articles-by-pa':
                    this['articlesBy'].activate()
                    break;

                case 'articles-about-pa':
                    this['articlesAbout'].activate()
                    break;

                default:
                    this[sectionName.slice(9)].activate()
                    break;
            }
        },

        swap : function(section, replace) {

            // there are some situations where there isn't a disabled section
            try {
                _.findWhere( this.sections, { active : true }).deactivate()
            } catch(err) {}

            section.activate(replace)
        },

        render : function() {

            _.each( this.links, function(aTag, index) {
                new Link({
                    el : '#' + aTag.id,
                    model : this.sections[index] // IMPORTANT: these rely on the profile page links being in a specific order
                })
            }, this )

            this.viewer = new Content({
                el : this.$('#showcaseContainer')
            })
        }
    })

    return Page
})
