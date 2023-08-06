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
declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use ArrayObject;
use dcCore;
use Dotclear\Helper\Html\Html;

class Template
{
    public string $html_prev = '&#171;prev.';
    public string $html_next = 'next&#187;';

    /* ComListeURL --------------------------------------- */
    public static function comListeURL(ArrayObject $attr): string
    {
        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->url.dcCore::app()->url->getBase("comListe")') . '; ?>';
    }

    /* ComListePageTitle --------------------------------------- */
    public static function comListePageTitle(ArrayObject $attr): string
    {
        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->settings->get("' . My::id() . '")->get("page_title")') . '; ?>';
    }

    /* ComListeNbCommentsPerPage --------------------------------------- */
    public static function comListeNbCommentsPerPage(ArrayObject $attr): string
    {
        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->ctx)) {
            return '10';
        }
        dcCore::app()->ctx->__set('nb_comment_per_page', (int) My::settings()->get('nb_comments_per_page'));

        return Html::escapeHTML((string) dcCore::app()->ctx->__get('nb_comment_per_page'));
    }

    /* comListeNbComments --------------------------------------- */
    public static function comListeNbComments(ArrayObject$attr): string
    {
        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->ctx)) {
            return '0';
        }
        if (!dcCore::app()->ctx->exists('pagination')) {
            dcCore::app()->ctx->__set('pagination', dcCore::app()->blog->getComments([], true));
        }
        $nb_comments = dcCore::app()->ctx->__get('pagination')->f(0);

        return Html::escapeHTML((string) $nb_comments);
    }

    /* ComListeCommentsEntries --------------------------------------- */
    public static function comListeCommentsEntries(ArrayObject $attr, string $content): string
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
                "\$params['sql'] = \"AND P.post_lang = '\".dcCore::app()->blog->con->escapeStr((string) dcCore::app()->langs->post_lang).\"' \"; " .
            "}\n";
        }

        // Sens de tri issu des paramètres du plugin
        $order = is_null(dcCore::app()->blog) ? 'desc' : My::settings()->get('comments_order');
        if (isset($attr['order']) && preg_match('/^(desc|asc)$/i', $attr['order'])) {
            $order = $attr['order'];
        }

        $p .= "\$params['order'] = 'comment_dt " . ($order ?? 'desc') . "';\n";

        if (isset($attr['no_content']) && $attr['no_content']) {
            $p .= "\$params['no_content'] = true;\n";
        }

        $res = "<?php\n";
        $res .= $p;
        $res .= 'dcCore::app()->ctx->comments_params = $params; ';
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
    public static function comListePaginationLinks(ArrayObject $attr): string
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
    
        $nb_per_page = abs((integer) dcCore::app()->blog->settings->get("' . My::id() . '")->get("nb_comments_per_page"));
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
    public static function comListeOpenPostTitle(ArrayObject $attr): string
    {
        return __('open post');
    }

    public static function comListeCommentOrderNumber(ArrayObject $attr): string
    {
        return
            '<?php echo ' .
            'dcCore::app()->ctx->comments->index() + 1 +' .
            '(dcCore::app()->public->getPageNumber() - 1) * ' .
            'abs((integer) dcCore::app()->blog->settings->get("' . My::id() . '")->get("nb_comments_per_page"));' .
            '?>';
    }

    public static function comListePagination(ArrayObject $attr, string $content): string
    {
        $params = "<?php\n" .
            '$params = dcCore::app()->ctx->comments_params;' . "\n" .
            dcCore::app()->callBehavior(
                'templatePrepareParams',
                [
                    'tag'    => 'Pagination',
                    'method' => 'comListe::getComments',
                ],
                $attr,
                $content
            ) .
            'dcCore::app()->ctx->pagination = dcCore::app()->blog->getComments($params,true); unset($params);' . "\n" .
            "?>\n";

        if (isset($attr['no_context']) && $attr['no_context']) {
            return $params . $content;
        }

        return
            "<?php\n" .
            '$bakcup_old_nbpp = dcCore::app()->ctx->nb_entry_per_page; ' . "\n" .
            'dcCore::app()->ctx->nb_entry_per_page = abs((integer) dcCore::app()->blog->settings->get("' . My::id() . '")->get("nb_comments_per_page"));' . "\n" .
            "?>\n" .
            $params .
            '<?php if (dcCore::app()->ctx->pagination->f(0) > dcCore::app()->ctx->comments->count()) : ?>' .
            $content .
            "<?php endif;\n" .
            'dcCore::app()->ctx->nb_entry_per_page = $bakcup_old_nbpp; ' . "\n" .
            '?>';
    }

    public static function comListePaginationCounter(ArrayObject $attr): string
    {
        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx::PaginationNbPages()') . '; ?>';
    }

    public static function comListePaginationCurrent(ArrayObject $attr): string
    {
        $offset = isset($attr['offset']) ? (int) $attr['offset'] : 0;

        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx::PaginationPosition(' . $offset . ')') . '; ?>';
    }

    public static function comListePaginationIf(ArrayObject $attr, string $content): string
    {
        $if = [];

        if (isset($attr['start'])) {
            $sign = (bool) $attr['start'] ? '' : '!';
            $if[] = $sign . 'dcCore::app()->ctx::PaginationStart()';
        }

        if (isset($attr['end'])) {
            $sign = (bool) $attr['end'] ? '' : '!';
            $if[] = $sign . 'dcCore::app()->ctx::PaginationEnd()';
        }

        if (count($if)) {
            return '<?php if(' . implode(' && ', $if) . ') : ?>' . $content . '<?php endif; ?>';
        }

        return $content;
    }

    public static function comListePaginationURL(ArrayObject $attr): string
    {
        $offset = 0;
        if (isset($attr['offset'])) {
            $offset = (int) $attr['offset'];
        }

        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx::PaginationURL(' . $offset . ')') . '; ?>';
    }
}
