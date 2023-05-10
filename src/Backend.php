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
use dcAdmin;
use dcCore;
use dcPage;
use dcFavorites;
use dcNsProcess;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->auth) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        // Admin menu
        dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
            My::name(),
            dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
            dcPage::getPF(My::id() . '/icon.png'),
            preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . My::id())) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
            dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_ADMIN]), dcCore::app()->blog->id)
        );

        dcCore::app()->addBehaviors([
            // Dashboard favorites
            'adminDashboardFavoritesV2' => function (dcFavorites $favs): void {
                if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->adminurl)) {
                    return;
                }
                $favs->register(My::id(), [
                    'title'       => My::name(),
                    'url'         => dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
                    'small-icon'  => dcPage::getPF(My::id() . '/icon.png'),
                    'large-icon'  => dcPage::getPF(My::id() . '/icon-big.png'),
                    'permissions' => dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_ADMIN]),
                ]);
            },
            'adminSimpleMenuAddType' => function (ArrayObject $items): void {
                $items[My::id()] = new ArrayObject([My::name(), false]);
            },
            'adminSimpleMenuBeforeEdit' => function (string $type, string $select, array &$item): void {
                if (is_null(dcCore::app()->blog)) {
                    return;
                }
                if (My::id() == $type) {
                    $item[0] = My::name();
                    $item[1] = dcCore::app()->blog->settings->get(My::id())->get('page_title') ?? My::name();
                    $item[2] = dcCore::app()->admin->__get('blog_url') . dcCore::app()->url->getURLFor(My::id());
                }
            },
        ]);

        return true;
    }
}
