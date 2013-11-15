/* app/views/partials/jumplist.js - Jump To List for use on Profile section */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst'
], function( $, Backbone, _, TPL ) {

    var JumpMenu = Backbone.View.extend({
        tagName : 'div',
        id : 'jump-to',
        className : 'jump-to date',
        template: TPL.jumps,

        initialize : function() {
            _.bindAll( this, 'jump', 'openMenu' )

            var $date = $('<ul />').attr('class', 'dates')

            $('.list section').each(function( idx, el ) {
                var li = document.createElement('li'),
                    $tag = $('<a />')
                $tag.attr('href', '#' + el.id).html(el.id)
                $tag.appendTo(li)
                $date.append(li)
            } )

            this.$el.append( this.template() )
            this.$('.wrapper').append($date)
        },

        render : function() {
            return this.el
        },

        events : {
            'click a' : 'jump',
            'click h3' : 'openMenu'
        },

        jump : function(e) {
            e.preventDefault()
            e.stopPropagation()
            $('.open').removeClass('open')
            $('html, body').animate({
                scrollTop : $(e.currentTarget.hash).offset().top - 115
            })
        },

        openMenu : function(e) {
            e.preventDefault()
            e.stopPropagation()
            $(e.target.parentElement).addClass('open')
        }

    })

    return JumpMenu
})

