<?php
/**
 * @brief writers, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\writers;

use dcAuth;
use dcCore;
use dcNsProcess;
use dcPage;
use dcUtils;
use Dotclear\Helper\Html\Html;
use Exception;
use form;

class Manage extends dcNsProcess
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::MANAGE);

        return static::$init;
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (dcCore::app()->auth->isSuperAdmin()) {
            // If super-admin then redirect to blog parameters, users tab
            dcCore::app()->adminurl->redirect('admin.blog.pref', [], '#users');
        }

        dcCore::app()->admin->u_id    = null;
        dcCore::app()->admin->u_name  = null;
        dcCore::app()->admin->chooser = false;

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

                dcCore::app()->admin->u_id   = $rs->user_id;
                dcCore::app()->admin->u_name = dcUtils::getUserCN(dcCore::app()->admin->u_id, $rs->user_name, $rs->user_firstname, $rs->user_displayname);
                unset($rs);
                dcCore::app()->admin->chooser = true;

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

                    dcCore::app()->auth->sudo([dcCore::app(), 'setUserBlogPermissions'], dcCore::app()->admin->u_id, dcCore::app()->blog->id, $set_perms, true);

                    dcPage::addSuccessNotice(sprintf(__('Permissions updated for user %s'), dcCore::app()->admin->u_name));
                    dcCore::app()->adminurl->redirect('admin.plugin.' . My::id(), [
                        'pup' => 1,
                    ]);
                }
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        $blog_users = dcCore::app()->getBlogPermissions(dcCore::app()->blog->id, false);
        $perm_types = dcCore::app()->auth->getPermissionsTypes();

        if (!empty($_GET['u_id'])) {
            try {
                if (!isset($blog_users[$_GET['u_id']])) {
                    throw new Exception(__('Writer does not exists.'));
                }

                if ($_GET['u_id'] == dcCore::app()->auth->userID()) {
                    throw new Exception(__('You cannot change your own permissions.'));
                }

                dcCore::app()->admin->u_id   = $_GET['u_id'];
                dcCore::app()->admin->u_name = dcUtils::getUserCN(
                    dcCore::app()->admin->u_id,
                    $blog_users[dcCore::app()->admin->u_id]['name'],
                    $blog_users[dcCore::app()->admin->u_id]['firstname'],
                    $blog_users[dcCore::app()->admin->u_id]['displayname']
                );
                dcCore::app()->admin->chooser = true;
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        $head = '';
        if (!dcCore::app()->admin->chooser) {
            $usersList = [];

            $rs = dcCore::app()->getUsers([
                'limit' => 100,
                'order' => 'nb_post ASC',
            ]);

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
                $head = dcPage::jsJson('writers', $usersList) .
                dcPage::jsLoad('js/jquery/jquery.autocomplete.js') .
                dcPage::jsModuleLoad(My::id() . '/js/writers.js');
            }
        }

        dcPage::openModule(__('Writers'), $head);

        echo dcPage::breadcrumb(
            [
                Html::escapeHTML(dcCore::app()->blog->name) => '',
                __('writers')                               => '',
            ]
        );
        echo dcPage::notices();

        // Form

        if (!dcCore::app()->admin->chooser) {
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
            form::field('i_id', 32, 32, dcCore::app()->admin->u_id) . '</label> ' .
            '<input type="submit" value="' . __('Invite') . '" />' .
            dcCore::app()->formNonce() . '</p>' .
            '</form>';
        } elseif (dcCore::app()->admin->u_id) {
            if (isset($blog_users[dcCore::app()->admin->u_id])) {
                $user_perm = $blog_users[dcCore::app()->admin->u_id]['p'];
            } else {
                $user_perm = [];
            }

            echo '<p><a class="back" href="' . Html::escapeURL(dcCore::app()->admin->getPageURL() . '&pup=1') . '">' . __('Back') . '</a></p>';
            echo
            '<p>' . sprintf(
                __('You are about to set permissions on the blog %s for user %s (%s).'),
                '<strong>' . Html::escapeHTML(dcCore::app()->blog->name) . '</strong>',
                '<strong>' . dcCore::app()->admin->u_id . '</strong>',
                Html::escapeHTML(dcCore::app()->admin->u_name)
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
            form::hidden('i_id', Html::escapeHTML(dcCore::app()->admin->u_id)) .
            form::hidden('set_perms', 1) . '</p>' .
                '</form>';
        }

        dcPage::helpBlock('writers');

        dcPage::closeModule();
    }
}
