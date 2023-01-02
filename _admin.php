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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

require __DIR__ . '/_widgets.php';

// Admin menu
dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('Comments list'),
    dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
    urldecode(dcPage::getPF(basename(__DIR__) . '/icon.png')),
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__))) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]), dcCore::app()->blog->id)
);

dcCore::app()->addBehaviors([
    // Dashboard favorites
    'adminDashboardFavoritesV2' => function (dcFavorites $favs) {
        $favs->register(basename(__DIR__), [
            'title'       => __('Comments list'),
            'url'         => dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
            'small-icon'  => urldecode(dcPage::getPF(basename(__DIR__) . '/icon.png')),
            'large-icon'  => urldecode(dcPage::getPF(basename(__DIR__) . '/icon-big.png')),
            'permissions' => dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]),
        ]);
    },
]);
