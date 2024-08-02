<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Yves\CheckoutPage\Form\DataProvider;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Spryker\Yves\Kernel\PermissionAwareTrait;
use SprykerShop\Yves\CheckoutPage\Form\DataProvider\ShipmentFormDataProvider as SpyShipmentFormDataProvider;

class ShipmentFormDataProvider extends SpyShipmentFormDataProvider
{
    use PermissionAwareTrait;

    /**
     * @var string
     */
    protected static $co2eData = '';

    /**
     * @deprecated Use {@link createAvailableMethodsByShipmentChoiceList()} instead.
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<string, array<string, int>>
     */
    protected function createAvailableShipmentChoiceList(QuoteTransfer $quoteTransfer)
    {
        $shipmentMethods = [];

        $shipmentMethodsTransfer = $this->getAvailableShipmentMethods($quoteTransfer);
        foreach ($shipmentMethodsTransfer->getMethods() as $shipmentMethodTransfer) {
            $carrierName = $shipmentMethodTransfer->getCarrierName();

            if ($carrierName === null) {
                continue;
            }

            $shipmentMethods[$carrierName] = $shipmentMethods[$carrierName] ?? [];

            $description = $this->getShipmentDescription($shipmentMethodTransfer);
            $shipmentMethods[$carrierName][$description] = $shipmentMethodTransfer->getIdShipmentMethod();
        }
        self::$co2eData = [];

        return $shipmentMethods;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentMethodTransfer $shipmentMethodTransfer
     *
     * @return string|null
     */
    protected function getFreightEmission(ShipmentMethodTransfer $shipmentMethodTransfer): ?string
    {
        $shipmentDescription = $this->translate($shipmentMethodTransfer->getName());
        $data = [
            'endpoint' => '/freight/intermodal',
            'data' => [
                'route' => [
                    [
                        'location' => [
                            'query' => 'New Delhi',
                        ],
                    ],
                    [
                        'transport_mode' => 'road',
                    ],
                    [
                        'transport_mode' => 'rail',
                    ],
                    [
                        'transport_mode' => 'road',
                    ],
                    [
                        'location' => [
                            'query' => 'Mumbai',
                        ],
                    ],
                ],
                'cargo' => [
                    'weight' => 11,
                    'weight_unit' => 'kg',
                ],
            ],
        ];

        $result = '';
        $url = 'https://www.climatiq.io/static/api/send';
        if (!self::$co2eData) {
            $result = $this->sendCurlRequest($url, $data);
            self::$co2eData = $result;
        } else {
            $result = self::$co2eData;
        }
        $result = ((bool)$result) ? json_decode($result, true) : null;
        if (!$result) {
            return null;
        }
        $routeData = $result['route'] ?? [];
        $co2e = null;
        if ($routeData) {
            foreach ($routeData as $route) {
                if ($route['type'] == 'leg') {
                    if (($shipmentDescription == 'Standard' || $shipmentDescription == 'Next Day') && $route['transport_mode'] == 'road') {
                        $co2e = sprintf('CO2e: %s %s', $route['co2e'], $route['co2e_unit']);
                    }

                    if (($shipmentDescription == 'Express' || $shipmentDescription == 'Same Day') && $route['transport_mode'] == 'rail') {
                        $co2e = sprintf('CO2e: %s %s', $route['co2e'], $route['co2e_unit']);
                    }
                }
            }
        }

        return $co2e;
    }

    /**
     * cUrl
     *
     * @param String $url
     * @param array $data
     *
     * @return string|bool
     */
    protected function sendCurlRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $contents = curl_exec($ch);
        curl_close($ch);

        return $contents;
    }

    /**
     * @param \Generated\Shared\Transfer\ShipmentMethodTransfer $shipmentMethodTransfer
     *
     * @return string
     */
    protected function getShipmentDescription(ShipmentMethodTransfer $shipmentMethodTransfer): string
    {
        $shipmentDescription = $this->translate($shipmentMethodTransfer->getName());
        $shipmentDescription = $this->appendDeliveryTime($shipmentMethodTransfer, $shipmentDescription);
        if ($this->can('SeePricePermissionPlugin')) {
            $shipmentDescription = $this->appendShipmentPrice($shipmentMethodTransfer, $shipmentDescription);
        }

        $shipmentDescriptionMessage = $this->getFreightEmission($shipmentMethodTransfer);
        $shipmentDescription = $shipmentDescriptionMessage ? $shipmentDescription . ' [' . $shipmentDescriptionMessage . ']' : $shipmentDescription;

        return $shipmentDescription;
    }
}
