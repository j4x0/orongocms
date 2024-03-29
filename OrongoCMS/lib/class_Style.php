<?php

/**
 * Style Class
 *
 * @author Jaco Ruit
 */
class Style {
    
    private static $publicSettings;
    private static $constructed = false;
    private $styleFolder;
    private $styleName;
    private $styleCopyright;
    private $authorName;
    private $authorWebsite;
    private $usePHP;
    private $phpFile;
    private $mainClass;
    private $doHTMLArticle;
    private $doHTMLComment;
    private $stylePath;
    
    /**
     * Style Object
     * @param String $paramPrefix Prefix for the folder, sub-folders use this
     * @param String $paramStyleFolder Folder where info.xml and template files are located 
     * @author Jaco Ruit
     */
    public function __construct($paramStyleFolder){
        self::$constructed = true;
        $this->styleFolder = $paramStyleFolder;
        $filePath = ROOT. '/themes/'. $this->styleFolder . '/info.xml';
        if(file_exists($filePath) == false) throw new Exception("Unable to load the style. <br /> The info.xml file of the style doesn't exist!");
        $xml = @simplexml_load_file($filePath);
        $this->stylePath = ROOT  . '/themes/'. $this->styleFolder . "/";
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        $this->styleName = $info['style']['name'];
        $this->styleCopyright = $info['style']['copyright'];
        $this->authorName = $info['style']['author']['name'];
        $this->authorWebsite = $info['style']['author']['website'];
        $this->usePHP = $info['style']['use_php'] == 'true' ? true : false;
        $this->phpFile = $this->usePHP ? $info['style']['php_file'] : null;
        if($this->usePHP){
            try{
                $pathString = ROOT. '/themes/' . $this->getStyleFolder() . '/' . $this->getPHPFile();
                if(!file_exists($pathString)) throw new ClassLoadException();
                require ROOT . '/themes/' . $this->getStyleFolder() . '/' . $this->getPHPFile();
                if(!class_exists($info['style']['main_class'])) throw new ClassLoadException($info['style']['main_class'] . " class doesn't exist!");
                $this->mainClass = new $info['style']['main_class'];
                if(($this->mainClass instanceof IOrongoStyle) == false) throw new Exception();
            }catch(Exception $e){
                throw new ClassLoadException("Unable to load the style. <br /> Please check the info.xml of the activated style for errors.");
            }
        }else{
            $this->mainClass = null;
        }
        $this->doHTMLArticle = $info['style']['own_article_html'] == "true" ? true : false;
        $this->doHTMLComment = $info['style']['own_comment_html'] == "true" ? true : false;
    }
    
    #   styleFolder
    /**
     * Returns style folder
     * Returns only the name of the folder not the whole URL
     * @return String folder of the style
     */
    public function getStyleFolder(){
        return $this->styleFolder;
    }
    
    #   styleName
    /**
     * Returns style name
     * This has to be put in the footer
     * @return String name of the style
     */
    public function getStyleName(){
        return $this->styleName;
    }
    
    #   styleCopyright
    /**
     * Returns copyright of the style
     * This has to be put in the footer
     * @return String Copyright string of the style
     */
    public function getCopyright(){
        return $this->styleCopyright;
    }
    
    #   authorName
    /**
     * Returns the name of the author of the style
     * This has to be put in the footer
     * @return String name of author
     */
    public function getAuthorName(){
        return $this->authorName;
    }
    
    #   authorWebsite
    /**
     * Returns website of the author of the style
     * This has to be put in the footer
     * @return String website of author
     */
    public function getAuthorWebsite(){
        return $this->authorWebsite;
    }
    
    #usePHP
    /**
     * Returns true if the style uses php to customize some Smarty things
     * @return boolean indicating if the style uses php
     */
    public function isUsingPHP(){
        return $this->usePHP;
    }
    
    #phpFile
    /**
     * Returns the PHP file name.
     * Always check if its not null before using this, because then it's not using php.
     * @return String PHP file name.
     */
    public function getPHPFile(){
        return $this->phpFile . '.php';
    }
    
    #stylePath
    /**
     * Returns the path of the style
     * @return String Path of style
     */
    public function getStylePath(){
        return $this->stylePath;
    }
    
    
    
    #mainClass
    /**
     * Returns style's main class
     * Always check if its not null before using this, because then it's not using php.
     * @return String Main Class
     */
    public function getMainClass(){
        return $this->mainClass;
    }
    
    /**
     * Checks if the style generates the HTML for articles
     * @return boolean indicating if it does
     */
    public function doArticleHTML(){
        return $this->doHTMLArticle;
    }
    
    /**
     * Checks if the style generates the HTML for comments
     * @return boolean indicating if it does
     */
    public function doCommentHTML(){
        return $this->doHTMLComment;
    }
    
    /**
     * Runs the style's PHP file if it has one.
     */
    public function run(){
        if($this->usePHP){
            if($this->getPHPFile() != null){
                try{
                    if($this->mainClass instanceof IOrongoStyle){
                        $this->mainClass->run();
                    }
                }catch(Exception $e){
                    $msg = new MessageBox("An error occured while running the style.");
                    $msg->bindException($e);
                    getDisplay()->addObject($msg);                    
                }
            }
        }
    }
    
    
    /**
     * Gets the HTML for an article array
     * @param array $paramArticles array with article objects
     */
    public function getArticlesHTML($paramArticles){
        try{
            if($this->doHTMLArticle && $this->usePHP &&($this->mainClass instanceof IOrongoStyle)){
                $genHTML = $this->mainClass->getArticlesHTML($paramArticles);
                if($genHTML != null && is_string($genHTML) && $genHTML != "") return $genHTML;
                else throw new Exception();
            }else throw new Exception();
        }catch(Exception $e){
            throw new Exception("Style doesn't generate the HTML for articles. Please call default function.");
        }
    }
    
    /**
     * Gets the HTML for a comment array
     * @param array $paramComments array with  comment objects
     */
    public function getCommentsHTML($paramArticles){
        try{
            if($this->doHTMLComment && $this->usePHP &&($this->mainClass instanceof IOrongoStyle)){
                $genHTML = $this->mainClass->getCommentsHTML($paramArticles);
                if($genHTML != null && is_string($genHTML) && $genHTML != "") return $genHTML;
                else throw new Exception();
            }else throw new Exception();
        }catch(Exception $e){
            throw new Exception("Style doesn't generate the HTML for comments. Please call default function.");
        }
    }
    
    /**
     * Installs database for the style
     * @param String $paramInfoXML path where info.xml of style is located
     */
    public static function install($paramInfoXML){
        if(file_exists($paramInfoXML) == false) throw new Exception("The style's info.xml doesn't exist!");
        $xml = @simplexml_load_file($paramInfoXML);
        $json = @json_encode($xml);
        $info = @json_decode($json, true);
        $setting = '';
        $typeSetting= '';
        if($info['style']['use_php'] != 'true') throw new Exception("Cannot install settings because the style is not using PHP.");
        foreach($info['style']['settings'] as $key=>$value){
            $setting = $key;
            foreach($info['style']['settings'][$key] as $key=>$value){
                if($key == 'type'){
                    $typeSetting = $value;
                    self::installSetting($info['style']['main_class'] , $setting, $typeSetting);
                }else if($key == 'default'){
                    $default = str_replace('{$website_url}', Settings::getWebsiteURL(), $value);
                    getDatabase()->update("style_data", array(
                       "setting_value" => $default 
                    ), "`style_main_class`=%s AND `setting`=%s", $info['style']['main_class'], $setting);
                }
            }
        }  
    }
    
    /**
     * Installs a setting
     * @param String $paramStyleMainClass Style main class
     * @param String $paramSetting     Setting name
     * @param String $paramSettingType Setting type
     */
    private static function installSetting($paramStyleMainClass, $paramSetting, $paramSettingType){
        getDatabase()->insert("style_data", array(
           "style_main_class" => $paramStyleMainClass,
           "setting" => $paramSetting,
           "setting_type" => $paramSettingType,
           "setting_value" => ""
        ));
    }
    
    /**
     * Gets the style settings
     * @return array Settings of style
     */
    public static function getSettings(){
        $backtrace = debug_backtrace();
        if(!is_array($backtrace)) throw new Exception ("Couldn't get array from debug_backtrace function.");
        if(!isset($backtrace[1]['class'])) throw new IllegalMemoryAccessException("You can only call this function inside a class.");
        $results = getDatabase()->query("SELECT `setting_value`, `setting`, `setting_type` FROM `style_data` WHERE `style_main_class` = %s", $backtrace[1]['class']);
        $settings = array();
        foreach($results as $row){
            if($row['setting_type'] == 'boolean')
                $settings[$row['setting']] = $row['setting_value'] == 'true' ? true : false;
            else
                $settings[$row['setting']] = $row['setting_value'];    
        }
        return $settings;
    }
    
    /**
     * Sets a style setting
     * @param String $paramSetting      The setting to edit
     * @param String $paramValue        New value of settings
     */
    public static function setSetting($paramSetting, $paramValue){
        $backtrace = debug_backtrace();
        if(!is_array($backtrace)) throw new Exception ("Couldn't get array from debug_backtrace function.");
        if(!isset($backtrace[1]['class'])) throw new IllegalMemoryAccessException("You can only call this function inside a class.");
        $paramSetting =  mysql_escape_string($paramSetting);
        $paramValue =  mysql_escape_string($paramValue);
        getDatabase()->query("SELECT `setting_value` FROM `style_data` WHERE `style_main_class` = %s AND `setting` = %s", $backtrace[1]['class'], $paramSetting);
        if(getDatabase()->count()  < 1 && $backtrace[1]['class'] != __CLASS__) throw new IllegalMemoryAccessException("This settings doesn't exist or you are accessing the setting illegal.");
        getDatabase()->update("style_data", array(
           "setting_value" => $paramValue 
        ), "`style_main_class`=%s AND `setting`=%s", $backtrace[1]['class'], $paramSetting);
    }
} 

?>
