<?php
namespace TYPO3\CMS\Vidi\Controller\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Fabien Udriot <fabien.udriot@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Controller which handles actions related to a Frontend User in the Backend.
 */
class FrontendUserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * Add a Frontend User Group to a Frontend User
	 *
	 * @param int $frontendUser
	 * @param array $frontendUserGroups
	 * @throws \TYPO3\CMS\Vidi\Exception\MissingUidException
	 * @return void
	 */
	public function addFrontendUserGroupAction($frontendUser, array $frontendUserGroups) {
		if ((int) $frontendUser <= 0) {
			throw new \TYPO3\CMS\Vidi\Exception\MissingUidException('Missing frontend User uid', 1351605124);
		}

		$userGroupList = '';
		if (!empty($frontendUserGroups)) {
			$userGroupList = implode(',', $frontendUserGroups);
		}

		$data['fe_users'][$frontendUser] = array(
			'usergroup' => $userGroupList,
		);

		/** @var $tce \TYPO3\CMS\Core\DataHandling\DataHandler */
		$tce = $this->objectManager->get('TYPO3\CMS\Core\DataHandling\DataHandler');
		$tce->start($data, array());
		$tce->process_datamap();

		// @todo check why data is not persisted
//		$frontendUser = $this->frontendUserRepository->findByUid($frontendUser);
//		if ($frontendUser) {
//			$frontendUserGroup = $this->frontendUserGroupRepository->findByUid($frontendUserGroup);
//			if ($frontendUserGroup) {
//				$frontendUser->addUsergroup($frontendUserGroup);
//				$result = TRUE;
//			}
//		}
		return json_encode(TRUE);
	}

	/**
	 * List Frontend User Groups for a User
	 *
	 * @param int $frontendUser
	 * @return string
	 */
	public function listFrontendUserGroupAction($frontendUser) {

		$frontendUserRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance('fe_users');
		$frontendUser = $frontendUserRepository->findByUid($frontendUser);
		$this->view->assign('frontendUser', $frontendUser);

		$frontendUserGroupsRepository = \TYPO3\CMS\Vidi\ContentRepositoryFactory::getInstance('fe_groups');
		$frontendUserGroups = $frontendUserGroupsRepository->findAll();
		$this->view->assign('frontendUserGroups', $frontendUserGroups);
	}
}

?>
