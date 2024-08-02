<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Yves\CheckoutPage;

use Pyz\Yves\CheckoutPage\Form\DataProvider\ShipmentFormDataProvider;
use SprykerShop\Yves\CheckoutPage\CheckoutPageFactory as SpyCheckoutPageFactory;

/**
 * @method \SprykerShop\Yves\CheckoutPage\CheckoutPageConfig getConfig()
 */
class CheckoutPageFactory extends SpyCheckoutPageFactory
{
    /**
     * @return \Pyz\Yves\CheckoutPage\Form\DataProvider\ShipmentFormDataProvider
     */
    public function createShipmentDataProvider(): ShipmentFormDataProvider
    {
        return new ShipmentFormDataProvider(
            $this->getShipmentClient(),
            $this->getGlossaryStorageClient(),
            $this->getLocaleClient(),
            $this->getMoneyPlugin(),
            $this->getShipmentService(),
            $this->getConfig(),
            $this->getProductBundleClient(),
        );
    }
}
