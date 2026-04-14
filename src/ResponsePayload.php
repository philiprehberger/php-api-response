<?php

declare(strict_types=1);

namespace PhilipRehberger\ApiResponse;

use JsonSerializable;
use Stringable;

final class ResponsePayload implements JsonSerializable, Stringable
{
    /**
     * @param  array<string, mixed>|null  $errors
     * @param  array<string, mixed>|null  $meta
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data = null,
        public readonly ?array $errors = null,
        public readonly int $statusCode = 200,
        public readonly ?array $meta = null,
        public readonly array $headers = [],
    ) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public function withMeta(array $meta): self
    {
        return new self(
            success: $this->success,
            message: $this->message,
            data: $this->data,
            errors: $this->errors,
            statusCode: $this->statusCode,
            meta: array_merge($this->meta ?? [], $meta),
            headers: $this->headers,
        );
    }

    /**
     * @param  array<string, string>  $headers
     */
    public function withHeaders(array $headers): self
    {
        return new self(
            success: $this->success,
            message: $this->message,
            data: $this->data,
            errors: $this->errors,
            statusCode: $this->statusCode,
            meta: $this->meta,
            headers: array_merge($this->headers, $headers),
        );
    }

    public function withPagination(int $total, int $page, int $perPage): static
    {
        return new self(
            success: $this->success,
            message: $this->message,
            data: $this->data,
            errors: $this->errors,
            statusCode: $this->statusCode,
            meta: array_merge($this->meta ?? [], [
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => (int) ceil($total / max($perPage, 1)),
                ],
            ]),
            headers: $this->headers,
        );
    }

    public function withStatusCode(int $code): self
    {
        return new self(
            success: $this->success,
            message: $this->message,
            data: $this->data,
            errors: $this->errors,
            statusCode: $code,
            meta: $this->meta,
            headers: $this->headers,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
        ];

        if ($this->errors !== null) {
            $result['errors'] = $this->errors;
        }

        if ($this->meta !== null) {
            $result['meta'] = $this->meta;
        }

        return $result;
    }

    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
