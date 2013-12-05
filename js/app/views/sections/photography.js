/* app/views/sections/photography.js - Main Photography page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    //'app/collections/covergallery',
    'app/collections/photography',
    'tpl/jst',
    'imagesLoaded'
    //'app/views/showcases/gallery'
], function( $, Backbone, _, photoCollection, G ) {

    // PhotoThumb
    var PhotoThumb = Backbone.View.extend({
        tagName : 'div',
        className : 'four-column-cell',
        template : G.filmThumb,
        render : function() {
            var html = this.template({
                url : '/photography/' + this.model.get('url-title'),
                thumb : this.model.get('cover')['thumb'],
                title : this.model.get('title'),
                summary : this.model.get('summary')
            })
            this.$el.append( html )
            return this.el
        }
    })

    // PhotoGrid
    var PhotoGrid = Backbone.View.extend({
        tagName : 'div',
        className: 'four-column showcase',
        rowTmpl : G.filmRow,
        $row : undefined,
        initialize : function() {
            if ( this.$el.children().length ) { return this.el }

            this.collection.forEach( function(model, index){
                if (index % 4 === 0) {
                    this.$row = $( this.rowTmpl() )
                    this.$el.append(this.$row)
                }
                this.$row.append( new PhotoThumb({
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

    var Photography = Backbone.View.extend({
        initialize : function(){
            _.bindAll( this, 'render', 'navigate' )
            var self = this

            this.collection.fetch({
                success : function(collection) {
                    self.covers = new PhotoGrid({
                        collection : collection
                    })
                    //self.covers = new G({
                    //    cover : true,
                    //    collection : new CoverGallery( collection.pluck( 'coverImage' ) ),
                    //    path : 'photography',
                    //    model : new Backbone.Model()
                    //})

                    Backbone.dispatcher.trigger('photography:ready')
                }
            })
        },

        events : {
            'click .showcase a' : 'navigate'
        },

        render : function() {
            this.$el.html( this.covers.render() )
        },

        onClose : function(){
            $('.page').removeClass('photography')
        },

        init : function(spinner) {
            this.$el.addClass('photography')

            if ( !this.collection.length ) {
                throw {
                    message : 'Photos aren\'t loaded.',
                    type : 'EmptyCollection'
                }
            }

            if (spinner){spinner.detach()}
            this.render()
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
        }
    })

    return new Photography({
        model : new Backbone.Model(),
        collection : photoCollection
    })
})
