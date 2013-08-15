"use strict";

var PA = PA || {}

PA.ProfileLinks = Backbone.View.extend({
    tagName : 'ul',
    className : 'showcase-links',
    id : 'showcaseLinks',
    template : PA.jst.showcaseLinks,
    render : function() {},
    events : {}
})

PA.ProfileViewer = Backbone.View.extend({
    tagName : 'div',
    className : 'profile viewer',
    baseTmpl : PA.jst.viewer,
    pLinks : PA.jst.profileLinks,
    initialize : function() {
        this.$el.html( this.baseTmpl() )
        this.$('#details').html( this.pLinks() )
        this.$('#bio').addClass('active')
        this.showcase = new PA.ShowcaseContainer({ el : this.$('#showcaseContainer') })
    },
    render : function() {
        return this.el
    }
})
