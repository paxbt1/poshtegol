@props(['class' => ''])
<div {{ $attributes->merge(['class' => trim('card '.$class)]) }}>
    {{ $slot }}
</div>
