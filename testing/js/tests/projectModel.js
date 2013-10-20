'use strict';

define(
    ['app/models/project', 'app/collections/showcases'],
    function( ProjectModel, Showcases ) {
        asyncTest('Valid Project Created', function() {
            var p = new ProjectModel()
            p.fetch({
                url : '/api/projects/alessi-product-design',
                success : function(model) {
                    start()
                    ok( model.get('url-title'), 'Project Created' )
                    equal( typeof p.attributes.coverImage, 'object', 'p has a cover image')
                    equal( typeof p.attributes.coverImage.get('year'), 'number', 'p has a year')
                    ok( p.get('showcases') instanceof Showcases, 'p has showcases')
                    ok( p.attributes.showcases.length, 'p has showcases')
                }
            })
        })
        test( 'Project Constructor returned', function(){
            equal( typeof ProjectModel, 'function', ' Project Constructor')
        })
    }
)
