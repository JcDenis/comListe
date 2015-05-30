<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of comListe, a plugin for Dotclear.
# 
# Copyright (c) 2008-2015 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {return;}

class urlcomListe extends dcUrlHandlers
{
	/**
	 * Traitement de l'URL
	 * Open the template
	 */
	public static function comListe($args)
	{
		global $_ctx,$core;
		
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->comListe;
		} else {
			$blog_settings =& $core->blog->settings;
		}		

		// definition de la page courante
		if ($args == '') {
			$GLOBALS['_page_number'] = (integer) 1;
		} else {
			$current = self::getPageNumber($args);
			if ($current) {
				$GLOBALS['_page_number'] = (integer) $current;
			}
		}
		// definition du nombre de commentaires par page
		$_ctx->nb_comment_per_page=$blog_settings->comliste_nb_comments_per_page;

		// ouverture de la page html
  $tplset = $core->themes->moduleInfo($core->blog->settings->system->theme,'tplset');
        if (!empty($tplset) && is_dir(dirname(__FILE__).'/../default-templates/'.$tplset)) {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates/'.$tplset);
        } else {
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates/'.DC_DEFAULT_TPLSET);
        }
		self::serveDocument('comListe.html');
		exit;
	}
}

class tplComListe
{
	public $html_prev = '&#171;prev.';
	public $html_next = 'next&#187;';

	/* ComListeURL --------------------------------------- */
	public static function comListeURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("comListe")').'; ?>';
	}
	
	/* ComListePageTitle --------------------------------------- */
	public static function comListePageTitle($attr)
	{
		global $core;
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			return '<?php echo '.sprintf($f,'$core->blog->settings->comListe->comliste_page_title').'; ?>';
		} else {
			return '<?php echo '.sprintf($f,'$core->blog->settings->comliste_page_title').'; ?>';
		}		
	}

	/* ComListeNbCommentsPerPage --------------------------------------- */
	public static function comListeNbCommentsPerPage($attr)
	{
		global $_ctx, $core;

		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$nb_comments_per_page = $_ctx->nb_comment_per_page=$core->blog->settings->comListe->comliste_nb_comments_per_page;
		} else {
			$nb_comments_per_page = $_ctx->nb_comment_per_page=$core->blog->settings->comliste_nb_comments_per_page;
		}		
		return ''.html::escapeHTML($nb_comments_per_page).'';
	}

	/* comListeNbComments --------------------------------------- */
	public static function comListeNbComments($attr)
	{
		// __('Number of comments')
		global $_ctx, $core;
		
		if(empty($params)) {
			$_ctx->pagination = $core->blog->getComments(null,true);
		} else {
			$_ctx->pagination = $core->blog->getComments($params,true);
			unset($params);
		}
		
		if ($_ctx->exists("pagination")) { 
			$nb_comments = $_ctx->pagination->f(0); 
		}	
		return ''.html::escapeHTML($nb_comments).'';
	}
	
	/* ComListeCommentsEntries --------------------------------------- */
	public static function comListeCommentsEntries($attr,$content)
	{
		global $_ctx, $core;

		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->comListe;
		} else {
			$blog_settings =& $core->blog->settings;
		}		
		
		$p =
		"if (\$_ctx->posts !== null) { ".
			"\$params['post_id'] = \$_ctx->posts->post_id; ".
			"\$core->blog->withoutPassword(false);\n".
		"}\n";
		
		if (empty($attr['with_pings'])) {
			$p .= "\$params['comment_trackback'] = false;\n";
		}
		
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_comment_per_page !== null) { \$params['limit'] = \$_ctx->nb_comment_per_page; }\n";
		}

		if (isset($GLOBALS["_page_number"])) { 
	   		$_page_number = $GLOBALS["_page_number"]; 
			$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		} else { 
			$_page_number = 1; 
			$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['sql'] = \"AND P.post_lang = '\".\$core->blog->con->escape(\$_ctx->langs->post_lang).\"' \"; ".
			"}\n";
		}
		
		// Sens de tri issu des paramètres du plugin
		$order = $blog_settings->comliste_comments_order;
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}		
		
		$p .= "\$params['order'] = 'comment_dt ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->comments = $core->blog->getComments($params); unset($params);'."\n";
		$res .= "if (\$_ctx->posts !== null) { \$core->blog->withoutPassword(true);}\n";
		
		if (!empty($attr['with_pings'])) {
			$res .= '$_ctx->pings = $_ctx->comments;'."\n";
		}
		
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->comments->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->comments = null; ?>';

		return $res;
	}	

	/* ComListePaginationLinks --------------------------------------- */
	/* Reprise et adaptation de la fonction PaginationLinks du plugin advancedPagination-1.9 */
	public static function comListePaginationLinks($attr)
	{
		global $_ctx, $core;
		
		$p = '<?php

		function comListeMakePageLink($pageNumber, $linkText) {
			if (isset($GLOBALS["_page_number"])) { 
				$current = $GLOBALS["_page_number"]; 
			} else { 
				$current = 0; 
			} 
			if ($pageNumber != $current) { 
				$args = $_SERVER["URL_REQUEST_PART"]; 
				$args = preg_replace("#(^|/)page/([0-9]+)$#","",$args); 
				$url = $GLOBALS["core"]->blog->url.$args; 

				if ($pageNumber > 1) { 
					$url = preg_replace("#/$#","",$url); 
					$url .= "/page/".$pageNumber; 
				} 
				
				if (!empty($_GET["q"])) { 
					$s = strpos($url,"?") !== false ? "&amp;" : "?"; 
					$url .= $s."q=".rawurlencode($_GET["q"]); 
				} 
				
				return "<a href=\"".$url."\">".$linkText."</a>&nbsp;";
			} else { 
				return $linkText."&nbsp;";
			} 
		}

		if (isset($GLOBALS["_page_number"])) { 
			$current = $GLOBALS["_page_number"]; 
		} else { 
			$current = 1; 
		}
	    
		if(empty($params)) {
			$_ctx->pagination = $core->blog->getComments(null,true);
		} else {
			$_ctx->pagination = $core->blog->getComments($params,true); 
			unset($params);
		}		
		
		if ($_ctx->exists("pagination")) { 
			$nb_comments = $_ctx->pagination->f(0); 
		} 

		# Settings compatibility test
		if (version_compare(DC_VERSION,\'2.2-alpha\',\'>=\')) {
			$blog_settings =& $core->blog->settings->comListe;
		} else {
			$blog_settings =& $core->blog->settings;
		}		
				
		$nb_per_page = abs((integer) $blog_settings->comliste_nb_comments_per_page);
		$nb_pages = ceil($nb_comments/$nb_per_page);
		$nb_max_pages = 10;
		$nb_sequence = 2*3+1;
		$quick_distance = 10;

		echo "Pages&nbsp;:&nbsp;";
		if($nb_pages <= $nb_max_pages) {
			/* less or equal than 10 pages, simple links */
			for ($i = 1; $i <= $nb_pages; $i++) { 
				echo comListeMakePageLink($i,$i);
			}
		} else { 
			/* more than 10 pages, smart links */
			echo comListeMakePageLink(1,1);
			$min_page = max($current - ($nb_sequence - 1) / 2, 2); 
			$max_page = min($current + ($nb_sequence - 1) / 2, $nb_pages - 1); 
			if ($min_page > 2) {
  				echo "..."; 
				echo "&nbsp;";
			}
			
			for ($i = $min_page; $i <= $max_page ; $i++) { 
				echo comListeMakePageLink($i,$i); 
			} 
			
			if ($max_page < $nb_pages - 1) {
				echo "...";
				echo "&nbsp;";
			}
			echo comListeMakePageLink($nb_pages,$nb_pages);

			/* quick navigation links */
			if($current >= 1 + $quick_distance) {
				echo "&nbsp;";
				echo comListeMakePageLink($current - $quick_distance, "<<");
			}
			
			if($current <= $nb_pages - $quick_distance) {
				echo "&nbsp;";
				echo comListeMakePageLink($current + $quick_distance, ">> ");
			}
		} 
		?>';
		
		return $p;
	}

	/* ComListeOpenPostTitle --------------------------------------- */
	public static function comListeOpenPostTitle($attr)
	{
		return __('open post');
	}

	# Widget function
	public static function comListeWidget($w)
	{
		global $core;
		
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->comListe;
		} else {
			$blog_settings =& $core->blog->settings;
		}

		if ($w->offline)
			return;
		
		if (($w->homeonly == 1 && $core->url->type != 'default') ||
			($w->homeonly == 2 && $core->url->type == 'default')) {
			return;
		}
		if (!$blog_settings->comliste_enable) {
			return;
		}

		$res =
		($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '').
		'<p><a href="'.$core->blog->url.$core->url->getBase('comListe').'">'.
		($w->link_title ? html::escapeHTML($w->link_title) : __('List of comments')).
		'</a></p>';

		return $w->renderDiv($w->content_only,'comliste '.$w->class,'',$res);
	}	

}