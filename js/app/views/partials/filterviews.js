/* app/views/partials/filterviews.js - Filter Bar View */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'bbq',
    'tpl/jst',
    'app/collections/projects'
], function( $, Backbone, _, bbq, TPL, Projects ) {

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
        tagName : mobile ? 'option' : 'li',
        logoTemplate : TPL.logoPartial,
        nameTemplate : TPL.namePartial,
        render : function() {
            if (mobile) {
                this.$el.attr({
                    'data-hash' : 'filter=.' + this.options.tagObj.className,
                    'id' : this.options.tagObj.className
                })
                this.$el.html( this.options.tagObj.title)
            } else {
                this.$el.append( this.logoTemplate({
                    tagFilter : this.options.tagObj.className,
                    tag : this.options.tagObj.title,
                    logo: this.options.tagObj.logo
                }) )
                this.$el.append( this.nameTemplate({
                    tagFilter : this.options.tagObj.className,
                    tag : this.options.tagObj.title
                }) )
            }
            return this.el
        }
    })

    var LogoUl = FilterMenu.extend({
        tagName : mobile ? 'select' : 'ul',
        id : 'brandList',
        className : mobile ? '' : 'icons',
        initialize : function() {
            if (!mobile) {
                this.listenTo( Backbone.dispatcher, 'brandToggler', function(mdl, id) {
                    this.$el.toggleClass('icons')
                    this.$el.toggleClass('names')
                })
            } else {
                this.$el.prepend('<option>Brands</option>')
            }
        },
        render : function() {
            var tags = this.reduce('brand_tags')

            if (mobile) { $('#brand').empty() }
            tags.forEach( function(tagObj, idx) {
                this.$el
                    .append( new LogoLi({
                        tagObj : tagObj
                    })
                    .render() )
            }, this)

            return this.el
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
                this.$('.wrapper')
                    .append('<h4>Jump To</h4>')
                    .append( new JumpMenu({
                        model : this.model,
                        collection : this.collection,
                        className : 'date',
                        id : 'date'
                    }).render() )
                    .append( new JumpMenu({
                        model : this.model,
                        collection : this.collection,
                        className : 'name',
                        id : 'name'
                    }).render() )
            }
            return this.el
        },
        toggleActive : function(model, set){
            this.$('button').removeClass('active')
            this.$('#' + set).addClass('active')
        }
    })

    var JumpMenu = Backbone.View.extend({
        tagName : mobile ? 'select' : 'ul',

        initialize : function(options) {
            _.bindAll(this, 'toggleActive')
            this.listenTo( this.model, 'change:sort', this.toggleActive)
        },

        render : function() {
            var sorted
            switch( this.className ) {
                case 'date':
                    sorted = this.collection.groupBy(function(model) {
                        return model.get('date').year()
                    })

                    _.each( sorted, function( model, date ) {
                        if ( mobile ) {
                            var $op = $('<option/>').attr('value', date).html(date)
                            this.$el.append($op)
                        } else {
                            var li = document.createElement('li'),
                                $tag = $('<a />').attr('href', '#' + date).html(date)
                            $tag.appendTo(li)
                            this.$el.append(li)
                        }
                    }, this )
                    break;
                case 'name':
                    sorted = this.collection.groupBy(function(model) {
                        return model.get('title')[0]
                    })

                    _.each( sorted, function( model, first) {
                        if ( mobile ) {
                            var $op = $('<option/>').attr('value', first).html(first)
                            this.$el.append($op)
                        } else {
                            var li = document.createElement('li'),
                                $tag = $('<a />').attr('href', '#' + first).html(first)
                            $tag.appendTo(li)
                            this.$el.append(li)
                        }
                    }, this )
                    break;
            }

            return this.el
        },

        toggleActive : function( pageModel, sort ) {
            this.$el.toggleClass( 'active', sort === this.className )
        }
    })

    // instantiate with projects collection
    var Filter = Backbone.View.extend({
        template : TPL.projectFilter,
        initialize : function() {
            _.bindAll(this, 'filter', 'openMenu', 'render' )
        },

        events : {
            'click #brand a' : 'hideTip',
            'click .filter a' : 'filter',
            'click .sorts button' : 'filter',
            'click .views button' : 'filter',
            'click .sorts a' : 'jump',
            'click h3' : 'openMenu',
            'change .sorts select' : 'filter',
            'change .views select' : 'filter',
            'change .filter select' : 'filter',
            'change .jumps select' : 'jump'
        },

        debug : function() {
            console.log('selected')
            console.log(arguments)
            console.log(this)
        },

        render : function() {
            this.$el.html( this.template() )
            this.$el.addClass('filter-bar')

            if ( !this.options.profile ) {

                if (mobile) {
                    this.$('#brand')
                        .append( new LogoUl({
                            model : this.model,
                            collection : this.collection
                        }).render() )
                    this.$('#industry')
                        .html( new ProjectUl({
                            type : 'industry',
                            model : this.model,
                            collection : this.collection
                        }).render() )
                    this.$('#type')
                        .html( new ProjectUl({
                            type : 'type',
                            model : this.model,
                            collection : this.collection
                        }).render() )

                    this.$el
                        .append('<div class="jumps"></div>')
                        .append( new ViewSort({
                            model : this.model,
                            id : 'sorts',
                            type : 'sort',
                            template : TPL.mobileSorts
                        }).render() )
                        .append( new ViewSort({
                            model : this.model,
                            id : 'views',
                            type : 'view',
                            template : TPL.mobileViews
                        }).render() )

                    this.$('.jumps')
                        .append( new JumpMenu({
                            model : this.model,
                            collection : this.collection,
                            className : 'date'
                        }).render() )
                        .append( new JumpMenu({
                            model : this.model,
                            collection : this.collection,
                            className : 'name'
                        }).render() )
                } else {
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
                        .append( new ViewSort({
                            model : this.model,
                            collection : this.collection,
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
            }


            if (mobile) {
                var hash = $.bbq.getState()
                for (var prop in hash) {
                    if (hash.hasOwnProperty(prop)) {
                        var id = hash[prop]
                        if (prop === 'filter') { id = id.slice(1) }
                        $('#' + id).prop('selected', true)
                    }
                }
            }
            this.delegateEvents()
        },

        onClose : function() {
            this.$el.removeClass('filter-bar')
            $('.tooltip').remove()
        },

        hideTip : function() {
            $('span.tooltip:visible').hide()
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

        jump : function(e) {
            var jump

            try {
                jump = e.type === 'change' ? '#' + e.currentTarget.selectedOptions[0].value : e.currentTarget.hash
            } catch(err) { return false }

            e.preventDefault()
            e.stopPropagation()
            //this.$('.open').removeClass('open')
            $('html, body').animate({
                scrollTop : $(jump).offset().top - (this.model.get('view') === 'list' ? 200 : 400)
            })
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

