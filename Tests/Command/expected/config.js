(function() {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);

    // use custom query parameters function to format the API request correctly
    app.config(function(RestangularProvider) {
        RestangularProvider.addFullRequestInterceptor(function(element, operation, what, url, headers, params) {
            if (operation == "getList") {
                // custom pagination params
                params._offset = (params._page - 1) * params._perPage;
                params._limit = params._perPage;
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

    app.config(function($provide, NgAdminConfigurationProvider, RestangularProvider) {
        $provide.factory("PostsAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var posts = nga.entity('posts');

            RestangularProvider.addElementTransformer('posts', function(element) {
                if (element.tags) {
                    element.tags = element.tags.map(function(item) {
                        return item.id;
                    });
                }

                return element;
            });

            posts.menuView()
                .icon('<span class="glyphicon glyphicon-pencil"></span>');

            posts.dashboardView()
                .title('Recent posts')
                .limit(5)
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title'),
                    nga.field('body', 'text'),
                ]);

            posts.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tags'))
                        .targetField(nga.field('name')),
                ])
                .listActions(['show', 'edit', 'delete']);

            posts.creationView()
                .fields([
                    nga.field('title'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tags'))
                        .targetField(nga.field('name')),
                ]);

            posts.editionView()
                .fields([
                    nga.field('id', 'number')
                        .editable(false)
                        .isDetailLink(false),
                    nga.field('title'),
                    nga.field('body', 'text'),
                    nga.field('comments', 'referenced_list')
                        .targetEntity(nga.entity('comments'))
                        .targetReferenceField('post_id')
                        .targetFields([
                            nga.field('id', 'number'),
                            nga.field('body', 'text'),

                    ]),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tags'))
                        .targetField(nga.field('name')),
                ]);

            posts.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('title'),
                    nga.field('body', 'text'),
                    nga.field('comments', 'referenced_list')
                        .targetEntity(nga.entity('comments'))
                        .targetReferenceField('post_id')
                        .targetFields([
                            nga.field('id', 'number'),
                            nga.field('body', 'text'),

                    ]),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tags'))
                        .targetField(nga.field('name')),
                ]);

            return posts;
        });
    });

    app.config(function($provide, NgAdminConfigurationProvider, RestangularProvider) {
        $provide.factory("CommentsAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var comments = nga.entity('comments');

            RestangularProvider.addElementTransformer('comments', function(element) {
                if (element.post) {
                    element.post = element.post.id;
                }

                return element;
            });

            comments.menuView()
                .icon('<span class="glyphicon glyphicon-comment"></span>');

            comments.dashboardView()
                .title('Recent comments')
                .limit(5)
                .fields([
                    nga.field('id', 'number'),
                    nga.field('body', 'text'),
                    nga.field('post', 'reference')
                        .targetEntity(nga.entity('posts'))
                        .targetField(nga.field('title')),
                ]);

            comments.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('body', 'text'),
                    nga.field('post', 'reference')
                        .targetEntity(nga.entity('posts'))
                        .targetField(nga.field('title')),
                ])
                .listActions(['show', 'edit', 'delete']);

            comments.creationView()
                .fields([
                    nga.field('body', 'text'),
                    nga.field('post', 'reference')
                        .targetEntity(nga.entity('posts'))
                        .targetField(nga.field('title')),
                ]);

            comments.editionView()
                .fields([
                    nga.field('id', 'number')
                        .editable(false)
                        .isDetailLink(false),
                    nga.field('body', 'text'),
                    nga.field('post', 'reference')
                        .targetEntity(nga.entity('posts'))
                        .targetField(nga.field('title')),
                ]);

            comments.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('body', 'text'),
                    nga.field('post', 'reference')
                        .targetEntity(nga.entity('posts'))
                        .targetField(nga.field('title')),
                ]);

            return comments;
        });
    });

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("TagsAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var tags = nga.entity('tags');

            tags.menuView()
                .icon('<span class="glyphicon glyphicon-tags"></span>');

            tags.dashboardView()
                .title('Recent tags')
                .limit(5)
                .fields([
                    nga.field('id', 'number'),
                    nga.field('name'),
                    nga.field('created_by'),
                ]);

            tags.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('name'),
                    nga.field('created_by'),
                ])
                .listActions(['show', 'edit', 'delete']);

            tags.creationView()
                .fields([
                    nga.field('name'),
                    nga.field('created_by'),
                ]);

            tags.editionView()
                .fields([
                    nga.field('id', 'number')
                        .editable(false)
                        .isDetailLink(false),
                    nga.field('name'),
                    nga.field('created_by'),
                ]);

            tags.showView()
                .fields([
                    nga.field('id', 'number')
                        .isDetailLink(false),
                    nga.field('name'),
                    nga.field('created_by'),
                ]);

            return tags;
        });
    });

    app.config(function(NgAdminConfigurationProvider, PostsAdminProvider, CommentsAdminProvider, TagsAdminProvider) {
        var admin = NgAdminConfigurationProvider
            .application('')
            .baseApiUrl(location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '') + '/api/')

        admin
            .addEntity(PostsAdminProvider.$get())
            .addEntity(CommentsAdminProvider.$get())
            .addEntity(TagsAdminProvider.$get())
        ;

        NgAdminConfigurationProvider.configure(admin);
    });
})();

