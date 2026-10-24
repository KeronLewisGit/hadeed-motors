@props(['value', 'highlight' => 0, 'roll' => false])
{{-- Odometer numerals: one drum window per character. The last $highlight digits get the coral drum. --}}
@php($chars = mb_str_split((string) $value))
<span {{ $attributes->class(['odo', 'roll' => $roll]) }} aria-label="{{ $value }}" role="img">
    @foreach ($chars as $i => $c)
        <span @class(['sep' => ! ctype_digit($c), 'hl' => ctype_digit($c) && $i >= count($chars) - $highlight]) style="--i: {{ $i }}" aria-hidden="true">{{ $c }}</span>
    @endforeach
</span>
