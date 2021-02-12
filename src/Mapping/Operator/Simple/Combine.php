<?php


namespace Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Operator\Simple;



use Pimcore\Bundle\DataHubBatchImportBundle\Exception\InvalidConfigurationException;
use Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Operator\AbstractOperator;
use Pimcore\Bundle\DataHubBatchImportBundle\Mapping\Type\TransformationDataTypeService;

class Combine extends AbstractOperator
{

    /**
     * @var string
     */
    protected $glue;

    public function setSettings(array $settings): void
    {
        $this->glue = $settings['glue'] ?? ' ';
    }


    public function process($inputData, bool $dryRun = false)
    {
        if(!is_array($inputData)) {
            $inputData = [$inputData];
        }
        return implode($this->glue, $inputData);
    }

    /**
     * @param string $inputType
     * @param int|null $index
     * @return string
     * @throws InvalidConfigurationException
     */
    public function evaluateReturnType(string $inputType, int $index = null): string {

        if(!$inputType === TransformationDataTypeService::DEFAULT_ARRAY) {
            throw new InvalidConfigurationException(sprintf("Unsupported input type '%s' for combine operator at transformation position %s", $inputType, $index));
        }

        return TransformationDataTypeService::DEFAULT_TYPE;

    }
}