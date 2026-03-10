<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * POST /api/payment/start
     * Yeni bir ödeme işlemi başlatır (SİMÜLASYON)
     * Sahte kart formu bu fonksiyonu tetikler.
     */
    public function startPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = (float) $request->amount;
        $user = $request->user();

        // -----------------------------------------------------------------
        // 🔥 SİMÜLASYON MODU (Geçici) 🔥
        // Bankaya gitmiş ve ödeme başarılı olmuş gibi davranıyoruz.
        // -----------------------------------------------------------------
        try {
            // Gerçekte bu kod bankadan gelen 'callback' (geri bildirim)
            // isteğiyle tetiklenmeli, 'startPayment' ile değil.
            
            // 1. Kullanıcıyı bul ve bakiyeyi ekle
            $freshUser = User::find($user->_id ?? $user->id);
            if (!$freshUser) {
                return response()->json(['message' => 'Kullanıcı bulunamadı.'], Response::HTTP_NOT_FOUND);
            }

            $freshUser->balance = (float) ($freshUser->balance ?? 0) + $amount;
            $freshUser->save();

            // 2. 'transactions' tablosuna log at (Hesap Hareketleri için)
            if (class_exists(Transaction::class)) {
                Transaction::create([
                    'user_id' => (string)($freshUser->_id ?? $freshUser->id),
                    'type'    => 'credit',
                    'amount'  => $amount,
                    'meta'    => [
                        'payment_id' => 'sim_' . uniqid(), // Simülasyon ID
                        'gateway'    => 'IsBank (Simulated)',
                    ]
                ]);
            }

            // Simülasyon başarılı, socket'e bakiye güncelleme bildirimi gönder
            try {
                $socketUrl = env('SOCKET_SERVER_URL', 'http://localhost:3001');
                Http::post("{$socketUrl}/api/balance-updated", [
                    'user_id'     => (string)($freshUser->_id ?? $freshUser->id),
                    'new_balance' => $freshUser->balance,
                ]);
            } catch (\Exception $socketEx) {
                Log::warning('Socket bakiye bildirimi hatası: ' . $socketEx->getMessage());
            }

            return response()->json([
                'message'     => 'Ödeme simülasyonu başarılı.',
                'new_balance' => $freshUser->balance,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Bakiye güncellenirken hata oluştu: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * POST /api/payment/isbank-callback
     * (GERÇEK ENTEGRASYON İÇİN)
     * İş Bankası'nın 3D Secure sonrası ödeme sonucunu bildireceği adres.
     */
    public function handleIsbankCallback(Request $request)
    {
        // Gerçek entegrasyon buraya...
        // ...
        // return response('OK', 200);
    }
}