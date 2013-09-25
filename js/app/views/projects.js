/* app/views/projects.js
 * projects landing page - shit is complex! */
'use strict';

define([
    'require',
    'jquery',
    'backbone',
    'underscore',
    //'app/views/showcaseviews',
    'app/views/filterviews',
    'foundation',
    'tooltips',
    'lib/requirejs/domReady!'
], function( require, $, Backbone, _, FilterBar ) {

    var ProjectLanding = Backbone.View.extend({
        initialize : function() {
            var self = this

            //require(['app/views/filterviews'],
            //function( FilterBar ) {
                self.filter = new FilterBar({
                    el : '#filter-bar',
                    model : self.model
                })
            //})

            this.setElement('.page')
            this.outlineTitle = this.$('h2')

            _.bindAll( this, 'render' )

            $(window).on('hashchange', this.render)
            Backbone.dispatcher.on('hashchange', this.render)
            this.debug()
        },

        semantics : function( className, outlineTitle ) {
            this.$el.addClass( className || '' )
            this.outlineTitle.html( outlineTitle || '' )
            this.$el.prepend( this.outlineTitle )
        },

        render : function() {
            var hashObj = $.deparam.fragment()
            hashObj.filter = hashObj.filter || '*'
            hashObj.view = hashObj.view || 'cover'
            hashObj.sort = hashObj.sort || 'name'

            this.model.set( hashObj )

            this.$el.html( this.model[hashObj.view].render() )

            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )

        },

        debug : function() {
            window.projects = this.model.get('projects')
            window.chrome = this.model
        },

        eventWatcher : function() {
            console.log('event triggered')
            console.log('this: ', this)
            console.log('arguments: ', arguments)
        },

        hashHandler : function() {
            var hashObj = $.deparam.fragment()
            this.model.set( hashObj )
            this.render( this.model, this.model[hashObj.view] )
        }
            /*
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

            // MIGHT BE A BETTER WAY TO DO THIS
            $('html, body').animate({ scrollTop : 0 })
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

            // MIGHT BE A BETTER WAY TO DO THIS
            $('html, body').animate({ scrollTop : 0 })
        },

        projectSort : function( pageModel, sort ) {
            console.log('change:sort handler')

            if ( !(pageModel.get('showcase') instanceof S.List) ) {
                pageModel.get('showcase').destroy()
                pageModel.set( 'showcase' , this.model.titles )
            }

            pageModel.get('showcase').render(sort)

            // MIGHT BE A BETTER WAY TO DO THIS
            $('html, body').animate({ scrollTop : 0 })
        },

        projectJump : function( pageModel, jump ) {
            console.log('change:jump handler')

            if ( !(pageModel.get('showcase') instanceof S.List) ) {
                pageModel.get('showcase').destroy()
                pageModel.set( 'showcase', this.model.titles )
            }

            pageModel.get('showcase').trigger('jump', jump)
        },
        */
    })

    $(document).foundation()
    return ProjectLanding
})
