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
            _.bindAll( this, 'showSearch', 'navigate', 'setView', 'detail', 'section', 'singleProject', 'singleAlbum', 'singleFilm', 'profile')

            this.model = new Backbone.Model()
            this.searchForm = new Search.Form({
                el : '#searchForm'
            })

            Backbone.dispatcher.on('projects:goBack', this.projects)
            Backbone.dispatcher.on('film:goBack', this.film)
            Backbone.dispatcher.on('photography:goBack', this.photography)
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
            e.preventDefault()
            if ( e.target.id === 'search') { return }
            this.currentView.close()

            var spinner = new Spinner()
            if ( e.target.id === 'profile' ) {
                this.profile(spinner)
            } else {
                this.section(spinner,e.target.id)
            }
            this.$('#nav a').removeClass( 'active' )
            $(e.target).addClass( 'active' )
            Backbone.dispatcher.trigger('navigate:section', e)
        },

        setView : function( view ) {
            this.currentView = view
        },

        detail : function(model) {
            console.log(model)
        },

        section : function(spinner, section) {
            var self = this

            require(['app/views/sections/' + section], function( view ) {
                self.setView(view)
                view.setElement('.page')
                if (section === 'home') {
                    var bootstrap = !!$('#n-container').length
                    view.render()
                } else if (section === 'stream') {
                    view.render(spinner)
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
                spinner.detach()
                if (section === 'projects') {
                    self.listenTo( view.collection, 'change:active', self.detail )
                }
            })
        },

/*
        home : function(spinner) {
            var self = this,
                bootstrap = !!$('#n-container').length

            require(['app/views/sections/home'], function( home ) {
                self.setView( home )
                home.setElement('.page')
                home.render()
                spinner.detach()
            })
        },
*/

/*
        projects : function(spinner) {
            var self = this
            require(['app/views/sections/projects'], function( projects ) {
                self.setView( projects )
                projects.setElement('.page')
                try {
                    projects.init(spinner)
                    projects.filter.$el.show()
                } catch (e) {
                    Backbone.dispatcher.on('projects:ready', function() {
                        projects.init(spinner)
                    })
                }

                self.listenTo( projects.collection, 'change:active', self.detail )

            })
        },
*/

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


/*
        photography : function( spinner ) {
            var self = this
            require(['app/views/sections/photography'], function( photography ) {
                self.setView( photography )
                photography.setElement( '.page' )
                try {
                    photography.init(spinner)
                } catch(e) {
                    Backbone.dispatcher.on('photography:ready', function() {
                        photography.init(spinner)
                    })
                }
            })
        },
*/


        singleAlbum : function( spinner, albumUrl ) {
            var self = this
            require(['app/views/details/photo'], function( albumView ) {
                self.setView( albumView )
                albumView.on('rendered', function() {
                    spinner.detach()
                })

                $('.page').html( albumView.render( albumUrl ) )
            })
        },

/*
        film : function( spinner ) {
            var self = this
            require(['app/views/sections/film'], function( film ){
                self.setView( film )
                film.setElement('.page')
                try{
                    film.init(spinner)
                } catch(e) {
                    Backbone.dispatcher.on('film:ready', function(){
                        film.init(spinner)
                    })
                }
            })
        },
*/

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

/*
        books : function( spinner ) {
            var self = this
            require(['app/views/sections/books'], function( book ){
                self.setView( book )
                book.setElement('.page')
                try{
                    book.init(spinner)
                } catch(e) {
                    Backbone.dispatcher.on('books:ready', function(){
                        book.init(spinner)
                    })
                }
            })
        },
*/

        profile : function( spinner, segment, urlTitle) {
            var self = this
            require(['app/views/sections/profile'], function( profileView ) {
                self.setView( profileView )
                profileView.on('rendered', function(){
                    spinner.detach()
                })
                try {
                    $('.page').html( profileView.el )
                    profileView.render( segment, urlTitle )
                } catch(e) {
                    profileView.model.on('change:loaded', function() {
                        $('.page').html( profileView.el )
                        profileView.render( segment, urlTitle )
                    })
                }
            })
        },

/*
        contact : function( spinner ) {
            var self = this
            require(['app/views/sections/contact'],
            function( c ) {
                self.setView( c )
                $('.page').html( c.render() )
                spinner.detach()
            })
        },
*/

/*
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
        search : function() {
            this.pageSearch = new Search.Form({
                el : '#pageSearchForm'
            })
            this.setView( this.pageSearch )
            this.pageSearch.render()
        }
    })

    return new App({ el : document })
})
