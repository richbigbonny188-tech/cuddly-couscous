<?php

use Gambio\CookieConsentPanel\Services\Purposes\Factories\PurposeWriteServiceFactory;
use GXModules\Gambio\StyleEdit\Adapters\ConfigurationAdapter;
use GXModules\Gambio\StyleEdit\Adapters\TextManagerAdapter;

/**
 * @param $params
 * @param $smarty
 */
function smarty_function_google_maps_widget($params, &$smarty)
{
    $configurationFactory = MainFactory::create(MapWidgetConfigurationFactory::class);
    $commandConfiguration = $configurationFactory->createCommandConfigurationFromArray([
       'id'               => $params['id'],
       'mapConfiguration' => json_decode($params['mapConfiguration'], true),
       'languageId'       => (int)$params['languageId'],
       'width'            => $params['width'],
       'height'           => $params['height']
    ]);
    
    $purposeWriterFactory = new PurposeWriteServiceFactory();
    $service = $purposeWriterFactory->service();
    
    return MainFactory::create(
        MapWidgetOutputCommand::class,
        $commandConfiguration,
        $service,
        TextManagerAdapter::create(),
        ConfigurationAdapter::create()
    )->execute() ?? '';
}