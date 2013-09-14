/* app/views/projects.js
 * projects landing page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'bbq',
    'app/views/filterviews',
    'app/views/showcaseviews',
    'app/collections/projects',
    'app/collections/covergallery'
], function( $, Backbone, _, bbq, FilterBar, cases, Projects, CoverGallery ) {

    var ProjectsLanding = Backbone.View.extend({ 
        initialize: function() {
            _.bindAll( this, 'render', 'singleView' )

            this.outlineTitle = $('<h2/>').addClass('visuallyhidden')
            this.$el.prepend(this.outlineTitle)

            this.filterBar = new FilterBar({ model : this.model })

            this.listenTo( this.model, 'change:showcase', this.render )

            this.listenTo( this.model, 'change:filter', this.filter )
            this.listenTo( this.model, 'change:view', this.view )
            this.listenTo( this.model, 'change:sort', this.sort )
            this.listenTo( this.model, 'change:jump', this.jump )

            $(window).on('hashchange', this.hashHandler)

            this.model.covers = new cases.ImageShowcase({
                cover : true,
                collection : new CoverGallery( Projects.pluck('coverImage') ),
                path : 'projects'
            })

            this.model.titles = new cases.ListShowcase({
                // refactor other lists so they don't use grouped Collection
                groupedCollection : Projects.groupBy('date'),
                collection : Projects,
                pageClass : 'projects',
                section : 'Projects'
            })

            this.model.random = new cases.Starfield({
                collection : this.model.covers.collection
            })

            this.model.set({
                className : 'projects',
                outlineTitle : 'Projects'
            })

            if ( document.location.hash ) {
                var hashObj = $.deparam.fragment()
                if ( hashObj.filter || hashObj.view === 'covers' ) {
                    this.model.set( 'showcase' , this.model.covers )
                } else {
                    this.model.set( 'showcase', this.model.titles )
                }
            } else {
                //$.bbq.pushState( { view : 'random' }, 2 )
            }
        },

        hashHandler : function() {
            var hashObj = $.deparam.fragment()

            if ( hashObj.filter ) {
                console.log('change:hashchange:filter handler')

                this.model.unset('filter', {silent : true} )
                this.model.set( 'filter', hashObj.filter )
            } else if ( hashObj.view ) {
                console.log('change:hashchange:view handler')

                this.model.unset('view', {silent : true} )
                this.model.set( 'view', hashObj.view )
            } else if ( hashObj.sort ) {
                console.log('change:hashchange:sort handler')

                this.model.unset('sort', {silent : true} )
                this.model.set( 'sort', hashObj.sort )
            } else if ( hashObj.jump ) {
                console.log('change:hashchange:jump handler')

                this.model.unset('jump', {silent : true} )
                this.model.set( 'jump', hashObj.jump )
            }
        },

        render : function() {

        },

        filter : function( pageModel, filter ) {
            console.log('change:filter handler')

            if ( !(pageModel.get('showcase') instanceof cases.ImageShowcase) ) {
                pageModel.get('showcase').destroy()

                // Don't need to call filter in PageView.render
                pageModel.set({
                    showcase : this.model.covers
                }, {
                    filtering : true
                })
            }

            pageModel.get('showcase').trigger('filter', filter)
        },

        view : function( pageModel, view ) {
            console.log('change:view handler')

            pageModel.set( 'showcase', this.model[view] )
            if ( pageModel.get('showcase') instanceof cases.ImageShowcase ) {
                console.log('instanceof ImageShowcase: filtering for *')

                pageModel.get('showcase').filter('*')
            } else if ( pageModel.get('showcase') instanceof cases.ListShowcase ) {
                console.log('instanceof ListShowcase: sorting by name')

                pageModel.set('sort', 'alpha')
            }
        },

        sort : function( pageModel, sort ) {
            console.log('change:sort handler')

            if ( !(pageModel.get('showcase') instanceof cases.ListShowcase) ) {
                pageModel.get('showcase').destroy()
                pageModel.set( 'showcase' , this.model.titles )
            }

            pageModel.get('showcase').render(sort)
        },

        jump : function( pageModel, jump ) {
            console.log('change:jump handler')

            if ( !(pageModel.get('showcase') instanceof cases.ListShowcase) ) {
                pageModel.get('showcase').destroy()
                pageModel.set( 'showcase', this.model.titles )
            }

            pageModel.get('showcase').trigger('jump', jump)
        },
    })

    return ProjectsLanding
})
