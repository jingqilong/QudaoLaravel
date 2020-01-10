@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {{$content}}
    {{-- Subcopy --}}
    @slot('subcopy')
        @component('mail::subcopy')
<p style='text-align: right'>上海渠道商务咨询有限公司</p>
<p style='text-align: right'>@php(print date("Y年m月d日 H时n分"))</p>
        @endcomponent
    @endslot

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }} 版权所有
        @endcomponent
    @endslot
@endcomponent
