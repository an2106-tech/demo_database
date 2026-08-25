<div>
    <style>
        .user-dropdown-logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #ef4444 !important;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: left;
            outline: none;
        }

        .user-dropdown-logout-btn:hover {
            background: #fef2f2;
            border-color: #fee2e2;
            color: #dc2626 !important;
            transform: translateX(2px);
        }

        .user-dropdown-logout-btn .logout-icon-box {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #fee2e2;
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .user-dropdown-logout-btn:hover .logout-icon-box {
            background: #ef4444;
            color: #ffffff;
        }

        .user-dropdown-logout-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    <button
        type="button"
        class="user-dropdown-logout-btn"
        wire:click="logout"
        wire:loading.attr="disabled"
        wire:target="logout"
        aria-label="Đăng xuất"
        title="Đăng xuất"
    >
        <div class="d-flex align-items-center gap-2">
            <div class="logout-icon-box">
                <i class="fa fa-sign-out" aria-hidden="true"></i>
            </div>
            <span>Đăng xuất</span>
        </div>
        <span wire:loading wire:target="logout">
            <i class="fa fa-circle-o-notch fa-spin text-danger"></i>
        </span>
    </button>
</div>
