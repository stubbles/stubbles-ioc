<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\lang;
use stubbles\lang\exception\FileNotFoundException;
use stubbles\lang\exception\IllegalArgumentException;
use stubbles\streams\file\FileInputStream;
/**
 * Class to load resources from arbitrary locations.
 *
 * @since  1.6.0
 * @Singleton
 */
class ResourceLoader
{
    /**
     * root path of application
     *
     * @type  Rootpath
     */
    private $rootpath;

    /**
     * constructor
     *
     * If no root path is given it tries to detect it automatically.
     *
     * @param  string|Rootpath  $rootpath  optional
     */
    public function __construct($rootpath = null)
    {
        $this->rootpath = Rootpath::castFrom($rootpath);
    }

    /**
     * returns resource uri from local project
     *
     * The method will always return a uri, even if the resource does not exist.
     *
     * @param   string  $resourceName
     * @return  string
     * @since   3.1.2
     * @deprecated  since 4.0.0, use either open() or load(), will be removed with 5.0.0
     */
    public function getProjectResourceUri($resourceName)
    {
        return $this->rootpath
                . DIRECTORY_SEPARATOR . 'src'
                . DIRECTORY_SEPARATOR . 'main'
                . DIRECTORY_SEPARATOR . 'resources'
                . DIRECTORY_SEPARATOR . $resourceName;
    }

    /**
     * opens an input stream to read resource contents
     *
     * Resource can either be a complete path to a resource or a local path. In
     * case it is a local path it is searched within the root path.
     * It is not possible to open resources outside of the root path by
     * providing a complete path, a complete path must always lead to a resource
     * located within the root path.
     *
     * @param   string  $resource
     * @return  \stubbles\streams\InputStream
     * @since   4.0.0
     */
    public function open($resource)
    {
        return new FileInputStream($this->checkedPathFor($resource));
    }

    /**
     * loads resource contents
     *
     * Resource can either be a complete path to a resource or a local path. In
     * case it is a local path it is searched within the root path.
     * It is not possible to load resources outside of the root path by
     * providing a complete path, a complete path must always lead to a resource
     * located within the root path.
     *
     * @param   string  $resource
     * @return  string
     * @since   4.0.0
     */
    public function load($resource)
    {
        return file_get_contents($this->checkedPathFor($resource));
    }

    /**
     * completes path for given resource
     *
     * In case the complete path is outside of the root path an
     * IllegalArgumentException is thrown.
     *
     * @param   string  $resource
     * @return  string
     * @throws  FileNotFoundException
     * @throws  IllegalArgumentException
     */
    private function checkedPathFor($resource)
    {
        $completePath = $this->completePath($resource);
        if (!file_exists($completePath)) {
            throw new FileNotFoundException($completePath);
        }

        if (!$this->rootpath->contains($completePath)) {
            throw new IllegalArgumentException('Given resource "' . $resource . '" located at "' . $completePath . '" is not inside root path ' . $this->rootpath);
        }

        return $completePath;
    }

    /**
     * returns complete path for given resource
     *
     * @param   string  $resource
     * @return  string
     */
    private function completePath($resource)
    {
        if (substr($resource, 0, strlen($this->rootpath)) == $this->rootpath) {
            return $resource;
        }

        return $this->rootpath
                . DIRECTORY_SEPARATOR . 'src'
                . DIRECTORY_SEPARATOR . 'main'
                . DIRECTORY_SEPARATOR . 'resources'
                . DIRECTORY_SEPARATOR . $resource;
    }

    /**
     * returns a list of all available uris for a resource
     *
     * @param   string  $resourceName  the resource to retrieve the uris for
     * @return  string[]
     * @since   4.0.0
     */
    public function availableResourceUris($resourceName)
    {
        return array_values(
                array_filter(
                        array_map(
                              function($sourcePath) use($resourceName)
                              {
                                  return str_replace('/src/main/php', '/src/main/resources', $sourcePath) . DIRECTORY_SEPARATOR . $resourceName;
                              },
                              $this->rootpath->sourcePathes()
                        ),
                        function($resourcePath)
                        {
                            return file_exists($resourcePath);
                        }
                )
        );
    }

    /**
     * return all uris for a resource
     *
     * @param   string  $resourceName  the resource to retrieve the uris for
     * @return  string[]
     * @deprecated  since 4.0.0, use listResourceUris() instead, will be removed with 5.0.0
     */
    public function getResourceUris($resourceName)
    {
        return $this->availableResourceUris($resourceName);
    }

    /**
     * returns root path
     *
     * @return  string
     * @deprecated  since 4.0.0, use stubbles\lang\Rootpath instead, will be removed with 5.0.0
     */
    public static function getRootPath()
    {
        static $rootpath = null;
        if (null === $rootpath) {
            $rootpath = new Rootpath();
        }

        return (string) $rootpath;
    }

    /**
     * returns root path for non-static mockable calls
     *
     * @return  string
     * @deprecated  since 4.0.0, use stubbles\lang\Rootpath instead, will be removed with 5.0.0
     */
    public function getRoot()
    {
        return (string) $this->rootpath;
    }
}
