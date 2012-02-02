<?php
/**
 * Admin frontend.
 *  Loads of classes! :)
 * 
 * @author Jaco Ruit
 */
class AdminFrontend extends OrongoFrontendObject {
    
    private $body;
    private $page;
    private $statics;
    private $pageTitle;
    private $msgs;
    private $objects;
    
    private static $msgTypes = array("warning","success","info","error");
    
    public function main($args){
        getDisplay()->setTemplateDir(ROOT . "/orongo-admin/theme/");
        if(!isset($args['page'])){
            $msgbox = new MessageBox("Can't render admin frontend: missing argument 'page'!");
            die($msgbox->getImports() . $msgbox->toHTML());
        }
        if(!is_string($args['page'])){
            $msgbox = new MessageBox("Can't render admin frontend: wrong argument 'page'!");
            die($msgbox->getImports() . $msgbox->toHTML());
        }
        $this->body = "<script src=\"" . Settings::getWebsiteURL() . "js/widget.prettyAlert.js\" type=\"text/javascript\" charset=\"utf-8\"></script>";
        $this->page = $args['page'];
        $this->generateStatistics();
        $this->msgs = array();
        $this->objects = array();
        switch($this->page){
            case "index":
                $this->pageTitle = "Dashboard";
                break;
            case "login":
                $this->pageTitle = "Login";
                break;
            default:
                $msgbox = new MessageBox("Can't render admin frontend: wrong argument 'page'!");
                die($msgbox->getImports() . $msgbox->toHTML());
        }
    }
    
    private function generateStatistics(){
        /**
         *<p><b>Articles:</b> 2201</p>
					<p><b>Comments:</b> 17092</p>
					<p><b>Users:</b> 3788</p> 
         */
        $this->statics .= "<p><b>Users:</b>" . User::getUserCount() . "</p>";
        $this->statics .= "<p><b>Articles:</b>" . Article::getArticleCount() . "</p>";
        $this->statics .= "<p><b>Comments:</b>" . Comment::getCommentCount() ."</p>";
        $this->statics .= "<p><b>Pages:</b>" . Page::getPageCount() . "</p>";
        $this->statics .= "<p><b>Items in storage:</b>" . Storage::getStorageCount() ."</p>";
        $this->statics .= "<p><b>Plugins:</b>" . Plugin::getPluginCount() ."</p>";

    }
    
    /**
     * Adds a message to the admin board
     * @param String $paramMsg Message string
     * @param String $paramMsgType Msg type must be warning, info, success or error
     */
    public function addMessage($paramMsg, $paramMsgType){
        if(!in_array($paramMsgType, self::$msgTypes)) throw new IllegalArgumentException("Invalid message type!");
        $newmsg = array(
            "msg" => $paramMsg,
            "msgtype" => $paramMsgType
        );
        $this->msgs[count($this->msgs) -1 ] = $newmsg;
    }
    
    /**
     * Adds object to the admin board
     * @param AdminFrontendObject $paramObject object to add
     */
    public function addObject($paramObject){
        if(($paramObject instanceof AdminFrontendObject) == false) throw new IllegalArgumentException("Invalid argument, AdminFrontendObject expected!");
        $this->objects[count($this->objects) - 1] = $paramObject;
    }
    
    /**
     * Deletes object from admin board
     * @param AdminFrontendObject $paramObject object to delete 
     * @return boolean indicating if delete was successful
     */
    public function deleteObject($paramObject){
        if(($paramObject instanceof AdminFrontendObject) == false) throw new IllegalArgumentException("Invalid argument, AdminFrontendObject expected!");
        foreach($this->objects as &$object){
            if($object == $paramObject){
                unset($object);
                return true;
            }
        }
        return false;
    }
    
    
    public function render(){
        getDisplay()->setTitle(Settings::getWebsiteName() . " - " . $this->pageTitle );
        getDisplay()->setTemplateVariable("body", $this->body);
    
        if(count($this->msgs) > 0){
        $msgstring = "";
            foreach($this->msgs as $msg){
                if(!is_array($msg)) continue;
                $msgstring .= '<h4 class="alert_' . $msg['msgtype'] . '">' . $msg['msg'] . "</h4>";
            }
            getDisplay()->setTemplateVariable("msgs", $msgstring);
        }
        
        $objectshtml = "";
        foreach($this->objects as $object){
            if(($object instanceof AdminFrontendObject) == false)continue;
            $objectshtml .= $object->toHTML();
        }
        
        getDisplay()->setTemplateVariable("objects", $objectshtml);
        
        getDisplay()->setTemplateVariable("statics", $this->statics);
        
        getDisplay()->setTemplateVariable("style_url", Settings::getWebsiteURL() ."orongo-admin/theme/");
        getStyle()->run();

        getDisplay()->add("header");
        getDisplay()->add($this->page);
        getDisplay()->render();
    }
}


class AdminFrontendObject implements IHTMLConvertable{
    
    private $header;
    private $footer;
    private $content;
    private $size;
    
    private static $sizes = array("3 quarter", "half", "quarter", "full");
    /**
     *Init admin frontend object
     * @param String $paramTitle Title of object
     * @param String $paramContent Content of object
     * @param String $paramFooter footer of object (optional)
     */
    public function __construct($paramTitle, $paramSize, $paramContent, $paramFooter = null){
        if(!in_array($paramSize, self::$sizes)) throw new IllegalArgumentException("Invalid size!");
        $this->header = '<h3>' . $paramTitle . '</h3>';
        $this->content = '<div class="module_content">' . $paramContent . '</div><div class="clear"></div>';
        $this->footer =  $paramFooter;
        $this->size = $paramSize;
    }
    
    /**
     * @return String title of object 
     */
    public function getTitle(){
        if(!stristr("</h3>", $this->header)) return "";
        $strippedH = explode("</h3>", $this->header);
        $title = $strippedH[0];
        if(!stristr("<h3>", $title)) return "";
        $title = explode("<h3>", $this->header);
        return end($title);
    }
    
    /**
     * Sets title of object
     * @param String $paramTitle new title 
     */
    public function setTitle($paramTitle){
        $this->header = str_replace("<h3>" .$this->getTitle() ."</h3>", "<h3>" . $paramTitle . "</h3>", $this->header);
    }
    
    /**
     * Gets content of object 
     * @return String content
     */
    public function getContent(){
        $str = str_replace("<div class=\"module_content\">", "", $this->content);
        $str = strrev($str);
        $str = str_replace(strrev('</div><div class="clear"></div>'), "", $str);
        return strrev($str);
    }
    
    /**
     * Sets the content of object 
     * @param String $paramContent new content
     */
    public function setContent($paramContent){
        $this->content = "<div class=\"module_content\">" . $paramContent . '</div><div class="clear"></div>';
    }
    
    /**
     * Gets raw content 
     * @return String content
     */
    public function getRawContent(){
        return $this->content;
    }
    
    /**
     * Sets raw content
     * @param String $paramContent new content 
     */
    public function setRawContent($paramContent){
        $this->content = $paramContent;
    }
    
    /**
     * Gets the footer
     * @return String footer 
     */
    public function getFooter(){
        return $this->footer;
    }
    
    /**
     * Sets the footer
     * @param String $paramFooter new footer 
     */
    public function setFooter($paramFooter){
        $this->footer = $paramFooter;
    }
    
    /**
     * Sets the raw header 
     * @param String $paramHeader new header
     */
    public function setHeader($paramHeader){
        $this->header = $paramHeader;
    }
    
    /**
     * Gets raw header
     * @return String raw header
     */
    public function getHeader(){
        return $this->header;
    }

    public function toHTML() {
        $rt = "<header>". $this->header . "</header>" . $this->content;
        if($this->footer != null) $rt .= "<footer>" . $this->footer . "</footer>";
        return "<article class=\"module width_". str_replace(" ", "_", $this->size) . "\">" . $rt . "</article>";
    }
}

class AdminFrontendForm extends AdminFrontendObject{
    
    private $method;
    private $action;
    private $buttons;
    private $inputs;
   
    private static $methods = array("get", "post");
    
    /**
     * Inits the form
     * @param String $paramTitle title of form
     * @param String $paramSize size of form
     * @param String $paramMethod POST or GET
     * @param String $paramAction action of the form
     */
    public function __construct($paramTitle, $paramSize, $paramMethod, $paramAction){
        $this->method = strtolower($paramMethod);
        $this->action = strtolower($paramAction);
        $this->buttons = array();
        $this->inputs = array();
        if(!in_array($this->method, self::$methods)) throw new IllegalArgumentException("Invalid method!");
        parent::__construct($paramTitle, $paramSize, "");
    }
    
    /**
     * Gets the action
     * @return String action 
     */
    public function getAction(){
        return $this->action;
    }
    
    /**
     * Sets the action
     * @param String $paramAction new action
     */
    public function setAction($paramAction){
        $this->action = $paramAction;
        $this->updateHTML();
    }
    
    /**
     * Adds an input
     * @param String $paramType HTML form type
     * @param String $paramLabel label for the input
     * @param String $paramName name of the input
     * @param String $paramValue value of the form (default nothing)
     * @param boolean $paramRequired indicating if this is required (default false)
     */
    public function addInput($paramType, $paramLabel, $paramName, $paramValue = "", $paramRequired = false){
        $input = array(
           "type" => $paramType,  
           "label" => $paramLabel,
           "name" => $paramName,
           "value" => $paramValue,
           "required" => $paramRequired
        );
        $this->inputs[count($this->inputs)] = $input;
        $this->updateHTML();
    }
    
    /**
     * Adds a button
     * @param String $paramText text of the button
     * @param boolean $paramBlue indicating if this buttons has to be blue
     */
    public function addButton($paramText, $paramBlue){
        $button = array(
            "text" => $paramText,
            "blue" => $paramBlue
        );
        $this->buttons[count($this->buttons)] = $button;
        $this->updateHTML();
    }
    

    /**
     * Updates the AdminFrontendObject 
     */
    private function updateHTML(){
        $content = "<form action=\"" . $this->action . "\" method = \"" . $this->method . "\">";
        foreach($this->inputs as $input){
            $content .= "<fieldset>";
            $content .= "<label>" . $input['label'] . "</label>";
            $content .= "<input type=\"" . $input['type'] . "\" name=\"" . $input['name'] . "\" value=\"" . $input['value'] . "\" ";
            if($input['required']) $content .= " required>";
            else $content .= ">";
            $content .= "</fieldset>";
        }
        $content .= "</form>";
        parent::setContent($content);
        
        if(count($this->buttons) > 0){
            $footer = "<div class=\"submit_link\">";
            foreach($this->buttons as $button){
                $footer .= "<input type=\"submit\" value=\"" . $button['text'] . "\" ";
                if($button['blue']) $footer .= 'class="alt_btn" >';
                else $footer .= ">";
            }
            $footer .= "</div></form>";
            parent::setFooter($footer);
        }else{
            parent::setContent(parent::getContent() . "</form>");
        }
        
    }
}

?>