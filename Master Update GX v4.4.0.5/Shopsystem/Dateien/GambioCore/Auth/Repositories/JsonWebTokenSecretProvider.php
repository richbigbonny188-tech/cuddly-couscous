<?php
/* --------------------------------------------------------------
   JsonWebTokenSecretProvider.php 2019-12-04
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2019 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Core\Auth\Repositories;

use Doctrine\DBAL\Connection;
use Exception;
use RuntimeException;

/**
 * Class JsonWebTokenSecretProvider
 *
 * @package Gambio\Core\Auth\Repositories
 */
class JsonWebTokenSecretProvider
{
    /**
     * String length of JSON web token secret.
     */
    private const SECRET_LENGTH = 64;
    
    /**
     * Configuration key for JSON web token secret.
     */
    private const SECRET_CONFIG_KEY = 'gm_configuration/REST_API_SECRET';
    
    
    /**
     * @var Connection
     */
    private $db;
    
    
    /**
     * JsonWebTokenParser constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
    
    
    /**
     * @return string
     */
    public function getSecret(): string
    {
        $configuration = $this->db->createQueryBuilder()
            ->select($this->db->quoteIdentifier('value'))
            ->from('gx_configurations')
            ->where("{$this->db->quoteIdentifier('key')} = :key")
            ->setParameter('key', self::SECRET_CONFIG_KEY)
            ->execute()
            ->fetch();
        
        if ($configuration === false || empty($configuration['value'])) {
            return $this->generateNewSecret();
        }
        
        return $configuration['value'];
    }
    
    
    /**
     * @param string $secret
     */
    private function setSecret(string $secret): void
    {
        $this->db->createQueryBuilder()
            ->update('gx_configurations')
            ->set($this->db->quoteIdentifier('value'), ':secret')
            ->where("{$this->db->quoteIdentifier('key')} = :key")
            ->setParameter('secret', $secret)
            ->setParameter('key', self::SECRET_CONFIG_KEY)
            ->execute();
    }
    
    
    /**
     * @return string
     */
    public function generateNewSecret(): string
    {
        try {
            $secret = bin2hex(random_bytes(self::SECRET_LENGTH));
        } catch (Exception $exception) {
            throw new RuntimeException('Could not generate a JSON web token secret.',
                                       $exception->getCode(),
                                       $exception);
        }
        
        $this->setSecret($secret);
        
        return $secret;
    }
}