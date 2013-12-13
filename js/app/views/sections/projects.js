/* app/views/sections/projects.js
 * projects landing page - shit is complex! */
'use strict';

define([
    'require',
    'jquery',
    'backbone',
    'underscore',
    'app/views/partials/filterviews',
    'app/views/showcases/gallery',
    'app/views/showcases/list',
    'app/views/showcases/starfield',
    'app/collections/covergallery',
    'app/collections/projects',
    'foundation',
    'tooltips',
    'bbq',
    'lib/requirejs/domReady!'
], function( require, $, Backbone, _, FilterBar, G, l, Starfield, CoverGallery, Projects ) {

    var ProjectLanding = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'render', 'jumpSet', 'navigate', 'init' )
            var self = this

            this.collection.fetch({
                success : function(projects) {

                    self.model.cover = new G({
                        projects : true,
                        //cover : true,
                        collection : new CoverGallery( projects.pluck('coverImage') ),
                        //path : 'projects',
                        model : self.model
                    })

                    self.model.list = new l.List({
                        collection : new CoverGallery( projects.pluck('coverImage') ),
                        pageClass : 'projects',
                        path : 'projects',
                        section : 'Projects',
                        model : self.model
                    })

                    self.model.random = new Starfield({
                        collection : self.model.cover.collection
                    })

                    self.filterbar = new FilterBar({
                        el : '#filter-bar',
                        model : self.model,
                        collection : projects
                    })

                    Backbone.dispatcher.trigger('projects:ready')
                }
            })

            this.model.on( 'layout', this.jumpSet )
            $(window).on('hashchange', this.render)
            Backbone.dispatcher.on('hashchange', this.render)
            Backbone.dispatcher.on('filterCheck', function(router){
                if ( router.previous.href.match('projects') ) {
                    self.filterbar.close()
                }
            })
        },

        events : {
            'click .showcase a' : 'navigate'
        },

        render : function() {
            var hashObj = $.deparam.fragment()
            if ( this.model.get('view') === 'random' && hashObj.view === 'random' ) {
                // random view is currently running
                // and a filter of another dimension has been chosen
                // use cover view as default
                $.bbq.pushState({ view : 'cover' })
                return
            } else {
                hashObj.filter = hashObj.filter || '*'
                hashObj.view = hashObj.view || 'cover'
                hashObj.sort = hashObj.sort || 'name'
            }
            this.model.set( hashObj )
            if (hashObj.view === 'random') {
                console.log('setting timeout at', Date.now() )
                setTimeout( this.spinner.detach, 750 )
            } else {
                this.spinner.detach()
            }
            this.$el.html( this.model[hashObj.view].render() )
            this.filterbar.delegateEvents()
        },

        init : function(spinner) {
            this.spinner = spinner
            this.delegateEvents()
            this.filterbar.render()
            this.$el.addClass('projects')

            if ( !this.collection.length ) {
                throw {
                    message : 'Projects aren\'t loaded.',
                    type : 'EmptyCollection'
                }
            }

            if ( document.location.hash ) {
                $(window).trigger('hashchange')
            } else {
                $.bbq.pushState({ view : 'random' })
            }
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
            //this.collection.get( e.currentTarget.id ).activate()
        },

        onClose : function() {
            this.model.unset('sort').unset('filter').unset('view')
            this.$el.removeClass('projects')
            this.filterbar.close()
            $(window).off('hashchange')
        },

        jumpSet : function() {
            var t = $('.thumb')
            var byFirst = _.groupBy( t, function(el) { return $(el).find('.title').text()[0] })
            var byDate = _.groupBy( t, function(el) { return $(el).find('.year').text() })

            _.each(byFirst, function(value, key) {
                $(value[0]).find('.title')[0].id = key
            })
            _.each(byDate, function(value, key) {
                $(value[0]).find('.year')[0].id = key
            })
        },

        debug : function() {
            window.projects = this.model.get('projects')
            window.chrome = this.model
        },
    })

    $(document).foundation()

    return new ProjectLanding({
        model : new Backbone.Model(),
        collection : Projects
    })
})
