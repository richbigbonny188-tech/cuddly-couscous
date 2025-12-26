<?php
/* --------------------------------------------------------------
   ParcelServiceApiRequestParser.php 2020-09-01
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2020 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

declare(strict_types=1);

namespace Gambio\Api\Modules\ParcelService\App;

use Exception;
use Gambio\Admin\Modules\ParcelService\Model\ValueObjects\ParcelServiceDescription;
use Gambio\Admin\Modules\ParcelService\Services\ParcelServiceFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ParcelServiceApiRequestParser
 *
 * @package Gambio\Api\Modules\ParcelService\App
 */
class ParcelServiceApiRequestParser
{
    /**
     * @var ParcelServiceFactory
     */
    private $factory;
    
    
    /**
     * ParcelServiceApiRequestParser constructor.
     *
     * @param ParcelServiceFactory $factory
     */
    public function __construct(ParcelServiceFactory $factory)
    {
        $this->factory = $factory;
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return int
     */
    public function getPerPage(ServerRequestInterface $request): int
    {
        return (int)$request->getQueryParam('per-page', 25);
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return int
     */
    public function getPage(ServerRequestInterface $request): int
    {
        return (int)$request->getQueryParam('page', 1);
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function getFields(ServerRequestInterface $request): array
    {
        $fields = $request->getQueryParam('fields');
        
        return ($fields === null) ? [] : explode(',', $fields);
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    public function getFilters(ServerRequestInterface $request): array
    {
        $filters      = $request->getQueryParam('filter', []);
        $createdAfter = $request->getQueryParam('created-after');
        if ($createdAfter !== null) {
            $filters['createdOn'] = 'gt|' . $createdAfter;
        }
        
        return $filters;
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getSorting(ServerRequestInterface $request): string
    {
        return $request->getQueryParam('sort', '');
    }
    
    
    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getResourceUrlFromRequest(ServerRequestInterface $request): string
    {
        return $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $request->getUri()->getPath();
    }
    
    
    /**
     * @param ServerRequestInterface $request
     * @param array                  $errors
     *
     * @return array
     */
    public function parseParcelServiceDataForCreation(ServerRequestInterface $request, &$errors = []): array
    {
        $names          = [];
        $descriptions   = [];
        $isDefaultFlags = [];
        
        foreach ($request->getParsedBody() as $index => $documentData) {
            try {
                $tmpDesc = array_map(function (array $descriptionData): ParcelServiceDescription {
                    return $this->factory->createParcelServiceDescription($descriptionData['languageCode'],
                                                                          $descriptionData['url'],
                                                                          $descriptionData['comment']);
                },
                    $documentData['descriptions']);
                
                $names[]          = $documentData['name'];
                $descriptions[]   = $this->factory->createParcelServiceDescriptions(...$tmpDesc);
                $isDefaultFlags[] = $documentData['isDefault'];
            } catch (Exception $exception) {
                $errors[$index][] = $exception->getMessage();
            }
        }
        
        return [
            'names'          => $names,
            'descriptions'   => $descriptions,
            'isDefaultFlags' => $isDefaultFlags,
        ];
    }
}