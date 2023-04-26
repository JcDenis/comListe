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
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

class Widgets
{
    public static function initWidgets(WidgetsStack $w): void
    {
        $w->create(
            My::id(),
            My::name(),
            [self::class, 'parseWidget'],
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
        if (is_null(dcCore::app()->blog)
            || $w->__get('offline')
            || !$w->checkHomeOnly(dcCore::app()->url->type)
            || !dcCore::app()->blog->settings->get(My::id())->get('enable')
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
                dcCore::app()->blog->url . dcCore::app()->url->getBase('comListe'),
                $w->__get('link_title') ? Html::escapeHTML($w->__get('link_title')) : (dcCore::app()->blog->settings->get(My::id())->get('page_title') ?? My::name())
            )
        );
    }
}
