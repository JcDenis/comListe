<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use Dotclear\App;
use Dotclear\Core\Process;
use Exception;

/**
 * @brief       comListe install class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            $s = My::settings();
            $s->put('enable', false, 'boolean', 'Enable comListe', false, true);
            $s->put('page_title', 'Comments list', 'string', 'Public page title', false, true);
            $s->put('nb_comments_per_page', 10, 'integer', 'Number of comments per page', false, true);
            $s->put('comments_order', 'desc', 'string', 'Comments order', false, true);

            return true;
        } catch (Exception $e) {
            App::error()->add($e->getMessage());

            return false;
        }
    }
}
