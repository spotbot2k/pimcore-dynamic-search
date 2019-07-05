<?php

namespace DynamicSearchBundle\Registry;

use DynamicSearchBundle\Normalizer\ResourceIdBuilderInterface;
use DynamicSearchBundle\Normalizer\ResourceNormalizerInterface;

interface ResourceNormalizerRegistryInterface
{
    /**
     * @param string $dataProviderName
     * @param string $identifier
     *
     * @return bool
     */
    public function hasNormalizerForDataProvider(string $dataProviderName, string $identifier);

    /**
     * @param string $dataProviderName
     * @param string $identifier
     *
     * @return ResourceNormalizerInterface
     */
    public function getNormalizerForDataProvider(string $dataProviderName, string $identifier);

    /**
     * @param string $dataProviderName
     * @param string $identifier
     *
     * @return bool
     */
    public function hasIdBuilderForDataProvider(string $dataProviderName, string $identifier);

    /**
     * @param string $dataProviderName
     * @param string $identifier
     *
     * @return ResourceIdBuilderInterface
     */
    public function getIdBuilderForDataProvider(string $dataProviderName, string $identifier);

}