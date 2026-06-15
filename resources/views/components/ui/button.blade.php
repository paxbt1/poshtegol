@props(['variant' => 'primary'])
<button {{ $attributes->merge(['class' => 'btn btn-'.$variant]) }}>
    {{ $slot }}
</button>
