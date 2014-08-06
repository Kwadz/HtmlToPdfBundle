<?php
/**
 * @package Plugin
 */
namespace Jihel\Plugin\HtmlToPdfBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Joseph LEMOINE <lemoine.joseph@gmail.com>
 * @link http://www.joseph-lemoine.fr
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jihel_plugin_html_to_pdf');

        $rootNode
            ->children()
                ->arrayNode('constants')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('commands')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('dpi')->defaultValue(150)->cannotBeEmpty()->end()
                        ->scalarNode('wkhtmltopdf')->defaultValue('/usr/bin/wkhtmltopdf')->cannotBeEmpty()->end()
                        ->scalarNode('wkhtmltopdf_args')->defaultValue('-T 0px -L 0px -B 0px -R 0px')->end()
                        ->scalarNode('xvfb')->defaultValue('/usr/bin/xvfb-run')->end()
                        ->scalarNode('xvfb_args')->defaultValue('--auto-servernum --server-args="-screen 0, 1920x1024x24"')->end()
                        ->scalarNode('concatenate')->defaultValue('/usr/bin/pdftk')->cannotBeEmpty()->end()
                        ->scalarNode('concatenate_args')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('tmp_folder')->defaultValue('/tmp')->end()
                ->scalarNode('tmp_prefix')->defaultValue('jihel_pdf-')->end()
                ->booleanNode('use_xvfb')->defaultTrue()->end()
                ->booleanNode('quiet_mode')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
