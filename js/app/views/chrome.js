/* app/views/chrome.js
 * outer most appviews */
'use strict';

define([
    'require',
    'exports',
    'jquery',
    'backbone',
    'underscore',
    'app/views/sections/search',
    'utils/spinner'
], function( require, exports, $, Backbone, _, Search, Spinner ) {

    var App = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'showSearch', 'navigate', 'setView', 'detail', 'section' )

            this.model = new Backbone.Model()
            this.searchForm = new Search.Form({
                el : '#searchForm'
            })

            Backbone.dispatcher.on('goBack', this.section)
            //Backbone.dispatcher.on('projects:goBack', this.projects)
            //Backbone.dispatcher.on('film:goBack', this.film)
            //Backbone.dispatcher.on('photography:goBack', this.photography)
        },

        events : {
            'click' : 'closeMenu',
            'click #search' : 'showSearch',
            'click #nav a' : 'navigate'
        },

        showSearch : function(e){
            e.preventDefault()
            this.searchForm.render()
        },

        closeMenu : function(e) {
            $('#filter-bar .open').removeClass('open')
        },

        navigate : function(e) {
            if ( e.target.id === 'home' ) {
                $('.site-header').addClass( 'home' )
            } else if ( e.target.id === 'search') {
                return
            }
            e.preventDefault()
            this.currentView.close()

            var spinner = new Spinner()
            this.section(spinner,e.target.id)
            Backbone.dispatcher.trigger('navigate:section', e)
        },

        setView : function( view ) {
            this.currentView = view
        },

        section : function( spinner, section, segment, urlTitle ) {
            this.$('#nav a').removeClass( 'active' )
            $('#' + section).addClass( 'active' )
            var self = this

            require(['app/views/sections/' + section], function( view ) {
                self.setView(view)
                view.setElement('.page')
                if (section === 'home') {
                    var bootstrap = !!$('#n-container').length
                    view.render(spinner)
                } else if (section === 'stream') {
                    view.render(spinner)
                } else if ( section === 'profile' ) {
                    try {
                        view.render( segment, urlTitle, spinner )
                    } catch(e) {
                        view.model.on('change:loaded', function() {
                            view.render( segment, urlTitle, spinner )
                        })
                    }
                    //self.listenTo( view.collection, 'change:active', self.detail )
                } else {
                    try {
                        view.init(spinner)
                        if (section === 'projets') { view.filter.$el.show() }
                    } catch(e) {
                        Backbone.dispatcher.on( section + ':ready', function() {
                            view.init(spinner)
                        })
                    }
                }
            })
        },

        detail : function( spinner, section, urlTitle, showcaseUrl, previous ) {
            var self = this
            require(['app/views/details/' + section], function( view ) {
                self.setView( view )
                view.on('rendered', function() {
                    spinner.detach()
                })

                $('.page')
                    .html( view.render( urlTitle, showcaseUrl, previous ) )
                    .removeClass('projects')
            })
        },

        search : function() {
            this.pageSearch = new Search.Form({
                el : '#pageSearchForm'
            })
            this.setView( this.pageSearch )
            this.pageSearch.render()
        }

/*
        singleProject : function( spinner, projectUrl, showcaseUrl, previous ) {
            var self = this
            require(['app/views/details/project'], function( projectView ) {
                self.setView( projectView )
                projectView.on('rendered', function() {
                    spinner.detach()
                })

                $('.page')
                    .html( projectView.render( projectUrl, showcaseUrl, previous ) )
                    .removeClass('projects')
            })
        },

        singleFilm : function( spinner, filmUrl ) {
            var self = this
            require(['app/views/details/film'], function( filmView ) {
                self.setView( filmView )
                filmView.on('rendered', function(){
                    spinner.detach()
                })

                $('.page').html( filmView.render( filmUrl ) )
            })
        },

        contact : function( spinner ) {
            var self = this
            require(['app/views/sections/contact'],
            function( c ) {
                self.setView( c )
                $('.page').html( c.render() )
                spinner.detach()
            })
        },

        stream : function( spinner ) {
            var self = this
            require(['app/views/sections/stream'],
            function( stream ) {
                self.setView( stream )
                stream.setElement( '.page' )
                stream.render(spinner)
            })
        },
*/
    })

    return new App({ el : document })
})
