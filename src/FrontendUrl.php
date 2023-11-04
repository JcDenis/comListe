<?php

declare(strict_types=1);

namespace Dotclear\Plugin\comListe;

use Dotclear\App;
use Dotclear\Core\Frontend\Url;

/**
 * @brief       comListe frontend URL class.
 * @ingroup     comListe
 *
 * @author      Benoit de Marne (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class FrontendUrl extends Url
{
    public static function comListe(?string $args): void
    {
        $args = (string) $args;

        if (!My::settings()->get('enable')) {
            self::p404();
        }

        App::frontend()->setPageNumber(self::getPageNumber($args) ?: 1);
        App::frontend()->context()->__set('nb_comment_per_page', (int) My::settings()->get('nb_comments_per_page'));

        $tplset = App::themes()->getDefine(App::blog()->settings()->get('system')->get('theme'))->get('tplset');
        if (empty($tplset) || !is_dir(implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]))) {
            $tplset = App::config()->defaultTplset();
        }
        App::frontend()->template()->appendPath(implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]));

        self::serveDocument('comListe.html');
        exit;
    }
}
