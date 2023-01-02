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
if (!defined('DC_RC_PATH')) {
    return null;
}

dcCore::app()->addBehavior('initWidgets', ['comListeWidget','initWidget']);

class comListeWidget
{
    public static function initWidget($w)
    {
        $w->create(
            'comListe',
            __('Comments list'),
            ['comListeWidget','publicWidget'],
            null,
            __('Comments list')
        )
        ->addTitle(__('Comments list'))
        ->setting(
            'link_title',
            __('Link title:'),
            __('Comments list')
        )
        ->addHomeOnly()
        ->addContentOnly()
        ->addClass()
        ->addOffline();
    }

    public static function publicWidget($w)
    {
        if ($w->offline
         || !$w->checkHomeOnly(dcCore::app()->url->type)
         || !dcCore::app()->blog->settings->get(basename(__DIR__))->get('enable')
        ) {
            return null;
        }

        return $w->renderDiv(
            $w->content_only,
            'comliste ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '') .
            sprintf(
                '<p><a href="%s">%s</a></p>',
                dcCore::app()->blog->url . dcCore::app()->url->getBase('comListe'),
                $w->link_title ? html::escapeHTML($w->link_title) : __('Comments list')
            )
        );
    }
}
