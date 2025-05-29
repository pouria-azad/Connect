<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="WalletTransactionResource",
 *     type="object",
 *     title="Wallet Transaction Resource",
 *     description="منبع تراکنش کیف پول شامل اطلاعات تراکنش",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="شناسه تراکنش",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="integer",
 *         description="مبلغ تراکنش به تومان",
 *         example=50000
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="نوع تراکنش",
 *         enum={"deposit", "withdraw", "transfer", "deposit_gift_card"},
 *         example="deposit"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="توضیحات تراکنش",
 *         example="شارژ کیف پول",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="تاریخ ایجاد تراکنش",
 *         example="2025-05-23 10:00:00"
 *     )
 * )
 */
class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
