<?php


namespace Kira0269\LogViewerBundle;


use Kira0269\LogViewerBundle\DependencyInjection\LogViewerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class LogViewerBundle extends Bundle
{
    /**
     * 
     * {@inheritDoc}
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::getContainerExtension()
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new LogViewerExtension();
        }

        return $this->extension;
    }
}