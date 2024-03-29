<?php

/**
 * OrongoEvent Class
 *
 * @author Jaco Ruit
 */
class OrongoEvent {
    
    private $invoker;
    private $signatureParamCount;
    private $functions;
    private $methods;
    private static $OSevents = array(); 
    
    /**
     * Init the event 
     * @param Closure $paramFunctionSignature the function signature
     */
    public function __construct($paramFunctionSignature){
        if(($paramFunctionSignature instanceof Closure) == false)
            throw new IllegalArgumentException("Invalid argument, Closure (anonymous function) expected!");
        $backtrace = debug_backtrace();
        if(!isset($backtrace[1]['class']))
            throw new Exception("Can't init an event outside a class!");
        $this->invoker = $backtrace[1]['class'];
        $rf = new ReflectionFunction($paramFunctionSignature); 
        $this->signatureParamCount = count($rf->getParameters());
        $this->functions = array();
        $this->methods = array();
    }
    
    /**
     * Subscribes the anonymous function to the event
     * @param Closure $paramFunction the anonymous function to add
     */
    public function subscribe($paramFunction){
        if(($paramFunction instanceof Closure) == false)
            throw new IllegalArgumentException("Invalid argument, Closure (anonymous function) expected!");
        $rf = new ReflectionFunction($paramFunction); 
        if(count($rf->getParameters()) != $this->signatureParamCount)
            throw new IllegalArgumentException("The parameter count of the function you tried to add doesn't match the signature param count.");
        $this->functions[count($this->functions)] = $paramFunction;
    }
    
    /**
     * Subscribes the method to the event
     * @param String $paramMethodName the name of the method
     * @param object/String $paramObject object or the string of the class (static methods) 
     */
    public function subscribeMethod($paramMethodName, $paramObject){
        $rf = new ReflectionMethod($paramObject, $paramMethodName);
        if(count($rf->getParameters()) != $this->signatureParamCount)
            throw new IllegalArgumentException("The parameter count of the function you tried to add doesn't match the signature param count.");
        $this->methods[count($this->methods)] = array ( 0 => $paramMethodName , 1 => $paramObject);
    }
    
    /**
     * Invokes all the functions which were subscribed to the event
     * @param array $paramArgs the args for the function (optional)
     */
    public function __invoke($paramArgs = null){
        $this->invoke($paramArgs);
    }
    
    /**
     * Invokes all the functions which were added to the event
     * @param array $paramArgs the args for the function (optional)
     */
    public function invoke($paramArgs = null){
        if($paramArgs == null && $this->signatureParamCount > 0)
            throw new IllegalArgumentException("Can't invoke the functions, arguments missing.");
        $backtrace = debug_backtrace();
        if(!isset($backtrace[1]['class']))
            throw new Exception("Can't invoke an event outside a class!");
        if($this->invoker != $backtrace[1]['class'])
            throw new Exception("Can't invoke the event from a different class than the class where it was initted.");
        $fixedArgs = array();
        if($paramArgs != null){
            $c = 0;
            foreach($paramArgs as $arg){
                if($c >= $this->signatureParamCount) break;
                $fixedArgs[$c] = $arg;
                $c++;
            }
        }
        foreach($this->functions as $function){
            if(($function instanceof Closure) == false) continue;
            call_user_func_array($function, $fixedArgs);
        }
        foreach($this->methods as $method){
            call_user_method_array($method[0], $method[1], $fixedArgs);
        }
    }
    
  
}

?>
