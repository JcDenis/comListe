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

try {
    if (!dcCore::app()->newVersion(
        basename(__DIR__),
        dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version')
    )) {
        return null;
    }

    $s = dcCore::app()->blog->settings->get(basename(__DIR__));
    $s->put('enable', false, 'boolean', 'Enable comListe', false, true);
    $s->put('page_title', 'Comments list', 'string', 'Public page title', false, true);
    $s->put('nb_comments_per_page', 10, 'integer', 'Number of comments per page', false, true);
    $s->put('comments_order', 'desc', 'string', 'Comments order', false, true);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
