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
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Admin menu
        My::addBackendMenuItem();

        dcCore::app()->addBehaviors([
            // Dashboard favorites
            'adminDashboardFavoritesV2' => function (Favorites $favs): void {
                $favs->register(My::id(), [
                    'title'       => My::name(),
                    'url'         => My::manageUrl(),
                    'small-icon'  => My::icons(),
                    'large-icon'  => My::icons(),
                    'permissions' => dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_ADMIN]),
                ]);
            },
            'adminSimpleMenuAddType' => function (ArrayObject $items): void {
                $items[My::id()] = new ArrayObject([My::name(), false]);
            },
            'adminSimpleMenuBeforeEdit' => function (string $type, string $select, array &$item): void {
                if (My::id() == $type) {
                    $item[0] = My::name();
                    $item[1] = My::settings()->get('page_title') ?? My::name();
                    $item[2] = dcCore::app()->admin->__get('blog_url') . dcCore::app()->url->getURLFor(My::id());
                }
            },
        ]);

        return true;
    }
}
