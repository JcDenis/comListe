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
        if (!self::status()) {
            return false;
        }

        if (!My::settings()->get('enable')) {
            return false;
        }

        App::frontend()->template()->addValue('ComListeURL', Template::comListeURL(...));
        App::frontend()->template()->addValue('ComListePageTitle', Template::comListePageTitle(...));
        App::frontend()->template()->addValue('ComListeNbComments', Template::comListeNbComments(...));
        App::frontend()->template()->addValue('ComListeNbCommentsPerPage', Template::comListeNbCommentsPerPage(...));
        App::frontend()->template()->addBlock('ComListeCommentsEntries', Template::comListeCommentsEntries(...));
        App::frontend()->template()->addValue('ComListePaginationLinks', Template::comListePaginationLinks(...));
        App::frontend()->template()->addValue('ComListeOpenPostTitle', Template::comListeOpenPostTitle(...));
        App::frontend()->template()->addValue('ComListeCommentOrderNumber', Template::comListeCommentOrderNumber(...));

        App::frontend()->template()->addBlock('ComListePagination', Template::comListePagination(...));
        App::frontend()->template()->addValue('ComListePaginationCounter', Template::comListePaginationCounter(...));
        App::frontend()->template()->addValue('ComListePaginationCurrent', Template::comListePaginationCurrent(...));
        App::frontend()->template()->addBlock('ComListePaginationIf', Template::comListePaginationIf(...));
        App::frontend()->template()->addValue('ComListePaginationURL', Template::comListePaginationURL(...));

        App::behavior()->addBehavior(
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
