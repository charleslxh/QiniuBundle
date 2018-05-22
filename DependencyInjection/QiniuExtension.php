<?php
namespace Charleslxh\QiniuBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class QiniuExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        
        $this->registerServices($configs, $container);
    }

    public function getAlias()
    {
        return 'qiniu';
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }

    private function registerServices(array $configs, ContainerBuilder $container)
    {
        if (class_exists(\Qiniu\Auth::class)) {
            $definition = new Definition();
            $definition->setClass(\Qiniu\Auth::class);
            $definition->setArguments(array($configs['access_key'], $configs['secret_key']));
            $definition->setPublic(true);
            $container->setDefinition('qiniu.auth', $definition);
        }

        if (class_exists(\Qiniu\Cdn\CdnManager::class)) {
            $definition = new Definition();
            $definition->setClass(\Qiniu\Cdn\CdnManager::class);
            $definition->setArguments(array(new Reference('qiniu.auth')));
            $definition->setPublic(true);
            $container->setDefinition('qiniu.cdn', $definition);
        }

        if (class_exists(\Qiniu\Storage\BucketManager::class)) {
            $definition = new Definition();
            $definition->setClass(\Qiniu\Storage\BucketManager::class);
            $definition->setArguments(array(new Reference('qiniu.auth')));
            $definition->setPublic(true);
            $container->setDefinition('qiniu.bucket', $definition);
        }

        if (class_exists(\Qiniu\Storage\UploadManager::class)) {
            $definition = new Definition();
            $definition->setClass(\Qiniu\Storage\UploadManager::class);
            $definition->setPublic(true);
            $container->setDefinition('qiniu.uploader', $definition);
        }

        if (class_exists(\Qiniu\Processing\ImageUrlBuilder::class)) {
            $definition = new Definition();
            $definition->setClass(\Qiniu\Processing\ImageUrlBuilder::class);
            $definition->setPublic(true);
            $container->setDefinition('qiniu.img_processor', $definition);
        }

        if (class_exists(\Qiniu\Processing\PersistentFop::class)) {
            $definition = new Definition();
            $definition->setClass(\Qiniu\Processing\PersistentFop::class);
            $definition->setArguments(array(new Reference('qiniu.auth')));
            $definition->setPublic(true);
            $container->setDefinition('qiniu.pfop_processor', $definition);
        }
    }
}