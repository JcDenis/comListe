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

#--- icon.png issue du pack mini_icons_2 offertes par Brandspankingnew
#--- http://www.brandspankingnew.net

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Blog']->addItem(__('List of comments'),'plugin.php?p=comListe','index.php?pf=comListe/icon.png',
		preg_match('/plugin.php\?p=comListe(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));
		
$core->addBehavior('adminDashboardFavorites','comListeDashboardFavorites');

function comListeDashboardFavorites($core,$favs)
{
	$favs->register('comListe', array(
		'title' => __('List of comments'),
		'url' => 'plugin.php?p=comListe',
		'small-icon' => 'index.php?pf=comListe/icon.png',
		'large-icon' => 'index.php?pf=comListe/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}