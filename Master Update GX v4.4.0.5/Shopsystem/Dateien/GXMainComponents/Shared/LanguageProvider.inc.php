<?php

/* --------------------------------------------------------------
   LanguageProvider.inc.php 2018-08-10
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2016 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

/**
 * Class LanguageProvider
 *
 * @category System
 * @package  Shared
 */
class LanguageProvider implements LanguageProviderInterface
{
    
    /**
     * Database connection.
     * @var CI_DB_query_builder
     */
    protected $db;
    
    
    /**
     * LanguageProvider constructor.
     *
     * @param CI_DB_query_builder $db Database connection.
     */
    public function __construct(CI_DB_query_builder $db)
    {
        $this->db = $db;
    }
    
    
    /**
     * Returns the language IDs.
     *
     * @return IdCollection
     * @throws InvalidArgumentException If ID is not valid.
     *
     * @throws UnexpectedValueException If no ID has been found.
     */
    public function getIds()
    {
        // Database query.
        $query = $this->db->select('languages_id')->from('languages')->order_by('languages_id', 'ASC');
        
        // Array in which the fetched languages IDs will be pushed as IdType to.
        $fetchedIds = [];
        
        // Iterate over each found row and push ID as IdType to array.
        foreach ($query->get()->result_array() as $row) {
            $id           = (integer)$row['languages_id'];
            $fetchedIds[] = new IdType($id);
        }
        
        // Throw exception if no ID has been found.
        if (empty($fetchedIds)) {
            throw new UnexpectedValueException('No language IDs were found in the database');
        }
        
        return MainFactory::create('IdCollection', $fetchedIds);
    }
    
    
    /**
     * Returns the language IDs for languages enabled for administrative purposes.
     *
     * @return IdCollection
     * @throws InvalidArgumentException If ID is not valid.
     *
     * @throws UnexpectedValueException If no ID has been found.
     */
    public function getAdminIds()
    {
        // Database query.
        $query = $this->db->select('languages_id')
            ->from('languages')
            ->where('status_admin', 1)
            ->order_by('languages_id',
                       'ASC');
        
        // Array in which the fetched languages IDs will be pushed as IdType to.
        $fetchedIds = [];
        
        // Iterate over each found row and push ID as IdType to array.
        foreach ($query->get()->result_array() as $row) {
            $id           = (integer)$row['languages_id'];
            $fetchedIds[] = new IdType($id);
        }
        
        // Throw exception if no ID has been found.
        if (empty($fetchedIds)) {
            throw new UnexpectedValueException('No language IDs were found in the database');
        }
        
        return MainFactory::create('IdCollection', $fetchedIds);
    }
    
    
    /**
     * Returns the language codes.
     *
     * @return KeyValueCollection
     * @throws InvalidArgumentException If code is not valid.
     *
     * @throws UnexpectedValueException If no code has been found.
     */
    public function getCodes()
    {
        // Database query.
        $query = $this->db->select('code')->from('languages')->order_by('languages_id', 'ASC');
        
        // Array in which the fetched languages codes will be pushed as StringType to.
        $fetchedCodes = [];
        
        // Iterate over each found row and push code as StringType to array.
        foreach ($query->get()->result_array() as $row) {
            $code           = $row['code'];
            $fetchedCodes[] = new LanguageCode(new StringType($code));
        }
        
        // Throw exception if no code has been found.
        if (empty($fetchedCodes)) {
            throw new UnexpectedValueException('No language codes were found in the database');
        }
        
        return MainFactory::create('KeyValueCollection', $fetchedCodes);
    }
    
    
    /**
     * Returns the language codes for languages enabled for administration
     *
     * @return \KeyValueCollection
     */
    public function getAdminCodes()
    {
        $adminCodes = $this->db->select('code')
            ->where('status_admin', 1)
            ->order_by('languages_id', 'ASC')
            ->get('languages')
            ->result_array();
        if (empty($adminCodes)) {
            throw new UnexpectedValueException('No language codes were found in the database');
        }
        $adminLanguageCodes = array_map(function ($row) {
            return new LanguageCode(new StringType($row['code']));
        },
            $adminCodes);
        /** @var \KeyValueCollection $adminLanguageCodesCollection */
        $adminLanguageCodesCollection = MainFactory::create('KeyValueCollection', $adminLanguageCodes);
        
        return $adminLanguageCodesCollection;
    }
    
    
    /**
     * Returns the language code from a specific language, selected by the language ID.
     *
     * @param IdType $id Language ID.
     *
     * @return LanguageCode
     * @throws InvalidArgumentException If code is not valid.
     *
     * @throws UnexpectedValueException If no code has been found.
     */
    public function getCodeById(IdType $id)
    {
        // Database query.
        $this->db->select('code')->from('languages')->where('languages_id', $id->asInt());
        
        // Fetch data from database and save.
        $data = $this->db->get()->row_array();
        
        // Throw error if no code has been found.
        if ($data === null) {
            throw new UnexpectedValueException('No language code has been found');
        }
        
        return new LanguageCode(new StringType($data['code']));
    }
    
    
    /**
     * Returns the directory from the a specific language, selected by the language ID.
     *
     * @param IdType $id Language ID.
     *
     * @return string
     * @throws InvalidArgumentException If code is not valid.
     *
     * @throws UnexpectedValueException If no directory has been found.
     */
    public function getDirectoryById(IdType $id)
    {
        // Database query.
        $this->db->select('directory')->from('languages')->where('languages_id', $id->asInt());
        
        // Fetch data from database and save.
        $data = $this->db->get()->row_array();
        
        // Throw error if no value has been found.
        if ($data === null) {
            throw new UnexpectedValueException('No language directory has been found');
        }
        
        // Return language directory.
        $directory = $data['directory'];
        
        return $directory;
    }
    
    
    /**
     * Returns the charset from the a specific language, selected by the language ID.
     *
     * @param IdType $id Language ID.
     *
     * @return string
     * @throws UnexpectedValueException If no charset has been found.
     *
     */
    public function getCharsetById(IdType $id)
    {
        // Database query.
        $this->db->select('language_charset')->from('languages')->where('languages_id', $id->asInt());
        
        // Fetch data from database and save.
        $data = $this->db->get()->row_array();
        
        // Throw error if no value has been found.
        if ($data === null) {
            throw new UnexpectedValueException('No language charset has been found');
        }
        
        // Return language charset.
        $charset = $data['language_charset'];
        
        return $charset;
    }
    
    
    /**
     * Returns the ID from the a specific language, selected by the language code.
     *
     * @param LanguageCode $code Language code.
     *
     * @return int
     * @throws UnexpectedValueException If no ID has been found.
     *
     */
    public function getIdByCode(LanguageCode $code)
    {
        // Database query.
        $this->db->select('languages_id')->from('languages')->where('code', $code->asString());
        
        // Fetch data from database and save.
        $data = $this->db->get()->row_array();
        
        // Throw error if no value has been found.
        if ($data === null) {
            throw new UnexpectedValueException('No language ID has been found');
        }
        
        // Return language ID.
        $id = (int)$data['languages_id'];
        
        return $id;
    }
    
    
    /**
     * Returns the directory from the a specific language, selected by the language code.
     *
     * @param LanguageCode $code Language code.
     *
     * @return string
     * @throws UnexpectedValueException If no directory has been found.
     *
     */
    public function getDirectoryByCode(LanguageCode $code)
    {
        // Database query.
        $this->db->select('directory')->from('languages')->where('code', $code->asString());
        
        // Fetch data from database and save.
        $data = $this->db->get()->row_array();
        
        // Throw error if no value has been found.
        if ($data === null) {
            throw new UnexpectedValueException('No language directory has been found');
        }
        
        // Return language directory.
        $directory = $data['directory'];
        
        return $directory;
    }
    
    
    /**
     * Returns the charset from the a specific language, selected by the language code.
     *
     * @param LanguageCode $code Language code.
     *
     * @return string
     * @throws UnexpectedValueException If no directory has been found.
     *
     */
    public function getCharsetByCode(LanguageCode $code)
    {
        // Database query.
        $this->db->select('language_charset')->from('languages')->where('code', $code->asString());
        
        // Fetch data from database and save.
        $data = $this->db->get()->row_array();
        
        // Throw error if no value has been found.
        if ($data === null) {
            throw new UnexpectedValueException('No language charset has been found');
        }
        
        // Return language directory.
        $charset = $data['language_charset'];
        
        return $charset;
    }
    
    
    /**
     * Returns the active language codes.
     *
     * @return KeyValueCollection
     * @throws InvalidArgumentException If code is not valid.
     *
     */
    public function getActiveCodes()
    {
        // Database query.
        $query = $this->db->select('code')->from('languages')->where('status', 1);
        
        // Array in which the fetched languages codes will be pushed as StringType to.
        $fetchedCodes = [];
        
        // Iterate over each found row and push code as StringType to array.
        foreach ($query->get()->result_array() as $row) {
            $code           = $row['code'];
            $fetchedCodes[] = new LanguageCode(new StringType($code));
        }
        
        // Throw exception if no active code has been found.
        if (empty($fetchedCodes)) {
            throw new UnexpectedValueException('No active language codes were found in the database');
        }
        
        return MainFactory::create('KeyValueCollection', $fetchedCodes);
    }
    
    
    /**
     * Returns the icon for a specific language by a given language code.
     *
     * @param LanguageCode $code The given language code
     *
     * @return string
     * @throws UnexpectedValueException If no icon has been found.
     *
     */
    public function getIconFilenameByCode(LanguageCode $code)
    {
        // Database query.
        $this->db->select('image')->from('languages')->where('code', $code->asString());
        
        // Fetch data from database and save.
        $data = $this->db->get()->row_array();
        
        // Throw error if no value has been found.
        if ($data === null) {
            throw new UnexpectedValueException('No language icon has been found');
        }
        
        // Return language icon filename.
        $icon = $data['image'];
        
        return $icon;
    }
    
    
    /**
     * Returns the default language code.
     *
     * @return string
     * @throws InvalidArgumentException If no default code exists.
     *
     */
    public function getDefaultLanguageCode()
    {
        $result = $this->db->select('value')
            ->from('gx_configurations')
            ->where('key',
                    'configuration/DEFAULT_LANGUAGE')
            ->get()
            ->row_array();
        
        if ($result === null) {
            
            $result = $this->db->select('code')
                ->from('languages')
                ->order_by('languages_id')
                ->limit(1)
                ->get()
                ->result_array();
            
        }
        
        if ($result === null) {
            
            throw new UnexpectedValueException('No default language has been found');
        }
        
        return $result['value'];
    }
    
    
    /**
     * Returns the default language ID.
     *
     * @return int
     * @throws InvalidArgumentException If no default code exists.
     *
     */
    public function getDefaultLanguageId()
    {
        $result = $this->db->select('languages_id')
            ->from('languages')
            ->join('gx_configurations',
                   'languages.code = gx_configurations.value')
            ->where('gx_configurations.key', 'configuration/DEFAULT_LANGUAGE')
            ->get()
            ->row_array();
        
        if ($result === null) {
            throw new UnexpectedValueException('No default language has been found');
        }
        
        return (int)$result['languages_id'];
    }
    
    
    /**
     * Returns the language id of the provided directory name.
     *
     * @param \StringType $directory Name of directory.
     *
     * @return int
     * @throws \UnexpectedValueException If no id was found by provided directory.
     */
    public function getIdByDirectory(StringType $directory)
    {
        $data = $this->db->select('languages_id')
            ->where('directory', strtolower($directory->asString()))
            ->get('languages')
            ->row_array();
        
        if (null === $data) {
            throw new \UnexpectedValueException('No language has been found by provided directory name "'
                                                . $directory->asString() . '".');
        }
        
        return (int)$data['languages_id'];
    }
}
