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
            if ( e.target.id === 'search') {
                return
            }
            e.preventDefault()

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

                if ( section === 'profile' ) {
                    try {
                        view.render( segment, urlTitle, spinner )
                    } catch(e) {
                        view.model.on('change:loaded', function() {
                            view.render( segment, urlTitle, spinner )
                        })
                    }
                } else {
                    try {
                        view.init(spinner)
                        if (section === 'projects') { view.filter.$el.show() }
                    } catch(e) {
                        Backbone.dispatcher.on( section + ':ready', function() {
                            view.init(spinner)
                        })
                    }
                }
            })
        },

        detail : function( spinner, section, urlTitle, hidden, previous ) {
            var self = this
            require(['app/views/details/' + section], function( view ) {
                self.setView( view )
                view.on('rendered', function() {
                    spinner.detach()
                })

                $('.page')
                    .html( view.render( urlTitle, hidden, previous ) )
            })
        },

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
