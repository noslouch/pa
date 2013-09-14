/* views/filterviews.js - Filter Bar View */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.ProjectFilterItem = Backbone.View.extend({
    tagName : 'li',
    template : PA.jst.namePartial,
    render : function() {
        this.$el
            .html( this.template({
                tagFilter : this.options.tagObj.className,
                tag : this.options.tagObj.title
            }) )
        return this.el
    }
})

PA.ProjectFilter = Backbone.View.extend({
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
                .append( new PA.ProjectFilterItem({ 
                    tagObj : tagObj })
                .render() )
        }, this )

        return this.el
    }
})

PA.BrandControls = Backbone.View.extend({
    tagName : 'div',
    className : 'controls',
    template : PA.jst.controlsPartial,
    initialize : function() {
        _.bindAll( this, 'toggleActive' )

        this.$el.html( this.template() )
        this.listenTo( PA.dispatcher, 'brandToggler', this.toggleActive )
    },

    render : function() {
        return this.el
    },

    events : {
        'click button' : function(e) {
            e.preventDefault()
            e.stopPropagation()
            PA.dispatcher.trigger('brandToggler')
        }
    },

    toggleActive : function(pageModel, id) {
        this.$('button').toggleClass('active')
    }

})

// Each brand filter item will need a logo, a tag className (for isotope)
// and a tag displayName to be shown as a list.
PA.BrandFilterItem = Backbone.View.extend({
    tagName : 'li',
    logoTemplate : PA.jst.logoPartial,
    nameTemplate : PA.jst.namePartial,
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

PA.BrandFilter = Backbone.View.extend({
    tagName : 'ul',
    id : 'brandList',
    className : 'icons',
    initialize : function() {
        this.listenTo( PA.dispatcher, 'brandToggler', function(mdl, id) {
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
                .append( new PA.BrandFilterItem({
                    tagObj : tagObj
                    //logo : logos[idx]
                })
                .render() )
        }, this)

        return this.el
    }
})

PA.ProjectViews = Backbone.View.extend({
    tagName : 'div',
    id : 'views',
    className : 'views',
    template: PA.jst.views,
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
            if ( view instanceof PA.ImageShowcase ) {
                this.$('#covers').addClass('active')
            } else {
                this.$('#titles').addClass('active')
            }
        }
    }
})

PA.ProjectSorts = Backbone.View.extend({
    tagName : 'div',
    id : 'sorts',
    className : 'sorts',
    template: PA.jst.sorts,

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

PA.ProjectJumps = Backbone.View.extend({
    tagName : 'div',
    id : 'jump-to',
    className : 'jump-to alpha',
    template: PA.jst.jumps,

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
PA.FilterBar = Backbone.View.extend({
    tagName: 'div',
    className : 'filter-bar',
    id : 'filter-bar',
    template : PA.jst.projectFilter,

    initialize : function() {
        _.bindAll(this, 'toggleView', 'openMenu', 'render' )

        this.$el.html( this.template() )
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
        PA.dispatcher.trigger( 'filter:toggleView', e.currentTarget )
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
        this.collection = PA.projects

        this.$('#brand .wrapper')
            .append( new PA.BrandControls({
                model : this.model
            }).render() )
            .append( new PA.BrandFilter({
                model : this.model,
                collection : this.collection
            }).render() )
        this.$('#industry .wrapper')
            .append( new PA.ProjectFilter({
                type : 'industry',
                model : this.model,
                collection : this.collection
            }).render() )
        this.$('#type .wrapper')
            .append( new PA.ProjectFilter({
                type : 'type',
                model : this.model,
                collection : this.collection
            }).render() )

        this.$el
            .append( new PA.ProjectJumps({
                model : this.model,
                collection : this.collection
            }).render() )
            .append( new PA.ProjectSorts({
                model : this.model
            }).render() )
            .append( new PA.ProjectViews({
                model : this.model
            }).render() )

        PA.app.header.$el.append( this.el )

    }
})
