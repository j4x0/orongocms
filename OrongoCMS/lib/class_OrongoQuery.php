<?php

/**
 * OrongoQuery object
 *
 * @author Jaco Ruit
 */
class OrongoQuery {
    

    
    private $queryArray;

    
    /**
     * Construct a query.
     * You can obtain like latest post, all posts by specified user etc.
     * @param String $paramQuery query string
     */
    public function __construct($paramQuery){
       
        if(!is_string($paramQuery)) throw new IllegalArgumentException("Invalid parameter, string expected.");  
        if(strstr($paramQuery, " ")) throw new QueryException("Invalid query string: string may not contain whitespaces.");
        $queryArray = explode("&", $paramQuery);
        $this->queryArray = $queryArray;
        $this->generateQueryArray();
        
    }
    
    /**
     * Generates a proper query array which can be interpret by OrongoQueryHandler
     */
    private function  generateQueryArray(){
       $query = array();
       foreach($this->queryArray as $key=>$value){
           $value = strtolower($value);
           if(!strstr($value,"=")) throw new QueryException("Invalid query string.");
           $single = explode("=", $value);
           if(count($single) != 2) throw new QueryException("Invalid query string.");
           if($single[0] == "" || $single[1]== "") throw new QueryException("Invalid query string: parameters may not be blank.");
           $query[trim($single[0])] = trim($single[1]);
       }
       if(!isset($query['action'])) throw new QueryException("Invalid query string: there is no action.");
       if(!isset($query['object'])) throw new QueryException("Invalid query string: there is no object defined.");
       $this->queryArray = $query;
    }
    
    /**
     * Returns the array generated by generateQueryArray() 
     * @return array array which can be interpret by OrongoQueryHandler
     */
    public function getQueryArray(){
        return $this->queryArray;
    }

}

?>