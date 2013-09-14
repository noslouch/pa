'use strict';

module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    // Metadata.
    pkg: grunt.file.readJSON('package.json'),
    banner: '/*! <%= pkg.title || pkg.name %> - v<%= pkg.version %> - ' +
      '<%= grunt.template.today("yyyy-mm-dd") %>\n' +
      '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
      '* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.dev %>;' +
      ' Licensed <%= _.pluck(pkg.licenses, "type").join(", ") %> */\n',
    // Task configuration.

    replace : {
      build_replace : {
        options : {
          variables : {
            'hash' : '<%= ( (new Date()).valueOf().toString() ) + ( Math.floor( (Math.random()*1000000)+1 ).toString() ) %>'
          }
        },
        files : [
          {
            flatten : true,
            expand : true,
            src: ['assets/html/header.html'],
            dest: 'templates/default_site/includes.group/'
          }
        ]
      }
    },

    concat: {
      options: {
        banner: '<%= banner %>',
        stripBanners: true
      },
      dist: {
        src: ['src/<%= pkg.name %>.js'],
        dest: 'build/<%= pkg.name %>.js'
      },
    },

    uglify: {
      options: {
        banner: '<%= banner %>'
      },
      dist: {
        src: '<%= concat.dist.dest %>',
        dest: 'build/<%= pkg.name %>.min.js'
      },
    },

    qunit: {
      files: ['test/**/*.html']
    },

    jshint: {
      gruntfile: {
        options: {
          jshintrc: '.jshintrc'
        },
        src: 'Gruntfile.js'
      },
      src: {
        options: {
          jshintrc: 'js/.jshintrc'
        },
        src: ['js/app/**/*.js', 'js/app.js', 'js/utils/**/*.js']
      }
    },

    compass : {
        create : {
            config: '/config.rb'
        }
    },

    copy: {
        dev : {
            //files : [{ expand: true, src: ['src/js/**/*.js'], dest: 'build/js/', flatten: true }]
        }
    },

    watch: {
      options : {
        livereload: true
      },
      gruntfile: {
        files: '<%= jshint.gruntfile.src %>',
        tasks: ['jshint:gruntfile']
      },
      src: {
        files: '<%= jshint.src.src %>',
        tasks: ['jshint:src', 'qunit']
      },
      dev: {
        files: ['js/**/*', 'css/sass/**/*', 'templates/**/*', 'assets/html/**/*'],
        tasks : ['compass', 'jshint:src']
      }
    },

    clean: {
        build : "build"
    }

  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-qunit');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-replace');

  // Default task.
  grunt.registerTask('default', ['jshint', 'qunit', 'concat', 'uglify']);
  grunt.registerTask('refresh', ['clean', 'compass', 'jshint:src', 'copy:dev']);

};
