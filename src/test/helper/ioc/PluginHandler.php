<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class for the test.
 */
class PluginHandler
{
    /**
     * some passed arguments
     *
     * @var  array
     */
    private array $args;

    /**
     * constructor
     *
     * @param  scalar[]  $configList  list of config values
     * @param  scalar[]  $configMap   map of config values
     * @param  Plugin[]  $pluginList  list of plugins
     * @param  Plugin[]  $pluginMap   map of plugins
     * @param  Plugin    $std
     * @param  mixed     $answer
     * @param  array     $list
     * @param  array     $map
     * @List{configList}('listConfig')
     * @Map{configMap}('mapConfig')
     * @List{pluginList}(stubbles\test\ioc\Plugin.class)
     * @Map{pluginMap}(stubbles\test\ioc\Plugin.class)
     * @Named{std}('foo')
     * @Named{answer}('foo')
     * @List{list}('aList')
     * @Map{map}('aMap')
     */
    public function __construct(
            private array $configList,
            private array $configMap,
            private ?array $pluginList = null,
            private ?array $pluginMap = null,
            ?Plugin $std = null,
            mixed $answer = null,
            ?array $list = null,
            ?array $map = null)
    {
        $this->configList = $configList;
        $this->configMap  = $configMap;
        $this->pluginList = $pluginList;
        $this->pluginMap  = $pluginMap;
        $this->args  = [
            'std'    => $std,
            'answer' => $answer,
            'list'   => $list,
            'map'    => $map
        ];
    }

    /**
     * returns list of config values
     *
     * @return  scalar[]
     */
    public function getConfigList(): array
    {
        return $this->configList;
    }

    /**
     * returns list of plugins
     *
     * @return  Plugin[]
     */
    public function getPluginList(): array
    {
        return $this->pluginList;
    }

    /**
     * returns map of config values
     *
     * @return  scalar[]
     */
    public function getConfigMap(): array
    {
        return $this->configMap;
    }

    /**
     * returns map of plugins
     *
     * @return  Plugin[]
     */
    public function getPluginMap(): array
    {
        return $this->pluginMap;
    }

    /**
     * returns bunch of values
     *
     * @return  array
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}
