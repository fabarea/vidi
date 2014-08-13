<?php
namespace TYPO3\CMS\Vidi;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Utility class related to a possible plugin loaded inside a Vidi module.
 * It can be convenient to load additional stuff in a special context.
 * The plugin is requested by a GET parameter.
 * Example: tx_vidi_user_vidisysfilem1[plugins][]=imageEditor
 *
 * @deprecated use \TYPO3\CMS\Vidi\Module\ModulePlugin instead, will be removed in Vidi 0.4.0 + 2 version
 */
class ModulePlugin extends \TYPO3\CMS\Vidi\Module\ModulePlugin {

}
