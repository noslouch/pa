/* app/views/partials/grid.js - Photo Grid View partials */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst'
], function( $, Backbone, _, TPL ) {

    var GridThumb = Backbone.View.extend({
        tagName : 'div',
        className : 'four-column-cell',
        template : TPL.gridThumb,
        render : function() {
            var html = this.template({
                url : this.url(),
                thumb : this.model.get('thumb'),
                title : this.model.get('title'),
                summary : this.model.get('summary')
            })
            this.$el.append( html )
            return this.el
        }
    })

    var Grid = Backbone.View.extend({
        tagName : 'div',
        className: 'four-column showcase',
        rowTmpl : TPL.gridRow,
        $row : undefined,
        initialize : function() {
            if ( this.$el.children().length ) { return this.el }

            this.collection.forEach( function(model, index){
                if (index % 4 === 0) {
                    this.$row = $( this.rowTmpl() ).addClass(this.class)
                    this.$el.append(this.$row)
                }
                this.$row.append( new this.Thumb({
                    model : model
                }).render() )
            }, this )

        },

        render : function() {
            this.delegateEvents()
            this.$('.four-column-row').imagesLoaded( function() {
                $('.four-column-row').addClass('loaded')
            })
            return this.el
        }
    })

    var GridPage = Backbone.View.extend({
        initialize : function(){
            _.bindAll( this, 'render', 'navigate' )
            var self = this

            this.collection.fetch({
                success : function(collection) {
                    self.grid = new self.Grid({
                        collection : collection
                    })

                    Backbone.dispatcher.trigger(self.class + ':ready')
                }
            })
        },

        events : {
            'click .four-column-cell a' : 'navigate'
        },

        render : function() {
            this.$el.html( this.grid.render() )
        },

        onClose : function() {
            $('.page').removeClass(this.class)
        },

        init : function(spinner) {
            this.$el.addClass(this.class)

            if ( !this.collection.length ) {
                throw {
                    message : 'Not Loaded Yet',
                    type : 'EmptyCollection'
                }
            }

            if (spinner) {spinner.detach()}
            this.render()
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
        }
    })

    return {
        Grid : Grid,
        Thumb : GridThumb,
        Page : GridPage
    }

})
