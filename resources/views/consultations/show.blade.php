@extends('layouts.app')

@section('title', $session->title)
@section('header', $session->title)

@section('content')
    <x-button size="md" href="{{ route('consultations.index') }}" class="mb-4">
        Kembali
    </x-button>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2">
            <x-card id="kak-vesta" x-data="vesaChat('{{ route('consultations.chat.ask', $session) }}', '{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}')">
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-[#071833]">Konsultasi Kak Vesta</h3>
                            <p class="text-xs text-[#667085] mt-0.5">
                                {{ $session->regulations->count() }} regulasi — tanyakan analisa lintas regulasi.
                            </p>
                        </div>
                    </div>
                </x-slot>

                <div x-ref="messages" class="max-h-[28rem] overflow-y-auto space-y-3">
                    @forelse($session->messages as $msg)
                        @if ($msg->role === 'user')
                            <div class="flex items-start justify-end gap-2.5">
                                <div
                                    class="max-w-[80%] rounded-2xl rounded-tr-md bg-navy-gradient px-4 py-3 text-sm leading-6 text-white shadow-sm">
                                    <div class="whitespace-pre-wrap break-words">{{ $msg->content }}</div>
                                </div>
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] text-xs font-bold text-white ring-1 ring-[#c99a3e]/40">
                                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-2.5">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] text-xs font-bold text-[#071b3a]">
                                    V</div>
                                <div
                                    class="max-w-[85%] min-w-0 rounded-2xl rounded-tl-md bg-[#f6f8fb] px-4 py-3 text-sm leading-6 text-[#071833] shadow-sm ring-1 ring-[#e7eaf0]">
                                    <div class="whitespace-pre-wrap break-words">{{ $msg->content }}</div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div x-ref="empty" class="text-center py-10">
                            <div
                                class="mx-auto w-12 h-12 rounded-2xl bg-[#f6f8fb] flex items-center justify-center text-[#c99a3e]">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                </svg>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-[#071833]">Belum ada percakapan.</p>
                            <p class="text-xs text-[#667085] mt-1">Konsultasi pertanyaan lintas regulasi di sini.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 border-t border-[#e7eaf0] pt-4">
                    <form @submit.prevent="send()" class="flex items-center gap-2">
                        <label class="relative flex-1">
                            <textarea x-model="question" :disabled="sending" rows="2" maxlength="4000" class="input-premium resize-none"
                                placeholder="Tanya Kak Vesta tentang regulasi…"></textarea>
                        </label>
                        <button type="submit" :disabled="sending"
                            class="shrink-0 inline-flex items-center justify-center gap-2 h-11 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-[#c99a3e] to-[#b17c24] hover:brightness-110 transition disabled:opacity-60 disabled:cursor-not-allowed">
                            <svg x-show="!sending" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            <svg x-show="sending" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
                            </svg>
                            <span x-text="sending ? 'Memproses…' : 'Kirim'"></span>
                        </button>
                    </form>
                    <div class="mt-1.5 flex items-center justify-between gap-3 text-[10px] text-[#667085]">
                        <span>Analisa lintas regulasi — bandingkan pasal, cek irisan, temukan kewajiban.</span>
                        <span x-text="question.length + ' / 4000'"></span>
                    </div>
                    <p x-show="error" x-cloak class="mt-2 text-xs font-medium text-rose-600" x-text="error"></p>
                </div>
            </x-card>
        </div>

        <aside class="space-y-6">
            <x-card>
                <x-slot name="header">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-[#071833]">Regulasi</h3>
                        <button type="button" @click="$dispatch('open-modal-add-regulations')"
                            class="text-xs font-semibold text-[#c99a3e] hover:text-[#8c6a25] transition"
                            @disabled($session->regulations->count() >= 10)>
                            + Tambah
                        </button>
                    </div>
                </x-slot>
                <div class="space-y-2 max-h-[26rem] overflow-y-auto">
                    @forelse($session->regulations as $reg)
                        <div class="flex items-start gap-2.5 p-3 rounded-xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0]">
                            <span
                                class="shrink-0 w-6 h-6 rounded-lg bg-[#c99a3e]/10 flex items-center justify-center text-[10px] font-bold text-[#8c6a25]">{{ $loop->index + 1 }}</span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-[#071833] truncate">{{ $reg->regulation_number }}</p>
                                <p class="text-[10px] text-[#667085] truncate">{{ $reg->title }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-[#667085] text-center py-6">Belum ada regulasi dipilih.</p>
                    @endforelse
                    <p class="text-[10px] text-[#667085] mt-1 text-center">{{ $session->regulations->count() }} / 10</p>
                </div>
            </x-card>
        </aside>
    </div>

    {{-- Add Regulations Modal --}}
    <x-modal name="add-regulations" max-width="2xl" title="Tambah Regulasi">
        <form method="POST" action="{{ route('consultations.regulations.add', $session) }}">
            @csrf
            @include('review-documents._regulation-picker', [
                'selectedIds' => $selectedIds,
                'categories' => $categories,
            ])
            <div class="mt-4 flex items-center justify-between">
                <p class="text-xs text-[#667085]">Maksimal 10 regulasi per sesi (saat ini
                    {{ $session->regulations->count() }}).</p>
                <x-button type="submit" variant="primary">Tambahkan</x-button>
            </div>
        </form>
    </x-modal>
@endsection

@push('scripts')
    <script>
        function vesaChat(url, userInitial) {
            return {
                question: '',
                sending: false,
                error: '',
                async send() {
                    const q = this.question.trim();
                    if (!q || this.sending) return;
                    this.sending = true;
                    this.error = '';
                    this.question = '';
                    this.appendBubble('user', q);
                    this.appendBubble('assistant', 'Kak Vesta sedang mengetik…', true);
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                question: q
                            }),
                        });
                        const data = await res.json();
                        this.removeTyping();
                        if (!res.ok) {
                            const message = data.errors ?
                                Object.values(data.errors).flat().join(' ') :
                                (data.message || 'Terjadi kesalahan.');
                            this.appendBubble('assistant', message);
                            return;
                        }
                        this.appendBubble('assistant', data.reply);
                    } catch (e) {
                        this.removeTyping();
                        this.appendBubble('assistant', 'Koneksi gagal. Coba lagi.');
                    } finally {
                        this.sending = false;
                    }
                },
                appendBubble(role, text, typing = false) {
                    this.$refs.empty?.remove();
                    const wrap = document.createElement('div');
                    wrap.className = role === 'user' ?
                        'flex justify-end items-start gap-2.5' :
                        'flex justify-start items-start gap-2.5';
                    if (typing) wrap.dataset.typing = '1';
                    const avatar = document.createElement('div');
                    avatar.className = role === 'user' ?
                        'shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] ring-1 ring-[#c99a3e]/40 flex items-center justify-center text-white font-bold text-xs' :
                        'shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] flex items-center justify-center text-[#071b3a] font-bold text-xs';
                    avatar.textContent = role === 'user' ? userInitial : 'V';
                    const bubble = document.createElement('div');
                    bubble.className = 'min-w-0 max-w-[80%] rounded-2xl ' +
                        (role === 'user' ? 'rounded-tr-md bg-navy-gradient text-white' :
                            'rounded-tl-md bg-[#f6f8fb] ring-1 ring-[#e7eaf0] text-[#071833]') +
                        ' px-4 py-3 text-sm leading-relaxed whitespace-pre-line break-words';
                    bubble.textContent = text;
                    if (role === 'user') {
                        wrap.appendChild(bubble);
                        wrap.appendChild(avatar);
                    } else {
                        wrap.appendChild(avatar);
                        wrap.appendChild(bubble);
                    }
                    this.$refs.messages.appendChild(wrap);
                    this.$nextTick(() => this.scrollBottom());
                },
                removeTyping() {
                    this.$refs.messages.querySelector('[data-typing]')?.remove();
                },
                scrollBottom() {
                    if (this.$refs.messages) {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    }
                },
            };
        }
    </script>
@endpush
