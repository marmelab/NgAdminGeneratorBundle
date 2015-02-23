NgAdminGeneratorBundle [![Build Status](https://travis-ci.org/marmelab/NgAdminGeneratorBundle.png?branch=master)](https://travis-ci.org/marmelab/NgAdminGeneratorBundle)
======================

You're a fan of [StanLemonRestBundle](#) because of its facility to create a REST API based on your entities?
You starred [ng-admin](#) as it helped you to create a fully functional administration panel with a single configuration file?
You will then love NgAdminGeneratorBundle, which bootstraps ng-admin based on your API structure!

## Installation

Using this bundle in your own project is pretty straightforward, thanks to composer:

`composer require marmelab/NgAdminGeneratorBundle`

Then, register it to your `AppKernel.php` file:

``` php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \marmelab\NgAdminGeneratorBundle\marmelabNgAdminGeneratorBundle(),
        );
    }
}
```

As you will generate your configuration only in development mode, no need to add a small overhead in production registering
the bundle for all your environments.

No more configuration, you are now ready to go!

## Generating your ng-admin configuration

This bundle just adds the following command to your application:

`./app/console ng-admin:configuration:generate`

By default, it outputs configuration into standard output. But you are free to redirect it into file of your choice:

`./app/console ng-admin:conf:generate > public/js/ng-admin-config.js`

Note that thanks to Symfony2 Console component, you can truncate parts of the command, such as `config` instead of 
`configuration`.

## Configuration sample

Here is a sample of auto-generated configuration, based on [stanlemon/rest-demo-app] demo application. This application
set up same domain as the official [ng-admin demonstration](#). Generator simply uses [entities mapping]
(https://github.com/stanlemon/rest-demo-app/tree/master/src/Lemon/RestDemoBundle/Entity) to better know which field to use.

Comments are addition for this README, and won't be added automatically:

``` js
    var app = angular.module('myApp', ['ng-admin']);

    // Deal with query parameters expected by StanLemon bundle
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
    
    /* Define a `config` block for each entity, allowing to split configuration
       across several files. */
    app.config(function($provide, NgAdminConfigurationProvider) {
        $provide.factory("PostAdmin", function() {
            var nga = NgAdminConfigurationProvider;
            var post = nga.entity('post');

            // Dashboard (as list) won't display referenced list of items.
            post.dashboardView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                    // We limit to 3 number of fields displayed on dashboard
                ]);

            post.listView()
                .fields([
                    nga.field('id', 'number'),
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                    // Take more meaningful field. Here, use `name` instead of `id`
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                ])
                .listActions(['show', 'edit', 'delete']);

            post.creationView()
                .fields([
                    // Do not display id: we don't have any yet
                    nga.field('title', 'string'),
                    nga.field('body', 'text'),
                    nga.field('tags', 'reference_many')
                        .targetEntity(nga.entity('tag'))
                        .targetField(nga.field('name')),
                    // No referenced_list either, as that's a brand new entity
                ]);

            post.editionView()
                .fields([
                    nga.field('id', 'number').readOnly(), // don't modify id
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
                            nga.field('created_at', 'date'),

                    ]),
                ]);

            /* To ease configuration per view, we repeat every field every time. If you want to display same fields
               across views, you can use for instance `post.editView().fields()` to get edition fields. */
            post.showView()
                .fields([
                    nga.field('id', 'number'),
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
                            nga.field('created_at', 'date'),

                    ]),
                ]);

            return post;
        });
    });

    // Same config block for comments
    // Same config block for tags

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
```

## Contributing

Your feedback about the usage of this bundle is valuable: don't hesitate to [open GitHub Issues](https://github.com/marmelab/ng-admin/issues)
for any problem or question you may have.

All contributions are welcome. New applications or options should be tested with the `phpunit` command.

## License

NgAdminGeneratorBundle is licensed under the [MIT Licence](LICENSE), courtesy of [marmelab](http://marmelab.com).
