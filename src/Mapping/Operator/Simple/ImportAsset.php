<?php

namespace Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Operator\Simple;

use Pimcore\Bundle\DataHubBatchImportBundle\Exception\InvalidConfigurationException;
use Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Operator\AbstractOperator;
use Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Type\TransformationDataTypeService;
use Pimcore\Bundle\DataHubBatchImportBundle\PimcoreDataHubBatchImportBundle;
use Pimcore\Log\FileObject;
use Pimcore\Model\Asset;
use Pimcore\Model\Element\Service;

class ImportAsset extends AbstractOperator
{

    /**
     * @var string
     */
    protected $parentFolderPath;

    /**
     * @var bool
     */
    protected $useExisting;

    public function setSettings(array $settings): void
    {
        $this->parentFolderPath = $settings['parentFolder'] ?? '/';
        $this->useExisting = $settings['useExisting'] ?? false;
    }


    public function process($inputData, bool $dryRun = false)
    {

        $returnScalar = false;
        if(!is_array($inputData)) {
            $returnScalar = true;
            $inputData = [$inputData];
        }

        $assets = [];

        foreach($inputData as $data) {

            $fileUrl = trim($data);
            $filename = Service::getValidKey(basename($fileUrl), 'asset');

            $asset = null;
            if($this->useExisting) {
                $asset = Asset::getByPath($this->parentFolderPath . '/' . $filename);
            }
            if(empty($asset)) {
                $assetData = @file_get_contents($fileUrl);

                if($assetData) {

                    $asset = new Asset();
                    $asset->setParent(Asset\Service::createFolderByPath($this->parentFolderPath));
                    $filename = $this->getSafeFilename($this->parentFolderPath, $filename);
                    $asset->setKey($filename);
                    $asset->setData($assetData);

                    if ($dryRun) {
                        $asset->correctPath();
                    } else {
                        $asset->save();
                    }
                } else {
                    $this->applicationLogger->error("Could not import asset data from `$fileUrl` ", [
                        'component' => PimcoreDataHubBatchImportBundle::LOGGER_COMPONENT_PREFIX . $this->configName,
                    ]);
                }
            }

            if(!empty($asset)) {
                $assets[] = $asset;
            }
        }

        if($returnScalar) {
            return reset($assets);
        } else {
            return $assets;
        }

    }

    protected function getSafeFilename($targetPath, $filename)
    {
        $pathinfo = pathinfo($filename);
        $originalFilename = $pathinfo['filename'];
        $originalFileextension = empty($pathinfo['extension']) ? '' : '.' . $pathinfo['extension'];
        $count = 1;

        if ($targetPath == '/') {
            $targetPath = '';
        }

        while (true) {
            if (Asset\Service::pathExists($targetPath . '/' . $filename)) {
                $filename = $originalFilename . '_' . $count . $originalFileextension;
                $count++;
            } else {
                return $filename;
            }
        }
    }

    /**
     * @param string $inputType
     * @param int|null $index
     * @return string
     * @throws InvalidConfigurationException
     */
    public function evaluateReturnType(string $inputType, int $index = null): string {

        if($inputType === TransformationDataTypeService::DEFAULT_TYPE) {
            return TransformationDataTypeService::ASSET;
        } else if($inputType === TransformationDataTypeService::DEFAULT_ARRAY) {
            return TransformationDataTypeService::ASSET_ARRAY;
        } else {
            throw new InvalidConfigurationException(sprintf("Unsupported input type '%s' for import/load asset operator at transformation position %s", $inputType, $index));
        }

    }


    public function generateResultPreview($inputData)
    {
        $returnScalar = false;
        if(!is_array($inputData)) {
            $returnScalar = true;
            $inputData = [$inputData];
        }

        foreach($inputData as &$data) {

            if($data instanceof Asset) {
                $data = 'Asset: ' . $data->getFullPath();
            }

        }

        if($returnScalar) {
            return reset($inputData);
        } else {
            return $inputData;
        }
    }
}