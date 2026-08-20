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
                        <div x-data="{ exportOpen: false }" class="relative">
                            <button @click="exportOpen = !exportOpen" @click.away="exportOpen = false"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#c99a3e] bg-[#c99a3e]/10 hover:bg-[#c99a3e]/20 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Export
                            </button>
                            <div x-show="exportOpen" x-cloak
                                class="absolute right-0 mt-1 w-36 rounded-lg bg-white shadow-lg ring-1 ring-[#e7eaf0] py-1 z-50">
                                <a href="{{ route('consultations.export.session.pdf', $session) }}"
                                    class="flex items-center gap-2 px-3 py-2 text-xs text-[#071833] hover:bg-[#f6f8fb]">
                                    <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    PDF
                                </a>
                                <a href="{{ route('consultations.export.session.word', $session) }}"
                                    class="flex items-center gap-2 px-3 py-2 text-xs text-[#071833] hover:bg-[#f6f8fb]">
                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    Word
                                </a>
                            </div>
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
                                    @if(!empty($msg->attachments))
                                        <div class="mt-2 space-y-1">
                                            @foreach($msg->attachments as $idx => $att)
                                                <a href="{{ route('consultations.attachments.download', [$session, $msg, $idx]) }}"
                                                    target="_blank"
                                                    class="flex items-center gap-1.5 text-[10px] text-white/80 hover:text-white transition">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                    </svg>
                                                    {{ $att['filename'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#071b3a] to-[#0b2a55] text-xs font-bold text-white ring-1 ring-[#c99a3e]/40">
                                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-2.5 group">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#c99a3e] to-[#e6c06a] text-xs font-bold text-[#071b3a]">
                                    V</div>
                                <div
                                    class="max-w-[85%] min-w-0 rounded-2xl rounded-tl-md bg-[#f6f8fb] px-4 py-3 text-sm leading-6 text-[#071833] shadow-sm ring-1 ring-[#e7eaf0]">
                                     <div class="whitespace-pre-wrap break-words">{{ $msg->content }}</div>
                                     @if ($msg->citations)
                                         <div class="mt-3 border-t border-[#e7eaf0] pt-2 text-xs">
                                             <p class="font-bold text-[#071833]">Sumber terverifikasi</p>
                                             @foreach ($msg->citations as $citation)
                                                 <div class="mt-2 rounded-lg bg-white p-2 ring-1 ring-[#e7eaf0]">
                                                     <p class="font-semibold">{{ $citation['source_label'] ?? 'Sumber regulasi' }}{{ !empty($citation['page']) ? ' · Halaman '.$citation['page'] : '' }}</p>
                                                     @if (!empty($citation['quote']))
                                                         <p class="mt-1 text-[#667085]">“{{ $citation['quote'] }}”</p>
                                                     @endif
                                                     @if (empty($citation['verified']))
                                                         <p class="mt-1 text-amber-700">Kutipan perlu diverifikasi.</p>
                                                     @endif
                                                 </div>
                                             @endforeach
                                         </div>
                                     @endif
                                     <p class="mt-2 text-[10px] text-[#667085]">Confidence: {{ ucfirst($msg->confidence ?? 'low') }}. Tetap verifikasi ke dokumen asli.</p>
                                    @if(!empty($msg->attachments))
                                        <div class="mt-2 space-y-1">
                                            @foreach($msg->attachments as $idx => $att)
                                                <a href="{{ route('consultations.attachments.download', [$session, $msg, $idx]) }}"
                                                    target="_blank"
                                                    class="flex items-center gap-1.5 text-[10px] text-[#667085] hover:text-[#c99a3e] transition">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                                    </svg>
                                                    {{ $att['filename'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="mt-2 pt-2 border-t border-[#e7eaf0] opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div x-data="{ msgExportOpen: false }" class="relative inline-flex items-center gap-1">
                                            <button @click="msgExportOpen = !msgExportOpen" @click.away="msgExportOpen = false"
                                                class="inline-flex items-center gap-1 text-[10px] text-[#667085] hover:text-[#c99a3e] transition">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                                Export
                                            </button>
                                            <div x-show="msgExportOpen" x-cloak
                                                class="absolute left-0 mt-1 w-28 rounded-lg bg-white shadow-lg ring-1 ring-[#e7eaf0] py-1 z-50">
                                                <a href="{{ route('consultations.export.message.pdf', [$session, $msg]) }}"
                                                    class="flex items-center gap-2 px-3 py-1.5 text-[10px] text-[#071833] hover:bg-[#f6f8fb]">
                                                    PDF
                                                </a>
                                                <a href="{{ route('consultations.export.message.word', [$session, $msg]) }}"
                                                    class="flex items-center gap-2 px-3 py-1.5 text-[10px] text-[#071833] hover:bg-[#f6f8fb]">
                                                    Word
                                                </a>
                                            </div>
                                        </div>
                                    </div>
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
                    <div x-show="attachments.length > 0" class="mb-3 flex flex-wrap gap-2">
                        <template x-for="(file, index) in attachments" :key="index">
                            <div class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-[#f6f8fb] ring-1 ring-[#e7eaf0] text-[10px] text-[#071833]">
                                <svg class="w-3 h-3 text-[#c99a3e]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span x-text="file.name" class="max-w-[120px] truncate"></span>
                                <button type="button" @click="removeAttachment(index)" class="text-[#667085] hover:text-rose-500 transition">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    <form @submit.prevent="send()" class="space-y-3">
                        <div class="flex items-start gap-2">
                            <label class="relative flex-1">
                                <textarea x-model="question" :disabled="sending" rows="2" maxlength="4000" class="input-premium resize-none"
                                    placeholder="Tanya Kak Vesta tentang regulasi…"></textarea>
                            </label>
                            <div class="flex flex-col gap-1">
                                <label for="file-upload" class="cursor-pointer shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-[#f6f8fb] ring-1 ring-[#e7eaf0] text-[#667085] hover:text-[#c99a3e] hover:ring-[#c99a3e] transition @disabled($session->messages->count() >= 50)">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </label>
                                <input id="file-upload" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" @change="handleFiles($event)" class="hidden" :disabled="sending || attachments.length >= 3">
                                <button type="submit" :disabled="sending || (!question.trim() && attachments.length === 0)"
                                    class="shrink-0 inline-flex items-center justify-center gap-2 h-10 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-[#c99a3e] to-[#b17c24] hover:brightness-110 transition disabled:opacity-60 disabled:cursor-not-allowed">
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
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-[10px] text-[#667085]">
                            <span>Analisa lintas regulasi — bandingkan pasal, cek irisan, temukan kewajiban. Maks 3 file (PDF, Word, Excel, Gambar).</span>
                            <span x-text="question.length + ' / 4000'"></span>
                        </div>
                    </form>
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
                attachments: [],
                handleFiles(event) {
                    const files = Array.from(event.target.files);
                    if (files.length === 0) return;
                    const remaining = 3 - this.attachments.length;
                    if (remaining <= 0) {
                        this.error = 'Maksimal 3 file per pesan.';
                        event.target.value = '';
                        return;
                    }
                    const toAdd = files.slice(0, remaining);
                    for (const file of toAdd) {
                        if (file.size > 10 * 1024 * 1024) {
                            this.error = `${file.name} terlalu besar. Maksimal 10MB per file.`;
                            continue;
                        }
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (!['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'].includes(ext)) {
                            this.error = `${file.name} format tidak didukung.`;
                            continue;
                        }
                        this.attachments.push(file);
                    }
                    event.target.value = '';
                },
                removeAttachment(index) {
                    this.attachments.splice(index, 1);
                },
                async send() {
                    let q = this.question.trim();
                    if ((!q && this.attachments.length === 0) || this.sending) return;
                    this.sending = true;
                    this.error = '';
                    if (!q && this.attachments.length > 0) {
                        q = 'Mohon jelaskan dokumen/gambar yang saya unggah.';
                    }
                    const questionText = q;
                    const filesToUpload = [...this.attachments];
                    this.question = '';
                    this.attachments = [];

                    const userBubble = this.appendBubble('user', questionText, false, filesToUpload);
                    this.appendBubble('assistant', 'Kak Vesta sedang mengetik…', true);
                    try {
                        const formData = new FormData();
                        formData.append('question', questionText);
                        filesToUpload.forEach((file, index) => {
                            formData.append('attachments[' + index + ']', file);
                        });

                        const res = await fetch(url, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData,
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
                        this.appendGeneratedBubble('assistant', data.reply, data.generated_file, data.image_url, data.citations || [], data.confidence || 'low');
                    } catch (e) {
                        this.removeTyping();
                        this.appendBubble('assistant', 'Koneksi gagal. Coba lagi.');
                    } finally {
                        this.sending = false;
                    }
                },
                appendGeneratedBubble(role, text, generatedFile = null, imageUrl = null, citations = [], confidence = 'low') {
                    this.$refs.empty?.remove();
                    const wrap = document.createElement('div');
                    wrap.className = role === 'user' ?
                        'flex justify-end items-start gap-2.5' :
                        'flex justify-start items-start gap-2.5';
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

                    if (role === 'assistant') {
                        const meta = document.createElement('div');
                        meta.className = 'mt-2 text-[10px] text-[#667085]';
                        meta.textContent = `Confidence: ${confidence.charAt(0).toUpperCase() + confidence.slice(1)}. Tetap verifikasi ke dokumen asli.`;
                        bubble.appendChild(meta);

                        if (citations.length) {
                            const sources = document.createElement('div');
                            sources.className = 'mt-3 border-t border-[#e7eaf0] pt-2 text-xs';
                            const title = document.createElement('p');
                            title.className = 'font-bold text-[#071833]';
                            title.textContent = 'Sumber terverifikasi';
                            sources.appendChild(title);
                            citations.forEach((citation) => {
                                const item = document.createElement('div');
                                item.className = 'mt-2 rounded-lg bg-white p-2 ring-1 ring-[#e7eaf0]';
                                item.textContent = `${citation.source_label || 'Sumber regulasi'}${citation.page ? ` · Halaman ${citation.page}` : ''}${citation.quote ? `\n“${citation.quote}”` : ''}`;
                                sources.appendChild(item);
                            });
                            bubble.appendChild(sources);
                        }
                    }

                    if (role === 'user') {
                        wrap.appendChild(bubble);
                        wrap.appendChild(avatar);
                    } else {
                        wrap.appendChild(avatar);
                        wrap.appendChild(bubble);
                    }

                    if (generatedFile || imageUrl) {
                        const fileDiv = document.createElement('div');
                        fileDiv.className = 'mt-3 pt-3 border-t border-[#e7eaf0]';

                        if (imageUrl) {
                            const imgContainer = document.createElement('div');
                            imgContainer.className = 'relative rounded-xl overflow-hidden bg-gray-100';

                            const img = document.createElement('img');
                            img.src = imageUrl;
                            img.className = 'w-full max-w-md mx-auto rounded-xl';
                            img.alt = 'Generated Image';
                            img.onerror = function () {
                                const link = document.createElement('a');
                                link.href = imageUrl;
                                link.target = '_blank';
                                link.className = 'block text-center text-sm text-[#c99a3e] underline';
                                link.textContent = 'Gambar tidak dapat ditampilkan. Klik untuk membuka.';
                                imgContainer.innerHTML = '';
                                imgContainer.appendChild(link);
                            };

                            imgContainer.appendChild(img);

                            if (generatedFile) {
                                const downloadBtn = document.createElement('a');
                                downloadBtn.href = generatedFile.url;
                                downloadBtn.target = '_blank';
                                downloadBtn.className = 'absolute bottom-2 right-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/90 backdrop-blur text-xs font-semibold text-[#071833] hover:bg-white transition shadow-sm';
                                downloadBtn.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>Download';
                                imgContainer.appendChild(downloadBtn);
                            }

                            fileDiv.appendChild(imgContainer);
                        } else {
                            const fileCard = document.createElement('a');
                            fileCard.href = generatedFile.url;
                            fileCard.target = '_blank';
                            fileCard.className = 'flex items-center gap-3 p-3 rounded-xl bg-white ring-1 ring-[#e7eaf0] hover:ring-[#c99a3e] transition';

                            const iconDiv = document.createElement('div');
                            iconDiv.className = 'w-10 h-10 rounded-lg flex items-center justify-center ' +
                                (generatedFile.type === 'image' ? 'bg-purple-100' : 'bg-blue-100');

                            const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                            iconSvg.setAttribute('class', 'w-5 h-5');
                            iconSvg.setAttribute('fill', 'none');
                            iconSvg.setAttribute('viewBox', '0 0 24 24');
                            iconSvg.setAttribute('stroke', generatedFile.type === 'image' ? '#7c3aed' : '#2563eb');
                            iconSvg.setAttribute('stroke-width', '2');
                            iconSvg.innerHTML = generatedFile.type === 'image'
                                ? '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />'
                                : '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />';

                            iconDiv.appendChild(iconSvg);
                            fileCard.appendChild(iconDiv);

                            const infoDiv = document.createElement('div');
                            infoDiv.className = 'min-w-0 flex-1';

                            const filenameP = document.createElement('p');
                            filenameP.className = 'text-sm font-semibold text-[#071833] truncate';
                            filenameP.textContent = generatedFile.filename;
                            infoDiv.appendChild(filenameP);

                            const typeP = document.createElement('p');
                            typeP.className = 'text-xs text-[#667085]';
                            const formatLabel = generatedFile.format === 'xlsx' ? 'Excel' : generatedFile.format.toUpperCase();
                            typeP.textContent = generatedFile.type === 'image' ? 'Gambar' : 'Dokumen ' + formatLabel;
                            infoDiv.appendChild(typeP);

                            fileCard.appendChild(infoDiv);

                            const downloadDiv = document.createElement('div');
                            downloadDiv.className = 'shrink-0';
                            const downloadSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                            downloadSvg.setAttribute('class', 'w-5 h-5 text-[#c99a3e]');
                            downloadSvg.setAttribute('fill', 'none');
                            downloadSvg.setAttribute('viewBox', '0 0 24 24');
                            downloadSvg.setAttribute('stroke', 'currentColor');
                            downloadSvg.setAttribute('stroke-width', '2');
                            downloadSvg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />';
                            downloadDiv.appendChild(downloadSvg);
                            fileCard.appendChild(downloadDiv);

                            fileDiv.appendChild(fileCard);
                        }
                        bubble.appendChild(fileDiv);
                    }

                    this.$refs.messages.appendChild(wrap);
                    this.$nextTick(() => this.scrollBottom());
                },
                appendBubble(role, text, typing = false, files = []) {
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

                    if (files.length > 0 && role === 'user') {
                        const attachDiv = document.createElement('div');
                        attachDiv.className = 'mt-2 space-y-1';
                        files.forEach(file => {
                            const fileEl = document.createElement('div');
                            fileEl.className = 'flex items-center gap-1.5 text-[10px] text-white/80';
                            fileEl.innerHTML = '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg><span class="max-w-[120px] truncate">' + file.name + '</span>';
                            attachDiv.appendChild(fileEl);
                        });
                        bubble.appendChild(attachDiv);
                    }

                    if (role === 'user') {
                        wrap.appendChild(bubble);
                        wrap.appendChild(avatar);
                    } else {
                        wrap.appendChild(avatar);
                        wrap.appendChild(bubble);
                    }
                    this.$refs.messages.appendChild(wrap);
                    this.$nextTick(() => this.scrollBottom());
                    return bubble;
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
