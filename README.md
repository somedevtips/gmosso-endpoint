# Gmosso endpoint

This WordPress plugin creates a new endpoint with slug `gmosso-users` for the root place.
So if the root of your WordPress installation is `https://www.example.com`, 
the url of this endpoint will be `https://www.example.com/gmosso-users`.

## Installation
This plugin does not require any build step to be used. Clone the repository,
upload the files to the `plugins` directory of a WordPress installation and
activate the plugin.

## Requirements
PHP version 7.4.0 or higher.

## Hooks and filters
The file `templates/users.php` is the template used to create the custom endpoint
page. This template defines two hooks and two filters. All are prefixed by 
`gmosso_endpoint_users`.

### Hooks
`gmosso_endpoint_users_before_content` is fired before the content of the page.  
`gmosso_endpoint_users_after_content` is fired after the content of the page.

### Filters
`gmosso_endpoint_users_title` is used to define the h1 page title. Filter 
function arguments: the title of the page, without html tags.  
`gmosso_endpoint_users_content` is used to define the users table content.
Filter function arguments: the html markup for the users table.

## Server side implementation notes

The root namespace is `GmossoEndpoint`. The namespace structure is reflected in 
the file-system structure and a [PSR-4](https://www.php-fig.org/psr/psr-4/) 
composer autoloader is used. Code structure is modular, a module is a first level
sub-namespace of the root namespace.

**For simplicity in the rest of this documentation I will omit the root namespace**.

### Code style
Both the src files and tests files are compliant with the 
[Inpsyde code style](https://github.com/inpsyde/php-coding-standards). At some
specific points in the code some rule is removed by using the `phpcs:disable`
comment. The reason is explained right above the comment.

### Plugin configuration and options
All plugin parameters are defined in the `Configuration` class, that implements a 
read-only `ArrayAccess` interface.  
Plugin options are saved in the `wp_options` table under
`option_name` = `gmosso_endpoint_options`. The file `queries.sql` in the root 
folder contains some useful queries to use for debugging.

### Activation, deactivation, uninstall and upgrade
The plugin manages activation and upgrade tasks in the `Installation` module.
Instead of using the `register_activation_hook` function I used a stored option
that manages both activation and upgrade. The reason is that 
`register_activation_hook` is not called during plugin upgrade. The option name
is `installed` and it is a 0/1 value. It is reset to 0 on deactivation and 
upgrade.

Adding a new endpoint requires flushing the rewrite rules. To optimize performance, 
this is only done once: after activation or upgrade.  
I tested plugin upgrade by using the 
[Github Updater](https://github.com/afragen/github-updater) plugin.

Deactivation: managed with the `register_deactivation_hook` function in 
the main plugin file. It removes the endpoint, flushes the rewrite rules and
resets the `installed` option.

Uninstallation: managed with the `uninstall.php` file and the `Uninstallation`
module. On uninstall all options and transients are deleted.

### Plugin bootstrap
The plugin bootstrap code is implemented in the `Bootstrapper` class. 
This class checks that the running requirements are satisfied, loads all modules
and then bootstraps the modules that have bootstrap code.
Modules that participate in the bootstrap phase implement the 
`BootstrappableInterface` with its `bootstrap` method.

### Endpoint creation
The custom endpoint is added in the `Endpoint` module by using the
`add_rewrite_endpoint` function of the [WordPress 
rewrite API](https://developer.wordpress.org/apis/handbook/rewrite/).

### Data fetching from rest api
The `DataProvider` module implements fetching of data from the remote api. This
module defines an abstract type `AbstractDataProvider` and its concrete
implementation `RestApiDataProvider`. This module also handles the error 
situations, validates the json data by using the `Seld\JsonLint\JsonParser`
library and analyses the response headers to verify if data can be cached.

### Caching
Caching is implemented in the `SimpleCache` module, that is a subset of the 
caching interface defined by [PSR-16](https://www.php-fig.org/psr/psr-16/). 
I have chosen to use WordPress transients
because caching plugins can optimize their storage for faster access. If a caching
plugin is not installed they still are a convenient mechanism because they 
implement the expiration concept. This is well explained in the
[WordPress developer handbook](https://developer.wordpress.org/apis/handbook/transients/).
The content of the transient is the json returned by the rest api call.
The expiration time is set by reading the `cache-control` HTTP header of the server
reply. AJAX calls that read the user details use the stored transients, if available.
When the plugin in uninstalled all transients are deleted.

### Data processing and output
Data incoming from the rest api call are processed and sent to the client by the
`Mvc`and `Users`modules. `Mvc` is the abstract representation of a Mvc pattern,
while `Users` is its concrete implementation for the `gmosso-users` endpoint. 

The entry point is the `Router` class, that routes the incoming calls to the correct 
controller:  

*  `Router->route()` manages the calls to the endpoint url by including the 
endpoint template file from the `templates` root folder.  
*  `Router->routeAjax()` manages the AJAX calls.  

The list of available controllers is injected into the `Router` as an instance 
of the `Controllers` class. 

`AbstractController` is the abstract representation of the controller: it 
is called by the `Router` and uses the model and the view to create the output.  

`AbstractModel` is the abstract representation of the model. It is called by
the controller and uses the DataProvider to get the data. If data can be cached 
it manages caching. Then it returns the data to the controller as `AbstractData`.

`AbstractData` is an abstract representation of the users data. It has three concrete 
implementations: `ErrorData`, `AllUsersData` and `SingleUserData`. Names are 
self-explanatory.

The fetched `AbstractData` (or the error) are returned to the controller, that 
injects them into an `AbstractView` to render them for output. The 
`AbstractController->view()` is a view factory that creates the view, depending on 
the `AbstractData` received.
The view can be an `ErrorView`, an `AllUsersView` or a `SingleUserView`.  
The controller injects the html markup of the users table in the template by 
using the filter `gmosso_endpoint_users_content`.  
For AJAX calls the data are returned to the browser in json format by using the
`wp_send_json*` functions.

### Logging
To activate it, set the parameter DEBUG = 1 (0 in production). The logger is
implemented in the module `Log` and it is a [PSR-3](https://www.php-fig.org/psr/psr-3/)
logger.

## Client side implementation notes
The users table is implemented with `flexbox` and is responsive. Since css code is very 
simple, I used plain css. Loading of details data is managed with a jQuery script 
in a IIFE function. The Javascript code is linted with [JSlint](http://jslint.com/).

The `Assets` module loads and manages css and js assets. Minification of files 
and creation of source maps is done with a `gulp` task. Before
running it, install the required libraries executing `npm install`. The minification
task is the default task, so you will simply have to execute `gulp` at the command
prompt.

## Tests
Tests are implemented with phpunit and Brain Monkey. Before execution run 
`composer install` to download dev dependencies. They produce the output 
in `testdox` format for easier reading and a test coverage report in the plugin 
parent directory. Coverage is roughly 90%.
