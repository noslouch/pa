/* DEPRECATED
 **************************************************************
/* app/views/details/photo.js
 * detail view for photo galleries
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcases/gallery',
    'app/models/photo'
], function( $, Backbone, _, TPL, G, AlbumModel ) {

    var PhotoDetails = A.Details.extend({
        buttonText : 'Back to All Photography',
        url : '/photography'
    })

    var PhotoAlbum = A.Album.extend({
        model : new AlbumModel(),
        url : '/api/photography/',
        namespace : 'photography'
    })

//    var Album = Backbone.View.extend({
//        tagName : 'div',
//        className : 'photo viewer',
//        baseTmpl : TPL.viewer,
//        initialize : function() {
//            _.bindAll( this, 'render', 'renderOut' )
//            this.model = new AlbumModel()
//        },
//        events : {
//            'click #back' : 'back'
//        },
//
//        render : function( albumUrl ) {
//            this.$el.html( this.baseTmpl() )
//            this.details = new AlbumDetails({
//                el : this.$('#details'),
//                model : this.model
//            })
//            this.$viewer = this.$('#showcaseContainer')
//            this.delegateEvents()
//
//            this.model.fetch({
//                url : '/api/photography/' + albumUrl,
//                success : this.renderOut
//            })
//
//            return this.el
//        },
//
//        renderOut : function( model, response, ops ) {
//            this.model.set('type', 'gallery')
//            this.collection = this.model.get('photos')
//
//            this.details.render({
//                collection : this.collection
//            })
//
//            var gallery = new G({
//                model : this.model,
//                collection : this.collection
//            })
//
//            this.$viewer.html( gallery.render() )
//
//                var projectTitle = this.model.get('title')
//                $('#showcaseContainer a').each(function(idx, el) {
//                    $(el).attr('title', ( el.title ? projectTitle + ': ' + el.title : projectTitle ))
//                })
//            this.trigger( 'rendered' )
//        },
//
//        back : function(e) {
//            e.preventDefault()
//            Backbone.dispatcher.trigger('navigate:section', e)
//            Backbone.dispatcher.trigger('photography:goBack')
//        }
//
//    })

    return new Album()
})
*/
