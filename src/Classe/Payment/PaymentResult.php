<?php

namespace App\Classe\Payment;

/**
 * Value object to standardize payment responses
 */
class PaymentResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';

    private string $status;
    private string $transactionId;
    private ?string $errorMessage;
    private array $responseData;

    public function __construct(
        string $status,
        string $transactionId,
        ?string $errorMessage = null,
        array $responseData = []
    ) {
        $this->status = $status;
        $this->transactionId = $transactionId;
        $this->errorMessage = $errorMessage;
        $this->responseData = $responseData;
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
