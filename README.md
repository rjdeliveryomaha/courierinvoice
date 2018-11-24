# CourierInvoice
A set of classes for the Courier Invoice API
#Files
<p><a href="#CommonFunctions">CommonFunctions</a></p>

#CommonFunctions
<p>Utility function used by all other classes</p>
<p>Throws exception on error</p>
<p>Usage: new CommonFunctions($config, $data);</p>
<p>
Public Methods:
<ul>
  <li>
    <p>getProperty($property)</p>
    <p>Returns a value if the property exists. False is it does not.</p>
  </li>
  <li>
    <p>updateProperty($property, $value)</p>
    <p>Sets property to new value. Returns true. False is property does not exist.</p>
  </li>
  <li>
    <p>addToProperty($property, $value)</p>
    <p>Adds value to property. Returns false if property does not exists or is not numeric.</p>
  </li>
  <li>
    <p>substractFromProperty($property, $value)</p>
    <p>Subtract value from property. Returns false if property does not exists or is not numeric.</p>
  </li>
  <li>
    <p>compareProperties($obj1, $obj2, $property, $strict=FALSE)</p>
    <p>Returns false if property does not exist in both objects. $strict compares type as well as value.</p>
  </li>
</ul>
</p>
