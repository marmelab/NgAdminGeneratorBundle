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
                nga.field('id', 'number'),
                nga.field('tags', 'reference_many')
                    .targetEntity(nga.entity('tag'))
                    .targetField(nga.field('name')),
                nga.field('comments', 'referenced_list')
                    .targetEntity(nga.entity('comment'))
                    .targetReferenceField('post_id')
                    .targetFields([
                        nga.field('body', 'text'),
                        nga.field('created_at', 'date'),
                        nga.field('id', 'number'),

                    ])
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
                nga.field('body', 'text'),
                nga.field('created_at', 'date'),
                nga.field('id', 'number'),
                nga.field('post_id', 'reference')
                    .targetEntity(nga.entity('post'))
                    .targetField(nga.field('title'))
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

    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("TagAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var tag = nga.entity('tag');

            var tagFields = [
                nga.field('name', 'string'),
                nga.field('id', 'number')
            ];

            tag.dashboardView()
                .fields(tagFields);

            tag.listView()
                .fields(tagFields)
                .listActions(['show', 'edit', 'delete']);

            tag.creationView()
                .fields(tagFields);

            tag.editionView()
                .fields(tagFields);

            tag.showView()
                .fields(tagFields);

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
