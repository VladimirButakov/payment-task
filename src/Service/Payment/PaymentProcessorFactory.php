<?php

declare(strict_types=1);

namespace App\Service\Payment;

class PaymentProcessorFactory
{
    /** @var array<string, PaymentProcessorInterface> */
    private array $processors = [];

    public function __construct(iterable $processors)
    {
        foreach ($processors as $processor) {
            if ($processor instanceof PaymentProcessorInterface) {
                $this->processors[$processor->getName()] = $processor;
            }
        }
    }

    /**
     * @throws \InvalidArgumentException если процессор не найден
     */
    public function getProcessor(string $name): PaymentProcessorInterface
    {
        if (!isset($this->processors[$name])) {
            throw new \InvalidArgumentException(
                sprintf('Payment processor "%s" not found. Available: %s', 
                    $name, 
                    implode(', ', array_keys($this->processors))
                )
            );
        }

        return $this->processors[$name];
    }
}

