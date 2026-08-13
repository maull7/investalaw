@extends('layouts.app')

@section('title', $legalCase->title)
@section('header', $legalCase->title)

@section('content')
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <p class="text-xs font-semibold tracking-[0.16em] uppercase text-[#c99a3e]">Analisa Kasus</p>
            <h2 class="mt-2 text-3xl font-bold text-[#071833] tracking-tight">{{ $legalCase->title }}</h2>
            @if($legalCase->case_number || $legalCase->court)
                <p class="mt-1.5 text-sm text-[#667085]">
                    {{ $legalCase->case_number }} {{ $legalCase->court ? '· '.$legalCase->court : '' }}
                </p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('legal-cases.edit', $legalCase) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d0d5dd] text-sm font-semibold text-[#071833] hover:bg-[#f8fafc] transition">Edit</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: summary + actions --}}
        <div class="lg:col-span-1 space-y-6">
            <x-card>
                <div class="flex items-center gap-2 mb-4">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $legalCase->status_case === 'ongoing' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-[#667085]' }}">
                        {{ $legalCase->status_case === 'ongoing' ? 'Berlangsung' : 'Selesai' }}
                    </span>
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-[#667085]">Parse PDF</dt>
                        <dd>
                            @if($legalCase->isParsed())
                                <x-badge color="emerald">Parsed</x-badge>
                            @else
                                <x-badge color="gray">Belum</x-badge>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-[#667085]">Analisa</dt>
                        <dd>
                            @if($legalCase->isAnalyzed())
                                <x-badge color="emerald">Selesai</x-badge>
                            @elseif($legalCase->isAiProcessing('analysis'))
                                <x-badge color="blue">Diproses</x-badge>
                            @else
                                <x-badge color="gray">Belum</x-badge>
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-5 space-y-2">
                    @if(! $legalCase->isAnalyzed() && ! $legalCase->isAiProcessing('analysis'))
                        <form method="POST" action="{{ route('legal-cases.generate', $legalCase) }}">
                            @csrf
                            <x-button type="submit" variant="primary" class="w-full">Generate Analisa</x-button>
                        </form>
                    @endif
                    @if($legalCase->isAiProcessing('analysis'))
                        <p class="text-xs text-center text-[#c99a3e]">Analisa sedang diproses di background. Refresh sebentar lagi.</p>
                    @endif
                </div>
            </x-card>

            @if($legalCase->regulations->isNotEmpty())
                <x-card>
                    <h4 class="text-sm font-bold text-[#071833] mb-3">Regulasi Relevan</h4>
                    <ul class="space-y-3">
                        @foreach($legalCase->regulations as $reg)
                            <li class="text-sm">
                                <a href="{{ route('regulations.show', $reg) }}" class="text-[#c99a3e] hover:underline font-semibold">
                                    {{ $reg->regulation_number }}
                                </a>
                                <p class="text-[#667085]">{{ $reg->title }} ({{ $reg->year }})</p>
                                @if($reg->pivot->explanation)
                                    <p class="mt-1.5 text-[13px] text-[#475467] leading-relaxed">
                                        {{ $reg->pivot->explanation }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>

        {{-- Right: analysis result --}}
        <div class="lg:col-span-2 space-y-6">
            <x-card>
                <h4 class="text-sm font-bold text-[#071833] mb-3">Analisa Hasil</h4>

                @if(! $legalCase->isAnalyzed())
                    <p class="text-sm text-[#667085]">
                        Belum ada hasil analisa. Parse &amp; generate analisa untuk mendapatkan ringkasan, dasar hukum, dan strategi.
                    </p>
                @else
                    @php $a = $legalCase->analysis; @endphp
                    <div class="space-y-5 text-sm text-[#333] leading-relaxed">
                        @if(! empty($a['ringkasan']))
                            <div>
                                <h5 class="text-xs font-bold uppercase tracking-wide text-[#c99a3e]">Ringkasan</h5>
                                <p class="mt-1.5 whitespace-pre-wrap">{{ $a['ringkasan'] }}</p>
                            </div>
                        @endif

                        @if(! empty($a['dasar_hukum']))
                            <div>
                                <h5 class="text-xs font-bold uppercase tracking-wide text-[#c99a3e]">Dasar Hukum</h5>
                                <ul class="mt-1.5 list-disc list-inside space-y-1">
                                    @foreach($a['dasar_hukum'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(! empty($a['pasal_yang_mungkin_dilanggar']))
                            <div>
                                <h5 class="text-xs font-bold uppercase tracking-wide text-[#c99a3e]">Pasal yang Mungkin Dilanggar</h5>
                                <ul class="mt-1.5 list-disc list-inside space-y-1">
                                    @foreach($a['pasal_yang_mungkin_dilanggar'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(! empty($a['analisa_struktur']) && is_array($a['analisa_struktur']))
                            <div>
                                <h5 class="text-xs font-bold uppercase tracking-wide text-[#c99a3e]">Analisa Struktur Gugatan</h5>
                                <div class="mt-1.5 space-y-3">
                                    @foreach(['posita' => 'Posita', 'petitum' => 'Petitum', 'eksepsi' => 'Eksepsi'] as $key => $label)
                                        @if(! empty($a['analisa_struktur'][$key]))
                                            <div>
                                                <p class="font-semibold text-[#071833]">{{ $label }}</p>
                                                <p class="text-[#667085] whitespace-pre-wrap">{{ $a['analisa_struktur'][$key] }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(! empty($a['strategi_peluang']))
                            <div>
                                <h5 class="text-xs font-bold uppercase tracking-wide text-[#c99a3e]">Strategi &amp; Peluang</h5>
                                <p class="mt-1.5 whitespace-pre-wrap">{{ $a['strategi_peluang'] }}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </x-card>

            @if($legalCase->isParsed())
                <x-card>
                    <h4 class="text-sm font-bold text-[#071833] mb-3">Teks Parsed <span class="text-[#b0b8c5] font-normal">({{ number_format(mb_strlen($legalCase->parsed_text)) }} karakter)</span></h4>
                    <div class="max-h-96 overflow-y-auto text-sm text-[#667085] whitespace-pre-wrap border border-[#e7eaf0] rounded-xl p-4">
                        {{ $legalCase->parsed_text }}
                    </div>
                </x-card>
            @endif
        </div>
    </div>
@endsection