<?php
/**
 * String OrongoScript Package
 *
 * @author Jaco Ruit
 */
class OrongoScriptString extends OrongoPackage {
    
    public function __construct($runtime) {
        
    }
    public function getFunctions() {
        return array(new FuncConcat(), new FuncAdd());
    }
}



/**
 * Concat OrongoScript function
 *
 * @author Jaco Ruit
 */
class FuncConcat extends OrongoFunction {
    

    public function __invoke($args) {
        $c = count($args);
        if($c < 1) throw new OrongoScriptParseException("Argument missing for String.Concat()");     
        foreach($args as $arg){
            if(is_int($arg)) $arg = strval($arg);
            else if(is_object($arg)) $arg = "Object";
            else if(!is_string($arg)) $arg = "?";
        }
        $pattern = $args[0];
        unset($args[0]);
        if($c == 1) return OrongoVariable($pattern);
        return new OrongoVariable(vsprintf($pattern,$args));
    }

    public function getShortname() {
        return "Concat";
    }
    
    public function getSpace(){
        return "String";
    }
}

/**
 * Add OrongoScript function
 *
 * @author Jaco Ruit
 */
class FuncAdd extends OrongoFunction {
    

    public function __invoke($args) {
        if(count($args) < 1) throw new OrongoScriptParseException("Argument missing for String.Add()"); 
        $return = "";
        foreach($args as $arg){
            if(is_int($arg)) $arg = strval($arg);
            else if(is_object($arg)) $arg = "Object";
            else if(!is_string($arg)) $arg = "?";
            $return = $return . $arg;
        }
        return new OrongoVariable($return);
    }

    public function getShortname() {
        return "Add";
    }
    
    public function getSpace(){
        return "String";
    }
}

?>
