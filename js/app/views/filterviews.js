/* views/filterviews.js - Filter Bar View */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'bbq',
    'tpl/jst',
    'app/views/showcaseviews',
    'app/collections/projects'
], function( $, Backbone, _, bbq, TPL, S, Projects ) {

    var LogoBtns = Backbone.View.extend({
        tagName : 'div',
        className : 'controls',
        template : TPL.controlsPartial,
        initialize : function() {
            _.bindAll( this, 'toggleActive' )

            this.$el.html( this.template() )
            this.listenTo( Backbone.dispatcher, 'brandToggler', this.toggleActive )
        },

        render : function() {
            return this.el
        },

        events : {
            'click button' : function(e) {
                e.preventDefault()
                e.stopPropagation()
                Backbone.dispatcher.trigger('brandToggler')
            }
        },

        toggleActive : function(pageModel, id) {
            this.$('button').toggleClass('active')
        }

    })

    var LogoLi = Backbone.View.extend({
        tagName : 'li',
        logoTemplate : TPL.logoPartial,
        nameTemplate : TPL.namePartial,
        render : function() {
            this.$el.append( this.logoTemplate({
                tagFilter : this.options.tagObj.className,
                tag : this.options.tagObj.title,
                logo: this.options.tagObj.logo
            }) )
            this.$el.append( this.nameTemplate({
                tagFilter : this.options.tagObj.className,
                tag : this.options.tagObj.title
            }) )
            return this.el
        }
    })

    var LogoUl = Backbone.View.extend({
        tagName : 'ul',
        id : 'brandList',
        className : 'icons',
        initialize : function() {
            this.listenTo( Backbone.dispatcher, 'brandToggler', function(mdl, id) {
                this.$el.toggleClass('icons')
                this.$el.toggleClass('names')
            })
        },
        render : function() {
            var tags = this.collection
                .pluck('brand_tags')
                .reduceRight( function(a,b) {
                    return b.concat(a)
                }, [] )

            //var logos = this.collection.pluck('logo')

            tags.forEach( function(tagObj, idx) {
                this.$el
                    .append( new LogoLi({
                        tagObj : tagObj
                        //logo : logos[idx]
                    })
                    .render() )
            }, this)

            return this.el
        }
    })

    var ProjectLi = Backbone.View.extend({
        tagName : 'li',
        template : TPL.namePartial,
        render : function() {
            this.$el
                .html( this.template({
                    tagFilter : this.options.tagObj.className,
                    tag : this.options.tagObj.title
                }) )
            return this.el
        }
    })

    var ProjectUl = Backbone.View.extend({
        tagName : 'ul',
        className : 'names',
        render : function() {
            var filter
            switch (this.options.type) {
                case 'industry':
                    filter = 'industry_tags'
                    break;
                case 'type':
                    filter = 'type_tags'
                    break;
                default:
                    break;
            }

            var tags = this.collection
                .pluck(filter)
                .reduceRight(function(a,b) {
                    return a.concat(b) 
                }, [])

            tags.forEach( function(tagObj) {
                this.$el
                    .append( new ProjectLi({ 
                        tagObj : tagObj })
                    .render() )
            }, this )

            return this.el
        }
    })

    var ViewMenu = Backbone.View.extend({
        tagName : 'div',
        id : 'views',
        className : 'views',
        template: TPL.views,
        initialize : function() {
            this.$el.append( this.template() )
            this.listenTo( this.model, 'change:view', this.toggleActive )
            this.listenTo( this.model, 'change:showcase', this.toggleActive )
        },

        events : {
            'click button' : 'viewChange'
        },

        render : function() {
            return this.el
        },

        viewChange : function(e){

            if ( $(e.currentTarget).hasClass('active') ) {
                e.preventDefault()
                e.stopPropagation()
                return false
            }

            $.bbq.pushState({ view : e.currentTarget.id }, 2)
        },

        toggleActive : function( pageModel, view ) {

            this.$('button').removeClass('active')
            try {
                this.$('#' + view).addClass('active')
            } catch(e) {
                if ( view instanceof S.Image ) {
                    this.$('#covers').addClass('active')
                } else {
                    this.$('#titles').addClass('active')
                }
            }
        }
    })

    var SortMenu = Backbone.View.extend({
        tagName : 'div',
        id : 'sorts',
        className : 'sorts',
        template: TPL.sorts,

        initialize : function() {
            this.$el.append( this.template() )
            this.listenTo( this.model, 'change:sort', this.toggleActive )
            this.listenTo( this.model, 'change:showcase', this.toggleActive )
        },

        events : {
            'click button' : 'sortChange'
        },

        render : function() {
            return this.el
        },

        sortChange : function(e){

            if ( $(e.currentTarget).hasClass('active') ) {
                e.preventDefault()
                e.stopPropagation()
                return false
            }

            $.bbq.pushState({ sort : e.currentTarget.id }, 2)
        },

        toggleActive : function( pageModel, sort ) {
            this.$('button').removeClass('active')
            try {
                this.$('#' + sort).addClass('active')
            } catch(e) {
                this.$('#alpha').addClass('active')
            }
        }
    })

    var JumpMenu = Backbone.View.extend({
        tagName : 'div',
        id : 'jump-to',
        className : 'jump-to alpha',
        template: TPL.jumps,

        initialize : function() {
            var $alpha = $('<ul />').attr('class', 'alphas'),
                $date = $('<ul />').attr('class', 'dates'),
                byDate = this.model.titles.byDate,
                byFirst = this.model.titles.byFirst

            _.each( byDate, function( model, date ) {
                var li = document.createElement('li'),
                    $tag = $('<a />')
                $tag.attr('href', '#jump=' + date).html(date)
                $tag.appendTo(li)
                $date.append(li)
            } )
            _.each( byFirst, function( model, first) {
                var li = document.createElement('li'),
                    $tag = $('<a />')
                $tag.attr('href', '#jump=' + first.toLowerCase() ).html(first)
                $tag.appendTo(li)
                $alpha.append(li)
            } )

            this.$el.append( this.template() )
            this.$('.wrapper').append($alpha).append($date)

            this.listenTo( this.model, 'change:sort', this.toggleActive)

        },

        render : function() {
            return this.el
        },

        toggleActive : function( pageModel, sort ) {
            this.$el.toggleClass( 'alpha', sort === 'alpha' )
            this.$el.toggleClass('date', sort === 'date' )
        }
    })

    // instantiate with projects collection
    var Filter = Backbone.View.extend({
        template : TPL.projectFilter,

        initialize : function() {
            _.bindAll(this, 'toggleView', 'openMenu', 'render' )
            this.$el.html( this.template() )
            this.render()
        },

        events : {
            'click .filter a' : function(e) {
                var href = e.currentTarget.hash.substring(1),
                    option = $.deparam( href, true )

                $.bbq.pushState( option, 2 )
            },

            'click h3' : 'openMenu'
        },

        toggleView : function(e) {
            e.preventDefault()
            e.stopPropagation()
            Backbone.dispatcher.trigger( 'filter:toggleView', e.currentTarget )
            if ( !$(e.currentTarget.parentElement).hasClass('views') ) {
                this.$('.open').removeClass('open')
            }
        },

        openMenu : function(e) {
            e.preventDefault()
            e.stopPropagation()
            this.$('.open').removeClass('open')
            $(e.target.parentElement).addClass('open')
        },

        render : function() {
            this.$el.addClass('filter-bar')
            this.collection = Projects

            this.$('#brand .wrapper')
                .append( new LogoBtns({
                    model : this.model
                }).render() )
                .append( new LogoUl({
                    model : this.model,
                    collection : this.collection
                }).render() )
            this.$('#industry .wrapper')
                .append( new ProjectUl({
                    type : 'industry',
                    model : this.model,
                    collection : this.collection
                }).render() )
            this.$('#type .wrapper')
                .append( new ProjectUl({
                    type : 'type',
                    model : this.model,
                    collection : this.collection
                }).render() )

            this.$el
                .append( new JumpMenu({
                    model : this.model,
                    collection : this.collection
                }).render() )
                .append( new SortMenu({
                    model : this.model
                }).render() )
                .append( new ViewMenu({
                    model : this.model
                }).render() )

            //return this.el
        }
    })

    return Filter
})

