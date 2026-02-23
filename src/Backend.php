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

use Dotclear\Core\Backend\Menus;
use Dotclear\Helper\Process\TraitProcess;

class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('Writers');
        __('Invite people to write on your blog');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem(Menus::MENU_BLOG);

        return true;
    }
}
