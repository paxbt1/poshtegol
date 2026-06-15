@props(['label', 'value', 'hint' => null])
<x-ui.card>
    <div class="muted small">{{ $label }}</div>
    <div style="font-size:24px; font-weight:900; margin-top:8px;">{{ $value }}</div>
    @if($hint)<div class="muted small" style="margin-top:6px;">{{ $hint }}</div>@endif
</x-ui.card>
