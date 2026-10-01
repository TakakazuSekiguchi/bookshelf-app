<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

// Fortifyが定義しているLogoutResponse（interface）を実装し、
// ログアウト後の遷移先をアプリ側でカスタマイズするためのクラス
class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        return redirect('/books');
    }
}