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
        render : function(renderOptions) {
            if (!mobile && renderOptions.jumpTo) {
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
                this.$('.wrapper')
                    .append('<h4>Jump To</h4>')
                    .append( this.dateList.render() )
                    .append( this.nameList.render() )
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
            _.bindAll(this, 'toggleActive', 'toggleVisible')
            this.listenTo( this.model, 'change:sort', this.toggleActive )

            this.listenTo( this.model, 'list:ready', this.toggleVisible )
            this.listenTo( this.model, 'isotope:ready', this.toggleVisible )
        },

        render : function() {
            var sorted,
                sortedKeys
            switch( this.className ) {
                case 'date':
                    sorted = this.collection.groupBy(function(model) {
                        return model.get('date').year()
                    })

                    sortedKeys = Object.keys(sorted).sort()

                    _.each( sortedKeys, function( key ) {
                        var model = sorted[key],
                            date = key
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
        },

        toggleVisible : function() {
            var currentView = this.model.get('view'),
                currentSort = this.model.get('sort'),
                currentItems,
                groups

            if ( currentView === 'cover' ) {
                currentItems = this.model.cover.$el.data('isotope').filteredItems

                if ( this.className === 'name' ) {
                    groups = _.groupBy(currentItems, function(thumb) {
                        return $(thumb.element).find('.title').text()[0]
                    })

                    // clear all "jump to" ids
                    $('.thumb').each(function(i, thumb) {
                        $(this).find('.title')[0].id = ''
                    })

                    // look at each group and ID first element with letter
                    _.each(groups, function(group, letter) {
                        $(group[0].element).find('.title')[0].id = letter
                    })
                } else {
                    groups = _.groupBy(currentItems, function(thumb) {
                        return $(thumb.element).find('.year').text()
                    })

                    // clear all "jump to" ids
                    $('.thumb').each(function(i, thumb) {
                        $(this).find('.year')[0].id = ''
                    })

                    // look at each group and ID first element with year
                    _.each(groups, function(group, year) {
                        $(group[0].element).find('.year')[0].id = year
                    })
                }

                groups = Object.keys(groups)

            } else {
                groups = []
                $('.list').children().each(function() {
                    groups.push(this.id)
                })
            }

            this.$el.children().each(function(i, el) {
                var t = el.innerText ? el.innerText : el.textContent
                if ( groups.indexOf(t) === -1 ) {
                    el.style.display = 'none'
                } else {
                    el.style.display = ''
                }
            })
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
            'change .jumps select' : 'jump'
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
            if ( !options.mixitup ) {
                //projects detail & landing
                if ( options.brands ) {
                    this.$('#brand')
                        .append( new LogoUl({
                            model : this.model,
                            collection : this.collection
                        }).render() )
                }
                if ( options.industry ) { 
                    this.$('#industry')
                        .html( new ProjectUl({
                            type : 'industry',
                            model : this.model,
                            collection : this.collection
                        }).render() )
                }
                if ( options.types ) {
                    this.$('#type')
                        .html( new ProjectUl({
                            type : 'type',
                            model : this.model,
                            collection : this.collection
                        }).render() )
                }

                if ( !this.options.parentSection ) {
                    // projects landing only
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
                }
            } else if ( options.mixitup ) {
                // books, film, photography
                this.sortList = new ViewSort({
                    model : this.model,
                    collection : this.collection,
                    id : 'sorts',
                    type : 'sort',
                    template : TPL.sorts
                })

                this.$el.append( this.sortList.render() )
                if ( options.hasTags ) {
                    // if there are tags on this section landing
                    this.$('#type')
                        .html( new ProjectUl({
                            type : 'type',
                            model : this.model,
                            collection : this.collection
                        }).render() )
                }
            } else {
                console.log('remove type and all?')
                // this.$('#type').remove()
                // this.$('#all').remove()
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
                hash = e.type === 'change' ? e.currentTarget.selectedOptions[0].dataset.hash : e.currentTarget.dataset.hash
                option = $.deparam( hash, true )
            } catch(err) { return false }

            e.preventDefault()

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

