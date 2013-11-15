/* app/views/film.js
 * Single Film detail view */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/views/showcases/video',
    'app/models/film'
], function( $, Backbone, _, TPL, V, FilmModel ) {

    var FilmDetails = Backbone.View.extend({
        template : TPL.textTemplate, // type, content
        header : TPL.textTemplateHeader, // title, htmlDate, date
        back : TPL.backButton, // buttonText, url
        render : function() {
            var $article = $( this.template({
                type : 'film',
                content : this.model.get('content')
            }) )
            $article.prepend( this.header({
                title : this.model.get('title'),
                htmlDate : this.model.get('htmlDate'),
                date : this.model.get('date').year()
            }) ).append( this.back({
                buttonText : 'View All Film',
                url : '/film'
            }) )

            this.$el.append($article)
        }
    })

    var Film = Backbone.View.extend({
        tagName : 'div',
        className : 'film viewer',
        baseTmpl : TPL.viewer,
        initialize : function() {
            _.bindAll( this, 'render', 'renderOut' )
            this.model = new FilmModel()
            this.$el.html( this.baseTmpl() )
            this.details = new FilmDetails({
                el : this.$('#details'),
                model : this.model
            })
            this.$viewer = this.$('#showcaseContainer')
        },

        events : {
            'click #back' : 'back'
        },

        render : function( filmUrl ) {
            this.delegateEvents()
            this.details.$el.empty()
            this.$viewer.empty()

            this.model.fetch({
                url : '/api/film/' + filmUrl,
                success : this.renderOut
            })

            return this.el
        },

        renderOut : function( model, response, ops ) {
            this.details.render()
            var video = new V({
                model : this.model
            })
            this.$viewer.html( video.render() )
            this.trigger( 'rendered' )
        },

        back : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:section', e)
            Backbone.dispatcher.trigger('film:goBack')
        }

    })

    return new Film()
})
