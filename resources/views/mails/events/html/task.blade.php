@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    遵敬的{{ $receiver_name }}：

    您好！

    渠道PLUS OA的工作流程{{ $process_full_name }}中又有新的审核流程需要处理了。请您及时处理。[点击这里可以打开处理页面]({{$link_url}})。谢谢！

    (注：此邮件无须回复！)祝您

    健康快乐！

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
