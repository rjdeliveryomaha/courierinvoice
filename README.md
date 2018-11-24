# CourierInvoice
    A set of classes for the Courier Invoice API
# Instalation
    composer --dev require "rjdeliveryomaha/courierinvoice"
  Or add "rjdeliveryomaha/courierinvoice":"dev-master" to composer.json
# Files
  CommonFunctions
  Ticket

# CommonFunctions
  Utility class extended by all other classes
  Throws exception on error
  Usage: <code>$functions = new CommonFunctions($config, $data);</code>
  $config and $data should both be array
  This class expects that a session exists unless the property 'noSession' is set in $data.
  Public Methods:
  - $functions->getProperty($property)
Returns a value if the property exists. False is it does not.
  $functions->updateProperty($property, $value)
Sets property to new value. Returns true. False is property does not exist.
  $functions->addToProperty($property, $value)
Adds value to property. Returns false if property does not exists or is not numeric.
  $functions->substractFromProperty($property, $value)
Subtract value from property. Returns false if property does not exists or is not numeric.
  $functions->compareProperties($obj1, $obj2, $property, $strict=FALSE)
Returns false if property does not exist in both objects. $strict compares type as well as value.
  $functions->debug()
Pretty print properties and values
  $functions->getError()
Return the last error.
  $functions->outputKey()
Generates unique session value for validating POST data. returns a hidden input.
  $functions->outputMultiKey()
Generates unique session value for validating POST data. returns the value.

# Ticket
  Handles display and update of ticket data
  Returns string on error.
  Usage: $ticket = new Ticket($config, $data)
  $config and $data should both be array.
