<?php
/**
 * Copyright 2016 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\Login\Model\Customer;

use Amazon\Core\Api\Data\AmazonCustomerInterface;
use Amazon\Login\Api\Customer\CompositeMatcherInterface;
use Amazon\Login\Api\Customer\MatcherInterface;

class CompositeMatcher implements CompositeMatcherInterface
{
    /**
     * @var MatcherInterface[]
     */
    private $matchers;

    /**
     * CompositeMatcher constructor.
     *
     * @param array $matchers
     */
    public function __construct(array $matchers)
    {
        $this->matchers = $matchers;
    }

    /**
     * {@inheritDoc}
     */
    public function match(AmazonCustomerInterface $amazonCustomer)
    {
        foreach ($this->matchers as $matcher) {
            if ($customerData = $matcher->match($amazonCustomer)) {
                return $customerData;
            }
        }

        return null;
    }
}
