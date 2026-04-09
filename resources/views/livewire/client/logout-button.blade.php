<div>
    <style>
        .client-logout-btn{
            appearance: none;
            width: 100%;
            border: 1px solid rgba(14, 116, 144, .16);
            background: linear-gradient(135deg, #ffffff 0%, #ecfeff 100%);
            color: #0f172a;
            padding: .9rem 1rem;
            border-radius: 16px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .65rem;
            line-height: 1.2;
            letter-spacing: .01em;
            box-shadow: 0 12px 28px rgba(14, 116, 144, .10);
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease, color .18s ease;
        }

        .client-logout-btn:hover{
            transform: translateY(-1px);
            background: linear-gradient(135deg, #0891b2 0%, #0ea5e9 100%);
            border-color: rgba(8, 145, 178, .35);
            color: #ffffff;
            box-shadow: 0 16px 34px rgba(14, 116, 144, .20);
        }

        .client-logout-btn:active{
            transform: translateY(0);
            box-shadow: 0 10px 20px rgba(14, 116, 144, .18);
        }

        .client-logout-btn:focus-visible{
            outline: none;
            box-shadow: 0 0 0 4px rgba(34, 211, 238, .22), 0 12px 28px rgba(14, 116, 144, .12);
        }

        .client-logout-btn:disabled{
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        .client-logout-btn i{
            font-size: 16px;
            transition: transform .18s ease;
        }

        .client-logout-btn:hover i{
            transform: translateX(2px);
        }

        .client-logout-btn span{
            white-space: nowrap;
        }

        .client-logout-btn__avatar{
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, .85);
            box-shadow: 0 4px 10px rgba(15, 23, 42, .12);
            flex: 0 0 28px;
        }
    </style>

    @php
        $avatar = auth()->user()?->avatar;
        $avatarUrl = $avatar ? asset('storage/' . ltrim($avatar, '/')) : asset('assets/img/avatar_detail.jpg');
    @endphp

    <button
        type="button"
        class="client-logout-btn"
        wire:click="logout"
        wire:loading.attr="disabled"
        wire:target="logout"
        aria-label="Đăng xuất"
        title="Đăng xuất"
    >
        <img class="client-logout-btn__avatar" src="{{ $avatarUrl }}" alt="Avatar">
        <i class="fa fa-sign-out" aria-hidden="true"></i>
        <span>Đăng xuất</span>
    </button>
</div>
