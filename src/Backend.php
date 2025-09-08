<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\Html\Html;

/**
 * @brief       comListe backend class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Backend
{
    use TraitProcess;

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

        App::behavior()->addBehaviors([
            // Dashboard favorites
            'adminDashboardFavoritesV2' => function (Favorites $favs): void {
                $favs->register(My::id(), [
                    'title'       => My::name(),
                    'url'         => My::manageUrl(),
                    'small-icon'  => My::icons(),
                    'large-icon'  => My::icons(),
                    'permissions' => App::auth()->makePermissions([App::auth()::PERMISSION_ADMIN]),
                ]);
            },
            'adminSimpleMenuAddType' => function (ArrayObject $items): void {
                $items[My::id()] = new ArrayObject([My::name(), false]);
            },
            'adminSimpleMenuBeforeEdit' => function (string $type, string $select, array &$item): void {
                if (My::id() == $type) {
                    $item[0] = My::name();
                    $item[1] = My::settings()->get('page_title') ?? My::name();
                    $item[2] = Html::stripHostURL(App::blog()->url()) . App::url()->getURLFor(My::id());
                }
            },
            'initWidgets' => Widgets::initWidgets(...),
        ]);

        return true;
    }
}
