<?php

declare(strict_types=1);

namespace Shwary\DTOs;

use DateTimeImmutable;
use Exception;
use JsonSerializable;
use Shwary\Enums\TransactionStatus;

final readonly class Transaction implements JsonSerializable
{
    /**
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        public string             $id,
        public string             $userId,
        public int                $amount,
        public string             $currency,
        public string             $type,
        public TransactionStatus  $status,
        public string             $recipientPhoneNumber,
        public string             $referenceId,
        public ?array             $metadata,
        public ?string            $failureReason,
        public ?DateTimeImmutable $completedAt,
        public DateTimeImmutable  $createdAt,
        public DateTimeImmutable  $updatedAt,
        public bool               $isSandbox,
        public ?string            $pretiumTransactionId = null,
        public ?string            $error = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): self
    {
        $completedAt = $data['completedAt'] ?? $data['completed_at'] ?? null;
        $createdAt = $data['createdAt'] ?? $data['created_at'] ?? null;
        $updatedAt = $data['updatedAt'] ?? $data['updated_at'] ?? null;

        $id = $data['id'] ?? '';
        $userId = $data['userId'] ?? $data['user_id'] ?? '';
        $amount = $data['amount'] ?? 0;
        $currency = $data['currency'] ?? '';
        $type = $data['type'] ?? 'deposit';
        $status = $data['status'] ?? 'pending';
        $recipientPhone = $data['recipientPhoneNumber'] ?? $data['recipient_phone_number'] ?? '';
        $referenceId = $data['referenceId'] ?? $data['reference_id'] ?? '';
        $failureReason = $data['failureReason'] ?? $data['failure_reason'] ?? null;
        $pretiumId = $data['pretiumTransactionId'] ?? $data['pretium_transaction_id'] ?? null;
        $error = $data['error'] ?? null;

        /** @var array<string, mixed>|null $metadata */
        $metadata = isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : null;

        return new self(
            id: is_scalar($id) ? (string) $id : '',
            userId: is_scalar($userId) ? (string) $userId : '',
            amount: is_numeric($amount) ? (int) $amount : 0,
            currency: is_scalar($currency) ? (string) $currency : '',
            type: is_scalar($type) ? (string) $type : 'deposit',
            status: TransactionStatus::from(is_scalar($status) ? (string) $status : 'pending'),
            recipientPhoneNumber: is_scalar($recipientPhone) ? (string) $recipientPhone : '',
            referenceId: is_scalar($referenceId) ? (string) $referenceId : '',
            metadata: $metadata,
            failureReason: is_scalar($failureReason) ? (string) $failureReason : null,
            completedAt: is_string($completedAt)
                ? new DateTimeImmutable($completedAt) 
                : null,
            createdAt: new DateTimeImmutable(is_string($createdAt) ? $createdAt : 'now'),
            updatedAt: new DateTimeImmutable(is_string($updatedAt) ? $updatedAt : 'now'),
            isSandbox: (bool) ($data['isSandbox'] ?? $data['is_sandbox'] ?? false),
            pretiumTransactionId: is_scalar($pretiumId) ? (string) $pretiumId : null,
            error: is_scalar($error) ? (string) $error : null,
        );
    }

    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === TransactionStatus::FAILED;
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'type' => $this->type,
            'status' => $this->status->value,
            'recipientPhoneNumber' => $this->recipientPhoneNumber,
            'referenceId' => $this->referenceId,
            'metadata' => $this->metadata,
            'failureReason' => $this->failureReason,
            'completedAt' => $this->completedAt?->format('c'),
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
            'isSandbox' => $this->isSandbox,
            'pretiumTransactionId' => $this->pretiumTransactionId,
            'error' => $this->error,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
