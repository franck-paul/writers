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
$this->registerModule(
    'Writers',
    'Invite people to write on your blog',
    'Olivier Meunier',
    '5.5',
    [
        'requires'    => [['core', '2.29']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'settings'    => [],

        'details'    => 'https://plugins.dotaddict.org/dc2/details/writers',
        'support'    => 'https://github.com/franck-paul/writers',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/writers/main/dcstore.xml',
    ]
);
