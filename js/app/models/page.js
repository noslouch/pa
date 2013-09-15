/* app/models/page.js - Page Model */
'use strict';

define([
    'backbone'
], function( Backbone ) {

    var PageModel = Backbone.Model.extend({
        initialize : function() {
            // render Jump To menu

        },
        debug : function(){
            console.log('debug')
            console.log('arguments: ', arguments)
            console.log('this: ', this)
        }
    })

    return PageModel
})
