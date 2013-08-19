'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.BrandControls = Backbone.View.extend({
    tagName : 'div',
    className : 'controls',
    template : PA.jst.controlsPartial,
    events : {
        'click #logoView' : 'toggleView',
        'click #titleView' : 'toggleView',
        'click #close' : 'close'
    },
    render : function() {
        this.$el.html( this.template() )
        return this.el
    }
})


PA.ProjectFilterItem = Backbone.View.extend({
    tagName : 'li',
    template : PA.jst.namePartial,
    events : {
        'click' : 'debug'
    },
    render : function() {
        this.$el
            .html( this.template({ 
                tag : this.options.tag
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

        tags.forEach( function(tag) {
            this.$el .append( new PA.ProjectFilterItem({ tag : tag }).render() )
        }, this )

        return this.el
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
            tag : this.options.tag,
            logo: this.options.logo 
        }) )
        this.$el.append( this.nameTemplate({ 
            tag : this.options.tag 
        }) )
        return this.el
    }
})

PA.BrandFilter = Backbone.View.extend({
    tagName : 'ul',
    id : 'brandList',
    className : 'icons',
    render : function() {
        var tags = this.collection
            .pluck('brand_tags')
            .reduceRight( function(a,b) {
                return a.concat(b)
            }, [] )

        tags.forEach( function(tag) {
            this.$el
                .append( new PA.BrandFilterItem({
                    tag : tag,
                    logo : 'http://placehold.it/80x45'
                })
                .render() )
        }, this)

        return this.el
    }
})


// instantiate with projects collection
PA.FilterBar = Backbone.View.extend({
    tagName: 'div',
    className : 'filter-bar',
    id : 'filter-bar',
    template : PA.jst.projectFilter,
    initialize : function() {
        _.bindAll(this, 'render', 'openMenu','debug')
        this.$el.html( this.template() )
    },
    events : {
        'click .filter' : function(e){
            e.preventDefault()
            e.stopPropagation() 
        },
        'click .filter a' : 'filter',
        'click h3' : 'openMenu'
        //'click h3' : 'debug'
    },
    openMenu : function(e) {
        this.$('.open').removeClass('open')
        $(e.target.parentElement).addClass('open')
    },
    filter : function(e) {
        PA.dispatcher.trigger('filter', e)
    },
    debug : function(e) { 
        //console.log($(e.currentTarget).data('filter'))
        //this.dispatcher.trigger('filter2', e)
    },
    render : function() {
        this.collection = PA.projects

        this.$('#brand .wrapper')
            .append( new PA.BrandControls().render() )
            .append( new PA.BrandFilter({
                collection : this.collection
            }).render() )
        this.$('#industry .wrapper')
            .append( new PA.ProjectFilter({
                type : 'industry',
                collection : this.collection
            }).render() )
        this.$('#type .wrapper')
            .append( new PA.ProjectFilter({
                type : 'type',
                collection : this.collection
            }).render() )

        this.$el
            .append( PA.jst.jumps() )
            .append( PA.jst.sorts() )
            .append( PA.jst.views() )

        PA.app.header.$el.append( this.el )
    }
})

