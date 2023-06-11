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

$this->registerModule(
    'Writers',
    'Invite people to write on your blog',
    'Olivier Meunier',
    '3.0',
    [
        'requires'    => [['core', '2.26']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_ADMIN,
        ]),
        'type'     => 'plugin',
        'settings' => [],

        'details'    => 'https://plugins.dotaddict.org/dc2/details/writers',
        'support'    => 'https://github.com/franck-paul/writers',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/writers/master/dcstore.xml',
    ]
);
