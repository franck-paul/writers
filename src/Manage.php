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

use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Li;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Ul;
use Dotclear\Helper\Html\Html;
use Exception;

class Manage extends Process
{
    private static ?string $u_id = null;

    private static ?string $u_name = null;

    private static bool $chooser = false;

    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (App::auth()->isSuperAdmin()) {
            // If super-admin then redirect to blog parameters, users tab
            App::backend()->url()->redirect('admin.blog.pref', [], '#users');
        }

        self::$u_id    = null;
        self::$u_name  = null;
        self::$chooser = false;

        if (!empty($_POST['i_id'])) {
            try {
                $rs = App::users()->getUser($_POST['i_id']);

                if ($rs->isEmpty() || is_null($rs->user_id)) {
                    throw new Exception(__('Writer does not exists.'));
                }

                if ($rs->user_super) {
                    throw new Exception(__('You cannot add or update this writer.'));
                }

                if ($rs->user_id == App::auth()->userID()) {
                    throw new Exception(__('You cannot change your own permissions.'));
                }

                self::$u_id   = $rs->user_id;
                self::$u_name = App::users()->getUserCN(self::$u_id, $rs->user_name, $rs->user_firstname, $rs->user_displayname);
                unset($rs);
                self::$chooser = true;

                if (!empty($_POST['set_perms'])) {
                    $set_perms = [];

                    if (!empty($_POST['perm'])) {
                        foreach ($_POST['perm'] as $perm_id => $v) {
                            if (defined('DC_WR_ALLOW_ADMIN') && !constant('DC_WR_ALLOW_ADMIN') && $perm_id === App::auth()::PERMISSION_ADMIN) {
                                continue;
                            }

                            if ($v) {
                                $set_perms[$perm_id] = true;
                            }
                        }
                    }

                    App::auth()->sudo(App::users()->setUserBlogPermissions(...), self::$u_id, App::blog()->id(), $set_perms, true);

                    Notices::addSuccessNotice(sprintf(__('Permissions updated for user %s'), self::$u_name));
                    My::redirect([
                        'pup' => 1,
                    ]);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $blog_users = App::blogs()->getBlogPermissions(App::blog()->id(), false);
        $perm_types = App::auth()->getPermissionsTypes();
        $perm_users = array_keys($blog_users);

        if (!empty($_GET['u_id'])) {
            try {
                if (!isset($blog_users[$_GET['u_id']])) {
                    throw new Exception(__('Writer does not exists.'));
                }

                if ($_GET['u_id'] == App::auth()->userID()) {
                    throw new Exception(__('You cannot change your own permissions.'));
                }

                self::$u_id   = $_GET['u_id'];
                self::$u_name = App::users()->getUserCN(
                    self::$u_id,
                    $blog_users[self::$u_id]['name'],
                    $blog_users[self::$u_id]['firstname'],
                    $blog_users[self::$u_id]['displayname']
                );
                self::$chooser = true;
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        $head = '';
        if (!self::$chooser) {
            $usersList = [];

            $rs = App::users()->getUsers([
                'limit' => 100,
                'order' => 'nb_post DESC',
            ])->toStatic();
            $rs->extend('rsExtUser');
            $rsStatic = $rs->toStatic();
            $rsStatic->lexicalSort('user_id');
            while ($rsStatic->fetch()) {
                if (!$rsStatic->user_super && !in_array($rsStatic->user_id, $perm_users)) {
                    // Keep only non superadmin and not already set user
                    $usersList[] = $rsStatic->user_id;
                }
            }

            if ($usersList !== []) {
                $head = Page::jsJson('writers', $usersList) .
                Page::jsLoad('js/jquery/jquery.autocomplete.js') .
                My::jsLoad('writers.js');
            }
        }

        Page::openModule(My::name(), $head);

        echo Page::breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                __('writers')                         => '',
            ]
        );
        echo Notices::getNotices();

        // Form

        if (!self::$chooser) {
            // Users list
            $users = [];
            if (count($blog_users) <= 1) {
                $users[] = (new Para())->items([
                    (new Text(null, __('No writers'))),
                ]);
            } else {
                foreach ($blog_users as $k => $v) {
                    if (isset($v['p']) && $v['p'] > 0 && $k !== App::auth()->userID()) {
                        $name = Html::escapeHTML(App::users()->getUserCN(
                            $k,
                            $v['name'],
                            $v['firstname'],
                            $v['displayname']
                        ));
                        $permissions = [];
                        foreach ($v['p'] as $permission => $value) {
                            $permissions[] = (new Li())->text(__($perm_types[$permission]));
                        }
                        $users[] = (new Div('user-' . $k))
                            ->class('user-perm')
                            ->items([
                                (new Text('h4', Html::escapeHTML($k) . ' (' . $name . ')')),
                                (new Text('h5', __('Permissions:'))),
                                (new Ul())
                                ->items($permissions),
                                (new Para())
                                ->items([
                                    (new Link('perm-' . $k))
                                    ->class('button')
                                    ->text(__('change permissions'))
                                    ->href(App::backend()->getPageURL() . '&u_id=' . Html::escapeHTML($k)),
                                ]),
                            ]);
                    }
                }
            }

            echo
            (new Div('part-users'))
            ->items([
                (new Text('h3', __('Active writers'))),
                (new Div())->items($users),
            ])
            ->render();

            echo
            (new Div())
            ->items([
                (new Text('h3', __('Invite a new writer'))),
                (new Form('add_writer'))
                ->action(App::backend()->getPageURL())
                ->method('post')
                ->fields([
                    (new Para())->items([
                        (new Input('i_id'))
                            ->size(32)
                            ->maxlength(32)
                            ->value(Html::escapeHTML((string) self::$u_id))
                            ->required(true)
                            ->label((new Label(__('Author ID (login): '), Label::OUTSIDE_TEXT_BEFORE))->class('classic')),
                    ]),
                    // Submit
                    (new Para())->items([
                        (new Submit(['frmsubmit']))
                            ->value(__('Invite')),
                        ...My::hiddenFields(),
                    ]),
                ]),
            ])
            ->render();
        } elseif (self::$u_id) {
            // Change user permission
            $user_perm = isset($blog_users[self::$u_id]) ? $blog_users[self::$u_id]['p'] : [];

            echo
            (new Para())
            ->items([
                (new Link())
                ->class('back')
                ->text(__('Back'))
                ->href(App::backend()->getPageURL() . '&pup=1'),
            ])
            ->render();

            echo
            (new Para())
            ->items([
                (new Text(
                    null,
                    sprintf(
                        __('You are about to set permissions on the blog %1$s for user %2$s (%3$s).'),
                        '<strong>' . Html::escapeHTML(App::blog()->name()) . '</strong>',
                        '<strong>' . self::$u_id . '</strong>',
                        Html::escapeHTML(self::$u_name)
                    )
                )),
            ])
            ->render();

            $permissions = [];
            foreach ($perm_types as $perm_id => $perm) {
                if (defined('DC_WR_ALLOW_ADMIN') && !constant('DC_WR_ALLOW_ADMIN') && $perm_id === App::auth()::PERMISSION_ADMIN) {
                    continue;
                }

                $checked       = isset($user_perm[$perm_id]) && $user_perm[$perm_id];
                $permissions[] = (new Para())->items([
                    (new Checkbox(
                        ['perm[' . Html::escapeHTML($perm_id) . ']', 'perm-' . $perm_id],
                        $checked
                    ))
                    ->label((new Label(__($perm), Label::INSIDE_LABEL_AFTER))->class('classic')),
                ]);
            }

            echo
            (new Form('set-perms'))
            ->action(App::backend()->getPageURL())
            ->method('post')
            ->fields([
                ...$permissions,
                // Submit
                (new Para())->items([
                    (new Submit(['frmsubmit']))
                        ->value(__('Save')),
                    ...My::hiddenFields([
                        'i_id'      => Html::escapeHTML(self::$u_id),
                        'set_perms' => (string) 1,
                    ]),
                ]),
            ])
            ->render();
        }

        Page::helpBlock('writers');

        Page::closeModule();
    }
}
