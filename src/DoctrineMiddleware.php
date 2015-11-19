<?php
/**
 * Slim Framework Doctrine middleware (https://github.com/juliangut/slim-doctrine-middleware)
 *
 * @link https://github.com/juliangut/slim-doctrine-middleware for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/slim-doctrine-middleware/master/LICENSE
 */

namespace Jgut\Slim\Middleware;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\EntityManager;

/**
 * Doctrine handler middleware.
 */
class DoctrineMiddleware
{
    /**
     * Helper for creating Doctrine's EntityManager instance
     * We need this in cli-config.php
     *
     * @return Doctrine\ORM\EntityManager
     **/
    public static function createEntityManager($container)
    {
        $options = [
            'annotation_files' => [],
            'annotation_namespaces' => [],
            'annotation_autoloaders' => [],
            'debug' => false
        ];

        $options = array_merge($options, $container['config']['doctrine']);

        // annotation_paths is required
        if ( ! isset($options['annotation_paths'])) {
            throw new \BadMethodCallException('annotation_paths config should be defined');
        }

        // connection is required
        if ( ! isset($options['connection'])) {
            throw new \BadMethodCallException('annotation_paths config should be defined');
        }

        // ------------------------------------------------------------
        // start doctrine setup

        foreach ($options['annotation_files'] as $file) {
            AnnotationRegistry::registerFile($file);
        }

        foreach ($options['annotation_namespaces'] as $namespaceMapping) {
            AnnotationRegistry::registerAutoloadNamespace(reset($namespaceMapping), end($namespaceMapping));
        }

        foreach ($options['annotation_autoloaders'] as $autoloader) {
            AnnotationRegistry::registerLoader($autoloader);
        }

        $annotationPaths = $options['annotation_paths'];

        if ( ! is_array($annotationPaths)) {
            $annotationPaths = array($annotationPaths);
        }

        $config = Setup::createAnnotationMetadataConfiguration($annotationPaths, !$options['debug'], $options['annotation_cache_dir']);
        $config->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

        return EntityManager::create($options['connection'], $config);
    }
}
