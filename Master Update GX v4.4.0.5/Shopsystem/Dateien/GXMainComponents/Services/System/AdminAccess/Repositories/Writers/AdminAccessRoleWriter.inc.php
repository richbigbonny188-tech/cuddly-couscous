<?php

/* --------------------------------------------------------------
    AdminAccessRoleWriter.inc.php 2020-12-14
    Gambio GmbH
    http://www.gambio.de
    Copyright (c) 2020 Gambio GmbH
    Released under the GNU General Public License (Version 2)
    [http://www.gnu.org/licenses/gpl-2.0.html]
    --------------------------------------------------------------
*/

/**
 * Class AdminAccessRoleWriter
 *
 * @category   System
 * @package    AdminAccess
 * @subpackage Writers
 */
class AdminAccessRoleWriter implements AdminAccessRoleWriterInterface
{
    /**
     * @var CI_DB_query_builder
     */
    protected $db;
    
    /**
     * @var LanguageProviderInterface
     */
    protected $languageProvider;
    
    /**
     * @var string
     */
    protected $rolesTable;
    
    /**
     * @var string
     */
    protected $rolesDescriptionsTable;
    
    
    /**
     * AdminAccessRoleWriter constructor.
     *
     * @param CI_DB_query_builder       $db               Query builder.
     * @param LanguageProviderInterface $languageProvider Language provider.
     */
    public function __construct(CI_DB_query_builder $db, LanguageProviderInterface $languageProvider)
    {
        $this->db               = $db;
        $this->languageProvider = $languageProvider;
        
        $this->rolesTable             = 'admin_access_roles';
        $this->rolesDescriptionsTable = 'admin_access_role_descriptions';
    }
    
    
    /**
     * Stores a role into the database.
     *
     * @param AdminAccessRole $role Role object.
     *
     * @return int ID of stored role.
     */
    public function insert(AdminAccessRole $role)
    {
        $roleData = [
            'sort_order'                     => $role->getSortOrder(),
            'deleting_unknown_group_granted' => $role->checkDeletingPermissionForUnknownGroup(),
            'reading_unknown_group_granted'  => $role->checkReadingPermissionForUnknownGroup(),
            'writing_unknown_group_granted'  => $role->checkWritingPermissionForUnknownGroup(),
        ];
        
        // Start transaction so we won't lose data if something goes wrong.
        $this->db->trans_start();
        
        $this->db->insert($this->rolesTable, $roleData);
        
        $roleId = $this->db->insert_id();
        
        $roleDescriptions = [];
        
        foreach ($this->languageProvider->getAdminCodes() as $langCode) {
            $langId = $this->languageProvider->getIdByCode($langCode);
            
            $roleDescriptions['language_id']          = $langId;
            $roleDescriptions['name']                 = $role->getName()->getValue((string)$langCode);
            $roleDescriptions['description']          = $role->getDescription()->getValue((string)$langCode);
            $roleDescriptions['admin_access_role_id'] = $roleId;
            
            $this->db->insert($this->rolesDescriptionsTable, $roleDescriptions);
        }
        
        $this->db->trans_complete();
        
        return $roleId;
    }
    
    
    /**
     * Updates a role from the database.
     *
     * @param AdminAccessRole $role Role object.
     *
     * @return AdminAccessRoleWriterInterface Returns same instance for chained method calls.
     */
    public function update(AdminAccessRole $role)
    {
        $roleData = [
            'sort_order'                     => $role->getSortOrder(),
            'deleting_unknown_group_granted' => $role->checkDeletingPermissionForUnknownGroup(),
            'reading_unknown_group_granted'  => $role->checkReadingPermissionForUnknownGroup(),
            'writing_unknown_group_granted'  => $role->checkWritingPermissionForUnknownGroup(),
        ];
        
        $this->db->where('admin_access_role_id', $role->getId());
        
        // Start transaction so we won't lose data if something goes wrong.
        $this->db->trans_start();
        
        $this->db->update($this->rolesTable, $roleData);
        
        $roleDescriptions = [];
        
        foreach ($this->languageProvider->getAdminCodes() as $langCode) {
            $langId = $this->languageProvider->getIdByCode($langCode);
            
            $name        = $role->getName()->keyExists((string)$langCode) ? $role->getName()
                ->getValue((string)$langCode) : '';
            $description = $role->getDescription()->keyExists((string)$langCode) ? $role->getDescription()
                ->getValue((string)$langCode) : '';
            
            $roleDescriptions['admin_access_role_id'] = $role->getId();
            $roleDescriptions['language_id']          = $langId;
            $roleDescriptions['name']                 = $name;
            $roleDescriptions['description']          = $description;
            
            $this->db->replace($this->rolesDescriptionsTable, $roleDescriptions);
        }
        
        $this->db->trans_complete();
        
        return $this;
    }
}
