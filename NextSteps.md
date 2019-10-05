# Things to do next

* Enable HSTS on subdomains.
* Use models and the Zend DB table adapters.
* Use the ~~session~~, ~~auth~~ and ACL components.
* Use Zend_Application_Resource_Navigation to define and render the menu:

    ```
    resources.navigation.pages.postcodeQuery.label = "Postcode Query"
    resources.navigation.pages.postcodeQuery.controller = "postcode"
    resources.navigation.pages.groupRoster.label = "Group Roster"
    resources.navigation.pages.groupRoster.controller = "group"
    resources.navigation.pages.submitEvent.label = "Submit Event Proposal"
    resources.navigation.pages.submitEvent.controller = "event"
    resources.view[] =
    ```
