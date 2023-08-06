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

use dcCore;
use dcUrlHandlers;

class UrlHandler extends dcUrlHandlers
{
    public static function comListe(?string $args): void
    {
        $args = (string) $args;

        if (is_null(dcCore::app()->blog)
            || is_null(dcCore::app()->ctx)
            || !My::settings()->get('enable')
        ) {
            self::p404();
        }

        dcCore::app()->public->setPageNumber(self::getPageNumber($args) ?: 1);
        dcCore::app()->ctx->__set('nb_comment_per_page', (int) My::settings()->get('nb_comments_per_page'));

        $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->get('system')->get('theme'), 'tplset');
        if (!empty($tplset) && is_dir(implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]))) {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]));
        } else {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', DC_DEFAULT_TPLSET]));
        }

        self::serveDocument('comListe.html');
        exit;
    }
}
