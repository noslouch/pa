/* app/views/profile.js - Profile Page Views and Section Views */
'use strict';

define([
    'require',
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcaseviews',
    'tpl/jst',
    'app/collections/profile',
    'utils/fbLoader'
], function( require, $, Backbone, _, S, TPL, Sections ) {

    var Content = Backbone.View.extend({
        id : 'showcaseContainer',
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
                        collection : model, path : false,
                        url : false
                    })
                    this.$el.html( showcase.render('date') )
                    break;

                case 'photos-of-pa':
                    var self = this
                    require(['app/models/album'], function(Album){
                        album = new Album( model.attributes )
                        showcase = new S.Image({
                            collection : album.get('photos'),
                            model : model
                        })
                        model.set('type', 'gallery')
                        self.$el.html( showcase.render({ container : self.$el }) )
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
            $('html, body').animate({ scrollTop : 0 })

            var layout = new S.Text(),
                $layout = layout.render(),
                type = model.get('type'),
                back,
                $base

            switch (type) {
                case 'press':
                    back = 'Press'
                    break;
                case 'articles-by-pa':
                    back = 'Articles by PA'
                    break;
                case 'articles-about-pa':
                    back = 'Articles about PA'
                    break;
                case 'interviews':
                    back = 'Interviews with PA'
                    break;
                case 'transcripts':
                    back = 'Transcripts'
                    break;
                default:
                    break;
            }

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
                buttonText : 'Back to All ' + back
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

    var Profile = Backbone.View.extend({
        className : 'profile viewer',
        id : 'profileViewer',
        initialize : function(options) {
            // initialized with options:
            //   options.segment string, first url segment
            //   options.urlTitle string, second url segment, optional
            _.bindAll( this, 'swap', 'back' )
            this.collection = Sections
            this.loadSeg = options.segment
            this.loadUrl = options.urlTitle

            this.$el.append( TPL.profileLinks() )
            this.listenTo( Backbone.dispatcher, 'profile:swap', this.swap )

            //this.sections = options.sections
            //this.links = this.$('#profileLinks a')
        },

        events : {
            'click #back' : 'back'
        },

        back : function(e) {
            e.preventDefault()
            var sectionName = e.currentTarget.pathname

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
            var promiseStack = []
            _.each( this.collection, function( section ) {
                promiseStack.push( section.fetch() )
            })

            $.when.apply( $, promiseStack ).done(function(){
                Backbone.dispatcher.trigger( 'profile:swap', this.collection[ this.loadSeg ? this.loadSeg : 'bio' ], this.loadSeg ? false : true )
                if ( this.loadUrl ) {
                    this.collection[this.loadSeg].findWhere({ url : this.loadUrl }).activate()
                }
            })

            _.each( this.collection, function(section, name, sections) {
                new Link({
                    el : this.$el('#' + name)[0],
                    model : section
                })
            }, this )

            this.viewer = new Content({
            })
        }
    })

    return Profile
})
