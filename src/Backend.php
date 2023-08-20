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

use dcCore;
use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('writers') . __('Writers');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->admin->menus[Menus::MENU_BLOG]->addItem(
            __('Writers'),
            My::manageUrl(),
            'images/menu/users.svg',
            preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
            My::checkContext(My::MENU)
        );

        return true;
    }
}
