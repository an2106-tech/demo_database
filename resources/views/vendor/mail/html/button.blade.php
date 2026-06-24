@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener">
<span class="button-text">{!! $slot !!}</span>
<span class="button-icon">→</span>
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
