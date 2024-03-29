<?php

/**
 * Storage Class
 *
 * @author Jaco Ruit
 */
class Storage {
    
    
    /**
     * Stores the value of the variable in the database
     * @param String $paramKey Key to access the variable later (Storage key)
     * @param String/object $paramVar Variable to store
     * @param boolean $paramOverwrite If true and the key already exists it will overwrite it
     * @return boolean Indicates if the storage was succesful
     */
    public static function store($paramKey, $paramVar, $paramOverwrite = true){
        $paramKey = mysql_escape_string($paramKey);
        getDatabase()->query("SELECT `var` FROM `storage` WHERE `key`=%s", $paramKey);
        $rows = getDatabase()->count();
        $isObj = false;
        if(is_object($paramVar)){
            if($paramVar instanceof IStorable){
                try{
                    $stSyntax = $paramVar->toStorageSyntax();
                }catch(Exception $e){
                    throw $e;
                    return false;
                }
                if(!is_array($stSyntax)) return false;
                $stSyntax['_orongo_istorable_class_name'] = get_class($paramVar);
                try{
                    $paramVar = json_encode($stSyntax);
                }
                catch(Exception $e){
                    throw $e;
                    return false;
                }
                $isObj = true;
            }else{
                throw new IllegalArgumentException("Invalid paramater, object implementing IStorable expected.");
            }
        }
        if($rows > 0){
            if($paramOverwrite == false){
                return false;
            }else{
                getDatabase()->update("storage", array(
                   "var" => $paramVar,
                   "is_object" => $isObj
                ),"`key`=%s", $paramKey);
                return true;
            }
        }else{
            getDatabase()->insert("storage",array(
                "key" => $paramKey,
                "var" => $paramVar,
                "is_object" => $isObj
            ));
            return true;
        }
    }
    
    /**
     * Deletes the key and its value from the storage
     * @param String $paramKey Storage key
     */
    public static function delete($paramKey){
        getDatabase()->delete("storage", "`key`=%s", $paramKey);
    }
    
    /**
     * Gets the value of the key
     * @param String $paramKey Storage key
     */
    public static function get($paramKey){
        $row = getDatabase()->queryFirstRow("SELECT `var`, `is_object` FROM `storage` WHERE `key` = %s", $paramKey);
        if($row['is_object'] == false) return $row['var'];
        else{
            try{
               $stSyntax = json_decode($row['var'], true);
               if(isset($stSyntax['_orongo_istorable_class_name'])){
                   $className = $stSyntax['_orongo_istorable_class_name'];
                   $obj = new $className($stSyntax);
                   return $obj;
               }else{
                   return $row['var'];
               }
            }catch(Exception $e){
                throw new Exception("Couldn't return the object: " . $e->getMessage());     
            }
        }
    }
    
    
    /**
     * Gets stored items count
     * @return int stored items count
     */
    public static function getStorageCount(){
        getDatabase()->query("SELECT `is_object` FROM `storage`");
        return getDatabase()->count();
    }
    
    
}

?>
