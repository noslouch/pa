/* app/views/singlealbum.js
 * detail view for photo galleries */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcaseviews',
    'app/models/album'
], function( $, Backbone, _, TPL, S, AlbumModel ) {

    var AlbumDetails = Backbone.View.extend({
        template : TPL.textTemplate, // type, content
        header : TPL.textTemplateHeader, // title, htmlDate, date
        back : TPL.backButton, // buttonText, url
        render : function() {
            var $article = $( this.template({
                type : 'photo',
                content : this.model.get('summary')
            }) ).prepend( this.header({
                title : this.model.get('title'),
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year()
            }) ).append( this.back({
                buttonText : 'Back to All Photography',
                url : '/photography'
            }) )

            this.$el.append($article)
        }
    })

    var Album = Backbone.View.extend({
        tagName : 'div',
        className : 'photo viewer',
        baseTmpl : TPL.viewer,
        initialize : function() {
            _.bindAll( this, 'render', 'renderOut' )
            this.model = new AlbumModel()
            this.$el.html( this.baseTmpl() )
            this.details = new AlbumDetails({
                el : this.$('#details'),
                model : this.model
            })
            this.$viewer = this.$('#showcaseContainer')
        },

        render : function( albumUrl ) {
            this.details.$el.empty()
            this.$viewer.empty()

            this.model.fetch({
                url : '/api/photography/' + albumUrl,
                success : this.renderOut
            })

            return this.el
        },

        renderOut : function( model, response, ops ) {
            this.model.set('type', 'gallery')
            this.collection = this.model.get('photos')

            this.details.render({
                collection : this.collection
            })

            var gallery = new S.Image({
                model : this.model,
                collection : this.collection
            })

            this.$viewer.html( gallery.render() )

            this.trigger( 'rendered' )
        }
    })

    return new Album()
})
