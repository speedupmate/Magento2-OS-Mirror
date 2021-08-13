<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

declare(strict_types=1);

namespace Vertex\Tax\Model\Plugin;

use Magento\Customer\Model\Metadata\Form;
use Magento\Framework\App\RequestInterface;
use Vertex\Tax\Model\Data\CustomerCountry;
use Vertex\Tax\Model\Config;

/**
 * Includes extension attributes from frontend forms in the customer data object
 */
class ExtensionAttributesFrontendForm
{
    /** @var Config */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Includes allowed extension attributes in the result data
     *
     * @param Form $subject
     * @param array $result
     * @param RequestInterface $request
     * @return array
     */
    public function afterExtractData(Form $subject, $result, RequestInterface $request): array
    {
        if ($this->config->isVertexActive()) {
            $extensionAttributes = $request->getParam('extension_attributes');

            if (is_array($extensionAttributes)) {
                foreach ($extensionAttributes as $attribute_code => $value) {
                    if (in_array($attribute_code, $this->getAllowedAttributes())) {
                        $result['extension_attributes'][$attribute_code] = $value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Includes extension attributes in the compacted data, if present
     *
     * @param Form $subject
     * @param array $result
     * @param array $data
     * @return array
     */
    public function afterCompactData(Form $subject, $result, $data): array
    {
        if ($this->config->isVertexActive()) {
            if (isset($data['extension_attributes'])) {
                $result['extension_attributes'] = $data['extension_attributes'];
            }
        }

        return $result;
    }

    /**
     * Returns a list of allowed attributes
     *
     * @return array
     */
    private function getAllowedAttributes(): array
    {
        return [
            CustomerCountry::EXTENSION_ATTRIBUTE_CODE
        ];
    }
}
