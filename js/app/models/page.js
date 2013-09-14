/* models/page.js - Page Model */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.PageModel = Backbone.Model.extend({
    initialize : function() {
        // render Jump To menu

    },
    debug : function(){
        console.log('debug')
        console.log('arguments: ', arguments)
        console.log('this: ', this)
    }
})
