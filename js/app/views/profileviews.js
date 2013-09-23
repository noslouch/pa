/* app/views/profileviews.js - Profile Page Views and Section Views */
'use strict';

define([
    'require',
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcaseviews',
    'tpl/jst',
    'utils/fbLoader'
], function( require, $, Backbone, _, S, TPL ) {

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
                    require(['imagesLoaded'], function() {
                        $('.bio').imagesLoaded(function(){
                            $('.bio').addClass('loaded')
                        })
                    })
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
                    this.$el.html( showcase.render('date') )
                    break;

                case 'photos-of-pa':
                    var self = this
                    require(['app/models/album'], function(Album){
                        album = new Album( model.attributes )
                        showcase = new S.Image({
                            collection : album.get('photos')
                        })
                        self.$el.html( showcase.render() )
                        showcase.firstLoad()
                    })
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

            if ( model.get('gallery').length ) {
                var fbLoader = require('utils/fbLoader')
                fbLoader()
            }
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

        initialize : function(options) {
            _.bindAll( this, 'swap', 'back' )
        /*
            this.bio = new options.sections.Bio() // model
            this.press = new options.sections.Press()
            this.awards = new options.sections.Awards()
            this['photos-of-pa'] = new options.sections.PhotosOf() // model
            this['articles-by-pa'] = new options.sections.ArticlesBy()
            this['articles-about-pa'] = new options.sections.ArticlesAbout()
            this.interviews = new options.sections.Interviews()
            this.transcripts = new options.sections.Transcripts()
            this.acknowledgements = new options.sections.Acknowledgements() // model
            */

            this.sections = options.sections
        /*
            this.sections.push(
                this.bio
                , this.press
                , this.awards
                , this['photos-of-pa']
                , this['articles-by-pa']
                , this['articles-about-pa']
                , this.interviews
                , this.transcripts
                , this.acknowledgements
            )
            */

            this.listenTo( Backbone.dispatcher, 'profile:swap', this.swap )

            this.links = this.$('#profileLinks a')
        },

        events : {
            'click #back' : 'back'
        },

        back : function(e) {
            e.preventDefault()
            var sectionName = e.currentTarget.pathname

            /*
            switch(sectionName.slice(9)) {
                case 'photos-of-pa':
                    this['photosOf'].activate()
                    break;

                case 'articles-by-pa':
                    this['articlesBy'].activate()
                    break;

                case 'articles-about-pa':
                    this['artAiclesAbout'].activate()
                    break;

                default:
                    this[sectionName.slice(9)].activate()
                    break;
            }
            */

            this.sections[sectionName.slice(9)].activate()
        },

        swap : function(section, replace) {

            // there are some situations where there isn't a disabled section
            try {
                _.findWhere( this.sections, { active : true }).deactivate()
            } catch(err) {}

            section.activate(replace)
        },

        render : function() {

            _.each( this.sections, function(section, name, sections) {
                new Link({
                    el : '#' + name,
                    model : section
                })
            }, this )

            this.viewer = new Content({
                el : this.$('#showcaseContainer')
            })
        }
    })

    return Page
})
