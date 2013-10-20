/* app/views/projects.js
 * projects landing page - shit is complex! */
'use strict';

define([
    'require',
    'jquery',
    'backbone',
    'underscore',
    'app/views/filterviews',
    'app/views/showcaseviews',
    'app/collections/covergallery',
    'app/collections/projects',
    'foundation',
    'tooltips',
    'bbq',
    'lib/requirejs/domReady!'
], function( require, $, Backbone, _, FilterBar, S, CoverGallery, Projects ) {

    var ProjectLanding = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'render', 'jumpSet', 'navigate' )
            var self = this

            this.collection.fetch({
                success : function(projects) {

                    self.model.cover = new S.Image({
                        cover : true,
                        collection : new CoverGallery( projects.pluck('coverImage') ),
                        path : 'projects',
                        model : self.model
                    })

                    self.model.list = new S.List({
                        collection : new CoverGallery( projects.pluck('coverImage') ),
                        pageClass : 'projects',
                        path : 'projects',
                        section : 'Projects',
                        model : self.model
                    })

                    self.model.random = new S.Starfield({
                        collection : self.model.cover.collection
                    })

                    self.filter = new FilterBar({
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
                if ( router.previous.match('projects') ) {
                    self.filter.close()
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
            this.$el.html( this.model[hashObj.view].render() )
        },

        init : function(spinner) {

            this.filter.render()

            if ( !this.collection.length ) {
                throw {
                    message : 'Projects aren\'t loaded.',
                    type : 'EmptyCollection'
                }
            }

            spinner.detach()
            if ( document.location.hash ) {
                $(window).trigger('hashchange')
            } else {
                $.bbq.pushState({ view : 'random' })
            }
        },

        navigate : function(e) {
            e.preventDefault()
            this.model.unset('view').unset('filter').unset('view')
            Backbone.dispatcher.trigger('navigate:detail', e, this)
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
