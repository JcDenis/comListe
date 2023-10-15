<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       comListe widgets class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Widgets
{
    public static function initWidgets(WidgetsStack $w): void
    {
        $w->create(
            My::id(),
            My::name(),
            self::parseWidget(...),
            null,
            __('Link to comments list public page')
        )
        ->addTitle(My::name())
        ->setting(
            'link_title',
            __('Link title: (leave empty to use page title'),
            My::name()
        )
        ->addHomeOnly()
        ->addContentOnly()
        ->addClass()
        ->addOffline();
    }

    public static function parseWidget(WidgetsElement $w): string
    {
        if (!App::blog()->isDefined()
            || $w->__get('offline')
            || !$w->checkHomeOnly(App::url()->type)
            || !My::settings()->get('enable')
        ) {
            return '';
        }

        return $w->renderDiv(
            (bool) $w->__get('content_only'),
            My::id() . ' ' . $w->__get('class'),
            '',
            ($w->__get('title') ? $w->renderTitle(Html::escapeHTML($w->__get('title'))) : '') .
            sprintf(
                '<p><a href="%s">%s</a></p>',
                App::blog()->url() . App::url()->getBase('comListe'),
                $w->__get('link_title') ? Html::escapeHTML($w->__get('link_title')) : (My::settings()->get('page_title') ?? My::name())
            )
        );
    }
}
