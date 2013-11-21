/* app/views/partials/jumplist.js - Jump To List for use on Profile section */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst'
], function( $, Backbone, _, TPL ) {

    var mobile = 'ontouchstart' in window

    var JumpMenu = Backbone.View.extend({
        tagName : 'div',
        id : 'jump-to',
        className : 'jumps date',
        template: mobile ? TPL.mobileJumps : TPL.jumps,

        initialize : function() {
            _.bindAll( this, 'jump', 'openMenu' )
        },

        render : function() {

            this.$el.append( this.template() )

            if ( mobile ) {
                var $select = this.$('select')

                $('.list section').each(function( idx, el ) {
                    var $op = $('<option/>').attr('value', el.id).html(el.id)
                    $select.append($op)
                } )
            } else {
                var $date = $('<ul />').attr('class', 'dates')
                this.$('.wrapper').append($date)

                $('.list section').each(function( idx, el ) {
                    var li = document.createElement('li'),
                        $tag = $('<a />')

                    $tag.attr('href', '#' + el.id).html(el.id)
                    $tag.appendTo(li)
                    $date.append(li)
                })
            }

            return this.el
        },

        events : {
            'click a' : 'jump',
            'click h3' : 'openMenu',
            'change select' : 'jump'
        },

        jump : function(e) {
            var jump

            try {
                jump = e.type === 'change' ? '#' + e.currentTarget.selectedOptions[0].value : e.currentTarget.hash
            } catch(err) { return false }

            e.preventDefault()
            e.stopPropagation()
            //$('.open').removeClass('open')
            $('html, body').animate({
                scrollTop : $(jump).offset().top - 115
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

