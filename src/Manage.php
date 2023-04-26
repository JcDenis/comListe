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
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Form,
    Hidden,
    Input,
    Label,
    Number,
    Para,
    Select,
    Submit,
    Text
};
use Dotclear\Helper\Html\Html;
use Exception;

class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && My::phpCompliant()
            && !is_null(dcCore::app()->auth) && !is_null(dcCore::app()->blog)
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        if (($_REQUEST['action'] ?? null) != 'saveconfig') {
            return true;
        }

        try {
            if (empty($_POST['comliste_page_title'])) {
                throw new Exception(__('No page title.'));
            }
            $s = dcCore::app()->blog->settings->get(My::id());
            $s->put('enable', !empty($_POST['comliste_enable']));
            $s->put('page_title', $_POST['comliste_page_title']);
            $s->put('nb_comments_per_page', $_POST['comliste_nb_comments_per_page'] ?? 10);
            $s->put('comments_order', $_POST['comliste_comments_order'] == 'asc' ? 'asc' : 'desc');

            dcCore::app()->blog->triggerBlog();

            dcPage::addSuccessNotice(
                __('Configuration successfully updated.')
            );

            dcCore::app()->adminurl->redirect(
                'admin.plugin.' . My::id()
            );
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return;
        }

        $s = dcCore::app()->blog->settings->get(My::id());

        dcPage::openModule(My::name());

        echo dcPage::breadcrumb([
            Html::escapeHTML(dcCore::app()->blog->name) => '',
            My::name()                                  => '',
        ]) .
        dcPage::notices() .

        (new Form('setting_form'))->method('post')->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id()))->separator('')->fields([
            (new Div())->class('fieldset')->items([
                (new Text('h4', __('Plugin activation'))),
                (new Para())->items([
                    (new Checkbox('comliste_enable', (bool) $s->get('enable')))->value(1),
                    (new Label(__('Enable comListe'), Label::OUTSIDE_LABEL_AFTER))->for('comliste_enable')->class('classic'),
                ]),
            ]),
            (new Div())->class('fieldset')->items([
                (new Text('h4', __('General options'))),
                (new Para())->items([
                    (new Label(__('Public page title:'), Label::OUTSIDE_LABEL_BEFORE))->for('comliste_page_title'),
                    (new Input('comliste_page_title'))->size(30)->maxlenght(255)->value((string) $s->get('page_title')),
                ]),
                (new Para())->items([
                    (new Label(__('Number of comments per page:'), Label::OUTSIDE_LABEL_BEFORE))->for('comliste_nb_comments_per_page'),
                    (new Number('comliste_nb_comments_per_page'))->min(0)->max(99)->value((int) $s->get('nb_comments_per_page')),
                ]),
                (new Label(__('Comments order:'), Label::OUTSIDE_LABEL_BEFORE))->for('comliste_comments_order'),
                (new Select('comliste_comments_order'))
                    ->items([__('Ascending') => 'asc', __('Descending') => 'desc'])
                    ->default($s->get('comments_order') == 'asc' ? 'asc' : 'desc'),
            ]),
            (new Para())->class('clear')->items([
                (new Submit(['do']))->value(__('Save')),
                (new Hidden(['action'], 'saveconfig')),
                (new Hidden(['p'], My::id())),
                dcCOre::app()->formNonce(false),
            ]),
        ])->render();

        dcPage::helpBlock('comListe');

        dcPage::closeModule();
    }
}
