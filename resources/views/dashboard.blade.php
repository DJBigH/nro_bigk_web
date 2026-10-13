@extends('layouts.app')

@section('title', 'Tài Khoản - NRO BigK')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px;">
    
    <!-- Left Column: User Info & Change Password -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- User Info Card -->
        <div class="card">
            <h2 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif;">
                TÀI KHOẢN
            </h2>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Tên đăng nhập:</span>
                    <strong style="color: #fff; font-family: 'Orbitron', sans-serif;">{{ $account->username }}</strong>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted);">Số dư VND:</span>
                    <strong style="color: var(--secondary); font-family: 'Orbitron', sans-serif; font-size: 1.3rem;">
                        {{ number_format($account->vnd ?? 0) }} đ
                    </strong>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-muted);">Xu game:</span>
                    <strong style="color: #00bcd4; font-family: 'Orbitron', sans-serif; font-size: 1.2rem;">
                        {{ number_format($account->coin ?? 0) }} Xu
                    </strong>
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Quyền hạn:</span>
                    @if($account->is_admin == 1)
                        <span class="badge badge-primary">ADMINISTRATOR</span>
                    @else
                        <span class="badge badge-secondary">PLAYER</span>
                    @endif
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">IP của bạn:</span>
                    <span style="color: #fff; font-size: 0.95rem;">{{ Request::ip() }}</span>
                </div>
            </div>

            <div style="margin-top: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <a href="{{ route('shop') }}" class="btn" style="text-align: center;">CỬA HÀNG</a>
                <a href="{{ route('acc_sales.history') }}" class="btn btn-secondary" style="text-align: center;">LỊCH SỬ MUA ACC</a>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card">
            <h2 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif;">
                ĐỔI MẬT KHẨU
            </h2>
            
            <form id="changepassword-form">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Mật khẩu cũ</label>
                    <input type="password" name="old_password" class="form-input" placeholder="Nhập mật khẩu hiện tại..." required>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-input" placeholder="Nhập mật khẩu mới..." required>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Nhập lại mật khẩu mới..." required>
                </div>
                
                <button type="submit" id="changepassword-btn" class="btn" style="width: 100%;">
                    ĐỔI MẬT KHẨU
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Game Characters & Gift Codes -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Character List Card -->
        <div class="card" style="height: fit-content;">
            <h2 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; font-family: 'Orbitron', sans-serif;">
                NHÂN VẬT TRONG GAME
            </h2>

            @if($players->isEmpty())
                <div style="text-align: center; padding: 40px 0;">
                    <div style="font-size: 3rem; margin-bottom: 15px;">🎮</div>
                    <h4 style="color: #fff; margin-bottom: 10px;">CHƯA CÓ NHÂN VẬT</h4>
                    <p style="color: var(--text-muted); font-size: 0.95rem;">
                        Bạn chưa tạo nhân vật game nào cho tài khoản này.<br>
                        Hãy đăng nhập vào game bằng client, chọn máy chủ và tạo nhân vật của bạn.
                    </p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    @foreach($players as $player)
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255, 107, 0, 0.1); border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 15px;">
                            
                            <!-- Avatar based on gender -->
                            <div style="width: 60px; height: 60px; border-radius: 8px; background: rgba(255,107,0,0.1); border: 2px solid var(--primary); display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                @if($player->gender == 0)
                                    🧑‍🦰 <!-- Trái Đất -->
                                @elseif($player->gender == 1)
                                    👽 <!-- Namếc -->
                                @else
                                    🦹 <!-- Xayda -->
                                @endif
                            </div>

                            <div style="flex: 1;">
                                <h3 style="color: #fff; font-family: 'Orbitron', sans-serif; margin-bottom: 3px; font-size: 1.1rem;">
                                    {{ $player->name }}
                                </h3>
                                <div style="font-size: 0.85rem; color: var(--text-muted); display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">
                                    <div>Hành tinh: 
                                        <span style="color: var(--primary); font-weight: 700;">
                                            @if($player->gender == 0) Trái Đất @elseif($player->gender == 1) Namếc @else Xayda @endif
                                        </span>
                                    </div>
                                    <div>Nhiệm vụ: <span style="color: #fff;">{{ App\Models\Player::getTaskName($player->capTT) }}</span></div>
                                    <div style="grid-column: span 2;">Sức mạnh: 
                                        <span style="color: var(--secondary); font-family: 'Orbitron', sans-serif; font-weight: 700;">
                                            {{ number_format($player->power) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Web Chest Card -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <h2 style="color: var(--primary); font-family: 'Orbitron', sans-serif; margin: 0; display: flex; align-items: center; gap: 8px;">
                    📦 RƯƠNG ĐỒ WEB
                </h2>
                @if(!$chestItems->isEmpty())
                    <button type="button" id="withdraw-all-btn" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                        RÚT TẤT CẢ
                    </button>
                @endif
            </div>

            <div style="background: rgba(255, 107, 0, 0.05); border: 1px dashed var(--primary); border-radius: 6px; padding: 12px; margin-bottom: 20px; font-size: 0.85rem; color: #ffb74d;">
                ⚠️ <strong>Lưu ý:</strong> Cần <strong>THOÁT GAME hoàn toàn</strong> trước khi rút vật phẩm. Hành trang cần có đủ ô trống.
            </div>

            @if($chestItems->isEmpty())
                <div style="text-align: center; padding: 30px 0;">
                    <p style="color: var(--text-muted); font-size: 0.95rem;">Rương đồ web của bạn đang trống.</p>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">Mua sắm tại <a href="{{ route('shop') }}" style="color: var(--secondary); text-decoration: underline;">Web Shop</a> hoặc quay <a href="{{ route('spin') }}" style="color: var(--secondary); text-decoration: underline;">Vòng quay</a> để nhận vật phẩm.</p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 400px; overflow-y: auto; padding-right: 5px;">
                    @foreach($chestItems as $item)
                        @php
                            $tpl = $item->template;
                        @endphp
                        @if($tpl)
                            <div style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
                                <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                                    <div style="width: 45px; height: 45px; border-radius: 6px; background: rgba(255,107,0,0.08); border: 1px solid rgba(255,107,0,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; position: relative;">
                                        📦
                                        <span style="position: absolute; bottom: -2px; right: -2px; background: var(--primary); color: #fff; font-size: 0.7rem; font-weight: bold; padding: 1px 4px; border-radius: 4px; font-family: 'Orbitron', sans-serif;">
                                            x{{ $item->quantity }}
                                        </span>
                                    </div>
                                    <div style="flex: 1;">
                                        <h4 style="color: #fff; margin: 0 0 3px 0; font-size: 0.95rem;">{{ $tpl->NAME }}</h4>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            ID Template: <span style="color: var(--secondary);">{{ $tpl->id }}</span>
                                        </div>
                                        @if(!empty($item->options))
                                            <div style="margin-top: 5px; font-size: 0.75rem; color: #ffb74d; display: flex; flex-wrap: wrap; gap: 5px;">
                                                @foreach($item->options as $opt)
                                                    @php
                                                        $optTpl = DB::table('item_option_template')->where('id', $opt[0] ?? null)->first();
                                                        $optName = $optTpl ? str_replace('#', $opt[1] ?? '', $optTpl->NAME) : 'Chỉ số ' . ($opt[0] ?? '');
                                                    @endphp
                                                    <span style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255, 183, 77, 0.2); border-radius: 4px; padding: 2px 6px;">
                                                        {{ $optName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div>
                                    <button type="button" class="btn btn-secondary withdraw-single-btn" data-id="{{ $item->id }}" style="font-size: 0.8rem; padding: 6px 12px;">
                                        Rút game
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Gift Code List Card -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <h2 style="color: var(--secondary); font-family: 'Orbitron', sans-serif; margin: 0;">
                    DANH SÁCH GIFT CODE
                </h2>
                @if(!$players->isEmpty() && $gifts->isNotEmpty())
                    <button type="button" id="claim-all-btn" class="btn btn-secondary" style="font-size: 0.8rem; padding: 6px 12px;">
                        NHẬN TẤT CẢ CODES
                    </button>
                @endif
            </div>
            
            <div style="background: rgba(255, 107, 0, 0.05); border: 1px dashed var(--primary); border-radius: 6px; padding: 12px; margin-bottom: 20px; font-size: 0.85rem; color: #ffb74d;">
                ⚠️ <strong>Cảnh Báo:</strong> Yêu cầu <strong>THOÁT GAME hoàn toàn</strong> trước khi nhận quà để hệ thống cập nhật hành trang an toàn. Hành trang cần có đủ ô trống.
            </div>

            @if($gifts->isEmpty())
                <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">Không có mã quà tặng nào khả dụng.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px; max-height: 350px; overflow-y: auto; padding-right: 5px;">
                    @foreach($gifts as $gift)
                        @php
                            $listUser = json_decode($gift->list_user, true) ?? [];
                            $isUsed = in_array($account->username, $listUser);
                            
                            $itemIds = json_decode($gift->item_id, true) ?? [];
                            $quantities = json_decode($gift->item_quantity, true) ?? [];
                        @endphp
                        
                        <div style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.05); border-radius: 6px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
                            <div>
                                <strong style="color: var(--primary); font-family: 'Orbitron', sans-serif; font-size: 1rem; text-transform: uppercase;">
                                    {{ $gift->name }}
                                </strong>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">
                                    Yêu cầu sức mạnh: <span style="color: #fff;">{{ number_format($gift->power) }}</span>
                                </div>
                                <div style="font-size: 0.8rem; color: #aaa; margin-top: 3px;">
                                    Quà: 
                                    @foreach($itemIds as $idx => $itemId)
                                        @php
                                            $itemName = DB::table('item_template')->where('id', $itemId)->value('NAME') ?? "Vật phẩm $itemId";
                                            $qty = $quantities[$idx] ?? 1;
                                        @endphp
                                        <span class="badge badge-secondary" style="font-size: 0.75rem; padding: 2px 5px;">{{ $itemName }} x{{ $qty }}</span>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div>
                                @if($isUsed)
                                    <span class="badge badge-secondary" style="font-size: 0.85rem; padding: 6px 12px; border: 1px solid rgba(255,255,255,0.1);">ĐÃ SỬ DỤNG</span>
                                @else
                                    <button type="button" class="btn btn-secondary claim-single-btn" data-code="{{ $gift->name }}" style="font-size: 0.8rem; padding: 6px 12px;">
                                        NHẬN QUÀ
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Direct Input Form for Custom Giftcode -->
            <div style="border-top: 1px solid var(--border-color); margin-top: 20px; padding-top: 15px;">
                <h4 style="color: #fff; margin-bottom: 10px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem;">NHẬP MÃ TAY</h4>
                <form id="giftcode-form" style="display: flex; gap: 10px;">
                    <input type="text" name="code" class="form-input" placeholder="Nhập mã code khác..." required style="text-transform: uppercase; flex: 1;">
                    <button type="submit" id="giftcode-btn" class="btn btn-secondary" style="padding: 0 20px;">
                        NHẬN
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Change Password Handler
        const cpForm = document.getElementById('changepassword-form');
        const cpBtn = document.getElementById('changepassword-btn');
        
        cpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            cpBtn.disabled = true;
            cpBtn.innerText = 'ĐANG XỬ LÝ...';

            const formData = new FormData(cpForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await window.csrfFetch('{{ route("profile.change-password") }}', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();

                if (result.success) {
                    window.showAlert(result.message, 'success');
                    cpForm.reset();
                } else {
                    window.showAlert(result.message, 'error');
                }
            } catch (error) {
                window.showAlert('Đã xảy ra lỗi kết nối. Vui lòng thử lại.', 'error');
            } finally {
                cpBtn.disabled = false;
                cpBtn.innerText = 'ĐỔI MẬT KHẨU';
            }
        });

        // Single Giftcode Claim Handler
        const claimSingleBtns = document.querySelectorAll('.claim-single-btn');
        claimSingleBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const code = btn.getAttribute('data-code');
                btn.disabled = true;
                btn.innerText = 'ĐANG NHẬN...';

                try {
                    const response = await window.csrfFetch('{{ route("giftcode.use") }}', {
                        method: 'POST',
                        body: JSON.stringify({ code })
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        window.showAlert(result.message, 'error');
                        btn.disabled = false;
                        btn.innerText = 'NHẬN QUÀ';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
                    btn.disabled = false;
                    btn.innerText = 'NHẬN QUÀ';
                }
            });
        });

        // Direct Input Form Handler
        const customForm = document.getElementById('giftcode-form');
        const customBtn = document.getElementById('giftcode-btn');
        customForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            customBtn.disabled = true;
            customBtn.innerText = 'CHỜ...';

            const formData = new FormData(customForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await window.csrfFetch('{{ route("giftcode.use") }}', {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    window.showAlert(result.message, 'success');
                    customForm.reset();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    window.showAlert(result.message, 'error');
                }
            } catch (error) {
                window.showAlert('Đã xảy ra lỗi kết nối.', 'error');
            } finally {
                customBtn.disabled = false;
                customBtn.innerText = 'NHẬN';
            }
        });

        // Claim All Handler
        const claimAllBtn = document.getElementById('claim-all-btn');
        if (claimAllBtn) {
            claimAllBtn.addEventListener('click', async () => {
                claimAllBtn.disabled = true;
                claimAllBtn.innerText = 'ĐANG NHẬN...';

                try {
                    const response = await window.csrfFetch('{{ route("giftcode.claim-all") }}', {
                        method: 'POST'
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        window.showAlert(result.message, 'error');
                        claimAllBtn.disabled = false;
                        claimAllBtn.innerText = 'NHẬN TẤT CẢ CODES';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
                    claimAllBtn.disabled = false;
                    claimAllBtn.innerText = 'NHẬN TẤT CẢ CODES';
                }
            });
        }

        // Single Withdraw Handler
        const withdrawSingleBtns = document.querySelectorAll('.withdraw-single-btn');
        withdrawSingleBtns.forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                btn.disabled = true;
                btn.innerText = 'Đang rút...';

                try {
                    const response = await window.csrfFetch(`/web-chest/withdraw/${id}`, {
                        method: 'POST'
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        window.showAlert(result.message, 'error');
                        btn.disabled = false;
                        btn.innerText = 'Rút game';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
                    btn.disabled = false;
                    btn.innerText = 'Rút game';
                }
            });
        });

        // Withdraw All Handler
        const withdrawAllBtn = document.getElementById('withdraw-all-btn');
        if (withdrawAllBtn) {
            withdrawAllBtn.addEventListener('click', async () => {
                withdrawAllBtn.disabled = true;
                withdrawAllBtn.innerText = 'Đang rút...';

                try {
                    const response = await window.csrfFetch('{{ route("web_chest.withdraw_all") }}', {
                        method: 'POST'
                    });
                    const result = await response.json();

                    if (result.success) {
                        window.showAlert(result.message, 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        window.showAlert(result.message, 'error');
                        withdrawAllBtn.disabled = false;
                        withdrawAllBtn.innerText = 'RÚT TẤT CẢ';
                    }
                } catch (error) {
                    window.showAlert('Lỗi kết nối. Vui lòng thử lại.', 'error');
                    withdrawAllBtn.disabled = false;
                    withdrawAllBtn.innerText = 'RÚT TẤT CẢ';
                }
            });
        }
    });
</script>
@endsection
