<?php
/**
 * Plugin Class
 *
 * @author Jaco Ruit
 */
class Plugin {
    
    private static $tPlugins = array();
    
    /**
     * Installs database for the plugin
     * @param String $paramPrefix Prefix for the folder, sub-folders use this (starts from plugins/)
     * @param String $paramPluginFolder folder where plugin is located
     */
    public static function install($paramPrefix, $paramPluginFolder){
        $filePath = $paramPrefix . 'plugins/'. $paramPluginFolder . '/info.xml';
        if(file_exists($filePath) == false) throw new Exception("The plugin's info.xml doesn't exist!");
        $xml = @simplexml_load_file($filePath);
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        $setting = '';
        $typeSetting= '';
        if(!isset($info['plugin']['access_key'])) throw new Exception("The plugin's access key wasn't found");
        $accessKey = $info['plugin']['access_key'];
        foreach($info['plugin']['settings'] as $key=>$value){
            $setting = $key;
            foreach($info['plugin']['settings'][$key] as $key=>$value){
                if($key == 'type'){
                    $typeSetting = $value;
                    self::installSetting($accessKey , $setting, $typeSetting);
                }else if($key == 'default'){
                    $default = $value;
                    $this->setSetting($accessKey, $settings, $default);
                }
            }
        }  
    }
    
    /**
     * Installs a setting
     * @param String $paramAccessKey Plugin access key
     * @param String $paramSetting     Setting name
     * @param String $paramSettingType Setting type
     */
    private static function installSetting($paramAccessKey, $paramSetting, $paramSettingType){
        $q = "INSERT INTO `plugins` (`access_key`, `setting`, `setting_type`, `setting_value`) VALUES ('" . $paramAccessKey . "', '" .$paramSetting . "', '" . $paramSettingType . "', '')";
        getDatabase()->execQuery($q);  
    }
    
    /**
     * Gets the plugin settings
     * @param String $paramAccessKey Plugin access key
     * @return array Settings of plugin
     */
    public static function getSettings($paramAccessKey){
        $q = "SELECT `setting_value`, `setting`, `setting_type` FROM `plugins` WHERE `access_key` = '" . $paramAccessKey . "'";
        $result = getDatabase()->execQuery($q);
        $settings = array();
        while($row = mysql_fetch_assoc($result)){
            if($row['setting_type'] == 'boolean'){
                if($row['setting_value'] == 'true'){
                    $settings[$row['setting']] = true;
                }else{
                    $settings[$row['setting']] = false;
                }
            }else{
                $settings[$row['setting']] = $row['setting_value'];
            }
        }
        mysql_free_result($result);
        return $settings;
    }
    
    /**
     * Sets a plugin setting
     * @param String $paramAccessKey Plugin access key
     * @param String $paramSetting      The setting to edit
     * @param String $paramValue        New value of settings
     */
    public static function setSetting($paramAccessKey, $paramSetting, $paramValue){
        $backtrace = debug_backtrace();

        $paramSetting =  mysql_escape_string($paramSetting);
        $paramValue =  mysql_escape_string($paramValue);
        $q1 = "SELECT `setting_value` FROM `plugins` WHERE `access_key` = '" . $paramAccessKey . "'' AND `setting` = '" . $paramSetting . "'";
        $result = getDatabase()->execQuery($q1);
        if(mysql_num_rows($result)  < 1 && $backtrace[1]['class'] != __CLASS__) throw new IllegalMemoryAccessException("This settings doesn't exist or you are accessing the setting illegal.");
        $q2 = "UPDATE `plugins` SET `setting_value` = '" . $paramValue . "' WHERE `access_key` = '" . $paramAccessKey . "' AND `setting` = '" . $paramSetting . "'";
        getDatabase()->execQuery($q);
    }
    
    /**
     * Gets the plugin name
     * @param String $paramPrefix Prefix for the folder, sub-folders use this
     * @param String $paramPluginFolder Plugin name
     * @return String Name of plugin
     */
    public static function getName($paramPrefix, $paramPluginFolder){
        $xml = @simplexml_load_file($paramPrefix . 'plugins/'. $paramPluginFolder . '/info.xml');
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        return $info['plugin']['name'];
    }
    
    /**
     * Gets the plugin main_class
     * @param String $paramPrefix Prefix for the folder, sub-folders use this
     * @param String $paramPluginFolder Plugin folder
     * @return String Main class of plugin
     */
    public static function getMainClass($paramPrefix,$paramPluginFolder){
        $xml = @simplexml_load_file($paramPrefix.'plugins/'. $paramPluginFolder . '/info.xml');
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        return $info['plugin']['main_class'];
    }
    
    /**
     * Gets the plugin description
     * @param String $paramInfoXML path of info.xml
     * @return String Description of plugin
     */
    public static function getDescription($paramInfoXML){
        if(empty($paramInfoXML) || !file_exists($paramInfoXML)) return "";
        $xml = @simplexml_load_file($paramInfoXML);
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        return $info['plugin']['description'];
    }
    
   /**
    * Gets the plugin name
    * @param String $paramInfoXML path of info.xml
    * @return String name of plugin
    */
    public static function getName($paramInfoXML){
        if(empty($paramInfoXML) || !file_exists($paramInfoXML)) return "";
        $xml = @simplexml_load_file($paramInfoXML);
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        return $info['plugin']['name'];
    }
    
    /**
     * Gets author info
     * @param String $paramInfoXML path of info.xml
     * @return array author info
     */
    public static function getAuthorInfo($paramInfoXML){
        if(empty($paramInfoXML) || !file_exists($paramInfoXML)) return "";
        $xml = @simplexml_load_file($paramInfoXML);
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        return $info['plugin']['author'];
    }
    
    /**
     * @param String $paramPrefix Prefix for the folder, sub-folders use this
     * @param String $paramPluginFolder Plugin folder
     * @return String PHP file of plugin
     */
    public static function getPHPFile($paramPrefix, $paramPluginFolder){
        $xml = @simplexml_load_file($paramPrefix . 'plugins/'. $paramPluginFolder . '/info.xml');
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        return $info['plugin']['php_file'] . '.php';
    }
    
    /**
     * Returns activated plugins
     * @param String $paramPrefix Prefix for the folder, sub-folders inserts before plugins/plugin_name
     * @return array containing plugins
     */
    public static function getActivatedPlugins($paramPrefix){
        $q =  "SELECT `plugin_folder` FROM `activated_plugins`";
        $result = getDatabase()->execQuery($q);
        $plugins = array();
        $count = 0;
        while($row = mysql_fetch_assoc($result)){
            $pluginFolder = $paramPrefix . 'plugins/' . $row['plugin_folder'] . '/'. self::getPHPFile($paramPrefix, $row['plugin_folder']);
            if(!file_exists($pluginFolder)) continue;
            require $pluginFolder;
            try{
               $className = self::getMainClass($paramPrefix, $row['plugin_folder']);
               $plugin = new $className;
               if($plugin instanceof OrongoPluggableObject) $plugins[$count] = $plugin; 
               $count++;
            }catch(IllegalMemoryAccessException $ie){
                throw new ClassLoadException("Plugin tried to access illegal memory. Unable to load plugin: <br /> " . $pluginFolder);
                continue;
            }catch(Exception $e){
                throw new ClassLoadException("Unable to load plugin: <br /> " . $pluginFolder);
                continue;
            }
        }
        mysql_free_result($result);
        return $plugins;
    }
    
    /**
     * Gets activated plugins count
     * @return int plugins count
     */
    public static function getPluginCount(){
        $q = "SELECT `plugin_folder` FROM `activated_plugins`";
        $result = getDatabase()->execQuery($q);
        $rows = mysql_num_rows($result);
        mysql_free_result($result);
        return $rows;
    }
    
    /**
     * Hooks a terminal plugin
     * @param $paramPlugin object class implementing IOrongoTerminalPlugin
     * @return boolean indicating if it was hooked succesfully
     */
    public static function hookTerminalPlugin($paramPlugin){
        if(($paramPlugin instanceof IOrongoTerminalPlugin) == false)
            throw new IllegalMemoryAccessException("Invalid argument, class implementing IOrongoTerminalPlugin expected.");
        $methods = array('about', 'version');
        $methods = array_merge($methods, get_class_methods('OrongoTerminal'));
        $pluginMethods = get_class_methods(get_class($paramPlugin));
        foreach($pluginMethods as $pluginMethod){
            if(in_array($pluginMethod, $methods))
                    return false;
        }
        self::$tPlugins[count(self::$tPlugins)] = $paramPlugin;
        return true;
    }
    
    /**
     * Returns the terminal plugins
     * @return array classes implementing IOrongoTerminalPlugin
     */
    public static function getHookedTerminalPlugins(){
        return self::$tPlugins;
    }
}

?>
