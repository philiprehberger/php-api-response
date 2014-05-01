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
     */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data = null,
        public readonly ?array $errors = null,
        public readonly int $statusCode = 200,
        public readonly ?array $meta = null,
    ) {}

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
