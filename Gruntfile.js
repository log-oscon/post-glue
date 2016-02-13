module.exports = function (grunt) {

  var pkg = grunt.file.readJSON('package.json');

  var distFiles =  [
    '**',
    '!**/.*',
    '!apigen.neon',
    '!assets/**',
    '!bin/**',
    '!build/**',
    '!bootstrap.php',
    '!CHANGELOG.md',
    '!composer.json',
    '!composer.lock',
    '!Gruntfile.js',
    '!node_modules/**',
    '!package.json',
    '!phpdoc.dist.xml',
    '!phpunit.xml',
    '!phpunit.xml.dist',
    '!README.md',
    '!svn/**',
    '!tests/**',
    '!vendor/**',
  ];

  // Project configuration
  grunt.initConfig({

    pkg: pkg,

    checktextdomain: {
      options: {
        text_domain:    'post-glue',
        correct_domain: false,
        keywords:       [
          '__:1,2d',
          '_e:1,2d',
          '_x:1,2c,3d',
          'esc_html__:1,2d',
          'esc_html_e:1,2d',
          'esc_html_x:1,2c,3d',
          'esc_attr__:1,2d',
          'esc_attr_e:1,2d',
          'esc_attr_x:1,2c,3d',
          '_ex:1,2c,3d',
          '_n:1,2,4d',
          '_nx:1,2,4c,5d',
          '_n_noop:1,2,3d',
          '_nx_noop:1,2,3c,4d',
          ' __ngettext:1,2,3d',
          '__ngettext_noop:1,2,3d',
          '_c:1,2d',
          '_nc:1,2,4c,5d'
        ]
      },
      files: {
        src: [
          'uninstall.php',
          'post-glue.php',
        ],
        expand: true
      }
    },

    makepot: {
      target: {
        options: {
          cwd:         '',
          domainPath:  '/languages',
          potFilename: 'post-glue.pot',
          mainFile:    'post-glue.php',
          include:     [],
          exclude:     [
            'assets/',
            'bin/',
            'build/',
            'languages/',
            'node_modules',
            'release/',
            'svn/',
            'tests/',
            'tmp',
            'vendor'
          ],
          potComments: '',
          potHeaders:  {
            poedit:                  true,
            'x-poedit-keywordslist': true,
            'language':              'en_US',
            'report-msgid-bugs-to':  'https://github.com/goblindegook/post-glue',
            'last-translator':       'Luís Rodrigues <hello@goblindegook.net>',
            'language-Team':         'Luís Rodrigues <hello@goblindegook.net>'
          },
          type:            'wp-plugin',
          updateTimestamp: true,
          updatePoFiles:   true,
          processPot:      null
        }
      }
    },

    clean: {
      main: [
        'build',
      ]
    },

    // Copy the plugin to build directory
    copy: {
      main: {
        expand: true,
        src:    distFiles,
        dest:   'build/post-glue'
      }
    },

    compress: {
      main: {
        options: {
          mode:    'zip',
          archive: './build/post-glue-<%= pkg.version %>.zip'
        },
        expand: true,
        src:    distFiles,
        dest:   '/post-glue/'
      }
    },

    wp_deploy: {
      deploy: {
        options: {
          plugin_slug: 'post-glue',
          build_dir:   'build/post-glue',
          assets_dir:  'assets',
          svn_url:     'https://plugins.svn.wordpress.org/post-glue'
        }
      }
    }

  });

  // Load tasks
  require('load-grunt-tasks')(grunt);

  // Register tasks
  grunt.registerTask('pot', [
    'checktextdomain',
    'makepot',
  ]);

  grunt.registerTask('build', [
    'clean',
    'copy',
    'compress',
  ]);

  grunt.registerTask('deploy', [
    'pot',
    'build',
    'wp_deploy',
  ]);

  grunt.util.linefeed = '\n';
};
