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
        render : function(ops) {
            var filter,
                title

            switch (this.options.type) {
                case 'industry':
                    filter = 'industry_tags'
                    title = 'Industry'
                    break;
                case 'type':
                    filter = 'type_tags'
                    title = document.location.pathname.match(/^\/film/) ? 'Film Type' : 'Project Type'
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
        render : function(renderOptions) {
            if ( renderOptions.jumpTo ) {
                this.dateList = new JumpMenu({
                    model : this.model,
                    collection : this.collection,
                    className : 'date',
                    id : 'date'
                })
                this.nameList = new JumpMenu({
                    model : this.model,
                    collection : this.collection,
                    className : 'name',
                    id : 'name'
                })
                this.onClose = function() {
                    this.nameList.close()
                    this.dateList.close()
                }

                if ( mobile ) {
                    this.$('#jumps')
                        .append( this.dateList.render() )
                        .append( this.nameList.render() )
                } else {
                    this.$('.wrapper')
                        .append('<h4>Jump To</h4>')
                        .append( this.dateList.render() )
                        .append( this.nameList.render() )
                }
            }

            if ( this.id === 'views' ) {
                this.toggleActive( this.model, this.model.get('view') )
            } else {
                this.toggleActive( this.model, this.model.get('sort') )
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
            _.bindAll( this, 'toggleActive'  )
            this.listenTo( this.model, 'change:sort', this.render )
            this.listenTo( this.model, 'list:ready', this.render )
            this.listenTo( this.model, 'isotope:ready', this.render )
        },

        render : function() {
            var currentView = this.model.get('view'),
                groups,
                currentItems

            this.$el.empty()
            this.toggleActive( this.model.get('sort') )

            if ( currentView === 'cover' ) {
                try {
                    currentItems = this.model.cover.$el.data('isotope').filteredItems
                } catch (e) {
                    // should only run on initial page render
                    currentItems = this.model.cover.$el.children()
                }

                if ( this.className === 'name' ) { 
                    groups = _.groupBy(currentItems, function(thumb) {
                        if ( thumb.element ) {
                            return $(thumb.element).find('.title').text()[0]
                        } else {
                            return $(thumb).find('.title').text()[0]
                        }
                    })

                    // clear all "jump to" ids
                    $('.thumb').each(function(i, thumb) {
                        $(this).find('.title').attr('id', '')
                    })

                    // look at each group and ID first element with letter
                    _.each(groups, function(group, letter) {
                        if ( group[0].element ) {
                            $(group[0].element).find('.title').attr('id', letter)
                        } else {
                            $(group[0]).find('.title').attr('id', letter)
                        }
                    })
                } else {
                    groups = _.groupBy(currentItems, function(thumb) {
                        if ( thumb.element ) {
                            return $(thumb.element).find('.year').text()
                        } else {
                            return $(thumb).find('.year').text()
                        }
                    })

                    // clear all "jump to" ids
                    $('.thumb').each(function(i, thumb) {
                        $(this).find('.year').attr('id' , '')
                    })

                    // look at each group and ID first element with year
                    _.each(groups, function(group, year) {
                        if ( group[0].element ) {
                            $(group[0].element).find('.year').attr('id', year)
                        } else {
                            $(group[0]).find('.year').attr('id', year)
                        }
                    })

                }

                groups = Object.keys(groups).sort()

            } else {
                groups = []
                $('.list').children().each(function() {
                    groups.push(this.id)
                })

            }

            _.each( groups, function(group) {
                if ( mobile ) {
                    var $op = $('<option />').attr('value', group).html(group)
                    this.$el.append($op)
                } else {
                    var li = document.createElement('li'),
                        $tag = $('<a />').attr('href', '#' + group).html(group)
                    $tag.appendTo(li)
                    this.$el.append(li)
                }
            }, this )

            return this.el
        },

        toggleActive : function( sort ) {
            this.$el.toggleClass( 'active', sort === this.className )
        }

    })

    // instantiate with a given collection
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
            'change #jumps select' : 'jump'
        },

        debug : function() {
            console.log('selected')
            console.log(arguments)
            console.log(this)
        },

        render : function(renderOptions) {
            var renderOptions = renderOptions || {}
            renderOptions.brands = !!_.flatten( this.collection.pluck('brand_tags') ).length
            renderOptions.types = !!_.flatten( this.collection.pluck('type_tags') ).length
            renderOptions.industry = !!_.flatten( this.collection.pluck('industry_tags') ).length

            this.$el.html( this.template() )
            this.$el.addClass('filter-bar')
            this.previous = renderOptions.previous

            if ( !this.options.profile ) {

                if (mobile) {
                    this.touchDOM(renderOptions)
                } else {
                    this.mouseDOM(renderOptions)
                }

                if (!renderOptions.brands && !renderOptions.types && !renderOptions.industry) {
                    this.$('#all').remove()
                    if ( document.body.classList.contains('detail-view') ) {
                        document.body.classList.add('detail-view--nofilter')
                    }
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

            $(document).foundation({
                tooltip : {
                    hover_delay: 50,
                    disable_for_touch: true
                }
            })
        },

        mouseDOM : function(options) {
            // detail & landing
            if ( options.brands ) {
                this.$('#brand .wrapper')
                    .append( new LogoBtns({
                        model : this.model
                    }).render() )
                    .append( new LogoUl({
                        model : this.model,
                        collection : this.collection
                    }).render() )
            } else {
                this.$('#brand').remove()
            }

            if ( options.industry ) {
                this.$('#industry .wrapper')
                    .append( new ProjectUl({
                        type : 'industry',
                        model : this.model,
                        collection : this.collection
                    }).render() )
            } else {
                this.$('#industry').remove()
            }

            if ( options.types ) {
                if ( location.pathname.match(/^\/film/) ) {
                    this.$('#type h3').text('Film Type')
                }
                this.$('#type .wrapper')
                    .append( new ProjectUl({
                        type : 'type',
                        model : this.model,
                        collection : this.collection
                    }).render() )
            } else {
                this.$('#type').remove()
            }

            if ( !this.options.parentSection ) {
                // landing only
                this.sortList = new ViewSort({
                    model : this.model,
                    collection : this.collection,
                    id : 'sorts',
                    type : 'sort',
                    template : TPL.sorts
                })
                this.$el
                    .append( this.sortList.render(options) )

                if ( !options.mixitup ) {
                    this.viewList = new ViewSort({
                        model : this.model,
                        id : 'views',
                        type : 'view',
                        template : TPL.views
                    })
                    this.$el
                        .append( this.viewList.render({ jumpTo : false }) )
                }
            }
        },

        touchDOM : function(options) {
            // detail & landing
            if ( options.brands ) {
                this.$('#brand')
                    .append( new LogoUl({
                        model : this.model,
                        collection : this.collection
                    }).render() )
            } else {
                this.$('#brand').remove()
            }

            if ( options.industry ) {
                this.$('#industry')
                    .html( new ProjectUl({
                        type : 'industry',
                        model : this.model,
                        collection : this.collection
                    }).render() )
            } else {
                this.$('#industry').remove()
            }

            if ( options.types ) {
                this.$('#type')
                    .html( new ProjectUl({
                        type : 'type',
                        model : this.model,
                        collection : this.collection
                    }).render() )
            } else {
                this.$('#type').remove()
            }

            if ( !this.options.parentSection ) {
                // landing only

                if ( !options.mixitup ) {
                    this.viewList = new ViewSort({
                        model : this.model,
                        id : 'views',
                        type : 'view',
                        template : TPL.mobileViews
                    })

                    this.$el
                        .append( this.viewList.render({ jumpTo : false }) )
                }

                this.sortList = new ViewSort({
                    model : this.model,
                    collection : this.collection,
                    id : 'sorts',
                    type : 'sort',
                    template : TPL.mobileSorts
                })

                this.$el
                    .append( this.sortList.render(options) )
            }
        },

        onClose : function() {
            if ( this.sortList ) {
                this.sortList.close()
            }
            if ( this.viewList ) {
                this.viewList.close()
            }
            this.undelegateEvents()
            this.$el.removeClass()
            $('.tooltip').remove()
        },

        hideTip : function() {
            $('span.tooltip:visible').hide()
        },

        filter : function(e) {
            var hash, option,
                isDetail = !!this.options.parentSection,
                topLevel = isDetail ? '/' + this.options.parentSection : false

            $('html, body').animate({ scrollTop: 0  }, 'fast')

            try {
                hash = e.type === 'change' ? $(e.currentTarget.selectedOptions[0]).data('hash') : $(e.currentTarget).data('hash')
                option = $.deparam( hash, true )
            } catch(err) { return false }

            e.preventDefault()

            if ( mobile ) {
                this.$el.find('.filter select').not(e.target).prop('selectedIndex', 0)
            }
            if ( isDetail ) {
                if ( !_.isEmpty(this.options.previous) ) {
                    hash = $.param( _.extend(this.options.previous, option) )
                }
                Backbone.dispatcher.trigger('navigate:section', topLevel + '#' + hash)
            } else {
                history.pushState({}, '', $.param.fragment(document.location.hash, option))
                this.model.set( option )
                if ( !$(e.target).parents('#sorts').length ) {
                    this.$('.open').removeClass('open')
                }
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
            var flag = $(e.target.parentElement).hasClass('open')
            this.$('.open').removeClass('open')
            $(e.target.parentElement).toggleClass('open', !flag)
        }
    })

    return Filter
})

