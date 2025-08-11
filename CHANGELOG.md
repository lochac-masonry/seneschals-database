<!-- markdownlint-configure-file { "no-duplicate-heading": { "siblings_only": true } } -->
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/). Version numbers are roughly based on
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.21.2] - 2025-08-11

### Changed

- Threshold for NZ event income requiring insurer notification increased from $5,000 to $10,000.

## [2.21.1] - 2025-07-13

### Fixed

- Improved validation of file uploads (event attachments) to avoid errors during cleanup after bot requests.

## [2.21.0] - 2024-11-14

### Added

- Checkbox on event form for NZ events that triggers insurance notification to NZ secretary if the event involves
  animals.

## [2.20.3] - 2024-06-03

### Changed

- Updated title of "Submit Event Proposal" page to "Submit Lochac Event for Approval" per request from Kingdom
  Seneschal.

## [2.20.2] - 2024-01-18

### Fixed

- Google Calendar integration re-enabled with new version of Google API Client.
- Undefined variable reference in single sign-on logic (correctly) no longer allows admin logic via SSO.

### Changed

- QuahogClient mode changed to PHP_NORMAL_READ to address deprecation warning.

## [2.20.1] - 2024-01-18

### Changed

- Google Calendar integration temporarily disabled due to to PHP 8 incompatibility.

## [2.20.0] - 2024-01-15

### Added

- Added support for viewing and editing groups with a type of "Hamlet" or "Corporate", or a status of "other".

### Changed

- Updated Composer dependencies other than the Google API Client - that appears to require a different credential
  format, and I don't currently have access to the Google account that owns the relevant developer console project.

## [2.19.0] - 2024-01-13

### Added

- Dev environment setup instructions (Windows only) have been added to the README.

### Changed

- Composer dependencies have been updated for compatibility with PHP 8.
- Use of `floor` on a string to convert to an integer has been replaced with `(int)` casting to resolve a PHP 8 error.

## [2.18.0] - 2023-12-31

### Added

- Victoria weapons legislation note to event notifications.

## [2.17.0] - 2023-09-15

### Added

- Website field to event form.
- EditorConfig file for cross-IDE compatibility, and Prettier config to support Markdown auto-formatting.

### Changed

- Setup time field on event form has been renamed and relabelled as timetable to collect more information and match the
  Announce email template.

## [2.16.3] - 2023-07-24

### Changed

- Run the session keepalive request on a timer instead of on change to keep the session active even for an absent user.

## [2.16.2] - 2023-07-23

### Added

- PHP config file has been added to repo (was already present on server).

### Changed

- CSRF timeout for all forms has been raised to 90 minutes.

## [2.16.1] - 2023-06-13

### Changed

- The navigation link for the Review Event Proposals (previously Event List) page has been updated to align with the
  title and function of the page, and the page has been added to the functionality summary on the homepage.
- Composer dependencies have been updated.

### Fixed

- HTML entity encoding was being incorrectly applied to parts of the subject line of the Announce event notification,
  and has been removed.

## [2.16.0] - 2023-03-03

### Added

- Single Sign-On support allowing direct access from the Registry/Regnumator application.

## [2.15.1] - 2023-02-26

### Changed

- Database migrated to UTF-8 (collation `utf8mb4_0900_ai_ci`) using PHPMyAdmin.
- Existing database records with latin1 / UTF-8 encoding errors have been fixed.
- Email subject and body are quoted-printable encoded to allow the use of UTF-8.

## [2.15.0] - 2023-02-25

### Added

- `scagroup.emailDomain` column for use in constructing officer email addresses.

### Changed

- Seneschal details on all pages now taken from the `warrants` (Regnumator) table.
- Tweaked format of Announce event notice subject - date before event name.
- Email aliases that match the standard officer email addresses must be edited through the Registry / Regnumator.

### Removed

- Several columns from the `scagroup` table have been removed as they have been superseded by Regnumator/Registry data:
  `scaname`, `realname`, `address`, `postcode`, `phone`, `email`, `warrantstart`, `warrantend`, `memnum`, `usevirtuser`.

## [2.14.0] - 2023-02-09

### Changed

- The event notification email sent to Announce has been updated and converted to HTML.

## [2.13.0] - 2023-01-30

### Added

- Checkbox on event form for NZ events that triggers insurance notification NZ secretary.

## [2.12.0] - 2022-01-16

### Removed

- Postcode list and group list (CSV) pages, as these are no longer used by the Registry.
- Group roster page, as this information is now available on the main Lochac site.

## [2.11.0] - 2021-10-05

### Removed

- Baron/Baroness management, as this has been taken over by the Regnum application.

## [2.10.1] - 2021-09-12

### Changed

- Updated sample reports to reflect modified questions.
- Expanded Statistics textarea in report to avoid need for scrolling.

## [2.10.0] - 2021-09-02

### Added

- Link to ABS postcode map from postcode query page.

### Changed

- Update all Composer dependencies.
- Updated wording of Attachments section in Event form - no longer optional in Australia.
- Updated wording of Report form to remind users of potentially sensitive questions.

### Removed

- Date input guidance from Event form, as all relevant browsers now display a date picker.

## [2.9.3] - 2021-07-06

### Changed

- Appended Covid stay-home note to event notice emails.
- Changed `From` address for event notice emails to appear as "Lochac Event Notice".

## [2.9.2] - 2021-04-18

### Changed

- Update all Composer dependencies.
- Tweaked sample statistics response in report form.

## [2.9.1] - 2020-12-30

### Fixed

- Links in notification emails now correctly point to the application homepage instead of the current page.

## [2.9.0] - 2020-10-21

### Added

- Hint for changing email alias in report form, based on selected country.
- Comprehensive list of report recipients in the form of disabled checkboxes, excluding Lochac Seneschal as parent group
  recipient.
- Pre-populated member count in report from view into registry database.
- "Drop-dead Deputy" report field.
- Static "Hamlets" question in subgroups section of report, except for Cantons and Colleges.
- Sample reports for different group types, with link from report based on type of group.
- Copyable report template as plain text (including generic JS to make an element copyable).
- Keep-alive functionality for the report form. A background XHR runs on form changes to check if the session is still
  active and warns the user if not.

### Changed

- Make group website not editable as part of report submission.
- Lists, Youth and Historian report fields combined into "Other officers".
- Minor changes to wording of report form labels.
- Report emails are sent From the Reporting Deputy, with Sender set to the normal app From address.

### Removed

- Reset button removed from report form.

## [2.8.1] - 2020-10-09

### Fixed

- Users now correctly receive a copy of their own reports (introduced in 2.8.0).

### Changed

- Updated composer dependencies.

## [2.8.0] - 2020-09-27

### Added

- GroupSelect and ListFilter forms now auto-submit on change, if JavaScript is available.

### Changed

- Extended session timeout and report form CSRF timeout to 90 minutes.
- Session is regenerated upon login and destroyed upon logout to mitigate session fixation attacks.
- Make Seneschal email address not editable as part of report submission.

## [2.7.1] - 2020-08-21

### Changed

- Updated footer contact details to refer to the Kingdom Seneschal.

## [2.7.0] - 2020-08-14

### Added

- New user for the Reporting Deputy with the same access as the Lochac Seneschal.
- All report emails are sent to the Reporting Deputy, in addition to previous recipients.

## [2.6.0] - 2020-05-25

### Changed

- All emails are now sent with a From address of `seneschaldb@lochac.sca.org`.

## [2.5.0] - 2020-04-09

### Changed

- Migrated from Zend to Laminas.
- Enabled the `strict_types` directive in all PHP files, enforced by PHP_CodeSniffer.

## [2.4.1] - 2020-03-15

### Added

- `robots.txt` that had been added directly to the web server.

## [2.4.0] - 2020-03-08

### Added

- Git pre-commit hook to run PHP_CodeSniffer, managed from the Composer config.

### Changed

- Updated composer dependencies.
- Increased CSRF timeout on Report form from 30 minutes to 60 minutes, as this form can take a long time to complete.

## [2.3.0] - 2019-12-07

### Added

- Attachments can be added to events by the submitter or a reviewer, and downloaded or deleted by a reviewer.
- Event attachments are scanned by ClamAV on upload, and downloads are streamlined through mod_xsendfile.

### Changed

- Navigation menu is now rendered using Zend-Navigation and Zend-Permissions-Acl.
- All authentication/authorisation logic moved to new User module.
- Non-module-specific config moved to `config/autoload/global.php`.
- Moved all routing to named, literal routes.
- All BaseController behaviour moved to controller plugins.
- BaseController renamed to DatabaseController as its only purpose is to have `$this->db` injected by the factory.
- All database tables have been migrated from the old MyISAM engine to InnoDB.

## [2.2.2] - 2019-10-08

### Fixed

- Workaround for undefined variable notice in the layout when rendering error pages.

## [2.2.1] - 2019-10-08

### Changed

- The username field in the login form is now forced to lowercase to prevent case-sensitivity login issues.

### Fixed

- Reinstated `servers` login which was missed in the authentication rewrite.

## [2.2.0] - 2019-10-05

### Added

- CSRF protection has been added to all forms that mutate data.
- Several security-focused HTTP headers have been added:
  - Content-Security-Policy
  - Feature-Policy
  - Referrer-Policy
  - X-Content-Type-Options
  - X-Frame-Options
- When a form is returned to the user with validation errors, focus and scroll to the first error.

### Changed

- Refactored \Application\ErrorListener to inject true dependencies instead of a service locator.
- Moved to session-backed authentication using Zend-Session and Zend-Authentication.
- Removed all inline styles and scripts (moved to separate files).
- HTTP requests now redirect to HTTPS by default.

### Fixed

- Added missing length validation to event names.

### Removed

- Removed Zend Framework version from Tools/Version page - Zend no longer has a single centralised version number.
- Removed `.htaccess` file - all of the settings there have been moved to the VHost config for performance reasons.

## [2.1.0] - 2019-08-29

### Changed

- Migrated to Zend 2.5.
- Migrated to Zend 3.1.
- Removed all sensitive config files/values from the repository - these should be set in `config/autoload/local.php`.
- Slight tweaks to style rules - enforce short array syntax and no space after the `!` operator.

## [2.0.0] - 2019-08-11

### Added

- README, with development and deployment instructions.
- Next Steps document, with notes on planned changes.
- Changelog, with placeholders for old releases.
- Composer integration to manage dependencies.
- Style checks using PHP_CodeSniffer.
- Historian section to officer report form.

### Changed

- Made use of short echo tags throughout `.phtml` view files.
- Moved index.php as close to standard Zend Framework setup as possible.
- Introduced namespaces wherever possible (essentially everything except controllers).
- Made use of Composer autoload config instead of custom autoload function.

### Fixed

- Line length and whitespace made consistent with PSR-12 standard.

## [1.6.0] - 2019-05-23

## [1.5.0] - 2018-08-20

## [1.4.0] - 2018-01-12

## [1.3.0] - 2018-01-06

## [1.2.0] - 2017-06-27

## [1.1.1] - 2016-09-06

## [1.1.0] - 2015-09-07

## [1.0.1] - 2015-06-15

## [1.0.0] - 2015-06-14

[unreleased]: https://github.com/lochac-masonry/seneschals-database/compare/v2.21.2...main
[2.21.2]: https://github.com/lochac-masonry/seneschals-database/compare/v2.21.1...v2.21.2
[2.21.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.21.0...v2.21.1
[2.21.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.20.3...v2.21.0
[2.20.3]: https://github.com/lochac-masonry/seneschals-database/compare/v2.20.2...v2.20.3
[2.20.2]: https://github.com/lochac-masonry/seneschals-database/compare/v2.20.1...v2.20.2
[2.20.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.20.0...v2.20.1
[2.20.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.19.0...v2.20.0
[2.19.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.18.0...v2.19.0
[2.18.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.17.0...v2.18.0
[2.17.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.16.3...v2.17.0
[2.16.3]: https://github.com/lochac-masonry/seneschals-database/compare/v2.16.2...v2.16.3
[2.16.2]: https://github.com/lochac-masonry/seneschals-database/compare/v2.16.1...v2.16.2
[2.16.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.16.0...v2.16.1
[2.16.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.15.1...v2.16.0
[2.15.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.15.0...v2.15.1
[2.15.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.14.0...v2.15.0
[2.14.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.13.0...v2.14.0
[2.13.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.12.0...v2.13.0
[2.12.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.11.0...v2.12.0
[2.11.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.10.1...v2.11.0
[2.10.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.10.0...v2.10.1
[2.10.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.9.3...v2.10.0
[2.9.3]: https://github.com/lochac-masonry/seneschals-database/compare/v2.9.2...v2.9.3
[2.9.2]: https://github.com/lochac-masonry/seneschals-database/compare/v2.9.1...v2.9.2
[2.9.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.9.0...v2.9.1
[2.9.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.8.1...v2.9.0
[2.8.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.8.0...v2.8.1
[2.8.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.7.1...v2.8.0
[2.7.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.7.0...v2.7.1
[2.7.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.6.0...v2.7.0
[2.6.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.5.0...v2.6.0
[2.5.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.4.1...v2.5.0
[2.4.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.4.0...v2.4.1
[2.4.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.3.0...v2.4.0
[2.3.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.2.2...v2.3.0
[2.2.2]: https://github.com/lochac-masonry/seneschals-database/compare/v2.2.1...v2.2.2
[2.2.1]: https://github.com/lochac-masonry/seneschals-database/compare/v2.2.0...v2.2.1
[2.2.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/lochac-masonry/seneschals-database/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/lochac-masonry/seneschals-database/compare/v1.6.0...v2.0.0
[1.6.0]: https://github.com/lochac-masonry/seneschals-database/compare/v1.5.0...v1.6.0
[1.5.0]: https://github.com/lochac-masonry/seneschals-database/compare/v1.4.0...v1.5.0
[1.4.0]: https://github.com/lochac-masonry/seneschals-database/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/lochac-masonry/seneschals-database/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/lochac-masonry/seneschals-database/compare/v1.1.1...v1.2.0
[1.1.1]: https://github.com/lochac-masonry/seneschals-database/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/lochac-masonry/seneschals-database/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/lochac-masonry/seneschals-database/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/lochac-masonry/seneschals-database/tree/v1.0.0
