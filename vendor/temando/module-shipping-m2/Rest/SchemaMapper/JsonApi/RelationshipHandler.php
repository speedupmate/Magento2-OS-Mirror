<?php
/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */
namespace Temando\Shipping\Rest\SchemaMapper\JsonApi;

use Temando\Shipping\Rest\Response\DataObject\AbstractResource;
use Temando\Shipping\Rest\SchemaMapper\Reflection\PropertyHandlerInterface;

/**
 * Temando REST API JSON API Relationship Handler
 *
 * @package Temando\Shipping\Rest
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.temando.com/
 */
class RelationshipHandler
{
    /**
     * @var PropertyHandlerInterface
     */
    private $propertyHandler;

    /**
     * RelationshipHandler constructor.
     * @param PropertyHandlerInterface $propertyHandler
     */
    public function __construct(PropertyHandlerInterface $propertyHandler)
    {
        $this->propertyHandler = $propertyHandler;
    }

    /**
     * Add related data to a resource.
     *
     * If the related resource is contained in the "included" section, add the
     * full resource. Otherwise add the resource ID only.
     *
     * @param AbstractResource $resource
     * @param ResourceContainerInterface $relatedResources
     * @return void
     */
    public function addRelationships(AbstractResource $resource, ResourceContainerInterface $relatedResources)
    {
        // iterate over each resource's relationships
        foreach ($resource->getRelationships() as $relationship) {
            // replace each relationship by its actual resource representation
            $related = [];
            $relatedIds = [];

            // collect related resources by resource type
            foreach ($relationship->getData() as $relationshipIdentifier) {
                $resourceType = $relationshipIdentifier->getType();
                $relatedResource = $relatedResources->getResource(
                    $relationshipIdentifier->getType(),
                    $relationshipIdentifier->getId()
                );

                if (!$relatedResource) {
                    // resource not included, add resource ID
                    if (!isset($relatedIds[$resourceType])) {
                        $relatedIds[$resourceType] = [];
                    }

                    $relatedIds[$resourceType][]= $relationshipIdentifier->getId();
                } else {
                    // resource included, add full resource
                    if (!isset($related[$resourceType])) {
                        $related[$resourceType] = [];
                    }

                    $related[$resourceType][]= $relatedResource;
                }
            }

            // set related resources by resource type
            foreach ($related as $resourceType => $relatedResource) {
                $setter = $this->propertyHandler->setter($resourceType);
                $method = "{$setter}s";
                if (method_exists($resource, $method)) {
                    call_user_func([$resource, $method], $relatedResource);
                }
            }

            // set related resource IDs by resource type
            foreach ($relatedIds as $resourceType => $relatedResourceIds) {
                $setter = $this->propertyHandler->setter($resourceType);
                $method = "{$setter}Ids";
                if (method_exists($resource, $method)) {
                    call_user_func([$resource, $method], $relatedResourceIds);
                }
            }
        }
    }
}
