/* utils/spinner.js
 * spinner class */
'use strict';

define([
    'jquery'
], function( $ ) {

    function Spinner(){
        var self = this,
            $loader = $('<div/>').addClass('loader').attr('id', 'loader'),
            $t = $('<div/>').addClass('table'),
            $tc = $('<div/>').addClass('table-cell')

        $tc.append('<span/>')
        $t.append($tc)
        $loader.append($t)

        this.detach = function() {
            $loader.detach()
            console.log('detached at', Date.now())
        }

        this.append = function() {
            $('body').append($loader)
        }

        this.append()

    }

    return Spinner
})

