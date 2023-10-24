<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       comListe frontend class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status() || !My::settings()->get('enable')) {
            return false;
        }

        $tpl = App::frontend()->template();

        $tpl->addValue('ComListeURL', FrontendTemplate::comListeURL(...));
        $tpl->addValue('ComListePageTitle', FrontendTemplate::comListePageTitle(...));
        $tpl->addValue('ComListeNbComments', FrontendTemplate::comListeNbComments(...));
        $tpl->addValue('ComListeNbCommentsPerPage', FrontendTemplate::comListeNbCommentsPerPage(...));
        $tpl->addBlock('ComListeCommentsEntries', FrontendTemplate::comListeCommentsEntries(...));
        $tpl->addValue('ComListePaginationLinks', FrontendTemplate::comListePaginationLinks(...));
        $tpl->addValue('ComListeOpenPostTitle', FrontendTemplate::comListeOpenPostTitle(...));
        $tpl->addValue('ComListeCommentOrderNumber', FrontendTemplate::comListeCommentOrderNumber(...));

        $tpl->addBlock('ComListePagination', FrontendTemplate::comListePagination(...));
        $tpl->addValue('ComListePaginationCounter', FrontendTemplate::comListePaginationCounter(...));
        $tpl->addValue('ComListePaginationCurrent', FrontendTemplate::comListePaginationCurrent(...));
        $tpl->addBlock('ComListePaginationIf', FrontendTemplate::comListePaginationIf(...));
        $tpl->addValue('ComListePaginationURL', FrontendTemplate::comListePaginationURL(...));

        App::behavior()->addBehaviors([
            'publicBreadcrumb' => fn (string $context, string $separator) => $context == 'comListe' ? __('Comments list') : null,
            'initWidgets'      => Widgets::initWidgets(...),
        ]);

        return true;
    }
}
