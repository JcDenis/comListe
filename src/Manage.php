<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Number;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Exception;

/**
 * @brief       comListe manage class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!App::blog()->isDefined()) {
            return false;
        }

        if (($_REQUEST['action'] ?? null) != 'saveconfig') {
            return true;
        }

        try {
            if (empty($_POST['comliste_page_title'])) {
                throw new Exception(__('No page title.'));
            }
            $s = My::settings();
            $s->put('enable', !empty($_POST['comliste_enable']));
            $s->put('page_title', $_POST['comliste_page_title']);
            $s->put('nb_comments_per_page', $_POST['comliste_nb_comments_per_page'] ?? 10);
            $s->put('comments_order', $_POST['comliste_comments_order'] == 'asc' ? 'asc' : 'desc');

            App::blog()->triggerBlog();
            Notices::addSuccessNotice(__('Configuration successfully updated.'));
            My::redirect();
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        if (!App::blog()->isDefined()) {
            return;
        }

        $s = My::settings();

        Page::openModule(My::name());

        echo Page::breadcrumb([
            Html::escapeHTML(App::blog()->name()) => '',
            My::name()                            => '',
        ]) .
        Notices::getNotices() .

        (new Form('setting_form'))->method('post')->action(My::manageUrl())->separator('')->fields([
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
                    (new Input('comliste_page_title'))->size(30)->maxlength(255)->value((string) $s->get('page_title')),
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
                ... My::hiddenFields(),
            ]),
        ])->render();

        Page::helpBlock('comListe');

        Page::closeModule();
    }
}
