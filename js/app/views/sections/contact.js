/* app/views/sections/contact.js
 * contact page */
'use strict';

define([
    'backbone',
    'underscore',
    'jquery'
], function( Backbone, _, $ ){

    var Contact = Backbone.View.extend({
        render : function(){
            var $wrapper = $('<div/>').addClass('wrapper'),
                $phone = $('<h3/>').addClass('phone').text('(212) 260-3631'),
                $email = $('<h3/>').addClass('email'),
                $link = $('<a/>').attr('href', 'mailto:hello@peterarnell.com').text('hello@peterarnell.com')

            $email.append($link)
            $wrapper.append($phone).append($email)
            this.$el.append($wrapper).addClass('contact')
            //$('.page').addClass('contact')
        },

        onClose : function() {
            $('.page').removeClass('contact')
            $('.inner-header').removeClass('contact')
        }
    })

    return new Contact()
})
