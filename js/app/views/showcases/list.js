/* app/views/showcases/lists.js
 * Variations on a List view */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst'
], function( $, Backbone, _, TPL ) {

    // Li
    // List Showcase LI element
    var Li = Backbone.View.extend({
        tagName : 'li',
        template : TPL.listItemPartial,

        initialize : function(model, options) {
            _.bindAll( this, 'toggle' )
        },

        events : {
            //'click a' : 'toggle'
        },

        toggle : function(e) {
            if ( !$(e.currentTarget).parents('.projects').length ) {
                e.preventDefault()
                this.model.activate()
            }
        },

        render : function() {
            this.$el.html( this.template({
                id : this.model.id,
                title : this.model.get('title'),
                summary : this.model.get('showcases') ? '' : this.model.get('summary'),
                url : this.model.get('url-title'),
                //url : this.options.url,
                path : this.options.path
            }) )
            return this.el
        }
    })

    // ListSect
    // List Showcase Section element container
    var ListSect = Backbone.View.extend({
        tagName : 'section',
        header : TPL.listHeaderPartial,

        render : function() {
            var listItems = this.options.listItems,
                path = this.options.path,
                date = this.options.date,
                url = this.options.url === false ? false : true

            this.$el.append( '<ul />')
            this.$('ul').append( this.header({ 
                htmlDate : date,
                date : date 
            }) )

            _.each( listItems, function(listItem) {
                this.$('ul')
                    .append( new Li({
                        model : listItem,
                        path : path ? path : '',
                        url : url ? listItem.get('url-title') : false
                    }).render() )
            }, this )

            return this.el
        }

    })

    // List
    // Outer List showcase made up of ListSects that contain Li views
    var List = Backbone.View.extend({
        tagName : 'div',
        className : 'showcase list',
        initialize : function() {
            _.bindAll( this, 'filter', 'groupSort', 'render', 'stagger' )
        },

        render : function(){
            var filtered,
                group
            try {
                filtered = this.filter( this.model.get('filter') )
                group = this.groupSort( this.model.get('sort'), filtered )
            } catch(e) {
                group = this.groupSort( 'date', this.collection.models )
            }

            this.$el.empty()
            _.each( group, function(v,k){
                var html = new ListSect({
                    id : v[0],
                    date : v[0],
                    listItems : v[1],
                    path : this.options.path,
                    url : this.options.url
                })
                this.$el.append( html.render() )
            }, this )

            setTimeout( this.stagger, 50 )
            return this.el
        },

        stagger : function() {
            var $li = this.$('li'),
                delay = 25

            $li.each(function(i,el){
                setTimeout(function(){
                    $(el).addClass('loaded')
                }, i*delay )
            })
        },

        filter : function( filter ) {
            if ( filter === '*' ) {
                return this.collection.models
            }
            // filter project cover
            return this.collection.filter( function( cover ){
                return _.find( cover.get('tags'), function(tag) {
                    return tag.className === filter.slice(1)
                } )
            } )
        },

        groupSort : function( method, toGroup )  {
            // sort project covers according to specified dimension
            var groupObj = _.groupBy( toGroup, function(model) {
                if ( method === 'date' ) {
                    return model.get('year') || model.get('date').year()
                } else if ( method === 'name' ) {
                    return model.get('title')[0]
                } else {
                    throw 'Invalid sort dimension'
                }
            })
            var groupArray = _.pairs( groupObj )
            var grouped =  _.sortBy( groupArray, function(item){
                return item[0]
            } )
            return grouped
        },

        jump : function(jump) {
            $('html, body').animate({
                scrollTop : $('#' + jump).offset().top - 200
            })
        }
    })

    // SmallList
    // Stripped down variant of List
    var SmallList = Backbone.View.extend({
        tagName : 'div',
        id : 'showcase',
        className : 'showcase list',
        initialize : function() {},
        render : function() {
            this.$el.append('<ul/>')
            _.each( this.collection, function(el, idx) {
                var li = document.createElement('li'),
                    a = document.createElement('a'),
                    h4 = document.createElement('h4')

                $(a).attr('href', el.url).text(el.title)
                $(a).appendTo(h4)
                $(h4).appendTo(li)
                this.$('ul').append(li)
            }, this )

            return this.el
        }

    })

    return {
        List : List,
        SmList : SmallList
    }

})
