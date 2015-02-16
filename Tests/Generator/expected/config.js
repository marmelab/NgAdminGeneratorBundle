(function() {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);

    app.config(function (NgAdminConfigurationProvider) {
        var nga = NgAdminConfigurationProvider;
        var app = nga.application('')
            .baseApiUrl(location.protocol + '//' + location.hostname + (location.port ? ':' + location.port : '') + '/api/');

        /** Post **/
        var post = nga.entity('post');
        app.addEntity(post);

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

        /** Comment **/
        var comment = nga.entity('comment');
        app.addEntity(comment);

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

        nga.configure(app);
    });
})();
