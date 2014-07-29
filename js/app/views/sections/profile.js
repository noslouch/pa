/* app/views/sections/profile.js - Profile Page Views and Section Views */
'use strict';

define([
    'require',
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcases/gallery',
    'app/views/showcases/text',
    'app/views/showcases/list',
    'tpl/jst',
    'app/collections/profile',
    'app/views/partials/jumplist',
    'utils/fbLoader'
], function( require, $, Backbone, _, G, T, l, TPL, sections, Jumps ) {

    var Content = Backbone.View.extend({
        id : 'showcaseContainer',
        className : 'container',
        initialize : function() {
            _.bindAll( this, 'render', 'contentController' )
        },

        render : function( section ){
            var model = section.get('content'),
                showcase,
                $layout,
                $base,
                album

            $('#filter-bar').empty()
            switch( section.id ) {

                case 'bio':
                    showcase = new T()
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
                    showcase = new l.List({
                        collection : model,
                        path : '/profile/press'
                    })
                    this.$el.html( showcase.render() )

                    $('#filter-bar').html( new Jumps({
                        collection : model
                    }).render() ).addClass('filter-bar profile')
                    break;

                case 'awards':
                    showcase = new l.List({
                        collection : model,
                        path : false,
                        url : false
                    })
                    this.$el.html( showcase.render('date') )

                    $('#filter-bar').html( new Jumps({
                        collection : model
                    }).render() ).addClass('filter-bar profile')
                    break;

                case 'photos-of-pa':
                    showcase = new G({
                        collection : model.get('photos'),
                        model : model
                    })
                    model.set('type', 'gallery')
                    this.$el.html( showcase.render({
                        container : this.$el
                    }) )
                    break;

                case 'articles-by-pa':
                    showcase = new l.List({
                        collection : model,
                        path : '/profile/articles-by-pa'
                    })
                    this.$el.html( showcase.render() )

                    $('#filter-bar').html( new Jumps({
                        collection : model
                    }).render() ).addClass('filter-bar profile')
                    break;

                case 'articles-about-pa':
                    showcase = new l.List({
                        collection : model,
                        path : '/profile/articles-by-pa'
                    })
                    this.$el.html( showcase.render() )

                    $('#filter-bar').html( new Jumps({
                        collection : model
                    }).render() ).addClass('filter-bar profile')
                    break;

                case 'interviews':
                    showcase = new l.List({
                        collection : model,
                        path : '/profile/interviews'
                    })
                    this.$el.html( showcase.render() )

                    $('#filter-bar').html( new Jumps({
                        collection : model
                    }).render() ).addClass('filter-bar profile')
                    break;

                case 'transcripts':
                    showcase = new l.List({
                        collection : model,
                        path : '/profile/transcripts'
                    })
                    this.$el.html( showcase.render() )

                    $('#filter-bar').html( new Jumps({
                        collection : model
                    }).render() ).addClass('filter-bar profile')
                    break;

                case 'acknowledgements':
                    showcase = new T()
                    $layout = showcase.render()
                    $base = $( showcase.base({
                        type : 'bio',
                        content : model.get('content')
                    }) )
                    $layout.append($base)
                    this.$el.html($layout)
                    setTimeout( function() {
                        $('.bio').addClass('loaded')
                    }, 5 )
                    break;

                default:
                    break;
            }

            Backbone.dispatcher.trigger('profile:navigate', '/profile/' + section.id )

            $('html, body').animate({ scrollTop : 0 })
        },

        contentController : function(model){
            $('#filter-bar').empty()

            var layout = new T(),
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
                url : '/profile/' + model.get('type'),
                buttonText : 'Back to All ' + back
            }) ).appendTo( $layout )

            this.$el.html( $layout )

            if ( model.get('gallery').length ) {
                var fbLoader = require('utils/fbLoader')
                fbLoader()
            }

            $('html, body').animate({ scrollTop : 0 })
        }

    })

    var Profile = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'navigate', 'toggleSection', 'swap', 'back' )
            var promiseStack = [],
                self = this

            this.collection = sections
            this.viewer = new Content()
            this.links = new Backbone.Collection()
            this.$container = $('<div/>').attr({
                class : 'profile viewer',
                id : 'profileViewer'
            })

            this.collection.each(function( section ) {
                promiseStack.push( section.get('content').fetch() )
            })
            $.when.apply( $, promiseStack ).done(function(){
                self.model.set('loaded', true)
            })
        },

        events : {
            'click .list a' : 'navigate',
            'click #back' : 'back',
            'click #profileLinks a' : 'toggleSection'
        },

        render : function( segment, urlTitle, spinner ) {
            this.listenTo( this.collection, 'change:active', this.swap )
            this.$container.html( TPL.profileLinks() ).append( this.viewer.el )
            $('.inner-header, .page').addClass('profile')
            this.delegateEvents()
            if ( !this.model.get('loaded') ){
                throw {
                    message : 'Profile isn\'t loaded.',
                    type : 'EmptyProfile'
                }
            }

            this.$el.html( this.$container )
            var section = segment ? segment : 'bio'
            this.collection.get(section).activate(urlTitle)

            if ( urlTitle ) {
                var item = this.collection.section(segment).findWhere({ 'url-title' : urlTitle })
                this.viewer.contentController( item )
            }

            this.trigger('rendered')
            spinner.detach()
        },

        onClose : function() {
            _.each( this.collection.where({ active : true }), function(model) {
                model.deactivate(true)
            })
            $('.inner-header').removeClass('profile')
            $('.page').removeClass('profile')
            $('#filter-bar').empty()
        },

        navigate : function(e){
            e.preventDefault()
            var id = e.currentTarget.id
            var item = this.collection.active().get(id)
            this.viewer.contentController( item )
            Backbone.dispatcher.trigger('profile:navigate', e.currentTarget.pathname)
        },

        back : function(e) {
            e.preventDefault()
            this.viewer.render( this.collection.findWhere({ active : true }) )
        },

        toggleSection : function(e) {
            e.preventDefault()
            var section = e.target.id
            this.collection.get(section).activate()
        },

        swap : function(section, val, ops) {
            // there are some situations where there isn't a disabled section
            if ( section.get('active') ) {
                try {
                    var last = this.collection.chain()
                        .filter(function(model) { return model.get('active') })
                        .reject(function(model) { return model.id === section.id })
                        .value()

                    last[0].deactivate()
                } catch(err) {}

                if (!ops.urlTitle){
                    this.viewer.render( section )
                }
                this.$('#profileLinks .active').removeClass('active')
                this.$('#' + section.id).addClass('active')
            }
        }
    })

    return new Profile({
        model : new Backbone.Model()
    })
})
