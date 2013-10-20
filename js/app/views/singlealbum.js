/* app/views/singleviews.js
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
            this.$el.html( this.baseTmpl() )
            this.details = new AlbumDetails({
                el : this.$('#details'),
                model : this.model
            })
        },

        render : function(options) {
            this.details.render()
            this.model.set('type', 'gallery')
            var gallery = new S.Image({
                collection : this.model.get('photos'),
                model : this.model
            })
            this.$('#showcaseContainer').html( gallery.render({ container : this.$('#showcaseContainer') }) )
            //gallery.firstLoad()

            return this.el
        }
    })

    return new Album()
})
