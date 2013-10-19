'use strict';

define(
    ['app/views/singleproject', 'app/models/project', 'jquery'],
    function( projectView, ProjectModel, $ ) {
        //projectView.setElement('#qunit-fixture')
        //projectView.render()

        test( 'Project Viewer returnd', function() {
            equal( typeof projectView, 'object', 'Project View created')
            ok( projectView.model instanceof ProjectModel, 'Project View instantiated with blank Project Model' )
            ok( projectView.model.attributes, 'Project View has attributes prop')
        })

        test( 'Project Viewer rendered', function(){
            stop()
            $('#qunit-fixture').html( projectView.render('fetish-world') )
            setTimeout( function() {
                ok( projectView.model.get('title'), 'Project View fetches data')
                ok( $('#qunit-fixture').children('.viewer').length, 'Project View rendered into dom')
                ok( projectView.collection.length, 'Showcases populated' )
                ok( projectView.collection.first().get('title'), 'First Showcase item present' )
                ok( projectView.collection.first().get('active'), 'First Showcase item activated' )
                ok( $('#showcaseContainer').children().length, 'Rendered Showcase' )
                start()
            }, 350 )
        })

    }
)
