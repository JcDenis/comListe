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
use Dotclear\Core\Process;
use Exception;

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

        if (is_null(dcCore::app()->blog)) {
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
            dcCore::app()->error->add($e->getMessage());

            return false;
        }
    }
}
