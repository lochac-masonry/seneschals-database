# Things to do next

* Use models and the Zend DB table adapters.
* Use the ACL component to redirect unauthorised users.
* Restore form data when session expiry causes redirect via login screen.

Add emailDomain column.

Remove columns:

* scaname (used in EventController)
* realname (unused)
* address (unused)
* postcode (unused)
* phone (unused)
* email (used in EventController, GroupController, ReportController)
* warrantstart (unused)
* warrantend (unused)
* memnum (unused)
* usevirtuser (unused)
