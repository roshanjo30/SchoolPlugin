<?php declare(strict_types=1);

namespace SchoolPlugin\Checkout;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;

class SchoolPriceCartProcessor implements CartProcessorInterface
{
    public function __construct(
        private readonly EntityRepository $schoolProductPriceRepository,
        private readonly RequestStack $requestStack
    ) {
    }

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {

        $session = $this->requestStack->getSession();
        if (!$session) {
            return;
        }

        $schoolId = $session->get('selected_school_id');
        if (!$schoolId) {
            return;
        }

        foreach ($toCalculate->getLineItems() as $lineItem) {
            if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }

            $productId = $lineItem->getReferencedId();
            if (!$productId) {
                continue;
            }

            $criteria = new Criteria();
            $criteria->addFilter(
                new EqualsFilter(
                    'schoolId',
                    $schoolId
                )
            );

            $criteria->addFilter(
                new EqualsFilter(
                    'productId',
                    $productId
                )
            );

            $schoolPriceEntity = $this->schoolProductPriceRepository
                ->search(
                    $criteria,
                    $context->getContext()
                )
                ->first();

            if (!$schoolPriceEntity) {
                continue;
            }

            $schoolPrice = (float) $schoolPriceEntity->getPrice();
            $taxId = $lineItem->getPayloadValue('taxId');
            if (!$taxId) {
                continue;
            }

            $taxRules = $context->buildTaxRules($taxId);
            $lineItem->setPriceDefinition(
                new QuantityPriceDefinition(
                    $schoolPrice,
                    $taxRules,
                    $lineItem->getQuantity()
                )
            );
        }
    }
}