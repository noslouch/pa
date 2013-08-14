'use strict';
var PA = PA || {}

var TagRow = Backbone.View.extend({
    tagName : 'li',
    className : 'row',
    template : PA.jst.tagLinks,
    tag : PA.jst.tag,
    render : function() {
        this.$el.html( this.template({ 
            type : this.options.type
        }) )
        this.options.tags.forEach( function(tag) {
            this.$('#tagLinks').append( this.tag({ tag : tag }) )
        }, this )

        return this.el
    }
})

// instantiated with el : #details
var ProjectDetails = Backbone.View.extend({
    events : {},
    template : PA.jst.projectDetails,
    render : function(options) {
        this.$el.html( this.template({
            htmlDate : new Date( parseInt(this.model.get('date'),10) ).getFullYear(),
            date : new Date( parseInt(this.model.get('date'),10) ).getFullYear(),
            title : this.model.get('title'),
            summary : this.model.get('summary')
        }) )

        return this.el
    }
})

var ProjectViewer = Backbone.View.extend({
    tagName : "div",
    className : "project viewer",
    baseTmpl : PA.jst.viewer,
    initialize : function() {
        this.$el.html( this.baseTmpl() )
        this.details = new ProjectDetails({ 
            el : this.$('#details'),
            model : this.model
        })
        this.showcase = new PA.ShowcaseContainer({ el : this.$('#showcaseContainer') })
    },
    render: function(options) {
        this.details.render()

        var showcases = this.model.get('showcases')
        showcases.forEach( function(showcase) {
            this.$('#showcaseLinks')
                .prepend( PA.jst.showcaseLinks({
                    cid : showcase.cid,
                    title : showcase.get('title')
                }) )
        }, this )

        this.$('#tags')
            .append( new TagRow({ 
                type : 'Brand', 
                tags : this.model.get('brand_tags') 
            }).render() )
            .append( new TagRow({ 
                type : 'Industry', 
                tags : this.model.get('industry_tags') 
            }).render() )
            .append( new TagRow({ 
                type : 'Project Type', 
                tags : this.model.get('type_tags') 
            }).render() )

        return this.el
    },
    events : {
        "click .showcase-links a" : function(e) {
            e.preventDefault()
            var showcaseModel = this.model.get('showcases').get(e.target.id)
            if (showcaseModel.get('type') === 'gallery') {
                var gallery = showcaseModel.get('gallery')
                PA.showcase = new PA.ShowcaseContainer({el : "#showcaseContainer"})
                PA.showcase.render({
                    collection : gallery,
                    cover : false,
                    type: 'image'
                })
            }
        }
    }
})

