"use strict";
var PA = PA || {}
PA.dispatcher = _.extend({}, Backbone.Events)

PA.Header = Backbone.View.extend({
    filterBar : new PA.FilterBar(),
    events : {
        'click nav' : function(e){
            e.preventDefault()
            PA.router.navigate(e.target.pathname, {trigger: true})
        }
    },
    render : function(options) {
        //if (options.filter) {
            this.$el.append( this.filterBar.render({
                collection : options.collection
            }) )
        //}
     }
})

PA.Page = Backbone.View.extend({
    initialize: function() {
        this.outlineTitle = $('<h2/>').addClass('visuallyhidden')
        this.$el.prepend(this.outlineTitle)
    },
    render : function(options) {
        this.$el.html( options.page.render() )
        this.$el.addClass( options.pageClass )

        this.outlineTitle.html( options.section )
        this.$el.prepend(this.outlineTitle)
    }
})

PA.App = Backbone.View.extend({
    initialize : function() {

        this.header = new PA.Header({ el : '.site-header' })
        this.page = new PA.Page({ el : '.page' })

        _.bindAll(this, 'projectFilter')
        PA.dispatcher.bind('filter', this.projectFilter)
    },
    projectFilter : function(e) {
        console.dir(this)
        var f = $(e.currentTarget).data('filter')

        if ( PA.starsRunning ) {
            PA.starDeath()
            PA.projectShowcase = new PA.ShowcaseContainer({ 
                collection : PA.coverImages,
                cover : true,
                type : 'image'
            })
        }

        this.page.$el.html( PA.projectShowcase.render() )
        PA.projectShowcase.$el.isotope({ filter : f })
        //$('#iso-grid').isotope({filter : f })

        //$(filterBar).children('.open').removeClass('open')
        return false
    },
    events : {
        'click' : function(e) { 
            this.$('.open').removeClass('open')
        }
    }
})

PA.app = new PA.App({ el : document })

