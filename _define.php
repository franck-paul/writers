<?php

/**
 * @brief writers, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
$this->registerModule(
    'Writers',
    'Invite people to write on your blog',
    'Olivier Meunier',
    '7.0',
    [
        'date'        => '2026-08-03T10:17:05+0200',
        'requires'    => [['core', '2.39']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'settings'    => [],

        'details'    => 'https://dotclear.org/plugin/detail/writers',
        'support'    => 'https://github.com/franck-paul/writers',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/writers/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
