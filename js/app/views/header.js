/* DEPRECATED
 * *****************************************************
/* app/views/chromeviews.js
 * outer most appviews *
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'bbq'
    //'app/router'
], function( $, Backbone, _, bbq ) {

    var Header = Backbone.View.extend({
        initialize: function() {
            //var r = require('app/router')
            //this.listenTo( r.router, 'route', this.toggle )
        },

        toggle : function( methodName, urlParam ){
            if ( methodName === 'home' ) {
            }
        }
    })

    return Header

})
*/
