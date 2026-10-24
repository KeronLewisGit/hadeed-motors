@props(['rows'])
{{-- Spec sheet: label, dotted leader, value. --}}
<dl {{ $attributes->class(['spec']) }}>
    @foreach ($rows as $label => $value)
        @continue($value === null || $value === '')
        <div><dt>{{ $label }}</dt><span class="leader" aria-hidden="true"></span><dd>{{ $value }}</dd></div>
    @endforeach
</dl>
