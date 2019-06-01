# Lochac Seneschals' Database

## Third-party dependencies

Dependencies are managed using (https://getcomposer.org)[Composer], which will need to be installed in order to run the application. Assuming it is installed, run `composer install` (or `php composer.phar install`) to download the dependencies.

## Deployment

1. Copy the code to the server, for example by using git to check out the master branch.
1. Set the APPLICATION_ENV environment variable in the `.htaccess` file appropriately. The supported values are `staging` (default) and `production`.
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
