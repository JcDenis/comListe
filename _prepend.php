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

dcCore::app()->url->register(
    'comListe',
    'comListe',
    '^comListe(?:/(.+))?$',
    ['urlcomListe','comListe']
);

class urlcomListe extends dcUrlHandlers
{
    public static function comListe($args)
    {
        $args = (string) $args;

        if (!dcCore::app()->blog->settings->get(basename(__DIR__))->get('enable')) {
            self::p404();

            return null;
        }

        $n = self::getPageNumber($args);
        if (!$n) {
            $n = 1;
        }

        dcCore::app()->public->setPageNumber($n);
        dcCore::app()->ctx->__set('nb_comment_per_page', (int) dcCore::app()->blog->settings->get(basename(__DIR__))->get('nb_comments_per_page'));

        $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->get('system')->get('theme'), 'tplset');
        if (!empty($tplset) && is_dir(implode(DIRECTORY_SEPARATOR, [__DIR__, 'default-templates', $tplset]))) {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [__DIR__, 'default-templates', $tplset]));
        } else {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [__DIR__, 'default-templates', DC_DEFAULT_TPLSET]));
        }

        self::serveDocument('comListe.html');
        exit;
    }
}
