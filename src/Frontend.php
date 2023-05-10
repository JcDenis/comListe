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
use dcNsProcess;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_RC_PATH');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (is_null(dcCore::app()->blog)) {
            return false;
        }

        if (!dcCore::app()->blog->settings->get(My::id())->get('enable')) {
            return false;
        }

        dcCore::app()->tpl->addValue('ComListeURL', [Template::class,'comListeURL']);
        dcCore::app()->tpl->addValue('ComListePageTitle', [Template::class,'comListePageTitle']);
        dcCore::app()->tpl->addValue('ComListeNbComments', [Template::class,'comListeNbComments']);
        dcCore::app()->tpl->addValue('ComListeNbCommentsPerPage', [Template::class,'comListeNbCommentsPerPage']);
        dcCore::app()->tpl->addBlock('ComListeCommentsEntries', [Template::class,'comListeCommentsEntries']);
        dcCore::app()->tpl->addValue('ComListePaginationLinks', [Template::class,'comListePaginationLinks']);
        dcCore::app()->tpl->addValue('ComListeOpenPostTitle', [Template::class,'comListeOpenPostTitle']);
        dcCore::app()->tpl->addValue('ComListeCommentOrderNumber', [Template::class,'comListeCommentOrderNumber']);

        dcCore::app()->tpl->addBlock('ComListePagination', [Template::class,'comListePagination']);
        dcCore::app()->tpl->addValue('ComListePaginationCounter', [Template::class,'comListePaginationCounter']);
        dcCore::app()->tpl->addValue('ComListePaginationCurrent', [Template::class,'comListePaginationCurrent']);
        dcCore::app()->tpl->addBlock('ComListePaginationIf', [Template::class,'comListePaginationIf']);
        dcCore::app()->tpl->addValue('ComListePaginationURL', [Template::class,'comListePaginationURL']);

        dcCore::app()->addBehavior(
            'publicBreadcrumb',
            function (string $context, string $separator): ?string {
                if ($context == 'comListe') {
                    return __('Comments list');
                }

                return null;
            },
        );

        return true;
    }
}
