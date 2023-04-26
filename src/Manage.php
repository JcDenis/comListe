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

dcPage::check(dcCore::app()->auth->makePermissions([
    dcAuth::PERMISSION_ADMIN,
]));

$s           = dcCore::app()->blog->settings->get(basename(__dir__));
$action      = $_REQUEST['action'] ?? null;
$order_combo = [
    __('Ascending')  => 'asc',
    __('Descending') => 'desc',
];

if ($action == 'saveconfig') {
    try {
        if (empty($_POST['comliste_page_title'])) {
            throw new Exception(__('No page title.'));
        }

        $s->put('enable', !empty($_POST['comliste_enable']));
        $s->put('page_title', $_POST['comliste_page_title']);
        $s->put('nb_comments_per_page', $_POST['comliste_nb_comments_per_page'] ?? 10);
        $s->put('comments_order', $_POST['comliste_comments_order'] == 'asc' ? 'asc' : 'desc');

        dcCore::app()->blog->triggerBlog();

        dcAdminNotices::addSuccessNotice(
            __('Configuration successfully updated.')
        );

        dcCore::app()->adminurl->redirect(
            'admin.plugin.' . basename(__DIR__)
        );
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

echo '
<html><head><title>' . __('Comments list') . '</title></head><body>' .
dcPage::breadcrumb([
    html::escapeHTML(dcCore::app()->blog->name) => '',
    __('Comments list')                         => '',
]) .
dcPage::notices() .

'<form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)) . '">' .
'<div class="fieldset"><h4>' . __('Plugin activation') . '</h4>' .

'<p class="field"><label for="comliste_enable" class="classic">' .
form::checkbox('comliste_enable', 1, (bool) $s->get('enable')) .
__('Enable comListe') . '</label></p>' .

'</div>' .
'<div class="fieldset"><h4>' . __('General options') . '</h4>' .

'<p><label for="comliste_page_title">' . __('Public page title:') . ' </label>' .
form::field('comliste_page_title', 30, 255, (string) $s->get('page_title')) .
'</label></p>' .

'<p><label for="comliste_nb_comments_per_page">' .
__('Number of comments per page:') . '</label>' .
form::number('comliste_nb_comments_per_page', ['min' => 0, 'max' => 99, 'default' => (int) $s->get('nb_comments_per_page')]) .
'</p>' .

'<p><label for="comliste_comments_order">' . __('Comments order:') . '</label>' .
form::combo('comliste_comments_order', $order_combo, $s->get('comments_order') == 'asc' ? 'asc' : 'desc') .
'</p>' .

'</div>

<p class="clear">' .
form::hidden(['action'], 'saveconfig') .
dcCore::app()->formNonce() . '
<input id="new-action" type="submit" name="save" value="' . __('Save') . '" />
</p>

</form>';

dcPage::helpBlock('comListe');

echo '</body></html>';
