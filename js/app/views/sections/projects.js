/*global PA*/
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
    'app/collections/covergallery',
    'app/collections/projects',
    'utils/spinner',
    'foundation',
    'tooltips',
    'bbq',
    'domReady!'
], function( require, $, Backbone, _, FilterBar, G, l, CoverGallery, Projects, Spinner ) {

    var ProjectLanding = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'render', 'navigate', 'init' )
            var self = this

            if ( !Projects.length ) {
                try {
                    this.collection.add(PA.projects, { parse : true })
                    this.build()
                } catch (e) {
                    this.collection.fetch({ success : this.build.bind(this) })
                }
            }

        },

        events : {
            'click .showcase a' : 'navigate'
        },

        init : function(spinner) {
            var hashObj = $.deparam.fragment()

            this.spinner = spinner
            this.delegateEvents()

            this.filterbar.render({ mixitup : false, jumpTo : true })

            this.$el.addClass('projects')

            if ( !this.collection.length ) {
                throw {
                    message : 'Projects aren\'t loaded.',
                    type : 'EmptyCollection'
                }
            }

            hashObj.filter = hashObj.filter || '*'
            hashObj.view = hashObj.view || 'cover'
            hashObj.sort = hashObj.sort || 'name'
            this.model.set( hashObj )
            history.replaceState({}, '', $.param.fragment('', hashObj))

            this.render()

            $(document).foundation({
                tooltip : {
                    hover_delay: 50,
                    disable_for_touch: true
                }
            })
        },

        render : function() {
            var currentView = this.model.get('view')
            this.$el.html( this[currentView].render({ gallery : false }) )

            this.filterbar.delegateEvents()

            this.spinner.detach()

            this.model.on('change', function(model) {
                var newAttr = model.changedAttributes()
                if ( newAttr.filter ) {
                    this.setFilter(newAttr.filter)
                } else if ( newAttr.view ) {
                    this.setView(newAttr.view)
                } else if ( newAttr.sort ) {
                    this.setSort(newAttr.sort)
                }
                Backbone.dispatcher.trigger('savehistory')
            }, this)

            $(window).on('hashchange', function() {
                var hashObj = $.deparam.fragment()
                this.model.set(hashObj)
            }.bind(this))
        },

        setSort : function( sort ) {
            var currentOrder = this.model.get('sort'),
                currentView = this.model.get('view')
            if ( currentView === 'list' ) {
                this.list.render()
            } else if ( currentView === 'cover' ) {
                this.cover.sort( sort )
            }
        },

        setFilter : function( filter ) {
            var currentView = this.model.get('view')
            if ( currentView ===  'list' ) {
                this.list.render()
            } else if ( currentView === 'cover' ) {
                this.cover.filter(filter)
            }
            this.model.trigger('isotope:ready')
        },

        setView : function( view ) {
            if ( view === 'cover' ) {
                this.$el.html( this.cover.$el )
                this.cover.isotope({ gallery: false })
            } else if ( view === 'list' ) {
                this.cover.isotope('destroy')
                this.cover.$('.thumb').show()

                this.$el.html( this.list.render() )
            }
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
        },

        onClose : function() {
            if ( $.contains(this.cover.$el) ) {
                this.cover.$el.isotope('destroy')
            }
            this.cover.$('.thumb').show().find('img').removeClass('loaded')
            this.$el.removeClass('projects')
            this.model.off('change')
            this.model.clear({silent: true})
            $(window).off('hashchange')
            if (this.filterbar) {
                this.filterbar.close()
            }
        },

        jumpSet : function() {
            console.log('jumpSet')
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

        build : function(projects) {
            this.model.cover = this.cover = new G({
                projects : true,
                collection : new CoverGallery( Projects.pluck('coverImage') ),
                model : this.model
            })

            this.model.list = this.list = new l.List({
                collection : new CoverGallery( Projects.pluck('coverImage') ),
                pageClass : 'projects',
                path : 'projects',
                section : 'Projects',
                model : this.model
            })

            this.filterbar = new FilterBar({
                el : '#filter-bar',
                model : this.model,
                collection : Projects
            })

            Backbone.dispatcher.trigger('projects:ready')
        }
    })

    return new ProjectLanding({
        model : new Backbone.Model(),
        collection : Projects
    })
})
