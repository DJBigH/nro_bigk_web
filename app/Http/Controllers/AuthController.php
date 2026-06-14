<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle login AJAX request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Tên tài khoản không được để trống.',
            'password.required' => 'Mật khẩu không được để trống.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $credentials = $request->only('username', 'password');

        // Check if account is banned first
        $account = Account::where('username', $credentials['username'])->first();
        if ($account && $account->ban > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.'
            ]);
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'redirect' => route('dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tài khoản hoặc mật khẩu không chính xác.'
        ]);
    }

    /**
     * Show register form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Handle register AJAX request.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:4',
                'max:20',
                'regex:/^[a-zA-Z0-9_]+$/',
                'unique:account,username'
            ],
            'password' => 'required|string|min:4|max:50',
            'password_confirmation' => 'required|same:password',
        ], [
            'username.required' => 'Tên tài khoản không được để trống.',
            'username.min' => 'Tên tài khoản phải chứa ít nhất 4 ký tự.',
            'username.max' => 'Tên tài khoản không được vượt quá 20 ký tự.',
            'username.regex' => 'Tên tài khoản chỉ được chứa chữ cái, số và dấu gạch dưới.',
            'username.unique' => 'Tên tài khoản đã tồn tại trên hệ thống.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 4 ký tự.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu.',
            'password_confirmation.same' => 'Xác nhận mật khẩu không trùng khớp.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // Create the account with all columns populated to bypass strict SQL issues
        $account = new Account();
        $account->username = $request->username;
        $account->password = $request->password;
        $account->ban = 0;
        $account->role = -1;
        $account->is_admin = 0;
        $account->active = 1;
        $account->vnd = 0;
        $account->coin = 0;
        $account->tongnap = 0;
        $account->thoi_vang = 0;
        $account->server_login = -1;
        $account->bd_player = 1;
        $account->is_gift_box = 0;
        $account->gift_time = '0';
        $account->reward = null;
        $account->gioithieu = 0;
        $account->mtvgt = 0;
        $account->vip1 = 0;
        $account->vip2 = 0;
        $account->vip3 = 0;
        $account->vip4 = 0;
        $account->vip5 = 0;
        $account->vip6 = 0;
        $account->admin = 0;
        $account->tichdiem = 0;
        $account->xacminh = 0;
        $account->thoigian_xacminh = 0;
        $account->xacnhan_gioitheu = 0;
        $account->matkhau_xacminh = 0;
        $account->tichdiem_moc1 = 0;
        $account->tichdiem_moc2 = 0;
        $account->tichdiem_moc3 = 0;
        $account->tichdiem_moc4 = 0;
        $account->tichdiem_moc5 = 0;
        $account->ip_address = $request->ip();
        $account->save();

        // Automatically log the user in after registration
        Auth::login($account);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công!',
            'redirect' => route('dashboard')
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
