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

$this->registerModule(
    'Writers',
    'Invite people to write on your blog',
    'Olivier Meunier',
    '2.1',
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
