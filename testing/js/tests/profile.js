'use strict';

define([
    'backbone',
    'jquery',
    'app/collections/profile',
    'app/views/profile'
],
    function( Backbone, $, s, v  ){
        asyncTest( 'Model created', function(){
            $.get('/api/bio').done(function(d){
                start()
                ok( d, 'valid response')
            })
        })
        asyncTest( 'Model Fetch', function(){
            var p = new Backbone.Model({}, { url : '/api/bio' })
            p.fetch({
                success : function(model){
                    start()
                    ok( model.get('title'), 'created' )
                }
            })
        })
        test( 's and fetches', function(){
            ok( s.length, 'Profile Sections collections come in')
            stop()
            var promiseStack = []
            s.each(function(section){
                promiseStack.push( section.get('content').fetch() )
            })
            $.when.apply( $, promiseStack ).done(function(){
                start()
                ok( s.section('bio').get('title'), 'Bio Content loaded via promise stack' )
                ok( s.section('articles-by-pa').first().get('date').year(), 'parseDate works on Collection class')
            })
        })
        test( 'Profile Section Render', function(){
            ok( !$('#qunit-fixture').children().length, 'qunit fixture starts empty')
            ok( !v.collection.findWhere({ active: true }), 'No Sections active' )
            $('#qunit-fixture').html( v.render('bio') )
            ok( $('#qunit-fixture').find('img').length, 'Bio rendered into DOM')
            equal( v.collection.findWhere({ active: true }).id, 'bio', 'Bio Section activated' )
            ok( $('.viewer a.active').length, 'Section link activated' )
            $('#qunit-fixture').html( v.render('press') )
            equal( v.collection.findWhere({ active: true }).id, 'press', 'Press Section activated' )
            equal( $('.viewer a.active').length, 1, 'Only one section active at a time')
        })
    }
)
