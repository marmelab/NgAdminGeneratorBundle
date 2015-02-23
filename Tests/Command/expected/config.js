(function() {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);

    // use custom query parameters function to format the API request correctly
    app.config(function(RestangularProvider) {
        RestangularProvider.addFullRequestInterceptor(function(element, operation, what, url, headers, params) {
            if (operation == "getList") {
                // custom pagination params
                params._start = (params._page - 1) * params._perPage;
                params._end = params._page * params._perPage;
                delete params._page;
                delete params._perPage;

                // custom sort params
                if (params._sortField) {
                    params._orderBy = params._sortField;
                    params._orderDir = params._sortDir;
                    delete params._sortField;
                    delete params._sortDir;
                }

                // custom filters
                if (params._filters) {
                    for (var filter in params._filters) {
                        params[filter] = params._filters[filter];
                    }
                    delete params._filters;
                }
            }

            return { params: params };
        });
    });

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("PostAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var post = nga.entity('post');

            post.menuView()
                .icon('<span class="glyphicon glyphicon-pencil"></span>');

            post.dashboardView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title'),
                    nga.field('body', 'text'),
                ]);

            post.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                ])
                .listActions(['show', 'edit', 'delete']);

            post.creationView()
                .fields([
                    nga.field('title'),
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
                    nga.field('title'),
                    nga.field('body', 'text'),
                    nga.field('comments', 'referenced_list')
                        .targetEntity(nga.entity('comment'))
                        .targetReferenceField('post_id')
                        .targetFields([
                            nga.field('id', 'number'),
                            nga.field('body', 'text'),

                    ]),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                ]);

            post.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('title'),
                    nga.field('body', 'text'),
                    nga.field('comments', 'referenced_list')
                        .targetEntity(nga.entity('comment'))
                        .targetReferenceField('post_id')
                        .targetFields([
                            nga.field('id', 'number'),
                            nga.field('body', 'text'),

                    ]),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                ]);

            return post;
        });
    });

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("CommentAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var comment = nga.entity('comment');

            comment.menuView()
                .icon('<span class="glyphicon glyphicon-comment"></span>');

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

            tag.menuView()
                .icon('<span class="glyphicon glyphicon-tags"></span>');

            tag.dashboardView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('name'),
                    nga.field('created_by'),
                ]);

            tag.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('name'),
                    nga.field('created_by'),
                ])
                .listActions(['show', 'edit', 'delete']);

            tag.creationView()
                .fields([
                    nga.field('name'),
                    nga.field('created_by'),
                ]);

            tag.editionView()
                .fields([
                    nga.field('id', 'number')
                        .editable(false)
                        .isDetailLink(false),
                    nga.field('name'),
                    nga.field('created_by'),
                ]);

            tag.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('name'),
                    nga.field('created_by'),
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

