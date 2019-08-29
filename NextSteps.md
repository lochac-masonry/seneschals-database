# Things to do next

* ~~Install a style checker.~~
* ~~Upgrade to Zend 2/3. This is more difficult than the other items on this list but probably needs to be done as early as possible to minimise re-work.~~
    * ~~Use namespaces wherever possible. Should include anything that isn't involved in Zend's automatic dispatch (mainly controllers).~~
* Use models and the Zend DB table adapters.
* Use the session, auth and ACL components.
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
