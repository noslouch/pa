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
    //'app/views/showcases/starfield',
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

            this.collection.fetch({
                success : function(projects) {

                    self.model.cover = self.cover = new G({
                        projects : true,
                        //cover : true,
                        collection : new CoverGallery( projects.pluck('coverImage') ),
                        //path : 'projects',
                        model : self.model
                    })

                    self.model.list = self.list = new l.List({
                        collection : new CoverGallery( projects.pluck('coverImage') ),
                        pageClass : 'projects',
                        path : 'projects',
                        section : 'Projects',
                        model : self.model
                    })

                    // self.model.random = new Starfield({
                    //     collection : self.model.cover.collection
                    // })

                    self.filterbar = new FilterBar({
                        el : '#filter-bar',
                        model : self.model,
                        collection : projects
                    })

                    Backbone.dispatcher.trigger('projects:ready')
                }
            })
        },

        events : {
            'click .showcase a' : 'navigate'
        },

        init : function(spinner) {
            var hashObj = $.deparam.fragment()

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
            //var spinner = new Spinner()

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
                this.$el.html( this.list.render() )

                this.cover.$el.find('.thumb').each(function(i, el) {
                    el.style.display = ''
                    $(el).find('img').removeClass('loaded')
                })
            }
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
            //this.collection.get( e.currentTarget.id ).activate()
        },

        onClose : function() {
            this.cover.$el.isotope('destroy')
            this.cover.$('.thumb').show()
            //this.model.unset('sort').unset('filter').unset('view')
            this.$el.removeClass('projects')
            this.model.off('change')
            this.model.clear({silent: true})
            if (this.filterbar) {
                this.filterbar.close()
            }
            //$(window).off('hashchange')
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
    })

    return new ProjectLanding({
        model : new Backbone.Model(),
        collection : Projects
    })
})
