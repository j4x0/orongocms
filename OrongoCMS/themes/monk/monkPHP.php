<?php
/**
 * http://www.makimyers.co.uk/monk-free-html-template/
 * 
 * Monk Style needed to generate own Article HTML ;)
 * @author Jaco Ruit
 */

class MonkStyle implements IOrongoStyle{
    
    public function __construct(){}
    
    public function run(){
        $settings = Style::getSettings();
        
        //Reverse the Menu, we've right float
        getMenu()->flip();
        
        foreach($settings as $setting=>$value){
            if($value != null)
                getDisplay()->setTemplateVariable($settings,$value);
        }
    }
    
    public function getArticlesHTML($paramArticles){ 
        $generatedHTML = "";
        $curPage = getCurrentPage();
        if(is_array($paramArticles) == false) return null; //Sup, Orongo? U nooo pass me an array :(
        
        $count = count($paramArticles);
        if($count < 1) return "<p>No articles we're found</p>";
        $generatedCount = 0;
        foreach($paramArticles as $article){
            $last = false;
            if(($article instanceof Article) == false) continue;
            $generatedCount++;
            if($generatedCount == 4 && $curPage == 'index') $last = true; 
            if(is_int($generatedCount / 4) && $curPage == 'archive') $last = true;
            if($curPage == 'archive' && $last == false && $generatedCount == count($paramArticles)) $last = true;
            $generatedHTML .= '<div class="one_fourth ';
            if($last) $generatedHTML .= 'column-last';
            $generatedHTML .= ' ">';
            $generatedHTML .= '<a href="'. Settings::getWebsiteURL() . 'article.php?id=' . $article->getID() . '"><h3>' . $article->getTitle() . '</h3></a>';
            $generatedHTML .= '<p>' . substr(strip_tags($article->getContent()), 0 ,500) . '</p>';
            $generatedHTML .= '</div>';
            if($last && $curPage == 'index' ) break;
        }
        
        return $generatedHTML;
    }
    
    public function getCommentsHTML($paramComments) {
        if(count($paramComments) < 1) return "<p>No comments, be the first to comment!</p>";
        $generatedHTML = "";
        foreach($paramComments as $comment){
            if(($comment instanceof Comment) == false) continue;
            $generatedHTML .= '<div class="comment">';
            $generatedHTML .= '<p>Comment by ' . $comment->getAuthorName() . ' - ' . date("Y-m-d H:i:s", $comment->getTimestamp() ) . '</p>';
            $generatedHTML .= '<p>' . $comment->getContent() . '</p>';
            $generatedHTML .= '</div>';
        }
        return $generatedHTML;
    }
}
?>
