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

$core->addBehavior('initWidgets',array('comListeWidgets','initWidgets'));

class comListeWidgets
{
	public static function initWidgets($w)
	{
		$w->create('comListe',__('ComListe'),array('tplComListe','comListeWidget'),
			null,
			__('List of comments'));
		$w->comListe->setting('title',__('Title:'),__('ComListe'));
		$w->comListe->setting('link_title',__('Link title:'),__('List of comments'));
		$w->comListe->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->comListe->setting('content_only',__('Content only'),0,'check');
    $w->comListe->setting('class',__('CSS class:'),'');
		$w->comListe->setting('offline',__('Offline'),0,'check');
	}
}