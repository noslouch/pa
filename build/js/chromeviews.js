"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Header = Backbone.View.extend({
    initialize: function() {
        _.bindAll(this, 'render')
        this.filterBar = new PA.FilterBar()
    },
    events : {
        'click nav' : function(e){
            //e.preventDefault()
            //PA.router.navigate(e.target.pathname, {trigger: true})
        }
    },
    render : function(options) {
     },
})

PA.Page = Backbone.View.extend({
    initialize: function() {
        _.bindAll(this, 'render')

        this.outlineTitle = $('<h2/>').addClass('visuallyhidden')
        this.$el.prepend(this.outlineTitle)
    },
    render : function(options) {
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

        PA.dispatcher.on('filter', this.projectFilter)
        PA.dispatcher.listenTo(PA.router, 'route', this.routeHandler)
    },
    projectFilter : function(e) {
        this.closeMenu()
        var f = $(e.currentTarget).data('filter')

        if ( PA.starsRunning ) {
            PA.starDeath()
            this.showcase.firstLoad()
        }

        this.showcase.filter(f)
    },
    events : {
        'click' : 'closeMenu'
    },
    closeMenu : function(e) { 
        this.$('.open').removeClass('open')
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