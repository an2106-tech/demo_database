<x-filament-panels::page>
    @php
        $toneClasses = [
            'gray' => 'is-gray',
            'info' => 'is-info',
            'warning' => 'is-warning',
            'primary' => 'is-primary',
            'success' => 'is-success',
            'danger' => 'is-danger',
        ];
    @endphp

    <style>
        .recruitment-kanban {
            --kanban-border: rgb(229 231 235);
            --kanban-card: #ffffff;
            --kanban-muted: rgb(100 116 139);
            --kanban-soft: rgb(248 250 252);
            --kanban-text: rgb(15 23 42);
            --kanban-orange: rgb(234 88 12);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .recruitment-kanban [x-cloak] {
            display: none !important;
        }

        .dark .recruitment-kanban {
            --kanban-border: rgb(39 39 42);
            --kanban-card: rgb(24 24 27);
            --kanban-muted: rgb(161 161 170);
            --kanban-soft: rgb(18 18 22);
            --kanban-text: rgb(250 250 250);
        }

        .kanban-panel {
            border: 1px solid var(--kanban-border);
            border-radius: 16px;
            background: var(--kanban-card);
            box-shadow: 0 10px 28px rgb(15 23 42 / 0.05);
        }

        .kanban-controls {
            display: grid;
            gap: 12px;
            padding: 14px;
        }

        .kanban-control-top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
        }

        .kanban-summary {
            min-width: 0;
        }

        .kanban-summary__title {
            margin: 0;
            color: var(--kanban-text);
            font-size: 18px;
            font-weight: 800;
            line-height: 1.25;
        }

        .kanban-summary__meta {
            margin: 4px 0 0;
            color: var(--kanban-muted);
            font-size: 13px;
            line-height: 1.35;
        }

        .kanban-search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: min(420px, 42vw);
        }

        .kanban-search {
            position: relative;
            min-width: 0;
            flex: 1;
        }

        .kanban-search__icon {
            position: absolute;
            left: 12px;
            top: 50%;
            width: 18px;
            height: 18px;
            color: var(--kanban-muted);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .kanban-search__input {
            width: 100%;
            height: 42px;
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-soft);
            color: var(--kanban-text);
            font-size: 13.5px;
            outline: none;
            padding: 0 38px 0 40px;
            transition: border-color 140ms ease, box-shadow 140ms ease, background 140ms ease;
        }

        .kanban-search__input:focus {
            border-color: rgb(251 146 60);
            background: var(--kanban-card);
            box-shadow: 0 0 0 3px rgb(251 146 60 / 0.14);
        }

        .kanban-search__clear,
        .kanban-reset {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            cursor: pointer;
        }

        .kanban-search__clear {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: transparent;
            color: var(--kanban-muted);
            transform: translateY(-50%);
        }

        .kanban-search__clear:hover {
            background: rgb(241 245 249);
            color: var(--kanban-orange);
        }

        .kanban-reset {
            height: 42px;
            border-radius: 13px;
            background: rgb(255 247 237);
            color: rgb(194 65 12);
            font-size: 13px;
            font-weight: 750;
            padding: 0 14px;
            white-space: nowrap;
        }

        .kanban-reset:hover {
            background: rgb(254 215 170);
        }

        .kanban-work-queues {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
        }

        .kanban-work-queue {
            min-width: 0;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
            align-items: center;
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-soft);
            color: var(--kanban-text);
            cursor: pointer;
            padding: 10px 11px;
            text-align: left;
            transition: border-color 140ms ease, box-shadow 140ms ease, transform 140ms ease, background 140ms ease;
        }

        .kanban-work-queue:hover,
        .kanban-work-queue.is-active {
            border-color: rgb(251 146 60);
            background: rgb(255 247 237);
            box-shadow: 0 8px 18px rgb(234 88 12 / 0.08);
            transform: translateY(-1px);
        }

        .dark .kanban-work-queue:hover,
        .dark .kanban-work-queue.is-active {
            background: rgb(39 39 42);
        }

        .kanban-work-queue__label {
            display: block;
            overflow: hidden;
            color: inherit;
            font-size: 13.25px;
            font-weight: 800;
            line-height: 1.25;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-work-queue__description {
            display: block;
            margin-top: 3px;
            overflow: hidden;
            color: var(--kanban-muted);
            font-size: 11.75px;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-work-queue__count {
            min-width: 30px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--kanban-card);
            color: rgb(194 65 12);
            font-size: 12.5px;
            font-weight: 850;
        }

        .kanban-stage-tabs {
            display: flex;
            gap: 8px;
            justify-content: center;
            overflow-x: auto;
            width: fit-content;
            max-width: 100%;
            margin-inline: auto;
            padding: 8px 10px;
            border: 1px solid var(--kanban-border);
            border-radius: 14px;
            background: var(--kanban-card);
            box-shadow: 0 8px 22px rgb(15 23 42 / 0.035);
        }

        .kanban-stage-tabs__button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex: none;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: rgb(71 85 105);
            cursor: pointer;
            font-size: 13.25px;
            font-weight: 700;
            line-height: 1;
            padding: 10px 14px;
            transition: background 140ms ease, color 140ms ease;
        }

        .kanban-stage-tabs__button:hover,
        .kanban-stage-tabs__button.is-active {
            background: rgb(255 247 237);
            color: rgb(194 65 12);
        }

        .kanban-stage-tabs__count {
            min-width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgb(253 186 116);
            background: rgb(255 247 237);
            color: rgb(194 65 12);
            font-size: 11.5px;
            font-weight: 800;
        }

        .kanban-board {
            display: grid;
            grid-template-columns: repeat(var(--kanban-column-count), minmax(210px, 1fr));
            gap: 12px;
            min-height: clamp(520px, calc(100vh - 260px), 680px);
            overflow-x: auto;
            padding-bottom: 12px;
            scroll-snap-type: x proximity;
        }

        .kanban-board.is-moving {
            cursor: progress;
            opacity: 0.92;
            pointer-events: none;
        }

        .kanban-column {
            min-height: 100%;
            overflow: hidden;
            border: 1px solid var(--kanban-border);
            border-radius: 14px;
            background: linear-gradient(180deg, var(--kanban-card), var(--kanban-soft));
            scroll-snap-align: start;
            transition: border-color 140ms ease, box-shadow 140ms ease, background 140ms ease;
        }

        .kanban-column.is-drop-target {
            border-color: rgb(251 146 60);
            box-shadow: inset 0 0 0 1px rgb(251 146 60 / 0.45), 0 12px 24px rgb(234 88 12 / 0.08);
            background: linear-gradient(180deg, rgb(255 247 237), var(--kanban-soft));
        }

        .kanban-column__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 12px;
            border-bottom: 1px solid var(--kanban-border);
            background: color-mix(in srgb, var(--kanban-card) 88%, transparent);
        }

        .kanban-column__title {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            color: var(--kanban-text);
            font-size: 13.5px;
            font-weight: 750;
            line-height: 1.3;
        }

        .kanban-column__dot {
            width: 8px;
            height: 8px;
            flex: none;
            border-radius: 999px;
            background: var(--kanban-orange);
            box-shadow: 0 0 0 4px rgb(234 88 12 / 0.12);
        }

        .kanban-column__count {
            min-width: 30px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgb(241 245 249);
            color: rgb(51 65 85);
            font-size: 13px;
            font-weight: 750;
        }

        .dark .kanban-column__count {
            background: rgb(39 39 42);
            color: rgb(228 228 231);
        }

        .kanban-column__body {
            display: flex;
            flex-direction: column;
            gap: 7px;
            max-height: calc(clamp(520px, calc(100vh - 260px), 680px) - 50px);
            overflow-y: auto;
            padding: 9px;
        }

        .kanban-card {
            display: block;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-card);
            cursor: grab;
            padding: 9px 10px;
            box-shadow: 0 6px 14px rgb(15 23 42 / 0.045);
            transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            border-color: rgb(251 146 60);
            box-shadow: 0 12px 24px rgb(15 23 42 / 0.09);
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-card.is-dragging {
            opacity: 0.62;
            transform: rotate(0.4deg) scale(0.99);
        }

        .kanban-card__content {
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .kanban-card__top {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 7px;
            align-items: start;
        }

        .kanban-card__candidate {
            margin: 0;
            color: var(--kanban-text);
            font-size: 13.25px;
            font-weight: 750;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .kanban-card__id {
            color: var(--kanban-muted);
            font-size: 12px;
            font-weight: 650;
        }

        .kanban-card__toggle {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--kanban-border);
            border-radius: 999px;
            background: transparent;
            color: var(--kanban-muted);
            cursor: pointer;
            transition: background 140ms ease, color 140ms ease, transform 140ms ease;
        }

        .kanban-card__toggle:hover {
            background: rgb(255 247 237);
            color: var(--kanban-orange);
        }

        .kanban-card__toggle svg {
            width: 13px;
            height: 13px;
            transition: transform 140ms ease;
        }

        .kanban-card.is-compact .kanban-card__toggle svg {
            transform: rotate(180deg);
        }

        .kanban-card__actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 7px;
        }

        .kanban-card__job {
            margin: 3px 0 0;
            color: rgb(71 85 105);
            font-size: 12.75px;
            line-height: 1.32;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .dark .kanban-card__job {
            color: rgb(212 212 216);
        }

        .kanban-card__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 7px;
        }

        .kanban-badge {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            border-radius: 999px;
            padding: 4px 6px;
            font-size: 11.25px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .kanban-badge.is-gray { background: rgb(241 245 249); color: rgb(51 65 85); }
        .kanban-badge.is-info { background: rgb(224 242 254); color: rgb(3 105 161); }
        .kanban-badge.is-warning { background: rgb(254 243 199); color: rgb(146 64 14); }
        .kanban-badge.is-primary { background: rgb(255 237 213); color: rgb(194 65 12); }
        .kanban-badge.is-success { background: rgb(220 252 231); color: rgb(21 128 61); }
        .kanban-badge.is-danger { background: rgb(254 226 226); color: rgb(185 28 28); }
        .kanban-badge.is-ai { background: rgb(238 242 255); color: rgb(67 56 202); }

        .kanban-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 120px;
            border: 1px dashed var(--kanban-border);
            border-radius: 12px;
            background: color-mix(in srgb, var(--kanban-card) 70%, transparent);
            color: var(--kanban-muted);
            font-size: 13px;
            text-align: center;
        }

        .kanban-card__description {
            margin: 7px 0 0;
            color: var(--kanban-muted);
            font-size: 12.25px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .kanban-card__details {
            overflow: hidden;
            transform-origin: top;
        }

        .kanban-card__footer {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-top: 7px;
            padding-top: 7px;
            border-top: 1px solid color-mix(in srgb, var(--kanban-border) 72%, transparent);
            color: var(--kanban-muted);
            font-size: 11.75px;
            line-height: 1.3;
        }

        .kanban-card__footer span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-card__quick-actions {
            display: grid;
            gap: 7px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid color-mix(in srgb, var(--kanban-border) 72%, transparent);
        }

        .kanban-card__hint {
            margin: 0;
            color: var(--kanban-muted);
            font-size: 11.6px;
            line-height: 1.35;
        }

        .kanban-card__buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .kanban-card__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 28px;
            border: 1px solid var(--kanban-border);
            border-radius: 999px;
            background: transparent;
            color: var(--kanban-text);
            cursor: pointer;
            font-size: 11.75px;
            font-weight: 750;
            line-height: 1;
            padding: 0 9px;
            text-decoration: none;
            transition: background 140ms ease, border-color 140ms ease, color 140ms ease;
        }

        .kanban-card__button:hover {
            border-color: rgb(251 146 60);
            background: rgb(255 247 237);
            color: rgb(194 65 12);
        }

        .kanban-card__button.is-primary {
            border-color: rgb(251 146 60);
            background: rgb(234 88 12);
            color: #ffffff;
        }

        .kanban-card__button.is-primary:hover {
            background: rgb(194 65 12);
            color: #ffffff;
        }

        .kanban-card__button.is-icon {
            width: 28px;
            min-width: 28px;
            padding: 0;
        }

        .kanban-card__button.is-icon svg {
            width: 15px;
            height: 15px;
        }

        .kanban-card__button.is-danger {
            color: rgb(185 28 28);
        }

        .kanban-card__button.is-danger:hover {
            border-color: rgb(252 165 165);
            background: rgb(254 242 242);
            color: rgb(153 27 27);
        }

        .kanban-card__date {
            margin: 0;
            color: var(--kanban-muted);
            font-size: 11.75px;
            line-height: 1.3;
        }

        .kanban-transition-enter,
        .kanban-transition-leave {
            transition: opacity 180ms ease, transform 180ms ease, max-height 180ms ease;
        }

        .kanban-transition-enter-start,
        .kanban-transition-leave-end {
            max-height: 0;
            opacity: 0;
            transform: translateY(-4px) scaleY(0.98);
        }

        .kanban-transition-enter-end,
        .kanban-transition-leave-start {
            max-height: 180px;
            opacity: 1;
            transform: translateY(0) scaleY(1);
        }

        .kanban-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgb(15 23 42 / 0.36);
            backdrop-filter: blur(3px);
        }

        /* Keep Filament feedback visible while a Kanban modal stays open. */
        .fi-no {
            z-index: 80 !important;
        }

        .kanban-modal-toast {
            position: fixed;
            top: 22px;
            right: 22px;
            z-index: 75;
            width: min(360px, calc(100vw - 44px));
            border: 1px solid rgb(252 165 165);
            border-radius: 14px;
            background: #ffffff;
            color: rgb(153 27 27);
            box-shadow: 0 18px 45px rgb(15 23 42 / 0.22);
            font-size: 13px;
            font-weight: 750;
            line-height: 1.45;
            padding: 12px 14px;
        }

        .kanban-modal-toast[hidden] {
            display: none;
        }

        .dark .kanban-modal-toast {
            border-color: rgb(127 29 29);
            background: rgb(24 24 27);
            color: rgb(254 202 202);
        }

        .kanban-modal {
            width: min(620px, 100%);
            max-height: min(86vh, 760px);
            overflow: hidden;
            border: 1px solid var(--kanban-border);
            border-radius: 18px;
            background: var(--kanban-card);
            box-shadow: 0 24px 60px rgb(15 23 42 / 0.22);
        }

        .kanban-modal.is-screening {
            width: min(1120px, calc(100vw - 40px));
        }

        .kanban-modal.is-pre-screening {
            width: min(820px, calc(100vw - 40px));
        }

        .kanban-pre-screening-layout {
            display: grid;
            grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr);
            gap: 14px;
            align-items: start;
        }

        .kanban-pre-screening-context,
        .kanban-pre-screening-form {
            display: grid;
            gap: 12px;
            min-width: 0;
        }

        .kanban-modal.is-interview-schedule {
            width: min(980px, calc(100vw - 40px));
        }

        .kanban-modal.is-interview-evaluation {
            width: min(820px, calc(100vw - 40px));
        }

        .kanban-modal.is-offer-draft {
            width: min(1040px, calc(100vw - 40px));
        }

        .kanban-modal__header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 18px 14px;
            border-bottom: 1px solid var(--kanban-border);
        }

        .kanban-modal__title {
            margin: 0;
            color: var(--kanban-text);
            font-size: 17px;
            font-weight: 800;
            line-height: 1.3;
        }

        .kanban-modal__meta {
            margin: 5px 0 0;
            color: var(--kanban-muted);
            font-size: 12.5px;
            line-height: 1.4;
        }

        .kanban-modal__close {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: none;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: var(--kanban-muted);
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
        }

        .kanban-modal__close:hover {
            background: rgb(241 245 249);
            color: var(--kanban-orange);
        }

        .kanban-modal__body {
            display: grid;
            gap: 14px;
            max-height: calc(min(86vh, 760px) - 76px);
            overflow-y: auto;
            padding: 16px 18px 18px;
        }

        .kanban-screening-layout {
            display: grid;
            grid-template-columns: minmax(340px, 0.85fr) minmax(440px, 1.15fr);
            gap: 14px;
            align-items: start;
        }

        .kanban-screening-main {
            display: grid;
            gap: 12px;
            min-width: 0;
        }

        .kanban-interview-layout {
            display: grid;
            grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr);
            gap: 14px;
            align-items: start;
            width: 100%;
            min-width: 0;
        }

        .kanban-offer-layout {
            display: grid;
            grid-template-columns: minmax(0, 36%) minmax(0, 64%);
            gap: 14px;
            align-items: start;
            width: 100%;
            min-width: 0;
        }

        .kanban-offer-context,
        .kanban-offer-form {
            display: grid;
            gap: 12px;
            min-width: 0;
            width: 100%;
            max-width: 100%;
        }

        .kanban-offer-context > *,
        .kanban-offer-form > * {
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
        }

        .kanban-offer-preview {
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-soft);
            overflow: hidden;
        }

        .kanban-offer-preview summary {
            cursor: pointer;
            padding: 11px 12px;
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 750;
        }

        .kanban-offer-preview > p {
            margin: 0;
            padding: 0 12px 9px;
            color: var(--kanban-muted);
            font-size: 11.5px;
            line-height: 1.4;
        }

        .kanban-offer-preview__body {
            max-height: 220px;
            overflow: auto;
            border-top: 1px solid var(--kanban-border);
            padding: 12px;
            color: var(--kanban-text);
            font-size: 12.5px;
            line-height: 1.6;
            white-space: pre-line;
        }

        .kanban-offer-brief {
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-soft);
            padding: 12px;
            overflow: hidden;
        }

        .kanban-offer-brief__name {
            margin: 0;
            color: var(--kanban-text);
            font-size: 14px;
            font-weight: 850;
            line-height: 1.3;
        }

        .kanban-offer-brief__job,
        .kanban-offer-brief__branch {
            margin: 4px 0 0;
            color: var(--kanban-muted);
            font-size: 12.5px;
            line-height: 1.4;
        }

        .kanban-offer-insight {
            display: grid;
            gap: 7px;
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-card);
            padding: 11px 12px;
            overflow: hidden;
        }

        .kanban-offer-insight__title {
            color: var(--kanban-muted);
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.02em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .kanban-offer-insight__text {
            margin: 0;
            color: var(--kanban-text);
            font-size: 12.5px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .kanban-offer-summary {
            border-color: rgb(219 234 254);
            background: rgb(239 246 255);
        }

        .dark .kanban-offer-summary {
            border-color: rgb(30 64 175 / 0.45);
            background: rgb(30 41 59);
        }

        .kanban-interview-context,
        .kanban-interview-form {
            min-width: 0;
            display: grid;
            gap: 12px;
            width: 100%;
            max-width: 100%;
        }

        .kanban-interview-context > *,
        .kanban-interview-form > * {
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
        }

        .kanban-interview-form__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .kanban-interview-ai summary {
            cursor: pointer;
            list-style: none;
        }

        .kanban-interview-ai summary::-webkit-details-marker {
            display: none;
        }

        .kanban-interview-ai summary .kanban-modal__ai-head {
            margin-bottom: 0;
        }

        .kanban-interview-ai[open] summary .kanban-modal__ai-head {
            margin-bottom: 7px;
        }

        .kanban-evaluation {
            gap: 12px;
        }

        .kanban-evaluation__brief,
        .kanban-evaluation__summary {
            display: grid;
            gap: 8px;
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-soft);
            padding: 10px 12px;
            color: var(--kanban-muted);
            font-size: 12.25px;
            line-height: 1.35;
        }

        .kanban-evaluation__brief {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .kanban-evaluation__summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .kanban-evaluation__brief span {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .kanban-evaluation__criteria {
            display: grid;
            gap: 8px;
        }

        .kanban-evaluation__section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 800;
        }

        .kanban-evaluation__criterion {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 96px;
            gap: 8px;
            align-items: start;
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-card);
            padding: 9px;
        }

        .kanban-evaluation__criterion-name {
            display: flex;
            align-items: center;
            min-height: 42px;
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 750;
            line-height: 1.35;
        }

        .kanban-evaluation__criterion .kanban-modal__textarea {
            grid-column: 1 / -1;
            min-height: 56px;
        }

        .kanban-evaluation__criterion-note {
            grid-column: 1 / -1;
        }

        .kanban-evaluation__criterion-note summary {
            cursor: pointer;
            color: var(--kanban-muted);
            font-size: 12px;
            font-weight: 700;
            list-style: none;
        }

        .kanban-evaluation__criterion-note summary::-webkit-details-marker {
            display: none;
        }

        .kanban-evaluation__criterion-note summary::before {
            content: '+';
            display: inline-block;
            margin-right: 6px;
            color: var(--kanban-accent);
            font-size: 15px;
            font-weight: 800;
            line-height: .8;
        }

        .kanban-evaluation__criterion-note[open] summary::before {
            content: '−';
        }

        .kanban-evaluation__criterion-note .kanban-modal__textarea {
            margin-top: 8px;
        }

        .kanban-evaluation__empty {
            border: 1px dashed var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-soft);
            color: var(--kanban-muted);
            font-size: 13px;
            line-height: 1.5;
            padding: 12px;
        }

        .kanban-evaluation__assignments {
            display: grid;
            margin: 8px 0 0;
            padding: 0;
            list-style: none;
        }

        .kanban-evaluation__assignment {
            padding: 9px 0;
            border-top: 1px solid var(--kanban-border);
        }

        .kanban-evaluation__assignment:first-child {
            border-top: 0;
        }

        .kanban-evaluation__assignment-row,
        .kanban-evaluation__submission-summary,
        .kanban-evaluation__panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .kanban-evaluation__assignment-person {
            min-width: 0;
        }

        .kanban-evaluation__assignment-person strong,
        .kanban-evaluation__assignment-person span {
            display: block;
        }

        .kanban-evaluation__assignment-person strong {
            overflow: hidden;
            color: var(--kanban-text);
            font-size: 13px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-evaluation__assignment-person span,
        .kanban-evaluation__assignment-note {
            color: var(--kanban-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .kanban-evaluation__assignment-actions {
            display: flex;
            align-items: center;
            flex: 0 0 auto;
            gap: 8px;
        }

        .kanban-evaluation__assignment-status {
            border-radius: 999px;
            background: var(--kanban-soft);
            padding: 4px 8px;
            color: var(--kanban-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .kanban-evaluation__assignment-status.is-submitted {
            background: rgb(220 252 231);
            color: rgb(21 128 61);
        }

        .kanban-evaluation__assignment-status.is-waived {
            background: rgb(241 245 249);
            color: rgb(71 85 105);
        }

        .kanban-evaluation__assignment-button {
            border: 0;
            background: transparent;
            padding: 4px 0;
            color: rgb(194 65 12);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .kanban-evaluation__waiver-editor {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 8px;
            margin-top: 9px;
        }

        .kanban-evaluation__waiver-editor .kanban-modal__field-error {
            grid-column: 1 / -1;
        }

        .kanban-evaluation__waiver-actions {
            display: flex;
            align-items: flex-end;
            gap: 6px;
        }

        .kanban-evaluation__panel-review {
            display: grid;
            gap: 8px;
            padding-top: 4px;
        }

        .kanban-evaluation__panel-head span {
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 800;
        }

        .kanban-evaluation__panel-head small {
            color: var(--kanban-muted);
            font-size: 12px;
        }

        .kanban-evaluation__submission {
            border-top: 1px solid var(--kanban-border);
            padding: 9px 0 0;
        }

        .kanban-evaluation__submission-summary {
            cursor: pointer;
            list-style: none;
        }

        .kanban-evaluation__submission-summary::-webkit-details-marker {
            display: none;
        }

        .kanban-evaluation__submission-person {
            min-width: 0;
        }

        .kanban-evaluation__submission-person strong,
        .kanban-evaluation__submission-person span {
            display: block;
        }

        .kanban-evaluation__submission-person strong {
            color: var(--kanban-text);
            font-size: 13px;
        }

        .kanban-evaluation__submission-person span {
            color: var(--kanban-muted);
            font-size: 11px;
        }

        .kanban-evaluation__submission-result {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 800;
        }

        .kanban-evaluation__submission-body {
            display: grid;
            gap: 8px;
            padding: 10px 0 2px;
        }

        .kanban-evaluation__submission-facts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .kanban-evaluation__submission-fact {
            color: var(--kanban-muted);
            font-size: 12px;
        }

        .kanban-evaluation__submission-fact strong {
            display: block;
            margin-top: 2px;
            color: var(--kanban-text);
        }

        .kanban-evaluation__submission-notes {
            margin: 0;
            color: var(--kanban-text);
            font-size: 12px;
            line-height: 1.5;
        }

        .kanban-evaluation__submission-criteria {
            display: grid;
            gap: 5px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .kanban-evaluation__submission-criterion {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 4px 10px;
            color: var(--kanban-text);
            font-size: 12px;
        }

        .kanban-evaluation__submission-criterion small {
            grid-column: 1 / -1;
            color: var(--kanban-muted);
            line-height: 1.4;
        }

        .kanban-evaluation__save-status {
            margin: 0;
            color: color-mix(in srgb, #166534 82%, var(--kanban-text));
            font-size: 12px;
            font-weight: 700;
            line-height: 1.45;
        }

        .kanban-evaluation__questions {
            border: 1px solid color-mix(in srgb, #60a5fa 38%, var(--kanban-border));
            border-radius: 12px;
            background: color-mix(in srgb, #eff6ff 62%, var(--kanban-soft));
            padding: 10px 12px;
        }

        .kanban-evaluation__questions summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            cursor: pointer;
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 800;
            list-style: none;
        }

        .kanban-evaluation__questions summary::-webkit-details-marker {
            display: none;
        }

        .kanban-evaluation__questions-count {
            color: var(--kanban-muted);
            font-size: 11.5px;
            font-weight: 700;
        }

        .kanban-evaluation__questions-help {
            margin: 8px 0 0;
            color: var(--kanban-muted);
            font-size: 11.5px;
            line-height: 1.4;
        }

        .kanban-evaluation__questions-list {
            display: grid;
            gap: 8px;
            margin: 10px 0 0;
            padding: 0;
            list-style: none;
        }

        .kanban-evaluation__question {
            padding: 9px 10px;
            border-radius: 9px;
            background: rgb(255 255 255 / .78);
            color: var(--kanban-text);
            font-size: 12.5px;
            line-height: 1.45;
        }

        .kanban-evaluation__question-meta {
            display: block;
            margin-top: 4px;
            color: var(--kanban-muted);
            font-size: 11.5px;
        }

        .kanban-evaluation__question-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .kanban-evaluation__question-actions p {
            margin: 0;
            color: var(--kanban-muted);
            font-size: 11.5px;
            line-height: 1.35;
        }

        .kanban-evaluation__early-completion {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-soft);
            color: var(--kanban-text);
            font-size: 13px;
            line-height: 1.45;
            padding: 10px 12px;
        }

        .kanban-evaluation__early-completion input {
            margin-top: 3px;
        }

        .kanban-evaluation__summary {
            grid-template-columns: minmax(86px, 1fr) auto auto minmax(78px, 1fr) auto;
            align-items: center;
        }

        .kanban-evaluation__summary strong {
            color: var(--kanban-text);
            font-size: 13px;
        }

        .kanban-interview-brief {
            border: 1px solid var(--kanban-border);
            border-radius: 14px;
            background: var(--kanban-soft);
            padding: 12px;
        }

        .kanban-interview-brief__title {
            margin: 0;
            color: var(--kanban-text);
            font-size: 14px;
            font-weight: 850;
            line-height: 1.3;
        }

        .kanban-interview-brief__meta {
            margin: 4px 0 0;
            color: var(--kanban-muted);
            font-size: 12.5px;
            line-height: 1.4;
        }

        .kanban-interview-note {
            border: 1px solid var(--kanban-border);
            border-radius: 13px;
            background: var(--kanban-card);
            padding: 11px 12px;
        }

        .kanban-interview-note summary {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 9px;
            cursor: pointer;
            list-style: none;
        }

        .kanban-interview-note summary::-webkit-details-marker {
            display: none;
        }

        .kanban-interview-note summary > span:first-child {
            min-width: 0;
        }

        .kanban-interview-note__label {
            display: block;
            color: var(--kanban-muted);
            font-size: 11.5px;
            font-weight: 850;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .kanban-interview-note__value {
            margin: 6px 0 0;
            color: var(--kanban-text);
            font-size: 12.75px;
            line-height: 1.5;
        }

        .kanban-interview-note__excerpt {
            display: block;
            overflow: hidden;
            color: var(--kanban-text);
            font-size: 12.5px;
            line-height: 1.4;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-interview-note__toggle {
            flex: 0 0 auto;
            color: var(--kanban-accent);
            font-size: 11.5px;
            font-weight: 800;
            line-height: 1.2;
        }

        .kanban-interview-note[open] .kanban-interview-note__toggle::after {
            content: 'Thu gọn';
        }

        .kanban-interview-note[open] .kanban-interview-note__excerpt {
            display: none;
        }

        .kanban-interview-note:not([open]) .kanban-interview-note__toggle::after {
            content: 'Xem';
        }

        .kanban-scorecard-preview {
            margin-top: 8px;
            border: 1px solid var(--kanban-border);
            border-radius: 11px;
            background: color-mix(in srgb, var(--kanban-card) 92%, var(--kanban-accent) 8%);
            padding: 9px 10px;
        }

        .kanban-scorecard-preview__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            color: var(--kanban-text);
            font-size: 11.5px;
            font-weight: 800;
            cursor: pointer;
            list-style: none;
        }

        .kanban-scorecard-preview__head::-webkit-details-marker {
            display: none;
        }

        .kanban-scorecard-preview__count {
            color: var(--kanban-muted);
            font-weight: 700;
        }

        .kanban-scorecard-preview__list {
            display: grid;
            gap: 4px;
            max-height: 92px;
            margin: 7px 0 0;
            overflow-y: auto;
            padding-left: 17px;
            color: var(--kanban-muted);
            font-size: 12px;
            line-height: 1.35;
        }

        .kanban-scorecard-preview__list li {
            overflow-wrap: anywhere;
        }

        .kanban-evaluator-picker {
            border: 1px solid var(--kanban-border);
            border-radius: 8px;
            background: color-mix(in srgb, var(--kanban-card) 96%, var(--kanban-accent) 4%);
            overflow: hidden;
        }

        .kanban-interview-panel {
            display: grid;
            gap: 10px;
            border: 1px solid var(--kanban-border);
            border-radius: 8px;
            background: color-mix(in srgb, var(--kanban-card) 98%, var(--kanban-accent) 2%);
            padding: 12px;
        }

        .kanban-interview-panel__head {
            display: grid;
            gap: 2px;
        }

        .kanban-interview-panel__title {
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 800;
        }

        .kanban-interview-panel__help,
        .kanban-evaluator-picker__disabled {
            margin: 0;
            color: var(--kanban-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .kanban-evaluator-picker__disabled {
            border: 1px dashed var(--kanban-border);
            border-radius: 8px;
            background: color-mix(in srgb, var(--kanban-card) 96%, var(--kanban-muted) 4%);
            padding: 10px 12px;
        }

        .kanban-evaluator-picker__disabled strong {
            display: block;
            margin-bottom: 2px;
            color: var(--kanban-text);
            font-size: 12px;
        }

        .kanban-evaluator-picker__summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 52px;
            padding: 9px 12px;
            cursor: pointer;
            list-style: none;
        }

        .kanban-evaluator-picker__summary::-webkit-details-marker {
            display: none;
        }

        .kanban-evaluator-picker__summary > div {
            display: grid;
            gap: 2px;
            min-width: 0;
        }

        .kanban-evaluator-picker__summary-count {
            flex: 0 0 auto;
            color: var(--kanban-muted);
            font-size: 12px;
            font-weight: 700;
        }

        .kanban-evaluator-picker[open] .kanban-evaluator-picker__summary {
            border-bottom: 1px solid var(--kanban-border);
        }

        .kanban-evaluator-picker__body {
            display: grid;
            gap: 9px;
            padding: 10px 12px 12px;
        }

        .kanban-evaluator-picker__help {
            margin: 0;
            color: var(--kanban-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .kanban-evaluator-picker__selected {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .kanban-evaluator-picker__chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            max-width: 100%;
            border: 1px solid color-mix(in srgb, var(--kanban-accent) 26%, var(--kanban-border));
            border-radius: 999px;
            background: color-mix(in srgb, var(--kanban-card) 90%, var(--kanban-accent) 10%);
            padding: 4px 6px 4px 9px;
            color: var(--kanban-text);
            font-size: 12px;
            line-height: 1.25;
        }

        .kanban-evaluator-picker__chip span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-evaluator-picker__chip button {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 20px;
            height: 20px;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--kanban-muted);
            cursor: pointer;
            font-size: 17px;
            line-height: 1;
        }

        .kanban-evaluator-picker__chip button:hover {
            background: color-mix(in srgb, var(--kanban-text) 8%, transparent);
            color: var(--kanban-text);
        }

        .kanban-evaluator-picker__search {
            width: 100%;
            min-height: 38px;
            border: 1px solid var(--kanban-border);
            border-radius: 8px;
            background: var(--kanban-card);
            padding: 7px 10px;
            color: var(--kanban-text);
            font: inherit;
            font-size: 13px;
        }

        .kanban-evaluator-picker__options {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 5px;
            max-height: 190px;
            overflow-y: auto;
            padding-right: 3px;
        }

        .kanban-evaluator-picker__option {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            min-width: 0;
            border: 1px solid var(--kanban-border);
            border-radius: 8px;
            background: color-mix(in srgb, var(--kanban-card) 98%, var(--kanban-accent) 2%);
            padding: 7px 9px;
            color: var(--kanban-text);
            font-size: 12px;
            line-height: 1.35;
            cursor: pointer;
        }

        .kanban-evaluator-picker__option:hover {
            border-color: color-mix(in srgb, var(--kanban-accent) 45%, var(--kanban-border));
            background: color-mix(in srgb, var(--kanban-card) 94%, var(--kanban-accent) 6%);
        }

        .kanban-evaluator-picker__option input {
            flex: 0 0 auto;
            width: 15px;
            height: 15px;
            margin-top: 1px;
            accent-color: var(--kanban-accent);
        }

        .kanban-evaluator-picker__option span {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .kanban-evaluator-picker__empty {
            margin: 0;
            color: var(--kanban-muted);
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .kanban-evaluator-picker__options {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .kanban-screening-cv {
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--kanban-border);
            border-radius: 14px;
            background: var(--kanban-card);
        }

        .kanban-screening-cv__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1px solid var(--kanban-border);
            padding: 10px 12px;
        }

        .kanban-screening-cv__title {
            min-width: 0;
        }

        .kanban-screening-cv__eyebrow {
            display: block;
            color: var(--kanban-muted);
            font-size: 11px;
            font-weight: 850;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .kanban-screening-cv__name {
            display: block;
            margin-top: 3px;
            overflow: hidden;
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 850;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-screening-cv__frame {
            width: 100%;
            height: min(62vh, 620px);
            min-height: 500px;
            border: 0;
            background: rgb(243 244 246);
        }

        .kanban-screening-cv__empty {
            min-height: 360px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--kanban-muted);
            font-size: 13px;
            text-align: center;
        }

        .kanban-modal__message {
            margin: 0;
            color: rgb(71 85 105);
            font-size: 13.5px;
            line-height: 1.5;
        }

        .dark .kanban-modal__message {
            color: rgb(212 212 216);
        }

        .kanban-modal__error {
            border: 1px solid rgb(252 165 165);
            border-radius: 12px;
            background: rgb(254 242 242);
            color: rgb(153 27 27);
            font-size: 13px;
            font-weight: 750;
            line-height: 1.45;
            padding: 10px 12px;
        }

        .dark .kanban-modal__error {
            border-color: rgb(127 29 29);
            background: rgb(69 10 10 / 0.32);
            color: rgb(254 202 202);
        }

        .kanban-modal__history {
            display: grid;
            gap: 8px;
            margin-top: 9px;
        }

        .kanban-modal__history-item {
            display: grid;
            gap: 3px;
            padding: 9px 10px;
            border-left: 2px solid rgb(203 213 225);
            color: var(--kanban-muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .kanban-modal__history-item strong {
            color: var(--kanban-text);
            font-size: 12.5px;
        }

        .kanban-modal__field {
            display: grid;
            gap: 7px;
        }

        .kanban-modal__label {
            color: var(--kanban-text);
            font-size: 12.5px;
            font-weight: 800;
            line-height: 1.25;
        }

        .kanban-modal__field-error {
            color: rgb(185 28 28);
            font-size: 11.5px;
            font-weight: 650;
            line-height: 1.35;
        }

        .kanban-modal__field-help {
            color: var(--kanban-muted);
            font-size: 11.5px;
            line-height: 1.4;
        }

        .kanban-modal__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .kanban-modal__input {
            width: 100%;
            height: 42px;
            border: 1px solid var(--kanban-border);
            border-radius: 10px;
            background: var(--kanban-card);
            color: var(--kanban-text);
            font-size: 13px;
            padding: 0 12px;
        }

        .kanban-modal__input:focus {
            border-color: rgb(251 146 60);
            outline: 0;
            box-shadow: 0 0 0 3px rgb(251 146 60 / 0.14);
        }

        .kanban-modal__notice {
            display: grid;
            gap: 4px;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-soft);
            color: var(--kanban-muted);
            font-size: 13px;
            line-height: 1.45;
            padding: 11px 12px;
        }

        .kanban-modal__notice strong {
            color: var(--kanban-text);
        }

        .kanban-modal__notice.is-warning {
            border-color: rgb(251 191 36 / 0.5);
            background: rgb(254 243 199 / 0.5);
        }

        @media (max-width: 640px) {
            .kanban-modal__grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .kanban-evaluation__assignment-row,
            .kanban-evaluation__submission-summary,
            .kanban-evaluation__panel-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .kanban-evaluation__assignment-actions {
                width: 100%;
                justify-content: space-between;
            }

            .kanban-evaluation__waiver-editor,
            .kanban-evaluation__submission-facts {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .kanban-modal__select {
            width: 100%;
            height: 42px;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-soft);
            color: var(--kanban-text);
            font-size: 13.5px;
            outline: none;
            padding: 0 12px;
        }

        .kanban-modal__select:focus {
            border-color: rgb(251 146 60);
            background: var(--kanban-card);
            box-shadow: 0 0 0 3px rgb(251 146 60 / 0.14);
        }

        .kanban-modal__readonly {
            display: flex;
            min-height: 42px;
            align-items: center;
            border: 1px solid var(--kanban-border);
            border-radius: 8px;
            background: color-mix(in srgb, var(--kanban-soft) 72%, var(--kanban-card) 28%);
            color: var(--kanban-text);
            font-size: 13.5px;
            font-weight: 700;
            padding: 9px 12px;
        }

        .kanban-modal__facts {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .kanban-modal__fact {
            min-width: 0;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-soft);
            padding: 9px 10px;
        }

        .kanban-modal__fact-label {
            display: block;
            color: var(--kanban-muted);
            font-size: 11px;
            font-weight: 800;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .kanban-modal__fact-value {
            display: block;
            margin-top: 4px;
            overflow-wrap: anywhere;
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 750;
            line-height: 1.35;
        }

        .kanban-modal__cv {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-card);
            padding: 10px 12px;
        }

        .kanban-modal__cv-name {
            min-width: 0;
            overflow: hidden;
            color: var(--kanban-text);
            font-size: 13px;
            font-weight: 800;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .kanban-offer-context .kanban-modal__cv .kanban-modal__button {
            flex: 0 0 auto;
            min-height: 34px;
            padding: 0 11px;
            white-space: nowrap;
        }

        .kanban-modal__ai {
            border: 1px solid rgb(219 234 254);
            border-radius: 13px;
            background: rgb(239 246 255);
            color: rgb(30 41 59);
            padding: 11px 12px;
        }

        .dark .kanban-modal__ai {
            border-color: rgb(30 64 175 / 0.45);
            background: rgb(30 41 59);
            color: rgb(226 232 240);
        }

        .kanban-modal__ai-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 7px;
        }

        .kanban-modal__ai-title {
            font-size: 13px;
            font-weight: 850;
            line-height: 1.25;
        }

        .kanban-modal__ai-title-group {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            min-width: 0;
        }

        .kanban-modal__ai-badges {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 5px;
        }

        .kanban-modal__ai-badge {
            min-height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgb(15 23 42);
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 850;
            line-height: 1.15;
            padding: 0 8px;
            text-align: center;
        }

        .kanban-modal__ai-badge.is-score-low {
            background: rgb(220 38 38);
        }

        .kanban-modal__ai-badge.is-score-medium {
            background: rgb(234 88 12);
        }

        .kanban-modal__ai-badge.is-score-high {
            background: rgb(22 163 74);
        }

        .kanban-modal__ai-badge.is-score-neutral {
            background: rgb(100 116 139);
        }

        .kanban-modal__ai-action {
            min-height: 28px;
            border: 1px solid rgb(191 219 254);
            border-radius: 999px;
            background: #ffffff;
            color: rgb(29 78 216);
            cursor: pointer;
            font-size: 11.5px;
            font-weight: 850;
            line-height: 1;
            padding: 0 10px;
            white-space: nowrap;
        }

        .kanban-modal__ai-action:hover {
            border-color: rgb(59 130 246);
            background: rgb(239 246 255);
        }

        .kanban-modal__ai-action:disabled {
            cursor: wait;
            opacity: 0.7;
        }

        .kanban-modal__ai-confirm {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 8px 0 9px;
            border: 1px solid rgb(191 219 254);
            border-radius: 11px;
            background: rgb(255 255 255 / 0.72);
            color: rgb(71 85 105);
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
            padding: 8px 9px;
        }

        .kanban-modal__ai-confirm-text {
            min-width: 0;
        }

        .kanban-modal__ai-confirm-actions {
            display: inline-flex;
            align-items: center;
            flex: none;
            gap: 6px;
        }

        .kanban-modal__ai-confirm-button {
            min-height: 26px;
            border: 1px solid rgb(191 219 254);
            border-radius: 999px;
            background: #ffffff;
            color: rgb(29 78 216);
            cursor: pointer;
            font-size: 11.5px;
            font-weight: 850;
            line-height: 1;
            padding: 0 9px;
            white-space: nowrap;
        }

        .kanban-modal__ai-confirm-button.is-primary {
            border-color: rgb(234 88 12);
            background: rgb(234 88 12);
            color: #ffffff;
        }

        .kanban-modal__ai-confirm-button:hover {
            border-color: rgb(59 130 246);
            background: rgb(239 246 255);
        }

        .kanban-modal__ai-confirm-button.is-primary:hover {
            border-color: rgb(194 65 12);
            background: rgb(194 65 12);
        }

        .kanban-modal__ai-text,
        .kanban-modal__ai-list {
            margin: 0;
            font-size: 12.75px;
            line-height: 1.5;
        }

        .kanban-modal__ai-list {
            display: grid;
            gap: 3px;
            padding-left: 16px;
        }

        .kanban-modal__ai-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
            margin-top: 10px;
        }

        .kanban-modal__ai-column {
            min-width: 0;
            border: 1px solid rgb(191 219 254 / 0.72);
            border-radius: 10px;
            background: rgb(255 255 255 / 0.58);
            padding: 8px 9px;
        }

        .kanban-modal__ai-column li {
            text-align: left;
            text-wrap: pretty;
        }

        .kanban-modal__ai-more {
            margin-top: 9px;
            border-top: 1px solid rgb(191 219 254);
            padding-top: 8px;
        }

        .kanban-modal__ai-more summary {
            cursor: pointer;
            color: rgb(29 78 216);
            font-size: 12px;
            font-weight: 800;
            list-style: none;
        }

        .kanban-modal__ai-more summary::-webkit-details-marker {
            display: none;
        }

        .kanban-modal__ai-section-title {
            margin-bottom: 4px;
            font-size: 11.5px;
            font-weight: 850;
            text-transform: uppercase;
        }

        .kanban-modal__textarea {
            width: 100%;
            min-height: 110px;
            resize: vertical;
            border: 1px solid var(--kanban-border);
            border-radius: 12px;
            background: var(--kanban-soft);
            color: var(--kanban-text);
            font-size: 13.5px;
            line-height: 1.45;
            outline: none;
            padding: 11px 12px;
        }

        .kanban-modal__textarea:focus {
            border-color: rgb(251 146 60);
            background: var(--kanban-card);
            box-shadow: 0 0 0 3px rgb(251 146 60 / 0.14);
        }

        .kanban-modal__actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .kanban-evaluation > .kanban-modal__actions {
            position: sticky;
            bottom: -18px;
            z-index: 2;
            margin: 0 -18px -18px;
            padding: 12px 18px 18px;
            border-top: 1px solid var(--kanban-border);
            background: color-mix(in srgb, var(--kanban-card) 94%, transparent);
        }

        .kanban-screening-main > .kanban-modal__actions {
            margin-top: -2px;
        }

        .kanban-modal__button {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--kanban-border);
            border-radius: 999px;
            background: var(--kanban-card);
            color: var(--kanban-text);
            cursor: pointer;
            font-size: 13px;
            font-weight: 750;
            padding: 0 14px;
            text-decoration: none;
        }

        .kanban-modal__button[disabled] {
            cursor: wait;
            opacity: 0.72;
        }

        .kanban-modal__button:hover {
            background: rgb(255 247 237);
            border-color: rgb(251 146 60);
            color: rgb(194 65 12);
        }

        .kanban-modal__button.is-primary {
            border-color: rgb(234 88 12);
            background: rgb(234 88 12);
            color: #ffffff;
        }

        .kanban-modal__button.is-primary:hover {
            background: rgb(194 65 12);
            color: #ffffff;
        }

        .kanban-modal__button.is-danger {
            border-color: rgb(220 38 38);
            background: rgb(220 38 38);
            color: #ffffff;
        }

        .kanban-modal__button.is-danger:hover {
            background: rgb(185 28 28);
            color: #ffffff;
        }

        .kanban-schedule-assist {
            display: grid;
            gap: 10px;
            border: 1px solid var(--kanban-border);
            border-radius: 14px;
            background: var(--kanban-soft);
            padding: 11px;
        }

        .kanban-availability-check__notice {
            border: 1px solid rgb(253 186 116);
            border-radius: 12px;
            background: rgb(255 247 237);
            color: rgb(154 52 18);
            font-size: 12.75px;
            font-weight: 700;
            line-height: 1.45;
            padding: 9px 11px;
        }

        .dark .kanban-availability-check__notice {
            border-color: rgb(124 45 18);
            background: rgb(67 20 7 / 0.36);
            color: rgb(254 215 170);
        }

        .kanban-schedule-preview {
            display: grid;
            gap: 7px;
        }

        .kanban-schedule-preview__title {
            color: var(--kanban-text);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .kanban-schedule-preview__list {
            display: grid;
            gap: 6px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .kanban-schedule-preview__item {
            display: grid;
            gap: 2px;
            border-radius: 10px;
            background: var(--kanban-card);
            padding: 8px 9px;
        }

        .kanban-schedule-preview__time {
            color: rgb(234 88 12);
            font-size: 12px;
            font-weight: 800;
        }

        .kanban-schedule-preview__name {
            color: var(--kanban-text);
            font-size: 12.75px;
            font-weight: 750;
            line-height: 1.35;
        }

        .kanban-schedule-preview__meta,
        .kanban-schedule-preview__empty,
        .kanban-schedule-preview__suggestions {
            color: var(--kanban-muted);
            font-size: 12.25px;
            line-height: 1.35;
        }

        @media (max-width: 900px) {
            .kanban-modal.is-screening,
            .kanban-modal.is-interview-schedule,
            .kanban-modal.is-offer-draft {
                width: min(680px, calc(100vw - 28px));
            }

            .kanban-screening-layout,
            .kanban-pre-screening-layout,
            .kanban-interview-layout,
            .kanban-offer-layout {
                grid-template-columns: 1fr;
            }

            .kanban-evaluation__brief {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .kanban-screening-cv__frame {
                height: 520px;
                min-height: 420px;
            }

            .kanban-control-top {
                grid-template-columns: 1fr;
            }

            .kanban-search-wrap {
                min-width: 0;
            }
        }

        @media (max-width: 768px) {
            .kanban-modal__facts,
            .kanban-modal__ai-grid,
            .kanban-interview-form__grid,
            .kanban-evaluation__brief,
            .kanban-evaluation__summary {
                grid-template-columns: 1fr;
            }

            .kanban-board {
                grid-template-columns: repeat(var(--kanban-column-count), minmax(260px, 86vw));
            }

            .kanban-work-queues {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div
        class="recruitment-kanban"
        x-data="{
            activeStage: 'all',
            draggingCardId: null,
            draggingFromStage: null,
            dragOverStage: null,
            isMovingCard: false,
            scrollToStage(stage) {
                document.getElementById(`kanban-stage-${stage}`)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'start',
                });
            },
            startDrag(event, cardId, stage) {
                if (this.isMovingCard) {
                    event.preventDefault();
                    return;
                }

                this.draggingCardId = cardId;
                this.draggingFromStage = stage;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', String(cardId));
            },
            async dropOnStage(event, stage) {
                const cardId = Number(event.dataTransfer.getData('text/plain') || this.draggingCardId);
                const fromStage = this.draggingFromStage;

                this.draggingCardId = null;
                this.draggingFromStage = null;
                this.dragOverStage = null;

                if (! cardId || fromStage === stage || this.isMovingCard) {
                    return;
                }

                this.isMovingCard = true;

                try {
                    await $wire.moveApplicationToStage(cardId, stage);
                } finally {
                    this.isMovingCard = false;
                }
            },
            endDrag() {
                this.draggingCardId = null;
                this.draggingFromStage = null;
                this.dragOverStage = null;
            },
        }"
    >
        <section class="kanban-panel kanban-controls" aria-label="Bộ lọc Kanban">
            <div class="kanban-control-top">
                <div class="kanban-summary">
                    <h2 class="kanban-summary__title">Bảng điều phối ứng tuyển</h2>
                    <p class="kanban-summary__meta">
                        Đang theo dõi {{ $totalApplications }} hồ sơ ứng tuyển.
                        @if ($quickFilter !== 'all')
                            Hàng đợi: {{ $activeQueue['label'] }}.
                        @endif
                    </p>
                </div>

                <div class="kanban-search-wrap">
                    <div class="kanban-search">
                        <svg class="kanban-search__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.631 2.631a.75.75 0 1 0 1.061-1.06l-2.631-2.632A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                        </svg>

                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            class="kanban-search__input"
                            placeholder="Tìm ứng viên, vị trí, email hoặc mã hồ sơ..."
                        >

                        @if (filled($search))
                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="kanban-search__clear"
                                aria-label="Xóa từ khóa tìm kiếm"
                            >
                                ×
                            </button>
                        @endif
                    </div>

                    @if ($quickFilter !== 'all' || filled($search))
                        <button
                            type="button"
                            class="kanban-reset"
                            wire:click="$set('quickFilter', 'all'); $set('search', '')"
                        >
                            Đặt lại
                        </button>
                    @endif
                </div>
            </div>

            <div class="kanban-work-queues" aria-label="Hàng đợi xử lý">
                @foreach ($workQueues as $queueKey => $queue)
                    <button
                        type="button"
                        wire:click="$set('quickFilter', '{{ $queueKey }}')"
                        class="kanban-work-queue {{ $quickFilter === $queueKey ? 'is-active' : '' }}"
                    >
                        <span>
                            <span class="kanban-work-queue__label">{{ $queue['label'] }}</span>
                            <span class="kanban-work-queue__description">{{ $queue['description'] }}</span>
                        </span>

                        <span class="kanban-work-queue__count">{{ $queue['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <nav class="kanban-stage-tabs" aria-label="Điều hướng giai đoạn">
                <button
                    type="button"
                    class="kanban-stage-tabs__button"
                    x-bind:class="{ 'is-active': activeStage === 'all' }"
                    x-on:click="activeStage = 'all'; scrollToStage('{{ $columns[0]['key'] ?? '' }}')"
                >
                    <span>Tất cả</span>
                    <span class="kanban-stage-tabs__count">{{ $totalApplications }}</span>
                </button>

                @foreach ($columns as $column)
                    <button
                        type="button"
                        class="kanban-stage-tabs__button"
                        x-bind:class="{ 'is-active': activeStage === '{{ $column['key'] }}' }"
                        x-on:click="activeStage = '{{ $column['key'] }}'; scrollToStage('{{ $column['key'] }}')"
                    >
                        <span>{{ $column['label'] }}</span>
                        <span class="kanban-stage-tabs__count">{{ $column['count'] }}</span>
                    </button>
                @endforeach
            </nav>
        </section>

        <div
            class="kanban-board"
            style="--kanban-column-count: {{ count($columns) }}"
            x-bind:class="{ 'is-moving': isMovingCard }"
            aria-label="Bảng Kanban ứng tuyển"
        >
            @foreach ($columns as $column)
                <section
                    class="kanban-column"
                    id="kanban-stage-{{ $column['key'] }}"
                    x-bind:class="{ 'is-drop-target': dragOverStage === '{{ $column['key'] }}' }"
                    x-on:dragover.prevent="dragOverStage = '{{ $column['key'] }}'"
                    x-on:dragleave.self="dragOverStage = null"
                    x-on:drop.prevent="dropOnStage($event, '{{ $column['key'] }}')"
                >
                    <header class="kanban-column__header">
                        <div class="kanban-column__title">
                            <span class="kanban-column__dot" aria-hidden="true"></span>
                            <span>{{ $column['label'] }}</span>
                        </div>

                        <span class="kanban-column__count">{{ $column['count'] }}</span>
                    </header>

                    <div class="kanban-column__body">
                        @forelse ($column['cards'] as $card)
                            <article
                                x-data="{ expanded: false }"
                                class="kanban-card"
                                draggable="true"
                                x-bind:class="{ 'is-compact': ! expanded, 'is-dragging': draggingCardId === {{ $card['id'] }} }"
                                x-on:dragstart="startDrag($event, {{ $card['id'] }}, '{{ $column['key'] }}')"
                                x-on:dragend="endDrag()"
                            >
                                <a href="{{ $card['url'] }}" class="kanban-card__content" aria-label="Xem chi tiết hồ sơ {{ $card['candidate'] }}">
                                    <div class="kanban-card__top">
                                        <div>
                                            <h3 class="kanban-card__candidate">{{ $card['candidate'] }}</h3>
                                            <p class="kanban-card__job">{{ $card['job'] }}</p>
                                        </div>

                                        <span class="kanban-card__id">#{{ $card['id'] }}</span>
                                    </div>

                                    <div class="kanban-card__badges">
                                        <span class="kanban-badge {{ $toneClasses[$card['color']] ?? $toneClasses['gray'] }}">
                                            {{ $card['status'] }}
                                        </span>

                                        @if ($card['has_ai'])
                                            <span class="kanban-badge is-ai">AI {{ $card['ai_score'] }}/100</span>
                                        @endif
                                    </div>

                                    <div
                                        x-show="expanded"
                                        x-transition:enter="kanban-transition-enter"
                                        x-transition:enter-start="kanban-transition-enter-start"
                                        x-transition:enter-end="kanban-transition-enter-end"
                                        x-transition:leave="kanban-transition-leave"
                                        x-transition:leave-start="kanban-transition-leave-start"
                                        x-transition:leave-end="kanban-transition-leave-end"
                                        class="kanban-card__details"
                                    >
                                        @if ($card['description'])
                                            <p class="kanban-card__description">{{ $card['description'] }}</p>
                                        @endif

                                        <div class="kanban-card__footer">
                                            @if ($card['branch'])
                                                <span>{{ $card['branch'] }}</span>
                                            @endif

                                            @if ($card['department'])
                                                <span>{{ $card['department'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>

                                <div
                                    x-show="expanded"
                                    x-transition:enter="kanban-transition-enter"
                                    x-transition:enter-start="kanban-transition-enter-start"
                                    x-transition:enter-end="kanban-transition-enter-end"
                                    x-transition:leave="kanban-transition-leave"
                                    x-transition:leave-start="kanban-transition-leave-start"
                                    x-transition:leave-end="kanban-transition-leave-end"
                                    class="kanban-card__quick-actions"
                                >
                                    @if (! empty($card['stage_actions']))
                                        <p class="kanban-card__hint">{{ $card['stage_actions'][0]['hint'] }}</p>
                                    @endif

                                    @if (! empty($card['stage_actions']) || $card['can_reject'])
                                        <div class="kanban-card__buttons">
                                            @foreach ($card['stage_actions'] as $action)
                                                <button
                                                    type="button"
                                                    class="kanban-card__button {{ $action['primary'] ? 'is-primary' : '' }} {{ $action['key'] === 'update_interview_schedule' ? 'is-icon' : '' }}"
                                                    @if ($action['key'] === 'update_interview_schedule')
                                                        aria-label="{{ $action['label'] }}"
                                                        title="{{ $action['label'] }}"
                                                    @endif
                                                    @if ($action['key'] === 'send_interview_schedule')
                                                        wire:click="requestInterviewScheduleDeliveryFromKanban({{ $card['id'] }})"
                                                    @elseif ($action['key'] === 'update_interview_schedule')
                                                        wire:click="openInterviewScheduleFromKanban({{ $card['id'] }})"
                                                    @elseif ($action['key'] === 'evaluate_interview')
                                                        wire:click="openInterviewEvaluationFromKanban({{ $card['id'] }})"
                                                    @elseif ($action['key'] === 'record_pre_screening')
                                                        wire:click="openPreScreeningFromKanban({{ $card['id'] }})"
                                                    @elseif ($action['key'] === 'edit_offer_draft')
                                                        wire:click="openOfferDraftFromKanban({{ $card['id'] }})"
                                                    @elseif ($action['key'] === 'submit_offer_approval')
                                                        wire:click="requestOfferApprovalFromKanban({{ $card['id'] }})"
                                                    @endif
                                                >
                                                    @if ($action['key'] === 'update_interview_schedule')
                                                        <x-filament::icon icon="heroicon-m-pencil-square" />
                                                    @else
                                                        {{ $action['label'] }}
                                                    @endif
                                                </button>
                                            @endforeach

                                            @if ($card['can_reject'])
                                                <button
                                                    type="button"
                                                    class="kanban-card__button is-danger"
                                                    wire:click="openKanbanRejectionFromCard({{ $card['id'] }})"
                                                >
                                                    Từ chối
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="kanban-card__actions">
                                    @if ($card['applied_at'])
                                        <p class="kanban-card__date">Nộp: {{ $card['applied_at'] }}</p>
                                    @else
                                        <span></span>
                                    @endif

                                    <button
                                        type="button"
                                        class="kanban-card__toggle"
                                        x-on:click="expanded = ! expanded"
                                        x-bind:aria-label="expanded ? 'Thu gọn thẻ hồ sơ' : 'Mở rộng thẻ hồ sơ'"
                                    >
                                        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M14.77 12.79a.75.75 0 0 1-1.06-.02L10 8.83l-3.71 3.94a.75.75 0 1 1-1.08-1.04l4.25-4.5a.75.75 0 0 1 1.08 0l4.25 4.5a.75.75 0 0 1-.02 1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="kanban-empty">
                                Chưa có hồ sơ ở giai đoạn này.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>

        @if ($kanbanDropAction)
            <div
                class="kanban-modal-backdrop"
                wire:key="kanban-drop-action-modal"
                x-data="{
                    modalVisible: true,
                    closeModal() {
                        this.modalVisible = false;
                        setTimeout(() => $wire.dismissKanbanDropAction(), 140);
                    },
                }"
                x-show="modalVisible"
                x-transition.opacity.duration.140ms
                x-on:keydown.escape.window="closeModal()"
            >
                @if (filled($kanbanModalError))
                    <div
                        class="kanban-modal-toast"
                        role="alert"
                        wire:key="kanban-modal-toast-{{ $kanbanModalErrorKey }}"
                        x-data="{ visible: true }"
                        x-init="setTimeout(() => visible = false, 3600)"
                        x-show="visible"
                        x-transition.opacity.duration.180ms
                    >
                        {{ $kanbanModalError }}
                    </div>
                @endif

                <section class="kanban-modal {{ ($kanbanDropAction['type'] ?? null) === 'screening' ? 'is-screening' : '' }} {{ ($kanbanDropAction['type'] ?? null) === 'pre_screening' ? 'is-pre-screening' : '' }} {{ ($kanbanDropAction['type'] ?? null) === 'interview_schedule' ? 'is-interview-schedule' : '' }} {{ ($kanbanDropAction['type'] ?? null) === 'interview_evaluation' ? 'is-interview-evaluation' : '' }} {{ ($kanbanDropAction['type'] ?? null) === 'offer_draft' ? 'is-offer-draft' : '' }}" role="dialog" aria-modal="true" aria-labelledby="kanban-drop-action-title">
                    <header class="kanban-modal__header">
                        <div>
                            <h3 class="kanban-modal__title" id="kanban-drop-action-title">
                                {{ $kanbanDropAction['title'] ?? 'Xử lý hồ sơ' }}
                            </h3>

                            @if (filled($kanbanDropAction['candidate'] ?? null))
                                <p class="kanban-modal__meta">
                                    {{ $kanbanDropAction['candidate'] }}
                                    @if (filled($kanbanDropAction['job'] ?? null))
                                        · {{ $kanbanDropAction['job'] }}
                                    @endif
                                </p>
                            @endif
                        </div>

                        <button
                            type="button"
                            class="kanban-modal__close"
                            x-on:click="closeModal()"
                            aria-label="Đóng"
                        >
                            ×
                        </button>
                    </header>

                    @if (($kanbanDropAction['type'] ?? null) === 'rejection')
                        <form class="kanban-modal__body" wire:submit.prevent="rejectApplicationFromKanban">
                            <p class="kanban-modal__message">
                                {{ $kanbanDropAction['message'] ?? 'Vui lòng nhập lý do từ chối hồ sơ.' }}
                            </p>

                            <textarea
                                class="kanban-modal__textarea"
                                wire:model.defer="kanbanRejectionReason"
                                placeholder="Nhập lý do từ chối để lưu căn cứ xử lý..."
                            ></textarea>

                            <div class="kanban-modal__actions">
                                <button type="button" class="kanban-modal__button" x-on:click="closeModal()">
                                    Hủy
                                </button>

                                <button
                                    type="submit"
                                    class="kanban-modal__button is-danger"
                                    wire:loading.attr="disabled"
                                    wire:target="rejectApplicationFromKanban"
                                >
                                    <span wire:loading.remove wire:target="rejectApplicationFromKanban">Xác nhận từ chối</span>
                                    <span wire:loading wire:target="rejectApplicationFromKanban">Đang xử lý...</span>
                                </button>
                            </div>
                        </form>
                    @elseif (($kanbanDropAction['type'] ?? null) === 'screening')
                        @php
                            $screening = $kanbanDropAction['screening_context'] ?? [];
                            $ai = $screening['ai'] ?? [];
                        @endphp
                        <form
                            class="kanban-modal__body"
                            wire:submit.prevent="screenApplicationFromKanban"
                            x-data="{ screeningDecision: @entangle('kanbanScreeningDecision'), aiNoteOpen: false }"
                        >
                            <div class="kanban-screening-layout">
                                <div class="kanban-screening-main">
                                    <div class="kanban-modal__facts">
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Email</span>
                                            <span class="kanban-modal__fact-value">{{ $screening['candidate_email'] ?? '-' }}</span>
                                        </div>
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Số điện thoại</span>
                                            <span class="kanban-modal__fact-value">{{ $screening['candidate_phone'] ?? '-' }}</span>
                                        </div>
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Kinh nghiệm</span>
                                            <span class="kanban-modal__fact-value">{{ $screening['experience'] ?? '-' }}</span>
                                        </div>
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Tiêu đề hồ sơ</span>
                                            <span class="kanban-modal__fact-value">{{ $screening['profile_title'] ?? '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="kanban-modal__ai" x-data="{ confirmAiAnalysis: false }">
                                        <div class="kanban-modal__ai-head">
                                            <div class="kanban-modal__ai-title-group">
                                                <div class="kanban-modal__ai-title">Gợi ý AI sàng lọc</div>
                                                <button
                                                    x-show="! confirmAiAnalysis"
                                                    type="button"
                                                    class="kanban-modal__ai-action"
                                                    x-on:click="confirmAiAnalysis = true"
                                                    wire:loading.attr="disabled"
                                                    wire:target="analyzeScreeningAiFromKanban"
                                                >
                                                    <span wire:loading.remove wire:target="analyzeScreeningAiFromKanban">
                                                        {{ ($ai['status'] ?? null) === 'completed' ? 'Phân tích lại' : 'Phân tích CV' }}
                                                    </span>
                                                    <span wire:loading wire:target="analyzeScreeningAiFromKanban">
                                                        Đang phân tích...
                                                    </span>
                                                </button>
                                                <div class="kanban-modal__ai-confirm" x-cloak x-show="confirmAiAnalysis" x-transition.opacity.duration.120ms>
                                                    <span>{{ ($ai['status'] ?? null) === 'completed' ? 'Tạo kết quả mới?' : 'Bắt đầu phân tích?' }}</span>
                                                    <button
                                                        type="button"
                                                        class="kanban-modal__ai-confirm-button is-primary"
                                                        x-on:click="confirmAiAnalysis = false; $wire.analyzeScreeningAiFromKanban()"
                                                        wire:loading.attr="disabled"
                                                        wire:target="analyzeScreeningAiFromKanban"
                                                    >
                                                        Tiếp tục
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="kanban-modal__ai-confirm-button"
                                                        x-on:click="confirmAiAnalysis = false"
                                                        wire:loading.attr="disabled"
                                                        wire:target="analyzeScreeningAiFromKanban"
                                                    >
                                                        Hủy
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="kanban-modal__ai-badges">
                                                @if (isset($ai['score']))
                                                    <span class="kanban-modal__ai-badge is-score-{{ $ai['score_tone'] ?? 'neutral' }}">{{ $ai['score'] }}/100</span>
                                                @endif
                                                <span class="kanban-modal__ai-badge is-score-{{ ($ai['status'] ?? null) === 'completed' ? ($ai['score_tone'] ?? 'neutral') : 'neutral' }}">{{ $ai['label'] ?? 'Chưa có khuyến nghị' }}</span>
                                            </div>
                                        </div>
                                        <p class="kanban-modal__ai-text">
                                            <span wire:loading.remove wire:target="analyzeScreeningAiFromKanban">
                                                {{ $ai['summary'] ?? 'Chưa có phân tích AI cho hồ sơ này.' }}
                                            </span>
                                            <span wire:loading wire:target="analyzeScreeningAiFromKanban">
                                                Đang trích xuất CV và so khớp với vị trí ứng tuyển.
                                            </span>
                                        </p>

                                        @if (($ai['status'] ?? null) === 'completed' && (! empty($ai['strengths']) || ! empty($ai['gaps'])))
                                            <div class="kanban-modal__ai-grid">
                                                <div class="kanban-modal__ai-column">
                                                    <div class="kanban-modal__ai-section-title">Điểm phù hợp</div>
                                                    <ul class="kanban-modal__ai-list">
                                                        @forelse (array_slice(($ai['strengths'] ?? []), 0, 2) as $item)
                                                            <li>{{ $item }}</li>
                                                        @empty
                                                            <li>Chưa có điểm phù hợp nổi bật.</li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                                <div class="kanban-modal__ai-column">
                                                    <div class="kanban-modal__ai-section-title">Cần làm rõ</div>
                                                    <ul class="kanban-modal__ai-list">
                                                        @forelse (array_slice(($ai['gaps'] ?? []), 0, 2) as $item)
                                                            <li>{{ $item }}</li>
                                                        @empty
                                                            <li>Chưa có điểm cần làm rõ.</li>
                                                        @endforelse
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif

                                        @if (($ai['status'] ?? null) === 'completed' && filled($ai['suggested_note'] ?? null))
                                            <details
                                                class="kanban-modal__ai-more"
                                                x-bind:open="aiNoteOpen"
                                                x-on:toggle="aiNoteOpen = $el.open"
                                            >
                                                <summary>Xem gợi ý ghi chú</summary>
                                                <p class="kanban-modal__ai-text" style="margin-top: 7px;">
                                                    {{ $ai['suggested_note'] }}
                                                </p>
                                            </details>
                                        @endif
                                    </div>

                                    <label class="kanban-modal__field">
                                        <span class="kanban-modal__label">Kết quả sàng lọc</span>
                                        <select class="kanban-modal__select" x-model="screeningDecision">
                                            <option value="">Chọn kết quả</option>
                                            <option value="pass">Đạt sơ tuyển</option>
                                            <option value="reject">Từ chối hồ sơ</option>
                                        </select>
                                        @error('kanbanScreeningDecision')
                                            <span class="kanban-modal__field-error">{{ $message }}</span>
                                        @enderror
                                    </label>

                                    <template x-if="screeningDecision === 'reject'">
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Lý do từ chối</span>
                                            <textarea
                                                class="kanban-modal__textarea"
                                                wire:model.defer="kanbanScreeningRejectedReason"
                                                placeholder="Nhập lý do từ chối để lưu căn cứ xử lý..."
                                            ></textarea>
                                            @error('kanbanScreeningRejectedReason')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </label>
                                    </template>

                                    <template x-if="screeningDecision === 'pass'">
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Ghi chú sàng lọc</span>
                                            <textarea
                                                class="kanban-modal__textarea"
                                                wire:model.defer="kanbanScreeningNote"
                                                placeholder="Ghi rõ căn cứ đạt sơ tuyển, ví dụ kinh nghiệm/kỹ năng phù hợp hoặc điểm cần xác minh ở vòng sau..."
                                            ></textarea>
                                            @error('kanbanScreeningNote')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </label>
                                    </template>

                                    <div class="kanban-modal__actions">
                                        <button type="button" class="kanban-modal__button" x-on:click="closeModal()">
                                            Hủy
                                        </button>

                                        <button
                                            type="submit"
                                            class="kanban-modal__button"
                                            wire:loading.attr="disabled"
                                            wire:target="screenApplicationFromKanban"
                                            x-bind:class="{ 'is-danger': screeningDecision === 'reject', 'is-primary': screeningDecision !== 'reject' }"
                                        >
                                            <span wire:loading.remove wire:target="screenApplicationFromKanban" x-text="screeningDecision === 'reject' ? 'Xác nhận từ chối' : 'Xác nhận sàng lọc'"></span>
                                            <span wire:loading wire:target="screenApplicationFromKanban">Đang xử lý...</span>
                                        </button>
                                    </div>
                                </div>

                                <aside class="kanban-screening-cv">
                                    <div class="kanban-screening-cv__header">
                                        <div class="kanban-screening-cv__title">
                                            <span class="kanban-screening-cv__eyebrow">CV ứng tuyển</span>
                                            <span class="kanban-screening-cv__name">{{ $screening['cv_name'] ?? 'CV ứng tuyển' }}</span>
                                        </div>

                                        @if (filled($screening['cv_url'] ?? null))
                                            <a href="{{ $screening['cv_url'] }}" target="_blank" rel="noopener" class="kanban-modal__button is-primary">
                                                Mở CV
                                            </a>
                                        @endif
                                    </div>

                                    @if (filled($screening['cv_url'] ?? null))
                                        <iframe
                                            src="{{ $screening['cv_url'] }}#toolbar=1&navpanes=0"
                                            class="kanban-screening-cv__frame"
                                            title="CV ứng tuyển"
                                        ></iframe>
                                    @else
                                        <div class="kanban-screening-cv__empty">
                                            Hồ sơ này chưa có CV đính kèm để xem trước.
                                        </div>
                                    @endif
                                </aside>
                            </div>
                        </form>
                    @elseif (($kanbanDropAction['type'] ?? null) === 'pre_screening')
                        @php
                            $preScreening = $kanbanDropAction['pre_screening_context'] ?? [];
                        @endphp
                        <form class="kanban-modal__body" wire:submit.prevent="savePreScreeningFromKanban" x-data="{ outcome: @entangle('kanbanPreScreeningForm.outcome'), contactMethod: @entangle('kanbanPreScreeningForm.contact_channel'), reasonCode: @entangle('kanbanPreScreeningForm.rejection_reason_code') }">
                            <div class="kanban-pre-screening-layout">
                                <aside class="kanban-pre-screening-context">
                                    <div class="kanban-modal__facts">
                                        <div class="kanban-modal__fact"><span class="kanban-modal__fact-label">Email</span><span class="kanban-modal__fact-value">{{ $preScreening['email'] ?? '-' }}</span></div>
                                        <div class="kanban-modal__fact"><span class="kanban-modal__fact-label">Số điện thoại</span><span class="kanban-modal__fact-value">{{ $preScreening['phone'] ?? '-' }}</span></div>
                                    </div>
                                    <div class="kanban-modal__notice"><strong>Kết quả sàng lọc CV</strong><br>{{ $preScreening['screening_note'] ?? 'Chưa có ghi chú sàng lọc.' }}</div>
                                    @if (filled($preScreening['latest_contacted_at'] ?? null))
                                        <div class="kanban-modal__notice">
                                            <strong>Lần liên hệ gần nhất</strong><br>
                                            {{ $preScreening['latest_contact_channel'] ?? 'Đã liên hệ' }} lúc {{ $preScreening['latest_contacted_at'] }}.
                                            @if (filled($preScreening['latest_note'] ?? null))
                                                <br>{{ $preScreening['latest_note'] }}
                                            @endif
                                        </div>
                                    @endif
                                    @if (filled($preScreening['latest_follow_up_at'] ?? null))
                                        <div class="kanban-modal__notice is-warning"><strong>Lịch liên hệ tiếp theo</strong><br>{{ $preScreening['latest_follow_up_at'] }}.</div>
                                    @endif
                                    @if (count($preScreening['history'] ?? []) > 1)
                                        <details class="kanban-modal__ai-more">
                                            <summary>Lịch sử liên hệ ({{ count($preScreening['history']) }})</summary>
                                            <div class="kanban-modal__history">
                                                @foreach (array_slice($preScreening['history'], 1) as $item)
                                                    <div class="kanban-modal__history-item">
                                                        <strong>{{ $item['outcome'] }}</strong>
                                                        <span>{{ $item['method'] }} · {{ $item['contacted_at'] }}</span>
                                                        @if (filled($item['note'] ?? null))<span>{{ $item['note'] }}</span>@endif
                                                        @if (filled($item['handled_by'] ?? null))<span>{{ $item['handled_by'] }}</span>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                    @if (filled($preScreening['cv_url'] ?? null))
                                        <a href="{{ $preScreening['cv_url'] }}" target="_blank" rel="noopener" class="kanban-modal__button">Xem CV ứng tuyển</a>
                                    @endif
                                </aside>
                                <div class="kanban-pre-screening-form">
                                    <label class="kanban-modal__field" x-show="outcome === 'rejected'" x-cloak>
                                        <span class="kanban-modal__label">Phân loại lý do</span>
                                        <select class="kanban-modal__select" wire:model="kanbanPreScreeningForm.rejection_reason_code">
                                            <option value="">Chọn lý do</option>
                                            <option value="unreachable">Không liên hệ được</option>
                                            <option value="candidate_withdrew">Ứng viên không còn quan tâm</option>
                                            <option value="availability_mismatch">Không phù hợp thời gian hoặc địa điểm</option>
                                            <option value="expectation_mismatch">Kỳ vọng chưa phù hợp</option>
                                            <option value="not_qualified">Thông tin chưa đáp ứng yêu cầu</option>
                                            <option value="other">Lý do khác</option>
                                        </select>
                                        @error('kanbanPreScreeningForm.rejection_reason_code')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror
                                    </label>
                                    <div class="kanban-modal__grid">
                                        <label class="kanban-modal__field"><span class="kanban-modal__label">Hình thức liên hệ</span><select class="kanban-modal__select" wire:model.live="kanbanPreScreeningForm.contact_channel"><option value="">Chọn hình thức</option><option value="phone">Điện thoại</option><option value="email">Email</option><option value="zalo">Zalo</option><option value="in_person">Trực tiếp</option><option value="other">Khác</option></select>@error('kanbanPreScreeningForm.contact_channel')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror</label>
                                        <label class="kanban-modal__field"><span class="kanban-modal__label">Thời điểm liên hệ</span><input type="datetime-local" max="{{ now(config('app.interview_timezone', 'Asia/Ho_Chi_Minh'))->format('Y-m-d\\TH:i') }}" class="kanban-modal__input" wire:model="kanbanPreScreeningForm.contacted_at">@error('kanbanPreScreeningForm.contacted_at')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror</label>
                                    </div>
                                    <template x-if="contactMethod === 'other'"><label class="kanban-modal__field"><span class="kanban-modal__label">Hình thức liên hệ khác</span><input type="text" maxlength="120" class="kanban-modal__input" wire:model.defer="kanbanPreScreeningForm.contact_channel_detail" placeholder="Ví dụ: LinkedIn, ngày hội tuyển dụng">@error('kanbanPreScreeningForm.contact_channel_detail')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror</label></template>
                                    <label class="kanban-modal__field"><span class="kanban-modal__label">Kết quả sơ tuyển</span><select class="kanban-modal__select" x-model="outcome"><option value="">Chọn kết quả</option><option value="passed">Đạt sơ tuyển</option><option value="follow_up">Hẹn liên hệ lại</option><option value="rejected">Từ chối hồ sơ</option></select>@error('kanbanPreScreeningForm.outcome')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror</label>
                                    <template x-if="outcome === 'follow_up'"><label class="kanban-modal__field"><span class="kanban-modal__label">Hẹn liên hệ lại</span><input type="datetime-local" class="kanban-modal__input" wire:model="kanbanPreScreeningForm.follow_up_at">@error('kanbanPreScreeningForm.follow_up_at')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror</label></template>
                                    <template x-if="outcome === 'passed' || outcome === 'follow_up'"><label class="kanban-modal__field"><span class="kanban-modal__label" x-text="outcome === 'passed' ? 'Ghi chú xác nhận' : 'Nội dung cần theo dõi'"></span><textarea class="kanban-modal__textarea" wire:model.defer="kanbanPreScreeningForm.note" placeholder="Tóm tắt mức độ quan tâm, điều kiện làm việc hoặc nội dung cần lưu ý."></textarea>@error('kanbanPreScreeningForm.note')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror</label></template>
                                    <template x-if="outcome === 'rejected'"><label class="kanban-modal__field"><span class="kanban-modal__label">Lý do từ chối</span><textarea class="kanban-modal__textarea" wire:model.defer="kanbanPreScreeningForm.rejection_reason" placeholder="Nêu lý do phù hợp để lưu lịch sử và phản hồi ứng viên."></textarea>@error('kanbanPreScreeningForm.rejection_reason')<span class="kanban-modal__field-error">{{ $message }}</span>@enderror</label></template>
                                    <div class="kanban-modal__actions"><button type="button" class="kanban-modal__button" x-on:click="closeModal()">Hủy</button><button type="submit" class="kanban-modal__button" x-bind:class="{ 'is-danger': outcome === 'rejected', 'is-primary': outcome !== 'rejected' }" wire:loading.attr="disabled" wire:target="savePreScreeningFromKanban"><span wire:loading.remove wire:target="savePreScreeningFromKanban" x-text="outcome === 'rejected' ? 'Xác nhận từ chối' : 'Xác nhận sơ tuyển'"></span><span wire:loading wire:target="savePreScreeningFromKanban">Đang lưu...</span></button></div>
                                </div>
                            </div>
                        </form>
                    @elseif (($kanbanDropAction['type'] ?? null) === 'interview_evaluation')
                        @php
                            $evaluationInterview = $kanbanDropAction['interview'] ?? [];
                            $isPanelFinalization = (bool) ($kanbanDropAction['finalization_mode'] ?? false);
                            $canEditEvaluation = (bool) ($kanbanDropAction['can_edit_evaluation'] ?? true);
                            $evaluationAverage = $isPanelFinalization
                                ? ($kanbanDropAction['panel_average'] ?? null)
                                : app(\App\Services\InterviewEvaluationService::class)->calculateAverage($kanbanEvaluationForm['criteria'] ?? []);
                            $evaluationRecommendation = $isPanelFinalization
                                ? ($kanbanDropAction['panel_recommendation'] ?? null)
                                : app(\App\Services\InterviewEvaluationService::class)->recommendedConclusion($evaluationAverage);
                            $isEvaluationOverride = filled($kanbanEvaluationForm['conclusion'] ?? null)
                                && filled($evaluationRecommendation)
                                && ($kanbanEvaluationForm['conclusion'] ?? null) !== $evaluationRecommendation;
                        @endphp
                        @php
                            $canFinalizeEvaluation = (bool) ($kanbanDropAction['can_finalize'] ?? false);
                            $hasEvaluationTemplate = filled($kanbanEvaluationForm['template_id'] ?? null);
                            $hasCompleteEvaluationScores = $hasEvaluationTemplate
                                && count($kanbanEvaluationForm['criteria'] ?? []) > 0
                                && collect($kanbanEvaluationForm['criteria'] ?? [])->every(
                                    fn ($criterion) => is_array($criterion) && filled($criterion['score'] ?? null)
                                );
                            $canChooseEvaluationConclusion = $isPanelFinalization || $hasCompleteEvaluationScores;
                            $requiresCandidateRejectionReason = $isPanelFinalization
                                || (($kanbanDropAction['single_evaluator'] ?? true) === true);
                            $requiresEvaluationNotes = filled($kanbanEvaluationForm['conclusion'] ?? null);
                            $hasRequiredEvaluationNotes = ! $requiresEvaluationNotes
                                || filled($kanbanEvaluationForm['notes'] ?? null);
                            $hasRequiredOverrideReason = ! $isEvaluationOverride
                                || filled($kanbanEvaluationForm['override_reason'] ?? null);
                            $hasRequiredRejectionReason = ($kanbanEvaluationForm['conclusion'] ?? '') !== 'fail'
                                || ! $requiresCandidateRejectionReason
                                || filled($kanbanEvaluationForm['rejected_reason'] ?? null);
                            $canConfirmEvaluation = $canChooseEvaluationConclusion
                                && filled($kanbanEvaluationForm['conclusion'] ?? null)
                                && $hasRequiredEvaluationNotes
                                && $hasRequiredOverrideReason
                                && $hasRequiredRejectionReason;
                            $canCompleteEvaluation = ($canEditEvaluation || $isPanelFinalization)
                                && $canConfirmEvaluation
                                && (bool) ($kanbanEvaluationForm['confirm_completion'] ?? false);
                            $isFinalInterviewRound = (bool) ($kanbanDropAction['is_final_round'] ?? true);
                            $missingEvaluationScores = max(
                                0,
                                count($kanbanEvaluationForm['criteria'] ?? [])
                                    - collect($kanbanEvaluationForm['criteria'] ?? [])->filter(
                                        fn ($criterion) => is_array($criterion) && filled($criterion['score'] ?? null)
                                    )->count()
                            );
                            $evaluationCompletionHint = ! $hasEvaluationTemplate
                                ? 'Chưa có mẫu đánh giá.'
                                : ($missingEvaluationScores > 0
                                    ? 'Còn '.$missingEvaluationScores.' tiêu chí chưa chấm.'
                                    : (blank($kanbanEvaluationForm['conclusion'] ?? null)
                                        ? 'Chưa chọn kết luận.'
                                        : (! $hasRequiredEvaluationNotes
                                            ? 'Cần nhập nhận xét nội bộ.'
                                            : (! $hasRequiredOverrideReason
                                                ? 'Cần nhập lý do kết luận khác khuyến nghị.'
                                                : (! $hasRequiredRejectionReason
                                                    ? 'Cần nhập nội dung phản hồi ứng viên.'
                                                    : (! (bool) ($kanbanEvaluationForm['confirm_completion'] ?? false)
                                                        ? 'Cần xác nhận đã hoàn tất trước khi gửi.'
                                                        : null))))));
                        @endphp
                        <form class="kanban-modal__body kanban-evaluation" wire:submit.prevent="saveInterviewEvaluationDraftFromKanban">
                            <div class="kanban-evaluation__brief">
                                <span>{{ $evaluationInterview['round_name'] ?? 'Vòng phỏng vấn' }}</span>
                                <span>{{ $evaluationInterview['scheduled_at'] ?? '-' }}</span>
                                <span>Phụ trách: {{ $evaluationInterview['interviewer'] ?? '-' }}</span>
                                <span>{{ $evaluationInterview['type'] ?? '-' }}</span>
                            </div>

                            @php
                                $evaluationProgress = $kanbanDropAction['evaluation_progress'] ?? [];
                            @endphp
                            @if (($evaluationProgress['is_panel'] ?? false) === true)
                                <div class="kanban-evaluation__summary">
                                    <span>Tiến độ hội đồng</span>
                                    <strong>{{ $evaluationProgress['submitted'] ?? 0 }}/{{ $evaluationProgress['required'] ?? 0 }} phiếu</strong>
                                    <span>
                                        {{ ($evaluationProgress['all_submitted'] ?? false) ? 'Đã đủ phiếu' : 'Còn '.($evaluationProgress['pending'] ?? 0).' phiếu' }}
                                        @if (($evaluationProgress['waived'] ?? 0) > 0)
                                            · {{ $evaluationProgress['waived'] }} không yêu cầu
                                        @endif
                                    </span>
                                </div>
                                <details class="kanban-evaluation__questions" @if (! $isPanelFinalization) open @endif>
                                    <summary>
                                        <span>Phân công đánh giá</span>
                                        <span class="kanban-evaluation__questions-count">{{ $evaluationProgress['assigned'] ?? 0 }} người</span>
                                    </summary>
                                    <ul class="kanban-evaluation__assignments">
                                        @foreach (($kanbanDropAction['evaluation_assignments'] ?? []) as $assignment)
                                            <li class="kanban-evaluation__assignment" wire:key="evaluation-assignment-{{ $assignment['user_id'] ?? $loop->index }}">
                                                <div class="kanban-evaluation__assignment-row">
                                                    <div class="kanban-evaluation__assignment-person">
                                                        <strong>{{ $assignment['name'] ?? 'Người đánh giá' }}</strong>
                                                        <span>{{ $assignment['role'] ?? 'Thành viên' }}</span>
                                                    </div>
                                                    <div class="kanban-evaluation__assignment-actions">
                                                        <span @class([
                                                            'kanban-evaluation__assignment-status',
                                                            'is-submitted' => ($assignment['submitted'] ?? false),
                                                            'is-waived' => ($assignment['waived'] ?? false),
                                                        ])>
                                                            @if ($assignment['submitted'] ?? false)
                                                                Đã gửi phiếu
                                                            @elseif ($assignment['waived'] ?? false)
                                                                Không yêu cầu
                                                            @else
                                                                Chưa gửi
                                                            @endif
                                                        </span>
                                                        @if ($assignment['can_waive'] ?? false)
                                                            <button
                                                                type="button"
                                                                class="kanban-evaluation__assignment-button"
                                                                wire:click="requestInterviewEvaluatorWaiverFromKanban({{ (int) $assignment['user_id'] }})"
                                                            >Bỏ yêu cầu phiếu</button>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if ($assignment['waived'] ?? false)
                                                    <p class="kanban-evaluation__assignment-note">
                                                        {{ $assignment['waiver_reason'] ?? 'Đã cập nhật yêu cầu gửi phiếu.' }}
                                                        @if (filled($assignment['waived_at'] ?? null))
                                                            · {{ $assignment['waived_at'] }}
                                                        @endif
                                                    </p>
                                                @endif

                                                @if ((int) $kanbanEvaluationWaiverUserId === (int) ($assignment['user_id'] ?? 0))
                                                    <div class="kanban-evaluation__waiver-editor">
                                                        <input
                                                            type="text"
                                                            maxlength="500"
                                                            class="kanban-modal__input"
                                                            wire:model.defer="kanbanEvaluationWaiverReason"
                                                            placeholder="Lý do, ví dụ: Không tham gia buổi phỏng vấn"
                                                        >
                                                        <div class="kanban-evaluation__waiver-actions">
                                                            <button type="button" class="kanban-card__button" wire:click="cancelInterviewEvaluatorWaiverFromKanban">Hủy</button>
                                                            <button type="button" class="kanban-card__button is-primary" wire:click="waiveInterviewEvaluatorFromKanban" wire:loading.attr="disabled" wire:target="waiveInterviewEvaluatorFromKanban">
                                                                <span wire:loading.remove wire:target="waiveInterviewEvaluatorFromKanban">Xác nhận</span>
                                                                <span wire:loading wire:target="waiveInterviewEvaluatorFromKanban">Đang lưu...</span>
                                                            </button>
                                                        </div>
                                                        @error('kanbanEvaluationWaiverReason')
                                                            <span class="kanban-modal__field-error">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif

                            @if ($isPanelFinalization && count($kanbanDropAction['panel_submissions'] ?? []) > 0)
                                <section class="kanban-evaluation__panel-review">
                                    <div class="kanban-evaluation__panel-head">
                                        <span>Đối chiếu phiếu đánh giá</span>
                                        <small>{{ count($kanbanDropAction['panel_submissions']) }} phiếu đã gửi</small>
                                    </div>
                                    @foreach ($kanbanDropAction['panel_submissions'] as $submission)
                                        <details class="kanban-evaluation__submission" wire:key="panel-submission-{{ $loop->index }}">
                                            <summary class="kanban-evaluation__submission-summary">
                                                <span class="kanban-evaluation__submission-person">
                                                    <strong>{{ $submission['name'] ?? 'Người đánh giá' }}</strong>
                                                    <span>{{ $submission['role'] ?? 'Thành viên' }} · {{ $submission['submitted_at'] ?? '-' }}</span>
                                                </span>
                                                <span class="kanban-evaluation__submission-result">
                                                    {{ isset($submission['average']) ? number_format((float) $submission['average'], 2, ',', '.').'/10' : '-' }}
                                                    · {{ $submission['conclusion'] ?? 'Chưa có đề xuất' }}
                                                </span>
                                            </summary>
                                            <div class="kanban-evaluation__submission-body">
                                                <div class="kanban-evaluation__submission-facts">
                                                    <span class="kanban-evaluation__submission-fact">Đề xuất<strong>{{ $submission['conclusion'] ?? '-' }}</strong></span>
                                                    <span class="kanban-evaluation__submission-fact">Khuyến nghị theo điểm<strong>{{ $submission['recommendation'] ?? '-' }}</strong></span>
                                                </div>
                                                @if (filled($submission['notes'] ?? null))
                                                    <p class="kanban-evaluation__submission-notes"><strong>Nhận xét:</strong> {{ $submission['notes'] }}</p>
                                                @endif
                                                @if (filled($submission['override_reason'] ?? null))
                                                    <p class="kanban-evaluation__submission-notes"><strong>Lý do khác khuyến nghị:</strong> {{ $submission['override_reason'] }}</p>
                                                @endif
                                                <ul class="kanban-evaluation__submission-criteria">
                                                    @foreach (($submission['criteria'] ?? []) as $criterion)
                                                        <li class="kanban-evaluation__submission-criterion">
                                                            <span>{{ $criterion['name'] ?? 'Tiêu chí' }}</span>
                                                            <strong>{{ filled($criterion['score'] ?? null) ? $criterion['score'].'/10' : '-' }}</strong>
                                                            @if (filled($criterion['note'] ?? null))
                                                                <small>{{ $criterion['note'] }}</small>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </details>
                                    @endforeach
                                </section>
                            @endif

                            @if (($kanbanDropAction['template_locked'] ?? false) === true)
                                <div class="kanban-evaluation__brief">
                                    <span>Mẫu đánh giá</span>
                                    <strong>{{ $kanbanDropAction['template_name'] ?? 'Mẫu đã gắn với lịch phỏng vấn' }}</strong>
                                </div>
                            @else
                                <label class="kanban-modal__field">
                                    <span class="kanban-modal__label">Mẫu đánh giá</span>
                                    <select class="kanban-modal__select" wire:model.live="kanbanEvaluationForm.template_id">
                                        <option value="">Chọn mẫu đánh giá</option>
                                        @foreach (($kanbanDropAction['template_options'] ?? []) as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <span class="kanban-modal__field-help">Lịch cũ chưa có mẫu đánh giá. Chọn mẫu để tiếp tục chấm.</span>
                                    @error('kanbanEvaluationForm.template_id')
                                        <span class="kanban-modal__field-error">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endif

                            @php
                                $interviewQuestions = $kanbanDropAction['interview_questions'] ?? [];
                                $hasScreeningAnalysis = (bool) ($kanbanDropAction['has_screening_analysis'] ?? false);
                                $questionsUseScreening = ($interviewQuestions['basis'] ?? null) === 'screening'
                                    || (($interviewQuestions['basis'] ?? null) === null && $hasScreeningAnalysis);
                            @endphp
                            @if (! $isPanelFinalization && ($interviewQuestions['available'] ?? false) === true)
                                <details class="kanban-evaluation__questions">
                                    <summary>
                                        <span>Câu hỏi tham khảo</span>
                                        <span class="kanban-evaluation__questions-count">{{ count($interviewQuestions['questions'] ?? []) }} câu hỏi</span>
                                    </summary>
                                    <p class="kanban-evaluation__questions-help">
                                        {{ $questionsUseScreening
                                            ? 'Được AI gợi ý từ kết quả đối chiếu CV và tiêu chí đánh giá.'
                                            : 'Được AI gợi ý từ vị trí tuyển dụng và tiêu chí đánh giá.' }}
                                    </p>
                                    <ol class="kanban-evaluation__questions-list">
                                        @foreach (($interviewQuestions['questions'] ?? []) as $question)
                                            <li class="kanban-evaluation__question">
                                                {{ $question['question'] ?? '' }}
                                                @if (filled($question['criterion'] ?? null) || filled($question['purpose'] ?? null))
                                                    <span class="kanban-evaluation__question-meta">
                                                        {{ collect([$question['criterion'] ?? null, $question['purpose'] ?? null])->filter()->implode(' · ') }}
                                                    </span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </details>
                            @elseif (! $isPanelFinalization && $hasEvaluationTemplate)
                                <div class="kanban-evaluation__questions">
                                    @if ($kanbanInterviewQuestionsConfirmation)
                                        <div class="kanban-evaluation__question-actions">
                                            <p>
                                                {{ $hasScreeningAnalysis
                                                    ? 'AI sẽ dùng kết quả đối chiếu CV và tiêu chí scorecard để tạo câu hỏi.'
                                                    : 'Chưa có phân tích sàng lọc AI. Câu hỏi sẽ dựa trên vị trí và tiêu chí scorecard.' }}
                                            </p>
                                            <div class="kanban-card__buttons">
                                                <button type="button" class="kanban-card__button" wire:click="cancelInterviewQuestionsFromKanban">Hủy</button>
                                                <button type="button" class="kanban-card__button is-primary" wire:click="generateInterviewQuestionsFromKanban" wire:loading.attr="disabled" wire:target="generateInterviewQuestionsFromKanban">
                                                    <span wire:loading.remove wire:target="generateInterviewQuestionsFromKanban">Tạo câu hỏi</span>
                                                    <span wire:loading wire:target="generateInterviewQuestionsFromKanban">Đang tạo...</span>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="kanban-evaluation__question-actions">
                                            <p>Chuẩn bị câu hỏi trước khi chấm khi cần làm rõ hồ sơ hoặc tiêu chí.</p>
                                            <button type="button" class="kanban-card__button" wire:click="requestInterviewQuestionsFromKanban">Tạo câu hỏi tham khảo</button>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if (! $hasEvaluationTemplate)
                                <div class="kanban-evaluation__empty">
                                    Chọn mẫu scorecard để hiển thị tiêu chí đánh giá.
                                </div>
                            @elseif (! $isPanelFinalization)
                            <div class="kanban-evaluation__criteria">
                                <div class="kanban-evaluation__section-head">
                                    <span>Tiêu chí đánh giá</span>
                                </div>

                                @error('kanbanEvaluationForm.criteria')
                                    <span class="kanban-modal__field-error">{{ $message }}</span>
                                @enderror

                                @foreach ($kanbanEvaluationForm['criteria'] as $index => $criterion)
                                    <div class="kanban-evaluation__criterion" wire:key="kanban-criterion-{{ $index }}">
                                        <span class="kanban-evaluation__criterion-name">{{ $criterion['name'] ?? 'Tiêu chí '.($index + 1) }}</span>
                                        <select class="kanban-modal__select" wire:model.live="kanbanEvaluationForm.criteria.{{ $index }}.score" @disabled(! $canEditEvaluation)>
                                            <option value="">Điểm</option>
                                            @for ($score = 0; $score <= 10; $score++)
                                                <option value="{{ $score }}">{{ $score }}/10</option>
                                            @endfor
                                        </select>
                                        <details class="kanban-evaluation__criterion-note" @if (filled($criterion['note'] ?? null)) open @endif>
                                            <summary>Thêm nhận xét tiêu chí</summary>
                                            <textarea
                                                class="kanban-modal__textarea"
                                                wire:model.defer="kanbanEvaluationForm.criteria.{{ $index }}.note"
                                                placeholder="Nhận xét ngắn cho tiêu chí này"
                                                @disabled(! $canEditEvaluation)
                                            ></textarea>
                                        </details>
                                    </div>
                                @endforeach
                            </div>
                            @endif

                            @if ($hasEvaluationTemplate)
                                <div class="kanban-evaluation__summary">
                                    @if (! $isPanelFinalization)
                                        <span>Đã chấm {{ collect($kanbanEvaluationForm['criteria'] ?? [])->filter(fn ($criterion) => is_array($criterion) && filled($criterion['score'] ?? null))->count() }}/{{ count($kanbanEvaluationForm['criteria'] ?? []) }}</span>
                                    @else
                                        <span>Kết quả tổng hợp</span>
                                    @endif
                                    <span>{{ $isPanelFinalization ? 'Điểm hội đồng' : (($kanbanDropAction['single_evaluator'] ?? true) ? 'Điểm trung bình' : 'Điểm phiếu của bạn') }}</span>
                                    <strong>{{ $evaluationAverage !== null ? number_format($evaluationAverage, 2, ',', '.').'/10' : 'Chưa đủ điểm' }}</strong>
                                    @if ($hasCompleteEvaluationScores)
                                        <span>{{ $isPanelFinalization ? 'Khuyến nghị tổng hợp' : (($kanbanDropAction['single_evaluator'] ?? true) ? 'Khuyến nghị' : 'Khuyến nghị theo phiếu') }}</span>
                                        <strong>{{ app(\App\Services\InterviewEvaluationService::class)->conclusionLabel($evaluationRecommendation) }}</strong>
                                    @endif
                                </div>
                            @endif

                            @if ($canChooseEvaluationConclusion)
                            <label class="kanban-modal__field">
                                <span class="kanban-modal__label">
                                    {{ $isPanelFinalization ? 'Kết luận chung của vòng' : (($kanbanDropAction['single_evaluator'] ?? true) ? 'Kết luận phỏng vấn' : 'Đề xuất của bạn') }}
                                </span>
                                <select class="kanban-modal__select" wire:model.live="kanbanEvaluationForm.conclusion" @disabled(! $canEditEvaluation && ! $isPanelFinalization)>
                                    <option value="">Chọn kết luận</option>
                                    <option value="pass">
                                        @if ($isPanelFinalization || ($kanbanDropAction['single_evaluator'] ?? true))
                                            {{ $isFinalInterviewRound ? 'Đạt - chuyển sang đề nghị tuyển dụng' : 'Đạt - chuyển sang '.($kanbanDropAction['next_round_name'] ?? 'vòng tiếp theo') }}
                                        @else
                                            Đạt
                                        @endif
                                    </option>
                                    <option value="hold">{{ ($isPanelFinalization || ($kanbanDropAction['single_evaluator'] ?? true)) ? 'Cân nhắc thêm - giữ ở Phỏng vấn' : 'Cần cân nhắc' }}</option>
                                    <option value="fail">{{ ($isPanelFinalization || ($kanbanDropAction['single_evaluator'] ?? true)) ? 'Không đạt - chuyển sang Từ chối' : 'Không đạt' }}</option>
                                </select>
                                @error('kanbanEvaluationForm.conclusion')
                                    <span class="kanban-modal__field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            @if ($isEvaluationOverride)
                                <label class="kanban-modal__field">
                                    <span class="kanban-modal__label">Lý do kết luận khác khuyến nghị</span>
                                    <textarea class="kanban-modal__textarea" wire:model.defer="kanbanEvaluationForm.override_reason"></textarea>
                                    @error('kanbanEvaluationForm.override_reason')
                                        <span class="kanban-modal__field-error">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endif

                            @if (($kanbanEvaluationForm['conclusion'] ?? '') === 'fail' && $requiresCandidateRejectionReason)
                                <label class="kanban-modal__field">
                                    <span class="kanban-modal__label">Thông tin phản hồi ứng viên khi từ chối</span>
                                    <textarea class="kanban-modal__textarea" wire:model.defer="kanbanEvaluationForm.rejected_reason" placeholder="Nêu ngắn gọn, lịch sự và phù hợp để phản hồi ứng viên."></textarea>
                                    @error('kanbanEvaluationForm.rejected_reason')
                                        <span class="kanban-modal__field-error">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endif

                            <label class="kanban-modal__field">
                                <span class="kanban-modal__label">{{ $isPanelFinalization ? 'Nhận xét chung của vòng' : 'Nhận xét nội bộ sau phỏng vấn' }}</span>
                                <textarea class="kanban-modal__textarea" wire:model.live.debounce.500ms="kanbanEvaluationForm.notes" placeholder="Tóm tắt điểm mạnh và điểm cần cân nhắc để nội bộ tham khảo." @disabled(! $canEditEvaluation && ! $isPanelFinalization)></textarea>
                                <span class="kanban-modal__field-help">
                                    {{ ($kanbanEvaluationForm['conclusion'] ?? '') === 'hold'
                                        ? 'Bắt buộc để làm rõ nội dung cần xem xét.'
                                        : ($isPanelFinalization
                                            ? 'Bắt buộc để lưu căn cứ chốt kết quả vòng.'
                                            : 'Bắt buộc để lưu căn cứ cho phiếu đánh giá.') }}
                                </span>
                                @error('kanbanEvaluationForm.notes')
                                    <span class="kanban-modal__field-error">{{ $message }}</span>
                                @enderror
                            </label>

                            @if (($canEditEvaluation || $isPanelFinalization) && $canConfirmEvaluation)
                                <label class="kanban-evaluation__early-completion">
                                    <input type="checkbox" wire:model.live="kanbanEvaluationForm.confirm_completion">
                                    <span>
                                        @if ($isPanelFinalization)
                                            Xác nhận chốt kết quả vòng
                                        @elseif (($kanbanDropAction['single_evaluator'] ?? true) === false)
                                            Tôi đã hoàn tất phiếu đánh giá
                                        @elseif ($canFinalizeEvaluation)
                                            Xác nhận đã kết thúc phỏng vấn
                                        @else
                                            Xác nhận kết thúc sớm
                                        @endif
                                    </span>
                                </label>
                                @error('kanbanEvaluationForm.confirm_completion')
                                    <span class="kanban-modal__field-error">{{ $message }}</span>
                                @enderror
                            @endif
                            @endif

                            @if ($kanbanEvaluationDraftStatus)
                                <p class="kanban-evaluation__save-status" role="status" aria-live="polite">
                                    {{ $kanbanEvaluationDraftStatus }} lúc {{ $kanbanEvaluationDraftSavedAt }}.
                                </p>
                            @endif

                            <div class="kanban-modal__actions">
                                <button type="button" class="kanban-modal__button" x-on:click="closeModal()">Hủy</button>
                                @if ($canEditEvaluation && ! $isPanelFinalization)
                                    <button type="submit" class="kanban-modal__button" wire:loading.attr="disabled" wire:target="saveInterviewEvaluationDraftFromKanban">
                                        <span wire:loading.remove wire:target="saveInterviewEvaluationDraftFromKanban">Lưu bản nháp</span>
                                        <span wire:loading wire:target="saveInterviewEvaluationDraftFromKanban">Đang lưu...</span>
                                    </button>
                                @endif
                                @if ($canEditEvaluation || $isPanelFinalization)
                                    <button type="button" class="kanban-modal__button is-primary" wire:click="completeInterviewEvaluationFromKanban" wire:loading.attr="disabled" wire:target="completeInterviewEvaluationFromKanban" @disabled(! $canCompleteEvaluation)>
                                        <span wire:loading.remove wire:target="completeInterviewEvaluationFromKanban">
                                            @if ($isPanelFinalization)
                                                Chốt kết quả vòng
                                            @elseif (($kanbanDropAction['single_evaluator'] ?? true) === true)
                                                Gửi và chốt kết quả
                                            @else
                                                Gửi phiếu đánh giá
                                            @endif
                                        </span>
                                        <span wire:loading wire:target="completeInterviewEvaluationFromKanban">Đang hoàn tất...</span>
                                    </button>
                                @endif
                            </div>
                            @if (($canEditEvaluation || $isPanelFinalization) && ! $canCompleteEvaluation && filled($evaluationCompletionHint))
                                <p class="kanban-modal__field-help" role="status">{{ $evaluationCompletionHint }}</p>
                            @endif
                        </form>
                    @elseif (($kanbanDropAction['type'] ?? null) === 'offer_draft')
                        <form class="kanban-modal__body" wire:submit.prevent="saveOfferDraftFromKanban">
                            @php
                                $offerContext = $kanbanDropAction['offer_context'] ?? [];
                                $publishedSalaryMin = data_get($offerContext, 'published_salary_min');
                                $publishedSalaryMax = data_get($offerContext, 'published_salary_max');
                                $offeredSalary = is_numeric($kanbanOfferForm['salary_offered'] ?? null)
                                    ? (int) $kanbanOfferForm['salary_offered']
                                    : null;
                                $outsidePublishedRange = ($offerContext['published_salary_currency'] ?? 'VND') === 'VND'
                                    && $offeredSalary !== null
                                    && (($publishedSalaryMin !== null && $offeredSalary < $publishedSalaryMin)
                                        || ($publishedSalaryMax !== null && $offeredSalary > $publishedSalaryMax));
                            @endphp

                            <div class="kanban-offer-layout">
                                <aside class="kanban-offer-context">
                                    <div class="kanban-offer-brief">
                                        <p class="kanban-offer-brief__name">{{ $offerContext['candidate_name'] ?? ($kanbanDropAction['candidate'] ?? 'Ứng viên') }}</p>
                                        <p class="kanban-offer-brief__job">{{ $offerContext['job_title'] ?? ($kanbanDropAction['job'] ?? '-') }}</p>
                                        <p class="kanban-offer-brief__branch">{{ $offerContext['branch'] ?? '-' }}</p>
                                    </div>

                                    <div class="kanban-modal__facts">
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Kết quả phỏng vấn</span>
                                            <span class="kanban-modal__fact-value">{{ $offerContext['interview_result'] ?? 'Chưa có kết luận' }}</span>
                                        </div>
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Điểm trung bình</span>
                                            <span class="kanban-modal__fact-value">{{ $offerContext['average_score'] ?? '-' }}</span>
                                        </div>
                                        <div class="kanban-modal__fact" style="grid-column: 1 / -1;">
                                            <span class="kanban-modal__fact-label">Khuyến nghị đánh giá</span>
                                            <span class="kanban-modal__fact-value">{{ $offerContext['recommendation'] ?? 'Chưa có khuyến nghị' }}</span>
                                        </div>
                                    </div>

                                    <div class="kanban-offer-insight">
                                        <span class="kanban-offer-insight__title">Nhận xét phỏng vấn</span>
                                        <p class="kanban-offer-insight__text">{{ $offerContext['interview_note'] ?? 'Chưa có nhận xét tổng quan từ buổi phỏng vấn.' }}</p>
                                    </div>

                                    @if (filled($offerContext['ai_summary'] ?? null))
                                        <div class="kanban-offer-insight kanban-offer-summary">
                                            <span class="kanban-offer-insight__title">Tóm tắt hồ sơ</span>
                                            <p class="kanban-offer-insight__text">{{ $offerContext['ai_summary'] }}</p>
                                        </div>
                                    @endif

                                    @if (filled($offerContext['cv_url'] ?? null))
                                        <div class="kanban-modal__cv">
                                            <span class="kanban-modal__cv-name">{{ $offerContext['cv_name'] ?? 'CV ứng tuyển' }}</span>
                                            <a class="kanban-modal__button is-primary" href="{{ $offerContext['cv_url'] }}" target="_blank" rel="noopener">Mở CV</a>
                                        </div>
                                    @endif
                                </aside>

                                <section class="kanban-offer-form">
                                    @if (filled($kanbanDropAction['approval_note'] ?? null))
                                        <div class="kanban-modal__notice is-warning">
                                            <strong>Yêu cầu điều chỉnh</strong>
                                            <span>{{ $kanbanDropAction['approval_note'] }}</span>
                                        </div>
                                    @endif

                                    <label class="kanban-modal__field">
                                        <span class="kanban-modal__label">Mẫu thư mời</span>
                                        <select class="kanban-modal__select" wire:model.live="kanbanOfferForm.offer_letter_template_id">
                                            <option value="">Chọn mẫu thư mời</option>
                                            @foreach (($kanbanDropAction['template_options'] ?? []) as $id => $label)
                                                <option value="{{ $id }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <span class="kanban-modal__field-help">Mẫu được dùng để tạo PDF và email gửi ứng viên sau khi được duyệt.</span>
                                        @error('kanbanOfferForm.offer_letter_template_id') <span class="kanban-modal__field-error">{{ $message }}</span> @enderror
                                    </label>

                                    @if (filled($kanbanOfferTemplatePreview))
                                        <details class="kanban-offer-preview">
                                            <summary>Xem trước thư mời</summary>
                                            <p>Thông tin được cập nhật từ dữ liệu đang nhập trước khi tạo PDF.</p>
                                            <div class="kanban-offer-preview__body">{{ $kanbanOfferTemplatePreview }}</div>
                                        </details>
                                    @endif

                                    <div class="kanban-modal__grid">
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Mức lương đề nghị</span>
                                            <input class="kanban-modal__input" type="number" min="1" step="1" required wire:model.live.debounce.500ms="kanbanOfferForm.salary_offered">
                                            <span class="kanban-modal__field-help">Khung lương tin tuyển dụng: {{ $offerContext['published_salary_label'] ?? 'Thỏa thuận' }}.</span>
                                            @error('kanbanOfferForm.salary_offered') <span class="kanban-modal__field-error">{{ $message }}</span> @enderror
                                        </label>
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Thời gian thử việc (tháng)</span>
                                            <select class="kanban-modal__select" wire:model.live="kanbanOfferForm.probation_months">
                                                <option value="0">Không thử việc</option>
                                                <option value="1">1 tháng</option>
                                                <option value="2">2 tháng</option>
                                                <option value="3">3 tháng</option>
                                                <option value="4">4 tháng</option>
                                                <option value="5">5 tháng</option>
                                                <option value="6">6 tháng</option>
                                            </select>
                                            @error('kanbanOfferForm.probation_months') <span class="kanban-modal__field-error">{{ $message }}</span> @enderror
                                        </label>
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Ngày nhận việc dự kiến</span>
                                            <input class="kanban-modal__input" type="date" required wire:model.live="kanbanOfferForm.start_date">
                                            @error('kanbanOfferForm.start_date') <span class="kanban-modal__field-error">{{ $message }}</span> @enderror
                                        </label>
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Hạn ứng viên phản hồi</span>
                                            <input class="kanban-modal__input" type="datetime-local" required wire:model.live="kanbanOfferForm.expires_at">
                                            @error('kanbanOfferForm.expires_at') <span class="kanban-modal__field-error">{{ $message }}</span> @enderror
                                        </label>
                                    </div>

                                    @if ($outsidePublishedRange)
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Lý do điều chỉnh lương</span>
                                            <textarea class="kanban-modal__textarea" wire:model.defer="kanbanOfferForm.salary_adjustment_reason" placeholder="Ví dụ: Điều chỉnh theo kinh nghiệm, năng lực và ngân sách vị trí."></textarea>
                                            <span class="kanban-modal__field-help">Mức đề nghị nằm ngoài khung công khai. Lý do này chỉ hiển thị cho nội bộ khi duyệt đề nghị.</span>
                                            @error('kanbanOfferForm.salary_adjustment_reason') <span class="kanban-modal__field-error">{{ $message }}</span> @enderror
                                        </label>
                                    @endif

                                    <label class="kanban-modal__field">
                                        <span class="kanban-modal__label">Nội dung bổ sung</span>
                                        <textarea class="kanban-modal__textarea" wire:model.defer="kanbanOfferForm.content" placeholder="Bắt buộc khi không chọn mẫu đề nghị."></textarea>
                                        @error('kanbanOfferForm.content') <span class="kanban-modal__field-error">{{ $message }}</span> @enderror
                                    </label>

                                    <div class="kanban-modal__actions">
                                        <button type="button" class="kanban-modal__button" x-on:click="closeModal()">Hủy</button>
                                        <button type="submit" class="kanban-modal__button is-primary" wire:loading.attr="disabled" wire:target="saveOfferDraftFromKanban">
                                            <span wire:loading.remove wire:target="saveOfferDraftFromKanban">Lưu bản nháp</span>
                                            <span wire:loading wire:target="saveOfferDraftFromKanban">Đang lưu...</span>
                                        </button>
                                    </div>
                                </section>
                            </div>
                        </form>
                    @elseif (($kanbanDropAction['type'] ?? null) === 'offer_approval')
                        <form class="kanban-modal__body" wire:submit.prevent="submitOfferForApprovalFromKanban">
                            <p class="kanban-modal__message">{{ $kanbanDropAction['message'] ?? '' }}</p>
                            <div class="kanban-modal__notice">
                                <strong>Sau khi gửi</strong>
                                <span>Nội dung đề nghị sẽ được khóa cho đến khi giám đốc duyệt hoặc trả về điều chỉnh.</span>
                            </div>
                            <div class="kanban-modal__actions">
                                <button type="button" class="kanban-modal__button" x-on:click="closeModal()">Hủy</button>
                                <button type="submit" class="kanban-modal__button is-primary" wire:loading.attr="disabled" wire:target="submitOfferForApprovalFromKanban">
                                    <span wire:loading.remove wire:target="submitOfferForApprovalFromKanban">Gửi duyệt</span>
                                    <span wire:loading wire:target="submitOfferForApprovalFromKanban">Đang gửi...</span>
                                </button>
                            </div>
                        </form>
                    @elseif (($kanbanDropAction['type'] ?? null) === 'interview_delivery')
                        <form class="kanban-modal__body" wire:submit.prevent="sendInterviewScheduleFromKanban">
                            <p class="kanban-modal__message">
                                {{ $kanbanDropAction['message'] ?? '' }}
                            </p>

                            <div class="kanban-modal__facts">
                                <div class="kanban-modal__fact">
                                    <span class="kanban-modal__fact-label">Người nhận</span>
                                    <span class="kanban-modal__fact-value">{{ $kanbanDropAction['recipient_count'] ?? 0 }} người</span>
                                </div>
                                <div class="kanban-modal__fact">
                                    <span class="kanban-modal__fact-label">Đính kèm</span>
                                    <span class="kanban-modal__fact-value">File lịch .ics</span>
                                </div>
                            </div>

                            <div class="kanban-modal__actions">
                                <button type="button" class="kanban-modal__button" x-on:click="closeModal()">
                                    Hủy
                                </button>

                                <button
                                    type="submit"
                                    class="kanban-modal__button is-primary"
                                    wire:loading.attr="disabled"
                                    wire:target="sendInterviewScheduleFromKanban"
                                >
                                    <span wire:loading.remove wire:target="sendInterviewScheduleFromKanban">
                                        {{ ($kanbanDropAction['is_update'] ?? false) ? 'Gửi cập nhật' : 'Gửi lịch' }}
                                    </span>
                                    <span wire:loading wire:target="sendInterviewScheduleFromKanban">Đang gửi...</span>
                                </button>
                            </div>
                        </form>
                    @elseif (($kanbanDropAction['type'] ?? null) === 'interview_schedule')
                        <form class="kanban-modal__body" wire:submit.prevent="scheduleInterviewFromKanban">
                            @php
                                $interviewContext = $kanbanDropAction['interview_context'] ?? [];
                                $interviewAi = $interviewContext['ai'] ?? [];
                            @endphp

                            <div class="kanban-interview-layout">
                                <aside class="kanban-interview-context">
                                    <div class="kanban-interview-brief">
                                        <h4 class="kanban-interview-brief__title">
                                            {{ $interviewContext['candidate_name'] ?? ($kanbanDropAction['candidate'] ?? 'Ứng viên') }}
                                        </h4>
                                        <p class="kanban-interview-brief__meta">
                                            {{ $interviewContext['job_title'] ?? ($kanbanDropAction['job'] ?? '-') }}
                                        </p>
                                    </div>

                                    <div class="kanban-modal__facts">
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Email</span>
                                            <span class="kanban-modal__fact-value">{{ $interviewContext['candidate_email'] ?? '-' }}</span>
                                        </div>
                                        <div class="kanban-modal__fact">
                                            <span class="kanban-modal__fact-label">Số điện thoại</span>
                                            <span class="kanban-modal__fact-value">{{ $interviewContext['candidate_phone'] ?? '-' }}</span>
                                        </div>
                                    </div>

                                    @php
                                        $screeningNote = trim((string) ($interviewContext['screening_note'] ?? ''));
                                    @endphp
                                    @if ($screeningNote !== '')
                                        <details class="kanban-interview-note">
                                            <summary>
                                                <span>
                                                    <span class="kanban-interview-note__label">Ghi chú sàng lọc</span>
                                                    <span class="kanban-interview-note__excerpt">{{ \Illuminate\Support\Str::limit($screeningNote, 110) }}</span>
                                                </span>
                                                <span class="kanban-interview-note__toggle" aria-hidden="true"></span>
                                            </summary>
                                            <p class="kanban-interview-note__value">{{ $screeningNote }}</p>
                                        </details>
                                    @else
                                        <div class="kanban-interview-note">
                                            <span class="kanban-interview-note__label">Ghi chú sàng lọc</span>
                                            <p class="kanban-interview-note__value">Chưa có ghi chú sàng lọc.</p>
                                        </div>
                                    @endif

                                    <details class="kanban-modal__ai kanban-interview-ai">
                                        <summary>
                                            <div class="kanban-modal__ai-head">
                                                <div class="kanban-modal__ai-title">Gợi ý phỏng vấn từ AI</div>
                                                <div class="kanban-modal__ai-badges">
                                                    @if (($interviewAi['score'] ?? null) !== null)
                                                        <span class="kanban-modal__ai-badge is-score-{{ $interviewAi['score_tone'] ?? 'neutral' }}">{{ $interviewAi['score'] }}/100</span>
                                                    @endif
                                                    <span class="kanban-modal__ai-badge">{{ $interviewAi['label'] ?? 'Chưa có khuyến nghị' }}</span>
                                                </div>
                                            </div>
                                        </summary>

                                        <p class="kanban-modal__ai-text">{{ $interviewAi['summary'] ?? 'Chưa có dữ liệu AI sàng lọc để gợi ý trọng tâm phỏng vấn.' }}</p>

                                        @if (! empty($interviewAi['gaps'] ?? []))
                                            <div class="kanban-modal__ai-more">
                                                <div class="kanban-modal__ai-section-title">Cần làm rõ</div>
                                                <ul class="kanban-modal__ai-list">
                                                    @foreach ($interviewAi['gaps'] as $gap)
                                                        <li>{{ $gap }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </details>

                                    @if (filled($interviewContext['cv_url'] ?? null))
                                        <a href="{{ $interviewContext['cv_url'] }}" target="_blank" rel="noopener noreferrer" class="kanban-card__button">
                                            Xem CV ứng tuyển
                                        </a>
                                    @endif
                                </aside>

                                <div class="kanban-interview-form">
                                    <p class="kanban-modal__message">
                                        Chọn lịch phỏng vấn phù hợp trước khi chuyển hồ sơ sang giai đoạn phỏng vấn.
                                    </p>

                                    <div class="kanban-modal__field">
                                        <span class="kanban-modal__label">
                                            Vòng {{ $kanbanDropAction['round_context']['number'] ?? 1 }}/{{ $kanbanDropAction['round_context']['total'] ?? 1 }}
                                        </span>
                                        <div class="kanban-modal__readonly" aria-readonly="true">
                                            {{ $kanbanDropAction['round_context']['name'] ?? 'Phỏng vấn' }}
                                        </div>
                                        <span class="kanban-modal__field-help">Vòng phỏng vấn được áp dụng theo quy trình của tin tuyển dụng.</span>
                                        @if (filled($kanbanDropAction['round_context']['objective'] ?? null))
                                            <span class="kanban-modal__field-help">{{ $kanbanDropAction['round_context']['objective'] }}</span>
                                        @endif
                                        <span class="kanban-modal__field-help">
                                            Vai trò phù hợp: {{ collect($kanbanDropAction['round_context']['evaluator_roles'] ?? [])->implode(', ') }}.
                                        </span>
                                    </div>

                                    <label class="kanban-modal__field">
                                        <span class="kanban-modal__label">Hình thức</span>
                                        <select class="kanban-modal__select" wire:model.live="kanbanInterviewForm.type">
                                            <option value="">Chọn hình thức</option>
                                            <option value="online">Online</option>
                                            <option value="offline">Offline</option>
                                        </select>
                                        @error('kanbanInterviewForm.type')
                                            <span class="kanban-modal__field-error">{{ $message }}</span>
                                        @enderror
                                    </label>

                                    <div class="kanban-interview-form__grid">
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Thời gian phỏng vấn</span>
                                            <input
                                                type="datetime-local"
                                                class="kanban-modal__select"
                                                wire:model.live.debounce.500ms="kanbanInterviewForm.scheduled_at"
                                            >
                                            @error('kanbanInterviewForm.scheduled_at')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </label>

                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Thời lượng</span>
                                            <select class="kanban-modal__select" wire:model.live="kanbanInterviewForm.duration_minutes">
                                                <option value="">Chọn thời lượng</option>
                                                <option value="30">30 phút</option>
                                                <option value="45">45 phút</option>
                                                <option value="60">60 phút</option>
                                                <option value="90">90 phút</option>
                                            </select>
                                            @error('kanbanInterviewForm.duration_minutes')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </label>
                                    </div>

                                    <div class="kanban-interview-panel">
                                        <div class="kanban-interview-panel__head">
                                            <span class="kanban-interview-panel__title">Phân công phỏng vấn</span>
                                            <p class="kanban-interview-panel__help">Chọn một người phụ trách vòng, sau đó thêm thành viên đánh giá nếu cần.</p>
                                        </div>

                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Người phụ trách vòng phỏng vấn</span>
                                            <select class="kanban-modal__select" wire:model.live="kanbanInterviewForm.interviewer_id">
                                                <option value="">Chọn người phụ trách</option>
                                                @foreach (($kanbanDropAction['interviewer_options'] ?? []) as $id => $label)
                                                    <option value="{{ $id }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <span class="kanban-modal__field-help">Bắt buộc · tham gia đánh giá và chốt kết quả vòng.</span>
                                            @error('kanbanInterviewForm.interviewer_id')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </label>

                                    @php
                                        $additionalEvaluatorOptions = collect($kanbanDropAction['interviewer_options'] ?? [])
                                            ->reject(fn ($label, $id) => (string) $id === (string) ($kanbanInterviewForm['interviewer_id'] ?? ''));
                                        $selectedEvaluatorOptions = collect((array) ($kanbanInterviewForm['evaluator_ids'] ?? []))
                                            ->mapWithKeys(function ($id) use ($additionalEvaluatorOptions): array {
                                                $label = $additionalEvaluatorOptions->get((int) $id)
                                                    ?? $additionalEvaluatorOptions->get((string) $id);

                                                return filled($label) ? [(int) $id => $label] : [];
                                            });
                                    @endphp
                                    @if (filled($kanbanInterviewForm['interviewer_id'] ?? null))
                                    <details class="kanban-evaluator-picker" x-data="{ open: false, search: '' }" x-bind:open="open">
                                        <summary class="kanban-evaluator-picker__summary" x-on:click.prevent="open = ! open">
                                            <div>
                                                <span class="kanban-modal__label">Thành viên đánh giá</span>
                                                <span class="kanban-modal__field-help">Không bắt buộc</span>
                                            </div>
                                            <span class="kanban-evaluator-picker__summary-count">
                                                {{ $selectedEvaluatorOptions->isEmpty() ? 'Thêm người' : $selectedEvaluatorOptions->count().' người' }}
                                            </span>
                                        </summary>

                                        <div class="kanban-evaluator-picker__body">
                                            <p class="kanban-evaluator-picker__help">
                                                Mỗi người gửi một phiếu độc lập; người phụ trách tổng hợp và chốt kết quả vòng.
                                            </p>

                                            @if ($selectedEvaluatorOptions->isNotEmpty())
                                                <div class="kanban-evaluator-picker__selected" aria-label="Thành viên đã chọn">
                                                    @foreach ($selectedEvaluatorOptions as $id => $label)
                                                        <span class="kanban-evaluator-picker__chip" wire:key="kanban-evaluator-chip-{{ $id }}">
                                                            <span title="{{ $label }}">{{ $label }}</span>
                                                            <button
                                                                type="button"
                                                                wire:click="removeKanbanInterviewEvaluator({{ $id }})"
                                                                wire:loading.attr="disabled"
                                                                wire:target="removeKanbanInterviewEvaluator({{ $id }})"
                                                                aria-label="Bỏ {{ $label }}"
                                                                title="Bỏ thành viên"
                                                            >×</button>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if ($additionalEvaluatorOptions->isNotEmpty())
                                                <input
                                                    type="search"
                                                    class="kanban-evaluator-picker__search"
                                                    x-model.debounce.150ms="search"
                                                    placeholder="Tìm theo tên hoặc vai trò"
                                                    aria-label="Tìm thành viên đánh giá"
                                                >
                                                <div class="kanban-evaluator-picker__options">
                                                    @foreach ($additionalEvaluatorOptions as $id => $label)
                                                        @php
                                                            $searchLabel = mb_strtolower($label).' '.mb_strtolower(\Illuminate\Support\Str::ascii($label));
                                                        @endphp
                                                        <label
                                                            class="kanban-evaluator-picker__option"
                                                            data-search="{{ $searchLabel }}"
                                                            x-show="$el.dataset.search.includes(search.toLowerCase())"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                value="{{ $id }}"
                                                                wire:model.live.debounce.180ms="kanbanInterviewForm.evaluator_ids"
                                                            >
                                                            <span>{{ $label }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="kanban-evaluator-picker__empty">Chưa có người phù hợp trong chi nhánh.</p>
                                            @endif

                                            @error('kanbanInterviewForm.evaluator_ids')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </details>
                                    @else
                                        <div class="kanban-evaluator-picker__disabled" aria-disabled="true">
                                            <strong>Thành viên đánh giá</strong>
                                            Chọn người phụ trách trước để thêm thành viên.
                                        </div>
                                    @endif
                                    </div>

                                    @if (($kanbanInterviewForm['type'] ?? '') === 'offline')
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Địa điểm phỏng vấn</span>
                                            <select class="kanban-modal__select" wire:model.live="kanbanInterviewForm.workplace_id">
                                                <option value="">Chọn địa điểm</option>
                                                @foreach (($kanbanDropAction['workplace_options'] ?? []) as $id => $label)
                                                    <option value="{{ $id }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('kanbanInterviewForm.workplace_id')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </label>
                                    @elseif (($kanbanInterviewForm['type'] ?? '') === 'online')
                                        <label class="kanban-modal__field">
                                            <span class="kanban-modal__label">Link phỏng vấn</span>
                                            <input
                                                type="url"
                                                class="kanban-modal__select"
                                                wire:model.defer="kanbanInterviewForm.meeting_link"
                                                placeholder="https://meet.google.com/..."
                                            >
                                            @error('kanbanInterviewForm.meeting_link')
                                                <span class="kanban-modal__field-error">{{ $message }}</span>
                                            @enderror
                                        </label>
                                    @endif

                                    @php
                                        $assignedScorecard = $kanbanDropAction['assigned_scorecard'] ?? [];
                                        $selectedScorecardCriteria = $assignedScorecard['criteria'] ?? [];
                                    @endphp
                                    <div class="kanban-modal__field">
                                        <span class="kanban-modal__label">Mẫu đánh giá của vòng</span>
                                        <div class="kanban-modal__readonly" aria-readonly="true">
                                            {{ $assignedScorecard['name'] ?? 'Chưa có mẫu đánh giá' }}
                                        </div>
                                        @if ($selectedScorecardCriteria !== [])
                                            <details class="kanban-scorecard-preview">
                                                <summary class="kanban-scorecard-preview__head">
                                                    <span>Xem tiêu chí đánh giá</span>
                                                    <span class="kanban-scorecard-preview__count">{{ count($selectedScorecardCriteria) }} tiêu chí</span>
                                                </summary>
                                                <ul class="kanban-scorecard-preview__list">
                                                    @foreach ($selectedScorecardCriteria as $criterion)
                                                        <li>{{ $criterion }}</li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        @endif
                                        <span class="kanban-modal__field-help">Mẫu và tiêu chí được giữ thống nhất cho toàn bộ vòng phỏng vấn.</span>
                                        @error('kanbanInterviewForm.scorecard_template_id')
                                            <span class="kanban-modal__field-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="kanban-schedule-assist">
                                        @if (filled($kanbanInterviewAvailabilityNotice))
                                            <div class="kanban-availability-check__notice">
                                                {{ $kanbanInterviewAvailabilityNotice }}
                                            </div>
                                        @endif

                                        <div class="kanban-schedule-preview">
                                            <div class="kanban-schedule-preview__title">
                                                {{ $kanbanInterviewSchedulePreview['title'] ?? 'Lịch phỏng vấn gần nhất' }}
                                            </div>

                                            @if (! empty($kanbanInterviewSchedulePreview['items'] ?? []))
                                                <ul class="kanban-schedule-preview__list">
                                                    @foreach ($kanbanInterviewSchedulePreview['items'] as $item)
                                                        <li class="kanban-schedule-preview__item">
                                                            <span class="kanban-schedule-preview__time">{{ $item['time'] }}</span>
                                                            <span class="kanban-schedule-preview__name">{{ $item['title'] }}</span>
                                                            <span class="kanban-schedule-preview__meta">{{ $item['meta'] }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div class="kanban-schedule-preview__empty">
                                                    {{ $kanbanInterviewSchedulePreview['empty'] ?? 'Chưa có lịch sắp tới.' }}
                                                </div>
                                            @endif

                                            @if (! empty($kanbanInterviewSchedulePreview['suggestions'] ?? []))
                                                <div class="kanban-schedule-preview__suggestions">
                                                    Có thể chọn: {{ implode(', ', $kanbanInterviewSchedulePreview['suggestions']) }}.
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <label class="kanban-modal__field">
                                        <span class="kanban-modal__label">Ghi chú gửi kèm</span>
                                        <textarea
                                            class="kanban-modal__textarea"
                                            wire:model.defer="kanbanInterviewForm.notes"
                                            placeholder="Nội dung gửi kèm lịch hoặc lưu ý cho vòng phỏng vấn..."
                                        ></textarea>
                                    </label>

                                    <div class="kanban-modal__actions">
                                        <button type="button" class="kanban-modal__button" x-on:click="closeModal()">
                                            Hủy
                                        </button>

                                        <button
                                            type="submit"
                                            class="kanban-modal__button is-primary"
                                            wire:loading.attr="disabled"
                                            wire:target="scheduleInterviewFromKanban"
                                        >
                                            <span wire:loading.remove wire:target="scheduleInterviewFromKanban">Lưu lịch phỏng vấn</span>
                                            <span wire:loading wire:target="scheduleInterviewFromKanban">Đang lưu...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="kanban-modal__body">
                            <p class="kanban-modal__message">
                                {{ $kanbanDropAction['message'] ?? 'Hồ sơ cần được xử lý thêm trước khi chuyển giai đoạn.' }}
                            </p>

                            <div class="kanban-modal__actions">
                                <button type="button" class="kanban-modal__button" x-on:click="closeModal()">
                                    Đóng
                                </button>

                                @if (($kanbanDropAction['type'] ?? null) === 'requirement' && filled($kanbanDropAction['action_url'] ?? null))
                                    <a href="{{ $kanbanDropAction['action_url'] }}" class="kanban-modal__button is-primary">
                                        {{ $kanbanDropAction['action_label'] ?? 'Mở hồ sơ xử lý' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
