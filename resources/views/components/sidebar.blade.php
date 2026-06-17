<aside class="fixed inset-y-0 left-0 z-40 w-72 transform transition-transform duration-300 ease-out lg:translate-x-0"
    :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="relative h-full overflow-hidden bg-navy-gradient text-white sidebar-scroll overflow-y-auto">
        {{-- Decorative glow --}}
        <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[#c99a3e]/20 blur-3xl">
        </div>
        <div class="pointer-events-none absolute bottom-0 -left-24 w-72 h-72 rounded-full bg-[#0b2a55]/40 blur-3xl">
        </div>

        <div class="relative flex flex-col h-full px-5 py-7">
            {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 group">
                <div class="relative">
                    <div
                        class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] flex items-center justify-center shadow-[0_10px_30px_rgba(201,154,62,.35)]">
                        <svg class="w-6 h-6 text-[#071b3a]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18M5 8l7-5 7 5M3 8h18M5 21h14M7 8v9M17 8v9" />
                        </svg>
                    </div>
                    <span
                        class="absolute -bottom-1 -right-1 w-3 h-3 rounded-full bg-emerald-400 ring-2 ring-[#071b3a]"></span>
                </div>
                <div>
                    <p class="text-base font-bold tracking-tight text-white">{{ config('app.name', 'InvestaLaw') }}</p>
                    <p class="text-[10.5px] font-medium tracking-[0.18em] uppercase text-[#c99a3e]">Legal · Strategic
                    </p>
                </div>
            </a>

            {{-- AUM-like card (executive summary) --}}
            <div class="mt-7 rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] uppercase tracking-[0.16em] text-white/60">Workspace</p>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Live
                    </span>
                </div>
                <p class="mt-2 text-lg font-bold text-white">Compliance Suite</p>
                <p class="text-xs text-white/55 mt-0.5">Regulatory · Capital Markets</p>
            </div>

            {{-- Navigation --}}
            <nav class="mt-7 flex-1">
                <p class="px-3 mb-2 text-[10.5px] font-semibold tracking-[0.18em] uppercase text-white/45">Overview</p>
                <ul class="space-y-1.5">
                    <li>
                        <a href="{{ route('dashboard') }}"
                            class="nav-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13.5 12 4l9 9.5M5 12v8a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-8" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                <p class="px-3 mt-7 mb-2 text-[10.5px] font-semibold tracking-[0.18em] uppercase text-white/45">Master
                    Data</p>
                <ul class="space-y-1.5">

                    <li>
                        <a href="{{ route('regulation-categories.index') }}"
                            class="nav-item {{ request()->routeIs('regulation-categories.*') || request()->routeIs('sub-categories.*') ? 'is-active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                            </svg>
                            <span>Kategori</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('regulation-types.index') }}"
                            class="nav-item {{ request()->routeIs('regulation-types.*') ? 'is-active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L12 12.75 6.429 9.75m11.142 0 4.179 2.25-4.179 2.25m0 0L12 17.25 6.429 14.25m11.142 0 4.179 2.25L12 21.75l-9.75-5.25 4.179-2.25" />
                            </svg>
                            <span>Jenis Regulasi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('regulations.index') }}"
                            class="nav-item {{ request()->routeIs('regulations.*') ? 'is-active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Regulasi</span>
                        </a>
                    </li>
                </ul>

                <p class="px-3 mt-7 mb-2 text-[10.5px] font-semibold tracking-[0.18em] uppercase text-white/45">
                    Compliance</p>
                <ul class="space-y-1.5">
                    <li>
                        <a href="{{ route('review-documents.index') }}"
                            class="nav-item {{ request()->routeIs('review-documents.*') ? 'is-active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Documents</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reviews.index') }}"
                            class="nav-item {{ request()->routeIs('reviews.*') || request()->routeIs('reports.*') ? 'is-active' : '' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12h6m-6 3h4m1.5 6H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.4 48.4 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586M8.25 8.25H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                            <span>Reviews &amp; Reports</span>
                        </a>
                    </li>
                </ul>
            </nav>

            {{-- Footer --}}
            <div
                class="mt-6 rounded-2xl border border-[#c99a3e]/25 bg-gradient-to-br from-[#c99a3e]/15 to-transparent p-4">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#c99a3e] flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5 text-[#071b3a]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2 4 5v6c0 5 3.5 9 8 11 4.5-2 8-6 8-11V5l-8-3Z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white">Need Assistance?</p>
                        <p class="text-[11px] text-white/60 leading-relaxed mt-0.5">Reach our compliance desk for any
                            regulatory inquiry.</p>
                    </div>
                </div>
                <a href="mailto:support@investalaw.test"
                    class="mt-3 inline-flex items-center justify-center gap-2 w-full text-xs font-semibold text-[#071b3a] bg-gradient-to-r from-[#c99a3e] to-[#e6c06a] rounded-xl py-2.5 hover:brightness-110 transition">
                    Contact Compliance
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M13 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</aside>
