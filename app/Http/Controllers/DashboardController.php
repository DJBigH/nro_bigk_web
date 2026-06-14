<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Player;
use App\Models\Post;
use App\Models\DownloadFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Show home page.
     */
    public function home()
    {
        // Get top 5 guide posts to show on home page
        $posts = Post::where('category', 'guide')
            ->orderBy('up_top', 'desc')
            ->orderBy('time_post', 'desc')
            ->limit(5)
            ->get();

        return view('home', compact('posts'));
    }

    /**
     * Show player dashboard.
     */
    public function dashboard()
    {
        $account = Auth::user();
        // Load the account's players
        $players = Player::where('account_id', $account->id)->get();
        
        // Load all available gift codes from the game 'gift' table
        $gifts = DB::table('gift')->get();

        return view('dashboard', compact('account', 'players', 'gifts'));
    }

    /**
     * Change password of current logged in user.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'password' => 'required|string|min:4|confirmed',
        ], [
            'old_password.required' => 'Vui lòng nhập mật khẩu cũ.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải chứa ít nhất 4 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.'
        ]);

        $account = Auth::user();

        // Verify old password
        $pwMatch = false;
        if (Hash::check($request->old_password, $account->password)) {
            $pwMatch = true;
        } elseif (md5($request->old_password) === $account->password) {
            $pwMatch = true;
        } elseif ($request->old_password === $account->password) {
            $pwMatch = true;
        }

        if (!$pwMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu cũ không chính xác.'
            ]);
        }

        // Save new plain text password
        DB::table('account')
            ->where('id', $account->id)
            ->update([
                'password' => $request->password
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }

    /**
     * Show download client page.
     */
    public function download()
    {
        $downloads = DownloadFile::where('is_active', 1)->get();
        return view('download', compact('downloads'));
    }

    /**
     * Show guides list page.
     */
    public function guide()
    {
        $posts = Post::where('category', 'guide')
            ->orderBy('up_top', 'desc')
            ->orderBy('time_post', 'desc')
            ->paginate(10);

        return view('guide.index', compact('posts'));
    }

    /**
     * Show specific guide details.
     */
    public function guideShow($id)
    {
        $post = Post::findOrFail($id);
        return view('guide.show', compact('post'));
    }

    /**
     * Show rankings page.
     */
    public function ranking(Request $request)
    {
        $type = request('type', 'power'); // 'power', 'task', 'recharge'

        $rankings = [];

        if ($type === 'power') {
            // TOP Power
            $rankings = DB::select("
                SELECT p.name, p.head, p.gender, p.capTT,
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(TRIM(BOTH '[]' FROM p.data_point), ',', 2), ',', -1) AS UNSIGNED) AS value
                FROM player p
                ORDER BY value DESC
                LIMIT 20
            ");
        } elseif ($type === 'task') {
            // TOP Tasks
            $rankings = DB::select("
                SELECT p.name, p.head, p.gender, p.capTT as value
                FROM player p
                ORDER BY value DESC
                LIMIT 20
            ");
        } elseif ($type === 'recharge') {
            // TOP Recharge (tongnap) from account
            // Map account username to player name if exists, else show username
            $rankings = DB::select("
                SELECT a.username as name, a.tongnap as value, p.head, p.gender
                FROM account a
                LEFT JOIN player p ON p.account_id = a.id
                WHERE a.active = 1
                ORDER BY value DESC
                LIMIT 20
            ");

            // Filter duplicates if account has multiple characters, select the first character
            $filtered = [];
            $seen = [];
            foreach ($rankings as $row) {
                if (!in_array($row->name, $seen)) {
                    $seen[] = $row->name;
                    $filtered[] = $row;
                }
            }
            $rankings = $filtered;
        }

        return view('ranking', compact('rankings', 'type'));
    }
}
