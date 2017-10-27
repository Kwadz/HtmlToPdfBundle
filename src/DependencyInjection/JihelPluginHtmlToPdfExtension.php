<?php
/**
 * @package Plugin
 */
namespace Jihel\Plugin\HtmlToPdfBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class JihelPluginHtmlToPdfExtension
 *
 * @author Joseph LEMOINE <lemoine.joseph@gmail.com>
 * @link http://www.joseph-lemoine.fr
 */
class JihelPluginHtmlToPdfExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.yml');
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this
            ->registerConstants($config, $container)
            ->registerParameters($config, $container)
        ;

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('generator.yml');
    }

    /**
     * Register only constant array
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param string           $prefix
     * @return $this
     */
    protected function registerConstants(array &$config, ContainerBuilder $container, $prefix = '')
    {
        $constants = [];
        foreach ($config['constants'] as $constant) {
            $constants[$constant] = constant($constant);
        }

        $container->setParameter('jihel.plugin.html_to_pdf.constants', $constants);
        unset($config['constants']);

        return $this;
    }

    /**
     * Lazy register parameters recursively and name parameter key as in configuration file,
     * with a dot as deep separator.
     * Arrays are still completely available from related key.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     * @param string           $prefix
     * @return $this
     */
    protected function registerParameters(array $config, ContainerBuilder $container, $prefix = '')
    {
        if (count($config)) {
            foreach ($config as $key => $value) {
                if (is_array($value)) {
                    $this->registerParameters($value, $container, $prefix.$key.'.');
                }

                $container->setParameter(
                    sprintf('jihel.plugin.html_to_pdf.%s', $prefix.$key),
                    $value
                );
            }
        }

        return $this;
    }
}
