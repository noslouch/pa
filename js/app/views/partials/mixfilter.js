/* app/views/partials/mixfilter.js - Filter for Fixed Photo grids running mixitup.js */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'bbq',
    'tpl/jst'
], function( $, Backbone, _, bbq, TPL ) {

    var mobile = 'ontouchstart' in window

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

    var ProjectLi = Backbone.View.extend({
        tagName : mobile ? 'option' : 'li',
        template : TPL.namePartial,
        render : function() {
            if (mobile) {
                this.$el.attr({
                    'data-hash' : 'filter=.' + this.options.tagObj.className,
                    'id' : this.options.tagObj.className
                })
                this.$el.html( this.options.tagObj.title )
            } else {
                this.$el
                    .html( this.template({
                        tagFilter : this.options.tagObj.className,
                        tag : this.options.tagObj.title
                    }) )
            }
            return this.el
        }
    })

    var ProjectUl = FilterMenu.extend({
        tagName : mobile ? 'select' : 'ul',
        className : 'names',
        render : function() {
            var filter,
                title
            switch (this.options.type) {
                case 'industry':
                    filter = 'industry_tags'
                    title = 'Industry'
                    break;
                case 'type':
                    filter = 'type_tags'
                    title = 'Project Type'
                    break;
                default:
                    break;
            }

            var tags = this.reduce(filter)

            if (mobile) { 
                $('#' + this.options.type).empty() 
                this.$el.prepend('<option>' + title + '</option>')
            }
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
            if (!mobile && this.id !== 'views') {
            }
            return this.el
        },
        toggleActive : function(model, set){
            this.$('button').removeClass('active')
            this.$('#' + set).addClass('active')
        }
    })

    var Filter = Backbone.View.extend({
        template : TPL.projectFilter,
        initialize : function() {
            _.bindAll(this, 'filter', 'openMenu', 'render' )
        },

        events : {
            'click .filter a' : 'filter',
            'click .sorts button' : 'filter',
            'click h3' : 'openMenu',
            'change .filter select' : 'filter',
            'change .sorts select' : 'filter'
        },

        render : function(){
            this.$el.html( this.template() )
            this.$el.addClass( 'filter-bar' )

            this.$('#type .wrapper')
                .append( new ProjectUl({
                    type : 'type',
                    model : this.model,
                    collection : this.collection
                }).render() )

            this.$el
                .append( new ViewSort({
                    model : this.model,
                    collection : this.collection,
                    id : 'sorts',
                    type : 'sort',
                    template : TPL.sorts
                }).render() )
            this.delegateEvents()
        },

        onClose : function() {
            this.$el.removeClass('filter-bar')
        },

        filter : function(e) {
            var hash, option

            try {
                hash = e.type === 'change' ? e.currentTarget.selectedOptions[0].dataset.hash : e.currentTarget.dataset.hash
                option = $.deparam( hash, true )
            } catch(err) { return false }

            e.preventDefault()
            e.stopPropagation()
            $.bbq.pushState( option, option.view === 'random' ? 2 : 0 )
            if ( !$(e.target).parents('#sorts').length ) {
                this.$('.open').removeClass('open')
            }
        },

        openMenu : function(e) {
            e.preventDefault()
            e.stopPropagation()
            this.$('.open').removeClass('open')
            $(e.target.parentElement).addClass('open')
        }
    })

    return Filter

})
