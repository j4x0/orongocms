<?php
/**
 * setUser function
 *
 * @author Jaco Ruit
 */

/**
* Defines the function getUser
* @param User $paramUser user object
*/

function setUser($paramUser){
    if(function_exists('getUser')) return;
    if(($paramUser instanceof User) == false && $paramUser != null) throw new IllegalArgumentException("Invalid argument, user object expected!");
    global $_orongo_user;
    $GLOBALS['_orongo_user'] = &$paramUser;
    /**
     * @return User
     */
    function getUser(){ return $GLOBALS['_orongo_user']; }
}
?>
