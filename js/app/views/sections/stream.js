/* app/views/sections/stream.js
 * Stream page */
'use strict';

define([
    'backbone',
    'underscore',
    'jquery',
    'app/collections/covergallery',
    'app/collections/projects',
    'app/views/showcases/starfield'
], function( Backbone, _, $, Covers, Projects, Starfield ) {

    var Stream = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'render', 'renderOut' )
            this.collection.fetch({
                success : function(projects) {
                    this.starfield = new Starfield({
                        collection : new Covers( projects.pluck('coverImage') )
                    })
                }.bind(this)
            })
        },
        render : function(spinner){
            this.spinner = spinner
            if (this.collection.length) {
                this.renderOut()
            } else {
                this.collection.on('sync', this.renderOut)
            }
        },
        renderOut : function() {
            this.$el.html( this.starfield.render() )
            this.spinner.detach()
        },
        onClose : function() {
            this.starfield.destroy()
        }
    })

    return new Stream({ collection : Projects })

})
