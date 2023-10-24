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
    '0.9.1',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'settings'    => ['self' => ''],
        'support'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository'  => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
