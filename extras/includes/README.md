# Integration Configuration

[Jump to user_functions]()

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


# user_functions
