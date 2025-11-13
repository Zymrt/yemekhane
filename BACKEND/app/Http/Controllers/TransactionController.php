<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * GET /api/transactions
     * Kullanıcının bakiye hareketlerini sayfalı olarak listeler.
     */
    public function index(Request $req)
    {
        $user = $req->user();

        // Sayfalama parametreleri (yorumlar.vue ile aynı mantık)
        $perPage = (int) $req->query('per_page', 10);
        $page = (int) $req->query('page', 1);

        // Sorgu
        $query = Transaction::where('user_id', (string)($user->_id ?? $user->id))
                           ->orderBy('created_at', 'desc'); // En yeniden eskiye

        // Paginate et
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Frontend'in beklediği 'meta' ve 'data' formatında dön
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }
}