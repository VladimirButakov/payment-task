<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Input\CalculatePriceRequest;
use App\Controller\Input\PurchaseRequest;
use App\Controller\Output\CalculatePriceResponse;
use App\Controller\Output\PurchaseResponse;
use App\Exception\CouponNotFoundException;
use App\Exception\PaymentException;
use App\Exception\ProductNotFoundException;
use App\Exception\TaxNotFoundException;
use App\Service\Dto\PriceCalculationData;
use App\Service\Dto\PurchaseData;
use App\Service\PaymentInterface;
use App\Service\PriceCalculatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PriceCalculatorInterface $priceCalculator,
        private readonly PaymentInterface $paymentService,
    ) {
    }

    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function calculatePrice(
        #[MapRequestPayload] CalculatePriceRequest $request
    ): JsonResponse {
        try {
            $data = new PriceCalculationData(
                productId: $request->product,
                taxNumber: $request->taxNumber,
                couponCode: $request->couponCode,
            );
            $price = $this->priceCalculator->calculate($data);
            $response = new CalculatePriceResponse();
            $response->price = $price;

            return $this->json($response);
        } catch (ProductNotFoundException|CouponNotFoundException|TaxNotFoundException|\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (PaymentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(
        #[MapRequestPayload] PurchaseRequest $request
    ): JsonResponse {
        try {
            $data = new PurchaseData(
                productId: $request->product,
                taxNumber: $request->taxNumber,
                paymentProcessor: $request->paymentProcessor,
                couponCode: $request->couponCode,
            );
            $price = $this->paymentService->purchase($data);
            $response = new PurchaseResponse();
            $response->success = true;
            $response->price = $price;
            $response->message = 'Payment processed successfully';

            return $this->json($response);
        } catch (ProductNotFoundException|CouponNotFoundException|TaxNotFoundException|\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (PaymentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Payment failed: ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
