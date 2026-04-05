<div>
    <style>
        .client-logout-btn{
            appearance: none;
            border: 1px solid rgba(15, 23, 42, .18);
            background: rgba(255, 255, 255, .92);
            color: #0f172a;
            padding: .7rem 1.05rem;
            border-radius: 999px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            line-height: 1;
            box-shadow: 0 14px 35px rgba(15, 23, 42, .10);
            transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease, border-color .15s ease;
            backdrop-filter: blur(10px);
        }
        .client-logout-btn:hover{
            transform: translateY(-1px);
            background: #ffffff;
            border-color: rgba(15, 23, 42, .26);
            box-shadow: 0 18px 45px rgba(15, 23, 42, .14);
        }
        .client-logout-btn:active{
            transform: translateY(0);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .10);
        }
        .client-logout-btn:disabled{
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }
        .client-logout-btn i{ font-size: 16px; }
    </style>

    <button
        type="button"
        class="client-logout-btn"
        wire:click="logout"
        wire:loading.attr="disabled"
        wire:target="logout"
        aria-label="Đăng xuất"
        title="Đăng xuất"
    >
        <i class="fa fa-sign-out" aria-hidden="true"></i>
        <span>Đăng xuất</span>
    </button>
</div>

