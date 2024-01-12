# Lochac Seneschals' Database

## Third-party dependencies

Dependencies are managed using [Composer](https://getcomposer.org), which will need to be installed in order to run the
application. Assuming it is installed, run `composer install` (or `php composer.phar install`) to download the
dependencies.

## Style checking / linting

The [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) is installed by Composer, configured in `phpcs.xml`,
run automatically before each commit to the Git repository and can be run though IDE plugins (such as
[phpcs](https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs) for VSCode) or from the command line:

```bash
# Report issues:
composer cs-check
# Auto-fix where possible:
composer cs-fix
```

## Development Mode

Several development and debugging utilities are included in the dev dependencies, installed by running
`composer install`. These include a toolbar that appears at the bottom of the app and exposes timing, memory, database
and session information. Composer scripts are provided to toggle these:

```bash
# Check status of development mode:
composer development-status
# Enable the toolbar and profiling utilities:
composer development-enable
# Disable development mode:
composer development-disable
```

## Configuration

Most of the static configuration (i.e. abstracted from code but the same for all environments and not secure/sensitive)
is provided in `config/autoload/global.php` or `module/*/config/module.config.php`. For example, this is where you would
register a new controller.

Sensitive or environment-specific configuration is not stored in the repository, and should instead be provided in
`config\autoload\local.php`. A template for this file is provided at `config\autoload\local.php.dist`, which includes a
description of all the values you are likely to need to change.

### Google API Credentials

An exception to the above configuration system is the `google-key.json` file, which stores credentials to access the
Google Calendar API. This file is not stored in the repository, but should be added to the project root folder.

To set up the necessary credentials:

1. Log into the [Google Cloud Platform console](https://console.developers.google.com) and create a new project (the
   resources we need are free).
1. From the APIs & Services area, go to the Credentials tab and click "Create credentials > Service account key".
1. Select "New service account", give it a name, select the "Project > Editor" role (more than needed, but the simplest
   approach), select the "JSON" key type and click "Create".
1. Save the generated key file as `google-key.json`, then edit the file to remove some unnecessary properties and add
   the Calendar ID of the Google Calendar you want to use (typically your email address, this can be found in the
   calendar's settings screen):

   ```json
   {
     "calendar_id": "calendar id",
     "private_key": "keep this property as set in the generated file",
     "client_email": "keep this property as set in the generated file"
   }
   ```

1. In the settings screen of the Google Calendar you want to you use, add the `client_email` from above to the sharing
   config with "Make changes to events" access.

## Single Sign-On

Users of the Registry/Regnumator application can access the Seneschals' DB application via single sign-on that is based
on the [OpenID Connect Implicit flow](https://openid.net/specs/openid-connect-core-1_0.html#ImplicitFlowAuth), starting
from the authentication response (similar to IdP-initiated SAML). This does not allow use of the `state` parameter and
is vulnerable to CSRF and replay attacks. CSRF (e.g. an attacker's token is used in place of the user's) is mitigated by
CSRF protection on subsequent operations and the display of the affected group throughout the UI. Replay attacks are
mitigated through a short expiry time on the ID tokens.

The Registry application should authenticate users and determine if they are the seneschal of a particular group, and if
so redirect them to the `/auth/single-sign-on` endpoint of the Seneschals' DB with the following query parameters:

- `id_token` REQUIRED - A JWT identifying the Seneschals' DB username to login as (i.e. the group name in lowercase with
  spaces removed). The JWT must be signed with a symmetric key (algorithm and key configured in
  `config\autoload\local.php`) and must contain `iat`/`exp` (validated against current time), `iss`/`aud` (validated
  against values configured in `config\autoload\local.php`) and `sub` (used as the username) claims. Any other claims
  are ignored.
- `redirectUrl` OPTIONAL - An absolute-path reference URL (e.g. `/report`) to which the user should be redirected upon
  successful login. If not provided, the user will be redirected to the app home page.

A useful JWT debugger is available at <https://jwt.io/>.

The JWT validation is implemented using [firebase/php-jwt](https://packagist.org/packages/firebase/php-jwt) which should
also be suitable for generating the tokens.

## Deployment

1. Copy the code to the server, for example by using git to check out the master branch.
1. The web server document root should be set to the `public` folder and all requests that do not resolve to a file
   should be redirected to `public/index.php`.
1. Ensure mod_xsendfile is installed and enabled, setting Apache config directives `XSendFile` to `On` and
   `XSendFilePath` to the absolute path of the `data/files` folder.
1. ClamAV should be installed with `clamd` running as a service. If using AppArmor, add the absolute path of the
   `data/files` folder to the clamd AppArmor profile (i.e. add a line like `/app/data/files/** r,` to
   `/etc/apparmor.d/local/usr.sbin.clamd`).
1. Create (or check if any changes are needed in) `config\autoload\local.php` using `config\autoload\local.php.dist` as
   a template.
1. Ensure the `google-key.json` file exists in the project root.
1. Install dependencies according to the versions in `composer.lock`, excluding development dependencies and taking
   extra time to optimise the autoloader for runtime performance:

   ```bash
   > composer install --no-dev -a
   ```

1. Check that all platform requirements (e.g. PHP version) stated by the application or any of the dependencies are met:

   ```bash
   > composer check-platform-reqs
   ```

1. Perform any necessary database migrations. These are scripted in the `sql` folder. For example:

   ```bash
   > mysql -u seneschal -D seneschal -p < sql/rollforward_1.1.0_1.2.0.sql
   ```

## Development Environment Setup - Windows

### PHP

1. Go to <https://windows.php.net/download/>, find the appropriate version and download the "x64 Non Thread Safe"
   release as a zip file, e.g. `php-8.1.27-nts-Win32-vs16-x64.zip`.
1. Extract the zip to a suitable installation folder, e.g. `C:\Program Files\PHP8.1.27`.
1. Within that folder, copy the example `php.ini-development` to `php.ini` and make the following changes:

   - Uncomment `extension_dir = "ext"`
   - Uncomment `extension=curl`
   - Uncomment `extension=intl`
   - Uncomment `extension=mbstring`
   - Uncomment `extension=openssl`
   - Uncomment `extension=pdo_sqlite`
   - Uncomment `extension=sockets`
   - Set `date.timezone = UTC`

1. Add the installation folder to the `PATH` environment variable.

### Composer

Run the [Composer installer](https://getcomposer.org/doc/00-intro.md#installation-windows).

### Xdebug

Follow the instructions given by the [installation wizard](https://xdebug.org/wizard).

This should include downloading `php_xdebug.dll` to the `ext` folder inside the PHP installation and making the
following changes to `php.ini`:

- Add `zend_extension = xdebug`
- Add `xdebug.mode = debug,develop`
