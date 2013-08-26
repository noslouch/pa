"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Header = Backbone.View.extend({
    initialize: function() {
        this.filterBar = new PA.FilterBar()
    }
})

PA.Page = Backbone.View.extend({
    initialize: function() {
        _.bindAll(this, 'render')

        this.outlineTitle = $('<h2/>').addClass('visuallyhidden')
        this.$el.prepend(this.outlineTitle)
    },
    render : function(options) {
       // NEED TO REFACTOR RENDERING SO SHOWCASES RENDER THEMSELVES TO THE PAGE 
        this.options.parent.showcase = options.view

        this.$el.html( options.view.render() )
        this.$el.addClass( options.pageClass )

        if (options.section) {
            this.outlineTitle.html( options.section )
            this.$el.prepend( this.outlineTitle )
        }

        this.last = options
    }
})

PA.App = Backbone.View.extend({
    initialize : function() {
        _.bindAll(this, 'projectFilter', 'render', 'routeHandler')

        this.header = new PA.Header({ el : '.site-header', parent : this })
        this.page = new PA.Page({ el : '.page', parent : this})

        this.listenTo( PA.dispatcher, 'filter', this.projectFilter )
        this.listenTo( PA.router, 'route', this.routeHandler )
    },

    projectFilter : function(e) {
        this.closeMenu()

        var hashObj = $.deparam.fragment()

        if ( this.showcase !== PA.coverShowcase ) {
            this.showcase.destroy()

            PA.app.page.render({
                view : PA.coverShowcase,
                pageClass : 'projects',
                section : 'Projects'
            })

            this.showcase.firstLoad()
        }

        this.showcase.filter(hashObj)
    },

    events : {
        'click' : 'closeMenu'
    },
    closeMenu : function(e) { 
        this.header.$('.open').removeClass('open')
    },
    routeHandler : function(methodName, urlParam) {
        if (methodName !== 'projects'){
            try {
                this.header.filterBar.remove()
            } catch(e) {}
        }
        try {
            this.page.$el.removeClass( this.last.pageClass )
        } catch(e) {}
    }
})
