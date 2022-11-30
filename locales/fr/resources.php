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
if (!isset(dcCore::app()->resources['help']['writers'])) {
    dcCore::app()->resources['help']['writers'] = __DIR__ . '/help/writers.html';
}
