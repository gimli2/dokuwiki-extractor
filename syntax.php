<?php
/**
 * Plugin Extractor: Take parts from another pages"
 *
 * Syntax: <TEST> - will be replaced with "Hello World!"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gimli2 <gimli2{at}gmail.com>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_extractor extends DokuWiki_Syntax_Plugin {
 
 
 
   /**
    * Get the type of syntax this plugin defines.
    *
    * @param none
    * @return String <tt>'substition'</tt> (i.e. 'substitution').
    * @public
    * @static
    */
    function getType(){
        return 'substition';
    }
 
    /**
     * What kind of syntax do we allow (optional)
     */
//    function getAllowedTypes() {
//        return array();
//    }

    /**
    * return some info
    */
    function getInfo() {
        return array(
            'base'   => 'extractor',
            'author' => 'Gimli2',
            'email'  => 'gimli2{at}gmail.com',
            'date'   => '2014-08-29',
            'name'   => 'extractor plugin',
            'desc'   => 'Extract parts of another wiki pages',
            'url'    => 'http://dokuwiki.org/plugin:extractor',
        );
    }

   /**
    * Define how this plugin is handled regarding paragraphs.
    *
    * <p>
    * This method is important for correct XHTML nesting. It returns
    * one of the following values:
    * </p>
    * <dl>
    * <dt>normal</dt><dd>The plugin can be used inside paragraphs.</dd>
    * <dt>block</dt><dd>Open paragraphs need to be closed before
    * plugin output.</dd>
    * <dt>stack</dt><dd>Special case: Plugin wraps other paragraphs.</dd>
    * </dl>
    * @param none
    * @return String <tt>'block'</tt>.
    * @public
    * @static
    */
    function getPType(){
        return 'normal';
    }
 
   /**
    * Where to sort in?
    *
    * @param none
    * @return Integer <tt>6</tt>.
    * @public
    * @static
    */
    function getSort(){
        return 100;
    }
 
 
   /**
    * Connect lookup pattern to lexer.
    *
    * @param $aMode String The desired rendermode.
    * @return none
    * @public
    * @see render()
    */
    function connectTo($mode) {
      $this->Lexer->addSpecialPattern('{{extractor>.+?}}',$mode,'plugin_extractor');
//      $this->Lexer->addEntryPattern('<TEST>',$mode,'plugin_test');
    }
 
//    function postConnect() {
//      $this->Lexer->addExitPattern('</TEST>','plugin_test');
//    }
 
 
   /**
    * Handler to prepare matched data for the rendering process.
    *
    * <p>
    * The <tt>$aState</tt> parameter gives the type of pattern
    * which triggered the call to this method:
    * </p>
    * <dl>
    * <dt>DOKU_LEXER_ENTER</dt>
    * <dd>a pattern set by <tt>addEntryPattern()</tt></dd>
    * <dt>DOKU_LEXER_MATCHED</dt>
    * <dd>a pattern set by <tt>addPattern()</tt></dd>
    * <dt>DOKU_LEXER_EXIT</dt>
    * <dd> a pattern set by <tt>addExitPattern()</tt></dd>
    * <dt>DOKU_LEXER_SPECIAL</dt>
    * <dd>a pattern set by <tt>addSpecialPattern()</tt></dd>
    * <dt>DOKU_LEXER_UNMATCHED</dt>
    * <dd>ordinary text encountered within the plugin's syntax mode
    * which doesn't match any pattern.</dd>
    * </dl>
    * @param $aMatch String The text matched by the patterns.
    * @param $aState Integer The lexer state for the match.
    * @param $aPos Integer The character position of the matched text.
    * @param $aHandler Object Reference to the Doku_Handler object.
    * @return Integer The current lexer state for the match.
    * @public
    * @see render()
    * @static
    */
    function handle($match, $state, $pos, Doku_Handler $handler){

        //echo '<pre>';

        preg_match('~^{{extractor>(.+?)}}$~si', $match, $m);
        $params = $m[1];
        $params = explode('&', $params);
        $cnf = array();
        foreach ($params as $key=>$value) {
          // namespace
        	if (preg_match('~^ns=(.+)$~si', $value, $m)) {
            $cnf['ns'] = $m[1];
          }
          // exclude
          if (preg_match('~^exclude=(.+)$~si', $value, $m)) {
            $cnf['exclude'] = explode(',',$m[1]);
          }

          // section
        	if (preg_match('~^section=(.+)$~si', $value, $m)) {
            $cnf['section'] = $m[1];
          }
          // templater sort key
        	if (preg_match('~^templaterSortKey=(.+)$~si', $value, $m)) {
            $cnf['sortkey'] = $m[1];
          }
          // take title from templater key
        	if (preg_match('~^templaterTitle=(.+)$~si', $value, $m)) {
            $cnf['titlekey'] = $m[1];
          }

          // flags
        	if (preg_match('~^noemptywarning$~si', $value, $m)) {
             $cnf['flags'][] = $m[0];
          }
          if (preg_match('~^noeditlinks$~si', $value, $m)) {
             $cnf['flags'][] = $m[0];
          }

        }

        //print_r($cnf);
        //echo '</pre>';
        
        switch ($state) {
          case DOKU_LEXER_ENTER : 
            break;
          case DOKU_LEXER_MATCHED :
            break;
          case DOKU_LEXER_UNMATCHED :
            break;
          case DOKU_LEXER_EXIT :
            break;
          case DOKU_LEXER_SPECIAL :
            break;
        }
        return array($cnf);
    }
 
   /**
    * Handle the actual output creation.
    *
    * <p>
    * The method checks for the given <tt>$aFormat</tt> and returns
    * <tt>FALSE</tt> when a format isn't supported. <tt>$aRenderer</tt>
    * contains a reference to the renderer object which is currently
    * handling the rendering. The contents of <tt>$aData</tt> is the
    * return value of the <tt>handle()</tt> method.
    * </p>
    * @param $aFormat String The output format to generate.
    * @param $aRenderer Object A reference to the renderer object.
    * @param $aData Array The data created by the <tt>handle()</tt>
    * method.
    * @return Boolean <tt>TRUE</tt> if rendered successfully, or
    * <tt>FALSE</tt> otherwise.
    * @public
    * @see handle()
    */
    function render($mode, Doku_Renderer $renderer, $data) {
        global $conf;
        $cnf = $data[0];
        
        $renderer->doc .= '<div class="wrap_em">';
        $renderer->doc .= "This page has generated content!<br />";
        $renderer->doc .= "Vyrané sekce <strong>".$cnf['section']."</strong> z namespace: <strong>".$cnf['ns']."</strong><br />";
        $renderer->doc .= "Plugin conf = ".print_r($cnf,true)."<br />";
        $renderer->doc .= '</div>';

        $pages = $this->_searchPagesInNS($cnf);
        $page = $pages[0];
        sort($pages);

        //$pages = array($pages[0], $pages[1]);
        $processed = array();
        $uniqcnt = 0;
        foreach ($pages as $key=>$page) {
          // exclude pages
          $basename = substr($page, strrpos($page, ':')+1);
          if (in_array($basename, $cnf['exclude'])) continue;
          // process page
          $meta = p_get_metadata($page);
          $ret = $this->_getSectionFromPage($page, $cnf, $meta);
          $processed[$ret['sort'].'_'.$uniqcnt] = $ret['content'];
          $uniqcnt++;
        }
        ksort($processed);
        /*
        echo '<pre>';
        print_r (array_keys($processed));
        echo '</pre>';
        */
        foreach ($processed as $key=>$value) {
          $renderer->nest($value);
        }
        
        return true;
    }

    function _getSectionFromPage($page, $cnf, $meta) {
      global $conf, $ID;
      if (page_exists($page)) {
        $backupID = $ID;
        $ID = $page; // Change the global $ID as otherwise plugins like the discussion plugin will save data for the wrong page
        $ins = p_cached_instructions(wikiFN($page), false, $page);
        $ID = $backupID;
      } else {
        $ins = array();
      }

      $kw = strtolower($cnf['section']);
      $sortkey = strtolower($cnf['sortkey']);
      $titlekey = strtolower($cnf['titlekey']);

      $found = false;
      $lvl = 0;
      $from = false;
      $end = false;
      if (is_array($ins)) {
        foreach ($ins as $key=>$value) {
          $headertitle = strtolower($value[1][0]);
          if ($value[0] == 'header' && $headertitle == $kw ) {
            $found = true;
            $from = $key;
            $lvl = $value[1][1];
          } elseif ($value[0] == 'header' && $found &&  $value[1][1] <= $lvl) {
            // dalsi header vyssi nebo stejne urovne (mansi cislo) nam ukoncuje tento
            $end = $key - $from;
            break;
          } elseif ($value[0] == 'plugin' && $value[1][0] == 'templater') {
            $keys = $value[1][1][1]['keys'];
            $keys = array_flip($keys);
            array_map(strtolower, $keys);
            $meta['sort'] = $value[1][1][1]['vals'][$keys['@'.$sortkey.'@']];
            $meta['titleOverride'] = $value[1][1][1]['vals'][$keys['@'.$titlekey.'@']];
          }
          // replace original section title if forced
          if ($titlekey != '' && $meta['titleOverride'] != '') {
            $meta['title'] = $meta['titleOverride'];
          }
          // TODO: probably problem when header is before plugin templater
          if ($value[0] == 'header') {
            $ins[$key][1][0] = ''.$meta['title'].' ('.$meta['sort'].')';
          }
        }
      }
      /*
      echo '<pre>';
      print_r($ins);
      echo '</pre>';
      */
      // pokud se sekce nenasla, vratime warning s odkazem na stranku a moznost opravy
      $warningblock = array(
        0 => array(
          0 => 'header',
          1 => array(
            0 => $meta['title'].' ('.prettyprint_id($page).') -> chybí sekce '.$kw.'!',
            1 => '2',
            2 => null
          ),
          2 => null
        ),
        1 => array ( 0 => section_open, 1 => array(0=>2), 2 => null),
        2 => array(
          0 => 'internallink',
          1 => array(
            0 => $page,
            1 => 'Přejít na stránku '.$meta['title'].' a opravit problém!'
          ),
          2 => null
        ),
        3 => array ( 0 => section_close, 1 => array(0=>2), 2 => null),
      );
      // if command doesn't suppress edit links, append them
      if (is_array($cnf['flags']) && in_array('noemptywarning', $cnf['flags'])) {
        $warningblock = array();
      }
      if (!$found) {
        return array(
          'sort' => $meta['sort'],
          'content' => $warningblock
        );
      }

      $from = $from ? $from : 0;
      $end = $end ? $end : (count($ins)-1);
      //echo "$from $end";
      $ins = array_slice($ins, $from, $end);

      $editblock = array(
        0 => array ( 0 => section_open, 1 => array(0=>2), 2 => null),
        1 => array(
          0 => 'internallink',
          1 => array(
            0 => $page,
            1 => 'Přejít na stránku '.$meta['title'].' a upravit sekci '.$kw.'.'
          ),
          2 => null
        ),
        2 => array ( 0 => section_close, 1 => array(0=>2), 2 => null),
      );
      // if command doesn't suppress edit links, append them
      if (is_array($cnf['flags']) && !in_array('noeditlinks', $cnf['flags'])) {
        foreach ($editblock as $key=>$value) {
          $ins[] = $value;
        }
      }

      return array(
        'sort' => $meta['sort'],
        'content' => $ins
      );
    }

    function _searchPagesInNS($cnf) {
      global $conf;
      $page  = cleanID($cnf['ns']);
      $ns    = utf8_encodeFN(str_replace(':', '/', $page));
      $pagearrays = array();
      // depth == 0 ... unlimited
      search($pagearrays, $conf['datadir'], search_allpages, array('depth' => 0), $ns);
      if (is_array($pagearrays)) {
          foreach ($pagearrays as $pagearray) {
            if (!isHiddenPage($pagearray['id'])) // skip hidden pages
                $pages[] = $pagearray['id'];
          }
      }
      return $pages;
    }
}
?>