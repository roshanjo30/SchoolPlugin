<?php declare(strict_types=1);

namespace SchoolPlugin\Checkout;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SchoolPriceCartProcessor implements CartProcessorInterface
{
    public function __construct(
        private readonly QuantityPriceCalculator $quantityPriceCalculator
    ) {
    }


    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {

        $priceMap = $data->get(
            SchoolPriceCartCollector::KEY
        );

        if (!is_array($priceMap) || $priceMap === []) {
            return;
        }


        foreach (
            $toCalculate
                ->getLineItems()
                ->filterFlatByType(LineItem::PRODUCT_LINE_ITEM_TYPE)
            as $lineItem
        ) {

            $productId = $lineItem->getReferencedId();

            if (
                $productId === null
                || !array_key_exists($productId, $priceMap)
            ) {
                continue;
            }


            $taxId = $lineItem->getPayloadValue('taxId');

            if (!$taxId) {
                continue;
            }


            $definition = new QuantityPriceDefinition(
                $priceMap[$productId],
                $context->buildTaxRules($taxId),
                $lineItem->getQuantity()
            );


            $lineItem->setPriceDefinition($definition);

            $lineItem->setPrice(
                $this->quantityPriceCalculator->calculate(
                    $definition,
                    $context
                )
            );
        }
    }
}