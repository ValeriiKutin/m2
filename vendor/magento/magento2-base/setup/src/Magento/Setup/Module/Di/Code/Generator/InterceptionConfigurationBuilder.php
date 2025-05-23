<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Setup\Module\Di\Code\Generator;

use Magento\Framework\App\Area;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Interception\Cache\CompiledConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Interception\Config\Config as InterceptionConfig;
use Magento\Framework\Interception\Definition\Runtime;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\InterceptableValidator;
use Magento\Setup\Module\Di\Code\Reader\Type;

class InterceptionConfigurationBuilder
{
    /**
     * Area code list: global, frontend, etc.
     *
     * @var array
     */
    private $areaCodesList = [];

    /**
     * @var InterceptionConfig
     */
    private $interceptionConfig;

    /**
     * @var PluginList
     */
    private $pluginList;

    /**
     * @var Type
     */
    private $typeReader;

    /**
     * @var Manager
     */
    private $cacheManager;

    /**
     * @var InterceptableValidator
     */
    private $interceptableValidator;

    /**
     * @var ConfigInterface
     */
    private $omConfig;

    /**
     * @param InterceptionConfig $interceptionConfig
     * @param PluginList $pluginList
     * @param Type $typeReader
     * @param Manager $cacheManager
     * @param InterceptableValidator $interceptableValidator
     * @param ConfigInterface|null $omConfig
     */
    public function __construct(
        InterceptionConfig $interceptionConfig,
        PluginList $pluginList,
        Type $typeReader,
        Manager $cacheManager,
        InterceptableValidator $interceptableValidator,
        ?ConfigInterface $omConfig = null
    ) {
        $this->interceptionConfig = $interceptionConfig;
        $this->pluginList = $pluginList;
        $this->typeReader = $typeReader;
        $this->cacheManager = $cacheManager;
        $this->interceptableValidator = $interceptableValidator;
        $this->omConfig = $omConfig ?? ObjectManager::getInstance()->get(ConfigInterface::class);
    }

    /**
     * Adds area code
     *
     * @param string $areaCode
     * @return void
     */
    public function addAreaCode($areaCode)
    {
        if (empty($this->areaCodesList[$areaCode])) {
            $this->areaCodesList[] = $areaCode;
        }
    }

    /**
     * Builds interception configuration for all defined classes
     *
     * @param array $definedClasses
     * @return array
     */
    public function getInterceptionConfiguration($definedClasses)
    {
        $interceptedInstances = $this->getInterceptedClasses($definedClasses);
        $inheritedConfig = $this->getPluginsList($interceptedInstances);
        $mergedAreaPlugins = $this->mergeAreaPlugins($inheritedConfig);
        $interceptedMethods = $this->getInterceptedMethods($mergedAreaPlugins);

        return $interceptedMethods;
    }

    /**
     * Get intercepted instances from defined class list
     *
     * @param array $definedClasses
     * @return array
     */
    private function getInterceptedClasses($definedClasses)
    {
        $intercepted = [];
        foreach ($definedClasses as $definedClass) {
            if ($this->interceptionConfig->hasPlugins($definedClass) && $this->typeReader->isConcrete($definedClass)
                && $this->interceptableValidator->validate($definedClass)
            ) {
                $intercepted[] = $definedClass;
            }
        }
        return $intercepted;
    }

    /**
     * Returns plugin list:
     * ['concrete class name' => ['plugin name' => [instance => 'instance name', 'order' => 'Order Number']]]
     *
     * @param array $interceptedInstances
     * @return array
     */
    private function getPluginsList($interceptedInstances)
    {
        $this->cacheManager->setEnabled([CompiledConfig::TYPE_IDENTIFIER], true);
        $this->pluginList->setInterceptedClasses($interceptedInstances);

        $inheritedConfig = [];
        foreach ($this->areaCodesList as $areaKey) {
            $scopePriority = [Area::AREA_GLOBAL];
            $pluginListCloned = clone $this->pluginList;
            if ($areaKey != Area::AREA_GLOBAL) {
                $scopePriority[] = $areaKey;
                $pluginListCloned->setScopePriorityScheme($scopePriority);
            }
            $key = implode('', $scopePriority);
            $inheritedConfig[$key] = $this->filterNullInheritance($pluginListCloned->getPluginsConfig());
        }
        return $inheritedConfig;
    }

    /**
     * Filters plugin inheritance list for instances without plugins, and abstract/interface
     *
     * @param array $pluginInheritance
     * @return array
     */
    private function filterNullInheritance($pluginInheritance)
    {
        $filteredData = [];
        foreach ($pluginInheritance as $instance => $plugins) {
            if ($plugins === null || !$this->typeReader->isConcrete($instance)) {
                continue;
            }

            $pluginInstances = [];
            foreach ($plugins as $plugin) {
                if (in_array($plugin['instance'], $pluginInstances)) {
                    continue;
                }
                $pluginInstances[] = $plugin['instance'];
            }
            $filteredData[$instance] = $pluginInstances;
        }

        return $filteredData;
    }

    /**
     * Merge plugins in areas
     *
     * @param array $inheritedConfig
     * @return array
     */
    private function mergeAreaPlugins($inheritedConfig)
    {
        $mergedConfig = [];
        foreach ($inheritedConfig as $configuration) {
            $mergedConfig = array_merge_recursive($mergedConfig, $configuration);
        }
        foreach ($mergedConfig as &$plugins) {
            $plugins = array_unique($plugins);
        }

        return $mergedConfig;
    }

    /**
     * Returns interception configuration with plugin methods
     *
     * @param array $interceptionConfiguration
     * @return array
     */
    private function getInterceptedMethods(array $interceptionConfiguration): array
    {
        $pluginDefinitionList = new Runtime();
        foreach ($interceptionConfiguration as &$plugins) {
            $pluginsMethods = [];

            foreach ($plugins as $plugin) {
                $plugin = $this->omConfig->getOriginalInstanceType($plugin);

                $pluginsMethods = [
                    ...$pluginsMethods,
                    ...\array_keys(
                        $pluginDefinitionList->getMethodList($plugin)
                    )
                ];
            }

            $plugins = \array_unique($pluginsMethods);
        }

        return $interceptionConfiguration;
    }
}
