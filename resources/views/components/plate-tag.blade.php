@props(['code' => 'HM', 'label', 'dark' => false])
{{-- Section label drawn as a number plate: a code panel and the label. --}}
<p {{ $attributes->class(['plate-tag', 'on-dark' => $dark]) }}><span class="code" aria-hidden="true">{{ $code }}</span><span class="label">{{ $label }}</span></p>
