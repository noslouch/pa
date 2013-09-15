/* app/views/projects.js
 * projects landing page - shit is complex! */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcaseviews',
    'app/views/filterviews'
], function( $, Backbone, _, S ) {

    var ProjectLanding = Backbone.View.extend({
        initialize : function() {
            this.setElement('.page')
            this.outlineTitle = this.$('h2')
            var FilterBar = require('app/views/filterviews')

            this.filter = new FilterBar({
                el : '#filter-bar',
                model : this.model
            })

            _.bindAll( this, 'hashHandler', 'projectFilter', 'projectView', 'projectSort', 'projectJump' )

            this.listenTo( this.model, 'change:showcase', this.render )
            this.listenTo( this.model, 'change:filter', this.projectFilter )
            this.listenTo( this.model, 'change:view', this.projectView )
            this.listenTo( this.model, 'change:sort', this.projectSort )
            this.listenTo( this.model, 'change:jump', this.projectJump )

            $(window).on('hashchange', this.hashHandler)
            Backbone.dispatcher.on('hashchange', this.hashHandler)
        },

        semantics : function( className, outlineTitle ) {
            this.$el.addClass( className || '' )
            this.outlineTitle.html( outlineTitle || '' )
            this.$el.prepend( this.outlineTitle )
        },

        render : function( pageModel, pageView, filtering ) {
            this.$el.html( pageView.render() )
            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )

            require(['app/views/showcaseviews'], function(S) {
                if ( pageModel.get('showcase') instanceof S.Image ) {
                    console.log('instanceof S.Image: loading isotope')

                    pageModel.get('showcase').firstLoad()
                    if ( !filtering ) {
                        pageModel.set('filter', '*')
                    }
                } else if ( pageModel.get('showcase') instanceof S.List ) {
                    console.log('instanceof S.List: sorting by name')

                    //pageModel.get('showcase').set( 'sort', 'alpha' )
                    //pageModel.set( 'sort', 'alpha' )
                }
            })
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

        projectFilter : function( pageModel, filter ) {
            console.log('change:filter handler')

            if ( !(pageModel.get('showcase') instanceof S.Image) ) {
                try {
                    pageModel.get('showcase').destroy()
                } catch(e) { }
                // catching for page loads with a hash 

                // Don't need to call filter in PageView.render
                pageModel.set({
                    showcase : this.model.covers
                }, {
                    filtering : true
                })
            }

            pageModel.get('showcase').trigger('filter', filter)
        },

        projectView : function( pageModel, view ) {
            console.log('change:view handler')

            pageModel.set( 'showcase', this.model[view] )
            if ( pageModel.get('showcase') instanceof S.Image ) {
                console.log('instanceof S.Image: filtering for *')

                pageModel.get('showcase').filter('*')
            } else if ( pageModel.get('showcase') instanceof S.List ) {
                console.log('instanceof S.List: sorting by name')

                pageModel.set('sort', 'alpha')
            }
        },

        projectSort : function( pageModel, sort ) {
            console.log('change:sort handler')

            if ( !(pageModel.get('showcase') instanceof S.List) ) {
                pageModel.get('showcase').destroy()
                pageModel.set( 'showcase' , this.model.titles )
            }

            pageModel.get('showcase').render(sort)
        },

        projectJump : function( pageModel, jump ) {
            console.log('change:jump handler')

            if ( !(pageModel.get('showcase') instanceof S.List) ) {
                pageModel.get('showcase').destroy()
                pageModel.set( 'showcase', this.model.titles )
            }

            pageModel.get('showcase').trigger('jump', jump)
        },
    })

    return ProjectLanding
})
