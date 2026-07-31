<?php declare(strict_types=1);

namespace SchoolPlugin\Storefront\Controller;

use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;


#[Route(defaults: ['_routeScope' => ['storefront']])]
class SchoolCartController extends StorefrontController
{
    public function __construct(
        private readonly CartService $cartService
    ) {
    }


    #[Route(
        path: '/school/{schoolId}/add/{productId}',
        name: 'frontend.school.cart.add',
        methods: ['POST']
    )]
    public function add(
        string $schoolId,
        string $productId,
        SalesChannelContext $context
    ): RedirectResponse {

        $cart = $this->cartService->getCart(
            $context->getToken(),
            $context
        );


        $lineItem = new LineItem(
            $productId,
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId,
            1
        );


        $lineItem->setPayloadValue(
            'schoolId',
            $schoolId
        );


        $this->cartService->add(
            $cart,
            $lineItem,
            $context,
        );


        return $this->redirectToRoute(
            'frontend.checkout.cart.page'
        );
    }
}