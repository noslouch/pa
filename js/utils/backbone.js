/* utils/backbone.js
 * custom backbone extension */
'use strict';

define([
    'lib/backbone/backbone-amd.min',
    'moment',
    'underscore'
], function( Backbone, moment, _ ) {

    Backbone.View.prototype.close = function() {
        this.$el.empty()
        this.unbind()
        if ( this.onClose ) {
            this.onClose()
        }
    }

    Backbone.Model.prototype.makeHtmlDate = function(dateString, onlyYear) {
        var res = [],
            d = parseInt(dateString, 10)

        d = new Date(d)

        res[0] = d.getFullYear()
        if (onlyYear) {
            return res[0]
        }
        res[1] = d.getMonth() + 1
        res[2] = d.getDate()
        return res.join("-")
    }

    Backbone.Model.prototype.parseDate = function(dateString) {
        return moment( new Date( parseInt(dateString, 10) ) )
    }

    Backbone.dispatcher = _.extend( {}, Backbone.Events )

    return Backbone
})
