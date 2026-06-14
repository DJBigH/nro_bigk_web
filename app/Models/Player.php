<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $table = 'player';

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'name',
        'head',
        'gender',
        'data_point',
        'items_bag',
        'capTT',
        'pointPvp',
        'NguHanhSonPoint',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    // Helper to get power (suc_manh) from data_point
    public function getPowerAttribute()
    {
        $data = json_decode($this->data_point, true);
        if (is_array($data)) {
            return isset($data[1]) ? (int) $data[1] : 0;
        }

        // Fallback for bracketed string format
        $data = $this->parseBracketsToArray($this->data_point);
        return isset($data[1]) ? (int) $data[1] : 0;
    }

    // Helper to parse string representation of array like "[100,200,300]"
    private function parseBracketsToArray($str)
    {
        if (empty($str)) {
            return [];
        }
        $cleaned = trim($str, '[]');
        if (empty($cleaned)) {
            return [];
        }
        return explode(',', $cleaned);
    }

    /**
     * Map capTT to detailed Vietnamese game task description.
     */
    public static function getTaskName($capTT)
    {
        $tasks = [
            0 => 'Tìm đùi gà (Nhiệm vụ Tân thủ)',
            1 => 'Thu hoạch Đậu thần',
            2 => 'Giải quyết Mộc nhân',
            3 => 'Kết bạn & Tìm sao băng',
            4 => 'Tiêu diệt Quái bay',
            5 => 'Thu thập Đùi gà / Rơi lệ',
            6 => 'Thách đấu Đệ tử Quy Lão',
            7 => 'Hạ quái map cao',
            8 => 'Diệt quái 3 hành tinh',
            9 => 'Đạt sức mạnh 1.5 triệu',
            10 => 'Bái sư học đạo',
            11 => 'Hạ Trung Úy Trắng',
            12 => 'Hạ Trung Úy Xanh Lơ / Độc Nhãn',
            13 => 'Tham gia Đại hội Võ thuật',
            14 => 'Hạ Độc Nhãn Thiền Sư',
            15 => 'Tìm Ngọc Rồng 6 Sao',
            16 => 'Tiêu diệt Ác Nhân',
            17 => 'Đạt sức mạnh 15 triệu',
            18 => 'Hạ Tiểu Đội Sát Thủ',
            19 => 'Đánh bại Fide Cấp 1',
            20 => 'Đánh bại Fide Cấp 2',
            21 => 'Đánh bại Fide Cấp 3',
            22 => 'Đánh bại Robot Sát Thủ',
            23 => 'Tiêu diệt Android 19, 20',
            24 => 'Tiêu diệt Android 17, 18',
            25 => 'Tiêu diệt Android 16',
            26 => 'Đánh bại Cell Cấp 1',
            27 => 'Đánh bại Cell Cấp 2',
            28 => 'Đánh bại Cell Hoàn Thiện',
            29 => 'Vượt ĐHVT lần thứ 23',
            30 => 'Giải cứu Quy Lão Kame',
        ];

        return $tasks[$capTT] ?? "Nhiệm vụ mốc {$capTT}";
    }
}
