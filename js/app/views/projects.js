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
            this.filter = new FilterBar({
                el : '#filter-bar',
                model : this.model
            })

            this.setElement('.page')
            this.outlineTitle = this.$('h2')

            _.bindAll( this, 'render' )

            $(window).on('hashchange', this.render)
            Backbone.dispatcher.on('hashchange', this.render)
        },

        semantics : function( className, outlineTitle ) {
            this.$el.addClass( className || '' )
            this.outlineTitle.html( outlineTitle || '' )
            this.$el.prepend( this.outlineTitle )
        },

        render : function() {
            var hashObj = $.deparam.fragment()
            if ( this.model.get('view') === 'random' && hashObj.view === 'random' ) {
                // random view is currently running
                $.bbq.pushState({ view : 'cover' })
                return
            } else {
                hashObj.filter = hashObj.filter || '*'
                hashObj.view = hashObj.view || 'cover'
                hashObj.sort = hashObj.sort || 'name'
            }
            this.model.set( hashObj )
            this.$el.html( this.model[hashObj.view].render() )
            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )
        },

        debug : function() {
            window.projects = this.model.get('projects')
            window.chrome = this.model
        },
    })

    $(document).foundation()
    return ProjectLanding
})
