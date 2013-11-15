/* app/views/sections/stream.js
 * Stream page */
'use strict';

define([
    'backbone',
    'underscore',
    'jquery',
    'app/collections/instagrams',
    'app/views/showcases/starfield'
], function( Backbone, _, $, IG, Starfield ) {

    var Stream = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'render', 'renderOut' )
            this.collection.fetch()
            this.starfield = new Starfield({ collection : this.collection }, true)
        },
        render : function(spinner){
            this.spinner = spinner
            try {
                this.renderOut()
            } catch(e) {
                this.collection.on('sync', this.renderOut)
            }
        },
        renderOut : function() {
            this.$el.html( this.starfield.render() )
            this.spinner.detach()
        }
    })

    return new Stream({ collection : IG })

})
