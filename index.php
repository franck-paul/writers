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

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

if ($core->auth->isSuperAdmin()) {
    // If super-admin then redirect to blog parameters, users tab
    http::redirect($core->adminurl->get("admin.blog.pref") . '#users');
}

$page_title = __('Writers');

$u_id    = null;
$u_name  = null;
$chooser = false;

$blog_users = $core->getBlogPermissions($core->blog->id, false);
$perm_types = $core->auth->getPermissionsTypes();

if (!empty($_POST['i_id'])) {
    try
    {
        $rs = $core->getUser($_POST['i_id']);

        if ($rs->isEmpty()) {
            throw new Exception(__('Writer does not exists.'));
        }

        if ($rs->user_super) {
            throw new Exception(__('You cannot add or update this writer.'));
        }

        if ($rs->user_id == $core->auth->userID()) {
            throw new Exception(__('You cannot change your own permissions.'));
        }

        $u_id   = $rs->user_id;
        $u_name = dcUtils::getUserCN($u_id, $rs->user_name, $rs->user_firstname, $rs->user_displayname);
        unset($rs);
        $chooser = true;

        if (!empty($_POST['set_perms'])) {
            $set_perms = array();

            if (!empty($_POST['perm'])) {
                foreach ($_POST['perm'] as $perm_id => $v) {
                    if (!DC_WR_ALLOW_ADMIN && $perm_id == 'admin') {
                        continue;
                    }

                    if ($v) {
                        $set_perms[$perm_id] = true;
                    }
                }
            }

            $core->auth->sudo(array($core, 'setUserBlogPermissions'), $u_id, $core->blog->id, $set_perms, true);
            http::redirect($p_url . '&pup=1');
        }
    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
} elseif (!empty($_GET['u_id'])) {
    try
    {
        if (!isset($blog_users[$_GET['u_id']])) {
            throw new Exception(__('Writer does not exists.'));
        }

        if ($_GET['u_id'] == $core->auth->userID()) {
            throw new Exception(__('You cannot change your own permissions.'));
        }

        $u_id   = $_GET['u_id'];
        $u_name = dcUtils::getUserCN($u_id, $blog_users[$u_id]['name'],
            $blog_users[$u_id]['firstname'], $blog_users[$u_id]['displayname']);
        $chooser = true;
    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}
?>
<html>
<head>
  <title><?php echo $page_title; ?></title>
</head>

<body>
<?php
if (!$chooser) {
    echo dcPage::breadcrumb(
        array(
            html::escapeHTML($core->blog->name)                   => '',
            '<span class="page-title">' . $page_title . '</span>' => ''
        ));
    echo dcPage::notices();

    echo '<h3>' . __('Active writers') . '</h3>';

    if (count($blog_users) <= 1) {
        echo '<p>' . __('No writers') . '</p>';
    } else {
        foreach ($blog_users as $k => $v) {
            if (count($v['p']) > 0 && $k != $core->auth->userID()) {
                echo
                '<h4>' . html::escapeHTML($k) .
                ' (' . html::escapeHTML(dcUtils::getUserCN(
                    $k, $v['name'], $v['firstname'], $v['displayname']
                )) . ') - ' .
                '<a href="' . $p_url . '&amp;u_id=' . html::escapeHTML($k) . '">' .
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
    '<form action="' . $p_url . '" method="post">' .
    '<p><label class="classic" for="i_id">' . __('Author ID (login): ') . ' ' .
    form::field('i_id', 32, 32, $u_id) . '</label> ' .
    '<input type="submit" value="' . __('Invite') . '" />' .
    $core->formNonce() . '</p>' .
        '</form>';
} elseif ($u_id) {
    if (isset($blog_users[$u_id])) {
        $user_perm = $blog_users[$u_id]['p'];
    } else {
        $user_perm = array();
    }

    echo dcPage::breadcrumb(
        array(
            html::escapeHTML($core->blog->name)                   => '',
            '<span class="page-title">' . $page_title . '</span>' => ''
        ));

    echo '<p><a class="back" href="' . html::escapeURL('plugin.php?p=writers&pup=1') . '">' . __('Back') . '</a></p>';
    echo
    '<p>' . sprintf(__('You are about to set permissions on the blog %s for user %s (%s).'),
        '<strong>' . html::escapeHTML($core->blog->name) . '</strong>',
        '<strong>' . $u_id . '</strong>',
        html::escapeHTML($u_name)) . '</p>' .

        '<form action="' . $p_url . '" method="post">';

    foreach ($perm_types as $perm_id => $perm) {
        if (!DC_WR_ALLOW_ADMIN && $perm_id == 'admin') {
            continue;
        }

        $checked = isset($user_perm[$perm_id]) && $user_perm[$perm_id];

        echo
        '<p><label class="classic">' .
        form::checkbox(array('perm[' . html::escapeHTML($perm_id) . ']'),
            1, $checked) . ' ' .
        __($perm) . '</label></p>';
    }

    echo
    '<p><input type="submit" value="' . __('Save') . '" />' .
    $core->formNonce() .
    form::hidden('i_id', html::escapeHTML($u_id)) .
    form::hidden('set_perms', 1) . '</p>' .
        '</form>';
}

dcPage::helpBlock('writers');
?>
</body>
</html>
