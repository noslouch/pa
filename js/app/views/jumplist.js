/* views/filterviews.js - Filter Bar View */
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

            var $date = $('<ul />').attr('class', 'dates'),
                byDate = this.collection.groupBy(function(model) {
                    return model.get('date').year()
                })

            _.each( byDate, function( model, date ) {
                var li = document.createElement('li'),
                    $tag = $('<a />')
                $tag.attr('href', '#' + date).html(date)
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
                scrollTop : $(e.currentTarget.hash).offset().top - 100
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

