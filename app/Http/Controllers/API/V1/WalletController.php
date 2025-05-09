<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Wallet\depositRequest;
use App\Http\Requests\V1\Wallet\requestWithdrawRequest;
use App\Http\Requests\V1\Wallet\TransferRequest;
use App\Http\Resources\V1\WalletTransactionResource;
use App\Models\User;
use App\Models\WalletWithdrawRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/wallet/deposit",
     *     summary="شارژ کیف پول",
     *     description="کاربر می‌تواند مبلغی به کیف پول خود واریز کند. نیاز به توکن احراز هویت Sanctum دارد که باید در هدر Authorization به‌صورت Bearer {token} ارسال شود.",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="integer", example=20000, description="مبلغ واریزی (باید عدد مثبت باشد)"),
     *             @OA\Property(property="description", type="string", example="شارژ برای خرید", description="توضیحات اختیاری برای تراکنش", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="شارژ با موفقیت انجام شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="شارژ انجام شد"),
     *             @OA\Property(property="balance", type="integer", example=40000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="درخواست نامعتبر",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="مقدار واریزی نامعتبر است")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function deposit(depositRequest $request): JsonResponse
    {
        $user = auth()->user();
        DB::transaction(function () use ($user, $request) {
            $user->walletTransactions()->create([
                'amount' => $request->amount,
                'type' => 'deposit',
                'description' => $request->description,
            ]);
            $user->updateBalance($request->amount);
        });

        return response()->json(['message' => 'شارژ انجام شد', 'balance' => $user->wallet_balance]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallet/transactions",
     *     summary="لیست تراکنش‌های کیف پول",
     *     description="دریافت لیست تراکنش‌های کیف پول کاربر با امکان صفحه‌بندی. نیاز به توکن Sanctum دارد.",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="تعداد تراکنش‌ها در هر صفحه (پیش‌فرض: 10)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="لیست تراکنش‌ها با موفقیت دریافت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="amount", type="integer", example=5000),
     *                 @OA\Property(property="type", type="string", example="deposit"),
     *                 @OA\Property(property="description", type="string", example="شارژ اولیه"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T14:22:00Z")
     *             )),
     *             @OA\Property(property="links", type="object", description="لینک‌های صفحه‌بندی"),
     *             @OA\Property(property="meta", type="object", description="متادیتا صفحه‌بندی")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function transactions(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 10);
        $transactions = auth()->user()->walletTransactions()->latest()->paginate($perPage);

        return WalletTransactionResource::collection($transactions);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/wallet/withdraw",
     *     summary="درخواست برداشت از کیف پول",
     *     description="کاربر می‌تواند درخواست برداشت مبلغی از کیف پول خود را ثبت کند. نیاز به توکن Sanctum دارد.",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="integer", example=10000, description="مبلغ درخواستی برای برداشت (باید مثبت و کمتر از موجودی باشد)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="درخواست برداشت با موفقیت ثبت شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="درخواست برداشت ثبت شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="موجودی ناکافی یا درخواست نامعتبر",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="موجودی کافی نیست")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function requestWithdraw(requestWithdrawRequest $request): JsonResponse
    {
        $user = auth()->user();

        if ($user->balance < $request->amount) {
            throw new \Exception('موجودی کافی نیست', 400);
        }

        DB::transaction(function () use ($user, $request) {
            WalletWithdrawRequest::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'status' => 'pending',
            ]);
        });

        return response()->json(['message' => 'درخواست برداشت ثبت شد']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/wallet/withdraw-review/{withdraw}",
     *     summary="بررسی درخواست برداشت توسط ادمین",
     *     description="فقط کاربران با نقش ادمین می‌توانند درخواست‌های برداشت را بررسی کنند. نیاز به توکن Sanctum و دسترسی ادمین دارد.",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="withdraw",
     *         in="path",
     *         required=true,
     *         description="شناسه درخواست برداشت",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"approved", "rejected"}, example="approved", description="وضعیت نهایی درخواست"),
     *             @OA\Property(property="admin_note", type="string", example="واریز انجام شد", description="یادداشت اختیاری ادمین", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="درخواست با موفقیت بررسی شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="درخواست با موفقیت بررسی شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="درخواست قبلاً بررسی شده یا نامعتبر است",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="این درخواست قبلا بررسی شده")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="عدم دسترسی",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Forbidden")
     *         )
     *     )
     * )
     * @throws \Exception
     */
    public function reviewWithdrawRequest(requestWithdrawRequest $request, WalletWithdrawRequest $withdraw): JsonResponse
    {
        $this->authorize('review', $withdraw);

        if ($withdraw->status !== 'pending') {
            throw new \Exception('این درخواست قبلا بررسی شده', 400);
        }

        DB::transaction(function () use ($request, $withdraw) {
            if ($request->status === 'approved') {
                $withdraw->user->walletTransactions()->create([
                    'amount' => -1 * $withdraw->amount,
                    'type' => 'withdraw',
                    'description' => 'برداشت تایید شده توسط ادمین',
                ]);
                $withdraw->user->updateBalance(-1 * $withdraw->amount);
            }

            $withdraw->update([
                'status' => $request->status,
                'admin_note' => $request->admin_note,
            ]);
        });

        return response()->json(['message' => 'درخواست با موفقیت بررسی شد']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/wallet/transfer",
     *     summary="انتقال وجه به کیف پول کاربر دیگر",
     *     description="کاربر می‌تواند مبلغی را به کیف پول کاربر دیگری انتقال دهد. نیاز به توکن Sanctum دارد.",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"to_user_id", "amount"},
     *             @OA\Property(property="to_user_id*", type="integer", example=5, description="شناسه کاربر مقصد"),
     *             @OA\Property(property="amount", type="integer", example=15000, description="مبلغ انتقالی (باید مثبت و کمتر از موجودی باشد)"),
     *             @OA\Property(property="description", type="string", example="پرداخت به دوست", description="توضیحات اختیاری برای تراکنش", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="انتقال با موفقیت انجام شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="انتقال با موفقیت انجام شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="موجودی ناکافی یا درخواست نامعتبر",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="موجودی کافی نیست")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="کاربر مقصد یافت نشد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="کاربر یافت نشد")
     *         )
     *     )
     * )
     * @throws \Exception
     */
    public function transfer(TransferRequest $request): JsonResponse
    {
        $fromUser = auth()->user();
        $toUser = User::findOrFail($request->to_user_id);

        if ($fromUser->id === $toUser->id) {
            throw new Exception('نمی‌توانید به خودتان انتقال دهید', 400);
        }

        if ($fromUser->balance < $request->amount) {
            throw new \Exception('موجودی کافی نیست', 400);
        }

        DB::transaction(function () use ($fromUser, $toUser, $request) {
            $fromUser->walletTransactions()->create([
                'amount' => -$request->amount,
                'type' => 'transfer_out',
                'description' => $request->description ?? 'انتقال به ' . $toUser->name,
                'related_user_id' => $toUser->id,
            ]);
            $fromUser->updateBalance(-$request->amount);

            $toUser->walletTransactions()->create([
                'amount' => $request->amount,
                'type' => 'transfer_in',
                'description' => $request->description ?? 'انتقال از ' . $fromUser->name,
                'related_user_id' => $fromUser->id,
            ]);
            $toUser->updateBalance($request->amount);
        });

        return response()->json(['message' => 'انتقال با موفقیت انجام شد']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallet/balance",
     *     summary="دریافت موجودی کیف پول",
     *     description="دریافت موجودی فعلی کیف پول کاربر. نیاز به توکن Sanctum دارد.",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="موجودی فعلی کیف پول",
     *         @OA\JsonContent(
     *             @OA\Property(property="balance", type="integer", example=15000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="عدم احراز هویت",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function balance(): JsonResponse
    {
        $user = auth()->user();
        return response()->json([
            'balance' => $user->wallet_balance,
        ]);
    }
}
