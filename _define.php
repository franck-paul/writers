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
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Writers',                             // Name
    'Invite people to write on your blog', // Description
    'Olivier Meunier',                     // Author
    '1.5',                                 // Version
    [
        'requires'    => [['core', '2.19']], // Dependencies
        'permissions' => 'admin',            // Permissions
        'type'        => 'plugin',           // Type
        'details'     => 'https://plugins.dotaddict.org/dc2/details/writers',
        'settings'    => []
    ]
);
