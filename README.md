# CourierInvoice
A set of classes for the Courier Invoice API
#Instalation
<p><code>composer --dev require "rjdeliveryomaha/courierinvoice"</code></p>
<p>Or add "rjdeliveryomaha/courierinvoice":"dev-master" to composer.json</p>
#Files
<p><a href="#CommonFunctions">CommonFunctions</a></p>
<p><a href="#Ticket">Ticket</a></a>

#CommonFunctions
<p>Utility class extended by all other classes</p>
<p>Throws exception on error</p>
<p>Usage: <code>$functions = new CommonFunctions($config, $data);</code></p>
<p>$config and $data should both be array</p>
<p>This class expects that a session exists unless the property 'noSession' is set in $data.</p>
<p>
  Public Methods:
  <ul>
    <li>
      <p>$functions->getProperty($property)</p>
      <p>Returns a value if the property exists. False is it does not.</p>
    </li>
    <li>
      <p>$functions->updateProperty($property, $value)</p>
      <p>Sets property to new value. Returns true. False is property does not exist.</p>
    </li>
    <li>
      <p>$functions->addToProperty($property, $value)</p>
      <p>Adds value to property. Returns false if property does not exists or is not numeric.</p>
    </li>
    <li>
      <p>$functions->substractFromProperty($property, $value)</p>
      <p>Subtract value from property. Returns false if property does not exists or is not numeric.</p>
    </li>
    <li>
      <p>$functions->compareProperties($obj1, $obj2, $property, $strict=FALSE)</p>
      <p>Returns false if property does not exist in both objects. $strict compares type as well as value.</p>
    </li>
    <li>
      <p>$functions->debug()</p>
      <p>Pretty print properties and values</p>
    </li>
    <li>
      <p>$functions->getError()</p>
      <p>Return the last error.</p>
    </li>
    <li>
      <p>$functions->outputKey()</p>
      <p>Generates unique session value for validating $_POST data. returns a hidden input.</p>
    </li>
    <li>
      <p>$functions->outputMultiKey()</p>
      <p>Generates unique session value for validating $_POST data. returns the value.</p>
    </li>
  </ul>
</p>
#Ticket
<p>Handles display and update of ticket data</p>
<p>Returns string on error.</p>
<p>Usage: $ticket = new Ticket($config, $data)</p>
<p>$config and $data should both be array.</p>
