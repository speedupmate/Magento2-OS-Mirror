/*
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */
define(['mage/translate'], function ($t) {
    'use strict';

    /**
     * Messages here may not be the final version.  Please check the module's
     * i18n.csv file for final english versions.
     *
     * Messages are kept here as-is for backwards compatibility with translations
     */
    return {
        noChangesNecessary: $t('The address is valid'),
        noAddressFound: $t('We did not find a valid address'),
        changesFound: $t('The address is not valid'),
        adminChangesFound: $t('The intended address could be:'),
        addressUpdated: $t('The address was updated'),
        streetAddressUpdateWarning: $t('Warning: Updating the address will replace all street address fields.')
    };
})
