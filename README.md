NgAdminGeneratorBundle [![Build Status](https://magnum.travis-ci.com/marmelab/NgAdminGeneratorBundle.svg?token=9pHQqqGqKzq39Gzb6stP&branch=master)](https://magnum.travis-ci.com/marmelab/NgAdminGeneratorBundle)
======================

You're a fan of [StanLemonRestBundle](https://github.com/stanlemon/rest-bundle) because it makes REST APIs based on Doctrine entities a piece of cake?
You starred [ng-admin](https://github.com/marmelab/ng-admin) because you love the idea of a JavaScript-powered administration panel consuming a REST API?
Then, you will love NgAdminGeneratorBundle, the Symfony2 bundle that  bootstraps ng-admin based on a Doctrine-powered REST API!

## Installation

Using this bundle in your own project is pretty straightforward, thanks to composer:

`composer require marmelab/NgAdminGeneratorBundle`

Then, register it to your `AppKernel.php` file. The NgAdminGeneratorBundle should only be used in development:

``` php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        // ...
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \marmelab\NgAdminGeneratorBundle\marmelabNgAdminGeneratorBundle();
        }
        // ...
    }
}
```

No more configuration, you are now ready to go!

## Generating your ng-admin configuration

This bundle just adds the `ng-admin:configuration:generate` command to your application. By default, it outputs a JavaScript configuration based on [the REST API defined by StanLemonRestBundle](https://github.com/stanlemon/rest-bundle/blob/master/Resources/doc/index.md#adding-support-for-your-doctrine-entities) into STDOUT. You are free to redirect STDOUT into the file of your choice:

```
./app/console ng-admin:configuration:generate > public/js/ng-admin-config.js
```

Tip: Thanks to the Symfony2 Console component, you can truncate parts of the command name and call the `ng-admin:c:g` command!

## Configuration sample

Here is a sample of an auto-generated configuration, based on the [stanlemon/rest-demo-app](https://github.com/stanlemon/rest-demo-app)
demo application. This application sets up the same entities as the official [ng-admin demo app](http://ng-admin.marmelab.com/), i.e. Posts, Comments, and Tags. The generator simply uses [entity mapping](https://github.com/stanlemon/rest-demo-app/tree/master/src/Lemon/RestDemoBundle/Entity) to better know
which fields to use.

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

## Known (current) limitations

This is an early release of our bundle. There are currently some known limitations, especially regarding relationships.

### One-to-many relationships

Your API should return only the id of referenced entity, not an object. For instance, an API like the following would
produce an error:

``` json
{
    "first_name": "John",
    "last_name": "Doe",
    "address": {
        "id": 1,
        "address": "88 Colin P Kelly Jr Street",
        "city": "San Francisco"
    }
}
```

You should instead return only an `address_id` field:

``` json
{
    "first_name": "John",
    "last_name": "Doe",
    "address_id": 1
}
```

This is easily done using the following `Serializer` annotations:

``` php
/**
 * @ORM\Column(name="address_id", type="integer", nullable=false)
 */
protected $address_id;

/**
 * @ORM\ManyToOne(targetEntity="Acme\FooBundle\Entity\Address")
 * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
 * @Serializer\Exclude()
 **/
private $address;
```

This issue would be solved in the next weeks using [Restangular entity transformers]
(https://github.com/marmelab/ng-admin/blob/master/doc/API-mapping.md#entry-format) in order to match automatically the
id field of given object.

### Many-to-many relationships

Many-to-many support is currently not well supported. We are prioritizing our efforts to make it fully functional out
of the box.

## Contributing

Your feedback about the usage of this bundle is valuable: don't hesitate to [open GitHub Issues](https://github.com/marmelab/ng-admin/issues)
for any problem or question you may have.

All contributions are welcome. New applications or options should be tested with the `phpunit` command.

## License

NgAdminGeneratorBundle is licensed under the [MIT Licence](LICENSE), courtesy of [marmelab](http://marmelab.com).