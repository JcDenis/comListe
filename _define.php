<?php
/**
 * @file
 * @brief       The plugin comListe definition
 * @ingroup     comListe
 *
 * @defgroup    comListe Plugin comListe.
 *
 * Display a list of all comments and trackbacks of a blog in a public page.
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Comments list',
    'Display a list of all comments and trackbacks of a blog in a public page',
    'Benoit de Marne, Pierre Van Glabeke and contributors',
    '1.0.1',
    [
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'settings'    => ['self' => ''],
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-09-08T18:40:32+00:00',
    ]
);
