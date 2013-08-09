"use strict";

var projects = new Backbone.Collection()

var Project = Backbone.Model.extend({
    initialize : function() {
        this.set({
            allTags : this.attributes.brand_tags.concat(this.attributes.type_tags).concat(this.attributes.industry_tags) })
    }
})
