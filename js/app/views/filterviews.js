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

    var FilterMenu = Backbone.View.extend({
        reduce : function(filter) {
            var flat = this.collection
                .pluck(filter)
                .reduceRight( function(a,b) {
                    return b.concat(a)
                }, [] )

            var tags = _.chain( flat )
                .groupBy('title')
                .sortBy('title')
                .map(function(tag){ return tag[0] })
                .value()

            return tags
        }
    })

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

    var LogoUl = FilterMenu.extend({
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
            //var logos = this.collection.pluck('logo')
            var tags = this.reduce('brand_tags')
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

    var ProjectUl = FilterMenu.extend({
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

            var tags = this.reduce(filter)

            tags.forEach( function(tagObj) {
                this.$el
                    .append( new ProjectLi({
                        tagObj : tagObj })
                    .render() )
            }, this )

            return this.el
        }
    })

    var ViewSort = Backbone.View.extend({
        tagName : 'div',
        initialize : function(options) {
            this.id = options.id
            this.$el.addClass(this.id)
            this.template = options.template
            this.$el.append( this.template() )
            this.listenTo( this.model, 'change:' + options.type, this.toggleActive )
        },
        render : function() {
            return this.el
        },
        toggleActive : function(model, set){
            this.$('button').removeClass('active')
            this.$('#' + set).addClass('active')
        }
    })

    var JumpMenu = Backbone.View.extend({
        tagName : 'div',
        id : 'jump-to',
        className : 'jump-to name',
        template: TPL.jumps,

        initialize : function() {
            var $name = $('<ul />').attr('class', 'names'),
                $date = $('<ul />').attr('class', 'dates'),
                byDate = this.collection.groupBy(function(model) {
                    return model.get('date').year()
                }),
                byFirst = this.collection.groupBy(function(model) {
                    return model.get('title')[0]
                })

            _.each( byDate, function( model, date ) {
                var li = document.createElement('li'),
                    $tag = $('<a />')
                $tag.attr('href', '#' + date).html(date)
                $tag.appendTo(li)
                $date.append(li)
            } )
            _.each( byFirst, function( model, first) {
                var li = document.createElement('li'),
                    $tag = $('<a />')
                $tag.attr('href', '#' + first).html(first)
                $tag.appendTo(li)
                $name.append(li)
            } )

            this.$el.append( this.template() )
            this.$('.wrapper').append($name).append($date)

            this.listenTo( this.model, 'change:sort', this.toggleActive)
        },

        render : function() {
            return this.el
        },

        toggleActive : function( pageModel, sort ) {
            this.$el.toggleClass( 'name', sort === 'name' )
            this.$el.toggleClass( 'date', sort === 'date' )
        }
    })

    // instantiate with projects collection
    var Filter = Backbone.View.extend({
        template : TPL.projectFilter,
        initialize : function() {
            _.bindAll(this, 'filter', 'openMenu', 'render' )
        },

        events : {
            'click .filter a' : 'filter',
            'click .sorts button' : 'filter',
            'click .views button' : 'filter',
            'click .jump-to a' : 'jump',
            'click h3' : 'openMenu'
        },

        filter : function(e) {
            var hash = e.currentTarget.dataset.hash,
                option = $.deparam( hash, true )

            e.preventDefault()
            e.stopPropagation()
            $.bbq.pushState( option, option.view === 'random' ? 2 : 0 )
            this.$('.open').removeClass('open')
        },

        jump : function(e) {
            e.preventDefault()
            e.stopPropagation()
            this.$('.open').removeClass('open')
            $('html, body').animate({
                scrollTop : $(e.currentTarget.hash).offset().top - (this.model.get('view') === 'list' ? 200 : 400)
            })
        },

        openMenu : function(e) {
            e.preventDefault()
            e.stopPropagation()
            this.$('.open').removeClass('open')
            $(e.target.parentElement).addClass('open')
        },

        render : function() {
            this.$el.html( this.template() )
            this.$el.addClass('filter-bar')

            if ( !this.options.profile ) {

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
            }

            this.$el
                .append( new JumpMenu({
                    model : this.model,
                    collection : this.collection
                }).render() )
                .append( new ViewSort({
                    model : this.model,
                    id : 'sorts',
                    type : 'sort',
                    template : TPL.sorts
                }).render() )
                .append( new ViewSort({
                    model : this.model,
                    id : 'views',
                    type : 'view',
                    template : TPL.views
                }).render() )
        }
    })

    return Filter
})

