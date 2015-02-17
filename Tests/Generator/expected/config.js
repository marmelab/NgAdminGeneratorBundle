(function() {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("PostAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var post = nga.entity('post');

            var postFields = [
                nga.field('title', 'string'),
                nga.field('body', 'text'),
                nga.field('id', 'number')
            ];

            post.dashboardView()
                .fields(postFields);

            post.listView()
                .fields(postFields)
                .listActions(['show', 'edit', 'delete']);

            post.creationView()
                .fields(postFields);

            post.editionView()
                .fields(postFields);

            post.showView()
                .fields(postFields);

            return post;
        });
    });

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("CommentAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var comment = nga.entity('comment');

            var commentFields = [
                nga.field('postId', 'number'),
                nga.field('body', 'text'),
                nga.field('createdAt', 'date'),
                nga.field('id', 'number')
            ];

            comment.dashboardView()
                .fields(commentFields);

            comment.listView()
                .fields(commentFields)
                .listActions(['show', 'edit', 'delete']);

            comment.creationView()
                .fields(commentFields);

            comment.editionView()
                .fields(commentFields);

            comment.showView()
                .fields(commentFields);

            return comment;
        });
    });

    app.config(function(NgAdminConfigurationProvider, PostAdminProvider, CommentAdminProvider) {
        var admin = NgAdminConfigurationProvider
            .application('')
            .baseApiUrl(location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '') + '/api/')

        admin
            .addEntity(PostAdminProvider.$get())
            .addEntity(CommentAdminProvider.$get())
        ;

        NgAdminConfigurationProvider.configure(admin);
    });
})();
