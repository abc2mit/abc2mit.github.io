/**
  * Example Class for getting around the problem with
  * overloaded functions constructors.
  *
  * This exapmple are asuming that you want to overload
  * the constructor Object() with different arguments. 
  */
class Object {

  /**
   * The real constuctor for this class.
   *
   * NOTE: Parlameter may not have the value null
   *      unless you changes the default value in
   *      the constructor.
   */
  function Object($param1 = null, $param2 = null) {
   $numargs = func_num_args();
   $arg_list = func_get_args();
   $args = "";
   for ($i = 0; $i < $numargs && $arg_list[$i] != null; $i++) {
     if ($i != 0) {
       $args .= ", ";
     }

     $args .= "\$param" . ($i + 1);
   }

   // Call constructor function
   eval("\$this->constructor" . $i . "(" . $args . ");");
  }

  /**
   * Functiorn that will be called if constructor is called
   * with no parlameter as agument.
   */
  function constructor0() {
   echo("Constructor 1: No parlameter." . "\n");
  }

  /**
   * Functiorn that will be called if constructor is called
   * with one parlameter as agument.
   */
  function constructor1($param) {
   echo("Constructor 2: \$param=" . $param . "\n");
  }

  /**
   * Functiorn that will be called if constructor is called
   * with two parlameter as agument.
   */
  function constructor2($param1, $param2) {
   echo("Constructor 3: \$param1=" . $param1 . "\n");
   echo("Constructor 3: \$param2=" . $param2 . "\n");
  }
}

new Object();
new Object("A String value");
new Object("Another String...", 1);

// Output:
Constructor 1: No parlameter.

Constructor 2: $param=A String value

Constructor 3: $param1=Another String...
Constructor 3: $param2=1