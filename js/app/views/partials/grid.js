/* app/views/partials/grid.js - Photo Grid View partials */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'mixfilter',
    'mixitup',
    'imagesLoaded'
], function( $, Backbone, _, TPL, Filter ) {

    var GridThumb = Backbone.View.extend({
        tagName : 'div',
        className : function() {
            if ( this.model.has('type_tags') ) {
                var tags = []
                _.each( this.model.get('type_tags'), function(obj) {
                    tags.push( obj.className )
                }, this )
                return 'photo-cell ' + tags.join(' ')
            } else {
                return 'photo-cell'
            }
        },
        template : TPL.gridThumb,
        render : function() {
            var html = this.template({
                url : this.url(),
                thumb : this.model.get('thumb'),
                title : this.model.get('title'),
                summary : this.model.get('summary')
            })
            this.$el.append( html )
            this.el.dataset.name = this.model.get('title')
            this.el.dataset.date = this.model.get('date').unix()
            return this.el
        }
    })

    var Grid = Backbone.View.extend({
        tagName : 'div',
        className: 'photo-grid showcase',
        rowTmpl : TPL.gridRow,
        $row : undefined,
        initialize : function() {
            if ( this.$el.children().length ) { return this.el }

            this.collection.forEach( function(model, index){
                //if (index % 4 === 0) {
                //    this.$row = $( this.rowTmpl() ).addClass(this.class)
                //    this.$el.append(this.$row)
                //}
                //this.$row.append( new this.Thumb({
                //    model : model
                //}).render() )

                this.$el.append( new this.Thumb({
                    model : model
                }).render() )
            }, this )

        },

        render : function() {
            //this.delegateEvents()
            return this.el
        }
    })

    var GridPage = Backbone.View.extend({
        initialize : function(){
            _.bindAll( this, 'render', 'navigate', 'filter', 'init' )
            var self = this

            this.collection.fetch({
                success : function(collection) {
                    self.grid = new self.Grid({
                        collection : collection,
                        id : self.class + '-grid'
                    })

                    self.filterbar = new Filter({
                        el : '#filter-bar',
                        model : self.model,
                        collection : collection
                    })

                    Backbone.dispatcher.trigger(self.class + ':ready', self)
                }
            })

        },

        events : {
            'click .photo-cell a' : 'navigate'
        },

        render : function() {
            var self = this
            $(window).on('hashchange', this.filter)
            this.$el.html( this.grid.render() )

            this.$('.page').imagesLoaded(function(){
                $( '#' + self.class + '-grid' ).mixitup({
                    targetSelector : '.photo-cell',
                    filterSelector : '',
                    minHeight : '100%',
                    onMixLoad : function() {
                        if ( document.location.hash ) {
                            $(window).trigger('hashchange')
                        }
                    }
                })

            })
        },

        init : function(spinner) {

            this.delegateEvents()
            this.filterbar.render( this.collection.some(function(m){ return m.has('type_tags') }) )
            this.$el.addClass(this.class)

            if ( !this.collection.length ) {
                throw {
                    message : 'Not Loaded Yet',
                    type : 'EmptyCollection'
                }
            }

            if (spinner) {spinner.detach()}
            this.render()
        },

        filter : function(e) {
            if (!e.fragment) { return }
            var hash = $.bbq.getState()
            this.model.set(hash)
            if (this.model.hasChanged('filter')) {
                this.grid.$el.mixitup('filter', hash.filter === '*' ? 'all' : hash.filter.slice(1) )
            }
            if (this.model.hasChanged('sort')) {
                this.grid.$el.mixitup('sort', 'data-' + hash.sort)
            }
            this.filterbar.delegateEvents()
        },

        onClose : function() {
            this.model.unset('sort').unset('filter')
            $('.page').removeClass(this.class)
            this.filterbar.close()
            $(window).off('hashchange')
        },


        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
        }

    })

    return {
        Grid : Grid,
        Thumb : GridThumb,
        Page : GridPage
    }

})
