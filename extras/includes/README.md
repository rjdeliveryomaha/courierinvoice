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

### alternateHijackingTest

Boolean

Bypass test of HTTP_USER_AGENT and REMOTE_ADDR.

Default ` false `.

It is not recommended that this value be set to ` true ` however, it may be necessary in situations where a users IP address is frequently changed due to network instability or service provider issues.

Making this setting ` true ` will instead test for a POST variable named 'formKey' and compare it to a SESSION variable of the same name. While effective this method is less than desirable because of the timing involved. If requests are made in rapid succession errors will result as the value isn't updated until a call is completed. The default test allows a 10 second window when calls can access the same session.

### enableLogging

Boolean

Indicates weather or not to log errors.

### targetFile

String

File location for logging. If enableLogging is ` true ` and this value is NULL or an empty string an exception will be thrown.

### testMode

Boolean

Indicates if test mode is active

### testURL

String

URL to use in test mode.

If test mode is true and this value is null or empty string an exception will be thrown.

### paperFormat

String

This setting, in conjunction with the ` paperOrientation ` setting, tells [DOMPDF](https://github.com/dompdf/dompdf) what paper format to expect and also sets the maximum number of line items per page. For invoices this maximum will be reduced by one for the page on which the invoice foot is to appear.
  - letter
    + portrait:
      Page 1: 8
      Page 2+: 10
    + landscape
      Page 1: 5
      Page 2+: 7
  - legal
    + portrait
      Page 1: 12
      Page 2+: 14
    + landscape
      Page 1: 5
      Page 2+: 7
  - A4
    + portrait
      Page 1: 9
      Page 2+: 11
    + landscape
      Page 1: 5
      Page 2+: 7

### paperOrientation

String

portrait or landscape

### deliveryTerms

String

Terms and conditions asscosiated with making deliveries. This can contain HTML elements that can then be styled by targeting ` #deliveryTerms _yourElement_ `.

An example is provided in the config file.

### enableChartPDF

Boolean

If true a button will be added to multi invoice and ticket chart queries to display / download the result as a pdf.

It is a known issue that [DOMPDF](https://github.com/dompdf/dompdf) doesn't currently handle vertical alignment properly.

For that reason it is not recommended that this value be set to ` true `.

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

### deliveryVAT

Associative array

By default if the ` ApplyVAT ` setting in your Courier Invoice configuration is checked, the standard VAT rate for client 0 will be applied to all deliveries. This setting is for exceptions to that rule. The default can be overridden by assigning a different VAT type to the key 'default'. Keys should be client ID numbers (preceded by 't' for non-repeat clients), values should be an integer VAT type. Ex:

```php
[
  'default' => 2,
  50 => 6,
  't1000' => 0
]
```

__VATtype values:__
* 0: Not VAT-able
* 1: Standard
* 2: Reduced
* 3: Client Standard
* 4: Client Reduced
* 5: Zero-Rated
* 6: Exempt

If ` ApplyVAT ` is configured as 1 and a VATtype is defined as other than numeric or is greater than 6 the VATtype will be set to 1.

` VATtype ` will define the ticket property of the same name. The ticket property ` VATrate ` will then be pulled from either client 0 or the billed client or set to 0 as appropriate.

### iceVAT

See [deliveryVAT](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/includes#deliveryVAT).

` VATtype ` will define the ticket property ` VATtypeIce `. The ticket property ` VATrateIce ` will then be pulled from either client 0 or the billed client or set to 0 as appropriate.

### ignoreValues

Indexed array

Values that should not be included on ticket entry datalists. Values should be lower case.


### emailConfig

Associative array

Setting to use with [PHPMailer](https://github.com/PHPMailer/PHPMailer/tree/6.0).

Setting this value to an empty array or ` null ` will cancel any attempt to send email notifications.

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

Height of chart body in rem.

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

### displayDryIce

Boolean

Indicates if dry ice should be included on ticket request forms and regenerations.

### dryIceStep

Float

Defines the step attribute of number elements for dry ice weight (diWeight) on ticket entry forms.

### extend

Associative array

Extend layout and functionality with custom css, menu items, pages, and javascript.

With the exception of __css__ the top level keys refer to whom the items are created for; __all__, __client__, __org__, __driver__, __dispatcher__, __client0__, __org0__.

__css__ is an associative array with the keys __client__, __org__, __driver__, and __dispatch__ witch are indexed arrays with each entry being the path to the desired css file.

client0 provides customization options for use when logged in as Courier Invoice user.

org0 provides customization options for use with Courier Invoice user organization that contains all not deleted clients.

Entries are indexed arrays with the following content:

```php

[ 'Menu Item', 'function_name', '../path/to/javascript.js', 'jsAttribute', 'jsAttribute' ]

```

Index 0 will be added, as is, to the menu. It will then have any HTML tags striped, be converted to lowercase, spaces replaced with underscore and used as the id attribute of a ` div.page `.

Index 1, if set and not null or an empty string, will be looked for first as a method in the Ticket, Route, Invoice, and Client classes then as a function in ../../includes/user_functions.php to populate the page. This function should ` return ` __not__ ` echo ` HTML content.

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

    These entries are _not_ applied to organization users.

    These entries are _not_ applied to Courier Invoice user client ID 0 (zero).

  - __org__

    Indexed array of settings entries as described above.

    These items will be added for _only_ organization users.

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

Providing your basic Courier Invoice configuration options is necessary when using these classes without a session, for example, when offering a public delivery price calculator or creating invoices with a cron job. An example is provided at the end of [APIToolConfig.php](https://github.com/rjdeliveryomaha/courierinvoice/blob/master/extras/includes/APIToolsConfig.php).

---

# user_functions

[Return to top](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/includes#integration-configuration)

This file is where functions should be located that will be called to extend the application.
