# Lochac Seneschals' Database

## Third-party dependencies

Dependencies are managed using [Composer](https://getcomposer.org), which will need to be installed in order to run the application. Assuming it is installed, run `composer install` (or `php composer.phar install`) to download the dependencies.

## Style checking / linting

The [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) is installed by Composer, configured in `phpcs.xml` and can be run though IDE plugins (such as [phpcs](https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs) for VSCode) or from the command line:

```
# Report issues:
composer cs-check
# Auto-fix where possible:
composer cs-fix
```

## Configuration

Most of the static configuration (i.e. abstracted from code but the same for all environments and not secure/sensitive) is provided in `config/autoload/global.php` or `module/*/config/module.config.php`.
For example, this is where you would register a new controller.

Sensitive or environment-specific configuration is not stored in the repository, and should instead be provided in `config\autoload\local.php`.
A template for this file is provided at `config\autoload\local.php.dist`, which includes a description of all the values you are likely to need to change.

### Google API Credentials

An exception to the above configuration system is the `google-key.json` file, which stores credentials to access the Google Calendar API. This file is not stored in the repository, but should be added to the project root folder.

To set up the necessary credentials:

1. Log into the [Google Cloud Platform console](https://console.developers.google.com) and create a new project (the resources we need are free).
1. From the APIs & Services area, go to the Credentials tab and click "Create credentials > Service account key".
1. Select "New service account", give it a name, select the "Project > Editor" role (more than needed, but the simplest approach), select the "JSON" key type and click "Create".
1. Save the generated key file as `google-key.json`, then edit the file to remove some unnecessary properties and add the Calendar ID of the Google Calendar you want to use (typically your email address, this can be found in the calendar's settings screen):

    ```json
    {
        "calendar_id": "calendar id",
        "private_key": "keep this property as set in the generated file",
        "client_email": "keep this property as set in the generated file"
    }
    ```

1. In the settings screen of the Google Calendar you want to you use, add the `client_email` from above to the sharing config with "Make changes to events" access.

## Deployment

1. Copy the code to the server, for example by using git to check out the master branch.
1. The web server document root should be set to the `public` folder and all requests that do not resolve to a file should be redirected to `public/index.php`.
1. Create (or check if any changes are needed in) `config\autoload\local.php` using `config\autoload\local.php.dist` as a template.
1. Ensure the `google-key.json` file exists in the project root.
1. Install dependencies according to the versions in `composer.lock`, excluding development dependencies and taking extra time to optimise the autoloader for runtime performance:

    ```
    > composer install --no-dev -a
    ```

1. Check that all platform requirements (e.g. PHP version) stated by the application or any of the dependencies are met:

    ```
    > composer check-platform-reqs
    ```

1. Perform any necessary database migrations. These are scripted in the `sql` folder. For example:

    ```
    > mysql -u seneschal -D seneschal -p < sql/rollforward_1.1.0_1.2.0.sql
    ```
