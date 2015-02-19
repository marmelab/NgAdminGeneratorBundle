(function() {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("PostAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var post = nga.entity('post');

            post.dashboardView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                ]);

            post.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                ])
                .listActions(['show', 'edit', 'delete']);

            post.creationView()
                .fields([
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                ]);

            post.editionView()
                .fields([
                    nga.field('id', 'number')
                        .editable(false)
                        .isDetailLink(false),
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                    nga.field('comments', 'referenced_list')
                        .targetEntity(nga.entity('comment'))
                        .targetReferenceField('post_id')
                        .targetFields([
                            nga.field('id', 'number'),
                            nga.field('body', 'text'),

                    ]),
                ]);

            post.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                    nga.field('comments', 'referenced_list')
                        .targetEntity(nga.entity('comment'))
                        .targetReferenceField('post_id')
                        .targetFields([
                            nga.field('id', 'number'),
                            nga.field('body', 'text'),

                    ]),
                ]);

            return post;
        });
    });

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("CommentAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var comment = nga.entity('comment');

            comment.dashboardView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('body', 'text'),
                    nga.field('post_id', 'reference')
                        .targetEntity(nga.entity('post'))
                        .targetField(nga.field('title')),
                ]);

            comment.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('body', 'text'),
                    nga.field('post_id', 'reference')
                        .targetEntity(nga.entity('post'))
                        .targetField(nga.field('title')),
                ])
                .listActions(['show', 'edit', 'delete']);

            comment.creationView()
                .fields([
                    nga.field('body', 'text'),
                    nga.field('post_id', 'reference')
                        .targetEntity(nga.entity('post'))
                        .targetField(nga.field('title')),
                ]);

            comment.editionView()
                .fields([
                    nga.field('id', 'number')
                        .editable(false)
                        .isDetailLink(false),
                    nga.field('body', 'text'),
                    nga.field('post_id', 'reference')
                        .targetEntity(nga.entity('post'))
                        .targetField(nga.field('title')),
                ]);

            comment.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('body', 'text'),
                    nga.field('post_id', 'reference')
                        .targetEntity(nga.entity('post'))
                        .targetField(nga.field('title')),
                ]);

            return comment;
        });
    });

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("TagAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var tag = nga.entity('tag');

            tag.dashboardView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('name', 'string'),
                ]);

            tag.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('name', 'string'),
                ])
                .listActions(['show', 'edit', 'delete']);

            tag.creationView()
                .fields([
                    nga.field('name', 'string'),
                ]);

            tag.editionView()
                .fields([
                    nga.field('id', 'number')
                        .editable(false)
                        .isDetailLink(false),
                    nga.field('name', 'string'),
                ]);

            tag.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('name', 'string'),
                ]);

            return tag;
        });
    });

    app.config(function(NgAdminConfigurationProvider, PostAdminProvider, CommentAdminProvider, TagAdminProvider) {
        var admin = NgAdminConfigurationProvider
            .application('')
            .baseApiUrl(location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '') + '/api/')

        admin
            .addEntity(PostAdminProvider.$get())
            .addEntity(CommentAdminProvider.$get())
            .addEntity(TagAdminProvider.$get())
        ;

        NgAdminConfigurationProvider.configure(admin);
    });
})();

