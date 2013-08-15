'use strict';
var PA = PA || {}

PA.ProjectListView = Backbone.View.extend({
    tagName : 'section',
    header : PA.jst.listHeaderPartial,
    partial : PA.jst.listItemPartial,

    render : function() {
        var projects = this.options.projects
        var path = this.options.path
        var date = this.options.date

        this.$el.append( '<ul />')
        this.$('ul').append( this.header({ date : date }) )

        _.each( projects, function(project) {

            this.$('ul')
            .append( this.partial({
                path : path ? path + "/" : "",
                url : project.get('url'),
                title : project.get('title'),
                summary : path === 'projects' ? project.get('summary') : ''
            }) )

        }, this )

        return this.el
    }

})

PA.ProjectListShowcase = Backbone.View.extend({
    tagName : 'div',
    className : 'showcase list',
    render : function(){
        this.$el.empty()

        // groupedCollection is an object of years paired with project objects that fall within that year.
        _.each( this.options.groupedCollection, function(v,k){
            var html = new PA.ProjectListView({
                date : k,
                projects : v,
                path : this.options.path
            })
            this.$el.append( html.render() )
        }, this )
        return this.el
    }
})

