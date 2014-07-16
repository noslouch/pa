/* app/views/partials/album.js
 * detail view for image galleries in photography and books */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcases/gallery',
    'utils/spinner'
], function( $, Backbone, _, TPL, G, Spinner ) {

    var AlbumDetails = Backbone.View.extend({
        template : TPL.textTemplate, // type, content
        header : TPL.textTemplateHeader, // title, htmlDate, date
        back : TPL.backButton, // buttonText, url
        render : function() {
            var $article = $( this.template({
                type : null,
                content : this.model.get('content')
            }) ).prepend( this.header({
                title : this.model.get('title'),
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year()
            }) ).prepend( this.back({
                buttonText : this.buttonText,
                url : this.url
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
        },
        events : {
            'click #back' : 'back'
        },

        render : function( albumUrl ) {
            this.$el.html( this.baseTmpl() )
            this.details = new this.Details({
                el : this.$('#details'),
                model : this.model
            })
            this.$viewer = this.$('#showcaseContainer')
            this.delegateEvents()

            this.model.fetch({
                url : this.url + albumUrl,
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

            var gallery = new G({
                model : this.model,
                collection : this.collection
            })

            this.$viewer.html( gallery.render({ gallery : true }) )

                var projectTitle = this.model.get('title')
                $('#showcaseContainer a').each(function(idx, el) {
                    $(el).attr('title', ( el.title ? projectTitle + ': ' + el.title : projectTitle ))
                })
            this.trigger( 'rendered' )
        },

        back : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:section', e)
            //Backbone.dispatcher.trigger( 'goBack', new Spinner(), this.namespace )
        }

    })

    return {
        Details : AlbumDetails,
        Album : Album
    }
})
