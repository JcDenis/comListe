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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$page_title = __('List of comments');

# Settings compatibility test
if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
	$core->blog->settings->addNamespace('comListe');
	$blog_settings =& $core->blog->settings->comListe;
	$system_settings = $core->blog->settings->system;
} else {
	$core->blog->settings->setNamespace('comListe');
	$blog_settings =& $core->blog->settings;
	$system_settings =& $core->blog->settings;
}

// initilisation des variables
$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : null;

// Setting default parameters if missing configuration
if (is_null($blog_settings->comliste_enable)) {
	try {
		$blog_settings->put('comliste_enable',false,'boolean','Enable comListe');
		$core->blog->triggerBlog();
		http::redirect(http::getSelfURI());
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

// Getting current parameters
$comliste_enable = (boolean)$blog_settings->comliste_enable;
$comliste_page_title = $blog_settings->comliste_page_title;
$comliste_nb_comments_per_page = $blog_settings->comliste_nb_comments_per_page;
$comliste_comments_order = $blog_settings->comliste_comments_order;

if ($comliste_page_title === null) {
	$comliste_page_title = __('List of comments');
}
if ($comliste_nb_comments_per_page === null) {
	$comliste_nb_comments_per_page = 10;
}
if ($comliste_comments_order === null) {
	$comliste_comments_order = 'desc';
}

// Saving new configuration
if ($action == 'saveconfig')
{
	try {
		
		// Enable plugin
		$comliste_enable = (empty($_POST['comliste_enable']))?false:true;
		
		// Title page
		$comliste_page_title = $_POST['comliste_page_title'];
		if (empty($_POST['comliste_page_title'])) {
		  throw new Exception(__('No page title.'));
		}

		//  Number of comments per page
		$comliste_nb_comments_per_page = !empty($_POST['comliste_nb_comments_per_page'])?$_POST['comliste_nb_comments_per_page']:$comliste_nb_comments_per_page;
		
		// Order
		$comliste_comments_order = !empty($_POST['comliste_comments_order'])?$_POST['comliste_comments_order']:$comliste_comments_order;
		
		// Insert settings values
		$blog_settings->put('comliste_enable',$comliste_enable,'boolean','Enable comListe');
		$blog_settings->put('comliste_page_title',$comliste_page_title,'string','Title page');
		$blog_settings->put('comliste_nb_comments_per_page',$comliste_nb_comments_per_page,'integer','Number of comments per page');
		$blog_settings->put('comliste_comments_order',$comliste_comments_order,'string','Comments order');
		
		$core->blog->triggerBlog();

		http::redirect($p_url.'&saveconfig=1');
		
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}	
}

?>

<!-- Creating HTML page -->
<html>

<!-- header -->
<head>
  <title><?php echo $page_title; ?></title>
</head>

<!-- body -->
<body>

<?php
	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));

if (!empty($_GET['saveconfig'])) {
  dcPage::success(__('Settings have been successfully updated.'));
}
?>

<?php

$order_combo = array(__('Ascending') => 'asc',
__('Descending') => 'desc' );
	
	// comListe plugin configuration
	if ($core->auth->check('admin',$core->blog->id))
	{
		echo
		'<form method="post" action="plugin.php">'.
		'<div class="fieldset"><h4>'. __('Plugin activation').'</h4>'.
		'<p class="field">'.
		'<label class="classic" for="comliste_enable">'.
		form::checkbox('comliste_enable', 1, $comliste_enable).
		__('Enable comListe').'</label></p>'.
		'</div>'.
		'<div class="fieldset"><h4>'. __('General options').'</h4>'.
		'<p><label class="classic">'. __('Title page').' : '.
		form::field('comliste_page_title', 30,256, $comliste_page_title).
		'</label></p>'.
		'<p><label class=" classic">'. __('Number of comments per page:').' '.
		form::field('comliste_nb_comments_per_page', 4, 4, $comliste_nb_comments_per_page).
		'</label></p>'.
		'<p><label class=" classic">'. __('Comments order').' : '.
		form::combo('comliste_comments_order', $order_combo, $comliste_comments_order).
		'</label></p>'.
		'</div>'.
		'<p><input type="submit" value="'.__('Save').'" onclick="affinfo(\''.__('Save').'\')" /> '.
		$core->formNonce().
		form::hidden(array('action'),'saveconfig').
		form::hidden(array('p'),'comListe').'</p>'.
		'</form>';
	}

?>

<?php dcPage::helpBlock('comListe');?>

</body>
</html>