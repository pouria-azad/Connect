<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Wallet\DepositRequest;
use App\Http\Requests\V1\Wallet\RequestWithdrawRequest;
use App\Http\Requests\V1\Wallet\ReviewWithdrawRequestRequest;
use App\Http\Requests\V1\Wallet\TransferRequest;
use App\Http\Requests\V1\Wallet\UseGiftCardRequest;
use App\Http\Requests\V1\Wallet\AddBankCardRequest;
use App\Http\Requests\V1\Wallet\ReviewBankCardRequest;
use App\Http\Resources\V1\WalletTransactionResource;
use App\Http\Resources\V1\UserBankCardResource;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\GiftCard;
use App\Models\UserBankCard;
use App\Models\WalletWithdrawRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Wallet",
 *     description="API Endpoints for managing user wallet and transactions"
 * )
 */
class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Deposit money to wallet
     *
     * @OA\Post(
     *     path="/api/v1/wallet/deposit",
     *     summary="Deposit money to wallet",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="integer", minimum=1000, maximum=10000000, example=50000),
     *             @OA\Property(property="description", type="string", example="Wallet deposit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deposit successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="شارژ انجام شد"),
     *             @OA\Property(property="balance", type="integer", example=50000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid amount",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="مبلغ واریزی باید بین 1000 تا 10,000,000 تومان باشد")
     *         )
     *     )
     * )
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $amount = $request->amount;
        $description = $request->description;

        DB::transaction(function () use ($user, $amount, $description) {
            $wallet = $user->wallet;
            $wallet->increment('balance', $amount);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'description' => $description,
                'status' => 'completed'
            ]);
        });

        return response()->json([
            'message' => 'شارژ انجام شد',
            'balance' => $user->wallet->balance
        ]);
    }

    /**
     * Use gift card
     *
     * @OA\Post(
     *     path="/api/v1/wallet/use-gift-card",
     *     summary="Use a gift card to add balance to wallet",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="GIFT123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Gift card used successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کارت هدیه با موفقیت استفاده شد"),
     *             @OA\Property(property="balance", type="integer", example=50000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid gift card",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کارت هدیه نامعتبر است")
     *         )
     *     )
     * )
     */
    public function useGiftCard(UseGiftCardRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $wallet = $user->wallet;
        $code = $request->code;

        $giftCard = GiftCard::where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->where('is_active', true)
            ->first();

        if (!$giftCard) {
            return response()->json(['message' => 'کارت هدیه نامعتبر است'], 400);
        }

        DB::transaction(function () use ($wallet, $giftCard, $user) {
            $wallet->balance += $giftCard->current_balance;
            $wallet->save();

            $giftCard->is_used = true;
            $giftCard->used_by_user_id = $user->id;
            $giftCard->redeemed_at = now();
            $giftCard->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'amount' => $giftCard->current_balance,
                'type' => 'deposit_gift_card',
                'description' => 'استفاده از کارت هدیه',
                'status' => 'completed',
                'reference_id' => $giftCard->id,
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'transaction',
                'title' => 'واریز از کارت هدیه',
                'body' => "مبلغ {$giftCard->current_balance} تومان از کارت هدیه به کیف پول شما واریز شد.",
                'data' => json_encode(['transaction_id' => $transaction->id, 'gift_card_id' => $giftCard->id]),
            ]);
        });

        return response()->json(['message' => 'کارت هدیه با موفقیت استفاده شد', 'balance' => (int) $wallet->balance]);
    }

    /**
     * Get wallet transactions
     *
     * @OA\Get(
     *     path="/api/v1/wallet/transactions",
     *     summary="Get user's wallet transactions",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of transactions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WalletTransaction")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function transactions(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $transactions = $user->wallet->transactions()
            ->latest()
            ->paginate($request->get('per_page', 10));

        return WalletTransactionResource::collection($transactions);
    }

    /**
     * Request withdrawal
     *
     * @OA\Post(
     *     path="/api/v1/wallet/withdraw",
     *     summary="Request withdrawal from wallet",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "bank_card_id"},
     *             @OA\Property(property="amount", type="integer", minimum=1000, maximum=10000000, example=50000),
     *             @OA\Property(property="bank_card_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Withdrawal request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="درخواست برداشت ثبت شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="موجودی کافی نیست")
     *         )
     *     )
     * )
     */
    public function requestWithdraw(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;
        $amount = $request->amount;
        if ($wallet->balance < $amount) {
            return response()->json([
                'message' => 'موجودی کافی نیست'
            ], 400);
        }
        $withdrawRequest = \App\Models\WalletWithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'status' => 'pending',
            'bank_card_id' => $request->bank_card_id,
        ]);
        return response()->json([
            'message' => 'درخواست برداشت با موفقیت ثبت شد'
        ]);
    }

    /**
     * Review withdrawal request (Admin only)
     *
     * @OA\Post(
     *     path="/api/v1/wallet/withdraw/{id}/review",
     *     summary="Review withdrawal request (Admin only)",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Withdrawal request ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"approved", "rejected"}),
     *             @OA\Property(property="admin_note", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Request reviewed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="درخواست با موفقیت بررسی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function reviewWithdrawRequest(Request $request, WalletWithdrawRequest $withdrawRequest): JsonResponse
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }
        if (!auth()->guard('sanctum')->check()) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }
        $status = $request->status;
        $adminNote = $request->admin_note;
        \DB::transaction(function () use ($withdrawRequest, $status, $adminNote) {
            $withdrawRequest->status = $status;
            $withdrawRequest->admin_note = $adminNote;
            $withdrawRequest->reviewed_at = now();
            $withdrawRequest->reviewed_by_user_id = auth()->guard('sanctum')->id();
            $withdrawRequest->save();
            $wallet = $withdrawRequest->user->wallet;
            if ($status === 'approved') {
                $wallet->decrement('balance', $withdrawRequest->amount);
                \App\Models\WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $withdrawRequest->user_id,
                    'amount' => -$withdrawRequest->amount,
                    'type' => 'withdraw',
                    'description' => 'برداشت از کیف پول',
                    'status' => 'completed',
                    'reference_id' => $withdrawRequest->id
                ]);
            }
        });
        return response()->json([
            'message' => 'درخواست با موفقیت بررسی شد'
        ]);
    }

    /**
     * Transfer money to another user
     *
     * @OA\Post(
     *     path="/api/v1/wallet/transfer",
     *     summary="Transfer money to another user",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"to_user_id", "amount"},
     *             @OA\Property(property="to_user_id", type="integer", example=2),
     *             @OA\Property(property="amount", type="integer", minimum=1000, maximum=10000000, example=50000),
     *             @OA\Property(property="description", type="string", example="Transfer payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="انتقال با موفقیت انجام شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="موجودی کافی نیست")
     *         )
     *     )
     * )
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $wallet = $user->wallet;
        $amount = $request->amount;
        $destinationUser = User::where('referral_code', $request->referral_code)->first();

        if (!$destinationUser) {
            return response()->json([
                'message' => 'کاربر مورد نظر یافت نشد'
            ], 404);
        }

        if ($wallet->balance < $amount) {
            return response()->json([
                'message' => 'موجودی کافی نیست'
            ], 400);
        }

        DB::transaction(function () use ($wallet, $amount, $destinationUser, $user) {
            $wallet->decrement('balance', $amount);
            $destinationUser->wallet->increment('balance', $amount);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => 'transfer',
                'description' => 'انتقال به کاربر دیگر',
                'status' => 'completed',
                'destination_wallet_id' => $destinationUser->wallet->id
            ]);

            WalletTransaction::create([
                'wallet_id' => $destinationUser->wallet->id,
                'user_id' => $destinationUser->id,
                'amount' => $amount,
                'type' => 'transfer',
                'description' => 'دریافت از کاربر دیگر',
                'status' => 'completed',
                'source_wallet_id' => $wallet->id
            ]);
        });

        return response()->json([
            'message' => 'انتقال با موفقیت انجام شد',
            'balance' => $wallet->balance
        ]);
    }

    /**
     * Add bank card
     *
     * @OA\Post(
     *     path="/api/v1/wallet/bank-cards",
     *     summary="Add a new bank card",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"card_number", "sheba_number", "bank_name", "card_holder_name"},
     *             @OA\Property(property="card_number", type="string", example="6037991234567890"),
     *             @OA\Property(property="sheba_number", type="string", example="IR123456789012345678901234"),
     *             @OA\Property(property="bank_name", type="string", example="بانک ملی"),
     *             @OA\Property(property="card_holder_name", type="string", example="علی محمدی")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank card added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کارت بانکی ثبت شد"),
     *             @OA\Property(property="bank_card", ref="#/components/schemas/UserBankCard")
     *         )
     *     )
     * )
     */
    public function addBankCard(AddBankCardRequest $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $bankCard = UserBankCard::create([
            'user_id' => $user->id,
            'card_number' => $request->card_number,
            'bank_name' => $request->bank_name,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'کارت بانکی با موفقیت ثبت شد',
            'data' => new UserBankCardResource($bankCard)
        ]);
    }

    /**
     * List bank cards
     *
     * @OA\Get(
     *     path="/api/v1/wallet/bank-cards",
     *     summary="Get user's bank cards",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of bank cards",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="bank_cards",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/UserBankCard")
     *             )
     *         )
     *     )
     * )
     */
    public function listBankCards(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        $bankCards = $user->bankCards()
            ->latest()
            ->get();

        return response()->json([
            'data' => UserBankCardResource::collection($bankCards)
        ]);
    }

    /**
     * Review bank card (Admin only)
     *
     * @OA\Post(
     *     path="/api/v1/wallet/bank-cards/{id}/review",
     *     summary="Review bank card (Admin only)",
     *     tags={"Wallet"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Bank card ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"approved", "rejected"}),
     *             @OA\Property(property="admin_note", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bank card reviewed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کارت بانکی با موفقیت بررسی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function reviewBankCard(ReviewBankCardRequest $request, UserBankCard $bankCard): JsonResponse
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user || !$user->is_admin) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }
        if (!auth()->guard('sanctum')->check()) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }
        $bankCard->status = $request->status;
        $bankCard->admin_note = $request->admin_note;
        $bankCard->reviewed_at = now();
        $bankCard->reviewed_by_user_id = auth()->guard('sanctum')->id();
        $bankCard->save();
        return response()->json([
            'message' => 'کارت بانکی با موفقیت بررسی شد',
            'data' => new UserBankCardResource($bankCard)
        ]);
    }
}

?>
