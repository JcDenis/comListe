<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of comListe, a plugin for Dotclear.
# 
# Copyright (c) 2008-2015 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

require_once dirname(__FILE__).'/inc/class.dc.comListe.php';
 
$core->url->register('comListe','comListe','^comListe(?:/(.+))?$',array('urlcomListe','comListe'));

$core->tpl->addValue('ComListeURL',array('tplComListe','comListeURL'));
$core->tpl->addValue('ComListePageTitle',array('tplComListe','comListePageTitle'));
$core->tpl->addValue('ComListeNbComments',array('tplComListe','comListeNbComments'));
$core->tpl->addValue('ComListeNbCommentsPerPage',array('tplComListe','comListeNbCommentsPerPage'));
$core->tpl->addBlock('ComListeCommentsEntries',array('tplComListe','comListeCommentsEntries'));
$core->tpl->addValue('ComListePaginationLinks',array('tplComListe','comListePaginationLinks'));
$core->tpl->addValue('ComListeOpenPostTitle',array('tplComListe','comListeOpenPostTitle'));
$core->addBehavior('publicBreadcrumb',array('extComListe','publicBreadcrumb'));