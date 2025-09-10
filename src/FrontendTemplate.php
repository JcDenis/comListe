<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use ArrayObject;
use Dotclear\App;
use Dotclear\Helper\Html\Html;

/**
 * @brief       comListe frontend template class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class FrontendTemplate
{
    public string $html_prev = '&#171;prev.';
    public string $html_next = 'next&#187;';

    /**
     * comListeURL.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListeURL(ArrayObject $attr): string
    {
        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::blog()->url.App::url()->getBase("comListe")') . '; ?>';
    }

    /**
     * comListePageTitle.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListePageTitle(ArrayObject $attr): string
    {
        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::blog()->settings()->get("' . My::id() . '")->get("page_title")') . '; ?>';
    }

    /**
     * comListeNbCommentsPerPage.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListeNbCommentsPerPage(ArrayObject $attr): string
    {
        if (!App::blog()->isDefined()) {
            return '10';
        }
        App::frontend()->context()->__set('nb_comment_per_page', (int) My::settings()->get('nb_comments_per_page'));

        return Html::escapeHTML((string) App::frontend()->context()->__get('nb_comment_per_page'));
    }

    /**
     * comListeNbComments.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListeNbComments(ArrayObject$attr): string
    {
        if (!App::blog()->isDefined()) {
            return '0';
        }
        if (!App::frontend()->context()->exists('pagination')) {
            App::frontend()->context()->__set('pagination', App::blog()->getComments([], true));
        }
        $nb_comments = App::frontend()->context()->__get('pagination')->f(0);

        return Html::escapeHTML((string) $nb_comments);
    }

    /**
     * comListeCommentsEntries.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListeCommentsEntries(ArrayObject $attr, string $content): string
    {
        $p = 'if (App::frontend()->context()->posts !== null) { ' .
            "\$params['post_id'] = App::frontend()->context()->posts->post_id; " .
            "App::blog()->withoutPassword(false);\n" .
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
            $p .= "if (App::frontend()->context()->nb_comment_per_page !== null) { \$params['limit'] = App::frontend()->context()->nb_comment_per_page; }\n";
        }

        $p .= "\$params['limit'] = array(((App::frontend()->getPageNumber()-1)*\$params['limit']),\$params['limit']);\n";

        if (empty($attr['no_context'])) {
            $p .= 'if (App::frontend()->context()->exists("categories")) { ' .
                "\$params['cat_id'] = App::frontend()->context()->categories->cat_id; " .
            "}\n";

            $p .= 'if (App::frontend()->context()->exists("langs")) { ' .
                "\$params['sql'] = \"AND P.post_lang = '\".App::db()->con()->escapeStr((string) App::frontend()->context()->langs->post_lang).\"' \"; " .
            "}\n";
        }

        // Sens de tri issu des paramètres du plugin
        $order = !App::blog()->isDefined() ? 'desc' : My::settings()->get('comments_order');
        if (isset($attr['order']) && preg_match('/^(desc|asc)$/i', $attr['order'])) {
            $order = $attr['order'];
        }

        $p .= "\$params['order'] = 'comment_dt " . ($order ?? 'desc') . "';\n";

        if (isset($attr['no_content']) && $attr['no_content']) {
            $p .= "\$params['no_content'] = true;\n";
        }

        $res = "<?php\n";
        $res .= $p;
        $res .= 'App::frontend()->context()->comments_params = $params; ';
        $res .= 'App::frontend()->context()->comments = App::blog()->getComments($params); unset($params);' . "\n";
        $res .= "if (App::frontend()->context()->posts !== null) { App::blog()->withoutPassword(true);}\n";

        if (!empty($attr['with_pings'])) {
            $res .= 'App::frontend()->context()->pings = App::frontend()->context()->comments;' . "\n";
        }

        $res .= "?>\n";

        $res .= '<?php while (App::frontend()->context()->comments->fetch()) : ?>' . $content . '<?php endwhile; App::frontend()->context()->pop("comments"); ?>';

        return $res;
    }

    /**
     * comListePaginationLinks.
     *
     * Reprise et adaptation de la fonction PaginationLinks du plugin advancedPagination-1.9
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListePaginationLinks(ArrayObject $attr): string
    {
        $p = '<?php

        function comListeMakePageLink($pageNumber, $linkText) {
            if ($pageNumber != App::frontend()->getPageNumber()) { 
                $args = $_SERVER["URL_REQUEST_PART"]; 
                $args = preg_replace("#(^|/)page/([0-9]+)$#","",$args); 
                $url = App::blog()->url().$args; 

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

        $current = App::frontend()->getPageNumber();
        
        if(empty($params)) {
            App::frontend()->context()->pagination = App::blog()->getComments(null,true);
        } else {
            App::frontend()->context()->pagination = App::blog()->getComments($params,true); 
            unset($params);
        }       
        
        if (App::frontend()->context()->exists("pagination")) { 
            $nb_comments = App::frontend()->context()->pagination->f(0); 
        } 
    
        $nb_per_page = abs((integer) App::blog()->settings->get("' . My::id() . '")->get("nb_comments_per_page"));
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

    /**
     * comListeOpenPostTitle.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListeOpenPostTitle(ArrayObject $attr): string
    {
        return __('open post');
    }

    /**
     * comListeCommentOrderNumber.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListeCommentOrderNumber(ArrayObject $attr): string
    {
        return
            '<?php echo ' .
            'App::frontend()->context()->comments->index() + 1 +' .
            '(App::frontend()->getPageNumber() - 1) * ' .
            'abs((integer) App::blog()->settings()->get("' . My::id() . '")->get("nb_comments_per_page"));' .
            '?>';
    }

    /**
     * comListePagination.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListePagination(ArrayObject $attr, string $content): string
    {
        $params = "<?php\n" .
            '$params = App::frontend()->context()->comments_params;' . "\n" .
            App::behavior()->callBehavior(
                'templatePrepareParams',
                [
                    'tag'    => 'Pagination',
                    'method' => 'comListe::getComments',
                ],
                $attr,
                $content
            ) .
            'App::frontend()->context()->pagination = App::blog()->getComments($params,true); unset($params);' . "\n" .
            "?>\n";

        if (isset($attr['no_context']) && $attr['no_context']) {
            return $params . $content;
        }

        return
            "<?php\n" .
            '$bakcup_old_nbpp = App::frontend()->context()->nb_entry_per_page; ' . "\n" .
            'App::frontend()->context()->nb_entry_per_page = abs((integer) App::blog()->settings()->get("' . My::id() . '")->get("nb_comments_per_page"));' . "\n" .
            "?>\n" .
            $params .
            '<?php if (App::frontend()->context()->pagination->f(0) > App::frontend()->context()->comments->count()) : ?>' .
            $content .
            "<?php endif;\n" .
            'App::frontend()->context()->nb_entry_per_page = $bakcup_old_nbpp; ' . "\n" .
            '?>';
    }

    /**
     * comListePaginationCounter.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListePaginationCounter(ArrayObject $attr): string
    {
        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::frontend()->context()::PaginationNbPages()') . '; ?>';
    }

    /**
     * comListePaginationCurrent.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListePaginationCurrent(ArrayObject $attr): string
    {
        $offset = isset($attr['offset']) ? (int) $attr['offset'] : 0;

        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::frontend()->context()::PaginationPosition(' . $offset . ')') . '; ?>';
    }

    /**
     * comListePaginationIf.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListePaginationIf(ArrayObject $attr, string $content): string
    {
        $if = [];

        if (isset($attr['start'])) {
            $sign = (bool) $attr['start'] ? '' : '!';
            $if[] = $sign . 'App::frontend()->context()::PaginationStart()';
        }

        if (isset($attr['end'])) {
            $sign = (bool) $attr['end'] ? '' : '!';
            $if[] = $sign . 'App::frontend()->context()::PaginationEnd()';
        }

        if (count($if)) {
            return '<?php if(' . implode(' && ', $if) . ') : ?>' . $content . '<?php endif; ?>';
        }

        return $content;
    }

    /**
     * comListePaginationURL.
     *
     * @param   ArrayObject<string, mixed>  $attr   The attributes
     */
    public static function comListePaginationURL(ArrayObject $attr): string
    {
        $offset = 0;
        if (isset($attr['offset'])) {
            $offset = (int) $attr['offset'];
        }

        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::frontend()->context()::PaginationURL(' . $offset . ')') . '; ?>';
    }
}
