'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.dispatcher.on('projectSort', function(sort) {
    console.log('sort changed to: ', sort)
})

PA.dispatcher.on('projectJump', function(jump) {
    console.log('jumping to: ', jump)
})

PA.BrandControls = Backbone.View.extend({
    tagName : 'div',
    className : 'controls',
    template : PA.jst.controlsPartial,
    initialize : function() {
        this.$el.html( this.template() )
    },

    render : function() {
        return this.el
    }

})


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
        this.listenTo( PA.dispatcher, 'filter:toggleView', function() {
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
    },
    events : {
        'click button' : 'viewChange'
    },
    viewChange : function(e){
        PA.dispatcher.trigger( 'projectView', e.currentTarget.id )
    },
    render : function() {
        return this.el
    }
})

PA.ProjectSorts = Backbone.View.extend({
    tagName : 'div',
    id : 'sorts',
    className : 'sorts',
    template: PA.jst.sorts,
    initialize : function() {
        this.$el.append( this.template() )
    },
    events : {
        'click button' : 'sortChange'
    },
    sortChange : function(e){
        PA.dispatcher.trigger( 'projectSort', e.currentTarget.id )
    },
    render : function() {
        return this.el
    }
})

PA.ProjectJumps = Backbone.View.extend({
    tagName : 'div',
    id : 'jump-to',
    className : 'jump-to alpha',
    template: PA.jst.jumps,
    initialize : function() {
        this.$el.append( this.template() )
    },
    events : {
        'click button' : 'jump'
    },
    jump : function(e){
        PA.dispatcher.trigger( 'projectJump', e.currentTarget.id )
    },
    render : function() {
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
        _.bindAll(this, 'toggleView', 'render', 'openMenu','debug', 'filter')

        $(window).on('hashchange', this.filter)


        PA.dispatcher.on('projectView', this.renderView )
        PA.dispatcher.on('projectSort', this.renderSort )

        this.$el.html( this.template() )

        this.$el.on('click', 'button', function(e){ 
            var $buttons = $(this).parents('.wrapper').find('button')
            $buttons.removeClass('active')
            $(this).addClass('active')
        })
    },

    events : {
        'click .filter' : function(e){
            e.preventDefault()
            e.stopPropagation()
        },
        'click .filter a' : function(e) {
            var href = e.currentTarget.hash.substring(1),
                option = $.deparam( href, true )

            $.bbq.pushState( option )
        },
        'click h3' : 'openMenu',
        'click button' : 'toggleView'
        //'click h3' : 'debug'
    },

    openMenu : function(e) {
        e.preventDefault()
        e.stopPropagation()
        this.$('.open').removeClass('open')
        $(e.target.parentElement).addClass('open')
    },

    filter : function(e) {
        if (e.fragment) {
            PA.dispatcher.trigger('filter', e)
        }
    },

    toggleView : function(e) {
        e.preventDefault()
        e.stopPropagation()
        PA.dispatcher.trigger( 'filter:toggleView', e.currentTarget )
        if ( !$(e.currentTarget.parentElement).hasClass('views') ) {
            this.$('.open').removeClass('open')
        }
    },

    debug : function(e) { 
        //console.log($(e.currentTarget).data('filter'))
        //this.dispatcher.trigger('filter2', e)
    },

    renderView : function(view) {
        $.bbq.removeState()

        var toRender

        switch(view){
            case 'covers':
                toRender = PA.coverShowcase
                break;
            case 'titles':
                toRender = PA.listShowcase
                break;
            case 'random':
                toRender = PA.randomShowcase
                break;
            default:
                break;
        }

        PA.app.showcase.destroy()

        PA.app.page.render({
            view : toRender,
            pageClass : 'projects',
            section : 'Projects'
        })

        if (view === 'covers') { 
            PA.coverShowcase.firstLoad()
            PA.coverShowcase.filter({ filter : '*' })
        }
    },

    renderSort : function(sort) {
       // NEED TO REFACTOR RENDERING SO SHOWCASES RENDER THEMSELVES TO THE PAGE 
       PA.app.page.$el.html( PA.listShowcase.render(sort) )
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
            .append( new PA.ProjectJumps().render() )
            .append( new PA.ProjectSorts().render() )
            .append( new PA.ProjectViews().render() )

        PA.app.header.$el.append( this.el )

    }
})
