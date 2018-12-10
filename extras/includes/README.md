# Integration Configuration

[Jump to user_functions](https://github.com/rjdeliveryomaha/courierinvoice/tree/master/extras/includes#user_functions)

### username

String

Your Courier Invoice account number.

### publicKey

String

Public API key. This can be found on the reconfigure page at [courierinvoice.com](https://rjdeliveryomaha.com/courierinvoice.com)

### privateKey

String

Private API key. This can be found on the reconfigure page at [courierinvoice.com](https://rjdeliveryomaha.com/courierinvoice.com)

### enableLogging

Boolean

Indicates weather or not to log errors.

### targetFile

String

File location for logging. If enableLogging is ``` true ``` and this value is NULL or an empty string an error will be thrown.

### showCancelledTicketsOnInvoiceExceptions

Indexed array

Canceled tickets are not displayed on invoices by default.

This array may contain client ID numbers to exclude from this behavior.

### consolidateContractTicketsOnInvoiceExceptions

Contract tickets are consolidated for display on invoices by default.

This array may contain client ID numbers to exclude from this behavior.

### clientNameExceptions

Associative array

Client names that should be changed, for example, to abbreviate.

Ex: ``` [ 'some long client name' => 'SLCN'] ```

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

Maximum number of months to display on a chart. Default is 6.

### userLogin

String

Login name for Courier Invoice user as an alternative to using ClientID 0 (zero).

### driverChargesEntryExclude

Indexed array

By default all charges are included on ticket forms.

This setting removes charges for drivers ticket entry and update form.

Index 0 driver can dispatch to self.

Index 1 driver can dispatch to all.

### driverChargesQueryExclude

Indexed array

This setting removes charges for drivers ticket query form.

Index 0 driver can dispatch to self.

Index 1 driver can dispatch to all.

### dispatchChargesEntryExclude

Indexed array

This setting removes charges for dispatchers ticket entry and update form.

### dispatchChargesQueryExclude

Indexed array

This setting removes charges for dispatchers ticket query form.

### clientChargesEntryExclude

Indexed array

This setting removes charges for clients ticket entry (request) form.

Index 0 admin clients.

Index 1 daily clients.

### clientChargesQueryExclude

Indexed array

This setting removes charges for client ticket query form.

Index 0 admin clients.

Index 1 daily clients.
---
# user_functions
