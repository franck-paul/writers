<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Writers, a plugin for Dotclear.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;

if (!defined('DC_CONTEXT_ADMIN')) {
    exit;
}

if (dcCore::app()->auth->isSuperAdmin()) {
    // If super-admin then redirect to blog parameters, users tab
    Http::redirect(dcCore::app()->adminurl->get('admin.blog.pref') . '#users');
}

$page_title = __('Writers');

$u_id    = null;
$u_name  = null;
$chooser = false;

$blog_users = dcCore::app()->getBlogPermissions(dcCore::app()->blog->id, false);
$perm_types = dcCore::app()->auth->getPermissionsTypes();

if (!empty($_POST['i_id'])) {
    try {
        $rs = dcCore::app()->getUser($_POST['i_id']);

        if ($rs->isEmpty()) {
            throw new Exception(__('Writer does not exists.'));
        }

        if ($rs->user_super) {
            throw new Exception(__('You cannot add or update this writer.'));
        }

        if ($rs->user_id == dcCore::app()->auth->userID()) {
            throw new Exception(__('You cannot change your own permissions.'));
        }

        $u_id   = $rs->user_id;
        $u_name = dcUtils::getUserCN($u_id, $rs->user_name, $rs->user_firstname, $rs->user_displayname);
        unset($rs);
        $chooser = true;

        if (!empty($_POST['set_perms'])) {
            $set_perms = [];

            if (!empty($_POST['perm'])) {
                foreach ($_POST['perm'] as $perm_id => $v) {
                    if (!DC_WR_ALLOW_ADMIN && $perm_id === dcAuth::PERMISSION_ADMIN) {    // @phpstan-ignore-line
                        continue;
                    }

                    if ($v) {
                        $set_perms[$perm_id] = true;
                    }
                }
            }

            dcCore::app()->auth->sudo([dcCore::app(), 'setUserBlogPermissions'], $u_id, dcCore::app()->blog->id, $set_perms, true);
            Http::redirect(dcCore::app()->admin->getPageURL() . '&pup=1');
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
} elseif (!empty($_GET['u_id'])) {
    try {
        if (!isset($blog_users[$_GET['u_id']])) {
            throw new Exception(__('Writer does not exists.'));
        }

        if ($_GET['u_id'] == dcCore::app()->auth->userID()) {
            throw new Exception(__('You cannot change your own permissions.'));
        }

        $u_id   = $_GET['u_id'];
        $u_name = dcUtils::getUserCN(
            $u_id,
            $blog_users[$u_id]['name'],
            $blog_users[$u_id]['firstname'],
            $blog_users[$u_id]['displayname']
        );
        $chooser = true;
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}
?>
<html>
<head>
  <title><?php echo $page_title; ?></title>
<?php
if (!$chooser) {
    $usersList = [];
    $rs        = dcCore::app()->getUsers([
        'limit' => 100,
        'order' => 'nb_post ASC', ]);
    $rsStatic = $rs->toStatic();
    $rsStatic->extend('rsExtUser');
    $rsStatic = $rsStatic->toExtStatic();   // @phpstan-ignore-line
    $rsStatic->lexicalSort('user_id');
    while ($rsStatic->fetch()) {
        if (!$rsStatic->user_super) {
            $usersList[] = $rsStatic->user_id;
        }
    }
    if ($usersList !== []) {
        echo
        dcPage::jsJson('writers', $usersList) .
        dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
        dcPage::jsModuleLoad('writers/js/writers.js');
    }
}
?>
</head>

<body>
<?php
if (!$chooser) {
    echo dcPage::breadcrumb(
        [
            Html::escapeHTML(dcCore::app()->blog->name)           => '',
            '<span class="page-title">' . $page_title . '</span>' => '',
        ]
    );
    echo dcPage::notices();

    echo '<h3>' . __('Active writers') . '</h3>';

    if (count($blog_users) <= 1) {
        echo '<p>' . __('No writers') . '</p>';
    } else {
        foreach ($blog_users as $k => $v) {
            if ((is_countable($v['p']) ? count($v['p']) : 0) > 0 && $k != dcCore::app()->auth->userID()) {
                echo
                '<h4>' . Html::escapeHTML($k) .
                ' (' . Html::escapeHTML(dcUtils::getUserCN(
                    $k,
                    $v['name'],
                    $v['firstname'],
                    $v['displayname']
                )) . ') - ' .
                '<a href="' . dcCore::app()->admin->getPageURL() . '&amp;u_id=' . Html::escapeHTML($k) . '">' .
                __('change permissions') . '</a></h4>';

                echo '<ul>';
                foreach ($v['p'] as $p => $V) {
                    echo '<li>' . __($perm_types[$p]) . '</li>';
                }
                echo '</ul>';
            }
        }
    }

    echo '<h3>' . __('Invite a new writer') . '</h3>';

    echo
    '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">' .
    '<p><label class="classic" for="i_id">' . __('Author ID (login): ') . ' ' .
    form::field('i_id', 32, 32, $u_id) . '</label> ' .
    '<input type="submit" value="' . __('Invite') . '" />' .
    dcCore::app()->formNonce() . '</p>' .
        '</form>';
} elseif ($u_id) {
    if (isset($blog_users[$u_id])) {
        $user_perm = $blog_users[$u_id]['p'];
    } else {
        $user_perm = [];
    }

    echo dcPage::breadcrumb(
        [
            Html::escapeHTML(dcCore::app()->blog->name)           => '',
            '<span class="page-title">' . $page_title . '</span>' => '',
        ]
    );

    echo '<p><a class="back" href="' . Html::escapeURL('plugin.php?p=writers&pup=1') . '">' . __('Back') . '</a></p>';
    echo
    '<p>' . sprintf(
        __('You are about to set permissions on the blog %s for user %s (%s).'),
        '<strong>' . Html::escapeHTML(dcCore::app()->blog->name) . '</strong>',
        '<strong>' . $u_id . '</strong>',
        Html::escapeHTML($u_name)
    ) . '</p>' .

        '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post">';

    foreach ($perm_types as $perm_id => $perm) {
        if (!DC_WR_ALLOW_ADMIN && $perm_id === dcAuth::PERMISSION_ADMIN) {    // @phpstan-ignore-line
            continue;
        }

        $checked = isset($user_perm[$perm_id]) && $user_perm[$perm_id];

        echo
        '<p><label class="classic">' .
        form::checkbox(
            ['perm[' . Html::escapeHTML($perm_id) . ']'],
            1,
            $checked
        ) . ' ' .
        __($perm) . '</label></p>';
    }

    echo
    '<p><input type="submit" value="' . __('Save') . '" />' .
    dcCore::app()->formNonce() .
    form::hidden('i_id', Html::escapeHTML($u_id)) .
    form::hidden('set_perms', 1) . '</p>' .
        '</form>';
}

dcPage::helpBlock('writers');
?>
</body>
</html>
