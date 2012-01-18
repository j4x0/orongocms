<?php

/**
 * Display Class
 *
 * @author Jaco Ruit
 */
class Display {
    
    private $smarty;
    private $tpls;
    private $rendered;
    private $objects;
    private $head;
    private $js;
    private $generalhtml;
    
    private static $jQueryIgnoreQuotes = array('document', 'window');
    
    /**
     * Initialize the smarty and display object
     * @param String $paramTemplateDir smarty/template dir
     * @param String $paramCompileDir smarty/compile dir
     * @param String $paramCacheDir smarty/cache dir
     * @param String $paramConfigDir  smarty/config dir
     */
    public function __construct($paramTemplateDir, $paramCompileDir = "../smarty/compile", $paramCacheDir = "../smarty/cache", $paramConfigDir = "../smarty/config"){
        $this->smarty = new Smarty();
        $this->smarty->template_dir = $paramTemplateDir;
        $this->smarty->compile_dir = $paramCompileDir;
        $this->smarty->cache_dir = $paramCacheDir;
        $this->smarty->config_dir = $paramConfigDir; 
        
        $this->rendered = false;
        $this->tpls = array();
        $this->objects = array();
        $this->head = "<meta name=\"generator\" content=\"OrongoCMS r" . REVISION . "\" />";
        $this->js = "";
        $this->generalhtml = "";
        $this->setTemplateVariable("menu", HTMLFactory::getMenuCode());
    }
    
    /**
     * Sets the template dir
     * @param String $paramTemplateDir new template directory
     */
    public function setTemplateDir($paramTemplateDir){
        if(!is_string($paramTemplateDir))
            throw new IllegalArgumentException("Invalid argument, string expected.");
        $this->smarty->template_dir = $paramTemplateDir;
    }
    
    /**
     * Adds string to template variable
     * @param String $paramVariable name of the variable
     * @param String $paramAdd string to add
     */
    public function addToTemplateVariable($paramVariable, $paramAdd){
        $this->setTemplateVariable($paramVariable, $this->getTemplateVariable($paramVariable) . $paramAdd);
    }
    
    /**
     * Returns template variable
     * @param String $paramVariable name of the variable
     */
    public function getTemplateVariable($paramVariable){
        $var = "";
        try{
            $var = $this->smarty->getTemplateVars($paramVariable);
        }catch(Exception $e){ }
        return $var;
    }
    
    /**
     * Sets a template variable
     * @param String $paramVariable name of the variable
     * @param String $paramValue value of the variable
     */
    public function setTemplateVariable($paramVariable, $paramValue){
        if($this->rendered) return;
        $this->smarty->assign($paramVariable,$paramValue);
    }
    
    /**
     * Add an OrongoDisplayableObject to display
     * @param OrongoDisplayableObject $paramObject (class extending abstract class OrongoDisplayableObject)
     */
    public function addObject(&$paramObject){
        if(($paramObject instanceof OrongoDisplayableObject) == false)
            throw new IllegalArgumentException("Invalid argument, class extending OrongoDisplayableObject expected.");
        $this->objects[count($this->objects)] = $paramObject;
    }
    
    /**
     * Add JavaScript to display
     * @param String $paramJS JavaScript
     * @param String $paramEvent jQuery event like document.ready or #example.scroll [OPTIONAL]
     */
    public function addJS($paramJS, $paramEvent = null){
        if(!is_string($paramJS) || ($paramEvent != null && !is_string($paramEvent)))
            throw new IllegalArgumentException("Invalid argument, string expected.");
        $jsBuilder = $paramJS;
        if(!empty($paramEvent)){
            $exploded = explode(".", $paramEvent);
            //TODO empty check => illegal argument exc
            $event = end($exploded);
            $eventele = str_replace("." . $event, "", $paramEvent);
            $jsBuilder = "$(";
            if(in_array($eventele, self::$jQueryIgnoreQuotes))
                $jsBuilder .= $eventele;
            else
                $jsBuilder .= "'" . $eventele . "'";
            $jsBuilder .= ")." . $event . "(function(event){ " . $paramJS . " });";
        }
        $this->js .= $jsBuilder;
    }
    
    /**
     * Add HTML to display
     * @param String $paramHTML HTML Code (This is added where style has $body var)
     */
    public function addHTML($paramHTML){
        if(!is_string($paramHTML)) throw new IllegalArgumentException("Invalid argument, string expected.");
        $this->generalhtml .= $paramHTML;
    }
    
    /**
     * Set CSS using jQuery(like http://api.jquery.com/css/)
     * @param String $paramElement HTML Element
     * @param String $paramProperty CSS property 
     * @param String $paramValue new value
     */
    public function setCSS($paramElement, $paramProperty, $paramValue){
        if(!is_string($paramElement) || !is_string($paramProperty) || !is_string($paramValue))
            throw new IllegalArgumentException("Invalid argument, string expected!");
        $this->addJS("$('" . $paramElement . "').css('".  $paramProperty . "', '" . $paramValue . "')");
    }
    
    /**
     * Adds a TPL file
     * @param String $paramTPLFile TPL (*.orongo) file to add to display 
     * @return boolean indicating if was added succesful
     */
    public function add($paramTPLFile){
        if(!is_string($paramTPLFile)) throw new IllegalArgumentException("Invalid argument, string expected.");
        if(in_array($paramTPLFile, $this->tpls)) return false;
        $this->tpls[count($this->tpls)] = $paramTPLFile;
        return true;
    }
    
    /**
     * Delets a TPL file
     * @param String $paramTPLFile TPL (*.orongo) file to delete from display
     */
    public function delete($paramTPLFile){
        foreach($this->tpls as &$tpl){
            if($tpl == $paramTPLFile){
                $tpl = null;
                return;
            }
        }
    }
    
    /**
     * Deletes an object from Display
     * @param OrongoDisplayableObject $paramObject (class extending abstract class OrongoDisplayableObject)
     * @return boolean indicating if its added succesful
     */
    public function deleteObject($paramObject){
        if(($paramObject instanceof OrongoDisplayableObject) == false)
            throw new IllegalArgumentException("Invalid argument, class extending OrongoDisplayableObject expected.");
        foreach($this->objects as &$object){
            if($object == $paramObject){
                $object = null;
                return true;
            }
        }
        return false;
    }
    
    /**
     * Renders the Display
     */
    public function render(){
        if($this->rendered) return;
        $this->setTemplateVariable("website_name", Settings::getWebsiteName());
        $this->setTemplateVariable("website_url", Settings::getWebsiteURL());
        $this->addToTemplateVariable("body", '<script type="text/javascript">' . $this->js . '</script>');
        $this->addToTemplateVariable("body", $this->generalhtml);
        foreach($this->objects as $object){
            if($object == null) continue;
            if(($object instanceof OrongoDisplayableObject) == false) continue;
            $this->addToTemplateVariable("body", $object->toHTML());
        }
        $this->addToTemplateVariable("head", $this->head);
        if(getUser() != null){
            $this->setTemplateVariable("user", getUser());
            $mb = new MenuBar(getUser());
            $this->setTemplateVariable("menu_bar", $mb->toHTML());
        }else{
            $this->setTemplateVariable("menu_bar", "");
        }
        foreach($this->tpls as $tpl){
            if(empty($tpl)) continue;
            $this->smarty->display($tpl);
        }
        $this->rendered = true;
    }
}

?>
