/* app/views/partials/grid.js - Photo Grid View partials */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    //'mixfilter',
    'app/views/partials/filterviews',
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
                this.$el.append( new this.Thumb({
                    model : model
                }).render() )
            }, this )

            this.$el.append($('<div class="gap" />')).append($('<div class="gap" />'))

        },

        render : function() {
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

        init : function(spinner) {
            var hashObj = $.deparam.fragment()

            this.delegateEvents()

            var hasTags = this.collection.some(function(m){ return m.has('type_tags') }) 
            this.filterbar.render({ mixitup : true, hasTags : hasTags })

            this.$el.addClass(this.class)

            if ( !this.collection.length ) {
                throw {
                    message : 'Not Loaded Yet',
                    type : 'EmptyCollection'
                }
            }

            hashObj.filter = hashObj.filter || '*'
            hashObj.sort = hashObj.sort || 'name'
            this.model.set( hashObj )

            history.replaceState({}, '', $.param.fragment('', hashObj))
            if (spinner) {spinner.detach()}
            this.render()
        },

        render : function() {
            var self = this

            this.$el.html( this.grid.render({
                scrolTo : this.lastY
            }) )

            this.lastY = null

            this.$('.page').imagesLoaded(function(){
                $( '#' + self.class + '-grid' ).mixItUp({
                    selectors : {
                        target : '.photo-cell',
                        filter : '',
                        sort : ''
                    },
                    callbacks : {},
                    controls : {
                        enable : false
                    },
                    load : {
                        filter : self.model.get('filter') || '*',
                        sort : self.model.get('sort') + ':asc'
                    }
                })
            })

            this.model.on('change', function(model) {
                var newAttr = model.changedAttributes()
                if ( newAttr.filter ) {
                    this.filter(newAttr.filter)
                } else if ( newAttr.sort ) {
                    this.sort(newAttr.sort)
                }
                Backbone.dispatcher.trigger('savehistory')
            }, this)

            $(window).on('hashchange', function() {
                var hashObj = $.deparam.fragment()
                this.model.set(hashObj)
            }.bind(this))
        },

        filter : function(filter) {
            this.grid.$el.mixItUp('filter', filter)
        },

        sort : function(sort) {
            this.grid.$el.mixItUp('sort', sort + ':asc')
        },

        onClose : function() {
            this.grid.$el.mixItUp('destroy', true)

            $('.page').removeClass(this.class)

            this.model.off('change')
            this.model.clear({ silent : true })

            $(window).off('hashchange')

            this.filterbar.close()
        },

        navigate : function(e) {
            e.preventDefault()
            this.lastY = window.pageYOffset
            Backbone.dispatcher.trigger('navigate:detail', e, this)
        }

    })

    return {
        Grid : Grid,
        Thumb : GridThumb,
        Page : GridPage
    }

})
