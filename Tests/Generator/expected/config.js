(function() {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);
    var admin;

    app.config(function (NgAdminConfigurationProvider) {
        admin = NgAdminConfigurationProvider
            .application('')
            .baseApiUrl(location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '') + '/api/');
    });

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("PostAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var post = nga.entity('post');
            admin.addEntity(post);

            var postFields = [
                nga.field('title'),
                nga.field('body'),
                nga.field('id')
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
            admin.addEntity(comment);

            var commentFields = [
                nga.field('postId'),
                nga.field('body'),
                nga.field('createdAt'),
                nga.field('id')
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
        admin
            .addEntity(PostAdminProvider.$get())
            .addEntity(CommentAdminProvider.$get())
        ;

        NgAdminConfigurationProvider.configure(admin);
    });
})();
