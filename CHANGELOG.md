# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Version numbers are roughly based on [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.7.1] - 2020-08-21

### Changed

* Updated footer contact details to refer to the Kingdom Seneschal.

## [2.7.0] - 2020-08-14

### Added

* New user for the Reporting Deputy with the same access as the Lochac Seneschal.
* All report emails are sent to the Reporting Deputy, in addition to previous recipients.

## [2.6.0] - 2020-05-25

### Changed

* All emails are now sent with a From address of `seneschaldb@lochac.sca.org`.

## [2.5.0] - 2020-04-09

### Changed

* Migrated from Zend to Laminas.
* Enabled the `strict_types` directive in all PHP files, enforced by PHP_CodeSniffer.

## [2.4.1] - 2020-03-15

### Added

* `robots.txt` that had been added directly to the web server.

## [2.4.0] - 2020-03-08

### Added

* Git pre-commit hook to run PHP_CodeSniffer, managed from the Composer config.

### Changed

* Updated composer dependencies.
* Increased CSRF timeout on Report form from 30 minutes to 60 minutes, as this form can take a long time to complete.

## [2.3.0] - 2019-12-07

### Added

* Attachments can be added to events by the submitter or a reviewer, and downloaded or deleted by a reviewer.
* Event attachments are scanned by ClamAV on upload, and downloads are streamlined through mod_xsendfile.

### Changed

* Navigation menu is now rendered using Zend-Navigation and Zend-Permissions-Acl.
* All authentication/authorisation logic moved to new User module.
* Non-module-specific config moved to `config/autoload/global.php`.
* Moved all routing to named, literal routes.
* All BaseController behaviour moved to controller plugins.
* BaseController renamed to DatabaseController as its only purpose is to have `$this->db` injected by the factory.
* All database tables have been migrated from the old MyISAM engine to InnoDB.

## [2.2.2] - 2019-10-08

### Fixed

* Workaround for undefined variable notice in the layout when rendering error pages.

## [2.2.1] - 2019-10-08

### Changed

* The username field in the login form is now forced to lowercase to prevent case-sensitivity login issues.

### Fixed

* Reinstated `servers` login which was missed in the authentication rewrite.

## [2.2.0] - 2019-10-05

### Added

* CSRF protection has been added to all forms that mutate data.
* Several security-focused HTTP headers have been added:
  * Content-Security-Policy
  * Feature-Policy
  * Referrer-Policy
  * X-Content-Type-Options
  * X-Frame-Options
* When a form is returned to the user with validation errors, focus and scroll to the first error.

### Changed

* Refactored \Application\ErrorListener to inject true dependencies instead of a service locator.
* Moved to session-backed authentication using Zend-Session and Zend-Authentication.
* Removed all inline styles and scripts (moved to separate files).
* HTTP requests now redirect to HTTPS by default.

### Fixed

* Added missing length validation to event names.

### Removed

* Removed Zend Framework version from Tools/Version page - Zend no longer has a single centralised version number.
* Removed `.htaccess` file - all of the settings there have been moved to the VHost config for performance reasons.

## [2.1.0] - 2019-08-29

### Changed

* Migrated to Zend 2.5.
* Migrated to Zend 3.1.
* Removed all sensitive config files/values from the repository - these should be set in `config/autoload/local.php`.
* Slight tweaks to style rules - enforce short array syntax and no space after the `!` operator.

## [2.0.0] - 2019-08-11

### Added

* README, with development and deployment instructions.
* Next Steps document, with notes on planned changes.
* Changelog, with placeholders for old releases.
* Composer integration to manage dependencies.
* Style checks using PHP_CodeSniffer.
* Historian section to officer report form.

### Changed

* Made use of short echo tags throughout `.phtml` view files.
* Moved index.php as close to standard Zend Framework setup as possible.
* Introduced namespaces wherever possible (essentially everything except controllers).
* Made use of Composer autoload config instead of custom autoload function.

### Fixed

* Line length and whitespace made consistent with PSR-12 standard.

## [1.6.0] - 2019-05-23

## [1.5.0] - 2018-08-20

## [1.4.0] - 2018-01-12

## [1.3.0] - 2018-01-06

## [1.2.0] - 2017-06-27

## [1.1.1] - 2016-09-06

## [1.1.0] - 2015-09-07

## [1.0.1] - 2015-06-15

## [1.0.0] - 2015-06-14

[unreleased]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/develop..v2.7.1
[2.7.1]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.7.1..v2.7.0
[2.7.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.7.0..v2.6.0
[2.6.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.6.0..v2.5.0
[2.5.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.5.0..v2.4.1
[2.4.1]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.4.1..v2.4.0
[2.4.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.4.0..v2.3.0
[2.3.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.3.0..v2.2.2
[2.2.2]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.2.2..v2.2.1
[2.2.1]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.2.1..v2.2.0
[2.2.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.2.0..v2.1.0
[2.1.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.1.0..v2.0.0
[2.0.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v2.0.0..v1.6.0
[1.6.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.6.0..v1.5.0
[1.5.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.5.0..v1.4.0
[1.4.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.4.0..v1.3.0
[1.3.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.3.0..v1.2.0
[1.2.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.2.0..v1.1.1
[1.1.1]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.1.1..v1.1.0
[1.1.0]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.1.0..v1.0.1
[1.0.1]: https://bitbucket.org/dtkerr/lochac-sendb/branches/compare/v1.0.1..v1.0.0
[1.0.0]: https://bitbucket.org/dtkerr/lochac-sendb/src/v1.0.0/
