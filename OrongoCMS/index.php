<?php
/**
 * @author Jaco Ruit
 */

//Debug line
//TODO remove on release
$time_start = microtime(true);

require 'globals.php';

Plugin::setCurrentPage(PAGE_INDEX);

#handle orongo-id, orongo-session-id
$user = handleSessions();


$head = "<meta name=\"generator\" content=\"OrongoCMS r" . REVISION . "\" />";
$errors = "";
$website_name = Settings::getWebsiteName();
$website_url = Settings::getWebsiteURL();
$document_ready = "";
$pages = Page::getPages();
$menu = HTMLFactory::getMenuCode($pages);
$pluginHTML = null;

try{
    $plugins = Plugin::getActivatedPlugins('orongo-admin/');
    $pluginHTML = handlePlugins($plugins);
}catch(Exception $e){
    $msgbox = new MessageBox();
    $msgbox->bindException($e);
    $errors.= $msgbox->toHTML();
}


$menu_bar = "";
if($user != null){
    $menu_bar = '<script src="'. $website_url . 'js/interface.menu_effects.js"  type="text/javascript" charset="utf-8"></script>';
    $menu_bar .= '<link rel="stylesheet" href="'. $website_url . 'orongo-admin/style/style.menu.css" type="text/css"/>';
    $menu_bar .= '<div class="orongo_menu fixed hide"><div class="seperator right hide" style="padding-right: 100px"></div><div class="menu_text right hide">Settings</div><div class="icon_settings_small right hide"></div><div class="seperator right hide"></div><div class="menu_text right hide">Notifications</div><div class="icon_messages_small right hide"></div><div class="seperator right hide"></div><div class="menu_text right hide">Pages</div><div class="icon_pages_small right hide"></div><div class="seperator right hide"></div><div class="menu_text left hide" style="padding-left: 200px"><div class="icon_account_small left"></div> Logged in as ' . $user->getName() . ' | <a href="'. $website_url . 'orongo-logout.php">Logout</a></div></div>';
}


#   Generate HTML of the last 5 articles
$articles = array();
$lastID = Article::getLastArticleID();
$c = 0;
$q = "action=fetch&object=article&max=5&order=article.id,desc";
$articles = OrongoQueryHandler::exec(new OrongoQuery($q));
if((count($articles) < 1)){
   try{
       $article = Article::createArticle("Hello World!");
       $article->setContent("<p>Thank you for installing OrongoCMS!</p><p>To edit this simply delete it and create a new article or change this article.</p><br /><p>The OrongoCMS team</p>");
       $articles[0] = $article;
   }catch(Exception $e){ }
}

$articleHTML = "";
if($style->doArticleHTML()){
    try{
        $articleHTML = $style->getArticlesHTML($articles);
    }catch(Exception $e){
        $msgbox = new MessageBox("The style didn't generate the HTML code for the articles, therefore the default generator was used. <br /><br />To hide this message open <br />" . $style->getStylePath() . "info.xml<br /> and set <strong>own_article_html</strong> to <strong>false</strong>.");
        $msgbox->bindException($e);
        $errors .= $msgbox->toHTML();
        foreach($articles as $article){
            $articleHTML .= $article->toShortHTML();
        }
    }
}else{
    foreach($articles as $article){
        $articleHTML .= $article->toShortHTML();
    }
}

#   Template


#       Assigns
   
    #General
    $smarty->assign("website_url", $website_url);
    $smarty->assign("website_name", $website_name);
    
    $smarty->assign("head", $head);
    $smarty->assign("head_title", $website_name .= " - Home");
    
    $smarty->assign("document_ready", $document_ready);
    $smarty->assign("menu_bar", $menu_bar);
    $smarty->assign("menu", $menu);
    $smarty->assign("errors", $errors);
    
    $smarty->assign("articles", $articleHTML);
    
    #Plugins
    $smarty->assign("plugin_document_ready", $pluginHTML['javascript']['document_ready']);
    $smarty->assign("plugin_head", $pluginHTML['html']['head']);
    $smarty->assign("plugin_body", $pluginHTML['html']['body']);
    $smarty->assign("plugin_footer", $pluginHTML['html']['footer']);
    
#       Handle Style
$style->run($smarty);

#       Show
$smarty->display("header.orongo");
$smarty->display("index.orongo");
$smarty->display("footer.orongo");

//Debug lines
// TODO remove on release
$time_end = microtime(true);
$time = $time_end - $time_start;
echo "<br /><br />Execution time: " . $time;

?>
