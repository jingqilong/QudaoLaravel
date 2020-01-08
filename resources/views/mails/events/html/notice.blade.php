@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
尊敬的{{ $receiver_name }}：

您好！

您所提交的{{ $process_full_name }}已经{{$precess_result}}。感谢您的参与！

(注：此邮件无须回复！)祝您

健康快乐！

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
