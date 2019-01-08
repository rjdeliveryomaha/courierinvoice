# Integration Configuration

### username

String

Your Courier Invoice account number.

### publicKey

String

Public API key. This can be found on the reconfigure page at [courierinvoice.com](https://rjdeliveryomaha.com/courierinvoice.com)

### privateKey

String

Private API key. This can be found on the reconfigure page at [courierinvoice.com](https://rjdeliveryomaha.com/courierinvoice.com)

### session_name

String

Custom session name to use instead of default PHPSESSID.

### domain

String

Domain name to associate the session with.

### lifetime

Integer

Time, in seconds, to keep the session alive.

Default 28800 (8 hours).

### path

String

Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain.

Default '/'.

### secure

Boolean

If TRUE cookie will only be sent over secure connections.

Default ` false `.

### bypassHijackingTest

Boolean

Bypass test of HTTP_USER_AGENT and REMOTE_ADDR.

Default ` false `.

It is not recommended that this value be set to ` true ` however, it may be necessary in situations where a users IP address is frequently changed due to network instability or service provider issues.

### enableLogging

Boolean

Indicates weather or not to log errors.

### targetFile

String

File location for logging. If enableLogging is ` true ` and this value is NULL or an empty string an error will be thrown.

### testMode

Boolean

Indicates if test mode is active

### testURL

String

URL to use in test mode.

If test mode is true and this value is null or empty string an exception will be thrown.

### invoicePage1Max

Integer

The maximum number of line items on the first page of an invoice. If this is equal to the total number of line items on an invoice if will be reduced by 1 to accommodate the invoice foot.

### invoicePageMax

Integer

The maximum number of line items on every page after the first.

### showCanceledTicketsOnInvoiceExceptions

Indexed array

Canceled tickets are not displayed on invoices by default.

This array may contain client ID numbers to exclude from this behavior. The client ID should be preceded with the letter 't' if the client is not a repeat client, ex: ` [ 25, 't1', 15, 't65' ] `.

### consolidateContractTicketsOnInvoiceExceptions

Contract tickets are consolidated for display on invoices by default.

This array may contain client ID numbers to exclude from this behavior. The client ID should be preceded with the letter 't' if the client is not a repeat client, ex: ` [ 25, 't1', 15, 't65' ] `.

### clientNameExceptions

Associative array

Client names that should be changed, for example, to abbreviate.

Ex: ` [ 'some long client name' => 'SLCN'] `

### clientAddressExceptions

Indexed array

Addresses that should be ignored, for example, due to change of address.

### ignoreValues

Indexed array

Values that should not be included on ticket entry datalists. Values should be lower case.


### emailConfig

Associative array

Setting to use with [PHPMailer](https://github.com/PHPMailer/PHPMailer/tree/6.0).

Keys:

  - fromAddress

  - password

  - smtpHost

  - secureType

    This can be either 'ssl' or 'tls'

  - port

    This will vary based upon which 'secureType' is chosen

  - fromName

  - BCCAddress

### allTimeChartLimit

Integer

Maximum number of months to display on a chart.

### chart_height

Float

Overall height of a chart in em.

### bar_width

Float

Width of each bar for a chart in em.

### bar_gap

Float

Gap between adjacent bars in em.

### interval_gap

Float

Gap between groups of bars in em.

### interval_border

Float

Border width in em of the container that holds each group of bars.

### userLogin

String

Login name for Courier Invoice user as an alternative to using client ID 0 (zero).

### driverChargesEntryExclude

Indexed array with 2 indices

By default all charges are included on ticket forms.

This setting removes charges for drivers ticket entry and update form.

Index 0: Indexed array of charges to remove for drivers who can dispatch to self.

Index 1: Indexed array of charges to remove for drivers who can dispatch to all.

### driverChargesQueryExclude

Indexed array with 2 indices.

This setting removes charges for drivers ticket query form.

Index 0: Indexed array of charges to remove for drivers who can dispatch to self.

Index 1: Indexed array of charges to remove for drivers who can dispatch to all.

### dispatchChargesEntryExclude

Indexed array

This setting removes charges for dispatchers ticket entry and update form.

### dispatchChargesQueryExclude

Indexed array

This setting removes charges for dispatchers ticket query form.

### clientChargesEntryExclude

Indexed array with 2 indices.

This setting removes charges for clients ticket entry (request) form.

Index 0: Indexed array of charges to remove for admin clients.

Index 1: Indexed array of charges to remove for daily clients.

### clientChargesQueryExclude

Indexed array with 2 indices

This setting removes charges for client ticket query form.

Index 0: Indexed array of charges to remove for admin clients.

Index 1: Indexed array of charges to remove for daily clients.

### orgChargesQueryExclude

Indexed array

This setting removes charges for organizations ticket query form.

### client0ChargesEntryExclude

Indexed array

This setting removes charges for Courier Invoice user client 0 (zero) ticket entry and update form.

### client0ChargesQueryExclude

Indexed array

This setting removes charges for Courier Invoice user client 0 (zero) ticket query form.

### initialCharge

Integer

By default the Charge property is null when the ticket entry form is initialized.

This setting selects a Charge value when the ticket entry form is initialized.

### extend

Associative array

Extend functionality with custom menu items, pages, and javascript.

The top level keys are who to create the items for; all, client, org, driver, dispatcher, client0, org0.

client0 provides customization options for use when logged in as Courier Invoice user.

org0 provides customization options for use with Courier Invoice use organization that contains all not deleted clients.

Entries are indexed arrays with the following content:

```php

[ 'Menu Item', 'function_name', '../path/to/javascript.js', 'jsAttribute', 'jsAttribute' ]

```

Index 0 will be added, as is, to the menu. It will then have any HTML tags striped, be converted to lowercase, spaces replaced with underscore and used as the id attribute of the page.

Index 1, if set and not null or an empty string, will be looked for first as a method in the Ticket, Route, Invoice, and Client classes then as a function in includes/user_functions.php to populate the page. This function should return HTML content.

Index 2, if set and not null or an empty string, will be added as the src of a script element.

If an entry has a non-null, not empty string at index 0 and a null or empty string at index 1 it will be moved to the end of the list. This is done to preserve the indexing of menu items to pages.

If both index 0 and 1 are null or empty string index 2 will be added as the src of a script element. All scripts are added to the page in the order they are encountered in this configuration.

Any indices beyond 2 will be interpreted as attributes to be applied to the script for example defer or async.

  - __all__

    Indexed array of settings entries as described above.

    These items will be added for all users.

  - __client__

    Indexed array with three indices.

    Each index is an indexed array of settings entries as described above.

    * 0: All clients.

    * 1: Admin clients.

    * 2: Daily user clients.

    These entries are not applied to organization users.

    These entries are not applied to Courier Invoice user client ID 0 (zero).

  - __org__

    Indexed array of settings entries as described above.

    These items will be added for only organization users.

  - __driver__

    Indexed array with three indices.

    Each index is an indexed array of settings entries as described above.

    * 0: All drivers.

    * 1: Drivers that _cannot_ dispatch.

    * 2: Drivers that can dispatch _only to themselves_.

    * 3: Drivers that can dispatch _all_.

  - __dispatch__

    Indexed array of settings entries as described above.

  - __client0__

    Indexed array of settings entries as described above.

  - __org0__

    Indexed array of settings entries as described above.

### invoiceCronIgnoreClients

Indexed array of client IDs to ignore when the cron job is run.

### invoiceCronIgnoreNonRepeat

Indexed array of non-repeat client IDs to ignore when the cron job is run.

### invoiceCronLogSuccess

Boolean indicates if success of the cron job should be logged.

### invoiceCronLogFailure

Boolean indicates if failure of the cron job should be logged.

---

Providing your basic Courier Invoice configuration options is necessary when using the these classes without a session, for example, when offering a public delivery price calculator or creating invoices with a cron job. An example is provided at the end of [api_config.php](https://github.com/rjdeliveryomaha/courierinvoice/blob/master/extras/includes/api_config.php).

---

# user_functions

[Return to top](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/includes#integration_configuration)

This file is where functions should be located that will be called to extend the application.
