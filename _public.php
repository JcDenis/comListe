<?php
/**
 * @brief comListe, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Benoit de Marne, Pierre Van Glabeke and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH')) {
    return null;
}

if (!dcCore::app()->blog->settings->get(basename(__DIR__))->get('enable')) {
    return null;
}

require __DIR__ . '/_widgets.php';

dcCore::app()->tpl->addValue('ComListeURL', ['tplComListe','comListeURL']);
dcCore::app()->tpl->addValue('ComListePageTitle', ['tplComListe','comListePageTitle']);
dcCore::app()->tpl->addValue('ComListeNbComments', ['tplComListe','comListeNbComments']);
dcCore::app()->tpl->addValue('ComListeNbCommentsPerPage', ['tplComListe','comListeNbCommentsPerPage']);
dcCore::app()->tpl->addBlock('ComListeCommentsEntries', ['tplComListe','comListeCommentsEntries']);
dcCore::app()->tpl->addValue('ComListePaginationLinks', ['tplComListe','comListePaginationLinks']);
dcCore::app()->tpl->addValue('ComListeOpenPostTitle', ['tplComListe','comListeOpenPostTitle']);

dcCore::app()->addBehaviors([
    'publicBreadcrumb' => function ($context, $separator) {
        if ($context == 'comListe') {
            return __('Comments list');
        }
    },
]);

class tplComListe
{
    public $html_prev = '&#171;prev.';
    public $html_next = 'next&#187;';

    /* ComListeURL --------------------------------------- */
    public static function comListeURL($attr)
    {
        $f = dcCore::app()->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'dcCore::app()->blog->url.dcCore::app()->url->getBase("comListe")') . '; ?>';
    }

    /* ComListePageTitle --------------------------------------- */
    public static function comListePageTitle($attr)
    {
        $f = dcCore::app()->tpl->getFilters($attr);

        return '<?php echo ' . sprintf($f, 'dcCore::app()->blog->settings->get("' . basename(__DIR__) . '")->get("page_title")') . '; ?>';
    }

    /* ComListeNbCommentsPerPage --------------------------------------- */
    public static function comListeNbCommentsPerPage($attr)
    {
        dcCore::app()->ctx->__set('nb_comment_per_page', (int) dcCore::app()->blog->settings->get(basename(__DIR__))->get('nb_comments_per_page'));

        return html::escapeHTML((string) dcCore::app()->ctx->__get('nb_comment_per_page'));
    }

    /* comListeNbComments --------------------------------------- */
    public static function comListeNbComments($attr)
    {
        if (!dcCore::app()->ctx->exists('pagination')) {
            dcCore::app()->ctx->__set('pagination', dcCore::app()->blog->getComments([], true));
        }
        $nb_comments = dcCore::app()->ctx->__get('pagination')->f(0);

        return html::escapeHTML((string) $nb_comments);
    }

    /* ComListeCommentsEntries --------------------------------------- */
    public static function comListeCommentsEntries($attr, $content)
    {
        $p = 'if (dcCore::app()->ctx->posts !== null) { ' .
            "\$params['post_id'] = dcCore::app()->ctx->posts->post_id; " .
            "dcCore::app()->blog->withoutPassword(false);\n" .
        "}\n";

        if (empty($attr['with_pings'])) {
            $p .= "\$params['comment_trackback'] = false;\n";
        }

        $lastn = 0;
        if (isset($attr['lastn'])) {
            $lastn = abs((int) $attr['lastn']) + 0;
        }

        if ($lastn > 0) {
            $p .= "\$params['limit'] = " . $lastn . ";\n";
        } else {
            $p .= "if (dcCore::app()->ctx->nb_comment_per_page !== null) { \$params['limit'] = dcCore::app()->ctx->nb_comment_per_page; }\n";
        }

        $p .= "\$params['limit'] = array(((dcCore::app()->public->getPageNumber()-1)*\$params['limit']),\$params['limit']);\n";

        if (empty($attr['no_context'])) {
            $p .= 'if (dcCore::app()->ctx->exists("categories")) { ' .
                "\$params['cat_id'] = dcCore::app()->ctx->categories->cat_id; " .
            "}\n";

            $p .= 'if (dcCore::app()->ctx->exists("langs")) { ' .
                "\$params['sql'] = \"AND P.post_lang = '\".dcCore::app()->blog->con->escape(dcCore::app()->langs->post_lang).\"' \"; " .
            "}\n";
        }

        // Sens de tri issu des paramètres du plugin
        $order = dcCore::app()->blog->settings->get(basename(__DIR__))->get('comments_order');
        if (isset($attr['order']) && preg_match('/^(desc|asc)$/i', $attr['order'])) {
            $order = $attr['order'];
        }

        $p .= "\$params['order'] = 'comment_dt " . $order . "';\n";

        if (isset($attr['no_content']) && $attr['no_content']) {
            $p .= "\$params['no_content'] = true;\n";
        }

        $res = "<?php\n";
        $res .= $p;
        $res .= 'dcCore::app()->ctx->comments = dcCore::app()->blog->getComments($params); unset($params);' . "\n";
        $res .= "if (dcCore::app()->ctx->posts !== null) { dcCore::app()->blog->withoutPassword(true);}\n";

        if (!empty($attr['with_pings'])) {
            $res .= 'dcCore::app()->ctx->pings = dcCore::app()->ctx->comments;' . "\n";
        }

        $res .= "?>\n";

        $res .= '<?php while (dcCore::app()->ctx->comments->fetch()) : ?>' . $content . '<?php endwhile; dcCore::app()->ctx->pop("comments"); ?>';

        return $res;
    }

    /* ComListePaginationLinks --------------------------------------- */
    /* Reprise et adaptation de la fonction PaginationLinks du plugin advancedPagination-1.9 */
    public static function comListePaginationLinks($attr)
    {
        $p = '<?php

        function comListeMakePageLink($pageNumber, $linkText) {
            if ($pageNumber != dcCore::app()->public->getPageNumber()) { 
                $args = $_SERVER["URL_REQUEST_PART"]; 
                $args = preg_replace("#(^|/)page/([0-9]+)$#","",$args); 
                $url = dcCore::app()->blog->url.$args; 

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

        $current = dcCore::app()->public->getPageNumber();
        
        if(empty($params)) {
            dcCore::app()->ctx->pagination = dcCore::app()->blog->getComments(null,true);
        } else {
            dcCore::app()->ctx->pagination = dcCore::app()->blog->getComments($params,true); 
            unset($params);
        }       
        
        if (dcCore::app()->ctx->exists("pagination")) { 
            $nb_comments = dcCore::app()->ctx->pagination->f(0); 
        } 
    
        $nb_per_page = abs((integer) dcCore::app()->blog->settings->get("' . basename(__DIR__) . '")->get("nb_comments_per_page"));
        $nb_pages = ceil($nb_comments/$nb_per_page);
        $nb_max_pages = 10;
        $nb_sequence = 2*3+1;
        $quick_distance = 10;

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
}
