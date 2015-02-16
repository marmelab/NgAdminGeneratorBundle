(function() {
    "use strict";

    var app = angular.module('myApp', ['ng-admin']);

    app.config(function(NgAdminConfigurationProvider, Application, Entity, Field, Reference, ReferencedList, ReferenceMany) {
        function pagination(page, maxPerPage) {
            return {
                _offset: (page - 1) * maxPerPage,
                _limit: maxPerPage
            };
        }

        var post = new Entity('post')
            .label('post')
            .pagination(pagination)
            .dashboard(10)
            .addField(new Field('id').type('integer'))
            .addField(new Field('title').type('string'))
            .addField(new Field('body').type('text'))
        ;

        var comment = new Entity('comment')
            .label('comment')
            .pagination(pagination)
            .dashboard(10)
            .addField(new Field('id').type('integer'))
            .addField(new Field('body').type('text'))
            .addField(new Field('created_at').type('date'))
            .addField(new Field('post_id').type('integer'))
        ;

        var app = new Application('ng-admin backend demo')
            .baseApiUrl('./api/')
            .addEntity(post)
            .addEntity(comment);

        NgAdminConfigurationProvider.configure(app);
    })
})();
