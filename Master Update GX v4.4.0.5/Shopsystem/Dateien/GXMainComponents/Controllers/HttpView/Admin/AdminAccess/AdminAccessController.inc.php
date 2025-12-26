<?php
/* --------------------------------------------------------------
 AdminAccessController.inc.php 2020-07-24
 Gambio GmbH
 http://www.gambio.de
 Copyright (c) 2018 Gambio GmbH
 Released under the GNU General Public License (Version 2)
 [http://www.gnu.org/licenses/gpl-2.0.html]
 --------------------------------------------------------------
 */

/**
 * Class AdminAccessController
 *
 * @category System
 * @package  AdminHttpViewControllers
 */
class AdminAccessController extends AdminHttpViewController
{
    /**
     * @var \AdminAccessService
     */
    protected $adminAccessService;
    
    /**
     * @var \CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var \LanguageTextManager
     */
    private $languageTextManager;
    
    /**
     * @var \LanguageProvider
     */
    protected $languageProvider;
    
    /**
     * @var string
     */
    protected $templatePath;
    
    
    /**
     * Initialize Controller
     */
    public function init()
    {
        $this->validateCurrentAdminStatus();
        
        $this->adminAccessService  = StaticGXCoreLoader::getService('AdminAccess');
        $this->db                  = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $this->languageTextManager = MainFactory::create('LanguageTextManager',
                                                         'admin_access',
                                                         $_SESSION['languages_id']);
        $this->languageProvider    = MainFactory::create('LanguageProvider', $this->db);
        $this->templatePath        = DIR_FS_ADMIN . '/html/content/admin_access/';
        
        AdminMenuControl::connect_with_page('admin.php?do=AdminAccess');
    }
    
    
    /**************************************************************************************************************
     * Controller page actions
     **************************************************************************************************************/
    
    /**
     * Default actions.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionDefault()
    {
        return $this->actionManageAdmins();
    }
    
    
    /**
     * Renders the admin access to manage the admins.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionManageAdmins()
    {
        $currentSection = 'manageAdmins';
        $title          = $this->languageTextManager->get_text('heading_title');
        $template       = 'overview_admins.html';
        $templateData   = [
            'mainAdmin' => $this->_getAdminById(1),
            'list'      => [
                'elements'       => $this->_getAdminsOverviewsListItems(),
                'elementActions' => $this->_getAdminsOverviewsListElementActions(),
            ],
        ];
        
        return $this->_returnHttpResponse($title, $template, $templateData, $currentSection);
    }
    
    
    /**
     * Renders the admin access to edit the admins.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionEditAdmin()
    {
        $adminId = (int)$this->_getQueryParameter('id');
        if ($adminId <= 0) {
            $GLOBALS['messageStack']->add($this->languageTextManager->get_text('error_invalid_id'), 'error');
            $this->actionManageRoles();
        }
        $admin = $this->_getAdminById($adminId);
        
        $currentSection = 'manageAdmins';
        $title          = $this->languageTextManager->get_text('heading_title');
        $template       = 'edit_admin.html';
        $templateData   = [
            'admin' => $admin,
            'list'  => [
                'title'          => $this->languageTextManager->get_text('overview_title_assigned_roles')
                                    . $admin['customers_firstname'] . ' ' . $admin['customers_lastname'],
                'action'         => [],
                'elements'       => $this->_getAdminEditsListItems($adminId),
                'elementActions' => $this->_getAdminEditsListElementActions(),
            ],
        ];
        
        return $this->_returnHttpResponse($title, $template, $templateData, $currentSection);
    }
    
    
    /**
     * Saves the assignment of the roles and redirects to the role assignment page.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionAssignRoles()
    {
        $adminId = (int)$this->_getPostData('adminId');
        if ($adminId <= 0) {
            $GLOBALS['messageStack']->add($this->languageTextManager->get_text('error_invalid_id'), 'error');
            $this->actionManageAdmins();
        }
        
        $assignedRoles = $this->_getPostData('assignedRole');
        if ($assignedRoles === null) {
            $assignedRoles = [];
        }
        $this->_updateAssignedRolesForAdmin($adminId, $assignedRoles);
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   'admin.php?do=AdminAccess/editAdmin&id=' . $adminId);
    }
    
    
    /**
     * Renders the admin access to manage the admins.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionManageRoles()
    {
        $currentSection = 'manageRoles';
        $title          = $this->languageTextManager->get_text('heading_title');
        $template       = 'overview_roles.html';
        $templateData   = [
            'list' => [
                'action'         => $this->_getRolesOverviewsListAction(),
                'elements'       => $this->_getRolesOverviewsListItems(),
                'elementActions' => [],
            ],
        ];
        
        return $this->_returnHttpResponse($title, $template, $templateData, $currentSection);
    }
    
    
    /**
     * Renders the admin access to edit the roles.
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    public function actionManagePermissions()
    {
        $roleId = (int)$this->_getQueryParameter('id');
        if ($roleId <= 0) {
            $GLOBALS['messageStack']->add($this->languageTextManager->get_text('error_invalid_id'), 'error');
            $this->actionManageRoles();
        }
        $role     = $this->_getRoleById($roleId);
        $langCode = $this->languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']))->asString();
        
        $currentSection = 'manageRoles';
        $title          = $this->languageTextManager->get_text('heading_title');
        $template       = 'overview_permissions.html';
        $templateData   = [
            'role'               => $role,
            'langCode'           => $langCode,
            'listElements'       => $this->_getPermissionsOverviewsListItems($roleId),
            'globalListElements' => $this->_getGlobalPermissonsOverviewListItems($roleId),
        ];
        
        return $this->_returnHttpResponse($title, $template, $templateData, $currentSection);
    }
    
    
    /**
     * Saves the granted and revoked permissions and redirects to the permission management page.
     *
     * @return \RedirectHttpControllerResponse
     */
    public function actionSavePermissions()
    {
        $roleId = (int)$this->_getPostData('roleId');
        if ($roleId <= 0) {
            $GLOBALS['messageStack']->add($this->languageTextManager->get_text('error_invalid_id'), 'error');
            $this->actionManageRoles();
        }
        
        $grantAll     = $this->_getPostData('grantAll');
        $granted      = $this->_getPostData('granted');
        $grantUnknown = $this->_getPostData('grantUnknown');
        
        foreach (['reading', 'writing', 'deleting'] as $type) {
            if (isset($grantAll[$type])) {
                $this->_grantAllPermissionsForRole($type, $roleId);
                continue;
            }
            
            if (!isset($granted[$type])) {
                $granted[$type] = [];
            }
            $this->_updatePermissionsForRole($type, $roleId, $granted[$type]);
        }
        
        return MainFactory::create('RedirectHttpControllerResponse',
                                   'admin.php?do=AdminAccess/managePermissions&id=' . $roleId);
    }
    
    
    /**************************************************************************************************************
     * Controller helper methods
     **************************************************************************************************************/
    
    /**
     * Creates and returns an AdminLayoutHttpControllerResponse.
     *
     * @param string $title
     * @param string $template
     * @param array  $templateData
     * @param string $currentSection
     *
     * @return \AdminLayoutHttpControllerResponse
     */
    protected function _returnHttpResponse(
        $title = '',
        $template = 'overview.html',
        $templateData = [],
        $currentSection = ''
    ) {
        if ($title === '') {
            $title = $this->languageTextManager->get_text('heading_title');
        }
        
        return MainFactory::create('AdminLayoutHttpControllerResponse',
                                   new NonEmptyStringType($title),
                                   new ExistingFile(new NonEmptyStringType($this->templatePath . $template)),
                                   new KeyValueCollection($templateData),
                                   $this->_getAssets(),
                                   $this->_createContentNavigation($currentSection));
    }
    
    
    /**
     * Returns the assets for the admin access pages.
     *
     * @return \AssetCollection
     */
    protected function _getAssets()
    {
        $assets = MainFactory::create('AssetCollection');
        $assets->add(MainFactory::create('Asset', 'admin_access.lang.inc.php'));
        $assets->add(MainFactory::create('Asset', 'admin_buttons.lang.inc.php'));
        $assets->add(MainFactory::create('Asset',
                                         DIR_WS_CATALOG . 'admin/html/assets/styles/modules/admin_access.min.css'));
        
        return $assets;
    }
    
    
    /**
     * Returns the db data for an admin by its given id.
     *
     * @param int $adminId
     *
     * @return array
     */
    protected function _getAdminById($adminId)
    {
        $admin = $this->db->select()->from('customers')->where('customers_id', $adminId)->get()->row_array();
        
        return $admin;
    }
    
    
    /**
     * Returns the db data for an role by its given id.
     *
     * @param int $roleId
     *
     * @return array
     */
    protected function _getRoleById($roleId)
    {
        $role = $this->adminAccessService->getRoleById(new IdType($roleId));
        
        return [
            'id'                   => $role->getId(),
            'sort_order'           => $role->getSortOrder(),
            'name'                 => $role->getName()->getArray(),
            'description'          => $role->getDescription()->getArray(),
            'readingUnknownGroup'  => $role->checkReadingPermissionForUnknownGroup(),
            'writingUnknownGroup'  => $role->checkWritingPermissionForUnknownGroup(),
            'deletingUnknownGroup' => $role->checkDeletingPermissionForUnknownGroup(),
        ];
    }
    
    
    /**
     * Creates the content navigation object for the admin access pages.
     *
     * @param string $currentSection Defines the current navigation item.
     *
     * @return \ContentNavigationCollection
     */
    protected function _createContentNavigation($currentSection = '')
    {
        $subNavigationItems = [
            'manageAdmins'  => [
                'title' => new StringType($this->languageTextManager->get_text('sub_navigation_admins')),
                'url'   => new StringType('admin.php?do=AdminAccess/manageAdmins'),
            ],
            'manageRoles'   => [
                'title' => new StringType($this->languageTextManager->get_text('sub_navigation_roles')),
                'url'   => new StringType('admin.php?do=AdminAccess/manageRoles'),
            ],
            'manageApiKeys' => [
                'title' => new StringType($this->languageTextManager->get_text('sub_navigation_api_keys')),
                'url'   => new StringType('admin.php?do=ApiKeys'),
            ],
        ];
        
        $contentNavigation = MainFactory::create('ContentNavigationCollection', []);
        
        foreach ($subNavigationItems as $itemName => $subNavigationItem) {
            $contentNavigation->add($subNavigationItem['title'],
                                    $subNavigationItem['url'],
                                    new BoolType($currentSection === $itemName));
        }
        
        return $contentNavigation;
    }
    
    
    /**
     * Returns the necessary information of all admins to generate the overview listing.
     *
     * @return array
     */
    protected function _getAdminsOverviewsListItems()
    {
        $admins   = $this->db->select('`customers_id` AS `id`, CONCAT((`customers_firstname`), (" "), (`customers_lastname`)) AS `title`')
            ->from('customers')
            ->where('customers_status = 0 AND customers_id != 1')
            ->get()
            ->result_array();
        $langCode = $this->languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']))->asString();
        
        foreach ($admins as $key => $admin) {
            $admins[$key]['roleBadges'] = [];
            $roles                      = $this->adminAccessService->getRolesByCustomerId(new IdType((int)$admin['id']));
            if ($roles->count() > 0) {
                /** @var \AdminAccessRoleInterface $role */
                $charCounter                     = 0;
                $admins[$key]['additionalRoles'] = '';
                foreach ($roles as $role) {
                    if ($charCounter + strlen($role->getName()->getArray()[$langCode]) <= 70) {
                        $admins[$key]['roleBadges'][] = $role->getName()->getArray()[$langCode];
                    } else {
                        $admins[$key]['additionalRoles'] .= ', ' . $role->getName()->getArray()[$langCode];
                    }
                    
                    $charCounter += strlen($role->getName()->getArray()[$langCode]) + 10;
                }
                $admins[$key]['additionalRoles'] = substr($admins[$key]['additionalRoles'], 2);
            }
        }
        
        return $admins;
    }
    
    
    /**
     * Returns the necessary information to provide the elements actions for the admins overview listing.
     *
     * @return array
     */
    protected function _getAdminsOverviewsListElementActions()
    {
        return [
            [
                'url'  => 'admin.php?do=AdminAccess/editAdmin',
                'icon' => 'fa-pencil',
            ],
        ];
    }
    
    
    /**
     * Returns the necessary information of the admin roles for the admin edit page.
     *
     * @return array
     */
    protected function _getAdminEditsListItems($adminId)
    {
        $adminRolesListItems = [];
        $relatedAdminRoles   = [];
        
        $langCode   = $this->languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']))->asString();
        $adminRoles = $this->adminAccessService->getRolesByCustomerId(new IdType($adminId))->getArray();
        if (count($adminRoles) > 0) {
            /* @var \AdminAccessRoleInterface $adminRole */
            foreach ($adminRoles as $adminRole) {
                $relatedAdminRoles[] = $adminRole->getId();
            }
        }
        $allRoles = $this->adminAccessService->getAllRoles()->getArray();
        if (count($allRoles) > 0) {
            /* @var \AdminAccessRoleInterface $role */
            foreach ($allRoles as $role) {
                $names        = $role->getName()->getArray();
                $descriptions = $role->getDescription()->getArray();
                
                $adminRolesListItems[] = [
                    'id'          => $role->getId(),
                    'title'       => $names[$langCode] ? : $names['EN'],
                    'description' => $descriptions[$langCode] ? : $descriptions['EN'],
                    'status'      => in_array($role->getId(), $relatedAdminRoles),
                ];
            }
        }
        
        return $adminRolesListItems;
    }
    
    
    /**
     * Returns the necessary information to provide the elements actions for the admin edit page.
     *
     * @return array
     */
    protected function _getAdminEditsListElementActions()
    {
        return [
            [
                'url'  => 'admin.php?do=AdminAccess/managePermissions',
                'icon' => 'fa-cog',
            ],
        ];
    }
    
    
    /**
     * Returns the necessary information of all admin roles to generate the overview listing.
     *
     * @return array
     */
    protected function _getRolesOverviewsListItems()
    {
        $listItems = [];
        
        $langCode = $this->languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']))->asString();
        $roles    = $this->adminAccessService->getAllRoles();
        if (count($roles) > 0) {
            /* @var \AdminAccessRoleInterface $role */
            foreach ($roles->getArray() as $role) {
                $names        = $role->getName()->getArray();
                $descriptions = $role->getDescription()->getArray();
                
                $listItems[] = [
                    'id'              => $role->getId(),
                    'title'           => $names[$langCode] ? : $names['EN'],
                    'description'     => $descriptions[$langCode] ? : $descriptions['EN'],
                    'permissionCount' => count($this->adminAccessService->getPermissionsByRoleId(new IdType($role->getId()))),
                    'protected'       => $role->getProtected(),
                ];
            }
        }
        
        return $listItems;
    }
    
    
    /**
     * Returns the necessary information to provide the action for the roles overview listing.
     *
     * @return array
     */
    protected function _getRolesOverviewsListAction()
    {
        return [
            'text'  => $this->languageTextManager->get_text('button_create_role'),
            'url'   => '#',
            'icon'  => 'fa-plus',
            'class' => 'create-role',
        ];
    }
    
    
    /**
     * Returns the necessary information of all role permissions to generate the overview listing.
     *
     * @param int $roleId
     *
     * @return array
     * @throws \GroupNotFoundException
     */
    protected function _getPermissionsOverviewsListItems($roleId)
    {
        $listItems = [];
        
        $langCode    = $this->languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']))->asString();
        $permissions = $this->adminAccessService->getPermissionsByGroupCollection(new IdType($roleId),
                                                                                  $this->_getPermissionOverviewsGroupCollection());
        if (count($permissions) > 0) {
            /* @var \AdminAccessPermissionInterface|\AdminAccessPermissionPersistenceInterface|\AdminAccessPermissionPresentationInterface $permission */
            foreach ($permissions->getArray() as $permission) {
                $names        = $permission->getGroup()->getName()->getArray();
                $descriptions = $permission->getGroup()->getDescription()->getArray();
                $hasChildren  = count($permission->getGroup()->getChildren()) > 0;
                
                try {
                    $parentGroupId = $permission->getGroup()->getParentGroup()->getId();
                } catch (GroupNotFoundException $e) {
                    $parentGroupId = 0;
                }
                
                $namesLocalized = $names[$langCode] ? : $names['EN'];
                if ($namesLocalized === 'API' || $namesLocalized === 'Gambio Admin Web UI'
                    || $namesLocalized === 'XML API') {
                    continue;
                } else {
                    $listItems[] = [
                        'group'    => [
                            'id'          => $permission->getGroup()->getId(),
                            'parentId'    => $parentGroupId,
                            'hasChildren' => $hasChildren,
                            'name'        => $names[$langCode] ? : $names['EN'],
                            'description' => $descriptions[$langCode] ? : $descriptions['EN'],
                        ],
                        'deleting' => $permission->isDeletingGranted(),
                        'reading'  => $permission->isReadingGranted(),
                        'writing'  => $permission->isWritingGranted(),
                    ];
                }
            }
        }
        
        return $listItems;
    }
    
    
    /**
     * Returns the necessary information of all global role permissions to generate the overview listing, such as API
     * access and Gambio Admin Web UI access
     *
     * @param $roleId
     *
     * @return array
     * @throws \GroupNotFoundException
     */
    protected function _getGlobalPermissonsOverviewListItems($roleId)
    {
        $listItems = [];
        
        $langCode    = $this->languageProvider->getCodeById(new IdType((int)$_SESSION['languages_id']))->asString();
        $permissions = $this->adminAccessService->getPermissionsByGroupCollection(new IdType($roleId),
                                                                                  $this->_getPermissionOverviewsGroupCollection());
        
        if (count($permissions) > 0) {
            /* @var \AdminAccessPermissionInterface|\AdminAccessPermissionPersistenceInterface|\AdminAccessPermissionPresentationInterface $permission */
            foreach ($permissions->getArray() as $permission) {
                $names        = $permission->getGroup()->getName()->getArray();
                $descriptions = $permission->getGroup()->getDescription()->getArray();
                
                $namesLocalized = $names[$langCode] ? : $names['EN'];
                if ($namesLocalized === 'API' || $namesLocalized === 'Gambio Admin Web UI'
                    || $namesLocalized === 'XML API') {
                    $listItems[] = [
                        'group'    => [
                            'id'          => $permission->getGroup()->getId(),
                            'name'        => $names[$langCode] ? : $names['EN'],
                            'description' => $descriptions[$langCode] ? : $descriptions['EN'],
                        ],
                        'deleting' => $permission->isDeletingGranted(),
                        'reading'  => $permission->isReadingGranted(),
                        'writing'  => $permission->isWritingGranted(),
                    ];
                }
            }
        }
        
        return $listItems;
    }
    
    
    /**
     * Returns the necessary group collection with the right sorting to generate the permission overview listing.
     *
     * @return \AdminAccessGroupCollection
     */
    protected function _getPermissionOverviewsGroupCollection()
    {
        $groups      = $this->adminAccessService->getAllGroups();
        $groupsArray = [];
        
        if ($groups->count() > 0) {
            $this->_appendGroupChildrenToGroupsArray($groups->getArray(), $groupsArray, 0);
        }
        
        return MainFactory::create(AdminAccessGroupCollection::class, $groupsArray);
    }
    
    
    /**
     * Appends group children to a given group array.
     *
     * @param array $children
     * @param array $groupsArray
     * @param int   $parentId
     */
    protected function _appendGroupChildrenToGroupsArray(array $children, array &$groupsArray, $parentId)
    {
        /* @var \AdminAccessGroup $group */
        foreach ($children as $group) {
            try {
                $parentGroupId = $group->getParentGroup()->getId();
            } catch (GroupNotFoundException $e) {
                $parentGroupId = 0;
            }
            
            if (!array_key_exists($group->getId(), $groupsArray) && $parentGroupId === $parentId) {
                $groupsArray[$group->getId()] = $group;
                
                $children = $group->getChildren();
                if ($children->count() > 0) {
                    $this->_appendGroupChildrenToGroupsArray($children->getArray(), $groupsArray, $group->getId());
                }
            }
        }
    }
    
    
    /**
     * Grants all permission to a given role.
     *
     * @param $type
     * @param $roleId
     *
     * @throws \GroupNotFoundException
     */
    protected function _grantAllPermissionsForRole($type, $roleId)
    {
        $permissions = $this->adminAccessService->getPermissionsByRoleId(new IdType($roleId));
        /** @var \AdminAccessPermissionPresentationInterface|\AdminAccessPermissionPersistenceInterface|\AdminAccessPermissionInterface $permission */
        foreach ($permissions as $permission) {
            if ($type === 'reading' && !$permission->isReadingGranted()) {
                $this->adminAccessService->grantReadingPermissionToRole(new IdType($permission->getGroup()->getId()),
                                                                        new IdType($roleId));
            } elseif ($type === 'writing' && !$permission->isWritingGranted()) {
                $this->adminAccessService->grantWritingPermissionToRole(new IdType($permission->getGroup()->getId()),
                                                                        new IdType($roleId));
            } elseif ($type === 'deleting' && !$permission->isDeletingGranted()) {
                $this->adminAccessService->grantDeletingPermissionToRole(new IdType($permission->getGroup()->getId()),
                                                                         new IdType($roleId));
            }
        }
    }
    
    
    /**
     * Updates the permission for unknown groups of a role by a given value.
     *
     * @param $type
     * @param $roleId
     * @param $value
     */
    protected function _updateUnknownPermissionsForRole($type, $roleId, $value)
    {
        $role = $this->adminAccessService->getRoleById(new IdType($roleId));
        switch ($type) {
            case 'reading':
                $this->adminAccessService->updateRoleById(new IdType($role->getId()),
                                                          $role->getName(),
                                                          $role->getDescription(),
                                                          new IntType($role->getSortOrder()),
                                                          new BoolType($value),
                                                          new BoolType($role->checkWritingPermissionForUnknownGroup()),
                                                          new BoolType($role->checkDeletingPermissionForUnknownGroup()));
                break;
            case 'writing':
                $this->adminAccessService->updateRoleById(new IdType($role->getId()),
                                                          $role->getName(),
                                                          $role->getDescription(),
                                                          new IntType($role->getSortOrder()),
                                                          new BoolType($role->checkReadingPermissionForUnknownGroup()),
                                                          new BoolType($value),
                                                          new BoolType($role->checkDeletingPermissionForUnknownGroup()));
                break;
            case 'deleting':
                $this->adminAccessService->updateRoleById(new IdType($role->getId()),
                                                          $role->getName(),
                                                          $role->getDescription(),
                                                          new IntType($role->getSortOrder()),
                                                          new BoolType($role->checkReadingPermissionForUnknownGroup()),
                                                          new BoolType($role->checkWritingPermissionForUnknownGroup()),
                                                          new BoolType($value));
                break;
        }
    }
    
    
    /**
     * Updates the given permissions of an admin.
     *
     * @param       $type
     * @param       $roleId
     * @param array $grantedGroups
     *
     * @throws \GroupNotFoundException
     */
    protected function _updatePermissionsForRole($type, $roleId, array $grantedGroups)
    {
        $permissions = $this->adminAccessService->getPermissionsByRoleId(new IdType($roleId));
        /** @var \AdminAccessPermissionPresentationInterface|\AdminAccessPermissionPersistenceInterface|\AdminAccessPermissionInterface $permission */
        foreach ($permissions as $permission) {
            $groupId = $permission->getGroup()->getId();
            switch ($type) {
                case 'reading':
                    if (in_array($groupId, $grantedGroups) && !$permission->isReadingGranted()) {
                        $this->adminAccessService->grantReadingPermissionToRole(new IdType($groupId),
                                                                                new IdType($roleId));
                    } elseif (!in_array($groupId, $grantedGroups) && $permission->isReadingGranted()) {
                        $this->adminAccessService->removeReadingPermissionFromRole(new IdType($groupId),
                                                                                   new IdType($roleId));
                    }
                    break;
                case 'writing':
                    if (in_array($groupId, $grantedGroups) && !$permission->isWritingGranted()) {
                        $this->adminAccessService->grantWritingPermissionToRole(new IdType($groupId),
                                                                                new IdType($roleId));
                    } elseif (!in_array($groupId, $grantedGroups) && $permission->isWritingGranted()) {
                        $this->adminAccessService->removeWritingPermissionFromRole(new IdType($groupId),
                                                                                   new IdType($roleId));
                    }
                    break;
                case 'deleting':
                    if (in_array($groupId, $grantedGroups) && !$permission->isDeletingGranted()) {
                        $this->adminAccessService->grantDeletingPermissionToRole(new IdType($groupId),
                                                                                 new IdType($roleId));
                    } elseif (!in_array($groupId, $grantedGroups) && $permission->isDeletingGranted()) {
                        $this->adminAccessService->removeDeletingPermissionFromRole(new IdType($groupId),
                                                                                    new IdType($roleId));
                    }
                    break;
            }
        }
    }
    
    
    /**
     * Saves the the given role assignments of an admin.
     *
     * @param       $adminId
     * @param array $assignedRoles
     */
    protected function _updateAssignedRolesForAdmin($adminId, array $assignedRoles)
    {
        $roles = $this->adminAccessService->getRolesByCustomerId(new IdType($adminId));
        /** @var \AdminAccessRole $role */
        foreach ($roles as $role) {
            $roleId = $role->getId();
            if (!in_array($roleId, $assignedRoles)) {
                $this->adminAccessService->removeRoleFromUserByCustomerId(new IdType($roleId), new IdType($adminId));
            } else {
                unset($assignedRoles[$roleId]);
            }
        }
        
        foreach ($assignedRoles as $assignedRole) {
            $this->adminAccessService->addRoleToUserByCustomerId(new IdType($assignedRole), new IdType($adminId));
        }
    }
}