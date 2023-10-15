<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       comListe prepend class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::url()->register(
            'comListe',
            'comListe',
            '^comListe(?:/(.+))?$',
            UrlHandler::comListe(...)
        );

        return true;
    }
}
